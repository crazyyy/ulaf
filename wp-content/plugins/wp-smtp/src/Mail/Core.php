<?php
/**
 * The main plugin class.
 *
 * @since   2.0.0
 *
 * @package SolidWP\Mail
 */

namespace SolidWP\Mail;

use InvalidArgumentException;
use SolidWP\Mail\Psr\Container\ContainerInterface as PsrContainerInterface;
use SolidWP\Mail\StellarWP\Assets\Config as Assets_Config;
use SolidWP\Mail\StellarWP\ContainerContract\ContainerInterface;
use SolidWP\Mail\StellarWP\Validation\Config;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The primary class responsible for booting up the plugin.
 *
 * @since   2.0.0
 *
 * @package SolidWP\Mail
 */
class Core {

	public const PLUGIN_FILE = 'solid_mail.file';

	/**
	 * The server path to the plugin's main file
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * The centralized container.
	 *
	 * @since 2.0.0
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * An array of providers to register within the container.
	 *
	 * @var array<int,string>
	 */
	private array $providers = [
		Admin\Provider::class,
		Hooks\Provider::class,
		Migration\Provider::class,
		Repository\Provider::class,
		Telemetry\Provider::class,
		Assets::class,
	];

	/**
	 * The singleton instance for the plugin.
	 *
	 * @var Core
	 */
	private static self $instance;

	/**
	 * @param string    $plugin_file The full server path to the main plugin file.
	 * @param Container $container   The container instance
	 */
	private function __construct(
		string $plugin_file,
		Container $container
	) {
		$this->plugin_file = $plugin_file;
		$this->container   = $container;

		$this->container->singleton( PsrContainerInterface::class, $this->container );
		$this->container->singleton( ContainerInterface::class, $this->container );
	}


	/**
	 * Get the singleton instance of our plugin
	 *
	 * @param string|null    $plugin_file The full server path to the main plugin file
	 * @param Container|null $container   The container instance.
	 *
	 * @return Core
	 */
	public static function instance( ?string $plugin_file = null, ?Container $container = null ): Core {
		if ( ! isset( self::$instance ) ) {
			if ( ! $plugin_file ) {
				throw new InvalidArgumentException( 'You must provide a $plugin_file path' );
			}

			if ( ! $container ) {
				throw new InvalidArgumentException( sprintf( 'You must provide a %s instance!', Container::class ) );
			}

			self::$instance = new self( $plugin_file, $container );
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since  2.0.0
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	public function init(): void {
		$this->container->setVar( self::PLUGIN_FILE, $this->plugin_file );
		$this->container->setVar( 'LOGS_PER_PAGE', 10 );

		// init validator.
		$this->init_validator_object();
		$this->init_assets_object();

		// Register all providers
		foreach ( $this->providers as $class ) {
			$this->container->get( $class )->register( $this->container );
		}
	}

	/**
	 * Returns the container instance
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Initializes the validator class.
	 *
	 * This method sets up the configuration for the validator class by setting the service container
	 * and hook prefix, and then initializing the configuration.
	 *
	 * @return void
	 */
	protected function init_validator_object() {
		Config::setServiceContainer( $this->container );
		Config::setHookPrefix( 'solidwp_mail_' );
		Config::initialize();
	}

	/**
	 * Initializes the asset libraries for SolidWP Mail.
	 *
	 * This function configures the asset settings for SolidWP Mail by setting the
	 * hook prefix, path, version, and relative asset path using the StellarWP\Assets\Config class.
	 *
	 * @return void
	 */
	protected function init_assets_object() {
		Assets_Config::set_hook_prefix( 'solidwp_mail_' );
		Assets_Config::set_path( WPSMTP_PATH );
		Assets_Config::set_version( WPSMTP_VERSION );
		Assets_Config::set_relative_asset_path( 'assets/' );
	}
}
