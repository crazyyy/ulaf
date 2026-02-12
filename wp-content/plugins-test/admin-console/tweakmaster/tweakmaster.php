<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:       TweakMaster
 * Description:       A collection or performance, privacy, security and other tweaks.. Minimalistic lightweight plugin.
 * Plugin URI:        https://nextgenthemes.com/plugins/tweakmaster/
 * Version:           1.1.1
 * Requires at least: 6.6
 * Author:            Nicolas Jonas
 * Author URI:        https://nextgenthemes.com
 * License:           GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION       = '1.1.1';
const PLUGIN_FILE   = __FILE__;
const PLUGIN_DIR    = __DIR__;
const TWEAKS_DIR    = __DIR__ . '/php/tweaks';
const TWEAKS_DIR_SA = __DIR__ . '/php/tweaks-standalone';

require_once __DIR__ . '/vendor/autoload_packages.php';
require_once __DIR__ . '/php/init.php';
