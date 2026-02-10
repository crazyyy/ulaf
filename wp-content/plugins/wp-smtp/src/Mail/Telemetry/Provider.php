<?php
/**
 * The provider to all telemetry related functionality.
 *
 * @since 2.0.0
 */
namespace SolidWP\Mail\Telemetry;

use SolidWP\Mail\Contracts\Service_Provider;
use SolidWP\Mail\Core;
use SolidWP\Mail\StellarWP\Telemetry\Config as TelemetryConfig;
use SolidWP\Mail\StellarWP\Telemetry\Core as Telemetry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Provider extends Service_Provider {

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		TelemetryConfig::set_container( $this->container );
		TelemetryConfig::set_server_url( 'https://telemetry.stellarwp.com/api/v1' );
		TelemetryConfig::set_hook_prefix( 'solid-mail' );
		TelemetryConfig::set_stellar_slug( 'solid-mail' );
		Telemetry::instance()->init( $this->container->getVar( Core::PLUGIN_FILE ) );

		add_filter( 'stellarwp/telemetry/optin_args', $this->container->callback( Modal::class, 'optin_args' ), 10, 2 );
		add_filter( 'debug_information', $this->container->callback( Health_Data::class, 'add_summary_to_telemetry' ), 10, 1 );
	}
}
