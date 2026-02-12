<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Dequeue jQuery Migrate
 * Description:      Dequeue jQuery Migrate from the jQuery script dependencies on the frontend.
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\dequeue_jquery_migrate', 15 );
/**
 * Dequeue jQuery Migrate from the jQuery script dependencies on the frontend.
 *
 * This function checks if the current request is not for the admin area and if
 * the 'jquery' script is registered. If so, it removes 'jquery-migrate' from
 * the dependencies of the 'jquery' script.
 */
function dequeue_jquery_migrate(): void {

	$scripts = wp_scripts();

	if ( ! empty( $scripts->registered['jquery'] ) ) {
		$jquery_dependencies                 = $scripts->registered['jquery']->deps;
		$scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
	}
}
