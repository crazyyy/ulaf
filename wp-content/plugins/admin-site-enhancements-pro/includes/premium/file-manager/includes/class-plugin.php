<?php
/**
 * Main Plugin Class
 *
 * @package ASENHA\FileManager
 */

namespace ASENHA\FileManager;

/**
 * Main plugin class - Singleton pattern
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Admin instance.
	 *
	 * @var Admin|null
	 */
	private $admin = null;

	/**
	 * REST API instance.
	 *
	 * @var REST_API|null
	 */
	private $rest_api = null;

	/**
	 * Get plugin instance (Singleton).
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - Initialize the plugin.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin components.
	 *
	 * @return void
	 */
	private function init() {
		// Initialize admin interface
		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		// Initialize REST API
		$this->rest_api = new REST_API();
	}

	/**
	 * Prevent cloning of the instance.
	 *
	 * @return void
	 */
	private function __clone() {
		// Prevent cloning
	}

	/**
	 * Prevent unserialization of the instance.
	 *
	 * @return void
	 */
	public function __wakeup() {
		// Prevent unserialization
	}
}

