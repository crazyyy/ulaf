<?php

namespace SolidWP\Mail\Repository;

use SolidWP\Mail\Contracts\Service_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Provider extends Service_Provider {

	/**
	 * Register services and actions.
	 *
	 * This method is responsible for booting repository controllers and
	 * registering necessary actions.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->get( ProvidersRepository::class );
		$this->container->get( LogsRepository::class );
		$this->container->get( SettingsRepository::class );
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
	}
}
