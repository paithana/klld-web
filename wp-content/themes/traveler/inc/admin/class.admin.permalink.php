<?php
    /**
     * @package    WordPress
     * @subpackage Traveler
     * @since      1.0
     *
     * Class STAdminPermalink
     *
     * Created by ShineTheme
     *
     */
    if ( !class_exists( 'STAdminPermalink' ) ) {

        class STAdminPermalink extends STAdmin
        {
            public function __construct()
            {
                //parent::__construct();
                $this->settings_init();
            }

            public function settings_init()
            {
                add_action( 'admin_init', [ $this, 'conditonal_includes' ] );
                add_action( 'current_screen', [ $this, 'settings_save' ] );
            }

            public function conditonal_includes()
            {
                add_settings_section( 'traver-permalink', __( 'Traveler permalink base', 'traveler' ), [ $this, 'settings' ], 'permalink' );
            }

            /**
             * Show the settings.
             */
            public function settings()
            {
                $hotel_permalink       = get_option( 'hotel_permalink', 'st_hotel' );
                $hotel_room_permalink  = get_option( 'hotel_room_permalink', 'hotel_room' );
                $rental_permalink      = get_option( 'rental_permalink', 'st_rental' );
                $rental_room_permalink = get_option( 'rental_room_permalink', 'rental_room' );
                $car_permalink         = get_option( 'car_permalink', 'st_car' );
                $activity_permalink    = get_option( 'activity_permalink', 'st_activity' );
                $tour_permalink        = get_option( 'tour_permalink', 'st_tour' );
                $location_permalink    = get_option( 'location_permalink', 'st_location' );
                $author_permalink    = get_option( 'author_permalink', 'author' );
                ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label><?php _e( 'Hotel Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="hotel_permalink" type="text"
                                   value="<?php echo esc_attr( $hotel_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e( 'Hotel Room Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="hotel_room_permalink" type="text"
                                   value="<?php echo esc_attr( $hotel_room_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e( 'Rental Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="rental_permalink" type="text"
                                   value="<?php echo esc_attr( $rental_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
					<?php
					$new_layout = st()->get_option( 'st_theme_style', 'modern' );
					if ( $new_layout == 'classic' ) : ?>
						<tr>
							<th><label><?php _e( 'Rental Room Custom Base', 'traveler' ); ?></label></th>
							<td>
								<input name="rental_room_permalink" type="text"
									value="<?php echo esc_attr( $rental_room_permalink ); ?>" class="regular-text code">
							</td>
						</tr>
					<?php endif; ?>
                    <tr>
                        <th><label><?php _e( 'Car Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="car_permalink" type="text" value="<?php echo esc_attr( $car_permalink ); ?>"
                                   class="regular-text code">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e( 'Tour Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="tour_permalink" type="text" value="<?php echo esc_attr( $tour_permalink ); ?>"
                                   class="regular-text code">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e( 'Activity Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="activity_permalink" type="text"
                                   value="<?php echo esc_attr( $activity_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
                    <?php do_action('st_add_custom_permalink'); ?>
                    <tr>
                        <th><label><?php _e( 'Location Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="location_permalink" type="text"
                                   value="<?php echo esc_attr( $location_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e( 'Author Custom Base', 'traveler' ); ?></label></th>
                        <td>
                            <input name="author_permalink" type="text"
                                   value="<?php echo esc_attr( $author_permalink ); ?>" class="regular-text code">
                        </td>
                    </tr>
                    </tbody>
					<?php wp_nonce_field( 'settings_option_tree', 'permalink_nonce' ); ?>
                </table>
                <?php
            }

            public function settings_save($screen)
            {
				if ( !is_admin() ) {
					return;
				}

				if ( $screen->id !== 'options-permalink' ) {
					return;
				}

				if ( ! current_user_can( 'manage_options' ) ) {
					echo json_encode(array(
						'status' => false,
						'err' => __("You are not allowed", 'traveler')
					));
					die;
				}

				if ( isset( $_POST['permalink_nonce'] ) && ! wp_verify_nonce( $_REQUEST[ 'permalink_nonce' ], 'settings_option_tree' ) ) {
					echo json_encode(array(
						'status' => false,
						'err' => __("You are not allowed", 'traveler')
					));
					die;
				}

                if ( isset( $_POST[ 'hotel_permalink' ] )
                    || isset( $_POST[ 'hotel_room_permalink' ] )
                    || isset( $_POST[ 'rental_permalink' ] )
                    || isset( $_POST[ 'rental_room_permalink' ] )
                    || isset( $_POST[ 'car_permalink' ] )
                    || isset( $_POST[ 'tour_permalink' ] )
                    || isset( $_POST[ 'activity_permalink' ] )
                    || isset( $_POST[ 'location_permalink' ] )
                    || isset( $_POST[ 'author_permalink' ] )
                ) {
                    update_option( 'hotel_permalink', $_POST[ 'hotel_permalink' ] );
                    update_option( 'hotel_room_permalink', $_POST[ 'hotel_room_permalink' ] );
                    update_option( 'rental_permalink', $_POST[ 'rental_permalink' ] );
                    // update_option( 'rental_room_permalink', $_POST[ 'rental_room_permalink' ] );
                    update_option( 'car_permalink', $_POST[ 'car_permalink' ] );
                    update_option( 'tour_permalink', $_POST[ 'tour_permalink' ] );
                    update_option( 'activity_permalink', $_POST[ 'activity_permalink' ] );
                    update_option( 'location_permalink', $_POST[ 'location_permalink' ] );
                    update_option( 'author_permalink', $_POST[ 'author_permalink' ] );
                }
            }

        }

        new STAdminPermalink();
    }
