<?php
/**
 * Temporary endpoint to receive scraped TA reviews from browser.
 * POST JSON data and it gets saved to ta_scraped_reviews.json
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$batch = isset($data['batch']) ? $data['batch'] : 'unknown';
$reviews = isset($data['reviews']) ? $data['reviews'] : [];

// Strip emoji from all text fields
function strip_emoji($text) {
    return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{200D}\x{20E3}\x{E0020}-\x{E007F}]/u', '', $text);
}

// Clean reviews
foreach ($reviews as &$r) {
    if (isset($r['ti'])) $r['ti'] = trim(strip_emoji($r['ti']));
    if (isset($r['co'])) $r['co'] = trim(strip_emoji($r['co']));
    if (isset($r['n']))  $r['n']  = trim(strip_emoji($r['n']));
    if (isset($r['ro'])) $r['ro'] = trim(strip_emoji($r['ro'])); // Review Of
    // Expand short keys to readable names
    $r = [
        'reviewer_name' => $r['n'] ?? '',
        'avatar_url'    => $r['av'] ?? null,
        'rating'        => $r['rt'] ?? 0,
        'title'         => $r['ti'] ?? '',
        'text'          => $r['co'] ?? '',
        'date'          => $r['dt'] ?? '',
        'review_of'     => $r['ro'] ?? '', // Preserve the tour name
        'photos'        => $r['ph'] ?? [], // Preserve photos
    ];
}
unset($r);

$file = __DIR__ . '/ta_scraped_reviews.json';

// Load existing data or create new
$existing = [];
if (file_exists($file)) {
    $existing = json_decode(file_get_contents($file), true) ?: [];
}

$existing[$batch] = [
    'count'   => count($reviews),
    'scraped' => date('Y-m-d H:i:s'),
    'reviews' => $reviews,
];

$total = 0;
foreach ($existing as $b) {
    $total += $b['count'] ?? 0;
}

file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'ok'          => true,
    'batch'       => $batch,
    'batch_count' => count($reviews),
    'total_all'   => $total,
]);
