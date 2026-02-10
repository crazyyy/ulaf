<?php

namespace BoltAudit\App\Repositories;

defined( 'ABSPATH' ) || exit;

class EnvironmentRepository {
	public static function get_php_version() {
		return phpversion();
	}

	public static function get_php_sapi() {
		return php_sapi_name();
	}

	public static function get_php_user() {
		return function_exists( 'get_current_user' ) ? get_current_user() : 'anonymous';
	}

	public static function get_php_ini_values() {
		return [
			'max_execution_time'  => ini_get( 'max_execution_time' ),
			'memory_limit'        => ini_get( 'memory_limit' ),
			'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
			'post_max_size'       => ini_get( 'post_max_size' ),
			'display_errors'      => ini_get( 'display_errors' ),
			'log_errors'          => ini_get( 'log_errors' ),
		];
	}

	public static function get_php_extensions() {
		$extensions = get_loaded_extensions();
		sort( $extensions );

		return $extensions;
	}

	public static function get_php_error_reporting() {
		return error_reporting();
	}

	public static function get_server_software() {
		return isset( $_SERVER['SERVER_SOFTWARE'] )
			? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) )
			: null;
	}

	public static function get_server_address() {
		return isset( $_SERVER['SERVER_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) )
			: null;
	}

	public static function get_server_os() {
		return function_exists( 'php_uname' ) ? php_uname( 's' ) . ' ' . php_uname( 'r' ) : null;
	}

	public static function get_server_host() {
		return function_exists( 'php_uname' ) ? php_uname( 'n' ) : null;
	}

	public static function get_server_arch() {
		return function_exists( 'php_uname' ) ? php_uname( 'm' ) : null;
	}

	public static function get_wordpress_version() {
		global $wp_version;

		return $wp_version ?? null;
	}

	public static function get_wordpress_constants() {
		return [
			'WP_DEBUG'            => defined( 'WP_DEBUG' ) ? WP_DEBUG : null,
			'WP_DEBUG_DISPLAY'    => defined( 'WP_DEBUG_DISPLAY' ) ? WP_DEBUG_DISPLAY : null,
			'WP_DEBUG_LOG'        => defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : null,
			'SCRIPT_DEBUG'        => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : null,
			'WP_CACHE'            => defined( 'WP_CACHE' ) ? WP_CACHE : null,
			'CONCATENATE_SCRIPTS' => defined( 'CONCATENATE_SCRIPTS' ) ? constant( 'CONCATENATE_SCRIPTS' ) : null,
			'COMPRESS_SCRIPTS'    => defined( 'COMPRESS_SCRIPTS' ) ? constant( 'COMPRESS_SCRIPTS' ) : null,
			'COMPRESS_CSS'        => defined( 'COMPRESS_CSS' ) ? constant( 'COMPRESS_CSS' ) : null,
		];
	}

	public static function get_environment_type() {
		return function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
	}

	public static function get_development_mode() {
		return function_exists( 'wp_get_development_mode' ) ? wp_get_development_mode() : null;
	}

	public static function get_database_info() {
		global $wpdb;

		if ( ! $wpdb ) {
			return [];
		}

		$server_version = $wpdb->get_var( 'SELECT VERSION()' );

		return [
			'database_name'  => $wpdb->dbname,
			'database_user'  => $wpdb->dbuser,
			'database_host'  => $wpdb->dbhost,
			'server_version' => $server_version,
		];
	}

	public static function get_all() {
		return [
			'php'       => [
				'version'         => self::get_php_version(),
				'sapi'            => self::get_php_sapi(),
				'user'            => self::get_php_user(),
				'ini_values'      => self::get_php_ini_values(),
				'extensions'      => self::get_php_extensions(),
				'error_reporting' => self::get_php_error_reporting(),
			],
			'server'    => [
				'software' => self::get_server_software(),
				'address'  => self::get_server_address(),
				'os'       => self::get_server_os(),
				'host'     => self::get_server_host(),
				'arch'     => self::get_server_arch(),
			],
			'wordpress' => [
				'version'          => self::get_wordpress_version(),
				'constants'        => self::get_wordpress_constants(),
				'environment_type' => self::get_environment_type(),
				'development_mode' => self::get_development_mode(),
			],
			'database'  => self::get_database_info(),
		];
	}
}
