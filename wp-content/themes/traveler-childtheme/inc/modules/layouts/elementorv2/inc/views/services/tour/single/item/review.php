<?php 
$post_id = get_the_ID();
// if (comments_open($post_id) and st()->get_option('activity_tour_review') == 'on') {
    $count_review = get_comment_count($post_id)['approved'];
    ?>
    <div class="st-section-single" id="st-reviews">
        <h2 class="st-heading-section mb20">
            <?php echo esc_html__('Reviews', 'traveler') ?>
        </h2>

        <div id="reviews" class="st-reviews">
            
            <!-- Review Form Above Everything -->
            <div id="write-review" class="mb30">
                <h4 class="heading text-center">
                    <a href="javascript: void(0)" class="btn btn-secondary toggle-write-review f16">
                        <?php echo __('Write a review', 'traveler') ?>
                        <i class="stt-icon-arrow-down ml5"></i>
                    </a>
                </h4>
                <div class="st-review-form mt20" style="display: none; background: #f9f9f9; padding: 30px; border-radius: 12px; border: 1px solid #eee;">
                    <div class="information-review">
                        <div class="review-box">
                            <div class="st-review-box-top">
                                <div class="infor-avg-wrapper d-flex text-center align-items-center align-self-center flex-column">
                                    <div class="review-avg d-flex text-center align-items-center">
                                        <i class="stt-icon-star1"></i>
                                        <?php
                                        $avg = STReview::get_avg_rate();
                                        ?>
                                        <div class="review-score">
                                            <?php echo esc_attr($avg); ?><span class="per-total">/5</span>
                                        </div>
                                    </div>
                                    <div class="review-score-text"><?php echo TravelHelper::get_rate_review_text($avg, $count_review); ?></div>
                                    <div class="review-score-base text-center">
                                        <span>(<?php comments_number(__('0 review', 'traveler'), __('1 review', 'traveler'), __('% reviews', 'traveler')); ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="st-summany d-flex flex-wrap justify-content-between">
                                <?php $total_comments = get_comments_number(); ?>
                                <?php $rate_exe = STReview::count_review_by_rate(null, 5); ?>
                                <div class="item d-flex align-items-center justify-content-between">
                                    <div class="label">
                                        <?php echo esc_html__('Excellent', 'traveler') ?>
                                    </div>
                                    <div class="progress">
                                        <div class="percent green"
                                            style="width: <?php echo TravelHelper::cal_rate($rate_exe, $total_comments) ?>%;"></div>
                                    </div>
                                    <div class="number text-end"><?php echo esc_html($rate_exe); ?></div>
                                </div>
                                <?php $rate_good = STReview::count_review_by_rate(null, 4); ?>
                                <div class="item d-flex align-items-center justify-content-between">
                                    <div class="label">
                                        <?php echo __('Very Good', 'traveler') ?>
                                    </div>
                                    <div class="progress">
                                        <div class="percent darkgreen"
                                            style="width: <?php echo TravelHelper::cal_rate($rate_good, $total_comments) ?>%;"></div>
                                    </div>
                                    <div class="number text-end"><?php echo esc_html($rate_good); ?></div>
                                </div>
                                <?php $rate_avg = STReview::count_review_by_rate(null, 3); ?>
                                <div class="item d-flex align-items-center justify-content-between">
                                    <div class="label">
                                        <?php echo __('Average', 'traveler') ?>
                                    </div>
                                    <div class="progress">
                                        <div class="percent yellow"
                                            style="width: <?php echo TravelHelper::cal_rate($rate_avg, $total_comments) ?>%;"></div>
                                    </div>
                                    <div class="number text-end"><?php echo esc_html($rate_avg); ?></div>
                                </div>
                                <?php $rate_poor = STReview::count_review_by_rate(null, 2); ?>
                                <div class="item d-flex align-items-center justify-content-between">
                                    <div class="label">
                                        <?php echo __('Poor', 'traveler') ?>
                                    </div>
                                    <div class="progress">
                                        <div class="percent orange"
                                            style="width: <?php echo TravelHelper::cal_rate($rate_poor, $total_comments) ?>%;"></div>
                                    </div>
                                    <div class="number text-end"><?php echo esc_html($rate_poor); ?></div>
                                </div>
                                <?php $rate_terible = STReview::count_review_by_rate(null, 1); ?>
                                <div class="item d-flex align-items-center justify-content-between">
                                    <div class="label">
                                        <?php echo __('Terrible', 'traveler') ?>
                                    </div>
                                    <div class="progress">
                                        <div class="percent red"
                                            style="width: <?php echo TravelHelper::cal_rate($rate_terible, $total_comments) ?>%;"></div>
                                    </div>
                                    <div class="number text-end"><?php echo esc_html($rate_terible); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="write-review-form-content" class="mt20">
                        <?php 
                        wp_enqueue_script( 'st-reviews-form' );
                        $commenter = wp_get_current_commenter();
                        $comment_form = [
                            'title_reply'          => __('Leave a review', 'traveler'),
                            'title_reply_to'       => __('Leave a review to %s', 'traveler'),
                            'comment_notes_before' => '',
                            'fields'               => [
                                'author' => '<div class="row"><div class="col-md-6"><div class="form-group">' .
                                    '<label for="author">' . __( 'Name*', 'traveler' ) . '</label>' .
                                    '<input id="author" name="author" type="text" value="' . esc_attr( $commenter[ 'comment_author' ] ) . '" size="30" aria-required="true" class="form-control" />' .
                                    '</div></div>',
                                'email'  => '<div class="col-md-6"><div class="form-group">' .
                                    '<label for="email">' . __( 'Your email address *', 'traveler' ) . '</label>' .
                                    '<input class="form-control" id="email" name="email" type="text" value="' . esc_attr( $commenter[ 'comment_author_email' ] ) . '" size="30" aria-required="true" />' .
                                    '</div></div></div>',
                            ],
                            'label_submit'         => __('Post Review', 'traveler'),
                            'logged_in_as'         => '',
                            'comment_field'        => '<input name="comment_type" value="st_reviews" type="hidden">',
                            'comment_notes_after'  => '',
                            'class_form'           => 'review-form',
                            'id_form'              => 'commentform'
                        ];

                        $comment_form_arg = apply_filters( get_post_type( $post_id ) . '_wp_review_form_args', $comment_form, $post_id );
                        
                        // Force display: block for our custom wrapper
                        echo '<style>#respond.comment-respond { display: block !important; }</style>';
                        comment_form( $comment_form_arg ); 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Review Source Filter -->
            <?php
            $count_all = get_comments(['post_id' => $post_id, 'status' => 'approve', 'parent' => 0, 'count' => true, 'type' => 'st_reviews']);
            
            $count_gyg = get_comments(['post_id' => $post_id, 'status' => 'approve', 'parent' => 0, 'count' => true, 'type' => 'st_reviews', 'meta_key' => 'ota_source', 'meta_value' => 'gyg']);
            
            $count_ta = get_comments(['post_id' => $post_id, 'status' => 'approve', 'parent' => 0, 'count' => true, 'type' => 'st_reviews', 'meta_query' => [
                ['key' => 'ota_source', 'value' => ['TA', 'tripadvisor'], 'compare' => 'IN']
            ]]);

            $count_vt = get_comments(['post_id' => $post_id, 'status' => 'approve', 'parent' => 0, 'count' => true, 'type' => 'st_reviews', 'meta_query' => [
                ['key' => 'ota_source', 'value' => ['vt', 'viator'], 'compare' => 'IN']
            ]]);

            $count_gmb = get_comments(['post_id' => $post_id, 'status' => 'approve', 'parent' => 0, 'count' => true, 'type' => 'st_reviews', 'meta_query' => [
                ['key' => 'ota_source', 'value' => ['gmb', 'google'], 'compare' => 'IN']
            ]]);

            $count_local = $count_all - ($count_gyg + $count_ta + $count_vt + $count_gmb);

            // Get Top Keywords for filtering
            $keywords_raw = get_post_meta($post_id, '_ota_keywords', true);
            $keywords = [];
            if ($keywords_raw) {
                $keywords = array_slice(array_map('trim', explode(',', $keywords_raw)), 0, 10);
            }
            ?>
            <div class="st-review-filter-wrapper mb30">
                <div class="st-review-filter-carousel-container" style="overflow-x: auto; padding: 10px 0; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
                    <ul class="st-review-filter-list d-flex justify-content-start align-items-center" style="list-style: none; padding: 0 2px; gap: 8px; margin: 0; white-space: nowrap;">
                        <li><a href="javascript:void(0);" class="btn btn-outline-primary active" data-source="all" data-keyword=""><?php echo __('All', 'traveler'); ?> (<?php echo $count_all; ?>)</a></li>
                        
                        <?php if ($count_local > 0): ?>
                            <li><a href="javascript:void(0);" class="btn btn-outline-primary" data-source="local" data-keyword="">Website (<?php echo $count_local; ?>)</a></li>
                        <?php endif; ?>
                        
                        <?php if ($count_gyg > 0): ?>
                            <li><a href="javascript:void(0);" class="btn btn-outline-primary" data-source="gyg" data-keyword="">GYG (<?php echo $count_gyg; ?>)</a></li>
                        <?php endif; ?>
                        
                        <?php if ($count_ta > 0): ?>
                            <li><a href="javascript:void(0);" class="btn btn-outline-primary" data-source="TA" data-keyword="">TA (<?php echo $count_ta; ?>)</a></li>
                        <?php endif; ?>

                        <!-- Keyword Buttons -->
                        <?php foreach ($keywords as $kw): if (empty($kw)) continue; ?>
                            <li><a href="javascript:void(0);" class="btn btn-outline-primary btn-keyword" data-source="all" data-keyword="<?php echo esc_attr($kw); ?>">#<?php echo esc_html($kw); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="review-pagination">
                <div id="reviews" class="review-list st-review-list-ajax" 
                     data-post-id="<?php echo $post_id; ?>" 
                     data-paged="1" 
                     data-source="all"
                     data-keyword=""
                     data-comment-per-page="<?php echo (int)get_option('comments_per_page', 10); ?>">
                    <?php
                    $comment_per_page = (int)get_option('comments_per_page', 10);
                    $args = [
                        'number' => $comment_per_page,
                        'post_id' => $post_id,
                        'status' => ['approve'],
                        'orderby' => 'comment_date',
                        'order' => 'DESC',
                        'parent' => 0
                    ];
                    $comments_query = new WP_Comment_Query;
                    $comments = $comments_query->query($args);

                    if ($comments):
                        foreach ($comments as $key => $comment):
                            echo stt_elementorv2()->loadView('services/common/single/review-list', ['comment' => (object)$comment, 'post_type' => 'st_tours']);
                        endforeach;
                    endif;
                    ?>
                </div>
                
                <!-- Hidden Trigger for Autoload -->
                <div id="st-load-more-reviews-trigger" style="height: 10px; margin-top: -50px;"></div>

                <div class="load-more-reviews-wrapper text-center mt20">
                    <a href="javascript:void(0);" class="btn btn-primary btn-load-more-reviews" id="st-btn-load-more-reviews" style="display: none;">
                        <?php echo esc_html__('Load More', 'traveler'); ?>
                        <i class="fa fa-spinner fa-spin d-none"></i>
                    </a>
                    <div id="st-autoload-spinner" class="text-center mt10" style="display: none;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
            
            <style>
                .st-review-filter-list .btn { border-radius: 20px; padding: 5px 20px; font-size: 14px; border: 1px solid #38761d; color: #38761d; background: transparent; transition: all 0.3s ease; }
                .st-review-filter-list .btn.active { background-color: #38761d; color: #fff; }
                .st-review-filter-list .btn:hover:not(.active) { background-color: #f0f0f0; }
                #write-review .btn-secondary { background-color: #ffcc00; color: #303030; border: none; font-weight: 600; padding: 10px 25px; border-radius: 8px; }
                #write-review .btn-secondary:hover { background-color: #e6b800; }
            </style>

            <script>
                jQuery(function($){
                    var loading = false;
                    var container = $('.st-review-list-ajax');
                    var btn = $('#st-btn-load-more-reviews');
                    var autoloadSpinner = $('#st-autoload-spinner');
                    var maxAutoload = 25;

                    function loadMoreReviews(reset = false) {
                        if (loading) return;
                        
                        var paged = reset ? 0 : container.data('paged');
                        var postId = container.data('post-id');
                        var perPage = container.data('comment-per-page');
                        var source = container.data('source');
                        var keyword = container.data('keyword') || '';

                        loading = true;
                        if (!reset && (paged * perPage) >= maxAutoload) {
                            btn.find('i').removeClass('d-none');
                        } else if (!reset) {
                            autoloadSpinner.show();
                        }

                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'klld_load_more_reviews',
                                paged: paged + 1,
                                post_id: postId,
                                source: source,
                                keyword: keyword
                            },
                            success: function(response){
                                if(response.success){
                                    if (reset) container.empty();
                                    
                                    if (response.data.html) {
                                        container.append(response.data.html);
                                        container.data('paged', paged + 1);
                                        
                                        var newCount = (paged + 1) * perPage;
                                        var itemsFound = (response.data.html.match(/comment-item/g) || []).length;
                                        
                                        if (itemsFound < perPage) {
                                            btn.hide();
                                            autoloadSpinner.hide();
                                        } else if (newCount >= maxAutoload) {
                                            btn.show();
                                            autoloadSpinner.hide();
                                        } else {
                                            btn.hide();
                                            autoloadSpinner.hide();
                                        }
                                    } else {
                                        if (reset) container.html('<p class="text-center mt20"><?php echo __('No reviews found for this selection.', 'traveler'); ?></p>');
                                        btn.hide();
                                        autoloadSpinner.hide();
                                    }
                                }
                                btn.find('i').addClass('d-none');
                                loading = false;
                            }
                        });
                    }

                    // Intersection Observer for Autoload
                    var observer;
                    function initObserver() {
                        if (observer) observer.disconnect();
                        if ('IntersectionObserver' in window) {
                            observer = new IntersectionObserver(function(entries) {
                                if (entries[0].isIntersecting) {
                                    var paged = container.data('paged');
                                    var perPage = container.data('comment-per-page');
                                    var keyword = container.data('keyword') || '';
                                    if ((paged * perPage) < maxAutoload && !loading) {
                                        loadMoreReviews();
                                    }
                                }
                            }, { threshold: 0.1 });
                            
                            var trigger = document.getElementById('st-load-more-reviews-trigger');
                            if (trigger) observer.observe(trigger);
                        }
                    }
                    initObserver();

                    btn.on('click', function(e){
                        e.preventDefault();
                        loadMoreReviews();
                    });

                    // Filter Click
                    $('.st-review-filter-list a').on('click', function(e){
                        e.preventDefault();
                        var source = $(this).data('source');
                        var keyword = $(this).data('keyword') || '';
                        
                        $('.st-review-filter-list a').removeClass('active');
                        $(this).addClass('active');
                        
                        container.data('source', source);
                        container.data('keyword', keyword);
                        container.data('paged', 0);
                        loadMoreReviews(true);
                    });

                    // Write a Review Toggle
                    $('.toggle-write-review').on('click', function(e){
                        e.preventDefault();
                        $('.st-review-form').slideToggle();
                        $(this).find('i').toggleClass('stt-icon-arrow-down stt-icon-arrow-up');
                        
                        // Scroll to form if opening
                        if ($(this).find('i').hasClass('stt-icon-arrow-up')) {
                            $('html, body').animate({
                                scrollTop: $("#write-review").offset().top - 100
                            }, 500);
                        }
                    });
                });
            </script>

        </div>
    </div>
<?php  ?>
