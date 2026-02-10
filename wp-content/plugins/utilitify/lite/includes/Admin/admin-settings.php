<?php
/**
 * WordPress Settings Framework
 *
 * @author  Gilbert Pellegrom, James Kemp
 * @link    https://github.com/gilbitron/WordPress-Settings-Framework
 * @license MIT
 */

/**
 * Define your settings
 *
 * The first parameter of this filter should be wpsf_register_settings_[options_group],
 * in this case "my_example_settings".
 *
 * Your "options_group" is the second param you use when running new WordPressSettingsFramework()
 * from your init function. It's important as it differentiates your options from others.
 *
 * To use the tabbed example, simply change the second param in the filter below to 'wpsf_tabbed_settings'
 * and check out the tabbed settings function on line 156.
 */

add_filter( 'wpsf_register_settings_kc_uf', 'kc_uf_wpsf_tabbed_settings' );

/**
 * Tabbed example
 */
function kc_uf_wpsf_tabbed_settings( $wpsf_settings ) {
	// Tabs
	$tabs = [

		[
			'id'    => 'general',
			'title' => __( 'General', 'utilitify' ),
		],
	];

	$wpsf_settings['tabs'] = apply_filters( 'kc_uf_filter_settings_tab', $tabs );

	$default_general_options = [

		[
			'id'      => 'enable_hide_admin_bar_from_frontend',
			'title'   => __( 'Hide Admin Bar From Frontend', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'enable_hide_admin_bar_from_backend',
			'title'   => __( 'Hide Admin Bar From Backend', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
	];

	$default_general_options = apply_filters( 'kc_uf_filter_default_link_options', $default_general_options );

	$wordpress_auto_update_settings = [
		[
			'id'      => 'disable_core_auto_updates',
			'title'   => __( 'Disable WordPress Core Auto Updates', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'disable_plugin_auto_updates',
			'title'   => __( 'Disable Plugin Auto Updates', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'disable_theme_auto_updates',
			'title'   => __( 'Disable Theme Auto Updates', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
	];

	$default_script_and_style_settings = [
		[
			'id'      => 'hide_css_file_version',
			'title'   => __( 'Hide CSS File Version', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'hide_js_file_version',
			'title'   => __( 'Hide JS File Version', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'disable_dashicons_in_frontend',
			'title'   => __( 'Disable Dashicons in Frontend', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'disable_embeds',
			'title'   => __( 'Disable Embeds', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

	];


	$wordpress_meta_tag_settings = [
		[
			'id'      => 'remove_rsd_link',
			'title'   => __( 'Remove RSD Link', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
		[
			'id'      => 'hide_wordpress_version',
			'title'   => __( 'Hide WordPress Version', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
		[
			'id'      => 'disable_xml_rpc',
			'title'   => __( 'Disable XML RPC', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
		[
			'id'      => 'remove_wlw_menifest_link',
			'title'   => __( 'Remove WLW Manifest Link', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
		[
			'id'      => 'remove_shortlink',
			'title'   => __( 'Remove Shortlink', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
	];

	$wordpress_rss_feed_settings = [
		[
			'id'      => 'disable_rss_feed',
			'title'   => __( 'Disable RSS Feed', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'remove_rss_feed_links',
			'title'   => __( 'Remove RSS Feed Links', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],

		[
			'id'      => 'add_feature_image_to_rss_feed',
			'title'   => __( 'Add Feature Image To RSS Feed', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
	];

	$wordpress_widget_settings =  [
		[
			'id'      => 'enable_shortcode_in_wp_widgets',
			'title'   => __( 'Enable Shortcode in WP Widgets', 'utilitify' ),
			'desc'    => '',
			'type'    => 'switch',
			'default' => 0,
		],
	];

	// Sections.
	$sections = [
		[
			'tab_id'        => 'general',
			'section_id'    => 'options',
			'section_title' => __( 'General Options', 'utilitify' ),
			'section_order' => 10,
			'fields'        => $default_general_options,
		],

		[
			'tab_id'        => 'general',
			'section_id'    => 'wordpress_auto_updates',
			'section_title' => __( 'WordPress Autoupdate Settings', 'utilitify' ),
			'section_order' => 10,
			'fields'        => $wordpress_auto_update_settings,
		],

		[
			'tab_id'        => 'general',
			'section_id'    => 'script_and_styles_settings',
			'section_title' => __( 'Script & Styles', 'utilitify' ),
			'section_order' => 15,
			'fields'        => $default_script_and_style_settings,
		],

		[
			'tab_id'        => 'general',
			'section_id'    => 'wordpress_meta_tag_settings',
			'section_title' => __( 'WordPress Meta Tag Settings', 'utilitify' ),
			'section_order' => 15,
			'fields'        => $wordpress_meta_tag_settings,
		],

		[
			'tab_id'        => 'general',
			'section_id'    => 'rss_feed_settings',
			'section_title' => __( 'RSS Feed Settings', 'utilitify' ),
			'section_order' => 15,
			'fields'        => $wordpress_rss_feed_settings,
		],

		[
			'tab_id'        => 'general',
			'section_id'    => 'wordpress_widget_settings',
			'section_title' => __( 'WordPress Widget Settings', 'utilitify' ),
			'section_order' => 15,
			'fields'        => $wordpress_widget_settings,
		],
	];


	$sections = apply_filters( 'kc_uf_filter_settings_sections', $sections );

	$wpsf_settings['sections'] = $sections;

	return $wpsf_settings;
}