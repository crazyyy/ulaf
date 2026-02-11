<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC Premium common validator class.
 * 
 * This class provides functions to validate and sanitize the common data sent by the user to the endpoints in the premium version.
 */
class ADBC_Premium_Common_Validator {

	/**
	 * Sanitize the DataTable filters sent by the user.
	 *
	 * @param array $sanitized_filters The filters to sanitize.
	 * 
	 * @return void
	 */
	public static function sanitize_filters( &$sanitized_filters ) {

		// $sanitized_filters['search_for'] kept as is.
		// $sanitized_filters['belongs_to_plugin_slug'] kept as is.
		// $sanitized_filters['belongs_to_theme_slug'] kept as is.
		$sanitized_filters['search_in'] = in_array( $sanitized_filters['search_in'], [ 'all', 'name', 'value' ] ) ? $sanitized_filters['search_in'] : 'name';
		$sanitized_filters['site_id'] = $sanitized_filters['site_id'] === 'all' ? 'all' : absint( $sanitized_filters['site_id'] );
		$sanitized_filters['show_manual_corrections_only'] = $sanitized_filters['show_manual_corrections_only'] === true;
		[ $sanitized_filters['start_date'], $sanitized_filters['end_date'] ] = ADBC_Common_Validator::validate_filter_date_range(
			$sanitized_filters['start_date'],
			$sanitized_filters['end_date'],
			'Y-m-d'
		);
		$sanitized_filters['frequency'] = in_array( $sanitized_filters['frequency'], array_merge( array_keys( wp_get_schedules() ), [ 'once' ] ) ) ? $sanitized_filters['frequency'] : 'all';
		$sanitized_filters['interval'] = in_array( $sanitized_filters['interval'], [ 'all', 'N/A' ] ) ? $sanitized_filters['interval'] : absint( $sanitized_filters['interval'] );

	}

	/**
	 * Validate the addon's activity type.
	 * 
	 * @param string $activity_type The activity type to validate.
	 * @return string The sanitized activity type or an empty string if invalid.
	 */
	public static function sanitize_validate_activity_type( $activity_type ) {

		// Sanitize the activity type
		$activity_type = sanitize_text_field( $activity_type );

		$valid_activity_types = [ '', 'activation', 'deactivation', 'uninstall' ];

		if ( ! in_array( $activity_type, $valid_activity_types ) )
			return '';

		return $activity_type;

	}

}