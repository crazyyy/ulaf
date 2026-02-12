<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

class WPDT_Config_Manager {

	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	public static function init() {

		// Update debug constants.
		add_action( 'wpdt_save_general_settings', array( __CLASS__, 'set_debug_constants' ) );
		add_action( 'wpdt_reset_general_settings', array( __CLASS__, 'set_debug_constants' ) );

		// Activation and deactivation hooks.
		add_action( 'wpdt_activation', array( __CLASS__, 'activate' ) );
		add_action( 'wpdt_deactivation', array( __CLASS__, 'deactivate' ) );
	}

	/**
	 * Update debug constants
	 *
	 * @param array $settings - Debug settings.
	 * @return void
	 */
	public static function set_debug_constants( $settings ) {

		$wp_debug = isset( $settings['wp_debug'] ) && $settings['wp_debug'] ? 'true' : 'false';
		$wp_debug_log = isset( $settings['wp_debug_log'] ) && $settings['wp_debug_log'] ? 'true' : 'false';
		$wp_debug_display = isset( $settings['wp_debug_display'] ) && $settings['wp_debug_display'] ? 'true' : 'false';
		$script_debug = isset( $settings['script_debug'] ) && $settings['script_debug'] ? 'true' : 'false';

		$wp_config_path = self::wpdt_get_config_path();
		$transformer = new WPConfigTransformer( $wp_config_path );

		$transformer->update( 'constant', 'WP_DEBUG', $wp_debug, array( 'raw' => true ) );
		$transformer->update( 'constant', 'WP_DEBUG_LOG', $wp_debug_log, array( 'raw' => true ) );
		$transformer->update( 'constant', 'WP_DEBUG_DISPLAY', $wp_debug_display, array( 'raw' => true ) );
		$transformer->update( 'constant', 'SCRIPT_DEBUG', $script_debug, array( 'raw' => true ) );
	}

	/**
	 * Actions to perform after plugin activated
	 *
	 * @return void
	 */
	public static function activate() {

		if ( ! self::wpdt_is_wp_config_writable() ) {
			return;
		}

		$wp_config_path = self::wpdt_get_config_path();
		$transformer = new WPConfigTransformer( $wp_config_path );

		// update constants.
		self::add_or_update_constant( $transformer, 'WP_DEBUG', 'true' );
		self::add_or_update_constant( $transformer, 'WP_DEBUG_LOG', 'true' );
		self::add_or_update_constant( $transformer, 'WP_DEBUG_DISPLAY', 'false' );
		self::add_or_update_constant( $transformer, 'SCRIPT_DEBUG', 'false' );

		// update debug settings.
		$settings = array(
			'wp_debug'         => 1,
			'wp_debug_log'     => 1,
			'wp_debug_display' => 0,
			'script_debug'     => 0,
			'savequeries'      => 0,
		);
		update_option( 'wpdt_settings', $settings );
	}

	/**
	 * Actions to perform after plugin deactivated
	 *
	 * @return void
	 */
	public static function deactivate() {

		if ( ! self::wpdt_is_wp_config_writable() ) {
			return;
		}

		$wp_config_path = self::wpdt_get_config_path();
		$transformer = new WPConfigTransformer( $wp_config_path );

		// update constants.
		self::add_or_update_constant( $transformer, 'WP_DEBUG', 'false' );
		self::add_or_update_constant( $transformer, 'WP_DEBUG_LOG', 'false' );
		self::add_or_update_constant( $transformer, 'WP_DEBUG_DISPLAY', 'false' );
		self::add_or_update_constant( $transformer, 'SCRIPT_DEBUG', 'false' );

		// update debug settings.
		$settings = array(
			'wp_debug'         => 0,
			'wp_debug_log'     => 0,
			'wp_debug_display' => 0,
			'script_debug'     => 0,
			'savequeries'      => 0,
		);
		update_option( 'wpdt_settings', $settings );
	}

	/**
	 * Add or update constant
	 *
	 * @param WPConfigTransformer $transformer - Transformer object.
	 * @param string              $name - Constant name.
	 * @param string              $value - Constant value.
	 * @return void
	 */
	private static function add_or_update_constant( $transformer, $name, $value ) {
		if ( $transformer->exists( 'constant', $name ) ) {
			$transformer->update( 'constant', $name, $value, array( 'raw' => true ) );
		} else {
			$transformer->add( 'constant', $name, $value, array( 'raw' => true ) );
		}
	}

	/**
	 * Get the path to the wp-config.php file
	 *
	 * @return string
	 */
	public static function wpdt_get_config_path() {

		return trailingslashit( get_home_path() ) . 'wp-config.php';
	}

	/**
	 * Check if wp-config.php is writable
	 *
	 * @return bool
	 */
	public static function wpdt_is_wp_config_writable() {
		$wp_config_path = self::wpdt_get_config_path();
		return is_writable( $wp_config_path ); // phpcs:ignore
	}

	/**
	 * Show notice if wp-config.php is not writable
	 *
	 * @return void
	 */
	public static function wpdt_config_writable_notice() {
		if ( ! self::wpdt_is_wp_config_writable() ) {
			?>
			<div class="wpdt-admin-notice-error">
				Please note, wp-config.php is not writable. Please change file permissions.
			</div>
			<?php
		}
	}
}

WPDT_Config_Manager::init();
