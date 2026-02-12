<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Class ADBC_Scan_Info
 *
 * This class is used to store the current scan information it is instantiated by the scan class to centralize the scan information.
 */
class ADBC_Scan_Info extends ADBC_Singleton {

	public $scan_info = array(
		'scan_type' => '', // full or local
		'continue_scan' => false, // if the scan is a continuation of a previous scan
		'batch_size' => 1000000, // the number of lines to read from all adbc files when we started the scan
		'preg_match' => true, // if the preg_match step should be executed
		'partial_match' => true, // if the partial_match step should be executed
		'what_to_scan' => '', // 'all', 'selected', 'custom
		'override_manual_categorization' => false, // if the scan should override the manual corrections
		'step' => '', // the current step of the scan (1_prepare_items_to_scan, 2_collect_php_files, 3_preg_match_scan, 4_exact_match_scan, 5_partial_match_scan, 6_prepare_local_scan_results, 7_requesting_remote_scan, 8_getting_remote_scan_results)
		'status' => '', // running, done, shutdown
		'shutdown_reason' => '', // timeout or exception
		'timeouts_count' => 0, // the number of timeouts we had during the scan
		'forced_timeouts_count' => 0, // the number of forced timeouts we had during the scan
		'updated_at' => null, // the last time the scan info was updated
		'local' => array(
			'status' => '', // inactive, running, done
			'total_items' => 0, // the total number of items to scan
			'collecting_files' => array(
				'collected_files' => 0, // the number of collected php files
				'total_files' => 0, // the total number of files we tried to collect
				'last_file' => '', // the last file we tried to collect (all files extensions)
			),
			'preg_match' => array(
				'batch_number' => 0, // the current line of the item we are scanning (from the items to scan file)
				'item_line' => 0, // the current line of the item we are scanning (from the collected items file)
				'file_line' => 0, // the current line of the file we are scanning (from the collected files file)
				'total_items' => 0, // the total number of items to scan
				'progress' => 0, // the progress of the preg_match scan
			),
			'exact_match' => array(
				'batch_number' => 0,
				'item_line' => 0,
				'file_line' => 0,
				'total_items' => 0,
				'progress' => 0,
			),
			'partial_match' => array(
				'batch_number' => 0,
				'item_line' => 0,
				'file_line' => 0,
				'total_items' => 0,
				'progress' => 0,
			),
		),
		'remote' => array(
			'status' => '', // 'inactive', 'failed', 'requested', 'done'
			'last_failed_request_time' => 0, // the time of the last retry
			'retry_count' => 0, // the number of retries the remote scan requested
			'successful_request_time' => 0, // the time when the first request was sent
			'request_id' => '', // the request id of the remote scan got from the API
			'trimmed_count' => 0, // the number of items trimmed to fit the post_max_size
			'nb_total_items' => 0, // the total number of items sent to the API
			'failure_code' => '', // the failure code of the remote scan request
			'corrected_items' => 0, // the number of items corrected by the remote scan
		)
	);

	public $items_type = '';

	public $what_to_scan = '';

	public $selected_items_to_scan = array();

	public $scan_finished = false;

	public $partial_match_batch_results = array();

	protected function __construct( $items_type = "", $scan_type = "", $preg_match = true, $partial_match = true, $what_to_scan = "", $selected_items_to_scan = array(), $override_manual_categorization = false, $continue_scan = null, $batch_size = 1000000 ) {

		parent::__construct();

		// Get the scan info from the database if a scan is already running
		$scan_info = get_option( "adbc_plugin_scan_info_{$items_type}" );

		if ( $scan_info ) {

			// Set the scan info properties from the already existing scan
			$this->items_type = $items_type;
			$this->scan_info = $scan_info;
			$this->what_to_scan = $scan_info['what_to_scan'];
			$this->scan_info['continue_scan'] = $continue_scan === null ? $scan_info['continue_scan'] : $continue_scan;

			// If the purpose of the instantiation is a continuation of a previous scan we update the updated_at time
			// Otherwise, if the purpose is for checking the scan status we don't update the updated_at time
			// E.g. instantiations from the heartbeat endpoint
			if ( $this->scan_info['continue_scan'] === true )
				$this->scan_info['updated_at'] = time();

		} else {

			// Set the new scan info properties from the parameters
			$this->items_type = $items_type;
			$this->scan_info['scan_type'] = $scan_type;
			$this->scan_info['preg_match'] = $preg_match;
			$this->scan_info['partial_match'] = $partial_match;
			$this->scan_info['what_to_scan'] = $what_to_scan;
			$this->scan_info['override_manual_categorization'] = $override_manual_categorization;
			$this->what_to_scan = $what_to_scan;
			$this->selected_items_to_scan = $selected_items_to_scan;
			$this->scan_info['continue_scan'] = $continue_scan;
			$this->scan_info['batch_size'] = $batch_size;
			$this->scan_info['updated_at'] = time();

		}

		$this->construct_update();

	}

	/**
	 * Get the scan info from the database without updating the updated_at time.
	 * This method is called when the scan class is instantiated.
	 */
	private function construct_update() {
		update_option( "adbc_plugin_scan_info_{$this->items_type}", $this->scan_info, false );
	}

	/**
	 * Update the scan info in the database and set the updated_at time.
	 * This method is called after each update of the scan info from the scan class.
	 */
	public function update() {
		$this->scan_info['updated_at'] = time();
		update_option( "adbc_plugin_scan_info_{$this->items_type}", $this->scan_info, false );
	}

}