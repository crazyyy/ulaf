<?php

namespace SolidWP\Mail\Hooks;

use PHPMailer\PHPMailer\SMTP;
use SolidWP\Mail\AbstractController;
use SolidWP\Mail\App;
use SolidWP\Mail\Connectors\ConnectionPool;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\Repository\SettingsRepository;
use SolidWP\Mail\SolidMailer;

/**
 * Class MailerController
 *
 * This class is responsible for handling email functionality within the Solid_SMTP plugin.
 *
 * @package Solid_SMTP\Controller
 */
class PHPMailer extends AbstractController {

	/**
	 * The repository for managing SMTP mailers.
	 *
	 * @var ProvidersRepository
	 */
	protected ProvidersRepository $providers_repository;

	/**
	 * The repository for getting settings.
	 *
	 * @var SettingsRepository
	 */
	protected SettingsRepository $settings_repository;

	/**
	 * Constructor for the class.
	 *
	 * @param ProvidersRepository $providers_repository The repository instance for managing providers.
	 * @param SettingsRepository $settings_repository The settings repository instance.
	 */
	public function __construct(
		ProvidersRepository $providers_repository,
		SettingsRepository $settings_repository
	) {
		$this->providers_repository = $providers_repository;
		$this->settings_repository  = $settings_repository;
	}

	/**
	 * Register hooks.
	 *
	 * Implementing the InterfaceController interface, this method registers hooks related to email functionality.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'pre_wp_mail', [ $this, 'init_solidmail_mailer' ] );
		add_action( 'phpmailer_init', [ $this, 'set_up_connection_pool' ], 9999 );
		add_action( 'wp_mail_failed', [ $this, 'maybe_capture_sending_error' ] );
	}

	/**
	 * Initializes the SolidMail mailer integration. Return null so the default behavior continue to run.
	 *
	 * @since 2.1.3
	 *
	 * @return null
	 */
	public function init_solidmail_mailer() {
		$active_providers = $this->providers_repository->get_active_providers();
		$default_provider = $this->providers_repository->get_default_provider();

		if ( ! $default_provider instanceof ConnectorSMTP && count( $active_providers ) === 0 ) {
			// if there is no active providers and default provider, do nothing.
			return null;
		}

		// Declare the phpmailer instance before the wp_mail does it.
		global $phpmailer;
		if ( ! $phpmailer instanceof SolidMailer ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			require_once WPSMTP_PATH . 'inc/wp-phpmailer-compatibility.php';

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$phpmailer = new SolidMailer( true );
		}

		return null;
	}

	/**
	 * Captures the sending error if one occurs.
	 *
	 * This function sets the PHPMailer send error variable in the application with the provided WP_Error object.
	 *
	 * @param \WP_Error $error The error object to capture.
	 */
	public function maybe_capture_sending_error( \WP_Error $error ) {
		App::setVar( 'phpmailer_send_error', $error );
	}

	/**
	 * Configure PHPMailer for SMTP.
	 *
	 * This method is invoked when PHPMailer is initialized to configure it for SMTP usage.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance.
	 *
	 * @return void
	 */
	public function set_up_connection_pool( \PHPMailer\PHPMailer\PHPMailer $phpmailer ): void {
		if ( ! $phpmailer instanceof SolidMailer ) {
			return;
		}

		$connection_pool = new ConnectionPool();

		$active_connections = $this->providers_repository->get_active_providers();

		// 1. Add connections with a full email match
		foreach ( $active_connections as $connection ) {
			if ( $connection->get_from_email() === $phpmailer->From ) {
				$connection_pool->append( $connection );
			}
		}

		// 2. Use a default connection if we could not find at least one
		// or prioritize the default connection if we want to add unmatched active connections
		$default_connection = $this->providers_repository->get_default_provider();
		if ( $connection_pool->count() === 0 || $this->settings_repository->use_unmatched_connections() ) {
			$connection_pool->append( $default_connection );
		}

		// 3. Add unmatched active connections if enabled
		if ( $this->settings_repository->use_unmatched_connections() ) {
			foreach ( $active_connections as $connection ) {
				$connection_pool->append( $connection );
			}
		}

		$phpmailer->init_pool( $connection_pool );
	}

	/**
	 * If Solid Mail is used for the current request
	 *
	 * @return bool
	 */
	public static function is_solid_mail_configured(): bool {
		global $phpmailer;
		return $phpmailer instanceof SolidMailer && $phpmailer->get_connection() instanceof ConnectorSMTP;
	}
}
