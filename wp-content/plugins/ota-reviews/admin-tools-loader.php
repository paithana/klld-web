<?php
/**
 * KLLD Tools Loader - Child Theme Integration
 * Integrates standalone OTA tools into the WordPress Admin Menu.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KLLD_Admin_Tools Class
 *
 * Integrates standalone OTA tools into the WordPress Admin Menu.
 */
class KLLD_Admin_Tools {

    /**
     * Constructor: Initializes actions and filters.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_pages' ] );
        add_action( 'admin_init', [ $this, 'intercept_tool_ajax' ] );
        add_action( 'admin_head', [ $this, 'inject_admin_css' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'cleanup_admin_scripts' ], 999 );
        
        // Include WP-CLI command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once KLLD_OTA_PLUGIN_DIR . 'class-ota-cli.php';
        }
    }

    /**
     * Dequeues scripts that cause conflicts on OTAs Manager pages.
     */
    public function cleanup_admin_scripts() {
        $screen = get_current_screen();
        $my_pages = [
            'toplevel_page_reviews-tools',
            'reviews-tools_page_klld-review-manager',
            'reviews-tools_page_klld-review-editor',
            'reviews-tools_page_klld-gttd-push',
            'reviews-tools_page_klld-review-generator'
        ];

        if ( in_array( $screen->id, $my_pages ) ) {
            wp_dequeue_script( 'wpml-content-stats' );
        }
    }

    /**
     * Injects custom CSS for the admin menu icon.
     */
    public function inject_admin_css() {
        ?>
        <style>
            #adminmenu .toplevel_page_reviews-tools .wp-menu-image img {
                width: 20px !important;
                height: auto !important;
                padding: 7px 0 !important;
                opacity: 0.8;
            }
            #adminmenu li.menu-top:hover .wp-menu-image img, 
            #adminmenu li.wp-has-current-submenu .wp-menu-image img {
                opacity: 1 !important;
                width: 100% !important;
            }
        </style>
        <?php
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
            'ota_direct_import',
            'generate_custom_reviews',
            'run_system_health_check',
            'run_auto_mapper',
            'klld_get_reviews',
            'klld_update_review_status',
            'klld_update_review_post_id',
            'klld_delete_review',
            'klld_export_reviews',
            'klld_ai_generate_title',
            'klld_save_ota_settings'
        ];

        if ( isset( $_POST['action'] ) && in_array( $_POST['action'], $ota_actions ) ) {
            // Determine which tool file to load
            $tool_file = 'review_tool.php'; // Consolidated OTA tool
            if (strpos($_POST['action'], 'klld_export') === 0) {
                $tool_file = 'export_tool.php';
            } elseif (strpos($_POST['action'], 'klld_ai') === 0) {
                $tool_file = 'ai_writer.php';
            } elseif (strpos($_POST['action'], 'klld_save_ota_settings') === 0) {
                $tool_file = 'settings.php';
            } elseif (strpos($_POST['action'], 'klld_') === 0) {
                $tool_file = 'review_editor.php';
            }
            
            $tool_path = KLLD_OTA_PLUGIN_DIR . $tool_file;
            if ( file_exists( $tool_path ) ) {
                if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
                include $tool_path;
                exit; // Ensure no other WP output follows
            }
        }
    }

    /**
     * Registers the admin menu and submenu pages.
     */
    public function add_menu_pages() {
        add_menu_page(
            'OTAs Manager',
            'OTAs Manager',
            'manage_options',
            'reviews-tools',
            [ $this, 'render_index_page' ],
            KLLD_OTA_PLUGIN_URL . 'img/ota-reviews-logo.svg',
            30
        );

        add_submenu_page(
            'reviews-tools',
            'Sync Manager',
            'Sync Manager',
            'manage_options',
            'klld-review-manager',
            [ $this, 'render_review_manager' ]
        );

        add_submenu_page(
            'reviews-tools',
            'Review Editor',
            'Review Editor',
            'manage_options',
            'klld-review-editor',
            [ $this, 'render_review_editor' ]
        );

        add_submenu_page(
            'reviews-tools',
            'SFTP Feed Push',
            'SFTP Feed Push',
            'manage_options',
            'klld-gttd-push',
            [ $this, 'render_gttd_status' ]
        );

        add_submenu_page(
            'reviews-tools',
            'Review Generator',
            'Review Generator',
            'manage_options',
            'klld-review-generator',
            [ $this, 'render_review_generator' ]
        );

        add_submenu_page(
            'reviews-tools',
            'Export Reviews',
            'Export Reviews',
            'manage_options',
            'klld-export-reviews',
            [ $this, 'render_export_reviews' ]
        );

        add_submenu_page(
            'reviews-tools',
            'AI Review Writer',
            'AI Review Writer',
            'manage_options',
            'klld-ai-writer',
            [ $this, 'render_ai_writer' ]
        );

        add_submenu_page(
            'reviews-tools',
            'Settings',
            'Settings',
            'manage_options',
            'klld-ota-settings',
            [ $this, 'render_settings' ]
        );
    }

    /**
     * Renders the main dashboard page.
     */
    public function render_index_page() {
        ?>
        <div class="wrap" id="klld-tools-dashboard">
            <style>
                #klld-tools-dashboard { margin-top: 20px; font-family: 'Inter', system-ui, sans-serif; }
                .k-dash-header { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; padding: 40px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
                .k-dash-header h1 { color: white; margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; }
                .k-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }
                .k-card-tool { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
                .k-card-tool:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08); border-color: #0ea5e9; }
                .k-card-tool h3 { margin-top: 0; font-size: 20px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 12px; }
                .k-card-tool p { color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 20px; flex-grow: 1; }
                .k-btn-link { display: inline-flex; align-items: center; padding: 10px 20px; background: #f1f5f9; color: #0f172a; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; transition: all 0.2s; width: fit-content; }
                .k-btn-link:hover { background: #0ea5e9; color: white; }
                
                .k-status-bar { display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 30px; font-size: 13px; color: #64748b; }
                .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px #22c55e; }

                .k-recent-reviews { margin-top: 50px; }
                .k-recent-reviews h2 { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
                .k-table-wrapper { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                .k-table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 900px; }
                .k-table th { background: #f8fafc; text-align: left; padding: 15px 25px; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
                .k-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
                .k-table tr:hover { background: #f8fafc; }
                .ota-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; text-transform: uppercase; border: 1px solid transparent; }
                .badge-gyg { background: #fff7ed; color: #ea580c; border-color: #ffedd5; }
                .badge-viator { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
                .badge-tri { background: #00af87; color: white; }
                .badge-gmb { background: #fdf2f2; color: #dc2626; border-color: #fee2e2; }
                .rating-stars { color: #f59e0b; font-size: 12px; }
                .tour-link { color: #0ea5e9; text-decoration: none; font-weight: 700; }
                .tour-link:hover { text-decoration: underline; }

                .k-collapsible { margin-top: 40px; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: white; }
                .k-collapsible summary { padding: 25px; background: #f8fafc; cursor: pointer; font-weight: 800; font-size: 1.2rem; color: #0f172a; list-style: none; display: flex; align-items: center; justify-content: space-between; }
                .k-collapsible summary::after { content: '▾'; font-size: 1.5rem; transition: transform 0.3s; }
                .k-collapsible[open] summary::after { transform: rotate(180deg); }
                .k-collapsible-content { padding: 30px; border-top: 1px solid #e2e8f0; background: #fafafa; }

                @media (max-width: 768px) {
                    .k-dash-header { padding: 30px 20px; text-align: center; }
                    .k-dash-header img { width: 80px; height: 80px; margin-bottom: 20px; }
                    .k-dash-header h1 { font-size: 24px; }
                    .k-dash-header p { font-size: 14px; }
                    .k-grid { grid-template-columns: 1fr; }
                    .k-card-tool { padding: 20px; }
                }
            </style>
            <div class="k-dash-header" style="display:flex; align-items:center; gap: 2.5rem; flex-wrap: wrap;">
                <img src="<?php echo KLLD_OTA_PLUGIN_URL; ?>img/ota-reviews-logo.svg" style="width:100px; height:100px; filter: brightness(0) invert(1);" alt="Logo">
                <div>
                    <h1>OTAs Manager Command Center</h1>
                    <p style="margin:5px 0 0 0; opacity:0.9; font-size:16px;">Centralized synchronization, moderation, and AI-enhancement for your tour reviews.</p>
                </div>
            </div>

            <div class="k-grid">
                <div class="k-card-tool">
                    <h3>🔄 Sync Manager</h3>
                    <p>Configure tour mappings for GYG, Viator, and TripAdvisor. Manage API keys and trigger global background syncs.</p>
                    <a href="?page=klld-review-manager" class="k-btn-link">Open Manager →</a>
                </div>

                <div class="k-card-tool">
                    <h3>✍️ Review Editor</h3>
                    <p>Modern moderation suite with search, bulk remapping, and accordion management. Best for cleaning up your review data.</p>
                    <a href="?page=klld-review-editor" class="k-btn-link">Open Editor →</a>
                </div>

                <div class="k-card-tool">
                    <h3>🤖 AI Review Writer</h3>
                    <p>Leverage OpenAI GPT-3.5 to automatically generate professional headlines for reviews that are missing titles.</p>
                    <a href="?page=klld-ai-writer" class="k-btn-link">Launch AI Writer →</a>
                </div>

                <div class="k-card-tool">
                    <h3>🌱 Review Generator</h3>
                    <p>Seed your tours with authentic review templates. Useful for new products or filling gaps in localized translations.</p>
                    <a href="?page=klld-review-generator" class="k-btn-link">Seed Reviews →</a>
                </div>

                <div class="k-card-tool">
                    <h3>📥 Export Reviews</h3>
                    <p>Full database backups in JSON or SQL format. Now includes associated tour content for comprehensive analysis.</p>
                    <a href="?page=klld-export-reviews" class="k-btn-link">Download Data →</a>
                </div>

                <div class="k-card-tool">
                    <h3>📊 Feed Health</h3>
                    <p>Verify exactly what Google sees. Preview or download the JSON and XML feeds before they are pushed via SFTP.</p>
                    <div class="flex gap-2">
                        <a href="<?php echo KLLD_OTA_PLUGIN_URL; ?>google-tours-feed.php?preview=1" target="_blank" class="k-btn-link">Preview →</a>
                        <a href="<?php echo KLLD_OTA_PLUGIN_URL; ?>google-tours-feed.php?format=xml&download=1" class="k-btn-link" style="background:#0ea5e9; color:white;">Download XML</a>
                    </div>
                </div>
            </div>

            <div class="k-recent-reviews">
                <h2>🕒 Recent Activity Log</h2>
                <div class="k-table-wrapper">
                    <table class="k-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Tour</th>
                                <th>Author</th>
                                <th>Rating</th>
                                <th>Snippet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            global $wpdb;
                            $recent = $wpdb->get_results("SELECT comment_ID, comment_post_ID, comment_author, comment_content, comment_date FROM {$wpdb->comments} WHERE comment_type = 'st_reviews' ORDER BY comment_date DESC LIMIT 10");
                            if ($recent) {
                                foreach ($recent as $comment) {
                                    $cid = $comment->comment_ID;
                                    $pid = $comment->comment_post_ID;
                                    $rating = get_comment_meta($cid, 'comment_rate', true);
                                    
                                    $source = 'Manual';
                                    $badge_class = '';
                                    if (get_comment_meta($cid, 'gyg_review_id', true)) { $source = 'GetYourGuide'; $badge_class = 'badge-gyg'; }
                                    elseif (get_comment_meta($cid, 'viator_review_id', true)) { $source = 'Viator'; $badge_class = 'badge-viator'; }
                                    elseif (get_comment_meta($cid, 'tripadvisor_review_id', true)) { $source = 'TripAdvisor'; $badge_class = 'badge-tri'; }
                                    elseif (get_comment_meta($cid, 'gmb_review_id', true)) { $source = 'Google'; $badge_class = 'badge-gmb'; }
                                    
                                    echo '<tr>';
                                    echo '<td style="color:#94a3b8; font-weight:600;">' . get_comment_date('M j, Y', $cid) . '</td>';
                                    echo '<td><span class="ota-badge ' . $badge_class . '">' . $source . '</span></td>';
                                    echo '<td><a href="' . get_permalink($pid) . '" class="tour-link" target="_blank">' . get_the_title($pid) . '</a></td>';
                                    echo '<td style="font-weight:700;">' . esc_html($comment->comment_author) . '</td>';
                                    echo '<td class="rating-stars">' . str_repeat('★', intval($rating)) . str_repeat('☆', 5 - intval($rating)) . '</td>';
                                    echo '<td style="max-width:300px; color:#64748b; font-size:12px;">' . esc_html(wp_trim_words($comment->comment_content, 12)) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align:center; padding:50px; color:#94a3b8;">No activity recorded yet.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <details class="k-collapsible">
                <summary>📡 SFTP Connection & Feed Settings</summary>
                <div class="k-collapsible-content">
                    <?php $this->render_gttd_status(); ?>
                </div>
            </details>
        </div>
        <?php
    }

    public function render_review_manager() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'review_tool.php';
        if ( file_exists( $tool_path ) ) {
            if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Sync Manager tool file not found.</p></div>';
        }
    }

    public function render_review_editor() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'review_editor.php';
        if ( file_exists( $tool_path ) ) {
            if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Review Editor tool file not found.</p></div>';
        }
    }

    public function render_gttd_status() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'gttd_sftp_push.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>GTTD Push tool file not found.</p></div>';
        }
    }

    public function render_review_generator() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'review_generator.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Review Generator tool file not found.</p></div>';
        }
    }

    public function render_export_reviews() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'export_tool.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Export tool file not found.</p></div>';
        }
    }

    public function render_ai_writer() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'ai_writer.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>AI Writer tool file not found.</p></div>';
        }
    }

    public function render_settings() {
        $tool_path = KLLD_OTA_PLUGIN_DIR . 'settings.php';
        if ( file_exists( $tool_path ) ) {
             if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
            include $tool_path;
        } else {
            echo '<div class="notice notice-error"><p>Settings tool file not found.</p></div>';
        }
    }
}

new KLLD_Admin_Tools();
