<?php
/**
 * OTA Review Sync - Automation Script
 * Can be run via Cron or URL: ota_sync.php?secret=YOUR_SECRET
 */

// ── Configuration ──────────────────────────────────────────────────────────
$secret = 'kld_sync_2024'; // Change this for security
$max_per_source = 50;      // Limit reviews per sync to avoid timeouts

// ── Load WordPress ────────────────────────────────────────────────────────
require_once( dirname(__FILE__) . '/wp-load.php' );

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
        http_response_code(403);
        die('Unauthorized.');
    }
    
    if (isset($_GET['clean'])) {
        require_once "wp-load.php";
        global $wpdb;
        $count = $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_author LIKE '%object Object%'");
        $wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)");
        die("Cleaned $count bad entries.");
    }
}

set_time_limit(300); // 5 minutes

class OTAReviewSync {
    private $sources = [
        'gyg' => [
            'meta' => '_gyg_activity_id',
            'review_meta' => 'gyg_review_id'
        ],
        'viator' => [
            'meta' => '_viator_activity_id',
            'review_meta' => 'viator_review_id'
        ]
        // TA, GMB, Trustpilot often require scraping or specific APIs not always suitable for basic server sync
    ];

    public function run() {
        echo "Starting OTA Sync at " . date('Y-m-d H:i:s') . "\n";
        
        $args = [
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_gyg_activity_id', 'compare' => 'EXISTS'],
                ['key' => '_viator_activity_id', 'compare' => 'EXISTS']
            ]
        ];

        $tours = get_posts($args);
        echo "Found " . count($tours) . " tours to process.\n";

        foreach ($tours as $tour) {
            echo "Processing: {$tour->post_title} (#{$tour->ID})\n";
            
            $gyg_id = get_post_meta($tour->ID, '_gyg_activity_id', true);
            $via_id = get_post_meta($tour->ID, '_viator_activity_id', true);

            if ($gyg_id) {
                $this->sync_gyg($tour->ID, $gyg_id);
            }
            if ($via_id) {
                $this->sync_viator($tour->ID, $via_id);
            }

            // Recalculate summary
            $this->update_summary($tour->ID);
        }

        echo "Sync complete.\n";
    }

    private function sync_gyg($post_id, $activity_id) {
        $batch = 300; 
        $url = "https://travelers-api.getyourguide.com/activities/{$activity_id}/reviews?limit={$batch}";
        
        $resp = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        ]);
        
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            echo "  - GYG Error: Could not connect to API for $activity_id.\n";
            return;
        }
        
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        
        // Handle variations in GYG structure
        $reviews = $data['reviews'] ?? $data['data']['reviews'] ?? $data['data'] ?? [];
        $total_found = $data['totalCount'] ?? $data['pagination']['totalCount'] ?? count($reviews);

        if (!is_array($reviews) || empty($reviews)) {
            echo "  - GYG ($activity_id): 0 reviews returned from API (Total claimed: $total_found).\n";
            return;
        }

        echo "  - GYG ($activity_id): Found $total_found reviews. Syncing...\n";

        $total_imported = 0;
        foreach ($reviews as $r) {
            $review_id = (string)($r['reviewId'] ?? $r['id'] ?? '');
            if (!$review_id || $review_id === 'undefined') continue;

            // Robust Author extraction
            $t = $r['traveler'] ?? $r['author'] ?? [];
            $author = is_array($t) ? ($t['firstName'] ?? $t['fullName'] ?? $t['name'] ?? 'Anonymous') : (is_string($t) ? $t : 'Anonymous');

            // Robust Content extraction
            $c = $r['message'] ?? $r['text'] ?? $r['comment'] ?? '';
            $content = is_array($c) ? ($c['message'] ?? $c['text'] ?? '') : (is_string($c) ? $c : '');

            if ($this->upsert_review($post_id, 'gyg', $review_id, [
                'author' => $author,
                'content' => $content,
                'rating' => round($r['overallRating'] ?? $r['rating'] ?? 5),
                'date' => $r['travelDate'] ?? $r['date'] ?? $r['created'] ?? ''
            ])) {
                $total_imported++;
            }
        }
        echo "  - GYG Total: Sync'd $total_imported/total reviews\n";
    }

    private function sync_viator($post_id, $product_code) {
        $offset = 0;
        $batch = 50;
        $total_imported = 0;

        while ($offset < 5000) {
            $url = "https://www.viator.com/api/product/reviews-v2?productCode={$product_code}&offset={$offset}&limit={$batch}&sort=NEWEST";
            $resp = wp_remote_get($url, [
                'timeout' => 20,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ]
            ]);
            
            if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) break;
            
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $reviews = $data['reviews'] ?? [];
            if (!is_array($reviews) || empty($reviews)) break;

            foreach ($reviews as $r) {
                $review_id = (string)($r['reviewReferenceId'] ?? $r['id'] ?? '');
                if (!$review_id) continue;

                $c = $r['reviewText'] ?? $r['text'] ?? '';
                $content = is_array($c) ? ($c['text'] ?? $c['message'] ?? '') : (is_string($c) ? $c : '');

                if ($this->upsert_review($post_id, 'viator', $review_id, [
                    'author' => $r['userName'] ?? $r['authorName'] ?? 'Viator Traveler',
                    'content' => $content,
                    'rating' => $r['rating'] ?? 5,
                    'date' => $r['publishedDate'] ?? $r['date'] ?? ''
                ])) {
                    $total_imported++;
                }
            }
            $offset += count($reviews);
            if (count($reviews) < $batch) break;
        }
        echo "  - Viator: Sync'd $total_imported reviews\n";
    }

    private function upsert_review($post_id, $source, $remote_id, $data) {
        global $wpdb;
        $meta_key = $source . '_review_id';
        
        // Check if exists
        $comment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            $meta_key, $remote_id
        ));

        if ($comment_id) return false;

        $rating = (float)$data['rating'];
        $title = !empty($data['title']) ? $data['title'] : wp_trim_words($data['content'], 6, '...');
        if (!$title) $title = "Expert tour from " . ucfirst($source);

        // Prepare serialized stats for Traveler Theme
        $stats = [
            'Itinerary' => $rating,
            'Tour guide' => $rating,
            'Service' => $rating,
            'Driver' => $rating
        ];
        $serialized_stats = serialize($stats);

        $comment_data = [
            'comment_post_ID' => $post_id,
            'comment_author' => $data['author'],
            'comment_author_email' => sanitize_title($data['author']) . '@' . $source . '.com',
            'comment_content' => $data['content'],
            'comment_type' => 'st_reviews',
            'comment_approved' => '1',
            'comment_date' => $this->normalize_date($data['date']),
        ];

        $new_id = wp_insert_comment($comment_data);
        if ($new_id) {
            update_comment_meta($new_id, 'st_reviews', $rating);
            update_comment_meta($new_id, 'st_star', $rating);
            update_comment_meta($new_id, 'comment_rate', $rating);
            update_comment_meta($new_id, 'comment_title', $title);
            update_comment_meta($new_id, 'st_review_stats', $serialized_stats);
            update_comment_meta($new_id, 'st_stat_itinerary', $rating);
            update_comment_meta($new_id, 'st_stat_tour-guide', $rating);
            update_comment_meta($new_id, 'st_stat_service', $rating);
            update_comment_meta($new_id, 'st_stat_driver', $rating);
            update_comment_meta($new_id, $meta_key, $remote_id);
            update_comment_meta($new_id, 'ota_source', $source);
            update_comment_meta($new_id, '_comment_like_count', 0);
            
            file_put_contents(dirname(__FILE__) . '/ota_sync_log.txt', "[" . date('Y-m-d H:i:s') . "] Imported $source review $remote_id for Post $post_id\n", FILE_APPEND);
            return true;
        }
        return false;
    }

    private function normalize_date($d) {
        if (!$d) return current_time('mysql');
        $time = strtotime($d);
        return $time ? date('Y-m-d H:i:s', $time) : current_time('mysql');
    }

    private function update_summary($post_id) {
        global $wpdb;
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total, AVG(CAST(m.meta_value AS DECIMAL(10,1))) as avg 
             FROM $wpdb->comments c 
             JOIN $wpdb->commentmeta m ON c.comment_ID = m.comment_id 
             WHERE c.comment_post_ID = %d AND c.comment_approved = '1' AND m.meta_key = 'st_reviews'",
            $post_id
        ));

        if ($stats) {
            update_post_meta($post_id, 'total_review', $stats->total);
            update_post_meta($post_id, 'rate_review', round($stats->avg, 1));
        }
    }
}

$sync = new OTAReviewSync();
$sync->run();
