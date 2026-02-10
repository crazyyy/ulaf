<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

// This needs to run on plugins_loaded, or WP will set this to 30, init is too late.
if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
	define( 'EMPTY_TRASH_DAYS', options()['trash-keep-days'] );
}
