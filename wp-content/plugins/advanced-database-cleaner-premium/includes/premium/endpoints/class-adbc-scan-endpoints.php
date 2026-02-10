<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC scan endpoints class.
 * 
 * This class contains the scan endpoints.
 */
class ADBC_Scan_Endpoints {

	// Heartbeat response codes
	private const ADBC_INVALID_INPUT = 1;
	private const ADBC_SCAN_NOT_FOUND = 2;
	private const ADBC_SCAN_COMPLETED = 3;
	private const ADBC_MAX_REMOTE_RETRIES_REACHED = 4;
	private const ADBC_SHOULD_CHECK_REMOTE_STATUS = 6;
	private const ADBC_SCAN_FOUND = 7;
	private const ADBC_HEARTBEAT_EXCEPTION = 8;
	private const ADBC_SCAN_STOPPED = 9;
	private const ADBC_SCAN_TIMEOUT = 10;
	private const ADBC_REMOTE_REQUEST_FAILED = 11;
	private const ADBC_SCAN_MEMORY = 12;
	private const ADBC_SCAN_EXCEPTION = 13;
	private const ADBC_SCAN_FORCED_TIMEOUT = 14;

	/**
	 * This endpoint starts or continue a scan.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function start_scan( WP_REST_Request $data ) {

		try {

			$scan_params = ADBC_Scan_Validator::sanitize_scan_params( $data );

			// Check if at least the items type is valid
			if ( $scan_params['items_type'] === "" ) {
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );
			}

			// If we are starting a new scan we should check also the other required parameters
			if ( $scan_params['continue_scan'] === false && ( $scan_params['scan_type'] === "" || $scan_params['preg_match'] === "" || $scan_params['partial_match'] === "" || $scan_params['what_to_scan'] === "" ) ) {
				return ADBC_Rest::error( 'Invalid or empty scan parameters.', ADBC_Rest::BAD_REQUEST );
			}

			// check if the user has sent the selected items to scan for the selected scan type
			if ( $scan_params['what_to_scan'] === ADBC_Scan::SELECTED && empty( $scan_params['selected_items_to_scan'] ) ) {
				return ADBC_Rest::error( 'No valid items selected to scan.', ADBC_Rest::UNPROCESSABLE_ENTITY );
			}

			// Check if there is a scan and still running for the same items_type
			$scan_info = get_option( "adbc_plugin_scan_info_" . $scan_params['items_type'] );
			if ( is_array( $scan_info ) && $scan_params['continue_scan'] === false ) {
				return ADBC_Rest::error( 'There is already an active scan.', ADBC_Rest::UNPROCESSABLE_ENTITY );
			}

			// Start a new scan
			ADBC_Scan::instance(
				$scan_params['items_type'],
				$scan_params['scan_type'],
				$scan_params['preg_match'],
				$scan_params['partial_match'],
				$scan_params['what_to_scan'],
				$scan_params['selected_items_to_scan'],
				$scan_params['override_manual_categorization'],
				$scan_params['continue_scan']
			)->run();

			return ADBC_Rest::success( '' );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint requests to stop a scan.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function stop_scan( WP_REST_Request $data ) {

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" ) {
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );
			}

			$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );
			if ( ! $scan_info ) {
				return ADBC_Rest::error( 'No active scan found.', ADBC_Rest::NOT_FOUND );
			}

			$should_stop_data = json_decode( get_option( "adbc_plugin_should_stop_scan_{$items_type}" ), true );
			if ( is_array( $should_stop_data ) && $should_stop_data['should_stop_flag'] === "should_stop" ) {
				return ADBC_Rest::error( 'Scan is already requested to stop.', ADBC_Rest::UNPROCESSABLE_ENTITY );
			}

			if ( is_array( $should_stop_data ) && $should_stop_data['should_stop_flag'] === "stopped" ) {
				return ADBC_Rest::error( 'Scan is already stopped.', ADBC_Rest::UNPROCESSABLE_ENTITY );
			}

			$should_stop_data = [ 
				"should_stop_flag" => "should_stop",
				"requested_at" => time()
			];

			update_option( "adbc_plugin_should_stop_scan_{$items_type}", json_encode( $should_stop_data ), false );

			return ADBC_Rest::success( '' );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint checks the current scan status.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function scan_heartbeat( WP_REST_Request $data ) {

		$scan_info = null;

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" ) {
				return ADBC_Rest::heartbeat( 'Invalid or empty items type.', self::ADBC_INVALID_INPUT );
			}

			$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );

			// check if there is an active scan
			if ( ! $scan_info ) {
				return ADBC_Rest::heartbeat( 'No active scan found.', self::ADBC_SCAN_NOT_FOUND );
			}

			// check if the scan is done, then delete the active scan info
			if ( ADBC_Scan::instance( $items_type )->is_done() ) {
				ADBC_Scan::instance( $items_type )->delete_current_scan();
				return ADBC_Rest::heartbeat( 'Scan completed.', self::ADBC_SCAN_COMPLETED, $scan_info );
			}

			// check if there was a stopped scan
			if ( ADBC_Scan::instance( $items_type )->was_stopped() ) {
				ADBC_Scan::instance( $items_type )->delete_current_scan();
				return ADBC_Rest::heartbeat( 'The scan has been stopped.', self::ADBC_SCAN_STOPPED, $scan_info );
			}

			// check if the scan was stopped because of an exception
			if ( ADBC_Scan::instance( $items_type )->is_scan_other_error() ) {
				ADBC_Scan::instance( $items_type )->delete_current_scan();
				return ADBC_Rest::heartbeat( 'Scan stopped due to an exception. Check the logs for more details.', self::ADBC_SCAN_EXCEPTION, $scan_info );
			}

			// check if the scan was stopped because of memory limit
			if ( ADBC_Scan::instance( $items_type )->is_scan_memory_exceeded() ) {
				return ADBC_Rest::heartbeat( 'Scan stopped due to memory limit.', self::ADBC_SCAN_MEMORY, $scan_info );
			}

			// check if the scan was shutdown due to timeout
			if ( ADBC_Scan::instance( $items_type )->is_scan_timeout() ) {
				return ADBC_Rest::heartbeat( 'The scan will resume automatically...', self::ADBC_SCAN_TIMEOUT, $scan_info );
			}

			// check if the scan was shutdown due to forced timeout
			if ( ADBC_Scan::instance( $items_type )->is_scan_forced_timeout() ) {
				return ADBC_Rest::heartbeat( 'The scan will resume automatically...', self::ADBC_SCAN_FORCED_TIMEOUT, $scan_info );
			}

			// check if the max remote retries was reached
			if ( ADBC_Scan::instance( $items_type )->is_max_remote_request_retries_reached() ) {
				return ADBC_Rest::heartbeat( 'Max remote retries reached.', self::ADBC_MAX_REMOTE_RETRIES_REACHED, $scan_info );
			}

			// check if the scan should be continued from a failed remote request
			if ( ADBC_Scan::instance( $items_type )->should_continue_from_failed_remote_request() ) {
				return ADBC_Rest::heartbeat( 'Remote request failed.', self::ADBC_REMOTE_REQUEST_FAILED, $scan_info );
			}

			// check if we need to check the remote scan status or if we need to get the remote scan results
			if ( ADBC_Scan::instance( $items_type )->should_check_remote_scan_status() ) {
				return ADBC_Rest::heartbeat( 'Should check remote scan status.', self::ADBC_SHOULD_CHECK_REMOTE_STATUS, $scan_info );
			}

			// return the scan info
			return ADBC_Rest::heartbeat( '', self::ADBC_SCAN_FOUND, $scan_info );

		} catch (Throwable $e) {
			ADBC_Logging::log_exception( __METHOD__, $e );
			return ADBC_Rest::heartbeat( $e->getMessage(), self::ADBC_HEARTBEAT_EXCEPTION, $scan_info );
		}

	}

	/**
	 * This endpoint checks the remote scan status.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function check_remote_scan_status( WP_REST_Request $data ) {

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" ) {
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );
			}

			$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );
			if ( ! $scan_info ) {
				return ADBC_Rest::error( 'No active scan found.', ADBC_Rest::NOT_FOUND );
			}

			$remote_status = ADBC_Remote_Scan::instance( $items_type )->check_status();

			if ( $remote_status['success'] === false ) {
				ADBC_Logging::log_exception( __METHOD__, new exception( $remote_status['message'] ) );
				return ADBC_Rest::error( $remote_status['message'], $remote_status['status_code'], $remote_status['failure_code'] );
			}

			return ADBC_Rest::success( '', array_merge( ADBC_Remote_Scan::instance( $items_type )->get_scan_info(), [ "remote_answer" => $remote_status ] ) );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint gets the remote scan results.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function get_remote_scan_results( WP_REST_Request $data ) {

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" ) {
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );
			}

			$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );
			if ( ! $scan_info ) {
				return ADBC_Rest::error( 'No active scan found.', ADBC_Rest::NOT_FOUND );
			}

			$response = ADBC_Remote_Scan::instance( $items_type )->get_results();

			if ( $response['success'] === false ) {
				ADBC_Logging::log_exception( __METHOD__, new exception( $response['message'] ) );
				return ADBC_Rest::error( $response['message'], $response['status_code'], $response['failure_code'] );
			}

			return ADBC_Rest::success( '' );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint resets the remote request retries.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function remote_request_retries_reset( WP_REST_Request $data ) {

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" ) {
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );
			}

			$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );
			if ( ! $scan_info ) {
				return ADBC_Rest::error( 'No active scan found.', ADBC_Rest::NOT_FOUND );
			}

			$scan_info['remote']['retry_count'] = 0;
			update_option( "adbc_plugin_scan_info_{$items_type}", $scan_info, false );

			return ADBC_Rest::success( '' );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint gets the scan requests balance from the remote server.
	 * 
	 * @return WP_REST_Response The response.
	 */
	public static function get_remote_scan_balance() {

		try {

			$response = ADBC_Scan_Utils::get_scan_balance();

			if ( $response['success'] === false ) {
				ADBC_Logging::log_exception( __METHOD__, new exception( $response['message'] ) );
				return ADBC_Rest::error( $response['message'], $response['status_code'], $response['failure_code'] );
			}

			return ADBC_Rest::success( '', $response['balance'] );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * This endpoint checks if a scan exists for the given items type.
	 * 
	 * @param WP_REST_Request $data The request data.
	 * 
	 * @return WP_REST_Response The response with the scan status.
	 */
	public static function is_scan_exists( WP_REST_Request $data ) {

		try {

			$items_type = ADBC_Common_Validator::sanitize_items_type( $data->get_param( "items_type" ) );

			if ( $items_type === "" )
				return ADBC_Rest::error( 'Invalid or empty items type.', ADBC_Rest::BAD_REQUEST );

			$scan_exists = ADBC_Scan_Utils::is_scan_exists( $items_type );

			return ADBC_Rest::success( '', $scan_exists );

		} catch (Throwable $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

}
