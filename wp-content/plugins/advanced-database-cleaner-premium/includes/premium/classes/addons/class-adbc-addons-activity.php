<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC addons activity class.
 * 
 * This class contains the methods to monitor the plugins and themes activations, deactivations, and uninstalls.
 */
class ADBC_Addons_Activity {

	public const ADDONS_ACTIVITY_LOG_FILE_PATH = ADBC_UPLOADS_DIR_PATH . '/addons_activity.log';
	public const ADDONS_ACTIVITY_DICTIONARY = ADBC_UPLOADS_DIR_PATH . '/addons_activity_dictionary.log';
	private const TEMP_ADDONS_ACTIVITY_LOG_FILE_PATH = ADBC_UPLOADS_DIR_PATH . '/temp_addons_activity.log';
	private const TEMP_ADDONS_ACTIVITY_DICTIONARY = ADBC_UPLOADS_DIR_PATH . '/temp_addons_activity_dictionary.log';

	private const PLUGIN_LOG_FORMAT = [ 
		'a' => [], // activation timestamp
		'd' => [], // deactivation timestamp
		'u' => [], // uninstall timestamp
	];

	private const THEME_LOG_FORMAT = [ 
		'a' => [], // activation timestamp
		'd' => [], // deactivation timestamp
		'u' => [], // uninstall timestamp
	];

	// Set up activity type mapping
	private const ACTIVITY_MAP = [ 
		'activation' => 'a',
		'deactivation' => 'd',
		'uninstall' => 'u',
	];

	// Set up the maximum file size for the log file after which we stop logging.
	private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 20 MB

	// Set up a maximum number of last activities per activity type to log for each addon.
	private const MAX_ACTIVITIES_PER_ADDON = 1000; // 1000 activities

	/**
	 * Checks if the addons activity setting is enabled, if the addons activity file exists and is writable,
	 * 
	 * @return bool True if the addons activity should be logged, false otherwise.
	 */
	private static function should_log_activity() {

		// Check if the addons activity setting is enabled
		$addons_activity_enabled = ADBC_Settings::instance()->get_setting( 'addons_activity_enabled' );
		if ( $addons_activity_enabled === '0' ) {
			return false;
		}

		// Check if the addons activity file exists and is writable
		if ( ! ADBC_Files::instance()->is_readable_and_writable( self::ADDONS_ACTIVITY_LOG_FILE_PATH ) ) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_error( 'The addons activity log file does not exist or is not readable/writable.', __METHOD__, __LINE__ );
			return false;
		}

		// Check if the addons activity file size is less than 20MB
		if ( ADBC_Files::instance()->size( self::ADDONS_ACTIVITY_LOG_FILE_PATH ) > self::MAX_FILE_SIZE ) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_error( 'The addons activity log file size exceeds the maximum limit.', __METHOD__, __LINE__ );
			return false;
		}

		return true;

	}

	/**
	 * Executed when a plugin is activated.
	 * 
	 * @param string $plugin The plugin activated.
	 * 
	 * @return void
	 */
	public static function on_plugin_activated( $plugin ) {

		try {

			if ( ! self::should_log_activity() ) {
				return;
			}

			$plugin_parts = explode( '/', $plugin );

			if ( $plugin_parts[0] === '' )
				return;

			$plugin_typed_slug = 'p:' . $plugin_parts[0];

			if ( ! self::log_activity( $plugin_typed_slug, 'plugin_activated' ) ) {
				self::update_execution_setting( 'fail' );
			}

			// Set the addon's activity execution success settings to current time
			self::update_execution_setting( 'success' );

			// update the addon activity dictionary
			self::update_dictionary();

		} catch (Exception $e) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Executed when a plugin is deactivated.
	 * 
	 * @param string $plugin The plugin deactivated.
	 * 
	 * @return void
	 */
	public static function on_plugin_deactivated( $plugin ) {

		try {

			if ( ! self::should_log_activity() ) {
				return;
			}

			$plugin_parts = explode( '/', $plugin );

			if ( $plugin_parts[0] === '' )
				return;

			$plugin_typed_slug = 'p:' . $plugin_parts[0];

			if ( ! self::log_activity( $plugin_typed_slug, 'plugin_deactivated' ) ) {
				self::update_execution_setting( 'fail' );
			}

			// Set the addon's activity execution success settings to current time
			self::update_execution_setting( 'success' );

		} catch (Exception $e) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Executed when a plugin is uninstalled.
	 * 
	 * @param string $plugin The plugin uninstalled.
	 * 
	 * @return void
	 */
	public static function on_plugin_uninstalled( $plugin ) {

		try {

			if ( ! self::should_log_activity() ) {
				return;
			}

			$plugin_parts = explode( '/', $plugin );

			if ( $plugin_parts[0] === '' )
				return;

			$plugin_typed_slug = 'p:' . $plugin_parts[0];

			if ( ! self::log_activity( $plugin_typed_slug, 'plugin_uninstalled' ) === false ) {
				self::update_execution_setting( 'fail' );
			}

			// Set the addon's activity execution success settings to current time
			self::update_execution_setting( 'success' );

		} catch (Exception $e) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Executed when a theme is switched (activated/deactivated).
	 * 
	 * @param string $new_name The name of the new theme being activated.
	 * @param WP_Theme $new_theme The new theme object.
	 * @param WP_Theme $old_theme The old theme object being deactivated.
	 * 
	 * @return void
	 */
	public static function on_theme_switched( $new_name, $new_theme, $old_theme ) {

		try {

			if ( ! self::should_log_activity() ) {
				return;
			}

			// Log the deactivation of the old theme first (if it exists)
			if ( $old_theme && ! is_wp_error( $old_theme ) ) {
				$old_theme_name = $old_theme->get( 'Name' );
				$old_theme_slug = ADBC_Themes::instance()->get_slug_from_theme_name( $old_theme_name );

				if ( $old_theme_slug !== '' ) {
					$old_theme_typed_slug = 't:' . $old_theme_slug;

					if ( ! self::log_activity( $old_theme_typed_slug, 'theme_deactivated' ) ) {
						self::update_execution_setting( 'fail' );
					}
				}
			}

			// Log the activation of the new theme
			$new_theme_slug = ADBC_Themes::instance()->get_slug_from_theme_name( $new_name );
			if ( $new_theme_slug !== '' ) {
				$new_theme_typed_slug = 't:' . $new_theme_slug;
				$forced_timestamp = time() + 1;
				if ( ! self::log_activity( $new_theme_typed_slug, 'theme_activated', $forced_timestamp ) ) {
					self::update_execution_setting( 'fail' );
				}
			}

			// Set the addon's activity execution success settings to current time
			self::update_execution_setting( 'success' );

			// update the addon activity dictionary
			self::update_dictionary();

		} catch (Exception $e) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Executed when a theme is uninstalled.
	 * 
	 * @param string $theme_slug The slug of the theme uninstalled.
	 * 
	 * @return void
	 */
	public static function on_theme_uninstalled( $theme_slug ) {

		try {

			if ( ! self::should_log_activity() ) {
				return;
			}

			// The delete_theme hook passes the theme slug directly, not the theme name
			// Since the theme has already been deleted, we can't look it up in the themes info
			// We'll use the slug directly to create the typed slug
			if ( empty( $theme_slug ) ) {
				return;
			}

			$theme_typed_slug = 't:' . $theme_slug;

			if ( ! self::log_activity( $theme_typed_slug, 'theme_uninstalled' ) ) {
				self::update_execution_setting( 'fail' );
			}

			// Set the addon's activity execution success settings to current time
			self::update_execution_setting( 'success' );

			// update the addon activity dictionary
			self::update_dictionary();

		} catch (Exception $e) {
			self::update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Update the addons activity dictionary file.
	 * 
	 * This method updates the addons activity dictionary file by removing any addons that are no longer installed.
	 * It creates a temporary file, writes the remaining addons to it, and then renames it to the original file name.
	 * 
	 * @return void
	 */
	public static function update_dictionary() {

		$dictionary_file_handle = ADBC_Files::instance()->get_file_handle( self::ADDONS_ACTIVITY_DICTIONARY );
		$temp_dictionary_file_handle = ADBC_Files::instance()->get_file_handle( self::TEMP_ADDONS_ACTIVITY_DICTIONARY, 'a' );

		if ( $dictionary_file_handle === false || $temp_dictionary_file_handle === false ) {
			return;
		}

		$installed_addons = ADBC_Addons::get_all_installed_addons();

		// Copy the addons activity dictionary file to the temp file and keep the installed addons that are not in the dictionary file.
		while ( ( $line = fgets( $dictionary_file_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );
			[ $slug, $addon_name ] = ADBC_Dictionary::split_slug_name_dictionary_line( $line );

			if ( $slug === false || $addon_name === false )
				continue;

			fwrite( $temp_dictionary_file_handle, $line . "\n" );

			if ( isset( $installed_addons[ $slug ] ) ) {
				unset( $installed_addons[ $slug ] );
			}

		}

		// Add the remaining installed addons to the temp file.
		if ( ! empty( $installed_addons ) ) {

			foreach ( $installed_addons as $installed_addon_slug => $installed_addon_name ) {
				$line = $installed_addon_slug . "|" . $installed_addon_name . "\n";
				fwrite( $temp_dictionary_file_handle, $line );
			}

		}

		// Close the file handles
		fclose( $dictionary_file_handle );
		fclose( $temp_dictionary_file_handle );

		// Replace the original file with the temp file
		if ( ADBC_Files::instance()->exists( self::TEMP_ADDONS_ACTIVITY_DICTIONARY ) && ! rename( self::TEMP_ADDONS_ACTIVITY_DICTIONARY, self::ADDONS_ACTIVITY_DICTIONARY ) )
			return;

	}

	/**
	 * Log an event to the addons activity log file.
	 * 
	 * @param string $typed_slug The typed slug of the addon.
	 * @param string $event The event that triggered the log.
	 * @param int $forced_timestamp The forced timestamp to use for the activity.
	 * 
	 * @return bool True if success, false otherwise.
	 */
	private static function log_activity( $typed_slug, $event, $forced_timestamp = null ) {

		$addons_activity_log_handle = ADBC_Files::instance()->get_file_handle( self::ADDONS_ACTIVITY_LOG_FILE_PATH, 'r' );
		$temp_addons_activity_log_handle = ADBC_Files::instance()->get_file_handle( self::TEMP_ADDONS_ACTIVITY_LOG_FILE_PATH, 'w' );

		if ( $addons_activity_log_handle === false || $temp_addons_activity_log_handle === false ) {
			return false;
		}

		// Loop over the addons activity file lines and add/update the addon activity data.
		$found_addon = false;

		while ( ( $line = fgets( $addons_activity_log_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );
			$addon_data = json_decode( $line, true );

			if ( empty( $addon_data ) )
				continue;

			$existing_typed_slug = array_key_first( $addon_data );

			// if the addon is not the one we are looking for, copy the line to the temp file.
			if ( $typed_slug !== $existing_typed_slug ) {
				fwrite( $temp_addons_activity_log_handle, $line . "\n" );
				continue;
			}

			// if the addon is the one we are looking for, flag it as found.
			$found_addon = true;

			// if the addon is the one we are looking for, then prepare its data and write it to the temp file.
			$line = self::prepare_addon_data_line( $typed_slug, $event, $addon_data, $forced_timestamp );

			fwrite( $temp_addons_activity_log_handle, $line );

		}

		// If the addon was not found, add it to the addons activity file.
		if ( ! $found_addon ) {
			$line = self::prepare_addon_data_line( $typed_slug, $event, null, $forced_timestamp );
			fwrite( $temp_addons_activity_log_handle, $line );
		}

		fclose( $addons_activity_log_handle );
		fclose( $temp_addons_activity_log_handle );

		// Replace the original file with the temp file
		if ( ADBC_Files::instance()->exists( self::TEMP_ADDONS_ACTIVITY_LOG_FILE_PATH ) && ! rename( self::TEMP_ADDONS_ACTIVITY_LOG_FILE_PATH, self::ADDONS_ACTIVITY_LOG_FILE_PATH ) )
			return false;

		return true;

	}

	/**
	 * Prepare the addon data line based on the event.
	 * Limits the number of activities per type to MAX_ACTIVITIES_PER_ADDON.
	 * Always preserves the first activity timestamp and latest ones.
	 * Adds microsecond precision to timestamps to prevent duplicate values.
	 * 
	 * @param string $typed_slug The typed slug of the addon.
	 * @param string $event The event that triggered the log.
	 * @param array $addon_data The addon data to update.
	 * @param int $forced_timestamp The forced timestamp to use for the activity.
	 * 
	 * @return string The addon data line.
	 */
	private static function prepare_addon_data_line( $typed_slug, $event, $addon_data = null, $forced_timestamp = null ) {

		$addon_type = substr( $typed_slug, 0, 1 );

		// If the addon data is not provided, create a new one with the appropriate format and default values.
		if ( $addon_data === null ) {
			$addon_data[ $typed_slug ] = $addon_type === 'p' ? self::PLUGIN_LOG_FORMAT : self::THEME_LOG_FORMAT;
		}

		// Determine which activity type to update based on the event
		$activity_key = '';
		switch ( $event ) {
			case 'plugin_activated':
				$activity_key = 'a';
				break;
			case 'plugin_deactivated':
				$activity_key = 'd';
				break;
			case 'plugin_uninstalled':
				$activity_key = 'u';
				break;
			case 'theme_activated':
				$activity_key = 'a';
				break;
			case 'theme_deactivated':
				$activity_key = 'd';
				break;
			case 'theme_uninstalled':
				$activity_key = 'u';
				break;
		}

		if ( ! empty( $activity_key ) ) {

			$current_timestamp = $forced_timestamp !== null ? $forced_timestamp : time();
			$addon_data[ $typed_slug ][ $activity_key ][] = $current_timestamp;

			// Check if we need to trim the activities array
			if ( count( $addon_data[ $typed_slug ][ $activity_key ] ) > self::MAX_ACTIVITIES_PER_ADDON ) {
				// Keep first timestamp (at index 0) and remove the second one (at index 1)
				// This preserves always the first activity timestamp.
				array_splice( $addon_data[ $typed_slug ][ $activity_key ], 1, 1 );
			}
		}

		$line = json_encode( $addon_data ) . "\n";

		return $line;

	}

	/**
	 * Get all logged addons typed slugs.
	 * 
	 * @return array The addons typed slugs found.
	 */
	public static function get_all_logged_addons_typed_slugs() {

		$addons_activity_log_handle = ADBC_Files::instance()->get_file_handle( self::ADDONS_ACTIVITY_LOG_FILE_PATH, 'r' );

		if ( $addons_activity_log_handle === false ) {
			return [];
		}

		$addons_typed_slugs = [];

		while ( ( $line = fgets( $addons_activity_log_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );
			$addon_data = json_decode( $line, true );

			if ( empty( $addon_data ) )
				continue;

			$addons_typed_slugs[ array_key_first( $addon_data ) ] = '';

		}

		fclose( $addons_activity_log_handle );

		return $addons_typed_slugs;

	}

	/**
	 * Get the activity timeline of addons sorted from the most recent to the oldest based on the search term and activity type.
	 * 
	 * @param string $search The search term.
	 * @param string $activity_type The activity type (activation, deactivation, uninstall).
	 * @param int $offset The offset for pagination.
	 * @param int $limit The limit for pagination.
	 * 
	 * @return array Associative array of two keys: 'total' and 'activity_timeline'.
	 * 				'total' is the total number of activities found.
	 * 				'activity_timeline' is an array of activity data with the following keys:
	 * 				'timestamp' (int), 'slug' (string), 'addon_name' (string), 'activity_type' (string).
	 */
	public static function get_activity_timeline( $search = '', $activity_type = '', $offset = 0, $limit = 10 ) {

		$results = [ 
			'total' => 0,
			'activity_timeline' => [],
		];

		// Get all logged addons with their names
		$logged_addons = self::get_all_logged_addons_names();
		if ( empty( $logged_addons ) )
			return $results;

		$addons_activity_log_handle = ADBC_Files::instance()->get_file_handle( self::ADDONS_ACTIVITY_LOG_FILE_PATH, 'r' );
		if ( $addons_activity_log_handle === false ) {
			return $results;
		}

		// Determine which activity keys to look for
		$activity_keys = self::get_activity_keys_for_filter( $activity_type );

		// Collect all activities that match the criteria
		$activities = [];

		while ( ( $line = fgets( $addons_activity_log_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );
			$addon_data = json_decode( $line, true );

			if ( empty( $addon_data ) )
				continue;

			$typed_slug = array_key_first( $addon_data );
			$slug = substr( $typed_slug, 2 );
			$addon_name = isset( $logged_addons[ $typed_slug ] ) ? $logged_addons[ $typed_slug ] : $typed_slug;

			// Skip if search term doesn't match slug or name
			if ( ! empty( $search ) && stripos( $slug, $search ) === false && stripos( $addon_name, $search ) === false ) {
				continue;
			}

			$activity_data = $addon_data[ $typed_slug ];

			foreach ( $activity_keys as $activity_key ) {

				// Skip if the addon doesn't have this activity type or if it's empty
				if ( ! isset( $activity_data[ $activity_key ] ) || empty( $activity_data[ $activity_key ] ) ) {
					continue;
				}

				// Map activity key back to activity type name
				$activity_type_name = array_search( $activity_key, self::ACTIVITY_MAP );

				// Add each timestamp as a separate activity entry
				foreach ( $activity_data[ $activity_key ] as $timestamp ) {
					$activities[] = [ 
						'timestamp' => $timestamp,
						'slug' => $slug,
						'addon_name' => $addon_name === '' ? $slug : $addon_name,
						'activity_type' => $activity_type_name,
					];
				}
			}
		}

		fclose( $addons_activity_log_handle );

		// Sort activities by timestamp (most recent first)
		usort( $activities, function ($a, $b) {
			return $b['timestamp'] - $a['timestamp'];
		} );

		// Set the total count
		$results['total'] = count( $activities );

		// Apply pagination
		$activities = array_slice( $activities, $offset, $limit );
		$results['activity_timeline'] = $activities;

		return $results;

	}

	/**
	 * Get activity keys based on the selected filter.
	 *
	 * @param string $activity_type The activity type filter.
	 * @return array Array of activity keys to search for.
	 */
	private static function get_activity_keys_for_filter( $activity_type = '' ) {

		// If no activity type is specified, return all activity keys
		if ( ! empty( $activity_type ) && isset( self::ACTIVITY_MAP[ $activity_type ] ) )
			return [ self::ACTIVITY_MAP[ $activity_type ] ];
		else
			return array_values( self::ACTIVITY_MAP );

	}

	/**
	 * Get all logged addons names.
	 * 
	 * @return array The addons names found.
	 */
	private static function get_all_logged_addons_names() {

		$logged_addons_slugs_names = self::get_all_logged_addons_typed_slugs();

		if ( empty( $logged_addons_slugs_names ) ) {
			return [];
		}

		// Collect names from the installed addons
		$installed_addons = ADBC_Addons::get_all_installed_addons();

		$should_get_names_from_dictionary = false;
		foreach ( $logged_addons_slugs_names as $logged_addon_slug => $addon_name ) {
			if ( isset( $installed_addons[ $logged_addon_slug ] ) ) {
				$logged_addons_slugs_names[ $logged_addon_slug ] = $installed_addons[ $logged_addon_slug ];
			} else {
				$should_get_names_from_dictionary = true;
			}
		}

		// If we have all the names from the installed addons, early return.
		if ( ! $should_get_names_from_dictionary ) {
			return $logged_addons_slugs_names;
		}

		// If we don't have all the names, we need to get them from the dictionary file.
		$dictionary_file_handle = ADBC_Files::instance()->get_file_handle( self::ADDONS_ACTIVITY_DICTIONARY, 'r' );

		// If the dictionary file is not found, return the already collected addons slugs names.
		if ( $dictionary_file_handle === false ) {
			return $logged_addons_slugs_names;
		}

		while ( ( $line = fgets( $dictionary_file_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );
			[ $slug, $addon_name ] = ADBC_Dictionary::split_slug_name_dictionary_line( $line );

			if ( $slug === false || $addon_name === false ) {
				continue;
			}

			// Skip if the slug is not in the logged addons slugs names
			if ( ! isset( $logged_addons_slugs_names[ $slug ] ) ) {
				continue;
			}

			// Skip if we have already the name for this slug
			if ( $logged_addons_slugs_names[ $slug ] !== '' ) {
				continue;
			}

			// Set the logged addons names that are currently installed.
			$logged_addons_slugs_names[ $slug ] = $addon_name;

		}

		fclose( $dictionary_file_handle );

		return $logged_addons_slugs_names;

	}

	/**
	 * Update the execution setting for the addons activity.
	 * 
	 * @param string $type The type of execution (success or fail).
	 * 
	 * @return void
	 */
	private static function update_execution_setting( $type ) {
		$addon_activity_settings = ADBC_Settings::instance()->get_setting( 'addons_activity_execution' );
		$addon_activity_settings[ $type ] = time();
		ADBC_Settings::instance()->update_settings( [ 'addons_activity_execution' => $addon_activity_settings ] );
	}

	/**
	 * Clear the addons activity file
	 * 
	 * @throws Exception
	 * 
	 * @return void
	 */
	public static function clear_all_activities() {

		if ( ! ADBC_Files::instance()->put_contents( self::ADDONS_ACTIVITY_LOG_FILE_PATH, "" ) ) {
			throw new Exception( "Unable to clear the addons activity file." );
		}

	}

}