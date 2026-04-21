<?php
require_once 'wp-load.php';
global $wpdb;

// Get tours with valid GYG IDs
$gyg_tours = $wpdb->get_results("SELECT post_id, meta_value as gyg_id FROM {$wpdb->postmeta} WHERE meta_key = 'getyourguide_id'");
$tour_map = [];
foreach ($gyg_tours as $t) {
    $tour_map[$t->post_id] = $t->gyg_id;
}

// Map GYG reviews to these tours (using tour title as a fallback if possible)
$reviews = $wpdb->get_results("SELECT comment_ID, comment_post_ID FROM {$wpdb->comments} WHERE comment_ID IN (SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = 'ota_source' AND meta_value = 'gyg')");

$remapped = 0;
foreach ($reviews as $review) {
    // Basic logic: if the tour doesn't have a GYG ID, look for a tour with a title match
    if (!isset($tour_map[$review->comment_post_ID])) {
        // Find a tour that likely has this review
        $title = get_the_title($review->comment_post_ID);
        foreach ($tour_map as $tid => $gygid) {
            $t_title = get_the_title($tid);
            if (stripos($t_title, $title) !== false || stripos($title, $t_title) !== false) {
                $wpdb->update($wpdb->comments, ['comment_post_ID' => $tid], ['comment_ID' => $review->comment_ID]);
                $remapped++;
                break;
            }
        }
    }
}
echo "✅ GYG Review Mapping Complete. Remapped: $remapped\n";
