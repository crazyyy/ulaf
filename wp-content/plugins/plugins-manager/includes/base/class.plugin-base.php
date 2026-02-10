<?php

namespace WTBP;

use Exception;

/*
 * Base plugin class
 *
 * */

abstract class Plugin_Base {

	/**
	 * Admin pages
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * @see self::app()
	 * @var self
	 */
	private static $_instance;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		self::$_instance = $this;

		if ( defined( 'WTBP_VERSION' ) ) {
			$this->version = WTBP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = WTBP_PLUGIN_SLUG;

		add_action( 'wp_enqueue_scripts', [ $this, 'front_enqueue_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );

		$this->global_code();

		if ( is_admin() ) {
			$this->admin_code();
		} else {
			$this->front_code();
		}
	}

	/**
	 * Static method for quick access to the plugin interface.
	 * Allows to globally access an instance of the plugin class anywhere of the plugin
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Add assets to front
	 */
	public function front_enqueue_assets() { }

	/**
	 * Add assets to admin pages
	 */
	public function admin_enqueue_assets() { }

	/**
	 * Admin code
	 */
	protected function admin_code() { }

	/**
	 * Front code
	 */
	protected function front_code() { }

	/**
	 * Global code
	 */
	protected function global_code() { }

	/**
	 *
	 * @param string $class_name Class Name.
	 *
	 * @throws Exception
	 */
	public function register_page( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = __NAMESPACE__ . '\\' . $class_name;
			if ( ! class_exists( $class_name ) ) {
				throw new Exception( 'A class with this name {' . $class_name . '} does not exist.' );
			}
		}

		new $class_name( $this );
	}

	/**
	 * Get settings option
	 *
	 * @param string $option_name
	 * @param string $default_value
	 *
	 * @return false|mixed|void
	 */
	public function getOption( $option_name, $default_value = '' ) {
		$option = get_option( WTBP_PLUGIN_PREFIX . '_' . $option_name );

		return $option !== false ? $option : $default_value;
	}

	/**
	 * Add settings option
	 *
	 * @param string $option_name
	 * @param string $option_value
	 *
	 * @return bool
	 */
	public function addOption( $option_name, $option_value ) {
		return add_option( WTBP_PLUGIN_PREFIX . '_' . $option_name, $option_value );
	}

	/**
	 * Update settings option
	 *
	 * @param string $option_name
	 * @param string $option_value
	 *
	 * @return bool
	 */
	public function updateOption( $option_name, $option_value ) {
		return update_option( WTBP_PLUGIN_PREFIX . '_' . $option_name, $option_value );
	}

	/**
	 * Delete settings option
	 *
	 * @param string $option_name
	 *
	 * @return bool
	 */
	public function deleteOption( $option_name ) {
		return delete_option( WTBP_PLUGIN_PREFIX . '_' . $option_name );
	}

	/**
	 * Method renders layout template
	 *
	 * @param string $template_name Template name without ".php"
	 * @param array $args Template arguments
	 *
	 * @return false|string
	 */
	public function render_template( $template_name, $args = [] ) {
		$template_name = apply_filters( WTBP_PLUGIN_PREFIX . '/template/name', $template_name, $args );

		$path = WTBP_PLUGIN_PATH . "/templates/$template_name.php";
		if ( file_exists( $path ) ) {
			ob_start();
			include $path;

			return apply_filters( WTBP_PLUGIN_PREFIX . '/content/template', ob_get_clean(), $template_name, $args );
		} else {
			return apply_filters( WTBP_PLUGIN_PREFIX . '/message/template_not_found', __( 'This template does not exist!', 'bulk-plugins' ) );
		}
	}
}
