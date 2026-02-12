<?php
/**
 * Uninstall Hook
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 */

// Exit if uninstall constant is not defined.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	define( 'KWLOGMANAGER_BASE', __DIR__ . '/' );
}

require_once KWLOGMANAGER_BASE . '/autoload.php';

use KolorWeb\KWLogManager\Config;

if ( ! function_exists( 'kw_log_manager_plugin_deactivate' ) ) {

	/**
	 * Deactivate plugin
	 *
	 * @since 1.0.0
	 */
	function kw_log_manager_plugin_deactivate() {
		$config = Config::get_instance();
		$config->disable_debugging();
	}
}

if ( ! function_exists( 'kw_log_manager_plugin_uninstall' ) ) {

	/**
	 * Uninstall plugin
	 *
	 * @since 1.0.0
	 */
	function kw_log_manager_plugin_uninstall() {

		global $wpdb;

		// Remove all Configurations Files and restore wp-config.php.
		kw_log_manager_plugin_deactivate();

		// Database Cleaning.
		$meta_key = array( '_kwlm_settings', 'kwlm_api_key' );
		foreach ( $meta_key as $meta ) {
			// Clean all user meta data having the specified key.
			\delete_metadata( 'user', 0, $meta, '', true );
		}
		$wpdb->query( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%_kwlm_%"' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}

// Let's go!
kw_log_manager_plugin_uninstall();
