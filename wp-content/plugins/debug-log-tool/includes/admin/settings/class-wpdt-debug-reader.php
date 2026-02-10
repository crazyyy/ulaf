<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Debug_Reader' ) ) :

	final class WPDT_Debug_Reader {

		/**
		 * Get debug logs.
		 *
		 * @param int $count Number of logs to get.
		 * @return array|string
		 */
		public static function get_debug_logs( $count ) {

			$log_file = self::get_debug_log_path();
			if ( ! file_exists( $log_file ) ) {
				return __( 'No debug log found.', 'debug-log-tool' );
			}

			$debug_data = self::read_log_from_end( $log_file, $count );

			$group_logs = get_option( 'wpdt_group_logs_status', false );
			return self::format_logs( $debug_data, (bool) $group_logs );
		}

		/**
		 * Read logs from the end of the file.
		 *
		 * @param string $file_path Path to log file.
		 * @param int    $count Number of logs to get.
		 * @return array
		 */
		private static function read_log_from_end( $file_path, $count ) {
			$logs = array();
			$fp = fopen( $file_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

			if ( ! $fp ) {
				return array();
			}

			fseek( $fp, 0, SEEK_END );
			$pos = ftell( $fp );

			$chunk_size = self::get_dynamic_chunk_size();
			$count = self::get_dynamic_log_count( $chunk_size );
			$buffer = '';
			$pattern = '/\[\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/';

			while ( $pos > 0 ) {
				if ( count( $logs ) >= $count ) {
					break;
				}

				$read_size = min( $chunk_size, $pos );
				$pos -= $read_size;
				fseek( $fp, $pos );
				$chunk = fread( $fp, $read_size ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread

				$buffer = $chunk . $buffer;

				preg_match_all( $pattern, $buffer, $matches, PREG_OFFSET_CAPTURE );
				$match_count = isset( $matches[0] ) ? count( $matches[0] ) : 0;

				if ( $match_count > 0 ) {
					$entries       = preg_split( $pattern, $buffer, -1, PREG_SPLIT_NO_EMPTY );
					$entries_count = count( $entries );

					for ( $i = $entries_count - 1; $i >= 0; $i-- ) {
						if ( count( $logs ) >= $count ) {
							break;
						}
						$logs[] = trim( $matches[0][ $i ][0] . $entries[ $i ] );
					}
				}
			}

			fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

			return array_reverse( $logs );
		}

		/**
		 * Format debug log function.
		 *
		 * @param array $log_data Log data.
		 * @param bool  $group_logs Whether to group similar logs.
		 * @return array
		 */
		public static function format_logs( $log_data, $group_logs = false ) {
			$formatted_logs = array();

			foreach ( $log_data as $log_entry ) {
				// Extract timestamp.
				preg_match( '/\[(.*?)\]/', $log_entry, $time_match );
				$time = isset( $time_match[1] ) ? $time_match[1] : '';
				$cleaned_date = str_replace( ' UTC', '', $time );

				$settings = get_option( 'wpdt_settings', array() );
				$timezone = isset( $settings['log_date_timezone'] ) ? $settings['log_date_timezone'] : 'UTC';
				if ( $timezone == 'local' ) {
					$date = DateTime::createFromFormat( 'd-M-Y H:i:s', $cleaned_date, new DateTimeZone( 'UTC' ) );
					if ( $date ) {
						$date->setTimezone( wp_timezone() );
						$cleaned_date = $date->format( 'd-M-Y H:i:s' );
					}
				}

				// Remove timestamp from log entry.
				$log_content = trim( str_replace( "[$time]", '', $log_entry ) );

				// Extract log type and message.
				if ( preg_match( '/^(PHP (Fatal error|Warning|Notice|Parse error|Deprecated|Error)): (.*)/s', $log_content, $matches ) ) {
					$type = $matches[1];
					$log  = trim( $matches[3] );
				} elseif ( preg_match( '/^(WordPress database error) (.*)/s', $log_content, $matches ) ) {
					$type = $matches[1];
					$log  = trim( $matches[2] );
				} else {
					$type = 'Other';
					$log  = trim( $log_content );
				}

				$formatted_logs[] = array(
					'type' => $type,
					'log'  => $log,
					'date' => $cleaned_date,
				);
			}

			if ( $group_logs ) {
				$grouped = array();

				foreach ( $formatted_logs as $entry ) {
					$key = md5( $entry['type'] . '|' . $entry['log'] );

					if ( ! isset( $grouped[ $key ] ) ) {
						$grouped[ $key ] = array(
							'type'       => $entry['type'],
							'log'        => $entry['log'],
							'date'       => $entry['date'],
							'first_seen' => $entry['date'],
							'occurences' => 1,
						);
					} else {
						$grouped[ $key ]['occurences'] += 1;
						$grouped[ $key ]['date']        = $entry['date'];
					}
				}

				return array_values( $grouped );
			}

			foreach ( $formatted_logs as &$entry ) {
				$entry['occurences'] = 1;
				$entry['first_seen'] = $entry['date'];
			}

			return $formatted_logs;
		}

		/**
		 * Get debug log path.
		 *
		 * @return string|bool
		 */
		public static function get_debug_log_path() {
			if ( defined( 'WP_DEBUG_LOG' ) ) {
				if ( is_string( WP_DEBUG_LOG ) && WP_DEBUG_LOG !== '' ) {
					return WP_DEBUG_LOG;
				} elseif ( WP_DEBUG_LOG === true ) {
					return WP_CONTENT_DIR . '/debug.log';
				}
			}
			return false;
		}

		/**
		 * Get dynamic chunk size based on PHP memory limit.
		 *
		 * @return int Chunk size in bytes.
		 */
		private static function get_dynamic_chunk_size() {
			$limit = ini_get( 'memory_limit' );
			if ( ! $limit || $limit === '-1' ) {
				$bytes = 128 * 1024 * 1024; // Default to 128MB.
			} else {
				$unit = strtolower( substr( $limit, -1 ) );
				$bytes = (int) $limit;
				switch ( $unit ) {
					case 'g':
						$bytes *= 1024 * 1024 * 1024;
						break;
					case 'm':
						$bytes *= 1024 * 1024;
						break;
					case 'k':
						$bytes *= 1024;
						break;
				}
			}
			// Use up to 25% of memory, max 8MB, min 512KB.
			return max( min( (int) ( $bytes * 0.25 ), 8 * 1024 * 1024 ), 512 * 1024 );
		}

		/**
		 * Get dynamic log count based on chunk size.
		 *
		 * @param int $chunk_size Chunk size in bytes.
		 * @return int Log count.
		 */
		private static function get_dynamic_log_count( $chunk_size ) {
			// Assume average log entry is 2KB, min 100 logs.
			return max( (int) ( $chunk_size / 2048 ), 100 );
		}
	}

endif;
