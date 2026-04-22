<?php
/**
 * Simple Image Proxy to bypass CORS and Hotlinking protection
 * Usage: img_proxy.php?url=ENCODED_URL
 */

if (!isset($_GET['url'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$url = urldecode($_GET['url']);

// Validate URL (only allow known OTA domains for safety)
$allowed_domains = [
    'tripadvisor.com',
    'googleusercontent.com',
    'getyourguide.com',
    'viator.com',
    'media-cdn.tripadvisor.com',
    'images.getyourguide.com'
];

$parsed_url = parse_url($url);
$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

$is_allowed = false;
foreach ($allowed_domains as $domain) {
    if (strpos($host, $domain) !== false) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    header("HTTP/1.1 403 Forbidden");
    echo "Domain not allowed.";
    exit;
}

// Fetch the image
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, false);

$img_data = curl_exec($ch);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 && $img_data) {
    header("Content-Type: $content_type");
    header("Cache-Control: public, max-age=86400"); // Cache for 24 hours
    header("Access-Control-Allow-Origin: *"); // Allow CORS from our site
    echo $img_data;
} else {
    header("HTTP/1.1 404 Not Found");
}
