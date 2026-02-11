<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Scan Paths.
 *
 * This class provides the paths used by scan process.
 */
class ADBC_Scan_Paths {

	public const SCAN_FOLDER_PATH = ADBC_UPLOADS_DIR_PATH . "/scan";

	/**
	 * Get the scan results file path based on the items type.
	 * 
	 * @return string The scan results file path.
	 */
	public static function get_scan_results_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}.txt";
	}

	/**
	 * Get the items to scan file path based on the items type.
	 * This file is a temporary file that contains all the items to scan and is deleted after the scan process is done.
	 * 
	 * @return string The items to scan file path.
	 */
	public static function get_items_to_scan_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_to_scan.txt";
	}

	/**
	 * Get the temporary items to scan file path based on the items type.
	 * This file is used as a temporary file to update the items to scan file data during the scan process.
	 * 
	 * @return string The temporary items to scan file path.
	 */
	public static function get_temp_items_to_scan_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_to_scan_temp.txt";
	}

	/**
	 * Get the remote scan results file path based on the items type.
	 * This file contains the scan results received from the remote server.
	 * 
	 * @return string The remote scan results file path.
	 */
	public static function get_remote_scan_results_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_remote.txt";
	}

	/**
	 * Get the remote temporary scan results file path based on the items type.
	 * This file is used as a temporary file to merge the remote scan results with the local scan results.
	 * 
	 * @return string The remote temporary scan results file path.
	 */
	public static function get_remote_temp_scan_results_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_remote_temp.txt";
	}

	/**
	 * Get the local temporary scan results file path based on the items type.
	 * This file is used as a temporary file to write the current local scan results in real-time.
	 * 
	 * @return string The local temporary scan results file path.
	 */
	public static function get_local_temp_scan_results_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_local_temp.txt";
	}

	/**
	 * Get the local merge temporary scan results file path based on the items type.
	 * This file is used as a temporary file to merge the new temp local scan results with the old existing scan results.
	 * 
	 * @return string The local merge temporary scan results file path.
	 */
	public static function get_local_merge_temp_scan_results_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_local_merge_temp.txt";
	}

	/**
	 * Get the files to scan file path based on the items type.
	 * This file contains the list of files to scan.
	 * 
	 * @return string The files to scan file path.
	 */
	public static function get_files_to_scan_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_files_to_scan.txt";
	}

	/**
	 * Get the partial match temp results file path based on the items type.
	 * This file is used as a temporary file to store the partial match results in the case of a shutdown.
	 * 
	 * @return string The partial match temp results file path.
	 */
	public static function get_partial_match_temp_results_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_partial_match_temp.txt";
	}

	/**
	 * Get the addons dictionary file path based on the items type.
	 * This file contains a dictionary of slug|name pairs for all the slugs in the scan results file.
	 * 
	 * @return string The addons dictionary file path. 
	 */
	public static function get_addons_dictionary_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_addons_names_dictionary.txt";
	}

	/**
	 * Get the addons dictionary temp file path based on the items type.
	 * This file is used as a temporary file to update the addons dictionary file data during the scan process.
	 * 
	 * @return string The addons dictionary temp file path.
	 */
	public static function get_addons_dictionary_temp_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_addons_names_dictionary_temp.txt";
	}

	/**
	 * Get the addons dictionary slug list temp file path based on the items type.
	 * This file is used as a temporary file to store the unique slugs list to get the name for, from the scan results file.
	 * 
	 * @return string The addons dictionary slug list temp file path.
	 */
	public static function get_addons_dictionary_slug_list_temp_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_addons_names_dictionary_slug_list_temp.txt";
	}

	/**
	 * Get the manual categorization results temp file path based on the items type.
	 * This file is used as a temporary file to store the manual categorization results before merging them with the scan results.
	 * 
	 * @return string The manual categorization results temp file path.
	 */
	public static function get_manual_categorization_results_temp_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_manual_categorization_temp.txt";
	}

	/**
	 * Get the updated scan results after deletion temp file path based on the items type.
	 * This file is used as a temporary file to store the updated scan results after deleting items such as tables, options, etc. We delete the items from the scan results file and save them in this file to rename it later to the scan results file.
	 * 
	 * @return string The 'updated scan results after deletion' temp file path.
	 */
	public static function get_updated_scan_results_after_deletion_temp_file_path( $items_type ) {
		return self::SCAN_FOLDER_PATH . "/{$items_type}_updated_scan_results_temp.txt";
	}

	/**
	 * Get all the ADBC scan temp files based on the items type.
	 * 
	 * @return array The ADBC scan temp files.
	 */
	public static function get_all_adbc_scan_temp_files( $items_type ) {

		$all_files = [ 
			self::get_temp_items_to_scan_path( $items_type ),
			self::get_remote_temp_scan_results_path( $items_type ),
			self::get_local_temp_scan_results_path( $items_type ),
			self::get_files_to_scan_file_path( $items_type ),
			self::get_items_to_scan_path( $items_type ),
			self::get_remote_scan_results_path( $items_type ),
			self::get_local_merge_temp_scan_results_path( $items_type ),
			self::get_partial_match_temp_results_file_path( $items_type ),
			self::get_addons_dictionary_temp_file_path( $items_type ),
			self::get_addons_dictionary_slug_list_temp_file_path( $items_type ),
		];

		return $all_files;

	}


}