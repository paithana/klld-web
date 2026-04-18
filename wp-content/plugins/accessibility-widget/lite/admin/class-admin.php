<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.cookieyes.com/
 * @since      3.0.0
 *
 * @package    CookieYes\AccessibilityWidget\Lite\Admin
 */

namespace CookieYes\AccessibilityWidget\Lite\Admin;
use CookieYes\AccessibilityWidget\Lite\Includes\Activator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AccessibilityWidget
 * @author     AccessibilityWidget <info@cookieyes.com>
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Admin modules of the plugin
	 *
	 * @var array
	 */
	private static $modules;

	/**
	 * Currently active modules
	 *
	 * @var array
	 */
	private static $active_modules;

	/**
	 * Existing modules
	 *
	 * @var array
	 */
	public static $existing_modules;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		self::$modules     = $this->get_default_modules();
		$this->load();
		$this->load_modules();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_print_scripts', array( $this, 'hide_admin_notices' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'handle_review_notice_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_global_styles' ) );

		// add_filter( 'admin_body_class', array( $this, 'admin_body_classes' ) );
	}

	/**
	 * Load activator on each load.
	 *
	 * @return void
	 */
	public function load() {
		Activator::init();
		if ( false === get_option( 'cya11y_review_install_date', false ) ) {
			add_option( 'cya11y_review_install_date', time() );
		}
	}

	/**
	 * Get the default modules array
	 *
	 * @return array
	 */
	public function get_default_modules() {
		$modules = array(
			'settings',
		);
		return $modules;
	}

	/**
	 * Get the active admin modules
	 *
	 * @return void
	 */
	public function get_active_modules() {
	}
	/**
	 * Load all the modules
	 *
	 * @return void
	 */
	public function load_modules() {
		foreach ( self::$modules as $module ) {
			$parts      = explode( '_', $module );
			$class      = implode( '_', $parts );
			$class_name = 'CookieYes\AccessibilityWidget\Lite\\Admin\\Modules\\' . ucfirst( $module ) . '\\' . ucfirst( $class );

			if ( class_exists( $class_name ) ) {
				$module_obj = new $class_name( $module );
				if ( $module_obj instanceof $class_name ) {
					if ( $module_obj->is_active() ) {
						self::$active_modules[ $module ] = true;
					}
				}
			}
		}
	}

	/**
	 * Register the global stylesheets for the admin area (loaded on all WP admin pages).
	 *
	 * @since    3.0.0
	 */
	public function enqueue_global_styles() {
		wp_enqueue_style( $this->plugin_name . '-admin-notice', plugin_dir_url( __FILE__ ) . 'css/admin-notice.css', array(), $this->version );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'app/dist/assets/index.css', array(), $this->version );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-app', plugin_dir_url( __FILE__ ) . 'app/dist/assets/index.js', array(), $this->version, true );
		wp_localize_script(
			$this->plugin_name . '-app',
			'cyA11yGlobals',
			array(
				'api' => array(
					'endpoint' => rest_url( 'cya11y/v1/' ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
				),
				'site' => array(
					'url' => home_url( '/' ),
				),
				'reviewBanner' => array(
					'installDate' => absint( get_option( 'cya11y_review_install_date', 0 ) ),
					'reviewUrl'   => 'https://wordpress.org/support/plugin/accessibility-widget/reviews/#new-post',
				),
			)
		);
	}

	/**
	 * Register main menu and sub menus
	 *
	 * @return void
	 */
	public function admin_menu() {
		$capability = 'manage_options';
		$slug       = 'accessibility-widget';

		$hook = add_menu_page(
			__( 'Accessibility Widget', 'accessibility-widget' ),
			__( 'AccessYes', 'accessibility-widget' ),
			$capability,
			$slug,
			array( $this, 'menu_page_template' ),
			'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDMiIGhlaWdodD0iNDkiIHZpZXdCb3g9IjAgMCA0MyA0OSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgY2xpcC1wYXRoPSJ1cmwoI2NsaXAwXzMwN18xNSkiPgo8cGF0aCBkPSJNMTkuNDQ3OCAxMy4zMDA5QzIyLjk1OTcgMTMuMzAwOSAyNS44MDc4IDEwLjQ3OTEgMjUuODA3OCA2Ljk5OTQ5VjYuODA1QzI1LjgwNzggMy4zMjU0MSAyMi45NTk3IDAuNTAzNTQgMTkuNDQ3OCAwLjUwMzU0QzE1LjkzNTggMC41MDM1NCAxMy4wODc3IDMuMzI1NDEgMTMuMDg3NyA2LjgwNVY2Ljk5OTQ5QzEzLjA4NzcgMTAuNDc5MSAxNS45MzU4IDEzLjMwMDkgMTkuNDQ3OCAxMy4zMDA5WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTAgNDIuODI0NEw2LjM2NzIgNDguNDk5OUwxOS4xNDggMzQuNDI5NUwxMi44MzQzIDI4LjY5MzhMMCA0Mi44MjQ0WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTQzIDExLjk1NzJMMzcuMDQzMyA1Ljg1NzNMMjAuMTA0NSAyMi4wOTg5TDExLjM0NiAxMy44MjA4TDUuNDY0MjMgMTkuOTI3N0wyMC4zMjk0IDMzLjY5NEwyMC4zODI5IDMzLjY0NDVMMjAuMzkgMzMuNjU1MUwyNS4xMjYyIDI4Ljk4MzlMMjEuMzA3MyAzNC42NTU5TDMyLjg5OTYgNDguMzkzOUwzOS40NTU5IDQyLjkzMDVMMjYuNTUzOCAyNy43Mjg1TDQzIDExLjk1NzJaIiBmaWxsPSJ3aGl0ZSIvPgo8L2c+CjxkZWZzPgo8Y2xpcFBhdGggaWQ9ImNsaXAwXzMwN18xNSI+CjxyZWN0IHdpZHRoPSI0MyIgaGVpZ2h0PSI0OCIgZmlsbD0id2hpdGUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMC41KSIvPgo8L2NsaXBQYXRoPgo8L2RlZnM+Cjwvc3ZnPgo=',
			40
		);
	}

	/**
	 * Main menu template
	 *
	 * @return void
	 */
	public function menu_page_template() {
		echo '<div id="accessibility-widget-app" class="accessibility-widget-app"></div>';
	}

	/**
	 * Returns Jed-formatted localization data. Added for backwards-compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $domain Translation domain.
	 * @return array          The information of the locale.
	 */
	public function get_jed_locale_data( $domain ) {
		$translations = get_translations_for_domain( $domain );
		$locale       = array(
			'' => array(
				'domain' => $domain,
				'lang'   => is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
			),
		);

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale[ $msgid ] = $entry->translations;
		}

		// If any of the translated strings incorrectly contains HTML line breaks, we need to return or else the admin is no longer accessible.
		$json = wp_json_encode( $locale );
		if ( preg_match( '/<br[\s\/\\\\]*>/', $json ) ) {
			return array();
		}

		return $locale;
	}

	/**
	 * Modify plugin action links on plugin listing page.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/accessibility-widget/#new-post" target="_blank">' . esc_html__( 'Support', 'accessibility-widget' ) . '</a>';
		$links[] = '<a href="' . get_admin_url( null, 'admin.php?page=accessibility-widget' ) . '">' . esc_html__( 'Settings', 'accessibility-widget' ) . '</a>';
		return array_reverse( $links );
	}

	/**
	 * Display the review banner as a standard WordPress admin notice.
	 */
	public function admin_notices() {
		// Do not show on the plugin's own settings page.
		if ( function_exists( 'cya11y_is_admin_page' ) && cya11y_is_admin_page() ) {
			return;
		}

		// Check if 7 days have passed since install.
		$install_date = get_option( 'cya11y_review_install_date', time() );
		if ( time() - $install_date < 7 * DAY_IN_SECONDS ) {
			return;
		}

		// Check if dismissed in options
		$banners = get_option( 'cya11y_banners', array() );
		$review_banner = isset( $banners['review-banner'] ) ? $banners['review-banner'] : null;

		// If status is false, banner is dismissed (permanently or temporarily).
		if (
			$review_banner &&
			isset( $review_banner['status'] ) &&
			false === $review_banner['status'] &&
			( ! isset( $review_banner['until'] ) || time() < $review_banner['until'] )
		) {
			return;
		}

		// Build URLs for actions and strictly sanitize SERVER data to pass PHPCS
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$current_url = set_url_scheme( 'http://' . $http_host . $request_uri );
		$permanent_url  = add_query_arg( array(
			'cya11y_dismiss_review' => 'permanent',
			'_wpnonce' => wp_create_nonce( 'cya11y_dismiss_review_nonce' )
		), $current_url );
		$temporary_url  = add_query_arg( array(
			'cya11y_dismiss_review' => 'temporary',
			'_wpnonce' => wp_create_nonce( 'cya11y_dismiss_review_nonce' )
		), $current_url );

		?>
		<div class="notice cya11y-review-notice-wrap">
			<div class="cya11y-review-notice">
				<a href="<?php echo esc_url( $temporary_url ); ?>" class="cya11y-review-close" aria-label="<?php esc_attr_e( 'Dismiss review banner', 'accessibility-widget' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</a>

				<div class="cya11y-review-content">
					<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'admin/app/dist/assets/accessyes-logo.png' ); ?>" alt="AccessYes by CookieYes" class="cya11y-review-logo" />

					<p class="cya11y-review-text">
						<?php esc_html_e( 'Hey, we at', 'accessibility-widget' ); ?> <strong><?php esc_html_e( 'AccessYes', 'accessibility-widget' ); ?></strong> <?php esc_html_e( 'would like to thank you for using our plugin. We would really appreciate if you could take a moment to drop a quick review that will inspire us to keep going.', 'accessibility-widget' ); ?>
					</p>

					<div class="cya11y-review-actions">
						<a href="https://wordpress.org/support/plugin/accessibility-widget/reviews/#new-post" class="cya11y-btn cya11y-btn-primary" target="_blank" rel="noopener noreferrer" onclick="window.location.href='<?php echo esc_url( $permanent_url ); ?>'; return true;">
							<?php esc_html_e( 'Review now', 'accessibility-widget' ); ?>
						</a>
						<a href="<?php echo esc_url( $permanent_url ); ?>" class="cya11y-btn cya11y-btn-outline">
							<?php esc_html_e( 'Never show again', 'accessibility-widget' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle admin notice dismiss actions for the review banner.
	 */
	public function handle_review_notice_actions() {
		if ( ! isset( $_GET['cya11y_dismiss_review'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'cya11y_dismiss_review_nonce' ) ) {
			return;
		}

		$dismiss_type = sanitize_text_field( wp_unslash( $_GET['cya11y_dismiss_review'] ) );
		$banners      = get_option( 'cya11y_banners', array() );

		if ( 'permanent' === $dismiss_type ) {
			$banners['review-banner'] = array(
				'status' => false,
			);
		} elseif ( 'temporary' === $dismiss_type ) {
			$banners['review-banner'] = array(
				'status' => false,
				'until'  => time() + ( 60 * DAY_IN_SECONDS ),
			);
		}

		update_option( 'cya11y_banners', $banners );

		// Redirect cleanly
		$redirect_url = remove_query_arg( array( 'cya11y_dismiss_review', '_wpnonce' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Hide all the unrelated notices from plugin page.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function hide_admin_notices() {
		// Bail if we're not on a CookieYes screen.
		if ( empty( $_REQUEST['page'] ) || ! preg_match( '/accessibility-widget/', esc_html( wp_unslash( $_REQUEST['page'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}
		global $wp_filter;

		$notices_type = array(
			'user_admin_notices',
			'admin_notices',
			'all_admin_notices',
		);

		foreach ( $notices_type as $type ) {
			if ( empty( $wp_filter[ $type ]->callbacks ) || ! is_array( $wp_filter[ $type ]->callbacks ) ) {
				continue;
			}

			foreach ( $wp_filter[ $type ]->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof \Closure ) {
						unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
						continue;
					}
					$class = ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ? strtolower( get_class( $arr['function'][0] ) ) : '';

					if ( ! empty( $class ) && preg_match( '/^(?:cya11y)/', $class ) ) {
						continue;
					}
					if ( ! empty( $name ) && ! preg_match( '/^(?:cya11y)/', $name ) ) {
						unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}
	}
}
