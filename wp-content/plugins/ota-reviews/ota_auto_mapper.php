<?php
/**
 * One-time OTA Auto-Mapper
 * Scrapes the GYG supplier page to find activity IDs and matches them to WP Tours.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$supplier_id = 203466;
if (!isset($logFile)) {
    $logFile = dirname(__FILE__) . '/ota_auto_mapper_log.txt';
}

function logMapper($msg) {
    $log_path = dirname(__FILE__) . '/ota_auto_mapper_log.txt';
    $txt = "[" . date('Y-m-d H:i:s') . "] $msg\n";
    // echo $txt;
    file_put_contents($log_path, $txt, FILE_APPEND);
}

logMapper("Starting Auto-Mapper Phase 1: Discover Activities...");

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

// Include Sync Engine for utilities
$sync_path = dirname(__FILE__) . '/ota_sync.php';
if (file_exists($sync_path)) {
    require_once $sync_path;
}

if (!PHP_SAPI === 'cli' && !current_user_can('manage_options')) die('Unauthorized.');

logMapper("Starting Auto-Mapper Phase 1: Discover Activities...");

// ── Phase 1a: GetYourGuide Discovery ──────────────────────────────────────
$activities = [];
$intUser = get_option('_gyg_integrator_user');
$intPass = get_option('_gyg_integrator_pass');

if ($intUser && $intPass) {
    logMapper("[GYG] Checking Integrator API...");
    $resp = wp_remote_get("https://api.getyourguide.com/integrator/v1/activities", [
        'timeout' => 20,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("$intUser:$intPass"),
            'Accept' => 'application/json'
        ]
    ]);

    if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        $acts = $data['activities'] ?? $data['data'] ?? (is_array($data) ? $data : []);
        foreach ($acts as $act) {
            $activities[] = [
                'type' => 'gyg',
                'id' => $act['activity_id'] ?? $act['activityId'] ?? $act['id'],
                'title' => $act['title'] ?? $act['name'] ?? $act['activityTitle'],
                'meta_key' => '_gyg_activity_id'
            ];
        }
        logMapper("✅ [GYG] Found " . count($activities) . " activities via Integrator API.");
    }
}

// Fallback to GYG Scraping if needed
if (empty($activities)) {
    logMapper("[GYG] Attempting Scraping fallback...");
    $urls = [
        "https://www.getyourguide.com/khao-lak-land-discovery-co-ltd-s{$supplier_id}/",
        "https://www.getyourguide.com/s/?q=Khao+Lak+Land+Discovery"
    ];
    foreach ($urls as $url) {
        logMapper("[GYG] Scraping: $url");
        $resp = wp_remote_get($url, ['timeout' => 15, 'user-agent' => 'Mozilla/5.0']);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $html = wp_remote_retrieve_body($resp);
            preg_match_all('/\/([a-z0-9\-]+)-t(\d+)\/|activity\/(\d+)\//i', $html, $matches);
            $foundIds = array_unique(array_merge($matches[2], $matches[3]));
            foreach (array_filter($foundIds) as $id) {
                $activities[] = ['type' => 'gyg', 'id' => $id, 'title' => "GYG Activity #$id", 'meta_key' => '_gyg_activity_id'];
            }
        }
    }
}

// ── Phase 1b: TripAdvisor Discovery ──────────────────────────────────────
$ta_api_key = get_option('_ta_api_key');
$ta_discoveries = [];
if ($ta_api_key) {
    logMapper("[TA] Starting discovery via Location Search API...");
    $tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => 'publish']);
    foreach ($tours as $tour) {
        if (get_post_meta($tour->ID, '_tripadvisor_activity_id', true)) continue;

        $search_query = urlencode($tour->post_title);
        $url = "https://api.content.tripadvisor.com/api/v1/location/search?key={$ta_api_key}&searchQuery={$search_query}&category=attractions&language=en";
        
        $resp = wp_remote_get($url, ['timeout' => 10]);
        if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            if (!empty($data['data'])) {
                foreach ($data['data'] as $loc) {
                    $ta_discoveries[] = [
                        'type' => 'tripadvisor',
                        'id' => 'd' . $loc['location_id'],
                        'title' => $loc['name'],
                        'meta_key' => '_tripadvisor_activity_id',
                        'target_wp_id' => $tour->ID,
                        'score' => 0 // Calculated below
                    ];
                }
            }
        }
    }
    logMapper("✅ [TA] Discovered " . count($ta_discoveries) . " potential matches.");
}

// ── Phase 2: WP Integration (Enhanced Matching) ───────────────────────────
logMapper("Starting Auto-Mapper Phase 2: Matching & Integration...");

$matchedCount = 0;
$all_tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => 'publish']);

// Match GYG
foreach ($activities as $act) {
    $highest_score = 0; $best_tour = null;
    foreach ($all_tours as $tour) {
        $score = OTAReviewSync::calculate_match_score($act['title'], $tour->post_title);
        if ($score > $highest_score && $score > 75) {
            $highest_score = $score;
            $best_tour = $tour;
        }
    }
    if ($best_tour) {
        update_post_meta($best_tour->ID, $act['meta_key'], $act['id']);
        logMapper("✅ [GYG] Matched ({$highest_score}%): '{$best_tour->post_title}' <-> '{$act['title']}'");
        $matchedCount++;
    }
}

// Match TripAdvisor (Guided by discovery target)
foreach ($ta_discoveries as $disc) {
    $tour_title = get_the_title($disc['target_wp_id']);
    $score = calculate_match_score($disc['title'], $tour_title);
    if ($score > 80) {
        update_post_meta($disc['target_wp_id'], $disc['meta_key'], $disc['id']);
        logMapper("✅ [TA] Matched ({$score}%): '{$tour_title}' <-> '{$disc['title']}'");
        $matchedCount++;
    }
}

logMapper("Done! Saved $matchedCount mappings across platforms.");
