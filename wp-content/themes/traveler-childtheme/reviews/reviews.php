<?php
/**
 * Reviews form — Child Theme Override
 * Adds: optional photo upload (≤5 MB) with client-side size check and
 *       a link to free online compressors when the file is too large.
 */

if ( post_password_required() ) return;

$potype  = get_post_type( get_the_ID() );
$item_id = get_the_ID();
$obj     = get_post_type_object( $potype );
$name    = $obj->labels->singular_name;

wp_enqueue_script( 'comment' );
wp_enqueue_script( 'comment-reply' );
wp_enqueue_script( 'st-reviews-form' );
?>
<div id="comments" class="comments-area">

    <?php
    $number = (int) STReview::count_all_comment( $item_id );
    if ( $number ) : ?>

        <ul class="booking-item-reviews list">
            <?php
            wp_list_comments( [
                'style'      => 'ul',
                'short_ping' => true,
                'avatar_size'=> 74,
                'callback'   => [ 'TravelHelper', 'reviewlist' ],
                'per_page'   => get_option( 'comments_per_page' ),
            ] );
            ?>
        </ul>

        <div class="gap gap-small"></div>
        <div class="row wrap">
            <div class="col-md-5">
                <p><small><?php
                    if ( $number > 1 ) {
                        $name = $obj->labels->name;
                        printf( st_get_language( 's_reviews' ), $number );
                    } else {
                        printf( st_get_language( 's_review' ), $number );
                        $name = $obj->labels->singular_name;
                    }
                    if ( $name === 'Hotels' ) $name = $obj->labels->singular_name;

                    $limit = get_option( 'comments_per_page' );
                    $page  = get_query_var( 'cpage' ) ?: 1;
                    $to    = min( $page * $limit, STReview::count_all_comment( $item_id ) );
                    echo ' ' . st_get_language( 'on_this' ) . ' ' . $name . ' &nbsp;&nbsp; ' . st_get_language( 'showing' );
                    printf( st_get_language( 'd_to_d' ), ( $limit * ( $page - 1 ) ) + 1, $to );
                ?></small></p>
            </div>
            <div class="col-md-7">
                <?php
                if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
                    TravelHelper::comments_paging();
                }
                ?>
            </div>
        </div>

    <?php endif;
    if ( ! $number ) : ?>
        <div class="alert alert-warning"><?php _e( 'There is no review', 'traveler' ) ?></div>
    <?php endif; ?>

    <div class="gap gap-small"></div>

    <?php
    $commenter = wp_get_current_commenter();

    /* ── Photo upload JS (inline, small) ─────────────────────────────── */
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const photoInput  = document.getElementById('review_photo');
        const photoError  = document.getElementById('review_photo_error');
        const photoPreview= document.getElementById('review_photo_preview');
        if (!photoInput) return;

        photoInput.addEventListener('change', function () {
            photoError.innerHTML  = '';
            photoPreview.innerHTML = '';
            const file = this.files[0];
            if (!file) return;

            const maxMB  = 5;
            const maxBytes = maxMB * 1024 * 1024;

            if (file.size > maxBytes) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                photoError.innerHTML =
                    '⚠ File is <strong>' + sizeMB + ' MB</strong> — max is <strong>' + maxMB + ' MB</strong>. ' +
                    'Please reduce the image size first: ' +
                    '<a href="https://squoosh.app/" target="_blank" rel="noopener">Squoosh (free)</a>, ' +
                    '<a href="https://tinypng.com/" target="_blank" rel="noopener">TinyPNG</a>, or ' +
                    '<a href="https://imagecompressor.com/" target="_blank" rel="noopener">imagecompressor.com</a>.';
                this.value = '';
                return;
            }

            // Preview
            const reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.innerHTML =
                    '<img src="' + e.target.result + '" alt="Preview" ' +
                    'style="max-width:160px;max-height:120px;border-radius:6px;margin-top:0.5rem;border:1px solid #e2e8f0;" ' +
                    'loading="lazy">';
            };
            reader.readAsDataURL(file);
        });

        // Block form submit if file still too large
        const form = document.getElementById('commentform');
        if (form) {
            form.addEventListener('submit', function (e) {
                const file = photoInput.files[0];
                if (file && file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    photoError.innerHTML = '⚠ Please compress your photo below 5 MB before submitting.';
                }
            });
        }
    });
    </script>

    <?php
    $comment_form = [
        'title_reply'          => __( 'Provide your Feedback', 'traveler' ),
        'title_reply_to'       => st_get_language( 'leave_a_reply_to' ) . __( ' %s', 'traveler' ),
        'comment_notes_before' => '',
        'fields'               => [
            'author' => '<div class="row"><div class="col-md-6"><div class="form-group">
                <label for="author">' . __( 'Name*', 'traveler' ) . '</label>
                <input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" class="form-control">
                </div></div>',
            'email'  => '<div class="col-md-6"><div class="form-group">
                <label for="email">' . __( 'Your email address *', 'traveler' ) . '</label>
                <input class="form-control" id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-required="true">
                </div></div></div>',
        ],
        'label_submit'        => __( 'Provide your Feedback', 'traveler' ),
        'logged_in_as'        => '',
        'comment_field'       => '',
        'comment_notes_after' => '',
    ];

    /* Rating stars */
    $comment_form['comment_field'] = '
        <input name="comment_type" value="st_reviews" type="hidden">
        <div class="form-group">
            <label>' . st_get_language( 'your_rating' ) . '</label>
            <ul class="icon-list add_rating icon-group booking-item-rating-stars">
                <li><i class="fa fa-star-o text-color"></i></li>
                <li><i class="fa fa-star-o text-color"></i></li>
                <li><i class="fa fa-star-o text-color"></i></li>
                <li><i class="fa fa-star-o text-color"></i></li>
                <li><i class="fa fa-star-o text-color"></i></li>
            </ul>
            <input name="comment_rate" class="comment_rate" type="hidden">
        </div>';

    /* Review title */
    $comment_form['comment_field'] .= '
        <div class="form-group">
            <label for="label_comment_title">' . st_get_language( 'review_title' ) . '</label>
            <input class="form-control" type="text" name="comment_title" id="label_comment_title">
        </div>';

    /* Review text */
    $comment_form['comment_field'] .= '
        <div class="form-group">
            <label for="comment">' . st_get_language( 'review_text' ) . '</label>
            <textarea name="comment" id="comment" class="form-control" rows="6"></textarea>
        </div>';

    /* ── Photo upload field ───────────────────────────────────────────── */
    $comment_form['comment_field'] .= '
        <div class="form-group review-photo-group" style="margin-top:1rem;">
            <label for="review_photo" style="display:flex;align-items:center;gap:0.5rem;">
                📷 ' . __( 'Add a photo', 'traveler' ) . '
                <span style="font-size:0.78rem;color:#94a3b8;font-weight:400;">(optional · max 5 MB · JPG/PNG/WebP)</span>
            </label>
            <input type="file" id="review_photo" name="review_photo" accept="image/jpeg,image/png,image/webp,image/gif"
                   style="margin-top:0.4rem;display:block;">
            <div id="review_photo_error" style="margin-top:0.4rem;font-size:0.82rem;color:#e53e3e;"></div>
            <div id="review_photo_preview"></div>
            <p style="font-size:0.75rem;color:#94a3b8;margin-top:0.35rem;">
                Image too large? Compress it free:
                <a href="https://squoosh.app/" target="_blank" rel="noopener">Squoosh</a> ·
                <a href="https://tinypng.com/" target="_blank" rel="noopener">TinyPNG</a> ·
                <a href="https://imagecompressor.com/" target="_blank" rel="noopener">imagecompressor.com</a>
            </p>
        </div>';

    /* Allow multipart form for file upload */
    $comment_form['id_form']   = 'commentform';
    // Note: WordPress comment_form() doesn't support enctype natively.
    // We add it via JS below and also via a filter in functions.php.

    $comment_form_arg = apply_filters( get_post_type( $item_id ) . '_wp_review_form_args', $comment_form, $item_id );
    $review_check     = STReview::review_check( $item_id );

    switch ( $review_check ) {
        case 'true':
            echo '<div class="box bg-gray">';
            comment_form( $comment_form_arg );
            // Make the form multipart so file uploads work
            echo '<script>
            (function(){
                var f = document.getElementById("commentform");
                if(f){ f.setAttribute("enctype","multipart/form-data"); f.setAttribute("method","post"); }
            })();
            </script>';
            echo '</div>';
            break;

        case 'must_login':
            echo '<div class="box bg-gray">';
            $enable_popup_login = st()->get_option( 'enable_popup_login', 'off' );
            $page_login         = st()->get_option( 'page_user_login' );
            $login_modal        = '';
            $page_login         = esc_url( get_the_permalink( $page_login ) );
            if ( $enable_popup_login === 'on' ) {
                $login_modal = 'data-toggle="modal" data-target="#login_popup"';
                $page_login  = 'javascript:void(0)';
            }
            echo sprintf( st_get_language( 'you_must' ) . '<a ' . $login_modal . ' href="' . $page_login . '">' . __( 'log in ', 'traveler' ) . '</a>' . st_get_language( 'to_write_review' ), get_permalink( st()->get_option( 'user_login_page' ) ) );
            echo '</div>';
            break;

        case 'need_open':
            echo '<div class="box bg-gray">' . esc_html__( 'Review is disabled from administrator', 'traveler' ) . '</div>';
            break;

        case 'need_booked':
            echo '<div class="box bg-gray">';
            _e( 'You must make a booking before writing a review', 'traveler' );
            echo '</div>';
            break;

        case 'wait_check_out_date':
            echo '<div class="box bg-gray">';
            global $wp_post_types;
            $obj  = $wp_post_types[ get_post_type( get_the_ID() ) ];
            $name = $obj->labels->singular_name;
            _e( 'You must experience of this ', 'traveler' );
            _e( $name );
            _e( ' to write review', 'traveler' );
            echo '</div>';
            break;

        case 'reviewed':
            echo '<div class="box bg-gray">' . st_get_language( 'you_have_been_post_a_review' ) . ' ' . $name . '</div>';
            break;
    }
    ?>

</div><!-- #comments -->
