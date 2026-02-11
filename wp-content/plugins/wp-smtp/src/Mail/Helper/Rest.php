<?php

/**
 * Helper class for REST API pagination.
 *
 * This class provides a method to add pagination headers and links to
 * REST API responses, facilitating navigation through paginated data.
 *
 * @package SolidWP\Mail\Helper
 */

namespace SolidWP\Mail\Helper;

/**
 * Class Rest
 *
 * Provides helper methods for REST API.
 */
class Rest {
	/**
	 * Adds pagination to a REST API response.
	 *
	 * This method adds pagination headers to the REST API response, including
	 * `X-WP-Total` for the total number of items and `X-WP-TotalPages` for the
	 * total number of pages. It also includes `prev` and `next` link headers
	 * if applicable.
	 *
	 * @param \WP_REST_Request  $request  The REST API request object.
	 * @param \WP_REST_Response $response The REST API response object.
	 * @param int               $count    The total number of items.
	 * @param string            $path     The base path for the REST API endpoints.
	 *
	 * @return void
	 */
	public static function paginate( \WP_REST_Request $request, \WP_REST_Response $response, int $count, string $path ) {
		$max_pages = ceil( $count / $request['per_page'] );
		$response->header( 'X-WP-Total', $count );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg(
			map_deep(
				$request_params,
				function ( $value ) {
					if ( is_bool( $value ) ) {
						$value = $value ? 'true' : 'false';
					}

					return rawurlencode( $value );
				}
			),
			rest_url( $path )
		);

		if ( $request['page'] > 1 ) {
			$prev_page = $request['page'] - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $request['page'] ) {
			$next_page = $request['page'] + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}
	}
}
