<?php
/**
 * Simple Image Proxy to bypass CORS and Hotlinking protection
 * Usage: img_proxy.php?url=ENCODED_URL
 */

// Basic security: don't allow direct browsing without URL
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header("HTTP/1.1 400 Bad Request");
    echo "URL parameter is required.";
    exit;
}

// PHP automatically decodes URL parameters in $_GET. 
// No need for extra urldecode() unless it was double-encoded.
$url = $_GET['url'];

// Validate URL (only allow known OTA domains for safety)
$allowed_domains = [
    'tripadvisor.com',
    'googleusercontent.com',
    'getyourguide.com',
    'viator.com',
    'media-cdn.tripadvisor.com',
    'images.getyourguide.com',
    'cdn.getyourguide.com'
];

$parsed_url = parse_url($url);
$host = isset($parsed_url['host']) ? strtolower($parsed_url['host']) : '';

$is_allowed = false;
foreach ($allowed_domains as $domain) {
    if (strpos($host, $domain) !== false) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    header("HTTP/1.1 403 Forbidden");
    echo "Domain not allowed: " . htmlspecialchars($host);
    exit;
}

// Fetch the image
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.tripadvisor.com/');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_HEADER, false);

$img_data = curl_exec($ch);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Image Proxy Connection Error: $error_msg for URL: $url");
    exit;
}
curl_close($ch);

// Ensure we got an actual image and a successful response
// Note: Some CDNs return 304 if cached, or other codes. We prefer 200.
if (($http_code === 200 || $http_code === 304) && $img_data && strpos($content_type, 'image/') !== false) {
    header("Content-Type: $content_type");
    header("Cache-Control: public, max-age=2592000"); // Cache for 30 days
    header("Access-Control-Allow-Origin: *");
    echo $img_data;
} else {
    header("HTTP/1.1 404 Not Found");
    error_log("Image Proxy Failed: HTTP $http_code, Content-Type: $content_type for URL: $url");
}
