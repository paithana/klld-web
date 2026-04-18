<?php
require_once __DIR__ . '/wp-load.php';
$autoload = ABSPATH . 'wp-content/plugins/google-listings-and-ads/vendor/autoload.php';
if (file_exists($autoload)) require_once $autoload;

use phpseclib3\Net\SFTP;

$host = 'partnerupload.google.com';
$port = 19321;
$user = 'mc-sftp-5520609361';
$pass = ':(2Q>%zv4e';

echo "DEBUG: Connecting to $host:$port...\n";
$sftp = new SFTP($host, $port);

define('PHPSECLIB_LOG_REALTIME', true);
// Enable logging
$sftp->setLogger(new class {
    public function log($message) {
        echo "LOG: $message\n";
    }
});

echo "DEBUG: Attempting login...\n";
if (!$sftp->login($user, $pass)) {
    echo "DEBUG: Login FAILED.\n";
    echo "DEBUG: Logs:\n" . $sftp->getSFTPLog() . "\n";
} else {
    echo "DEBUG: Login SUCCESS!\n";
}
