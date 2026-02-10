<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC_Automation_Events_Log class.
 * 
 * This class manages logging of events related to automation tasks.
 */
class ADBC_Automation_Events_Log extends ADBC_Singleton {

	public const EVENTS_RETENTION_MONTHS = 6;     // keep only the last N months

	/**
	 * Log an event for a specific task ID and keep the log file within the retention period.
	 * This method writes the event data to a temporary file, copies over previous events that are within the retention period, and then replaces the original file with the temporary file.
	 * 
	 * @param string $task_id The ID of the task.
	 * @param array $event_data Associative array containing event data, where the key is a timestamp and the value is the event details.
	 * 
	 * @return void
	 */
	public static function log_event( $task_id, $event_data ) {

		$events_file_path = ADBC_Automation::get_events_file( $task_id );
		$temp_events_file_path = "{$events_file_path}.tmp";

		// Create empty log file if it does not exist
		if ( ! ADBC_Files::instance()->exists( $events_file_path ) ) {
			ADBC_Files::instance()->put_contents( $events_file_path, '' );
		}

		$events_file_handle = ADBC_Files::instance()->get_file_handle( $events_file_path, 'r' );
		$temp_events_file_handle = ADBC_Files::instance()->get_file_handle( $temp_events_file_path, 'w' );

		if ( ! $events_file_handle || ! $temp_events_file_handle ) {
			return; // cannot write events file → do nothing
		}

		// write this event in the first line of the temp file (newest first)
		$current_event_line = json_encode( $event_data );
		fwrite( $temp_events_file_handle, $current_event_line . "\n" );

		// copy all previous events that are within the retention period from the original file to the temp file
		$cutoff_timestamp = strtotime( sprintf( '-%d months', self::EVENTS_RETENTION_MONTHS ) );

		while ( ( $line = fgets( $events_file_handle ) ) !== false ) {

			$line = rtrim( $line, "\r\n" );

			// Skip empty lines
			if ( $line === '' ) {
				continue;
			}

			$line_decoded = json_decode( $line, true );

			// Skip invalid lines
			if ( ! is_array( $line_decoded ) ) {
				continue;
			}

			// Write the line to the temp file if it is within the retention period
			$line_timestamp = (int) key( $line_decoded );
			if ( $line_timestamp >= $cutoff_timestamp ) {
				fwrite( $temp_events_file_handle, $line . "\n" );
			}

		}

		fclose( $events_file_handle );
		fclose( $temp_events_file_handle );

		// Replace the original events file with the temp file
		if ( ADBC_Files::instance()->exists( $temp_events_file_path ) && ! rename( $temp_events_file_path, $events_file_path ) ) {
			unlink( $temp_events_file_path );
			return;
		}

	}

	/**
	 * Clear the events file for a specific task ID.
	 * 
	 * @param string $task_id The ID of the task whose events file should be cleared.
	 * 
	 * @return bool True if the events was cleared successfully, false otherwise.
	 */
	public static function clear_events( $task_id ) {

		$task_events_file = ADBC_Automation::get_events_file( $task_id );
		if ( ADBC_Files::instance()->is_writable( $task_events_file ) === false ) {
			return false;
		}

		return ADBC_Files::instance()->put_contents( $task_events_file, '' );

	}

	/**
	 * Return one page of events (newest → oldest) and the global count.
	 *
	 * @param string $task_id
	 * @param int    $page   1-based page number requested by the UI
	 * @param int    $limit  events per page
	 *
	 * @return array{events:array, total_items:int}
	 */
	public static function get_events( $task_id, $page = 1, $limit = 50 ) {

		$offset = ( $page - 1 ) * $limit;              // first index we want to keep

		$events_file_path = ADBC_Automation::get_events_file( $task_id );
		$events_file_handle = ADBC_Files::instance()->get_file_handle( $events_file_path, 'r' );
		if ( ! $events_file_handle ) {
			return [ 'events' => [], 'total_items' => 0 ];
		}

		$events = [];
		$total_valid = 0;
		$window_end = $offset + $limit - 1;         // last index we want to keep

		while ( ( $line = fgets( $events_file_handle ) ) !== false ) {

			// skip empty lines
			$line = rtrim( $line, "\r\n" );
			if ( $line === '' ) {
				continue;
			}

			// skip invalid lines
			$line_decoded = json_decode( $line, true );
			if ( ! is_array( $line_decoded ) ) {
				continue;
			}

			$index = $total_valid++;

			// collect only if it falls inside the requested window
			if ( $index >= $offset && $index <= $window_end ) {
				$events[] = $line_decoded;
			}

		}

		fclose( $events_file_handle );

		return [ 
			'events' => $events,
			'total_items' => $total_valid,
		];

	}
}