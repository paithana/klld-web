<?php
/**
 * KLLD Tools Loader - Child Theme Integration
 * Integrates standalone OTA tools into the WordPress Admin Menu.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class KLLD_Admin_Tools {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
        add_action( 'admin_init', [ $this, 'intercept_tool_ajax' ] );
    }

    /**
     * Intercept AJAX/POST actions from tools before WP Admin UI starts outputting HTML.
     */
    public function intercept_tool_ajax() {
        $ota_actions = [
            'save_ota_mappings', 
            'proxy_ota_discover', 
            'proxy_gyg_integrator', 
            'proxy_gyg_official', 
            'proxy_ota_fetch', 
            'ota_db_maintenance',
            'ota_direct_import'
        ];

        if ( isset( $_POST['action'] ) && in_array( $_POST['action'], $ota_actions ) ) {
            // Determine which tool file to load
            $tool_file = 'review_tool.php'; // Consolidated OTA tool
            
            $tool_path = get_stylesheet_directory() . '/inc/ota-tools/' . $tool_file;
            if ( file_exists( $tool_path ) ) {
                if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
                include $tool_path;
                exit; // Ensure no other WP output follows
            }
        }
    }

    public function add_menu_pages() {
        add_menu_page(
            'KLLD Tools',
            'KLLD Tools',
            'manage_options',
            'klld-tools',
            [ $this, 'render_index_page' ],
            'dashicons-admin-tools',
            30
        );

        add_submenu_page(
            'klld-tools',
            'Review Manager',
            'Review Manager',
            'manage_options',
            'klld-review-manager',
            [ $this, 'render_review_manager' ]
        );

        add_submenu_page(
            'klld-tools',
            'SFTP Feed Push',
            'SFTP Feed Push',
            'manage_options',
            'klld-gttd-push',
            [ $this, 'render_gttd_status' ]
        );
    }

    public function render_index_page() {
        echo '<div class="wrap"><h1>KLLD Admin Tools</h1><p>Select a tool from the menu to manage OTA reviews or GTTD feeds.</p></div>';
    }

    public function render_review_manager() {
        $tool_path = get_stylesheet_directory() . '/inc/ota-tools/review_tool.php';
        if ( file_exists( $tool_path ) ) {
            if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Review Manager tool file not found.</p></div>';
        }
    }

    public function render_gttd_status() {
        $tool_path = get_stylesheet_directory() . '/inc/ota-tools/gttd_sftp_push.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>GTTD Push tool file not found.</p></div>';
        }
    }
}

new KLLD_Admin_Tools();
