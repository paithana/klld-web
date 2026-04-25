<?php
/**
 * OTA Keyword Generator - Translation Aware (V3)
 * Automatically populates _ota_keywords from SEO focus keywords and on-page content
 * across all WPML translations. Now includes phrase detection (n-grams).
 */

// ── Load WordPress ────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php',
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

echo "=== KLLD OTA Keyword Generator (Phrase-Aware) ===\n";

global $wpdb;

// 1. Get all base (original) tours
$args = [
    'post_type' => 'st_tours',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'suppress_filters' => true 
];

$all_tours = get_posts($args);
$processed_trids = [];

// Comprehensive multi-lingual stop words
$stop_words = [
    'the', 'and', 'with', 'tour', 'trip', 'private', 'guided', 'from', 'to', 'for', 'this', 'that', 'with', 'your', 'full', 'day', 'best', 'discovery', 'discover', 'visit', 'will', 'have', 'were', 'about', 'after', 'there',
    'mit', 'der', 'die', 'das', 'und', 'avec', 'pour', 'dans', 'votre', 'notre', 'avant', 'nous', 'vous', 'eine', 'einer', 'einem', 'einen', 'ihrem', 'ihnen', 'ihrer', 'sind', 'auch', 'diese', 'dieser', 'dieses', 'beim', 'bevor',
    'mais', 'plus', 'tous', 'tout', 'toute', 'toutes', 'cette', 'ceux', 'celle', 'celles'
];

foreach ($all_tours as $tour) {
    $trid = null;
    if (function_exists('wpml_get_content_trid')) {
        $trid = wpml_get_content_trid('post_st_tours', $tour->ID);
    }

    if ($trid && in_array($trid, $processed_trids)) continue;
    if ($trid) $processed_trids[] = $trid;

    $group_ids = [$tour->ID];
    if ($trid) {
        $translations = $wpdb->get_results($wpdb->prepare(
            "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid = %d AND element_type = 'post_st_tours'",
            $trid
        ));
        foreach ($translations as $t) {
            $group_ids[] = (int)$t->element_id;
        }
    }
    $group_ids = array_unique($group_ids);

    echo "Processing Group (TRID: " . ($trid ?: 'N/A') . ")... ";
    
    $aggregated_keywords = [];
    $raw_corpus = "";

    foreach ($group_ids as $pid) {
        $p = get_post($pid);
        if (!$p) continue;

        // 1. Pull from Rank Math Focus Keyword
        $rm_kw = get_post_meta($pid, '_rank_math_focus_keyword', true);
        if ($rm_kw) {
            foreach (explode(',', $rm_kw) as $kw) {
                $aggregated_keywords[] = trim(strtolower($kw));
            }
        }

        // 2. Extract from Title (Keep as phrases if relevant)
        $title_clean = preg_replace('/[^a-z0-9 ]/iu', '', $p->post_title);
        $aggregated_keywords[] = trim(strtolower($title_clean));

        // 3. Accumulate corpus for frequency analysis
        $raw_corpus .= " " . $p->post_title;
        $itinerary_keys = ['tours_program', 'tours_program_style4', 'itinerary'];
        foreach ($itinerary_keys as $key) {
            $prog = get_post_meta($pid, $key, true);
            if (is_array($prog)) {
                foreach ($prog as $item) {
                    $raw_corpus .= " " . ($item['title'] ?? '') . " " . ($item['desc'] ?? $item['content'] ?? '') . " ";
                }
            }
        }
        $raw_corpus .= " " . get_post_meta($pid, 'tours_highlight', true);
    }

    $corpus_clean = wp_strip_all_tags($raw_corpus);
    $words = explode(' ', preg_replace('/[^a-z0-9 ]/iu', '', strtolower($corpus_clean)));
    $words = array_values(array_filter($words, function($w) { return strlen($w) > 2; }));

    // Phrase Detection (Bigrams)
    $bigrams = [];
    for ($i = 0; $i < count($words) - 1; $i++) {
        $w1 = $words[$i];
        $w2 = $words[$i+1];
        if (in_array($w1, $stop_words) || in_array($w2, $stop_words)) continue;
        if (strlen($w1) < 4 || strlen($w2) < 4) continue;
        $bigrams[] = "$w1 $w2";
    }
    
    $bigram_freq = array_count_values($bigrams);
    arsort($bigram_freq);
    $top_bigrams = array_slice(array_keys($bigram_freq), 0, 8);
    foreach ($top_bigrams as $tb) {
        $aggregated_keywords[] = $tb;
    }

    // Single word frequency
    $word_freq = array_count_values(array_filter($words, function($w) use ($stop_words) {
        return mb_strlen($w) > 4 && !in_array($w, $stop_words);
    }));
    arsort($word_freq);
    $top_words = array_slice(array_keys($word_freq), 0, 15);
    foreach ($top_words as $tw) {
        $aggregated_keywords[] = $tw;
    }

    // Final cleanup
    $aggregated_keywords = array_unique(array_filter($aggregated_keywords, function($k) use ($stop_words) {
        return strlen($k) > 3 && !in_array($k, $stop_words);
    }));
    $final_kw_str = implode(', ', $aggregated_keywords);
    
    if ($final_kw_str) {
        foreach ($group_ids as $pid) {
            update_post_meta($pid, '_ota_keywords', $final_kw_str);
        }
        echo "OK (" . count($aggregated_keywords) . " keywords synced)\n";
    } else {
        echo "Skipped (No keywords found)\n";
    }
}

echo "\n=== Done! Keywords redefined and synced. ===\n";
