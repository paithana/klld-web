<?php
/**
 * Import Scraped TripAdvisor Reviews into WordPress with Auto-Matching
 * 
 * This script processes 'ta_scraped_reviews.json' and matches reviews
 * to WordPress tours based on the "Review of" title.
 */

if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php'
    ];
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

$json_file = __DIR__ . '/data/source_ta.json';
if (!file_exists($json_file)) {
    die("JSON file not found: $json_file\n");
}

$data = json_decode(file_get_contents($json_file), true);
if (!$data) {
    die("Invalid JSON data.\n");
}

/**
 * Fetch all tours for matching
 */
$all_tours = get_posts([
    'post_type'      => 'st_tours',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'lang'           => 'en' // Match against English titles primarily
]);

$tour_map = [];
foreach ($all_tours as $t) {
    $tour_map[$t->ID] = $t->post_title;
}

/**
 * Manual Mapping for TripAdvisor Slugs
 */
$manual_slug_map = [
    'Off Road Safari Khao Lak' => 'Khao Lak Off-Road Safari',
    'James Bond Phang Nga with canoe Full Day Tour' => 'James Bond Island tour & Phang Nga Bay',
    'Surin Islands Early Bird Snorkeltour from Khao Lak in English French German It' => 'Surin Islands Snorkeling Day Trip',
    'Phuket Weeked Market' => 'Phuket Weekend Market',
    'Khao Lak Expedition from Phuket' => 'Phuket Tour DIY',
    'Khao Sok Wildlife 2 Days' => '2 Day Khao Sok Wildlife',
    'Amazing 3 Temples' => 'Amazing 3 Temples',
    'Phuket Sunday Walking Street Market' => 'Phuket Sunday Walking Street',
    'Phang Nga Bay Sunset Serenity Cruise' => 'Phang Nga Bay Sunset Serenity Cruise',
    'Similan Islands Early Bird Snorkeltour' => 'Similan Islands Snorkeling Day Trip',
];

/**
 * Helper: Find all translations of a tour post
 */
if (!function_exists('klld_get_translated_post_ids')) {
    function klld_get_translated_post_ids($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'icl_translations';
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
}

$total_imported = 0;
$total_skipped = 0;
$total_unmatched = 0;

foreach ($data as $batch_id => $batch_data) {
    $reviews = $batch_data['reviews'] ?? [];
    $batch_post_ids = $batch_data['post_ids'] ?? [];
    $batch_tour_name = $batch_data['tour_name'] ?? 'Unknown Tour';

    echo "\n📦 Processing Batch: $batch_id ($batch_tour_name) - " . count($reviews) . " reviews\n";

    foreach ($reviews as $r) {
        $target_post_ids = $batch_post_ids;
        $review_of = $r['review_of'] ?? '';
        
        $reviewer_name = $r['reviewer_name'] ?? 'Unknown';
        // echo "   🔍 Review by {$reviewer_name} of '{$review_of}'\n";

        // 1. Try Manual Mapping
        if (empty($target_post_ids) && isset($manual_slug_map[$review_of])) {
            $mapped_title = $manual_slug_map[$review_of];
            foreach ($tour_map as $tid => $title) {
                if (stripos($title, $mapped_title) !== false || stripos($mapped_title, $title) !== false) {
                    $target_post_ids = [$tid];
                    echo "   📍 Manual Matched '{$review_of}' -> '{$title}'\n";
                    break;
                }
            }
        }

        // 2. If still no hardcoded post_ids, try to match based on 'review_of' or 'text'
        if (empty($target_post_ids)) {
            $content_lower = strtolower($r['text'] ?? '');

            // Detect Multi-Tour Intent
            $is_multi = false;
            $multi_phrases = ['2 tours', '2 trips', 'two tours', 'two trips', '3 tours', 'both tours', 'multiple tours'];
            foreach ($multi_phrases as $phrase) {
                if (strpos($content_lower, $phrase) !== false) {
                    $is_multi = true;
                    break;
                }
            }

            // Primary content for matching
            $match_text = $review_of . " " . ($r['text'] ?? '');

            foreach ($tour_map as $tid => $title) {
                $score = klld_calculate_review_match_score($match_text, $tid);

                if ($review_of && stripos(strtolower($title), strtolower($review_of)) !== false) {
                    $score += 60;
                }

                // Logic: 
                // - 100+ (Unique Anchor) -> Always add
                // - 40+ (Partial Match)  -> Add if Multi-Tour intent detected
                if ($score >= 100 || ($is_multi && $score >= 40)) {
                    $target_post_ids[] = $tid;
                }
            }

            $target_post_ids = array_unique($target_post_ids);

            if (!empty($target_post_ids)) {
                echo "   🎯 Matched '" . count($target_post_ids) . "' tours for review from '{$review_of}' " . ($is_multi ? "(Multi-tour detected)" : "") . "\n";
            }
        }

        if (empty($target_post_ids)) {
            $total_unmatched++;
            continue;
        }

        $review_id = $r['id'] ?? md5(($r['reviewer_name'] ?? 'anon') . ($r['text'] ?? '') . ($r['date'] ?? ''));
        $meta_key = 'tripadvisor_review_id';

        foreach ($target_post_ids as $base_post_id) {
            $localized_ids = klld_get_translated_post_ids($base_post_id);
            
            foreach ($localized_ids as $target_post_id) {
                global $wpdb;
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s AND comment_id IN (SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d)",
                    $meta_key, $review_id, $target_post_id
                ));

                if ($exists) {
                    $total_skipped++;
                    continue;
                }

                // Convert TripAdvisor relative dates if necessary
                $date_str = $r['date'] ?? 'now';
                // Handle "March 2024" or similar
                if (preg_match('/^[A-Z][a-z]+ \d{4}$/', $date_str)) {
                    $date_str = "1 " . $date_str;
                }
                
                $comment_data = [
                    'comment_post_ID'      => $target_post_id,
                    'comment_author'       => ($r['reviewer_name'] ?? '') ?: 'TripAdvisor Traveler',
                    'comment_author_email' => sanitize_title(($r['reviewer_name'] ?? '') ?: 'traveler') . '@tripadvisor.com',
                    'comment_content'      => $r['text'],
                    'comment_type'         => 'st_reviews',
                    'comment_date'         => date('Y-m-d H:i:s', strtotime($date_str)),
                    'comment_approved'     => 1,
                    'comment_agent'        => 'KLLD TA Auto-Matcher',
                ];

                $comment_id = wp_insert_comment($comment_data);
                if ($comment_id) {
                    update_comment_meta($comment_id, $meta_key, $review_id);
                    update_comment_meta($comment_id, 'st_reviews', $r['rating'] ?? 5);
                    update_comment_meta($comment_id, 'ota_source', 'TA');
                    update_comment_meta($comment_id, 'comment_rate', $r['rating'] ?? 5);
                    update_comment_meta($comment_id, 'comment_title', $r['title'] ?? '');
                    update_comment_meta($comment_id, 'st_category_name', 'st_tours');
                    if (!empty($r['photos'])) {
                        update_comment_meta($comment_id, 'ota_review_photos', $r['photos']);
                    }
                    
                    if (function_exists('st_helper_update_total_review')) {
                        st_helper_update_total_review($target_post_id);
                    }
                    $total_imported++;
                }
            }
        }
    }
}

echo "\n✅ IMPORT & MATCH COMPLETE\n";
echo "Total Imported:  $total_imported\n";
echo "Total Skipped:   $total_skipped (Duplicates)\n";
echo "Total Unmatched: $total_unmatched (Could not find tour)\n";
