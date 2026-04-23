<?php
/**
 * GYG Reviews Tool - WordPress Admin Only (Integrated)
 * Consolidates mapping and fetching within the WP environment.
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

if ( ! current_user_can( 'manage_options' ) && ! defined('KLLD_TOOL_RUN') ) {
    wp_die( '<h1>Unauthorized</h1><p>You need Administrator privileges to access this tool.</p>' );
}

/**
 * Helper: Find all translations of a tour post
 */
function klld_get_translated_post_ids($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'icl_translations';
    
    // Check if table exists (WPML might be inactive)
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        return [$post_id];
    }

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

// ── AJAX Handler: Save Mappings ──────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_ota_mappings') {
    $mappings = json_decode(stripslashes($_POST['mappings']), true);
    if (!is_array($mappings)) {
        wp_send_json_error('Invalid mapping data.');
    }

    $count = 0;
    foreach ($mappings as $map) {
        $wp_id = (int)($map['wpId'] ?? 0);
        if (!$wp_id) continue;

        update_post_meta($wp_id, '_gyg_activity_id', sanitize_text_field($map['gygId'] ?? ''));
        update_post_meta($wp_id, '_gyg_url', esc_url_raw($map['gygUrl'] ?? ''));
        update_post_meta($wp_id, '_viator_activity_id', sanitize_text_field($map['viatorId'] ?? ''));
        update_post_meta($wp_id, '_viator_url', esc_url_raw($map['viatorUrl'] ?? ''));
        update_post_meta($wp_id, '_tripadvisor_activity_id', sanitize_text_field($map['taId'] ?? ''));
        update_post_meta($wp_id, '_ta_url', esc_url_raw($map['taUrl'] ?? ''));
        update_post_meta($wp_id, '_ota_keywords', sanitize_text_field($map['keywords'] ?? ''));
        update_post_meta($wp_id, '_gmb_id', sanitize_text_field($map['gmbId'] ?? ''));
        update_post_meta($wp_id, '_trustpilot_id', sanitize_text_field($map['tpId'] ?? ''));
        $count++;
    }

    // Save Partner API Keys
    if (isset($_POST['gyg_partner_key'])) {
        update_option('_gyg_partner_api_key', sanitize_text_field($_POST['gyg_partner_key']));
    }
    if (isset($_POST['ta_api_key'])) {
        update_option('_ta_api_key', sanitize_text_field($_POST['ta_api_key']));
    }

    // Save GTTD SFTP Settings
    if (isset($_POST['sftp_host'])) {
        update_option('_gttd_sftp_host', sanitize_text_field($_POST['sftp_host']));
        update_option('_gttd_sftp_port', intval($_POST['sftp_port'] ?? 22));
        update_option('_gttd_sftp_user', sanitize_text_field($_POST['sftp_user']));
        update_option('_gttd_sftp_pass', sanitize_text_field($_POST['sftp_pass']));
        update_option('_gttd_sftp_key', sanitize_text_field($_POST['sftp_key']));
        update_option('_gttd_sftp_file', sanitize_text_field($_POST['sftp_file']));
    }

    wp_send_json_success(['message' => "Saved $count mappings and settings successfully."]);
}

// ── AJAX Handler: Proxy Discovery (Fixes CORS/404) ────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_ota_discover') {
    $supplier_id = 203466;
    $offset = (int)($_POST['offset'] ?? 0);
    $limit  = (int)($_POST['limit'] ?? 50);
    $token = "Basic VGhpbmtXZWIubWU6MmE3YzIwOGExOWM0MWE3Mzg4ZDYwZDA5YjQxMzhmZDI=";
    // Try multiple possible GYG endpoints server-side
    $gyg_urls = [
        "https://travelers-api.getyourguide.com/activities?supplierId={$supplier_id}&limit={$limit}&offset={$offset}",
        "https://www.getyourguide.com/api/catalog/v1/suppliers/{$supplier_id}/activities?limit={$limit}&offset={$offset}"
    ];

    $response_data = null;
    foreach ($gyg_urls as $url) {
        $resp = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Authorization' => $token
            ]
        ]);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $response_data = wp_remote_retrieve_body($resp);
            break;
        }
    }

    if ($response_data) {
        header('Content-Type: application/json');
        echo $response_data;
    } else {
        $last_error = (is_wp_error($resp)) ? $resp->get_error_message() : 'HTTP ' . wp_remote_retrieve_code($resp);
        $is_blocked = (strpos($response_data, 'Cloudflare') !== false || wp_remote_retrieve_response_code($resp) === 403);
        
        echo json_encode([
            'error' => 'Could not fetch activities from GYG endpoints.',
            'details' => $last_error,
            'is_blocked' => $is_blocked,
            'debug_urls' => $gyg_urls
        ]);
    }
    exit;
}

// ── AJAX Handler: Integrator API Discovery (Basic Auth) ──────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_gyg_integrator') {
    $user = sanitize_text_field($_POST['user'] ?? '');
    $pass = sanitize_text_field($_POST['pass'] ?? '');
    
    if (!$user || !$pass) {
        wp_send_json_error('Integrator credentials missing.');
    }

    $url = "https://api.getyourguide.com/integrator/v1/activities";
    
    $resp = wp_remote_get($url, [
        'timeout' => 20,
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . base64_encode("$user:$pass")
        ]
    ]);

    if (is_wp_error($resp)) {
        wp_send_json_error($resp->get_error_message());
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);

    if ($code !== 200) {
        echo json_encode(['error' => "Integrator API error (HTTP $code)", 'details' => $body]);
        exit;
    }

    header('Content-Type: application/json');
    echo $body;
    exit;
}

// ── AJAX Handler: Official GYG Partner API Proxy ─────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_gyg_official') {
    $tour_id  = intval($_POST['tour_id'] ?? 0);
    $api_key  = sanitize_text_field($_POST['api_key'] ?? '');
    $limit    = intval($_POST['limit'] ?? 100);
    $offset   = intval($_POST['offset'] ?? 0);

    if (!$tour_id || !$api_key) {
        wp_send_json_error('tour_id and api_key are required.');
    }

    $currency = function_exists('st_get_default_currency') ? st_get_default_currency() : 'THB';
    $url = "https://api.getyourguide.com/1/reviews/tour/{$tour_id}?cnt_language=en&currency={$currency}&limit={$limit}&offset={$offset}&sortfield=date&sortdirection=DESC";

    $resp = wp_remote_get($url, [
        'timeout' => 20,
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => $api_key,
        ]
    ]);

    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        // FALLBACK: Try travelers API if official one fails (e.g. auth issue)
        $fallback_url = "https://travelers-api.getyourguide.com/activities/{$tour_id}/reviews?limit={$limit}";
        $resp = wp_remote_get($fallback_url, [
            'timeout' => 20,
            'headers' => [ 'Accept' => 'application/json' ]
        ]);
        
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
             echo json_encode([
                'error'   => "Both GYG APIs failed",
                'details' => wp_remote_retrieve_body($resp),
                'is_blocked' => true
            ]);
            exit;
        }
        $body = wp_remote_retrieve_body($resp);
    } else {
        $body = wp_remote_retrieve_body($resp);
    }

    header('Content-Type: application/json');
    echo $body;
    exit;
}

// ── AJAX Handler: Universal OTA Proxy Fetch ──────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'proxy_ota_fetch') {
    $url = $_POST['url'] ?? '';
    if (!$url) wp_send_json_error('URL missing');

    $is_ta = (strpos($url, 'tripadvisor.com') !== false);

    $resp = wp_remote_get($url, [
        'timeout' => 20,
        'headers' => [
            'Accept' => ($is_ta ? 'text/html' : 'application/json'),
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]
    ]);
    if (is_wp_error($resp)) wp_send_json_error($resp->get_error_message());
    
    $body = wp_remote_retrieve_body($resp);

    if ($is_ta && strpos($body, 'reviewCard') !== false) {
        // Basic TripAdvisor Scraper Logic
        $reviews = [];
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
        $xpath = new DOMXPath($dom);
        
        // This is a simplified scraper for modern TA layout
        $cards = $xpath->query("//div[contains(@data-automation, 'reviewCard')]");
        foreach ($cards as $card) {
            $id = $card->getAttribute('id') ?: bin2hex(random_bytes(8));
            $text = $xpath->query(".//span[contains(@class, 'ySdfQ')]", $card)->item(0)?->nodeValue ?? '';
            $author = $xpath->query(".//span[contains(@class, 'biGQs')]", $card)->item(0)?->nodeValue ?? 'TA Traveler';
            
            // Extract rating from SVG or bubble class
            $bubbles = $xpath->query(".//span[contains(@class, 'ui_bubble_rating')]", $card)->item(0);
            $rating = 5;
            if ($bubbles) {
                $class = $bubbles->getAttribute('class');
                if (preg_match('/bubble_(\d+)/', $class, $m)) $rating = intval($m[1]) / 10;
            }

            if ($text) {
                $reviews[] = [
                    'id' => $id,
                    'text' => $text,
                    'author' => $author,
                    'rating' => $rating
                ];
            }
        }
        echo json_encode(['data' => ['reviews' => $reviews]]);
        exit;
    }

    echo $body;
    exit;
}

// ── AJAX Handler: Database Maintenance ───────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'ota_db_maintenance') {
    $job = $_POST['job'] ?? '';
    global $wpdb;
    $stats = [];

    if ($job === 'deduplicate') {
        // Keep 1 copy of identical reviews (author + content + post + date)
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
        wp_send_json_success(['message' => "Cleaned up $deleted duplicate reviews."]);

    } elseif ($job === 'remap_orphans') {
        // Map post_id = 0 based on keywords
        $orphans = $wpdb->get_results("SELECT comment_ID, comment_content FROM {$wpdb->comments} WHERE comment_post_ID = 0 AND comment_type = 'st_reviews'");
        $mappings = [
            'Similan' => 14625,
            'Safari'  => 14755,
            'white water rafting' => 15162,
            'ATV' => 15162,
            'Treehouse' => 16171,
            'Wildlife' => 14583,
            'Elephant Bath' => 14791,
            'James Bond' => 16255,
            'Phang Nga' => 16255,
            'Phuket Shopping' => 16299,
        ];
        $mapped = 0;
        foreach ($orphans as $o) {
            foreach ($mappings as $kw => $pid) {
                if (stripos($o->comment_content, $kw) !== false) {
                    $wpdb->update($wpdb->comments, ['comment_post_ID' => $pid], ['comment_ID' => $o->comment_ID]);
                    $mapped++;
                    break;
                }
            }
        }
        wp_send_json_success(['message' => "Re-mapped $mapped orphan reviews using keywords."]);
    } elseif ($job === 'gmb_filter') {
        // Assign GMB reviews to tours based on keywords
        $all_mapped_tours = get_posts([
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'meta_query' => [['key' => '_ota_keywords', 'compare' => 'EXISTS']]
        ]);

        $tour_keywords = [];
        foreach ($all_mapped_tours as $t) {
            $kw = get_post_meta($t->ID, '_ota_keywords', true);
            if ($kw) {
                $tour_keywords[$t->ID] = array_map('trim', explode(',', $kw));
            }
        }

        if (empty($tour_keywords)) wp_send_json_error('No keywords defined for any tour.');

        // Get GMB reviews (either post_id=0 or ota_source=gmb)
        $gmb_reviews = $wpdb->get_results("
            SELECT c.comment_ID, c.comment_content, c.comment_post_ID 
            FROM {$wpdb->comments} c
            JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id
            WHERE cm.meta_key = 'ota_source' AND cm.meta_value = 'gmb'
        ");

        $mapped = 0;
        foreach ($gmb_reviews as $rev) {
            foreach ($tour_keywords as $pid => $kws) {
                foreach ($kws as $word) {
                    if ($word && stripos($rev->comment_content, $word) !== false) {
                        // Match Found!
                        $wpdb->update($wpdb->comments, ['comment_post_ID' => $pid], ['comment_ID' => $rev->comment_ID]);
                        // Also update summary
                        if (function_exists('st_helper_update_total_review')) {
                            st_helper_update_total_review($pid);
                        }
                        $mapped++;
                        break 2; // Move to next review
                    }
                }
            }
        }
        wp_send_json_success(['message' => "Scanned " . count($gmb_reviews) . " GMB reviews. Re-assigned $mapped reviews based on keywords."]);
    } elseif ($job === 'refresh_ratings') {
        // Recalculate all ratings for all mapped tours
        // Optimized query: Get IDs first to avoid slow meta joins
        $mapped_ids = $wpdb->get_col("
            SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key IN ('_gyg_activity_id', '_viator_activity_id', '_tripadvisor_activity_id')
        ");

        if (empty($mapped_ids)) {
            wp_send_json_success(['message' => "No tours with OTA mappings found."]);
        }

        $all_mapped_tours = get_posts([
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'post__in' => $mapped_ids,
            'post_status' => 'any',
            'no_found_rows' => true
        ]);
        $tour_ids = wp_list_pluck($all_mapped_tours, 'ID');
        $total_updated = 0;
        
        foreach ($tour_ids as $id) {
            $localized_ids = klld_get_translated_post_ids($id);
            foreach ($localized_ids as $tid) {
                if (function_exists('st_helper_update_total_review')) {
                    st_helper_update_total_review($tid);
                    $total_updated++;
                }
            }
        }
        wp_send_json_success(['message' => "Successfully refreshed aggregate ratings for $total_updated localized tour pages."]);
    } elseif ($job === 'gmb_scrape') {
        // Run the Puppeteer scraper
        $scraper_dir = KLLD_OTA_PLUGIN_DIR . 'scrapers/gmb';
        $cmd = "cd $scraper_dir && node scraper.js 2>&1";
        $output = shell_exec($cmd);
        
        if (strpos($output, 'Success') !== false) {
            wp_send_json_success(['message' => "Scraper finished successfully: " . trim($output)]);
        } else {
            wp_send_json_error(['message' => "Scraper failed: " . trim($output)]);
        }

    } elseif ($job === 'approve_all') {
        // Approve all st_reviews
        $updated = $wpdb->query("UPDATE {$wpdb->comments} SET comment_approved = '1' WHERE comment_type = 'st_reviews' AND comment_approved = '0'");
        wp_send_json_success(['message' => "Successfully approved $updated tour reviews."]);
    } elseif ($job === 'sftp_push') {
        $push_file = dirname(__FILE__) . '/gttd_sftp_push.php';
        if (file_exists($push_file)) {
            ob_start();
            include($push_file);
            $results = ob_get_clean();
            wp_send_json_success(['message' => "SFTP Push execution completed.", 'results' => $results]);
        } else {
            wp_send_json_error('SFTP Push script not found.');
        }
    }
    wp_send_json_error('Unknown maintenance job.');
}

if (isset($_POST['action']) && $_POST['action'] === 'run_auto_mapper') {
    $mapper_file = dirname(__FILE__) . '/ota_auto_mapper.php';
    if (file_exists($mapper_file)) {
        ob_start();
        include($mapper_file);
        $results = ob_get_clean();
        wp_send_json_success(['results' => $results]);
    } else {
        wp_send_json_error('Auto-Mapper script not found.');
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'run_system_health_check') {
    $test_suite = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/test_tripadvisor_suite.php';
    if (file_exists($test_suite)) {
        ob_start();
        include($test_suite);
        $results = ob_get_clean();
        wp_send_json_success(['results' => $results]);
    } else {
        wp_send_json_error('Test suite file not found at: ' . $test_suite);
    }
}

// ── AJAX Handler: Direct Import from UI ──────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'ota_direct_import') {
    $batch = json_decode(stripslashes($_POST['batch']), true);
    if (!is_array($batch)) wp_send_json_error('Invalid batch data.');

    global $wpdb;
    $count = 0;
    foreach ($batch as $entry) {
        $post_id   = intval($entry['postId'] ?? 0);
        $review_id = sanitize_text_field($entry['reviewId'] ?? '');
        $meta_key  = sanitize_text_field($entry['metaKey'] ?? '');
        
        if (!$post_id || !$review_id || !$meta_key) continue;

        $localized_ids = klld_get_translated_post_ids($post_id);

        foreach ($localized_ids as $target_post_id) {
            // 1. DEDUPLICATION: Remove old version of this OTA review for THIS localized post
            $old_id = $wpdb->get_var($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1", $meta_key, $review_id));
            if ($old_id) {
                // Verify if the old comment belongs to the target post to avoid accidental deletion
                $old_comment = get_comment($old_id);
                if ($old_comment && (int)$old_comment->comment_post_ID === (int)$target_post_id) {
                    wp_delete_comment($old_id, true);
                }
            }

            // 2. INSERT: Add new comment
            $comment_data = [
                'comment_post_ID'      => $target_post_id,
                'comment_author'       => sanitize_text_field($entry['author'] ?? 'Traveler'),
                'comment_author_email' => sanitize_email($entry['email'] ?? 'traveler@getyourguide.com'),
                'comment_content'      => wp_kses_post($entry['content'] ?? ''),
                'comment_type'         => 'st_reviews',
                'comment_parent'       => 0,
                'user_id'              => 0,
                'comment_author_IP'    => '127.0.0.1',
                'comment_agent'        => 'KLLD OTA Importer',
                'comment_date'         => sanitize_text_field($entry['dateStr'] ?? current_time('mysql')),
                'comment_approved'     => 1,
            ];
            $new_id = wp_insert_comment($comment_data);

            if ($new_id) {
                // 3. PERSIST META
                update_comment_meta($new_id, $meta_key, $review_id);
                update_comment_meta($new_id, 'st_category_name', 'st_tours');
                update_comment_meta($new_id, 'comment_rate', intval($entry['rating'] ?? 5));
                
                // Serialized Traveler Stats (language-aware)
                // We use the language of the target_post_id to determine labels
                $target_lang = $wpdb->get_var($wpdb->prepare("SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1", $target_post_id)) ?: 'en';
                
                $labels = [
                    'en' => ['iti' => 'Itinerary', 'guide' => 'Tour guide', 'svc' => 'Service', 'drv' => 'Driver', 'food' => 'Food', 'trans' => 'Transport'],
                    'de' => ['iti' => 'Reiseverlauf', 'guide' => 'Reiseleiter', 'svc' => 'Service', 'drv' => 'Fahrer', 'food' => 'Essen', 'trans' => 'Transport'],
                    'da' => ['iti' => 'Rejseplan', 'guide' => 'Tour guide', 'svc' => 'Service', 'drv' => 'Chauff\u00f8r', 'food' => 'Mad', 'trans' => 'Transport'],
                    'no' => ['iti' => 'Reiseplan', 'guide' => 'Turguide', 'svc' => 'Service', 'drv' => 'Sj\u00e5f\u00f8r', 'food' => 'Mat', 'trans' => 'Transport'],
                    'sv' => ['iti' => 'Resplan', 'guide' => 'Reseledare', 'svc' => 'Service', 'drv' => 'F\u00f6rare', 'food' => 'Mat', 'trans' => 'Transport'],
                    'fr' => ['iti' => 'Itin\u00e9raire', 'guide' => 'Guide touristique', 'svc' => 'Service', 'drv' => 'Chauffeur', 'food' => 'Nourriture', 'trans' => 'Transport'],
                    'nl' => ['iti' => 'Reisroute', 'guide' => 'Gids', 'svc' => 'Service', 'drv' => 'Bestuurder', 'food' => 'Eten', 'trans' => 'Vervoer'],
                ];
                $l = $labels[$target_lang] ?? $labels['en'];
                
                $stats = [
                    $l['iti']   => intval($entry['statItinerary'] ?? $entry['rating']),
                    $l['guide'] => intval($entry['statGuide']     ?? $entry['rating']),
                    $l['svc']   => intval($entry['statService']   ?? $entry['rating']),
                    $l['drv']   => intval($entry['statDriver']    ?? $entry['rating']),
                ];
                // Add Food & Transport to serialized stats if provided
                if (isset($entry['statFood'])) $stats[$l['food']] = intval($entry['statFood']);
                if (isset($entry['statTransport'])) $stats[$l['trans']] = intval($entry['statTransport']);

                update_comment_meta($new_id, 'st_stat_itinerary', $stats[$l['iti']]);
                update_comment_meta($new_id, 'st_stat_tour-guide', $stats[$l['guide']]);
                update_comment_meta($new_id, 'st_stat_service', $stats[$l['svc']]);
                update_comment_meta($new_id, 'st_stat_driver', $stats[$l['drv']]);
                
                // New fields from user example
                update_comment_meta($new_id, 'st_stat_food', intval($entry['statFood'] ?? $entry['rating']));
                update_comment_meta($new_id, 'st_stat_transport', intval($entry['statTransport'] ?? $entry['rating']));

                update_comment_meta($new_id, 'st_review_stats', serialize($stats));

                // Sync total counts for post
                if (function_exists('st_helper_update_total_review')) {
                    st_helper_update_total_review($target_post_id);
                }
                $count++;
            }
        }
    }
    wp_send_json_success(['message' => "Successfully imported $count reviews directly to database."]);
}

// Fetch All Tours for UI
$all_st_tours = get_posts([
    'post_type' => 'st_tours',
    'posts_per_page' => -1,
    'post_status' => ['publish', 'private', 'draft'],
    'orderby' => 'title',
    'order' => 'ASC'
]);

// All good — serve the tool
if ( ! defined( 'KLLD_TOOL_RUN' ) ) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GetYourGuide Reviews → SQL Generator | KLD</title>
<?php } ?>
    <script>
        /**
         * KLLD State Management (React-inspired pattern)
         */
        const State = {
            data: {
                mappings: [],
                isSyncing: false,
                currentTab: 'sync',
                lastLog: 'Ready...',
                stopSync: false,
                searchQuery: ''
            },
            
            listeners: [],

            subscribe(fn) { this.listeners.push(fn); },

            setState(update) {
                this.data = { ...this.data, ...update };
                this.listeners.forEach(fn => fn(this.data));
                this.render();
            },

            render() {
                // Handle tab switching
                document.querySelectorAll('.tab-content').forEach(c => c.classList.toggle('active', c.id === this.data.currentTab));
                document.querySelectorAll('.tab-btn').forEach(b => {
                    const text = b.textContent.toLowerCase();
                    const tid = text.includes('sync') ? 'sync' : (text.includes('mapping') ? 'mapping' : (text.includes('feed') ? 'feed' : 'maintenance'));
                    b.classList.toggle('active', tid === this.data.currentTab);
                });

                // Handle sync badge/button
                const syncBtn = document.getElementById('sync-runner-btn');
                if (syncBtn) {
                    syncBtn.disabled = this.data.isSyncing;
                    syncBtn.innerHTML = this.data.isSyncing ? '⏳ Syncing...' : '🚀 Start Full Sync';
                }

                const badge = document.getElementById('status-badge');
                if (badge) {
                    badge.textContent = this.data.isSyncing ? 'Syncing...' : 'Ready';
                    badge.className = `badge ${this.data.isSyncing ? 'badge-blue pulse' : 'badge-green'}`;
                }
            }
        };

        // Initialize state
        window.addEventListener('DOMContentLoaded', () => {
            const tourKeywords = {
                <?php
                foreach ($all_st_tours as $t) {
                    $kw = get_post_meta($t->ID, '_ota_keywords', true);
                    if ($kw) echo "'{$t->ID}': " . json_encode(array_map('trim', explode(',', $kw))) . ",\n";
                }
                ?>
            };
            State.setState({ tourKeywords });
            State.subscribe(data => console.log('State Updated:', data));
            State.render();
        });

        function showTab(tabId) {
            State.setState({ currentTab: tabId });
        }

        /**
         * Confirmation Hurdle
         */
        function confirmAction(actionName, callback) {
            if (confirm(`Are you sure you want to perform: ${actionName}?`)) {
                callback();
            }
        }

        async function syncSingleGYG(wpId, mode = 'auto') {
            const logBox = document.getElementById('sync-runner-log');
            const secret = 'kld_sync_2024';
            
            State.setState({ isSyncing: true, currentTab: 'sync' });
            logBox.innerHTML = `<b>Syncing Individual Tour (#${wpId}) in ${mode} mode...</b><br>`;
            logBox.scrollTop = logBox.scrollHeight;

            try {
                // Using corrected path relative to plugin root
                const url = `<?php echo KLLD_OTA_PLUGIN_URL; ?>ota_sync.php?post_id=${wpId}&limit=5000&secret=${secret}&format=json&force=1&mode=${mode}`;
                const resp = await fetch(url);
                const data = await resp.json();

                if (data.success) {
                    const count = (data.log.match(/Sync'd (\d+) reviews/) || [0, 0])[1];
                    logBox.innerHTML += `<div style="color:var(--success); font-size:11px;">&nbsp;&nbsp;✓ ${count} reviews imported.</div>`;
                    logBox.innerHTML += `<pre style="font-size:9px; color:var(--text-muted); padding:5px; background:#0002; border-radius:4px; max-height:100px; overflow:auto;">${data.log}</pre>`;
                } else {
                    logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;✗ ${data.message || 'Error'}</div>`;
                }
            } catch (e) {
                logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;⚠ Network Failure</div>`;
            }
            
            State.setState({ isSyncing: false });
            logBox.innerHTML += '<br><b style="color:var(--success);">[COMPLETED]</b>';
            logBox.scrollTop = logBox.scrollHeight;
        }

        async function syncAllSources(wpId) {
            const logBox = document.getElementById('sync-runner-log');
            const secret = 'kld_sync_2024';
            
            State.setState({ isSyncing: true, currentTab: 'sync' });
            logBox.innerHTML = `<b>Syncing ALL Sources for Tour (#${wpId})...</b><br>`;
            logBox.scrollTop = logBox.scrollHeight;

            try {
                // Using corrected path relative to plugin root
                const url = `<?php echo KLLD_OTA_PLUGIN_URL; ?>ota_sync.php?post_id=${wpId}&limit=5000&secret=${secret}&format=json&force=1`;
                const resp = await fetch(url);
                const data = await resp.json();

                if (data.success) {
                    logBox.innerHTML += `<div style="color:var(--success); font-size:11px;">&nbsp;&nbsp;✓ Sync completed for all sources.</div>`;
                    logBox.innerHTML += `<pre style="font-size:9px; color:var(--text-muted); padding:5px; background:#0002; border-radius:4px; max-height:200px; overflow:auto;">${data.log}</pre>`;
                } else {
                    logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;✗ ${data.message || 'Error'}</div>`;
                }
            } catch (e) {
                logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;⚠ Network Failure</div>`;
            }
            
            State.setState({ isSyncing: false });
            logBox.innerHTML += '<br><b style="color:var(--success);">[COMPLETED]</b>';
            logBox.scrollTop = logBox.scrollHeight;
        }


        async function startHistoricalSync() {
            confirmAction('FULL HISTORICAL SYNC (All Tours)', async () => {
                const logBox = document.getElementById('sync-runner-log');
                const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
                const secret = 'kld_sync_2024';
                
                State.setState({ isSyncing: true, stopSync: false });
                logBox.innerHTML = '<b>Initializing Global Sync Sequence...</b><br>';

                for (let i = 0; i < rows.length; i++) {
                    if (State.data.stopSync) {
                        logBox.innerHTML += '<br><b style="color:var(--danger);">[ABORTED]</b>';
                        break;
                    }

                    const wpId = rows[i].querySelector('.wp-id').value.trim();
                    const tourName = rows[i].querySelector('.tour-name').value;
                    if (!wpId) continue;

                    logBox.innerHTML += `<div class="text-xs mt-1">[${i+1}/${rows.length}] ${tourName}...</div>`;
                    logBox.scrollTop = logBox.scrollHeight;

                    try {
                        const url = `<?php echo KLLD_OTA_PLUGIN_URL; ?>ota_sync.php?post_id=${wpId}&limit=5000&secret=${secret}&format=json&force=1`;
                        const resp = await fetch(url);
                        const data = await resp.json();

                        if (data.success) {
                            const count = (data.log.match(/Sync'd (\d+) reviews/) || [0, 0])[1];
                            logBox.innerHTML += `<div style="color:var(--success); font-size:11px;">&nbsp;&nbsp;✓ ${count} reviews imported.</div>`;
                        } else {
                            logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;✗ ${data.message || 'Error'}</div>`;
                        }
                    } catch (e) {
                        logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;⚠ Network Failure</div>`;
                    }
                    logBox.scrollTop = logBox.scrollHeight;
                }

                State.setState({ isSyncing: false });
                logBox.innerHTML += '<br><b style="color:var(--success);">[COMPLETED]</b>';
            });
        }

        async function saveMappings() {
            confirmAction('SAVE ALL MAPPINGS', async () => {
                const rows = [...document.querySelectorAll('#tour-rows .tour-row')];
                const mappings = rows.map(r => ({
                    wpId: r.querySelector('.wp-id').value,
                    gygId: r.querySelector('.gyg-id').value,
                    gygUrl: r.querySelector('.gyg-url')?.value || '',
                    viatorId: r.querySelector('.viator-id').value,
                    viatorUrl: r.querySelector('.viator-url')?.value || '',
                    taId: r.querySelector('.ta-id').value,
                    taUrl: r.querySelector('.ta-url')?.value || '',
                    gmbId: r.querySelector('.gmb-id').value,
                    tpId: r.querySelector('.tp-id').value,
                    keywords: r.querySelector('.keywords').value
                }));

                try {
                    const formData = new FormData();
                    formData.append('action', 'save_ota_mappings');
                    formData.append('mappings', JSON.stringify(mappings));
                    formData.append('ta_api_key', document.getElementById('ta-api-key').value);
                    
                    // SFTP Fields
                    formData.append('sftp_host', document.getElementById('sftp-host').value);
                    formData.append('sftp_port', document.getElementById('sftp-port').value);
                    formData.append('sftp_user', document.getElementById('sftp-user').value);
                    formData.append('sftp_pass', document.getElementById('sftp-pass').value);
                    formData.append('sftp_key', document.getElementById('sftp-key').value);
                    formData.append('sftp_file', document.getElementById('sftp-file').value);

                    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                    const data = await resp.json();
                    if (data.success) {
                        alert('✅ Mappings and Supplier URLs saved.');
                        location.reload();
                    } else {
                        alert('❌ ' + (data.data || 'Save failed'));
                    }
                } catch (e) {
                    alert('❌ Connection Error: ' + e.message);
                }
            });
        }

        async function runMaintenance(job) {
            confirmAction(`DATABASE MAINTENANCE: ${job}`, async () => {
                try {
                    const formData = new FormData();
                    formData.append('action', 'ota_db_maintenance');
                    formData.append('job', job);
                    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                    const data = await resp.json();
                    alert(data.success ? '✅ ' + data.data.message : '❌ ' + (data.data.message || data.data));
                } catch(e) { alert('Maintenance Error'); }
            });
        }

        async function runHealthCheck() {
            const logPanel = document.getElementById('health-check-results');
            const logPre = document.getElementById('health-log');
            logPanel.style.display = 'block';
            logPre.textContent = 'Running suite...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'run_system_health_check');
                const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                const data = await resp.json();
                if (data.success) {
                    logPre.textContent = data.data.results;
                } else {
                    logPre.textContent = 'Health check failed to run.';
                }
            } catch(e) { logPre.textContent = 'Error: ' + e.message; }
        }

        async function runAutoMapper() {
            confirmAction('RUN AUTO-MAPPER (Attempts to match GYG IDs to Tours automatically)', async () => {
                const logBox = document.getElementById('sync-runner-log');
                State.setState({ isSyncing: true, currentTab: 'sync' });
                logBox.innerHTML = '<b>Initializing Auto-Mapper Script...</b><br>';
                logBox.scrollTop = logBox.scrollHeight;

                try {
                    const formData = new FormData();
                    formData.append('action', 'run_auto_mapper');
                    const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                    const data = await resp.json();
                    
                    if (data.success) {
                        logBox.innerHTML += `<div style="color:var(--success); font-size:11px;">&nbsp;&nbsp;✓ Auto-Mapping Complete.</div>`;
                        logBox.innerHTML += `<pre style="font-size:9px; color:var(--text-muted); padding:5px; background:#0002; border-radius:4px; max-height:400px; overflow:auto;">${data.data.results}</pre>`;
                        alert('✅ Auto-Mapping completed. Please refresh the page to see updated mappings.');
                    } else {
                        logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;✗ ${data.data || 'Error'}</div>`;
                    }
                } catch (e) {
                    logBox.innerHTML += `<div style="color:var(--danger); font-size:11px;">&nbsp;&nbsp;⚠ Network Failure</div>`;
                }
                
                State.setState({ isSyncing: false });
                logBox.innerHTML += '<br><b style="color:var(--success);">[COMPLETED]</b>';
                logBox.scrollTop = logBox.scrollHeight;
            });
        }

        async function manualImport(source = 'detect') {
            const jsonText = document.getElementById('manual-json').value.trim();
            const postId = document.getElementById('manual-post-id').value;
            const logBox = document.getElementById('sync-runner-log');

            if (!jsonText) { alert('Please paste JSON data first.'); return; }
            if (!postId) { alert('Please select a target tour.'); return; }

            try {
                const raw = JSON.parse(jsonText);
                let reviews = [];
                
                // 1. Detect/Force Format & Normalize
                if (source === 'gyg' || (source === 'detect' && raw.reviews && Array.isArray(raw.reviews) && (raw.reviews[0]?.review_id || raw.reviews[0]?.id))) {
                    // GYG: Travelers API or Official API v1
                    const data = source === 'gyg' ? (raw.reviews || raw) : raw.reviews;
                    const items = Array.isArray(data) ? data : (data.reviews || []);
                    
                    reviews = items.map(r => {
                        // Official API v1 mapping
                        if (r.review_id) {
                            return {
                                postId: postId,
                                reviewId: r.review_id,
                                metaKey: 'gyg_review_id',
                                author: r.author_name || 'Traveler',
                                content: r.review_content,
                                rating: r.review_rating,
                                dateStr: r.review_date,
                                targetLang: 'en'
                            };
                        }
                        // Travelers API mapping
                        if (r.id) {
                            const subStats = {};
                            if (r.ratings && Array.isArray(r.ratings)) {
                                r.ratings.forEach(sr => {
                                    if (sr.ratingType === 'rating_guide') subStats.statGuide = sr.ratingValue;
                                    if (sr.ratingType === 'rating_transport') subStats.statDriver = sr.ratingValue;
                                    if (sr.ratingType === 'rating_overall') subStats.statService = sr.ratingValue;
                                    if (sr.ratingType === 'rating_value') subStats.statItinerary = sr.ratingValue;
                                });
                            }

                            return {
                                postId: postId,
                                reviewId: r.id,
                                metaKey: 'gyg_review_id',
                                author: r.fullName || 'Traveler',
                                content: r.message,
                                rating: r.rating,
                                dateStr: r.created,
                                statGuide: subStats.statGuide,
                                statDriver: subStats.statDriver,
                                statService: subStats.statService,
                                statItinerary: subStats.statItinerary,
                                statTransport: subStats.statDriver, // Map transport to driver if available
                                targetLang: 'en'
                            };
                        }
                        return null;
                    }).filter(r => r !== null);
                } else if (source === 'viator' || (source === 'detect' && raw.reviews && Array.isArray(raw.reviews) && (raw.reviews[0]?.reviewReferenceId || raw.reviews[0]?.reviewText))) {
                    // Viator Format
                    const items = Array.isArray(raw.reviews) ? raw.reviews : (Array.isArray(raw) ? raw : []);
                    reviews = items.map(r => ({
                        postId: postId,
                        reviewId: r.reviewReferenceId || r.id,
                        metaKey: 'viator_review_id',
                        author: r.userName || r.authorName || 'Viator Traveler',
                        content: r.reviewText || r.text || '',
                        rating: r.rating || 5,
                        dateStr: r.publishedDate || r.date || '',
                        targetLang: 'en'
                    }));
                } else if (source === 'tripadvisor' || (source === 'detect' && raw.data && Array.isArray(raw.data) && raw.data[0]?.location_id)) {
                    // TripAdvisor Content API Format
                    const items = raw.data || (Array.isArray(raw) ? raw : []);
                    reviews = items.map(r => ({
                        postId: postId,
                        reviewId: r.id,
                        metaKey: 'tripadvisor_review_id',
                        author: r.user?.username || 'TA Traveler',
                        content: (r.title ? '<strong>' + r.title + '</strong><br>' : '') + r.text,
                        rating: r.rating || 5,
                        dateStr: r.published_date || '',
                        targetLang: 'en'
                    }));
                } else if (source === 'gmb' || (source === 'detect' && Array.isArray(raw) && raw[0]?.author)) {
                    // GMB Scraped Format (from browser tool)
                    const kwsMap = State.data.tourKeywords || {};
                    reviews = raw.map(r => {
                        let matchedPostId = postId; // Default to selected
                        
                        // Try to auto-detect if postId was 0 or "Detect"
                        if (postId === 'detect' || postId === '0') {
                            for (const [tid, kws] of Object.entries(kwsMap)) {
                                for (const kw of kws) {
                                    if (kw && r.text.toLowerCase().includes(kw.toLowerCase())) {
                                        matchedPostId = tid;
                                        break;
                                    }
                                }
                                if (matchedPostId !== postId) break;
                            }
                        }

                        return {
                            postId: matchedPostId,
                            reviewId: btoa(r.author + r.date).substring(0, 16), // Generate unique ID
                            metaKey: 'gmb_review_id',
                            author: r.author,
                            content: r.text,
                            rating: parseFloat(String(r.rating).replace(/[^0-9.]/g, '')),
                            dateStr: r.date,
                            source: 'gmb',
                            targetLang: 'en'
                        };
                    });
                } else if (Array.isArray(raw) && (raw[0]?.text || raw[0]?.content)) {
                    // Generic / TripAdvisor JSON
                    reviews = raw.map(r => ({
                        postId: postId,
                        reviewId: r.id || btoa(r.author + r.text).substring(0,12),
                        metaKey: 'tripadvisor_review_id',
                        author: r.author || 'Traveler',
                        content: r.text || r.content || '',
                        rating: r.rating || 5,
                        dateStr: r.date || '',
                        targetLang: 'en'
                    }));
                }

                if (reviews.length === 0) {
                    alert('No reviews found in the provided JSON format.');
                    return;
                }

                if (!confirm(`Found ${reviews.length} reviews. Import them now?`)) return;

                State.setState({ isSyncing: true, currentTab: 'sync' });
                logBox.innerHTML = `<b>Manually Importing ${reviews.length} reviews...</b><br>`;

                const formData = new FormData();
                formData.append('action', 'ota_direct_import');
                formData.append('batch', JSON.stringify(reviews));

                const resp = await fetch(window.location.href, { method: 'POST', body: formData });
                const data = await resp.json();

                if (data.success) {
                    logBox.innerHTML += `<div style="color:var(--success);">${data.data.message}</div>`;
                } else {
                    logBox.innerHTML += `<div style="color:var(--danger);">Import Error: ${data.data}</div>`;
                }

            } catch (e) {
                alert('Invalid JSON: ' + e.message);
            }
            State.setState({ isSyncing: false });
            logBox.scrollTop = logBox.scrollHeight;
        }

        function filterTours() {
            const query = document.getElementById('tour-search').value.toLowerCase();
            const rows = document.querySelectorAll('#tour-rows .tour-row');
            
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(query)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Subtle feedback? maybe alert is too much
                // alert('Copied: ' + text);
            });
        }

        function clearAll() {
            document.getElementById('sync-runner-log').innerHTML = 'Ready...';
        }
    </script>
    <style>
        :root {
            --primary: #0ea5e9;
            --secondary: #6366f1;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        .klld-dashboard {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-section h1 {
            font-size: 1.8rem;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* --- Tabs --- */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1px;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* --- Cards --- */
        .k-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .k-card h2 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Mapping Table --- */
        .mapping-wrapper {
            overflow-x: auto;
            border-radius: 8px;
        }

        .mapping-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .mapping-table th {
            text-align: left;
            padding: 12px;
            background: #020617;
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .mapping-table td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }

        .mapping-table tr:hover {
            background: #ffffff05;
        }

        input.k-input {
            width: 100%;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px 10px;
            color: var(--text);
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        input.k-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px #0ea5e920;
        }

        .id-cell { width: 120px; }
        .wp-id-cell { width: 100px; }
        .name-cell { min-width: 250px; }

        /* --- Buttons --- */
        .k-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            gap: 8px;
            font-size: 0.9rem;
        }

        .k-btn-primary { background: var(--primary); color: white; }
        .k-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .k-btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .k-btn-outline:hover { background: var(--border); }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            .mapping-table thead { display: none; }
            .mapping-table tr {
                display: block;
                padding: 1rem;
                border-bottom: 2px solid var(--border);
                margin-bottom: 1rem;
                background: #ffffff05;
                border-radius: 8px;
            }
            .mapping-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 5px 0;
                border: none;
            }
            .mapping-table td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                color: var(--text-muted);
                text-transform: uppercase;
            }
        .id-cell, .wp-id-cell, .name-cell { width: 100% !important; }
        }

        .search-container {
            position: relative;
            margin-bottom: 1rem;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-container input {
            padding-left: 35px;
        }

        .search-container::before {
            content: "🔍";
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .ota-link-icon {
            text-decoration: none;
            color: var(--primary);
            font-size: 14px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .ota-link-icon:hover {
            opacity: 1;
        }

        .copy-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 2px;
            font-size: 10px;
            margin-left: 5px;
            border-radius: 4px;
        }

        .copy-btn:hover {
            color: var(--primary);
            background: #ffffff10;
        }

        .tour-row.hidden {
            display: none !important;
        }

        .badge-count {
            background: var(--border);
            color: var(--text);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 8px;
        }

        .log-panel {
            background: #020617;
            border-radius: 8px;
            padding: 12px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            margin-top: 1rem;
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-online { background: var(--success); box-shadow: 0 0 8px var(--success); }
        .status-offline { background: var(--text-muted); opacity: 0.5; }

        .flex { display: flex; }
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .w-full { width: 100%; }
        .mt-1 { margin-top: 0.25rem; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .text-xs { font-size: 0.75rem; }
    </style>
<?php if ( ! defined( 'KLLD_TOOL_RUN' ) ) { ?>
</head>

<body>
<?php } ?>
    <div class="klld-dashboard">
        <div class="header-section">
            <div style="display:flex; align-items:center; gap: 1rem;">
                <img src="<?php echo KLLD_OTA_PLUGIN_URL; ?>img/ota-reviews-logo.svg" style="width:50px; height:50px;" alt="Logo">
                <div>
                    <h1>🎯 OTA Review Manager</h1>
                    <p class="subtitle">Multi-source synchronization & mapping dashboard</p>
                </div>
            </div>
            <div class="badge badge-blue pulse" id="status-badge">System Active</div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('sync')">🔄 Sync & Import</button>
            <button class="tab-btn" onclick="showTab('mapping')">🗺 Tour Mapping</button>
            <button class="tab-btn" onclick="showTab('feed')">📡 Feed Settings</button>
            <button class="tab-btn" onclick="showTab('maintenance')">🛠 Maintenance</button>
        </div>

        <!-- --- Tab: Sync & Import --- -->
        <div id="sync" class="tab-content active">
            <div class="k-card" style="border-left: 4px solid var(--primary);">
                <h2>🚀 Historical Sync Runner</h2>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; background: #020617; padding:1.5rem; border-radius:8px; border: 1px solid var(--border);">
                    <div>
                        <label class="text-xs text-muted">TripAdvisor API Key <a href="https://www.tripadvisor.com/ContentAPIRequest" target="_blank" class="text-blue-500 hover:underline">(Get Key)</a></label>
                        <input type="password" id="ta-api-key" class="k-input w-full" value="<?php echo esc_attr(get_option('_ta_api_key')); ?>" placeholder="Enter API Key...">
                    </div>
                    <div>
                        <label class="text-xs text-muted">GYG API Key <a href="https://partner.getyourguide.com/api-management" target="_blank" class="text-blue-500 hover:underline">(Get Key)</a></label>
                        <input type="password" id="gyg-partner-api-key" class="k-input w-full" value="<?php echo esc_attr(get_option('_gyg_partner_api_key')); ?>" placeholder="Enter API Key...">
                    </div>
                    <div>
                        <label class="text-xs text-muted">Viator Partner <a href="https://partner.viator.com/en/user/login" target="_blank" class="text-blue-500 hover:underline">(Portal)</a></label>
                        <div class="text-xs text-muted mt-2">Manage your Viator connectivity directly on their portal.</div>
                    </div>
                </div>
                <p class="text-sm text-muted mb-4">Trigger a full historical sync for all mapped tours. Processes tours sequentially to avoid timeouts.</p>
                <div class="flex gap-2">
                    <button id="sync-runner-btn" onclick="startHistoricalSync()" class="k-btn k-btn-primary">Start Full Sync</button>
                    <button onclick="stopSync = true" class="k-btn k-btn-outline">Stop</button>
                </div>
                <div id="sync-runner-log" class="log-panel">Ready...</div>
            </div>

            <div class="k-card" style="border-left: 4px solid var(--success);">
                <h2>🤖 Automated Daily Sync</h2>
                <p class="text-sm text-muted mb-4">Cron endpoint for daily updates. Set this in your hosting panel.</p>
                <code style="display:block; background:#020617; padding:10px; border-radius:6px; font-size:12px; color:var(--success);">
                    https://khaolaklanddiscovery.com/ota_sync.php?secret=kld_sync_2024
                </code>
            </div>

            <div class="k-card" style="border-left: 4px solid var(--warning);">
                <h2>🛡 Manual JSON Fallback</h2>
                <p class="text-sm text-muted mb-4">Paste raw API responses if the server is blocked or for custom imports.</p>
                <div class="flex gap-2 items-center mb-2">
                    <div style="flex: 1;">
                        <label class="text-xs text-muted">Target Tour (Optional if using Keywords):</label>
                        <select id="manual-post-id" class="k-input w-full">
                            <option value="detect">-- Auto-Detect by Keywords --</option>
                            <?php foreach ($all_st_tours as $t): ?>
                                <option value="<?php echo $t->ID; ?>"><?php echo esc_html($t->post_title); ?> (#<?php echo $t->ID; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <textarea id="manual-json" class="k-input" style="height:120px; font-family:monospace; font-size:11px;" placeholder="Paste JSON data from GYG / Viator / TA / GMB here..."></textarea>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button onclick="manualImport('gyg')" class="k-btn k-btn-primary" style="background:#f97316; border-color:#f97316;">Import GYG</button>
                    <button onclick="manualImport('viator')" class="k-btn k-btn-primary" style="background:#5559be; border-color:#5559be;">Import Viator</button>
                    <button onclick="manualImport('tripadvisor')" class="k-btn k-btn-primary" style="background:#34e0a1; border-color:#34e0a1; color:#000;">Import TA</button>
                    <button onclick="manualImport('gmb')" class="k-btn k-btn-primary" style="background:#4285f4; border-color:#4285f4;">Import GMB</button>
                    <button onclick="document.getElementById('manual-json').value=''" class="k-btn k-btn-outline" style="margin-left:auto;">Clear</button>
                </div>
            </div>
        </div>

        <!-- --- Tab: Mapping --- -->
        <div id="mapping" class="tab-content">
            <div class="k-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display:flex; align-items:center; gap: 15px;">
                        <h2>🗺 Global Tour Mapping <span class="badge-count"><?php echo count($all_st_tours); ?> tours</span></h2>
                        <div class="search-container">
                            <input type="text" id="tour-search" class="k-input" placeholder="Search tours by name or ID..." oninput="filterTours()">
                        </div>
                    </div>
                        <div class="flex gap-2 items-center">
                            <div class="flex items-center gap-1">
                                <label class="text-xs text-muted">GYG Partner API Key:</label>
                                <input type="password" id="gyg-partner-api-key" class="k-input" style="width:160px; font-size:10px;" value="<?php echo esc_attr(get_option('_gyg_partner_api_key')); ?>" placeholder="GYG Auth Header">
                                <input type="password" id="ta-api-key" class="k-input" style="width:160px; font-size:10px;" value="<?php echo esc_attr(get_option('_ta_api_key')); ?>" placeholder="TripAdvisor API Key">
                            </div>
                            <button onclick="runAutoMapper()" class="k-btn k-btn-outline" style="border-color:var(--secondary); color:var(--secondary);">Auto-Map IDs</button>
                            <button onclick="saveMappings()" class="k-btn k-btn-primary">Save Mappings</button>
                        </div>
                </div>
                
                <div class="mapping-wrapper">
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th class="wp-id-cell">WP ID</th>
                                <th class="name-cell">Tour Name</th>
                                <th class="id-cell">GYG ID</th>
                                <th class="id-cell">Viator ID</th>
                                <th class="id-cell">TA ID</th>
                                <th class="id-cell">Actions</th>
                                <th class="id-cell">GMB/TP</th>
                                <th class="id-cell">Keywords</th>
                                <th style="width:50px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tour-rows">
                            <?php
                            foreach ($all_st_tours as $tour) {
                                $id = $tour->ID;
                                $gyg = get_post_meta($id, '_gyg_activity_id', true);
                                $gyg_url = get_post_meta($id, '_gyg_url', true);
                                $via = get_post_meta($id, '_viator_activity_id', true);
                                $via_url = get_post_meta($id, '_viator_url', true);
                                $ta  = get_post_meta($id, '_tripadvisor_activity_id', true);
                                $ta_url = get_post_meta($id, '_ta_url', true);

                                // Fallback URLs
                                if (!$gyg_url && $gyg) $gyg_url = "https://www.getyourguide.com/activity/-t{$gyg}/";
                                if (!$via_url && $via) $via_url = "https://www.viator.com/tours/search?query={$via}";
                                if (!$ta_url && $ta) {
                                    $clean_ta = preg_replace('/[^0-9]/', '', $ta);
                                    $ta_url = (strpos($ta, 'd') === 0) ? "https://www.tripadvisor.com/Attraction_Review-g1-d{$clean_ta}" : "https://www.tripadvisor.com/{$ta}";
                                }

                                $gmb = get_post_meta($id, '_gmb_id', true);
                                $tp  = get_post_meta($id, '_trustpilot_id', true);
                                $title = $tour->post_title;
                                
                                $has_ota = ($gyg || $via || $ta || $gmb || $tp);
                                ?>
                                <tr class="tour-row" data-search="<?php echo esc_attr(strtolower($title . ' ' . $id)); ?>" style="<?php echo !$has_ota ? 'opacity:0.6;' : ''; ?>">
                                    <td data-label="WP ID">
                                        <div class="flex items-center">
                                            <input type="number" class="k-input wp-id" value="<?php echo $id; ?>" disabled>
                                            <button class="copy-btn" onclick="copyToClipboard('<?php echo $id; ?>')" title="Copy ID">📋</button>
                                        </div>
                                    </td>
                                    <td data-label="Name">
                                        <div class="tour-name-cell">
                                            <div style="font-weight: 500; margin-bottom: 2px;"><?php echo esc_html($title); ?></div>
                                            <div class="flex gap-2">
                                                <a href="<?php echo get_edit_post_link($id); ?>" target="_blank" class="text-xs text-muted">Edit Tour</a>
                                                <a href="<?php echo get_permalink($id); ?>" target="_blank" class="text-xs text-muted">View Page</a>
                                            </div>
                                            <input type="hidden" class="tour-name" value="<?php echo esc_attr($title); ?>">
                                        </div>
                                    </td>
                                    <td data-label="GYG">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex gap-1 items-center">
                                                <input type="text" class="k-input gyg-id" value="<?php echo esc_attr($gyg); ?>" placeholder="ID">
                                                <?php if ($gyg): ?>
                                                    <a href="<?php echo esc_url($gyg_url); ?>" target="_blank" class="ota-link-icon" title="View on GYG">🔗</a>
                                                    <div class="flex flex-col gap-1">
                                                        <div class="flex gap-1">
                                                            <button onclick="syncSingleGYG(<?php echo $id; ?>, 'partner')" class="k-btn k-btn-secondary k-btn-sm" style="font-size:9px; padding:2px 4px; background:#f97316;" title="Official API Sync">Official</button>
                                                            <button onclick="syncSingleGYG(<?php echo $id; ?>, 'traveler')" class="k-btn k-btn-secondary k-btn-sm" style="font-size:9px; padding:2px 4px;" title="Traveler API Fallback">Public</button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <input type="url" class="k-input gyg-url mt-1" value="<?php echo esc_attr($gyg_url); ?>" placeholder="URL" style="font-size:10px;">
                                        </div>
                                    </td>
                                    <td data-label="Viator">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex gap-1 items-center">
                                                <input type="text" class="k-input viator-id" value="<?php echo esc_attr($via); ?>" placeholder="Code">
                                                <?php if ($via_url): ?>
                                                    <a href="<?php echo esc_url($via_url); ?>" target="_blank" class="ota-link-icon" title="View on Viator">🔗</a>
                                                <?php endif; ?>
                                            </div>
                                            <input type="url" class="k-input viator-url mt-1" value="<?php echo esc_attr($via_url); ?>" placeholder="URL" style="font-size:10px;">
                                        </div>
                                    </td>
                                    <td data-label="TA">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex gap-1 items-center">
                                                <input type="text" class="k-input ta-id" value="<?php echo esc_attr($ta); ?>" placeholder="ID">
                                                <?php if ($ta_url): ?>
                                                    <a href="<?php echo esc_url($ta_url); ?>" target="_blank" class="ota-link-icon" title="View on TA">🔗</a>
                                                <?php endif; ?>
                                            </div>
                                            <input type="url" class="k-input ta-url mt-1" value="<?php echo esc_attr($ta_url); ?>" placeholder="URL" style="font-size:10px;">
                                        </div>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="flex flex-col gap-2">
                                            <?php if ($has_ota): ?>
                                                <button onclick="syncAllSources(<?php echo $id; ?>)" class="k-btn k-btn-primary k-btn-sm" style="background:#0ea5e9; font-weight:600;" title="Fetch reviews from all mapped OTAs">🚀 Sync All</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="GMB/TP">
                                        <div class="flex flex-col gap-1">
                                            <input type="text" class="k-input gmb-id" value="<?php echo esc_attr($gmb); ?>" placeholder="GMB Place ID">
                                            <input type="text" class="k-input tp-id" value="<?php echo esc_attr($tp); ?>" placeholder="Trustpilot Slug">
                                        </div>
                                    </td>
                                    <td data-label="Keywords">
                                        <input type="text" class="k-input keywords" value="<?php echo esc_attr(get_post_meta($id, '_ota_keywords', true)); ?>" placeholder="Keyword1, Keyword2" style="font-size:10px;">
                                    </td>
                                    <td data-label="Status">
                                        <div class="flex flex-col items-center">
                                            <span class="status-indicator <?php echo $has_ota ? 'status-online' : 'status-offline'; ?>" title="<?php echo $has_ota ? 'Mapped' : 'Unmapped'; ?>"></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- --- Tab: Maintenance --- -->
        <div id="maintenance" class="tab-content">
            <div class="k-card" style="border-left: 4px solid var(--danger);">
                <h2>🛠 System Maintenance</h2>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>🧹 Deduplicate</h3>
                        <p class="text-xs text-muted mb-4">Remove identical reviews imported multiple times.</p>
                        <button onclick="runMaintenance('deduplicate')" class="k-btn k-btn-outline w-full">Deduplicate Now</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>📍 Remap Orphans</h3>
                        <p class="text-xs text-muted mb-4">Match Post ID 0 reviews to correct tours by keyword.</p>
                        <button onclick="runMaintenance('remap_orphans')" class="k-btn k-btn-outline w-full">Remap Now</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>⭐ Refresh Ratings</h3>
                        <p class="text-xs text-muted mb-4">Force recalculate all star ratings (Fixes 0-rating display).</p>
                        <button onclick="runMaintenance('refresh_ratings')" class="k-btn k-btn-primary w-full">Refresh All Now</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>✅ Approve Reviews</h3>
                        <p class="text-xs text-muted mb-4">Set status to 'Approved' for all imported tour reviews.</p>
                        <button onclick="runMaintenance('approve_all')" class="k-btn k-btn-outline w-full" style="color:var(--success); border-color:var(--success);">Approve All</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>🤖 GMB Scraper</h3>
                        <p class="text-xs text-muted mb-4">Run Puppeteer to fetch live Google Maps reviews.</p>
                        <button onclick="runMaintenance('gmb_scrape')" class="k-btn k-btn-primary w-full" style="background:#0ea5e9;">Scrape GMB Now</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>🔍 GMB Filter</h3>
                        <p class="text-xs text-muted mb-4">Assign GMB reviews to tours using keywords.</p>
                        <button onclick="runMaintenance('gmb_filter')" class="k-btn k-btn-primary w-full" style="background:var(--secondary);">Filter GMB Now</button>
                    </div>
                    <div class="stat-box" style="background: #020617; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                        <h3>🧬 Health Check</h3>
                        <p class="text-xs text-muted mb-4">Run the project-wide integration test suite.</p>
                        <button onclick="runHealthCheck()" class="k-btn k-btn-primary w-full">Run Suite</button>
                    </div>
                </div>
                <div id="health-check-results" style="margin-top:1.5rem; display:none;">
                    <h3 style="font-size:0.9rem; color:var(--primary);">System Health Results:</h3>
                    <pre id="health-log" style="background:#020617; padding:15px; border-radius:8px; font-size:11px; color:var(--text-muted); border:1px solid var(--border); overflow:auto; max-height:300px;"></pre>
                </div>
            </div>
        </div>

        <!-- --- Tab: Feed Settings --- -->
        <div id="feed" class="tab-content">
            <div class="k-card" style="border-left: 4px solid var(--secondary);">
                <h2>📡 Google Things to Do - SFTP Feed Delivery</h2>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: #020617; padding:1.5rem; border-radius:8px; border: 1px solid var(--border); margin-bottom: 20px;">
                    <div>
                        <label class="text-xs text-muted">SFTP Host</label>
                        <input type="text" id="sftp-host" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_host', 'partnerupload.google.com')); ?>">
                    </div>
                    <div>
                        <label class="text-xs text-muted">SFTP Port</label>
                        <input type="number" id="sftp-port" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_port', 19321)); ?>">
                    </div>
                    <div>
                        <label class="text-xs text-muted">SFTP Username</label>
                        <input type="text" id="sftp-user" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_user', 'mc-sftp-5520609361')); ?>">
                    </div>
                    <div>
                        <label class="text-xs text-muted">SFTP Password</label>
                        <input type="password" id="sftp-pass" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_pass', ':(2Q>%zv4e')); ?>">
                    </div>
                    <div>
                        <label class="text-xs text-muted">Private Key Path</label>
                        <input type="text" id="sftp-key" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_key', '/home/u451564824/.ssh/gttd_rsa')); ?>">
                    </div>
                    <div>
                        <label class="text-xs text-muted">Target Filename (.xml)</label>
                        <input type="text" id="sftp-file" class="k-input w-full" value="<?php echo esc_attr(get_option('_gttd_sftp_file', 'tours_feed.xml')); ?>">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="saveMappings()" class="k-btn k-btn-primary">Save SFTP Settings</button>
                    <button onclick="runMaintenance('sftp_push')" class="k-btn k-btn-outline" style="border-color:var(--success); color:var(--success);">Push Feed Now</button>
                </div>
                <div id="sftp-status" style="margin-top:10px; font-size:11px; color:var(--text-muted);"></div>
            </div>
        </div>
    </div>

<?php if ( ! defined( "KLLD_TOOL_RUN" ) ) { ?>
</body>
</html><?php } ?>
