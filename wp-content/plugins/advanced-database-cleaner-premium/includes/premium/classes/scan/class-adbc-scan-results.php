<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Scan Results class.
 * 
 * This class provides methods for loading and processing scan results.
 */
class ADBC_Scan_Results extends ADBC_Singleton {

	/** 
	 * All tables prefixes as array, to search for the table name with and without prefix.
	 * 
	 * @var array
	 */
	private static $prefixes = [];

	/**
	 * Constructor.
	 */
	protected function __construct() {

		parent::__construct();
		// Prepare tables prefixes as array to be used for tables scan results
		self::$prefixes = array_keys( ADBC_Sites::instance()->get_all_prefixes() );
		self::$prefixes[] = ""; // Add an empty prefix to search for the table name without prefix as well.

	}

	/**
	 * Get the "belongs_to" data for manual correction "m".
	 * 
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $site_id The site id from which we should get the plugin/theme status.
	 * @return array|bool|string The "belongs_to" data array, false if the scan results are not found, "corrupted" if the scan results are not valid.
	 */
	private function get_belongs_to_data_for_m( $scan_results_decoded, $site_id ) {

		// Check if 'm' is set in the decoded scan results
		if ( ! isset( $scan_results_decoded['m'] ) )
			return false;

		// If the "m" is not an array or is empty, return 'corrupted'
		if ( ! is_array( $scan_results_decoded['m'] ) )
			return 'corrupted';

		// If the "m" array is empty, return orphan
		if ( empty( $scan_results_decoded['m'] ) ) {
			return [ 
				'type' => 'o',
				'slug' => 'o',
				'name' => __( 'Orphan', 'advanced-database-cleaner' ),
				'by' => 'm',
				'percent' => 100,
				'status' => '',
			];
		}

		// Get the first item in the "m" array and split it by ":"
		list( $type, $slug ) = explode( ':', $scan_results_decoded['m'][0], 2 );

		// If the type is not valid, this item is invalid
		if ( ! in_array( $type, [ 'p', 't', 'w' ] ) )
			return 'corrupted';

		// Prepare the base belongs_to_array
		$belongs_to_array = [ 
			'type' => $type,
			'slug' => $slug,
			'by' => 'm',
			'percent' => 100,
		];

		// Handle WordPress core directly
		if ( $type === 'w' )
			return array_merge( $belongs_to_array, [ 'name' => __( 'WordPress core', 'advanced-database-cleaner' ), 'status' => 'active' ] );

		// Get the name of the plugin/theme from the current installed addons. If not found, an empty string is returned.
		$name = ADBC_Addons::get_addon_name( $slug, $type, '' );
		// Get the status of the plugin/theme.
		$status = ADBC_Addons::get_addon_status( $slug, $type, $site_id );

		return array_merge( $belongs_to_array, [ 'name' => $name, 'status' => $status ] );

	}

	/**
	 * Get the "belongs_to" data for local and remote corrections "r" and "l".
	 * 
	 * @param string $scan_type The scan type. "r" for remote correction, "l" for local correction.
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $active_status The active status. "active"/"inactive" to search respectively for installed active/inactive addons.
	 * @param string $site_id The site id from which we should get the plugin/theme status.
	 * @return array|bool|string The "belongs_to" data array, false if the scan results are not found, "corrupted" if the scan results are not valid.
	 */
	private function get_belongs_to_data_for_r_and_l( $scan_type, $scan_results_decoded, $active_status, $site_id ) {

		// Check if the specified scan type is set in the decoded scan results.
		if ( ! isset( $scan_results_decoded[ $scan_type ] ) )
			return false;

		// If the specified scan type is not an array, return 'corrupted'
		if ( ! is_array( $scan_results_decoded[ $scan_type ] ) )
			return 'corrupted';

		// Get the plugins and themes info
		$plugins_info = ADBC_Plugins::instance()->get_plugins_info();
		$themes_info = ADBC_Themes::instance()->get_themes_info();

		foreach ( $scan_results_decoded[ $scan_type ] as $addon ) {

			// Split the addon into type, slug (and percent)
			$parts = explode( ':', $addon );
			$type = $parts[0];
			$slug = $parts[1];
			$percent = ( $scan_type === 'l' && isset( $parts[2] ) ) ? $parts[2] : 100; // Default percent to 100 for 'r' and 'm'

			// Check if the type is one of the expected values
			if ( ! in_array( $type, [ 'p', 't', 'w' ] ) )
				return 'corrupted';

			// Prepare the base belongs_to_array
			$belongs_to_array = [ 
				'type' => $type,
				'slug' => $slug,
				'name' => '',
				'by' => $scan_type,
				'percent' => $percent,
				'status' => '',
			];

			// Handle WordPress core type
			if ( $type === 'w' ) {
				$belongs_to_array['name'] = __( 'WordPress core', 'advanced-database-cleaner' );
				$belongs_to_array['status'] = 'active';
				return $belongs_to_array;
			}

			// Use the pre-fetched information based on type
			$addons_info = $type === 'p' ? $plugins_info : $themes_info;

			// Check if the slug exists in the respective addon's information
			if ( isset( $addons_info[ $slug ] ) ) {

				// Check if the addon is active on the site in parameter
				$isActive = in_array( $site_id, $addons_info[ $slug ]['active_on'] );

				if ( ( $active_status === 'active' && $isActive ) || ( $active_status === 'inactive' && ! $isActive ) ) {

					$belongs_to_array['name'] = $addons_info[ $slug ]['name'];
					$belongs_to_array['status'] = ADBC_Addons::get_addon_status( $slug, $type, $site_id );
					return $belongs_to_array;

				}
			}
		}

		return false;
	}

	/**
	 * Check if an item has detected relations but they're all from uninstalled plugins/themes.
	 * 
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @return bool True if has uninstalled relations, false otherwise.
	 */
	private function has_uninstalled_relations_only( $scan_results_decoded, $site_id ) {

		$scan_types = [ 'r', 'l' ];
		$has_relations = false;

		foreach ( $scan_types as $scan_type ) {
			if ( ! isset( $scan_results_decoded[ $scan_type ] ) || ! is_array( $scan_results_decoded[ $scan_type ] ) ) {
				continue;
			}

			foreach ( $scan_results_decoded[ $scan_type ] as $addon ) {
				$parts = explode( ':', $addon );
				$type = $parts[0];
				$slug = $parts[1];

				// Skip if not a valid type
				if ( ! in_array( $type, [ 'p', 't', 'w' ] ) ) {
					continue;
				}

				$has_relations = true;

				// WordPress core is always "installed"
				if ( $type === 'w' ) {
					return false; // Has installed relations
				}

				// Check if the plugin/theme is installed
				$addon_status = ADBC_Addons::get_addon_status( $slug, $type, $site_id );
				if ( in_array( $addon_status, [ 'active', 'inactive' ] ) ) {
					return false; // Has installed relations
				}
			}
		}

		return $has_relations; // True if has relations but all are uninstalled
	}

	/**
	 * Check if item has no detected relations at all.
	 * 
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @return bool True if no relations detected, false otherwise.
	 */
	private function has_no_detected_relations( $scan_results_decoded ) {

		$scan_types = [ 'r', 'l' ];

		foreach ( $scan_types as $scan_type ) {
			if ( isset( $scan_results_decoded[ $scan_type ] ) &&
				is_array( $scan_results_decoded[ $scan_type ] ) &&
				! empty( $scan_results_decoded[ $scan_type ] ) ) {
				return false; // Has relations
			}
		}

		return true; // No relations detected
	}

	/**
	 * Determine the final belongs_to type for posts_meta and users_meta items.
	 * 
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $item_name The item name to check.
	 * @param string $items_type The items type (posts_meta or users_meta).
	 * @return string The belongs_to type: 'o' for orphan, 'unknown' for unknown.
	 */
	private function determine_meta_belongs_to_type( $scan_results_decoded, $item_name, $items_type, $site_id ) {

		// Check if item is in the dictionary of common meta keys
		$is_in_common_dict = ADBC_Hardcoded_Items::instance()->is_item_in_known_meta_dict( $item_name, $items_type );

		// Check relation conditions
		$has_no_relations = $this->has_no_detected_relations( $scan_results_decoded );
		$has_uninstalled_only = $this->has_uninstalled_relations_only( $scan_results_decoded, $site_id );

		// Apply the rules for Unknown vs Orphan
		if ( $has_no_relations || ( $has_uninstalled_only && $is_in_common_dict ) ) {
			return 'unk'; // Unknown
		} else {
			return 'o'; // Orphan
		}
	}

	/**
	 * Get the "belongs_to" data from the scan results.
	 * 
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $site_id The site id from which we should get the plugin/theme status.
	 * @param string $item_name The item name (needed for meta type determination).
	 * @param string $items_type The items type (needed for meta type determination).
	 * @return array|bool The "belongs_to" data array, false if the scan results are not found or invalid.
	 */
	private function get_belongs_to_data( $scan_results_decoded, $site_id, $item_name = '', $items_type = '' ) {

		// Early exit if neither 'm' nor 'l' is set (for 'r', it may exist or not).
		if ( ! isset( $scan_results_decoded['m'] ) && ! isset( $scan_results_decoded['l'] ) )
			return false;

		// First, try to get data for 'm'
		$belongs_to_data = $this->get_belongs_to_data_for_m( $scan_results_decoded, $site_id );

		if ( $belongs_to_data === 'corrupted' )
			return false;

		if ( $belongs_to_data !== false )
			return $belongs_to_data;

		// Define the order and types for checking
		$types = [ 
			[ 'type' => 'r', 'status' => 'active' ],
			[ 'type' => 'l', 'status' => 'active' ],
			[ 'type' => 'r', 'status' => 'inactive' ],
			[ 'type' => 'l', 'status' => 'inactive' ],
		];

		foreach ( $types as $item ) {

			$scan_type = $item['type'];
			$active_status = $item['status'];

			// Try to get data for the current scan type
			$belongs_to_data = $this->get_belongs_to_data_for_r_and_l( $scan_type, $scan_results_decoded, $active_status, $site_id );

			if ( $belongs_to_data === 'corrupted' )
				return false;

			if ( $belongs_to_data !== false )
				return $belongs_to_data;

		}

		// If we are here, it means that no relations were found or all relations are from uninstalled plugins/themes
		// For posts_meta and users_meta, we need to determine if it's orphan or unknown
		if ( in_array( $items_type, [ 'posts_meta', 'users_meta' ] ) ) {

			$belongs_to_type = $this->determine_meta_belongs_to_type( $scan_results_decoded, $item_name, $items_type, $site_id );

			if ( $belongs_to_type === 'unk' ) {
				return [ 
					'type' => 'unk',
					'slug' => '',
					'name' => __( 'Unknown', 'advanced-database-cleaner' ),
					'by' => '',
					'percent' => '',
					'status' => '',
				];
			}
		}

		// Check if we have a unique relation in "l" which has a percent < 100, this case don't say it's orphan, instead say it belongs to "not installed"
		if ( empty( $scan_results_decoded['r'] ) && count( $scan_results_decoded['l'] ) === 1 ) {

			$parts = explode( ':', $scan_results_decoded['l'][0] );
			$type = $parts[0];
			$slug = $parts[1];
			$percent = isset( $parts[2] ) ? $parts[2] : 100;

			if ( in_array( $type, [ 'p', 't' ] ) && $percent < 100 ) {
				return [ 
					'type' => $type,
					'slug' => $slug,
					'name' => "", // Name is empty because we didn't find any relation with an installed addon
					'by' => 'l',
					'percent' => $percent,
					'status' => 'not_installed', // 'not_installed' is a special status to indicate that the addon is not installed
				];
			}
		}

		// Default to orphan for all other cases
		return [ 
			'type' => 'o',
			'slug' => 'o',
			'name' => __( 'Orphan', 'advanced-database-cleaner' ),
			'by' => 'l',
			'percent' => '',
			'status' => '',
		];
	}

	/**
	 * Get other known plugins/themes matching the "belongs to", based on the scan results.
	 * 
	 * @param string $slug_to_exclude The slug to exclude from the known plugins/themes.
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $site_id The site id from which we should get the plugin/theme status.
	 * @return array The known plugins/themes array or an empty array if no known plugins/themes are found.
	 */
	private function get_known_addons( $slug_to_exclude, $scan_results_decoded, $site_id ) {

		$known_addons = [ 'p' => [], 't' => []]; // 'p' for plugins, 't' for themes
		$scan_types = [ 'r', 'l' ]; // Types to look for: 'r' and 'l'. 'r' should be checked first.

		foreach ( $scan_types as $scan_type ) {

			if ( ! isset( $scan_results_decoded[ $scan_type ] ) ) // Skip if the scan type is not set
				continue;

			foreach ( $scan_results_decoded[ $scan_type ] as $addon ) {

				$parts = explode( ':', $addon );
				$type = $parts[0];
				$slug = $parts[1];
				$percent = ( $scan_type === 'l' && isset( $parts[2] ) ) ? $parts[2] : 100;

				// Skip the slug to exclude or if the slug is already added to the known addons
				if ( $slug === $slug_to_exclude || isset( $known_addons[ $type ][ $slug ] ) )
					continue;

				// Get the name of the plugin/theme from the current installed addons. If not found, an empty string is returned.
				$name = ADBC_Addons::get_addon_name( $slug, $type, '' );
				// Gte the status of the plugin/theme.
				$status = ADBC_Addons::get_addon_status( $slug, $type, $site_id );

				$known_addons[ $type ][ $slug ] = [ 
					'slug' => $slug,
					'name' => $name,
					'status' => $status,
					'percent' => $percent,
					'scan_type' => $scan_type
				];
			}
		}

		return [ 
			'known_plugins' => array_values( $known_addons['p'] ),
			'known_themes' => array_values( $known_addons['t'] )
		];
	}

	/**
	 * Load scan results to array of tables by reference.
	 * 
	 * @param array $tables_rows The tables rows array to load the scan results to.
	 * 
	 * @return void|bool False if the scan results file does not exist or is not readable.
	 */
	public function load_scan_results_to_tables_rows( &$tables_rows ) {

		// First of all, add some properties to each item to prevent any undefined index error.
		foreach ( $tables_rows as $table_name => $item_data ) {
			$tables_rows[ $table_name ]->belongs_to = [ 
				'type' => 'u',
				'slug' => '',
				'name' => '',
				'by' => '',
				'percent' => '',
				'status' => '',
			];
			$tables_rows[ $table_name ]->known_plugins = []; // known plugins that are related to the item
			$tables_rows[ $table_name ]->known_themes = []; // known themes that are related to the item
		}

		// Verify if the scan results file exists and is readable and can be opened.
		$scan_file_results_path = ADBC_Scan_Paths::get_scan_results_path( 'tables' );
		$handle = ADBC_Files::instance()->get_file_handle( $scan_file_results_path );
		if ( $handle === false )
			return false;

		while ( ( $line = fgets( $handle ) ) !== false ) {

			// Find the last occurrence of the separator "|" in the line
			$last_separator_position = strrpos( $line, '|' );

			// If the separator "|" is not found, skip the line
			if ( $last_separator_position === false )
				continue;

			// Get the item name
			$table_name = substr( $line, 0, $last_separator_position );

			// Get the scan results
			$scan_results = substr( $line, $last_separator_position + 1 );

			// If the scan results is not a valid JSON, skip the line
			$scan_results_decoded = json_decode( $scan_results, true );
			if ( ! $scan_results_decoded )
				continue;

			// Since the item name does not contain the prefix in the scan results, we should iterate through all prefixes to find the item name
			foreach ( self::$prefixes as $prefix ) {

				$table_name_with_prefix = $prefix . $table_name; // The prefix can be empty, to handle the table name without prefix.
				$this->load_scan_results_to_one_table_row( $tables_rows, $table_name_with_prefix, $scan_results_decoded );

			}

		}

		fclose( $handle );
	}

	/**
	 * Load scan results to one row of tables by reference.
	 * 
	 * @param object $tables_row The tables row to load the scan results to.
	 * @param string $table_name The table name to search for in the rows array.
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @return void
	 */
	private function load_scan_results_to_one_table_row( &$tables_rows, $table_name, $scan_results_decoded ) {

		// If the item name is not found in the rows array, skip the line
		if ( ! isset( $tables_rows[ $table_name ] ) )
			return;

		// Get the belongs_to data
		$belongs_to_data = $this->get_belongs_to_data( $scan_results_decoded, $tables_rows[ $table_name ]->site_id );

		if ( $belongs_to_data === false ) // If the belongs_to data is not found, skip the line
			return;

		$known_addons = $this->get_known_addons( $belongs_to_data['slug'], $scan_results_decoded, $tables_rows[ $table_name ]->site_id );

		$tables_rows[ $table_name ]->belongs_to = $belongs_to_data; // Set the belongs_to data to the item
		$tables_rows[ $table_name ]->known_plugins = $known_addons['known_plugins']; // Set the known plugins to the item
		$tables_rows[ $table_name ]->known_themes = $known_addons['known_themes']; // Set the known themes to the item

	}

	/**
	 * Load scan results to array of items by reference.
	 * 
	 * @param array $items_rows The items rows array to load the scan results to.
	 * @param string $items_type The items type. "options", "cron_jobs", "transients", "posts_meta", "users_meta".
	 * @return void|bool False if the scan results file does not exist or is not readable.
	 */
	public function load_scan_results_to_items_rows( &$items_rows, $items_type ) {

		// First of all, add some properties to each item to prevent any undefined index error.
		foreach ( $items_rows as $item ) {
			$item->belongs_to = [ 
				'type' => 'u',
				'slug' => 'u',
				'name' => '',
				'by' => '',
				'percent' => '',
				'status' => '',
			];
			$item->known_plugins = []; // known plugins that are related to the item
			$item->known_themes = []; // known themes that are related to the item
		}

		/**
		 * Build a fast index once:
		 * item_name => [row_index_1, row_index_2, ...]
		 */
		$indexes_by_name = [];
		foreach ( $items_rows as $i => $item ) {
			$name = $item->name;
			$indexes_by_name[ $name ][] = $i;
		}

		// Verify if the scan results file exists and is readable and can be opened.
		$scan_file_results_path = ADBC_Scan_Paths::get_scan_results_path( $items_type );
		$handle = ADBC_Files::instance()->get_file_handle( $scan_file_results_path );
		if ( $handle === false )
			return false;

		while ( ( $line = fgets( $handle ) ) !== false ) {

			// Find the last occurrence of the separator "|" in the line
			$last_separator_position = strrpos( $line, '|' );

			// If the separator "|" is not found, skip the line
			if ( $last_separator_position === false )
				continue;

			// Get the item name
			$item_name = substr( $line, 0, $last_separator_position );

			// If we don't have this name in rows, skip without decoding JSON
			if ( ! isset( $indexes_by_name[ $item_name ] ) )
				continue;

			$scan_results = substr( $line, $last_separator_position + 1 );

			// If the scan results is not a valid JSON, skip the line
			$scan_results_decoded = json_decode( $scan_results, true );
			if ( ! is_array( $scan_results_decoded ) )
				continue;

			// Load the scan results to the item
			$this->load_scan_results_to_one_item_row( $items_rows, $item_name, $scan_results_decoded, $items_type, $indexes_by_name );

		}

		fclose( $handle );
	}

	/**
	 * Load scan results to one row of items by reference.
	 * 
	 * @param object $items_row The items row to load the scan results to.
	 * @param string $item_name The item name to search for in the rows array.
	 * @param array $scan_results_decoded The scan results decoded array.
	 * @param string $items_type The items type for unknown detection.
	 * @param array|null $indexes_by_name Optional pre-built indexes by name for fast lookup.
	 * 
	 * @return void
	 */
	private function load_scan_results_to_one_item_row( &$items_rows, $item_name, $scan_results_decoded, $items_type, $indexes_by_name = null ) {

		// If the item name does not have at least one occurrence in the rows array, skip the item_name
		if ( ! isset( $indexes_by_name[ $item_name ] ) )
			return;


		$matched_indexes = $indexes_by_name[ $item_name ];

		// set the scan results data to all the occurrences of the item_name
		foreach ( $matched_indexes as $index ) {

			// Get the belongs_to data
			$belongs_to_data = $this->get_belongs_to_data( $scan_results_decoded, $items_rows[ $index ]->site_id, $item_name, $items_type );

			if ( $belongs_to_data === false ) // If the belongs_to data is not found, skip the item_name
				return;

			$known_addons = $this->get_known_addons( $belongs_to_data['slug'], $scan_results_decoded, $items_rows[ $index ]->site_id );

			$items_rows[ $index ]->belongs_to = $belongs_to_data; // Set the belongs_to data to the item
			$items_rows[ $index ]->known_plugins = $known_addons['known_plugins']; // Set the known plugins to the item
			$items_rows[ $index ]->known_themes = $known_addons['known_themes']; // Set the known themes to the item


		}

	}

}