<?php
/**
 * Ajax Class
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
use KolorWeb\KWLogManager\Log;
use KolorWeb\KWLogManager\Settings;


/**
 * Handle ajax requests
 *
 * @since 1.0.0
 */
class Ajax {

	use IsSingleton;


	/**
	 * Initialize Log
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Register ajax actions.
		add_action( 'wp_ajax_get-log', array( $this, 'get_log_details' ) );
		add_action( 'wp_ajax_log-changed', array( $this, 'check_if_log_modified' ) );
		add_action( 'wp_ajax_log-exists', array( $this, 'check_if_log_exists' ) );
		add_action( 'wp_ajax_debug-enabled', array( $this, 'check_if_debug_enabled' ) );
		add_action( 'wp_ajax_toggle-debugging', array( $this, 'toggle_debugging_status' ) );
		add_action( 'wp_ajax_get-entries', array( $this, 'get_log_entries' ) );
		add_action( 'wp_ajax_get-entries-if-modified', array( $this, 'get_log_entries_if_modified' ) );
		add_action( 'wp_ajax_clear-log', array( $this, 'clear_log' ) );
		add_action( 'wp_ajax_get-global-settings', array( $this, 'get_global_settings' ) );
		add_action( 'wp_ajax_update-global-settings', array( $this, 'update_global_settings' ) );
		add_action( 'wp_ajax_get-user-settings', array( $this, 'get_user_settings' ) );
		add_action( 'wp_ajax_update-user-settings', array( $this, 'update_user_settings' ) );
	}


	/**
	 * Check if user has permission to access KolorWeb Log Manager
	 *
	 * @since 1.1.6
	 */
	public function check_if_authorized() {
		$authorized = \current_user_can( 'manage_options' );
		return apply_filters( 'kwlm_user_authorized', $authorized );
	}

	/**
	 * Get log file details.
	 *
	 * @since 1.0.0
	 */
	public function get_log_details() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log    = Log::get_instance();
		$config = Config::get_instance();

		wp_send_json(
			array(
				'debugEnabled'  => $config->debug_enabled(),
				'debugDetected' => $config->debug_status_detected(),
				'entries'       => $log->get_entries(),
				'found'         => $log->file_exists(),
				'timezone'      => $log->get_timezone(),
				'modified'      => $log->last_modified(),
				'filesize'      => $log->get_file_size(),
			)
		);
	}

	/**
	 * Check if log file has been modified
	 *
	 * @since 1.0.0
	 */
	public function check_if_log_modified() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log      = Log::get_instance();
		$modified = isset( $_REQUEST['modified'] ) ? strtotime( sanitize_text_field( wp_unslash( $_REQUEST['modified'] ) ) ) : '';

		wp_send_json(
			array(
				'modified'  => isset( $_REQUEST['modified'] ) && $log->is_modified( $modified ) ? true : false,
				'truncated' => $log->is_smaller(),
			)
		);
	}


	/**
	 * Check if log file exists
	 *
	 * @since 1.0.0
	 */
	public function check_if_log_exists() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log = Log::get_instance();

		wp_send_json(
			array(
				'exists' => $log->file_exists(),
			)
		);
	}


	/**
	 * Check if debugging is enabled
	 *
	 * @since 1.0.0
	 */
	public function check_if_debug_enabled() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$config = Config::get_instance();

		wp_send_json(
			array(
				'debugEnabled'  => $config->debug_enabled(),
				'debugDetected' => $config->debug_status_detected(),
			)
		);
	}


	/**
	 * Toggle debugging status
	 *
	 * @since 1.0.0
	 */
	public function toggle_debugging_status() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$config     = Config::get_instance();
		$new_status = isset( $_POST['status'] ) && 1 === intval( $_POST['status'] ) ? true : false;
		$old_status = ! $new_status;
		$changed    = $new_status ? $config->enable_debugging() : $config->disable_debugging();

		wp_send_json(
			array(
				'changed' => $changed,
				'status'  => $new_status,
			)
		);
	}


	/**
	 * Get log entries
	 *
	 * @since 1.0.0
	 */
	public function get_log_entries() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log       = Log::get_instance();
		$truncated = $log->is_smaller();

		wp_send_json(
			array(
				'truncated' => $truncated,
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// 'entries'   => $truncated ? $log->get_recent_entries() : $log->get_entries()
				'entries'   => $log->get_entries(),
				'modified'  => $log->last_modified(),
				'filesize'  => $log->get_file_size(),
			)
		);
	}


	/**
	 * Get log entries if log file has been modified
	 *
	 * @since 1.0.0
	 */
	public function get_log_entries_if_modified() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log      = Log::get_instance();
		$modified = isset( $_REQUEST['modified'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['modified'] ) ) : '';
		if ( isset( $_REQUEST['modified'] ) && $log->is_modified( $modified ) ) {

			$truncated = $log->is_smaller();

			wp_send_json(
				array(
					'truncated' => $truncated,
					'entries'   => $log->get_entries(),
					'modified'  => $log->last_modified(),
					'filesize'  => $log->get_file_size(),
					'changed'   => true,
				)
			);
		}

		wp_send_json(
			array(
				'changed' => false,
			)
		);
	}


	/**
	 * Clear log file
	 *
	 * @since 1.0.0
	 */
	public function clear_log() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$log = Log::get_instance();

		wp_send_json(
			array(
				'cleared' => $log->clear() || $log->delete() ? true : false,
			)
		);
	}


	/**
	 * Get global settings
	 *
	 * @since 1.0.0
	 */
	public function get_global_settings() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$handler  = Settings::get_instance();
		$settings = $handler->get_global_settings();

		wp_send_json(
			array(
				'settings' => $settings,
			)
		);
	}


	/**
	 * Update global settings
	 *
	 * @since 1.0.0
	 */
	public function update_global_settings() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$updated = false;

		$handler  = Settings::get_instance();
		$settings = isset( $_REQUEST['settings'] ) ? $handler->clean_settings( wp_unslash( $_REQUEST['settings'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$updated  = $handler->update_global_settings( $settings );

		wp_send_json(
			array(
				'updated' => $updated,
			)
		);
	}


	/**
	 * Get user settings
	 *
	 * @since 1.0.0
	 */
	public function get_user_settings() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$settings = array();

		if ( isset( $_REQUEST['user_id'] ) ) {
			$handler  = Settings::get_instance();
			$settings = $handler->get_user_settings( intval( $_REQUEST['user_id'] ) );
		}

		wp_send_json(
			array(
				'settings' => $settings,
			)
		);
	}


	/**
	 * Update user settings
	 *
	 * @since 1.0.0
	 */
	public function update_user_settings() {

		if ( ! $this->check_if_authorized() ) {
			die( 'Busted!' );
		}

		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'kwlm-nonce' ) ) {
			die( 'Busted!' );
		}

		$updated = false;

		if ( isset( $_REQUEST['user_id'] ) ) {
			$handler  = Settings::get_instance();
			$settings = isset( $_REQUEST['settings'] ) ? $handler->clean_settings( wp_unslash( $_REQUEST['settings'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$updated  = $handler->update_user_settings( intval( $_REQUEST['user_id'] ), $settings );
		}

		wp_send_json(
			array(
				'updated' => $updated,
			)
		);
	}
}
