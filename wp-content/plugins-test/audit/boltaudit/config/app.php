<?php
/**
 * BoltAudit configuration.
 *
 * @package BoltAudit
 */

defined( 'ABSPATH' ) || exit;

use BoltAudit\App\Http\Middleware\EnsureIsUserAdmin;
use BoltAudit\App\Providers\MenuServiceProvider;
use BoltAudit\WpMVC\Helpers\Helpers;

return array(
	/**
	 * The version of the plugin.
	 */
	'version'                     => Helpers::get_plugin_version( 'boltaudit' ),

	/**
	 * Configuration for the REST API.
	 */
	'rest_api'                    => array(
		/**
		 * The namespace for the REST API.
		 */
		'namespace' => 'boltaudit',

		/**
		 * The versions of the REST API.
		 */
		'versions'  => array(),
	),

	/**
	 * Configuration for the AJAX API.
	 */
	'ajax_api'                    => array(
		/**
		 * The namespace for the AJAX API.
		 */
		'namespace' => 'boltaudit',

		/**
		 * The versions of the AJAX API.
		 */
		'versions'  => array(),
	),

	/**
	 * Service providers for the plugin.
	 */
	'providers'                   => array(),

	/**
	 * Service providers for the admin area of the plugin.
	 */
	'admin_providers'             => array(
		MenuServiceProvider::class,
	),

	/**
	 * Middleware configuration for the plugin.
	 */
	'middleware'                  => array(
		/**
		 * Middleware for admin routes.
		 */
		'admin' => EnsureIsUserAdmin::class,
	),

	/**
	 * The database option key for storing migration information.
	 */
	// 'migration_db_option_key'     => 'boltaudit_migrations',

	/**
	 * List of migrations for the plugin.
	 */
	'migrations'                  => array(),

	/**
	 * This configuration option defines a hook that will fire before executing the route callback,
	 * such as before a controller action. It provides two parameters:
	 *
	 * @param WP_REST_Request $wp_rest_request The current REST request object.
	 * @param string $full_route The full route being accessed.
	 */
	'rest_response_action_hook'   => 'boltaudit_rest_response_action',

	/**
	 * Configuration for the REST API response filter hook.
	 *
	 * This filter hook allows overriding the entire REST API response.
	 *
	 * @param $response The response object from the controller.
	 * @param WP_REST_Request  $wp_rest_request The request object.
	 * @param string           $full_route The full route of the request.
	 */
	'rest_response_filter_hook'   => 'boltaudit_rest_response_filter',

	/**
	 * This filter hook that can override all REST API permissions.
	 *
	 * @param mixed $permission The current permission setting.
	 * @param mixed $middleware The middleware being applied.
	 * @param string $full_route The full route of the API endpoint.
	 */
	'rest_permission_filter_hook' => 'boltaudit_rest_permission_filter',
);
