<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Helpers\Helper;

/**
 * XML_Sitemap class
 */
class XML_Sitemap {
	use Helper;

	const OPTION_NAME = 'adminoptim_xml_sitemap';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var XML_Sitemap_Settings
	 */
	protected $settings;

	/**
	 * Metaboxes class
	 *
	 * @var XML_Sitemap_Metaboxes
	 */
	protected $metabox;

	const MENU_SLUG = 'adminoptimizer-xml-sitemap';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'xml-sitemap/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'xml-sitemap/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [] );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new XML_Sitemap_Settings( $this->options );
		$this->metabox  = new XML_Sitemap_Metaboxes( $this->options );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_filter( 'wp_sitemaps_post_types', [ $this, 'edit_sitemap_post_types' ] );
		add_filter( 'wp_sitemaps_taxonomies', [ $this, 'edit_sitemap_taxonomies' ] );
		add_filter( 'wp_sitemaps_add_provider', [ $this, 'maybe_add_sitemap_users' ], 10, 2 );
		add_filter( 'wp_sitemaps_register_providers', [ $this, 'maybe_register_users' ] );
		add_filter( 'wp_sitemaps_posts_query_args', [ $this, 'edit_sitemap_posts_query_args' ] );
		add_filter( 'wp_sitemaps_taxonomies_query_args', [ $this, 'edit_sitemap_categories_query_args' ], 10, 2 );
		add_filter( 'wp_sitemaps_max_urls', [ $this, 'change_sitemap_max_urls_count' ] );
		if ( ! empty( $this->options['include_in_robotstxt'] ) ) {
			$this->add_sitemap_link_robotstxt();
		} else {
			$this->remove_sitemap_link_robotstxt();
		}
		if ( ! empty( $this->options['custom_sitemap_slug'] ) ) {
			add_action( 'init', [ $this, 'rewrite_sitemap_url' ] );
			add_filter( 'home_url', [ $this, 'fix_wp_sitemap_url' ], 11, 2 );
		}
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'XML Sitemap', 'admin-optimizer' ),
			__( 'XML Sitemap', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Edit sitemap post types
	 *
	 * @return array
	 */
	public function edit_sitemap_post_types() {
		$post_types = [];
		if ( ! empty( $this->options['post_types'] ) ) {
			foreach ( $this->options['post_types'] as $post_type ) {
				$post_types[ $post_type ] = get_post_type_object( $post_type );
			}
		}
		return $post_types;
	}

	/**
	 * Edit sitemap taxonomies
	 *
	 * @return array
	 */
	public function edit_sitemap_taxonomies() {
		$taxonomies = [];
		if ( ! empty( $this->options['taxonomies'] ) ) {
			foreach ( $this->options['taxonomies'] as $taxonomy ) {
				$taxonomies[ $taxonomy ] = get_taxonomy( $taxonomy );
			}
		}
		return $taxonomies;
	}

	/**
	 * Maybe disable user entries from showing up in sitemap
	 *
	 * @param \WP_Sitemaps_Provider $provider Sitemap provider.
	 * @param string                $name Name of provider.
	 * @return bool|void
	 */
	public function maybe_add_sitemap_users( $provider, $name ) {
		if ( empty( $this->options['include_authors'] ) && 'users' === $name ) {
			return false;
		}

		return $provider;
	}

	/**
	 * Maybe disable user from registered in Sitemap provider
	 *
	 * @param \WP_Sitemaps_Provider $providers Sitemap provider.
	 * @return \WP_Sitemaps_Provider
	 */
	public function maybe_register_users( $providers ) {
		if ( empty( $this->options['include_authors'] ) ) {
			unset( $providers['users'] );
		}
		return $providers;
	}

	/**
	 * Filter the sitemap max urls count
	 *
	 * @param int $count Urls count.
	 * @return int
	 */
	public function change_sitemap_max_urls_count( $count ) {
		if ( ! empty( $this->options['max_entries'] ) ) {
			return absint( $this->options['max_entries'] );
		} else {
			return $count;
		}
	}

	/**
	 * Edit Sitemap query args
	 *
	 * @param array $args Post arguments.
	 * @return array
	 */
	public function edit_sitemap_posts_query_args( $args ) {

		$meta_query = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : [];

		$meta_query[] = [
			'key'     => '_adminoptim_xml_exclude',
			'compare' => 'NOT EXISTS',
		];

		$args['meta_query'] = $meta_query;
		$args['orderby']    = 'modified';
		$args['order']      = 'DESC';
		return $args;
	}

	/**
	 * Exclude selected categories from the sitemap
	 *
	 * @param []     $args Args of the query.
	 * @param string $taxonomy Taxonomy of the query.
	 * @return []
	 */
	public function edit_sitemap_categories_query_args( $args, $taxonomy ) {
		if ( 'category' !== $taxonomy ) {
			return $args;
		}
		if ( ! empty( $this->options['exclude_categories'] ) && is_array( $this->options['exclude_categories'] ) ) {
			$args['exclude'] = $this->options['exclude_categories'];
		}
		return $args;
	}


	/**
	 * Add sitemap link to robots.txt
	 *
	 * @return void
	 */
	public function add_sitemap_link_robotstxt() {
		$module_activated = $this->is_module_activated( 'enable_txt_management' );
		if ( $module_activated ) {
			$robots_options = get_option( Ads_Robots_Txt::OPTION_NAME, [] );
			$robots_txt     = $robots_options['robotstxt_content'];
			if ( ! str_contains( $robots_txt, '# BEGIN XML-SITEMAP LINK' ) ) {
				$robots_txt                         .= "\n\n# BEGIN XML-SITEMAP LINK\n";
				$robots_txt                         .= 'Sitemap: ' . esc_url( $this->get_sitemap_url() ) . "\n";
				$robots_txt                         .= '# END XML-SITEMAP LINK';
				$robots_options['robotstxt_content'] = $robots_txt;
				update_option( Ads_Robots_Txt::OPTION_NAME, $robots_options );
			} else { // check if the sitemap url is still the same one.
				preg_match( '/Sitemap: (.*).xml/', $robots_txt, $matches );
				if ( ! empty( $matches[1] ) ) {
					$current_sitemap_url = $matches[1] . '.xml';
					if ( $current_sitemap_url !== $this->get_sitemap_url() ) {
						$this->remove_sitemap_link_robotstxt();
					}
				}
			}
		}
	}

	/**
	 * Remove sitemap link to robots.txt
	 *
	 * @return void
	 */
	public function remove_sitemap_link_robotstxt() {
		$module_activated = $this->is_module_activated( 'enable_txt_management' );
		if ( $module_activated ) {
			$robots_options = get_option( Ads_Robots_Txt::OPTION_NAME, [] );
			$robots_txt     = $robots_options['robotstxt_content'];
			if ( str_contains( $robots_txt, '# BEGIN XML-SITEMAP LINK' ) ) {
				$robots_txt                          = preg_replace( '/# BEGIN XML-SITEMAP LINK(.*)# END XML-SITEMAP LINK/s', '', $robots_txt );
				$robots_options['robotstxt_content'] = rtrim( $robots_txt );
				update_option( Ads_Robots_Txt::OPTION_NAME, $robots_options );
			}
		}
	}

	/**
	 * Get sitemap url
	 *
	 * @return string
	 */
	private function get_sitemap_url() {
		if ( ! empty( $this->options['custom_sitemap_slug'] ) ) {
			return home_url( '/' . $this->options['custom_sitemap_slug'] . '.xml' );
		} else {
			return home_url( 'wp-sitemap.xml' );
		}
	}

	/**
	 * Rewrite sitemap url
	 *
	 * @return void
	 */
	public function rewrite_sitemap_url() {
		add_rewrite_rule( "^{$this->options['custom_sitemap_slug']}\.xml$", 'index.php?sitemap=index', 'top' );
	}

	/**
	 * Replaces the url from wp-sitemap.xml to sitemap.xml
	 *
	 * @param string $url Url of the sitemap.
	 * @param string $path Url path of the sitemap.
	 * @return string
	 */
	public function fix_wp_sitemap_url( $url, $path ) {
		if ( '/wp-sitemap.xml' === $path ) {
			$slug = $this->options['custom_sitemap_slug'];
			return str_replace( '/wp-sitemap.xml', "/{$slug}.xml", $url );
		}

		return $url;
	}
}
