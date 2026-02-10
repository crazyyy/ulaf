<?php
/**
 * Redirect Manager Loop Detection
 *
 * Handles detection and prevention of redirect loops
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for redirect loop detection
 */
class ASENHA_Redirect_Manager_Loop_Detection {

	/**
	 * Maximum number of redirects allowed in a chain
	 *
	 * @var int
	 */
	const MAX_REDIRECTS = 3;

	/**
	 * Transient expiration time (60 seconds)
	 *
	 * @var int
	 */
	const TRANSIENT_EXPIRATION = 60;

	/**
	 * Check for redirect loops
	 *
	 * @since 8.1.0
	 * @param string $path The path being redirected
	 * @return bool True if no loop detected, false if loop detected
	 */
	public function is_not_loop( $path ) {
		$session_hash = $this->get_session_hash();
		$transient_key = 'asenha_redirect_loop_' . $session_hash;
		
		// Get visited paths for this session
		$visited_paths = get_transient( $transient_key );
		
		if ( false === $visited_paths ) {
			$visited_paths = array();
		}
		
		// Normalize path for comparison
		$normalized_path = strtolower( $path );
		
		// Count how many times this path has been visited
		$visit_count = 0;
		foreach ( $visited_paths as $visited_path ) {
			if ( $visited_path === $normalized_path ) {
				$visit_count++;
			}
		}
		
		// If this path has been visited too many times, it's a loop
		if ( $visit_count >= self::MAX_REDIRECTS ) {
			// Log the error if WP_DEBUG is enabled
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				/* translators: %s is the path, %d is the visit count */
				error_log( sprintf(
					'ASENHA Redirect Manager: Redirect loop detected for path "%s". Loop count: %d',
					$path,
					$visit_count
				) );
			}
			
			return false; // Loop detected
		}
		
		// Add current path to visited paths
		$visited_paths[] = $normalized_path;
		
		// Store updated visited paths
		set_transient( $transient_key, $visited_paths, self::TRANSIENT_EXPIRATION );
		
		return true; // No loop detected
	}

	/**
	 * Get a unique session hash for the current request
	 *
	 * @since 8.1.0
	 * @return string Session hash
	 */
	private function get_session_hash() {
		// Create a hash based on IP address and user agent
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$time = floor( time() / self::TRANSIENT_EXPIRATION ); // Changes every minute
		
		return md5( $ip . $user_agent . $time );
	}

	/**
	 * Clear loop detection data for a specific session
	 *
	 * @since 8.1.0
	 * @param string $session_hash The session hash (optional, uses current if not provided)
	 */
	public function clear_loop_data( $session_hash = '' ) {
		if ( empty( $session_hash ) ) {
			$session_hash = $this->get_session_hash();
		}
		
		$transient_key = 'asenha_redirect_loop_' . $session_hash;
		delete_transient( $transient_key );
	}
}

