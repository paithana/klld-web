<?php
/**
 * Improved Multilingual Test Suite
 * Avoids early exit by using core synchronization logic.
 */

define('KLLD_TOOL_RUN', true);
define('KLLD_SYNC_NO_RUN', true);

require_once dirname(__FILE__, 5) . '/wp-load.php';
require_once dirname(__FILE__, 2) . '/ota_sync.php';

echo "================================================\n";
echo "🌐 MULTILINGUAL DUPLICATION TEST\n";
echo "================================================\n\n";

// Target post ID 14528 (EN) which usually has translations
$post_id = 14528;
if (!get_post($post_id)) {
    // Attempt to find any tour with translations
    global $wpdb;
    $table = $wpdb->prefix . 'icl_translations';
    $post_id = $wpdb->get_var("SELECT element_id FROM $table WHERE element_type = 'post_st_tours' LIMIT 1");
    if (!$post_id) {
        die("❌ Error: No tour with translations found for testing.\n");
    }
}

$review_id = 'test_multi_lang_' . time();
$sync = new OTAReviewSync();

echo "Post ID: $post_id\n";
echo "Review ID: $review_id\n";

echo "Running upsert_review...\n";
$inserted_count = $sync->upsert_review($post_id, 'test_source', $review_id, [
    'author' => 'MultiLang Tester',
    'content' => 'Checking multilingual duplication logic in the database.',
    'rating' => 5,
    'date' => date('Y-m-d')
]);

echo "Total New Comments Created: $inserted_count\n";

global $wpdb;
$comments = $wpdb->get_results($wpdb->prepare(
    "SELECT comment_ID, comment_post_ID FROM {$wpdb->comments} c 
     JOIN {$wpdb->commentmeta} m ON c.comment_ID = m.comment_id 
     WHERE m.meta_key = 'test_source_review_id' AND m.meta_value = %s", 
    $review_id
));

echo "Found " . count($comments) . " comments in database.\n";

foreach ($comments as $c) {
    $lang = $wpdb->get_var($wpdb->prepare(
        "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1", 
        $c->comment_post_ID
    )) ?: 'en';
    echo "  - Comment #{$c->comment_ID} | Post #{$c->comment_post_ID} | Language: $lang\n";
}

// Cleanup
foreach ($comments as $c) {
    wp_delete_comment($c->comment_ID, true);
}
echo "Cleanup complete.\n";

if ($inserted_count > 1 && count($comments) === $inserted_count) {
    echo "\n✅ SUCCESS: Multilingual duplication verified.\n";
} else if ($inserted_count === 1) {
    echo "\n⚠️ NOTE: Only 1 comment created. This indicates the tour might not have translations.\n";
} else {
    echo "\n❌ FAILURE: Inconsistent results.\n";
}

echo "================================================\n";
