<?php
/**
 * OTA Review Export Tool
 * Provides JSON and SQL exports for st_reviews.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Handle Export Action
if ( isset( $_POST['action'] ) && $_POST['action'] === 'klld_export_reviews' ) {
    $format = $_POST['format'] ?? 'json';
    
    global $wpdb;
    
    // Fetch all st_reviews
    $reviews = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_type = 'st_reviews'" );
    
    if ( $format === 'json' ) {
        $export_data = [];
        foreach ( $reviews as $review ) {
            $review_data = (array) $review;
            
            // 1. Fetch Review Meta
            $meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->commentmeta} WHERE comment_id = %d", $review->comment_ID ) );
            $review_data['meta'] = [];
            foreach ( $meta as $m ) {
                $review_data['meta'][$m->meta_key] = maybe_unserialize( $m->meta_value );
            }

            // 2. Fetch Associated Tour (wp_posts)
            $tour_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $review->comment_post_ID ), ARRAY_A );
            if ($tour_post) {
                $review_data['tour'] = $tour_post;
                
                // 3. Fetch Tour Meta (wp_postmeta)
                $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $review->comment_post_ID ) );
                $review_data['tour']['meta'] = [];
                foreach ( $postmeta as $pm ) {
                    $review_data['tour']['meta'][$pm->meta_key] = maybe_unserialize( $pm->meta_value );
                }

                // 4. Fetch Traveler Optimized Data (wp_st_tours)
                $st_tour = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}st_tours WHERE post_id = %d", $review->comment_post_ID ), ARRAY_A );
                $review_data['tour']['st_data'] = $st_tour;
            }

            $export_data[] = $review_data;
        }
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="klld_reviews_deep_export_' . date('Y-m-d') . '.json"');
        echo json_encode( $export_data, JSON_PRETTY_PRINT );
        exit;
    } elseif ( $format === 'sql' ) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="klld_reviews_deep_export_' . date('Y-m-d') . '.sql"');
        
        echo "-- OTAs Manager Deep Export SQL\n";
        echo "-- Includes: wp_posts, wp_postmeta, wp_comments, wp_commentmeta, wp_st_tours\n";
        echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        $exported_tours = [];

        foreach ( $reviews as $review ) {
            $pid = $review->comment_post_ID;

            // DEDUPLICATED TOUR EXPORT
            if (!in_array($pid, $exported_tours)) {
                $tour_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $pid ), ARRAY_A );
                if ($tour_post) {
                    echo "-- TOUR: " . esc_sql($tour_post['post_title']) . "\n";
                    $pcols = implode( "`, `", array_keys( $tour_post ) );
                    $pvals = implode( "', '", array_map( 'esc_sql', array_values( $tour_post ) ) );
                    echo "INSERT INTO `{$wpdb->posts}` (`{$pcols}`) VALUES ('{$pvals}');\n";

                    $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d", $pid ), ARRAY_A );
                    foreach ($postmeta as $pm) {
                        $pm_cols = implode( "`, `", array_keys( $pm ) );
                        $pm_vals = implode( "', '", array_map( 'esc_sql', array_values( $pm ) ) );
                        echo "INSERT INTO `{$wpdb->postmeta}` (`{$pm_cols}`) VALUES ('{$pm_vals}');\n";
                    }

                    $st_tour = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}st_tours WHERE post_id = %d", $pid ), ARRAY_A );
                    if ($st_tour) {
                        $st_cols = implode( "`, `", array_keys( $st_tour ) );
                        $st_vals = implode( "', '", array_map( 'esc_sql', array_values( $st_tour ) ) );
                        echo "INSERT INTO `{$wpdb->prefix}st_tours` (`{$st_cols}`) VALUES ('{$st_vals}');\n";
                    }
                    echo "\n";
                }
                $exported_tours[] = $pid;
            }

            // REVIEW EXPORT
            $review_array = (array) $review;
            $cols = implode( "`, `", array_keys( $review_array ) );
            $vals = implode( "', '", array_map( 'esc_sql', array_values( $review_array ) ) );
            echo "INSERT INTO `{$wpdb->comments}` (`{$cols}`) VALUES ('{$vals}');\n";
            
            $meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->commentmeta} WHERE comment_id = %d", $review->comment_ID ), ARRAY_A );
            foreach ( $meta as $m ) {
                $mcols = implode( "`, `", array_keys( $m ) );
                $mvals = implode( "', '", array_map( 'esc_sql', array_values( $m ) ) );
                echo "INSERT INTO `{$wpdb->commentmeta}` (`{$mcols}`) VALUES ('{$mvals}');\n";
            }
            echo "\n";
        }
        exit;
    }
}

// Render UI
?>
<div class="wrap">
    <h1>📥 Export Reviews Data</h1>
    <p>Export all <strong>st_reviews</strong> from the database. This includes reviews from GetYourGuide, TripAdvisor, Google, and Viator.</p>
    
    <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
        <form method="post">
            <input type="hidden" name="action" value="klld_export_reviews">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="format">Export Format</label></th>
                    <td>
                        <select name="format" id="format" style="width: 100%;">
                            <option value="json">JSON Format (Readable/Programmatic)</option>
                            <option value="sql">SQL Format (Direct Database Insert)</option>
                        </select>
                        <p class="description">JSON is best for analysis or external apps. SQL is best for database migrations.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Database Tables</th>
                    <td>
                        <ul style="margin-top: 0;">
                            <li>✅ <code>wp_comments</code> (Review Content & Author)</li>
                            <li>✅ <code>wp_commentmeta</code> (Ratings, Source IDs, AI Titles)</li>
                            <li>✅ <code>wp_posts</code> (Full Tour Descriptions & Settings)</li>
                            <li>✅ <code>wp_postmeta</code> (Tour Settings & SEO Data)</li>
                            <li>✅ <code>wp_st_tours</code> (Traveler Theme Optimized Data)</li>
                        </ul>
                        <p class="description">Includes deep-linking between reviews and their respective tours.</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Download Export File', 'primary', 'submit', true, ['style' => 'width: 100%; height: 50px; font-size: 16px;']); ?>
        </form>
    </div>

    <div style="margin-top: 40px; color: #64748b; font-size: 13px;">
        <p><strong>Note:</strong> Large datasets might take a few seconds to generate. Ensure your PHP memory limit is sufficient if you have tens of thousands of reviews.</p>
    </div>
</div>
