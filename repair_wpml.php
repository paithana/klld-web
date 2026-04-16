<?php
/**
 * Cross-Language Repair Script
 * Ensures all reviews are duplicated across all translations of a tour.
 * Also normalizes meta keys to match localized labels.
 */

define('KLLD_SYNC_NO_RUN', true);
require_once __DIR__ . '/wp-content/themes/traveler-childtheme/inc/ota-tools/ota_sync.php';

$sync = new OTAReviewSync();
global $wpdb;

echo "--- Cross-Language Repair Started ---\n";

// Get all reviews with a GYG or Viator ID
$reviews_raw = $wpdb->get_results("
    SELECT c.comment_ID, c.comment_post_ID, cm.meta_key, cm.meta_value as remote_id, cm2.meta_value as ota_source
    FROM {$wpdb->comments} c
    JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id
    JOIN {$wpdb->commentmeta} cm2 ON c.comment_ID = cm2.comment_id
    WHERE c.comment_type = 'st_reviews' 
    AND cm.meta_key IN ('gyg_review_id', 'viator_review_id')
    AND cm2.meta_key = 'ota_source'
");

echo "Found " . count($reviews_raw) . " OTA reviews to verify.\n";

$processed_remotes = [];

foreach ($reviews_raw as $r) {
    $source = $r->ota_source;
    $remote_id = $r->remote_id;
    $post_id = $r->comment_post_ID;
    
    $unique_key = $source . '_' . $remote_id;
    if (isset($processed_remotes[$unique_key])) continue;
    $processed_remotes[$unique_key] = true;

    // Get all expected translations for this tour
    $target_ids = $sync->get_translated_post_ids($post_id);
    if (count($target_ids) <= 1) continue;

    echo "Processing Review $remote_id (Base Post: $post_id, Found " . count($target_ids) . " translations)\n";

    // Get original review data to replicate
    $comment = get_comment($r->comment_ID);
    $rating = get_comment_meta($r->comment_ID, 'comment_rate', true);
    $title = get_comment_meta($r->comment_ID, 'comment_title', true);
    
    // Extract optional sub-ratings if they exist
    $data = [
        'author' => $comment->comment_author,
        'content' => $comment->comment_content,
        'rating' => $rating,
        'title' => $title,
        'date' => $comment->comment_date,
        'statGuide' => get_comment_meta($r->comment_ID, 'st_stat_tour-guide', true) ?: (get_comment_meta($r->comment_ID, 'st_stat_reiseleiter', true) ?: $rating),
        'statDriver' => get_comment_meta($r->comment_ID, 'st_stat_transportation', true) ?: (get_comment_meta($r->comment_ID, 'st_stat_transport', true) ?: $rating),
        'statService' => get_comment_meta($r->comment_ID, 'st_stat_service', true) ?: $rating,
        'statItinerary' => get_comment_meta($r->comment_ID, 'st_stat_organization', true) ?: (get_comment_meta($r->comment_ID, 'st_stat_organisation', true) ?: $rating),
        'statFood' => get_comment_meta($r->comment_ID, 'st_stat_food', true) ?: (get_comment_meta($r->comment_ID, 'st_stat_essen', true) ?: $rating)
    ];

    foreach ($target_ids as $tid) {
        // This will either create it or skip if it exists, AND it will update to localized labels
        $new_id = $sync->upsert_review($tid, $source, $remote_id, $data);
        if ($new_id) {
            echo "  - [NEW] Replicated to Post #$tid (Comment #$new_id)\n";
        } else {
            // It already exists, but let's ensure labels are correct
            // For now, we trust upsert_review's duplicate check.
            // If we want to HEAL existing ones, we'd need more logic here.
        }
    }
}

echo "--- Repair Finished ---\n";
