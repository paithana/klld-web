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

/**
 * Google Customer Reviews Opt-in
 * Injected on the checkout success page.
 */
add_action( 'st_after_order_success_page_information_table', function( $order_id ) {
    $email = get_post_meta( $order_id, 'st_email', true );
    $country = get_post_meta( $order_id, 'st_country', true );
    $tour_date = get_post_meta( $order_id, 'check_in', true ); // Tour date

    // Format date to YYYY-MM-DD
    if ( $tour_date ) {
        $tour_date = date( 'Y-m-d', strtotime( $tour_date ) );
    }

    if ( ! $email ) return;

    ?>
    <!-- Google Customer Reviews Opt-in -->
    <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
    <script>
    window.renderOptIn = function() {
        window.gapi.load('surveyoptin', function() {
        window.gapi.surveyoptin.render(
            {
            "merchant_id": 5520609361,
            "order_id": "<?php echo esc_attr( $order_id ); ?>",
            "email": "<?php echo esc_attr( $email ); ?>",
            "delivery_country": "<?php echo esc_attr( $country ); ?>",
            "estimated_delivery_date": "<?php echo esc_attr( $tour_date ); ?>"
            });
        });
    }
    </script>
    <!-- End Google Customer Reviews Opt-in -->
    <?php
});
