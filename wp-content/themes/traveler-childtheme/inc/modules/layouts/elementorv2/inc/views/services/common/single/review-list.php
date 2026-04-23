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

        /* ── Identify Source ───────────────────────────── */
        $ota_source = get_comment_meta($comment_id, 'ota_source', true);
        
        $is_gyg = ($ota_source === 'gyg');
        $is_via = ($ota_source === 'vt' || $ota_source === 'viator');
        $is_tri = ($ota_source === 'TA' || $ota_source === 'tripadvisor');
        $is_gmb = ($ota_source === 'gmb' || $ota_source === 'google');
        $is_tp  = get_comment_meta($comment_id, 'trustpilot_review_id', true);

        /* ── Determine Source Label ───────────────────────────── */
        $source_label = 'Khao Lak Land Discovery';
        if ($is_gyg) $source_label = 'GetYourGuide';
        elseif ($is_tri) $source_label = 'TripAdvisor';
        elseif ($is_via) $source_label = 'Viator';
        elseif ($is_gmb) $source_label = 'Google';

        $author_name = $comment->comment_author;
        if (!empty($user_id)) {
            $author_name = TravelHelper::get_username($user_id);
        }

        /* ── Determine Origin URL ───────────────────────────── */
        $post_id = $comment->comment_post_ID;
        $origin_url = '';
        if ($is_gyg) $origin_url = get_post_meta($post_id, '_gyg_url', true);
        elseif ($is_tri) $origin_url = get_post_meta($post_id, '_ta_url', true);
        elseif ($is_via) $origin_url = get_post_meta($post_id, '_viator_url', true);
        elseif ($is_gmb) $origin_url = 'https://www.google.com/maps/search/?api=1&query=Khao+Lak+Land+Discovery&query_place_id=' . get_post_meta($post_id, '_gmb_id', true);
        ?>
        <div class="comment-item" style="position:relative;" itemprop="review" itemscope itemtype="https://schema.org/Review">
            <div class="comment-item-head pd-3" style="position:relative;">
                <div class="d-flex align-items-center">
                    <div class="review-avatar" style="flex-shrink: 0; margin-right: 15px;">
                        <?php 
                        $img_dir = plugins_url('ota-reviews/img/');
                        $avatar_url = '';
                        if ($is_gyg) $avatar_url = $img_dir . 'avatar-gyg.svg';
                        elseif ($is_tri) $avatar_url = $img_dir . 'avatar-tripadvisor.svg';
                        elseif ($is_via) $avatar_url = $img_dir . 'avatar-viator.svg';
                        elseif ($is_gmb) $avatar_url = $img_dir . 'avatar-google.svg';
                        elseif ($is_tp)  $avatar_url = $img_dir . 'avatar-trustpilot.svg';

                        if ($avatar_url) {
                            echo '<img src="' . esc_url($avatar_url) . '" alt="OTA" class="ota-logo-header" style="width:40px; height:48px; object-fit:contain; border-radius:50%; background:#f8fafc; padding:2px; border:1px solid #e2e8f0;" loading="lazy">';
                        } else {
                            // Use Site Logo for native reviews
                            $site_logo = $img_dir . 'avatar-klld.svg';
                            echo '<img src="' . esc_url($site_logo) . '" alt="Site Logo" style="width:48px; height:48px; object-fit:contain; border-radius:50%; background:#fff; padding:2px; border:1px solid #eee;" loading="lazy">';
                        }
                        ?>
                    </div>
                    <div class="review-meta-content" style="flex-grow: 1; min-width: 0; padding-right: 80px;">
                        <!-- Line 1: Name -->
                        <div class="review-meta-name mb-1">
                            <?php if ($origin_url): ?>
                                <a href="<?php echo esc_url($origin_url); ?>" target="_blank" rel="nofollow" class="author-name-link" style="text-decoration:none;">
                            <?php endif; ?>
                            <span class="author-name" style="font-size: 15px; font-weight: 700; color: #1a2b48; display: block;" title="<?php echo esc_attr($author_name); ?>">
                                <?php echo esc_html($author_name); ?>
                            </span>
                            <?php if ($origin_url): ?></a><?php endif; ?>
                        </div>
                        
                        <!-- Line 2: Rate (Stars) -->
                        <div class="review-meta-stars mb-1">
                            <?php
                            $comment_rate = (float)get_comment_meta( $comment_id, 'comment_rate', true );
                            if ($comment_rate) {
                                echo '<ul class="review-star small" style="margin:0; padding:0; list-style:none; display:flex; gap:2px; font-size: 8px;">';
                                echo TravelHelper::rate_to_string($comment_rate);
                                echo '</ul>';
                            }
                            ?>
                        </div>

                        <!-- Line 3: Source + Date Time -->
                        <div class="review-meta-details" style="font-size: 8px; color: #94a3b8; line-height: 1.2; display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                            <span class="source-label">
                                <?php echo __('via', 'traveler'); ?> 
                                <span style="font-weight:600;"><?php echo esc_html($source_label); ?></span>
                            </span>
                            <span class="separator" style="color: #cbd5e1;">&bull;</span>
                            <span class="date-time">
                                <?php 
                                $raw_date = get_comment_meta($comment_id, 'review_date', true);
                                if ($raw_date) {
                                    $timestamp = strtotime($raw_date);
                                    echo esc_html(date('d-M-Y H:i', $timestamp));
                                } else {
                                    echo esc_html(get_comment_date('d-M-Y H:i', $comment_id)); 
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="like-button-wrapper" style="position: absolute; top: 15px; right: 15px; display: flex; align-items: center; gap: 12px; color: #94a3b8;">
                    <!-- Like -->
                    <div class="like-container d-flex align-items-center" style="gap: 5px;">
                        <?php $review_obj = new STReview();
                            if ( $review_obj->check_like( $comment_id ) ):
                                ?>
                                <a data-id="<?php echo esc_attr( $comment_id ); ?>" href="#"
                                   class="btn-like st-like-review" style="color:#0ea5e9;">
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
                            echo '<span style="font-size:12px;">' . esc_html($count_like) . '</span>';
                        ?>
                    </div>
                    
                    <!-- Dislike placeholder (Theme icon) -->
                    <a href="#" class="review-dislike-link" style="color: inherit; transform: rotate(180deg); display: inline-block;">
                        <i class="stt-icon-like"></i>
                    </a>

                    <!-- Reply Action -->
                    <a href="#write-review" class="review-reply-link" title="<?php echo __('Reply', 'traveler'); ?>" style="color: inherit; font-size: 14px;">
                        <i class="fa fa-reply"></i>
                    </a>
                </div>
            </div>
            <div class="comment-item-body">
                <div class="detail">
                    <?php
                        $comment_title = get_comment_meta($comment_id, 'comment_title', true);
                        if ($comment_title && strpos($comment_title, 'Expert tour from') === false): ?>
                            <h4 class="comment-title" style="font-size: 15px; font-weight: 700; color: #1a2b48; margin-bottom: 8px; line-height: 1.4;">
                                <?php echo esc_html($comment_title); ?>
                            </h4>
                        <?php endif; ?>

                    <?php
                        $content = get_comment_text( $comment_id );
                        $char_limit = 150; 
                    ?>
                    <div class="st-description line-clamp" id="st-description-<?php echo esc_attr($comment_id); ?>" itemprop="reviewBody" style="line-height: 1.5; margin-bottom: 5px;">
                        <?php echo balanceTags($content); ?>
                    </div>
                    
                    <?php if (strlen($content) > $char_limit): ?>
                        <a href="javascript:void(0);" class="st-read-more-review" 
                           style="color: #0ea5e9; font-size: 13px; font-weight: 600; text-decoration: none;"
                           onclick="var desc=document.getElementById('st-description-<?php echo esc_attr($comment_id); ?>'); if(desc.classList.contains('line-clamp')){ desc.classList.remove('line-clamp'); this.innerText='<?php echo __('Read less', 'traveler'); ?>'; } else { desc.classList.add('line-clamp'); this.innerText='<?php echo __('Read more..', 'traveler'); ?>'; }">
                            <?php echo __('Read more..', 'traveler'); ?>
                        </a>
                    <?php endif; ?>

                    <?php 
                    /* ── Display Review Photos (Carousel) ──────────────── */
                    $ota_photos = get_comment_meta($comment_id, 'ota_review_photos', true);
                    $legacy_photo = get_comment_meta($comment_id, 'comment_photo', true);
                    
                    $photos = is_array($ota_photos) ? $ota_photos : ($legacy_photo ? [$legacy_photo] : []);
                    
                    if (!empty($photos)) {
                        if (!defined('KLLD_REVIEWS_STYLE_LOADED')) {
                            define('KLLD_REVIEWS_STYLE_LOADED', true);
                            ?>
                            <style>
                                .st-description.line-clamp {
                                    display: -webkit-box;
                                    -webkit-line-clamp: 3;
                                    -webkit-box-orient: vertical;
                                    overflow: hidden;
                                }
                                .st-review-carousel { 
                                    display: flex; 
                                    overflow-x: auto; 
                                    gap: 12px; 
                                    padding: 10px 2px; 
                                    scroll-snap-type: x mandatory; 
                                    -webkit-overflow-scrolling: touch; 
                                    margin-top: 15px;
                                    scrollbar-width: thin;
                                }
                                .st-review-carousel::-webkit-scrollbar { height: 6px; }
                                .st-review-carousel::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
                                .st-review-carousel::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
                                .st-review-carousel .photo-item { 
                                    flex: 0 0 180px; 
                                    width: 180px;
                                    height: 135px;
                                    aspect-ratio: 4/3;
                                    scroll-snap-align: start; 
                                    position: relative; 
                                    border-radius: 10px; 
                                    overflow: hidden; 
                                    border: 1px solid #e2e8f0; 
                                    background: #f8fafc;
                                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                                }
                                .st-review-carousel .photo-item img { 
                                    width: 100%; 
                                    height: 100%; 
                                    object-fit: contain; 
                                    display: block;
                                    background: #f8fafc;
                                    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
                                }
                                .st-review-carousel .photo-item:hover img { transform: scale(1.1); }
                                @media (max-width: 768px) {
                                    .st-review-carousel .photo-item { flex: 0 0 140px; width: 140px; height: 105px; }
                                }
                            </style>
                            <?php
                        }
                        
                        echo '<div class="st-review-carousel">';
                        $proxy_base = plugins_url('ota-reviews/img_proxy.php?url=');
                        foreach ($photos as $i => $url) {
                            $lazy = ($i < 2) ? 'eager' : 'lazy'; 
                            
                            $display_url = $url;
                            if (strpos($url, 'http') === 0 && strpos($url, home_url()) === false) {
                                $display_url = $proxy_base . rawurlencode($url);
                            }

                            echo '<div class="photo-item" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">';
                            echo '<a href="' . esc_url($url) . '" target="_blank" class="st-review-photo-link" itemprop="contentUrl">';
                            ?>
                            <img src="<?php echo esc_url($display_url); ?>" 
                                 alt="Review Photo" 
                                 loading="<?php echo $lazy; ?>" 
                                 itemprop="thumbnail" 
                                 data-origin="<?php echo esc_attr($url); ?>"
                                 onerror="if(this.src.indexOf('img_proxy.php') !== -1) { this.src=this.getAttribute('data-origin'); } else { this.parentElement.parentElement.style.display='none'; }">
                            <?php
                            echo '</a>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    ?>

                    <?php 
                    /* ── Display Review Replies (Children) ──────────────── */
                    $children = get_comments([
                        'parent'  => $comment_id,
                        'status'  => 'approve',
                        'orderby' => 'comment_date',
                        'order'   => 'ASC'
                    ]);

                    if ($children) {
                        echo '<div class="st-review-replies ml30 mt20" style="border-left: 2px solid #eee; padding-left: 20px;">';
                        foreach ($children as $child) {
                            echo stt_elementorv2()->loadView('services/common/single/review-list', ['comment' => $child, 'post_type' => $post_type]);
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    endif;
?>