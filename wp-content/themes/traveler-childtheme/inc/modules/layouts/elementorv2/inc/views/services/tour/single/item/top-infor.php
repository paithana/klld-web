<div class="st-service-header2 d-flex align-self-start justify-content-between">
    <div class="left">
        <h1 class="st-heading" itemprop="name"><?php the_title(); ?></h1>
		<div class="sub-heading">
			<div class="d-inline-block d-sm-flex align-items-center">
				<div class="st-review-score">
					</div>
					<div class="head d-inline-block d-sm-flex justify-content-between align-items-center clearfix">
						<div class="left">
                            <a href="#st-reviews" class="st-review-link" style="text-decoration: none; color: inherit;">
                                <div class="reviews" style="display: flex; align-items: center; gap: 4px;">
                                    <i class="stt-icon-star1" style="color: #ffcc00;"></i>
                                    <span class="rate" style="font-weight: 700;">
                                        <?php echo esc_html( $review_rate ); ?>
                                    </span>
                                    <span class="summary" style="text-decoration: underline; text-underline-offset: 3px; color: #0ea5e9;">
                                        <?php echo sprintf(__(' of %s Reviews', 'traveler'), get_comments_number()); ?>
                                    </span>
                                </div>
                            </a>
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
