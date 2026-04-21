<?php
require_once 'wp-load.php';

// 1. Load tour/service data for keyword matching
$posts = get_posts([
    'post_type'      => ['st_tours', 'st_cars', 'st_hotel'],
    'posts_per_page' => -1,
    'post_status'    => 'publish'
]);

$post_data = [];
foreach ($posts as $p) {
    $keywords = get_post_meta($p->ID, '_ota_keywords', true);
    $itinerary_raw = get_post_meta($p->ID, 'itinerary', true);
    $itinerary_text = "";
    if (is_array($itinerary_raw)) {
        foreach($itinerary_raw as $item) {
            $itinerary_text .= ($item['title'] ?? '') . " " . ($item['content'] ?? '') . " ";
        }
    }

    $post_data[$p->ID] = [
        'title' => $p->post_title,
        'keywords' => $keywords ? array_map('trim', explode(',', $keywords)) : [],
        'itinerary' => strip_tags($itinerary_text)
    ];
}

function find_best_post($review_text, $post_data) {
    $best_id = null;
    $max_score = 0;

    foreach ($post_data as $pid => $data) {
        $score = 0;
        foreach ($data['keywords'] as $kw) {
            if ($kw && stripos($review_text, $kw) !== false) $score += (strlen($kw) * 2);
        }
        
        $words = explode(' ', strtolower(preg_replace('/[^\w\s]/', '', $data['itinerary'])));
        foreach (array_unique(array_filter($words, function($w) { return strlen($w) > 4; })) as $word) {
            if (stripos($review_text, $word) !== false) $score += 1;
        }

        if ($score > $max_score) {
            $max_score = $score;
            $best_id = $pid;
        }
    }
    return ($max_score > 5) ? $best_id : null;
}

// 2. Process ALL approved reviews
global $wpdb;
$reviews = $wpdb->get_results("SELECT comment_ID, comment_post_ID, comment_content FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' AND comment_approved = '1'");

$remapped = 0;
$skipped = 0;

foreach ($reviews as $review) {
    // Exclude GYG reviews as requested previously
    $source = get_comment_meta($review->comment_ID, 'ota_source', true);
    if ($source === 'gyg') continue;

    $best_id = find_best_post($review->comment_content, $post_data);

    if ($best_id && $best_id != $review->comment_post_ID) {
        $wpdb->update($wpdb->comments, ['comment_post_ID' => $best_id], ['comment_ID' => $review->comment_ID]);
        $remapped++;
    } else {
        $skipped++;
    }
}

echo "✅ Global Auto-Remapping Complete (Excluding GYG)\n";
echo "Total Remapped: $remapped\n";
echo "Total Already Correct/Skipped: $skipped\n";
