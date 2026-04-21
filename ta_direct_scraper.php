<?php
/**
 * Advanced TripAdvisor CLI Scraper
 * Tries to bypass blocks using various headers and domains.
 */

function fetch_ta_page($url) {
    $agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agents[array_rand($agents)]);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Crucial headers to look less like a bot
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8",
        "Accept-Language: en-US,en;q=0.9",
        "Cache-Control: max-age=0",
        "Sec-Ch-Ua: \"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Google Chrome\";v=\"120\"",
        "Sec-Ch-Ua-Mobile: ?0",
        "Sec-Ch-Ua-Platform: \"Windows\"",
        "Upgrade-Insecure-Requests: 1"
    ]);

    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    return ['body' => $result, 'code' => $info['http_code']];
}

$base_url = "https://www.tripadvisor.com/Attraction_Review-g297914-d2413571-Reviews-Khao_Lak_Land_Discovery-Khao_Lak_Phang_Nga_Province.html";
echo "Attempting to scrape $base_url ...\n";

$res = fetch_ta_page($base_url);
if ($res['code'] !== 200) {
    die("Failed with HTTP {$res['code']}. Still blocked.\n");
}

$body = $res['body'];
if (strpos($body, 'reviewCard') === false) {
    echo "Page fetched but no reviews found. Might be a partial block or layout change.\n";
    echo "Body snippet: " . substr(strip_tags($body), 0, 500) . "...\n";
    exit;
}

echo "Success! Found review cards. Parsing...\n";

// Parsing logic (simplified)
$reviews = [];
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $body);
$xpath = new DOMXPath($dom);

$cards = $xpath->query("//div[contains(@data-automation, 'reviewCard')]");
foreach ($cards as $card) {
    $id = $card->getAttribute('id');
    $name = $xpath->query(".//span[contains(@class, 'biGQs')]", $card)->item(0)?->nodeValue ?? "Traveler";
    $text = $xpath->query(".//span[contains(@class, 'ySdfQ')]", $card)->item(0)?->nodeValue ?? "";
    
    $review_of = "";
    $links = $xpath->query(".//a", $card);
    foreach ($links as $link) {
        if (strpos($link->nodeValue, 'Review of') !== false) {
            $review_of = trim(str_replace(['Review of:', 'Review of'], '', $link->nodeValue));
            break;
        }
    }

    if ($text) {
        $reviews[] = [
            'reviewer_name' => $name,
            'text' => $text,
            'review_of' => $review_of,
            'id' => $id
        ];
    }
}

echo "Extracted " . count($reviews) . " reviews.\n";
print_r($reviews);
