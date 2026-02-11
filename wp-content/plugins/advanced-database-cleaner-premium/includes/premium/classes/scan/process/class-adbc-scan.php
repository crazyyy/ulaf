<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * The scan class.
 *
 * This class is responsible for running the whole scan process that includes the local and remote scans type based on multiple parameters got from the user.
 * In the end it will save the categorization results in the scan results file.
 * It also handles the scan shutdown and the scan continuation after a shutdown.
 * Only one scan per items_type can run at a time.
 */
class ADBC_Scan extends ADBC_Singleton {

	public const ADBC_MAX_REMOTE_RETRIES = 3; // remote request tries after which we display the max retries reached

	// The "what to scan" values
	public const ALL = "all";
	public const UNCATEGORIZED = "uncategorized";
	public const SELECTED = "selected"; // public because it is used in the scan endpoints

	// Scan statuses
	protected const LOCAL_SCAN = "local";
	protected const FULL_SCAN = "full";
	protected const DONE = "done";
	protected const RUNNING = "running";
	protected const SHUTDOWN = "shutdown";
	protected const INACTIVE = "inactive";
	protected const REQUESTED = "requested";
	protected const FAILED = "failed";

	// The scan steps
	protected const PREPARE_ITEMS_TO_SCAN = "1_prepare_items_to_scan";
	protected const COLLECT_PHP_FILES = "2_collect_php_files";
	protected const PREG_MATCH_SCAN = "3_preg_match_scan";
	protected const EXACT_MATCH_SCAN = "4_exact_match_scan";
	protected const PARTIAL_MATCH_SCAN = "5_partial_match_scan";
	protected const PREPARE_LOCAL_SCAN_RESULTS = "6_prepare_local_scan_results";
	protected const REQUESTING_REMOTE_SCAN = "7_requesting_remote_scan";
	protected const GETTING_REMOTE_SCAN_RESULTS = "8_getting_remote_scan_results";

	// The scan steps numbers mapping array
	protected const STEPS_NUMBERS = array(
		self::PREPARE_ITEMS_TO_SCAN => 1,
		self::COLLECT_PHP_FILES => 2,
		self::PREG_MATCH_SCAN => 3,
		self::EXACT_MATCH_SCAN => 4,
		self::PARTIAL_MATCH_SCAN => 5,
		self::PREPARE_LOCAL_SCAN_RESULTS => 6,
		self::REQUESTING_REMOTE_SCAN => 7,
		self::GETTING_REMOTE_SCAN_RESULTS => 8
	);

	protected const ADBC_ABNORMAL_FILE_SIZE = 60 * 1024 * 1024; // size of files to skip in files collections

	private const ADBC_STOP_SCAN_REQUEST_EXPIRATION = 10; // duration in seconds starting from the stop scan user's request after which we force stopping the scan 

	protected $scan_info_instance = null; // instance of the scan info class holding the scan info

	// Scan files paths
	protected $scan_results_file_path = '';
	protected $partial_match_results_file_path = '';
	protected $local_temp_results_file_path = '';
	protected $dict_file_path = '';
	protected $temp_dict_file_path = '';
	protected $slug_list_temp_file_path = '';

	// Scan settings
	protected $database_rows_batch_size = 0;
	protected $file_content_chunk_size = 0;

	// Timeout management attributes
	protected static $safe_timeout_limit = 60; // safe timeout limit to avoid timeout
	protected static $current_script_start_time = 0; // start time of the current script
	protected static $current_script_max_execution_time = 0; // max execution time of the current script
	protected static $exit_time = 0; // time to exit the current script to avoid timeout

	// CPU usage control attributes
	protected $reduce_cpu_usage = false; // whether to reduce CPU usage
	protected $cpu_work_time_ms = 50; // how long to work before a short pause
	protected $cpu_rest_time_ms = 10; // how long to pause to give CPU a break
	protected $cpu_last_tick = 0.0; // last time a work window started

	protected function __construct( $items_type = "", $scan_type = "", $preg_match = true, $partial_match = true, $what_to_scan = "", $selected_items_to_scan = array(), $override_manual_categorization = false, $continue_scan = null ) {

		parent::__construct();

		// set the scan user settings
		$settings = ADBC_Settings::instance()->get_settings();
		$this->database_rows_batch_size = $settings['database_rows_batch'];
		$this->file_content_chunk_size = $settings['file_content_chunks'] * 1024; // convert to bytes from ko

		// set the scan info instance
		$this->scan_info_instance = ADBC_Scan_Info::instance( $items_type, $scan_type, $preg_match, $partial_match, $what_to_scan, $selected_items_to_scan, $override_manual_categorization, $continue_scan, $settings['file_lines_batch'] );

		// set all the necessary scan files paths
		$this->scan_results_file_path = ADBC_Scan_Paths::get_scan_results_path( $this->scan_info_instance->items_type );
		$this->partial_match_results_file_path = ADBC_Scan_Paths::get_partial_match_temp_results_file_path( $this->scan_info_instance->items_type );
		$this->local_temp_results_file_path = ADBC_Scan_Paths::get_local_temp_scan_results_path( $this->scan_info_instance->items_type );
		$this->dict_file_path = ADBC_Scan_Paths::get_addons_dictionary_file_path( $this->scan_info_instance->items_type );
		$this->temp_dict_file_path = ADBC_Scan_Paths::get_addons_dictionary_temp_file_path( $this->scan_info_instance->items_type );
		$this->slug_list_temp_file_path = ADBC_Scan_Paths::get_addons_dictionary_slug_list_temp_file_path( $this->scan_info_instance->items_type );

		// initialize CPU usage control config
		$this->reduce_cpu_usage = $settings['reduce_cpu_usage'] === '1' ? true : false;
		$this->cpu_work_time_ms = $settings['cpu_work_time_ms'];
		$this->cpu_rest_time_ms = $settings['cpu_rest_time_ms'];
		$this->cpu_last_tick = microtime( true );

	}

	/**
	 * Run the scan process.
	 */
	public function run() {

		try {

			// Register the shutdown callback
			$this->register_shutdown_callback( $this );

			// Check if the scan is possible
			$this->check_if_scan_can_start();

			// Try to set the max execution time for the current script run
			$this->set_current_script_max_execution_time();

			// Set the current script scan start time and calculate the exiting time to avoid timeout
			$this->set_start_and_exit_times();

			// Change the scan status to running if the scan is not done
			if ( ! $this->is_done() )
				$this->mark_scan_as_running();

			// Start or continue the local scan if the local scan status is not "done"
			if ( ! $this->is_local_scan_done() )
				ADBC_Local_Scan::instance()->run();

			// If the scan type is "local" or there was no item to scan, mark the scan as finished and return
			if ( $this->is_local_only() || $this->get_total_items_to_scan() === 0 ) {
				$this->mark_scan_as_done();
				return;
			}

			// Move to the remote scan step only if we are coming after a local scan
			$this->prepare_for_remote_scan();

			// If the remote scan is requested or done, mark the scan as finished for shutdown and return 
			if ( $this->is_remote_scan_successfully_requested() ) {
				$this->mark_scan_as_finished_for_shutdown();
				return;
			}

			// If the remote scan is not requested or done, request it
			ADBC_Remote_Scan::instance()->run();

		} catch (Throwable $e) {
			throw $e;
		}

	}

	/**
	 * Register the shutdown callback, which will be called when the script is terminated.
	 *
	 * @param ADBC_Scan $instance The scan instance.
	 */
	public static function register_shutdown_callback( ADBC_Scan $instance ) {

		register_shutdown_function( function () use ($instance) {
			$instance->do_when_shutdown();
		} );

	}

	/**
	 * The function that will be called when the script is terminated, it will save the scan progress to be continued later.
	 */
	public function do_when_shutdown( $forced = false ) {

		// If the scan finished successfully, don't do anything
		if ( $this->scan_info_instance->scan_finished )
			return;

		// Change the scan status to shutdown and the local and remote statuses to inactive
		$this->scan_info_instance->scan_info['status'] = self::SHUTDOWN;

		// Change the local scan status to inactive if we are still in the local scan
		if ( ! $this->is_local_scan_done() )
			$this->scan_info_instance->scan_info['local']['status'] = self::INACTIVE;

		// Change the remote scan status to inactive if we are in the requesting remote scan step (7) and the remote scan status is not requested or done
		if ( $this->is_requesting_remote_scan() )
			$this->scan_info_instance->scan_info['remote']['status'] = self::INACTIVE;

		// save the partial match result to a file if we are in the partial match step
		if ( $this->get_current_step() === self::PARTIAL_MATCH_SCAN )
			$this->save_partial_match_results_to_file();

		// if the shutdown was forced, we set the shutdown reason to timeout, otherwise we initialize it to 'other'
		$shutdown_reason = $forced ? "forced_timeout" : "other";

		// If the shutdown happened naturally we try to get the reason
		if ( ! $forced ) {

			$error = error_get_last();

			if ( $error ) {

				if ( strpos( $error['message'], 'Maximum execution time' ) !== false )
					$shutdown_reason = "timeout";
				elseif ( strpos( $error['message'], 'Allowed memory size' ) !== false )
					$shutdown_reason = "memory";

			}

		}

		$this->scan_info_instance->scan_info['shutdown_reason'] = $shutdown_reason;

		if ( $shutdown_reason === "timeout" )
			$this->scan_info_instance->scan_info['timeouts_count']++;

		if ( $shutdown_reason === "forced_timeout" )
			$this->scan_info_instance->scan_info['forced_timeouts_count']++;

		$this->update_scan_info();

	}

	/**
	 * Set the safe exit time for the current script to avoid timeout.
	 * 
	 * @return void
	 */
	private function set_start_and_exit_times() {

		self::$current_script_start_time = time();

		// 5 seconds before the timeout if is less than 1 minute, otherwise 10 seconds 
		$safe_interval = self::$current_script_max_execution_time <= 60 ? 5 : 10;
		self::$exit_time = self::$current_script_start_time + self::$current_script_max_execution_time - $safe_interval;

	}

	/**
	 * Cooperatively reduce CPU usage by briefly pausing after a short work window.
	 * Call this frequently in hot loops.
	 * 
	 * @return void
	 */
	protected function reduce_cpu_usage() {

		if ( ! $this->reduce_cpu_usage )
			return;

		$now = microtime( true );
		$elapsed_ms = ( $now - $this->cpu_last_tick ) * 1000.0;

		if ( $elapsed_ms >= $this->cpu_work_time_ms ) {
			usleep( $this->cpu_rest_time_ms * 1000 );
			$this->cpu_last_tick = microtime( true );
		}

	}

	/**
	 * Delete the scan temporary files.
	 */
	public function delete_scan_temporary_files() {

		$all_temp_files = ADBC_Scan_Paths::get_all_adbc_scan_temp_files( $this->scan_info_instance->items_type );

		foreach ( $all_temp_files as $temp_file )
			@unlink( $temp_file );

	}

	/**
	 * Delete the current scan from the database and delete the scan temporary files.
	 */
	public function delete_current_scan() {

		delete_option( "adbc_plugin_scan_info_{$this->scan_info_instance->items_type}" );
		delete_option( "adbc_plugin_should_stop_scan_{$this->scan_info_instance->items_type}" );
		$this->delete_scan_temporary_files();

	}

	/**
	 * Check if the scan was stopped.
	 * 
	 * @return bool True if the scan was stopped, false otherwise.
	 */
	public function was_stopped() {
		return $this->was_stopped_by_user_request() || $this->is_remote_scan_stop_requested() || $this->is_scan_should_stop_request_expired();
	}

	/**
	 * Check if the scan is done.
	 * 
	 * @return bool True if the scan is done, false otherwise.
	 */
	public function is_done() {
		return $this->scan_info_instance->scan_info['status'] === self::DONE;
	}

	/**
	 * Check if the maximum remote request retries is reached.
	 * 
	 * @return bool True if the maximum remote request retries is reached, false otherwise.
	 */
	public function is_max_remote_request_retries_reached() {
		return $this->scan_info_instance->scan_info['remote']['retry_count'] >= self::ADBC_MAX_REMOTE_RETRIES && $this->get_current_step_number() === 7;
	}

	/**
	 * Check if we should check the remote scan status.
	 * 
	 * @return bool True if the remote scan status is requested, false otherwise.
	 */
	public function should_check_remote_scan_status() {
		return $this->is_running() && $this->scan_info_instance->scan_info['remote']['status'] === self::REQUESTED;
	}

	/**
	 * Determines if the scan has been shut down.
	 *
	 * @return bool Returns true if the scan has been shut down, false otherwise.
	 */
	public function is_scan_timeout() {
		return $this->scan_info_instance->scan_info['status'] === self::SHUTDOWN && $this->scan_info_instance->scan_info['shutdown_reason'] === "timeout";
	}

	/**
	 * Determines if the scan has been shut down due to forced timeout.
	 *
	 * @return bool Returns true if the scan has been shut down due to forced timeout, false otherwise.
	 */
	public function is_scan_forced_timeout() {
		return $this->scan_info_instance->scan_info['status'] === self::SHUTDOWN && $this->scan_info_instance->scan_info['shutdown_reason'] === "forced_timeout";
	}

	/**
	 * Determines if the scan has been shut down due to memory limit.
	 *
	 * @return bool Returns true if the scan has been shut down due to memory limit, false otherwise.
	 */
	public function is_scan_memory_exceeded() {
		return $this->scan_info_instance->scan_info['status'] === self::SHUTDOWN && $this->scan_info_instance->scan_info['shutdown_reason'] === "memory";
	}

	/**
	 * Determines if the scan has been shut down due to other reasons.
	 *
	 * @return bool Returns true if the scan has been shut down due to other reasons, false otherwise.
	 */
	public function is_scan_other_error() {
		return $this->scan_info_instance->scan_info['status'] === self::SHUTDOWN && $this->scan_info_instance->scan_info['shutdown_reason'] === "other";
	}

	/**
	 * Get the current scan info.
	 * 
	 * @return array The current scan info.
	 */
	public function get_scan_info() {
		return $this->scan_info_instance->scan_info;
	}

	/**
	 * Determines if the scan should continue from a failed remote request.
	 *
	 * @return bool Returns true if the scan should continue from a failed remote request, false otherwise.
	 */
	public function should_continue_from_failed_remote_request() {
		return $this->is_full() && $this->is_running() && $this->is_local_scan_done() && $this->is_remote_request_failed() && ! $this->is_max_remote_request_retries_reached();
	}

	/**
	 * Check if the remote scan is successfully requested.
	 * 
	 * @return bool True if the remote scan is successfully requested, false otherwise.
	 */
	protected function is_remote_scan_successfully_requested() {

		$status = $this->scan_info_instance->scan_info['remote']['status'];
		return $status === self::REQUESTED || $status === self::DONE;

	}

	/**
	 * Reset the remote request status.
	 */
	protected function reset_remote_request_status() {

		$this->scan_info_instance->scan_info['remote']['status'] = "";
		$this->update_scan_info();

	}

	/**
	 * Mark the scan as finished for shutdown.
	 */
	protected function mark_scan_as_finished_for_shutdown() {
		$this->scan_info_instance->scan_finished = true;
	}

	/**
	 * Update the scan info and stops the scan if requested
	 */
	protected function update_scan_info() {

		$this->stop_scan_if_requested();
		$this->stop_scan_if_scan_info_option_deleted();
		$this->scan_info_instance->update();

	}

	/**
	 * Get the current step.
	 * 
	 * @return string The current step.
	 */
	protected function get_current_step() {
		return $this->scan_info_instance->scan_info['step'];
	}

	/**
	 * Get the current step number.
	 * 
	 * @return int The current step number.
	 */
	protected function get_current_step_number() {
		return self::STEPS_NUMBERS[ $this->get_current_step()];
	}

	/**
	 * Set the current step.
	 * 
	 * @param string $step The step to set.
	 */
	protected function set_current_step( $step ) {
		$this->scan_info_instance->scan_info['step'] = $step;
	}

	/**
	 * Check if the remote request failed.
	 * 
	 * @return bool True if the remote request failed, false otherwise.
	 */
	protected function is_remote_request_failed() {
		return $this->scan_info_instance->scan_info['remote']['status'] === self::FAILED;
	}

	/**
	 * Update the dictionary file with the new slugs names.
	 * This function will be called in the end of the local scan and the end of the remote scan.
	 */
	protected function update_slug_name_dictionary() {

		// Get the unique slugs from the scan results file and write them to the slug list temp file.
		$found_slugs = $this->create_unique_slugs_list_from_scan_results();

		if ( $found_slugs === false )
			return;

		// If the dictionary file does not exist, create it
		if ( ADBC_Files::instance()->exists( $this->dict_file_path ) === false )
			ADBC_Files::instance()->put_contents( $this->dict_file_path, "" );

		// Open the necessary files
		$dict_file_handle = ADBC_Files::instance()->get_file_handle( $this->dict_file_path, 'r' );
		$temp_dict_file_handle = ADBC_Files::instance()->get_file_handle( $this->temp_dict_file_path, 'w' );
		$slug_list_temp_file_handle = ADBC_Files::instance()->get_file_handle( $this->slug_list_temp_file_path, 'r' );

		if ( $temp_dict_file_handle === false || $slug_list_temp_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// Get all installed addons names for the local scan dictionary update
		$installed_addons = ADBC_Addons::get_all_installed_addons();

		// Read the slug list temp file by batches and add them to the temp dictionary file
		do {

			$slug_list_batch = [];

			// Read the slug list temp file by batches
			while ( ( $line = fgets( $slug_list_temp_file_handle ) ) !== false ) {

				$line = rtrim( $line, "\r\n" );

				if ( $line === '' )
					continue;

				$slug_list_batch[ $line ] = "";

				if ( count( $slug_list_batch ) === $this->scan_info_instance->scan_info['batch_size'] )
					break;

			}

			// If there are no slugs to scan, break the loop
			if ( empty( $slug_list_batch ) )
				break;

			// write all the slug name pairs that already exist in the dictionary to the temp dictionary file
			rewind( $dict_file_handle );

			while ( ( $line = fgets( $dict_file_handle ) ) !== false ) {

				$line = rtrim( $line, "\r\n" );
				[ $typed_slug, $addon_name ] = ADBC_Dictionary::split_slug_name_dictionary_line( $line );

				if ( $typed_slug === false || $addon_name === false )
					continue;

				// If the slug is already in the dictionary, write it to the temp dictionary file and remove it from the batch
				if ( key_exists( $typed_slug, $slug_list_batch ) ) {
					fwrite( $temp_dict_file_handle, $typed_slug . '|' . $addon_name . "\n" );
					unset( $slug_list_batch[ $typed_slug ] );
				}

			}

			// If there are no slugs to add, continue to the next batch
			if ( empty( $slug_list_batch ) )
				continue;

			// Get the remaining slugs names from the installed addons list and add them to the temp dictionary file
			foreach ( $slug_list_batch as $typed_slug => $addon_name ) {

				if ( isset( $installed_addons[ $typed_slug ] ) ) {
					$addon_name = $installed_addons[ $typed_slug ];
					fwrite( $temp_dict_file_handle, $typed_slug . '|' . $addon_name . "\n" );
				}

			}

		} while ( ! empty( $slug_list_batch ) );

		fclose( $dict_file_handle );
		fclose( $temp_dict_file_handle );
		fclose( $slug_list_temp_file_handle );

		// Replace the dictionary file with the temp dictionary file
		if ( ADBC_Files::instance()->exists( $this->temp_dict_file_path ) && ! rename( $this->temp_dict_file_path, $this->dict_file_path ) )
			throw new Exception( "Unable to rename the file." );

	}

	/** Stop the remote scan if requested.
	 * 
	 * @return bool True if the scan was stopped, false otherwise.
	 */
	private function is_remote_scan_stop_requested() {
		return $this->should_stop_scan() && $this->scan_info_instance->scan_info['remote']['status'] != "";
	}

	/**
	 * Check if the scan should stop request is expired.
	 * 
	 * @return bool True if the scan should stop, false otherwise.
	 */
	private function is_scan_should_stop_request_expired() {

		$should_stop_data = $this->get_should_stop_scan_option();

		if ( $should_stop_data === null )
			return false;

		$expired = time() - $should_stop_data['requested_at'] > self::ADBC_STOP_SCAN_REQUEST_EXPIRATION;

		return $should_stop_data['should_stop_flag'] === "should_stop" && $expired;

	}

	/**
	 * Check if the scan was stopped, or if should stop flag has expired.
	 * 
	 * @return bool True if the scan was stopped, false otherwise.
	 */
	private function was_stopped_by_user_request() {

		$should_stop_data = $this->get_should_stop_scan_option();

		if ( $should_stop_data === null )
			return false;

		return $should_stop_data['should_stop_flag'] === "stopped";

	}

	/**
	 * Stop the scan if the scan info option is deleted.
	 */
	private function stop_scan_if_scan_info_option_deleted() {

		// delete the scan info option cache before getting the option value 
		wp_cache_delete( "adbc_plugin_scan_info_{$this->scan_info_instance->items_type}", "options" );
		$scan_info_option = get_option( "adbc_plugin_scan_info_{$this->scan_info_instance->items_type}" );

		if ( $scan_info_option === false ) {
			$this->delete_current_scan();
			$this->mark_scan_as_finished_for_shutdown();
			exit;
		}

	}

	/**
	 * Get the should stop scan option decoded.
	 * Uses direct SQL query to avoid the cache.
	 * 
	 * @return string The should stop scan option value.
	 */
	private function get_should_stop_scan_option() {

		global $wpdb;
		$option_name = 'adbc_plugin_should_stop_scan_' . $this->scan_info_instance->items_type;

		$query = $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option_name );

		$should_stop_scan = $wpdb->get_var( $query );
		return $should_stop_scan === null ? null : json_decode( $should_stop_scan, true );
	}

	/**
	 * Check if the scan should stop.
	 * 
	 * @return bool True if the scan should stop, false otherwise.
	 */
	private function should_stop_scan() {

		$should_stop_data = $this->get_should_stop_scan_option();

		if ( $should_stop_data === null )
			return false;

		return $should_stop_data['should_stop_flag'] === "should_stop";

	}

	/**
	 * Stop the scan if requested, by exiting the script and marking the should stop scan option as stopped.
	 */
	private function stop_scan_if_requested() {

		if ( $this->should_stop_scan() === true ) {

			$should_stop_data = [ 'should_stop_flag' => "stopped", 'requested_at' => time()];
			update_option( "adbc_plugin_should_stop_scan_{$this->scan_info_instance->items_type}", json_encode( $should_stop_data ), false );
			$this->mark_scan_as_finished_for_shutdown();
			exit;

		}

	}

	/**
	 * Check if the scan is possible.
	 * 
	 * @return void
	 */
	private function check_if_scan_can_start() {

		// Check if the scan folder exists, readable and writable
		if ( ! ADBC_Files::instance()->is_readable_and_writable( ADBC_Scan_Paths::SCAN_FOLDER_PATH ) ) {
			throw new Exception( "The scan cannot be started because the scan folder does not exist or is not readable/writable." );
		}

	}

	/**
	 * Force the shutdown of the scan process to avoid timeout.
	 * 
	 * @return void
	 */
	protected function force_shutdown() {

		$this->do_when_shutdown( true );
		$this->scan_info_instance->scan_finished = true;
		exit();

	}

	/**
	 * Check if the script should be forcefully shut down to avoid timeout.
	 * Calls the force_shutdown() method if the script execution time exceeds the exit time.
	 * 
	 * @return void
	 */
	protected function force_shutdown_if_needed() {

		if ( time() >= self::$exit_time ) {
			$this->force_shutdown();
		}

	}

	/**
	 * Check if the local scan is done.
	 * 
	 * @return bool True if the local scan is done, false otherwise.
	 */
	private function is_local_scan_done() {
		return $this->scan_info_instance->scan_info['local']['status'] === self::DONE;
	}

	/**
	 * Checks if the scan is local only.
	 *
	 * @return bool Returns true if the scan is local only, false otherwise.
	 */
	private function is_local_only() {
		return $this->scan_info_instance->scan_info['scan_type'] === self::LOCAL_SCAN;
	}

	/**
	 * Determines if the scan type is a full scan.
	 *
	 * @return bool Returns true if the scan type is a full scan, false otherwise.
	 */
	private function is_full() {
		return $this->scan_info_instance->scan_info['scan_type'] === self::FULL_SCAN;
	}

	/**
	 * Determines if the scan is currently running.
	 *
	 * @return bool Returns true if the scan is running, false otherwise.
	 */
	private function is_running() {
		return $this->scan_info_instance->scan_info['status'] === self::RUNNING;
	}

	/**
	 * Determines if a remote scan is being requested.
	 *
	 * @return bool Returns true if a remote scan is being requested, false otherwise.
	 */
	private function is_requesting_remote_scan() {
		return ! $this->is_remote_scan_successfully_requested() && $this->get_current_step() === self::REQUESTING_REMOTE_SCAN;
	}

	/**
	 * Marks the scan as running.
	 */
	private function mark_scan_as_running() {

		$this->scan_info_instance->scan_info['status'] = self::RUNNING;
		$this->scan_info_instance->scan_info['shutdown_reason'] = "";
		$this->update_scan_info();

	}

	/**
	 * Marks the scan as done.
	 */
	private function mark_scan_as_done() {

		$this->scan_info_instance->scan_info['status'] = self::DONE;
		$this->update_scan_info();
		$this->mark_scan_as_finished_for_shutdown();

	}

	/**
	 * Prepares for remote scan if we are in the end of the local scan.
	 */
	private function prepare_for_remote_scan() {

		if ( $this->get_current_step() === self::PREPARE_LOCAL_SCAN_RESULTS ) {
			$this->set_current_step( self::REQUESTING_REMOTE_SCAN );
			$this->update_scan_info();
		}

	}

	/**
	 * Saves the partial match results to the partial match temp results file to keep them for potential scan shutdown continuation.
	 */
	private function save_partial_match_results_to_file() {

		$file_handle = ADBC_Files::instance()->get_file_handle( $this->partial_match_results_file_path, 'w' );

		if ( $file_handle === false )
			return;

		foreach ( $this->scan_info_instance->partial_match_batch_results as $item_name => $partial_match_results ) {
			$line = $item_name . '|' . json_encode( $partial_match_results ) . "\n";
			fwrite( $file_handle, $line );
		}

		fclose( $file_handle );

	}

	/**
	 * Create the unique slugs list from the scan results file.
	 * 
	 * @return bool True if we found any slugs to add to the dictionary file, false otherwise.
	 */
	private function create_unique_slugs_list_from_scan_results() {

		$scan_results_file_handle = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'r' );
		$slug_list_temp_file_handle = ADBC_Files::instance()->get_file_handle( $this->slug_list_temp_file_path, 'w' );

		if ( $scan_results_file_handle === false || $slug_list_temp_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// This flag will be returned as true if we found any slugs to add to the dictionary file.
		$found_slugs_flag = false;

		// Create the unique slugs file list from the scan results by batches.
		do {

			$slug_list_batch = [];

			// Read the scan results file by batches
			$lines_count = 0;

			while ( ( $line = fgets( $scan_results_file_handle ) ) !== false ) {

				$lines_count++;

				$line = rtrim( $line, "\r\n" );

				if ( $line === '' )
					continue;

				[ $item_name, $categorization ] = ADBC_Scan_Utils::split_result_file_line( $line );

				if ( $item_name === false || $categorization === false )
					continue;

				// Add all the slugs ("l", "r", "m") to the batch
				foreach ( $categorization as $categorization_type => $slugs_list ) {

					foreach ( $slugs_list as $slug_percentage ) {

						$slug_parts = explode( ":", $slug_percentage );

						if ( count( $slug_parts ) < 2 )
							continue;

						$slug = $slug_parts[0] . ":" . $slug_parts[1];

						$slug_list_batch[ $slug ] = "";

					}

				}

				// Break the loop if the batch size is reached
				if ( $lines_count === $this->scan_info_instance->scan_info['batch_size'] )
					break;

			}

			// If we reached the end of the scan results file, break the loop
			if ( $lines_count === 0 )
				break;

			// If there are no slugs to process, continue to the next batch
			if ( empty( $slug_list_batch ) )
				continue;

			// Remove the already saved slugs from the batch
			$this->remove_already_saved_slug_list( $slug_list_batch );

			// If there are no slugs to process, continue to the next batch
			if ( empty( $slug_list_batch ) )
				continue;

			// Set the flag to true if we found any slugs to add to the dictionary file
			$found_slugs_flag = true;

			// Write the remaining slugs to the slug list temp file
			fwrite( $slug_list_temp_file_handle, implode( "\n", array_keys( $slug_list_batch ) ) . "\n" );

		} while ( $lines_count > 0 );

		fclose( $scan_results_file_handle );
		fclose( $slug_list_temp_file_handle );

		return $found_slugs_flag;

	}

	/**
	 * Remove the already saved slugs from the batch to avoid duplicates.
	 * 
	 * @param array $slug_list_batch The slugs list batch.
	 */
	private function remove_already_saved_slug_list( &$slug_list_batch ) {

		if ( empty( $slug_list_batch ) )
			return;

		$slug_list_file_handle = ADBC_Files::instance()->get_file_handle( $this->slug_list_temp_file_path, 'r' );

		if ( $slug_list_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// Read the file and remove the slugs that are already written to the file.
		while ( ( $line = fgets( $slug_list_file_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			if ( key_exists( $line, $slug_list_batch ) )
				unset( $items_to_scan[ $line ] );

			if ( empty( $slug_list_batch ) )
				break;

		}

		fclose( $slug_list_file_handle );

	}

	/**
	 * Set the current script max execution time.
	 */
	private function set_current_script_max_execution_time() {

		$desired = ADBC_Settings::instance()->get_setting( 'scan_max_execution_time' );
		$current_ini = function_exists( 'ini_get' ) ? (int) ini_get( 'max_execution_time' ) : 0;

		// Decide the target value
		// In the auto mode, we use the safe default if we can't get the value of the current_ini, otherwise we use the smaller value
		if ( $desired === 0 )
			$target = ( $current_ini === 0 ) ? self::$safe_timeout_limit : min( $current_ini, self::$safe_timeout_limit );
		else
			$target = $desired;

		// If our target is the same as the current ini value, just save it and return
		if ( $current_ini !== 0 && $current_ini === $target ) {
			self::$current_script_max_execution_time = $current_ini;
			return;
		}

		// Try to set the target value using set_time_limit and ini_set
		$applied = false;
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( $target );
			$applied = function_exists( 'ini_get' ) && ( (int) ini_get( 'max_execution_time' ) === $target );
		}

		if ( ! $applied && function_exists( 'ini_set' ) )
			$applied = @ini_set( 'max_execution_time', (string) $target ) !== false;

		// Finally, If we can't set, we use the smaller value between the current_ini and the safe timeout limit
		if ( ! $applied )
			self::$current_script_max_execution_time = ( $current_ini === 0 ) ? self::$safe_timeout_limit : min( $current_ini, self::$safe_timeout_limit );
		else
			self::$current_script_max_execution_time = $target;

	}

	protected function get_total_items_to_scan() {
		return $this->scan_info_instance->scan_info['local']['total_items'];
	}

}