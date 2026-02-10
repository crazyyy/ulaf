<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/Frontend
 */

namespace Kaizencoders\Utilitify;

use Kaizencoders\Utilitify\Models\Link;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/Frontend
 * @author     Your Name <email@example.com>
 */
class Frontend {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin $plugin This plugin's instance.
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  Plugin  $plugin  This plugin's instance.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined in that particular
		 * class.
		 *
		 * The Loader will then create the relationship between the defined
		 * hooks and the functions defined in this class.
		 */

		\wp_enqueue_style(
			$this->plugin->get_plugin_name(),
			\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/styles/utilitify.css',
			[],
			$this->plugin->get_version(),
			'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined in that particular
		 * class.
		 *
		 * The Loader will then create the relationship between the defined
		 * hooks and the functions defined in this class.
		 */

		\wp_enqueue_script(
			$this->plugin->get_plugin_name(),
			\plugin_dir_url( dirname( __FILE__ ) ) . 'dist/scripts/utilitify.js',
			[ 'jquery' ],
			$this->plugin->get_version(),
			false );

	}

	/**
	 * Remove version from query args.
	 *
	 * @since 1.1.0
	 *
	 * @param $src
	 *
	 * @return false|mixed|string
	 *
	 */
	public function remove_version_from_query_args( $src ) {
		if ( strpos( $src, '?ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Remove redundant shortlink.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function remove_redundant_shortlink() {
		// remove HTML meta tag
		// <link rel='shortlink' href='http://example.com/?p=25' />
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// remove HTTP header
		// Link: <https://example.com/?p=25>; rel=shortlink
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}

	/**
	 * Remove Dashicons.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function remove_dashicons() {
		if ( current_user_can( 'update_core' ) ) {
			return;
		}
		wp_deregister_style( 'dashicons' );
	}

	/**
	 * Disable WP Embed.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function disable_wp_embed() {
		wp_deregister_script( 'wp-embed' );
	}

	/**
	 * Add Feature Image to RSS Feed.
	 *
	 * @since 1.1.0
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 *
	 */
	public function feature_image_to_RSS_feed( $content ) {
		global $post;
		if ( has_post_thumbnail( $post->ID ) ) {
			$content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail',
					[ 'style' => 'float:left; margin:0 15px 15px 0;' ] ) . '' . $content;
		}

		return $content;
	}

	/**
	 * Disable WP Emoji.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function disable_wp_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Remove from TinyMCE.
		add_filter( 'tiny_mce_plugins', [ $this, 'disable_emojis_tinymce' ] );
		add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
	}

	/**
	 * Remove the tinymce emoji plugin.
	 *
	 * @since 1.1.0
	 *
	 * @param $plugins
	 *
	 * @return array
	 *
	 */
	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}

		return [];
	}


	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @since 1.1.0
	 *
	 * @param $relation_type
	 *
	 * @param $urls
	 *
	 * @return array|mixed
	 *
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

			$urls = array_diff( $urls, [ $emoji_svg_url ] );
		}

		return $urls;
	}

	/**
	 * Disable RSS Feed Message.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function disable_rss_feed() {
		wp_die( __( 'No feed available, please visit the',
				'utilitify' ) . '<a href="' . esc_url( home_url( '/' ) ) . '">' . __( 'homepage',
				'utilitify' ) . '</a>!' );
	}

	/**
	 * Add/ Remove function based on settings.
	 *
	 * @since 1.1.0
	 * @return void
	 *
	 */
	public function kc_uf_action_filters() {
		$settings = KC_UF()->get_settings();

		// CSS Version.
		if ( Helper::get_data( $settings, 'general_script_and_styles_settings_hide_css_file_version', false ) ) {
			add_filter( 'style_loader_src', [ $this, 'remove_version_from_query_args' ], 10, 2 );
		}

		// Js Version.
		if ( Helper::get_data( $settings, 'general_script_and_styles_settings_hide_js_file_version', false ) ) {
			add_filter( 'script_loader_src', [ $this, 'remove_version_from_query_args' ], 10, 2 );
		}

		// Remove RSD Link.
		if ( Helper::get_data( $settings, 'general_wordpress_meta_tag_settings_remove_rsd_link', false ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		// WordPress Version Generator Tag
		if ( Helper::get_data( $settings, 'general_wordpress_meta_tag_settings_hide_wordpress_version', false ) ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		// Remove WLW Manifest Link.
		if ( Helper::get_data( $settings, 'general_wordpress_meta_tag_settings_remove_wlw_menifest_link', false ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		// Remove Shortlink.
		if ( Helper::get_data( $settings, 'general_wordpress_meta_tag_settings_remove_shortlink', false ) ) {
			add_filter( 'after_setup_theme', [ $this, 'remove_redundant_shortlink' ] );
		}

		// Disable XML RPC
		if ( Helper::get_data( $settings, 'general_wordpress_meta_tag_settings_disable_xml_rpc', false ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		// Disable Auto Update For Plugins.
		if ( Helper::get_data( $settings, 'general_wordpress_auto_updates_disable_plugin_auto_updates', false ) ) {
			add_filter( 'auto_update_plugin', '__return_false' );
		}

		// Disable Auto Update For Themes.
		if ( Helper::get_data( $settings, 'general_wordpress_auto_updates_disable_theme_auto_updates', false ) ) {
			add_filter( 'auto_update_theme', '__return_false' );
		}

		// Disable dashicons.
		if ( Helper::get_data( $settings, 'general_script_and_styles_settings_disable_dashicons_in_frontend',
			false ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_dashicons' ] );
		}

		// WP Embeds
		if ( Helper::get_data( $settings, 'general_script_and_styles_settings_disable_embeds', false ) ) {
			add_action( 'wp_footer', [ $this, 'disable_wp_embed' ] );
		}

		// Disable RSS Feed.
		if ( Helper::get_data( $settings, 'general_rss_feed_settings_disable_rss_feed', false ) ) {
			add_action( 'do_feed', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_rdf', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_rss', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_rss2', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_atom', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_rss2_comments', [ $this, 'disable_rss_feed' ], 1 );
			add_action( 'do_feed_atom_comments', [ $this, 'disable_rss_feed' ], 1 );
		}

		// Remove RSS Feed Links
		if ( Helper::get_data( $settings, 'general_rss_feed_settings_remove_rss_feed_links', false ) ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}

		// Add Feature Image to RSS Feed.
		if ( Helper::get_data( $settings, 'general_rss_feed_settings_add_feature_image_to_rss_feed', false ) ) {
			add_filter( 'the_excerpt_rss', [ $this, 'feature_image_to_RSS_feed' ] );
			add_filter( 'the_content_feed', [ $this, 'feature_image_to_RSS_feed' ] );
		}

		// Shortcode in Widget.
		if ( Helper::get_data( $settings, 'general_wordpress_widget_settings_enable_shortcode_in_wp_widgets',
			false ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}
	}
}
