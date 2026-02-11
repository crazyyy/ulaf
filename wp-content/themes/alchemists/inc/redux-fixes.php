<?php
/**
 * Redux Framework Fixes
 *
 * @package Alchemists
 * @since   4.7.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fix Redux Select field undefined array key warning
 *
 * @param array $field Field array.
 * @return array
 */
function alchemists_fix_redux_select_field( $field ) {
	// Only process select fields with sortable enabled
	if ( ! isset( $field['type'] ) || 'select' !== $field['type'] ) {
		return $field;
	}

	if ( ! isset( $field['sortable'] ) || ! $field['sortable'] ) {
		return $field;
	}

	if ( ! isset( $field['multi'] ) || ! $field['multi'] ) {
		return $field;
	}

	// Ensure options is an array
	if ( ! isset( $field['options'] ) || ! is_array( $field['options'] ) ) {
		$field['options'] = array();
		return $field;
	}

	// If no value set, return as is
	if ( empty( $field['value'] ) ) {
		return $field;
	}

	// Ensure value is an array
	if ( ! is_array( $field['value'] ) ) {
		$field['value'] = array( $field['value'] );
	}

	// Filter out non-existent keys from value
	$field['value'] = array_filter(
		$field['value'],
		function( $key ) use ( $field ) {
			return array_key_exists( $key, $field['options'] );
		}
	);

	return $field;
}
add_filter( 'redux/field/select/field_array', 'alchemists_fix_redux_select_field', 10 );

/**
 * Suppress Redux select field warnings
 */
function alchemists_suppress_redux_select_warnings() {
	if ( ! is_admin() ) {
		return;
	}

	// Check if Redux is active
	if ( ! class_exists( 'Redux' ) ) {
		return;
	}

	// Add custom error handler for Redux select fields
	add_action( 'redux/field/select/render/before', function() {
		set_error_handler( function( $errno, $errstr, $errfile, $errline ) {
			// Suppress only "Undefined array key" warnings from Redux select field
			if ( false !== strpos( $errfile, 'redux-framework/redux-core/inc/fields/select/class-redux-select.php' ) ) {
				if ( false !== strpos( $errstr, 'Undefined array key' ) ) {
					return true; // Suppress this warning
				}
			}
			return false; // Let other errors through
		}, E_WARNING );
	});

	add_action( 'redux/field/select/render/after', function() {
		restore_error_handler();
	});
}
add_action( 'init', 'alchemists_suppress_redux_select_warnings' );