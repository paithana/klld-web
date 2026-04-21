<?php
// Load WordPress
require_once '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php';

$api_key = get_option('_ta_api_key');
$location_id = '15068375'; 
$url_modern = "https://api.content.tripadvisor.com/api/v1/location/{$location_id}/reviews?language=en&limit=5";
$url_legacy = "https://api.tripadvisor.com/api/v1/location/{$location_id}/reviews?language=en&limit=5";
$url_partner = "https://api.tripadvisor.com/api/partner/2.0/location/{$location_id}/reviews?language=en&limit=5";

echo "Testing TripAdvisor API Variations...\n";
echo "API Key Length: " . strlen($api_key) . "\n";

$variations = [
    'Header key' => ['headers' => ['Accept' => 'application/json', 'key' => $api_key]],
    'Header X-TripAdvisor-API-Key' => ['headers' => ['Accept' => 'application/json', 'X-TripAdvisor-API-Key' => $api_key]],
    'Query key' => ['headers' => ['Accept' => 'application/json']], // URL already has key in this variation
    'Referer + Query key' => ['headers' => ['Accept' => 'application/json', 'Referer' => 'https://khaolaklanddiscovery.com']]
];

$endpoints = [
    'Modern' => $url_modern,
    'Legacy' => $url_legacy,
    'Partner 2.0' => $url_partner
];

foreach ($endpoints as $ep_type => $base_url) {
    echo "=== Testing $ep_type Endpoint ===\n";
    foreach ($variations as $name => $args) {
        echo "--- Variation: $name ---\n";
        $test_url = $base_url;
        if ($name === 'Query key' || $name === 'Referer + Query key') {
            $test_url .= "&key={$api_key}";
        } else {
            // key is already handled in headers
        }
        
        $resp = wp_remote_get($test_url, array_merge(['timeout' => 10], $args));
        if (is_wp_error($resp)) {
            echo "Error: " . $resp->get_error_message() . "\n";
        } else {
            $code = wp_remote_retrieve_response_code($resp);
            echo "Response Code: $code\n";
            if ($code === 200) {
                echo "SUCCESS on $ep_type with $name!\n";
                $body = wp_remote_retrieve_body($resp);
                echo "Preview: " . substr($body, 0, 100) . "...\n";
                // If it's a success, we stop here
            }
        }
    }
}
