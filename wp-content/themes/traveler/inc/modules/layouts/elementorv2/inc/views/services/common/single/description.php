<div class="st-description" id="st-description">
    
    <?php
    if(isset($title)){
        echo '<h2 class="st-heading-section">'. esc_html($title) .'</h2>';
    } else { ?>
        <h2 class="st-heading-section">
        <?php 
            $get_posttype = get_post_type(get_the_ID());
            switch ($get_posttype) {
                case 'st_hotel':
                    echo __('About this hotel','traveler');
                    break;
                case 'st_tours':
                    echo __('About this tour','traveler');
                    break;
                case 'st_cars':
                    echo __('About this car','traveler');
                    break;
                case 'st_rental':
                    echo __('About this rental','traveler');
                    break;
                case 'st_activity':
                    echo __('About this activity','traveler');
                    break;
                case 'hotel_room':
                    echo __('About this room','traveler');
                    break;
                default:
                    echo __('About this hotel','traveler');
                    break;
            }
        ?>
    </h2>
    <?php }
    $content = apply_filters('the_content', get_the_content());
    if (!empty($content)) {
        $paragraphs = explode('</p>', $content);
        if (count($paragraphs) > 1) {
            $first_p = $paragraphs[0] . '</p>';
            unset($paragraphs[0]);
            $rest_content = implode('</p>', $paragraphs);
            ?>
            <div class="st-description-content" id="st-description-text">
                <div class="description-first-p">
                    <?php echo $first_p; ?>
                </div>
                <div class="description-more" style="display: none;">
                    <?php echo $rest_content; ?>
                </div>
                <a href="javascript:void(0);" class="st-read-more" 
                   style="color: #0ea5e9; font-weight: 600; text-decoration: none; display: inline-block; margin-top: 10px;"
                   onclick="var more=this.previousElementSibling; if(more.style.display==='none'){ more.style.display='block'; this.innerText='<?php echo __('Read less', 'traveler'); ?>'; } else { more.style.display='none'; this.innerText='<?php echo __('Read more', 'traveler'); ?>'; }">
                    <?php echo __('Read more', 'traveler'); ?>
                </a>
            </div>
            <?php
        } else {
            echo $content;
        }
    }
    ?>
</div>