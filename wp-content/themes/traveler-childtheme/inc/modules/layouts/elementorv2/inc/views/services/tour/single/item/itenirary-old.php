<div class="tour-program">
<?php $tour_programs = get_post_meta(get_the_ID(), 'tours_program', true);
if(!empty($tour_programs)){
?>
<div class="st-hr"></div>
<h2 class="st-heading-section" id="st-itinerary">
    <?php echo __('Itinerary', 'traveler'); ?>
</h2>
<div class="st-program-list">
    <?php
        $i = 0;
        foreach ($tour_programs as $k => $v){
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            $title = $f->format($v['title']);
            echo '<script>console.log("' . $title." ". $v . '")</script>';
            $section_id = strtolower(trim(preg_replace('/[^A-Za-z]+/', '-', esc_html($v['title'])), '-'));
            ?>
            <div class="accordion faq st-program style1" id="accordion_<?php echo esc_attr($section_id);?>">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading_<?php echo esc_attr($section_id);?>">
                        <button class="accordion-button<?php echo ($i == 0) ? '' : ' collapsed' ?> " 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#<?php echo esc_attr($section_id)?>" 
                            aria-expanded="true"
                            aria-controls="<?php echo esc_attr($section_id)?>"
                        >
                            <?php echo balanceTags($v['title']);?>
                        </button>
                    </h2>
                    <div id="<?php echo esc_attr($section_id)?>" class="accordion-collapse collapse <?php echo ($i == 0) ? 'show' : '' ?>" 
                        aria-labelledby="heading_<?php echo esc_attr($section_id);?>" 
                        data-bs-parent="#accordion_<?php echo esc_attr($section_id);?>"
                    >
                        <div class="accordion-body">
                            <?php
                                if(isset($v['image']) and !empty($v['image']) and !empty($v['desc'])){

                                    $img = $v['image'];
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <img src="<?php echo esc_url($v['image']) ?>" alt="<?php echo esc_attr($v['title']) ?>" class="img-fluid"/>
                                        </div>
                                        <div class="col-lg-12">
                                            <p class="content-itinerary">
                                            <?php echo balanceTags($v['desc']);?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                }

                                elseif(isset($v['image']) and !empty($v['image']) and empty($v['desc']) ){ 
                                    $img = $v['image'];
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <img src="<?php echo esc_url($v['image']) ?>" alt="<?php echo esc_attr($v['title']) ?>" class="img-fluid"/>
                                        </div>
                                    </div>
                                <?php }
                                else{
                                    echo '<p class="content-itinerary">';
                                    echo balanceTags($v['desc']);
                                    echo '</p>';
                                }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php
            $i++;
        }
    ?>
</div>
<?php 
    }
?>
</div>
