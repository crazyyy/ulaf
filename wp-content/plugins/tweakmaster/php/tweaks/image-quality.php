<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'wp_editor_set_quality', __NAMESPACE__ . '\set_image_quality', 10, 2 );
/**
 * Sets image quality based on MIME type.
 *
 * @param int    $quality Original quality value.
 * @param string $mime_type MIME type of the image.
 *
 * @return int Quality value to use.
 */
function set_image_quality( int $quality, string $mime_type ): int {

	switch ( $mime_type ) {
		case 'image/jpeg':
			return options()['jpeg_quality']; // Default is 82
		case 'image/avif':
			return options()['avif_quality']; // Default is 82???
		case 'image/webp':
			return options()['webp_quality']; // Default is 86
		default:
			return $quality;
	}
}
