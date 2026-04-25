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
    if ( ! defined( 'ABSPATH' ) ) {
        die("Fatal: Could not find wp-load.php.");
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
    $cli_args = isset($argv) && is_array($argv) ? array_slice($argv, 1) : [];
    parse_str(implode('&', $cli_args), $_CLI);
    $target_post_id = isset($_CLI['post_id']) ? intval($_CLI['post_id']) : null;
    $target_limit   = (isset($_CLI['limit']) && $_CLI['limit'] != '0') ? intval($_CLI['limit']) : 10000;
    $target_offset  = isset($_CLI['offset']) ? intval($_CLI['offset']) : 0;
    $force_sync     = (isset($_CLI['force']) && $_CLI['force'] == '1');
    $format         = 'text';
} else {
    $target_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
    $target_limit   = (isset($_GET['limit']) && $_GET['limit'] != '0') ? intval($_GET['limit']) : 10000;
    $target_offset  = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $force_sync     = (isset($_GET['force']) && $_GET['force'] == '1');
    $format         = isset($_GET['format']) ? $_GET['format'] : 'html';
}

if ($format === 'json') {
    ob_start(); 
}

set_time_limit(0); // No time limit for large syncs

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

    public function run($limits = null, $force_sync = null) {
        if ($limits === null) {
            $limits = isset($_GET['limits']) ? intval($_GET['limits']) : (isset($GLOBALS['argv'][1]) && is_numeric($GLOBALS['argv'][1]) ? intval($GLOBALS['argv'][1]) : 10);
        }
        if ($force_sync === null) {
            $force_sync = isset($_GET['force']) || (isset($GLOBALS['argv'][2]) && $GLOBALS['argv'][2] == 'force');
        }

        echo "Starting OTA Sync (Limits: $limits) at " . date('Y-m-d H:i:s') . "\n";
        $stats = ['tours' => 0, 'imported' => 0];
        global $wpdb;
        $mapped_ids = $wpdb->get_col("
            SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key IN ('_gyg_activity_id', '_viator_activity_id', '_tripadvisor_activity_id', '_gmb_id')
        ");

        if (empty($mapped_ids)) {
            echo "No tours with OTA mappings found.\n";
            return $stats;
        }

        $args = [
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'private', 'draft', 'trash'],
            'post__in' => $mapped_ids,
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        ];

        global $target_post_id;
        $target_limit = $limits ?: ($GLOBALS['target_limit'] ?? 10000);
        
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

            $force_sync = $force_sync ?? ($GLOBALS['force_sync'] ?? false);
            /**
             * Multilingual Duplication:
             * For each tour, we find all associated translation post IDs (EN, DE, FR, etc.)
             * and sync reviews to each one.
             */
            $localized_ids = $this->get_translated_post_ids($tour->ID);
            
            foreach ($localized_ids as $loc_id) {
                if ($gyg_id) {
                    $this->sync_gyg($loc_id, $gyg_id, $target_limit, $force_sync, $mode);
                }
                if ($via_id) {
                    $this->sync_viator($loc_id, $via_id, $target_limit, $force_sync);
                }
                
                $ta_id = get_post_meta($tour->ID, '_tripadvisor_activity_id', true);
                if ($ta_id) {
                    $this->sync_tripadvisor($loc_id, $ta_id, $target_limit, $force_sync);
                }

                $gmb_id = get_post_meta($tour->ID, '_gmb_id', true);
                if ($gmb_id) {
                    $this->sync_gmb($loc_id, $gmb_id, $target_limit, $force_sync);
                }
            }

            // Recalculate summary
            $this->update_summary($tour->ID);
            $stats['tours']++;
            // Note: need to capture imported counts from sync_ methods if we want granular stats here
        }

        $this->cleanup();

        echo "Sync complete.\n";
        return $stats;
    }

    public function cleanup() {
        global $wpdb;
        echo "🧹 Starting review cleanup (Duplicates & Invalid)...\n";

        // 1. Remove invalid entries (broken author/content)
        $invalid_count = $wpdb->query("
            DELETE FROM {$wpdb->comments} 
            WHERE (comment_author LIKE '%object Object%' OR comment_content LIKE '%object Object%')
            AND comment_type = 'st_reviews'
        ");
        if ($invalid_count) echo "   - Removed $invalid_count invalid 'object Object' reviews.\n";

        // 2. Remove duplicates (Same author, content, post, and date)
        $duplicates = $wpdb->get_results("
            SELECT MIN(comment_ID) as keep_id, comment_post_ID, comment_author, comment_content, comment_date, COUNT(*) as cnt
            FROM {$wpdb->comments}
            WHERE comment_type = 'st_reviews'
            GROUP BY comment_post_ID, comment_author, comment_content, comment_date
            HAVING cnt > 1
        ");
        
        $deleted = 0;
        foreach ($duplicates as $dup) {
            $ids_to_del = $wpdb->get_col($wpdb->prepare("
                SELECT comment_ID FROM {$wpdb->comments} 
                WHERE comment_post_ID = %d AND comment_author = %s AND comment_content = %s AND comment_date = %s AND comment_ID != %d
            ", $dup->comment_post_ID, $dup->comment_author, $dup->comment_content, $dup->comment_date, $dup->keep_id));
            
            if (!empty($ids_to_del)) {
                $ids_str = implode(',', array_map('intval', $ids_to_del));
                $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_ID IN ($ids_str)");
                $wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ($ids_str)");
                $deleted += count($ids_to_del);
            }
        }
        if ($deleted) echo "   - Cleaned up $deleted duplicate reviews.\n";
        
        echo "✅ Cleanup finished.\n";
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

        $offset = 0;
        $batch = 50;
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
                $fallback_url = "https://travelers-api.getyourguide.com/activities/{$activity_id}/reviews?limit={$batch}&offset={$offset}&sort=date:desc";
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
            
            // Dynamic Limit Detection
            if ($offset === 0) {
                $found_total = $data['totalCount'] ?? $data['total_reviews'] ?? $data['meta']['total_count'] ?? 0;
                echo "  [DEBUG] API reports totalCount: $found_total\n";
                if ($found_total > $limit_total) {
                    echo "  [GYG] Found $found_total total reviews. Extending sync limit.\n";
                    $limit_total = $found_total;
                }
            }

            if (empty($reviews)) {
                echo "  [DEBUG] No reviews found in response. Data keys: " . implode(',', array_keys((array)$data)) . "\n";
                break;
            }
            echo "  [DEBUG] Processing " . count($reviews) . " reviews (Offset: $offset)\n";

            foreach ($reviews as $r) {
                $review_id = (string)($r['reviewId'] ?? $r['id'] ?? '');
                if (!$review_id || $review_id === 'undefined') continue;

                $t = $r['traveler'] ?? $r['author'] ?? [];
                $author = is_array($t) ? ($t['firstName'] ?? $t['fullName'] ?? $t['name'] ?? 'Anonymous') : (is_string($t) ? $t : 'Anonymous');
                $c = $r['message'] ?? $r['text'] ?? $r['comment'] ?? '';
                $content = is_array($c) ? ($c['message'] ?? $c['text'] ?? '') : (is_string($c) ? $c : '');

                // Extract Photos
                $photos = [];
                foreach (($r['media'] ?? []) as $m) {
                    $media_urls = $m['urls'] ?? [];
                    $best_url = '';
                    foreach ($media_urls as $u) {
                        if ($u['size'] === 'gallery' || $u['size'] === 'large' || $u['size'] === 'desktop') {
                            $best_url = $u['url'];
                            break;
                        }
                    }
                    if (!$best_url && !empty($media_urls)) {
                        $best_url = end($media_urls)['url'] ?? '';
                    }
                    if ($best_url) $photos[] = $best_url;
                }

                $comment_id = $this->upsert_review($post_id, 'gyg', $review_id, [
                    'author' => $author,
                    'content' => $content,
                    'rating' => round($r['overallRating'] ?? $r['rating'] ?? 5),
                    'date' => $r['travelDate'] ?? $r['date'] ?? $r['created'] ?? '',
                    'photos' => $photos
                ]);

                if ($comment_id) {
                    $total_imported++;
                    $consecutive_existing = 0;
                } else {
                    $consecutive_existing++;
                }

                if ($consecutive_existing >= $max_skipped && $offset > 0) break 2;
            }

            $offset += count($reviews);
            if (count($reviews) < $batch) break;
            
            sleep(1); // Avoid 429 Too Many Requests
        }
        echo "  - GYG Total: Sync'd $total_imported reviews for $activity_id\n";
    }

    private function sync_viator($post_id, $product_code, $limit_total = 100, $force = false) {
        $offset = 0;
        $batch = 50; // Optimized for GYG API limits
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
                $code = is_wp_error($resp) ? 'WP_Error' : wp_remote_retrieve_response_code($resp);
                $body = wp_remote_retrieve_body($resp);
                $diag = "";
                if ($code == 403) {
                    if (strpos($body, 'DataDome') !== false) $diag = " [Blocked by DataDome]";
                    elseif (strpos($body, 'Cloudflare') !== false) $diag = " [Blocked by Cloudflare]";
                    else $diag = " [Forbidden/Bot Protection]";
                }
                echo "  [Viator] Error: {$code}{$diag}\n";
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

                // Extract Photos
                $photos = [];
                foreach (($r['photos'] ?? []) as $m) if (isset($m['photoUrl'])) $photos[] = $m['photoUrl'];

                $comment_id = $this->upsert_review($post_id, 'viator', $review_id, [
                    'author' => $r['userName'] ?? $r['authorName'] ?? 'Viator Traveler',
                    'content' => $content,
                    'rating' => $r['rating'] ?? 5,
                    'date' => $r['publishedDate'] ?? $r['date'] ?? '',
                    'photos' => $photos
                ]);

                if ($comment_id) {
                    $total_imported++;
                    $consecutive_existing = 0;
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

    private function sync_tripadvisor($post_id, $ta_id, $limit_total = 100, $force = false) {
        $api_key = get_option('_ta_api_key');
        if ($api_key && $ta_id) {
            return $this->sync_tripadvisor_api($post_id, $ta_id, $limit_total, $force);
        }

        $ta_url = get_post_meta($post_id, '_ta_url', true);
        if (!$ta_url) {
            echo "  [TA] Skipping sync: No URL provided for ID $ta_id\n";
            return;
        }

        $is_profile = (strpos($ta_url, 'Attraction_Review') !== false);
        echo "  [TA] Scraper-based " . ($is_profile ? "Profile" : "Product") . " sync for: $ta_url\n";
        // Logic similar to review_tool.php sync
        $resp = wp_remote_get($ta_url, [
            'timeout' => 20,
            'headers' => [
                'Accept' => 'text/html',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        ]);
        
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            $code = is_wp_error($resp) ? 'WP_Error' : wp_remote_retrieve_response_code($resp);
            $body = wp_remote_retrieve_body($resp);
            $diag = "";
            if ($code == 403) {
                if (strpos($body, 'Cloudflare') !== false) $diag = " [Blocked by Cloudflare]";
                else $diag = " [Forbidden/Bot Protection]";
            }
            echo "  [TA] Error: {$code}{$diag}\n";
            return;
        }

        $body = wp_remote_retrieve_body($resp);
        $reviews = [];
        
        // Use DOMDocument to extract data
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
        $xpath = new DOMXPath($dom);
        $cards = $xpath->query("//div[contains(@data-automation, 'reviewCard')]");
        
        $total_imported = 0;
        foreach ($cards as $card) {
            $remote_id = $card->getAttribute('id') ?: bin2hex(random_bytes(8));
            $text = $xpath->query(".//span[contains(@class, 'ySdfQ')]", $card)->item(0)?->nodeValue ?? '';
            $author = $xpath->query(".//a[contains(@href, '/Profile/')]", $card)->item(0)?->nodeValue ?? 
                      $xpath->query(".//span[contains(@class, 'biGQs')]", $card)->item(0)?->nodeValue ?? 'TA Traveler';
            
            $rating = 5;
            // Try SVG title first (Modern UI)
            $rating_svg = $xpath->query(".//svg[contains(@title, 'of 5 bubble')]", $card)->item(0);
            if ($rating_svg) {
                $title = $rating_svg->getAttribute('title');
                if (preg_match('/([0-9.]+)/', $title, $m)) $rating = floatval($m[1]);
            } else {
                // Fallback to legacy bubble class
                $bubbles = $xpath->query(".//span[contains(@class, 'ui_bubble_rating')]", $card)->item(0);
                if ($bubbles) {
                    $class = $bubbles->getAttribute('class');
                    if (preg_match('/bubble_(\d+)/', $class, $m)) $rating = intval($m[1]) / 10;
                }
            }

            if ($text) {
                // Extract Photos
                $photos = [];
                $imgs = $xpath->query(".//img", $card);
                foreach ($imgs as $img) {
                    $src = $img->getAttribute('data-src') ?: $img->getAttribute('data-lazy-src') ?: $img->getAttribute('src');
                    if ($src && strpos($src, '/media/photo-') !== false && strpos($src, 'avatar') === false) {
                        $photos[] = $src;
                    }
                }

                $comment_id = $this->upsert_review($post_id, 'tripadvisor', $remote_id, [
                    'author' => strip_tags($author),
                    'content' => $text,
                    'rating' => $rating,
                    'date' => '', // Dates are hard to scrape from simple HTML
                    'photos' => $photos
                ]);
                if ($comment_id) $total_imported++;
            }
        }
        echo "  - TA (Scraper): Scraped $total_imported reviews from $ta_url\n";
    }

    private function sync_tripadvisor_api($post_id, $ta_id, $limit_total = 100, $force = false) {
        $location_id = preg_replace('/[^0-9]/', '', $ta_id);
        $api_key = get_option('_ta_api_key');
        $batch = 5; 
        $offset = 0;
        $total_imported = 0;

        echo "  [TA] Official API sync for Location ID: $location_id\n";

        while ($offset < $limit_total) {
            $url = "https://api.content.tripadvisor.com/api/v1/location/{$location_id}/reviews?key={$api_key}&language=en&limit={$batch}&offset={$offset}";
            
            $resp = wp_remote_get($url, [
                'timeout' => 20,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'KLLD Sync Agent'
                ]
            ]);

            if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
                $code = is_wp_error($resp) ? 'WP_Error' : wp_remote_retrieve_response_code($resp);
                echo "  [TA] API Error: {$code}\n";
                if ($offset === 0) return $this->sync_tripadvisor_scraper($post_id, $ta_id, $limit_total, $force);
                break;
            }

            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $reviews = $data['data'] ?? [];
            if (empty($reviews)) break;

            echo "  [TA] Processing " . count($reviews) . " reviews (Offset: $offset)\n";

            foreach ($reviews as $review) {
                $remote_id = $review['id'] ?? '';
                $text = $review['text'] ?? '';
                $author = $review['user']['username'] ?? 'TA Traveler';
                $rating = isset($review['rating']) ? intval($review['rating']) : 5;
                $date = $review['published_date'] ?? '';
                $title = $review['title'] ?? '';

                if ($text && $remote_id) {
                    $comment_id = $this->upsert_review($post_id, 'tripadvisor', $remote_id, [
                        'author' => $author,
                        'content' => $text,
                        'rating' => $rating,
                        'title' => $title,
                        'date' => $this->normalize_date($date)
                    ]);
                    if ($comment_id) $total_imported++;
                }
            }

            $offset += count($reviews);
            if (count($reviews) < $batch) break;
        }
        echo "  - TA (API): Imported $total_imported reviews for Location ID $location_id\n";
    }

    private function sync_tripadvisor_scraper($post_id, $ta_id, $limit_total = 100, $force = false) {
        $ta_url = get_post_meta($post_id, '_ta_url', true);
        if (!$ta_url) return;
        
        $is_profile = (strpos($ta_url, 'Attraction_Review') !== false);
        // Logic similar to review_tool.php sync
        $resp = wp_remote_get($ta_url, [
            'timeout' => 20,
            'headers' => [
                'Accept' => 'text/html',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        ]);
        
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            $code = is_wp_error($resp) ? 'WP_Error' : wp_remote_retrieve_response_code($resp);
            echo "  [TA] Scraper Error: {$code}\n";
            return;
        }

        $body = wp_remote_retrieve_body($resp);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
        $xpath = new DOMXPath($dom);
        $cards = $xpath->query("//div[contains(@data-automation, 'reviewCard')]");
        
        $total_imported = 0;
        foreach ($cards as $card) {
            $remote_id = $card->getAttribute('id') ?: bin2hex(random_bytes(8));
            $text = $xpath->query(".//span[contains(@class, 'ySdfQ')]", $card)->item(0)?->nodeValue ?? '';
            $author = $xpath->query(".//a[contains(@href, '/Profile/')]", $card)->item(0)?->nodeValue ?? 
                      $xpath->query(".//span[contains(@class, 'biGQs')]", $card)->item(0)?->nodeValue ?? 'TA Traveler';
            
            $rating = 5;
            // Try SVG title first
            $rating_svg = $xpath->query(".//svg[contains(@title, 'of 5 bubble')]", $card)->item(0);
            if ($rating_svg) {
                $title = $rating_svg->getAttribute('title');
                if (preg_match('/([0-9.]+)/', $title, $m)) $rating = floatval($m[1]);
            }

            if ($text) {
                $comment_id = $this->upsert_review($post_id, 'tripadvisor', $remote_id, [
                    'author' => strip_tags($author),
                    'content' => $text,
                    'rating' => $rating,
                    'date' => ''
                ]);
                if ($comment_id) $total_imported++;
            }
        }
        echo "  - TA (Scraper Fallback): Scraped $total_imported reviews from $ta_url\n";
    }

    private function sync_gmb($post_id, $gmb_id, $limit_total = 100, $force = false) {
        // GMB Sync is usually manual or via specific API
        // For now, we just log that we processed it, as GMB is handled via the "Filter" maintenance job
        echo "  [GMB] Place ID $gmb_id mapped. Fetch reviews via Google Business Dashboard or API.\n";
    }

    public function upsert_review($post_id, $source, $remote_id, $data) {
        if (!get_post_status($post_id)) return false; 
        
        global $wpdb;
        $meta_key = $source . '_review_id';
        $localized_ids = $this->get_translated_post_ids($post_id);
        $total_new = 0;

        foreach ($localized_ids as $target_post_id) {
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT cm.comment_id 
                 FROM {$wpdb->commentmeta} cm
                 JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
                 WHERE cm.meta_key = %s AND cm.meta_value = %s AND c.comment_post_ID = %d 
                 LIMIT 1",
                $meta_key, $remote_id, $target_post_id
            ));

            if ($existing_id) {
                // Update photos if missing or requested
                if (!empty($data['photos'])) {
                    update_comment_meta($existing_id, 'ota_review_photos', $data['photos']);
                }
                continue;
            }

            $rating = (float)$data['rating'];
            $title = !empty($data['title']) ? $data['title'] : wp_trim_words($data['content'], 6, '...');
            if (!$title) $title = "Expert tour from " . ucfirst($source);

            $lang = $this->get_post_language($target_post_id);
            $labels = $this->get_sub_rating_labels($lang);

            $stats = [
                $labels['statItinerary'] => (float)($data['statItinerary'] ?? $rating),
                $labels['statGuide']      => (float)($data['statGuide'] ?? $rating),
                $labels['statService']    => (float)($data['statService'] ?? $rating),
                $labels['statDriver']     => (float)($data['statDriver'] ?? $rating),
                $labels['statFood']       => (float)($data['statFood'] ?? $rating)
            ];
            $serialized_stats = serialize($stats);

            $comment_data = [
                'comment_post_ID' => $target_post_id,
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
                update_comment_meta($new_id, 'st_category_name', get_post_type($post_id));
                update_comment_meta($new_id, 'comment_title', $title);
                update_comment_meta($new_id, 'st_review_stats', $serialized_stats);
                
                foreach ($stats as $label => $val) {
                    $meta_key_sub = 'st_stat_' . sanitize_title($label);
                    update_comment_meta($new_id, $meta_key_sub, $val);
                }

                update_comment_meta($new_id, $meta_key, $remote_id);
                update_comment_meta($new_id, 'ota_source', $source);
                update_comment_meta($new_id, 'review_date_formatted', date('d-m-Y', strtotime($comment_data['comment_date'])));
                update_comment_meta($new_id, '_comment_like_count', 0);
                
                if (!empty($data['photos'])) {
                    update_comment_meta($new_id, 'ota_review_photos', $data['photos']);
                }

                if (function_exists('st_helper_update_total_review')) {
                    st_helper_update_total_review($target_post_id);
                }
                
                file_put_contents(__DIR__ . '/ota_sync_log.txt', "[" . date('Y-m-d H:i:s') . "] Imported $source review $remote_id for Post $target_post_id ($lang)\n", FILE_APPEND);
                $total_new++;
            }
        }
        return $total_new;
    }

    public static function normalize_date($d) {
        if (empty($d)) return '';
        
        $ts = is_numeric($d) ? $d : strtotime($d);
        if (!$ts) {
            // Check for format like 18-04-2026
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $d, $m)) {
                $ts = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
            } else {
                // Check for format like 2024-04-18T10:00:00Z
                $ts = strtotime(str_replace('T', ' ', substr($d, 0, 19)));
            }
        }
        return $ts ? date('Y-m-d H:i:s', $ts) : '';
    }

    public static function calculate_match_score($s1, $s2) {
        $s1 = strtolower(trim($s1));
        $s2 = strtolower(trim($s2));
        if ($s1 == $s2) return 100;
        
        // Tokenize and calculate word intersection
        $stop_words = ['the', 'and', 'with', 'tour', 'trip', 'private', 'guided', 'from', 'to'];
        $tokens1 = array_diff(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $s1)), $stop_words);
        $tokens2 = array_diff(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $s2)), $stop_words);
        $tokens1 = array_filter($tokens1); $tokens2 = array_filter($tokens2);

        $intersect = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));
        $jaccard = count($union) > 0 ? (count($intersect) / count($union)) * 100 : 0;
        
        // Combine with similarity (to handle typos)
        similar_text($s1, $s2, $percent);
        
        $score = ($jaccard * 0.7) + ($percent * 0.3);
        
        // Boost for "Khao Lak" presence
        if (strpos($s1, 'khao lak') !== false && strpos($s2, 'khao lak') !== false) {
            $score += 5;
        }
        
        return min(100, round($score, 1));
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

        if ($stats && $stats->total > 0) {
            update_post_meta($post_id, 'total_review', (int)$stats->total);
            update_post_meta($post_id, 'rate_review', round((float)($stats->avg ?? 0), 1));
        } else {
            update_post_meta($post_id, 'total_review', 0);
            update_post_meta($post_id, 'rate_review', 0);
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
