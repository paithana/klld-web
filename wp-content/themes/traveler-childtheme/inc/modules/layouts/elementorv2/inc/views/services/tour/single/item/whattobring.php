<?php
$what_to_bring = get_post_meta(get_the_ID(), 'tour_whattobring', true);
if (!empty($what_to_bring)) {
    // Standardize delimiters (newline or comma)
    $items = preg_split('/[\n,]+/', trim($what_to_bring));
    $items = array_filter(array_map('trim', $items));

    if (!empty($items)) {
        ?>
        <div class="st-whattobring-v2 mb40">
            <h3 class="st-section-title" style="margin-bottom: 20px; font-size: 20px; font-weight: 700;">
                <i class="fa fa-suitcase mr10" style="color: #0b7016;"></i><?php echo __('What to bring', 'traveler'); ?>
            </h3>
            <div class="whattobring-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
                <?php
                foreach ($items as $item) {
                    if (empty($item)) continue;
                    
                    // Smart Icon Mapping (FontAwesome 4.7 compatible)
                    $icon = 'fa-check-circle-o';
                    $lower_item = mb_strtolower($item);
                    
                    if (strpos($lower_item, 'sun') !== false) $icon = 'fa-sun-o';
                    elseif (strpos($lower_item, 'towel') !== false) $icon = 'fa-bath';
                    elseif (strpos($lower_item, 'swim') !== false) $icon = 'fa-life-ring';
                    elseif (strpos($lower_item, 'camera') !== false) $icon = 'fa-camera';
                    elseif (strpos($lower_item, 'shoe') !== false || strpos($lower_item, 'footwear') !== false || strpos($lower_item, 'flip') !== false) $icon = 'fa-tag'; 
                    elseif (strpos($lower_item, 'money') !== false || strpos($lower_item, 'cash') !== false) $icon = 'fa-money';
                    elseif (strpos($lower_item, 'hat') !== false || strpos($lower_item, 'cap') !== false) $icon = 'fa-modx'; 
                    elseif (strpos($lower_item, 'water') !== false || strpos($lower_item, 'drink') !== false) $icon = 'fa-tint';
                    elseif (strpos($lower_item, 'bug') !== false || strpos($lower_item, 'spray') !== false || strpos($lower_item, 'insect') !== false) $icon = 'fa-shield';
                    elseif (strpos($lower_item, 'dry') !== false || strpos($lower_item, 'bag') !== false) $icon = 'fa-briefcase';
                    ?>
                    <div class="wtb-item d-flex align-items-center" style="background: #f8fafc; padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; transition: transform 0.2s;">
                        <i class="fa <?php echo $icon; ?>" style="color: #0b7016; font-size: 16px; margin-right: 12px; width: 20px; text-align: center;"></i>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3;"><?php echo esc_html($item); ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <style>
            .wtb-item:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
            @media (max-width: 575px) {
                .whattobring-grid { grid-template-columns: 1fr 1fr; }
            }
        </style>
        <?php
    }
}
?>