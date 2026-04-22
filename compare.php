<?php
require 'wp-load.php';

$tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1]);
foreach ($tours as $tour) {
    $gyg_id = get_post_meta($tour->ID, '_gyg_activity_id', true);
    if ($gyg_id) {
        $web_count = get_comments(['post_id' => $tour->ID, 'type' => 'st_reviews', 'status' => 'approve', 'count' => true]);
        
        $url = "https://travelers-api.getyourguide.com/activities/{$gyg_id}/reviews?limit=1&offset=0";
        $resp = wp_remote_get($url, ['timeout' => 10]);
        if (!is_wp_error($resp)) {
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $ota_count = $data['totalCount'] ?? 0;
            
            echo "Tour {$tour->ID} (GYG $gyg_id) | Web: $web_count | OTA: $ota_count";
            if ($web_count != $ota_count) {
                echo " [MISMATCH]\n";
            } else {
                echo " [OK]\n";
            }
        }
    }
}
