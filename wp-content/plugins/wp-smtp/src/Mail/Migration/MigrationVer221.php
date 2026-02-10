<?php

namespace SolidWP\Mail\Migration;

use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;
use WPSMTP\Logger\Table;

/**
 * Class MigrationVer221
 *
 * Migration for version 2.2.1 to add the 'is_default' property to all providers.
 * The currently active provider will be set as default.
 *
 * @package SolidWP\Mail\Migration
 */
class MigrationVer221 {
	/**
	 * The repository for managing SMTP mailers.
	 *
	 * @var ProvidersRepository
	 */
	protected ProvidersRepository $providers_repository;

	/**
	 * Constructor for MigrationVer220.
	 *
	 * Initializes the migration class and sets up dependencies.
	 *
	 * @param ProvidersRepository $providers_repository The repository instance for managing providers.
	 */
	public function __construct( ProvidersRepository $providers_repository ) {
		$this->providers_repository = $providers_repository;
	}

	/**
	 * Migration logic for version 2.2.0.
	 *
	 * Adds 'is_default' property to all existing providers.
	 * Sets is_default to true for the currently active provider, false for all others.
	 *
	 * @param string $version
	 *
	 * @return void
	 */
	public function migration( string $version ): void {
		global $wpdb;

		if ( version_compare( $version, '2.2.1', '>=' ) ) {
			return;
		}

		// Upgrade logs table
		Table::install();

		$logsTableName = Table::$name;

		// phpcs:ignore WordPress.DB
		$wpdb->query( "UPDATE $logsTableName SET content_type = 'text/html' WHERE headers LIKE '%html%'" );

		$default_provider = $this->providers_repository->get_default_provider();
		if ( $default_provider instanceof ConnectorSMTP ) {
			// Default provider has been set up by the user, no needs for programmatic changes
			return;
		}

		$active_provider    = current( $this->providers_repository->get_active_providers() );
		$active_provider_id = $active_provider ? $active_provider->get_id() : null;

		if ( ! $active_provider_id ) {
			// No active providers, plugin is unused, or it's the first installation
			return;
		}

		$providers_data = get_option( ProvidersRepository::OPTION_NAME, [] );

		foreach ( $providers_data as $provider_id => &$provider_data ) {
			// Set is_default to true only for the currently active provider
			$provider_data['is_default'] = ( $provider_id === $active_provider_id );
		}
		unset( $provider_data );

		update_option( ProvidersRepository::OPTION_NAME, $providers_data );
	}
}
