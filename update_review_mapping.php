<?php
/**
 * Admin Tool: Update Review Mapping
 * Usage: php update_review_mapping.php <comment_id> <new_post_id>
 */

require_once 'wp-load.php';

if ($argc < 3) {
    die("Usage: php update_review_mapping.php <comment_id> <new_post_id>\n");
}

$comment_id = (int)$argv[1];
$new_post_id = (int)$argv[2];

global $wpdb;

// Verify review exists
$review = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_ID = %d", $comment_id));

if (!$review) {
    die("Review ID $comment_id not found.\n");
}

// Verify tour exists
$tour = get_post($new_post_id);
if (!$tour) {
    die("Post ID $new_post_id not found.\n");
}

// Perform Update
$wpdb->update(
    $wpdb->comments,
    ['comment_post_ID' => $new_post_id],
    ['comment_ID' => $comment_id]
);

// Update review counts
if (function_exists('st_helper_update_total_review')) {
    st_helper_update_total_review($review->comment_post_ID); // Old tour
    st_helper_update_total_review($new_post_id); // New tour
}

echo "✅ Review $comment_id successfully remapped to tour $new_post_id (" . $tour->post_title . ")\n";
