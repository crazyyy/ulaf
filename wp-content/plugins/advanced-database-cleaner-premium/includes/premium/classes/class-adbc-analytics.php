<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
***********************************************************************
Database file structure
***********************************************************************
a = added
d = deleted
s = total size
t = total number of tables
***********************************************************************
Tables file structure
************************************************************************
s = size
r = total rows
c = total columns
z = size change
w = rows change
m = columns change
***********************************************************************
*/

/**
 * ADBC analytics class.
 * 
 * This class contains the methods to schedule and run the analytics cron job, and to get the analytics data.
 */
class ADBC_Analytics extends ADBC_Singleton {

	public const ADBC_ANALYTICS_DIR = ADBC_UPLOADS_DIR_PATH . '/analytics';
	public const ADBC_ANALYTICS_DATABASE_DIR = self::ADBC_ANALYTICS_DIR . '/database';
	public const ADBC_ANALYTICS_TABLES_DIR = self::ADBC_ANALYTICS_DIR . '/tables';

	private $current_year;
	private $current_month;
	private $current_date;
	private $current_year_tables_folder_path;
	private $current_year_database_file_path;
	private $current_month_tables_file_path;

	protected function __construct() {

		parent::__construct();

		// Set the properties
		$this->current_year = date( 'Y' );
		$this->current_month = date( 'm' );
		$this->current_date = date( 'm-d' );
		$this->current_year_tables_folder_path = self::ADBC_ANALYTICS_TABLES_DIR . "/{$this->current_year}";
		$this->current_year_database_file_path = self::ADBC_ANALYTICS_DATABASE_DIR . "/{$this->current_year}.txt";
		$this->current_month_tables_file_path = $this->current_year_tables_folder_path . "/{$this->current_month}.txt";

	}

	/**
	 * Main method to check and schedule the cron job, it's hooked to the 'plugins_loaded' action.
	 * @return void
	 */
	public static function check_and_schedule_cron() {

		// Schedule the cron job if it's not already scheduled
		if ( ! wp_next_scheduled( 'adbc_cron_analytics' ) )
			wp_schedule_event( time(), 'daily', 'adbc_cron_analytics' );
	}

	/**
	 * create the dynamic folders for the analytics data.
	 * 
	 * @return void
	 */
	private function create_dynamic_folders() {
		ADBC_Files::instance()->create_folder( $this->current_year_tables_folder_path, true );
	}

	/**
	 * Check if the analytics should be logged based on the settings and folder/file existence.
	 * 
	 * @return bool True if the analytics should be logged, false otherwise.
	 */
	private function should_log_analytics() {

		// Check if the addons activity setting is enabled
		$analytics_enabled = ADBC_Settings::instance()->get_setting( 'analytics_enabled' );
		if ( $analytics_enabled === '0' ) {
			return false;
		}

		// create the dynamic analytics folders if they don't exist
		$this->create_dynamic_folders();

		// Check if the tables current folder exists and database folder exists
		if ( ! ADBC_Files::instance()->is_dir( $this->current_year_tables_folder_path ) || ! ADBC_Files::instance()->is_dir( self::ADBC_ANALYTICS_DATABASE_DIR ) ) {
			$this->update_execution_setting( 'fail' );
			ADBC_Logging::log_error( 'The analytics folders do not exist.', __METHOD__, __LINE__ );
			return false;
		}

		// Create the database and tables current files if they don't exist
		ADBC_Files::instance()->create_file( $this->current_year_database_file_path, true );
		ADBC_Files::instance()->create_file( $this->current_month_tables_file_path, true );

		// Check if the database and tables current files exists, readable and writable
		if ( ! ADBC_Files::instance()->is_readable_and_writable( $this->current_year_database_file_path ) || ! ADBC_Files::instance()->is_readable_and_writable( $this->current_month_tables_file_path ) ) {
			$this->update_execution_setting( 'fail' );
			ADBC_Logging::log_error( 'The analytics files do not exist or are not readable/writable.', __METHOD__, __LINE__ );
			return false;
		}

		return true;

	}

	/**
	 * Static proxy for run_analytics_cron to be used in the hooks.
	 * 
	 * @return void
	 */
	public static function _run_analytics_cron() {
		self::instance()->run_analytics_cron();
	}

	/**
	 * The function that is called by the analytics cron job to save the analytics data each day.
	 * 
	 * @return void
	 */
	public function run_analytics_cron() {

		try {

			// Check if the analytics should be logged
			if ( ! $this->should_log_analytics() ) {
				return;
			}

			// Force MySQL to update the information schema
			ADBC_Tables::analyze_all_tables();

			// Save the database analytics
			if ( ! $this->save_database_analytics() ) {
				$this->update_execution_setting( 'fail' );
				return;
			}

			// Save the tables analytics
			if ( ! $this->save_tables_analytics() ) {
				$this->update_execution_setting( 'fail' );
				return;
			}

			// Set the analytics execution success settings to current time
			$this->update_execution_setting( 'success' );

		} catch (Exception $e) {
			$this->update_execution_setting( 'fail' );
			ADBC_Logging::log_exception( __METHOD__, $e );
		}

	}

	/**
	 * Get the database analytics data by day for a specific date range.
	 * 
	 * @param string $start_date The start date in 'Y-m-d' format.
	 * @param string $end_date   The end date in 'Y-m-d' format.
	 * 
	 * @return array An associative array of the database analytics data keyed by date.
	 */
	public function get_database_chart_data_by_day( $start_date, $end_date ) {

		// Initialize the result array
		$chart_data = [];

		// Parse the start and end dates
		$start = new DateTime( $start_date );
		$end = new DateTime( $end_date );

		// Adjust end date to include the entire day
		$end->setTime( 23, 59, 59 );

		// Get the years we need to check
		$start_year = $start->format( 'Y' );
		$end_year = $end->format( 'Y' );

		// Create a range of years to check
		$years_to_check = range( $start_year, $end_year );

		// Loop through each year
		foreach ( $years_to_check as $year ) {

			$year_file_path = self::ADBC_ANALYTICS_DATABASE_DIR . "/$year.txt";

			// Check if the year file exists
			if ( ! ADBC_Files::instance()->exists( $year_file_path ) )
				continue;

			// Read the file line by line
			$file_handle = ADBC_Files::instance()->get_file_handle( $year_file_path, 'r' );
			if ( $file_handle === false )
				continue;

			while ( ( $line = fgets( $file_handle ) ) !== false ) {

				$line = rtrim( $line, "\r\n" );
				if ( empty( $line ) )
					continue;

				// Decode the JSON data from the line
				$data = json_decode( $line, true );
				if ( ! $data )
					continue;

				// Get the date from the data (first key)
				$date_key = key( $data );

				// Convert the date format from "m-d" to full date with year
				$date_obj = DateTime::createFromFormat( 'm-d', $date_key );
				if ( ! $date_obj )
					continue;

				// Set the year
				$date_obj->setDate( $year, $date_obj->format( 'm' ), $date_obj->format( 'd' ) );
				$full_date = $date_obj->format( 'Y-m-d' );

				// Check if the date is within our range
				if ( $date_obj >= $start && $date_obj <= $end )
					$chart_data[ $full_date ] = $data[ $date_key ];
			}

			fclose( $file_handle );
		}

		// Sort the array by date keys
		ksort( $chart_data );

		return $chart_data;
	}

	/**
	 * Get the tables analytics data by day for a specific date range.
	 * 
	 * @param string $start_date The start date in 'Y-m-d' format.
	 * @param string $end_date   The end date in 'Y-m-d' format.
	 * @param array  $tables     Array of table names with their prefix to include. Empty for all existing wordpress core tables.
	 * 
	 * @return array An associative array of the tables analytics data keyed by table name.
	 */
	public function get_tables_chart_data_by_day( $start_date, $end_date, $tables = [] ) {

		// Fill the tables by wordpress core tables if not provided
		if ( empty( $tables ) )
			$tables = ADBC_Tables::get_all_wp_core_tables_with_prefix();

		// Initialize the result array
		$date_keyed_data = [];

		// Parse the start and end dates
		$start = new DateTime( $start_date );
		$end = new DateTime( $end_date );

		// Adjust end date to include the entire day
		$end->setTime( 23, 59, 59 );

		// Get the years we need to check
		$start_year = $start->format( 'Y' );
		$end_year = $end->format( 'Y' );

		// Create a range of years to check
		$years_to_check = range( $start_year, $end_year );

		// Loop through each year
		foreach ( $years_to_check as $year ) {

			$year_dir_path = self::ADBC_ANALYTICS_TABLES_DIR . "/$year";

			// Check if the year directory exists
			if ( ! ADBC_Files::instance()->exists( $year_dir_path ) || ! ADBC_Files::instance()->is_dir( $year_dir_path ) )
				continue;

			// Determine which months we need to check
			$start_month = ( $year == $start_year ) ? $start->format( 'm' ) : '01';
			$end_month = ( $year == $end_year ) ? $end->format( 'm' ) : '12';
			$months_to_check = range( (int) $start_month, (int) $end_month );

			// Loop through each month
			foreach ( $months_to_check as $month ) {

				// Format month with leading zero
				$month = str_pad( $month, 2, '0', STR_PAD_LEFT );
				$month_file_path = "$year_dir_path/$month.txt";

				// Check if the month file exists
				if ( ! ADBC_Files::instance()->exists( $month_file_path ) )
					continue;

				// Read the file line by line
				$file_handle = ADBC_Files::instance()->get_file_handle( $month_file_path, 'r' );
				if ( $file_handle === false )
					continue;

				while ( ( $line = fgets( $file_handle ) ) !== false ) {

					$line = rtrim( $line, "\r\n" );
					if ( empty( $line ) )
						continue;

					// Decode the JSON data from the line
					$data = json_decode( $line, true );
					if ( ! $data )
						continue;

					// Get the date from the data (first key)
					$date_key = key( $data );

					// Convert the date format from "m-d" to full date with year
					$date_obj = DateTime::createFromFormat( 'm-d', $date_key );
					if ( ! $date_obj )
						continue;

					// Set the year for the date object
					$date_obj->setDate( $year, $date_obj->format( 'm' ), $date_obj->format( 'd' ) );
					$full_date = $date_obj->format( 'Y-m-d' );

					// Check if the date is within our range
					if ( $date_obj >= $start && $date_obj <= $end ) {

						// Remove tables that are not requested
						if ( ! empty( $tables ) )
							$this->remove_not_requested_tables_from_data( $data, $tables );

						// Add the data to the chart data if still has data
						if ( ! empty( $data[ $date_key ] ) )
							$date_keyed_data[ $full_date ] = $data[ $date_key ];
					}
				}

				fclose( $file_handle );
			}
		}

		// Convert date-keyed data to table-keyed data
		$table_keyed_data = [];
		$this->convert_date_keyed_to_table_keyed( $date_keyed_data, $table_keyed_data );

		return $table_keyed_data;
	}

	/**
	 * Get database chart data by month for a specific date range.
	 *
	 * @param string $start_month The start month in 'Y-m' format.
	 * @param string $end_month   The end month in 'Y-m' format.
	 * 
	 * @return array An associative array of the database analytics data keyed by month.
	 */
	public function get_database_chart_data_by_month( $start_month, $end_month ) {

		// Initialize result array
		$chart_data = [];

		// Parse start and end months
		$start = DateTime::createFromFormat( 'Y-m', $start_month );
		$end = DateTime::createFromFormat( 'Y-m', $end_month );

		// Clone start date to iterate through months
		$current = clone $start;

		// Process each month in the range
		while ( $current <= $end ) {

			$year = $current->format( 'Y' );
			$month = $current->format( 'm' );
			$month_key = $current->format( 'Y-m' );

			$year_file_path = self::ADBC_ANALYTICS_DATABASE_DIR . "/$year.txt";

			// Skip if the year file doesn't exist
			if ( ! ADBC_Files::instance()->exists( $year_file_path ) ) {
				$current->modify( '+1 month' );
				continue;
			}

			// Read the file line by line and collect the data for this month
			$file_handle = ADBC_Files::instance()->get_file_handle( $year_file_path, 'r' );
			if ( $file_handle === false ) {
				$current->modify( '+1 month' );
				continue;
			}

			$last_day_data = null;
			$last_day_date = null;
			$added_tables = [];
			$deleted_tables = [];

			while ( ( $line = fgets( $file_handle ) ) !== false ) {

				$line = rtrim( $line, "\r\n" );
				if ( empty( $line ) ) {
					continue;
				}

				// Decode the JSON data from the line
				$data = json_decode( $line, true );
				if ( ! $data ) {
					continue;
				}

				// Get the date from the data (first key)
				$date_key = key( $data );

				// Convert the date format from "m-d" to full date with year
				$date_obj = DateTime::createFromFormat( 'm-d', $date_key );
				if ( ! $date_obj )
					continue;

				// Set the year
				$date_obj->setDate( $year, $date_obj->format( 'm' ), $date_obj->format( 'd' ) );

				// Check if this entry belongs to our target month
				if ( $date_obj->format( 'm' ) === $month ) {

					// Track added and deleted tables for this day (keeping duplicates)
					if ( isset( $data[ $date_key ]['a'] ) && is_array( $data[ $date_key ]['a'] ) ) {
						foreach ( $data[ $date_key ]['a'] as $table ) {
							$added_tables[] = $table;
						}
					}

					if ( isset( $data[ $date_key ]['d'] ) && is_array( $data[ $date_key ]['d'] ) ) {
						foreach ( $data[ $date_key ]['d'] as $table ) {
							$deleted_tables[] = $table;
						}
					}

					// Update last day data if this is the latest entry for this month
					if ( $last_day_date === null || $date_obj > $last_day_date ) {
						$last_day_data = $data[ $date_key ];
						$last_day_date = $date_obj;
					}
				}
			}

			fclose( $file_handle );

			// Skip if no data found for this month
			if ( $last_day_data === null ) {
				$current->modify( '+1 month' );
				continue;
			}

			// Calculate the final state of added and deleted tables by counting occurrences
			[ $final_added, $final_deleted ] = $this->get_final_month_added_deleted_tables( $added_tables, $deleted_tables );

			// Add the data to the chart
			$chart_data[ $month_key ] = [ 
				's' => $last_day_data['s'],
				't' => $last_day_data['t'],
				'a' => $final_added,
				'd' => $final_deleted
			];

			// Move to next month
			$current->modify( '+1 month' );
		}

		return $chart_data;

	}

	/**
	 * Get tables chart data by month for a specific date range.
	 *
	 * @param string $start_month The start month in 'Y-m' format.
	 * @param string $end_month   The end month in 'Y-m' format.
	 * @param array  $tables      Array of table names with their prefix to include. Empty for all existing wordpress core tables.
	 * 
	 * @return array An associative array of the tables analytics data keyed by table name.
	 */
	public function get_tables_chart_data_by_month( $start_month, $end_month, $tables = [] ) {

		// Fill the tables by wordpress core tables if not provided
		if ( empty( $tables ) )
			$tables = ADBC_Tables::get_all_wp_core_tables_with_prefix();

		// Parse start and end dates
		$start_date = DateTime::createFromFormat( 'Y-m', $start_month );
		$end_date = DateTime::createFromFormat( 'Y-m', $end_month );

		// Set to first day of month
		$start_date->modify( 'first day of this month' );
		// Set to last day of month
		$end_date->modify( 'last day of this month' );

		// Initialize result array
		$chart_data = [];

		// Clone start date to iterate through months
		$current_date = clone $start_date;

		while ( $current_date <= $end_date ) {

			$year = $current_date->format( 'Y' );
			$month = $current_date->format( 'm' );
			$month_folder_path = self::ADBC_ANALYTICS_TABLES_DIR . "/{$year}";
			$month_file_path = $month_folder_path . "/{$month}.txt";

			// Check if file exists
			if ( ADBC_Files::instance()->exists( $month_file_path ) ) {

				// Get the last day data for this month
				$month_data = $this->get_last_day_data_for_month( $month_file_path );

				if ( ! empty( $month_data ) ) {

					$month_key = $year . '-' . $month;

					// Process each table
					foreach ( $month_data as $table_name => $table_data ) {

						// Skip if specific tables are requested and this table is not in the list
						if ( ! empty( $tables ) && ! in_array( $table_name, $tables ) )
							continue;

						// Initialize table data if not exists
						if ( ! isset( $chart_data[ $table_name ] ) )
							$chart_data[ $table_name ] = [];

						// Calculate monthly aggregate changes
						$monthly_changes = $this->calculate_monthly_changes( $month_file_path, $table_name );

						// Store table metrics for this month
						$chart_data[ $table_name ][ $month_key ] = [ 
							's' => $table_data['s'],
							'r' => $table_data['r'],
							'c' => $table_data['c'],
							'z' => $monthly_changes['z'],
							'w' => $monthly_changes['w'],
							'm' => $monthly_changes['m']
						];
					}
				}
			}

			// Move to next month
			$current_date->modify( 'first day of next month' );
		}

		return $chart_data;
	}

	/**
	 * Get the last week analytics database size. The current day size is fetched from the database in real-time.
	 * 
	 * @return array An associative array of the database size data keyed by date.
	 */
	public function get_last_week_database_size() {

		// Get the last 7 days of data from the analytics files
		$start_date = date( 'Y-m-d', strtotime( '-6 days' ) );
		$end_date = date( 'Y-m-d' );
		$last_week_data = $this->get_database_chart_data_by_day( $start_date, $end_date );

		// Get the current database size
		$current_database_size = ADBC_Database::get_database_size_sql( false );

		// Create an array with all days of the last week initialized to 0
		$result = [];
		for ( $i = 6; $i >= 0; $i-- ) {
			$date = date( 'Y-m-d', strtotime( "-$i days" ) );
			$result[ $date ] = 0;
		}

		// Fill in the values we have from the database
		foreach ( $last_week_data as $date => $data ) {
			$result[ $date ] = $data['s'];
		}

		// Set today's value to the current database size
		$today = date( 'Y-m-d' );
		$result[ $today ] = $current_database_size;

		return $result;

	}

	/**
	 * Analyze the added and deleted tables arrays for a specific month and returns the final state of the added and deleted tables in this month compared to the previous month.
	 * 
	 * @param array $added_tables   Array of added tables names.
	 * @param array $deleted_tables Array of deleted tables names.
	 * 
	 * @return array An array containing the final state of the added and deleted tables in this month compared to the previous month.
	 */
	private function get_final_month_added_deleted_tables( $added_tables, $deleted_tables ) {

		$added_count = array_count_values( $added_tables );
		$deleted_count = array_count_values( $deleted_tables );

		$final_added = [];
		$final_deleted = [];

		// Get all unique tables
		$all_tables = array_unique( array_merge( $added_tables, $deleted_tables ) );

		foreach ( $all_tables as $table ) {

			$add_count = isset( $added_count[ $table ] ) ? $added_count[ $table ] : 0;
			$del_count = isset( $deleted_count[ $table ] ) ? $deleted_count[ $table ] : 0;

			$difference = $add_count - $del_count;

			if ( $difference > 0 )
				$final_added[] = $table;
			elseif ( $difference < 0 )
				$final_deleted[] = $table;
		}

		return [ $final_added, $final_deleted ];
	}

	/**
	 * Save the database analytics data if it's not already saved for the current day.
	 * 
	 * @return bool True if the data was saved successfully, false otherwise.
	 */
	private function save_database_analytics() {

		// Try to get the last saved data from the current year file
		[ $last_date, $is_current_date_saved ] = $this->get_last_saved_database_analytics_date();

		// Don't save analytics if the last saved date is the same as the current date
		if ( $last_date === $this->current_date && $is_current_date_saved === true )
			return false;

		// Get the added and deleted tables names
		[ $added_tables, $deleted_tables ] = $this->get_added_deleted_tables_names_from_last_saved();

		$analytics_data_line[ $this->current_date ] = array(
			'a' => $added_tables,
			'd' => $deleted_tables,
			's' => ADBC_Database::get_database_size_sql( false ),
			't' => ADBC_Database::get_number_of_tables()
		);

		$current_year_file_path_handle = ADBC_Files::instance()->get_file_handle( $this->current_year_database_file_path, 'a' );

		if ( $current_year_file_path_handle === false ) {
			ADBC_Logging::log_error( 'The analytics database file does not exist or is not readable/writable.', __METHOD__, __LINE__ );
			return false;
		}

		// Save the analytics data
		fwrite( $current_year_file_path_handle, json_encode( $analytics_data_line ) . "\n" );
		fclose( $current_year_file_path_handle );

		return true;

	}

	/**
	 * Save the tables analytics data if it's not already saved for the current day.
	 * 
	 * @return bool True if the data was saved successfully, false otherwise.
	 */
	private function save_tables_analytics() {

		// Get the last day the tables analytics data was saved
		[ $last_data, $is_current_date_saved ] = $this->get_last_saved_tables_analytics();

		// If the last saved day is the same as the current day, do nothing
		if ( key( $last_data ) === $this->current_date && $is_current_date_saved === true )
			return false;

		// Get the tables analytics data
		$current_data = ADBC_Tables::get_all_tables_info_for_analytics();

		// Get the date of the last saved data
		$last_date = key( $last_data );

		// Add the size_change, rows_change and columns_change values by comparing the current data with the last saved data
		$this->add_changes_values_to_tables_analytics_data( $current_data, $last_data[ $last_date ] );

		// Create the file if it doesn't exist and get the handle
		$current_month_tables_file_path_handle = ADBC_Files::instance()->get_file_handle( $this->current_month_tables_file_path, 'a' );

		if ( $current_month_tables_file_path_handle === false ) {
			ADBC_Logging::log_error( 'The analytics tables file does not exist or is not readable/writable.', __METHOD__, __LINE__ );
			return false;
		}

		// Save the analytics data
		fwrite( $current_month_tables_file_path_handle, json_encode( [ $this->current_date => $current_data ] ) . "\n" );
		fclose( $current_month_tables_file_path_handle );

		return true;

	}

	/**
	 * Calculate and add the changes values to the tables analytics data by comparing the current data with the last saved data.
	 * 
	 * @param array $current_tables_data The current tables data.
	 * @param array $last_saved_data     The last saved tables data.
	 * 
	 * @return void
	 */
	private function add_changes_values_to_tables_analytics_data( &$current_tables_data, &$last_saved_data ) {

		foreach ( $current_tables_data as $table_name => $current_table_data ) {

			// If the table doesn't exist in the last saved data, add the current data as is
			if ( ! isset( $last_saved_data[ $table_name ] ) ) {
				$current_tables_data[ $table_name ]['z'] = 0;
				$current_tables_data[ $table_name ]['w'] = 0;
				$current_tables_data[ $table_name ]['m'] = 0;
				continue;
			}

			// Calculate the changes in size, rows and columns
			$current_tables_data[ $table_name ]['z'] = $current_table_data['s'] - $last_saved_data[ $table_name ]['s'];
			$current_tables_data[ $table_name ]['w'] = $current_table_data['r'] - $last_saved_data[ $table_name ]['r'];
			$current_tables_data[ $table_name ]['m'] = $current_table_data['c'] - $last_saved_data[ $table_name ]['c'];
		}
	}

	/**
	 * Get the last saved database analytics data.
	 * 
	 * @return array An array containing the last saved database data and a boolean indicating if the date was saved in the current date. Empty array if no data was found.
	 */
	private function get_last_saved_database_analytics_date() {

		// Try to get the last saved data from the current year file
		$last_data = $this->get_last_line_data( $this->current_year_database_file_path );

		// Return the last saved data if it exists
		if ( $last_data )
			return [ key( $last_data ), true ];

		$years_files = ADBC_Files::instance()->get_list_of_dirs_inside_dir( self::ADBC_ANALYTICS_DATABASE_DIR, true );

		// If there are no years files, return an empty array
		if ( empty( $years_files ) )
			return [ '', false ];

		// Reverse the array to get the last year file first
		$years_files = array_reverse( $years_files );

		// loop through the years files to get the last saved data
		foreach ( $years_files as $year_file ) {

			// Skip the current year file or empty files
			if ( $year_file === "{$this->current_year}.txt" || ADBC_Files::instance()->size( self::ADBC_ANALYTICS_DATABASE_DIR . "/$year_file" ) === 0 )
				continue;

			$last_date = $this->get_last_line_data( self::ADBC_ANALYTICS_DATABASE_DIR . "/$year_file" );

			if ( $last_date )
				return [ key( $last_date ), false ];

		}

		// If no data was found, return an empty string
		return [ '', false ];

	}

	/**
	 * Get the last saved tables analytics data.
	 * 
	 * @return array An array containing the last saved tables data and a boolean indicating if the date was saved in the current date. Empty array if no data was found.
	 */
	private function get_last_saved_tables_analytics() {

		// Try to get the last saved data from the current year and month
		$last_data = $this->get_last_line_data( $this->current_month_tables_file_path );

		if ( $last_data )
			return [ $last_data, true ];

		// If there's no other years folders, return an empty array
		$years_dirs = ADBC_Files::instance()->get_list_of_dirs_inside_dir( self::ADBC_ANALYTICS_TABLES_DIR, true );

		if ( empty( $years_dirs ) )
			return [[], false ];

		// Sort years in descending order to check the most recent first
		rsort( $years_dirs );

		foreach ( $years_dirs as $year_dir ) {

			// Get all month files in the current year directory
			$months_files = ADBC_Files::instance()->get_list_of_files_inside_dir( self::ADBC_ANALYTICS_TABLES_DIR . "/$year_dir", true );

			if ( empty( $months_files ) ) {
				continue;
			}

			// Sort month files in descending order (12.txt, 11.txt, etc.)
			rsort( $months_files );

			foreach ( $months_files as $month_file ) {

				// Skip the current month file if we're in the current year (already checked)
				if ( $year_dir == $this->current_year && $month_file == $this->current_month . ".txt" )
					continue;

				// Skip empty files
				$file_path = self::ADBC_ANALYTICS_TABLES_DIR . "/$year_dir/$month_file";
				if ( ADBC_Files::instance()->size( $file_path ) === 0 )
					continue;

				// Get the last line data
				$last_data = $this->get_last_line_data( $file_path );
				if ( $last_data )
					return [ $last_data, false ];
			}
		}

		// If no data was found, return an empty array
		return [[], false ];
	}

	/**
	 * Get the JSON encoded data from the last line of a file.
	 * 
	 * @param string $file_path Path to the file.
	 * 
	 * @return array JSON Data encoded from the last line of the file. Empty array if no data was found.
	 */
	private function get_last_line_data( $file_path ) {

		// read line by line to get the last day
		$file_path_handle = ADBC_Files::instance()->get_file_handle( $file_path, 'r' );

		if ( $file_path_handle === false )
			return [];

		$last_data = [];

		while ( ( $line = fgets( $file_path_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			if ( empty( $line ) )
				continue;

			$last_data = json_decode( $line, true );
		}

		fclose( $file_path_handle );

		return $last_data;
	}

	/**
	 * Get the added and deleted tables names by comparing the current tables names with the last saved tables names.
	 * 
	 * @return array An array containing two arrays, added tables names and deleted tables names.
	 */
	private function get_added_deleted_tables_names_from_last_saved() {

		// Get the last saved tables data
		[ $last_data, $is_current_date ] = $this->get_last_saved_tables_analytics();

		// If the last data is empty, return an empty array
		if ( empty( $last_data ) )
			return [[], []];

		// Get the last saved table names
		$last_saved_tables = array_keys( reset( $last_data ) ); // get the first element of the array and get its keys

		// Get the current tables names
		$current_tables = array_keys( ADBC_Tables::get_tables_names( 1000000, 0 ) );

		// Get the added tables
		$deleted_tables = array_values( array_diff( $last_saved_tables, $current_tables ) );

		// Get the deleted tables
		$added_tables = array_values( array_diff( $current_tables, $last_saved_tables ) );

		return [ $added_tables, $deleted_tables ];
	}

	/**
	 * Get the last day data from a month file.
	 *
	 * @param string $file_path Path to the month file.
	 * 
	 * @return array Data from the last day of the month.
	 */
	private function get_last_day_data_for_month( $file_path ) {

		$last_data = $this->get_last_line_data( $file_path );

		if ( empty( $last_data ) ) {
			return [];
		}

		// Return the data for the tables from the last day
		return reset( $last_data );
	}

	/**
	 * Calculate the aggregate changes over a month for a specific table to get the size, rows, and columns changes compared to the previous month.
	 *
	 * @param string $file_path  Path to the month file.
	 * @param string $table_name Name of the table.
	 * 
	 * @return array Aggregate changes for size, rows, and columns.
	 */
	private function calculate_monthly_changes( $file_path, $table_name ) {

		$file_handle = ADBC_Files::instance()->get_file_handle( $file_path, 'r' );

		// Return 0 changes if the file doesn't exist
		if ( $file_handle === false ) {
			return [ 
				'z' => 0,
				'w' => 0,
				'm' => 0
			];
		}

		// Initialize the changes
		$total_size_change = 0;
		$total_rows_change = 0;
		$total_columns_change = 0;

		// Read the file line by line and calculate the changes
		while ( ( $line = fgets( $file_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			if ( empty( $line ) ) {
				continue;
			}

			$data = json_decode( $line, true );

			if ( ! empty( $data ) ) {
				$day_data = reset( $data );

				if ( isset( $day_data[ $table_name ] ) ) {
					$table_data = $day_data[ $table_name ];

					// Add the changes if they exist
					if ( isset( $table_data['z'] ) ) {
						$total_size_change += $table_data['z'];
					}

					if ( isset( $table_data['w'] ) ) {
						$total_rows_change += $table_data['w'];
					}

					if ( isset( $table_data['m'] ) ) {
						$total_columns_change += $table_data['m'];
					}
				}
			}
		}

		fclose( $file_handle );

		return [ 
			'z' => $total_size_change,
			'w' => $total_rows_change,
			'm' => $total_columns_change
		];
	}

	/**
	 * Removes not requested tables data from the fetched data.
	 * 
	 * @param array &$data The data to filter (passed by reference)
	 * 
	 * @param array $tables The list of tables to keep
	 */
	private function remove_not_requested_tables_from_data( &$data, $tables ) {

		// If no tables are requested, return the data as is
		if ( empty( $tables ) )
			return;

		// Loop through the data and remove tables that are not requested
		foreach ( $data as $date => $tables_data ) {

			foreach ( $tables_data as $table_name => $table_data ) {

				if ( ! in_array( $table_name, $tables ) )
					unset( $data[ $date ][ $table_name ] );
			}
		}
	}

	/**
	 * Converts date-keyed data to table-keyed data
	 * 
	 * @param array $date_keyed_data The source data keyed by date
	 * 
	 * @param array &$table_keyed_data The destination array to populate (passed by reference)
	 */
	private function convert_date_keyed_to_table_keyed( $date_keyed_data, &$table_keyed_data ) {

		// Initialize the table-keyed array if not already
		if ( ! is_array( $table_keyed_data ) )
			$table_keyed_data = [];

		// Convert the date-keyed data to table-keyed data
		foreach ( $date_keyed_data as $date => $tables_data ) {

			foreach ( $tables_data as $table_name => $table_value ) {

				if ( ! isset( $table_keyed_data[ $table_name ] ) )
					$table_keyed_data[ $table_name ] = [];

				$table_keyed_data[ $table_name ][ $date ] = $table_value;

			}

		}

		// Sort the dates within each table
		foreach ( $table_keyed_data as $table_name => &$dates )
			ksort( $dates );

	}

	/**
	 * Update the execution setting for the analytics process.
	 * 
	 * @param string $type The type of execution setting to update (success or fail).
	 */
	private function update_execution_setting( $type ) {
		$addon_activity_settings = ADBC_Settings::instance()->get_setting( 'analytics_execution' );
		$addon_activity_settings[ $type ] = time();
		ADBC_Settings::instance()->update_settings( [ 'analytics_execution' => $addon_activity_settings ] );
	}

}
