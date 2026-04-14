<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 1/17/2019
 * Time: 11:41 AM
 */

global $woocommerce;
$cart_page_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();

$st_is_woocommerce_checkout = apply_filters('st_is_woocommerce_checkout',false);
$menu_style = st()->get_option( 'menu_style_modern', "" );
$color_cart = '';
$stroke_cart = false;

if($menu_style == '2' || 'location' === get_post_type()) {
    $color_cart = '#fff';
    $stroke_cart = true;
}
if($st_is_woocommerce_checkout and function_exists('WC')){
    $cart_url = wc_get_cart_url();
    $cart_total_item = (int) WC()->cart->get_cart_contents_count();
    $cart_total_amount = WC()->cart->get_cart_subtotal();
    ?>
<li class="dropdown dropdown-minicart">
    <div id="d-minicart" class="mini-cart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php if ($cart_total_item > 0) { ?>
        <div class="cart-caret"><?php echo esc_html($cart_total_item) ?></div>
        <?php } ?>
        <?php
            echo TravelHelper::getNewIcon('ico_card', $color_cart, '26px', '26px', $stroke_cart);
            ?>
    </div>
    <ul class="dropdown-menu" aria-labelledby="d-minicart">
        <li class="heading">
            <h4 class="st-heading-section"><?php echo esc_html__('Your Cart', 'traveler') ?></h4>
        </li>
        <?php
            $items = WC()->cart->get_cart();
            if (!empty($items)):
                foreach($items as $item => $values):
                    $_product = wc_get_product( $values['data']->get_id());
                    $post_id = (int) get_post_meta($_product->get_id(), '_st_booking_id', true );

                    $post_title = $_product->get_title();
                    if( get_post_type( $post_id ) == 'st_hotel' ){
                        $room_id = (int) get_post_meta( $_product->get_id(), 'room_id', true );
                        $post_title = get_the_title( $room_id );
                    }
                    $quantity = (int) $values['quantity'];
                    $price = (float) $values['line_total'];
                    $tax = (float) $values['line_tax'];
                    $price = $price + $tax;
                    ?>
        <li class="cart-item">
            <div class="media">
                <div class="media-left">
                    <?php
                                if( has_post_thumbnail( $post_id ) ){
                                    echo get_the_post_thumbnail( $post_id, 'thumbnail', array('class' => 'img-responsive media-object', 'alt' => TravelHelper::get_alt_image(get_post_thumbnail_id($post_id ))) );
                                }
                                ?>
                </div>
                <div class="media-body">
                    <?php
                                if( get_post_type( $post_id ) == 'st_hotel '):
                                    $room_id = (int) get_post_meta( $_product->ID, 'room_id', true );
                                    ?>
                    <h4 class="media-heading"><a class="st-link c-main"
                            href="<?php echo get_the_permalink($room_id) ?>"><?php echo esc_html($post_title); ?></a>
                    </h4>
                    <?php else: ?>
                    <h4 class="media-heading"><a class="st-link c-main"
                            href="<?php echo get_the_permalink($post_id) ?>"><?php echo esc_html($post_title); ?></a>
                    </h4>
                    <?php endif; ?>
                    <div class="price-wrapper"><?php echo __('Price', 'traveler') ?>:
                        <span class="price"><?php echo TravelHelper::format_money($price); ?></span>
                    </div>
                </div>
            </div>
        </li>
        <?php
                endforeach;
                ?>
        <li class="cart-total">
            <div class="sub-total"><?php echo __('Subtotal', 'traveler') ?> <span
                    class="price"><?php echo balanceTags($cart_total_amount); ?></span>
            </div>
            <!--
            <a href="<?php //echo add_query_arg(['action' => 'st-remove-cart', 'security' => wp_create_nonce('st-security')]); ?>"
                class="btn btn-danger btn-full upper">
                <?php //echo __('Remove Cart', 'traveler') ?>
            </a>

            -->
            <a href="<?php echo $cart_page_url; ?>" class="btn btn-primary btn-full upper">
                <?php echo __('View Cart', 'traveler') ?>
            </a>


            <a href="<?php echo esc_url(get_permalink( wc_get_page_id( 'checkout' ) )) ?>"
                class="btn btn-green btn-full upper mt10"><?php echo __('Check Out', 'traveler') ?></a>
        </li>
        <?php
            else:
                ?>
        <div class="col-lg-12 cart-text-empty text-warning"><?php echo __('Your cart is empty', 'traveler'); ?></div>
        <?php
            endif;
            ?>
    </ul>
</li>
<?php
}else {
    ?>
<li class="dropdown dropdown-minicart">
    <?php
        $check_out_url = (int)st()->get_option('page_checkout', '');
        $check_out_url = get_permalink($check_out_url);
        $cart_total_item = (int)STCart::count();
        $cart_total_amount = (float)(STCart::check_cart()) ? STPrice::getTotal() : 0;
        $post_id_global = 0;
        ?>
    <div id="d-minicart" class="mini-cart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php if ($cart_total_item > 0) { ?>
        <div class="cart-caret"><?php echo esc_html($cart_total_item) ?></div>
        <?php } ?>
        <?php echo TravelHelper::getNewIcon('ico_card', $color_cart, '26px', '26px', $stroke_cart); ?>
    </div>
    <ul class="dropdown-menu" aria-labelledby="d-minicart">
        <li class="heading">
            <h4 class="st-heading-section"><?php echo esc_html__('Your Cart', 'traveler') ?></h4>
        </li>
        <?php
            if (STCart::check_cart()):
                $items = STCart::get_carts();
                foreach ($items as $post_id => $value):
                    $post_id_global = $post_id;
                    $post_title = get_the_title($post_id);
                    if (get_post_type($post_id) == 'st_hotel') {
                        $room_id = (int)$value['data']['room_id'];
                        $post_title = get_the_title($room_id);
                    }
                    $quantity = (int)count($items);
                    $price = (float)STPrice::getTotal();
                    if($post_id == 'travelport_api'){
                        $post_title = $value['data']['fromCode'] . ' -> ' . $value['data']['toCode'];
                    }
                    ?>
        <li class="cart-item">
            <div class="media">
                <?php if($post_id != 'travelport_api'){ ?>
                <div class="media-left">
                    <?php
                                if (has_post_thumbnail($post_id)) {
                                    echo get_the_post_thumbnail($post_id, [70, 70], ['class' => 'media-object', 'alt' => TravelHelper::get_alt_image()]);
                                }
                                ?>
                </div>
                <?php } ?>
                <div class="media-body">
                    <?php
                                if (get_post_type($post_id) == 'st_hotel'):
                                    $room_id = (int)$value['data']['room_id'];
                                    ?>
                    <h4 class="media-heading"><a class="st-link c-main"
                            href="<?php echo get_the_permalink($room_id) ?>"><?php echo esc_html($post_title); ?></a>
                    </h4>
                    <?php else: ?>
                    <h4 class="media-heading"><a class="st-link c-main"
                            href="<?php echo get_the_permalink($post_id) ?>"><?php echo esc_html($post_title); ?></a>
                    </h4>
                    <?php endif; ?>
                    <div class="price-wrapper"><?php echo __('Price', 'traveler') ;?>:
                        <span class="price"><?php echo TravelHelper::format_money($price,false); ?></span>
                    </div>
                </div>
            </div>

        </li>
        <?php
                endforeach;
                ?>
        <li class="cart-total">
            <div class="sub-total"><?php echo __('Subtotal', 'traveler') ?> <span
                    class="price"><?php echo TravelHelper::format_money($cart_total_amount,false); ?></span>
            </div>
            <a href="<?php echo esc_url($check_out_url) ?>"
                class="btn btn-green btn-full upper"><?php _e('Pay Now', 'traveler') ?></a>
        </li>
        <?php
            else:
                ?>
        <div class="col-lg-12 cart-text-empty text-warning"><?php echo __('Your cart is empty', 'traveler'); ?></div>
        <?php
            endif;
            ?>
    </ul>
</li>
<?php
}