<?php
/**
 * Redirect Manager Cache Management
 *
 * Handles caching of redirects using Transients API
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing redirect cache
 */
class ASENHA_Redirect_Manager_Cache {

	/**
	 * Cache key for storing redirects
	 *
	 * @var string
	 */
	const CACHE_KEY = 'asenha_redirect_cache';

	/**
	 * Cache expiration time (30 days in seconds)
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = 30 * DAY_IN_SECONDS;

	/**
	 * Get redirects from cache
	 *
	 * @since 8.1.0
	 * @return array|false Array of redirects or false if not cached
	 */
	public function get_cache() {
		return get_transient( self::CACHE_KEY );
	}

	/**
	 * Set redirects cache
	 *
	 * @since 8.1.0
	 * @param array $redirects Array of redirect data
	 * @return bool True on success, false on failure
	 */
	public function set_cache( $redirects ) {
		return set_transient( self::CACHE_KEY, $redirects, self::CACHE_EXPIRATION );
	}

	/**
	 * Bust (delete) the redirects cache
	 *
	 * @since 8.1.0
	 * @return bool True on success, false on failure
	 */
	public function bust_cache() {
		return delete_transient( self::CACHE_KEY );
	}

	/**
	 * Build cache from database
	 *
	 * Queries redirect posts in batches of 100 to prevent memory issues
	 *
	 * @since 8.1.0
	 * @return array Array of redirect data
	 */
	public function build_cache() {
		$redirects = array();
		$batch_size = 100;
		$paged = 1;

		do {
			$args = array(
				'post_type'      => 'asenha_redirect',
				'post_status'    => 'publish',
				'posts_per_page' => $batch_size,
				'paged'          => $paged,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
					$redirect_from = get_post_meta( $post_id, '_redirect_from', true );
					$redirect_to = get_post_meta( $post_id, '_redirect_to', true );
					$status_code = (int) get_post_meta( $post_id, '_redirect_http_status_code', true ) ?: 302;
					
					// Error status codes don't require a redirect_to value
					$error_codes = array( 400, 401, 403, 404, 410, 500, 501, 503 );
					
					// Cache redirects with "Redirect From" value and either:
					// 1. A "Redirect To" value (for redirect status codes), OR
					// 2. An error status code (which don't need a "Redirect To" value)
					if ( ! empty( $redirect_from ) && ( ! empty( $redirect_to ) || in_array( $status_code, $error_codes, true ) ) ) {
						$redirects[ $post_id ] = array(
							'from'        => $redirect_from,
							'regex'       => (bool) get_post_meta( $post_id, '_redirect_from_regex', true ),
							'status_code' => $status_code,
							'to'          => $redirect_to,
							'strip_query_params' => (bool) get_post_meta( $post_id, '_redirect_strip_query_params', true ),
							'group'       => get_post_meta( $post_id, '_redirect_group', true ),
							'message'     => get_post_meta( $post_id, '_redirect_message', true ),
						);
					}
				}
			}

			$paged++;
		} while ( $query->have_posts() );

		wp_reset_postdata();

		// Store in cache
		$this->set_cache( $redirects );

		return $redirects;
	}
}

