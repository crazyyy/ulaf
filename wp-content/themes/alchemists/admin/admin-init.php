<?php
/**
 * Load the theme options
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.1
 */

/**
 * Load Redux Framework options on init hook to comply with WordPress 6.7.0+ translation requirements
 */
function alchemists_load_redux_options() {
	if ( file_exists( get_template_directory() . '/admin/options-init.php' ) ) {
		require_once get_template_directory() . '/admin/options-init.php';
	}
}
add_action( 'init', 'alchemists_load_redux_options', 5 );
