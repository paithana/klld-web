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
    
    echo "🚀 Launching Antigravity Engine for URL: $source\n";
    
    // Path to our new stealth scraper
    $scraper_path = __DIR__ . '/wp-content/plugins/ota-reviews/scrapers/ta/antigravity_scraper.js';
    $node_path = "/home/u451564824/.nvm/versions/node/v24.15.0/bin/node";
    $cmd = "$node_path $scraper_path " . escapeshellarg($source) . " 2>&1";
    
    $html = '';
    if ( function_exists( 'shell_exec' ) ) {
        $html = shell_exec( $cmd );
    } elseif ( function_exists( 'proc_open' ) ) {
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $process = proc_open( $cmd, $descriptorspec, $pipes );
        if ( is_resource( $process ) ) {
            $html = stream_get_contents( $pipes[1] );
            fclose( $pipes[0] );
            fclose( $pipes[1] );
            fclose( $pipes[2] );
            proc_close( $process );
        }
    } else {
        echo "❌ Error: Both shell_exec and proc_open are disabled.\n";
        return null;
    }
    
    if (empty($html) || strpos($html, 'DataDome') !== false) {
        echo "❌ Antigravity was blocked by DataDome (Server IP Blocked) or returned empty output.\n";
        echo "Tip: Run the 'ta-scrape.js' in your browser and pass the saved HTML file to this script.\n";
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
