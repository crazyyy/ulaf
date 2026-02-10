<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Convert JPEG to AVIF on upload
 * Description:      Convert JPEG to AVIF on upload
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'image_editor_output_format', __NAMESPACE__ . '\convert_jpeg_to_avif' );

/**
 * Output AVIFs for uploaded JPEGs
 *
 * @param array<string, string> $formats
 * @return array<string, string>
 */
function convert_jpeg_to_avif( array $formats ): array {
	$formats['image/jpeg'] = 'image/avif';
	return $formats;
}
