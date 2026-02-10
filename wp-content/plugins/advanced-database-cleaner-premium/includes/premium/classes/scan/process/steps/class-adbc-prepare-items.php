<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Prepare the items to scan step class.
 * 
 * This class is responsible for preparing the items to scan by saving them to the items_to_scan file.
 * It uses an optimized logic to collect and process items in batches to avoid memory usage issues.
 */
class ADBC_Prepare_Items extends ADBC_Local_Scan {

	/*
	 * These models methods are called here dynamically depending on the items type using call_user_func:
	 * 
	 * get_options_names()
	 * get_options_names_from_ids()
	 * get_users_meta_names()
	 * get_users_meta_names_from_ids()
	 * get_posts_meta_names()
	 * get_posts_meta_names_from_ids()
	 * get_transients_names()
	 * get_transients_names_from_ids()
	 * get_tables_names()
	 * get_cron_jobs_names()
	 * 
	 */

	private $model_class = null;

	private $get_names_method_name = '';

	private $get_names_from_ids_method_name = '';

	protected function __construct() {

		parent::__construct();

		// prepare the callbacks names that fetches the items names to get data dynamically depending on the items type
		$item_class_name = "ADBC_{$this->scan_info_instance->items_type}";
		$this->model_class = $item_class_name;
		$this->get_names_method_name = "get_{$this->scan_info_instance->items_type}_names";
		$this->get_names_from_ids_method_name = "get_{$this->scan_info_instance->items_type}_names_from_ids";

	}

	/**
	 * Run the step.
	 * 
	 * @return void
	 */
	public function run() {

		// If we should scan the uncategorized items and the scan results file doesn't exist, scan all items.
		if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::UNCATEGORIZED && ! ADBC_Files::instance()->exists( $this->scan_results_file_path ) )
			$this->scan_info_instance->what_to_scan = ADBC_Scan::ALL;

		// Save the items to the items_to_scan file depending on the items type.
		switch ( $this->scan_info_instance->items_type ) {

			case "tables":
				if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::SELECTED )
					$this->save_selected_items_by_name_to_file();
				else
					$this->collect_and_save_tables_to_file();
				break;

			case "cron_jobs":
				if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::SELECTED )
					$this->save_selected_items_by_name_to_file();
				else
					$this->collect_and_save_cron_jobs_to_file();
				break;

			default:
				if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::SELECTED )
					$this->save_selected_items_by_id_to_file();
				else
					$this->collect_and_save_items_to_file();
				break;

		}

	}

	/**
	 * Collect and save the items to the items_to_scan file for the "ALL" and "UNCATEGORIZED" scans.
	 */
	private function collect_and_save_items_to_file() {

		// open the items_to_scan file for writing
		$items_to_scan_file = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'w' );

		if ( $items_to_scan_file === false )
			throw new Exception( "Unable to open the file." );

		$offset = 0;

		// get all sites prefixes
		$sites_prefixes = array_keys( ADBC_Sites::instance()->get_all_prefixes() );

		// loop through all sites and write the items names to the items_to_scan file
		foreach ( $sites_prefixes as $site_prefix ) {

			$offset = 0; // reset the offset for each site

			// loop through the items names by batches and write them to the items_to_scan file
			while ( $items_batch = call_user_func( array( $this->model_class, $this->get_names_method_name ), $site_prefix, $this->database_rows_batch_size, $offset ) ) {

				// If the what_to_scan is uncategorized, remove the categorized items from the batch.
				if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::UNCATEGORIZED )
					$this->remove_categorized_items_from_batch( $items_batch );

				// if the override_manual_categorization is disabled, remove the manual categorization items from the batch
				if ( $this->scan_info_instance->scan_info['override_manual_categorization'] === false && $this->scan_info_instance->what_to_scan !== ADBC_Scan::UNCATEGORIZED )
					$this->remove_manual_categorization_items_from_batch( $items_batch );

				// Remove the WordPress items from the batch.
				$this->remove_wordpress_items_from_batch( $items_batch );

				// remove the ADBC items from the batch
				$this->remove_adbc_items_from_batch( $items_batch );

				// Avoid adding duplicate items to the items_to_scan file.
				$this->remove_already_saved_items( $items_batch );

				// Write the batch to the items_to_scan file.
				if ( ! empty( $items_batch ) ) {

					fwrite( $items_to_scan_file, implode( "\n", array_keys( $items_batch ) ) . "\n" );

					// Update the total items count in the scan info.
					$this->scan_info_instance->scan_info['local']['total_items'] += count( $items_batch );

				}

				$offset += $this->database_rows_batch_size;

			}

		}

		fclose( $items_to_scan_file );

	}

	/**
	 * Collect and save the tables to the items_to_scan file for the "ALL" and "UNCATEGORIZED" scans.
	 */
	private function collect_and_save_tables_to_file() {

		// open the items_to_scan file for writing
		$items_to_scan_file = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'w' );

		if ( $items_to_scan_file === false )
			throw new Exception( "Unable to open the file." );

		$offset = 0;

		$show_tables_with_invalid_prefix = ADBC_Settings::instance()->get_setting( 'show_tables_with_invalid_prefix' ) === '1';

		// loop through the tables names by batches and write them to the items_to_scan file
		while ( $tables_batch = ADBC_Tables::get_tables_names( $this->database_rows_batch_size, $offset, false, $show_tables_with_invalid_prefix ) ) {

			// If the what_to_scan is uncategorized, remove the categorized items from the batch.
			if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::UNCATEGORIZED )
				$this->remove_categorized_items_from_batch( $tables_batch );

			// if the override_manual_categorization is disabled, remove the manual categorization items from the batch
			if ( $this->scan_info_instance->scan_info['override_manual_categorization'] === false && $this->scan_info_instance->what_to_scan !== ADBC_Scan::UNCATEGORIZED )
				$this->remove_manual_categorization_items_from_batch( $tables_batch );

			// Remove the WordPress items from the batch.
			$this->remove_wordpress_items_from_batch( $tables_batch );

			// remove the ADBC items from the batch
			$this->remove_adbc_items_from_batch( $tables_batch );

			// Avoid adding duplicate items to the items_to_scan file.
			$this->remove_already_saved_items( $tables_batch );

			// Write the batch to the items_to_scan file.
			if ( ! empty( $tables_batch ) ) {

				fwrite( $items_to_scan_file, implode( "\n", array_keys( $tables_batch ) ) . "\n" );

				// Update the total items count in the scan info.
				$this->scan_info_instance->scan_info['local']['total_items'] += count( $tables_batch );

			}

			$offset += $this->database_rows_batch_size;

		}

		fclose( $items_to_scan_file );

	}

	/**
	 * Collect and save the cron jobs to the items_to_scan file for the "ALL" and "UNCATEGORIZED" scans.
	 */
	public function collect_and_save_cron_jobs_to_file() {

		// open the items_to_scan file for writing
		$items_to_scan_file = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'w' );

		if ( $items_to_scan_file === false )
			throw new Exception( "Unable to open the file." );

		$crons_names = ADBC_Cron_Jobs::get_cron_jobs_names();

		// If the what_to_scan is uncategorized, remove the categorized items from the batch.
		if ( $this->scan_info_instance->what_to_scan === ADBC_Scan::UNCATEGORIZED )
			$this->remove_categorized_items_from_batch( $crons_names );

		// if the override_manual_categorization is disabled, remove the manual categorization items from the batch
		if ( $this->scan_info_instance->scan_info['override_manual_categorization'] === false && $this->scan_info_instance->what_to_scan !== ADBC_Scan::UNCATEGORIZED )
			$this->remove_manual_categorization_items_from_batch( $crons_names );

		// Remove the WordPress items from the batch.
		$this->remove_wordpress_items_from_batch( $crons_names );

		// remove the ADBC items from the batch
		$this->remove_adbc_items_from_batch( $crons_names );

		// Avoid adding duplicate items to the items_to_scan file.
		$this->remove_already_saved_items( $crons_names );

		// Write the batch to the items_to_scan file.
		if ( ! empty( $crons_names ) ) {

			fwrite( $items_to_scan_file, implode( "\n", array_keys( $crons_names ) ) . "\n" );

			// Update the total items count in the scan info.
			$this->scan_info_instance->scan_info['local']['total_items'] += count( $crons_names );

		}

		fclose( $items_to_scan_file );

	}

	/**
	 * Save the selected items to the items_to_scan file.
	 */
	private function save_selected_items_by_id_to_file() {

		// open the items_to_scan file for writing
		$items_to_scan_file = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'w' );

		if ( $items_to_scan_file === false )
			throw new Exception( "Unable to open the file." );

		// get all sites prefixes from the selected items to only loop through the appropriate sites ids
		$sites_prefixes = $this->get_all_sites_prefixes_from_selected_items();

		foreach ( $sites_prefixes as $site_prefix ) {

			// get the ids of the selected items for the current site prefix
			$selected_items_ids = $this->get_selected_items_ids_by_site_prefix( $site_prefix );

			$selected_items_names = call_user_func( array( $this->model_class, $this->get_names_from_ids_method_name ), $site_prefix, $selected_items_ids );

			// if the override_manual_categorization is disabled, remove the manual categorization items from the batch
			if ( $this->scan_info_instance->scan_info['override_manual_categorization'] === false )
				$this->remove_manual_categorization_items_from_batch( $selected_items_names );

			// Remove the WordPress items from the batch.
			$this->remove_wordpress_items_from_batch( $selected_items_names );

			// remove the ADBC items from the batch
			$this->remove_adbc_items_from_batch( $selected_items_names );

			if ( ! empty( $selected_items_names ) ) {

				fwrite( $items_to_scan_file, implode( "\n", array_keys( $selected_items_names ) ) . "\n" );

				// Update the total items count in the scan info.
				$this->scan_info_instance->scan_info['local']['total_items'] += count( $selected_items_names );

			}

		}

		fclose( $items_to_scan_file );

	}

	/**
	 * Save the selected tables to the items_to_scan file.
	 */
	private function save_selected_items_by_name_to_file() {

		// convert the selected items to associative array with the item name as key and true as value for easier manipulation
		$items_to_scan = array_fill_keys( $this->scan_info_instance->selected_items_to_scan, true );

		// if the override_manual_categorization is disabled, remove the manual categorization items from the batch
		if ( $this->scan_info_instance->scan_info['override_manual_categorization'] === false )
			$this->remove_manual_categorization_items_from_batch( $items_to_scan );

		// Remove the WordPress items from the selected items.
		$this->remove_wordpress_items_from_batch( $items_to_scan );

		// remove the ADBC items from the selected items
		$this->remove_adbc_items_from_batch( $items_to_scan );

		if ( ! empty( $items_to_scan ) ) {

			ADBC_Files::instance()->put_contents( $this->items_to_scan_file_path, implode( "\n", array_keys( $items_to_scan ) ) . "\n" );

			// Update the total items count in the scan info.
			$this->scan_info_instance->scan_info['local']['total_items'] = count( $items_to_scan );

		}

	}

	/**
	 * Filter the items that are already written to the items_to_scan file to avoid duplicates.
	 * 
	 * @param array $items_to_scan Items to scan.
	 */
	private function remove_already_saved_items( &$items_to_scan ) {

		if ( empty( $items_to_scan ) )
			return;

		$items_to_scan_file = ADBC_Files::instance()->get_file_handle( $this->items_to_scan_file_path, 'r' );

		if ( $items_to_scan_file === false )
			throw new Exception( "Unable to open the file." );

		// Read the file and remove the items that are already written to the file.
		while ( ( $line = fgets( $items_to_scan_file ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			if ( key_exists( $line, $items_to_scan ) )
				unset( $items_to_scan[ $line ] );

			if ( empty( $items_to_scan ) )
				break;

		}

		fclose( $items_to_scan_file );

	}

	/**
	 * Remove the categorized items from the batch array, used for the "UNCATEGORIZED" scan.
	 * 
	 * @param array $items Items.
	 */
	private function remove_categorized_items_from_batch( &$items ) {

		if ( empty( $items ) )
			return;

		$scan_results_file = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'r' );

		if ( $scan_results_file === false )
			throw new Exception( "Unable to open the file." );

		// Read the file and remove the categorized items from the batch.
		while ( ( $line = fgets( $scan_results_file ) ) !== false ) {

			list( $item_name, $result_json ) = ADBC_Scan_Utils::split_result_file_line( $line );

			if ( $item_name === false )
				continue;

			if ( key_exists( $item_name, $items ) )
				unset( $items[ $item_name ] );

		}

		fclose( $scan_results_file );

	}

	/**
	 * Remove the hardcoded WordPress core items from the batch array.
	 * 
	 * @param array $items Items.
	 * @return void
	 */
	private function remove_wordpress_items_from_batch( &$items ) {

		if ( empty( $items ) )
			return;

		$wp_hardcoded_items = ADBC_Hardcoded_Items::instance()->get_wordpress_items( $this->scan_info_instance->items_type );

		foreach ( $items as $item_name => $data ) {

			if ( ADBC_Hardcoded_Items::instance()->is_item_belongs_to_wp_core( $item_name, $this->scan_info_instance->items_type, $wp_hardcoded_items ) )
				unset( $items[ $item_name ] );

		}

	}


	/**
	 * Remove the hardcoded ADBC items from the batch array.
	 * 
	 * @param array $items Items.
	 */
	private function remove_adbc_items_from_batch( &$items ) {

		if ( empty( $items ) )
			return;

		$adbc_items = ADBC_Hardcoded_Items::instance()->get_adbc_items( $this->scan_info_instance->items_type );

		foreach ( $adbc_items as $adbc_item => $data )
			unset( $items[ $adbc_item ] );

	}

	/**
	 * Remove the manual categorization items from the batch array, used when the override_manual_categorization is disabled.
	 * 
	 * @param array $items Items.
	 */
	private function remove_manual_categorization_items_from_batch( &$items ) {

		if ( empty( $items ) )
			return;

		$scan_results_file = ADBC_Files::instance()->get_file_handle( $this->scan_results_file_path, 'r' );

		// if the scan results file doesn't exist, we don't need to remove the manual categorization items
		if ( $scan_results_file === false )
			return;

		$local_temp_results_file_handle = ADBC_Files::instance()->get_file_handle( $this->local_temp_results_file_path, 'a' );

		if ( $local_temp_results_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// Read the file and remove the categorized items from the batch.
		while ( ( $line = fgets( $scan_results_file ) ) !== false ) {

			list( $item_name, $result_json ) = ADBC_Scan_Utils::split_result_file_line( $line );

			if ( $item_name === false )
				continue;

			// If the item is manually categorized, remove it from the batch and write it to the local temp results file.
			if ( key_exists( $item_name, $items ) && key_exists( "m", $result_json ) ) {
				unset( $items[ $item_name ] );
				fwrite( $local_temp_results_file_handle, $line );
			}

		}

		fclose( $scan_results_file );
		fclose( $local_temp_results_file_handle );

	}

	/**
	 * Get all sites prefixes from the selected items, used to loop through the appropriate sites ids.
	 * 
	 * @return array Sites prefixes.
	 */
	private function get_all_sites_prefixes_from_selected_items() {

		$sites_prefixes = [];

		// loop through the selected items and get the site id from each item
		foreach ( $this->scan_info_instance->selected_items_to_scan as $selected_item ) {

			$site_prefix = ADBC_Sites::instance()->get_prefix_from_site_id( $selected_item['site_id'] );

			if ( $site_prefix !== null && ! in_array( $site_prefix, $sites_prefixes ) )
				$sites_prefixes[] = $site_prefix;

		}

		return $sites_prefixes;

	}

	/**
	 * Get the selected items ids by site prefix.
	 * 
	 * @param string $site_prefix Site prefix.
	 * @return array Selected items ids.
	 */
	private function get_selected_items_ids_by_site_prefix( $site_prefix ) {

		$selected_items_ids = [];

		// loop through the selected items and get the ids of the items that belong to the current site prefix
		foreach ( $this->scan_info_instance->selected_items_to_scan as $selected_item ) {

			$selected_item_site_prefix = ADBC_Sites::instance()->get_prefix_from_site_id( $selected_item['site_id'] );

			// if the selected item belongs to the current site prefix, add its id to the selected items ids array
			if ( $selected_item_site_prefix === $site_prefix )
				$selected_items_ids[] = $this->scan_info_instance->items_type === 'transients' ?
					[ $selected_item['id'] => $selected_item['found_in'] ]
					: $selected_item['id'];

		}

		return $selected_items_ids;

	}

}