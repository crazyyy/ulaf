<?php
/**
 * Core plugin functionality.
 *
 * Handles plugin initialization, settings, and directory management.
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook Locator Core class.
 *
 * Manages core plugin functionality including directory scanning
 * and plugin environment setup.
 *
 * @since 1.0
 */
class Hook_Locator_Core {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0
	 * @var   Hook_Locator_Core|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * Implements singleton pattern to ensure only one instance exists.
	 *
	 * @since 1.0
	 * @return Hook_Locator_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get available directories for scanning.
	 *
	 * Returns an organized array of plugin and theme directories
	 * available for hook scanning.
	 *
	 * @since 1.0
	 * @return array Associative array of directory keys and paths.
	 */
	public function get_scan_directories() {
		$dirs = array();

		// Get plugins directory.
		$plugin_path = WP_PLUGIN_DIR;
		if ( is_dir( $plugin_path ) ) {
			$plugin_folders = glob( $plugin_path . '/*', GLOB_ONLYDIR );
			if ( false !== $plugin_folders ) {
				foreach ( $plugin_folders as $dir ) {
					$slug = basename( $dir );
					// Skip current plugin to avoid recursion.
					if ( 'hook-locator' !== $slug ) {
						$dirs[ 'plugin:' . $slug ] = $dir;
					}
				}
			}
		}

		// Get themes directory.
		$theme_path = get_theme_root();
		if ( is_dir( $theme_path ) ) {
			$theme_folders = glob( $theme_path . '/*', GLOB_ONLYDIR );
			if ( false !== $theme_folders ) {
				foreach ( $theme_folders as $dir ) {
					$slug                       = basename( $dir );
					$dirs[ 'theme:' . $slug ] = $dir;
				}
			}
		}

		return $dirs;
	}

	/**
	 * Get organized directories for dropdown display.
	 *
	 * Returns directories organized by type (plugins/themes) for
	 * better user interface display.
	 *
	 * @since 1.0
	 * @return array Multi-dimensional array organized by type.
	 */
	public function get_organized_directories() {
		$all_dirs  = $this->get_scan_directories();
		$organized = array(
			'plugins' => array(),
			'themes'  => array(),
		);

		foreach ( $all_dirs as $key => $path ) {
			$parts = explode( ':', $key, 2 );
			if ( 2 === count( $parts ) ) {
				$type = sanitize_key( $parts[0] );
				$slug = sanitize_key( $parts[1] );

				if ( 'plugin' === $type ) {
					$organized['plugins'][ $key ] = $slug;
				} elseif ( 'theme' === $type ) {
					$organized['themes'][ $key ] = $slug;
				}
			}
		}

		// Sort alphabetically.
		ksort( $organized['plugins'] );
		ksort( $organized['themes'] );

		return $organized;
	}
}
