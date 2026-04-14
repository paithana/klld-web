<?php

!class_exists('\LiteSpeed\Cloud') || \LiteSpeed\Cloud::save_summary(['disabled_node' => [], 'err_domains' => []]);

//Wildlife Tour Change Ages
// function custom_update_post_meta_on_load($post_id) {
	
//      // Check if it's in the list of target post IDs
//      $target_post_ids = [14583, 17489, 17490];
//      if (in_array($post_id, $target_post_ids)) {
//  		// echo "<pre>".var_dump($st_post_meta)."</pre>";
//          // Example: change or update custom field 'my_custom_key' to new value
//         update_post_meta($post_id, 'tour_guest_adult', 'Ages 13+');
//  		update_post_meta($post_id, 'tour_guest_child', 'Ages 8-12');
//      }
// }

// add_filter( ‘xmlrpc_enabled’, ‘__return_false’ );

// Hook into a good time (admin only or frontend? Adjust as needed)

// Option 1: Update when post is viewed on frontend
// add_action('wp', function() {
//     if (is_singular()) {
//         global $post;
//         if ($post) {
//             custom_update_post_meta_on_load($post->ID);
//         }
//     }
// });

// Option 2: Update during save in admin (if you want it to trigger on saving post in backend)
// add_action('save_post', 'custom_update_post_meta_on_load');

/**
* Filter to change Separator %sep%.
**/

add_filter( 'rank_math/settings/title_separator', function( $sep ) {
	return $sep;
});


/**
* Google Map API
**/
use Google\Api\Billing;

/**
 * Apply a coupon for minimum cart total
 **/

// function log_post_meta($post_id) {
// // Get all post meta
// if(is_admin()){

//    $post_meta = get_post_meta($post_id);

//    // Loop through each post meta key and value
//    foreach ($post_meta as $key => $value) {
//        // Log the key and its value to the console
//        error_log("Post Meta Key: ".$key, "Value:". $value);
//    }
// }

// }

// add_action('wp_head', function() {
//        $post_id = get_the_ID();
//        log_post_meta($post_id);
// 	}
// }

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
add_filter('action_scheduler_run_queue', function ($arg) {
    return 86400;
});

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
//Defer JS
// Defer Javascripts Speed up loading for external js files wait till page loads
// Defer jQuery Parsing using the HTML5 defer property
////if (!(is_admin() )) {
////  function defer_parsing_of_js ( $url ) {
////    if ( FALSE === strpos( $url, '.js' ) ) return $url;
////    if ( strpos( $url, 'jquery.js' ) || strpos( $url, 'jquery.c.js' ) || strpos( $url, 'wpfront-notification-bar.js' ) ) return $url;
////    // return "$url' defer ";
////    return "$url' defer onload='";
////}
////  add_filter( 'clean_url', 'defer_parsing_of_js', 11, 1 );
//}


// Cartransfer.js
add_action('wp_enqueue_scripts', 'custom_transfer_js', 100);

function custom_transfer_js()
{
    wp_dequeue_script('filter-transfer');
    wp_deregister_script('filter-transfer');

    // Enqueue replacement child theme script
    wp_enqueue_script('filter-transfer', get_stylesheet_directory_uri() . '/v3/js/cartransfer.js', array('jquery'));
    //    wp_register_style('select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/select2.css', false, '1.0', 'all');
//    wp_register_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js', array('jquery'), '1.0', true);
//    wp_enqueue_style('select2css');
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

    if (is_checkout()) {
        //remove unused field
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_postcode']);
        //    $item_data = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );
        //    foreach ( $item_data as $data ) {
        // global $woocommerce;
        // $cart_items = $woocommerce->cart->get_cart();
        // foreach($cart_items as $item => $values) { st_st_booking_d
        foreach (WC()->cart->get_cart() as $cart_item) {
            $st_booking_data = isset($cart_item['st_booking_data']) ? $cart_item['st_booking_data'] : array();
            //    echo var_dump($cart_item);

            //    $st_booking_data = $item['st_booking_data'];
            //    echo "<pre>";
            //    echo "". var_dump($st_booking_data) ."";
            //    echo "</pre>";





            //tour details
            $post_type = isset($st_booking_data['st_booking_post_type']) ? $st_booking_data['st_booking_post_type'] : '';
            if ($post_type == 'st_tours') {
                $fields['billing']['billing_tour_pickup'] = array(
                    'id' => 'billing_tour_pickup',
                    'label' => __('Pick-up Place', 'woocommerce'),
                    'placeholder' => _x('Your Hotel to pick-up in Khao Lak area.', 'placeholder', 'woocommerce'),
                    'required' => true,
                    'class' => array('tour-hotel-pickup', 'hotel-field', 'auto-complete'),
                    'priority' => 600
                );
                $fields['billing']['billing_tour_pickup_roomno'] = array(
                    'label' => __('Room No.', 'woocommerce'),
                    'placeholder' => _x('TBA', 'placeholder', 'woocommerce'),
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
                $fields['billing']['billing_tour_availability'] = array(
                    'id' => 'billing_tour_availability',
                    'label' => __('<strong>Thank you for your interest in Khao Lak Land Discovery.</strong></br> We kindly ask you to contact us via WhatsApp or the "contact form" before booking a tour to confirm availability.  </br>In the event that you proceed to book a tour without checking availability and the tour is unavailable, we will contact you to discuss alternate dates. If no dates that suit your travel itinerary are available, all of "Your Payment will be refunded (in condition).</br> Thank you.', 'woocommerce'),
                    'required' => true,
		    'class' => 'form-row-wide',
                    'type' => 'checkbox',
                    'options' => array(
                        'yes' => __('I have checked the availability of the tour with Khao Lak Land Discovery', 'woocommerce'),
                        'WhatsApp' => __('<a href="" target="_blank">Contact us via WhatsApp</a>', 'woocommerce'),
                    ),
                    'priority' => 706
                );
            }
            if ($post_type == 'car_transfer') {

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
                if (str_contains($pickup_place, 'airport') || str_contains($pickup_place, 'Airport') || str_contains($pickup_place, 'flughafen') || str_contains($pickup_place, 'Flughafen') || str_contains($pickup_place, 'Aeroport') || str_contains($pickup_place, 'aeroport')) {
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

                if (str_contains($dropoff_place, 'airport') || str_contains($dropoff_place, 'Airport') || str_contains($dropoff_place, 'flughafen') || str_contains($dropoff_place, 'Flughafen') || str_contains($dropoff_place, 'Aeroport') || str_contains($dropoff_place, 'aeroport')) {
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
                    if (str_contains($pickup_place, 'airport') || str_contains($pickup_place, 'Airport') || str_contains($pickup_place, 'flughafen') || str_contains($pickup_place, 'Flughafen') || str_contains($pickup_place, 'Aeroport') || str_contains($pickup_place, 'aeroport')) {
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
}

add_action('woocommerce_admin_order_data_after_billing_address', 'admin_order_item_values', 10, 3);
function admin_order_item_values($order)
{
    // get the post meta value from the associated product
    $meta_data = get_post_meta($order->id);
    //    echo "<pre>";
//    echo var_dump($);
//    echo "</pre>";
    foreach ($meta_data as $meta => $key) {
        // echo '<tr>' . $meta . '</tr>';
        foreach ($key as $k) {
            echo '<p class="form-field ' . $meta . '">';
            echo '<label for="' . $meta . '">' . $meta . '</label>';
            echo '<input type="text" class="short" style="" name="' . $meta . '" id="' . $meta . '" value="' . esc_html($k) . '" placeholder=""> </p>';
        }
    }
}



/**
 * Display field value on the order edit page
 */

// add_action('woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1);

// function my_custom_checkout_field_display_admin_order_meta($order)
// {
//     echo '<h3><tr>Transfer Details:</tr></h3>';
//     echo '<td><p>' . __('Adult: ') .  get_post_meta($order->get_id(), '_billing_adult', true) . '</p></td>';
//     echo '<td><p>' . __('Child: ') .  get_post_meta($order->get_id(), '_billing_child', true) . '</p></td>';
//     echo '<td><p>' . __('Infant: ') .  get_post_meta($order->get_id(), '_billing_infant', true) . '</p></td>';
//     echo '<td><p>' . __('Luggage: ') .  get_post_meta($order->get_id(), '_billing_luggage', true) . '</p></td>';
//     echo '<h3><tr>Arrival Details:</tr></h3>';
//     echo '<td>' . __('Arrival Flight') . get_post_meta($order->get_id(), '_billing_arr_flight', true) . '</td>';
//     echo '<td>' . __('Arrival Flight Time') . get_post_meta($order->get_id(), '_billing_arr_flight_time', true) . '</td>';
//     echo '<td>' . __('Representative') . get_post_meta($order->get_id(), '_billing_arr_rep', true) . '</td>';
//     echo '<td>' . __('Arrival Pickup place') . get_post_meta($order->get_id(), '_billing_arr_pickup_place', true) . '</td>';
//     echo '<td>' . __('Arrival Dropoff place') . get_post_meta($order->get_id(), '_billing_arr_dropoff_place', true) . '</td>';
//     echo '<h3><tr>Departure Details:</tr></h3>';
//     echo '<th>' . __('Departure Flight') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_flight', true) . '</th>';
//     echo '<th>' . __('Departure Flight Time') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_flight_time', true) . '</th>';
//     echo '<th>' . __('Departure Pickup place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_pickup_place', true) . '</th>';
//     echo '<th>' . __('Departure Dropoff place') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dep_dropoff_place', true) . '</th>';
//     echo '<th>' . __('Transfer Note') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_transfer_note', true) . '</th>';
//     echo '<h3><tr>Tour Details:</tr></h3>';
//     echo '<th>' . __('Hotel to pick-up: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_pickup', true) . '</th>';
//     echo '<th>' . __('Room: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_pickup_roomno', true) . '</th>';
//     echo '<th>' . __('Additional Information: ') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_tour_additional', true) . '</th>';

// }



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
            if (is_checkout() && ($key === 'billing_arr_rep')) {
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
    if (!$sent_to_admin || !is_object($order)) {
        return $fields;
    } else {
        $fields['billing_arr_rep'] = array('label' => __('Representative:'), 'value' => get_post_meta($order->id, '_billing_arr_rep', true), );
        $fields['billing_transfer_note'] = array('label' => __('Transfer Note:'), 'value' => get_post_meta($order->id, '_billing_transfer_note', true), );
        $fields['billing_arr_flight'] = array('label' => __('Arrival Flight'), 'value' => get_post_meta($order->id, '_billing_arr_flight', true), );
        $fields['billing_arr_flight_time'] = array('label' => __('Arrival Flight Time'), 'value' => get_post_meta($order->id, '_billing_arr_flight_time', true), );
        $fields['billing_arr_pickup_place'] = array('label' => __('Pick up place'), 'value' => get_post_meta($order->id, '_billing_arr_pickup_place', true), );
        $fields['billing_arr_dropoff_place'] = array('label' => __('Arrival Pick up place'), 'value' => get_post_meta($order->id, '_billing_arr_dropoff_place', true), );
        $fields['billing_dep_flight'] = array('label' => __('Departure Flight'), 'value' => get_post_meta($order->id, '_billing_dep_flight', true), );
        $fields['billing_dep_flight_time'] = array('label' => __('Departure Flight Time'), 'value' => get_post_meta($order->id, '_billing_dep_flight_time', true), );
        $fields['billing_dep_pickup_place'] = array('label' => __('Departure Pick up place'), 'value' => get_post_meta($order->id, '_billing_dep_pickup_place', true), );
        $fields['billing_dep_dropoff_place'] = array('label' => __('Departure Drop off place'), 'value' => get_post_meta($order->id, '_billing_dep_dropoff_place', true), );
        $fields['billing_adult'] = array('label' => __('Adult: '), 'value' => get_post_meta($order->id, '_billing_adult', true), );
        $fields['billing_child'] = array('label' => __('Child: '), 'value' => get_post_meta($order->id, '_billing_child', true), );
        $fields['billing_infant'] = array('label' => __('Infant: '), 'value' => get_post_meta($order->id, '_billing_infant', true), );
        $fields['billing_luggage'] = array('label' => __('Luggage: '), 'value' => get_post_meta($order->id, '_billing_luggage', true), );
        $fields['billing_tour_pickup'] = array('label' => __('Tour Pickup: '), 'value' => get_post_meta($order->id, '_billing_tour_pickup', true), );
        $fields['billing_tour_pickup_roomno'] = array('label' => __('Room'), 'value' => get_post_meta($order->id, '_billing_tour_pickup_roomno', true), );
        $fields['billing_tour_additional'] = array('label' => __('Additional Information: '), 'value' => get_post_meta($order->id, '_billing_tour_additional', true), );
        return $fields;
    }
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


// add_filter('woocommerce_email_headers', 'new_order_reply_to_admin_header', 20, 3);
// function new_order_reply_to_admin_header($header, $email_id, $order)
// {

//     if ($email_id === 'new_order') {
//         $email = new WC_Email($email_id);

//         $header = "Content-Type: " . $email->get_content_type() . "\r\n";
//         $header .= 'Reply-to: ' . $order->get_billing_email() . "\r\n";
//     }
//     return $header;
// }
/*
// woocommerce new order email chooe email from custom post type
add_filter('woocommerce_email_from_address', 'custom_woocommerce_email_from_address', 10, 2);
function custom_woocommerce_email_from_address($email, $order)
{
    $trf_email = 'transfer@khaolaklanddiscovery.com';
    $tour_email = 'tour@khaolaklanddiscovery.com';
foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $post_type = get_post_type($product->get_id());
        if ($post_type === 'st_tours') {
            $email = $tour_email;
        }
        elseif ($post_type === 'car_transfer') {
            $email = $trf_email;
        }
        else {
            $email = get_option( 'admin_email' );
        }
    }
    return $email;
}*/
// Add a new column to the orders list
add_filter( 'woocommerce_admin_orders_list_head', 'add_custom_meta_column_header', 10, 1 );
function add_custom_meta_column_header( $columns ) {
    $columns['_payment_method_title'] = 'Payment'; // Replace with your desired label
    return $columns;
}

// Populate the new column with data
add_filter( 'woocommerce_admin_orders_list_row_data', 'add_custom_meta_column_data', 10, 1 );
function add_custom_meta_column_data( $data ) {
    global $post; // Assuming the order ID is available in $post
    $order_id = $post->ID;
    $custom_meta_value = get_post_meta( $order_id, '_payment_method_title', true ); // Replace with your meta key

    $data['_payment_method_title'] = $custom_meta_value;
    return $data;
}

/**
 * Display post featured image in WooCommerce order items
 * Uses the original post (tour/transfer) featured image instead of product image
 */
add_filter('woocommerce_order_item_thumbnail', 'custom_order_item_thumbnail', 10, 2);
function custom_order_item_thumbnail($image, $item) {
    // Get the booking post ID from item meta
    $post_id = !empty($item['item_meta']['_st_booking_id']) ? $item['item_meta']['_st_booking_id'] : false;
    
    // If no booking ID, try to get it from st_booking_post_id
    if (!$post_id) {
        $post_id = !empty($item['item_meta']['st_booking_post_id']) ? $item['item_meta']['st_booking_post_id'] : false;
    }
    
    // If still no post ID, try the product ID
    if (!$post_id) {
        $post_id = $item->get_product_id();
    }
    
    if ($post_id && has_post_thumbnail($post_id)) {
        $image = get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'order-item-thumbnail'));
    }
    
    return $image;
}

/**
 * Also display featured image in admin order items
 */
add_filter('woocommerce_admin_order_item_thumbnail', 'custom_admin_order_item_thumbnail', 10, 3);
function custom_admin_order_item_thumbnail($image, $item_id, $item) {
    // Get the booking post ID from item meta
    $post_id = wc_get_order_item_meta($item_id, '_st_booking_id', true);
    
    if (!$post_id) {
        $post_id = wc_get_order_item_meta($item_id, 'st_booking_post_id', true);
    }
    
    if (!$post_id && is_callable(array($item, 'get_product_id'))) {
        $post_id = $item->get_product_id();
    }
    
    if ($post_id && has_post_thumbnail($post_id)) {
        $image = get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'order-item-thumbnail', 'style' => 'width:50px;height:auto;'));
    }
    
    return $image;
}

/**
 * Add Guest Name to WooCommerce Admin Order Notification Email Subject
 */
add_filter('woocommerce_email_subject_new_order', 'custom_admin_email_subject_with_guest_name', 10, 2);
function custom_admin_email_subject_with_guest_name($subject, $order) {
    $guest_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $subject = sprintf('[%s] New Order (#%s) from %s', get_bloginfo('name'), $order->get_order_number(), $guest_name);
    return $subject;
}

/**
 * Add Guest Name and booking details to admin order email
 */
// add_action('woocommerce_email_order_details', 'add_guest_info_to_admin_email', 5, 4);
// function add_guest_info_to_admin_email($order, $sent_to_admin, $plain_text, $email) {
//     // Only add to admin emails
//     if (!$sent_to_admin) {
//         return;
//     }
    
//     $billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
//     $guest_email = $order->get_billing_email();
//     $guest_phone = $order->get_billing_phone();
    
//     // Get guest names and counts from order items
//     $guest_names = array();
//     $total_adults = 0;
//     $total_children = 0;
//     $total_infants = 0;
    
//     foreach ($order->get_items() as $item) {
//         // Get guest titles and names from item meta
//         $guest_titles = $item->get_meta('_st_guest_title');
//         $guest_name_list = $item->get_meta('_st_guest_name');
        
//         // Build guest names with titles
//         if (!empty($guest_name_list) && is_array($guest_name_list)) {
//             foreach ($guest_name_list as $index => $name) {
//                 $title = '';
//                 if (!empty($guest_titles) && is_array($guest_titles) && isset($guest_titles[$index])) {
//                     $title = strtoupper($guest_titles[$index]) . ' ';
//                 }
//                 if (!empty(trim($name))) {
//                     $guest_names[] = $title . ucwords(strtolower(trim($name)));
//                 }
//             }
//         }
        
//         // Get adult/child/infant counts
//         $adults = $item->get_meta('_st_adult_number');
//         $children = $item->get_meta('_st_child_number');
//         $infants = $item->get_meta('_st_infant_number');
        
//         $total_adults += !empty($adults) ? intval($adults) : 0;
//         $total_children += !empty($children) ? intval($children) : 0;
//         $total_infants += !empty($infants) ? intval($infants) : 0;
//     }
    
//     // Format guest names for display
//     $guest_names_display = !empty($guest_names) ? implode(', ', $guest_names) : $billing_name;
    
//     if ($plain_text) {
//         echo "\n========== GUEST INFORMATION ==========\n";
//         echo "Billing Name: " . $billing_name . "\n";
//         echo "Guest Name(s): " . $guest_names_display . "\n";
//         echo "Email: " . $guest_email . "\n";
//         echo "Phone: " . $guest_phone . "\n";
//         echo "Guests: " . $total_adults . " Adults, " . $total_children . " Children, " . $total_infants . " Infants\n";
//         echo "========================================\n\n";
//     } else {
//         echo '<div style="margin-bottom: 20px; padding: 15px; background-color: #f8f8f8; border-left: 4px solid #0073aa;">';
//         echo '<h2 style="margin: 0 0 10px 0; color: #0073aa;">Guest Information</h2>';
//         echo '<p style="margin: 5px 0;"><strong>Billing Name:</strong> ' . esc_html($billing_name) . '</p>';
//         echo '<p style="margin: 5px 0;"><strong>Guest Name(s):</strong> ' . esc_html($guest_names_display) . '</p>';
//         echo '<p style="margin: 5px 0;"><strong>Email:</strong> ' . esc_html($guest_email) . '</p>';
//         echo '<p style="margin: 5px 0;"><strong>Phone:</strong> ' . esc_html($guest_phone) . '</p>';
//         echo '<p style="margin: 5px 0;"><strong>Guests:</strong> ' . $total_adults . ' Adults, ' . $total_children . ' Children, ' . $total_infants . ' Infants</p>';
//         echo '</div>';
//     }
// }


