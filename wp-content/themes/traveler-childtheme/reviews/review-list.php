<?php
/**
 * Modernized Review List Template
 * Overrides the default Traveler review list with OTA-specific branding and gallery support.
 */

$comment = $GLOBALS['comment'];
if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) return;

if ( ! $comment->comment_approved ) return;

$comment_id = get_comment_ID();
$ota_source = get_comment_meta( $comment_id, 'ota_source', true );
$rating     = get_comment_meta( $comment_id, 'comment_rate', true ) ?: 5;
$title      = get_comment_meta( $comment_id, 'comment_title', true );
$photo_url  = get_comment_meta( $comment_id, 'comment_photo', true );

// Determine Source Branding
$source_label = 'Direct Review';
$source_class = '';
$source_icon  = '👤';

if ( $ota_source === 'getyourguide' || get_comment_meta( $comment_id, 'gyg_review_id', true ) ) {
    $source_label = 'GetYourGuide';
    $source_class = 'ota-badge-gyg';
    $source_icon  = '🟠';
} elseif ( $ota_source === 'tripadvisor' || get_comment_meta( $comment_id, 'tripadvisor_review_id', true ) ) {
    $source_label = 'TripAdvisor';
    $source_class = 'ota-badge-tripadvisor';
    $source_icon  = '🦉';
} elseif ( $ota_source === 'viator' || get_comment_meta( $comment_id, 'viator_review_id', true ) ) {
    $source_label = 'Viator';
    $source_class = 'ota-badge-viator';
    $source_icon  = '🟢';
} elseif ( $ota_source === 'gmb' || get_comment_meta( $comment_id, 'gmb_review_id', true ) ) {
    $source_label = 'Google';
    $source_class = 'ota-badge-gmb';
    $source_icon  = '🔵';
}
?>

<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'modern-ota-review' ); ?>>
    <div class="ota-review-header">
        <div class="ota-author-meta">
            <?php echo get_avatar( $comment, 48, '', '', [ 'class' => 'ota-avatar' ] ); ?>
            <div class="ota-author-info">
                <h6><?php comment_author(); ?></h6>
                <div class="ota-date"><?php printf( __( '%s ago', 'traveler' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?></div>
            </div>
        </div>
        <div class="ota-source-badge <?php echo esc_attr( $source_class ); ?>">
            <span><?php echo $source_icon; ?></span> <?php echo esc_html( $source_label ); ?>
        </div>
    </div>

    <div class="ota-stars">
        <?php for ( $i = 1; $i <= 5; $i++ ): ?>
            <i class="fa <?php echo ( $i <= $rating ) ? 'fa-star' : 'fa-star-o'; ?>"></i>
        <?php endfor; ?>
    </div>

    <?php if ( $title ): ?>
        <div class="ota-title">"<?php echo esc_html( $title ); ?>"</div>
    <?php endif; ?>

    <div class="ota-content">
        <?php 
        $content = get_comment_text();
        echo wp_kses_post( $content );
        ?>
    </div>

    <?php if ( $photo_url ): ?>
        <div class="ota-photo-gallery">
            <img src="<?php echo esc_url( $photo_url ); ?>" class="ota-photo" alt="Review Photo" loading="lazy">
        </div>
    <?php endif; ?>
</li>
