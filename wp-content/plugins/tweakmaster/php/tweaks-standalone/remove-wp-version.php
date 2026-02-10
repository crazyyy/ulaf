<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nextgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:     Remove WP Version
 * Description:     Removes Remove WP Version from head and feeds
 * Author:          Nicolas Jonas
 * Author URI:      https://nextgenthemes.com
 * Version:         1.0.0
 * License:         GPLv3
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

// remove version from head
remove_action( 'wp_head', 'wp_generator' );

// remove version from feeds
add_filter( 'the_generator', '__return_empty_string' );
