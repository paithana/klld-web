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
$sftp_key  = get_option('_gttd_sftp_key', '/home/u451564824/.ssh/gttd_new_rsa');
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
    ?>
    <style>
        .gttd-container { font-family: 'Inter', system-ui, sans-serif; margin-top: 20px; }
        .gttd-header { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .gttd-header h1 { color: white; margin: 0; font-size: 28px; font-weight: 700; }
        
        .gttd-card { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .gttd-btn { display: inline-flex; align-items: center; padding: 12px 24px; background: #0ea5e9; color: white; border-radius: 8px; text-decoration: none; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; gap: 10px; }
        .gttd-btn:hover { background: #0284c7; transform: translateY(-1px); }
        
        .gttd-log { background: #0f172a; border-radius: 12px; padding: 20px; font-family: 'Fira Code', 'Courier New', monospace; font-size: 13px; color: #38bdf8; border: 1px solid #1e293b; margin-top: 20px; line-height: 1.6; min-height: 200px; }
    </style>

    <div class="wrap gttd-container">
        <div class="gttd-header">
            <h1>📡 GTTD Feed Delivery</h1>
            <p style="margin-top:5px; opacity:0.9;">Push the latest product feed to Google servers via SFTP.</p>
        </div>

        <div class="gttd-card">
            <?php if ( ! isset( $_POST['run_push'] ) ) : ?>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div>
                        <p style="margin-top:0;"><b>Current Target:</b> <code><?php echo $sftp_host; ?>:<?php echo $sftp_port; ?></code></p>
                        <p style="margin-bottom:0; color:#64748b; font-size:13px;">This process generates an XML feed and uploads it to your partner account.</p>
                    </div>
                    <form method="post" style="margin-left:auto;">
                        <button type="submit" name="run_push" value="1" class="gttd-btn">🚀 Launch SFTP Push Now</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="font-weight:700; margin-bottom:10px; color:#0f172a;">Transmission in progress...</div>
                <div class="gttd-log">
            <?php endif;
}

// ... rest of script ...

if ( defined( 'KLLD_TOOL_RUN' ) && isset($_POST['run_push']) ) {
    echo '</div><div style="margin-top:20px;"><a href="?page=klld-gttd-push" class="button">← Back to Overview</a></div></div>';
}
