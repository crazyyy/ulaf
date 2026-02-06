<?php
/**
 * Fallbacks
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   1.0.0
 * @version   4.7.0
 */


/**
 * Check if plugin active
 */
if ( is_admin() || class_exists( 'ACF' ) || function_exists( 'acf' ) ) {
	return;
}

/**
 * ACF fallback (if not installed or activated)
 */
if ( ! function_exists( 'the_field' ) ):
	function the_field( $selector, $post_id = false, $format_value = true ) {
		return false;
	}
endif;

if ( ! function_exists( 'get_field' ) ):
	function get_field( $selector, $post_id = false, $format_value = true ) {
		return false;
	}
endif;

if ( ! function_exists( 'have_rows' ) ):
	function have_rows( $selector, $post_id = false ) {
		return false;
	}
endif;

if ( ! function_exists( 'get_sub_field' ) ):
	function get_sub_field( $selector, $format_value = true ) {
		return false;
	}
endif;

if ( ! function_exists( 'the_row' ) ):
	function the_row( $format = false ) {
		return false;
	}
endif;
