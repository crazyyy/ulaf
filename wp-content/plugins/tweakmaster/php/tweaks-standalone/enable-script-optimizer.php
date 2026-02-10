<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Nextgenthemes Script Optimizer
 * Description:      Makes scripts load deferred
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\ScriptOptimizer;

use _WP_Dependency;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\optimize_scripts', PHP_INT_MAX, 0 );
/**
 * Loops through the scripts enqueued on the frontend and optionally sets their strategy to defer or async.
 */
function optimize_scripts(): void {
	foreach ( wp_scripts()->queue as $handle ) {
		optimize_script( $handle, get_array_key_value( $handle, wp_scripts()->registered ) );
	}
}

/**
 * Optimize Single Script
 *
 * If script does not have a strategy, it is set to be deferred unless it is defined as excluded or async.
 *
 * @param string $handle Name of the script
 * @param _WP_Dependency|null $data Optional. The enqueue/registration data of the script.
 */
function optimize_script( string $handle, ?_WP_Dependency $data ): void {

	if ( empty( $data ) ) {
		return;
	}

	// If script already has a strategy set, do nothing
	if ( get_array_key_value( 'strategy', $data->extra ) ) {
		return;
	}

	$async                   = false;
	$exclude_handles         = (array) apply_filters( 'tweakmaster/script_optimizer/exclude_handles', [] );
	$exclude_handle_prefixes = (array) apply_filters( 'tweakmaster/script_optimizer/exclude_handle_prefixes', [] );
	$exclude_urls            = (array) apply_filters( 'tweakmaster/script_optimizer/exclude_urls', [] );
	$exclude_url_parts       = (array) apply_filters( 'tweakmaster/script_optimizer/exclude_url_parts', [] );
	$async_handles           = (array) apply_filters( 'tweakmaster/script_optimizer/async_handles', [] );
	$async_handle_prefixes   = (array) apply_filters( 'tweakmaster/script_optimizer/async_handle_prefixes', [] );
	$async_url_parts         = (array) apply_filters( 'tweakmaster/script_optimizer/async_url_parts', [] );

	/**
	 * Identify Excluded
	 */
	if ( in_array( $handle, $exclude_handles, true ) ) {
		return; // Exclude specified handles
	}

	if ( ! empty( $exclude_handle_prefixes ) ) {

		foreach ( $exclude_handle_prefixes as $exclude_handle_prefix ) {
			if ( str_starts_with( $handle, $exclude_handle_prefix ) ) {
				return; // Exclude those that include this part of a handle
			}
		}
	}

	if ( in_array( $data->src, $exclude_urls, true ) ) {
		return; // Exclude specified URLs
	}

	foreach ( $exclude_url_parts as $exclude_url_part ) {
		if ( str_contains( $data->src, $exclude_url_part ) ) {
			return; // Exclude those that include this part in their URL
		}
	}

	if ( in_array( $handle, $async_handles, true ) ) {
		$async = true;
	} else {

		foreach ( $async_handle_prefixes as $async_prefix ) {
			if ( str_starts_with( $handle, $async_prefix ) ) {
				$async = true;
				break; // Break early from for-loop if found
			}
		}
	}

	if ( ! $async ) {

		foreach ( $async_url_parts as $async_url_part ) {
			if ( str_contains( $data->src, $async_url_part ) ) {
				$async = true;
				break; // Break early from for-loop if found
			}
		}
	}

	if ( 'jquery' === $handle ) {
		// Also do it for jQuery core since it's not enqueued the same as jQuery
		optimize_script( 'jquery-core', get_array_key_value( 'jquery-core', wp_scripts()->registered ) );
		optimize_script( 'jquery-migrate', get_array_key_value( 'jquery-migrate', wp_scripts()->registered ) );
		return;
	} elseif ( empty( $data->src ) ) {
		return; // This should not be done to scripts registered without a src (inline scripts), we exit early.
	}

	// Finally set the script's strategy
	if ( $async ) {
		wp_script_add_data( $handle, 'strategy', 'async' );
	} else {
		// Move to head and defer everything else
		wp_script_add_data( $handle, 'group', 0 ); // Move to <head> in case it is not already there
		wp_script_add_data( $handle, 'strategy', 'defer' ); // Set the script's strategy to defer

		// Update dependencies since changing to defer load
		foreach ( $data->deps as $handle_dep ) {
			optimize_script( $handle_dep, get_array_key_value( $handle_dep, wp_scripts()->registered ) );
		}
	}
}

/**
 * Array Key Exists and Has Value
 *
 * @param string               $key           The key to search for in the array.
 * @param array<string, mixed> $arr           The array to search.
 * @param mixed                $default_value The default value to return if not found or is empty. Default is an empty string.
 *
 * @return mixed                              The value of the key found in the array if it exists or the value of `$default_value` if not found or is empty.
 */
function get_array_key_value( string $key, array $arr, $default_value = null ) {
	return array_key_exists( $key, $arr ) && ! empty( $arr[ $key ] ) ? $arr[ $key ] : $default_value;
}
