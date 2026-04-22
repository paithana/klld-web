<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-11-2018
 * Time: 8:47 AM
 * Since: 1.0.0
 * Updated: 1.0.0
 */
wp_enqueue_script('filter-tour');
while (have_posts()): the_post();
    $post_id = get_the_ID();
    $address = get_post_meta($post_id, 'address', true);
    $review_rate = STReview::get_avg_rate();
    $count_review = STReview::count_review($post_id);
    $tour_external = get_post_meta(get_the_ID(), 'st_tour_external_booking', true);
    $tour_external_link = get_post_meta(get_the_ID(), 'st_tour_external_booking_link', true);
    $booking_type = st_get_booking_option_type();
    $tour_type = get_post_meta(get_the_ID(), 'type_tour', true);
    ?>
    <div id="st-content-wrapper" class="st-style-4 st-style-elementor st-single-tour st-single-tour-<?php echo esc_attr($style_single);?>" itemscope itemtype="https://schema.org/Tour">
        <?php
        echo stt_elementorv2()->loadView('components/banner');
        ?>
        <div class="container st-single-service-content">
            <?php echo stt_elementorv2()->loadView('services/tour/single/item/top-infor', array(
                'address'=> $address,
                'review_rate' => $review_rate,
                'count_review' => $count_review,
                )) ?>
            <?php echo stt_elementorv2()->loadView('services/common/single/gallery',array('style'=> 'grid')) ?>
        </div>
        <div class="container st-single-service-content">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                    <div class="st-hotel-content">
                        <div class="hotel-target-book-mobile d-flex justify-content-between align-items-center">
                            <div class="price-wrapper">
                                <div id="mobile-price">
                                    <?php 
                                    $price_info = STTour::get_info_price();
                                    ?>
                                    <div class="st-schema-data" itemprop="offers" itemscope itemtype="https://schema.org/Offer" style="display:none;">
                                        <meta itemprop="priceCurrency" content="<?php echo TravelHelper::get_current_currency('name'); ?>" />
                                        <meta itemprop="price" content="<?php echo (float)($price_info['price_new'] ?? $price_info['price']); ?>" />
                                        <link itemprop="availability" href="https://schema.org/InStock" />
                                    </div>
                                    <?php echo wp_kses(sprintf(__('From:<span class="price">%s</span>', 'traveler'), STTour::get_price_html(get_the_ID())), ['span' => ['class' => []]]) ?>
                                </div>
                                <div class="st-review-booking-form">
                                    <div class="st-review-box-top d-flex align-items-center">
                                        <i class="stt-icon-star1"></i>
                                        <?php
                                        $count_review = get_comment_count($post_id)['approved'];
                                        $avg = STReview::get_avg_rate();
                                        ?>
                                        <div class="review-score">
                                            <?php echo esc_attr($avg); ?>
                                        </div>
                                        <div class="review-score-base text-center">
                                            <span>(<?php comments_number(__('0 review', 'traveler'), __('1 review', 'traveler'), __('% reviews', 'traveler')); ?>)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="" class="btn-v2 btn-primary btn-mpopup btn-green" onclick="event.preventDefault(); event.stopImmediatePropagation(); jQuery('.fixed-on-mobile').toggleClass('open').fadeToggle(300); jQuery('body').addClass('st_overflow');"><?php echo esc_html__('Book Now', 'traveler') ?></a>
                        </div>
                    </div>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/infor', array('tour_type' => $tour_type)); ?>
                    <div class="st-hr"></div>
                    <?php echo stt_elementorv2()->loadView('services/common/single/description') ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/itenirary',['post_id'=>$post_id]) ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/highlights') ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/include-exclude') ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/whattobring') ?>

                    <div id="st-attributes">
                        <?php echo stt_elementorv2()->loadView('services/common/single/attributes',['post_type' => 'st_tours']) ?>
                    </div>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/faq',['post_id'=>$post_id]) ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/location',['post_id'=>$post_id]) ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/discount'); ?>
                    <?php echo stt_elementorv2()->loadView('services/tour/single/item/review',['post_id' => $post_id]); ?>
                    <div class="stoped-scroll-section"></div>
                </div>
                <div class="col-12 col-sm-12 col-md-12 col-lg-4">
                    <div class="widgets sticky-top">
                        <div class="fixed-on-mobile st-fixed-form-booking" data-screen="992px">
                            <div class="close-icon" onclick="event.preventDefault(); event.stopImmediatePropagation(); jQuery(this).closest('.fixed-on-mobile').removeClass('open').fadeOut(200); jQuery('body').removeClass('st_overflow');">
                                <i class="fa fa-times"></i>
                            </div>
                            <?php
                                $info_price = STTour::get_info_price();
                                echo stt_elementorv2()->loadView('services/tour/single/item/form-book', [
                                    'info_price' =>$info_price,
                                    'tour_external' => $tour_external,
                                    'tour_external_link' => $tour_external_link,
                                    'tour_type' => $tour_type,
                                    'booking_type' => $booking_type,
                                    'review_rate' => $review_rate,
                                ]);
                            ?>
                            <?php echo st()->load_template('layouts/elementor/hotel/single/item/owner-info','',array('size_avatar_custom' => 90)); ?>

                            <?php echo st()->load_template('layouts/modern/common/single/information-contact'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo stt_elementorv2()->loadView('services/tour/single/item/relate',['post_id' => $post_id]) ?>
        </div>
    </div>
<?php endwhile;
