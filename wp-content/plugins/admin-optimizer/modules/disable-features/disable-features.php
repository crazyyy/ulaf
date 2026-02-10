<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Disable_Features class
 */
class Disable_Features {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options User options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		add_filter( 'adminoptimizer_settings_navtab', [ $this, 'add_settings_navtab' ] );
		add_filter( 'adminoptimizer_settings_sections', [ $this, 'settings_fields' ] );
		if ( ! empty( $this->options['remove_rest_api_link'] ) ) {
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
			remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
		}
		if ( ! empty( $this->options['remove_rsd'] ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}
		if ( ! empty( $this->options['remove_shortlink'] ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		}
		if ( ! empty( $this->options['remove_oembed'] ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		}
		if ( ! empty( $this->options['disable_emojis'] ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
		}
		if ( ! empty( $this->options['disable_jquery_migrate'] ) ) {
			add_action( 'wp_default_scripts', [ $this, 'dequeue_jquery_migrate' ] );
		}
		add_action( 'wp', [ $this, 'configure_http_header' ] );
		if ( ! empty( $this->options['disable_404_redirect'] ) ) {
			add_filter( 'do_redirect_guess_404_permalink', '__return_false' );
		}
		if ( ! empty( $this->options['disable_gutenberg_editor'] ) ) {
			add_filter( 'gutenberg_can_edit_post', '__return_false', 5 );
			add_filter( 'use_block_editor_for_post', '__return_false', 5 );
		}
		add_action( 'template_redirect', [ $this, 'disable_frontend_pages' ] );
	}

	/**
	 * Add settings navtab
	 *
	 * @param array $nav_tab List of nav tabs.
	 *
	 * @return array
	 */
	public function add_settings_navtab( $nav_tab ) {
		if ( empty( $nav_tab['disable_features'] ) ) {
			$nav_tab['disable-features'] = [
				'title' => __( 'Disable Features', 'admin-optimizer' ),
				'slug'  => 'adminoptim-disable-features-settings',
			];
		}
		return $nav_tab;
	}

	/**
	 * List of Settings fields
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ) {
		if ( empty( $fields['disable_features'] ) ) {
			$fields['disable_features'] = [
				'id'          => 'adminoptimizer-disable-features-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-disable-features-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => [
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove REST API link', 'admin-optimizer' ),
						'id'    => 'remove-rest-api-link',
						'name'  => 'remove_rest_api_link',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove the REST API link from the header.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-rest-api-link/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove Really Simple Discovery (RSD) Link', 'admin-optimizer' ),
						'id'    => 'remove-rsd',
						'name'  => 'remove_rsd',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove Really Simple Discovery (RSD) &lt;link&gt; tag from the header.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-really-simple-discovery-rsd-link/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove shortlink', 'admin-optimizer' ),
						'id'    => 'remove-shortlink',
						'name'  => 'remove_shortlink',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove shortlink from the header.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-shortlink/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove oEmbed links', 'admin-optimizer' ),
						'id'    => 'remove-oembed',
						'name'  => 'remove_oembed',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove links used for embedding your content on other sites.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-oembed-links/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Emojis', 'admin-optimizer' ),
						'id'    => 'disable-emojis',
						'name'  => 'disable_emojis',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable emoji support and remove emoji scripts and styles from your site.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-emojis/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable jQuery Migrate', 'admin-optimizer' ),
						'id'    => 'disable-jquery-migrate',
						'name'  => 'disable_jquery_migrate',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove script bloat from your site.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-jquery-migrate/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove Pingback HTTP header', 'admin-optimizer' ),
						'id'    => 'remove-pingback-header',
						'name'  => 'remove_pingback_header',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove \'X-Pingback\' from the HTTP header. This will prevent others sites from pinging yours when they link to you.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-pingback-http-header/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Remove Powered By HTTP header', 'admin-optimizer' ),
						'id'    => 'remove-powered-by-header',
						'name'  => 'remove_powered_by_header',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Remove \'X-Powered-By\' from the HTTP header. This will remove information about the plugins and software used by your site.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/remove-powered-by-http-header/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable 404 URL Redirect', 'admin-optimizer' ),
						'id'    => 'disable-404-redirect',
						'name'  => 'disable_404_redirect',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'By default, WordPress will attempt to guess a redirect URL for a 404 request. This module disable the guessing and return the 404 page without performing a redirect.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-404-url-redirect/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Gutenberg Editor', 'admin-optimizer' ),
						'id'    => 'disable-gutenberg-editor',
						'name'  => 'disable_gutenberg_editor',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'This will disable Gutenberg Block Editor and restore the Classic editor as the default editor.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-gutenberg-editor/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Category Archive Page (frontend)', 'admin-optimizer' ),
						'id'    => 'disable-category-archive',
						'name'  => 'disable_category_archive',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable all Category archive pages in the site frontend. Loading the Category archive URL will return a 404 (Not Found) error. This will not remove the Category feature in the Admin dashboard.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-archive-pages/#disable-category-archive' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Tag Archive Page (frontend)', 'admin-optimizer' ),
						'id'    => 'disable-tag-archive',
						'name'  => 'disable_tag_archive',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable all Tag archive pages in the site frontend. Loading the Tag archive URL will return a 404 (Not Found) error. This will not remove the Tag feature in the Admin dashboard.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-archive-pages/#disable-tag-archive' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Author Archive Page (frontend)', 'admin-optimizer' ),
						'id'    => 'disable-author-archive',
						'name'  => 'disable_author_archive',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable all Author archive pages in the site frontend. Loading the Author archive URL will return a 404 (Not Found) error. This will not remove the Author feature in the Admin dashboard.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-archive-pages/#disable-author-archive' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Disable Date Archive Page (frontend)', 'admin-optimizer' ),
						'id'    => 'disable-date-archive',
						'name'  => 'disable_date_archive',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Disable all Date archive pages in the site frontend. Loading the Date archive URL will return a 404 (Not Found) error. This will not remove the Date feature in the Admin dashboard.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/disable-archive-pages/#disable-date-archive' ) . '" target="_blank">', '</a>' ),
					],
				],
			];
		}
		return $fields;
	}

	/**
	 * Remove entries from the HTTP header
	 *
	 * @return void
	 */
	public function configure_http_header() {
		if ( headers_sent() ) {
			return;
		}

		if ( ! empty( $this->options['remove_pingback_header'] ) ) {
			header_remove( 'X-Pingback' );
		}

		if ( ! empty( $this->options['remove_powered_by_header'] ) ) {
			header_remove( 'X-Powered-By' );
		}
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array  $urls URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 * @return array Difference between the two arrays.
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			$urls = array_diff( $urls, [ $emoji_svg_url ] );
		}

		return $urls;
	}

	/**
	 * Disable jQuery Migrate
	 *
	 * @param \WP_Scripts $scripts WP_Scripts instance.
	 *
	 * @return void
	 */
	public function dequeue_jquery_migrate( $scripts ) {
		if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				[ 'jquery-migrate' ]
			);
		}
	}

	/**
	 * Disable Frontend archive pages
	 *
	 * @return void
	 */
	public function disable_frontend_pages() {
		if ( ! is_admin() ) {
			global $wp_query;
			$set_404 = false;
			if ( ! empty( $this->options['disable_category_archive'] ) && is_category() ) {
				$set_404 = true;
			} elseif ( ! empty( $this->options['disable_tag_archive'] ) && is_tag() ) {
				$set_404 = true;
			} elseif ( ! empty( $this->options['disable_author_archive'] ) && is_author() ) {
				$set_404 = true;
			} elseif ( ! empty( $this->options['disable_date_archive'] ) && is_date() ) {
				$set_404 = true;
			}
			if ( $set_404 ) {
				$wp_query->set_404();
			}
		}
	}
}
