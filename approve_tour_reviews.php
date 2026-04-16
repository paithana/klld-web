<?php
require_once( dirname(__FILE__) . '/wp-load.php' );

global $wpdb;

$count = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->comments} 
    WHERE comment_type = 'st_reviews' 
    AND comment_approved = '0'
");

echo "Unapproved st_reviews: $count\n";

if ($count > 0) {
    echo "Approving $count reviews...\n";
    $updated = $wpdb->query("
        UPDATE {$wpdb->comments} 
        SET comment_approved = '1' 
        WHERE comment_type = 'st_reviews' 
        AND comment_approved = '0'
    ");
    echo "Successfully approved $updated reviews.\n";
    
    // Also refresh ratings for posts that had unapproved reviews
    $post_ids = $wpdb->get_col("
        SELECT DISTINCT comment_post_ID 
        FROM {$wpdb->comments} 
        WHERE comment_type = 'st_reviews' 
        AND comment_ID IN (
            SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' AND comment_approved = '1'
        )
    ");
    
    echo "Refreshing ratings for impacted posts...\n";
    foreach ($post_ids as $pid) {
        if (function_exists('st_helper_update_total_review')) {
            st_helper_update_total_review($pid);
        }
    }
    echo "Done.\n";
} else {
    echo "No unapproved reviews found.\n";
}
