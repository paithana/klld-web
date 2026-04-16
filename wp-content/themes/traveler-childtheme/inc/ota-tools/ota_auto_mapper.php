<?php
/**
 * One-time OTA Auto-Mapper
 * Scrapes the GYG supplier page to find activity IDs and matches them to WP Tours.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$supplier_id = 203466;
$logFile = dirname(__FILE__) . '/ota_auto_mapper_log.txt';
function logMapper($msg) {
    global $logFile;
    $txt = "[" . date('Y-m-d H:i:s') . "] $msg\n";
    echo $txt;
    file_put_contents($logFile, $txt, FILE_APPEND);
}

logMapper("Starting Auto-Mapper Phase 1: Discover Activities...");

// ── Load WordPress ────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    $search_path = __DIR__;
    $found = false;
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($search_path . '/wp-load.php')) {
            require_once $search_path . '/wp-load.php';
            $found = true;
            break;
        }
        $parent = dirname($search_path);
        if ($parent === $search_path) break;
        $search_path = $parent;
    }
    if (!$found) {
        $abs_path = '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php';
        if (file_exists($abs_path)) {
            require_once $abs_path;
        }
    }
}

if (!PHP_SAPI === 'cli' && !current_user_can('manage_options')) die('Unauthorized.');

$intUser = get_option('_gyg_integrator_user');
$intPass = get_option('_gyg_integrator_pass');

$activities = [];

if ($intUser && $intPass) {
    logMapper("Checking Integrator API (Basic Auth)...");
    $ch = curl_init("https://api.getyourguide.com/integrator/v1/activities");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$intUser:$intPass"),
        'Accept: application/json'
    ]);
    $respBody = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        $data = json_decode($respBody, true);
        $acts = $data['activities'] ?? $data['data'] ?? (is_array($data) ? $data : []);
        foreach ($acts as $act) {
            $activities[] = [
                'activity_id' => $act['activity_id'] ?? $act['activityId'] ?? $act['id'],
                'title' => $act['title'] ?? $act['name'] ?? $act['activityTitle']
            ];
        }
        if (!empty($activities)) {
            logMapper("✅ Success via Integrator API! Found " . count($activities) . " activities.");
        }
    } else {
        logMapper("⚠ Integrator API failed (HTTP $code). Body: " . substr($respBody, 0, 100));
    }
}

// Phase 1b: Fallback to Scraping (if no activities yet)
if (empty($activities)) {
    logMapper("Attempting Scraping fallback...");
    $urls = [
        "https://www.getyourguide.com/khao-lak-land-discovery-co-ltd-s{$supplier_id}/",
        "https://www.getyourguide.com/s/?q=Khao+Lak+Land+Discovery"
    ];

    foreach ($urls as $url) {
        logMapper("Scraping: $url");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($html)) {
            preg_match_all('/\/([a-z0-9\-]+)-t(\d+)\/|activity\/(\d+)\//i', $html, $matches);
            $foundIds = array_unique(array_merge($matches[2], $matches[3]));
            $foundIds = array_filter($foundIds);

            foreach ($foundIds as $id) {
                logMapper("  - Getting metadata for Activity $id...");
                $metaUrl = "https://travelers-api.getyourguide.com/activities/{$id}/reviews?limit=1";
                $ch = curl_init($metaUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                $metaBody = curl_exec($ch);
                curl_close($ch);
                
                $metaData = json_decode($metaBody, true);
                $title = $metaData['data']['activityTitle'] ?? $metaData['activityTitle'] ?? ('Activity #' . $id);

                $activities[] = [
                    'activity_id' => $id,
                    'title' => $title
                ];
            }

            if (!empty($activities)) {
                logMapper("✅ Success via Scraping! Found " . count($activities) . " activities.");
                break;
            }
        }
        logMapper("  - Failed to scrape (HTTP $code).");
    }
}

if (empty($activities)) {
    logMapper("❌ Error: Could not find any activities.");
    die();
}

logMapper("Starting Auto-Mapper Phase 2: WP Integration...");

$tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => -1, 'post_status' => 'publish']);
logMapper("Comparing with " . count($tours) . " tours...");

$matchedCount = 0;
foreach ($activities as $act) {
    $gyg_title = strtolower(trim($act['title']));
    $gyg_id = $act['activity_id'];

    $best_match = null; $highest_score = 0;
    foreach ($tours as $tour) {
        $wp_title = strtolower(trim($tour->post_title));
        if ($wp_title == $gyg_title || strpos($wp_title, $gyg_title) !== false || strpos($gyg_title, $wp_title) !== false) {
            $score = 100;
        } else {
            similar_text($gyg_title, $wp_title, $percent);
            $score = $percent;
        }
        if ($score > $highest_score && $score > 70) {
            $highest_score = $score;
            $best_match = $tour;
        }
    }

    if ($best_match) {
        update_post_meta($best_match->ID, '_gyg_activity_id', $gyg_id);
        logMapper("✅ Matched ({$highest_score}%): '{$best_match->post_title}' <-> '{$act['title']}'");
        $matchedCount++;
    } else {
        logMapper("❌ No match for: '{$act['title']}'");
    }
}

logMapper("Done! Saved $matchedCount mappings.");
