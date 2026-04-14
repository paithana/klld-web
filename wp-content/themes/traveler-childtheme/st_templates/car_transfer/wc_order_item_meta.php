<?php
    /**
     * Created by PhpStorm.
     * User: MSI
     * Date: 14/07/2015
     * Time: 3:17 CH
     */
    $item_data = isset( $item[ 'item_meta' ] ) ? $item[ 'item_meta' ] : [];

?>
<ul class="wc-order-item-meta-list">

    <?php if ( isset( $item_data[ '_st_pick_up' ] ) and $item_data[ '_st_pick_up' ] ) { ?>
        <li>
            <span class="meta-label"><?php _e( 'Pick-up:', 'traveler' ) ?></span>
            <span class="meta-data"><?php
                    if ( $item_data[ '_st_pick_up' ] ) {
                        echo esc_html($item_data[ '_st_pick_up' ]);
                    }

                ?></span>
        </li>
        <?php

    } ?>
    
    <?php if ( isset( $item_data[ '_st_drop_off' ] ) and $item_data[ '_st_drop_off' ] ) { ?>
        <li>
            <span class="meta-label"><?php _e( 'Drop-off:', 'traveler' ) ?></span>
            <span class="meta-data"><?php
                    if ( $item_data[ '_st_drop_off' ] ) {
                        echo esc_html($item_data[ '_st_drop_off' ]);
                    }
                ?>
        </span>
        </li>
        <?php

    } ?>
                <?php 
                    if(!empty($item_data['_st_roundtrip']) && $item_data['_st_roundtrip'] === 'yes'){ ?>
                        <li><?php echo "Transfer: <b>Roundtrip</b>" ?></li><?php
                    }
                    if( $item_data['_st_roundtrip'] === 'no'){ ?>
                        <li><?php echo "Transfer: <b>Oneway</b>" ?></li><?php
                    }
                ?>
    <?php if ( isset( $item_data[ '_st_check_in_timestamp' ] ) ): ?>
        <li>
            <span class="meta-label"><?php _e( 'Arrival Date:', 'traveler' ) ?></span>
            <span
                class="meta-data"><?php echo date_i18n( TravelHelper::getDateFormat() . ' ' . get_option( 'time_format' ), $item_data[ '_st_check_in_timestamp' ] ) ?>
                
            </span>
        </li>
    <?php endif; ?>
        <?php if (!empty($item_data['_st_roundtrip']) && $item_data['_st_roundtrip'] === 'yes'): ?>
        <li>
            <span class="meta-label"><?php _e( 'Departure Date:', 'traveler' ) ?></span>
            <span
                class="meta-data"><?php echo date_i18n( TravelHelper::getDateFormat() . ' ' . get_option( 'time_format' ), $item_data[ '_st_check_out_timestamp' ] ) ?>
                
            </span>
        </li>
    <?php endif; ?>
    <?php
        if ( isset( $item_data[ '_st_distance' ] ) ): ?>
            <li>
                <span class="meta-label"><?php _e( 'Distance:', 'traveler' ) ?></span>
                <span
                    class="meta-data">
                    <?php
                        $time   = $item_data[ '_st_distance' ];
                        $hour   = ( $time[ 'hour' ] >= 2 ) ? $time[ 'hour' ] . ' ' . esc_html__( 'hours', 'traveler' ) : $time[ 'hour' ] . ' ' . esc_html__( 'hour', 'traveler' );
                        $minute = ( $time[ 'minute' ] >= 2 ) ? $time[ 'minute' ] . ' ' . esc_html__( 'minutes', 'traveler' ) : $time[ 'minute' ] . ' ' . esc_html__( 'minute', 'traveler' );
                        echo esc_attr( $hour ) . ' ' . esc_attr( $minute ) . ' - ' . esc_html($time[ 'distance' ]) . __( 'Km', 'traveler' );
                    ?>
                </span>
            </li>
        <?php endif; ?>

        <?php //extra
        if ( isset( $item_data[ '_st_extras' ] ) ): ?>
            <li>
                <span class="meta-label"><?php _e( 'Extra:', 'traveler' ) ?></span>
                <span
                    class="meta-data">
                    <?php
                        $extras = $item_data['_st_extras'];
                        echo "</br>";
                        foreach ( $extras as $key => $data ) {
                            echo "&nbsp;&nbsp;&nbsp;- " . esc_html($extras[$key]['title']) . ": " . TravelHelper::format_money( $extras[$key]['price'] ) ." (x" . esc_html($extras[$key]['number']) . ")" . " <br>";
                        }
                        echo "</br>";
                        
                    ?>
                </span>
            </li>
        <?php endif; ?>
</ul>