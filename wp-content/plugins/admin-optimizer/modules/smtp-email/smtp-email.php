<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP_Email class
 */
class SMTP_Email {

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'smtp-email/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'smtp-email/';

	const MENU_SLUG = 'adminoptimizer-smtp-email';

	const OPTION_NAME = 'adminoptim_smtp';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var SMTP_Email_Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new SMTP_Email_Settings();

		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'phpmailer_init', [ $this, 'phpmailer_smtp_setup' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'SMTP Email', 'admin-optimizer' ),
			__( 'SMTP Email', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Phpmailer smtp setup
	 *
	 * @param \PHPMailer $phpmailer phpmailer class.
	 *
	 * @return void
	 */
	public function phpmailer_smtp_setup( $phpmailer ) {
		if ( ! empty( $this->options ) ) {
			$phpmailer->isSMTP();
			if ( isset( $this->options['smtp_server'] ) ) {
				$phpmailer->Host = $this->options['smtp_server'];
			}

			if ( isset( $this->options['smtp_auth'] ) ) {
				$phpmailer->SMTPAuth = true;
			}

			if ( isset( $this->options['smtp_port'] ) ) {
				$phpmailer->Port = $this->options['smtp_port'];
			}

			if ( isset( $this->options['smtp_username'] ) ) {
				$phpmailer->Username = $this->options['smtp_username'];
			}

			if ( isset( $this->options['smtp_password'] ) ) {
				$phpmailer->Password = $this->options['smtp_password'];
			}

			if ( isset( $this->options['smtp_secure'] ) ) {
				$phpmailer->SMTPSecure = $this->options['smtp_secure'];
			}

			$phpmailer->Sender     = $this->options['email_from'] ?? '';
			$phpmailer->ReturnPath = $this->options['email_from'] ?? '';
			if ( ! empty( $this->options['email_from'] ) ) {
				$phpmailer->From = $this->options['email_from'];
			}
			if ( ! empty( $this->options['send_from'] ) ) {
				$phpmailer->FromName = $this->options['send_from'];
			}
		}
	}
}
