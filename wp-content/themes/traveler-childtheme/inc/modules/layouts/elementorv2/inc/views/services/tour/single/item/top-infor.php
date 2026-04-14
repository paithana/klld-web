<div class="st-service-header2 d-flex align-self-start justify-content-between">
    <div class="left">
        <h1 class="st-heading" itemprop="name"><?php the_title(); ?></h1>
		<div class="sub-heading">
			<div class="d-inline-block d-sm-flex align-items-center">
				<div class="st-review-score">
					</div>
					<div class="head d-inline-block d-sm-flex justify-content-between align-items-center clearfix">
						<div class="left">
                            <?php if(!empty($review_rate) && $review_rate > 0){ ?>
							    <!-- <div class="reviews" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating"> -->
                                <div class="reviews">

                            <?php } else { ?>
                                <div class="reviews">
                            <?php } ?>
								<i class="stt-icon-star1"></i>
								<span class="rate" itemprop="ratingValue" name="review-rate">
									<?php echo esc_html( $review_rate ); ?>
								</span>
								<span class="summary">
									(<?php echo sprintf(_n('<span class="rate" itemprop="reviewCount" name="review-count">%s</span> Review', '<span class="rate" itemprop="reviewCount" name="review-count">%s</span> Reviews', get_comments_number(), 'traveler'), get_comments_number())?>)
								</span>
							</div>
                            <?php if(!empty($review_rate) && $review_rate > 0){ ?>
                                <!-- End AggregateRating -->
                            <?php } ?>
						</div>
					</div>
				<span class="st-dot"></span>
				<div class="st-address">
					<?php
					if ( $address ) {
						echo esc_html( $address );
					}
					?>
				</div>
			</div>

		</div>
    </div>
    <div class="right d-flex align-items-center">
        <div class="shares dropdown">
            <a href="#" class="share-item social-share">
                <i class="stt-icon stt-icon-share"></i>
            </a>
            <ul class="share-wrapper">
                <li><a class="facebook"
                        href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
                        target="_blank" rel="noopener" original-title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a class="twitter"
                        href="https://twitter.com/share?url=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
                        target="_blank" rel="noopener" original-title="Twitter"><i class="fab fa-twitter"></i></a></li>
                <li><a class="no-open pinterest"
                    href="http://pinterest.com/pin/create/bookmarklet/?url=<?php the_permalink() ?>&is_video=false&description=<?php the_title() ?>&media=<?php echo get_the_post_thumbnail_url(get_the_ID())?>"
                        target="_blank" rel="noopener" original-title="Pinterest"><i class="fab fa-pinterest-p"></i></a></li>
                <li><a class="linkedin"
                        href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
                        target="_blank" rel="noopener" original-title="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
            </ul>
        </div>
        <div class="wistlist-single">
            <?php if (is_user_logged_in()) { ?>
                <?php $data = STUser_f::get_icon_wishlist(); ?>
                <div class="service-add-wishlist login <?php echo ($data['status']) ? 'added' : ''; ?>"
                    data-id="<?php echo get_the_ID(); ?>" data-type="<?php echo get_post_type(get_the_ID()); ?>"
                    title="<?php echo ($data['status']) ? __('Remove from wishlist', 'traveler') : __('Add to wishlist', 'traveler'); ?>">
                    <span class="stt-icon stt-icon-heart1"></span>
                    <div class="lds-dual-ring"></div>
                </div>
            <?php } else { ?>
                <a href="javascript: void(0)" class="login" data-bs-toggle="modal" data-bs-target="#st-login-form">
                    <div class="service-add-wishlist" title="<?php echo __('Add to wishlist', 'traveler'); ?>">
                        <span class="stt-icon stt-icon-heart1"></span>
                        <div class="lds-dual-ring"></div>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
