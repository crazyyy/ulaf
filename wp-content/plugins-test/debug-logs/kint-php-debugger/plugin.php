<?php
/**
 * Kint PHP Debugger - a modern and powerful PHP debugging helper
 *
 * @package         KnowTheCode\Kint_PHP_Debugger
 * @author          hellofromTonya
 * @license         dual license GPL-2.0+ & MIT (Kint is licensed MIT)
 * @link            https://wordpress.org/plugins/kint-php-debugger/
 *
 * @wordpress-plugin
 * Plugin Name:     Kint PHP Debugger
 * Plugin URI:      https://github.com/KnowTheCode/kint-php-debugger
 * Description:     Kint is a a modern and powerful PHP debugging helper, which requires zero-setup and replaces var_dump(), print_r() and debug_backtrace().  This plugin is a wrapper for Kint.
 * Version:         2.0.2
 * Author:          hellofromTonya
 * Author URI:      https://KnowTheCode.io/
 * Text Domain:     wpkint
 * Requires WP:     3.5
 * Requires PHP:    5.3
 */
namespace KnowTheCode\Kint_PHP_Debugger;

// Bail out if Kint already exists.
if ( class_exists( 'Kint' ) ) {
	return;
}

/**
 * Gets the plugin's root directory.
 *
 * @since 1.2.1
 * @access private
 *
 * @return string Returns the plugin's root directory.
 */
function _get_plugin_root_dir() {
	return __DIR__;
}

require_once __DIR__ . '/src/kint-php/kint/Kint.class.php';
require_once __DIR__ . '/src/admin-color.php';
