<?php

namespace SolidWP\Mail\Migration;

use SolidWP\Mail\AbstractController;
use SolidWP\Mail\Admin\SettingsScreen;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\Service\ConnectionService;
use SolidWP\Mail\StellarWP\SuperGlobals\SuperGlobals;

/**
 * Class ControllerMigration130
 *
 * Handles the migration of old SMTP options to the new provider model.
 *
 * @package Solid_SMTP\Controller
 */
class MigrationVer130 extends AbstractController {
	/**
	 * The repository for managing SMTP mailers.
	 *
	 * @var ProvidersRepository
	 */
	protected ProvidersRepository $providers_repository;

	/**
	 * The service class.
	 *
	 * @var ConnectionService
	 */
	protected ConnectionService $service;

	/**
	 * Constructor for the class.
	 *
	 * @param ProvidersRepository $providers_repository The repository instance for managing providers.
	 */
	public function __construct( ProvidersRepository $providers_repository, ConnectionService $service ) {
		$this->providers_repository = $providers_repository;
		$this->service              = $service;
	}

	/**
	 * Register hooks for the migration.
	 */
	public function register_hooks() {
		add_action( 'admin_menu', [ $this, 'redirect_to_new_page_slug' ] );
	}

	/**
	 * If the user visit the old slug wp-smtp/wp-smtp.php, it should redirect to the new solidwp-mail
	 * the purpose is when upgrade, user will see a wp_die page which is very trouble
	 *
	 * @return void
	 */
	public function redirect_to_new_page_slug() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = (string) SuperGlobals::get_get_var( 'page' );

		if ( $page === 'wp-smtp/wp-smtp.php' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=solidwp-mail' ) );
			exit();
		}
	}

	/**
	 * Upgrades the SMTP settings to the provider model if needed.
	 *
	 * Checks the stored plugin version and compares it with the current plugin version.
	 * If an upgrade is needed, it converts the old SMTP options to the new provider model.
	 */
	public function migration( string $version ): void {
		if ( version_compare( $version, '2.0.0', '>=' ) ) {
			return;
		}

		// we need to convert the old smtp to provider model.
		$smtp = get_option( 'wp_smtp_options' );

		if ( empty( $smtp['from'] ) || empty( $smtp['host'] ) ) {
			return;
		}

		$active_connection = current( $this->providers_repository->get_active_providers() );

		$provider = new ConnectorSMTP(
			[
				'id'            => 'legacy_smtp_id',
				'from_email'    => $smtp['from'],
				'from_name'     => $smtp['fromname'],
				'smtp_host'     => $smtp['host'],
				'smtp_port'     => $smtp['port'],
				'smtp_secure'   => $smtp['smtpsecure'],
				'smtp_auth'     => $smtp['smtpauth'],
				// todo we need to encrypt this in the db.
				'smtp_username' => base64_decode( $smtp['username'] ),
				'smtp_password' => base64_decode( $smtp['password'] ),
				'name'          => 'other',
				'description'   => 'Other SMTP: ' . $smtp['from'],
				'is_active'     => ! is_object( $active_connection ),
			]
		);

		/**
		 * In the version 1.2.3, the smtp_username and smtp_password do not have base64_encode when storing smtp credential
		 * When user update to 1.2.4, the plugin introduce the base64_encode for user and password
		 *
		 * When migrate to 2.0.0, we will need to handle the both case because there are still number of users that may using an old version.
		 */

		// Assume the upgrade is from 1.2.7, test the credential.
		$result = $this->service->test_smtp_connection( $provider );

		if ( is_wp_error( $result ) ) {
			// then try without the base64 encode.
			$provider->set_username( $smtp['username'] );
			$provider->set_password( $smtp['password'] );
			// test again.
			$result = $this->service->test_smtp_connection( $provider );
		}

		// if the creds is good, then saving.
		if ( true === $result ) {
			$this->providers_repository->save( $provider );
		} else {
			// if we got an error from the SMTP, then we should bring the connection as-is and user able to update it.
			$provider->set_username( $smtp['username'] );
			$provider->set_password( $smtp['password'] );
			$this->providers_repository->save( $provider );

			// and we add a notice about the error.
			update_option( 'solid_mail_migration_error', $result->get_error_message() );
		}

		// we will need to migrate the disable_logs too.
		$disable_logs = 'no';

		if ( isset( $smtp['disable_logs'] ) && in_array( $smtp['disable_logs'], [ 'yes', 'no' ] ) ) {
			$disable_logs = $smtp['disable_logs'];
		}

		update_option(
			SettingsScreen::SETTINGS_SLUG,
			[
				'disable_logs' => $disable_logs,
			] 
		);
	}
}
