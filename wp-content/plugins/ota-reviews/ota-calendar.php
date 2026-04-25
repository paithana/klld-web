<?php
/**
 * OTA Calendar Sync Tool
 * Pulls availability from GYG and updates Traveler table.
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

class KLLD_OTA_Calendar {
    private $gyg_user;
    private $gyg_pass;

    public function __construct() {
        $this->gyg_user = get_option('_gyg_integrator_user');
        $this->gyg_pass = get_option('_gyg_integrator_pass');
    }

    public function fetch_gyg_availability($activity_id, $start_date = null, $end_date = null) {
        if (!$this->gyg_user || !$this->gyg_pass) {
            return new WP_Error('missing_auth', 'GYG Integrator credentials not configured.');
        }

        if (!$start_date) $start_date = date('Y-m-d');
        if (!$end_date)   $end_date   = date('Y-m-d', strtotime('+3 months'));

        $url = "https://api.getyourguide.com/integrator/v1/activities/{$activity_id}/availabilities?from={$start_date}&to={$end_date}";
        
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

        return $data;
    }

    public function sync_to_db($post_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'st_tour_availability';
        
        $availabilities = $data['availabilities'] ?? [];
        if (empty($availabilities)) return 0;

        $count = 0;
        foreach ($availabilities as $av) {
            $ts = strtotime($av['dateTime']);
            $date_str = date('Y-m-d', $ts);
            $check_in = $ts;
            $check_out = $ts; // Single day tour usually

            $adult_price = 0;
            $child_price = 0;
            foreach (($av['pricesByCategory']['retailPrices'] ?? []) as $p) {
                if ($p['category'] === 'ADULT') $adult_price = $p['price'] / 100; // API usually returns in cents
                if ($p['category'] === 'CHILD') $child_price = $p['price'] / 100;
            }

            // Upsert
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE post_id = %d AND check_in = %d",
                $post_id, $check_in
            ));

            $data_array = [
                'post_id' => $post_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'adult_price' => $adult_price,
                'child_price' => $child_price,
                'price' => $adult_price,
                'status' => $av['vacancies'] > 0 ? 'available' : 'unavailable',
                'number' => $av['vacancies'],
                'is_base' => 1
            ];

            if ($existing) {
                $wpdb->update($table, $data_array, ['id' => $existing]);
            } else {
                $wpdb->insert($table, $data_array);
            }
            $count++;
        }

        return $count;
    }
}

// CLI / Web Handler
if (isset($_GET['action']) || PHP_SAPI === 'cli') {
    $cal_sync = new KLLD_OTA_Calendar();
    $action = $_GET['action'] ?? ($argv[1] ?? 'fetch');
    $post_id = intval($_GET['post_id'] ?? ($argv[2] ?? 0));
    $activity_id = $_GET['activity_id'] ?? ($argv[3] ?? '');

    if (!$activity_id && $post_id) {
        $activity_id = get_post_meta($post_id, '_gyg_activity_id', true);
    }

    if ($action === 'fetch' && $activity_id) {
        $data = $cal_sync->fetch_gyg_availability($activity_id);
        header('Content-Type: application/json');
        echo json_encode($data);
    } elseif ($action === 'sync' && $post_id && $activity_id) {
        $data = $cal_sync->fetch_gyg_availability($activity_id);
        if (!is_wp_error($data)) {
            $count = $cal_sync->sync_to_db($post_id, $data);
            echo json_encode(['success' => true, 'synced' => $count]);
        } else {
            echo json_encode(['success' => false, 'message' => $data->get_error_message()]);
        }
    }
}
