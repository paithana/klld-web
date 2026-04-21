<?php
// Function to fetch with randomized delay and headers
function fetch_with_retry($url, $retry_count = 3) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Connection: keep-alive",
        "Upgrade-Insecure-Requests: 1"
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $http_code, 'body' => $response];
}

$url = "https://www.tripadvisor.com/Attraction_Review-g297914-d2413571-Reviews-Khao_Lak_Land_Discovery-Khao_Lak_Phang_Nga_Province.html";
echo "Attempting fetch...\n";
$result = fetch_with_retry($url);

if ($result['code'] === 200) {
    echo "✅ Success! Content captured.\n";
    file_put_contents('ta_scraped_test.html', $result['body']);
} else {
    echo "❌ Failed (HTTP " . $result['code'] . "). Skipping.\n";
}
