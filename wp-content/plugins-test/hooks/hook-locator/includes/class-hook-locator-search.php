<?php
/**
 * File scanning and hook search functionality.
 *
 * Handles scanning plugin and theme files for WordPress hook usage patterns.
 * Implements efficient file processing with performance safeguards.
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook Locator Search class.
 *
 * Responsible for scanning PHP files in plugins and themes to locate
 * WordPress hook usage patterns including actions and filters.
 *
 * @since 1.0
 */
class Hook_Locator_Search {

	/**
	 * Maximum file size to scan (1MB).
	 *
	 * @since 1.0
	 * @var   int
	 */
	const MAX_FILE_SIZE = 1048576;

	/**
	 * Maximum files to scan per request.
	 *
	 * @since 1.0
	 * @var   int
	 */
	const MAX_FILES_PER_SCAN = 1000;

	/**
	 * Hook function patterns to search for.
	 *
	 * @since 1.0
	 * @var   array
	 */
	private static $hook_patterns = array(
		'add_action',
		'add_filter',
		'do_action',
		'apply_filters',
		'remove_action',
		'remove_filter',
		'has_action',
		'has_filter',
		'do_action_ref_array',
		'apply_filters_ref_array',
	);

	/**
	 * Scan plugin and theme files for hook usage.
	 *
	 * Searches through PHP files looking for WordPress hook function calls
	 * that match the specified hook name.
	 *
	 * @since 1.0
	 * @param string $hook_name   The hook name to search for.
	 * @param string $folder_key  The directory key to search in.
	 * @return array Array of found hook usages with file, line, and context info.
	 */
	public static function scan_files( $hook_name, $folder_key = 'all' ) {
		$results = array();

		// Validate hook name.
		if ( empty( $hook_name ) || ! is_string( $hook_name ) ) {
			return $results;
		}

		$hook_name = sanitize_text_field( $hook_name );
		if ( empty( $hook_name ) ) {
			return $results;
		}

		// Get directories to scan.
		$scan_dirs = self::get_scan_directories( $folder_key );
		if ( empty( $scan_dirs ) ) {
			return $results;
		}

		$file_count = 0;

		foreach ( $scan_dirs as $dir_path ) {
			if ( ! is_dir( $dir_path ) || ! is_readable( $dir_path ) ) {
				continue;
			}

			$results = array_merge( $results, self::scan_directory( $dir_path, $hook_name, $file_count ) );

			// Stop if we've scanned too many files.
			if ( $file_count >= self::MAX_FILES_PER_SCAN ) {
				break;
			}
		}

		return $results;
	}

	/**
	 * Get directories to scan based on folder key.
	 *
	 * @since 1.0
	 * @param string $folder_key The directory key to search in.
	 * @return array Array of directory paths to scan.
	 */
	private static function get_scan_directories( $folder_key ) {
		$all_dirs = Hook_Locator_Core::get_instance()->get_scan_directories();

		if ( 'all' === $folder_key ) {
			return $all_dirs;
		}

		if ( isset( $all_dirs[ $folder_key ] ) ) {
			return array( $folder_key => $all_dirs[ $folder_key ] );
		}

		return array();
	}

	/**
	 * Scan a directory for hook usage.
	 *
	 * @since 1.0
	 * @param string $dir_path   Directory path to scan.
	 * @param string $hook_name  Hook name to search for.
	 * @param int    $file_count Reference to file count (passed by reference).
	 * @return array Array of found hook usages.
	 */
	private static function scan_directory( $dir_path, $hook_name, &$file_count ) {
		$results = array();

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $dir_path, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $file->isDir() ) {
					continue;
				}

				// Check file count limit.
				if ( $file_count >= self::MAX_FILES_PER_SCAN ) {
					break;
				}

				$file_path = $file->getPathname();

				// Only scan PHP files.
				if ( ! self::is_php_file( $file_path ) ) {
					continue;
				}

				// Skip files that are too large.
				if ( filesize( $file_path ) > self::MAX_FILE_SIZE ) {
					continue;
				}

				$file_results = self::scan_file( $file_path, $hook_name );
				if ( ! empty( $file_results ) ) {
					$results = array_merge( $results, $file_results );
				}

				++$file_count;
			}
		} catch ( Exception $e ) {
			// Log error but continue processing.
			printf( 'Hook Locator: Error scanning directory ' . esc_html( $dir_path ) . ': ' . esc_html( $e->getMessage() ) );
		}

		return $results;
	}

	/**
	 * Scan a single file for hook usage.
	 *
	 * @since 1.0
	 * @param string $file_path File path to scan.
	 * @param string $hook_name Hook name to search for.
	 * @return array Array of found hook usages in the file.
	 */
	private static function scan_file( $file_path, $hook_name ) {
		$results = array();

		// Read file contents.
		$lines = @file( $file_path, FILE_IGNORE_NEW_LINES );
		if ( false === $lines ) {
			return $results;
		}

		// Scan each line.
		foreach ( $lines as $line_num => $line ) {
			$line = trim( $line );

			// Skip empty lines and comments.
			if ( empty( $line ) || 0 === strpos( $line, '//' ) || 0 === strpos( $line, '/*' ) || 0 === strpos( $line, '*' ) ) {
				continue;
			}

			$hook_type = self::find_hook_in_line( $line, $hook_name );
			if ( false !== $hook_type ) {
				$identifier = md5( $file_path . '-' . $line_num . '-' . $hook_name . '-' . $hook_type );

				$results[ $identifier ] = array(
					'file'      => $file_path,
					'line'      => $line_num + 1,
					'code'      => $line,
					'type'      => $hook_type,
					'hook_name' => $hook_name,
				);
			}
		}

		return $results;
	}

	/**
	 * Find hook usage in a line of code.
	 *
	 * @since 1.0
	 * @param string $line      Line of code to search.
	 * @param string $hook_name Hook name to search for.
	 * @return string|false Hook type if found, false otherwise.
	 */
	private static function find_hook_in_line( $line, $hook_name ) {
		$escaped_hook_name = preg_quote( $hook_name, '/' );

		foreach ( self::$hook_patterns as $pattern ) {
			// Create regex pattern for this hook function.
			$regex = '/\b' . preg_quote( $pattern, '/' ) . '\s*\(\s*[\'"]' . $escaped_hook_name . '[\'"]\s*[,)]/';

			if ( preg_match( $regex, $line ) ) {
				return $pattern;
			}
		}

		return false;
	}

	/**
	 * Check if file is a PHP file.
	 *
	 * @since 1.0
	 * @param string $file_path File path to check.
	 * @return bool True if PHP file, false otherwise.
	 */
	private static function is_php_file( $file_path ) {
		$extension = pathinfo( $file_path, PATHINFO_EXTENSION );
		return 'php' === strtolower( $extension );
	}

	/**
	 * Get hook type display name.
	 *
	 * @since 1.0
	 * @param string $hook_type Hook type identifier.
	 * @return string Display name for the hook type.
	 */
	public static function get_hook_type_label( $hook_type ) {
		$labels = array(
			'add_action'              => __( 'add_action', 'hook-locator' ),
			'add_filter'              => __( 'add_filter', 'hook-locator' ),
			'do_action'               => __( 'do_action', 'hook-locator' ),
			'apply_filters'           => __( 'apply_filters', 'hook-locator' ),
			'remove_action'           => __( 'remove_action', 'hook-locator' ),
			'remove_filter'           => __( 'remove_filter', 'hook-locator' ),
			'has_action'              => __( 'has_action', 'hook-locator' ),
			'has_filter'              => __( 'has_filter', 'hook-locator' ),
			'do_action_ref_array'     => __( 'do_action_ref_array', 'hook-locator' ),
			'apply_filters_ref_array' => __( 'apply_filters_ref_array', 'hook-locator' ),
		);

		return isset( $labels[ $hook_type ] ) ? $labels[ $hook_type ] : $hook_type;
	}
}
