<?php

namespace BoltAudit\App\Repositories;

use BoltAudit\App\Repositories\OptionsRepository;
use BoltAudit\App\Services\PluginMetricsCollector;

defined( 'ABSPATH' ) || exit;

class SinglePluginRepository {

	protected static ?PluginMetricsCollector $collector = null;
	protected static array $active_plugins              = [];
	protected static array $all_plugins                 = [];
	protected static array $plugin_updates              = [];

	protected static function ensure_wp_functions(): void {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		self::$plugin_updates = get_plugin_updates();
		self::$active_plugins = get_option( 'active_plugins', [] );
		self::$all_plugins    = get_plugins();
	}

	protected static function get_plugin_key( string $slug, string $version ): string {
		return "plugin_data_{$slug}_v{$version}";
	}

	public static function get_basic_plugin_data( string $plugin_file, array $plugin_data ): array {
		self::ensure_wp_functions();

		$slug       = dirname( $plugin_file );
		$version    = $plugin_data['Version'] ?? 'unknown';
		$option_key = self::get_plugin_key( $slug, $version );

		$cached = OptionsRepository::get_option( $option_key, 'plugin_basic_cache' );
		if ( $cached && ! empty( $cached['data'] ) ) {
			$data              = json_decode( $cached['data'], true );
			$data['is_active'] = in_array( $plugin_file, self::$active_plugins );

			return $data;
		}

		$wp_org_info  = self::fetch_wp_org_info( $slug );
		$is_wp_repo   = ! empty( $wp_org_info ) && ! is_wp_error( $wp_org_info );
		$last_updated = $is_wp_repo ? ( $wp_org_info->last_updated ?? null ) : null;
		$is_abandoned = $last_updated ? self::is_abandoned( $last_updated ) : null;

		$data = [
			'name'          => $plugin_data['Name'] ?? '',
			'slug'          => $slug,
			'plugin_file'   => $plugin_file,
			'needs_upgrade' => isset( self::$plugin_updates[$plugin_file] ),
			'is_wp_repo'    => $is_wp_repo,
			'is_active'     => in_array( $plugin_file, self::$active_plugins ),
			'last_updated'  => $last_updated,
			'is_abandoned'  => $is_abandoned,
			'version'       => $version,
			'author'        => $plugin_data['Author'] ?? '',
		];

		OptionsRepository::create_option( $option_key, 'plugin_basic_cache', $data );

		return $data;
	}

	public static function get_plugin_page_data( string $slug ): array {
		self::ensure_wp_functions();

		$resolved = self::resolve_plugin_data_by_slug( $slug );
		if ( empty( $resolved ) ) {
			return [
				'error' => "Plugin not found for slug '{$slug}'",
			];
		}

		$plugin_file = $resolved['plugin_file'];
		$plugin_data = $resolved['plugin_data'];
		$version     = $plugin_data['Version'] ?? 'unknown';
		$option_key  = self::get_plugin_key( $slug, $version );

		$plugin_basic  = self::get_basic_plugin_data( $plugin_file, $plugin_data );
		$plugin_single = OptionsRepository::get_option( $option_key, 'plugin_single_cache' );

		if ( $plugin_single && ! empty( $plugin_single['data'] ) ) {
			return [
				'plugin_page'  => json_decode( $plugin_single['data'], true ),
				'plugin_basic' => $plugin_basic,
			];
		}

		$metrics = ( new PluginMetricsCollector() )->run( $slug );

		OptionsRepository::create_option( $option_key, 'plugin_single_cache', $metrics );

		return [
			'plugin_page'  => $metrics,
			'plugin_basic' => $plugin_basic,
		];
	}

	protected static function fetch_wp_org_info( string $slug ) {
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		try {
			$info = plugins_api( 'plugin_information', [
				'slug'   => $slug,
				'fields' => ['last_updated' => true],
			] );
		} catch ( \Throwable $e ) {
			error_log( sprintf( 'plugins_api failed for %s: %s', $slug, $e->getMessage() ) );
			$info = null;
		}

		return is_wp_error( $info ) ? null : $info;
	}

	protected static function is_abandoned( string $last_updated ): bool {
		$timestamp = strtotime( $last_updated );

		return $timestamp && ( time() - $timestamp ) > YEAR_IN_SECONDS;
	}

	protected static function resolve_plugin_data_by_slug( string $slug ): array {
		self::ensure_wp_functions();

		foreach ( self::$all_plugins as $plugin_file => $plugin_data ) {
			if ( dirname( $plugin_file ) === $slug ) {
				return [
					'plugin_file' => $plugin_file,
					'plugin_data' => $plugin_data,
				];
			}
		}

		return [];
	}
}
