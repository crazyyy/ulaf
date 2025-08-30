<?php
/**
 * LearnPress Announcements Functions
 *
 * Define common functions for both front-end and back-end
 *
 * @author   ThimPress
 * @package  LearnPress/Announcements/Functions
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'learn_press_announcements_template' ) ) {
	/**
	 * Get template.
	 *
	 * @param $name
	 * @param null $args
	 */
	function learn_press_announcements_template( $name, $args = null ) {
		learn_press_get_template( $name, $args, learn_press_template_path() . '/addons/announcements/', LP_ANNOUNCEMENTS_TEMPLATE );
	}
}

if ( ! function_exists( 'learn_press_announcements_admin_view' ) ) {

	/**
	 * Get admin view file.
	 *
	 * @param $view
	 * @param string $args
	 */
	function learn_press_announcements_admin_view( $view, $args = '' ) {
		learn_press_admin_view( $view, wp_parse_args( $args, array( 'plugin_file' => LP_ADDON_ANNOUNCEMENTS_FILE ) ) );
	}
}