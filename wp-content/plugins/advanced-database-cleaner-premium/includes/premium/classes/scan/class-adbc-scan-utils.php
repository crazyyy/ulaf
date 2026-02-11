<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Scan Utils.
 * 
 * This class provides methods for the scan process.
 */
class ADBC_Scan_Utils {

	/**
	 * Edit scan results based on the user's manual categorization (Called by the endpoints).
	 * 
	 * @param WP_REST_Request $request_data The request data containing the manual categorization and selected items.
	 * @param string $action_type The type of action to perform. "edit_scan_results_tables", "edit_scan_results_options", etc.
	 * @param string $items_type The type of items to edit. "tables", "options", "posts_meta", etc.
	 * 
	 * @return WP_REST_Response The response with the result of the operation.
	 */
	public static function edit_scan_results( WP_REST_Request $request_data, $action_type, $items_type ) {

		// Verify if there is a scan in progress. If there is, return an error.
		if ( ADBC_Scan_Utils::is_scan_exists( $items_type ) )
			return ADBC_Rest::error( __( 'A scan is in progress. Please wait until it finishes before performing this action.', 'advanced-database-cleaner' ), ADBC_Rest::BAD_REQUEST );

		// Validate the $manual_categorization
		$manual_categorization = $request_data->get_param( 'manualCategorization' );
		$validation_answer = ADBC_Common_Validator::is_manual_categorization_valid( $manual_categorization );

		if ( $validation_answer !== true )
			return ADBC_Rest::error( $validation_answer, ADBC_Rest::BAD_REQUEST );

		$validation_answer = ADBC_Common_Validator::validate_endpoint_action_data( $action_type, $items_type, $request_data, false );

		// If $validation_answer is not an array => the validation failed and we have an error message.
		if ( ! is_array( $validation_answer ) )
			return ADBC_Rest::error( $validation_answer, ADBC_Rest::BAD_REQUEST );

		// Exclude hardcoded items from selected items. Because hardcoded will override manual categorization anyway.
		$cleaned_selected = ADBC_Hardcoded_Items::instance()->exclude_hardcoded_items_from_selected_items( $validation_answer, $items_type );

		// Create an array containing only the items names
		$items_names = array_column( $cleaned_selected, 'name' );

		// Proceed to file categorization correction.
		ADBC_Scan_Utils::manually_categorize_items( $items_type, $items_names, $manual_categorization );

		// Update the slug name dictionary with the new manual categorization if there are selected items.
		ADBC_Dictionary::update_slug_name_dictionary_for_manual( $manual_categorization, $items_type );

		$not_processed = 0; // Always return 0 in this case.

		return ADBC_Rest::success( "", $not_processed );
	}

	/**
	 * Edit scan results based on the user's manual categorization (Called internally).
	 * 
	 * @param string $items_type The type of items to correct. "tables", "options", "cron_jobs", etc.
	 * @param array $selected_items The list of items selected by the user.
	 * @param string $manual_categorization The manual categorization value set by the user.
	 * 
	 * @return void
	 */
	public static function manually_categorize_items( $items_type, $selected_items, $manual_categorization ) {

		if ( empty( $selected_items ) )
			return; // Nothing to correct.

		$categorization_category = $manual_categorization['type']; // We assume $manual_categorization has been validated before.
		$encoded_belongs_to = self::get_encoded_manual_belongs_to( $manual_categorization ); // Encode the manual belongs to value set by the user.
		$corrected_items = []; // The list of items that were corrected.
		$scan_results_file_path = ADBC_Scan_Paths::get_scan_results_path( $items_type );

		// Create the scan results file if it doesn't exist.
		ADBC_Files::instance()->create_file( $scan_results_file_path );

		// Check if opened successfully.
		$scan_results_file_handle = ADBC_Files::instance()->get_file_handle( $scan_results_file_path, 'r' );
		if ( $scan_results_file_handle === false )
			throw new Exception( "Cannot open the file!" );

		$temp_results_file_path = ADBC_Scan_Paths::get_manual_categorization_results_temp_file_path( $items_type );
		$temp_result_file_handle = ADBC_Files::instance()->get_file_handle( $temp_results_file_path, 'w' );

		// Check if the temp file was created successfully.
		if ( $temp_result_file_handle === false )
			throw new Exception( "Cannot create the file!" );

		while ( ( $line = fgets( $scan_results_file_handle ) ) !== false ) {

			list( $item_name, $belong_to_json ) = self::split_result_file_line( $line );
			// Update the line if the item was selected and manually categorized.
			if ( in_array( $item_name, $selected_items ) ) {
				// Update the line only if we are not in "u" category, which means the user wants to set the items as "not categorized"
				// In this case, just skip the line and don't write it to the temp file.
				if ( $categorization_category != 'u' ) {
					$updated_line = $item_name . "|" . $encoded_belongs_to;
					fwrite( $temp_result_file_handle, $updated_line . "\n" );
				}
				$corrected_items[] = $item_name; // Add the item to the corrected items list.
			} else {
				fwrite( $temp_result_file_handle, $line ); // Write the line as is if the item is not selected.
			}

		}

		fclose( $scan_results_file_handle );
		fclose( $temp_result_file_handle );

		// Rename the temp file to the scan results file.
		if ( ADBC_Files::instance()->exists( $temp_results_file_path ) && ! rename( $temp_results_file_path, $scan_results_file_path ) )
			throw new Exception( "Cannot rename the file!" );

		$remaining_items = array_diff( $selected_items, $corrected_items );
		self::save_remaining_manual_categorization( $remaining_items, $categorization_category, $encoded_belongs_to, $scan_results_file_path );

		// Send items to server if user choose to send them.
		self::send_manual_categorization_to_server_if_needed( $items_type, $selected_items, $manual_categorization );

	}

	/**
	 * Count the number of items that were not scanned in the given list.
	 * 
	 * @param string $items_type The type of items to count. "tables", "options", "cron_jobs", etc.
	 * @param array  $items_list The list of items to count. (In practice: [0 => 'name1', 1 => 'name2', ...])
	 * 
	 * @return int The number of items that were not scanned.
	 */
	public static function count_not_scanned_items_in_list( $items_type, &$items_list ) {

		if ( empty( $items_list ) )
			return 0; // Nothing to count.

		// ADBC hardcoded items (exact matches only).
		$adbc_items = ADBC_Hardcoded_Items::instance()->get_adbc_items( $items_type );
		$wp_hardcoded_items = ADBC_Hardcoded_Items::instance()->get_wordpress_items( $items_type );

		// Remove WordPress core and ADBC hardcoded items from the list.
		foreach ( $items_list as $index => $item ) {

			// WordPress core (exact + rule-based for transients).
			if ( ADBC_Hardcoded_Items::instance()->is_item_belongs_to_wp_core( $item, $items_type, $wp_hardcoded_items ) ) {
				unset( $items_list[ $index ] );
				continue;
			}

			// ADBC hardcoded items (exact matches).
			if ( isset( $adbc_items[ $item ] ) ) {
				unset( $items_list[ $index ] );
				continue;
			}

		}

		$scan_results_file_path = ADBC_Scan_Paths::get_scan_results_path( $items_type );
		$scan_results_file_handle = ADBC_Files::instance()->get_file_handle( $scan_results_file_path, 'r' );

		// After unsetting hardcoded items, return the length of the items list if the scan results file cannot be opened.
		if ( $scan_results_file_handle === false )
			return count( $items_list );

		if ( empty( $items_list ) )
			return 0; // Nothing to count.

		// Optimization: Build a lookup map once: item_name => [indexes...]
		$indexes_by_item_name = [];
		foreach ( $items_list as $index => $item )
			$indexes_by_item_name[ $item ][] = $index;

		// Read the scan results file line by line and remove the items that were scanned.
		while ( ( $line = fgets( $scan_results_file_handle ) ) !== false ) {

			list( $item_name, $belongs_to_json ) = self::split_result_file_line( $line );

			if ( isset( $indexes_by_item_name[ $item_name ] ) ) {

				// Remove all occurrences of the item from the items list.
				foreach ( $indexes_by_item_name[ $item_name ] as $idx )
					unset( $items_list[ $idx ] );

				// Prevent re-processing the same item_name if it appears again in the file.
				unset( $indexes_by_item_name[ $item_name ] );

				// Early exit if nothing left.
				if ( empty( $items_list ) )
					break;

			}
		}

		fclose( $scan_results_file_handle );

		return count( $items_list );

	}

	/**
	 * Update the scan results file after deleting items.
	 * 
	 * @param string $items_type The type of items to update. "tables", "options", "cron_jobs", etc.
	 * @param array $items_list The list of items that were selected to be deleted.
	 * @param array $items_not_deleted The list of items that were not deleted.
	 * 
	 * @return void
	 */
	public static function update_scan_results_file_after_deletion( $items_type, $items_list, $items_not_deleted = [] ) {

		// Delete items_not_deleted from items_list to update only the items that were deleted.
		$deleted_items = array_diff( $items_list, $items_not_deleted );

		// Remove items that still have duplicates in the database to keep their categorization
		$items_that_still_have_duplicates = [];
		switch ( $items_type ) {
			case 'options':
				$items_that_still_have_duplicates = ADBC_Options::get_options_names_that_exists_from_list( $deleted_items );
				break;
			case 'cron_jobs':
				$items_that_still_have_duplicates = ADBC_Cron_Jobs::get_cron_jobs_names_that_exists_from_list( $deleted_items );
				break;
			case 'transients':
				$items_that_still_have_duplicates = ADBC_Transients::get_transients_names_that_exists_from_list( $deleted_items );
				break;
			case 'posts_meta':
				$items_that_still_have_duplicates = ADBC_Posts_Meta::get_posts_meta_names_that_exists_from_list( $deleted_items );
				break;
			case 'users_meta':
				$items_that_still_have_duplicates = ADBC_Users_Meta::get_users_meta_names_that_exists_from_list( $deleted_items );
				break;
		}

		$deleted_items = array_diff( $deleted_items, $items_that_still_have_duplicates );

		if ( empty( $deleted_items ) )
			return; // Nothing to correct.

		$scan_results_file_path = ADBC_Scan_Paths::get_scan_results_path( $items_type );

		if ( ADBC_Files::instance()->exists( $scan_results_file_path ) === false )
			return; // Nothing to update if the scan results file doesn't exist.

		$scan_results_file_handle = ADBC_Files::instance()->get_file_handle( $scan_results_file_path, 'r' );

		if ( $scan_results_file_handle === false ) {
			ADBC_Logging::log_error( "Cannot open the the scan results file!", __METHOD__, __LINE__ );
			return;
		}

		// Create the temp file to store the updated scan results.
		$temp_updated_file_path = ADBC_Scan_Paths::get_updated_scan_results_after_deletion_temp_file_path( $items_type );
		$temp_updated_file_handle = ADBC_Files::instance()->get_file_handle( $temp_updated_file_path, 'w' );

		if ( $temp_updated_file_handle === false ) {
			ADBC_Logging::log_error( "Cannot create the updated scan results temp file!", __METHOD__, __LINE__ );
			return;
		}

		while ( ( $line = fgets( $scan_results_file_handle ) ) !== false ) {

			list( $item_name, $belong_to_json ) = self::split_result_file_line( $line );

			if ( ! in_array( $item_name, $deleted_items ) )
				fwrite( $temp_updated_file_handle, $line ); // Write the line as is if the item is not deleted.

		}

		fclose( $scan_results_file_handle );
		fclose( $temp_updated_file_handle );

		// Rename the temp file to the scan results file.
		if ( ADBC_Files::instance()->exists( $temp_updated_file_path ) && ! rename( $temp_updated_file_path, $scan_results_file_path ) ) {
			ADBC_Logging::log_error( "Cannot rename the updated scan results file!", __METHOD__, __LINE__ );
			return;
		}
	}

	/**
	 * Get the encoded manual belongs to value set by the user.
	 * 
	 * @param string $manual_categorization The manual categorization value.
	 * @return string The encoded manual belongs to value.
	 */
	private static function get_encoded_manual_belongs_to( $manual_categorization ) {

		// We assume $manual_categorization has been validated before.
		$category_type = $manual_categorization['type'];
		$slug = $manual_categorization['slug'];
		$correction_value = [];

		switch ( $category_type ) {
			case 'w':
				$correction_value[] = 'w:w';
				break;
			case 't':
			case 'p':
				$correction_value[] = $category_type . ':' . $slug;
				break;
			case 'o':
				// Keep the original empty array.
				break;
			default:
				// Do nothing.
				break;
		}

		$correction = [ 'm' => $correction_value ];
		return json_encode( $correction );
	}

	/**
	 * Save the remaining items that were manually categorized but not found in the scan results file.
	 * 
	 * @param array $remaining_items The list of remaining items.
	 * @param string $categorization_category The manual categorization category. "w", "t", "p", "o" or "u".
	 * @param string $encoded_belongs_to The encoded belongs to value set by the user.
	 * @param string $scan_results_file_path The scan results file path to write the remaining items to.
	 * @return void
	 */
	private static function save_remaining_manual_categorization( $remaining_items, $categorization_category, $encoded_belongs_to, $scan_results_file_path ) {

		if ( empty( $remaining_items ) || $categorization_category == 'u' )
			return; // Nothing to save.

		$scan_results_file_handle = ADBC_Files::instance()->get_file_handle( $scan_results_file_path, 'a' );
		if ( $scan_results_file_handle === false )
			throw new Exception( "Cannot open the file!" );

		foreach ( $remaining_items as $item_name ) {
			$line = $item_name . "|" . $encoded_belongs_to . "\n";
			fwrite( $scan_results_file_handle, $line );
		}

		fclose( $scan_results_file_handle );
	}

	/**
	 * Send the manually categorized items to the server if the user choose to send them.
	 * 
	 * @param string $items_type The type of items to send. "tables", "options", "cron_jobs", etc.
	 * @param array $selected_items The list of items selected by the user.
	 * @param string $manual_categorization The manual categorization value set by the user.
	 */
	private static function send_manual_categorization_to_server_if_needed( $items_type, $selected_items, $manual_categorization ) {

		$category_type = $manual_categorization['type'];
		$slug = $manual_categorization['slug'];
		$should_send = $manual_categorization['send_to_server'];

		// Don't send items if the user choose not to send them or if the category is "u" or "o".
		if ( $should_send === '0' || in_array( $category_type, [ 'u', 'o' ] ) )
			return;

		$corrections = "";
		foreach ( $selected_items as $item_name ) {
			$corrections .= $item_name . "|" . $category_type . ":" . $slug . "\n";
		}

		$data = array(
			'website' => ADBC_WEBSITE_HOME_URL,
			'items_type' => $items_type,
			'corrections' => $corrections,
		);

		ADBC_Remote_Request::send_request( '/scan/correction', $data, 'POST', false );

	}

	/**
	 * Split a line from the scan results file into item name and scan results.
	 * 
	 * @param string $line The line to split.
	 * @return array An array containing the item name and the scan results. An array of false values if the line is not valid.
	 */
	public static function split_result_file_line( $line ) {

		$last_separator_position = strrpos( $line, '|' );

		if ( $last_separator_position === false )
			return [ false, false ];

		$item_name = substr( $line, 0, $last_separator_position );
		$scan_results = substr( $line, $last_separator_position + 1 ); // +1 to skip the delimiter itself

		$scan_results_decoded = json_decode( $scan_results, true );
		if ( ! is_array( $scan_results_decoded ) )
			return [ false, false ];

		return [ $item_name, $scan_results_decoded ];

	}

	/**
	 * Get the scan requests balance from the API server.
	 * 
	 * @return array The server response.
	 */
	public static function get_scan_balance() {

		$request_route = '/scan/balance';

		$json_data = [ 'website' => ADBC_WEBSITE_HOME_URL ];

		$server_response = ADBC_Remote_Request::send_request( $request_route, $json_data, 'POST', true, ADBC_Remote_Scan::GET_SCAN_BALANCE_TIMEOUT );

		// if the server didn't answer our request or an error occurred in wordpress, we set a generic code
		if ( $server_response['success'] === false && ! isset( $server_response['failure_code'] ) )
			$server_response['failure_code'] = ADBC_Remote_Scan::GENERIC_ERROR;

		if ( $server_response['success'] === true ) {
			$new_balance_with_updated_at = ADBC_Remote_Scan::update_balance( $server_response['balance'] );
			$server_response['balance'] = $new_balance_with_updated_at;
		}

		return $server_response;

	}

	/**
	 * Check if there is a scan for the given items type.
	 * 
	 * @param string $items_type The type of items to check. "tables", "options", "cron_jobs", etc.
	 * 
	 * @return bool True if a scan exists, false otherwise.
	 */
	public static function is_scan_exists( $items_type ) {

		// Check if there is a scan and still running for the same items_type
		$scan_info = get_option( "adbc_plugin_scan_info_" . $items_type );
		if ( is_array( $scan_info ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Excludes remotely categorized wordpress items from the selected items list.
	 * 
	 * @param array $selected_items The list of selected items.
	 * @param string $items_type The type of items. "tables", "options", "posts_meta", etc.
	 * 
	 * @return array The cleaned selected items list or false on failure.
	 */
	public static function exclude_r_wp_items_from_selected_items( $selected_items, $items_type ) {

		$scan_file_results_path = ADBC_Scan_Paths::get_scan_results_path( $items_type );
		$handle = ADBC_Files::instance()->get_file_handle( $scan_file_results_path );
		if ( $handle === false )
			return $selected_items;

		// Get all "r" items that belong to "w:w"
		$r_wp_items = [];
		while ( ( $line = fgets( $handle ) ) !== false ) {

			list( $item_name, $belong_to_json ) = self::split_result_file_line( $line );

			if ( $item_name === false )
				continue;

			if ( isset( $belong_to_json['r'] ) && is_array( $belong_to_json['r'] ) ) {

				foreach ( $belong_to_json['r'] as $belonging ) {
					if ( $belonging === 'w:w' ) {
						$r_wp_items[ $item_name ] = '';
						break;
					}
				}
			}
		}
		fclose( $handle );

		// Clean the selected items by removing the "r" wordpress items
		$cleaned_selected = [];
		foreach ( $selected_items as $item ) {

			$item_name = $item['name'];

			if ( $items_type === 'tables' )
				$item_name = ADBC_Tables::remove_prefix_from_table_name( $item_name );

			if ( ! isset( $r_wp_items[ $item_name ] ) ) {
				$cleaned_selected[] = $item;
			}

		}

		return $cleaned_selected;

	}

}