<?php
require_once 'wp-load.php';

// 1. Load tour/service data for keyword matching
$posts = get_posts([
    'post_type'      => ['st_tours', 'st_cars', 'st_hotel'],
    'posts_per_page' => -1,
    'post_status'    => 'publish'
]);

function standardize_duration($text) {
    $text = mb_strtolower($text);
    // 3 Days
    if (strpos($text, '3 day') !== false || strpos($text, '3 tage') !== false || strpos($text, '3 tge') !== false || strpos($text, '3-tge') !== false || strpos($text, '3-tage') !== false || strpos($text, '3 jours') !== false || strpos($text, '3 nuits') !== false) return '3 days';
    // 2 Days
    if (strpos($text, '2 day') !== false || strpos($text, '2 tage') !== false || strpos($text, '2 tge') !== false || strpos($text, '2-tge') !== false || strpos($text, '2-tage') !== false || strpos($text, '2 jours') !== false || strpos($text, '2 nuits') !== false || strpos($text, 'overnight') !== false) return '2 days';
    // Single Day / Full Day
    if (strpos($text, 'full') !== false || strpos($text, 'complet') !== false || strpos($text, 'ganz') !== false || strpos($text, 'day trip') !== false || strpos($text, 'tagesausflug') !== false || strpos($text, 'journée') !== false) return 'full';
    // Half Day
    if (strpos($text, 'half') !== false || strpos($text, 'demi') !== false || strpos($text, '1/2') !== false || strpos($text, 'halb') !== false) return 'half';
    
    return null;
}

function get_location($text) {
    $text = mb_strtolower($text);
    $text = str_replace(' ', '', $text); // Remove spaces for khaolak/khaosok
    if (strpos($text, 'khaosok') !== false) return 'khao sok';
    if (strpos($text, 'khaolak') !== false) return 'khao lak';
    return null;
}

$post_data = [];
foreach ($posts as $p) {
    $keywords = get_post_meta($p->ID, '_ota_keywords', true);
    
    // Collect Itinerary from multiple possible keys
    $itinerary_text = "";
    $itinerary_keys = ['tours_program', 'tours_program_style4', 'itinerary'];
    foreach ($itinerary_keys as $key) {
        $prog = get_post_meta($p->ID, $key, true);
        if (is_array($prog)) {
            foreach ($prog as $item) {
                $itinerary_text .= ($item['title'] ?? '') . " " . ($item['desc'] ?? $item['content'] ?? '') . " ";
            }
        }
    }
    
    $duration_raw = get_post_meta($p->ID, 'duration_day', true);
    $highlights = get_post_meta($p->ID, 'tours_highlight', true);
    $full_text = $p->post_title . " " . $p->post_content . " " . $itinerary_text . " " . $highlights;

    $trid = null;
    if (function_exists('wpml_get_content_trid')) {
        $trid = wpml_get_content_trid('post_st_tours', $p->ID);
    }

    $post_data[$p->ID] = [
        'title' => $p->post_title,
        'keywords' => $keywords ? array_map('trim', explode(',', $keywords)) : [],
        'full_content' => mb_strtolower(strip_tags($full_text)),
        'duration' => standardize_duration($duration_raw . " " . $p->post_title),
        'location' => get_location($p->post_title . " " . $highlights),
        'trid' => $trid
    ];
}

function find_best_posts($review_text, $post_data) {
    $candidates = [];
    $review_text_lower = mb_strtolower($review_text);
    
    $review_duration = standardize_duration($review_text_lower);
    $review_location = get_location($review_text_lower);
    
    $common_words = ['khao lak', 'thailand', 'tour', 'trip', 'guide', 'excellent', 'great', 'amazing', 'best', 'discovery', 'discover'];

    $anchors = [
        'day1' => ['elephants', 'elefanten', 'sok river', 'canoe', 'treehouse resort', 'panturat', 'monkeys', 'bathing'],
        'day2' => ['cheow lan', 'lake', 'floating bungalow', 'raft house', 'pakarang cave', 'bamboo raft', 'limestone', 'dam'],
        'day3' => ['ban nam rad', 'lagoon', 'suspension bridge', 'watershed']
    ];

    $review_has_day1 = false;
    foreach($anchors['day1'] as $a) { if(stripos($review_text_lower, $a) !== false) { $review_has_day1 = true; break; } }
    $review_has_day2 = false;
    foreach($anchors['day2'] as $a) { if(stripos($review_text_lower, $a) !== false) { $review_has_day2 = true; break; } }
    $review_has_day3 = false;
    foreach($anchors['day3'] as $a) { if(stripos($review_text_lower, $a) !== false) { $review_has_day3 = true; break; } }

    foreach ($post_data as $pid => $data) {
        $score = 0;
        $match_count = 0;
        $matched_words = [];

        // 1. Keyword Matching
        foreach ($data['keywords'] as $kw) {
            $kw = mb_strtolower(trim($kw));
            if (!$kw || mb_strlen($kw) < 3) continue;
            if (stripos($review_text_lower, $kw) !== false) {
                if (!in_array($kw, $matched_words)) { $match_count++; $matched_words[] = $kw; }
                $score += (in_array($kw, $common_words)) ? 2 : (mb_strlen($kw) * 5);
            }
        }

        // 2. Deep Content Scan
        $review_words = array_unique(explode(' ', preg_replace('/[^\w\s]/u', '', $review_text_lower)));
        foreach ($review_words as $rw) {
            if (mb_strlen($rw) < 5 || in_array($rw, $common_words)) continue;
            if (stripos($data['full_content'], $rw) !== false) {
                if (!in_array($rw, $matched_words)) $score += 3;
            }
        }

        // 3. Bundle Logic
        if (in_array($pid, [49149, 49221, 49230])) {
            if ($review_has_day1 && $review_has_day2 && $review_has_day3) $score += 200;
            elseif ($review_has_day1 && $review_has_day2) { $score += 50; if (!$review_has_day3) $score -= 100; }
        }
        if (in_array($pid, [16171, 17485, 17484])) {
            if ($review_has_day1 && $review_has_day2) { $score += 150; if ($review_has_day3) $score -= 120; }
        }

        // 4. Location
        if ($review_location && $data['location']) {
            if ($review_location === $data['location']) $score += 60;
            else $score -= 50;
        }

        // 5. Duration
        if ($review_duration && $data['duration']) {
            if ($review_duration === $data['duration']) $score += 150;
            else {
                $is_multi_review = (strpos($review_duration, 'days') !== false);
                $is_multi_post = (strpos($data['duration'], 'days') !== false);
                $score -= ($is_multi_review !== $is_multi_post) ? 300 : 100;
            }
        }

        if ($score > 30) { // Slightly higher threshold for multi-tour
            $candidates[$pid] = [
                'score' => $score,
                'matches' => $match_count,
                'title' => $data['title'],
                'trid' => $data['trid']
            ];
        }
    }

    if (empty($candidates)) return [];

    uasort($candidates, function($a, $b) {
        if ($a['score'] == $b['score']) return $b['matches'] - $a['matches'];
        return $b['score'] - $a['score'];
    });

    $results = [];
    $seen_trids = [];
    $top_score = reset($candidates)['score'];

    foreach ($candidates as $pid => $c) {
        // Multi-tour criteria: 
        // 1. Must be at least 70% of the top score OR explicitly mention different tours
        // 2. Must be a different translation group (TRID)
        if ($c['score'] >= ($top_score * 0.7)) {
            if (!$c['trid'] || !in_array($c['trid'], $seen_trids)) {
                $results[$pid] = $c;
                if ($c['trid']) $seen_trids[] = $c['trid'];
            }
        }
        if (count($results) >= 3) break; // Limit to top 3 distinct tours
    }

    return $results;
}

// 2. Process ALL approved reviews
global $wpdb;
$reviews = $wpdb->get_results("SELECT comment_ID, comment_post_ID, comment_content, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' AND comment_approved = '1'");

$remapped = 0;
$cloned = 0;
$skipped = 0;

foreach ($reviews as $review) {
    $source = get_comment_meta($review->comment_ID, 'ota_source', true);
    if ($source === 'gyg') continue;

    $best_posts = find_best_posts($review->comment_content, $post_data);

    if (empty($best_posts)) {
        $skipped++;
        continue;
    }

    $post_ids = array_keys($best_posts);
    $primary_id = $post_ids[0];

    // Check if primary ID is different from current
    if ($primary_id != $review->comment_post_ID) {
        $old_title = get_the_title($review->comment_post_ID);
        $new_title = $best_posts[$primary_id]['title'];
        echo "[REMAP] Review #{$review->comment_ID}: '{$old_title}' -> '{$new_title}'\n";
        $wpdb->update($wpdb->comments, ['comment_post_ID' => $primary_id], ['comment_ID' => $review->comment_ID]);
        $remapped++;
    }

    // Handle additional tours (cloning)
    if (count($post_ids) > 1) {
        for ($i = 1; $i < count($post_ids); $i++) {
            $cloned_id = $post_ids[$i];
            
            // Check if this review (same author and content) already exists on the cloned_id
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_content = %s AND comment_author = %s LIMIT 1",
                $cloned_id, $review->comment_content, $review->comment_author
            ));

            if (!$exists) {
                $new_comment_data = [
                    'comment_post_ID' => $cloned_id,
                    'comment_author' => $review->comment_author,
                    'comment_author_email' => $review->comment_author_email,
                    'comment_author_url' => $review->comment_author_url,
                    'comment_author_IP' => $review->comment_author_IP,
                    'comment_date' => $review->comment_date,
                    'comment_date_gmt' => $review->comment_date_gmt,
                    'comment_content' => $review->comment_content,
                    'comment_karma' => $review->comment_karma,
                    'comment_approved' => $review->comment_approved,
                    'comment_agent' => $review->comment_agent,
                    'comment_type' => $review->comment_type,
                    'comment_parent' => $review->comment_parent,
                    'user_id' => $review->user_id
                ];
                $new_id = wp_insert_comment($new_comment_data);
                if ($new_id) {
                    // Copy all meta
                    $metas = get_comment_meta($review->comment_ID);
                    foreach ($metas as $key => $values) {
                        foreach ($values as $value) {
                            add_comment_meta($new_id, $key, maybe_unserialize($value));
                        }
                    }
                    echo "[CLONE] Review #{$review->comment_ID} cloned to #{$new_id} for Tour '{$best_posts[$cloned_id]['title']}'\n";
                    $cloned++;
                }
            }
        }
    }
}

echo "\n✅ Global Remapping & Multi-Tour Sync Complete\n";
echo "Total Remapped: $remapped\n";
echo "Total Cloned: $cloned\n";
echo "Total Already Correct/Skipped: $skipped\n";
