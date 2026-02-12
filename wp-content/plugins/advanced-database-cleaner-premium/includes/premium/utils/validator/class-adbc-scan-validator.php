<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC scan validator class.
 * 
 * This class provides functions to validate and sanitize the scan data sent by the user to the endpoints.
 */
class ADBC_Scan_Validator {

	/**
	 * Sanitize the scan params sent by the user.
	 * 
	 * @param WP_REST_Request $data The scan params request.
	 * 
	 * @return array The sanitized scan params, each param is set to an empty string if invalid.
	 */
	public static function sanitize_scan_params( WP_REST_Request $data ) {

		$sanitized_params = [];

		// Get params from the request and sanitize them
		$items_type = sanitize_key( $data->get_param( 'items_type' ) );
		$scan_type = sanitize_key( $data->get_param( 'scan_type' ) );
		$preg_match = $data->get_param( 'preg_match' );
		$partial_match = $data->get_param( 'partial_match' );
		$what_to_scan = sanitize_key( $data->get_param( 'what_to_scan' ) );
		$override_manual_categorization = $data->get_param( 'override_manual_categorization' );
		$continue_scan = $data->get_param( 'continue_scan' );
		$selected_items_to_scan = $data->get_param( 'selected_items_to_scan' );

		// validate params
		$sanitized_params['items_type'] = ADBC_Common_Validator::sanitize_items_type( $items_type );
		$sanitized_params['scan_type'] = in_array( $scan_type, [ 'full', 'local' ] ) ? $scan_type : '';
		$sanitized_params['preg_match'] = is_bool( $preg_match ) ? $preg_match : '';
		$sanitized_params['partial_match'] = is_bool( $partial_match ) ? $partial_match : '';
		$sanitized_params['what_to_scan'] = in_array( $what_to_scan, [ 'all', 'uncategorized', 'selected' ] ) ? $what_to_scan : '';
		$sanitized_params['override_manual_categorization'] = is_bool( $override_manual_categorization ) ? $override_manual_categorization : '';
		$sanitized_params['continue_scan'] = is_bool( $continue_scan ) ? $continue_scan : '';

		// if the user has selected items to scan, validate them
		if ( ! is_array( $selected_items_to_scan ) || empty( $sanitized_params['items_type'] ) ) {
			$sanitized_params['selected_items_to_scan'] = [];
			return $sanitized_params;
		}

		// Validate and sanitize selected items
		$sanitized_params['selected_items_to_scan'] = ADBC_Selected_Items_Validator::remove_invalid_selected_items( $sanitized_params['items_type'], $selected_items_to_scan, false );

		// For tables or cron jobs, extract only the names
		if ( ! empty( $sanitized_params['selected_items_to_scan'] ) && in_array( $sanitized_params['items_type'], [ 'tables', 'cron_jobs' ] ) )
			$sanitized_params['selected_items_to_scan'] = array_column( $sanitized_params['selected_items_to_scan'], 'name' );

		return $sanitized_params;

	}

}