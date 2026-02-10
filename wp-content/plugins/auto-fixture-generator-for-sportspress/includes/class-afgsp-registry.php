<?php
/**
 * Algorithm registry for Auto Fixture Generator for SportsPress.
 *
 * @package AFGSP
 */

declare( strict_types=1 );

namespace AFGSP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AFGSP_Registry
 *
 * Provides loading of algorithm callbacks from discovered files. Algorithms must
 * implement a function `afgsp_generate_fixtures( array $teams, array $options ): array` in their file
 * and register themselves via `add_filter( 'afgsp_algorithms', ... )`.
 */
class AFGSP_Registry {
	/**
	 * Get available algorithms.
	 *
	 * @return array<string,array>
	 */
	public static function get_algorithms(): array {
		return \AFGSP\afgsp_discover_algorithms();
	}

	/**
	 * Load and return the callable for the given algorithm slug.
	 *
	 * @param string $slug Algorithm slug.
	 * @return callable|null
	 */
	public static function get_algorithm_callable( string $slug ) {
		$algorithms = self::get_algorithms();
		if ( ! isset( $algorithms[ $slug ] ) ) {
			return null;
		}

		$file = (string) $algorithms[ $slug ]['file'];
		if ( $file && file_exists( $file ) ) {
			// Load algorithm file dynamically - this is safe as we control the file paths.
			require_once $file;
		}

		// Algorithms must expose a global namespaced function with a known name pattern.
		$function = '\\AFGSP\\Algorithms\\' . str_replace( '-', '_', $slug ) . '\\generate_fixtures';
		if ( is_callable( $function ) ) {
			return $function;
		}

		return null;
	}

	/**
	 * Get algorithm info for the given algorithm slug.
	 *
	 * @param string $slug Algorithm slug.
	 * @return array|null
	 */
	public static function get_algorithm_info( string $slug ) {
		$algorithms = self::get_algorithms();
		return isset( $algorithms[ $slug ] ) ? $algorithms[ $slug ] : null;
	}
}


