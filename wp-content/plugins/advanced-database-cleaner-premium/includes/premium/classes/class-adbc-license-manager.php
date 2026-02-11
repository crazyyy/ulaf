<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC License Manager.
 *
 * This class handles license activation, deactivation, and status checking via EDD API.
 * It is designed to share the same storage as the EDD SDK, so both the SDK modal
 * and the custom ADBC UI read/write the same options.
 */
class ADBC_License_Manager {

	private const EDD_STORE_URL = 'https://edd-api.sigmaplugin.com'; // Call our proxy droplet
	private const EDD_ITEM_ID = 3289507;

	// Canonical option names shared with the EDD SDK.
	private const LICENSE_KEY_OPTION = 'adbc_plugin_license_key'; // Stores the raw license key.
	private const LICENSE_DATA_OPTION = 'adbc_plugin_license_key_license'; // Stores stdClass from EDD (status, expires, etc.).

	/**
	 * Register the SDK with the license manager.
	 * This registry handles activating and deactivating the license key, and checking for updates to the plugin.
	 *
	 * @param object $init The license manager instance.
	 * @return void
	 */
	public static function register_sdk( $init ) {
		$init->register(
			array(
				'id' => 'advanced-database-cleaner-premium',
				'url' => self::EDD_STORE_URL,
				'item_id' => self::EDD_ITEM_ID,
				'version' => ADBC_PLUGIN_VERSION,
				'file' => ADBC_MAIN_PLUGIN_FILE_PATH,
				'option_name' => self::LICENSE_KEY_OPTION, // IMPORTANT: this ties the EDD SDK storage to our own storage.
				'type' => 'plugin',
			)
		);
	}

	/**
	 * Activate a license key.
	 *
	 * @param string $license_key The license key to activate.
	 * @return array The response array with success status, message, and data.
	 */
	public static function activate_license( $license_key ) {

		$license_key = sanitize_text_field( trim( (string) $license_key ) );

		$response = self::call_edd_api( 'activate_license', $license_key );

		if ( ! $response['success'] )
			return $response;

		$license_data = $response['data'];

		if ( 'valid' !== $license_data->license )
			return self::format_error( $license_data->error );

		// Store the raw license key in the SDK's key option.
		update_option( self::LICENSE_KEY_OPTION, $license_key );

		// Store the full license object in the SDK's license-data option. This mirrors what the SDK's License::save() does.
		update_option( self::LICENSE_DATA_OPTION, $license_data );

		// Build sanitized data for returning to the UI.
		$license_info = array(
			'key' => ADBC_Common_Utils::mask_license_key( $license_key ),
			'status' => 'valid',
			'expires' => $license_data->expires ?? '',
			'price_id' => $license_data->price_id ?? '',
		);

		$license_info = array_map( 'sanitize_text_field', $license_info );

		return array(
			'success' => true,
			'message' => __( 'License activated successfully.', 'advanced-database-cleaner' ),
			'data' => $license_info,
		);
	}

	/**
	 * Deactivate a license key.
	 *
	 * @return array The response array with success status, message, and data.
	 */
	public static function deactivate_license() {

		$license_key = sanitize_text_field(
			trim( (string) get_option( self::LICENSE_KEY_OPTION, '' ) )
		);

		// Call API (result doesn't matter, we clean up locally regardless).
		if ( ! empty( $license_key ) )
			self::call_edd_api( 'deactivate_license', $license_key );

		// Clear canonical storage so both SDK & our UI see the same empty state.
		delete_option( self::LICENSE_KEY_OPTION );
		delete_option( self::LICENSE_DATA_OPTION );

		return array(
			'success' => true,
			'message' => __( 'License deactivated successfully.', 'advanced-database-cleaner' ),
			'data' => array(),
		);
	}

	/**
	 * Refresh license status in DB and return the result.
	 *
	 * @return array The response array with success status, message, and data.
	 */
	public static function refresh_license() {

		$license_key = sanitize_text_field(
			trim( (string) get_option( self::LICENSE_KEY_OPTION, '' ) )
		);

		if ( empty( $license_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'No license key found to check.', 'advanced-database-cleaner' ),
				'data' => null,
			);
		}

		$response = self::call_edd_api( 'check_license', $license_key );

		if ( ! $response['success'] )
			return $response;

		$license_data = $response['data'];

		// Update canonical storage with whatever EDD returned.
		update_option( self::LICENSE_DATA_OPTION, $license_data );

		// Prepare normalized info for UI.
		$license_info = array(
			'key' => ADBC_Common_Utils::mask_license_key( $license_key ),
			'status' => $license_data->license ?? '',
			'expires' => $license_data->expires ?? '',
			'price_id' => $license_data->price_id ?? '',
		);

		$license_info = array_map( 'sanitize_text_field', $license_info );

		$message = 'invalid' === $license_data->license
			? __( 'Your license key is invalid.', 'advanced-database-cleaner' )
			: __( 'License check completed.', 'advanced-database-cleaner' );

		return array(
			'success' => true,
			'message' => $message,
			'data' => $license_info,
		);
	}

	/**
	 * Make API call to EDD store.
	 *
	 * @param string $action      The action to perform (activate_license, deactivate_license, check_license).
	 * @param string $license_key The license key to use.
	 * @return array The response array with success status, message, and data.
	 */
	private static function call_edd_api( $action, $license_key ) {
		$api_params = array(
			'edd_action' => $action,
			'license' => $license_key,
			'item_id' => self::EDD_ITEM_ID,
			'url' => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		$response = wp_remote_post(
			self::EDD_STORE_URL,
			array(
				'timeout' => 15,
				'sslverify' => false,
				'body' => $api_params,
			)
		);

		// Handle HTTP errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
				'data' => null,
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'An error occurred, please try again.', 'advanced-database-cleaner' ),
				'data' => null,
			);
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		return array(
			'success' => true,
			'message' => '',
			'data' => $license_data,
		);
	}

	/**
	 * Format error message based on EDD error code.
	 *
	 * @param string $error_code The error code returned by EDD.
	 * @return array The formatted error response.
	 */
	private static function format_error( $error_code ) {
		$messages = array(
			'expired' => __( 'Your license key has expired.', 'advanced-database-cleaner' ),
			'revoked' => __( 'Your license key has been disabled.', 'advanced-database-cleaner' ),
			'disabled' => __( 'Your license key has been disabled.', 'advanced-database-cleaner' ),
			'missing' => __( 'Invalid license key.', 'advanced-database-cleaner' ),
			'site_inactive' => __( 'Your license is not active for this URL.', 'advanced-database-cleaner' ),
			'invalid' => sprintf(
				/* translators: %s is the plugin name */
				__( 'This appears to be an invalid license key for %s.', 'advanced-database-cleaner' ),
				'Advanced Database Cleaner'
			),
			'invalid_item_id' => sprintf(
				/* translators: %s is the plugin name */
				__( 'This appears to be an invalid license key for %s.', 'advanced-database-cleaner' ),
				'Advanced Database Cleaner'
			),
			'item_name_mismatch' => sprintf(
				/* translators: %s is the plugin name */
				__( 'This appears to be an invalid license key for %s.', 'advanced-database-cleaner' ),
				'Advanced Database Cleaner'
			),
			'key_mismatch' => sprintf(
				/* translators: %s is the plugin name */
				__( 'This appears to be an invalid license key for %s.', 'advanced-database-cleaner' ),
				'Advanced Database Cleaner'
			),
			'no_activations_left' => __( 'Your license key has reached its activation limit. Please deactivate it on any unused sites to be able to use it again, or consider upgrading your license to allow more activations.', 'advanced-database-cleaner' ),
			'license_not_activable' => __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'advanced-database-cleaner' ),
			'deactivated' => __( 'Your license key has been deactivated.', 'advanced-database-cleaner' ),
		);

		$message = $messages[ $error_code ] ?? __( 'An error has occurred, please try again.', 'advanced-database-cleaner' );

		return array(
			'success' => false,
			'message' => sanitize_text_field( $message ),
			'data' => null,
		);
	}

	/**
	 * Get the current license data stored in the database.
	 *
	 * @param bool $mask_key Whether to mask the license key in the returned data.
	 * @return array The license data array with key, status, expires, and price_id.
	 */
	public static function get_license_data( $mask_key = true ) {

		// 1. Get the raw key saved by the SDK
		$license_key = trim( (string) get_option( self::LICENSE_KEY_OPTION, '' ) );

		// If no key, return empty array
		if ( empty( $license_key ) )
			return [];

		// 2. Get license object saved by the SDK
		$license_obj = get_option( self::LICENSE_DATA_OPTION );

		// If no license object OR not a stdClass, return empty array
		if ( ! ( $license_obj instanceof \stdClass ) )
			return [];

		// 3. Return license data
		return array(
			'key' => $mask_key ? ADBC_Common_Utils::mask_license_key( $license_key ) : $license_key,
			'status' => sanitize_text_field( $license_obj->license ?? '' ),
			'expires' => sanitize_text_field( $license_obj->expires ?? '' ),
			'price_id' => sanitize_text_field( $license_obj->price_id ?? '' ),
		);
	}

	/**
	 * Removes the EDD SDK "Manage License" link from the Plugins page.
	 *
	 * @param array  $actions
	 * @param string $plugin_file
	 * @param array  $plugin_data
	 * @return array
	 */
	public static function filter_remove_sdk_manage_link( $actions, $plugin_file, $plugin_data ) {

		if ( isset( $actions['edd_sdk_manage'] ) ) {
			unset( $actions['edd_sdk_manage'] );
		}

		return $actions;
	}

	/**
	 * Get the plan name from the price ID.
	 *
	 * @return string The plan name.
	 */
	public static function get_plan_name() {

		$price_id = self::get_license_data()['price_id'] ?? '';

		if ( '' === $price_id ) {
			return '';
		}

		switch ( $price_id ) {
			case "1":
				return __( 'Starter plan', 'advanced-database-cleaner' );
			case "2":
				return __( 'Standard plan', 'advanced-database-cleaner' );
			case "3":
				return __( 'Business plan', 'advanced-database-cleaner' );
			case "4":
				return __( 'Agency plan', 'advanced-database-cleaner' );
			default:
				return __( 'Unknown plan', 'advanced-database-cleaner' );
		}

	}

	/**
	 * Get the formatted license expiration date.
	 *
	 * @return string The formatted expiration date or 'Lifetime' if applicable.
	 */
	public static function get_license_expiration() {

		$expires = self::get_license_data()['expires'] ?? '';

		if ( null === $expires || '' === $expires ) {
			return '';
		}

		// Normalize string values.
		if ( is_string( $expires ) ) {
			$expires = trim( $expires );
		}

		// Handle lifetime label.
		if ( is_string( $expires ) && 'lifetime' === strtolower( $expires ) ) {
			return __( 'Lifetime', 'advanced-database-cleaner' );
		}

		// Determine timestamp.
		if ( is_numeric( $expires ) ) {
			// JS version treats numeric as Unix seconds, so do the same.
			$timestamp = (int) $expires;
		} else {
			// Accept MySQL-style "Y-m-d H:i:s" or ISO-like strings.
			$timestamp = strtotime( (string) $expires );
		}

		// If parsing failed, fall back to the raw value.
		if ( ! $timestamp ) {
			return (string) $expires;
		}

		// Format like "December 10, 2025", but allow translators to adjust.
		$date_format = _x(
			'F j, Y',
			'License expiration date format (e.g. December 10, 2025)',
			'advanced-database-cleaner'
		);

		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $date_format, $timestamp );
		}

		// Fallback for very old WordPress versions.
		return date_i18n( $date_format, $timestamp );
	}

	/**
	 * Get the human-readable license status label.
	 *
	 * @return string The license status label.
	 */
	public static function get_license_status() {

		$license_data = self::get_license_data();
		$status = isset( $license_data['status'] ) ? (string) $license_data['status'] : '';

		if ( '' === $status ) {
			return '';
		}

		$normalized = strtolower( $status );

		switch ( $normalized ) {
			case 'valid':
				return __( 'Active', 'advanced-database-cleaner' );
			case 'expired':
				return __( 'Expired', 'advanced-database-cleaner' );
			case 'invalid':
				return __( 'Invalid', 'advanced-database-cleaner' );
			case 'disabled':
				return __( 'Disabled', 'advanced-database-cleaner' );
			case 'invalid_item_id':
				return __( 'Invalid item ID', 'advanced-database-cleaner' );
			case 'item_name_mismatch':
				return __( 'Item name mismatch', 'advanced-database-cleaner' );
			case 'inactive':
				return __( 'Inactive', 'advanced-database-cleaner' );
			case 'site_inactive':
				return __( 'Site inactive', 'advanced-database-cleaner' );
			default:
				// Fallback to the raw status string if it's some unexpected value.
				return $status;
		}
	}

}
