<?php
/**
 * Reads file in reverse order
 *
 * @package advanced-analytics
 *
 * @since 1.1.1
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\ADVAN\Controllers\Reverse_Line_Reader' ) ) {
	/**
	 * Responsible for reading lines from the end of file.
	 *
	 * @since 1.1.1
	 */
	class Reverse_Line_Reader {
		const BUFFER_SIZE = 16384;
		const SEPARATOR   = PHP_EOL;

		/**
		 * Memory limit in MB before triggering cleanup or termination
		 */
		const MEMORY_LIMIT_MB = 32;

		/**
		 * Time limit in seconds for processing
		 */
		const TIME_LIMIT_SECONDS = 25;

		/**
		 * Maximum number of collected items to prevent memory exhaustion
		 */
		const MAX_COLLECTED_ITEMS = 1000;

		/**
		 * Keeps track of of the current position in the file.
		 *
		 * @var array
		 *
		 * @since 1.1.1
		 */
		private static $buffer = array( '' );

		/**
		 * Holds the value of the buffer size.
		 *
		 * @var int
		 *
		 * @since 1.1.1
		 */
		private static $buffer_size = self::BUFFER_SIZE;

		/**
		 * The file size.
		 *
		 * @var int
		 *
		 * @since 1.1.1
		 */
		private static $file_size = 0;

		/**
		 * Keeps track of of the current position in the file.
		 *
		 * @var int
		 *
		 * @since 1.1.1
		 */
		private static $pos = null;

		/**
		 * Stores the temp file handle for showing the truncated error log.
		 *
		 * @var resource
		 *
		 * @since 1.1.1
		 */
		private static $temp_handle = null;

		/**
		 * Stores the memory file handle for showing the truncated error log.
		 *
		 * @var resource
		 *
		 * @since 1.1.1
		 */
		private static $memory_handle = null;

		/**
		 * Stores the overflow file handle for spilling data to disk when memory is critical.
		 *
		 * @var resource|null
		 *
		 * @since 1.9.3
		 */
		private static $overflow_handle = null;

		/**
		 * Stores the error log file handle for reading the error log.
		 *
		 * @var resource
		 *
		 * @since 1.6.0
		 */
		private static $error_log_handle = null;

		/**
		 * Reads lines from given file reversed order.
		 *
		 * @param string|resource $file_or_handle - The file or handle to read from.
		 * @param function        $callback - The function to call back when result is returned.
		 * @param integer         $max_lines - Maximum number of lines to read.
		 * @param int|null        $pos - The current position to start reading from.
		 * @param bool            $temp_writer - Whether to write the error log to a temporary file or not.
		 *
		 * @return void|bool
		 *
		 * @since 1.1.1
		 */
		public static function read_file_from_end( $file_or_handle, $callback, &$max_lines = 0, $pos = null, bool $temp_writer = true ) {
			if ( \is_a( $file_or_handle, 'WP_Error' ) ) {
				return $file_or_handle;
			}

			if ( null === $pos ) {
				self::$pos    = -1;
				self::$buffer = array( '' );
			}
			if ( null === self::$error_log_handle && \is_string( $file_or_handle ) ) {
				if ( \file_exists( $file_or_handle ) && \is_readable( $file_or_handle ) ) {
					self::$error_log_handle = fopen( $file_or_handle, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
				} else {
					$max_lines = 0;

					self::reset_class_globals();

					return false;
				}
			} elseif ( null === self::$error_log_handle && \is_resource( $file_or_handle ) && ( 'handle' === \get_resource_type( $file_or_handle ) || 'stream' === get_resource_type( $file_or_handle ) ) ) {
				self::$error_log_handle = $file_or_handle;
			} elseif ( null === self::$error_log_handle ) {
				$max_lines = 0;

				self::reset_class_globals();

				return false;
			}
			// Lets check the size and act appropriately.
			if ( null === $pos ) {
				fseek( self::$error_log_handle, 0, SEEK_END );
				$size = ftell( self::$error_log_handle );
				if ( 0 === (int) $size ) {
					self::reset_class_globals();

					$max_lines = 0;

					return false;
				} elseif ( self::$buffer_size >= (int) $size ) {
					// self::$pos is holding negative values - so sum.
					self::$buffer_size = ( (int) $size ) + self::$pos;
				}

				// Adaptive buffer sizing based on available memory.
				$available_memory = self::get_available_memory_mb();
				if ( $available_memory < 50 ) { // Low memory system.
					self::$buffer_size = min( self::$buffer_size, 4096 ); // Use smaller buffer.
				} elseif ( $available_memory < 100 ) { // Medium memory system.
					self::$buffer_size = min( self::$buffer_size, 8192 ); // Use medium buffer.
				}
				// Otherwise use default BUFFER_SIZE.

				self::$file_size = - (int) $size;
			}

			// Initialize memory and time monitoring.
			$start_time      = time();
			$start_memory    = memory_get_usage( true );
			$processed_lines = 0;

			$line = self::readline();

			if ( null === $line ) {
				self::reset_class_globals();

				$max_lines = 0;

				return false;
			} else {
				$line = \esc_html( $line );
			}

			if ( $temp_writer ) {
				self::write_memory_file( $line . self::SEPARATOR );
			}
			$result = $callback( $line, self::$pos );

			// Memory and time safety checks.
			++$processed_lines;
			$current_memory_mb = ( memory_get_usage( true ) - $start_memory ) / 1024 / 1024;
			$elapsed_time      = time() - $start_time;

			// Terminate if memory limit exceeded.
			if ( $current_memory_mb > self::MEMORY_LIMIT_MB ) {
				if ( function_exists( 'error_log' ) ) {
					error_log( 'Reverse_Line_Reader: Memory limit exceeded (' . round( $current_memory_mb, 2 ) . 'MB), terminating processing' );
				}
				self::reset_class_globals();
				$max_lines = 0;
				return false;
			}

			// Create overflow file if memory is getting critical (80% of limit).
			if ( $current_memory_mb > ( self::MEMORY_LIMIT_MB * 0.8 ) && null === self::$overflow_handle ) {
				self::$overflow_handle = self::create_overflow_temp_file();
				if ( self::$overflow_handle && function_exists( 'error_log' ) ) {
					error_log( 'Reverse_Line_Reader: Created overflow file due to high memory usage (' . round( $current_memory_mb, 2 ) . 'MB)' );
				}
			}

			// Terminate if time limit exceeded.
			if ( $elapsed_time > self::TIME_LIMIT_SECONDS ) {
				if ( function_exists( 'error_log' ) ) {
					error_log( 'Reverse_Line_Reader: Time limit exceeded (' . $elapsed_time . 's), terminating processing' );
				}
				self::reset_class_globals();
				$max_lines = 0;
				return false;
			}

			if ( true === $result['close'] ) {
				self::reset_class_globals();

				$max_lines = 0;

				return false;
			}
			if ( $max_lines > 0 ) {
				if ( $result['line_done'] && ! $result['no_flush'] ) {
					if ( $temp_writer ) {
						self::flush_memory_file_to_temp();
					}
					--$max_lines;
				}
				if ( $result['line_done'] && $result['no_flush'] ) {

					if ( null !== self::$memory_handle ) {
						\fclose( self::$memory_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

						self::$memory_handle = null;
					}
				}
				if ( 0 === $max_lines ) {
					self::reset_class_globals();

					$max_lines = 0;

					return false;
				}
			}
		}

		/**
		 * Reads buffer from the end of the file backwards to the beginning.
		 *
		 * @param int $size - The buffer size to read.
		 *
		 * @return string|false
		 *
		 * @since 1.1.1
		 */
		public static function read( int $size ) {
			self::$pos -= $size;
			if ( 0 === self::$pos ) {
				fseek( self::$error_log_handle, 0 );
			} else {
				if ( self::$pos < self::$file_size ) {
					$size      = abs( abs( self::$pos ) - abs( self::$file_size ) - self::$buffer_size );
					self::$pos = self::$file_size;
				}
				fseek( self::$error_log_handle, self::$pos, SEEK_END );
			}
			$read_string = fread( self::$error_log_handle, $size ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread

			return $read_string;
		}

		/**
		 * Reads line from file
		 *
		 * @return string
		 *
		 * @since 1.1.1
		 */
		public static function readline() {
			$buffer =& self::$buffer;
			while ( true ) {
				if ( 0 === self::$pos || self::$pos <= self::$file_size ) {

					if ( self::$pos < self::$file_size ) {

						self::$buffer_size = abs( ( self::$file_size - -self::$buffer_size ) + 1 );
						self::$pos         = self::$buffer_size;
						$buffer            = explode( self::SEPARATOR, self::read( self::$buffer_size ) . ( ( isset( $buffer[0] ) ) ? $buffer[0] : '' ) );

						self::$pos = 0;

						return array_pop( $buffer );
					}

					return array_pop( $buffer );
				}
				if ( count( $buffer ) > 1 ) {
					return array_pop( $buffer );
				}
				// Read next chunk from the file into the buffer.
				$buffer = explode( self::SEPARATOR, self::read( self::$buffer_size ) . ( ( isset( $buffer[0] ) ) ? $buffer[0] : '' ) );
			}
		}

		/**
		 * Writes temporary file used lated on to show the content of the error log (in reverse order and truncated to the last couple of errors)
		 *
		 * @param string $line - The line to be written.
		 *
		 * @return void
		 *
		 * @since 1.1.1
		 */
		public static function write_temp_file( string $line ) {
			if ( null === self::$temp_handle ) {
				self::$temp_handle = fopen( 'php://temp', 'w+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			}

			fwrite( self::$temp_handle, $line ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		}

		/**
		 * Writes memory file used lated on to show the content of the error log (in reverse order and truncated to the last couple of errors)
		 *
		 * @param string $line - The line to be written.
		 *
		 * @return void
		 *
		 * @since 1.1.1
		 */
		public static function write_memory_file( string $line ) {
			// If overflow file is active, write to it instead of memory.
			if ( null !== self::$overflow_handle ) {
				fwrite( self::$overflow_handle, $line ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
				return;
			}

			if ( null === self::$memory_handle ) {
				self::$memory_handle = fopen( 'php://memory', 'w+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			}

			fwrite( self::$memory_handle, $line ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		}

		/**
		 * Reads the contents of the temp file and returns the contents.
		 *
		 * @return void
		 *
		 * @since 1.1.1
		 */
		public static function read_temp_file() {
			if ( \is_resource( self::$temp_handle ) && ( 'handle' === get_resource_type( self::$temp_handle ) || 'stream' === get_resource_type( self::$temp_handle ) ) ) {
				rewind( self::$temp_handle ); // resets the position of pointer.

				// Content is pre-escaped with esc_html() before being written to temp file
				echo fread( self::$temp_handle, fstat( self::$temp_handle )['size'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread, WordPress.Security.EscapeOutput.OutputNotEscaped

				fclose( self::$temp_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}
		}

		/**
		 * Reads the contents of the memory file and returns the contents.
		 *
		 * @return void
		 *
		 * @since 1.1.1
		 */
		public static function read_memory_file() {
			// If overflow file exists, read from it instead.
			if ( \is_resource( self::$overflow_handle ) && ( 'handle' === get_resource_type( self::$overflow_handle ) || 'stream' === get_resource_type( self::$overflow_handle ) ) ) {
				rewind( self::$overflow_handle ); // resets the position of pointer.

				// Content is pre-escaped with esc_html() before being written to overflow file.
				echo fread( self::$overflow_handle, fstat( self::$overflow_handle )['size'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread, WordPress.Security.EscapeOutput.OutputNotEscaped

				fclose( self::$overflow_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				self::$overflow_handle = null;
			} elseif ( \is_resource( self::$memory_handle ) && ( 'handle' === get_resource_type( self::$memory_handle ) || 'stream' === get_resource_type( self::$memory_handle ) ) ) {
				rewind( self::$memory_handle ); // resets the position of pointer.

				// Content is pre-escaped with esc_html() before being written to memory file.
				echo fread( self::$memory_handle, fstat( self::$memory_handle )['size'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread, WordPress.Security.EscapeOutput.OutputNotEscaped

				fclose( self::$memory_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}

			self::close_streams();
		}

		/**
		 * Writes the contents of the memory file to a temporary file in reverse order.
		 *
		 * @return void
		 *
		 * @since 1.5.0
		 */
		public static function flush_memory_file_to_temp() {
			// Handle overflow file first if it exists.
			if ( \is_resource( self::$overflow_handle ) && ( 'handle' === get_resource_type( self::$overflow_handle ) || 'stream' === get_resource_type( self::$overflow_handle ) ) ) {
				$line = '';
				for ( $x_pos = 0; fseek( self::$overflow_handle, $x_pos, SEEK_END ) !== -1; $x_pos-- ) { // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed
					$char = fgetc( self::$overflow_handle );

					if ( PHP_EOL === $char ) {
						self::write_temp_file( $line . PHP_EOL );
						$line = '';
						continue;
					} else {
						$line = $char . $line;
					}
				}
				if ( ! empty( $line ) ) {
					self::write_temp_file( $line . PHP_EOL );
				}
				fclose( self::$overflow_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				self::$overflow_handle = null;
			} elseif ( \is_resource( self::$memory_handle ) && ( 'handle' === get_resource_type( self::$memory_handle ) || 'stream' === get_resource_type( self::$memory_handle ) ) ) {
				$line = '';
				for ( $x_pos = 0; fseek( self::$memory_handle, $x_pos, SEEK_END ) !== -1; $x_pos-- ) { // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed
					$char = fgetc( self::$memory_handle );

					if ( PHP_EOL === $char ) {
						self::write_temp_file( $line . PHP_EOL );
						$line = '';
						continue;
					} else {
						$line = $char . $line;
					}
				}
				if ( ! empty( $line ) ) {
					self::write_temp_file( $line . PHP_EOL );
				}
				fclose( self::$memory_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				self::$memory_handle = null;
			}
		}

		/**
		 * Closes the streams.
		 *
		 * @return void
		 *
		 * @since 1.6.0
		 */
		public static function close_streams() {
			if ( \is_resource( self::$temp_handle ) && ( 'handle' === get_resource_type( self::$temp_handle ) || 'stream' === get_resource_type( self::$temp_handle ) ) ) {
				fclose( self::$temp_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

				self::$temp_handle = null;
			}
			if ( \is_resource( self::$memory_handle ) && ( 'handle' === get_resource_type( self::$memory_handle ) || 'stream' === get_resource_type( self::$memory_handle ) ) ) {
				fclose( self::$memory_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

				self::$memory_handle = null;
			}
			if ( \is_resource( self::$overflow_handle ) && ( 'handle' === get_resource_type( self::$overflow_handle ) || 'stream' === get_resource_type( self::$overflow_handle ) ) ) {
				fclose( self::$overflow_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

				self::$overflow_handle = null;
			}
		}

		/**
		 * Resets the class globals.
		 *
		 * @return void
		 *
		 * @since 1.6.0
		 */
		public static function reset_class_globals() {
			if ( \is_resource( self::$error_log_handle ) && ( 'handle' === get_resource_type( self::$error_log_handle ) || 'stream' === get_resource_type( self::$error_log_handle ) ) ) {
				\fclose( self::$error_log_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}

			if ( \is_resource( self::$overflow_handle ) ) {
				\fclose( self::$overflow_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}

			self::$buffer_size      = self::BUFFER_SIZE;
			self::$buffer           = array( '' );
			self::$pos              = null;
			self::$error_log_handle = null;
			self::$overflow_handle  = null;
		}

		/**
		 * Get available memory in MB
		 *
		 * @return int Available memory in MB
		 *
		 * @since latest
		 */
		public static function get_available_memory_mb() {
			$memory_limit = ini_get( 'memory_limit' );
			if ( '-1' === $memory_limit ) {
				return 256; // Assume 256MB if no limit.
			}

			$memory_limit_bytes = wp_convert_hr_to_bytes( $memory_limit );
			$current_usage      = memory_get_usage( true );
			$available          = max( 0, $memory_limit_bytes - $current_usage );

			return (int) ( $available / 1024 / 1024 );
		}

		/**
		 * Check if available memory is below critical threshold
		 *
		 * @return bool True if memory is critical, false otherwise
		 *
		 * @since latest
		 */
		public static function is_memory_critical() {
			$available_mb = self::get_available_memory_mb();
			return $available_mb < 10; // Less than 10MB available.
		}

		/**
		 * Sets temporary error log file in order to extract / set some data.
		 *
		 * @param string $file_path - The full path for the temp file.
		 *
		 * @return \WP_Error|resource
		 *
		 * @since 1.9.3
		 */
		public static function set_temp_handle_from_file_path( string $file_path ) {
			// Basic validation: reject null bytes and disallow non-file stream wrappers.
			if ( '' === $file_path ) {
				self::$temp_handle = null;
				return self::$temp_handle;
			}

			if ( false !== strpos( $file_path, "\0" ) ) {
				return new \WP_Error( 'invalid_path', 'Invalid file path.' );
			}

			$scheme = \wp_parse_url( $file_path, PHP_URL_SCHEME );
			if ( false !== $scheme && null !== $scheme && '' !== $scheme && 'file' !== strtolower( (string) $scheme ) ) {
				return new \WP_Error( 'invalid_scheme', 'Only local file paths are allowed.' );
			}

			$dir = dirname( $file_path );
			if ( ! is_dir( $dir ) ) {
				return new \WP_Error( 'directory_not_found', 'The directory does not exist: ' . $dir );
			}

			// Resolve the directory to its real path to avoid traversal and symlink surprises.
			$real_dir = realpath( $dir );
			if ( false === $real_dir || ! is_dir( $real_dir ) ) {
				return new \WP_Error( 'directory_resolution_failed', 'Unable to resolve directory path.' );
			}

			if ( ! is_writable( $real_dir ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
				return new \WP_Error( 'directory_not_writable', 'The directory is not writable: ' . $real_dir );
			}

			// Rebuild the final path from the resolved directory and the base filename to prevent path tricks.
			$final_path = rtrim( $real_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . basename( $file_path );

			// Choose a read/write binary mode so later reads work as expected.
			$mode = 'w+';

			// Attempt to open the file.
			$handle = @fopen( $final_path, $mode ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			if ( false === $handle ) {
				return new \WP_Error( 'file_open_error', 'Unable to open or create the file: ' . $final_path );
			}

			self::$temp_handle = $handle;

			return self::$temp_handle;
		}

		/**
		 * Create a temporary file for overflow data when memory is critical
		 *
		 * @return resource|false File handle or false on failure
		 *
		 * @since latest
		 */
		public static function create_overflow_temp_file() {
			$temp_dir  = get_temp_dir();
			$temp_file = tempnam( $temp_dir, 'advana_overflow_' );

			if ( $temp_file && is_writable( $temp_file ) ) {
				return fopen( $temp_file, 'w+' );
			}

			return false;
		}

		/**
		 * Clean up temporary overflow files
		 *
		 * @return void
		 *
		 * @since latest
		 */
		public static function cleanup_overflow_files() {
			$temp_dir = get_temp_dir();
			$pattern  = $temp_dir . '/advana_overflow_*';

			foreach ( glob( $pattern ) as $file ) {
				if ( is_file( $file ) && filemtime( $file ) < time() - 3600 ) { // Older than 1 hour.
					unlink( $file );
				}
			}
		}
	}
}
