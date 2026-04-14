<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.0
 *
 * Class STGatewayPaypal
 *
 * Created by ShineTheme
 *
 */

use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\PaymentExecution;

if (!class_exists('STGatewayPaypal') and class_exists('STPaypal')) {
    class STGatewayPaypal extends STAbstactPaymentGateway
    {
        public static $_ints;
        private $_gateway_id = 'st_paypal';
        private $apiContext;

        function __construct()
        {
            add_filter('st_payment_gateway_st_paypal_name', array($this, 'get_name'));
        }


        /**
         * @update 1.2.0
         * @return bool
         */
        function _pre_checkout_validate()
        {
            return true;

        }

        function html()
        {
            echo Traveler_Paypal_New_Payment::get_inst()->loadTemplate('paypal');
        }

        private function setDefaultParams()
        {
            $clientId = st()->get_option('paypal_client_id');
            $clientSecret = st()->get_option('paypal_client_secret');

            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $clientId,
                    $clientSecret
                )
            );
            $testMode = st()->get_option('paypal_enable_sandbox', 'off');
            $apiContext->setConfig(
                array(
                    'mode' => ($testMode == 'on') ? 'sandbox' : 'live',
                    'log.LogEnabled' => false
                )
            );

            $this->apiContext = $apiContext;
        }


        function do_checkout($order_id)
        {
            $this->setDefaultParams();

            $total = get_post_meta($order_id, 'total_price', true);

            $currency = TravelHelper::get_current_currency();
			$total = $total * $currency['rate'];

            $booking_currency_conversion = st()->get_option('booking_currency_conversion');
            if ($booking_currency_conversion == 'on') {
				$main_currency = st()->get_option( 'booking_primary_currency' );
                $_currency = TravelHelper::find_currency($main_currency);
                if (!empty($_currency)) {
                    $total = $total / $currency['rate'];
                    $currency = $_currency;
                }
            }

            $total = round((float)$total, 2);

            $params = [
                'items' => [
                    'name' => get_the_title($order_id),
                    'currency' => $currency['name'],
                    'quantity' => 1,
                    'itemNumber' => $order_id,
                    'price' => $total
                ],
                'currency' => $currency['name'],
                'total' => $total,
                'description' => sprintf(esc_html__('Booking ID: %s', 'traveler-paypal'), $order_id),
                'returnUrl' => $this->get_return_url($order_id),
                'cancelUrl' => $this->get_cancel_url($order_id),
                'invoice' => uniqid()
            ];


            $payer = new Payer();
            $payer->setPaymentMethod("paypal");

            $item = new Item();
            $item->setName($params['items']['name'])
                ->setCurrency($params['items']['currency'])
                ->setQuantity($params['items']['quantity'])
                ->setSku($params['items']['itemNumber'])
                ->setPrice($params['items']['price']);
            $itemList = new ItemList();
            $itemList->setItems([$item]);

            $amount = new Amount();
            $amount->setCurrency($params['currency'])->setTotal($params['total']);

            $transaction = new Transaction();
            $transaction->setAmount($amount)->setItemList($itemList)->setDescription($params['description'])->setInvoiceNumber($params['invoice']);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($params['returnUrl'])->setCancelUrl($params['cancelUrl']);

            $payment = new Payment();
            $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array($transaction));

            do_action('st_before_redirect_paypal', $params);

            try {
                $payment->create($this->apiContext);
                $approvalUrl = $payment->getApprovalLink();
                return array(
                    'status' => true,
                    'redirect' => $approvalUrl
                );
            } catch (Exception $ex) {
                return [
                    'status' => false,
                    'message' => sprintf(esc_html__('Have error when processing: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                ];
            }
        }

        public function package_do_checkout($order_id)
        {
            if (!class_exists('STAdminPackages')) {
                return ['status' => TravelHelper::st_encrypt($order_id . 'st0'), 'message' => __('This function is off', 'traveler-paypal')];
            }
            $order = STAdminPackages::get_inst()->get('*', $order_id);

            $this->setDefaultParams();

            $currency = TravelHelper::get_current_currency();

            $total = (float)$order->package_price;
			$total = $total * $currency['rate'];

            $booking_currency_conversion = st()->get_option('booking_currency_conversion');
            if ($booking_currency_conversion == 'on') {
				$main_currency = st()->get_option( 'booking_primary_currency' );
                $_currency = TravelHelper::find_currency($main_currency);
                if (!empty($_currency)) {
                    $total = $total / $currency['rate'];
                    $currency = $_currency;
                }
            }

            $total = round((float)$total, 2);

            $params = [
                'items' => [
                    'name' => __('Member Package', 'traveler-paypal'),
                    'currency' => $currency['name'],
                    'quantity' => 1,
                    'itemNumber' => $order_id,
                    'price' => $total
                ],
                'currency' => $currency['name'],
                'total' => $total,
                'description' => sprintf(esc_html__('MemberShip ID: %s', 'traveler-paypal'), $order_id),
                'returnUrl' => STAdminPackages::get_inst()->get_return_url($order_id),
                'cancelUrl' => STAdminPackages::get_inst()->get_cancel_url($order_id),
                'invoice' => uniqid()
            ];

            $payer = new Payer();
            $payer->setPaymentMethod("paypal");

            $item = new Item();
            $item->setName($params['items']['name'])
                ->setCurrency($params['items']['currency'])
                ->setQuantity($params['items']['quantity'])
                ->setSku($params['items']['itemNumber'])
                ->setPrice($params['items']['price']);
            $itemList = new ItemList();
            $itemList->setItems([$item]);

            $amount = new Amount();
            $amount->setCurrency($params['currency'])->setTotal($params['total']);

            $transaction = new Transaction();
            $transaction->setAmount($amount)->setItemList($itemList)->setDescription($params['description'])->setInvoiceNumber($params['invoice']);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($params['returnUrl'])->setCancelUrl($params['cancelUrl']);

            $payment = new Payment();
            $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array($transaction));

            try {
                $payment->create($this->apiContext);
                $approvalUrl = $payment->getApprovalLink();
                return array(
                    'status' => TravelHelper::st_encrypt($order_id . 'st1'),
                    'redirect_url' => $approvalUrl
                );
            } catch (Exception $ex) {
                return [
                    'status' => TravelHelper::st_encrypt($order_id . 'st0'),
                    'message' => sprintf(esc_html__('Have error when processing: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                ];
            }
        }

        function check_complete_purchase($order_id)
        {
            if (isset($_GET['PayerID']) && isset($_GET['paymentId'])) {
                $this->setDefaultParams();
                $total = get_post_meta($order_id, 'total_price', true);

                $currency = TravelHelper::get_current_currency();
				$total = $total * $currency['rate'];

                $booking_currency_conversion = st()->get_option('booking_currency_conversion');
                if ($booking_currency_conversion == 'on') {
					$main_currency = st()->get_option( 'booking_primary_currency' );
                    $_currency = TravelHelper::find_currency($main_currency);
                    if (!empty($_currency)) {
                        $total = $total / $currency['rate'];
                        $currency = $_currency;
                    }
                }

                $total = round((float)$total, 2);

                $params = [
                    'currency' => $currency['name'],
                    'total' => $total,
                ];
                $paymentId = $_GET['paymentId'];

                $payment = Payment::get($paymentId, $this->apiContext);

                $execution = new PaymentExecution();
                $execution->setPayerId($_GET['PayerID']);

                $transaction = new Transaction();
                $amount = new Amount();

                $amount->setCurrency($params['currency']);
                $amount->setTotal($params['total']);
                $transaction->setAmount($amount);

                $execution->addTransaction($transaction);
                try {
                    $payment->execute($execution, $this->apiContext);
                    try {
                        $payment = Payment::get($paymentId, $this->apiContext);
						$this->update_available($order_id);
                        return [
                            'status' => true,
                            'message' => sprintf(esc_html__('Executed Payment. The Payment is: %s', 'traveler-paypal'), $payment->getId())
                        ];
                    } catch (Exception $ex) {
                        return [
                            'status' => false,
                            'message' => sprintf(esc_html__('Get the error: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                        ];
                    }
                } catch (Exception $ex) {
                    return [
                        'status' => false,
                        'message' => sprintf(esc_html__('Get the error: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => esc_html__('The Approval Cancelled', 'traveler-paypal')
                ];
            }
        }

		public function update_available($order_id) {
			$get_order = $this->st_get_order_by_order_item_id( $order_id );
			if ( empty( $get_order ) ) {
				return;
			}
			global $wpdb;
			$post_type = $get_order['st_booking_post_type'];
			$check_in_timestamp = $get_order['check_in_timestamp'];
			$check_out_timestamp = $get_order['check_out_timestamp'];
			$post_id = $get_order['st_booking_id'];
			$booked = 1;

			switch ( $post_type ) {
				case 'st_tours':
				case 'st_activity':
					$table_avai = $wpdb->prefix . 'st_tour_availability';
					if ($post_type == 'st_activity') {
						$table_avai = $wpdb->prefix . 'st_activity_availability';
					}
					$adult_number = $get_order['adult_number'];
					$child_number = $get_order['child_number'];
					$infant_number = $get_order['infant_number'];
					$number_had_boooked = $adult_number + $child_number + $infant_number;
					$sql = $wpdb->prepare( "UPDATE {$table_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in = %s", $number_had_boooked, $post_id, $check_in_timestamp );
					$wpdb->query( $sql );
					break;
				case 'st_rental':
					$table_st_rental_avai = $wpdb->prefix . 'st_rental_availability';
					$sql = $wpdb->prepare( "UPDATE {$table_st_rental_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in >= %d AND check_out <= %d", $booked, $post_id, $check_in_timestamp , $check_out_timestamp );
					$wpdb->query( $sql );
					break;
				case 'st_hotel':
				case 'hotel_room':
					$table_st_room_avai = $wpdb->prefix . 'st_room_availability';
					$sql = $wpdb->prepare( "UPDATE {$table_st_room_avai} SET number_booked = IFNULL(number_booked, 0) + %d WHERE post_id = %d AND check_in >= %d AND check_out <= %d", $booked, $post_id, $check_in_timestamp , $check_out_timestamp );
					$wpdb->query( $sql );
					break;
			}
		}
		public function st_get_order_by_order_item_id( $order_item_id ) {
			global $wpdb;
			$querystr  = 'SELECT * FROM  ' . $wpdb->prefix . 'st_order_item_meta WHERE 1=1 AND order_item_id = ' . $order_item_id;
			$pageposts = $wpdb->get_row( $querystr, ARRAY_A );
			return $pageposts;
		}

        function package_completed_checkout($order_id)
        {
            if (!class_exists('STAdminPackages')) {
                return ['status' => false, 'message' => __('This function is off', 'traveler-paypal')];
            }
            if (isset($_GET['PayerID']) && isset($_GET['paymentId'])) {
                $this->setDefaultParams();

                $order = STAdminPackages::get_inst()->get('*', $order_id);

                $currency = TravelHelper::get_current_currency();

                $total = (float)$order->package_price;
				$total = $total * $currency['rate'];

                $booking_currency_conversion = st()->get_option('booking_currency_conversion');
                if ($booking_currency_conversion == 'on') {
					$main_currency = st()->get_option( 'booking_primary_currency' );
                    $_currency = TravelHelper::find_currency($main_currency);
                    if (!empty($_currency)) {
                        $total = $total / $currency['rate'];
                        $currency = $_currency;
                    }
                }

                $total = round((float)$total, 2);

                $params = [
                    'currency' => $currency['name'],
                    'total' => $total,
                ];
                $paymentId = $_GET['paymentId'];

                $payment = Payment::get($paymentId, $this->apiContext);

                $execution = new PaymentExecution();
                $execution->setPayerId($_GET['PayerID']);

                $transaction = new Transaction();
                $amount = new Amount();

                $amount->setCurrency($params['currency']);
                $amount->setTotal($params['total']);
                $transaction->setAmount($amount);

                $execution->addTransaction($transaction);
                try {
                    $payment->execute($execution, $this->apiContext);
                    try {
                        $payment = Payment::get($paymentId, $this->apiContext);
                        return [
                            'status' => true,
                            'message' => sprintf(esc_html__('Executed Payment. The Payment is: %s', 'traveler-paypal'), $payment->getId())
                        ];
                    } catch (Exception $ex) {
                        return [
                            'status' => false,
                            'message' => sprintf(esc_html__('Get the error: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                        ];
                    }
                } catch (Exception $ex) {
                    return [
                        'status' => false,
                        'message' => sprintf(esc_html__('Get the error: Code %s - Message %s', 'traveler-paypal'), $ex->getCode(), $ex->getMessage())
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => esc_html__('The Approval Cancelled', 'traveler-paypal')
                ];
            }
        }

        function get_name()
        {
            return __('Paypal', 'traveler-paypal');
        }

        /**
         *
         * Check payment method for all items or specific is enable
         *
         * @update 1.1.7
         * @param bool $item_id
         * @return bool
         */
        function is_available($item_id = false)
        {
            $result = false;

            if (st()->get_option('pm_gway_st_paypal_enable') == 'on') {
                $result = true;
            }
            if ($item_id) {
                $meta = get_post_meta($item_id, 'is_meta_payment_gateway_st_paypal', true);
                if ($meta == 'off') {
                    $result = false;
                }
            }


            return $result;
        }

        function get_option_fields()
        {
            return array(
                array(
                    'id' => 'paypal_enable_sandbox',
                    'label' => __('Paypal Enable Sandbox', 'traveler-paypal'),
                    'type' => 'on-off',
                    'section' => 'option_pmgateway',
                    'std' => 'on',
                    'desc' => __('Allow you to enable sandbox mod for testing', 'traveler-paypal'),
                    'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
                ),
                array(
                    'id' => 'paypal_client_id',
                    'label' => __('Paypal Client ID', 'traveler-paypal'),
                    'type' => 'text',
                    'section' => 'option_pmgateway',
                    'desc' => __('Paypal Client ID', 'traveler-paypal'),
                    'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
                ),
                array(
                    'id' => 'paypal_client_secret',
                    'label' => __('Paypal Client Secret', 'traveler-paypal'),
                    'type' => 'text',
                    'section' => 'option_pmgateway',
                    'desc' => __('Paypal Client Secret', 'traveler-paypal'),
                    'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
				),
				[
                    'id' => 'booking_currency_conversion',
                    'label' => __('Currency conversion', ST_TEXTDOMAIN),
                    'desc' => __('If ON, the system converts any currency into your primary currency during checkout with PayPal (the primary currency must be supported by PayPal). If OFF, the system will proceed with the currency at the time of checkout.', ST_TEXTDOMAIN),
                    'type' => 'on-off',
                    'std' => 'off',
                    'section' => 'option_pmgateway',
					'condition' => 'pm_gway_' . $this->_gateway_id . '_enable:is(on)'
                ],
            );
        }

        function get_default_status()
        {
            return true;
        }

        function is_check_complete_required()
        {
            return true;
        }

        function get_logo()
        {
            return Traveler_Paypal_New_Payment::get_inst()->pluginUrl . 'assets/img/pp-logo.png';
        }

        function getGatewayId()
        {
            return $this->_gateway_id;
        }

        static function instance()
        {
            if (!self::$_ints) {
                self::$_ints = new self();
            }

            return self::$_ints;
        }

        static function add_payment($payment)
        {
            $payment['st_paypal'] = self::instance();

            return $payment;
        }
    }

    add_filter('st_payment_gateways', array('STGatewayPaypal', 'add_payment'));
}

