   <!--Tour highlight-->
   <?php
    $tours_highlight = get_post_meta(get_the_ID(), 'tours_highlight', true);
    if (!empty($tours_highlight)) {
        $arr_highlight = explode("\n", trim($tours_highlight));
        ?>
	<div class="st-hr large"></div>
        <div class="st-highlight">
            <h3 class="st-heading-section st-section-title"><?php echo __('What to Bring', 'traveler'); ?></h3>
            <ul>
                <?php
                if (!empty($arr_highlight)) {
                    foreach ($arr_highlight as $k => $v) {
                        echo '<li>' . esc_html($v) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    <?php } ?>
    <!--End Tour highlight-->
