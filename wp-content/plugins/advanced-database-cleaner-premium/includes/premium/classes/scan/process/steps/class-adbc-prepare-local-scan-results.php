<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Prepare the local scan results step class.
 *
 * This class is responsible for preparing the local scan results file after the scan is done.
 * It uses an optimized logic to read and write in files by batches to avoid memory usage issues.
 */
class ADBC_Prepare_Local_Scan_Results extends ADBC_Local_Scan {

	/**
	 * Run the step.
	 */
	public function run() {

		// Force shutdown in the beginning of this step if we still have less than 60% of the script execution time left.
		$this->force_shutdown_if_needed();

		// if temp results file doesn't exists, return
		if ( ! ADBC_Files::instance()->exists( $this->local_temp_results_file_path ) )
			throw new Exception( "Unable to open the file." );

		// add all items to scan that were not categorized to the temp results if the partial match scan is disabled because it does it by itself
		if ( $this->scan_info_instance->scan_info['partial_match'] === false )
			$this->add_uncategorized_items_to_temp_results_file();

		// if the scan results file doesn't exists create it from the temp results file and update the slug name dictionary
		if ( ! ADBC_Files::instance()->exists( $this->scan_results_file_path ) ) {
			copy( $this->local_temp_results_file_path, $this->scan_results_file_path );
			$this->update_slug_name_dictionary();
			return;
		}

		// prepare the results based on the what_to_scan option
		switch ( $this->scan_info_instance->what_to_scan ) {
			case self::SELECTED:
				$this->prepare_results_for_selected();
				break;
			case self::UNCATEGORIZED:
				$this->prepare_results_for_uncategorized();
				break;
			case self::ALL:
				copy( $this->local_temp_results_file_path, $this->scan_results_file_path );
				break;
		}

		$this->update_slug_name_dictionary();

	}

	/**
	 * Prepare the results for "SELECTED" what_to_scan.
	 */
	private function prepare_results_for_selected() {

		// create a temp scan results file to merge the old results with the current scan temp results
		$local_merge_temp_scan_results_path = ADBC_Scan_Paths::get_local_merge_temp_scan_results_path( $this->scan_info_instance->items_type );
		$local_merge_temp_scan_results_handle = ADBC_Files::instance()->get_file_handle( $local_merge_temp_scan_results_path, 'a' );

		// get the scan results file handle
		$scan_results_handle = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'r' );

		// get all temp results from the temp results file
		$temp_results = ADBC_Files::instance()->get_contents( $this->local_temp_results_file_path );

		if ( $temp_results === false || $local_merge_temp_scan_results_handle === false || $scan_results_handle === false )
			throw new Exception( "Unable to open the file." );

		// convert the temp results to an associative array
		$temp_results = explode( "\n", $temp_results );

		foreach ( $temp_results as $temp_result ) {

			[ $item_name, $categorization ] = ADBC_Scan_Utils::split_result_file_line( $temp_result );

			if ( $item_name === false || $categorization === false )
				continue;

			$temp_results_assoc[ $item_name ] = $categorization;

		}

		// replace the old categorization of the selected items with the new categorization
		while ( ( $line = fgets( $scan_results_handle ) ) !== false ) {

			[ $item_name, $categorization ] = ADBC_Scan_Utils::split_result_file_line( $line );

			if ( $item_name === false || $categorization === false )
				continue;

			// if the item exists in the old results and in the selected ones, update the categorization and unset it from the temp results
			if ( isset( $temp_results_assoc[ $item_name ] ) ) {
				$categorization = $temp_results_assoc[ $item_name ];
				unset( $temp_results_assoc[ $item_name ] );
			}

			// always write the item to the merge temp scan results file either with the new categorization or the old one
			fwrite( $local_merge_temp_scan_results_handle, $item_name . '|' . json_encode( $categorization ) . "\n" );

		}

		// write the remaining items in the temp results to the merge temp scan results file
		foreach ( $temp_results_assoc as $item_name => $categorization )
			fwrite( $local_merge_temp_scan_results_handle, $item_name . '|' . json_encode( $categorization ) . "\n" );

		fclose( $scan_results_handle );
		fclose( $local_merge_temp_scan_results_handle );

		// rename the temp scan results file to the scan results file
		if ( ADBC_Files::instance()->exists( $local_merge_temp_scan_results_path ) && ! rename( $local_merge_temp_scan_results_path, $this->scan_results_file_path ) )
			throw new Exception( "Unable to rename the file." );

	}

	/**
	 * Prepare the results for "UNCATEGORIZED" what_to_scan.
	 */
	private function prepare_results_for_uncategorized() {

		// append all the temp results to the scan results file since they are all uncategorized
		$local_temp_results_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, 'r' );
		$scan_results_handle = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'a' );

		if ( $local_temp_results_handle === false || $scan_results_handle === false )
			throw new Exception( "Unable to open the file." );

		while ( ( $line = fgets( $local_temp_results_handle ) ) !== false )
			fwrite( $scan_results_handle, $line );

		fclose( $local_temp_results_handle );
		fclose( $scan_results_handle );

	}

	/**
	 * Add uncategorized items to the temp results file, used when the partial match scan is disabled.
	 * The partial match scan will add the items that were not categorized to the temp results file in the end.
	 */
	private function add_uncategorized_items_to_temp_results_file() {

		$scan_results_temp_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, 'a' );
		$items_to_scan_handle = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'r' );

		if ( $scan_results_temp_handle === false || $items_to_scan_handle === false )
			throw new Exception( "Unable to open the file." );

		// get items to scan from the items to scan file by batches and add the uncategorized items to the temp results file
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

			// remove the already categorized items from the items to scan batch
			$this->remove_already_categorized_items_from_batch( $items_to_scan_batch );

			// write the remaining items to the items to scan temp file as no results were found for them
			foreach ( array_keys( $items_to_scan_batch ) as $item_name )
				fwrite( $scan_results_temp_handle, $item_name . '|{"l":[]}' . "\n" );

		} while ( ! empty( $items_to_scan_batch ) );

		fclose( $scan_results_temp_handle );
		fclose( $items_to_scan_handle );

	}

	/**
	 * Remove already categorized items from the items to scan batch.
	 *
	 * @param array $items_to_scan_batch The items to scan batch.
	 */
	private function remove_already_categorized_items_from_batch( &$items_to_scan_batch ) {

		$scan_results_temp_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, 'r' );

		if ( $scan_results_temp_handle === false )
			throw new Exception( "Unable to open the file." );

		while ( ( $line = fgets( $scan_results_temp_handle ) ) !== false ) {

			[ $item_name, $categorization ] = ADBC_Scan_Utils::split_result_file_line( $line );

			if ( $item_name === false || $categorization === false )
				continue;

			unset( $items_to_scan_batch[ $item_name ] );

		}

		fclose( $scan_results_temp_handle );

	}

	/**
	 * Override of the force shutdown method to adjust the shutdown time in this step
	 * 
	 * @return void
	 */
	protected function force_shutdown_if_needed() {

		// Check if more than 40% of the script execution time has passed since the start of the script.
		self::$exit_time = 0.4 * self::$current_script_max_execution_time + self::$current_script_start_time;

		if ( time() >= self::$exit_time ) {
			$this->force_shutdown();
		}

	}

}