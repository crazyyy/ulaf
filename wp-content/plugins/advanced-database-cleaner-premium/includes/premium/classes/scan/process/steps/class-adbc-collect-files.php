<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Class ADBC_Collect_Files
 * 
 * This class is responsible for collecting all the PHP files for the scan, while also handling a smart/fast resuming logic in case of shutdowns.
 */
class ADBC_Collect_Files extends ADBC_Local_Scan {

	private $folders_to_scan = [];

	private $files_to_scan_file_handle = null;

	private $start_time = 0;

	/**
	 * Run the step
	 */
	public function run() {

		// If we are starting a new scan, then delete the "files to scan" file
		if ( $this->get_resume_from_file_path() === "" && ADBC_Files::instance()->exists( $this->files_to_scan_file_path ) )
			@unlink( $this->files_to_scan_file_path );

		// Open the file in append mode
		$this->files_to_scan_file_handle = ADBC_Files::instance()->get_file_handle( $this->files_to_scan_file_path, "a" );

		if ( $this->files_to_scan_file_handle === false )
			throw new Exception( "Unable to open the file." );

		// Get all other folders to scan
		$this->get_folders_to_scan();

		// Scan the folder for php files and save them to the file
		$this->collect_files();

		// Close the file
		fclose( $this->files_to_scan_file_handle );

		// Update the scan info
		$this->update_scan_info();

	}

	/**
	 * The main function responsible for collecting all the PHP files for the scan.
	 */
	private function collect_files() {

		// Get the last file scanned and determine if we need to resume from a it or start from the beginning
		$last_file = $this->get_resume_from_file_path();
		$found_folder_to_resume_from = empty( $last_file ) ? true : false;

		// Initialize start time for progress updates
		$this->start_time = time();

		// Loop through the folders to scan to collect PHP files from them
		foreach ( $this->folders_to_scan as $folder_to_scan ) {

			// If we still haven't found the folder to resume from, then skip this folder
			if ( $found_folder_to_resume_from === false && strpos( $last_file, $folder_to_scan ) === false ) {
				continue;
			}

			// If we found the folder to resume from, then set the flag to true
			$found_folder_to_resume_from = true;

			// Scan the folder for php files and save them in the all_files_paths file
			$this->handle_files_collection_from_folder( $folder_to_scan, $last_file );

			$last_file = ''; // Reset the last file to empty for the next folder

		}

	}

	/**
	 * This function is responsible for handling the high level of files collection from a folder.
	 * 
	 * @param string $folder_to_scan The folder to scan for PHP files.
	 * @param string $resume_from_file_path The file to resume from.
	 */
	private function handle_files_collection_from_folder( $folder_to_scan, $resume_from_file_path = '' ) {

		// Flag to indicate if the resume file has been found; default to true if no resume path provided
		$found_resume_from_file_flag = $resume_from_file_path === '' ? true : false;

		// Determine the starting folder for scanning - resume file's folder or the specified folder
		$resume_file_folder = $resume_from_file_path !== '' ? dirname( $resume_from_file_path ) : $folder_to_scan;
		$resume_from_file_name = basename( $resume_from_file_path );

		// Start scanning for PHP files in the determined starting folder
		$this->collect_php_files_from_folder( $resume_file_folder, $resume_from_file_name, $found_resume_from_file_flag );

		// If a specific file is set to resume from, process its parent directories as well
		if ( $resume_from_file_path !== '' ) {

			$current_dir = $resume_file_folder;

			// Loop through parent directories until reaching the top-level folder to scan
			while ( $current_dir && realpath( $current_dir ) !== realpath( $folder_to_scan ) ) {

				// Get the parent directory
				$parent_dir = dirname( $current_dir );

				// Break the loop if the parent directory is the same as the current one to avoid an infinite loop
				if ( $parent_dir === $current_dir ) {
					break;
				}

				// Move to the parent directory for the next iteration
				$current_dir = $parent_dir;

				// Call the function to scan the parent directory, updating the last processed directory
				$this->collect_php_files_from_folder( $current_dir, '', $found_resume_from_file_flag, basename( $resume_file_folder ) );

				// Update the last processed directory
				$resume_file_folder = $current_dir;

			}

		}

	}

	/**
	 * This function is responsible for directly collecting PHP files from a folder and its subdirectories.
	 * 
	 * @param string $directory The directory to scan for PHP files.
	 * @param string $resume_file The file to resume from.
	 * @param bool $found_resume_file Flag to indicate if the resume file has been found.
	 * @param string $last_processed_dir The last processed directory.
	 */
	private function collect_php_files_from_folder( $directory, $resume_file = '', &$found_resume_file = false, $last_processed_dir = '' ) {

		$directory = rtrim( $directory, '/' ); // Trim trailing slash
		$entries = scandir( $directory ); // Get directory contents
		natsort( $entries ); // Sort entries naturally

		$should_start_processing = $last_processed_dir === '' ? true : false; // Flag to determine when to start processing

		foreach ( $entries as $entry ) {

			// Update progress every 2 seconds
			if ( time() - $this->start_time >= 2 ) {
				$this->update_scan_info();
				$this->start_time = time();
			}

			// Force do_when_shutdown execution if we are about to have a max_execution_time error
			$this->force_shutdown_if_needed();

			// reduce CPU usage while traversing directories/files
			$this->reduce_cpu_usage();

			// Skip current, parent and node_modules directory entries
			if ( $entry === '.' || $entry === '..' || $entry === 'node_modules' ) {
				continue;
			}

			$full_path = $directory . '/' . $entry; // Construct full path without realpath

			// Skip hidden directories
			if ( ADBC_Files::instance()->is_dir( $full_path ) && strpos( basename( $entry ), '.' ) === 0 )
				continue;

			// Check if we should start processing the files
			if ( ! $should_start_processing && $entry === $last_processed_dir ) {
				$should_start_processing = true;
				continue;
			}

			// Check if we should resume from a specific file
			if ( ! $found_resume_file && $resume_file !== '' && $entry === basename( $resume_file ) ) {
				$found_resume_file = true;
				continue;
			}

			// Process the file or directory
			if ( $should_start_processing && ( $found_resume_file || $resume_file === '' ) ) {

				// reduce CPU usage while traversing directories/files
				$this->reduce_cpu_usage();

				// Check if the entry is a PHP file then collect it
				if ( ADBC_Files::instance()->is_file( $full_path ) ) {

					if ( substr( $full_path, -4 ) === '.php' ) {

						fwrite( $this->files_to_scan_file_handle, $full_path . "\n" );

						$this->scan_info_instance->scan_info['local']['collecting_files']['collected_files']++;

					}

					$this->set_resume_from_file_path( $full_path );

				} elseif ( ADBC_Files::instance()->is_dir( $full_path ) ) { // Check if the entry is a directory then scan it recursively

					// Prevent recursion ONLY if we are scanning the root Plugin directory.
					// This ensures we catch files in root, but don't re-scan subfolders handled by `get_folders_to_scan`.
					if ( $directory === rtrim( WP_PLUGIN_DIR, '/' ) ) {
						continue;
					}

					$this->collect_php_files_from_folder( $full_path, $resume_file, $found_resume_file );

				}

				$this->scan_info_instance->scan_info['local']["collecting_files"]["total_files"]++; // Increment scanned files count here for PHP files and directories

			}

		}

	}

	/**
	 * Get all the folders to scan for PHP files.
	 * Plugins, MU plugins and all registered themes directories.
	 */
	private function get_folders_to_scan() {

		// Add the plugins directories
		if ( ADBC_Files::instance()->is_dir( WP_PLUGIN_DIR ) ) {

			$plugin_dirs = scandir( WP_PLUGIN_DIR );

			foreach ( $plugin_dirs as $plugin_dir ) {

				// Skip current and parent directory entries
				if ( $plugin_dir === '.' || $plugin_dir === '..' ) {
					continue;
				}

				// Skip the plugins folders to be excluded
				if ( in_array( $plugin_dir, $this->get_plugins_folders_to_skip() ) ) {
					continue;
				}

				$full_plugin_path = WP_PLUGIN_DIR . '/' . $plugin_dir;

				if ( ADBC_Files::instance()->is_dir( $full_plugin_path ) ) {
					array_push( $this->folders_to_scan, $full_plugin_path );
				}

			}

			// Add the root plugin folder itself to the end of the list.
			// This allows us to scan for files living directly in /wp-content/plugins/
			array_push( $this->folders_to_scan, WP_PLUGIN_DIR );

		}


		// Add the main site's mu-plugins directory
		if ( ADBC_Files::instance()->is_dir( WPMU_PLUGIN_DIR ) )
			array_push( $this->folders_to_scan, WPMU_PLUGIN_DIR );

		// Add all the main site's themes directories
		global $wp_theme_directories;

		foreach ( $wp_theme_directories as $theme_path ) {

			if ( ADBC_Files::instance()->is_dir( $theme_path ) )
				array_push( $this->folders_to_scan, $theme_path );

		}

	}

	/**
	 * Get the file path to resume from.
	 * 
	 * @return string The file path to resume from.
	 */
	private function get_resume_from_file_path() {
		return $this->scan_info_instance->scan_info['local']['collecting_files']['last_file'];
	}

	/**
	 * Set the file path to resume from.
	 * 
	 * @param string $file_path The file path to resume from.
	 */
	private function set_resume_from_file_path( $file_path ) {
		$this->scan_info_instance->scan_info['local']['collecting_files']['last_file'] = $file_path;
	}

	/**
	 * Get the list of plugins folders to skip during the scan.
	 * 
	 * @return array The list of plugins folders to skip.
	 */
	private function get_plugins_folders_to_skip() {

		$plugins_folders_to_skip = [ 
			'advanced-database-cleaner-premium',
			'advanced-database-cleaner',
			"advanced-database-cleaner-pro"
		];

		return $plugins_folders_to_skip;

	}

}