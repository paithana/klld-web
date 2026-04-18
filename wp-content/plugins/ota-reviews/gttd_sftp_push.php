<?php
/**
 * GTTD SFTP Push - Automated Feed Delivery
 * This script captures the Google Things to Do feed and uploads it via SFTP.
 */

// ── Load WordPress ─────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    $search_paths = [
        __DIR__ . '/wp-load.php',
        dirname(__DIR__, 2) . '/wp-load.php',
        dirname(__DIR__, 3) . '/wp-load.php',
        dirname(__DIR__, 4) . '/wp-load.php',
        '/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php'
    ];
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

if (!defined('ABSPATH')) die('Error: Could not load WordPress environment.');

// ── Configuration ──────────────────────────────────────────────────────────
// 1. GTTD SFTP Credentials (from WordPress options)
$sftp_host = get_option('_gttd_sftp_host', 'partnerupload.google.com'); 
$sftp_port = (int)get_option('_gttd_sftp_port', 19321);
$sftp_user = get_option('_gttd_sftp_user', 'mc-sftp-5520609361'); 
$sftp_pass = get_option('_gttd_sftp_pass');
$sftp_key  = get_option('_gttd_sftp_key', '/home/u451564824/.ssh/gttd_rsa');
$target_file = get_option('_gttd_sftp_file', 'tours_feed.xml');
$local_temp = __DIR__ . '/tours_feed.tmp.xml';
$auth_method = $sftp_pass ? 'password' : 'key';

// ── Load Dependencies ──────────────────────────────────────────────────────
$autoload = ABSPATH . 'wp-content/plugins/google-listings-and-ads/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader;

if ( defined( 'KLLD_TOOL_RUN' ) ) {
    echo '<div class="wrap"><h1>Google Things to Do Feed Delivery</h1>';
    echo '<p>Push the latest product feed to Google servers via SFTP.</p>';
    if ( ! isset( $_POST['run_push'] ) ) {
        echo '<form method="post"><input type="submit" name="run_push" class="button button-primary" value="Run SFTP Push Now"></form>';
        return; // Stop here if not running
    }
    echo '<pre style="background:#f0f0f0; padding:15px; border:1px solid #ccc; margin-top:10px;">';
}

// ── Generate Feed Data ─────────────────────────────────────────────────────
if (!PHP_SAPI === 'cli' && !current_user_can('manage_options')) die('Unauthorized.');

echo "Generating feed data...\n";
$_GET['format'] = 'xml';
ob_start();
include __DIR__ . '/google-tours-feed.php';
$xml_content = ob_get_clean();

if (empty($xml_content)) {
    die("Error: Failed to generate XML feed content.\n");
}

file_put_contents($local_temp, $xml_content);
echo "Feed saved locally to $local_temp (" . strlen($xml_content) . " bytes).\n";

// ── SFTP Upload ────────────────────────────────────────────────────────────
echo "Connecting to $sftp_host:$sftp_port as $sftp_user...\n";

try {
    $sftp = new SFTP($sftp_host, $sftp_port);
    
    // Auth Method
    if ($sftp_pass) {
        if (!$sftp->login($sftp_user, $sftp_pass)) {
            die("SFTP Login Failed using password for $sftp_user\n");
        }
    } else {
        // Load the private key
        if (!file_exists($sftp_key)) {
            die("Error: Private key file not found at $sftp_key\n");
        }
        $key_content = file_get_contents($sftp_key);
        $key = PublicKeyLoader::load($key_content);

        if (!$sftp->login($sftp_user, $key)) {
            die("SFTP Login Failed using key at $sftp_key\n");
        }
    }

    echo "Login successful. Uploading $target_file...\n";
    
    if ($sftp->put($target_file, $local_temp, SFTP::SOURCE_LOCAL_FILE)) {
        echo "Upload successful!\n";
        @unlink($local_temp);
    } else {
        echo "Upload failed.\n";
    }

} catch (\Exception $e) {
    echo "Error during SFTP upload: " . $e->getMessage() . "\n";
}

if ( defined( 'KLLD_TOOL_RUN' ) ) {
    echo '</pre></div>';
}
