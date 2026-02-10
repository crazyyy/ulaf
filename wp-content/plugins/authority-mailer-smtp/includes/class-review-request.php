<?php
/**
 * Authority Mailer Review Request System
 *
 * Implements a 3-tier review request system to encourage WordPress.org reviews
 * without being intrusive.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Authority_Mailer_Review_Request
 *
 * Manages review requests across three tiers:
 * - Tier 1: Admin notice (after 100 emails OR 7 days + 10 emails)
 * - Tier 2: Settings footer (always visible unless dismissed)
 * - Tier 3: Success toast (after test email)
 */
class Authority_Mailer_Review_Request {

	/**
	 * Review URL on WordPress.org
	 *
	 * @var string
	 */
	const REVIEW_URL = 'https://wordpress.org/support/plugin/authority-mailer-smtp/reviews/#new-post';

	/**
	 * Days to snooze after "Maybe Later" is clicked
	 *
	 * @var int
	 */
	const SNOOZE_DAYS = 15;

	/**
	 * Minimum emails for time-based trigger
	 *
	 * @var int
	 */
	const MIN_EMAILS_TIME_TRIGGER = 10;

	/**
	 * Days of active use for time-based trigger
	 *
	 * @var int
	 */
	const ACTIVE_DAYS_TRIGGER = 7;

	/**
	 * Email count for immediate trigger
	 *
	 * @var int
	 */
	const EMAIL_COUNT_TRIGGER = 100;

	/**
	 * Initialize the review request system
	 *
	 * @return void
	 */
	public static function init() {
		// Hook into email logging to track count.
		add_action( 'authority_mailer_smtp_email_logged', array( __CLASS__, 'increment_email_count' ), 10, 2 );

		// Admin notices (Tier 1).
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_admin_notice' ) );

		// Admin footer (Tier 2).
		add_action( 'admin_footer', array( __CLASS__, 'maybe_show_footer' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_authority_mailer_review_action', array( __CLASS__, 'handle_review_action' ) );

		// Enqueue assets.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Increment email count when an email is successfully logged
	 *
	 * @param int   $log_id  The email log ID.
	 * @param array $data    The email data.
	 * @return void
	 */
	public static function increment_email_count( $log_id, $data ) {
		// Only count successful emails.
		if ( ! isset( $data['status'] ) || 'success' !== $data['status'] ) {
			return;
		}

		$count = get_option( 'authority_mailer_emails_sent_count', 0 );
		++$count;
		update_option( 'authority_mailer_emails_sent_count', $count );

		// Set first email date if not already set.
		if ( ! get_option( 'authority_mailer_first_email_date' ) ) {
			update_option( 'authority_mailer_first_email_date', time() );
		}
	}

	/**
	 * Check if the admin notice should be displayed
	 *
	 * @return bool
	 */
	public static function should_show_notice() {
		// Don't show if permanently dismissed.
		if ( get_option( 'authority_mailer_review_completed' ) ) {
			return false;
		}

		// Don't show if "Maybe Later" was clicked within snooze period.
		$dismissed_date = get_option( 'authority_mailer_review_dismissed_date' );
		if ( $dismissed_date ) {
			$days_since_dismiss = ( time() - $dismissed_date ) / DAY_IN_SECONDS;
			if ( $days_since_dismiss < self::SNOOZE_DAYS ) {
				return false;
			}
		}

		// Check email count.
		$email_count = get_option( 'authority_mailer_emails_sent_count', 0 );

		// Trigger 1: 100 emails sent.
		if ( $email_count >= self::EMAIL_COUNT_TRIGGER ) {
			return true;
		}

		// Trigger 2: 7 days active + 10 emails.
		$first_email_date = get_option( 'authority_mailer_first_email_date' );
		if ( $first_email_date ) {
			$days_active = ( time() - $first_email_date ) / DAY_IN_SECONDS;
			if ( $days_active >= self::ACTIVE_DAYS_TRIGGER && $email_count >= self::MIN_EMAILS_TIME_TRIGGER ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the footer should be displayed
	 *
	 * @return bool
	 */
	public static function should_show_footer() {
		// Hide if permanently dismissed.
		return ! get_option( 'authority_mailer_review_completed' );
	}

	/**
	 * Check if the toast should be displayed after test email
	 *
	 * @return bool
	 */
	public static function should_show_toast() {
		// Hide if permanently dismissed.
		return ! get_option( 'authority_mailer_review_completed' );
	}

	/**
	 * Check if we're on an Authority Mailer admin page
	 *
	 * @return bool
	 */
	public static function is_plugin_page() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		// Check if screen ID contains our plugin slug.
		return strpos( $screen->id, 'authority-mailer-smtp' ) !== false;
	}

	/**
	 * Display admin notice (Tier 1)
	 *
	 * @return void
	 */
	public static function maybe_show_admin_notice() {
		// Only show on plugin pages.
		if ( ! self::is_plugin_page() ) {
			return;
		}

		// Check if notice should be shown.
		if ( ! self::should_show_notice() ) {
			return;
		}

		$email_count = get_option( 'authority_mailer_emails_sent_count', 0 );
		$title       = sprintf( authority_mailer_smtp_get_string( 'review_notice_title' ), $email_count );
		$body        = authority_mailer_smtp_get_string( 'review_notice_body' );
		$btn_review  = authority_mailer_smtp_get_string( 'review_btn_leave_review' );
		$btn_later   = authority_mailer_smtp_get_string( 'review_btn_maybe_later' );
		$btn_did     = authority_mailer_smtp_get_string( 'review_btn_already_did' );

		?>
		<div class="notice notice-info is-dismissible authority-mailer-review-notice" id="authority-mailer-review-notice">
			<div class="authority-mailer-review-notice-content">
				<p class="authority-mailer-review-notice-title">🎉 <?php echo esc_html( $title ); ?></p>
				<p class="authority-mailer-review-notice-body"><?php echo esc_html( $body ); ?> 🙏</p>
				<p class="authority-mailer-review-notice-buttons">
					<a href="<?php echo esc_url( self::REVIEW_URL ); ?>" 
						class="button button-primary authority-mailer-review-btn" 
						data-action="leave_review" 
						target="_blank" 
						rel="noopener noreferrer">
						<?php echo esc_html( $btn_review ); ?>
					</a>
					<button type="button" class="button authority-mailer-review-btn" data-action="maybe_later">
						<?php echo esc_html( $btn_later ); ?>
					</button>
					<button type="button" class="button authority-mailer-review-btn" data-action="already_did">
						<?php echo esc_html( $btn_did ); ?>
					</button>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Display footer review link (Tier 2)
	 *
	 * @return void
	 */
	public static function maybe_show_footer() {
		// Only show on plugin pages.
		if ( ! self::is_plugin_page() ) {
			return;
		}

		// Check if footer should be shown.
		if ( ! self::should_show_footer() ) {
			return;
		}

		$text = authority_mailer_smtp_get_string( 'review_footer_text' );
		$link = authority_mailer_smtp_get_string( 'review_footer_link' );

		?>
		<div class="authority-mailer-review-footer">
			<p>💙 <?php echo esc_html( $text ); ?> <a href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Handle AJAX review actions
	 *
	 * @return void
	 */
	public static function handle_review_action() {
		// Verify nonce.
		check_ajax_referer( 'authority_mailer_review', 'nonce' );

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied' ) );
		}

		// Get action.
		$action = isset( $_POST['review_action'] ) ? sanitize_text_field( wp_unslash( $_POST['review_action'] ) ) : '';

		switch ( $action ) {
			case 'leave_review':
			case 'already_did':
				// Permanently dismiss.
				update_option( 'authority_mailer_review_completed', true );
				wp_send_json_success( array( 'message' => 'Review completed' ) );
				break;

			case 'maybe_later':
				// Set snooze timestamp.
				update_option( 'authority_mailer_review_dismissed_date', time() );
				wp_send_json_success( array( 'message' => 'Snoozed for ' . self::SNOOZE_DAYS . ' days' ) );
				break;

			default:
				wp_send_json_error( array( 'message' => 'Invalid action' ) );
		}
	}

	/**
	 * Enqueue CSS and JavaScript assets
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		// Only enqueue on plugin pages.
		if ( ! self::is_plugin_page() ) {
			return;
		}

		// Enqueue CSS.
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/review-request.css';
		$css_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/review-request.css';
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : AUTHORITY_MAILER_VERSION;
		wp_enqueue_style( 'authority-mailer-review-request', $css_url, array(), $css_ver );

		// Enqueue JS.
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/review-request.js';
		$js_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/review-request.js';
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : AUTHORITY_MAILER_VERSION;
		wp_enqueue_script( 'authority-mailer-review-request', $js_url, array( 'jquery' ), $js_ver, true );

		// Localize script.
		wp_localize_script(
			'authority-mailer-review-request',
			'authorityMailerReview',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'authority_mailer_review' ),
				'reviewUrl'    => self::REVIEW_URL,
				'showToast'    => self::should_show_toast(),
				'toastSuccess' => authority_mailer_smtp_get_string( 'review_toast_success' ),
				'toastPrompt'  => authority_mailer_smtp_get_string( 'review_toast_prompt' ),
				'toastLink'    => authority_mailer_smtp_get_string( 'review_toast_link' ),
			)
		);
	}
}
