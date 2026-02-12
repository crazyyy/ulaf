<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC analytics endpoints class.
 * 
 * This class contains the analytics endpoints.
 */
class ADBC_Analytics_Endpoints {

	private const MAX_CHART_DAYS = PHP_INT_MAX;

	/**
	 * Get database chart data by day.
	 * 
	 * @param WP_REST_Request $request Request.
	 * 
	 * @return WP_REST_Response
	 */
	public static function get_database_chart_data_by_day( WP_REST_Request $request ) {

		try {

			// Get date parameters
			$start_date = $request->get_param( 'start_date' );
			$end_date = $request->get_param( 'end_date' );

			// Sanitize and validate dates
			[ $start_date, $end_date ] = ADBC_Common_Validator::validate_strict_date_range( $start_date, $end_date, 'Y-m-d', self::MAX_CHART_DAYS );

			if ( empty( $start_date ) || empty( $end_date ) )
				return ADBC_Rest::error( sprintf( 'Dates must be valid format YYYY-MM-DD and cannot exceed %d days.', self::MAX_CHART_DAYS ), ADBC_Rest::BAD_REQUEST );

			// Get the chart data
			$chart_data = ADBC_Analytics::instance()->get_database_chart_data_by_day( $start_date, $end_date );

			return ADBC_Rest::success( '', $chart_data );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Get database chart data by month.
	 * 
	 * @param WP_REST_Request $request Request.
	 * 
	 * @return WP_REST_Response
	 */
	public static function get_database_chart_data_by_month( WP_REST_Request $request ) {

		try {

			// Get date parameters
			$start_date = $request->get_param( 'start_date' );
			$end_date = $request->get_param( 'end_date' );

			// Sanitize and validate dates
			[ $start_date, $end_date ] = ADBC_Common_Validator::validate_strict_date_range( $start_date, $end_date, 'Y-m' );

			if ( empty( $start_date ) || empty( $end_date ) )
				return ADBC_Rest::error( 'Dates must be in the format YYYY-MM and be valid calendar dates.', ADBC_Rest::BAD_REQUEST );

			// Get the chart data
			$chart_data = ADBC_Analytics::instance()->get_database_chart_data_by_month( $start_date, $end_date );

			return ADBC_Rest::success( '', $chart_data );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Get tables chart data by day.
	 * 
	 * @param WP_REST_Request $request Request.
	 * 
	 * @return WP_REST_Response
	 */
	public static function get_tables_chart_data_by_day( WP_REST_Request $request ) {

		try {

			$start_date = $request->get_param( 'start_date' );
			$end_date = $request->get_param( 'end_date' );
			$tables = $request->get_param( 'tables' );

			[ $start_date, $end_date ] = ADBC_Common_Validator::validate_strict_date_range( $start_date, $end_date, 'Y-m-d', self::MAX_CHART_DAYS );

			if ( empty( $start_date ) || empty( $end_date ) )
				return ADBC_Rest::error( sprintf( 'Dates must be valid format YYYY-MM-DD and cannot exceed %d days.', self::MAX_CHART_DAYS ), ADBC_Rest::BAD_REQUEST );

			$sanitized_tables = ADBC_Tables_Validator::validate_tables_names_list( $tables );

			// If tables was not empty but became empty after sanitization, return error
			if ( ! empty( $tables ) && empty( $sanitized_tables ) )
				return ADBC_Rest::error( 'Please provide a valid list of tables.', ADBC_Rest::UNPROCESSABLE_ENTITY );

			$chart_data = ADBC_Analytics::instance()->get_tables_chart_data_by_day( $start_date, $end_date, $sanitized_tables );

			return ADBC_Rest::success( '', $chart_data );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Get tables chart data by month.
	 * 
	 * @param WP_REST_Request $request Request.
	 * 
	 * @return WP_REST_Response
	 */
	public static function get_tables_chart_data_by_month( WP_REST_Request $request ) {

		try {

			$start_date = $request->get_param( 'start_date' );
			$end_date = $request->get_param( 'end_date' );
			$tables = $request->get_param( 'tables' );

			[ $start_date, $end_date ] = ADBC_Common_Validator::validate_strict_date_range( $start_date, $end_date, 'Y-m' );

			if ( empty( $start_date ) || empty( $end_date ) )
				return ADBC_Rest::error( 'Dates must be in the format YYYY-MM and be valid calendar dates.', ADBC_Rest::BAD_REQUEST );

			$sanitized_tables = ADBC_Tables_Validator::validate_tables_names_list( $tables );

			// If tables was not empty but became empty after sanitization, return error
			if ( ! empty( $tables ) && empty( $sanitized_tables ) )
				return ADBC_Rest::error( 'Please provide a valid list of tables.', ADBC_Rest::UNPROCESSABLE_ENTITY );

			$chart_data = ADBC_Analytics::instance()->get_tables_chart_data_by_month( $start_date, $end_date, $sanitized_tables );

			return ADBC_Rest::success( '', $chart_data );


		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Get last week database size.
	 * 
	 * @return WP_REST_Response
	 */
	public static function get_last_week_database_size() {

		try {

			$chart_data = ADBC_Analytics::instance()->get_last_week_database_size();

			return ADBC_Rest::success( '', $chart_data );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

}