<?php
/**
 * WP-CLI Command: wp ota-reviews
 * Provides command-line interface for managing OTA tour reviews.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;

class KLLD_OTA_CLI_Command {

    /**
     * Synchronize reviews from all OTA sources (GYG, Viator, TripAdvisor).
     * 
     * ## OPTIONS
     * 
     * [--post_id=<id>]
     * : Specific post ID to sync.
     * 
     * [--limits=<number>]
     * : Number of reviews to fetch per tour (default 10).
     * 
     * [--force]
     * : Force sync even if already synced recently.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews sync --limits=5 --force
     *
     * @when after_wp_load
     */
    public function sync( $args, $assoc_args ) {
        $limits  = isset( $assoc_args['limits'] ) ? intval( $assoc_args['limits'] ) : 10;
        $force   = isset( $assoc_args['force'] );
        $post_id = isset( $assoc_args['post_id'] ) ? intval( $assoc_args['post_id'] ) : 0;

        WP_CLI::log( "🚀 Starting Global OTA Review Sync..." );
        if ($post_id) WP_CLI::log( "   - Target Post: #$post_id" );
        WP_CLI::log( "   - Limits: $limits" );
        WP_CLI::log( "   - Force Sync: " . ($force ? 'Enabled' : 'Disabled') );

        if ( ! defined( 'KLLD_SYNC_NO_RUN' ) ) define( 'KLLD_SYNC_NO_RUN', true );
        
        // Pass target_post_id to global for ota_sync.php to pick up
        if ($post_id) {
            $GLOBALS['target_post_id'] = $post_id;
        }

        require_once dirname(__FILE__) . '/ota_sync.php';
        
        $sync = new OTAReviewSync();
        $results = $sync->run($limits, $force);

        if ($results && isset($results['tours'])) {
            WP_CLI::success( "Sync completed! Processed {$results['tours']} tours." );
        } else {
            WP_CLI::success( "Sync process finished." );
        }
    }

    /**
     * Remove duplicate and invalid reviews from the database.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews cleanup
     *
     * @when after_wp_load
     */
    public function cleanup( $args, $assoc_args ) {
        if ( ! defined( 'KLLD_SYNC_NO_RUN' ) ) define( 'KLLD_SYNC_NO_RUN', true );
        require_once dirname(__FILE__) . '/ota_sync.php';
        
        $sync = new OTAReviewSync();
        $sync->cleanup();
        WP_CLI::success( "Cleanup finished." );
    }

    /**
     * Run the Auto-Mapper tool to discover and link OTA IDs for tours.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews map
     *
     * @when after_wp_load
     */
    public function map( $args, $assoc_args ) {
        WP_CLI::log( "🔍 Running OTA Auto-Mapper Discovery..." );
        
        if ( ! defined( 'KLLD_TOOL_RUN' ) ) define( 'KLLD_TOOL_RUN', true );
        require_once dirname(__FILE__) . '/ota_auto_mapper.php';
        
        WP_CLI::success( "Auto-Mapping process finished. Check the log file for detailed match results." );
    }

    /**
     * Scrape live Google Maps reviews using Puppeteer.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews scrape-gmb
     *
     * @when after_wp_load
     */
    public function scrape_gmb( $args, $assoc_args ) {
        $scraper_dir = KLLD_OTA_PLUGIN_DIR . 'scrapers/gmb';
        $cmd = "cd $scraper_dir && node scraper.js 2>&1";
        
        WP_CLI::log( "🚀 Starting Google Maps Scraper..." );
        
        $output = '';
        if ( function_exists( 'shell_exec' ) ) {
            $output = shell_exec( $cmd );
        } elseif ( function_exists( 'proc_open' ) ) {
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            $process = proc_open( $cmd, $descriptorspec, $pipes );
            if ( is_resource( $process ) ) {
                $output = stream_get_contents( $pipes[1] );
                fclose( $pipes[0] );
                fclose( $pipes[1] );
                fclose( $pipes[2] );
                proc_close( $process );
            }
        } else {
            WP_CLI::error( "Both shell_exec and proc_open are disabled. Please run the scraper manually: $cmd" );
            return;
        }

        WP_CLI::line( $output );

        if ( strpos( (string)$output, 'Success' ) !== false ) {
            WP_CLI::success( "GMB Scraping complete." );
            WP_CLI::log( "Note: Run 'wp ota-reviews cleanup' to deduplicate if needed." );
        } else {
            WP_CLI::error( "Scraper failed. Check the output above." );
        }
    }

    /**
     * Synchronize tour content (descriptions, highlights) from OTA sources.
     * 
     * ## OPTIONS
     * 
     * [--post_id=<id>]
     * : Specific post ID to sync.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews sync-content --post_id=14625
     *
     * @when after_wp_load
     */
    public function sync_content( $args, $assoc_args ) {
        $post_id = 0;
        if (isset($assoc_args['post_id'])) $post_id = intval($assoc_args['post_id']);
        elseif (isset($assoc_args['post-id'])) $post_id = intval($assoc_args['post-id']);
        elseif (isset($args[0])) $post_id = intval($args[0]);
        
        require_once dirname(__FILE__) . '/ota-content.php';
        $content_sync = new KLLD_OTA_Content();

        if ($post_id) {
            $activity_id = get_post_meta($post_id, '_gyg_activity_id', true);
            if (!$activity_id) {
                WP_CLI::error("No GYG Activity ID found for post #$post_id");
                return;
            }
            WP_CLI::log("Syncing content for post #$post_id (GYG: $activity_id)...");
            $data = $content_sync->fetch_gyg_activity($activity_id);
            if (!is_wp_error($data)) {
                $content_sync->update_tour_content($post_id, $data);
                WP_CLI::success("Content updated successfully.");
            } else {
                WP_CLI::error($data->get_error_message());
            }
        } else {
            WP_CLI::log("Bulk content sync not yet implemented. Please specify --post_id.");
        }
    }

    /**
     * Synchronize tour availability (calendar) from OTA sources.
     * 
     * ## OPTIONS
     * 
     * [--post_id=<id>]
     * : Specific post ID to sync.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews sync-calendar --post_id=14625
     *
     * @when after_wp_load
     */
    public function sync_calendar( $args, $assoc_args ) {
        $post_id = 0;
        if (isset($assoc_args['post_id'])) $post_id = intval($assoc_args['post_id']);
        elseif (isset($args[0])) $post_id = intval($args[0]);

        require_once dirname(__FILE__) . '/ota-calendar.php';
        $cal_sync = new KLLD_OTA_Calendar();

        if ($post_id) {
            $activity_id = get_post_meta($post_id, '_gyg_activity_id', true);
            if (!$activity_id) {
                WP_CLI::error("No GYG Activity ID found for post #$post_id");
                return;
            }
            WP_CLI::log("Syncing calendar for post #$post_id (GYG: $activity_id)...");
            $data = $cal_sync->fetch_gyg_availability($activity_id);
            if (!is_wp_error($data)) {
                $count = $cal_sync->sync_to_db($post_id, $data);
                WP_CLI::success("Calendar updated: $count dates synced.");
            } else {
                WP_CLI::error($data->get_error_message());
            }
        } else {
             WP_CLI::log("Bulk calendar sync not yet implemented. Please specify --post_id.");
        }
    }

    /**
     * Check the sync status and mapping coverage for all tours.
     * 
     * ## EXAMPLES
     * 
     *     wp ota-reviews status
     *
     * @when after_wp_load
     */
    public function status( $args, $assoc_args ) {
        $tours = get_posts([
            'post_type' => 'st_tours',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        $rows = [];
        foreach ($tours as $tour) {
            $gyg = get_post_meta($tour->ID, '_gyg_activity_id', true);
            $viator = get_post_meta($tour->ID, '_viator_activity_id', true);
            $ta = get_post_meta($tour->ID, '_tripadvisor_activity_id', true);
            
            $rows[] = [
                'ID' => $tour->ID,
                'Title' => wp_trim_words($tour->post_title, 5),
                'GYG' => $gyg ? "✅ $gyg" : "❌",
                'Viator' => $viator ? "✅ $viator" : "❌",
                'TA' => $ta ? "✅ $ta" : "❌",
                'Reviews' => count(get_comments(['post_id' => $tour->ID, 'type' => 'st_reviews', 'status' => 'approve']))
            ];
        }

        WP_CLI\Utils\format_items( 'table', $rows, ['ID', 'Title', 'GYG', 'Viator', 'TA', 'Reviews'] );
    }
}

WP_CLI::add_command( 'ota-reviews', 'KLLD_OTA_CLI_Command' );
