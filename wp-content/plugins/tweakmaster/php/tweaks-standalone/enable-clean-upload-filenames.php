<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @copyright OzTheGreat (WPArtisan)
 * Based on https://github.com/WPArtisan/wpartisan-filename-sanitizer, License: GPLv2 or later
 *
 * @wordpress-plugin
 * Plugin Name:      Clean Upload Filenames
 * Description:      Sanitize media filenames to remove non-latin special characters and accents.
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'sanitize_file_name', __NAMESPACE__ . '\clean_filenames' );
/**
 * Produces cleaner filenames for uploads
 *
 * @param  string $filename The name of the file to sanitize
 */
function clean_filenames( string $filename ): string {

	$filename = remove_accents( $filename ); // Convert to ASCII
	$filename = strtr(
		$filename,
		[
			' '   => '-',
			'%20' => '-',
			'_'   => '-',
		]
	);
	$filename = preg_replace( '/[^A-Za-z0-9-\. ]/', '', $filename ); // Remove all non-alphanumeric except .
	$filename = preg_replace( '/\.(?=.*\.)/', '', $filename ); // Remove all but last .
	$filename = preg_replace( '/-+/', '-', $filename ); // Replace any more than one - in a row
	$filename = str_replace( '-.', '.', $filename ); // Remove last - if at the end
	$filename = strtolower( $filename ); // Lowercase

	return $filename;
}
