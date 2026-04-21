<?php
get_header();
wp_enqueue_script('filter-car-transfer-js');
?>
    <div id="st-content-wrapper" class="search-result-page st-tours">
        <?php
        echo st()->load_template('layouts/modern/hotel/elements/banner');
        ?>
        <div class="container">
            <div class="st-hotel-result">
                <?php
                    $transfer = new STCarTransfer();
                    $transfer_from = (int)STInput::get('transfer_from','');
                    $transfer_to = (int)STInput::get('transfer_to','');
                    $roundtrip = STInput::get('roundtrip','');
                    $route = $transfer->get_routes($transfer_from, $transfer_to, $roundtrip);
                    $time = $transfer->get_driving_distance($transfer_from, $transfer_to, $roundtrip);
                    if(!empty($route)){
                ?>
                <div class="transfer-map mt20" data-route="<?php echo esc_attr( json_encode($route) ); ?>">
                    <div class="transfer-map-content" style="height: 400px; margin-bottom: 20px;"></div>
                    <div class="transfer-map-infor" style="margin-bottom: 30px; padding: 15px; background: #f9f9f9; border-radius: 5px; font-weight: 500;">
                        <i class="fa fa-clock-o mr10"></i> <?php echo __('Travel time about: ', 'traveler'); ?>
                        <?php
                            $hour = ( $time[ 'hour' ] >= 2 ) ? $time[ 'hour' ] . ' ' . esc_html__( 'hours', 'traveler' ) : $time[ 'hour' ] . ' ' . esc_html__( 'hour', 'traveler' );
                            $minute = ( $time[ 'minute' ] >= 2 ) ? $time[ 'minute' ] . ' ' . esc_html__( 'minutes', 'traveler' ) : $time[ 'minute' ] . ' ' . esc_html__( 'minute', 'traveler' );
                            echo esc_attr( $hour ) . ' ' . esc_attr( $minute ) . ' - ' . $time['distance'] . __('Km', 'traveler');
                        ?>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <?php echo st()->load_template('layouts/modern/car_transfer/elements/sidebar'); ?>
                    <?php
                    $query           = array(
                        'post_type'      => 'st_cars' ,
                        'post_status'    => 'publish' ,
                        's'              => '' ,
                        'orderby' => 'post_modified',
                        'order'   => 'DESC',
                    );
                    global $wp_query , $st_search_query;
                    $car = STCarTransfer::inst();
                    $car->get_search_results();
                    query_posts( $query );
                    $st_search_query = $wp_query;
                    $car->get_search_results_remove_filter();
                    wp_reset_query();
                   
                    echo st()->load_template('layouts/modern/car_transfer/elements/content');
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
echo st()->load_template('layouts/modern/car_transfer/elements/popup/date');
get_footer();
