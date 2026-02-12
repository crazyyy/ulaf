<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableEmojis
 * Removes WordPress emoji detection scripts and styles from both frontend and admin areas.
 * This improves page load performance by eliminating unnecessary HTTP requests and inline scripts.
 */
class DisableEmojis {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'performance' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['disable_emojis'] ) ) {
			add_action( 'init', [ $this, 'disable_emojis' ] );
		}
	}
	
	/**
	 * Modifies and returns the settings fields for the plugin.
	 *
	 * @param array $fields An array containing the existing fields configuration.
	 *
	 * @return array The updated fields array with the disable emojis configuration.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['performance']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-emojis',
			'name'        => 'adminease[performance][disable_emojis]',
			'value'       => $this->settings['disable_emojis'] ?? '',
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable Emojis', 'adminease' ),
			'description' => __( 'Remove WordPress emoji detection scripts and styles to improve page load performance. WordPress loads emoji scripts on every page by default to convert emoji characters into images for browsers that don\'t support native emoji rendering. Modern browsers (Chrome 58+, Firefox 52+, Safari 10+, Edge 79+) all support native emoji rendering, making these scripts unnecessary for most users. Disabling emojis eliminates extra HTTP requests and reduces page weight, resulting in faster load times.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Removes all emoji-related functionality from WordPress.
	 * Disables emoji detection scripts, TinyMCE plugins, DNS prefetch, and email conversion.
	 *
	 * @return void
	 */
	public function disable_emojis(): void {
		// Remove emoji detection script from front-end and admin
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		
		// Remove emoji styles
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		
		// Remove emoji from RSS feeds
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		
		// Remove emoji from emails
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		
		// Disable TinyMCE emoji plugin
		add_filter( 'tiny_mce_plugins', [ $this, 'disable_tinymce_emojis' ] );
		
		// Remove emoji DNS prefetch
		add_filter( 'wp_resource_hints', [ $this, 'disable_emoji_dns_prefetch' ], 10, 2 );
	}
	
	/**
	 * Removes the emoji plugin from TinyMCE editor.
	 *
	 * @param array $plugins An array of TinyMCE plugins.
	 *
	 * @return array The modified array with emoji plugin removed.
	 */
	public function disable_tinymce_emojis( array $plugins ): array {
		if( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}
		
		return [];
	}
	
	/**
	 * Removes emoji DNS prefetch from resource hints.
	 *
	 * @param array  $urls An array of resource URLs.
	 * @param string $relation_type The relation type of the resource hints.
	 *
	 * @return array The modified array with emoji DNS prefetch removed.
	 */
	public function disable_emoji_dns_prefetch( array $urls, string $relation_type ): array {
		if( 'dns-prefetch' === $relation_type ) {
			// Remove emoji CDN hostname from DNS prefetch
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$urls = array_diff( $urls, [ $emoji_svg_url ] );
		}
		
		return $urls;
	}
}