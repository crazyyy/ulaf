<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Remove Asset Version Parameter
 * Description:      Removes ?ver=1.2.3 from all styles and scripts
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'script_loader_src', __NAMESPACE__ . '\remove_script_version', 15, 1 );
add_filter( 'style_loader_src', __NAMESPACE__ . '\remove_script_version', 15, 1 );

function remove_script_version( string $src ): string {
	return $src ? esc_url( remove_query_arg( 'ver', $src ) ) : '';
}
