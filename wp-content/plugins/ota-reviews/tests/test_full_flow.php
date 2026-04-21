<?php
require_once 'wp-load.php';
echo "Starting Full Flow Verification...\n";

// 1. Check Data Availability
$ta_file = __DIR__ . '/../data/source_ta.json';
echo (file_exists($ta_file) ? "✅ TA Data Found" : "⚠️ TA Data Missing") . "\n";

// 2. Check Database Consistency (Source Counts)
global $wpdb;
$sources = $wpdb->get_results("SELECT meta_value, count(*) as count FROM {$wpdb->commentmeta} WHERE meta_key = 'ota_source' GROUP BY meta_value");
echo "Current Review Distribution:\n";
foreach ($sources as $s) {
    echo "- {$s->meta_value}: {$s->count}\n";
}

// 3. Verify Mapping Logic (Check for unassigned reviews)
$unassigned = $wpdb->get_var("SELECT count(*) FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' AND comment_post_ID = 0");
echo ($unassigned == 0 ? "✅ All reviews mapped to tours" : "⚠️ $unassigned reviews unmapped") . "\n";

echo "Full Flow Verification Complete.\n";
