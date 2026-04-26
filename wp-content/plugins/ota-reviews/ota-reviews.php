<?php
/**
 * Plugin Name: OTAs Manager
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
 * Injected on the checkout success page (Both Traveler and WooCommerce).
 */
function klld_render_google_customer_reviews_optin( $order_id, $email = '', $country = '', $tour_date = '', $products = [] ) {
    if ( ! $email || ! $order_id ) return;

    // Only render on checkout/success endpoints to prevent leaks on search/product pages
    if ( ! is_order_received_page() && ! isset( $_GET['st_code'] ) && ! isset( $_GET['order_code'] ) ) {
        // Simple heuristic for Traveler/WC success pages
        if ( ! is_wc_endpoint_url( 'order-received' ) ) return;
    }

    // 1. Sanitize Country (Must be 2-letter CLDR)
    $country = strtoupper( trim( (string)$country ) );
    if ( strlen( $country ) !== 2 || !preg_match('/^[A-Z]{2}$/', $country) ) {
        $country = 'TH';
    }

    // 2. Sanitize and Format Date (Must be YYYY-MM-DD)
    // For tours/transfers, the "delivery" is the service date
    $ts = is_numeric($tour_date) ? (int)$tour_date : strtotime( (string)$tour_date );
    if ( ! $ts || $ts < time() - 86400 ) {
        // If date is invalid or in the past, use +3 days as safe estimate for "delivery"
        $ts = time() + ( 3 * 86400 );
    }
    $formatted_date = date( 'Y-m-d', $ts );

    // 3. GTINs (Using Product IDs)
    $products_json = [];
    if ( ! empty( $products ) ) {
        foreach ( (array)$products as $p_id ) {
            $products_json[] = [ 'gtin' => (string)$p_id ];
        }
    }

    ?>
    <!-- Google Customer Reviews Opt-in -->
    <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
    <script>
    window.renderOptIn = function() {
        window.gapi.load('surveyoptin', function() {
            var config = {
                "merchant_id": 5520609361,
                "order_id": "<?php echo esc_js( $order_id ); ?>",
                "email": "<?php echo esc_js( $email ); ?>",
                "delivery_country": "<?php echo esc_js( $country ); ?>",
                "estimated_delivery_date": "<?php echo esc_js( $formatted_date ); ?>"
            };
            <?php if ( ! empty( $products_json ) ) : ?>
                config.products = <?php echo json_encode( $products_json ); ?>;
            <?php endif; ?>
            window.gapi.surveyoptin.render(config);
        });
    }
    </script>
    <!-- End Google Customer Reviews Opt-in -->
    <?php
}

// Traveler Theme Success Page
add_action( 'st_after_order_success_page_information_table', function( $order_id ) {
    $email = get_post_meta( $order_id, 'st_email', true );
    $country = get_post_meta( $order_id, 'st_country', true );
    $tour_date = get_post_meta( $order_id, 'check_in', true );
    $item_id = get_post_meta( $order_id, 'item_id', true );

    klld_render_google_customer_reviews_optin( $order_id, $email, $country, $tour_date, [ $item_id ] );
});

// WooCommerce Thank You Page
add_action( 'woocommerce_thankyou', function( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $email = $order->get_billing_email();
    $country = $order->get_billing_country();
    $tour_date = '';
    $products = [];

    // Extract tour date and IDs from items
    foreach ( $order->get_items() as $item ) {
        $products[] = $item->get_product_id();
        $check_in = $item->get_meta( '_st_check_in' );
        if ( ! $tour_date && $check_in ) {
            $tour_date = $check_in;
        }
    }

    klld_render_google_customer_reviews_optin( $order_id, $email, $country, $tour_date, $products );
});

