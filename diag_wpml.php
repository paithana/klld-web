<?php
/**
 * WPML Compatibility Check
 * Verifies that reviews for a specific tour are correctly duplicated across all translations
 * and that localized metadata (labels) is applied.
 */

define('KLLD_SYNC_NO_RUN', true);
require_once __DIR__ . '/wp-content/themes/traveler-childtheme/inc/ota-tools/ota_sync.php';

$sync = new OTAReviewSync();
$test_post_id = 14528; // Khao Lak Off-Road Safari (EN)

echo "--- WPML Compatibility Logic Diagnosis ---\n";

// 1. Check translation discovery
$translated_ids = $sync->get_translated_post_ids($test_post_id);
echo "Tour #$test_post_id has " . count($translated_ids) . " translations: " . implode(', ', $translated_ids) . "\n";

foreach ($translated_ids as $tid) {
    $lang = $sync->get_post_language($tid);
    $title = get_the_title($tid);
    echo "  - Post #$tid [$lang]: \"$title\"\n";
    
    // 2. Check review counts for this post
    $reviews = get_comments([
        'post_id' => $tid,
        'status'  => 'approve',
        'type'    => 'st_reviews'
    ]);
    echo "    Reviews Count: " . count($reviews) . "\n";
    
    if (!empty($reviews)) {
        $sample = $reviews[0];
        echo "    Sample Review (#{$sample->comment_ID}):\n";
        
        $meta = get_comment_meta($sample->comment_ID);
        echo "      Meta Keys Found: " . implode(', ', array_keys($meta)) . "\n";
        
        // 3. Check localized labels for sub-ratings
        $sub_ratings = $sync->get_sub_rating_labels($lang);
        echo "      Expected Labels for [$lang]: " . json_encode($sub_ratings) . "\n";
        
        foreach ($sub_ratings as $key => $label) {
            $meta_key = 'st_stat_' . sanitize_title($label);
            $val = get_comment_meta($sample->comment_ID, $meta_key, true);
            echo "        $meta_key [$label]: " . ($val ? "FOUND ($val)" : "MISSING") . "\n";
        }
    }
    echo "\n";
}

echo "--- End Diagnosis ---\n";
