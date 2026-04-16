<?php
/**
 * OTA Integration Test Suite
 * Verifies Sync, Feed, and Mapping health as required by the /run-tests workflow.
 */

require_once('wp-load.php');
define('KLLD_TOOL_RUN', true);

echo "==========================================\n";
echo "🚀 KLD OTA INTEGRATION TEST SUITE\n";
echo "==========================================\n\n";

$errors = [];

// 1. Database Health & Review Status
echo "Checking Review Status...\n";
global $wpdb;
$pending = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' AND comment_approved = '0'");
if ($pending == 0) {
    echo "✅ PASS: All st_reviews are approved.\n";
} else {
    echo "❌ FAIL: Found $pending unapproved st_reviews.\n";
    $errors[] = "Unapproved reviews present: $pending";
}

// 2. Mapping Health
echo "\nChecking Tour Mappings...\n";
$tour_ids = [37796, 14528, 14583, 14625, 14755, 14761, 14789, 14790, 14791, 15162, 15166, 15168, 16171, 49149, 16255, 16271, 16299, 16321, 16352, 16371, 27793, 28002, 28139, 14353, 52439];
$mapped_count = 0;
foreach ($tour_ids as $id) {
    $gyg = get_post_meta($id, '_gyg_activity_id', true);
    $via = get_post_meta($id, '_viator_activity_id', true);
    if ($gyg || $via) $mapped_count++;
}
echo "Found $mapped_count mapped tours out of " . count($tour_ids) . ".\n";
if ($mapped_count > 0) {
    echo "✅ PASS: Mapping system active.\n";
} else {
    echo "❌ FAIL: No tour mappings found.\n";
    $errors[] = "No tour mappings found.";
}

// 3. Feed Generation
echo "\nChecking GTTD Feed (JSON)...\n";
ob_start();
include('wp-content/themes/traveler-childtheme/inc/ota-tools/google-tours-feed.php');
$output = ob_get_clean();
$data = json_decode($output, true);

if (json_last_error() === JSON_ERROR_NONE && isset($data['items'])) {
    echo "✅ PASS: Feed generated successfully (" . count($data['items']) . " items).\n";
} else {
    echo "❌ FAIL: Feed generation failed or invalid JSON.\n";
    $errors[] = "Feed JSON invalid.";
}

// 4. Multilingual Sync Test (Dry-run Logic)
echo "\nChecking Sync Engine Path...\n";
if (file_exists('wp-content/themes/traveler-childtheme/inc/ota-tools/ota_sync.php')) {
    echo "✅ PASS: Sync engine exists.\n";
} else {
    echo "❌ FAIL: Sync engine missing.\n";
    $errors[] = "Sync engine file not found.";
}

echo "\n==========================================\n";
if (empty($errors)) {
    echo "🎉 ALL TESTS PASSED SUCCESSFULLY!\n";
} else {
    echo "⚠️ TEST SUITE COMPLETED WITH ERRORS:\n";
    foreach ($errors as $e) echo "  - $e\n";
    exit(1);
}
echo "==========================================\n";
