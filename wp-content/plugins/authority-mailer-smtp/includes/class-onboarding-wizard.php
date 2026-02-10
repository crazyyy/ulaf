<?php
/**
 * Authority Mailer Onboarding Wizard Class
 *
 * Onboarding wizard class: registers AJAX handlers, enqueues assets, localizes
 * strings, handles provider save actions, and renders the UI.
 *
 * The onboarding page is registered from the main plugin bootstrap (authority-mailer-smtp.php)
 * under the Authority Mailer top-level menu. This class intentionally does NOT register
 * the page under Settings to ensure the left menu highlights Authority Mailer → Setup.
 *
 * This revision hardens handling of superglobals:
 *  - always wp_unslash() and properly sanitize incoming $_GET/$_POST values
 *  - when possible verify a generic nonce early (if present) before routing;
 *    provider-specific nonces are still validated in provider handlers
 *    (this is necessary because provider key is derived from the action).
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Authority_Mailer_Onboarding class.
 *
 * Handles onboarding wizard functionality including AJAX handlers, asset enqueuing,
 * string localization, provider save actions, and UI rendering.
 *
 * @since 1.0.0
 */
class Authority_Mailer_Onboarding {

	const NONCE_ACTION = 'authority_mailer_smtp_onboarding_action';
	const OPTION_KEY   = 'authority_mailer_smtp_options';

	/**
	 * List of available providers with metadata used when rendering the choose step.
	 *
	 * @var array
	 */
	private static $providers = array(
		array(
			'id'    => 'sendlayer',
			'label' => 'SendLayer',
			'pro'   => false,
			'icon'  => 'sendlayer.svg',
		),
		array(
			'id'       => 'smtpcom',
			'label'    => 'SMTP.com',
			'pro'      => true,
			'pro_only' => true,
			'icon'     => 'smtpcom.svg',
		),
		array(
			'id'    => 'brevo',
			'label' => 'Brevo',
			'pro'   => true,
			'pro_only' => true,
			'icon'  => 'brevo.svg',
		),
		array(
			'id'       => 'aws',
			'label'    => 'Amazon SES',
			'pro'      => true,
			'pro_only' => true,
			'icon'     => 'amazonses.svg',
		),
		array(
			'id'    => 'elastic',
			'label' => 'Elastic Email',
			'pro'   => false,
			'icon'  => 'elasticemail.svg',
		),
		array(
			'id'    => 'gmail',
			'label' => 'Google / Gmail',
			'pro'   => false,
			'icon'  => 'gmail.svg',
		),
		array(
			'id'    => 'mailersend',
			'label' => 'MailerSend',
			'pro'   => false,
			'icon'  => 'mailersend.svg',
		),
		array(
			'id'    => 'mailgun',
			'label' => 'Mailgun',
			'pro'   => false,
			'icon'  => 'mailgun.svg',
		),
		array(
			'id'    => 'mailjet',
			'label' => 'Mailjet',
			'pro'   => false,
			'icon'  => 'mailjet.svg',
		),
		array(
			'id'    => 'mandrill',
			'label' => 'Mandrill',
			'pro'   => false,
			'icon'  => 'mandrill.svg',
		),
		array(
			'id'       => 'office365',
			'label'    => '365 / Outlook',
			'pro'      => false,
			'icon'     => 'outlook.svg',
		),
		array(
			'id'    => 'postmark',
			'label' => 'Postmark',
			'pro'   => false,
			'icon'  => 'postmark.svg',
		),
		array(
			'id'    => 'sendgrid',
			'label' => 'SendGrid',
			'pro'   => false,
			'icon'  => 'sendgrid.svg',
		),
		array(
			'id'    => 'smtp2go',
			'label' => 'SMTP2GO',
			'pro'   => false,
			'icon'  => 'smtp2go.svg',
		),
		array(
			'id'    => 'sparkpost',
			'label' => 'SparkPost',
			'pro'   => false,
			'icon'  => 'sparkpost.svg',
		),
		array(
			'id'    => 'zoho',
			'label' => 'Zoho Mail',
			'pro'   => false,
			'icon'  => 'zoho.svg',
		),
		array(
			'id'    => 'other',
			'label' => 'Other SMTP',
			'pro'   => false,
			'icon'  => '',
		),
	);

	/**
	 * Known boolean / toggle field names per provider.
	 *
	 * When saving a provider form, checkbox inputs that are unchecked are not
	 * present in $_POST. To allow turning toggles OFF we must explicitly set
	 * those option keys to 0 when they are absent from the request.
	 *
	 * Add provider keys here as needed. Keys are top-level option keys used
	 * in authority_mailer_options array.
	 *
	 * @var array
	 */
	private static $boolean_fields = array(
		'sendlayer'  => array( 'sendlayer_force_from_name', 'sendlayer_force_from_email' ),
		'smtpcom'    => array( 'smtpcom_force_from_name', 'smtpcom_force_from_email' ),
		'brevo'      => array( 'brevo_force_from_name', 'brevo_force_from_email', 'brevo_use_smtp' ),
		'elastic'    => array( 'elasticmail_force_from_name', 'elasticmail_force_from_email' ),
		'mailersend' => array( 'mailersend_force_from_name', 'mailersend_force_from_email' ),
		'mailgun'    => array( 'mailgun_force_from_name', 'mailgun_force_from_email' ),
		'postmark'   => array( 'postmark_force_from_name', 'postmark_force_from_email' ),
		'sendgrid'   => array( 'sendgrid_force_from_name', 'sendgrid_force_from_email' ),
		'smtp2go'    => array( 'smtp2go_force_from_name', 'smtp2go_force_from_email' ),
		'sparkpost'  => array( 'sparkpost_force_from_name', 'sparkpost_force_from_email' ),
		'aws'        => array( 'aws_force_from_name', 'aws_force_from_email' ),
		'office365'  => array( 'office365_force_from_name', 'office365_force_from_email' ),
		'gmail'      => array( 'google_force_from_name', 'google_force_from_email' ),
		'mailjet'    => array( 'mailjet_force_from_name', 'mailjet_force_from_email' ),
		'mandrill'   => array( 'mandrill_force_from_name', 'mandrill_force_from_email' ),
		'zoho'       => array( 'zoho_force_from_name', 'zoho_force_from_email', 'zoho_smtp_auth' ),
		'other'      => array( 'other_force_from_name', 'other_force_from_email', 'other_smtp_auth' ),
	);

	/**
	 * Initialize hooks and provider handlers.
	 */
	public static function init() {
		add_action( 'wp_ajax_authority_mailer_smtp_set_selected_mailer', array( __CLASS__, 'ajax_set_selected_mailer' ) );

		// New AJAX for running a saved test from step 3 (uses saved provider settings)
		add_action( 'wp_ajax_authority_mailer_smtp_run_saved_test', array( __CLASS__, 'ajax_run_saved_test' ) );

		// Register resend AJAX here so we use the same class
		add_action( 'wp_ajax_authority_mailer_smtp_resend_email', array( __CLASS__, 'ajax_resend_email' ) );

		// include both 'gmail' and 'google' here to handle both the tile id and the partial/form naming.
		$handlers = array(
			'sendlayer',
			'smtpcom',
			'brevo',
			'elastic',
			'aws',
			'gmail',
			'google',
			'mailersend',
			'mailgun',
			'mailjet',
			'mandrill',
			'office365',
			'postmark',
			'sendgrid',
			'smtp2go',
			'sparkpost',
			'zoho',
			'other',
		);

		foreach ( $handlers as $h ) {
			$action_ajax = 'authority_mailer_smtp_save_' . $h;
			$action_post = 'admin_post_authority_mailer_save_' . $h;
			add_action( 'wp_ajax_' . $action_ajax, array( __CLASS__, 'handle_provider_ajax_router' ) );
			add_action( $action_post, array( __CLASS__, 'handle_provider_post_router' ) );
		}
	}

	/**
	 * Enqueue onboarding assets and localize strings for JS.
	 *
	 * @param string $hook Admin page hook.
	 */
	public static function enqueue_assets( $hook ) {
		// Only load on our plugin page: safely read and sanitize $_GET['page']
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking page parameter for script enqueue only, no data processing
		$page_param = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'authority-mailer-smtp-onboarding' !== $page_param ) {
			return;
		}

		$css_path       = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/onboarding.css';
		$popup_css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/onboarding-popup.css';
		$js_path        = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/onboarding.js';
		$gmail_oauth_js = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/gmail-oauth.js';

		$css_url         = plugins_url( 'assets/css/onboarding.css', AUTHORITY_MAILER_PLUGIN_FILE );
		$popup_css_url   = plugins_url( 'assets/css/onboarding-popup.css', AUTHORITY_MAILER_PLUGIN_FILE );
		$js_url          = plugins_url( 'assets/js/onboarding.js', AUTHORITY_MAILER_PLUGIN_FILE );
		$gmail_oauth_url = plugins_url( 'assets/js/gmail-oauth.js', AUTHORITY_MAILER_PLUGIN_FILE );

		$css_ver         = file_exists( $css_path ) ? filemtime( $css_path ) : time();
		$popup_css_ver   = file_exists( $popup_css_path ) ? filemtime( $popup_css_path ) : time();
		$js_ver          = file_exists( $js_path ) ? filemtime( $js_path ) : time();
		$gmail_oauth_ver = file_exists( $gmail_oauth_js ) ? filemtime( $gmail_oauth_js ) : time();

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style( 'authority-mailer-smtp-onboarding', $css_url, array(), $css_ver );
		}
		if ( file_exists( $popup_css_path ) ) {
			wp_enqueue_style( 'authority-mailer-smtp-onboarding-popup', $popup_css_url, array( 'authority-mailer-smtp-onboarding' ), $popup_css_ver );
		}

		if ( file_exists( $js_path ) ) {
			wp_register_script( 'authority-mailer-smtp-onboarding', $js_url, array( 'jquery' ), $js_ver, true );
			wp_enqueue_script( 'authority-mailer-smtp-onboarding' );

			global $AUTHORITY_MAILER_STRINGS;

			$strings_for_js                = array();
			$strings_for_js['ajax_url']    = esc_url_raw( admin_url( 'admin-ajax.php' ) );
			$strings_for_js['nonce']       = wp_create_nonce( self::NONCE_ACTION );
			$strings_for_js['upgrade_url'] = apply_filters( 'authority_mailer_smtp_upgrade_url', '' );

			$i18n_keys = array(
				'i18n_select_mailer',
				'i18n_save_error',
				'i18n_request_failed',
				'i18n_api_key_required',
				'i18n_name_required',
				'i18n_sender_name_required',
				'i18n_email_invalid',
				'i18n_saving_settings',
				'i18n_settings_saved',
				'i18n_google_client_id_required',
				'i18n_google_client_secret_required',
				'i18n_sending_domain_required',
				'i18n_sending_domain_invalid',
			);
			foreach ( $i18n_keys as $k ) {
				$strings_for_js[ $k ] = isset( $AUTHORITY_MAILER_STRINGS[ $k ] ) ? $AUTHORITY_MAILER_STRINGS[ $k ] : '';
			}

			$strings_for_js['strings'] = array(
				'pro_modal_title'    => isset( $AUTHORITY_MAILER_STRINGS['pro_modal_title'] ) ? $AUTHORITY_MAILER_STRINGS['pro_modal_title'] : '%s — PRO',
				'pro_modal_intro'    => isset( $AUTHORITY_MAILER_STRINGS['pro_modal_intro'] ) ? $AUTHORITY_MAILER_STRINGS['pro_modal_intro'] : '',
				'pro_modal_benefits' => isset( $AUTHORITY_MAILER_STRINGS['pro_modal_benefits'] ) ? $AUTHORITY_MAILER_STRINGS['pro_modal_benefits'] : array(),
			);

			$strings_for_js['step_labels'] = isset( $AUTHORITY_MAILER_STRINGS['step_labels'] ) ? $AUTHORITY_MAILER_STRINGS['step_labels'] : array();

			// Expose the whole strings array for JS convenience
			$strings_for_js = array_merge( $strings_for_js, (array) $AUTHORITY_MAILER_STRINGS );

			wp_localize_script( 'authority-mailer-smtp-onboarding', 'authorityMailerOnboarding', $strings_for_js );
		}

		// Enqueue Gmail OAuth handler script.
		if ( file_exists( $gmail_oauth_js ) ) {
			wp_enqueue_script( 'authority-mailer-gmail-oauth', $gmail_oauth_url, array( 'jquery', 'authority-mailer-smtp-onboarding' ), $gmail_oauth_ver, true );
		}

		// Enqueue Brevo settings script for SMTP/API toggle functionality.
		$brevo_js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/settings-brevo.js';
		$brevo_js_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/settings-brevo.js';
		$brevo_js_ver  = file_exists( $brevo_js_path ) ? filemtime( $brevo_js_path ) : AUTHORITY_MAILER_VERSION;
		if ( file_exists( $brevo_js_path ) ) {
			wp_enqueue_script( 'authority-mailer-settings-brevo', $brevo_js_url, array(), $brevo_js_ver, true );
		}

		// Enqueue white-glove installation service sidebar CSS/JS.
		$white_glove_css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/white-glove-sidebar.css';
		$white_glove_js_path  = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/white-glove-sidebar.js';

		if ( file_exists( $white_glove_css_path ) ) {
			$white_glove_css_url = plugins_url( 'assets/css/white-glove-sidebar.css', AUTHORITY_MAILER_PLUGIN_FILE );
			$white_glove_css_ver = filemtime( $white_glove_css_path );
			wp_enqueue_style( 'authority-mailer-white-glove-sidebar', $white_glove_css_url, array( 'authority-mailer-smtp-onboarding' ), $white_glove_css_ver );
		}

		if ( file_exists( $white_glove_js_path ) ) {
			$white_glove_js_url = plugins_url( 'assets/js/white-glove-sidebar.js', AUTHORITY_MAILER_PLUGIN_FILE );
			$white_glove_js_ver = filemtime( $white_glove_js_path );
			wp_enqueue_script( 'authority-mailer-white-glove-sidebar', $white_glove_js_url, array( 'jquery' ), $white_glove_js_ver, true );
		}
	}

	/**
	 * AJAX handler: store the selected mailer.
	 */
	public static function ajax_set_selected_mailer() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => '' ), 403 );
		}
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		if ( '' === $provider ) {
			wp_send_json_error( array( 'message' => self::get_string( 'i18n_select_mailer' ) ), 400 );
		}

		$allowed = wp_list_pluck( self::$providers, 'id' );
		if ( ! in_array( $provider, $allowed, true ) ) {
			wp_send_json_error( array( 'message' => self::get_string( 'i18n_save_error' ) ), 400 );
		}

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$options['selected_mailer'] = $provider;
		update_option( self::OPTION_KEY, $options );

		wp_send_json_success(
			array(
				'message'  => self::get_string( 'i18n_settings_saved' ),
				'provider' => $provider,
			)
		);
	}

	/**
	 * New AJAX handler: run saved provider test for the provider already saved in options.
	 */
	public static function ajax_run_saved_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => '' ), 403 );
		}
		// nonce localized as 'nonce'
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$provider = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
		if ( empty( $provider ) ) {
			wp_send_json_error( array( 'message' => 'Missing provider.' ), 400 );
		}

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$settings = isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ? $options[ $provider ] : array();

		// Allow overriding the recipient at test time
		$test_recipient = isset( $_POST['test_recipient'] ) ? sanitize_email( wp_unslash( $_POST['test_recipient'] ) ) : '';
		if ( ! empty( $test_recipient ) && is_email( $test_recipient ) ) {
			$settings['test_recipient'] = $test_recipient;
		}

		$testers_file = __DIR__ . '/testers.php';
		if ( file_exists( $testers_file ) ) {
			require_once $testers_file;
		} else {
			wp_send_json_error( array( 'message' => 'Testers not available.' ), 500 );
		}

		if ( ! function_exists( 'authority_mailer_smtp_test_provider' ) ) {
			wp_send_json_error( array( 'message' => 'Test runner not found.' ), 500 );
		}

		$steps = authority_mailer_smtp_test_provider( $provider, $settings );
		if ( ! is_array( $steps ) ) {
			$steps = array(
				array(
					'status'  => 'error',
					'message' => 'Test runner returned invalid result.',
				),
			);
		}

		wp_send_json_success( array( 'steps' => $steps ) );
	}

	/**
	 * New AJAX handler: resend a previously logged email using the saved provider settings.
	 */
	public static function ajax_resend_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => '' ), 403 );
		}
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$log_id = isset( $_POST['log_id'] ) ? absint( wp_unslash( $_POST['log_id'] ) ) : 0;
		if ( ! $log_id ) {
			wp_send_json_error( array( 'message' => 'Missing log id.' ), 400 );
		}

		// Ensure logger getter exists, try include if missing
		if ( ! function_exists( 'authority_mailer_smtp_email_logger_get' ) ) {
			$authority_mailer_logger_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/email-logger.php';
			if ( file_exists( $authority_mailer_logger_file ) ) {
				require_once $authority_mailer_logger_file;
			}
		}
		if ( ! function_exists( 'authority_mailer_smtp_email_logger_get' ) ) {
			wp_send_json_error( array( 'message' => 'Email logger fetch function not available on this site.' ), 500 );
		}

		$log = authority_mailer_smtp_email_logger_get( $log_id );
		if ( ! $log || ! is_array( $log ) ) {
			wp_send_json_error( array( 'message' => 'Logged email not found.' ), 404 );
		}

		$provider = isset( $log['provider'] ) ? sanitize_key( $log['provider'] ) : '';
		if ( '' === $provider ) {
			wp_send_json_error( array( 'message' => 'Provider not recorded for this log entry.' ), 500 );
		}

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// Start with saved provider-specific settings if available, otherwise fall back to top-level options
		$settings = array();
		if ( isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ) {
			$settings = $options[ $provider ];
		} else {
			$settings = $options;
		}

		// Preserve original recipient/from/subject/body from the log (if present) so resend matches original
		if ( ! empty( $log['to_email'] ) ) {
			$settings['test_recipient'] = sanitize_email( $log['to_email'] );
		}
		if ( ! empty( $log['from_email'] ) ) {
			$settings[ $provider . '_from_email' ] = sanitize_email( $log['from_email'] );
			$settings['from_email']                = sanitize_email( $log['from_email'] );
		}
		if ( ! empty( $log['from_name'] ) ) {
			$settings[ $provider . '_from_name' ] = sanitize_text_field( $log['from_name'] );
			$settings['from_name']                = sanitize_text_field( $log['from_name'] );
		}
		if ( ! empty( $log['subject'] ) ) {
			$settings['test_subject'] = sanitize_text_field( $log['subject'] );
		}

		// If payload is present in log, try to decode and extract message body (best-effort)
		if ( ! empty( $log['payload'] ) ) {
			$payload_raw = $log['payload'];
			$payload_arr = null;
			if ( is_string( $payload_raw ) ) {
				$payload_arr = @json_decode( $payload_raw, true );
			} elseif ( is_array( $payload_raw ) ) {
				$payload_arr = $payload_raw;
			}

			if ( is_array( $payload_arr ) ) {
				if ( ! empty( $payload_arr['Messages'][0]['HTMLPart'] ) ) {
					$settings['html_content'] = (string) $payload_arr['Messages'][0]['HTMLPart'];
				}
				if ( ! empty( $payload_arr['Messages'][0]['TextPart'] ) ) {
					$settings['plain_content'] = (string) $payload_arr['Messages'][0]['TextPart'];
				}
				if ( empty( $settings['html_content'] ) && ! empty( $payload_arr['html'] ) ) {
					$settings['html_content'] = (string) $payload_arr['html'];
				}
				if ( empty( $settings['plain_content'] ) && ! empty( $payload_arr['text'] ) ) {
					$settings['plain_content'] = (string) $payload_arr['text'];
				}
			}
		}

		// Fallback: ensure we have values for at least recipient/from
		if ( empty( $settings['test_recipient'] ) && ! empty( $options['test_recipient'] ) ) {
			$settings['test_recipient'] = sanitize_email( $options['test_recipient'] );
		}
		if ( empty( $settings['from_email'] ) ) {
			$settings['from_email'] = get_option( 'admin_email', '' );
		}
		if ( empty( $settings['from_name'] ) ) {
			$settings['from_name'] = get_bloginfo( 'name' );
		}

		// Load the test runner
		$testers_file = dirname( __DIR__ ) . '/testers.php';
		if ( ! file_exists( $testers_file ) ) {
			wp_send_json_error( array( 'message' => 'Test runner unavailable.' ), 500 );
		}
		require_once $testers_file;

		if ( ! function_exists( 'authority_mailer_smtp_test_provider' ) ) {
			wp_send_json_error( array( 'message' => 'Test runner not found.' ), 500 );
		}

		$steps = authority_mailer_smtp_test_provider( $provider, $settings );
		if ( ! is_array( $steps ) ) {
			$steps = array(
				array(
					'status'  => 'error',
					'message' => 'Resend runner returned invalid data.',
				),
			);
		}

		wp_send_json_success( array( 'steps' => $steps ) );
	}

	/**
	 * Generic router for AJAX provider save actions.
	 *
	 * We try to verify a generic nonce early if present (the JS may send it).
	 * If not present we still need to read 'action' to determine provider so the
	 * provider-specific nonce can be checked in the concrete handler. We sanitize
	 * all inputs using wp_unslash() + sanitize_text_field().
	 */
	public static function handle_provider_ajax_router() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => '' ), 403 );
		}

		// If the request contains a generic nonce (localized), verify it now.
		if ( isset( $_POST['nonce'] ) ) {
			check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( ! $action ) {
			wp_send_json_error( array( 'message' => '' ), 400 );
		}

		$parts = explode( 'authority_mailer_smtp_save_', $action );
		if ( count( $parts ) !== 2 || empty( $parts[1] ) ) {
			wp_send_json_error( array( 'message' => '' ), 400 );
		}

		$provider = sanitize_key( $parts[1] );
		$method   = 'ajax_save_' . $provider;

		if ( is_callable( array( __CLASS__, $method ) ) ) {
			call_user_func( array( __CLASS__, $method ) );
		} else {
			self::generic_ajax_save( $provider );
		}
	}

	/**
	 * Generic router for admin_post provider saves (form POST fallback).
	 *
	 * We attempt to verify a generic nonce early if present (some flows may include it).
	 * If not present we still need to inspect the action to determine provider so the
	 * provider-specific nonce can be validated in the handler that actually processes
	 * the save (generic_post_save does that).
	 */
	public static function handle_provider_post_router() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( '' ) );
		}

		// If the request has a generic nonce param, verify it early.
		if ( isset( $_REQUEST['nonce'] ) ) {
			check_admin_referer( self::NONCE_ACTION, 'nonce' );
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( ! $action ) {
			wp_die( esc_html( '' ) );
		}

		$parts = explode( 'authority_mailer_smtp_save_', $action );
		if ( count( $parts ) !== 2 || empty( $parts[1] ) ) {
			wp_die( esc_html( '' ) );
		}

		$provider = sanitize_key( $parts[1] );
		$method   = 'handle_post_save_' . $provider;

		if ( is_callable( array( __CLASS__, $method ) ) ) {
			call_user_func( array( __CLASS__, $method ) );
		} else {
			self::generic_post_save( $provider );
		}
	}

	/**
	 * Generic AJAX save: store all posted fields under provider-specific keys.
	 *
	 * Verifies provider nonce and persists posted data under option keys.
	 *
	 * @param string $provider Provider key.
	 */
	protected static function generic_ajax_save( $provider ) {
		// Provider-specific nonce is checked here.
		check_ajax_referer( 'authority_mailer_smtp_save_' . $provider, '_authority_mailer_' . $provider . '_nonce' );

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// Prepare a nested provider group so saved tests can read provider-specific settings
		$provider_group = isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ? $options[ $provider ] : array();

		foreach ( $_POST as $key => $value ) {
			if ( ! is_string( $key ) ) {
				continue;
			}
			// Skip common non-data keys
			if ( in_array( $key, array( 'action', '_wpnonce', '_wp_http_referer', '_authority_mailer_' . $provider . '_nonce', 'nonce' ), true ) ) {
				continue;
			}
			$sanitized_key   = sanitize_text_field( $key );
			$sanitized_value = is_string( $value ) ? wp_unslash( $value ) : $value;
			$clean_value     = is_string( $sanitized_value ) ? sanitize_text_field( $sanitized_value ) : $sanitized_value;

			// Save top-level option key for legacy compatibility
			$options[ $sanitized_key ] = $clean_value;

			// If key looks like provider-specific (provider_*) or 'other_*' when provider is other,
			// also store it in the nested provider group so the saved-test runner can read it easily.
			$provider_prefix = $provider . '_';
			if ( 0 === strpos( $sanitized_key, $provider_prefix ) || ( 'other' === $provider && 0 === strpos( $sanitized_key, 'other_' ) ) || in_array( $sanitized_key, array( 'from_email', 'from_name', 'test_recipient' ), true ) ) {
				$provider_group[ $sanitized_key ] = $clean_value;
			}
		}

		// Ensure boolean checkbox fields are explicitly set to 0 when unchecked
		$bools = array();
		if ( isset( self::$boolean_fields[ $provider ] ) && is_array( self::$boolean_fields[ $provider ] ) ) {
			$bools = self::$boolean_fields[ $provider ];
		}
		$inferred = array(
			$provider . '_force_from_name',
			$provider . '_force_from_email',
			$provider . '_force_from',
			'other_smtp_auth',
		);
		$bools    = array_unique( array_merge( $bools, $inferred ) );

		foreach ( $bools as $bkey ) {
			if ( ! array_key_exists( $bkey, $_POST ) ) {
				$options[ $bkey ] = 0;
				$provider_group[ $bkey ] = 0;
			} else {
				$val = in_array( (string) $_POST[ $bkey ], array( '1', 'on', 'yes', 'true' ), true ) ? 1 : 0;
				$options[ $bkey ] = $val;
				$provider_group[ $bkey ] = $val;
			}
		}

		// Persist nested provider group
		$options[ $provider ] = $provider_group;

		update_option( self::OPTION_KEY, $options );

		wp_send_json_success(
			array(
				'message'  => self::get_string( 'i18n_settings_saved' ),
				'provider' => $provider,
			)
		);
	}

	/**
	 * Generic POST save fallback (redirects back to wizard).
	 *
	 * @param string $provider Provider key.
	 */
	protected static function generic_post_save( $provider ) {
		// provider-specific nonce is validated here
		if ( ! isset( $_POST[ '_authority_mailer_' . $provider . '_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_authority_mailer_' . $provider . '_nonce' ] ) ), 'authority_mailer_smtp_save_' . $provider ) ) {
			wp_die( esc_html( '' ) );
		}

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// Prepare nested provider group
		$provider_group = isset( $options[ $provider ] ) && is_array( $options[ $provider ] ) ? $options[ $provider ] : array();

		foreach ( $_POST as $key => $value ) {
			if ( ! is_string( $key ) ) {
				continue;
			}
			if ( in_array( $key, array( 'action', '_wpnonce', '_wp_http_referer', '_authority_mailer_' . $provider . '_nonce', 'nonce' ), true ) ) {
				continue;
			}
			$sanitized_key   = sanitize_text_field( $key );
			$sanitized_value = is_string( $value ) ? wp_unslash( $value ) : $value;
			$clean_value     = is_string( $sanitized_value ) ? sanitize_text_field( $sanitized_value ) : $sanitized_value;

			$options[ $sanitized_key ] = $clean_value;

			// mirror provider-prefixed keys into nested group
			$provider_prefix = $provider . '_';
			if ( 0 === strpos( $sanitized_key, $provider_prefix ) || ( 'other' === $provider && 0 === strpos( $sanitized_key, 'other_' ) ) || in_array( $sanitized_key, array( 'from_email', 'from_name', 'test_recipient' ), true ) ) {
				$provider_group[ $sanitized_key ] = $clean_value;
			}
		}

		// Ensure boolean checkbox fields are explicitly set to 0 when unchecked (same logic as AJAX)
		$bools = array();
		if ( isset( self::$boolean_fields[ $provider ] ) && is_array( self::$boolean_fields[ $provider ] ) ) {
			$bools = self::$boolean_fields[ $provider ];
		}
		$inferred = array(
			$provider . '_force_from_name',
			$provider . '_force_from_email',
			$provider . '_force_from',
			'other_smtp_auth',
		);
		$bools    = array_unique( array_merge( $bools, $inferred ) );

		foreach ( $bools as $bkey ) {
			if ( ! array_key_exists( $bkey, $_POST ) ) {
				$options[ $bkey ]        = 0;
				$provider_group[ $bkey ] = 0;
			} else {
				$val                     = in_array( (string) $_POST[ $bkey ], array( '1', 'on', 'yes', 'true' ), true ) ? 1 : 0;
				$options[ $bkey ]        = $val;
				$provider_group[ $bkey ] = $val;
			}
		}

		// persist nested provider group
		$options[ $provider ] = $provider_group;

		update_option( self::OPTION_KEY, $options );

		$redirect = add_query_arg(
			array(
				'page'     => 'authority-mailer-smtp-onboarding',
				'step'     => 2,
				'provider' => $provider,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Example concrete handler: SendLayer.
	 */
	public static function ajax_save_sendlayer() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => '' ), 403 );
		}
		check_ajax_referer( 'authority_mailer_smtp_save_sendlayer', '_authority_mailer_sendlayer_nonce' );

		$api_key    = isset( $_POST['sendlayer_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['sendlayer_api_key'] ) ) : '';
		$from_name  = isset( $_POST['sendlayer_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sendlayer_from_name'] ) ) : '';
		$from_email = isset( $_POST['sendlayer_from_email'] ) ? sanitize_email( wp_unslash( $_POST['sendlayer_from_email'] ) ) : '';
		$force_name = isset( $_POST['sendlayer_force_from_name'] ) ? ( in_array( (string) $_POST['sendlayer_force_from_name'], array( '1', 'on', 'yes', 'true' ), true ) ? 1 : 0 ) : 0;
		$force_mail = isset( $_POST['sendlayer_force_from_email'] ) ? ( in_array( (string) $_POST['sendlayer_force_from_email'], array( '1', 'on', 'yes', 'true' ), true ) ? 1 : 0 ) : 0;

		$options = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$options['sendlayer_api_key']          = $api_key;
		$options['sendlayer_from_name']        = $from_name;
		$options['sendlayer_from_email']       = $from_email;
		$options['sendlayer_force_from_name']  = $force_name;
		$options['sendlayer_force_from_email'] = $force_mail;

		// also mirror into nested sendlayer group for saved-test runner
		if ( ! isset( $options['sendlayer'] ) || ! is_array( $options['sendlayer'] ) ) {
			$options['sendlayer'] = array();
		}
		$options['sendlayer']['sendlayer_api_key']          = $api_key;
		$options['sendlayer']['sendlayer_from_name']        = $from_name;
		$options['sendlayer']['sendlayer_from_email']       = $from_email;
		$options['sendlayer']['sendlayer_force_from_name']  = $force_name;
		$options['sendlayer']['sendlayer_force_from_email'] = $force_mail;

		update_option( self::OPTION_KEY, $options );

		wp_send_json_success(
			array(
				'message'  => self::get_string( 'i18n_settings_saved' ),
				'provider' => 'sendlayer',
			)
		);
	}

	/**
	 * Render the onboarding wizard page including the PRO modal markup.
	 */
	public static function render_wizard_page() {
		// Redirect to login if not authenticated
		if ( ! is_user_logged_in() ) {
			auth_redirect();
			return;
		}

		// Check capability for logged-in users
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( authority_mailer_smtp_get_string( 'no_permission' ) ) );
		}

		global $AUTHORITY_MAILER_STRINGS;

		$hero_title            = self::get_string( 'hero_title' );
		$hero_sub              = self::get_string( 'hero_sub' );
		$labels                = self::get_string( 'step_labels' );
		$configure_heading     = self::get_string( 'configure_heading' );
		$configure_copy        = self::get_string( 'configure_copy' );
		$welcome_heading       = self::get_string( 'welcome_heading' );
		$welcome_copy          = self::get_string( 'welcome_copy' );
		$choose_mailer_heading = self::get_string( 'choose_mailer_heading' );
		$choose_mailer_copy    = self::get_string( 'choose_mailer_copy' );
		$smtp_provider_heading = self::get_string( 'smtp_provider_heading' );

		$btn_get_started   = self::get_string( 'btn_get_started' );
		$btn_previous      = self::get_string( 'btn_previous' );
		$btn_save_continue = self::get_string( 'btn_save_continue' );

		$btn_send_test      = self::get_string( 'btn_send_test' );
		$label_send_test_to = self::get_string( 'label_send_test_to' );

		// sanitize and unslash incoming GET values
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameters for display/navigation, all sanitized below
		$step     = isset( $_GET['step'] ) ? absint( wp_unslash( $_GET['step'] ) ) : 0;
		$options  = get_option( self::OPTION_KEY, array() );
		$selected = '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_unslash() used, then sanitized with sanitize_text_field() below
		if ( isset( $_GET['provider'] ) && '' !== trim( (string) wp_unslash( $_GET['provider'] ) ) ) {
			$selected = sanitize_text_field( wp_unslash( $_GET['provider'] ) );
		} elseif ( isset( $options['selected_mailer'] ) ) {
			$selected = sanitize_text_field( $options['selected_mailer'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Get connected provider for header
		$connected_provider = isset( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';

		// Render header using the shared function (without Send Test button)
		?>
		<div class="am-wrap">
			<div class="am-container">
				<?php
				if ( function_exists( 'authority_mailer_smtp_render_admin_header' ) ) {
					// Header actions are hidden via CSS in onboarding.css
					authority_mailer_smtp_render_admin_header( 'onboarding', $connected_provider );
				}
				?>
			</div>
		</div>
		<!-- New two-column wizard layout: header row + main content left + narrow right sidebar -->
		<div class="authority-mailer-wizard-layout">

			<!-- Main + Sidebar grid -->
			<div class="authority-mailer-wizard-body">

				<!-- LEFT: main wizard content (keeps original wrapper id and data attributes for JS) -->
				<main class="authority-mailer-wizard-main">
					<div class="wpmsl-onboarding-wrap" role="main" aria-labelledby="authority-mailer-smtp-onboarding-title" data-current-step="<?php echo esc_attr( $step ); ?>" data-current-provider="<?php echo esc_attr( $selected ); ?>">
						<header class="wpmsl-onboarding-hero" id="authority-mailer-smtp-onboarding-title">
							<h1><?php echo esc_html( $hero_title ); ?></h1>
							<p class="wpmsl-hero-sub"><?php echo esc_html( $hero_sub ); ?></p>
						</header>

						<nav class="wpmsl-steps" aria-hidden="true" style="display:none;">
							<ol>
								<?php foreach ( (array) $labels as $i => $lbl ) : ?>
									<li data-step="<?php echo esc_attr( $i ); ?>" class="<?php echo ( $i === $step ) ? 'active' : ''; ?>">
										<span><?php echo esc_html( $i + 1 ); ?></span>
										<small><?php echo esc_html( $lbl ); ?></small>
									</li>
								<?php endforeach; ?>
							</ol>
						</nav>

						<main class="wpmsl-steps-content" id="wpmsl-steps-content">
							<section class="wpmsl-step" data-step="0" aria-hidden="false">
								<div class="wpmsl-card">
									<span class="wpmsl-step-meta"><?php echo esc_html( self::get_string( 'onboarding_step_1_meta' ) ); ?></span>
									<h2 class="wpmsl-card-heading"><?php echo esc_html( self::get_string( 'onboarding_get_started_heading' ) ); ?></h2>
									<p class="wpmsl-card-subheading"><?php echo esc_html( self::get_string( 'onboarding_get_started_subheading' ) ); ?></p>

									<!-- NEW CONTENT STARTS HERE -->

									<div class="wpmsl-welcome-intro">
										<p style="font-size: 15px; color: var(--am-gray-700); line-height: 1.7; margin: 24px 0;">
											<?php echo esc_html( self::get_string( 'onboarding_intro_paragraph' ) ); ?>
										</p>
									</div>

									<div class="wpmsl-welcome-benefits" style="margin-top: 32px; padding: 20px; background: var(--am-brand-primary-light); border-radius: var(--am-radius-lg); border: 1px solid rgba(99, 102, 241, 0.2);">
										<h3 style="margin: 0 0 12px; font-size: 16px; font-weight: 700; color: var(--am-gray-900);">
											<?php echo esc_html( self::get_string( 'onboarding_what_happens_next' ) ); ?>
										</h3>
										<ol style="margin: 0; padding-left: 24px; color: var(--am-gray-700); line-height: 1.8;">
											<li><?php echo wp_kses_post( self::get_string( 'onboarding_step_choose_provider' ) ); ?></li>
											<li><?php echo wp_kses_post( self::get_string( 'onboarding_step_enter_credentials' ) ); ?></li>
											<li><?php echo wp_kses_post( self::get_string( 'onboarding_step_send_test' ) ); ?></li>
											<li><?php echo wp_kses_post( self::get_string( 'onboarding_step_done' ) ); ?></li>
										</ol>
									</div>

									<!-- NEW CONTENT ENDS HERE -->

									<div class="wpmsl-center wpmsl-welcome-actions">
										<button class="wpmsl-btn-primary wpmsl-next" type="button">
											<?php echo esc_html( $btn_get_started ); ?> →
										</button>
									</div>
								</div>
							</section>

							<section class="wpmsl-step" data-step="1" aria-hidden="<?php echo 1 === $step ? 'false' : 'true'; ?>" style="<?php echo 1 === $step ? '' : 'display:none;'; ?>">
								<div class="wpmsl-card">
									<span class="wpmsl-step-meta"><?php echo esc_html( sprintf( self::get_string( 'step_meta' ), 2, count( (array) $labels ) ) ); ?></span>
									<h2 class="wpmsl-card-heading"><?php echo esc_html( $choose_mailer_heading ); ?></h2>
									<p class="wpmsl-muted"><?php echo esc_html( $choose_mailer_copy ); ?></p>

									<h4 class="wpmsl-section-title"><?php echo esc_html( $smtp_provider_heading ); ?></h4>

									<div class="wpmsl-mailers-grid" role="list">
										<?php
										$is_premium = apply_filters( 'authority_mailer_smtp_is_premium', false );
										foreach ( self::$providers as $p ) :
											$icon_url = $p['icon'] ? plugins_url( 'assets/images/' . $p['icon'], AUTHORITY_MAILER_PLUGIN_FILE ) : '';
											$checked  = ( $selected === $p['id'] ) ? 'checked' : '';
											$pro      = ! empty( $p['pro'] );
											$pro_only = ! empty( $p['pro_only'] );
											$is_locked = $pro_only && ! $is_premium;
											$locked_class = $is_locked ? 'mm-provider-card locked' : '';
											?>
											<label class="wpmsl-mailer-tile <?php echo $pro ? 'wpmsl-mailer-pro' : ''; ?> <?php echo esc_attr( $locked_class ); ?>" data-mailer="<?php echo esc_attr( $p['id'] ); ?>" data-pro="<?php echo $pro ? '1' : '0'; ?>" data-locked="<?php echo $is_locked ? '1' : '0'; ?>">
												<?php if ( ! $is_locked ) : ?>
													<input type="radio" name="authority_mailer_smtp_choice_full" value="<?php echo esc_attr( $p['id'] ); ?>" <?php echo $checked ? 'checked' : ''; ?> />
												<?php else : ?>
													<input type="radio" disabled="disabled" aria-hidden="true" title="<?php echo esc_attr( self::get_string( 'onboarding_upgrade_to_unlock' ) ); ?>" />
												<?php endif; ?>

												<?php if ( $icon_url ) : ?>
													<img class="wpmsl-mailer-icon <?php echo $is_locked ? 'mm-provider-icon' : ''; ?>" src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $p['label'] ); ?>" />
												<?php else : ?>
													<span class="wpmsl-mailer-icon-fallback" aria-hidden="true"><?php echo esc_html( substr( $p['label'], 0, 1 ) ); ?></span>
												<?php endif; ?>

												<div class="wpmsl-mailer-body">
													<span class="wpmsl-mailer-title"><?php echo esc_html( $p['label'] ); ?></span>
												</div>
											</label>
										<?php endforeach; ?>
									</div>

									<div class="wpmsl-card-actions" role="toolbar" aria-label="<?php echo esc_attr( self::get_string( 'onboarding_actions_label' ) ); ?>">
										<div class="wpmsl-actions-left">
										<button type="button" class="wpmsl-btn-primary wpmsl-prev"><?php echo esc_html( $btn_previous ); ?></button>
										</div>
										<div class="wpmsl-actions-right">
										<button type="button" class="wpmsl-btn-primary" id="authority-mailer-choose-save"><?php echo esc_html( $btn_save_continue ); ?></button>
										</div>
									</div>
								</div>
							</section>

							<section class="wpmsl-step" data-step="2" aria-hidden="<?php echo 2 === $step ? 'false' : 'true'; ?>" style="<?php echo 2 === $step ? '' : 'display:none;'; ?>">
								<div class="wpmsl-card">
									<span class="wpmsl-step-meta"><?php echo esc_html( sprintf( self::get_string( 'step_meta' ), 3, count( (array) $labels ) ) ); ?></span>
									<h2 class="wpmsl-card-heading"><?php echo esc_html( $configure_heading ); ?></h2>
									<p class="wpmsl-muted"><?php echo esc_html( $configure_copy ); ?></p>

									<?php
									$partials_dir = __DIR__ . '/partials/';
									$provider     = strtolower( $selected );
									if ( $provider ) {
										$partial_file = $partials_dir . 'settings-' . $provider . '.php';
										if ( file_exists( $partial_file ) ) {
											// Include the provider partial. The partial is expected to render the provider form.
											include $partial_file;
										} else {
											echo '<p class="wpmsl-muted">' . esc_html( self::get_string( 'no_provider_selected_copy' ) ) . '</p>';
										}
									} else {
										echo '<p class="wpmsl-muted">' . esc_html( self::get_string( 'no_provider_selected_copy' ) ) . '</p>';
									}
									?>

									<div class="wpmsl-card-actions" role="toolbar" aria-label="<?php echo esc_attr( self::get_string( 'onboarding_actions_label' ) ); ?>">
										<div class="wpmsl-actions-left">
										<button type="button" class="wpmsl-btn-primary wpmsl-prev"><?php echo esc_html( $btn_previous ); ?></button>
										</div>
										<div class="wpmsl-actions-right">
										<!-- Keep existing save button that navigates to step 3 after saving -->
										<button class="wpmsl-btn-primary authority-mailer-save" type="button"><?php echo esc_html( $btn_save_continue ); ?></button>
										</div>
									</div>
								</div>
							</section>

							<section class="wpmsl-step" data-step="3" aria-hidden="<?php echo 3 === $step ? 'false' : 'true'; ?>" style="<?php echo 3 === $step ? '' : 'display:none;'; ?>">
								<div class="wpmsl-card">
									<span class="wpmsl-step-meta"><?php echo esc_html( sprintf( self::get_string( 'step_meta' ), 4, count( (array) $labels ) ) ); ?></span>
									<h2 class="wpmsl-card-heading"><?php echo esc_html( isset( $labels[3] ) ? $labels[3] : '' ); ?></h2>
									<p class="wpmsl-muted"><?php echo esc_html( self::get_string( 'step_follow_instructions' ) ); ?></p>

									<!-- NEW: recipient input and explicit Send Test Email button -->
									<div style="margin-top:12px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
										<label for="authority-mailer-test-recipient" style="font-weight:600;margin-right:6px;"><?php echo esc_html( $label_send_test_to ); ?></label>
										<input type="email" id="authority-mailer-test-recipient" class="authority-mailer-test-recipient" style="padding:8px;border-radius:6px;border:1px solid #d1d5db;min-width:280px;" value="<?php echo esc_attr( get_option( 'admin_email', '' ) ); ?>" aria-label="<?php echo esc_attr( $label_send_test_to ); ?>" />
										<button id="authority-mailer-send-test-button" class="wpmsl-btn-primary" type="button" style="white-space:nowrap;">
											<?php echo esc_html( $btn_send_test ); ?>
										</button>
									</div>

									<!-- Live test log moved to Step 3 (Configure/Test results playback) -->
									<div class="wpmsl-config-test-area" aria-live="polite" style="margin-top:12px;">
										<textarea id="wpmsl-config-test-log" class="wpmsl-config-test-log" readonly
													style="width:100%;height:220px;padding:10px;box-sizing:border-box;font-family:monospace;"
													aria-label="<?php echo esc_attr( self::get_string( 'log_saving_settings' ) ); ?>"></textarea>
									</div>

									<div class="wpmsl-card-actions" role="toolbar" aria-label="<?php echo esc_attr( self::get_string( 'onboarding_actions_label' ) ); ?>">
										<div class="wpmsl-actions-left">
										<button type="button" class="wpmsl-btn-primary wpmsl-prev"><?php echo esc_html( $btn_previous ); ?></button>
										</div>

										<!-- RIGHT side intentionally left empty on final step to avoid duplicate Save & Continue button -->
									</div>
								</div>
							</section>

						</main>
					</div>
				</main>

				<aside class="authority-mailer-wizard-sidebar" role="complementary" aria-label="<?php echo esc_attr( self::get_string( 'onboarding_sidebar_label' ) ); ?>">
					<?php
					// Display white-glove service sidebar
					$white_glove_component = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/components/white-glove-sidebar.php';
					if ( file_exists( $white_glove_component ) ) {
						require_once $white_glove_component;
						authority_mailer_smtp_render_white_glove_sidebar( $step );
					}
					?>
				</aside>

			</div><!-- .authority-mailer-wizard-body -->

		</div><!-- .authority-mailer-wizard-layout -->
		<?php
	}

	/**
	 * Helper to retrieve a string from the centralized $AUTHORITY_MAILER_STRINGS array.
	 *
	 * @param string $key Key in $AUTHORITY_MAILER_STRINGS.
	 * @return mixed Value from strings array or empty string.
	 */
	private static function get_string( $key ) {
		global $AUTHORITY_MAILER_STRINGS;
		return isset( $AUTHORITY_MAILER_STRINGS[ $key ] ) ? $AUTHORITY_MAILER_STRINGS[ $key ] : '';
	}
}
