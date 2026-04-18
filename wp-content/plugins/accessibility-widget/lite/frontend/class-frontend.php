<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.cookieyes.com/
 * @since      3.0.0
 *
 * @package    AccessibilityWidget
 * @subpackage AccessibilityWidget/Frontend
 */

namespace CookieYes\AccessibilityWidget\Lite\Frontend;
use CookieYes\AccessibilityWidget\Lite\Admin\Modules\Settings\Includes\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    AccessibilityWidget
 * @subpackage CookieYes\AccessibilityWidget\Lite\Frontend
 * @author     CookieYes <info@cookieyes.com>
 */
class Frontend {


	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $plugin_name  The ID of this plugin.
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
	 * Banner object
	 *
	 * @var object
	 */
	protected $banner;

	/**
	 * Plugin settings
	 *
	 * @var object
	 */
	protected $settings;

	/**
	 * Plugin settings
	 *
	 * @var object
	 */
	protected $gcm_settings;

	/**
	 * Banner template
	 *
	 * @var object
	 */
	protected $template;

	/**
	 * Providers list
	 *
	 * @var array
	 */
	protected $providers = array();
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_modules();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
	}

	/**
	 * Get the default modules array
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_default_modules() {
		$modules = array();
		return $modules;
	}

	/**
	 * Load all the modules
	 *
	 * @return void
	 */
	public function load_modules() {}

	/**
	 * Enqeue front end scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/widget' . $suffix . '.js', array(), $this->version, true );
		wp_localize_script( $this->plugin_name, '_cyA11yConfig', $this->get_store_data() );
		wp_localize_script(
			$this->plugin_name,
			'_cyA11yAssets',
			array(
				'fonts' => plugin_dir_url( __FILE__ ) . 'assets/fonts/',
			)
		);
		wp_localize_script( $this->plugin_name, '_cyA11yStatementTemplates', $this->get_statement_templates() );
	}

	/**
	 * Load statement templates from JSON files and return as an associative array keyed by language code.
	 *
	 * @return array
	 */
	private function get_statement_templates(): array {
		$templates = array();
		$dir       = plugin_dir_path( __FILE__ ) . 'assets/statement-templates/';
		$files     = glob( $dir . '*.json' );
		if ( ! $files ) {
			return $templates;
		}
		foreach ( $files as $file ) {
			$lang = basename( $file, '.json' );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$raw = file_get_contents( $file );
			if ( false === $raw ) {
				continue;
			}
			$data = json_decode( $raw, true );
			if ( is_array( $data ) ) {
				$templates[ $lang ] = $data;
			}
		}
		return $templates;
	}

	/**
	 * Get store data
	 *
	 * @return array
	 */
	public function get_store_data() {
		return Settings::get_instance()->get();
	}
}
