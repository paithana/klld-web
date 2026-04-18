<?php
/**
 * Test Google Customer Reviews Integration
 * Exercises the metadata mapping for the Google Opt-in snippet.
 */

// ── Load WordPress ─────────────────────────────────────────────────────────
require_once dirname(__DIR__, 4) . '/wp-load.php';

echo "=== Google Customer Reviews Integration Test ===\n";

// 1. Find a recent order to test against
$args = [
    'post_type'      => 'st_order',
    'posts_per_page' => 1,
    'post_status'    => 'any',
    'orderby'        => 'date',
    'order'          => 'DESC'
];

$orders = get_posts($args);

if (empty($orders)) {
    die("Error: No st_order found in the database. Integration cannot be verified without data.\n");
}

$order = $orders[0];
$order_id = $order->ID;

echo "Found Order: #$order_id\n";

// 2. Simulate the metadata extraction logic found in ota-reviews.php
$email = get_post_meta( $order_id, 'st_email', true );
$country = get_post_meta( $order_id, 'st_country', true );
$tour_date_raw = get_post_meta( $order_id, 'check_in', true ); 

// 3. Verify Date Formatting
$tour_date_formatted = '';
if ( $tour_date_raw ) {
    $tour_date_formatted = date( 'Y-m-d', strtotime( $tour_date_raw ) );
}

echo "Extracted Data:\n";
echo " - Email: " . ($email ?: "MISSING") . "\n";
echo " - Country: " . ($country ?: "MISSING") . "\n";
echo " - Raw Date: " . ($tour_date_raw ?: "MISSING") . "\n";
echo " - Formatted Date: " . ($tour_date_formatted ?: "INVALID") . "\n";

// 4. Assertions
$errors = [];
if ( ! $email ) $errors[] = "Email is missing from order meta.";
if ( ! $tour_date_formatted || $tour_date_formatted === '1970-01-01' ) {
    $errors[] = "Tour date formatting failed or is missing.";
}

if ( empty($errors) ) {
    echo "\n✅ SUCCESS: Metadata mapping for Google Reviews snippet looks correct.\n";
    
    // Preview the snippet
    echo "\nSnippet Preview:\n";
    ?>
    <script>
    window.renderOptIn = function() {
        window.gapi.load('surveyoptin', function() {
        window.gapi.surveyoptin.render(
            {
            "merchant_id": 5520609361,
            "order_id": "<?php echo $order_id; ?>",
            "email": "<?php echo $email; ?>",
            "delivery_country": "<?php echo $country; ?>",
            "estimated_delivery_date": "<?php echo $tour_date_formatted; ?>"
            });
        });
    }
    </script>
    <?php
} else {
    echo "\n❌ FAILED: Found issues in metadata mapping:\n- " . implode("\n- ", $errors) . "\n";
    exit(1);
}
