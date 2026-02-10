<?php

namespace SolidWP\Mail\Admin;

use SolidWP\Mail\Container;
use SolidWP\Mail\Admin\REST\Logs;
use SolidWP\Mail\Admin\REST\Connections;
use SolidWP\Mail\Contracts\Service_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Admin related functionality.
 *
 * @since   1.3.0
 *
 * @package SolidWP\Mail
 */
class Provider extends Service_Provider {

	/**
	 * Register services and actions.
	 *
	 * This method is responsible for booting admin controllers and
	 * registering necessary actions.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->singleton( SettingsScreen::class, SettingsScreen::class );
		$this->container->singleton( ScreenConnectors::class, ScreenConnectors::class );

		$this->container->when( ScreenConnectors::class )
			->needs( Container::class )
			->give( $this->container );

		$this->container->singleton( Notice::class, Notice::class );

		// Boot admin controllers.
		$this->container->get( ScreenConnectors::class )->register_hooks();
		$this->container->get( LogsScreen::class )->register_hooks();
		$this->register_actions();
	}

	/**
	 * Register actions with WordPress.
	 *
	 * This method registers WordPress actions needed for the admin functionality.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_actions(): void {

		add_action( 'admin_init', $this->container->callback( SettingsScreen::class, 'register_settings_screen' ) );
		add_action( 'rest_api_init', $this->container->callback( SettingsScreen::class, 'register_settings_screen' ) );

		add_action( 'admin_notices', $this->container->callback( Notice::class, 'display_notice_migration_error' ) );
		add_action( 'admin_notices', $this->container->callback( Notice::class, 'maybe_display_notice_200_211_error' ) );
		add_action( 'wp_ajax_dismiss_solid_mail_notice', $this->container->callback( Notice::class, 'dismiss_notice' ) );

		add_action( 'rest_api_init', [ $this, 'queue_rest_controllers' ] );
	}

	/**
	 * Queue REST controllers.
	 *
	 * This method queues the REST controllers by calling their register_routes() method.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function queue_rest_controllers(): void {
		$this->container->get( Logs::class )->register_routes();
		$this->container->get( Connections::class )->register_routes();
	}
}
