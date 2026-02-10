<?php
/**
 * Log Class
 *
 * @package   kw-log-manager
 * @author    Vincenzo Casu <vincenzo.casu@gmail.com>
 * @link      https://kolorweb.it
 */

namespace KolorWeb\KWLogManager;

if ( ! defined( 'KWLOGMANAGER_BASE' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}


/**
 * Dependencies
 */
use KolorWeb\KWLogManager\Characteristic\IsSingleton;
use KolorWeb\KWLogManager\Config;

/**
 * Log file handler
 *
 * @since 1.0.0
 */
class Log {

	use IsSingleton;

	/**
	 * Path to debug.log file
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $log_file = '';

	/**
	 * Config
	 *
	 * @since 1.0.2
	 *
	 * @var KolorWeb\KWLogManager\Config;
	 */
	private $config;

	/**
	 * Initialize Log
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->log_file = apply_filters( 'kwlm_log_file_path', WP_CONTENT_DIR . '/debug.log' );
		$this->config   = Config::get_instance();
	}


	/**
	 * Get debug.log path
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_debug_file_path() {
		return $this->log_file;
	}

	/**
	 * Check if debug.log file exists
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if debug.log exists
	 */
	public function file_exists() {
		return file_exists( $this->log_file );
	}


	/**
	 * Check if file was modified since provided timestamp
	 *
	 * @since 1.0.0
	 *
	 * @param int $timestamp Timestamp to check against.
	 * @return boolean True if was modified.
	 */
	public function is_modified( $timestamp = '' ) {
		if ( file_exists( $this->log_file ) ) {
			$now = filemtime( $this->log_file );
			if ( is_int( $timestamp ) ) {
				return $timestamp !== $now ? true : false;
			} elseif ( is_string( $timestamp ) ) {
				return gmdate( 'c', $now ) !== $timestamp ? true : false;
			}
		}

		return false;
	}


	/**
	 * Get last modified timestamp
	 *
	 * @since 1.0.0
	 *
	 * @return int Last modified timestamp
	 */
	public function last_modified() {
		if ( file_exists( $this->log_file ) ) {
			return gmdate( 'c', filemtime( $this->log_file ) );
		}

		return false;
	}


	/**
	 * Get timezone for server
	 *
	 * @since 1.0.0
	 *
	 * @return string The server timezone
	 */
	public function get_timezone() {
		return date_default_timezone_get();
	}


	/**
	 * Check if file is smaller in size than provided size in bytes
	 *
	 * @since 1.0.0
	 *
	 * @param int $size Size to check against.
	 * @return boolean True if file is smaller
	 */
	public function is_smaller( $size = '' ) {
		if ( file_exists( $this->log_file ) ) {
			return filesize( $this->log_file ) < intval( $size ) ? true : false;
		}

		return false;
	}


	/**
	 * Get the file size for the log file
	 *
	 * @since 1.0.0
	 *
	 * @return int The size in bytes
	 */
	public function get_file_size() {
		if ( file_exists( $this->log_file ) ) {
			return filesize( $this->log_file );
		}

		return false;
	}


	/**
	 * Get all log entries
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of entries
	 */
	public function get_entries() {

		$settings        = Settings::get_instance();
		$user_id         = \get_current_user_id();
		$user_settings   = $settings->get_settings( $user_id );
		$check_log_limit = intval( $user_settings['log_limit'] ) > 0 ? intval( $user_settings['log_limit'] ) : false;

		$sep     = '$!$';
		$entries = array();

		if ( $this->file_exists() ) {

			if ( false !== $check_log_limit ) {
				$last_modified = filemtime( $this->log_file );
				$content       = file( $this->log_file );
				if ( count( $content ) > $check_log_limit ) {
					$content = array_slice( $content, ( count( $content ) - $check_log_limit ) - 1 );
					file_put_contents( $this->log_file, $content );  // phpcs:ignore
					touch( $this->log_file, $last_modified ); // phpcs:ignore
				}
			}

			$fp = @fopen( $this->log_file, 'r' ); // phpcs:ignore

			if ( $fp ) {

				while ( false !== ( $line = @fgets( $fp ) ) ) { // phpcs:ignore

					$line  = preg_replace( '/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i', '$1' . $sep . '$2' . $sep . '$3' . $sep . '$4', $line );
					$parts = explode( $sep, $line );

					if ( count( $parts ) >= 4 ) {

						$entries[] = array(
							// phpcs:ignore
							// 'date'   => strtotime($parts[0] . ' ' . $parts[1] . ' ' . $parts[2]),
							'date'     => gmdate( 'Y-m-d', strtotime( $parts[0] ) ),
							'time'     => $parts[1],
							'timezone' => $parts[2],
							'message'  => stripslashes( str_replace( '\\', '/', $parts[3] ) ),
						);

					}
				}

				@fclose( $fp ); // phpcs:ignore

			}
		} else {

			touch( $this->log_file ); // phpcs:ignore

		}

		if ( false !== $check_log_limit ) {
			$filtered_entries = array_slice( $entries, -$check_log_limit, $check_log_limit, true );
			if ( is_array( $filtered_entries ) ) {
				$entries = $filtered_entries;
			}
		}

		return array_reverse( $entries );
	}


	/**
	 * Get all file contents
	 *
	 * @since 1.0.0
	 *
	 * @return string The contents or empty string
	 */
	public function get_contents() {
		if ( $this->file_exists() ) {
			return file_get_contents( $this->log_file ); // phpcs:ignore
		}

		return '';
	}


	/**
	 * Get all entries newer than specified timestamp
	 *
	 * @since 1.0.0
	 *
	 * @param int $timestamp The oldest date and time to get entries for.
	 * @return array Array of entries
	 */
	public function get_recent_entries( $timestamp ) {
		// TODO.
		return array();
	}

	/**
	 * Clear debug.log file
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if file was cleared
	 */
	public function clear() {

		$fp = @fopen( $this->log_file, 'r+' ); // phpcs:ignore
		return @ftruncate( $fp, 0 ); // phpcs:ignore
	}


	/**
	 * Delete debug.log file
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if file was deleted
	 */
	public function delete() {
		return true === unlink( $this->log_file ) ? true : false; // phpcs:ignore
	}
}
