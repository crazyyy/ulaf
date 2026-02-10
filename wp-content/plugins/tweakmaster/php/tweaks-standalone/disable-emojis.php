<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 * Pretty much copy pasta from Ryan Hellyer, just added namespacing and strict types
 *
 * @copyright 2023 Ryan Hellyer
 * https://github.com/ryanhellyer/disable-emojis/
 * https://wordpress.org/plugins/disable-emojis/
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Emojis (GDPR friendly)
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Description:      Disable Emojis (GDPR friendly)
 * Version:          1.7.6
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'init', __NAMESPACE__ . '\disable_emojis' );
/**
 * Disable the emojis.
 */
function disable_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', __NAMESPACE__ . '\disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', __NAMESPACE__ . '\disable_emojis_remove_dns_prefetch', 10, 2 );
}

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param  array<string> $plugins Array of plugins
 * @return array<string>          Array of plugins (wpemoji removed)
 */
function disable_emojis_tinymce( array $plugins ): array {
	return array_diff( $plugins, array( 'wpemoji' ) );
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param  array<string> $urls          URLs to print for resource hints.
 * @param  string        $relation_type The relation type the URLs are printed for.
 * @return array<string>                 Difference between the two arrays.
 */
function disable_emojis_remove_dns_prefetch( array $urls, string $relation_type ): array {

	if ( 'dns-prefetch' === $relation_type ) {

		// Strip out any URLs referencing the WordPress.org emoji location
		$emoji_svg_url_bit = 'https://s.w.org/images/core/emoji/';
		foreach ( $urls as $key => $url ) {
			if ( strpos( $url, $emoji_svg_url_bit ) !== false ) {
				unset( $urls[ $key ] );
			}
		}
	}

	return $urls;
}
