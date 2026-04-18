<?php
/**
 * Admin Tools Redirect - Points to new integrated KLLD Tools menu.
 * Centralized in: wp-content/themes/traveler-childtheme/inc/ota-tools/admin-tools-loader.php
 */
require_once __DIR__ . '/wp-load.php';

if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
    wp_redirect( admin_url( 'admin.php?page=review-tools' ) );
    exit;
} else {
    wp_redirect( wp_login_url( admin_url( 'admin.php?page=review-tools' ) ) );
    exit;
}
