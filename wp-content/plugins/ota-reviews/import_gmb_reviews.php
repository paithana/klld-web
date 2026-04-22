<?php
/**
 * Import Consolidated GMB Reviews into WordPress with Keyword Mapping
 */

require_once '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php';

$json_file = __DIR__ . '/data/source_gmb.json';
if (!file_exists($json_file)) {
    die("JSON file not found: $json_file\n");
}

$data = json_decode(file_get_contents($json_file), true);
if (!$data) {
    die("Invalid JSON data.\n");
}

// Get all tours with keywords
$all_tours = get_posts([
    'post_type' => 'st_tours',
    'posts_per_page' => -1,
    'meta_key' => '_ota_keywords'
]);

$tour_keywords = [];
foreach ($all_tours as $t) {
    $kws = get_post_meta($t->ID, '_ota_keywords', true);
    if ($kws) {
        $tour_keywords[$t->ID] = array_map('trim', explode(',', $kws));
    }
}

function find_best_tour($text, $tour_keywords) {
    $best_tour_id = null;
    $max_matches = 0;
    
    foreach ($tour_keywords as $tid => $kws) {
        $matches = 0;
        foreach ($kws as $kw) {
            if ($kw && stripos($text, $kw) !== false) {
                $matches += strlen($kw);
            }
        }
        if ($matches > $max_matches) {
            $max_matches = $matches;
            $best_tour_id = $tid;
        }
    }
    return $best_tour_id;
}

if (!function_exists('klld_get_translated_post_ids')) {
    function klld_get_translated_post_ids($post_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'icl_translations';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [$post_id];
        }
        $trid = $wpdb->get_var($wpdb->prepare(
            "SELECT trid FROM {$table} WHERE element_id = %d AND element_type = 'post_st_tours' LIMIT 1",
            $post_id
        ));
        if (!$trid) return [$post_id];
        return $wpdb->get_col($wpdb->prepare(
            "SELECT element_id FROM {$table} WHERE trid = %d AND element_type = 'post_st_tours'",
            $trid
        ));
    }
}

$total_imported = 0;
$total_skipped = 0;
$total_unmapped = 0;

foreach ($data as $r) {
    $text = $r['text'];
    $author = $r['author'];
    $rating_val = (float)$r['rating'];
    $review_id = $r['id'];

    $target_post_id = find_best_tour($text, $tour_keywords);
    
    if (!$target_post_id) {
        echo "❓ Unmapped review from $author: " . substr(str_replace("\n", " ", $text), 0, 80) . "...\n";
        $total_unmapped++;
        continue;
    }

    $tour_title = get_the_title($target_post_id);
    echo "🎯 Mapping to: $tour_title (#$target_post_id) - Review by $author\n";

    $localized_ids = klld_get_translated_post_ids($target_post_id);
    foreach ($localized_ids as $tid) {
        global $wpdb;
        $meta_key = 'gmb_review_id';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s AND comment_id IN (SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d)",
            $meta_key, $review_id, $tid
        ));

        if ($exists) {
            $total_skipped++;
            continue;
        }

        // Try to parse date correctly
        $date_ts = strtotime($r['date']);
        if (!$date_ts) $date_ts = time();
        
        $comment_data = [
            'comment_post_ID'      => $tid,
            'comment_author'       => $author,
            'comment_author_email' => sanitize_title($author) . '@google-reviews.com',
            'comment_content'      => $text,
            'comment_type'         => 'st_reviews',
            'comment_date'         => date('Y-m-d H:i:s', $date_ts),
            'comment_approved'     => 1,
            'comment_agent'        => 'KLLD GMB Importer',
        ];

        $comment_id = wp_insert_comment($comment_data);
        if ($comment_id) {
            update_comment_meta($comment_id, $meta_key, $review_id);
            update_comment_meta($comment_id, 'st_reviews', $rating_val);
            update_comment_meta($comment_id, 'ota_source', 'gmb');
            update_comment_meta($comment_id, 'comment_rate', $rating_val);
            update_comment_meta($comment_id, 'st_star', $rating_val);
            update_comment_meta($comment_id, 'comment_title', wp_trim_words($text, 6, '...'));
            update_comment_meta($comment_id, 'st_category_name', 'st_tours');
            
            if (function_exists('st_helper_update_total_review')) {
                st_helper_update_total_review($tid);
            }
            $total_imported++;
        }
    }
}

echo "\n✅ GMB CONSOLIDATED IMPORT COMPLETE\n";
echo "Total Imported: $total_imported\n";
echo "Total Skipped (Duplicates): $total_skipped\n";
echo "Total Unmapped: $total_unmapped\n";
