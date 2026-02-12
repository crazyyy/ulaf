<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Contact Form 7 CSS
 * Description:      Sets wpcf7_load_css filter to false
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\remove_admin_bar_wp_logo', 20 );
function remove_admin_bar_wp_logo(): void {

	global $wp_admin_bar;

	$wp_admin_bar->remove_node( 'wp-logo' );
}
