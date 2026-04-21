<?php if (comments_open() and st()->get_option('activity_tour_review') == 'on') {
    $count_review = get_comment_count($post_id)['approved'];
    ?>
    <div class="st-section-single" id="st-reviews">
        <h2 class="st-heading-section">
            <?php echo esc_html__('Reviews', 'traveler') ?>
        </h2>
        <div id="reviews" class="st-reviews">
            <div class="st-review-form" style="display: none;">
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
                            <?php $total = get_comments_number(); ?>
                            <?php $rate_exe = STReview::count_review_by_rate(null, 5); ?>
                            <div class="item d-flex align-items-center justify-content-between">
                                <div class="label">
                                    <?php echo esc_html__('Excellent', 'traveler') ?>
                                </div>
                                <div class="progress">
                                    <div class="percent green"
                                        style="width: <?php echo TravelHelper::cal_rate($rate_exe, $total) ?>%;"></div>
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
                                        style="width: <?php echo TravelHelper::cal_rate($rate_good, $total) ?>%;"></div>
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
                                        style="width: <?php echo TravelHelper::cal_rate($rate_avg, $total) ?>%;"></div>
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
                                        style="width: <?php echo TravelHelper::cal_rate($rate_poor, $total) ?>%;"></div>
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
                                        style="width: <?php echo TravelHelper::cal_rate($rate_terible, $total) ?>%;"></div>
                                </div>
                                <div class="number text-end"><?php echo esc_html($rate_terible); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="write-review-form-content" class="mt20">
                    <?php TravelHelper::comment_form(); ?>
                </div>
            </div>

            <div class="review-pagination">
                <div class="summary text-center">
                    <?php
                    $comments_count = wp_count_comments(get_the_ID());
                    $total = (int)$comments_count->approved;
                    $comment_per_page = (int)get_option('comments_per_page', 10);
                    $paged = (int)STInput::get('comment_page', 1);
                    $from = $comment_per_page * ($paged - 1) + 1;
                    $to = ($paged * $comment_per_page < $total) ? ($paged * $comment_per_page) : $total;
                    ?>
                    <?php comments_number(__('0 review on this Tour', 'traveler'), __('1 review on this Tour', 'traveler'), __('% reviews on this Tour', 'traveler')); ?>
                </div>
                <div id="reviews" class="review-list st-review-list-ajax" 
                     data-post-id="<?php echo get_the_ID(); ?>" 
                     data-paged="1" 
                     data-total-pages="<?php echo ceil($total / $comment_per_page); ?>"
                     data-comment-per-page="<?php echo $comment_per_page; ?>">
                    <?php
                    $offset = ($paged - 1) * $comment_per_page;
                    $args = [
                        'number' => $comment_per_page,
                        'offset' => $offset,
                        'post_id' => get_the_ID(),
                        'status' => ['approve'],
                        'orderby' => 'comment_date',
                        'order' => 'DESC'
                    ];
                    $comments_query = new WP_Comment_Query;
                    $comments = $comments_query->query($args);

                    if ($comments):
                        foreach ($comments as $key => $comment):
                            echo stt_elementorv2()->loadView('services/common/single/review-list',['comment' => (object)$comment, 'post_type' => 'st_tours']);
                        endforeach;
                    endif;
                    ?>
                </div>
                
                <div id="st-load-more-reviews-trigger" style="height: 10px; margin-top: -50px;"></div>

                <?php if ($total > $comment_per_page): ?>
                    <div class="load-more-reviews-wrapper text-center mt20">
                        <a href="javascript:void(0);" class="btn btn-primary btn-load-more-reviews" id="st-btn-load-more-reviews" style="display: none;">
                            <?php echo esc_html__('Load More', 'traveler'); ?>
                            <i class="fa fa-spinner fa-spin d-none"></i>
                        </a>
                        <div id="st-autoload-spinner" class="text-center mt10" style="display: none;">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <script>
                jQuery(function($){
                    var loading = false;
                    var container = $('.st-review-list-ajax');
                    var btn = $('#st-btn-load-more-reviews');
                    var autoloadSpinner = $('#st-autoload-spinner');
                    var maxAutoload = 50;

                    function loadMoreReviews() {
                        if (loading) return;
                        
                        var paged = container.data('paged');
                        var totalPages = container.data('total-pages');
                        var postId = container.data('post-id');
                        var perPage = container.data('comment-per-page');
                        var currentCount = paged * perPage;

                        if (paged < totalPages) {
                            loading = true;
                            if (currentCount >= maxAutoload) {
                                btn.find('i').removeClass('d-none');
                            } else {
                                autoloadSpinner.show();
                            }

                            $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',
                                data: {
                                    action: 'klld_load_more_reviews',
                                    paged: paged + 1,
                                    post_id: postId
                                },
                                success: function(response){
                                    if(response.success){
                                        container.append(response.data.html);
                                        container.data('paged', paged + 1);
                                        
                                        var newCount = (paged + 1) * perPage;
                                        if ((paged + 1) >= totalPages) {
                                            btn.hide();
                                            autoloadSpinner.hide();
                                        } else if (newCount >= maxAutoload) {
                                            btn.show();
                                            autoloadSpinner.hide();
                                        }
                                    }
                                    btn.find('i').addClass('d-none');
                                    loading = false;
                                }
                            });
                        }
                    }

                    // Intersection Observer for Autoload
                    if ('IntersectionObserver' in window) {
                        var observer = new IntersectionObserver(function(entries) {
                            if (entries[0].isIntersecting) {
                                var paged = container.data('paged');
                                var perPage = container.data('comment-per-page');
                                if ((paged * perPage) < maxAutoload) {
                                    loadMoreReviews();
                                }
                            }
                        }, { threshold: 0.5 });
                        
                        var trigger = document.getElementById('st-load-more-reviews-trigger');
                        if (trigger) observer.observe(trigger);
                    }

                    btn.on('click', function(e){
                        e.preventDefault();
                        loadMoreReviews();
                    });

                    // Write a Review Toggle
                    $('.toggle-write-review').on('click', function(e){
                        e.preventDefault();
                        $('.st-review-form').slideToggle();
                        $(this).find('i').toggleClass('stt-icon-arrow-down stt-icon-arrow-up');
                    });
                });
            </script>

            <?php
            if (comments_open($post_id)) {
                ?>
                <div id="write-review" class="mt20">
                    <h4 class="heading text-center">
                        <a href="javascript: void(0)" class="btn btn-secondary toggle-write-review f16">
                            <?php echo __('Write a review', 'traveler') ?>
                            <i class="stt-icon-arrow-down ml5"></i>
                        </a>
                    </h4>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
<?php } ?>
