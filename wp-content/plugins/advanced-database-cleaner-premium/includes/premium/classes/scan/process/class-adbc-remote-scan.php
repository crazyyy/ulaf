<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC remote scan class.
 * 
 * This class is used to request a remote scan from the API server, check its status and get the results.
 */
class ADBC_Remote_Scan extends ADBC_Scan {

	private $remote_results_file_path = '';

	private $temp_remote_results_file_path = '';

	// Remote API failure codes
	public const GENERIC_ERROR = 4;
	private const INVALID_LICENSE = 1;
	private const NOT_ENOUGH_BALANCE = 2;
	private const TOO_MANY_ITEMS = 3;

	// Remote requests timeouts
	public const GET_SCAN_BALANCE_TIMEOUT = 30;
	private const GET_RESULTS_TIMEOUT = 30;
	private const REQUEST_SCAN_TIMEOUT = 30;
	private const CHECK_STATUS_TIMEOUT = 30;

	protected function __construct( $items_type = '' ) {

		parent::__construct( $items_type );

		$this->remote_results_file_path = ADBC_Scan_Paths::get_remote_scan_results_path( $this->scan_info_instance->items_type );
		$this->temp_remote_results_file_path = ADBC_Scan_Paths::get_remote_temp_scan_results_path( $this->scan_info_instance->items_type );

	}

	/**
	 * Run the remote scan process.
	 */
	public function run() {

		try {

			// check if the scan is already done or already requested
			if ( $this->is_remote_scan_successfully_requested() ) {
				$this->mark_scan_as_finished_for_shutdown();
				return;
			}

			// Reset the failed remote request before requesting another one to avoid multiple requests issue
			if ( $this->is_remote_request_failed() )
				$this->reset_remote_request_status();

			// always update the retry count
			$this->increment_remote_request_retries();

			// send the request to the API server
			$request_info = $this->request_scan();

			// update the scan info with the request info
			if ( $request_info['success'] === true ) {

				$this->update_scan_info_on_request_success( $request_info );
				self::update_balance( $request_info['balance'] );

			} else {

				// if the server didn't answer our request or didn't send a message code, we set a generic code
				if ( ! isset( $request_info['failure_code'] ) )
					$request_info['failure_code'] = self::GENERIC_ERROR;

				$this->update_scan_info_on_request_failure( $request_info['failure_code'] );

				// update the balance if the failure code is NOT_ENOUGH_BALANCE
				if ( $request_info['failure_code'] === self::NOT_ENOUGH_BALANCE )
					self::update_balance( $request_info['balance'] );

				// Log the error message
				ADBC_Logging::log_exception( __METHOD__, new Exception( $request_info['message'] ) );

			}

		} catch (Throwable $e) {

			$this->update_scan_info_on_request_failure( self::GENERIC_ERROR );
			throw $e;

		} finally {

			// always mark the scan as finished to avoid being continued by the heartbeat automatically by the shutdown codes
			// in case of request failure the heartbeat will handle the continuation through the REMOTE_REQUEST_FAILED code
			$this->mark_scan_as_finished_for_shutdown();
			$this->update_scan_info();

		}

	}

	/**
	 * Check the status of the remote scan.
	 * 
	 * @return array The server response.
	 */
	public function check_status() {

		$request_id = $this->scan_info_instance->scan_info["remote"]["request_id"];
		$request_route = "/scan/request/{$request_id}/status";

		$server_response = ADBC_Remote_Request::send_request( $request_route, [], 'GET', true, self::CHECK_STATUS_TIMEOUT );

		// if the server didn't answer our request or an error occurred in wordpress, we set a generic code
		if ( $server_response['success'] === false && ! isset( $server_response['failure_code'] ) )
			$server_response['failure_code'] = self::GENERIC_ERROR;

		return $server_response;

	}

	/**
	 * Get the results of the remote scan.
	 * Save the results to the local scan results file and merge the results with the local scan results.
	 */
	public function get_results() {

		$request_id = $this->scan_info_instance->scan_info['remote']['request_id'];
		$request_route = "/scan/request/{$request_id}/results";

		$server_response = ADBC_Remote_Request::send_request( $request_route, [], 'GET', true, self::GET_RESULTS_TIMEOUT );

		if ( $server_response['success'] === false ) {

			if ( ! isset( $server_response['failure_code'] ) )
				$server_response['failure_code'] = self::GENERIC_ERROR;

			return $server_response;

		}

		$this->save_remote_results_file( $server_response['corrected_items'] );

		// Count the number of lines in the corrected_items string
		// If string is empty, count is 0, otherwise count all newlines and add 1
		if ( empty( $server_response['corrected_items'] ) ) {
			$corrected_items_count = 0;
		} else {
			$corrected_items_count = substr_count( $server_response['corrected_items'], "\n" ) + ( substr( $server_response['corrected_items'], -1 ) === "\n" ? 0 : 1 );
		}

		$this->scan_info_instance->scan_info['remote']['corrected_items'] = $corrected_items_count;

		unset( $server_response['corrected_items'] ); // remove the corrected_items from the memory

		$this->merge_remote_scan_results();

		$this->merge_slug_name_dictionary( $server_response['slug_name_dict'] );

		$this->mark_full_scan_as_done();

		// remove the scan temp files
		$this->delete_scan_temporary_files();

		// Acknowledge the API server that we got the results successfully by a non blocking request
		$this->acknowledge_results_receipt();

		return [ 
			'success' => true
		];

	}

	/**
	 * Acknowledge the receipt of the results to the API server.
	 */
	private function acknowledge_results_receipt() {

		$request_id = $this->scan_info_instance->scan_info["remote"]["request_id"];

		$request_route = "/scan/request/{$request_id}/acknowledgement";

		ADBC_Remote_Request::send_request( $request_route, [], "POST", false );

	}

	/**
	 * Update the scan info if the request was successful.
	 */
	private function update_scan_info_on_request_success( $request_info ) {

		// update the scan info with the request info if the request was successful
		$this->scan_info_instance->scan_info['remote']['request_id'] = $request_info['request_id'];
		$this->scan_info_instance->scan_info['remote']['nb_total_items'] = $request_info['nb_total_items'];
		$this->scan_info_instance->scan_info['remote']['successful_request_time'] = time();
		$this->scan_info_instance->scan_info['remote']['status'] = self::REQUESTED;
		$this->scan_info_instance->scan_info['step'] = self::GETTING_REMOTE_SCAN_RESULTS;

	}

	/**
	 * Update the scan info if the request failed.
	 */
	private function update_scan_info_on_request_failure( $failure_code ) {

		// update the last_failed_request_time and the status if the request failed
		$this->scan_info_instance->scan_info['remote']['last_failed_request_time'] = time();
		$this->scan_info_instance->scan_info['remote']['status'] = self::FAILED;
		$this->scan_info_instance->scan_info['remote']['failure_code'] = $failure_code;

	}

	/**
	 * Increase the remote request retries count.
	 */
	private function increment_remote_request_retries() {
		$this->scan_info_instance->scan_info['remote']['retry_count']++;
	}

	/**
	 * Request a new remote scan from the API server by sending the current scan results for more accurate results.
	 * 
	 * @return array The server response.
	 */
	private function request_scan() {

		$preparation_results = $this->prepare_items_for_remote_scan();

		$this->scan_info_instance->scan_info['remote']['trimmed_count'] = $preparation_results['trimmed_count'];

		// Prepare the data to be sent to the API server
		$json_data = array(
			'website' => ADBC_WEBSITE_HOME_URL,
			'items_type' => $this->scan_info_instance->items_type,
			'wp_prefix_flag' => $this->is_prefix_wp(),
			'installed_addons' => $this->get_addons(),
			'prepared_items' => $preparation_results['items_trimmed'],
		);

		// Send the prepared data to the API server and get the results
		$request_route = '/scan/request';
		$server_response = ADBC_Remote_Request::send_request( $request_route, $json_data, "POST", true, self::REQUEST_SCAN_TIMEOUT );

		return $server_response;

	}

	/**
	 * Mark the full scan as done.
	 */
	private function mark_full_scan_as_done() {

		$this->scan_info_instance->scan_info['status'] = self::DONE;
		$this->scan_info_instance->scan_info['remote']['status'] = self::DONE;
		$this->update_scan_info();

	}

	/**
	 * Prepare the items to be sent to the API server from the local scan results file.
	 * If the file size is bigger than the post_max_size, we trim it to fit the post_max_size.
	 * 
	 * @return array The trimmed items and the count of the trimmed items.
	 */
	private function prepare_items_for_remote_scan() {

		// Check if the file is readable
		if ( ! ADBC_Files::instance()->is_readable( $this->local_temp_results_file_path ) )
			throw new Exception( "Unable to open the file." );

		// Calculate the final post_max_size after removing the installed addons size and some extra bytes
		$post_max_size = ini_get( 'post_max_size' );
		$size_installed_addons = strlen( json_encode( $this->get_addons() ) );
		$final_post_max_size = ADBC_Common_Utils::convert_post_max_size_to_bytes( $post_max_size ) - $size_installed_addons - 1000;

		// Get the file size
		$file_size = ADBC_Files::instance()->size( $this->local_temp_results_file_path );

		// If the file size is less than or equal to the final post_max_size, we don't need to trim it
		if ( $file_size <= $final_post_max_size ) {

			$file_contents = ADBC_Files::instance()->get_contents( $this->local_temp_results_file_path );

			if ( $file_contents === false )
				throw new Exception( "Unable to open the file." );

			return [ 
				'items_trimmed' => $file_contents,
				'trimmed_count' => 0
			];

		}

		// if we are here it means that the file size is bigger than the post_max_size, so we need to trim it
		$handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path );

		if ( $handle === false )
			throw new Exception( "Unable to open the file." );

		[ $items_trimmed, $trimmed_count ] = $this->trim_scan_results_file_to_size( $handle, $final_post_max_size );

		fclose( $handle );

		return [ 
			'items_trimmed' => $items_trimmed,
			'trimmed_count' => $trimmed_count
		];

	}

	/**
	 * Trim the scan results file to fit the post_max_size.
	 * 
	 * @param array Trimmed items and the count of the trimmed items.
	 */
	private function trim_scan_results_file_to_size( $handle, $size ) {

		// If the file need to be trimmed we read it line by line and trim it to the max size
		$accumulated_size = 0;
		$items_trimmed = [];
		$total_lines = 0;

		while ( ( $line = fgets( $handle ) ) !== false ) {

			$total_lines++;
			$line_size = strlen( $line );

			if ( ( $accumulated_size + $line_size ) > $size )
				continue;

			$accumulated_size += $line_size;
			$items_trimmed[] = rtrim( $line, "\r\n" );

		}

		$trimmed_count = $total_lines - count( $items_trimmed );

		return [ 
			implode( "\n", $items_trimmed ),
			$trimmed_count
		];

	}

	/**
	 * Save the remote scan results to the remote results file.
	 */
	private function save_remote_results_file( $result ) {

		$success = ADBC_Files::instance()->put_contents( $this->remote_results_file_path, $result );

		if ( $success === false )
			throw new Exception( "Unable to open the file." );

	}

	/**
	 * Merge the remote scan results with the local scan results.
	 */
	private function merge_remote_scan_results() {

		// open all the files
		$scan_results_file = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'r' );
		$remote_results_file = ADBC_Files::instance()->get_file_handle( $this->remote_results_file_path, 'r' );
		$temp_remote_results_file = ADBC_Files::instance()->get_file_handle( $this->temp_remote_results_file_path, 'w' );

		if ( $scan_results_file === false || $remote_results_file === false || $temp_remote_results_file === false )
			throw new Exception( "Unable to open the file." );

		do {

			$local_result_batch_lines = [];

			// Read the local results file line by line until we reach the batch size
			while ( ( $line = fgets( $scan_results_file ) ) !== false ) {

				list( $item_name, $belong_to_json ) = ADBC_Scan_Utils::split_result_file_line( $line );

				if ( $item_name === false || $belong_to_json === false )
					continue;

				$local_result_batch_lines[ $item_name ] = $belong_to_json;

				if ( count( $local_result_batch_lines ) === $this->scan_info_instance->scan_info['batch_size'] )
					break;

			}

			// If the local results file is empty, break the loop
			if ( count( $local_result_batch_lines ) === 0 )
				break;

			// Rewind the remote results file to read from the beginning
			rewind( $remote_results_file );

			// Read remote results line by line and update the local results if necessary
			while ( ( $line = fgets( $remote_results_file ) ) !== false ) {

				list( $item_name, $belong_to_json ) = ADBC_Scan_Utils::split_result_file_line( $line );

				if ( $item_name === false || $belong_to_json === false )
					continue;

				if ( array_key_exists( $item_name, $local_result_batch_lines ) && ! array_key_exists( "m", $local_result_batch_lines[ $item_name ] ) ) {
					$local_result_batch_lines[ $item_name ]['r'] = $belong_to_json['r'];
				}

			}

			// Write the updated results back to the temp results file
			foreach ( $local_result_batch_lines as $item_name => $belong_to_json ) {

				if ( ! isset( $belong_to_json['r'] ) )
					$belong_to_json['r'] = [];

				$updated_line = $item_name . "|" . json_encode( $belong_to_json );

				fwrite( $temp_remote_results_file, $updated_line . "\n" );

			}

		} while ( count( $local_result_batch_lines ) > 0 );

		// Close all the files
		fclose( $scan_results_file );
		fclose( $remote_results_file );
		fclose( $temp_remote_results_file );

		// Replace the local results file with the temp results file
		if ( ADBC_Files::instance()->exists( $this->temp_remote_results_file_path ) && ! rename( $this->temp_remote_results_file_path, $this->scan_results_file_path ) )
			throw new Exception( "Unable to rename the file." );

	}

	/**
	 * Update the user's API server requests balance in the database.
	 * 
	 * @param array $balance The balance data to be inserted into the database.
	 * @return array The updated balance data.
	 */
	public static function update_balance( $balance ) {

		// Check if the balance is not array or empty
		if ( ! is_array( $balance ) || empty( $balance ) )
			return [];

		$balance['updated_at'] = time();

		ADBC_Settings::instance()->update_settings( [ 'api_scan_balance' => $balance ] );

		return $balance;

	}

	/**
	 * Check if the current site prefix is "wp_".
	 * 
	 * @return bool True if the prefix is "wp_", false otherwise.
	 */
	private function is_prefix_wp() {

		global $wpdb;
		return $wpdb->prefix === 'wp_';

	}

	/**
	 * Get the list of all known installed addons by getting the current installed and the monitored ones.
	 * 
	 * @return array The list of all known installed addons typed slugs.
	 */
	public function get_addons() {

		// Installed addons: [ 'p:slug' => 'Name', 't:slug' => 'Name', ... ] => want list of slugs.
		$installed_addons = array_keys( ADBC_Addons::get_all_installed_addons() );

		// Logged addons currently returned as: [ 'p:slug' => '', 't:slug' => '', ... ] => want list of slugs.
		$all_logged_addons = array_keys( ADBC_Addons_Activity::get_all_logged_addons_typed_slugs() );

		// Merge while avoiding duplicates, ensure numeric indexes (JSON array, not object).
		$all_addons = array_values(
			array_unique(
				array_merge( $installed_addons, $all_logged_addons )
			)
		);

		return $all_addons;

	}

	/**
	 * Update the slug name dictionary file by the received dictionary from the API server.
	 * 
	 * @param string $server_slug_name_dict The server slug name dictionary.
	 */
	public function merge_slug_name_dictionary( $server_slug_name_dict ) {

		$handle = ADBC_Files::instance()->get_file_handle( $this->dict_file_path, "a" );

		if ( $handle === false )
			throw new Exception( "Unable to open the file." );

		fwrite( $handle, $server_slug_name_dict );
		fclose( $handle );

		$this->update_slug_name_dictionary();

	}

}
