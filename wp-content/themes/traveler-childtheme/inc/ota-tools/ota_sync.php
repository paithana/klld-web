<?php
/**
 * OTA Review Sync - Automation Script
 * Can be run via Cron or URL: ota_sync.php?secret=YOUR_SECRET
 */

// ── Configuration ──────────────────────────────────────────────────────────
$secret = 'kld_sync_2024'; // Change this for security
$max_per_source = 50;      // Limit reviews per sync to avoid timeouts

// ── Load WordPress ────────────────────────────────────────────────────────
// Load WordPress
if ( ! defined( 'ABSPATH' ) ) {
    $search_path = __DIR__;
    $found = false;
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($search_path . '/wp-load.php')) {
            require_once $search_path . '/wp-load.php';
            $found = true;
            break;
        }
        $parent = dirname($search_path);
        if ($parent === $search_path) break;
        $search_path = $parent;
    }
    
    if (!$found) {
        // Absolute fallback for this specific server if discovery fails
        $abs_path = '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php';
        if (file_exists($abs_path)) {
            require_once $abs_path;
        } else {
            die("Fatal: Could not find wp-load.php (Last search: $search_path)");
        }
    }
}

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
        if (!current_user_can('manage_options')) {
            http_response_code(403);
            die('Unauthorized.');
        }
    }
    
    if (isset($_GET['clean'])) {
        global $wpdb;
        $count = $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_author LIKE '%object Object%'");
        $wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)");
        die("Cleaned $count bad entries.");
    }
}

// ── Global overrides for targeted execution ──────────────────────────────────
// Detect CLI vs Browser and parse arguments
if (PHP_SAPI === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $_CLI);
    $target_post_id = isset($_CLI['post_id']) ? intval($_CLI['post_id']) : null;
    $target_limit   = isset($_CLI['limit']) ? intval($_CLI['limit']) : 1000;
    $target_offset  = isset($_CLI['offset']) ? intval($_CLI['offset']) : 0;
    $force_sync     = (isset($_CLI['force']) && $_CLI['force'] == '1');
    $format         = 'text';
} else {
    $target_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
    $target_limit   = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
    $target_offset  = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $force_sync     = (isset($_GET['force']) && $_GET['force'] == '1');
    $format         = isset($_GET['format']) ? $_GET['format'] : 'html';
}

if ($format === 'json') {
    ob_start(); 
}

set_time_limit(300); // 5 minutes

class OTAReviewSync {
    private $sources = [
        'gyg' => [
            'meta' => '_gyg_activity_id',
            'review_meta' => 'gyg_review_id'
        ],
        'viator' => [
            'meta' => '_viator_activity_id',
            'review_meta' => 'viator_review_id'
        ]
        // TA, GMB, Trustpilot often require scraping or specific APIs not always suitable for basic server sync
    ];

    public function run() {
        echo "Starting OTA Sync at " . date('Y-m-d H:i:s') . "\n";
        $stats = ['tours' => 0, 'imported' => 0];
        $args = [
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_gyg_activity_id', 'compare' => 'EXISTS'],
                ['key' => '_viator_activity_id', 'compare' => 'EXISTS']
            ]
        ];

        global $target_post_id, $target_limit;
        
        $tours = get_posts($args);
        echo "Found " . count($tours) . " tours to process.\n";

        foreach ($tours as $tour) {
            if ($target_post_id && $tour->ID !== $target_post_id) continue;
            
            echo "Processing: {$tour->post_title} (#{$tour->ID})\n";
            
            $mode = 'auto';
            if (php_sapi_name() === 'cli' || PHP_SAPI === 'cli') {
                global $_CLI;
                $mode = $_CLI['mode'] ?? 'auto';
            } else {
                $mode = $_GET['mode'] ?? 'auto';
            }
            echo "  [DEBUG] Selected Mode: $mode\n";

            $gyg_id = get_post_meta($tour->ID, '_gyg_activity_id', true);
            $via_id = get_post_meta($tour->ID, '_viator_activity_id', true);

            global $force_sync;
            /**
             * Multilingual Duplication:
             * For each tour, we find all associated translation post IDs (EN, DE, FR, etc.)
             * and sync reviews to each one.
             */
            $localized_ids = $this->get_translated_post_ids($tour->ID);
            
            foreach ($localized_ids as $target_post_id) {
                if ($gyg_id) {
                    $this->sync_gyg($target_post_id, $gyg_id, $target_limit, $force_sync, $mode);
                }
                if ($via_id) {
                    $this->sync_viator($target_post_id, $via_id, $target_limit, $force_sync);
                }
            }

            // Recalculate summary
            $this->update_summary($tour->ID);
            $stats['tours']++;
            // Note: need to capture imported counts from sync_ methods if we want granular stats here
        }

        echo "Sync complete.\n";
        return $stats;
    }

    private function sync_gyg($post_id, $activity_id, $limit_total = 1000, $force = false, $mode = 'auto') {
        $batch = 100;
        $offset = 0;
        $total_imported = 0;
        $consecutive_existing = 0;
        $max_skipped = $force ? 5000 : 10; // Stop after 10 existing reviews found in a row (historical sync caught up)

        $user = get_option('_gyg_integrator_user');
        $pass = get_option('_gyg_integrator_pass');
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'KLLD Sync Agent'
        ];
        if ($user && $pass) {
            $headers['Authorization'] = 'Basic ' . base64_encode("$user:$pass");
        }

        while ($offset < $limit_total) {
            $response_body = '';
            
            // 1. Try Partner API (v1 reviews) if key exists (Skip if mode is 'traveler')
            $partner_key = get_option('_gyg_partner_api_key');
            if ($partner_key && $mode !== 'traveler') {
                $currency = function_exists('st_get_default_currency') ? st_get_default_currency() : 'THB';
                $partner_url = "https://api.getyourguide.com/1/reviews/tour/{$activity_id}?cnt_language=en&currency={$currency}&limit={$batch}&offset={$offset}&sortfield=date&sortdirection=DESC";
                
                echo "  [GYG] Trying Partner API: $partner_url\n";
                $resp = wp_remote_get($partner_url, [
                    'timeout' => 30,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => strpos($partner_key, ' ') === false ? 'Bearer ' . $partner_key : $partner_key,
                        'X-ACCESS-TOKEN' => $partner_key,
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ]
                ]);
                
                if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                    $response_body = wp_remote_retrieve_body($resp);
                }
            }

            // 2. Fallback to Integrator API if no Partner response yet (Skip if mode is 'traveler')
            if (!$response_body && $mode !== 'traveler') {
                $url = "https://api.getyourguide.com/integrator/v1/activities/{$activity_id}/reviews?limit={$batch}&offset={$offset}";
                echo "  [GYG] Fetching Integrator API: $url\n";
                $resp = wp_remote_get($url, [ 'timeout' => 30, 'headers' => $headers ]);
                
                if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                    $response_body = wp_remote_retrieve_body($resp);
                } else {
                    $code = is_wp_error($resp) ? 'WP_Error' : wp_remote_retrieve_response_code($resp);
                    echo "  [GYG] Integrator API Error: $code. Trying legacy reception...\n";
                    
                    $reception_url = "https://api.getyourguide.com/reception/v1/activities/{$activity_id}/reviews?limit={$batch}&offset={$offset}&sort=date:desc";
                    $resp = wp_remote_get($reception_url, [ 'timeout' => 30, 'headers' => $headers ]);
                    
                    if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                        $response_body = wp_remote_retrieve_body($resp);
                    }
                }
            }

            // 3. Trying Travelers-API fallback (Only if mode is 'auto' or 'traveler')
            if (!$response_body && ($mode === 'auto' || $mode === 'traveler')) {
                $fallback_url = "https://travelers-api.getyourguide.com/activities/{$activity_id}/reviews?limit={$batch}";
                echo "  [GYG] Trying Travelers-API Fallback: $fallback_url\n";
                $resp = wp_remote_get($fallback_url, [
                    'timeout' => 20,
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ]
                ]);
                if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                    $response_body = wp_remote_retrieve_body($resp);
                }
            }

            if (!$response_body) {
                echo "  [GYG] ALL Methods Failed for activity $activity_id. Skipping batch.\n";
                break;
            }

            $data = json_decode($response_body, true);
            $reviews = $data['reviews'] ?? $data['data']['reviews'] ?? $data['data'] ?? [];
            if (empty($reviews)) break;

            foreach ($reviews as $r) {
                $review_id = (string)($r['reviewId'] ?? $r['id'] ?? '');
                if (!$review_id || $review_id === 'undefined') continue;

                $t = $r['traveler'] ?? $r['author'] ?? [];
                $author = is_array($t) ? ($t['firstName'] ?? $t['fullName'] ?? $t['name'] ?? 'Anonymous') : (is_string($t) ? $t : 'Anonymous');
                $c = $r['message'] ?? $r['text'] ?? $r['comment'] ?? '';
                $content = is_array($c) ? ($c['message'] ?? $c['text'] ?? '') : (is_string($c) ? $c : '');

                $comment_id = $this->upsert_review($post_id, 'gyg', $review_id, [
                    'author' => $author,
                    'content' => $content,
                    'rating' => round($r['overallRating'] ?? $r['rating'] ?? 5),
                    'date' => $r['travelDate'] ?? $r['date'] ?? $r['created'] ?? ''
                ]);

                if ($comment_id) {
                    $total_imported++;
                    $consecutive_existing = 0;
                    // Extract Photos
                    $photos = [];
                    foreach (($r['media'] ?? []) as $m) if (isset($m['url'])) $photos[] = $m['url'];
                    if (!empty($photos)) update_comment_meta($comment_id, 'ota_review_photos', $photos);
                } else {
                    $consecutive_existing++;
                }

                if ($consecutive_existing >= $max_skipped && $offset > 0) break 2;
            }

            $offset += count($reviews);
            if (count($reviews) < $batch) break;
        }
        echo "  - GYG Total: Sync'd $total_imported reviews for $activity_id\n";
    }

    private function sync_viator($post_id, $product_code, $limit_total = 100, $force = false) {
        $offset = 0;
        $batch = 50;
        $total_imported = 0;
        $consecutive_existing = 0;
        $max_skipped = $force ? 9999 : 5;

        while ($offset < $limit_total) {
            $url = "https://www.viator.com/api/product/reviews-v2?productCode={$product_code}&offset={$offset}&limit={$batch}&sort=NEWEST";
            echo "  [Viator] Fetching: $url\n";
            $resp = wp_remote_get($url, [
                'timeout' => 20,
                'headers' => [
                    'Accept' => 'application/json',
                    'Referer' => 'https://www.viator.com/',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
                ]
            ]);
            if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
                echo "  [Viator] Error: " . (is_wp_error($resp) ? $resp->get_error_message() : wp_remote_retrieve_response_code($resp)) . "\n";
                break;
            }

            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $reviews = $data['reviews'] ?? [];
            if (empty($reviews)) break;

            foreach ($reviews as $r) {
                $review_id = (string)($r['reviewReferenceId'] ?? $r['id'] ?? '');
                if (!$review_id) continue;

                $c = $r['reviewText'] ?? $r['text'] ?? '';
                $content = is_array($c) ? ($c['text'] ?? $c['message'] ?? '') : (is_string($c) ? $c : '');

                $comment_id = $this->upsert_review($post_id, 'viator', $review_id, [
                    'author' => $r['userName'] ?? $r['authorName'] ?? 'Viator Traveler',
                    'content' => $content,
                    'rating' => $r['rating'] ?? 5,
                    'date' => $r['publishedDate'] ?? $r['date'] ?? ''
                ]);

                if ($comment_id) {
                    $total_imported++;
                    $consecutive_existing = 0;
                    // Extract Photos
                    $photos = [];
                    foreach (($r['photos'] ?? []) as $m) if (isset($m['photoUrl'])) $photos[] = $m['photoUrl'];
                    if (!empty($photos)) update_comment_meta($comment_id, 'ota_review_photos', $photos);
                } else {
                    $consecutive_existing++;
                }

                if ($consecutive_existing >= $max_skipped && $offset > 0) break 2;
            }
            $offset += count($reviews);
            if (count($reviews) < $batch) break;
        }
        echo "  - Viator: Sync'd $total_imported reviews for $product_code\n";
    }

    public function upsert_review($post_id, $source, $remote_id, $data) {
        global $wpdb;
        $meta_key = $source . '_review_id';
        
        // Check if exists for THIS specific post
        $comment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT cm.comment_id 
             FROM {$wpdb->commentmeta} cm
             JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
             WHERE cm.meta_key = %s AND cm.meta_value = %s AND c.comment_post_ID = %d 
             LIMIT 1",
            $meta_key, $remote_id, $post_id
        ));

        if ($comment_id) return false;

        $rating = (float)$data['rating'];
        $title = !empty($data['title']) ? $data['title'] : wp_trim_words($data['content'], 6, '...');
        if (!$title) $title = "Expert tour from " . ucfirst($source);

        // Prepare localized sub-rating labels
        $lang = $this->get_post_language($post_id);
        $labels = $this->get_sub_rating_labels($lang);

        // Prepare serialized stats for Traveler Theme
        $stats = [
            $labels['statItinerary'] => (float)($data['statItinerary'] ?? $rating),
            $labels['statGuide']      => (float)($data['statGuide'] ?? $rating),
            $labels['statService']    => (float)($data['statService'] ?? $rating),
            $labels['statDriver']     => (float)($data['statDriver'] ?? $rating),
            $labels['statFood']       => (float)($data['statFood'] ?? $rating)
        ];
        $serialized_stats = serialize($stats);

        $comment_data = [
            'comment_post_ID' => $post_id,
            'comment_author' => $data['author'],
            'comment_author_email' => sanitize_title($data['author']) . '@' . $source . '.com',
            'comment_content' => $data['content'],
            'comment_type' => 'st_reviews',
            'comment_approved' => '1',
            'comment_date' => $this->normalize_date($data['date']),
        ];

        $new_id = wp_insert_comment($comment_data);
        if ($new_id) {
            update_comment_meta($new_id, 'st_reviews', $rating);
            update_comment_meta($new_id, 'st_star', $rating);
            update_comment_meta($new_id, 'comment_rate', $rating);
            update_comment_meta($new_id, 'comment_title', $title);
            update_comment_meta($new_id, 'st_review_stats', $serialized_stats);
            
            // Sub-rating individual keys (localized)
            foreach ($stats as $label => $val) {
                $meta_key_sub = 'st_stat_' . sanitize_title($label);
                update_comment_meta($new_id, $meta_key_sub, $val);
            }

            update_comment_meta($new_id, $meta_key, $remote_id);
            update_comment_meta($new_id, 'ota_source', $source);
            update_comment_meta($new_id, '_comment_like_count', 0);
            
            file_put_contents(__DIR__ . '/ota_sync_log.txt', "[" . date('Y-m-d H:i:s') . "] Imported $source review $remote_id for Post $post_id\n", FILE_APPEND);
            return $new_id;
        }
        return false;
    }

    public function normalize_date($d) {
        if (!$d) return current_time('mysql');
        $time = strtotime($d);
        return $time ? date('Y-m-d H:i:s', $time) : current_time('mysql');
    }

    public function update_summary($post_id) {
        global $wpdb;
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total, AVG(CAST(m.meta_value AS DECIMAL(10,1))) as avg 
             FROM $wpdb->comments c 
             JOIN $wpdb->commentmeta m ON c.comment_ID = m.comment_id 
             WHERE c.comment_post_ID = %d AND c.comment_approved = '1' AND m.meta_key = 'st_reviews'",
            $post_id
        ));

        if ($stats) {
            update_post_meta($post_id, 'total_review', $stats->total);
            update_post_meta($post_id, 'rate_review', round($stats->avg, 1));
        }
    }

    public function get_translated_post_ids($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'icl_translations';
        
        $trid = $wpdb->get_var($wpdb->prepare(
            "SELECT trid FROM {$table} WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1",
            $post_id
        ));

        if (!$trid) return [$post_id];

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT element_id FROM {$table} WHERE trid = %d AND element_type = 'post_st_tours'",
            $trid
        ));

        return !empty($ids) ? $ids : [$post_id];
    }

    public function get_post_language($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'icl_translations';
        $lang = $wpdb->get_var($wpdb->prepare(
            "SELECT language_code FROM {$table} WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1",
            $post_id
        ));
        return $lang ?: 'en';
    }

    public function get_sub_rating_labels($lang = 'en') {
        $labels = [
            'en' => [
                'statGuide' => 'Tour guide',
                'statDriver' => 'Transportation',
                'statService' => 'Service',
                'statItinerary' => 'Organization',
                'statFood' => 'Food'
            ],
            'de' => [
                'statGuide' => 'Reiseleiter',
                'statDriver' => 'Transport',
                'statService' => 'Service',
                'statItinerary' => 'Organisation',
                'statFood' => 'Essen'
            ],
            'fr' => [
                'statGuide' => 'Guide touristique',
                'statDriver' => 'Transport',
                'statService' => 'Service',
                'statItinerary' => 'Organisation',
                'statFood' => 'Nourriture'
            ]
        ];
        return isset($labels[$lang]) ? $labels[$lang] : $labels['en'];
    }
}

if ( ! defined( 'KLLD_SYNC_NO_RUN' ) ) {
    $sync = new OTAReviewSync();
    $results = $sync->run();

    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        $log = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'log' => $log,
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
