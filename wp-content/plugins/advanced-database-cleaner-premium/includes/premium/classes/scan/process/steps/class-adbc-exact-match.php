<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Exact Match Step Class.
 * 
 * This class is responsible for the exact match step in the scan process, which is responsible for categorizing the items based on their exact match in the files content.
 * It implements a smart/fast resuming logic in case of shutdowns.
 * It uses an optimized logic to scan the files content in chunks and items in batches to avoid memory usage issues.
 */
class ADBC_Exact_Match extends ADBC_Local_Scan {

	private $files_to_scan_file_handle = null;

	private $local_temp_results_file_handle = null;

	private $effective_total_files = 0;

	private $start_time = 0;

	/**
	 * Run the exact match step.
	 */
	public function run() {

		// Open the files_to_scan and temp_scan_results files
		$this->files_to_scan_file_handle = ADBC_Files::instance()->get_file_handle( $this->files_to_scan_file_path, "r" );
		$this->local_temp_results_file_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, "a" );

		if ( $this->files_to_scan_file_handle === false || $this->local_temp_results_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// Calculate the effective total files to scan for the progress calculation
		$number_of_batches = ceil( $this->scan_info_instance->scan_info['local']['exact_match']['total_items'] / $this->scan_info_instance->scan_info['batch_size'] );
		$this->effective_total_files = $this->scan_info_instance->scan_info['local']['collecting_files']['collected_files'] * $number_of_batches;

		// Set the start time for scan info updates
		$this->start_time = time();

		// Start processing the items in batches in all the files paths taking into consideration the resuming logic for shutdowns
		do {

			// Get the next batch of items to scan
			$items_to_scan_batch = $this->get_next_items_batch();

			// Break the loop if there are no items to scan in the current batch
			if ( empty( $items_to_scan_batch ) )
				break;

			// Flag the already categorized items in the current batch if we are resuming the scan
			if ( $this->scan_info_instance->scan_info['continue_scan'] === true )
				$this->flag_already_categorized_items( $items_to_scan_batch );

			// Always start from the first line of the files_to_scan file
			rewind( $this->files_to_scan_file_handle );
			$current_line_number = 0;

			// Loop over the files_to_scan file starting from the line following the last processed line
			while ( ( $file_path = fgets( $this->files_to_scan_file_handle ) ) !== false ) {

				// reduce CPU usage between files
				$this->reduce_cpu_usage();

				$current_line_number++;

				// Skip the file if it was already processed
				if ( $current_line_number <= $this->get_current_file_line() )
					continue;

				// break the loop if all items in the batch are already categorized
				if ( $this->are_all_batch_items_categorized( $items_to_scan_batch ) )
					break;

				// If we are here, then we are processing the batch in a new file
				$file_path = rtrim( $file_path, "\r\n" );

				// Get the file content if the file size is less than 2MB else we should get the file content in chunks
				$file_size = ADBC_Files::instance()->size( $file_path );

				// Skip the file if it's too big
				if ( $file_size > self::ADBC_ABNORMAL_FILE_SIZE ) {
					$this->set_current_file_line( $current_line_number );
					continue;
				}

				// Search for the items in the current file using chunks if the file size is bigger than the max file size setting
				$should_search_by_chunks = $file_size > $this->file_content_chunk_size ? true : false;

				$this->search_for_items_batch_in_file( $items_to_scan_batch, $file_path, $should_search_by_chunks );

				// Mark the current file line number as the last processed one and reset the current item line number
				$this->set_current_file_line( $current_line_number );
				$this->set_current_item_line( 0 );

			}

			// Reset the file line to 0 after processing all the files for the next batch
			$this->set_current_file_line( 0 );

			// Mark this batch as processed
			$this->set_current_batch_number( $this->get_current_batch_number() + 1 );

			// reset the continue scan flag for next batch (to only categorize the items in case of resuming)
			$this->scan_info_instance->scan_info['continue_scan'] = false;

		} while ( count( $items_to_scan_batch ) > 0 );

		// Close the files
		fclose( $this->files_to_scan_file_handle );
		fclose( $this->local_temp_results_file_handle );

		// update progress to 100%
		$this->scan_info_instance->scan_info['local']['exact_match']['progress'] = 100;

		// Update the scan info
		$this->update_scan_info();

	}

	/**
	 * Search for the items in the current file content
	 * 
	 * @param array $items_to_scan_batch The items to search for in the file content
	 * @param string $file_path The file path to search in
	 * @param bool $should_search_by_chunks Whether to search in the file content by chunks or not
	 */
	private function search_for_items_batch_in_file( &$items_to_scan_batch, $file_path, $should_search_by_chunks = false ) {

		if ( ! $should_search_by_chunks ) {

			$file_content = ADBC_Files::instance()->get_contents( $file_path );

			if ( $file_content === false )
				return;

			$this->search_for_items_batch_in_file_content_chunk( $items_to_scan_batch, $file_content, $file_path );

		} else {

			$file_handle = ADBC_Files::instance()->get_file_handle( $file_path, 'rb' );

			if ( ! $file_handle )
				return;

			while ( ! feof( $file_handle ) ) {

				$chunk_content = fread( $file_handle, $this->file_content_chunk_size );

				if ( $chunk_content !== "" )
					$this->search_for_items_batch_in_file_content_chunk( $items_to_scan_batch, $chunk_content, $file_path );

			}

			fclose( $file_handle );

		}

	}

	/**
	 * Search for the items in the current file content chunk
	 * 
	 * @param array $items_to_scan_batch The items to search for in the file content
	 * @param string $chunk_content The file content chunk to search in
	 * @param string $file_path The file path to search in
	 */
	private function search_for_items_batch_in_file_content_chunk( &$items_to_scan_batch, &$chunk_content, $file_path ) {

		$current_item_line_number = $this->scan_info_instance->scan_info['batch_size'] * $this->get_current_batch_number();

		foreach ( $items_to_scan_batch as $item => $categorized ) {

			// reduce CPU usage between items
			$this->reduce_cpu_usage();

			// Force do_when_shutdown execution if we are about to have a max_execution_time error
			$this->force_shutdown_if_needed();

			$current_item_line_number++;

			// update scan info each 2 seconds
			if ( time() - $this->start_time >= 2 ) {
				$this->update_progress();
				$this->update_scan_info();
				$this->start_time = time();
			}

			// if we still not reached the last processed item line number or the item was already processed, then skip it
			if ( $current_item_line_number <= $this->get_current_item_line() || $categorized )
				continue;

			$item_to_search_for = $item;

			// If the item type is transient remove the prefix 
			if ( $this->scan_info_instance->items_type === 'transients' )
				$item_to_search_for = ADBC_Common_Utils::strip_transient_prefix( $item );

			// Check if the item is found in the current chunk (case-insensitive)
			$item_found = stripos( $chunk_content, $item_to_search_for ) !== false;

			// if the item was found, then save it to the temp results file
			if ( $item_found ) {

				// Get the slug from the file path
				$slug = $this->get_slug_from_file_path( $file_path );
				$belong_to = [ "l" => [ $slug . ":100" ] ];

				// Write the categorization to the temp results file
				fwrite( $this->local_temp_results_file_handle, $item . "|" . json_encode( $belong_to ) . "\n" );

				// Mark the item as categorized
				$items_to_scan_batch[ $item ] = true;

			}

			// Mark the current item line number as the last processed one
			$this->set_current_item_line( $current_item_line_number );

		}

	}

	/**
	 * Get the next batch of items to scan
	 * 
	 * @return array The next batch of items to scan
	 */
	private function get_next_items_batch() {

		// The offset is always the end of the last processed batch
		$offset = $this->get_current_batch_number() * $this->scan_info_instance->scan_info['batch_size'];

		$items_batch = $this->get_batch_items_to_scan( $offset );

		return $items_batch;

	}

	/**
	 * Get the items to scan batch starting from the given offset
	 * 
	 * @param int $offset The offset to start from
	 * 
	 * @return array The items to scan in the current batch or false if the file is not found
	 */
	private function get_batch_items_to_scan( $offset ) {

		$handle = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'r' );

		if ( ! $handle )
			throw new Exception( "Unable to open the file." );

		$items_batch = [];
		$current_line = 0;

		// Loop over the items to scan file to get the next batch of items starting from the offset
		while ( ( ( $line = fgets( $handle ) ) !== false ) && $current_line < $offset + $this->scan_info_instance->scan_info['batch_size'] ) {

			if ( $current_line >= $offset ) {
				$line = rtrim( $line, "\r\n" );
				$items_batch[ $line ] = false; // Mark the item as not categorized by default
			}

			$current_line++;

		}

		fclose( $handle );

		return $items_batch;

	}

	/**
	 * Mark the already categorized items in the current batch from the temp results file
	 * 
	 */
	private function flag_already_categorized_items( &$items_to_scan_batch ) {

		// open the file in mode read
		$handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, "r" );

		if ( $handle === false )
			throw new Exception( "Unable to open the file." );

		// loop over the temp results file and remove the already categorized items
		while ( ( $line = fgets( $handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			[ $item_name, $belong_to_json ] = ADBC_Scan_Utils::split_result_file_line( $line );

			// if the item is already categorized, then flag it as true
			if ( array_key_exists( $item_name, $items_to_scan_batch ) )
				$items_to_scan_batch[ $item_name ] = true;

		}

		fclose( $handle );

	}

	private function update_progress() {
		$total_files_processed = $this->get_current_batch_number() * $this->get_collected_files_count() + $this->get_current_file_line();
		$progress = round( $total_files_processed / $this->effective_total_files * 100, 2 );
		$this->set_progress( $progress );
	}

	private function set_progress( $progress ) {
		$this->scan_info_instance->scan_info['local']['exact_match']['progress'] = $progress;
	}

	private function get_current_batch_number() {
		return $this->scan_info_instance->scan_info['local']['exact_match']['batch_number'];
	}

	private function set_current_batch_number( $batch_number ) {
		$this->scan_info_instance->scan_info['local']['exact_match']['batch_number'] = $batch_number;
	}

	private function get_current_item_line() {
		return $this->scan_info_instance->scan_info['local']['exact_match']['item_line'];
	}

	private function set_current_item_line( $item_line ) {
		$this->scan_info_instance->scan_info['local']['exact_match']['item_line'] = $item_line;
	}

	private function get_current_file_line() {
		return $this->scan_info_instance->scan_info['local']['exact_match']['file_line'];
	}

	private function set_current_file_line( $file_line ) {
		$this->scan_info_instance->scan_info['local']['exact_match']['file_line'] = $file_line;
	}

	private function are_all_batch_items_categorized( &$items_to_scan_batch ) {
		foreach ( $items_to_scan_batch as $item => $categorized ) {
			if ( ! $categorized )
				return false;
		}
		return true;
	}

}