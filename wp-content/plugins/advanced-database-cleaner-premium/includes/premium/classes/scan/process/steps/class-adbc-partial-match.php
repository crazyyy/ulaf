<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Partial match step class.
 * 
 * This class is responsible for the partial match step in the scan process, which is responsible for categorizing the items based on their best partial match in the files content.
 * It implements a smart/fast resuming logic in case of shutdowns.
 * It uses an optimized logic to scan the files content in chunks and items in batches to avoid memory usage issues.
 */
class ADBC_Partial_Match extends ADBC_Local_Scan {

	private $files_to_scan_file_handle = null;

	private $local_temp_results_file_handle = null;

	private $effective_total_files = 0;

	private $start_time = 0;

	/**
	 * Run the partial match step.
	 */
	public function run() {

		// Open the files_to_scan and temp_scan_results files
		$this->files_to_scan_file_handle = ADBC_Files::instance()->get_file_handle( $this->files_to_scan_file_path, "r" );
		$this->local_temp_results_file_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, "a" );

		if ( ! $this->files_to_scan_file_handle || ! $this->local_temp_results_file_handle )
			throw new Exception( "Unable to open the files." );

		// Calculate the effective total files to scan for the progress calculation
		$number_of_batches = ceil( $this->scan_info_instance->scan_info['local']['partial_match']['total_items'] / $this->scan_info_instance->scan_info['batch_size'] );
		$this->effective_total_files = $this->scan_info_instance->scan_info['local']['collecting_files']['collected_files'] * $number_of_batches;

		// Set the start time for scan info updates
		$this->start_time = time();

		// Start processing the items in batches in all the files paths taking into consideration the resuming logic for shutdowns
		do {

			// fill the partial_match_batch_results array with the next batch of items
			$this->get_next_items_batch();

			// Break the loop if there are no items to process
			if ( empty( $this->scan_info_instance->partial_match_batch_results ) )
				break;

			// Map the already categorized items if we are resuming the scan
			if ( $this->scan_info_instance->scan_info['continue_scan'] === true && ADBC_Files::instance()->exists( $this->partial_match_results_file_path ) )
				$this->map_batch_saved_results_before_shutdown();

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

				$this->search_for_items_batch_in_file( $file_path, $should_search_by_chunks );

				// Mark the current file line number as the last processed one and reset the current item line number
				$this->set_current_file_line( $current_line_number );
				$this->set_current_item_line( 0 );

			}

			// Save the batch results to the temp results file
			$this->save_batch_results_to_temp_results_file();

			// Reset the file line to 0 after processing all the files for the next batch
			$this->set_current_file_line( 0 );

			// Mark this batch as processed
			$this->set_current_batch_number( $this->get_current_batch_number() + 1 );

			// reset the continue scan flag for next batch (to only categorize the items in case of resuming)
			$this->scan_info_instance->scan_info['continue_scan'] = false;

		} while ( count( $this->scan_info_instance->partial_match_batch_results ) > 0 );

		// Close the files
		fclose( $this->files_to_scan_file_handle );
		fclose( $this->local_temp_results_file_handle );

		// update progress to 100%
		$this->scan_info_instance->scan_info['local']['partial_match']['progress'] = 100;

		// Update the scan info
		$this->update_scan_info();

	}

	/**
	 * Save the partial match results for a batch to the temp results file
	 * 
	 */
	private function save_batch_results_to_temp_results_file() {

		foreach ( $this->scan_info_instance->partial_match_batch_results as $item_name => $partial_match_result ) {

			$final_partial_match_result = [ "l" => []];

			if ( $partial_match_result["nb_chars_found"] > 0 ) {

				$slug = $this->get_slug_from_file_path( $partial_match_result["file"] );
				$percentage = round( ( $partial_match_result["nb_chars_found"] * 100 ) / strlen( $item_name ), 2 ); // percentage of (found_chars/item_length) rounded to 2 decimal
				$percentage = $percentage < 35 ? 35 : $percentage; // set the minimum percentage to 35 to fix differences between the calculation in the substring extraction and the final result calculation
				$final_partial_match_result = [ "l" => [ $slug . ":" . $percentage ] ];

			}

			fwrite( $this->local_temp_results_file_handle, $item_name . "|" . json_encode( $final_partial_match_result ) . "\n" );

		}

	}


	/**
	 * Search for the items in the current file content
	 * 
	 * @param array $items_to_scan_batch The items to search for in the file content
	 * @param string $file_path The file path to search in
	 * @param bool $should_search_by_chunks Whether to search in the file content by chunks or not
	 */
	private function search_for_items_batch_in_file( $file_path, $should_search_by_chunks = false ) {

		if ( ! $should_search_by_chunks ) {

			$file_content = ADBC_Files::instance()->get_contents( $file_path );

			if ( $file_content === false )
				return;

			$this->search_for_items_batch_in_file_content_chunk( $file_content, $file_path );

		} else {

			$file_handle = ADBC_Files::instance()->get_file_handle( $file_path, 'rb' );

			if ( ! $file_handle )
				return;

			while ( ! feof( $file_handle ) ) {

				$chunk_content = fread( $file_handle, $this->file_content_chunk_size );

				if ( $chunk_content !== "" )
					$this->search_for_items_batch_in_file_content_chunk( $chunk_content, $file_path );

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
	private function search_for_items_batch_in_file_content_chunk( &$chunk_content, $file_path ) {

		$current_item_line_number = $this->scan_info_instance->scan_info['batch_size'] * $this->get_current_batch_number();

		foreach ( $this->scan_info_instance->partial_match_batch_results as $item_name => $current_result ) {

			// reduce CPU usage between items
			$this->reduce_cpu_usage();

			// Force do_when_shutdown execution if we are about to have a max_execution_time error
			$this->force_shutdown_if_needed();

			$current_item_line_number++;

			$item_to_search_for = $item_name;

			// If the item type is transient remove the prefix 
			if ( $this->scan_info_instance->items_type === 'transients' )
				$item_to_search_for = ADBC_Common_Utils::strip_transient_prefix( $item_name );

			$item_name_length = strlen( $item_to_search_for );

			// update scan info each 2 seconds
			if ( time() - $this->start_time >= 2 ) {
				$this->update_progress();
				$this->update_scan_info();
				$this->start_time = time();
			}

			// if we still not reached the last processed item line number or the item length is less than 4 characters or the percentage is the max possible then skip the item
			if ( $current_item_line_number <= $this->get_current_item_line() || $current_result["nb_chars_found"] >= $item_name_length - 1 )
				continue;

			// Search for the items substrings longer than the current best number found
			$nb_chars_found = $this->partial_match( $item_to_search_for, $chunk_content, $current_result["nb_chars_found"] );

			// if the number of chars found is more than the existing one it means we have a better match
			if ( $nb_chars_found > $current_result["nb_chars_found"] ) {
				$this->scan_info_instance->partial_match_batch_results[ $item_name ]["nb_chars_found"] = $nb_chars_found;
				$this->scan_info_instance->partial_match_batch_results[ $item_name ]["file"] = $file_path;
			}

			// Mark the current item line number as the last processed one
			$this->set_current_item_line( $current_item_line_number );

		}

	}

	/**
	 * Search if there is a better partial match of the item name in the file content.
	 * 
	 * @param string $item_name The item name to search for
	 * @param string $content The file content to search in
	 * @param int $best_nb_chars_found The best number of chars found so far
	 * 
	 * @return int The number of chars found in the content
	 */
	function partial_match( $item_name, &$content, $best_nb_chars_found ) {

		$item_name_length = strlen( $item_name );

		// get the prefix nb of chars to start from
		$prefix_min_nb_chars = ( (int) ( ( 35 * $item_name_length ) / 100 ) );
		$prefix_nb_char_to_start_from = $best_nb_chars_found >= $prefix_min_nb_chars ? $best_nb_chars_found : $prefix_min_nb_chars - 1;

		// get the suffix nb of chars to start from
		$suffix_min_nb_chars = ( (int) ( ( 75 * $item_name_length ) / 100 ) );
		$suffix_nb_char_to_start_from = $best_nb_chars_found >= $suffix_min_nb_chars ? $best_nb_chars_found : $suffix_min_nb_chars - 1;

		// search for the prefix and suffix nb of chars +1 in the content as an entry point before starting the search
		$found_prefix = stripos( $content, substr( $item_name, 0, $prefix_nb_char_to_start_from + 1 ) );
		$found_suffix = stripos( $content, substr( $item_name, -$suffix_nb_char_to_start_from - 1 ) );

		// if the prefix or suffix is found in the content then return the percentage
		if ( $found_prefix === false && $found_suffix === false )
			return $best_nb_chars_found;

		// set the suffix and prefix number of chars found so far
		$prefix_nb_char_found = $found_prefix !== false ? $prefix_nb_char_to_start_from + 1 : 0;
		$suffix_nb_char_found = $found_suffix !== false ? $suffix_nb_char_to_start_from + 1 : 0;

		// loop over the item name starting from the best number of chars found or the first 3 chars if no best number of chars found
		for ( $i = $item_name_length - 1, $j = 1; $i > $prefix_nb_char_found, $j < $item_name_length - $suffix_nb_char_found; $i--, $j++ ) {

			// Search for this prefix substring in the content, if found return, no need to search for the suffix
			if ( $found_prefix !== false ) {
				$prefix_substring = substr( $item_name, 0, $i );
				if ( stripos( $content, $prefix_substring, $found_prefix ) !== false )
					return strlen( $prefix_substring );
			}

			// Search for this suffix substring in the content
			if ( $found_suffix !== false ) {
				$suffix_substring = substr( $item_name, $j );
				if ( $found_suffix && stripos( $content, $suffix_substring ) !== false )
					return strlen( $suffix_substring );
			}

		}

		// if we are here, it means no match was found
		return max( $prefix_nb_char_found, $suffix_nb_char_found, $best_nb_chars_found );

	}

	/**
	 * Fill the partial_match_batch_results array with the next batch of items
	 *
	 */
	private function get_next_items_batch() {

		$handle = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'r' );

		if ( $handle === false )
			throw new Exception( "Unable to open the file." );

		// reset the partial_match_batch_results array
		$this->scan_info_instance->partial_match_batch_results = [];
		$offset = $this->get_current_batch_number() * $this->scan_info_instance->scan_info['batch_size'];
		$current_line = 0;

		// loop over the items_to_scan file and fill the partial_match_batch_results array with the next batch of items
		while ( ( ( $line = fgets( $handle ) ) !== false ) && $current_line < $offset + $this->scan_info_instance->scan_info['batch_size'] ) {

			if ( $current_line >= $offset ) {

				$item_name = rtrim( $line, "\r\n" );

				$this->scan_info_instance->partial_match_batch_results[ $item_name ] = [ 
					"file" => "",
					"nb_chars_found" => 0,
				];

			}

			$current_line++;

		}

		fclose( $handle );

	}

	/**
	 * Map the already saved partial match results before shutdown with the current batch results.
	 * 
	 */
	private function map_batch_saved_results_before_shutdown() {

		// open the file in mode read
		$handle = ADBC_Files::instance()->get_file_handle( $this->partial_match_results_file_path, "r" );

		if ( $handle === false )
			throw new Exception( "Unable to open the file." );

		// loop over the temp results file and map already saved results
		while ( ( $line = fgets( $handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			[ $item_name, $partial_match_result ] = ADBC_Scan_Utils::split_result_file_line( $line );

			if ( ! isset( $this->scan_info_instance->partial_match_batch_results[ $item_name ] ) )
				continue;

			$this->scan_info_instance->partial_match_batch_results[ $item_name ] = $partial_match_result;

		}

		fclose( $handle );

		// delete the temp results file since we don't need it anymore
		@unlink( $this->partial_match_results_file_path );

	}

	private function update_progress() {
		$total_files_processed = $this->get_current_batch_number() * $this->get_collected_files_count() + $this->get_current_file_line();
		$progress = round( $total_files_processed / $this->effective_total_files * 100, 2 );
		$this->set_progress( $progress );
	}

	private function set_progress( $progress ) {
		$this->scan_info_instance->scan_info['local']['partial_match']['progress'] = $progress;
	}

	private function get_current_batch_number() {
		return $this->scan_info_instance->scan_info['local']['partial_match']['batch_number'];
	}

	private function set_current_batch_number( $batch_number ) {
		$this->scan_info_instance->scan_info['local']['partial_match']['batch_number'] = $batch_number;
	}

	private function get_current_item_line() {
		return $this->scan_info_instance->scan_info['local']['partial_match']['item_line'];
	}

	private function set_current_item_line( $item_line ) {
		$this->scan_info_instance->scan_info['local']['partial_match']['item_line'] = $item_line;
	}

	private function get_current_file_line() {
		return $this->scan_info_instance->scan_info['local']['partial_match']['file_line'];
	}

	private function set_current_file_line( $file_line ) {
		$this->scan_info_instance->scan_info['local']['partial_match']['file_line'] = $file_line;
	}

}