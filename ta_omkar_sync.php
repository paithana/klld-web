<?php
/**
 * ta_omkar_sync.php
 * Incremental TripAdvisor review sync using Omkar Cloud API.
 * 
 * Usage: php ta_omkar_sync.php [--force]
 */

require_once __DIR__ . '/wp-load.php';

$api_key = get_option('_omkar_api_key');
if (!$api_key) {
    // Default placeholder for safety
    $api_key = 'ok_16a26f4d70cdc7c0838b81ad397b44b3';
}
$base_url = 'https://tripadvisor-scraper-api.omkar.cloud/tripadvisor/reviews';
$query_url = 'https://www.tripadvisor.com/Attraction_Review-g297914-d1960808-Reviews-Khao_Lak_Land_Discovery-Khao_Lak_Takua_Pa_Phang_Nga_Province.html';

$force = in_array('--force', $argv);
$last_known_id = get_option('_ta_last_synced_id', '');

echo "🚀 Starting Incremental TripAdvisor Sync...\n";
if ($last_known_id && !$force) {
    echo "   🔍 Last synced ID: $last_known_id\n";
}

$all_new_reviews = [];
$page = 1;
$total_pages = 1;
$stop_sync = false;

do {
    echo "   📄 Page $page... ";
    $params = [
        'query' => $query_url,
        'page' => $page,
        'sort_by' => 'most_recent'
    ];
    $url = $base_url . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'API-Key: ' . $api_key,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        echo "❌ HTTP $http_code\n";
        break;
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['results'])) {
        echo "❌ Invalid JSON\n";
        break;
    }

    $total_pages = $data['total_pages'] ?? 1;
    $results = $data['results'];
    
    foreach ($results as $r) {
        $review_id = (string)($r['review_id'] ?? '');
        
        // Stop if we hit the last synced ID
        if (!$force && $last_known_id && $review_id === $last_known_id) {
            echo "✅ Hit last known ID ($review_id). Stopping.\n";
            $stop_sync = true;
            break;
        }

        // Extract product name
        $product_name = 'Khao Lak Land Discovery';
        if (isset($r['review_link']) && preg_match('/-r\d+-([^-]+)-/', $r['review_link'], $m)) {
            $product_name = str_replace('_', ' ', $m[1]);
        }

        $all_new_reviews[] = [
            'id' => $review_id,
            'reviewer_name' => $r['reviewer']['name'] ?? 'TripAdvisor Traveler',
            'text' => $r['text'] ?? '',
            'title' => $r['title'] ?? '',
            'rating' => $r['rating'] ?? 5,
            'date' => $r['published_at_date'] ?? '',
            'review_of' => $product_name,
            'photos' => $r['images'] ?? []
        ];
    }

    if ($stop_sync) break;
    
    echo "Got " . count($results) . " reviews.\n";
    $page++;
    usleep(100000); // 100ms

} while ($page <= $total_pages && $page <= 5); // Limit to 5 pages per sync unless forced

$count = count($all_new_reviews);
if ($count > 0) {
    echo "\n📦 Sync complete. Collected $count new reviews.\n";
    
    // Save to temp file for importer
    $output = [
        'automated_sync' => [
            'tour_name' => 'TripAdvisor Incremental Sync',
            'post_ids' => [],
            'reviews' => $all_new_reviews
        ]
    ];
    
    $file_path = __DIR__ . '/wp-content/plugins/ota-reviews/data/source_ta.json';
    file_put_contents($file_path, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Run the existing importer
    echo "📥 Importing to WordPress...\n";
    include_once __DIR__ . '/wp-content/plugins/ota-reviews/import_ta_reviews.php';
    
    // Update the last known ID with the first review from the first page
    if (!empty($all_new_reviews)) {
        update_option('_ta_last_synced_id', $all_new_reviews[0]['id']);
        echo "💾 Updated last synced ID to: " . $all_new_reviews[0]['id'] . "\n";
    }
} else {
    echo "\n✨ Everything up to date. No new reviews found.\n";
}
