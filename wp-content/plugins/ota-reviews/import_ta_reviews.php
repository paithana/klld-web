<?php
/**
 * Import Scraped TripAdvisor Reviews into WordPress with Auto-Matching
 * 
 * This script processes 'ta_scraped_reviews.json' and matches reviews
 * to WordPress tours based on the "Review of" title.
 */

require_once '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php';

$json_file = __DIR__ . '/data/source_ta.json';
if (!file_exists($json_file)) {
    die("JSON file not found: $json_file\n");
}

$data = json_decode(file_get_contents($json_file), true);
if (!$data) {
    die("Invalid JSON data.\n");
}

/**
 * Matching Algorithm (from ota_sync.php)
 */
function calculate_match_score($title1, $title2) {
    $title1 = strtolower(trim($title1));
    $title2 = strtolower(trim($title2));
    
    // Remove common filler words to focus on unique keywords
    $stop_words = ['tour', 'from', 'khao', 'lak', 'and', 'with', 'the', 'private', 'day', 'trip', 'island', 'islands'];
    foreach ($stop_words as $word) {
        $title1 = str_replace(" $word ", ' ', " $title1 ");
        $title2 = str_replace(" $word ", ' ', " $title2 ");
    }
    $title1 = trim(preg_replace('/\s+/', ' ', $title1));
    $title2 = trim(preg_replace('/\s+/', ' ', $title2));

    if ($title1 === $title2) return 100;
    
    // Jaccard similarity based on words
    $words1 = explode(' ', $title1);
    $words2 = explode(' ', $title2);
    $intersect = array_intersect($words1, $words2);
    $union = array_unique(array_merge($words1, $words2));
    
    if (empty($union)) return 0;
    $jaccard = (count($intersect) / count($union)) * 100;

    // also check for substring
    if (str_contains($title1, $title2) || str_contains($title2, $title1)) {
        $jaccard = max($jaccard, 80);
    }

    return $jaccard;
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
        
        echo "   🔍 Review by {$r['reviewer_name']} of '{$review_of}'\n";

        // If no hardcoded post_ids, try to match based on 'review_of'
        if (empty($target_post_ids) && !empty($review_of)) {
            $best_score = 0;
            $best_id = 0;
            foreach ($tour_map as $tid => $title) {
                $score = calculate_match_score($review_of, $title);
                if ($score > $best_score) {
                    $best_score = $score;
                    $best_id = $tid;
                }
            }

            if ($best_score >= 70) {
                $target_post_ids = [$best_id];
                echo "   🎯 Auto-matched '{$review_of}' to '{$tour_map[$best_id]}' (Score: " . round($best_score) . "%)\n";
            } else {
                echo "   ❌ No match for '{$review_of}' (Best: '{$tour_map[$best_id]}' @ " . round($best_score) . "%)\n";
            }
        }

        if (empty($target_post_ids)) {
            $total_unmatched++;
            continue;
        }

        $review_id = $r['id'] ?? md5($r['reviewer_name'] . $r['text'] . ($r['date'] ?? ''));
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
                    'comment_author'       => $r['reviewer_name'] ?: 'TripAdvisor Traveler',
                    'comment_author_email' => sanitize_title($r['reviewer_name'] ?: 'traveler') . '@tripadvisor.com',
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
                    update_comment_meta($comment_id, 'ota_source', 'tripadvisor');
                    update_comment_meta($comment_id, 'comment_rate', $r['rating'] ?? 5);
                    update_comment_meta($comment_id, 'comment_title', $r['title'] ?? '');
                    update_comment_meta($comment_id, 'st_category_name', 'st_tours');
                    
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
