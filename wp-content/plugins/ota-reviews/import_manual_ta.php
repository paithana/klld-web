<?php
/**
 * Specialized Manual TripAdvisor Importer
 * Targets the 'manual_import.json' format from the browser scraper.
 */

require_once 'wp-load.php';

$json_file = __DIR__ . '/data/manual_import.json';
if (!file_exists($json_file)) {
    die("JSON file not found: $json_file\n");
}

$reviews = json_decode(file_get_contents($json_file), true);
if (!$reviews) {
    die("Invalid JSON data.\n");
}

function calculate_match_score($title1, $title2) {
    $title1 = strtolower(trim($title1));
    $title2 = strtolower(trim($title2));
    $stop_words = ['tour', 'from', 'khao', 'lak', 'and', 'with', 'the', 'private', 'day', 'trip', 'island', 'islands'];
    foreach ($stop_words as $word) {
        $title1 = str_replace(" $word ", ' ', " $title1 ");
        $title2 = str_replace(" $word ", ' ', " $title2 ");
    }
    if ($title1 === $title2) return 100;
    $words1 = explode(' ', $title1);
    $words2 = explode(' ', $title2);
    $intersect = array_intersect($words1, $words2);
    $union = array_unique(array_merge($words1, $words2));
    if (empty($union)) return 0;
    $score = (count($intersect) / count($union)) * 100;
    if (str_contains($title1, $title2) || str_contains($title2, $title1)) {
        $score = max($score, 80);
    }
    return $score;
}

$all_tours = get_posts([
    'post_type'      => 'st_tours',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'lang'           => 'en'
]);

$tour_map = [];
foreach ($all_tours as $t) {
    $tour_map[$t->ID] = $t->post_title;
}

function get_translated_ids($post_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'icl_translations';
    $trid = $wpdb->get_var($wpdb->prepare("SELECT trid FROM {$table} WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1", $post_id));
    if (!$trid) return [$post_id];
    return $wpdb->get_col($wpdb->prepare("SELECT element_id FROM {$table} WHERE trid = %d AND element_type = 'post_st_tours'", $trid));
}

$total_imported = 0;
$total_skipped = 0;

foreach ($reviews as $r) {
    $review_of = $r['ro'] ?? '';
    $reviewer_name = $r['n'] ?? 'TripAdvisor Traveler';
    $text = $r['co'] ?? '';
    $rating = $r['rt'] ?? 5;
    $date_str = $r['dt'] ?: 'now';
    $comment_date = date('Y-m-d H:i:s', strtotime($date_str));
    if (!$comment_date || $comment_date === '1970-01-01 00:00:00') {
        $comment_date = current_time('mysql');
    }

    echo "🔍 Review by {$reviewer_name} of '{$review_of}'\n";

    $best_score = 0;
    $best_id = 0;
    foreach ($tour_map as $tid => $title) {
        $score = calculate_match_score($review_of, $title);
        if ($score > $best_score) {
            $best_score = $score;
            $best_id = $tid;
        }
    }

    if ($best_score < 70) {
        echo "   ❌ No tour match found (Score: " . round($best_score) . "%). Skipping.\n";
        continue;
    }

    echo "   🎯 Matched to '{$tour_map[$best_id]}' (" . round($best_score) . "%)\n";

    $localized_ids = get_translated_ids($best_id);
    foreach ($localized_ids as $target_post_id) {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = 'tripadvisor_review_id' AND meta_value = %s AND comment_id IN (SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d)",
            $review_id, $target_post_id
        ));

        if ($exists) {
            echo "   ⏩ Duplicate on ID $target_post_id. Skipping.\n";
            continue;
        }

        $comment_data = [
            'comment_post_ID'      => $target_post_id,
            'comment_author'       => $reviewer_name,
            'comment_author_email' => sanitize_title($reviewer_name) . '@tripadvisor.com',
            'comment_content'      => $text,
            'comment_type'         => 'st_reviews',
            'comment_date'         => $comment_date,
            'comment_approved'     => 1,
            'comment_agent'        => 'Manual TA Scraper',
        ];

        $comment_id = wp_insert_comment($comment_data);
        if ($comment_id) {
            update_comment_meta($comment_id, 'tripadvisor_review_id', $review_id);
            update_comment_meta($comment_id, 'st_reviews', $rating);
            update_comment_meta($comment_id, 'ota_source', 'TA');
            update_comment_meta($comment_id, 'comment_rate', $rating);
            update_comment_meta($comment_id, 'comment_title', $r['ti'] ?? '');
            update_comment_meta($comment_id, 'st_category_name', 'st_tours');
            if (!empty($r['ph'])) {
                update_comment_meta($comment_id, 'ota_review_photos', $r['ph']);
            }
            $total_imported++;
        }
    }
}

echo "\n✅ IMPORT FINISHED: $total_imported imported.\n";
