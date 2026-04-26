<?php
/**
 * ta_omkar_full_sync.php
 * Deep sync for ALL TripAdvisor products found in the database.
 */

require_once __DIR__ . '/wp-load.php';

$api_key = get_option('_omkar_api_key');
if (!$api_key) {
    // Default placeholder for safety
    $api_key = 'ok_16a26f4d70cdc7c0838b81ad397b44b3';
}
$base_url = 'https://tripadvisor-scraper-api.omkar.cloud/tripadvisor/reviews';

// 1. Gather all unique TA IDs from the database
global $wpdb;
$db_ids = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_tripadvisor_activity_id' AND meta_value != ''");
$db_urls = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_ta_url' AND meta_value != ''");

// Also add the main supplier ID if not present
$queries = array_merge(['d1960808'], $db_ids);
foreach ($db_urls as $url) {
    if (preg_match('/-d(\d+)-/', $url, $m)) {
        $queries[] = 'd' . $m[1];
    } else {
        $queries[] = $url;
    }
}
$queries = array_unique($queries);

echo "🚀 Starting Full TripAdvisor Sync for " . count($queries) . " entities...\n";

$all_reviews = [];
$total_imported_batch = 0;

foreach ($queries as $query) {
    echo "\n🔍 Querying: $query\n";
    $page = 1;
    $total_pages = 1;
    $query_count = 0;

    do {
        echo "   📄 Page $page... ";
        $params = [
            'query' => $query,
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
            echo "❌ Error $http_code\n";
            break;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['results'])) {
            echo "❌ Invalid JSON\n";
            break;
        }

        $total_pages = $data['total_pages'] ?? 1;
        $results = $data['results'];
        $count = count($results);
        echo "✅ Got $count\n";

        foreach ($results as $r) {
            $product_name = $data['location']['name'] ?? 'Khao Lak Land Discovery';
            
            $all_reviews[] = [
                'id' => (string)($r['review_id'] ?? ''),
                'reviewer_name' => $r['reviewer']['name'] ?? 'TripAdvisor Traveler',
                'text' => $r['text'] ?? '',
                'title' => $r['title'] ?? '',
                'rating' => $r['rating'] ?? 5,
                'date' => $r['published_at_date'] ?? '',
                'review_of' => $product_name,
                'photos' => $r['images'] ?? []
            ];
            $query_count++;
        }

        $page++;
        usleep(50000); // 50ms

    } while ($page <= $total_pages);
    
    echo "   📊 Subtotal for $query: $query_count reviews\n";
}

$total_collected = count($all_reviews);
echo "\n🏁 Finished Collection. Total reviews collected: $total_collected\n";

// Save to source_ta.json
$output = [
    'automated_sync' => [
        'tour_name' => 'KLLD Deep Sync (Omkar)',
        'post_ids' => [],
        'reviews' => $all_reviews
    ]
];

$file_path = __DIR__ . '/wp-content/plugins/ota-reviews/data/source_ta.json';
file_put_contents($file_path, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "💾 Saved results to: $file_path\n";
echo "📥 Importing to WordPress...\n";

// Set last synced ID for incremental sync later (use first review from first query)
if (!empty($all_reviews)) {
    update_option('_ta_last_synced_id', $all_reviews[0]['id']);
}

include_once __DIR__ . '/wp-content/plugins/ota-reviews/import_ta_reviews.php';
