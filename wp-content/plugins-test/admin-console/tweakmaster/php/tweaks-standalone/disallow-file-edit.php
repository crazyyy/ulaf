<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disallow File Edits
 * Description:      Sets DISALLOW_FILE_EDIT constant to true. Does only work if the constant is not already defined, usually in wp-config.php
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

if ( ! defined( 'WPINC' ) ) {
	die();
}

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
