<?php
/**
 * Uninstall feedback modal and handler for Accessibility Widget
 *
 * @link       https://www.cookieyes.com/
 * @since      3.1.0
 *
 * @package    CookieYes\AccessibilityWidget\Lite\Includes
 */

namespace CookieYes\AccessibilityWidget\Lite\Includes;

use function add_action;
use function __;
use function esc_html_e;
use function esc_attr;
use function esc_attr_e;
use function esc_js;
use function esc_html;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function sanitize_email;
use function home_url;
use function get_bloginfo;
use function get_locale;
use function get_available_languages;
use function wp_get_theme;
use function is_multisite;
use function wp_remote_post;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_create_nonce;
use function wp_verify_nonce;
use function current_user_can;
use function wp_unslash;
use function wp_die;
use function phpversion;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uninstall feedback modal and handler for Accessibility Widget
 */
class Uninstall_Feedback {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_footer', array( $this, 'deactivate_scripts' ) );
		add_action( 'wp_ajax_cya11y_submit_uninstall_reason', array( $this, 'send_uninstall_reason' ) );
	}

	/**
	 * Get uninstall reasons
	 *
	 * @return array
	 */
	private function get_uninstall_reasons() {
		$reasons = array(
			array(
				'id'     => 'testing-only',
				'text'   => __( 'It was for testing only', 'accessibility-widget' ),
				'banner' => array(
					'message'   => __( 'Test didn\'t go as planned? Tell us what features would make us a better fit.', 'accessibility-widget' ),
					'link_text' => __( 'Suggest a feature', 'accessibility-widget' ),
					'link_url'  => 'https://wordpress.org/support/plugin/accessibility-widget/#new-post',
				),
			),
			array(
				'id'     => 'cant-figure-out',
				'text'   => __( 'I couldn\'t figure out how to use it', 'accessibility-widget' ),
				'fields' => array(
					array(
						'type'        => 'textarea',
						'label'       => __( 'What was confusing or difficult?', 'accessibility-widget' ),
						'placeholder' => __( 'e.g. Navigation was difficult, settings were unclear...', 'accessibility-widget' ),
					),
				),
				'banner' => array(
					'message'   => __( 'Need quick help? Our team can guide you step-by-step.', 'accessibility-widget' ),
					'link_text' => __( 'Connect with support', 'accessibility-widget' ),
					'link_url'  => 'https://wordpress.org/support/plugin/accessibility-widget/#new-post',
				),
			),
			array(
				'id'     => 'technical-issues',
				'text'   => __( 'It caused technical issues on my site', 'accessibility-widget' ),
				'fields' => array(
					array(
						'type'        => 'textarea',
						'label'       => __( 'What issue did you experience? (e.g., layout break, performance issue, plugin conflict)', 'accessibility-widget' ),
						'placeholder' => __( 'Describe the technical issue in detail...', 'accessibility-widget' ),
					),
				),
				'banner' => array(
					'message'   => __( 'We can usually resolve compatibility issues quickly.', 'accessibility-widget' ),
					'link_text' => __( 'Contact support', 'accessibility-widget' ),
					'link_url'  => 'https://wordpress.org/support/plugin/accessibility-widget/#new-post',
				),
			),
			array(
				'id'     => 'compliance-concern',
				'text'   => __( 'I\'m concerned about compliance or legal coverage', 'accessibility-widget' ),
				'fields' => array(
					array(
						'type'        => 'textarea',
						'label'       => __( 'What concerns do you have?', 'accessibility-widget' ),
						'placeholder' => __( 'Share your legal or compliance questions...', 'accessibility-widget' ),
					),
				),
			),
			array(
				'id'     => 'feature-missing',
				'text'   => __( 'I need a feature that isn\'t available', 'accessibility-widget' ),
				'fields' => array(
					array(
						'type'        => 'textarea',
						'label'       => __( 'Which feature were you looking for?', 'accessibility-widget' ),
						'placeholder' => __( 'Describe the feature you need...', 'accessibility-widget' ),
					),
				),
				'banner' => array(
					'message'   => __( 'We\'re actively improving AccessYes and prioritizing feature requests.', 'accessibility-widget' ),
					'link_text' => __( 'Submit feature request', 'accessibility-widget' ),
					'link_url'  => 'https://wordpress.org/support/plugin/accessibility-widget/#new-post',
				),
			),
			array(
				'id'     => 'found-another-solution',
				'text'   => __( 'I found another solution', 'accessibility-widget' ),
				'fields' => array(
					array(
						'type'        => 'textarea',
						'label'       => __( 'What made the other solution a better fit?', 'accessibility-widget' ),
						'placeholder' => __( 'Which alternative are you using and why?', 'accessibility-widget' ),
					),
				),
				'banner' => array(
					'message'   => __( 'If there\'s something missing, let us know — we may already support it.', 'accessibility-widget' ),
					'link_text' => __( 'Tell us what’s missing', 'accessibility-widget' ),
					'link_url'  => 'https://wordpress.org/support/plugin/accessibility-widget/#new-post',
				),
			),
		);
		return $reasons;
	}

	/**
	 * Add deactivation scripts (modal + JS)
	 */
	public function deactivate_scripts() {
		global $pagenow;
		if ( 'plugins.php' !== $pagenow ) {
			return;
		}
		$reasons = $this->get_uninstall_reasons();
		$reasons = ( isset( $reasons ) && is_array( $reasons ) ) ? $reasons : array();

		// Generate nonce for security
		$nonce = wp_create_nonce( 'cya11y_uninstall_feedback_nonce' );
		?>
			<div class="cya11y-modal" id="cya11y-uninstall-modal" role="dialog" aria-modal="true" aria-labelledby="cya11y-modal-title" aria-hidden="true">
				<div class="cya11y-modal-wrap">
					<div class="cya11y-modal-header">
						<h2 id="cya11y-modal-title"><?php esc_html_e( 'Before you deactivate, help us improve', 'accessibility-widget' ); ?></h2>
						<button class="cya11y-modal-close" aria-label="<?php esc_attr_e( 'Close dialog', 'accessibility-widget' ); ?>">&times;</button>
					</div>
					<div class="cya11y-modal-body">
						<p class="cya11y-feedback-caption"><?php echo esc_html__( 'Your feedback helps us make AccessYes better.', 'accessibility-widget' ); ?></p>
						
						<h3 id="cya11y-reasons-question" class="cya11y-feedback-question"><?php echo esc_html__( 'Why are you deactivating AccessYes?', 'accessibility-widget' ); ?></h3>

						<ul class="cya11y-feedback-reasons-list" role="radiogroup" aria-labelledby="cya11y-reasons-question">
							<?php foreach ( $reasons as $reason ) : ?>
								<li>
									<div class="cya11y-feedback-form-group">
										<label class="cya11y-feedback-label">
											<input type="radio" name="selected-reason" value="<?php echo esc_attr( $reason['id'] ); ?>" class="cya11y-feedback-input-radio">
											<?php echo esc_html( $reason['text'] ); ?>
										</label>
										<div class="cya11y-feedback-dynamic-content" data-reason="<?php echo esc_attr( $reason['id'] ); ?>" style="display:none;">
											<?php
											// Render Fields (Textarea/Input)
											if ( ! empty( $reason['fields'] ) ) {
												foreach ( $reason['fields'] as $field ) {
													$field_type        = isset( $field['type'] ) ? $field['type'] : 'text';
													$field_placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
													$field_name        = $reason['id'] . '-info'; // Simplified name

													// Label for accessibility (visually hidden or explicit)
													$label_text = isset( $field['label'] ) ? $field['label'] : $reason['text'];
													$label_class = isset( $field['label'] ) ? 'cya11y-feedback-input-label' : 'screen-reader-text';
													
													if ( 'textarea' === $field_type ) {
														echo '<label for="cya11y-reason-' . esc_attr($reason['id']) . '" class="' . esc_attr( $label_class ) . '">' . esc_html($label_text) . '</label>';
														echo '<textarea id="cya11y-reason-' . esc_attr($reason['id']) . '" rows="3" class="cya11y-feedback-input-field" name="reason_info" placeholder="' . esc_attr( $field_placeholder ) . '"></textarea>';
													} else {
														echo '<label for="cya11y-reason-' . esc_attr($reason['id']) . '" class="' . esc_attr( $label_class ) . '">' . esc_html($label_text) . '</label>';
														echo '<input type="text" id="cya11y-reason-' . esc_attr($reason['id']) . '" class="cya11y-feedback-input-field" name="reason_info" placeholder="' . esc_attr( $field_placeholder ) . '">';
													}
												}
											}

											// Render Banners based on Reason Data
											if ( ! empty( $reason['banner'] ) ) {
												?>
												<div class="cya11y-feedback-banner">
													<p>
														<?php echo esc_html( $reason['banner']['message'] ); ?>
														<a href="<?php echo esc_url( $reason['banner']['link_url'] ); ?>" target="_blank">
															<?php echo esc_html( $reason['banner']['link_text'] ); ?>
															<span class="dashicons dashicons-external"></span>
														</a>
													</p>
												</div>
												<?php
											}
											?>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="cya11y-uninstall-feedback-privacy">
							<?php 
								printf(
									/* translators: %s: Privacy Policy */
									esc_html__( 'We don’t collect personal data in this form. We only use your feedback to improve AccessYes. %s', 'accessibility-widget' ),
									'<a href="https://www.cookieyes.com/privacy-policy/" target="_blank">' . esc_html__( 'Privacy Policy', 'accessibility-widget' ) . '</a>'
								);
							?>
						</div>
					</div>
					<div class="cya11y-modal-footer">
						<div class="cya11y-footer-left">
							<button class="button button-primary cya11y-modal-submit">
								<?php echo esc_html__( 'Submit & Deactivate', 'accessibility-widget' ); ?>
							</button>
							<a href="https://wordpress.org/support/plugin/accessibility-widget/#new-post" target="_blank" class="cya11y-goto-support">
								<span class="dashicons dashicons-external"></span>
								<?php echo esc_html__( 'Go to support', 'accessibility-widget' ); ?>
							</a>
						</div>
						<button class="button-link cya11y-modal-skip">
							<?php echo esc_html__( 'Skip & Deactivate', 'accessibility-widget' ); ?>
						</button>
					</div>
				</div>
			</div>

			<style type="text/css">
				.cya11y-modal {
					position: fixed;
					z-index: 99999;
					top: 0;
					right: 0;
					bottom: 0;
					left: 0;
					background: rgba(0, 0, 0, 0.5);
					display: none;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				}
				.cya11y-modal.modal-active {
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.cya11y-modal-wrap {
					width: 550px;
					max-width: 90%;
					background: #fff;
					border-radius: 8px;
					box-shadow: 0 4px 10px rgba(0,0,0,0.1);
					overflow: hidden;
					display: flex;
					flex-direction: column;
				}
				.cya11y-modal-header {
					padding: 24px 30px 0 30px;
					display: flex;
					align-items: flex-start;
					justify-content: space-between;
				}
				.cya11y-modal-close {
					background: none;
					border: none;
					cursor: pointer;
					font-size: 22px;
					line-height: 1;
					color: #787c82;
					padding: 0 0 0 12px;
					margin: -2px 0 0 0;
					flex-shrink: 0;
				}
				.cya11y-modal-close:hover {
					color: #1e1e1e;
				}
				.cya11y-modal-header h2 {
					margin: 0;
					font-size: 20px;
					font-weight: 600;
					color: #1e1e1e;
				}
				.cya11y-modal-body {
					padding: 10px 30px 20px 30px;
					font-size: 14px;
					color: #3c434a;
					overflow-y: auto;
					max-height: 70vh;
				}
				.cya11y-feedback-caption {
					margin: 0 0 20px 0;
					font-size: 14px;
					color: #646970;
				}
				.cya11y-feedback-question {
					margin: 0 0 15px 0;
					font-size: 14px;
					font-weight: 600;
					color: #1e1e1e;
				}
				.cya11y-feedback-reasons-list {
					margin: 0;
					padding: 0;
					list-style: none;
				}
				.cya11y-feedback-reasons-list li {
					margin-bottom: 12px;
				}
				.cya11y-feedback-label {
					display: flex;
					align-items: center;
					cursor: pointer;
					font-size: 14px;
					color: #3c434a;
				}
				.cya11y-feedback-input-radio {
					margin-right: 12px !important;
					margin-top: 0 !important;
				}
				.cya11y-feedback-dynamic-content {
					margin-left: 28px;
					margin-top: 10px;
					margin-bottom: 10px;
				}
				.cya11y-feedback-input-label {
					display: block;
					font-weight: 600;
					color: #3c434a;
					margin-bottom: 4px;
					font-size: 13px;
				}
				.cya11y-feedback-input-field {
					width: 100%;
					padding: 8px 12px;
					border: 1px solid #c3c4c7;
					border-radius: 4px;
					font-size: 13px;
					margin-bottom: 8px;
				}
				.cya11y-feedback-banner {
					background-color: #f0f6fc;
					border-left: 4px solid #72aee6;
					padding: 12px;
					margin-top: 5px;
					border-radius: 0 4px 4px 0;
				}
				.cya11y-feedback-banner p {
					margin: 0;
					font-size: 13px;
					line-height: 1.5;
				}
				.cya11y-feedback-banner a {
					display: block;
					margin-top: 6px;
					font-weight: 500;
					text-decoration: none;
				}
				.cya11y-feedback-banner a .dashicons {
					font-size: 14px;
					line-height: 1.5;
					margin-left: 2px;
				}
				.cya11y-error-message {
					color: #d63638;
					font-size: 13px;
					margin-top: 10px;
				}
				.cya11y-uninstall-feedback-privacy {
					margin-top: 25px;
					font-size: 12px;
					color: #8c8f94;
				}
				.cya11y-uninstall-feedback-privacy a {
					color: #2271b1;
					text-decoration: none;
				}
				.cya11y-modal-footer {
					padding: 16px 30px;
					background: #fff;
					border-top: 1px solid #f0f0f1;
					display: flex;
					justify-content: space-between;
					align-items: center;
				}
				.cya11y-footer-left {
					display: flex;
					gap: 12px;
					align-items: center;
				}
				.cya11y-modal-skip {
					color: #2271b1;
					text-decoration: none;
					font-size: 13px;
					padding: 0;
					background: none;
					border: none;
					cursor: pointer;
					opacity: 0.6;
				}
				.cya11y-goto-support {
					font-size: 13px;
					color: #2271b1;
					text-decoration: none;
					display: flex;
					align-items: center;
					gap: 4px;
				}
				.cya11y-goto-support .dashicons {
					font-size: 14px;
					line-height: 1.5;
				}
				.cya11y-modal-submit {
					background-color: #2271b1;
					border-color: #2271b1;
					color: #fff;
				}
				.screen-reader-text {
					border: 0;
					clip: rect(1px, 1px, 1px, 1px);
					-webkit-clip-path: inset(50%);
					clip-path: inset(50%);
					height: 1px;
					margin: -1px;
					overflow: hidden;
					padding: 0;
					position: absolute;
					width: 1px;
					word-wrap: normal !important;
				}
			</style>
			<script type="text/javascript">
			(function($){
				$(function(){
					const modal = $('#cya11y-uninstall-modal');
					let deactivateLink = '';
					const nonce = '<?php echo esc_js( $nonce ); ?>';
					const firstFocusableElement = modal.find('h2'); // Initial focus on title or close button if present (removed close button for redesign, title is good for context)
					
					// Focus Trap Logic
					const focusableElementsString = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';
					
					function trapFocus(e) {
						const focusableElements = modal.find(focusableElementsString);
						const firstTab = focusableElements.first();
						const lastTab = focusableElements.last();

						if (e.keyCode === 9) { // Tab key
							if (e.shiftKey) { // Shift + Tab
								if (document.activeElement === firstTab[0]) {
									e.preventDefault();
									lastTab.focus();
								}
							} else { // Tab
								if (document.activeElement === lastTab[0]) {
									e.preventDefault();
									firstTab.focus();
								}
							}
						}
					}

					// Open Modal
					$('#the-list').on('click', 'a[href*="action=deactivate"][href*="accessibility-widget"]', function(e){
						e.preventDefault();
						deactivateLink = $(this).attr('href');
						
						// Reset form
						modal.find('input[type="radio"]').prop('checked', false);
						modal.find('.cya11y-feedback-dynamic-content').hide();
						modal.find('.cya11y-feedback-input-field').val('');
						$('#cya11y-feedback-error').hide();

						modal.addClass('modal-active').attr('aria-hidden', 'false');
						modal.on('keydown', trapFocus);
						// Focus on the close button so no radio button gets highlighted by default
						modal.find('.cya11y-modal-close').focus(); 
					});

					// Close Modal Logic (Close button, Escape)
					function closeModal() {
						modal.removeClass('modal-active').attr('aria-hidden', 'true');
						modal.off('keydown', trapFocus);
					}

					modal.on('click', '.cya11y-modal-close', function(e) {
						e.preventDefault();
						closeModal();
					});

					// Skip Feedback -> Just Deactivate
					modal.on('click', '.cya11y-modal-skip', function(e) {
						e.preventDefault();
						window.location.href = deactivateLink;
					});

					// Close on Escape key
					$(document).on('keydown', function(e) {
						if (e.keyCode === 27 && modal.hasClass('modal-active')) { // Escape key
							closeModal();
						}
					});

					// Handle Radio Selection
					modal.on('change', 'input[type="radio"]', function(){
						const selectedReason = $(this).val();
						
						// Hide other dynamic content
						const $allContent = modal.find('.cya11y-feedback-dynamic-content');
						const $targetContent = $allContent.filter(`[data-reason="${selectedReason}"]`);
						const $otherContent = $allContent.not($targetContent);

						$otherContent.slideUp();
						$otherContent.find('.cya11y-feedback-input-field').prop('disabled', true); // Disable hidden inputs

						// Show content for selected reason
						if ($targetContent.length) {
							$targetContent.slideDown();
							$targetContent.find('.cya11y-feedback-input-field').prop('disabled', false).focus();
						}
						
						$('#cya11y-feedback-error').hide();
					});

					// Submit Feedback
					modal.on('click', 'button.cya11y-modal-submit', function(e){
						e.preventDefault();
						const button = $(this);
						if (button.hasClass('disabled')) { return; }

						const $radio = $('input[type="radio"]:checked', modal);
						const reason_id = $radio.length ? $radio.val() : 'none';

						let reason_info = '';
						if ($radio.length) {
							const $visibleContent = modal.find(`.cya11y-feedback-dynamic-content[data-reason="${reason_id}"]`);
							if ($visibleContent.length) {
								reason_info = $visibleContent.find('.cya11y-feedback-input-field').val();
							}
						}

						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'cya11y_submit_uninstall_reason',
								reason_id: reason_id,
								reason_info: reason_info ? reason_info.trim() : '',
								nonce: nonce
							},
							beforeSend: function(){ button.addClass('disabled').text('Processing...'); },
							complete: function(){ window.location.href = deactivateLink; }
						});
					});
				});
			})(jQuery);
			</script>
			<?php
	}

	/**
	 * AJAX: Send uninstall reason to server
	 */
	public function send_uninstall_reason() {

		// Security check: Verify this is a valid AJAX request
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'accessibility-widget' ) ) );
		}

		// Security check: Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cya11y_uninstall_feedback_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'accessibility-widget' ) ) );
			wp_die();
		}

		// Security check: Verify user capability (only administrators can deactivate plugins)
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'accessibility-widget' ) ) );
			wp_die();
		}

		global $wpdb;
		if ( ! isset( $_POST['reason_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required data.', 'accessibility-widget' ) ) );
			wp_die();
		}
		
		# User requested data structure
		$data = wp_json_encode( array(
			'reason_id'      => sanitize_text_field( wp_unslash( $_POST['reason_id'] ) ),
			'date'           => gmdate( 'M d, Y h:i:s A' ),
			'url'            => home_url(),
			'user_email'     => isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '',
			'reason_info'    => isset( $_POST['reason_info'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason_info'] ) ) : '',
			'software'       => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'php_version'    => phpversion(),
			'mysql_version'  => $wpdb->db_version(),
			'wp_version'     => get_bloginfo( 'version' ),
			'wc_version'     => ( function_exists( 'WC' ) ) ? WC()->version : '',
			'locale'         => get_locale(),
			'languages'      => implode( ',', get_available_languages() ),
			'theme'          => wp_get_theme()->get( 'Name' ),
			'plugin_version' => defined( 'CY_A11Y_VERSION' ) ? CY_A11Y_VERSION : '',
			'is_multisite'   => is_multisite() ? true: false,
		));

		// Send feedback to remote endpoint (non-blocking) - Placeholder URL
		wp_remote_post(
			'https://feedback.cookieyes.com/api/v1/accessyes-feedbacks',
			array(
				'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => false,
				'body'        => $data,
				'cookies'     => array(),
			)
		);

		wp_send_json_success();
	}
}

new Uninstall_Feedback();
