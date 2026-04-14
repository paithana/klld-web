   <!--Tour highlight-->
   <?php
    $tours_highlight = get_post_meta(get_the_ID(), 'tour_whattobring', true);
    if (!empty($tours_highlight)) {
        $arr_highlight = explode("\n", trim($tours_highlight));
        ?>
        <div class="st-whattobring">
            <h3 class="st-section-title"><?php echo __('Please bring', 'traveler'); ?></h3>
            <ul>
                <?php
                if (!empty($arr_whattobring)) {
                    foreach ($arr_whattobring as $k => $v) {
                        echo '<li>' . esc_html($v) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    <?php } ?>
    <!--End Tour highlight-->