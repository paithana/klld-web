<?php
$inner_style = '';
$thumb_id = get_post_thumbnail_id(get_the_ID());
if (!empty($thumb_id)) {
    $img = wp_get_attachment_image_url($thumb_id, 'full');
    $inner_style = Assets::build_css("background-image: url(" . esc_url($img) . ") !important;");
}

$transfer = new STCarTransfer();
$transfer_from = (int)STInput::get('transfer_from','');
$transfer_to = (int)STInput::get('transfer_to','');
$roundtrip = STInput::get('roundtrip','');
$time = $transfer->get_driving_distance($transfer_from, $transfer_to, $roundtrip);
$title = RankMath\Paper\Paper::get()->get_title();
$meta = RankMath\Paper\Paper::get()->get_description();

if(empty($title)){
	$title = get_the_title();
}
if (empty($meta)){
	$meta = get_the_excerpt();
}
if (isset($transfer_from) && !empty($transfer_from) && isset($transfer_to) && !empty($transfer_to)){
	$transfer_text = STInput::get('transfer_from_name','')." - ".STInput::get('transfer_to_name','');
}
if (!empty(get_the_title())){
	$title = get_the_title();
}
?>
<div class="banner <?php echo esc_attr($inner_style) ?>">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <h1 class="tag_h1 d-none d-lg-none"><?php echo $title ?></h1>
					<div class="trf-header-wrap">
						<h2 class="trf-header">
							<?php echo esc_html__($title,'traveler'); ?>
						</h2>
						<p class="trf-description"><?php echo esc_html__($meta,'traveler'); ?></p>
						
					</div>
                </div>
                <div class="st-banner-search-form style_2">
                    <div class="st-search-form-el st-border-radius">
                        <div class="st-search-el search-form-v2">
                            <?php echo stt_elementorv2()->loadView('services/car_transfer/search-form/wrapper'); ?>
                        </div>
                    </div>
					<div class="st-estimate-distance transfer-map-infor d-flex align-items-center justify-content-start justify-content-sm-start justify-content-md-center justify-content-lg-center">
					</div>
                    <div class="st-estimate-distance transfer-map-infor d-flex align-items-center justify-content-start justify-content-sm-start justify-content-md-center justify-content-lg-center">
						
                        <?php if(!empty($time['error_message'])){
                             echo esc_html($time['error_message']);

                         } else {?>
                            <span><?php echo esc_html__('Estimated distance:','traveler');?></span>
                            <span>
                                <?php
                                $hour = ( $time[ 'hour' ] >= 2 ) ? $time[ 'hour' ] . ' ' . esc_html__( 'hours', 'traveler' ) : $time[ 'hour' ] . ' ' . esc_html__( 'hour', 'traveler' );
                                $minute = ( $time[ 'minute' ] >= 2 ) ? $time[ 'minute' ] . ' ' . esc_html__( 'minutes', 'traveler' ) : $time[ 'minute' ] . ' ' . esc_html__( 'minute', 'traveler' );
                                echo esc_html($time['distance']) . __(' km', 'traveler') .' ('. esc_attr( $hour ) . ' ' . esc_attr( $minute ).')';
                                ?>
                            </span>
                        <?php }?>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>