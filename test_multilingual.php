<?php
require_once('wp-load.php');
require_once('wp-admin/includes/comment.php');

echo "Testing Multilingual Duplication...\n";

// Target post ID 14528 (EN) which has translations 15872 (DE) and 17105 (FR)
$post_id = 14528;
$review_id = 'test_multi_lang_' . time();
$batch = [
    [
        'postId' => $post_id,
        'reviewId' => $review_id,
        'metaKey' => '_gyg_review_id',
        'author' => 'MultiLang Traveler',
        'email' => 'multi@traveler.com',
        'content' => 'Checking multilingual duplication!',
        'rating' => 5,
        'dateStr' => date('Y-m-d H:i:s')
    ]
];

// Simmons the POST request
$_POST['action'] = 'ota_direct_import';
$_POST['batch'] = json_encode($batch);

define('KLLD_TOOL_RUN', true);
require_once('wp-content/themes/traveler-childtheme/inc/ota-tools/review_tool.php');

echo "\nImport triggered. Checking database for $review_id...\n";

global $wpdb;
$comments = $wpdb->get_results($wpdb->prepare("SELECT comment_ID, comment_post_ID FROM {$wpdb->comments} c JOIN {$wpdb->commentmeta} m ON c.comment_ID = m.comment_id WHERE m.meta_key = '_gyg_review_id' AND m.meta_value = %s", $review_id));

echo "Found " . count($comments) . " comments for this review ID.\n";

foreach ($comments as $c) {
    $lang = $wpdb->get_var($wpdb->prepare("SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1", $c->comment_post_ID)) ?: 'en';
    echo "  - Comment ID: {$c->comment_ID} | Post ID: {$c->comment_post_ID} | Lang: $lang\n";
    
    // Cleanup
    wp_delete_comment($c->comment_ID, true);
}

if (count($comments) === 3) {
    echo "✅ Success: Duplicated to 3 languages!\n";
} else {
    echo "❌ Failure: Expected 3, found " . count($comments) . "\n";
}
echo "Cleanup complete.\n";
