<?php
require_once '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-content/plugins/google-listings-and-ads/vendor/autoload.php';

use phpseclib3\Net\SFTP;

$host = 'partnerupload.google.com';
$port = 19321;
$user = 'mc-sftp-5520609361';
$pass = ':(2Q>%zv4e';

echo "DEBUG: Connecting to $host:$port...\n";
try {
    $sftp = new SFTP($host, $port);
    
    // Enable logging
    define('PHPSECLIB_LOG_REALTIME', true);
    
    echo "DEBUG: Attempting login...\n";
    if (!$sftp->login($user, $pass)) {
        echo "DEBUG: Login FAILED.\n";
        echo "DEBUG: Logs:\n" . $sftp->getSFTPLog() . "\n";
    } else {
        echo "DEBUG: Login SUCCESS!\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
