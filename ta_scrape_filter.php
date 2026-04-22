<?php
/**
 * TripAdvisor Scraper & Filter Tool
 * Usage: php ta_scrape_filter.php <url_or_html_file> <keyword1> <keyword2> <keyword3>
 */

if (php_sapi_name() !== 'cli') die('CLI only');

$source = $argv[1] ?? '';
$keywords = array_slice($argv, 2);

if (empty($source)) {
    die("Usage: php ta_scrape_filter.php <url_or_html_file> <keyword1> [keyword2] [keyword3]\n");
}

function fetch_content($source) {
    if (file_exists($source)) {
        return file_get_contents($source);
    }
    
    echo "Attempting to fetch URL: $source\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $source,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code !== 200) {
        echo "❌ Failed to fetch URL (HTTP $code). TripAdvisor likely blocked the server.\n";
        echo "Tip: Save the page as HTML and pass the filename instead.\n";
        return null;
    }
    return $html;
}

function parse_reviews($html) {
    $reviews = [];
    $parts = preg_split('/data-automation="reviewCard"/', $html);
    array_shift($parts);
    
    foreach ($parts as $part) {
        $card = substr($part, 0, 5000);
        $review = [];
        
        // Name
        if (preg_match('/<a[^>]*href="\/Profile\/[^"]*"[^>]*>([^<]+)<\/a>/i', $card, $m)) {
            $review['author'] = trim(strip_tags($m[1]));
        } else continue;

        // Text
        if (preg_match('/<span[^>]*class="[^"]*yCeTE[^"]*"[^>]*>(.*?)<\/span>/is', $card, $m)) {
            $review['text'] = trim(strip_tags($m[1]));
        } else $review['text'] = '';

        // Rating
        if (preg_match('/<title>(\d+) of 5 bubbles<\/title>/', $card, $m)) {
            $review['rating'] = (int)$m[1];
        } else $review['rating'] = 0;

        $reviews[] = $review;
    }
    return $reviews;
}

$html = fetch_content($source);
if (!$html) exit;

$all_reviews = parse_reviews($html);
$filtered = [];

foreach ($all_reviews as $r) {
    $match = false;
    if (empty($keywords)) {
        $match = true;
    } else {
        foreach ($keywords as $kw) {
            if (stripos($r['text'], $kw) !== false) {
                $match = true;
                break;
            }
        }
    }
    if ($match) $filtered[] = $r;
}

echo "\n--- RESULTS ---\n";
echo "Total parsed: " . count($all_reviews) . "\n";
echo "Filtered (" . implode(', ', $keywords) . "): " . count($filtered) . "\n\n";

foreach ($filtered as $i => $r) {
    echo "[" . ($i+1) . "] Author: {$r['author']} (Rating: {$r['rating']})\n";
    echo "Text: " . substr($r['text'], 0, 150) . "...\n\n";
}
