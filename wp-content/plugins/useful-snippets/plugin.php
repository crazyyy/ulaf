<?php
/**
 * Plugin Name:       Useful Snippets
 * Description:       Switch on and off code snippets on your site.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           1.1.0
 * Author:            Janos Moga
 * Author URI:        https://github.com/janibras/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       useful-snippets
 *
 * @package           UsefulSnippets
*/

/**
 * Protection against direct access.
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define folder access.
 * @since 1.0.0
 */
define( 'USEFULSNIPPETS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load necessary files.
 * @since 1.0.0
 */
require_once USEFULSNIPPETS_DIR . 'includes/invite.php';