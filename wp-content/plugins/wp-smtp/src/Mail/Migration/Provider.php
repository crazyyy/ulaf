<?php

namespace SolidWP\Mail\Migration;

use SolidWP\Mail\Contracts\Service_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Admin related functionality.
 *
 * @since 1.3.0
 *
 * @package SolidWP\Mail
 */
class Provider extends Service_Provider {

	/**
	 * Database version
	 */
	private const OPTION_VERSION_NAME = 'solid_smtp_version';

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->get( MigrationVer130::class )->register_hooks();
		add_action( 'wp_loaded', fn() => $this->migrate() );
	}

	private function migrate(): void {
		$version = get_option( self::OPTION_VERSION_NAME, '' );

		if ( version_compare( $version, WPSMTP_VERSION, '==' ) ) {
			return;
		}

		$this->container->get( MigrationVer130::class )->migration( $version );
		$this->container->get( MigrationVer210::class )->migration( $version );
		$this->container->get( MigrationVer221::class )->migration( $version );

		update_option( self::OPTION_VERSION_NAME, WPSMTP_VERSION );
	}
}
