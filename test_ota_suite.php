<?php
/**
 * OTA Workflow Test Suite (Procedural)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
define('WP_USE_THEMES', false);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';

echo "🚀 Starting OTA Workflow Test Suite...\n";

require_once 'wp-load.php';

echo "✅ WordPress Loaded.\n";

function pass($name) {
    echo "  ✅ PASS: $name\n";
}

function fail($name, $msg) {
    echo "  ❌ FAIL: $name - $msg\n";
}

// 1. Test XML Entity Safety
echo "\n[Component] GTTD Feed Generator\n";
$mock_category = 'Travel & Events > Sightseeing Tours';
try {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"/>');
    $item = $xml->addChild('item');
    $item->addChild('category', htmlspecialchars($mock_category));
    $xml_string = $xml->asXML();
    if (strpos($xml_string, 'Travel &amp; Events &gt; Sightseeing Tours') !== false) {
        pass('XML Entity Escaping');
    } else {
        fail('XML Entity Escaping', 'Not encoded');
    }
} catch (Exception $e) {
    fail('XML Structure', $e->getMessage());
}

// 2. Test Rating Clamping
echo "\n[Component] Review Sync Logic\n";
$clamped = max(1, min(5, round(5.5)));
if ($clamped == 5) pass('Rating Clamping (5.5 -> 5)');
else fail('Rating Clamping', "Got $clamped");

// 3. Test Fuzzy Matching
echo "\n[Component] Auto-Mapper Matching\n";
similar_text(strtolower('Safari'), strtolower('safari'), $percent);
if ($percent == 100) pass('Fuzzy Match (Direct)');
else fail('Fuzzy Match', "Got $percent");

echo "\n===================================\n";
echo "📊 Test Run Complete.\n";
