<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @copyright  2023 Peter Wilson
 * Copy pasta from https://github.com/peterwilsoncc/fonts-to-uploads/blob/main/inc/namespace.php license MIT
 *
 * @wordpress-plugin
 * Plugin Name:      Fonts to Uploads
 * Description:      Move (Google) Fonts enabled in the Block Editor from wp-content/fonts to wp-content/uploads
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

bootstrap();




/**
 * Bootstrap the plugin.
 */
function bootstrap(): void {
	add_filter( 'font_dir', __NAMESPACE__ . '\\filter_default_font_directory' );

	/*
	 * Prime the uploads directory cache.
	 *
	 * This runs late on the `init` hook to allow time for plugins to register
	 * custom upload directory file handlers.
	 */
	add_action( 'init', __NAMESPACE__ . '\\cached_wp_get_upload_dir_primer', 20 );
}

/**
 * Prime the uploads directory cache. Exists to phpstan does not complain about hook function having a return value.
 *
 * This runs late on the `init` hook to allow time for plugins to register
 * custom upload directory file handlers.
 *
 */
function cached_wp_get_upload_dir_primer(): void {
	cached_wp_get_upload_dir();
}

/**
 * Store the uploads directory in a static variable.
 *
 * This is the prevent the potential for infinite loops in the event
 * an extender includes `add_filter( 'upload_dir', 'wp_get_font_dir' );`
 * in their code base.
 *
 * Without a primed cache, `wp_get_upload_dir()` would trigger the a call
 * to `wp_get_font_dir()` which would trigger a call to `wp_get_upload_dir()`.
 *
 * @see https://github.com/pantheon-systems/pantheon-mu-plugin/blob/main/inc/fonts.php for inspiration.
 *
 * Returns the cached upload directory information.
 *
 * This function stores the uploads directory in a static variable to prevent
 * potential infinite loops that could occur if an extender includes
 * `add_filter( 'upload_dir', 'wp_get_font_dir' );` in their code base.
 *
 * Without a primed cache, `wp_get_upload_dir()` would trigger a call to
 * `wp_get_font_dir()` which would trigger a call to `wp_get_upload_dir()`.
 *
 * @return array<string, string|false>
 */
function cached_wp_get_upload_dir(): array {
	static $cached = null;

	if ( null === $cached ) {
		$cached = wp_get_upload_dir();
	}

	return $cached;
}

/**
 * Filter the WordPress default font directory to use the uploads folder.
 *
 * Relocated files uploaded by the Font Library from `wp-content/fonts/` to a
 * sub-directory of the uploads folder.
 *
 * @param  array<string, string|false> $font_directory The default in which to store fonts.
 * @return array<string, string|false>                 The modified fonts directory.
 */
function filter_default_font_directory( array $font_directory ): array {
	$upload_dir = cached_wp_get_upload_dir();

	$font_directory = array(
		'path'    => untrailingslashit( $upload_dir['basedir'] ) . '/fonts',
		'url'     => untrailingslashit( $upload_dir['baseurl'] ) . '/fonts',
		'subdir'  => '',
		'basedir' => untrailingslashit( $upload_dir['basedir'] ) . '/fonts',
		'baseurl' => untrailingslashit( $upload_dir['baseurl'] ) . '/fonts',
		'error'   => false,
	);

	return $font_directory;
}
