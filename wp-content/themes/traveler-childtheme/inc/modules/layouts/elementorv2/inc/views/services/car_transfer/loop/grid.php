<?php
$transfer = STCarTransfer::inst();
$pickup_date = STInput::get('pick-up-date', date(TravelHelper::getDateFormat()));
$dropoff_date = STInput::get('drop-off-date', date(TravelHelper::getDateFormat(), strtotime("+ 1 day")));

$pickup_date = TravelHelper::convertDateFormatNew($pickup_date);
$dropoff_date = TravelHelper::convertDateFormatNew($dropoff_date);

$pick_up_time = STInput::get('pick-up-time', '12:00 PM');
$drop_off_time = STInput::get('drop-off-time', '12:00 PM');

$transfer_from = (int)STInput::get( 'transfer_from' );
$transfer_to   = (int)STInput::get( 'transfer_to' );
$roundtrip     = STInput::get( 'roundtrip', '' );

$price_type = get_post_meta(get_the_ID(), 'price_type', true);
$pasenger = (int)get_post_meta(get_the_ID(), 'passengers', true);
$auto_transmission = get_post_meta(get_the_ID(), 'auto_transmission', true);
$baggage = (int)get_post_meta(get_the_ID(), 'baggage', true);
$door = (int)get_post_meta(get_the_ID(), 'door', true);
$number_pass = (int)get_post_meta(get_the_ID(), 'num_passenger', true);
$post_id = get_the_ID();
$post_translated = TravelHelper::post_translated($post_id);

$class_image = 'image-feature st-hover-grow';

// Modified: Allow display if we are in fallback mode (even if route is not fully selected)
$is_valid_route = !empty($transfer_from) && $transfer_from != 0 && !empty($transfer_to) && $transfer_to != 0;
?>

    <div class="services-item grid item-elementor" itemscope itemtype="https://schema.org/RentalCarReservation" data-format="<?php echo TravelHelper::getDateFormatMoment() ?>, hh:mm A" data-date-format="<?php echo TravelHelper::getDateFormatMoment() ?>" data-time-format="hh:mm A"
        data-timepicker="true">
        <form class="item service-border form-booking-car-transfer st-border-radius">
            <div class="featured-image">
                <div class="st-tag-feature-sale">
                    <?php
                    $is_featured = get_post_meta($post_translated, 'is_featured', true);
                    if ($is_featured == 'on') { 
                        $feature_text   = st()->get_option( 'st_text_featured', __( 'Featured', 'traveler' ) ); ?>
                        <div class="featured"><?php echo esc_html__($feature_text, 'traveler') ?></div>
                    <?php } ?>
                </div>
                <?php if (is_user_logged_in()) { ?>
                    <?php $data = STUser_f::get_icon_wishlist(); ?>
                    <div class="service-add-wishlist login <?php echo ($data['status']) ? 'added' : ''; ?>"
                        data-id="<?php echo get_the_ID(); ?>" data-type="<?php echo get_post_type(get_the_ID()); ?>"
                        title="<?php echo ($data['status']) ? __('Remove from wishlist', 'traveler') : __('Add to wishlist', 'traveler'); ?>">
                        <?php echo TravelHelper::getNewIconV2('wishlist');?>
                        <div class="lds-dual-ring"></div>
                    </div>
                <?php } else { ?>
                    <a href="javascript: void(0)" class="login" data-bs-toggle="modal" data-bs-target="#st-login-form">
                        <div class="service-add-wishlist" title="<?php echo __('Add to wishlist', 'traveler'); ?>">
                            <?php echo TravelHelper::getNewIconV2('wishlist');?>
                            <div class="lds-dual-ring"></div>
                        </div>
                    </a>
                <?php } ?>
                <a href="javascript:void(0)" class="img-feature">
                    <?php
                    if(has_post_thumbnail()){
                        the_post_thumbnail(array(450, 300), array('alt' => TravelHelper::get_alt_image(), 'class' => 'img-responsive', 'itemprop'=>"image"));
                    }else{
                        echo '<img src="'. get_template_directory_uri() . '/img/no-image.png' .'" alt="'.esc_attr(get_the_title()).'" class="img-responsive" />';
                    }
                    ?>
                </a>
                <?php do_action('st_list_compare_button', get_the_ID(), get_post_type(get_the_ID())); ?>
                <?php echo st_get_avatar_in_list_service(get_the_ID(),70)?>
            </div>
            <div class="content-item">
                <?php
                $category = get_the_terms(get_the_ID(), 'st_category_cars');
                if (!is_wp_error($category) && is_array($category)) {
                    $category = array_shift($category);
                    echo '<div class="car-type plr15">' . esc_html($category->name) . '</div>';
                }
                ?>
                <h4 class="title" itemprop="name">
                    <a href="javascript:void(0)" class="st-link c-main">
                        <?php 
                        if ($is_valid_route) {
                            echo get_the_title($transfer_from). " to " . get_the_title($transfer_to);
                        } else {
                            echo get_the_title($post_translated);
                        }
                        ?>
                    </a>
                </h4>
                <div class="car-equipments d-flex align-items-center justify-content-start clearfix">
                    <?php
                    $pasenger = (int)get_post_meta(get_the_ID(), 'passengers', true);
                    $baggage = (int)get_post_meta(get_the_ID(), 'baggage', true);
                    ?>
                    <div class="item d-flex flex-column" data-bs-toggle="tooltip" title="<?php echo esc_attr__('Passenger', 'traveler') ?>">
                        <span class="ico"><i class="stt-icon-user2"></i></span>
                        <span class="text text-center"><?php echo esc_attr($pasenger); ?> Max.</span>
                    </div>
                    <div class="item d-flex flex-column" data-bs-toggle="tooltip" title="<?php echo esc_attr__('Baggage', 'traveler') ?>">
                        <span class="ico"><i class="stt-icon-baggage"></i></span>
                        <span class="text text-center"><?php echo esc_attr($baggage); ?></span>
                    </div>
                </div>

                <?php if ($is_valid_route): ?>
                    <?php
                        $sr_carstrander = new STCarTransfer();
                        $get_transfer = $sr_carstrander->get_transfer(get_the_ID(),$transfer_from, $transfer_to);
                        $return_car = isset($get_transfer->has_return) ? $get_transfer->has_return : 'no';
                    ?>
                    <?php if($return_car === 'yes'): ?>
                        <div class="extra-service-return-wrap mt10">
                            <label class="control-label"><?php echo __('Transfer: ', 'traveler');?></label> 
                            <span><input type="radio" name="return_car" checked value="no"> <?php echo __('Oneway', 'traveler');?> </span>
                            <span><input type="radio" name="return_car"  value="yes"> <?php echo __('Roundtrip', 'traveler');?> </span>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mt10">
                        <label class="custom-transfer-alert" style="font-size: 11px; color: #d07e2f;"><?php echo __('Please select Pick-up/Drop-off for pricing', 'traveler');?></label> 
                    </div>
                <?php endif; ?>

                <div class="booking-item-features booking-item-features-small clearfix mt10">
                    <div class="st-choose-datetime">
                        <a class="st_click_choose_datetime_transfer" type="button"
                            data-target="#st_click_choose_datetime" aria-expanded="false"
                            aria-controls="st_click_choose_datetime">
                            <?php echo __('Choose Pickup time', 'traveler'); ?> 
                        </a>
                    </div>
                </div>

                    <div class="price-wrapper d-flex flex-column align-items-center" itemprop="totalPrice">
                        <span class="label" style="font-size: 11px; color: #94a3b8; font-weight: 400;"><?php echo __('Starting from', 'traveler'); ?></span>
                        <span class="price">
                            <?php
                            $display_price = 0;
                            if ($is_valid_route) {
                                $passengers = (int)STInput::get('passengers', 1);
                                
                                // 1. Attempt to get price for CURRENT language ID
                                $display_price = $transfer->get_transfer_total_price(get_the_ID(), $transfer_from, $transfer_to, $roundtrip, $passengers);
                                
                                // 2. Fallback: Check translated versions
                                if (!$display_price || $display_price == 0) {
                                    if (function_exists('icl_object_id')) {
                                        $languages = icl_get_languages('skip_missing=0');
                                        foreach($languages as $lang) {
                                            $tr_id = icl_object_id(get_the_ID(), 'st_cars', false, $lang['language_code']);
                                            if ($tr_id && $tr_id != get_the_ID()) {
                                                $fallback_price = $transfer->get_transfer_total_price($tr_id, $transfer_from, $transfer_to, $roundtrip, $passengers);
                                                if ($fallback_price > 0) {
                                                    $display_price = $fallback_price;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }

                                // 3. Safety Fallback: Check 'journey' meta directly if still 0
                                if (!$display_price || $display_price == 0) {
                                    $journey_data = get_post_meta(get_the_ID(), 'journey', true);
                                    if (!empty($journey_data) && is_array($journey_data)) {
                                        foreach ($journey_data as $j) {
                                            if (($j['transfer_from'] == $transfer_from && $j['transfer_to'] == $transfer_to) ||
                                                ($j['transfer_from'] == $transfer_to && $j['transfer_to'] == $transfer_from && ($j['return'] ?? '') == 'yes')) {
                                                $display_price = (float)$j['price'];
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            if (!$display_price || $display_price == 0) {
                                $minmax = STAdminCars::inst()->get_min_max_price_transfer(get_the_ID());
                                $display_price = $minmax['min_price'];
                            }
                            
                            if ($display_price > 0) {
                                echo TravelHelper::format_money($display_price);
                            } else {
                                echo '<span style="font-size: 14px;">' . __('Price on Request', 'traveler') . '</span>';
                            }
                            ?>
                        </span>
                        <span class="unit">/<?php echo esc_html($transfer->get_transfer_unit( get_the_ID() )); ?></span>
                    </div>
                    <input type="hidden" name="transfer_from" value="<?php echo esc_attr( $transfer_from ); ?>">
                    <input type="hidden" name="transfer_to" value="<?php echo esc_attr( $transfer_to ); ?>">
                    <input type="hidden" name="roundtrip" value="<?php echo esc_attr( $roundtrip ); ?>">
                    <input type="hidden" name="start" value="<?php echo esc_attr( $pickup_date ); ?>">
                    <input type="hidden" name="start-time" value="<?php echo esc_attr( $pick_up_time ); ?>">
                    <input type="hidden" name="end" value="<?php echo esc_attr( $dropoff_date ); ?>">
                    <input type="hidden" name="end-time" value="<?php echo esc_attr( $drop_off_time ); ?>">
                    <input type="hidden" name="action" value="add_to_cart_transfer">
                    <input type="hidden" name="car_id" value="<?php echo get_the_ID(); ?>">
                    <div class="service-type type-btn-view-more service-price-book">
                        <input type="submit" name="booking_car_transfer" class="view-detail btn-book_cartransfer" value="<?php echo __( 'Book Now', 'traveler' ); ?>">
                    </div>
                </div>
            </div>
        </form>
        <div class="message" role="alert"></div>
    </div>
<?php 
?>
