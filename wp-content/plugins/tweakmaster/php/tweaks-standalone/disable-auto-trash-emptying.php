<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Auto Trash Emptying
 * Description:      By default WordPress removes items that are older then 30 days.
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'init', __NAMESPACE__ . '\disable_auto_trash_emptying' );

function disable_auto_trash_emptying(): void {
	remove_action( 'wp_scheduled_delete', 'wp_scheduled_delete' );
}
