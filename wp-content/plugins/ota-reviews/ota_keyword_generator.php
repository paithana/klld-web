<?php
/**
 * OTA Keyword Generator
 * Automatically populates _ota_keywords from SEO focus keywords and on-page content.
 */

// ── Load WordPress ────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php',
        '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php'
    ];
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

if (!defined('ABSPATH')) die('Error: Could not load WordPress environment.');

if (!PHP_SAPI === 'cli' && !current_user_can('manage_options')) die('Unauthorized.');

echo "=== KLLD OTA Keyword Generator ===\n";

$args = [
    'post_type' => 'st_tours',
    'posts_per_page' => -1,
    'post_status' => 'any'
];

$tours = get_posts($args);
echo "Found " . count($tours) . " tours to analyze.\n";

$stop_words = ['the', 'and', 'with', 'tour', 'trip', 'private', 'guided', 'from', 'to', 'for', 'this', 'that', 'with', 'your', 'full', 'day', 'best', 'discovery', 'discover'];

foreach ($tours as $tour) {
    echo "Processing: {$tour->post_title} (#{$tour->ID})...\n";
    
    $keywords = [];
    
    // 1. Pull from Rank Math Focus Keyword
    $rm_kw = get_post_meta($tour->ID, '_rank_math_focus_keyword', true);
    if ($rm_kw) {
        $rm_kws = explode(',', $rm_kw);
        foreach ($rm_kws as $kw) {
            $keywords[] = trim(strtolower($kw));
        }
        echo "  - Added Rank Math keywords: $rm_kw\n";
    }

    // 2. Extract from Title
    $title_parts = explode(' ', preg_replace('/[^a-z0-9 ]/i', '', $tour->post_title));
    foreach ($title_parts as $part) {
        $part = trim(strtolower($part));
        if (strlen($part) > 3 && !in_array($part, $stop_words)) {
            $keywords[] = $part;
        }
    }

    // 3. Extract from Content (First 500 chars)
    $content = wp_strip_all_tags($tour->post_content);
    $content_parts = explode(' ', preg_replace('/[^a-z0-9 ]/i', '', substr($content, 0, 800)));
    $content_freq = array_count_values(array_filter($content_parts, function($p) use ($stop_words) {
        $p = strtolower($p);
        return strlen($p) > 4 && !in_array($p, $stop_words);
    }));
    arsort($content_freq);
    $top_content = array_slice(array_keys($content_freq), 0, 5);
    foreach ($top_content as $tc) {
        $keywords[] = strtolower($tc);
    }

    // Standardize and Save
    $keywords = array_unique(array_filter($keywords));
    $final_kw_str = implode(', ', $keywords);
    
    if ($final_kw_str) {
        update_post_meta($tour->ID, '_ota_keywords', $final_kw_str);
        echo "  - Saved Keywords: $final_kw_str\n";
    }
}

echo "\n=== Done! All tours processed. ===\n";
