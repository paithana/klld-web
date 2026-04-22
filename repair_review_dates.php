<?php
require_once 'wp-load.php';
require_once 'wp-content/plugins/ota-reviews/ota_sync.php';

global $wpdb;

$results = $wpdb->get_results("
    SELECT cm.comment_id, cm.meta_value as date_str 
    FROM {$wpdb->commentmeta} cm
    JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
    WHERE cm.meta_key = 'review_date_formatted' 
    AND c.comment_date = '0000-00-00 00:00:00'
    AND c.comment_type = 'st_reviews'
");

echo "Found " . count($results) . " reviews to repair.\n";

$count = 0;
foreach ($results as $row) {
    $normalized = OTAReviewSync::normalize_date($row->date_str);
    if ($normalized) {
        $wpdb->update(
            $wpdb->comments,
            ['comment_date' => $normalized, 'comment_date_gmt' => $normalized],
            ['comment_ID' => $row->comment_id]
        );
        $count++;
    }
}

echo "Successfully repaired $count review dates.\n";
