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
        add_action( 'admin_head', [ $this, 'inject_admin_css' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'cleanup_admin_scripts' ], 999 );
        
        // Include WP-CLI command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once KLLD_OTA_PLUGIN_DIR . 'class-ota-cli.php';
        }
    }

    public function cleanup_admin_scripts() {
        $screen = get_current_screen();
        $my_pages = [
            'toplevel_page_reviews-tools',
            'reviews-tools_page_klld-review-manager',
            'reviews-tools_page_klld-gttd-push',
            'reviews-tools_page_klld-review-generator'
        ];

        if ( in_array( $screen->id, $my_pages ) ) {
            wp_dequeue_script( 'wpml-content-stats' );
        }
    }

    public function inject_admin_css() {
        ?>
        <style>
            #adminmenu li.menu-top:hover .wp-menu-image img, 
            #adminmenu li.wp-has-current-submenu .wp-menu-image img {
                opacity: 1 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            #adminmenu .wp-menu-image img {
                padding: 4px 0 !important;
                opacity: 0.7;
                transition: opacity 0.2s;
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
            'run_auto_mapper'
        ];

        if ( isset( $_POST['action'] ) && in_array( $_POST['action'], $ota_actions ) ) {
            // Determine which tool file to load
            $tool_file = 'review_tool.php'; // Consolidated OTA tool
            
            $tool_path = KLLD_OTA_PLUGIN_DIR . $tool_file;
            if ( file_exists( $tool_path ) ) {
                if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
                include $tool_path;
                exit; // Ensure no other WP output follows
            }
        }
    }

    public function add_menu_pages() {
        add_menu_page(
            'Reviews Tools',
            'Reviews Tools',
            'manage_options',
            'reviews-tools',
            [ $this, 'render_index_page' ],
            KLLD_OTA_PLUGIN_URL . 'img/ota-reviews-logo.svg',
            30
        );

        add_submenu_page(
            'reviews-tools',
            'Review Manager',
            'Review Manager',
            'manage_options',
            'klld-review-manager',
            [ $this, 'render_review_manager' ]
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
                
                /* Recent Reviews Table */
                .k-recent-reviews { margin-top: 40px; }
                .k-recent-reviews h2 { font-size: 1.4rem; color: #1e293b; margin-bottom: 20px; }
                .k-table-wrapper { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
                .k-table { width: 100%; border-collapse: collapse; font-size: 13px; }
                .k-table th { background: #f8fafc; text-align: left; padding: 12px 20px; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; border-bottom: 1px solid #e2e8f0; }
                .k-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
                .k-table tr:last-child td { border-bottom: none; }
                .k-table tr:hover { background: #f8fafc; }
                .ota-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
                .badge-gyg { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
                .badge-viator { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
                .badge-tri { background: #f0f9ff; color: #0284c7; border: 1px solid #e0f2fe; }
                .badge-gmb { background: #fdf2f2; color: #dc2626; border: 1px solid #fee2e2; }
                .badge-tp { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
                .rating-stars { color: #f59e0b; }
                .tour-link { color: #0ea5e9; text-decoration: none; font-weight: 600; }
                .tour-link:hover { text-decoration: underline; }

                /* Collapsible Preview */
                .k-collapsible { margin-top: 30px; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; }
                .k-collapsible summary { padding: 20px; background: #f8fafc; cursor: pointer; font-weight: 700; font-size: 1.1rem; color: #1e293b; list-style: none; display: flex; align-items: center; justify-content: space-between; }
                .k-collapsible summary::-webkit-details-marker { display: none; }
                .k-collapsible summary::after { content: '↓'; font-size: 1.2rem; transition: transform 0.3s; }
                .k-collapsible[open] summary::after { transform: rotate(180deg); }
                .k-collapsible-content { padding: 20px; border-top: 1px solid #e2e8f0; max-height: 800px; overflow-y: auto; }
            </style>
            
            <div class="k-dash-header" style="display:flex; align-items:center; gap: 2rem;">
                <img src="<?php echo KLLD_OTA_PLUGIN_URL; ?>img/ota-reviews-logo.svg" style="width:80px; height:80px; filter: brightness(0) invert(1);" alt="Logo">
                <div>
                    <h1>Khaolak Land Discovery Tools</h1>
                    <p>Welcome to your centralized management hub for OTA reviews and product feeds.</p>
                </div>
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

            <div class="k-recent-reviews">
                <h2>📈 Recent OTA Reviews (Last 10)</h2>
                <div class="k-table-wrapper">
                    <table class="k-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Tour</th>
                                <th>Reviewer</th>
                                <th>Rating</th>
                                <th>Snippet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $args = [
                                'number' => 10,
                                'type'   => 'st_reviews',
                                'status' => 'approve',
                                'orderby' => 'comment_date',
                                'order'   => 'DESC',
                            ];
                            $comments = get_comments($args);
                            if ($comments) {
                                foreach ($comments as $comment) {
                                    $cid = $comment->comment_ID;
                                    $pid = $comment->comment_post_ID;
                                    $rating = get_comment_meta($cid, 'comment_rate', true);
                                    
                                    // Source Detection
                                    $source = 'Manual';
                                    $badge_class = '';
                                    if (get_comment_meta($cid, 'gyg_review_id', true)) { $source = 'GetYourGuide'; $badge_class = 'badge-gyg'; }
                                    elseif (get_comment_meta($cid, 'viator_review_id', true)) { $source = 'Viator'; $badge_class = 'badge-viator'; }
                                    elseif (get_comment_meta($cid, 'tripadvisor_review_id', true)) { $source = 'TripAdvisor'; $badge_class = 'badge-tri'; }
                                    elseif (get_comment_meta($cid, 'gmb_review_id', true)) { $source = 'Google'; $badge_class = 'badge-gmb'; }
                                    elseif (get_comment_meta($cid, 'trustpilot_review_id', true)) { $source = 'Trustpilot'; $badge_class = 'badge-tp'; }
                                    
                                    echo '<tr>';
                                    echo '<td>' . get_comment_date('M j, Y', $cid) . '</td>';
                                    echo '<td><span class="ota-badge ' . $badge_class . '">' . $source . '</span></td>';
                                    echo '<td><a href="' . get_permalink($pid) . '" class="tour-link" target="_blank">' . get_the_title($pid) . '</a></td>';
                                    echo '<td>' . esc_html($comment->comment_author) . '</td>';
                                    echo '<td class="rating-stars">' . str_repeat('★', intval($rating)) . str_repeat('☆', 5 - intval($rating)) . '</td>';
                                    echo '<td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . esc_html(wp_trim_words($comment->comment_content, 15)) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align:center; padding:40px;">No reviews found yet.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- --- Collapsible Feed Preview --- -->
            <details class="k-collapsible">
                <summary>📊 Google Things to Do Feed Preview (XML/JSON Data)</summary>
                <div class="k-collapsible-content">
                    <?php
                    if (!defined('KLLD_DASHBOARD_PREVIEW')) define('KLLD_DASHBOARD_PREVIEW', true);
                    $_GET['preview'] = 1;
                    $feed_path = KLLD_OTA_PLUGIN_DIR . 'google-tours-feed.php';
                    if (file_exists($feed_path)) {
                        include $feed_path;
                    } else {
                        echo '<p>Feed preview file not found.</p>';
                    }
                    ?>
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
            echo '<div class="notice notice-error"><p>Review Manager tool file not found.</p></div>';
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
}

new KLLD_Admin_Tools();
