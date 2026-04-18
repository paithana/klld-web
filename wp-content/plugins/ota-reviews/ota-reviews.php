<?php
/**
 * Plugin Name: KLLD OTA Reviews Manager
 * Description: Multi-platform review synchronization and management tool (GYG, Viator, TripAdvisor, GMB).
 * Version: 2.1.0
 * Author: Antigravity (KLLD Team)
 * Text Domain: ota-reviews
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define Plugin Constants
define( 'KLLD_OTA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KLLD_OTA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Initialize the Tools Loader
require_once KLLD_OTA_PLUGIN_DIR . 'admin-tools-loader.php';

// Enqueue Modern Styles
add_action( 'wp_enqueue_scripts', function() {
    if ( is_singular( 'st_tours' ) ) {
        wp_enqueue_style( 'ota-modern-reviews', KLLD_OTA_PLUGIN_URL . 'assets/css/modern-reviews.css', [], '2.1.0' );
    }
});
