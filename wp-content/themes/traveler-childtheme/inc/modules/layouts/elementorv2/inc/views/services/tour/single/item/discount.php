 <!--Table Discount group -->
 <?php
$discount_by_adult = !empty(get_post_meta(get_the_ID(),'discount_by_adult')) ? get_post_meta(get_the_ID(),'discount_by_adult',true) : '';
$discount_by_child = !empty(get_post_meta(get_the_ID(),'discount_by_child')) ? get_post_meta(get_the_ID(),'discount_by_child',true) : '';
if(!empty($discount_by_adult) || !empty($discount_by_child)){
    $discount_by_people_type = !empty(get_post_meta(get_the_ID(),'discount_by_people_type')) ? get_post_meta(get_the_ID(),'discount_by_people_type',true) : '';
    ?>
    <div class="st-hr large"></div>
    <div class="accordion-item stt-discount">
        <h2 class="st-heading-section">
            <?php echo __('Bulk discount', 'traveler') .' (by '.esc_html($discount_by_people_type).')'; ?>
        </h2>
        <div id="collapseDiscount">
            <div class="accordion-body">
                
                 <?php if(!empty($discount_by_adult)){?>
                    <h5><?php echo esc_html__('Bulk discount adult','traveler'); ?></h5>
                    <div class="st-program">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col"><?php echo esc_html__('Discount group','traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('From adult','traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('To adult', 'traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('Value', 'traveler');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach($discount_by_adult as $key=>$discount_adult){
                                        if(!empty($discount_adult['title'])):
                                        ?>
                                        <tr>
                                            <th scope="row"><?php echo intval($key + 1)?></th>
                                            <td><?php echo esc_html($discount_adult['title']);?></td>
                                            <td><?php echo esc_html($discount_adult['key']);?></td>
                                            <td><?php echo esc_html($discount_adult['key_to']);?></td>
                                            <td><?php echo esc_html($discount_adult['value']);?></td>
                                        </tr>
                                    <?php 
                                        endif;   
                                    }
                                ?>
                                
                            </tbody>
                        </table>
                    </div>
                    <?php }?>
                    <?php if(!empty($discount_by_child)){?>
                    <h5 class="discount-child"><?php echo esc_html__('Bulk discount children','traveler'); ?></h5>
                    <div class="st-program">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col"><?php echo esc_html__('Discount group','traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('From children','traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('To children', 'traveler');?></th>
                                    <th scope="col"><?php echo esc_html__('Value', 'traveler');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach($discount_by_child as $key=>$discount_child){?>
                                        <tr>
                                            <th scope="row"><?php echo intval($key + 1)?></th>
                                            <td><?php echo esc_html($discount_child['title']);?></td>
                                            <td><?php echo esc_html($discount_child['key']);?></td>
                                            <td><?php echo esc_html($discount_child['key_to']);?></td>
                                            <td><?php echo esc_html($discount_child['value']);?></td>
                                        </tr>
                                    <?php }
                                ?>
                                
                            </tbody>
                        </table>
                    </div>
                    <?php }?>
            </div>
        </div>
    </div>
<?php }?>
<!--End Table Discount group -->