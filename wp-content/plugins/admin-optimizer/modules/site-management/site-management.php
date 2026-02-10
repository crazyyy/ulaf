<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Site Management Class
 */
class Site_Management {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * List of modules
	 *
	 * @var array
	 */
	protected $modules = [];

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

		if ( ! empty( $this->options['enable_txt_management'] ) ) {
			$this->modules['txt'] = new Ads_Robots_Txt();
		}
		if ( ! empty( $this->options['enable_xml_sitemap'] ) ) {
			$this->modules['sitemap'] = new XML_Sitemap();
		}
	}

	/**
	 * Add settings navtab
	 *
	 * @param array $nav_tab List of nav tabs.
	 *
	 * @return array
	 */
	public function add_settings_navtab( $nav_tab ) {
		if ( empty( $nav_tab['site-management'] ) ) {
			$nav_tab['site-management'] = [
				'title' => __( 'Site Management', 'admin-optimizer' ),
				'slug'  => 'adminoptim-site-settings',
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
		if ( empty( $fields['site-management'] ) ) {
			$modules                   = [
				'txt-mgt' => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable robots.txt, ads.txt and app-ads.txt on your site', 'admin-optimizer' ),
					'id'    => 'enable-txt-management',
					'name'  => 'enable_txt_management',
					'label' => __( 'Manage robots.txt, ads.txt, app-ads.txt directly in WordPress.', 'admin-optimizer' ),
				],
				'xml-sitmap' => [
					'type'  => 'slider-checkbox',
					'title' => __( 'Enable XML Sitemap on your site', 'admin-optimizer' ),
					'id'    => 'enable-xml-sitemap',
					'name'  => 'enable_xml_sitemap',
					'label' => __( 'Enable and manage XML Sitemap for your site.', 'admin-optimizer' ),
				],
			];
			$fields['site-management'] = [
				'id'          => 'adminoptimizer-site-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-site-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => $modules,
			];
		}
		return $fields;
	}
}
