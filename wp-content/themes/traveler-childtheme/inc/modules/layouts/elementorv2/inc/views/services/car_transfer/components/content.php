<?php
$style = get_post_meta(get_the_ID(), 'rs_style', true);
if (empty($style))
    $style = 'grid';
if(wp_is_mobile()){
$style = 'grid';
}
$style = 'grid';

global $wp_query, $st_search_query;
if ($st_search_query) {
    $query = $st_search_query;
} else $query = $wp_query;

if(empty($format))
    $format = '';

if(empty($layout))
    $layout = '';
?>
<div class="col-sm-12 col-md-12 col-lg-12">
    <?php echo stt_elementorv2()->loadView('services/hotel/components/toolbar', ['style' => $style, 'has_filter' => false, 'post_type' => 'st_car_transfer', 'service_text' => __('New','traveler')]); ?>
    
    <?php
    $transfer_from = (int)STInput::get('transfer_from');
    $transfer_to = (int)STInput::get('transfer_to');
    if ($transfer_from > 0 && $transfer_to > 0) {
        global $wpdb;
        // Check both directions (A->B and B->A) since most journeys are returnable
        $check_transfer = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}st_journey_car WHERE (transfer_from = %d AND transfer_to = %d) OR (transfer_from = %d AND transfer_to = %d) LIMIT 1", $transfer_from, $transfer_to, $transfer_to, $transfer_from));
        if (!$check_transfer) {
            echo '<div class="alert alert-warning text-center mt20" style="background-color: #fff9e6; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">' . 
                 '<strong>' . __('Note:', 'traveler') . '</strong> ' . 
                 __('No direct transfers found for this specific route. Showing our full vehicle range with estimated pricing:', 'traveler') . 
                 '</div>';
        }
    }
    ?>

    <div id="modern-search-result" class="modern-search-result" data-layout="4">
        <?php echo st()->load_template('layouts/elementor/common/loader', 'content'); ?>
        <div class="service-list-wrapper service-tour list-style">
        <?php
        if($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo '<div class="col-12 item-service">';
                if(wp_is_mobile()){
                    echo stt_elementorv2()->loadView('services/car_transfer/loop/grid');
                } else {
                    echo stt_elementorv2()->loadView('services/car_transfer/loop/list');
                }
                
                echo '</div>';
            }
        }else{
            echo st()->load_template('layouts/modern/car_transfer/elements/loop/none');
        }
        wp_reset_query();
        echo '</div>';
        ?>
    </div>
    <div class="pagination moderm-pagination" id="moderm-pagination">
        <?php echo TravelHelper::paging(false, false); ?>
    </div>
</div>

<?php         if (have_posts()) : while (have_posts()) : the_post(); 
        the_content(); 
        endwhile; endif; ?>