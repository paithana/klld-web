<?php
/**
 * OTA Content Sync Tool
 * Pulls tour descriptions, highlights, and images from GYG/Viator.
 */

if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php',
    ];
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

if ( ! current_user_can( 'manage_options' ) && PHP_SAPI !== 'cli' ) {
    wp_die( 'Unauthorized.' );
}

class KLLD_OTA_Content {
    private $gyg_user;
    private $gyg_pass;

    public function __construct() {
        $this->gyg_user = get_option('_gyg_integrator_user');
        $this->gyg_pass = get_option('_gyg_integrator_pass');
    }

    public function fetch_gyg_activity($activity_id) {
        if (!$this->gyg_user || !$this->gyg_pass) {
            return new WP_Error('missing_auth', 'GYG Integrator credentials not configured.');
        }

        $url = "https://api.getyourguide.com/integrator/v1/activities/{$activity_id}";
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->gyg_user . ':' . $this->gyg_pass)
            ]
        ]);

        if (is_wp_error($response)) return $response;
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['message'] ?? 'Unknown GYG API error');
        }

        return $data;
    }

    public function update_tour_content($post_id, $data) {
        // Map GYG data to Traveler fields
        $activity = $data['activity'] ?? [];
        if (empty($activity)) return false;

        // 1. Description (post_content)
        $description = $activity['description'] ?? '';
        
        // 2. Highlights (st_highlights)
        $highlights = "";
        if (!empty($activity['highlights'])) {
            $highlights = "<ul><li>" . implode("</li><li>", $activity['highlights']) . "</li></ul>";
        }

        // 3. Inclusions / Exclusions
        $inclusions = "";
        if (!empty($activity['inclusions'])) {
            $inclusions = implode("\n", array_column($activity['inclusions'], 'description'));
        }
        $exclusions = "";
        if (!empty($activity['exclusions'])) {
            $exclusions = implode("\n", array_column($activity['exclusions'], 'description'));
        }

        // 4. Itinerary (tours_program)
        $itinerary = [];
        if (!empty($activity['itinerary'])) {
            foreach ($activity['itinerary'] as $index => $item) {
                $itinerary[] = [
                    'title' => $item['title'] ?? "Step " . ($index + 1),
                    'desc' => $item['description'] ?? '',
                    'image' => ''
                ];
            }
        }

        // Update Post
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $description
        ]);

        // Update Meta
        update_post_meta($post_id, 'tours_highlight', $highlights);
        update_post_meta($post_id, 'tours_include', $inclusions);
        update_post_meta($post_id, 'tours_exclude', $exclusions);
        
        if (!empty($itinerary)) {
            update_post_meta($post_id, 'tours_program', $itinerary);
            update_post_meta($post_id, 'tours_program_style', 'style1');
        }

        return true;
    }
}

// CLI / Web Handler
if (isset($_GET['action']) || PHP_SAPI === 'cli') {
    $content_sync = new KLLD_OTA_Content();
    $action = $_GET['action'] ?? ($argv[1] ?? 'fetch');
    $post_id = intval($_GET['post_id'] ?? ($argv[2] ?? 0));
    $activity_id = $_GET['activity_id'] ?? ($argv[3] ?? '');

    if (!$activity_id && $post_id) {
        $activity_id = get_post_meta($post_id, '_gyg_activity_id', true);
    }

    if ($action === 'fetch' && $activity_id) {
        $data = $content_sync->fetch_gyg_activity($activity_id);
        if (is_wp_error($data)) {
            echo json_encode(['success' => false, 'message' => $data->get_error_message()]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
        }
    } elseif ($action === 'sync' && $post_id && $activity_id) {
        $data = $content_sync->fetch_gyg_activity($activity_id);
        if (!is_wp_error($data)) {
            $success = $content_sync->update_tour_content($post_id, $data);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => $data->get_error_message()]);
        }
    } elseif ($action === 'import_json') {
        $raw_data = file_get_contents('php://input');
        $data = json_decode($raw_data, true);
        
        if (empty($data['source_url'])) {
            echo json_encode(['success' => false, 'message' => 'No URL provided.']);
            exit;
        }

        // Find the tour by meta (GYG activity ID or URL)
        global $wpdb;
        $post_id = 0;
        
        if ($data['source'] === 'gyg' && preg_match('/-t(\d+)\//', $data['source_url'], $matches)) {
            $activity_id = $matches[1];
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_gyg_activity_id' AND meta_value = %s", $activity_id));
        } elseif ($data['source'] === 'tripadvisor' && preg_match('/-d(\d+)-/', $data['source_url'], $matches)) {
            $ta_id = $matches[1];
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_tripadvisor_activity_id' AND meta_value = %s", $ta_id));
        } elseif ($data['source'] === 'viator' && preg_match('/-p(\d+)/', $data['source_url'], $matches)) {
            $via_id = $matches[1];
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_viator_activity_id' AND meta_value = %s", $via_id));
        }

        if (!$post_id) {
            echo json_encode(['success' => false, 'message' => 'Could not find a matching tour for this URL. Please map it first.']);
            exit;
        }

        // Format for update_tour_content
        $formatted_data = [
            'activity' => [
                'description' => $data['description'],
                'highlights' => $data['highlights'],
                'inclusions' => array_map(function($i) { return ['description' => $i]; }, $data['inclusions']),
                'exclusions' => array_map(function($i) { return ['description' => $i]; }, $data['exclusions']),
                'itinerary' => $data['itinerary']
            ]
        ];

        $success = $content_sync->update_tour_content($post_id, $formatted_data);
        echo json_encode(['success' => $success, 'post_id' => $post_id]);
        exit;
    }
}
