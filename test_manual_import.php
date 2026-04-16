<?php
require_once('wp-load.php');
require_once('wp-admin/includes/comment.php');

echo "Testing Manual Import Logic...\n";

$batch = [
    [
        'postId' => 14528,
        'reviewId' => 'test_manual_123',
        'metaKey' => '_gyg_review_id',
        'author' => 'Test Manual User',
        'email' => 'test@manual.com',
        'content' => 'This is a manual import test review.',
        'rating' => 5,
        'dateStr' => date('Y-m-d H:i:s'),
        'targetLang' => 'en'
    ]
];

// Simmons the POST request
$_POST['action'] = 'ota_direct_import';
$_POST['batch'] = json_encode($batch);

// Include the tool file to trigger the handler
// We need to bypass the die() or exit if any
define('KLLD_TOOL_RUN', true);
require_once('wp-content/themes/traveler-childtheme/inc/ota-tools/review_tool.php');

echo "Import triggered. Checking database...\n";

global $wpdb;
$comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_author = 'Test Manual User' ORDER BY comment_ID DESC LIMIT 1"));

if ($comment) {
    echo "✅ Success: Found manual test comment ID {$comment->comment_ID}\n";
    $rate = get_comment_meta($comment->comment_ID, 'comment_rate', true);
    echo "Rating: $rate\n";
    $gyg_id = get_comment_meta($comment->comment_ID, '_gyg_review_id', true);
    echo "GYG ID: $gyg_id\n";
    
    // Cleanup
    wp_delete_comment($comment->comment_ID, true);
    echo "Cleanup complete.\n";
} else {
    echo "❌ Failure: Comment not found.\n";
}
