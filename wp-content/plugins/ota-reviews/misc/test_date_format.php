<?php
define('KLLD_TOOL_RUN', true);
require_once('wp-load.php');
include(dirname(__DIR__) . '/review_generator.php');

$_POST = [
    'action' => 'generate_custom_reviews',
    'post_ids' => [14528],
    'count' => 1,
    'approve' => 1
];

// Buffer output to catch the JSON response
ob_start();
// Since the file exits on success, we need to catch it or mock it.
// Actually let's just use the function if it was structured that way, 
// but it's procedural code in an IF block.

// We can just run the script and check the last inserted comment meta.
include(dirname(__DIR__) . '/review_generator.php');
