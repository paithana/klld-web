<?php
//use Google\Api\Billing;

/**
* Apply a coupon for minimum cart total
**/

//function log_post_meta($post_id) {
//    // Get all post meta
//    $post_meta = get_post_meta($post_id);
//
//    // Loop through each post meta key and value
//    foreach ($post_meta as $key => $value) {
//        // Log the key and its value to the console
//        error_log("Post Meta Key: ".$key, "Value:". $value);
//    }
//}
//
//add_action('wp_head', function() {
//        $post_id = get_the_ID();
//        log_post_meta($post_id);
//});


//add_action( 'woocommerce_applied_coupon', 'check_coupon_klld');
//add_action( 'woocommerce_before_checkout_form', 'check_coupon_klld');
//add_action( 'woocommerce_remove_cart_item', 'check_coupon_klld');

//function check_coupon_klld (){
//	$item_count = WC()->cart->get_cart_contents_count();
//    wc_clear_notices();
//    $coupon_code = 'KLLD2024'; // Replace with your actual coupon code
//    $coupon = new WC_Coupon( $coupon_code );
//    $excluded_categories = $coupon->get_excluded_product_categories();
//	$cart_has_excluded_category = false;
//    // Check if any product in the cart belongs to an excluded category
//    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
//        $product_id = $cart_item['product_id'];
//        $product = wc_get_product( $product_id );
//
//        if (in_array( $product->get_category_ids()[0], $excluded_categories )) {
//            $cart_has_excluded_category = true;
//            break;
//        }
//		else {
//			if ( $item_count > 2 && $cart_has_excluded_category == false ) {
//				WC()->cart->apply_coupon( $coupon_code );
//				//wc_print_notice( 'You just got 10% off your bookings, Enjoy your Holidays!', 'notice' );
//			} else {
//				WC()->cart->remove_coupon( $coupon_code );
// 				//wc_print_notice( "This coupon required minimum of 3 tours - CODE: KLLD2023", 'notice' );
//			}
//    		wc_clear_notices();
//		}
//	}
//
//}
//if ( define('EXCHANGE_TOKEN' && 'EXCHANGE_TOKEN != ""') ){
//	wp_remote_get("https://api.exchangeratesapi.io/v1/latest?access_key="
//}
/* WooCron */
add_filter('action_scheduler_run_queue', function($arg) { return 86400; });

add_filter('rank_math/sitemap/enable_caching', '__return_false');

add_action('wp_enqueue_scripts', 'enqueue_parent_styles', 20);

function enqueue_parent_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri());
}

// // Query JS handle name

// function inspect_scripts() {
//     global $wp_scripts;
//     print_r($wp_scripts->queue);
// }
// add_action( 'wp_print_scripts', 'inspect_scripts' );
//
// function inspect_styles() {
//     global $wp_styles;
//     print_r($wp_styles->queue);
// }
// add_action( 'wp_print_styles', 'inspect_styles' );


/* Put this in the functions.php of your child theme */
// Deregister older versions, loaded by Types, Advanced Custom Fields etc.

// Cartransfer.js
add_action('wp_enqueue_scripts', 'wpshout_dequeue_and_then_enqueue', 100);

function wpshout_dequeue_and_then_enqueue()
{
    wp_dequeue_script('filter-transfer');
    wp_deregister_script('filter-transfer');

    // Enqueue replacement child theme script
    wp_enqueue_script('filter-transfer', get_stylesheet_directory_uri() . '/v3/js/cartransfer.js', array('jquery'));
    wp_register_style('select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all');
    wp_register_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array('jquery'), '1.0', true);
    wp_enqueue_style('select2css');
    wp_enqueue_script('select2');
}

//Custom transfer name on product and email noti
add_filter('woocommerce_order_item_name', 'transfer_title_change', 20, 3);
function transfer_title_change($item_name, $item)
{
    //$product = $item->get_product();
    $post_type = !empty($item['item_meta']['_st_st_booking_post_type']) ? $item['item_meta']['_st_st_booking_post_type'] : false;
    $transfer_title = $item['item_meta']['_st_title_cart'];
    if ($post_type == "car_transfer") {
        // On front end orders
        if (is_wc_endpoint_url()) {
            $item_name = '<a href="#" class="order_item">' . __("Transfer: ", "woocommerce") . $transfer_title . '</a>';
        }
        // On email notifications
        else {
            $item_name = $transfer_title;
        }
    }
    return $item_name;
}



//Custom Woo Checkout field by condition
add_filter('woocommerce_checkout_fields', 'conditional_checkout_fields_products');

function conditional_checkout_fields_products($fields)
{

    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
//    $item_data = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );
//    foreach ( $item_data as $data ) {
    if ( ! WC()->cart ) {
        return $fields;
    }
    foreach (WC()->cart->get_cart() as $cart_item) {
//        $st_booking_data = $item_data['st_booking_data'];
       $st_booking_data = $cart_item['st_booking_data'] ?? [];

        //tour details
        if (isset($st_booking_data['st_booking_post_type']) && $st_booking_data['st_booking_post_type'] == 'st_tours') {
            $fields['billing']['billing_tour_pickup'] = array(
                'id' => 'billing_tour_pickup',
                'label' => __('Hotel to pick-up', 'woocommerce'),
                'placeholder' => _x('Hotel to pick-up in Khao Lak area.', 'placeholder', 'woocommerce'),
                'required' => true,
                'class' => array('tour-hotel-pickup', 'hotel-field', 'auto-complete'),
                'priority' => 600
            );
            $fields['billing']['billing_tour_pickup_roomno'] = array(
                'label' => __('Room', 'woocommerce'),
                'placeholder' => _x('NO', 'placeholder', 'woocommerce'),
                //'required' => true,
		'default' => 'TBA',
                'class' => array('tour-hotel-pickup', 'room-field', 'auto-complete'),
                'priority' => 601
            );
            $fields['billing']['billing_tour_additional'] = array(
                'label' => __('Additional Information', 'woocommerce'),
                'type' => 'textarea',
                'placeholder' => _x('What else would you like us to know?', 'placeholder', 'woocommerce'),
                'class' => array('form-row-wide'),
                'priority' => 601
            );
            // if(isset($st_booking_data['guest_name']) && !empty($st_booking_data['guest_name'])){
            //     foreach($st_booking_data['guest_name'] as $guest => $val){
            //         $guest_num = (int) $guest+1;
            //         echo $guest_num . " ". $val ." </br> " ;
            //     }
            // }
        }
        if ($st_booking_data['st_booking_post_type'] == 'car_transfer') {

            $pickup_placeholder = 'Hotel/Place to pickup at ' . $st_booking_data['pick_up'];
            $dropoff_placeholder = 'Hotel/Place to dropoff at ' . $st_booking_data['drop_off'];
            $value_pickup = WC()->checkout->get_value('billing_pickup_place');
            $value_dropoff = WC()->checkout->get_value('billing_dropoff_place');
            $pickup_place = $st_booking_data['pick_up'];
            $dropoff_place = $st_booking_data['drop_off'];

            //transfer pax
            $fields['billing']['billing_adult'] = array(
                'label' => __('Adult', 'woocommerce'),
                'type' => 'number',
                'placeholder' => _x('0', 'placeholder', 'woocommerce'),
                'required' => true,
                'class' => array('transfer-number'),
                'priority' => 661
            );
            $fields['billing']['billing_child'] = array(
                'label' => __('Child', 'woocommerce'),
                'type' => 'number',
                'placeholder' => _x('0', 'placeholder', 'woocommerce'),
                'required' => false,
                'class' => array('transfer-number'),
                'priority' => 662
            );
            $fields['billing']['billing_infant'] = array(
                'label' => __('Infant', 'woocommerce'),
                'type' => 'number',
                'placeholder' => _x('0', 'placeholder', 'woocommerce'),
                'required' => false,
                'class' => array('transfer-number'),
                'priority' => 663
            );
            $fields['billing']['billing_luggage'] = array(
                'label' => __('Luggage', 'woocommerce'),
                'type' => 'number',
                'placeholder' => _x('0', 'placeholder', 'woocommerce'),
                'required' => false,
                'class' => array('transfer-number'),
                'priority' => 664
            );

            //arrival details
            $fields['billing']['billing_arr_pickup_place'] = array(
                'id' => 'billing_arr_pickup_place',
                'label' => __('Pick up Place', 'woocommerce'),
                'placeholder' => _x($pickup_placeholder, 'placeholder', 'woocommerce'),
                'required' => true,
                'class' => array('form-row-wide', 'auto-complete'),
                'priority' => 667
            );

            $fields['billing']['billing_arr_dropoff_place'] = array(
                'id' => 'billing_arr_dropoff_place',
                'label' => __('Drop off Place', 'woocommerce'),
                'placeholder' => _x($dropoff_placeholder, 'placeholder', 'woocommerce'),
                'required' => true,
                'class' => array('form-row-wide', 'auto-complete'),
                'clear' => true,
                'priority' => 668,
                'default' => '',
            );

//	    $airports[] = ['airport', 'flughafen', 'aeroport', 'Airport', 'Flughafen', 'Aeroport'];
//            var_dump($pickup_place);
//	    foreach($airport in $airports){
        if (str_contains($pickup_place, 'airport') || str_contains($pickup_place, 'Airport') || str_contains($pickup_place, 'flughafen') || str_contains($pickup_place, 'Flughafen') || str_contains($pickup_place, 'Aeroport') || str_contains($pickup_place, 'aeroport') ) {
                $fields['billing']['billing_arr_flight'] = array(
                    'label' => __('Arrival Flight', 'woocommerce'),
                    'placeholder' => _x('TH747', 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-first'),
                    'clear' => true,
                    'priority' => 666
                );
                $fields['billing']['billing_arr_flight_time'] = array(
                    'label' => __('Arrival Flight Time', 'woocommerce'),
                    'placeholder' => _x('17:45', 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-last'),
                    'clear' => true,
                    'priority' => 666
                );
                $fields['billing']['billing_arr_rep'] = array(
                    'label' => __('Would you like us to send our Representative to visit at your hotel and give some useful tips about the surrounding, activities, restaurants, taxi prices and excursions. (Free of charge)', 'woocommerce'),
                    'class' => array('form-row-wide', 'checkout-rep'),
                    'required' => true,
                    'type' => 'radio',
                    'options' => array(
                        'no' => __('No Thanks', 'woocommerce'),
                        'yes' => __('Yes, Please send your representative to my hotel', 'woocommerce'),
                    ),
                    'priority' => 669,
                    'default' => 'no',
                );
                unset($fields['billing']['billing_arr_pickup_place']);
            }
//	    }

            if (str_contains($dropoff_place, 'airport') || str_contains($dropoff_place, 'Airport') || str_contains($dropoff_place, 'flughafen') || str_contains($dropoff_place, 'Flughafen') || str_contains($dropoff_place, 'Aeroport') || str_contains($dropoff_place, 'aeroport') ) {
                $fields['billing']['billing_dep_flight'] = array(
                    'label' => __('Departure Flight', 'woocommerce'),
                    'placeholder' => _x('TH477', 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-first', 'auto-complete'),
                    'clear' => true,
                    'priority' => 701
                );
                $fields['billing']['billing_dep_flight_time'] = array(
                    'label' => __('Departure Flight Time', 'woocommerce'),
                    'placeholder' => _x('20:35', 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-last'),
                    'clear' => true,
                    'priority' => 702
                );
                unset($fields['billing']['billing_arr_dropoff_place']);
            }

            // roundtrip departure details
            if ($st_booking_data['roundtrip'] == 'yes') {
                $dep_pickup_placeholder = $dropoff_placeholder;
                $dep_dropoff_placeholder = $pickup_placeholder;
                $fields['billing']['billing_dep_pickup_place'] = array(
                    'id' => 'billing_dep_pickup_place',
                    'label' => __('Departure Pick up Place', 'woocommerce'),
                    'placeholder' => _x($dep_pickup_placeholder, 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-wide'),
                    'priority' => 703
                );
                $fields['billing']['billing_dep_dropoff_place'] = array(
                    'id' => 'billing_dep_dropoff_place',
                    'label' => __('Departure Drop off Place', 'woocommerce'),
                    'placeholder' => _x($dep_dropoff_placeholder, 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('form-row-wide'),
                    'clear' => true,
                    'priority' => 704
                );
                if (str_contains($pickup_place, 'airport') || str_contains($pickup_place, 'Airport') || str_contains($pickup_place, 'flughafen') || str_contains($pickup_place, 'Flughafen') || str_contains($pickup_place, 'Aeroport') || str_contains($pickup_place, 'aeroport') ) {
                    $fields['billing']['billing_dep_flight'] = array(
                        'label' => __('Departure Flight', 'woocommerce'),
                        'placeholder' => _x('TH477', 'placeholder', 'woocommerce'),
                        'required' => true,
                        'class' => array('form-row-first'),
                        'clear' => true,
                        'priority' => 701
                    );
                    $fields['billing']['billing_dep_flight_time'] = array(
                        'label' => __('Departure Flight Time', 'woocommerce'),
                        'placeholder' => _x('20:35', 'placeholder', 'woocommerce'),
                        'required' => true,
                        'class' => array('form-row-last'),
                        'clear' => true,
                        'priority' => 702
                    );
                    unset($fields['billing']['billing_dep_dropoff_place']);
                }
            }
            $fields['billing']['billing_transfer_note'] = array(
                'label' => __('Transfer Note:', 'woocommerce'),
                'type' => 'textarea',
                'placeholder' => _x('Please enter any additional information here', 'placeholder', 'woocommerce'),
                'required' => false,
                'class' => array('form-row-wide'),
                'clear' => true,
                'priority' => 705
            );
        }
    }
    return $fields;
}



/**
 * Display field value on the order edit page
 */

add_action('woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1);

function my_custom_checkout_field_display_admin_order_meta($order)
{
    echo '<h2><strong>Transfer Details:</strong</h2>';
    echo '<p><strong>' . __('Adult: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_adult', true) . '</p>';
    echo '<p><strong>' . __('Child: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_child', true) . '</p>';
    echo '<p><strong>' . __('Infant: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_infant', true) . '</p>';
    echo '<p><strong>' . __('Luggage: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_luggage', true) . '</p>';
    echo '<h2><strong>Arrival Details:</strong</h2>';
    echo '<p><strong>' . __('Arrival Flight') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_arr_flight', true) . '</p>';
    echo '<p><strong>' . __('Arrival Flight Time') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_arr_flight_time', true) . '</p>';
    echo '<p><strong>' . __('Representative') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_arr_rep', true) . '</p>';
    echo '<p><strong>' . __('Arrival Pickup place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_arr_pickup_place', true) . '</p>';
    echo '<p><strong>' . __('Arrival Dropoff place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_arr_dropoff_place', true) . '</p>';
    echo '<h2><strong>Departure Details:</strong</h2>';
    echo '<p><strong>' . __('Departure Flight') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_flight', true) . '</p>';
    echo '<p><strong>' . __('Departure Flight Time') . ':</strong> ' . get_post_meta($order->get_id(), '_wbilling_dep_flight_time', true) . '</p>';
    echo '<p><strong>' . __('Departure Pickup place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_pickup_place', true) . '</p>';
    echo '<p><strong>' . __('Departure Dropoff place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_dropoff_place', true) . '</p>';
    echo '<p><strong>' . __('Transfer Note') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_transfer_note', true) . '</p>';
    echo '<h2><strong>Tour Details:</strong</h2>';
    echo '<p><strong>' . __('Hotel to pick-up: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_pickup', true) . '</p>';
    echo '<p><strong>' . __('Room: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_pickup_roomno', true) . '</p>';
    echo '<p><strong>' . __('Additional Information: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_additional', true) . '</p>';

}



/** Insert text for Arr/Dep Details */
add_action('woocommerce_form_field', 'woo_custom_heading', 10, 2);
function woo_custom_heading($field, $key)
{
    if (is_checkout('checkout')):
        foreach (WC()->cart->get_cart() as $cart_item) {
            // $st_booking_data = $cart_item['st_booking_data'];
            // echo "<pre>";
            // var_dump($st_booking_data);
            // echo "</pre>";
            // will only execute if the field is $key = args and we are on the checkout page...
            if (is_checkout() && ($key === 'billing_adult')) {
                $field = '<h3 class="form-row form-row-wide" style="padding-top: 30px;">Transfer Details: </h3>' . $field;
            }
            if (is_checkout() && ($key === 'billing_arr_flight')) {
                $field = '<p class="form-row form-row-wide" style="font-size: 16px; font-weight: 800;">Arrival Details:</p>' . $field;
            }
            if (is_checkout() && ($key === 'billing_dep_flight')) {
                $field = '<p class="form-row form-row-wide" style="font-size: 16px; font-weight: 800;">Departure Details:</p>' . $field;
            }
            if (is_checkout() && ($key === 'billing_tour_pickup')) {
                $field = '<h3 class="form-row form-row-wide" style="padding-top: 30px;">Tour Details:</h3>' . $field;
            }
            if (is_checkout() && ($key === 'billing_arr_rep')){
                $field = '<p class="form-row form-row-wide" style="padding-top: 30px;">Don\'t miss this chance to explore Khao Lak and surrounding area on a whole new level! Request our representative to visiting at your hotel and get an information on a personalized tour of discovery.</p>' . $field;
            }
            return $field;
        }
    endif;
}

 //Change the 'Billing details' checkout label to 'Booking details'
 function wc_billing_field_strings($translated_text, $text, $domain)
 {
     switch ($translated_text) {
         case 'Billing details':
             $translated_text = __('Booking details', 'woocommerce');
             break;
     }
     return $translated_text;
 }
 add_filter('gettext', 'wc_billing_field_strings', 20, 3);

/**
 * Add a custom field (in an order) to the emails
 */
add_filter('woocommerce_email_order_meta_fields', 'custom_woocommerce_email_order_meta_fields', 10, 3);

function custom_woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order)
{
    if (!is_object($order)) {
        return $fields;
    }

    // Use HPOS-compatible $order->get_meta() instead of get_post_meta()
    $custom_fields = array(
        'billing_adult'             => __('Adult'),
        'billing_child'             => __('Child'),
        'billing_infant'            => __('Infant'),
        'billing_luggage'           => __('Luggage'),
        'billing_arr_flight'        => __('Arrival Flight'),
        'billing_arr_flight_time'   => __('Arrival Flight Time'),
        'billing_arr_pickup_place'  => __('Pick up place'),
        'billing_arr_dropoff_place' => __('Drop off place'),
        'billing_arr_rep'           => __('Representative'),
        'billing_dep_flight'        => __('Departure Flight'),
        'billing_dep_flight_time'   => __('Departure Flight Time'),
        'billing_dep_pickup_place'  => __('Departure Pick up place'),
        'billing_dep_dropoff_place' => __('Departure Drop off place'),
        'billing_transfer_note'     => __('Transfer Note'),
        'billing_tour_pickup'       => __('Tour Pickup'),
        'billing_tour_pickup_roomno'=> __('Room'),
        'billing_tour_additional'   => __('Additional Information'),
    );

    foreach ($custom_fields as $key => $label) {
        $value = $order->get_meta('_' . $key, true);
        if (!empty($value)) {
            $fields[$key] = array('label' => $label, 'value' => $value);
        }
    }

    return $fields;
}

// remove (optional) in checkout field

add_filter('woocommerce_form_field', 'elex_remove_checkout_optional_text', 10, 4);
function elex_remove_checkout_optional_text($field, $key, $args, $value)
{
    if (is_checkout() && !is_wc_endpoint_url()) {
        $optional = '<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
        $field = str_replace($optional, '', $field);
    }
    return $field;
}

// do_action('check_coupon_klld');

/* ============================================================
 * REVIEW PHOTO UPLOAD
 * Saves uploaded photo as WP attachment, stores URL in
 * comment meta key "comment_photo".
 * Also stores OTA source (gyg / viator / tripadvisor) for badge.
 * ============================================================ */

/**
 * Save review photo on comment post.
 * Hooked late (20) so the comment_ID is already available.
 */
add_action( 'comment_post', 'klld_save_review_photo', 20, 2 );
function klld_save_review_photo( $comment_id, $comment_approved ) {
    if ( empty( $_FILES['review_photo']['tmp_name'] ) ) return;

    $file = $_FILES['review_photo'];

    // 5 MB hard cap server-side
    if ( $file['size'] > 5 * 1024 * 1024 ) return;

    // Allow only images
    $allowed = [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ];
    if ( ! in_array( $file['type'], $allowed, true ) ) return;

    // Use WP media uploader
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Temporarily fake POST so media_handle_upload works outside admin
    $post_id = (int) get_comment( $comment_id )->comment_post_ID;

    // Override $_FILES key expected by media_handle_upload
    $_FILES['async-upload'] = $file;
    $attach_id = media_handle_upload( 'async-upload', $post_id );

    if ( ! is_wp_error( $attach_id ) ) {
        $url = wp_get_attachment_url( $attach_id );
        update_comment_meta( $comment_id, 'comment_photo', esc_url_raw( $url ) );
        update_comment_meta( $comment_id, 'comment_photo_id', $attach_id );
    }
}

/**
 * Add lazy loading + OTA badge to images in OTA-imported review text.
 * OTA reviews have meta: gyg_review_id / viator_review_id / tripadvisor_review_id.
 */
add_filter( 'get_comment_text', 'klld_ota_review_image_lazyload', 10, 2 );
function klld_ota_review_image_lazyload( $text, $comment ) {
    $comment_id = isset( $comment->comment_ID ) ? $comment->comment_ID : 0;
    if ( ! $comment_id ) return $text;

    // Only touch OTA reviews
    $is_ota = get_comment_meta( $comment_id, 'gyg_review_id', true )
           || get_comment_meta( $comment_id, 'viator_review_id', true )
           || get_comment_meta( $comment_id, 'tripadvisor_review_id', true )
           || get_comment_meta( $comment_id, 'gmb_review_id', true )
           || get_comment_meta( $comment_id, 'trustpilot_review_id', true );

    if ( ! $is_ota ) return $text;

    // Add loading="lazy" to any <img> tags that don't already have it
    $text = preg_replace_callback(
        '/<img(?![^>]*loading=)[^>]+>/i',
        function( $matches ) {
            return str_replace( '<img', '<img loading="lazy"', $matches[0] );
        },
        $text
    );

    return $text;
}


/**
 * Ensure 'st_reviews' are always sorted by date (newest first).
 */
add_filter( 'comments_template_query_args', 'klld_sort_reviews_by_date_desc' );
function klld_sort_reviews_by_date_desc( $comment_args ) {
    $comment_args['orderby'] = 'comment_date_gmt';
    $comment_args['order']   = 'DESC';
    return $comment_args;
}

/**
 * Optimize Google Maps script loading with async and defer
 */
add_filter('script_loader_tag', 'klld_add_async_defer_attribute', 10, 2);
function klld_add_async_defer_attribute($tag, $handle) {
    if ('gmap-apiv3' !== $handle) {
        return $tag;
    }
    return str_replace(' src', ' async defer src', $tag);
}

/**
 * AJAX Handler for Load More Reviews
 */
add_action('wp_ajax_klld_load_more_reviews', 'klld_load_more_reviews');
add_action('wp_ajax_nopriv_klld_load_more_reviews', 'klld_load_more_reviews');
function klld_load_more_reviews() {
    $paged = (int)($_POST['paged'] ?? 1);
    $post_id = (int)($_POST['post_id'] ?? 0);
    $source = sanitize_text_field($_POST['source'] ?? 'all');
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');
    $comment_per_page = (int)get_option('comments_per_page', 10);
    $offset = ($paged - 1) * $comment_per_page;

    $args = [
        'number'  => $comment_per_page,
        'offset'  => $offset,
        'post_id' => $post_id,
        'status'  => ['approve'],
        'orderby' => 'comment_date',
        'order'   => 'DESC',
        'parent'  => 0,
        'search'  => $keyword
    ];

    if ($source !== 'all') {
        if ($source === 'local') {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => 'ota_source',
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => 'ota_source',
                    'value' => '',
                    'compare' => '='
                ]
            ];
        } elseif ($source === 'TA') {
            $args['meta_query'] = [['key' => 'ota_source', 'value' => ['TA', 'tripadvisor'], 'compare' => 'IN']];
        } elseif ($source === 'vt') {
            $args['meta_query'] = [['key' => 'ota_source', 'value' => ['vt', 'viator'], 'compare' => 'IN']];
        } elseif ($source === 'gmb') {
            $args['meta_query'] = [['key' => 'ota_source', 'value' => ['gmb', 'google'], 'compare' => 'IN']];
        } elseif ($source === 'gyg') {
            $args['meta_query'] = [['key' => 'ota_source', 'value' => ['gyg', 'getyourguide'], 'compare' => 'IN']];
        } else {
            $args['meta_query'] = [
                [
                    'key' => 'ota_source',
                    'value' => $source,
                    'compare' => '='
                ]
            ];
        }
    }

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    $html = '';
    if ($comments) {
        foreach ($comments as $comment) {
            $html .= stt_elementorv2()->loadView('services/common/single/review-list', ['comment' => (object)$comment, 'post_type' => 'st_tours']);
        }
    }

    wp_send_json_success(['html' => $html]);
}

/**
 * Ensure images in reviews are lazy-loaded
 */
/**
 * Replace smile icons with stars in review form
 */
add_filter('st_tours_wp_review_form_args', 'klld_replace_smiles_with_stars', 999);
function klld_replace_smiles_with_stars($args) {
    if (isset($args['comment_field'])) {
        $args['comment_field'] = str_replace('fa-smile-o', 'fa-star grey', $args['comment_field']);
    }
    return $args;
}

add_filter('wp_get_attachment_image_attributes', function($atts, $attachment, $size) {
    $atts['loading'] = 'lazy';
    return $atts;
}, 10, 3);
