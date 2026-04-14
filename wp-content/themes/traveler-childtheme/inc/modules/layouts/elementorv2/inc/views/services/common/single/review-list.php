<?php
    /**
     * @package    WordPress
     * @subpackage Traveler
     * @since      1.0
     *
     * Review list
     *
     * Created by ShineTheme
     *
     */
    $args[ 'avatar_size' ] = 50;
    if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) :
    else :
        $comment_class = empty( $args[ 'has_children' ] ) ? '' : $comment_class .= 'parent';
        if ( !$comment->comment_approved ) {
            return;
        }

        $comment_id   = $comment->comment_ID;
        $user_id      = get_comment( $comment_id )->user_id;
        $user_email   = get_comment( $comment_id )->comment_author_email;
        $current_user = wp_get_current_user();
        ?>
        <div class="comment-item" style="position:relative;" itemprop="review" itemscope itemtype="https://schema.org/Review">
            <?php 
            /* ── OTA Source Badge (Top Right) ───────────────────────── */
            $is_gyg = get_comment_meta($comment_id, 'gyg_review_id', true);
            $is_via = get_comment_meta($comment_id, 'viator_review_id', true);
            $is_tri = get_comment_meta($comment_id, 'tripadvisor_review_id', true);
            $is_gmb = get_comment_meta($comment_id, 'gmb_review_id', true);
            $is_tp  = get_comment_meta($comment_id, 'trustpilot_review_id', true);

            $badge_html = '';
            if ($is_gyg) {
                $badge_html = '<span class="ota-badge badge-gyg" style="padding:4px 8px; font-size:10px; background:#ff5533; color:#fff; border-radius:4px; font-weight:600;">GetYourGuide</span>';
            } elseif ($is_via) {
                $badge_html = '<span class="ota-badge badge-viator" style="padding:4px 8px; font-size:10px; background:#00af87; color:#fff; border-radius:4px; font-weight:600;">Viator</span>';
            } elseif ($is_tri) {
                $badge_html = '<span class="ota-badge badge-tripadvisor" style="padding:4px 8px; font-size:10px; background:#34e0a1; color:#000; border-radius:4px; font-weight:600;">TripAdvisor</span>';
            } elseif ($is_gmb) {
                $badge_html = '<span class="ota-badge badge-google" style="padding:4px 8px; font-size:10px; background:#4285F4; color:#fff; border-radius:4px; font-weight:600;">Google</span>';
            } elseif ($is_tp) {
                $badge_html = '<span class="ota-badge badge-trustpilot" style="padding:4px 8px; font-size:10px; background:#00b67a; color:#fff; border-radius:4px; font-weight:600;">Trustpilot</span>';
            }

            if ($badge_html) {
                echo '<div class="ota-badge-top-right" style="position:absolute; top:20px; right:20px; z-index:10;">' . $badge_html . '</div>';
            }
            ?>
            <div class="comment-item-head d-flex justify-content-between align-items-center">
                <div class="media d-flex align-items-center">
                    <div class="media-left">
                        <?php echo st_get_profile_avatar( $user_id, 50 ) ?>
                    </div>
                    <div class="media-body">
                        <?php
                        if(!empty($user_id)){
                            ?>
                            <div class="media-heading" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name"><?php echo TravelHelper::get_username( $user_id ); ?></span></div>
                            <?php
                        }else{
                            ?>
                            <div class="media-heading" itemprop="author" itemscope itemtype="https://schema.org/Person">
                                <span itemprop="name"><?php echo esc_html($comment->comment_author); ?></span>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="date" itemprop="datePublished"><?php echo get_comment_date( TravelHelper::getDateFormat(), $comment_id ) ?></div>
                    </div>
                </div>
                <div class="like">
                    <?php $review_obj = new STReview();
                        if ( $review_obj->check_like( $comment_id ) ):
                            ?>
                            <a data-id="<?php echo esc_attr( $comment_id ); ?>" href="#"
                               class="btn-like st-like-review ">
                               <i class="stt-icon-like bold"></i>
                            </a>
                        <?php else: ?>
                            <a data-id="<?php echo esc_attr( $comment_id ); ?>" href="#"
                               class="btn-like st-like-review ">
                               <i class="stt-icon-like"></i>
                            </a>
                        <?php
                        endif;
                    ?>
                    <?php
                        $count_like = (int)get_comment_meta( $comment_id, '_comment_like_count', true );
                        echo '<span>' . esc_html($count_like) . '</span>';
                    ?>
                </div>
            </div>
            <div class="comment-item-body">
                <?php
                    $stats        = STReview::get_review_stats( get_the_ID() );
                    $comment_rate = (float)get_comment_meta( $comment_id, 'comment_rate', true );
                ?>
                <?php if(isset($post_type) and in_array($post_type, ['st_tours', 'st_activity', 'st_hotel'])){ ?>
                    <?php if ( $comment_title = get_comment_meta( $comment_id, 'comment_title', true ) ): ?>
                        <?php
                            if ( $stats ) {
                                echo '<ul class="review-star" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">';
                                echo '<meta itemprop="ratingValue" content="' . esc_attr($comment_rate) . '">';
                                echo TravelHelper::rate_to_string($comment_rate);
                                echo '</ul>';
                            }
                        ?>
                        <h3 class="h4 title st_tours" <?php if(!$stats) echo 'style="padding-left: 0;"'; ?>>
                            <?php echo esc_html(balanceTags( $comment_title )) ?>
                        </h3>
                    <?php else:
                        if ( $stats ) {
                            echo '<ul class="review-star" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">';
                            echo '<meta itemprop="ratingValue" content="' . esc_attr($comment_rate) . '">';
                            echo TravelHelper::rate_to_string($comment_rate);
                            echo '</ul>';
                        }
                    endif; ?>
                <?php }else{
                    ?>
                    <?php if ( $comment_title = get_comment_meta( $comment_id, 'comment_title', true ) ): ?>
                        <h4 class="title d-flex align-items-center" <?php if(!$stats) echo 'style="padding-left: 0;"'; ?>>
                            <?php
                            if ( $stats ) {
                                echo '<span class="comment-rate" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"><span itemprop="ratingValue">' . number_format( $comment_rate, 1, '.', ',' ) . '</span></span>';
                            }
                            ?>
                            "<?php echo esc_html(balanceTags( $comment_title )) ?>"
                        </h4>
                    <?php else: ?>
                        <h4 class="title d-flex align-items-center" <?php if(!$stats) echo 'style="padding-left: 0;"'; ?>>
                            <?php if ( $stats ) {
                                echo '<span class="comment-rate" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"><span itemprop="ratingValue">' . number_format( $comment_rate, 1, '.', ',' ) . '</span></span>';
                            } ?>
                        </h4>
                    <?php endif; ?>
                    <?php
                } ?>
                    <?php
                        if ( !$stats && $comment_rate ) {
                            ?>
                            <div class="st-stars style-2">
                                <?php
                                    for ( $i = 1; $i <= 5; $i++ ) {
                                        if ( $i <= $comment_rate ) {
                                            echo '<i class="fa fa-star"></i>';
                                        } else {
                                            echo '<i class="fa fa-star grey"></i>';
                                        }
                                    }
                                ?>
                            </div>
                        <?php }
                    ?>
                <div class="detail">
                    <?php
                        $content = get_comment_text( $comment_id );
                    ?>
                    <div class="st-description" itemprop="reviewBody"
                         data-show-all="st-description-<?php echo esc_attr($comment_id); ?>" <?php if ( str_word_count( $content ) >= 80 ) {
                        echo ' data-height="80"';
                    } ?>>
                        <?php echo esc_html(balanceTags($content)); ?>
                    </div>
                    <?php if ( str_word_count( $content ) >= 80 ) { ?>
                        <a href="#" class="st-link block"
                           data-show-target="st-description-<?php echo esc_attr($comment_id); ?>"
                           data-text-less="<?php echo esc_html__( 'View Less', 'traveler' ) ?>"
                           data-text-more="<?php echo esc_html__( 'View More', 'traveler' ) ?>">
                           <span class="text"><?php echo esc_html__( 'View More', 'traveler' ) ?></span>
                           <i class="fa fa-caret-down ml3"></i>
                        </a>
                    <?php } ?>

                    <?php 
                    /* ── Display Uploaded Review Photo ────────────────── */
                    $photo_url = get_comment_meta($comment_id, 'comment_photo', true);
                    if ($photo_url) {
                        echo '<div class="st-review-photo" style="margin-top:10px;">';
                        echo '<a href="' . esc_url($photo_url) . '" target="_blank">';
                        echo '<img src="' . esc_url($photo_url) . '" alt="Review Photo" style="max-width:200px; max-height:150px; border-radius:8px; border:1px solid #ddd;" loading="lazy">';
                        echo '</a>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    endif;
?>