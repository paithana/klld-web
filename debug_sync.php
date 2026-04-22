<?php
require 'wp-load.php';
require_once 'wp-content/plugins/ota-reviews/ota_sync.php';

$sync = new OTAReviewSync();
$sync->run(5000, true);
