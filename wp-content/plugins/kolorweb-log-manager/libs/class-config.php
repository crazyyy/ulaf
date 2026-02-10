<?php
/**
 * Config Class
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


/**
 * Log file handler
 *
 * @since 1.1.0
 */
class Config {

	use IsSingleton;

	/**
	 * Path to wp-config file
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $wp_config_file = '';


	/**
	 * Initialize Log
	 *
	 * @since 1.1.0
	 */
	public function init() {
		$this->wp_config_file = apply_filters( 'kwlm_wp_config_file_path', ABSPATH . '/wp-config.php' );
	}

	/**
	 * Get wp-config.php path
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_wp_config_file_path() {
		return $this->wp_config_file;
	}


	/**
	 * Check if debug.log file exists
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if debug.log exists
	 */
	public function file_exists() {
		return file_exists( $this->wp_config_file );
	}


	/**
	 * Maybe_bool_to_string.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param string $value value to check.
	 *
	 * @return string
	 */
	public function maybe_bool_to_string( $value ) {
		if ( ! is_string( $value ) ) {
			if ( filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
				return 'true';
			} else {
				return 'false';
			}
		}
		return $value;
	}


	/**
	 * Check if wp-config.php is writable.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @return mixed
	 */
	public function is_wp_config_writable() {
		WP_Filesystem();
		global $wp_filesystem;
		return $wp_filesystem->is_writable( $this->get_wp_config_file_path() );
	}


	/**
	 * Check if debugging is enabled
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if debugging is enabled
	 */
	public function debug_enabled() {

		return defined( 'WP_DEBUG' ) && ( true === WP_DEBUG || 'true' === WP_DEBUG ) ? true : false;
	}


	/**
	 * Check if debugging status can be toggled and saved.
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if status can be toggled and saved
	 */
	public function is_debug_toggleable() {

		return $this->file_exists() && $this->is_wp_config_writable();
	}


	/**
	 * Check if debug status was detected or not
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if debugging status was detected
	 */
	public function debug_status_detected() {
		return defined( 'WP_DEBUG' );
	}


	/**
	 * Enable debugging
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if debugging was enabled
	 */
	public function enable_debugging() {
		return $this->set_debugging_status( true );
	}


	/**
	 * Disable debugging
	 *
	 * @since 1.1.0
	 *
	 * @return boolean True if debugging was disabled
	 */
	public function disable_debugging() {
		return $this->set_debugging_status( false );
	}

	/**
	 * Set debugging status if possible
	 *
	 * @since 1.1.0
	 *
	 * @param boolean $status The new status.
	 * @return boolean True if status updated
	 */
	private function set_debugging_status( $status = false ) {

		return $this->update_debug_status( $status );
	}

	/**
	 * Update Debug Status.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 *
	 * @param bool $status New Debug Status.
	 *
	 * @return bool
	 * @throws \Exception Update wp-config.php exceptions.
	 */
	private function update_debug_status( $status ) {

		if ( ! $this->is_wp_config_writable() ) {
			return false;
		}

		$config_transformer = new \WPConfigTransformer( $this->get_wp_config_file_path() );
		$response           = false;
		$new_status         = false;

		try {

			$var_names = array(
				'WP_DEBUG'         => $status,
				'WP_DEBUG_LOG'     => $status,
				'WP_DEBUG_DISPLAY' => false,
			);

			$config_args = array(
				'raw'       => true,
				'normalize' => true,
			);

			foreach ( $var_names as $var_name => $value ) {

				$response = $config_transformer->update( 'constant', $var_name, $this->maybe_bool_to_string( $value ), $config_args );
				if ( 'WP_DEBUG' === $var_name ) {
					$new_status = $response;
				}
			}
		} catch ( \Exception $e ) {

			$response   = false;
			$new_status = false;
		}

		return $new_status;
	}
}
