<?php
/**
 * TGM Init Class
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.1
 */

include_once get_template_directory() . '/admin/tgm/class-tgm-plugin-activation.php';

if ( ! function_exists('alchemists_register_required_plugins')) {
	function alchemists_register_required_plugins() {

		$plugins = array(
			array(
				'name'         => 'Redux Framework',
				'slug'         => 'redux-framework',
				'required'     => true,
			),
			array(
				'name'         => 'Advanced Custom Fields Pro',
				'slug'         => 'advanced-custom-fields-pro',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/advanced-custom-fields-pro.zip',
				'required'     => true,
				'version'      => '6.7.0.2'
			),
			array(
				'name'         => 'WPBakery Page Builder for WordPress',
				'slug'         => 'js_composer',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/js_composer.zip',
				'required'     => true,
				'version'      => '8.7.2'
			),
			array(
				'name'         => 'Alchemists Extensions',
				'slug'         => 'alc-advanced-posts',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-advanced-posts.zip',
				'required'     => true,
				'version'      => '2.4.0'
			),
			array(
				'name'         => 'Alchemists SCSS Compiler',
				'slug'         => 'alc-scss',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-scss.zip',
				'required'     => true,
				'version'      => '4.5.0',
			),
			array(
				'name'         => 'One Click Demo Import',
				'slug'         => 'one-click-demo-import',
				'required' 	   => true,
			),
			array(
				'name'         => 'Contact Form 7',
				'slug'         => 'contact-form-7',
				'required'     => false,
			),
			array(
				'name'         => 'Breadcrumb Trail',
				'slug'         => 'breadcrumb-trail',
				'required'     => true,
			),
			array(
				'name'         => 'MailChimp for WordPress',
				'slug'         => 'mailchimp-for-wp',
				'required'     => false,
			),
			array(
				'name'         => 'Easy Custom Sidebars',
				'slug'         => 'easy-custom-sidebars',
				'required'     => false,
			),
			array(
				'name'         => 'Custom Twitter Feeds (Tweets Widget)',
				'slug'         => 'custom-twitter-feeds',
				'required'     => false,
			),
			array(
				'name'         => 'DF Social Count',
				'slug'         => 'df-social-count',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/df-social-count.zip',
				'required'     => false,
				'version'      => '1.3.2'
			),
			array(
				'name'         => 'WooCommerce',
				'slug'         => 'woocommerce',
				'required'     => false,
			),
			array(
				'name'         => 'Alchemists WooCommerce Grid / List toggle',
				'slug'         => 'alc-woocommerce-grid-list-toggle',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-woocommerce-grid-list-toggle.zip',
				'required'     => false,
				'version'      => '1.1.7'
			),
			array(
				'name'         => 'Alchemists Color Filters for WooCommerce',
				'slug'         => 'alc-color-filters',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-color-filters.zip',
				'required'     => false,
				'version'      => '1.0.7'
			),
			array(
				'name'         => 'Slider Revolution',
				'slug'         => 'revslider',
				'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/sliderrevolution-wordpress.zip',
				'required'     => false,
				'version'      => '6.7.40',
			),
			array(
				'name'         => 'Nav Menu Roles',
				'slug'         => 'nav-menu-roles',
				'required'     => true,
			),
			array(
				'name'         => 'Smash Balloon Social Photo Feed',
				'slug'         => 'instagram-feed',
				'required'     => false,
			),
		);

		// Require SportsPress only if SportsPress Pro is not activated
		if ( ! class_exists( 'SportsPress_Pro') ) {
			$plugins[] = array(
				'name'         => 'SportsPress',
				'slug'         => 'sportspress',
				'required'     => true,
				'version'      => '2.7.8',
			);
		}

		$config = array(
			'id'             => 'alchemists',               // Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path'   => '',                         // Default absolute path to pre-packaged plugins
			'menu'           => 'tgmpa-install-plugins',    // Menu slug
			'has_notices'    => true,                       // Show admin notices or not
			'is_automatic'   => true,                       // Automatically activate plugins after installation or not
			'dismissable'    => true,                       // If false, a user cannot dismiss the nag message.
			'dismiss_msg'    => '',                         // If 'dismissable' is false, this message will be output at top of nag.
			'message'        => '',
		);

		tgmpa( $plugins, $config );

	}
}

add_action( 'tgmpa_register', 'alchemists_register_required_plugins' );
