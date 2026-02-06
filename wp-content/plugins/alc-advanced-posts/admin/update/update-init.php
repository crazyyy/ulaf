<?php
/**
 * Initialize License Activation (delayed load)
 * This ensures WordPress is fully loaded before running license checks
 */

// Do not allow directly accessing this file.
if ( !defined('ABSPATH') ) {
        exit('Direct script access denied.');
}

/**
 * Initialize license system after WordPress is loaded
 * Hooked to 'init' to comply with WordPress 6.7.0+ requirements for translation loading
 */
function alc_init_license_system() {
        require_once plugin_dir_path( __FILE__ ) . 'update-base.php';
        require_once plugin_dir_path( __FILE__ ) . 'update.php';
}
add_action( 'init', 'alc_init_license_system', 1 );
