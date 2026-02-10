<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Local scan class.
 *
 * This class is responsible for running the local scan steps, it is designed to either start a new scan or continue a scan that was interrupted from where it was left exactly.
 * It uses an optimized logic to read and write in files and database by batches to avoid memory usage issues.
 */
class ADBC_Local_Scan extends ADBC_Scan {

	protected $items_to_scan_file_path = '';

	protected $files_to_scan_file_path = '';

	private $items_to_scan_temp_file_path = '';

	protected function __construct() {

		parent::__construct();

		// set the necessary local scan files paths
		$this->items_to_scan_file_path = ADBC_Scan_Paths::get_items_to_scan_path( $this->scan_info_instance->items_type );
		$this->files_to_scan_file_path = ADBC_Scan_Paths::get_files_to_scan_file_path( $this->scan_info_instance->items_type );
		$this->items_to_scan_temp_file_path = ADBC_Scan_Paths::get_temp_items_to_scan_path( $this->scan_info_instance->items_type );

	}

	/**
	 * Run the local scan.
	 */
	public function run() {

		try {

			// always mark the scan as running to handle the case when we are continuing a scan
			$this->mark_local_scan_as_running();

			// If the current step is empty, set it to the first step
			if ( $this->get_current_step() === "" ) {
				$this->set_current_step( self::PREPARE_ITEMS_TO_SCAN );
				$this->update_scan_info();
			}

			// Execute the prepare items to scan step and prepare for the next step
			if ( $this->prepare_items_to_scan() === true ) {

				// If there are no items to scan, mark the local scan as done and return
				if ( $this->get_total_items_to_scan() === 0 ) {
					$this->mark_local_scan_as_done();
					$this->update_scan_info();
					return;
				}

				// prepare the next step
				$this->set_current_step( self::COLLECT_PHP_FILES );
				$this->update_scan_info();

			}

			// Execute the collect php files step and prepare for the next step
			if ( $this->collect_php_files() === true ) {
				$this->set_current_step( self::PREG_MATCH_SCAN );
				$this->update_scan_info();
			}

			// Execute the preg match scan step and prepare for the next step
			if ( $this->preg_match_scan() === true ) {
				$this->set_current_step( self::EXACT_MATCH_SCAN );
				$this->update_scan_info();
			}

			// Execute the exact match scan step and prepare for the next step
			if ( $this->exact_match_scan() === true ) {
				$this->set_current_step( self::PARTIAL_MATCH_SCAN );
				$this->update_scan_info();
			}

			// Execute the partial match scan step and prepare for the next step
			if ( $this->partial_match_scan() === true ) {
				$this->set_current_step( self::PREPARE_LOCAL_SCAN_RESULTS );
				$this->update_scan_info();
			}

			// Execute the prepare local scan results step and mark the local scan as done
			if ( $this->prepare_local_scan_results() === true ) {
				$this->mark_local_scan_as_done();
				$this->update_scan_info();
			}

		} catch (Throwable $e) {
			throw $e;
		}

	}

	/**
	 * Mark the local scan as running.
	 */
	private function mark_local_scan_as_running() {

		$this->scan_info_instance->scan_info['local']['status'] = "running";
		$this->update_scan_info();

	}

	/**
	 * Execute the prepare items to scan step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function prepare_items_to_scan() {

		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::PREPARE_ITEMS_TO_SCAN ] )
			return false;

		// prepare items to scan
		ADBC_Prepare_Items::instance()->run();

		$this->update_scan_info();

		return true;

	}

	/**
	 * Execute the collect php files step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function collect_php_files() {
		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::COLLECT_PHP_FILES ] )
			return false;

		// collect the php files
		ADBC_Collect_Files::instance()->run();

		// reset the continue scan flag for next step
		$this->scan_info_instance->scan_info['continue_scan'] = false;

		$this->update_scan_info();

		return true;

	}

	/**
	 * Execute the preg match scan step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function preg_match_scan() {

		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::PREG_MATCH_SCAN ] )
			return false;

		// If the preg match scan is disabled, skip this step and continue to the next step
		if ( $this->scan_info_instance->scan_info['preg_match'] === false )
			return true;

		// set the preg match total items count
		$this->scan_info_instance->scan_info['local']['preg_match']['total_items'] = $this->scan_info_instance->scan_info['local']['total_items'];

		// run the preg match scan

		// remove already categorized items from the items to scan for the next step
		$categorized_items_count = $this->remove_already_categorized_items();

		// update the total items count for the exact match scan (next step)
		$this->scan_info_instance->scan_info['local']['exact_match']['total_items'] = $this->scan_info_instance->scan_info['local']['preg_match']['total_items'] - $categorized_items_count;

		// reset the continue scan flag for next step
		$this->scan_info_instance->scan_info['continue_scan'] = false;

		$this->update_scan_info();

		return true;

	}

	/**
	 * Execute the exact match scan step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function exact_match_scan() {

		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::EXACT_MATCH_SCAN ] )
			return false;

		// if the preg match scan is disabled, set the total items count for the exact match scan to the total items count of the local scan
		if ( $this->scan_info_instance->scan_info['preg_match'] === false )
			$this->scan_info_instance->scan_info['local']['exact_match']['total_items'] = $this->scan_info_instance->scan_info['local']['total_items'];

		// if there are no items to scan, consider the step as executed successfully
		if ( $this->scan_info_instance->scan_info['local']['exact_match']['total_items'] === 0 )
			return true;

		// run the exact match scan
		ADBC_Exact_Match::instance()->run();

		// remove already categorized items from the items to scan for the next step
		$categorized_items_count = $this->remove_already_categorized_items();

		// update the total items count for the partial match scan if it is enabled
		if ( $this->scan_info_instance->scan_info['partial_match'] === true )
			$this->scan_info_instance->scan_info['local']['partial_match']['total_items'] = $this->scan_info_instance->scan_info['local']['exact_match']['total_items'] - $categorized_items_count;

		// reset the continue scan flag for next step
		$this->scan_info_instance->scan_info['continue_scan'] = false;

		$this->update_scan_info();

		return true;

	}

	/**
	 * Execute the partial match scan step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function partial_match_scan() {

		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::PARTIAL_MATCH_SCAN ] )
			return false;

		// if the partial match scan is disabled or there are no items to scan, consider the step as executed successfully
		if ( $this->scan_info_instance->scan_info['partial_match'] === false || $this->scan_info_instance->scan_info['local']['partial_match']['total_items'] === 0 )
			return true;

		// partial match scan
		ADBC_Partial_Match::instance()->run();

		$this->update_scan_info();

		return true;

	}

	/**
	 * Execute the prepare local scan results step if it is not executed yet.
	 *
	 * @return bool
	 *  True if the step was executed successfully, false otherwise.
	 */
	private function prepare_local_scan_results() {

		// test if we should execute this step
		if ( $this->get_current_step_number() > self::STEPS_NUMBERS[ self::PREPARE_LOCAL_SCAN_RESULTS ] )
			return false;

		// prepare local scan results
		ADBC_Prepare_Local_Scan_Results::instance()->run();

		$this->update_scan_info();

		return true;

	}

	/**
	 * Mark the local scan as done.
	 */
	private function mark_local_scan_as_done() {

		$this->scan_info_instance->scan_info['local']['status'] = "done";
		$this->update_scan_info();

		if ( $this->scan_info_instance->scan_info['scan_type'] === self::LOCAL_SCAN )
			$this->delete_scan_temporary_files();

	}

	/**
	 * Get the addon_type:slug from a file path.
	 * 
	 * @param string $file_path
	 * The file path.
	 * 
	 * @return string
	 * The addon_type:slug of the item.
	 */
	public function get_slug_from_file_path( $file_path ) {

		// Normalize file path
		$file_path = wp_normalize_path( $file_path );

		// Get WordPress directories for themes, plugins, and mu-plugins, normalized
		$plugin_directory = wp_normalize_path( WP_PLUGIN_DIR );
		$mu_plugin_directory = wp_normalize_path( WPMU_PLUGIN_DIR );

		global $wp_theme_directories;
		$themes_directories = [];

		foreach ( $wp_theme_directories as $theme_directory ) {
			if ( ADBC_Files::instance()->is_dir( $theme_directory ) )
				$themes_directories[] = wp_normalize_path( $theme_directory );
		}

		// Check if the file belongs to a theme
		foreach ( $themes_directories as $theme_directory ) {
			if ( strpos( $file_path, $theme_directory ) === 0 ) {
				$relative_path = substr( $file_path, strlen( $theme_directory ) );
				// Extract the slug (the first directory name in the path)
				$slug = trim( strtok( $relative_path, '/' ) );
				return "t:" . $slug;
			}
		}

		// Check if the file belongs to a plugin
		if ( strpos( $file_path, $plugin_directory ) === 0 ) {
			$relative_path = substr( $file_path, strlen( $plugin_directory ) );
			// Extract the slug (the first directory name in the path)
			$slug = trim( strtok( $relative_path, '/' ) );
			return "p:" . $slug;
		}

		// Check if the file belongs to a mu-plugin
		if ( strpos( $file_path, $mu_plugin_directory ) === 0 ) {
			$relative_path = substr( $file_path, strlen( $mu_plugin_directory ) );
			// Extract the slug (the file name or directory name in the path)
			$slug = trim( strtok( $relative_path, '/' ) );
			return "p:" . $slug;
		}

	}

	/**
	 * Remove already categorized items from the items to scan file, used between the scan steps to avoid scanning the same items multiple times.
	 *
	 * @return int
	 *  The number of removed items.
	 */
	private function remove_already_categorized_items() {

		$scan_results_temp_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, 'r' );
		$items_to_scan_handle = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'r' );
		$items_to_scan_temp_handle = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_temp_file_path, 'w' );

		if ( ! $scan_results_temp_handle || ! $items_to_scan_handle || ! $items_to_scan_temp_handle )
			throw new Exception( "Unable to open the file." );

		$removed_items_count = 0;

		// get items to scan from the items to scan file by batches and remove the already categorized items
		do {

			$items_to_scan_batch = [];

			// read the items to scan file by batches
			while ( ( $line = fgets( $items_to_scan_handle ) ) !== false ) {

				$item_name = rtrim( $line, "\r\n" );

				if ( $item_name === '' )
					continue;

				$items_to_scan_batch[ $item_name ] = false;

				if ( count( $items_to_scan_batch ) == $this->scan_info_instance->scan_info['batch_size'] )
					break;

			}

			// if there are no items to scan, break the loop
			if ( empty( $items_to_scan_batch ) )
				break;

			// reset the file pointer to the beginning of the temp local scan results file
			rewind( $scan_results_temp_handle );

			// remove the already categorized items from the items to scan batch
			while ( ( $line = fgets( $scan_results_temp_handle ) ) !== false ) {

				list( $item_name, $belong_to_json ) = ADBC_Scan_Utils::split_result_file_line( $line );

				if ( $item_name === false )
					continue;

				// remove the item from the items to scan batch if it exists
				if ( isset( $items_to_scan_batch[ $item_name ] ) ) {
					unset( $items_to_scan_batch[ $item_name ] );
					$removed_items_count++;
				}

			}

			// write the remaining items to the items to scan temp file
			fwrite( $items_to_scan_temp_handle, implode( "\n", array_keys( $items_to_scan_batch ) ) . "\n" );

		} while ( ! empty( $items_to_scan_batch ) );

		fclose( $scan_results_temp_handle );
		fclose( $items_to_scan_handle );
		fclose( $items_to_scan_temp_handle );

		// replace the items to scan file with the items to scan temp file
		if ( ADBC_Files::instance()->exists( $this->items_to_scan_temp_file_path ) && ! rename( $this->items_to_scan_temp_file_path, $this->items_to_scan_file_path ) )
			throw new Exception( "Unable to rename the file." );

		return $removed_items_count;

	}

	protected function get_collected_files_count() {
		return $this->scan_info_instance->scan_info['local']['collecting_files']['collected_files'];
	}

}