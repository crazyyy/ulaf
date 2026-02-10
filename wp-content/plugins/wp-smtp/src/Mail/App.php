<?php

namespace SolidWP\Mail;

use SolidWP\Mail\lucatume\DI52\App as DI52App;
use SolidWP\Mail\StellarWP\ContainerContract\ContainerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class App
 *
 * This class serves as the main entry point for accessing the dependency container.
 * It follows the singleton pattern to ensure that only one instance of the container exists.
 *
 * @package Solid_SMTP
 */
class App extends DI52App {

	/**
	 * A reference to the singleton instance of the DI container
	 * the application uses as Service Locator.
	 *
	 * @since 0.1.0
	 *
	 * @var ContainerInterface
	 *
	 * @phpstan-ignore-next-line
	 */
	protected static $container;

	/**
	 * Returns the singleton instance of the DI container the application
	 * will use as Service Locator.
	 *
	 * @since 1.3.0
	 *
	 * @return ContainerInterface The singleton instance of the Container used as Service Locator
	 *                            by the application.
	 */
	public static function container(): ContainerInterface {
		return static::$container;
	}


	/**
	 * Sets the container instance the Application should use as a Service Locator.
	 *
	 * If the Application already stores a reference to a Container instance, then
	 * this will be replaced by the new one.
	 *
	 * @since 1.3.0
	 *
	 * @param ContainerInterface $container A reference to the Container instance the Application
	 *                                      should use as a Service Locator.
	 *
	 * @return void The method does not return any value.
	 */
	public static function setContainer( $container ) {
		static::$container = $container;
	}
}
