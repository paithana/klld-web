<?php
get_header();

wp_enqueue_script('filter-car-transfer');
?>
    <div id="st-content-wrapper" class="st-style-elementor search-result-page" data-layout="3" data-format="popup">
        <?php echo stt_elementorv2()->loadView('services/car_transfer/components/banner'); ?>
        <div class="container">
            <div class="st-results st-hotel-result st-search-tour">
                <div class="row">
                    <?php
                    echo stt_elementorv2()->loadView('services/car_transfer/components/sidebar', ['format' => 'popupmap']);
                    
                    global $wp_query , $st_search_query;
                    $car = STCarTransfer::inst();
                    $car->get_search_results();

                    $query_args = array(
                        'post_type'      => 'st_cars' ,
                        'post_status'    => 'publish' ,
                        's'              => '' ,
                        'orderby' => 'post_modified',
                        'order'   => 'DESC',
                        'meta_query' => array(
                            array(
                                'key'     => 'car_type',
                                'value'   => 'car_transfer',
                                'compare' => '=',
                            ),
                        ),
                    );

                    query_posts( $query_args );
                    $st_search_query = $wp_query;

                    // Fallback: If no results found for specific route, show all available transfers
                    if (!$st_search_query || !$st_search_query->have_posts()) {
                        $car->get_search_results_remove_filter();
                        query_posts( $query_args );
                        $st_search_query = $wp_query;
                        // Alert will be shown in content.php via the is_valid_route check
                    }

                    $car->get_search_results_remove_filter();
                    wp_reset_query();

                    if (TravelHelper::is_wpml()) {
                        $current_lang = 'en';
                        if (defined('ICL_LANGUAGE_CODE')) {
                            $current_lang = ICL_LANGUAGE_CODE;
                        }
                        global $sitepress;
                        $sitepress->switch_lang($current_lang, true);
                    }

                    echo stt_elementorv2()->loadView('services/car_transfer/components/content');
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();