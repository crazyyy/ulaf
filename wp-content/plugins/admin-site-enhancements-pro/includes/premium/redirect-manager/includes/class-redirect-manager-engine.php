<?php
/**
 * Redirect Manager Engine
 *
 * Handles redirect execution and matching logic
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for redirect execution engine
 */
class ASENHA_Redirect_Manager_Engine {

	/**
	 * Cache instance
	 *
	 * @var ASENHA_Redirect_Manager_Cache
	 */
	private $cache;

	/**
	 * Loop detection instance
	 *
	 * @var ASENHA_Redirect_Manager_Loop_Detection
	 */
	private $loop_detection;

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		$this->cache = new ASENHA_Redirect_Manager_Cache();
		$this->loop_detection = new ASENHA_Redirect_Manager_Loop_Detection();
		
		add_action( 'template_redirect', array( $this, 'execute_redirects' ), 1 );
		add_action( 'save_post_asenha_redirect', array( $this, 'bust_cache_on_save' ) );
		add_action( 'delete_post', array( $this, 'bust_cache_on_delete' ) );
	}

	/**
	 * Execute redirects
	 *
	 * @since 8.1.0
	 */
	public function execute_redirects() {
		// Don't redirect in admin, customizer, AJAX, or cron
		if ( is_admin() || is_customize_preview() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}
		
		// Get redirects from cache or build cache
		$redirects = $this->get_redirects();
		
		if ( empty( $redirects ) ) {
			return;
		}
		
		// Get and normalize the requested path
		$raw_path = esc_url_raw( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		$requested_path = $this->normalize_path( $raw_path );
		
		// Find matching redirect
		$matched_redirect = $this->find_match( $requested_path, $redirects );
		
		if ( ! $matched_redirect ) {
			return;
		}
		
		// Check for redirect loops
		if ( ! $this->loop_detection->is_not_loop( $requested_path ) ) {
			return; // Loop detected, abort redirect
		}
		
		// Prepare redirect URL
		$redirect_to = $matched_redirect['to'];
		
		// Handle regex backreference replacements
		if ( ! empty( $matched_redirect['regex'] ) && ! empty( $matched_redirect['regex_matches'] ) ) {
			$redirect_to = $this->regex_replacement( $redirect_to, $matched_redirect['regex_matches'] );
		}
		// Handle wildcard replacements
		elseif ( strpos( $matched_redirect['from'], '*' ) !== false && strpos( $redirect_to, '*' ) !== false ) {
			$redirect_to = $this->wildcard_replacement( $matched_redirect['from'], $redirect_to, $requested_path );
		}
		
		// Forward query parameters only if not disabled
		if ( empty( $matched_redirect['strip_query_params'] ) ) {
			$redirect_to = $this->forward_query_params( $redirect_to );
		}
		
		// Add custom headers
		$this->add_redirect_headers( $matched_redirect['id'] );
		
		// Perform redirect based on status code
		$this->perform_redirect( $redirect_to, $matched_redirect['status_code'], $matched_redirect['message'] );
	}

	/**
	 * Get redirects from cache or build cache
	 *
	 * @since 8.1.0
	 * @return array Redirects array
	 */
	private function get_redirects() {
		$redirects = $this->cache->get_cache();
		
		if ( false === $redirects ) {
			$redirects = $this->cache->build_cache();
		}
		
		return $redirects;
	}

	/**
	 * Normalize requested path
	 *
	 * @since 8.1.0
	 * @param string $path The requested path
	 * @return string Normalized path
	 */
	private function normalize_path( $path ) {		
		// Parse URL
		$parsed = parse_url( $path );
		$path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
		
		// Handle subdirectory installs
		$home_path = parse_url( home_url(), PHP_URL_PATH );
		if ( $home_path && $home_path !== '/' ) {
			$path = str_replace( $home_path, '', $path );
		}
		
		// Ensure path starts with /
		if ( substr( $path, 0, 1 ) !== '/' ) {
			$path = '/' . $path;
		}
		
		// Collapse multiple consecutive slashes into single slash
		$path = preg_replace( '#/+#', '/', $path );
		
		// Remove trailing slash for consistency (except for root)
		if ( $path !== '/' ) {
			$path = rtrim( $path, '/' );
		}
		
		return $path;
	}

	/**
	 * Find matching redirect
	 *
	 * @since 8.1.0
	 * @param string $requested_path The requested path
	 * @param array $redirects Array of redirects
	 * @return array|false Matched redirect or false
	 */
	private function find_match( $requested_path, $redirects ) {
		foreach ( $redirects as $redirect_id => $redirect ) {
			$from_path = $redirect['from'];
			
			// Normalize from path
			$from_path = rtrim( $from_path, '/' );
			if ( empty( $from_path ) ) {
				$from_path = '/';
			}
			
			// Check if regex is enabled
			if ( $redirect['regex'] ) {
				$regex_matches = array();
				if ( $this->regex_match( $from_path, $requested_path, $regex_matches ) ) {
					$redirect['id'] = $redirect_id;
					$redirect['regex_matches'] = $regex_matches;
					return $redirect;
				}
			}
			
			// Check for wildcard match
			elseif ( strpos( $from_path, '*' ) !== false ) {
				if ( $this->wildcard_match( $from_path, $requested_path ) ) {
					$redirect['id'] = $redirect_id;
					return $redirect;
				}
			}
			// Exact match (case-insensitive)
			elseif ( strtolower( $from_path ) === strtolower( $requested_path ) ) {
				$redirect['id'] = $redirect_id;
				return $redirect;
			}
		}
		
		return false;
	}

	/**
	 * Wildcard match
	 *
	 * @since 8.1.0
	 * @param string $pattern The pattern with wildcard
	 * @param string $path The path to match
	 * @return bool True if matches
	 */
	private function wildcard_match( $pattern, $path ) {
		// Convert wildcard to regex pattern
		$regex_pattern = str_replace( '\*', '.*', preg_quote( $pattern, '/' ) );
		$regex_pattern = '/^' . $regex_pattern . '$/i'; // Case insensitive
		
		return preg_match( $regex_pattern, $path ) === 1;
	}

	/**
	 * Wildcard replacement
	 *
	 * @since 8.1.0
	 * @param string $from_pattern The from pattern
	 * @param string $to_pattern The to pattern
	 * @param string $requested_path The requested path
	 * @return string Modified redirect URL
	 */
	private function wildcard_replacement( $from_pattern, $to_pattern, $requested_path ) {
		// Extract the wildcard part from the requested path
		$from_pattern_regex = str_replace( '\*', '(.*)', preg_quote( $from_pattern, '/' ) );
		$from_pattern_regex = '/^' . $from_pattern_regex . '$/i';
		
		if ( preg_match( $from_pattern_regex, $requested_path, $matches ) ) {
			if ( isset( $matches[1] ) ) {
				// Replace the first * in the to pattern with the matched string
				$to_pattern = preg_replace( '/\*/', $matches[1], $to_pattern, 1 );
			}
		}
		
		return $to_pattern;
	}

	/**
	 * Regex backreference replacement
	 *
	 * Replaces $1, $2, $3, etc. in redirect-to URL with captured groups
	 *
	 * @since 8.1.0
	 * @param string $to_pattern The redirect-to pattern
	 * @param array $matches Array of regex matches from preg_match
	 * @return string Modified redirect URL with backreferences replaced
	 */
	private function regex_replacement( $to_pattern, $matches ) {
		// Replace $1, $2, $3, etc. with captured groups
		// Use reverse order to prevent $1 replacing part of $10, $11, etc.
		for ( $i = count( $matches ) - 1; $i >= 0; $i-- ) {
			$to_pattern = str_replace( '$' . $i, $matches[ $i ], $to_pattern );
		}
		
		return $to_pattern;
	}

	/**
	 * Regex match
	 *
	 * @since 8.1.0
	 * @param string $pattern The regex pattern
	 * @param string $path The path to match
	 * @param array $matches Optional array to store captured groups
	 * @return bool True if matches
	 */
	private function regex_match( $pattern, $path, &$matches = array() ) {
		// Ensure pattern has delimiters
		// Check if first character is a common delimiter
		$first_char = substr( $pattern, 0, 1 );
		if ( ! in_array( $first_char, array( '/', '#', '~', '|', '@', '`' ), true ) ) {
			// No delimiter, add # delimiters with case-insensitive flag
			$pattern = '#' . $pattern . '#i';
		}
		
		// Suppress regex errors
		$result = @preg_match( $pattern, $path, $matches );
		
		// Return false if regex is invalid
		if ( $result === false ) {
			return false;
		}
		
		return $result === 1;
	}

	/**
	 * Forward query parameters
	 *
	 * @since 8.1.0
	 * @param string $redirect_url The redirect URL
	 * @return string URL with query parameters
	 */
	private function forward_query_params( $redirect_url ) {
		if ( empty( $_SERVER['QUERY_STRING'] ) ) {
			return $redirect_url;
		}
		
		$query_string = $_SERVER['QUERY_STRING'];
		
		// Check if redirect URL already has query parameters
		if ( strpos( $redirect_url, '?' ) !== false ) {
			$redirect_url .= '&' . $query_string;
		} else {
			$redirect_url .= '?' . $query_string;
		}
		
		return $redirect_url;
	}

	/**
	 * Add custom redirect headers
	 *
	 * @since 8.1.0
	 * @param int $redirect_id The redirect post ID
	 */
	private function add_redirect_headers( $redirect_id ) {
		header( 'X-ASENHA-Redirect-Manager: 1' );
		header( 'X-ASENHA-Redirect-ID: ' . absint( $redirect_id ) );
	}

	/**
	 * Check if a URL is external (different domain from the current site)
	 *
	 * @since 8.1.0
	 * @param string $url The URL to check
	 * @return bool True if external, false if internal
	 */
	private function is_external_url( $url ) {
		// If URL doesn't start with http:// or https://, it's a relative path (internal)
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			return false;
		}
		
		// Parse the URL and get the host
		$url_host = wp_parse_url( $url, PHP_URL_HOST );
		
		// If we can't parse the host, treat as internal for safety
		if ( empty( $url_host ) ) {
			return false;
		}
		
		// Get the current site's host
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		
		// Compare hosts (case-insensitive)
		return strtolower( $url_host ) !== strtolower( $site_host );
	}

	/**
	 * Perform redirect based on status code
	 *
	 * @since 8.1.0
	 * @param string $redirect_url The redirect URL
	 * @param int $status_code The HTTP status code
	 * @param string $message Custom message for error codes
	 */
	private function perform_redirect( $redirect_url, $status_code, $message = '' ) {
		// Define error codes that should show wp_die screen with message
		$error_codes_with_message = array( 400, 401, 403, 410, 500, 501, 503 );
		
		if ( in_array( $status_code, $error_codes_with_message ) ) {
			// Get title and default message for each error code
			$error_data = $this->get_error_data( $status_code );
			$title = $error_data['title'];
			
			// Use custom message if provided, otherwise use default
			if ( empty( $message ) ) {
				$message = $error_data['message'];
			}
			
			status_header( $status_code );
			wp_die( 
				wp_kses_post( $message ),
				esc_html( $title ),
				array( 'response' => $status_code )
			);
		} elseif ( $status_code === 404 ) {
			// Set 404 status and load 404 template
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			include( get_404_template() );
			exit;
		} else {
			// Standard redirect (3xx codes)
			// Use wp_redirect() for external URLs since redirect rules are admin-created
			// and URLs are sanitized with esc_url_raw() when saved
			if ( $this->is_external_url( $redirect_url ) ) {
				// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				wp_redirect( $redirect_url, $status_code );
			} else {
				wp_safe_redirect( $redirect_url, $status_code );
			}
			exit;
		}
	}

	/**
	 * Get error data for HTTP status codes
	 *
	 * @since 8.1.0
	 * @param int $status_code HTTP status code
	 * @return array Array with 'title' and 'message' keys
	 */
	private function get_error_data( $status_code ) {
		$error_data = array(
			400 => array(
				'title'   => __( 'Bad Request', 'admin-site-enhancements' ),
				'message' => __( 'The server cannot process the request due to a client error.', 'admin-site-enhancements' ),
			),
			401 => array(
				'title'   => __( 'Unauthorized', 'admin-site-enhancements' ),
				'message' => __( 'Authentication is required to access this resource.', 'admin-site-enhancements' ),
			),
			403 => array(
				'title'   => __( 'Forbidden', 'admin-site-enhancements' ),
				'message' => __( 'You do not have permission to access this page.', 'admin-site-enhancements' ),
			),
			410 => array(
				'title'   => __( 'Gone', 'admin-site-enhancements' ),
				'message' => __( 'The requested resource is no longer available.', 'admin-site-enhancements' ),
			),
			500 => array(
				'title'   => __( 'Internal Server Error', 'admin-site-enhancements' ),
				'message' => __( 'The server encountered an unexpected condition that prevented it from fulfilling the request.', 'admin-site-enhancements' ),
			),
			501 => array(
				'title'   => __( 'Not Implemented', 'admin-site-enhancements' ),
				'message' => __( 'The server does not support the functionality required to fulfill the request.', 'admin-site-enhancements' ),
			),
			503 => array(
				'title'   => __( 'Service Unavailable', 'admin-site-enhancements' ),
				'message' => __( 'The server is currently unable to handle the request. Please try again later.', 'admin-site-enhancements' ),
			),
		);
		
		// Return default if status code not found
		if ( isset( $error_data[ $status_code ] ) ) {
			return $error_data[ $status_code ];
		}
		
		return array(
			'title'   => __( 'Error', 'admin-site-enhancements' ),
			'message' => __( 'An error occurred while processing your request.', 'admin-site-enhancements' ),
		);
	}

	/**
	 * Bust cache on save
	 *
	 * @since 8.1.0
	 */
	public function bust_cache_on_save() {
		$this->cache->bust_cache();
	}

	/**
	 * Bust cache on delete
	 *
	 * @since 8.1.0
	 * @param int $post_id The post ID
	 */
	public function bust_cache_on_delete( $post_id ) {
		if ( get_post_type( $post_id ) === 'asenha_redirect' ) {
			$this->cache->bust_cache();
		}
	}
}

