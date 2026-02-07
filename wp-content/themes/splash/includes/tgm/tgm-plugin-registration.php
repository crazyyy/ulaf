<?php

require_once get_template_directory() . '/includes/tgm/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'stm_require_plugins' );

function stm_require_plugins() {
	$plugins_path = 'https://splash.stylemixthemes.com/demo-plugins';
	$plugins      = array(
		array(
			'name'             => 'STM Configurations',
			'slug'             => 'stm-configurations',
			'source'           => $plugins_path . '/stm-configurations.zip',
			'required'         => true,
			'force_activation' => false,
			'version'          => '4.0.5',
		),
		array(
			'name'             => 'STM Importer',
			'slug'             => 'stm_importer',
			'source'           => $plugins_path . '/stm_importer.zip',
			'required'         => false,
			'force_activation' => false,
			'version'          => '4.3.6',
		),
		array(
			'name'             => 'WPBakery Visual Composer',
			'slug'             => 'js_composer',
			'source'           => $plugins_path . '/js_composer.zip',
			'required'         => true,
			'force_activation' => false,
			'version'          => '8.6.1',
			'external_url'     => 'https://wpbakery.com/',
		),
		array(
			'name'         => 'Revolution Slider',
			'slug'         => 'revslider',
			'source'       => $plugins_path . '/revslider.zip',
			'required'     => false,
			'version'      => '6.7.16',
			'external_url' => 'https://www.sliderrevolution.com/',
		),
		array(
			'name'             => 'Breadcrumb NavXT',
			'slug'             => 'breadcrumb-navxt',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => 'Contact Form 7',
			'slug'             => 'contact-form-7',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => 'Woocommerce',
			'slug'             => 'woocommerce',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => 'SportsPress',
			'slug'             => 'sportspress',
			'required'         => true,
			'force_activation' => false,
		),
		array(
			'name'     => 'Spotlight Social Media Feeds',
			'slug'     => 'spotlight-social-photo-feeds',
			'required' => false,
		),
		array(
			'name'         => 'MailChimp for WordPress',
			'slug'         => 'mailchimp-for-wp',
			'required'     => false,
			'external_url' => 'https://mc4wp.com/',
		),
		array(
			'name'         => 'AddToAny Share Buttons',
			'slug'         => 'add-to-any',
			'required'     => false,
			'external_url' => 'https://www.addtoany.com/',
		),
	);

	$config = array(
		'id'      => 'tgm_message_update',
		'strings' => array(
			'nag_type' => 'update-nag',
		),
	);

	if ( splash_is_layout( 'af' ) || splash_is_layout( 'sccr' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'rugby' ) ) {
		$plugins[] = array(
			'name'         => 'Custom Twitter Feeds',
			'slug'         => 'custom-twitter-feeds',
			'required'     => true,
			'external_url' => 'http://smashballoon.com/',
		);
	}

	if ( splash_is_layout( 'magazine_one' ) || splash_is_layout( 'soccer_news' ) ) {
		$plugins[] = array(
			'name'         => 'AccessPress Social Counter',
			'slug'         => 'accesspress-social-counter',
			'required'     => false,
			'external_url' => 'http://accesspressthemes.com',
		);
	}

	tgmpa( $plugins, $config );

}
