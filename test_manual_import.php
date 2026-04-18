<?php
/**
 * Manual Import Simulation Test
 */
define('KLLD_TOOL_RUN', true);
require_once 'wp-load.php';

// Prepare Mock Data for "Amazing 3 Temples" (Post 16271)
$postId = 16271;
$mockBatch = [
    [
        'postId' => $postId,
        'reviewId' => 'man_ta_123',
        'metaKey' => 'tripadvisor_review_id',
        'author' => 'Manual Tester',
        'content' => 'This is a manually imported TripAdvisor review test.',
        'rating' => 5,
        'dateStr' => '2026-04-18',
        'targetLang' => 'en'
    ]
];

$_POST['action'] = 'ota_direct_import';
$_POST['batch'] = json_encode($mockBatch);

echo "Starting Manual Import Simulation...\n";

// Require the tool file from the new plugin directory
require_once 'wp-content/plugins/ota-reviews/review_tool.php';

// The review_tool.php with wp_send_json_success will exit, so we might need to capture or check DB
echo "\nImport triggered. Checking database for review_id 'man_ta_123'...\n";

global $wpdb;
$found = $wpdb->get_var($wpdb->prepare(
    "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = 'tripadvisor_review_id' AND meta_value = 'man_ta_123' LIMIT 1"
));

if ($found) {
    echo "✅ SUCCESS: Manual review found in database (Comment ID: $found)\n";
} else {
    echo "❌ FAILURE: Manual review not found in database.\n";
}
