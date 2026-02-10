<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Addons Endpoints.
 * 
 * This class provides the endpoints (controllers) for the addons routes.
 */
class ADBC_Addons_Endpoints {

	/**
	 * Get the list of plugins/themes.
	 *
	 * @return WP_REST_Response The list of plugins/themes.
	 */
	public static function get_addons_list() {

		try {

			return ADBC_Rest::success( "", [ 
				'plugins_list' => ADBC_Plugins::instance()->get_plugins_info(),
				'themes_list' => ADBC_Themes::instance()->get_themes_info()
			] );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}
	}

	/**
	 * Get addons activity timeline with pagination and filtering.
	 *
	 * @param WP_REST_Request $request The request object.
	 * 
	 * @return WP_REST_Response The response containing addons activity timeline.
	 */
	public static function get_addons_activity_timeline( WP_REST_Request $request ) {

		try {

			// Get and sanitize parameters
			$search = $request->get_param( 'search' ) === null ? '' : $request->get_param( 'search' );
			$activity_type = ADBC_Premium_Common_Validator::sanitize_validate_activity_type( $request->get_param( 'activity_type' ) );
			$offset = ADBC_Common_Validator::sanitize_validate_offset( $request->get_param( 'offset' ) );
			$limit = ADBC_Common_Validator::sanitize_validate_limit( $request->get_param( 'limit' ) );

			// Get activity timeline
			$timeline_data = ADBC_Addons_Activity::get_activity_timeline( $search, $activity_type, $offset, $limit );

			return ADBC_Rest::success( "", $timeline_data );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Clears all addons activities
	 *
	 * @return WP_REST_Response The response containing addons activity timeline.
	 */
	public static function clear_addons_activity() {

		try {

			ADBC_Addons_Activity::clear_all_activities();

			return ADBC_Rest::success( "" );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

}