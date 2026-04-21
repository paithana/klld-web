<?php
/**
 * Test Google Customer Reviews Integration
 * Exercises the metadata mapping for the Google Opt-in snippet.
 */

// ── Load WordPress ─────────────────────────────────────────────────────────
require_once dirname(__DIR__, 4) . '/wp-load.php';

echo "=== Google Customer Reviews Integration Test ===\n";

// 1. Find a recent order to test against (Prefer shop_order if WooCommerce is active)
$is_wc = class_exists('WooCommerce');
$order_type = $is_wc ? 'shop_order' : 'st_order';

echo "Searching for recent $order_type...\n";

$args = [
    'post_type'      => $order_type,
    'posts_per_page' => 1,
    'post_status'    => 'any',
    'orderby'        => 'date',
    'order'          => 'DESC'
];

$orders = get_posts($args);

if (empty($orders)) {
    die("Error: No $order_type found in the database. Integration cannot be verified without data.\n");
}

$order_id = $orders[0]->ID;
echo "Found Order: #$order_id\n";

// 2. Extract metadata
echo "Testing with Order ID: $order_id\n";

if ($is_wc) {
    $order = wc_get_order($order_id);
    $email = $order->get_billing_email();
    $country = $order->get_billing_country();
    $tour_date = '';
    foreach ($order->get_items() as $item) {
        $check_in = $item->get_meta('_st_check_in');
        if ($check_in) {
            $tour_date = $check_in;
            break;
        }
    }
} else {
    $email = get_post_meta( $order_id, 'st_email', true );
    $country = get_post_meta( $order_id, 'st_country', true );
    $tour_date = get_post_meta( $order_id, 'check_in', true );
}

echo "Extracted Email: $email\n";
echo "Extracted Country: $country\n";
echo "Extracted Tour Date: $tour_date\n";

if (!$email) {
    echo "FAIL: Email missing in order.\n";
    exit(1);
}

// 3. Verify Date Formatting Logic
$tour_date_formatted = '';
if ($tour_date) {
    $tour_date_formatted = date( 'Y-m-d', strtotime( $tour_date ) );
    echo "Formatted Date for Google: $tour_date_formatted\n";
    
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tour_date_formatted)) {
        echo "PASS: Date format is valid (YYYY-MM-DD).\n";
    } else {
        echo "FAIL: Invalid date format: $tour_date_formatted\n";
        exit(1);
    }
} else {
    echo "WARNING: Tour date missing, but email found. Proceeding...\n";
}

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
