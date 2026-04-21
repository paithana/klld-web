<?php
/**
 * Server-side TripAdvisor HTML scraper.
 * Fetches review pages and extracts data using DOMDocument/XPath.
 * 
 * Usage: php ta_scraper.php [start_offset] [end_offset]
 * Example: php ta_scraper.php 0 1230
 */

if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

$start = isset($argv[1]) ? (int)$argv[1] : 0;
$end   = isset($argv[2]) ? (int)$argv[2] : 1230;

$base = 'https://www.tripadvisor.com/Attraction_Review-g297914-d1960808-Reviews';
$sfx  = '-Khao_Lak_Land_Discovery-Khao_Lak_Takua_Pa_Phang_Nga_Province.html';

$all_reviews = [];

function strip_emoji($text) {
    return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{200D}\x{20E3}\x{E0020}-\x{E007F}]/u', '', $text);
}

function fetch_page($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
        ],
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'html' => $html];
}

function extract_reviews($html) {
    if (!$html || strlen($html) < 1000) return [];
    
    $reviews = [];
    
    // Use regex to find review data in the HTML since DOMDocument may struggle with modern React HTML
    // Look for reviewCard sections
    
    // Extract reviewer names: profile links with text
    // Pattern: data-automation="reviewCard"
    
    // Split HTML by reviewCard markers
    $parts = preg_split('/data-automation="reviewCard"/', $html);
    array_shift($parts); // Remove before first card
    
    foreach ($parts as $part) {
        // Limit to just this card (find next card boundary or reasonable amount)
        $card = substr($part, 0, 5000);
        
        $review = [];
        
        // Extract reviewer name from Profile link
        if (preg_match('/<a[^>]*href="\/Profile\/[^"]*"[^>]*>([^<]+)<\/a>/i', $card, $m)) {
            $name = trim(strip_tags($m[1]));
            if ($name === 'Khao Lak Land Discovery' || strlen($name) < 2) continue;
            $review['reviewer_name'] = trim(strip_emoji($name));
        } else {
            continue;
        }
        
        // Extract avatar URL
        if (preg_match('/img[^>]*src="(https:\/\/dynamic-media-cdn\.tripadvisor\.com\/media\/photo[^"]*)"/', $card, $m)) {
            $review['avatar_url'] = $m[1];
        } else {
            $review['avatar_url'] = null;
        }
        
        // Extract rating from SVG title "X of 5 bubbles"
        if (preg_match('/<title>(\d+) of 5 bubbles<\/title>/', $card, $m)) {
            $review['rating'] = (int)$m[1];
        } else {
            $review['rating'] = 0;
        }
        
        // Extract title from ShowUserReviews link
        if (preg_match('/<a[^>]*href="[^"]*ShowUserReviews[^"]*"[^>]*>(.*?)<\/a>/is', $card, $m)) {
            $title = trim(strip_tags($m[1]));
            $review['title'] = trim(strip_emoji($title));
        } else {
            $review['title'] = '';
        }
        
        // Extract review text from span.yCeTE  
        if (preg_match_all('/<span[^>]*class="[^"]*yCeTE[^"]*"[^>]*>(.*?)<\/span>/is', $card, $m)) {
            $texts = array_filter(array_map(function($t) use ($review) {
                $t = trim(strip_tags($t));
                return ($t !== ($review['title'] ?? '') && strlen($t) > 0) ? $t : '';
            }, $m[1]));
            $review['text'] = trim(strip_emoji(implode(' ', $texts)));
        } else {
            $review['text'] = '';
        }
        
        // Extract date
        if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{4}/', $card, $m)) {
            $review['date'] = $m[0];
        } else {
            $review['date'] = '';
        }
        
        if (!empty($review['reviewer_name']) && (!empty($review['text']) || !empty($review['title']))) {
            $reviews[] = $review;
        }
    }
    
    return $reviews;
}

echo "TripAdvisor Review Scraper\n";
echo "Scraping offsets $start to $end\n\n";

for ($offset = $start; $offset <= $end; $offset += 10) {
    $url = ($offset === 0) 
        ? $base . $sfx 
        : $base . '-or' . $offset . $sfx;
    
    echo "Fetching offset $offset... ";
    $result = fetch_page($url);
    
    if ($result['code'] !== 200) {
        echo "HTTP {$result['code']} - skipping\n";
        if ($result['code'] === 403) {
            echo "  -> Got 403 Forbidden. Server may be blocked.\n";
            // Try a few more, then give up if persistent
            continue;
        }
        continue;
    }
    
    $reviews = extract_reviews($result['html']);
    echo count($reviews) . " reviews extracted\n";
    
    $all_reviews = array_merge($all_reviews, $reviews);
    
    // Be nice
    usleep(500000); // 500ms
}

echo "\n=== RESULTS ===\n";
echo "Total reviews: " . count($all_reviews) . "\n";
echo "With ratings: " . count(array_filter($all_reviews, fn($r) => $r['rating'] > 0)) . "\n";
echo "With text: " . count(array_filter($all_reviews, fn($r) => strlen($r['text']) > 0)) . "\n";
echo "With avatar: " . count(array_filter($all_reviews, fn($r) => !empty($r['avatar_url']))) . "\n";

// Save to JSON
$output_file = __DIR__ . '/ta_scraped_reviews.json';
$data = [
    'source' => 'tripadvisor',
    'location_id' => 'd1960808',
    'location_name' => 'Khao Lak Land Discovery',
    'scraped_at' => date('Y-m-d H:i:s'),
    'total_reviews' => count($all_reviews),
    'reviews' => $all_reviews,
];

file_put_contents($output_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nSaved to: $output_file\n";
