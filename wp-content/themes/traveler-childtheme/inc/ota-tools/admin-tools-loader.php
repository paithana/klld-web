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
        ?>
        <div class="wrap" id="klld-tools-dashboard">
            <style>
                #klld-tools-dashboard { margin-top: 20px; font-family: 'Inter', system-ui, sans-serif; }
                .k-dash-header { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 40px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
                .k-dash-header h1 { color: white; margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; }
                .k-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
                .k-card-tool { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0; transition: all 0.3s; position: relative; overflow: hidden; }
                .k-card-tool:hover { transform: translateY(-5px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05); border-color: #0ea5e9; }
                .k-card-tool h3 { margin-top: 0; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
                .k-card-tool p { color: #64748b; font-size: 14px; line-height: 1.6; }
                .k-btn-link { display: inline-block; margin-top: 15px; padding: 8px 16px; background: #f1f5f9; color: #0f172a; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; transition: background 0.2s; }
                .k-btn-link:hover { background: #e2e8f0; color: #0ea5e9; }
                .k-status-bar { display: flex; align-items: center; gap: 8px; font-size: 12px; margin-top: 20px; padding: 8px 12px; background: #f8fafc; border-radius: 6px; color: #64748b; border: 1px solid #edf2f7; }
                .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px #22c55e; }
            </style>
            
            <div class="k-dash-header">
                <h1>Khaolak Land Discovery Tools</h1>
                <p>Welcome to your centralized management hub for OTA reviews and product feeds.</p>
            </div>

            <div class="k-grid">
                <!-- Tool: Review Manager -->
                <div class="k-card-tool">
                    <h3>🔄 Review Manager</h3>
                    <p>Synchronize reviews from GetYourGuide, Viator, and TripAdvisor. Manage tour mappings and aggregate ratings.</p>
                    <a href="?page=klld-review-manager" class="k-btn-link">Open Manager →</a>
                </div>

                <!-- Tool: SFTP Feed Push -->
                <div class="k-card-tool">
                    <h3>🎯 GTTD SFTP Push</h3>
                    <p>Automate your "Google Things to Do" feed delivery. Monitor upload status and verify XML generation.</p>
                    <a href="?page=klld-gttd-push" class="k-btn-link">Manage Feed Push →</a>
                </div>

                <!-- Tool: Google Feed Preview -->
                <div class="k-card-tool">
                    <h3>📊 Feed Health</h3>
                    <p>Verify exactly what Google sees. Preview the JSON and XML feeds before they are pushed to servers.</p>
                    <a href="<?php echo get_stylesheet_directory_uri(); ?>/inc/ota-tools/google-tours-feed.php?preview=1" target="_blank" class="k-btn-link">Preview Feed Statistics</a>
                </div>
            </div>

            <div class="k-status-bar">
                <span class="status-dot"></span>
                <span>OTA Synchronization Engine is active. Local time: <?php echo date('H:i'); ?></span>
            </div>
        </div>
        <?php
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
