<?php

	/**
	 * For full documentation, please visit: http://docs.reduxframework.com/
	 * For a more extensive sample-config file, you may look at:
	 * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
	 *
	 * @author    Dan Fisher
	 * @package   Alchemists
	 * @since     1.0.0
	 * @version   4.6.0
	 */

	if ( ! class_exists( 'Redux' ) ) {
			return;
	}

	// This is our option name where all the Redux data is stored.
	$opt_name = "alchemists_data";

	/**
	 * ---> SET ARGUMENTS
	 * All the possible arguments for Redux.
	 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
	 * */

	$theme = wp_get_theme(); // For use with some settings. Not necessary.

	$args = array(
		'opt_name' => 'alchemists_data',
		'dev_mode' => FALSE,
		'disable_tracking' => TRUE,
		'use_cdn' => TRUE,
		'display_name' => $theme->get( 'Name' ),
		'display_version' => $theme->get( 'Version' ),
		'page_slug' => '_options',
		'page_title' => esc_html__('Theme Options', 'alchemists'),
		'admin_bar' => TRUE,
		'menu_type' => 'menu',
		'menu_title' => esc_html__('Theme Options', 'alchemists'),
		'admin_bar_icon' => 'dashicons-admin-generic',
		'allow_sub_menu' => TRUE,
		'page_parent_post_type' => 'alchemists_options_post_type',
		'customizer' => false,
		'hints' => array(
			'icon'          => 'el el-question-sign',
			'icon_position' => 'right',
			'icon_size'     => 'normal',
			'tip_style'     => array(
				'color' => 'dark',
			),
			'tip_position' => array(
				'my' => 'top left',
				'at' => 'bottom right',
			),
			'tip_effect' => array(
				'show' => array(
					'duration' => '500',
					'event'    => 'mouseover',
				),
				'hide' => array(
					'duration' => '500',
					'event'    => 'mouseleave unfocus',
				),
			),
		),
		'output' => TRUE,
		'output_tag' => TRUE,
		'settings_api' => TRUE,
		'cdn_check_time' => '1440',
		'compiler' => TRUE,
		'page_permissions' => 'manage_options',
		'save_defaults' => TRUE,
		'show_import_export' => TRUE,
		'transient_time' => '3600',
		'network_sites' => TRUE,
		'disable_tracking' => true,
	);

	Redux::setArgs( $opt_name, $args );

	/*
	 * ---> END ARGUMENTS
	 */




	/*
	 *
	 * ---> START SECTIONS
	 *
	 */

	// ACTUAL DECLARATION OF SECTIONS

	// General Settings
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__('General', 'alchemists'),
		'icon'      => 'el-icon-cogs',
		'id'        => 'alchemists__section-general',
		'fields'    => array(
			array(
				'id'        => 'alchemists__opt-logo-standard',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__opt-logo-retina',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo @2x', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image for retina displays or specify the image URL. It should be x2 time bigger than standard logo image.', 'alchemists'),
			),
			array(
				'id'             => 'alchemists__opt-logo-width',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Custom Logo Width', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your Logo width.', 'alchemists' ),
				'desc'           => esc_html__( 'Logo width can be set in px. Height will automatically calculated.', 'alchemists' ),
				'height'         => false,
				'mode'           => array(
					'width'  => true,
					'height' => false,
				),
			),
			array(
				'id'             => 'alchemists__opt-logo-width-mobile',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Custom Logo Width on Tablet/Mobile', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your Logo width on Tablet/Mobile devices.', 'alchemists' ),
				'desc'           => esc_html__( 'Logo width can be set in px. Height will automatically calculated.', 'alchemists' ),
				'height'         => false,
				'mode'           => array(
					'width'  => true,
					'height' => false,
				),
			),
			array(
				'id'        => 'alchemists__opt-logo-position',
				'type'      => 'switch',
				'title'     => esc_html__( 'Adjust Logo Position?', 'alchemists'),
				'subtitle'  => esc_html__( 'Enable Logo Position Adjustments.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'             => 'alchemists__opt-logo-position-desktop',
				'type'           => 'spacing',
				'mode'           => 'absolute',
				'units'          => array('px'),
				'units_extended' => 'false',
				'top'            => false,
				'left'           => false,
				'title'          => esc_html__( 'Logo Position - Desktop', 'alchemists' ),
				'subtitle'       => esc_html__( 'Adjust Logo position for desktops.', 'alchemists' ),
				'desc'           => esc_html__( 'Use this field to adjust Logo position. You can also use negative values. Note: For screens more than 992px', 'alchemists' ),
				'default'        => array(
					'bottom' => '0px',
					'right'  => '0px',
					'units'  => 'px',
				),
				'required'       => array('alchemists__opt-logo-position', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-logo-position-mobile',
				'type'           => 'spacing',
				'mode'           => 'absolute',
				'units'          => array('px'),
				'units_extended' => 'false',
				'top'            => false,
				'left'           => false,
				'title'          => esc_html__( 'Logo Position - Tablet/Mobile', 'alchemists' ),
				'subtitle'       => esc_html__( 'Adjust Logo position on Tablet/Mobile devices.', 'alchemists' ),
				'desc'           => esc_html__( 'Use this field to adjust Logo position. You can also use negative values. Note: For screens less than 991px', 'alchemists' ),
				'default'        => array(
					'bottom' => '5px',
					'right'  => '0px',
					'units'  => 'px',
				),
				'required'       => array('alchemists__opt-logo-position', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-logo-mobile',
				'type'      => 'switch',
				'title'     => esc_html__( 'Use separate Logo on Tablet/Mobile devices?', 'alchemists'),
				'subtitle'  => esc_html__( 'Adds option to use separate logo on mobile devices.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__opt-logo-mobile-standard',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo - Tablet/Mobile', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
				'required'  => array('alchemists__opt-logo-mobile', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-logo-mobile-retina',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo @2x - Tablet/Mobile', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image for retina displays or specify the image URL. It should be x2 time bigger than standard logo image.', 'alchemists'),
				'required'  => array('alchemists__opt-logo-mobile', '=', '1'),
			),
		)
	) );


	// Header
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__( 'Header', 'alchemists' ),
		'icon'      => 'el-icon-arrow-up',
		'id'        => 'alchemists__section-header',
		'fields'    => array(
			// Header Layout
			array(
				'id'        => 'alchemists__header-layout',
				'type'      => 'image_select',
				'compiler'  => true,
				'presets'   => true,
				'title'     => esc_html__( 'Header Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Predefined position of elements in the Header.', 'alchemists' ),
				'desc'      => esc_html__( 'Select preferred layout for the Header.', 'alchemists' ),
				'options'   => array(
					'layout-1' => array(
						'alt' => esc_html__( 'Layout 1', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/header-layout-1.png',
						'presets' => array(
							'alchemists__header-secondary-info-1'        => 1,
							'alchemists__header-secondary-info-2'        => 1,
							'alchemists__header-shopping-cart-on-action' => 'on_hover',
							'alchemists__header-banner'                  => 0,
							'alchemists__header-primary-social'          => 1,
							'alchemists__header-social-position'         => 'header_primary',
							'alchemists__header-pushy-panel'             => 1,
							'alchemists__header-search-form'             => 1,
							'alchemists__header-search-form-posiiton'    => 'header_secondary',
							'alchemists__header-primary-height'          => 62,
						)
					),
					'layout-2' => array(
						'alt' => esc_html__( 'Layout 2', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/header-layout-2.png',
						'presets' => array(
							'alchemists__header-secondary-info-1'        => 1,
							'alchemists__header-secondary-info-2'        => 1,
							'alchemists__header-shopping-cart-on-action' => 'on_hover',
							'alchemists__header-banner'                  => 1,
							'alchemists__header-primary-social'          => 0,
							'alchemists__header-pushy-panel'             => 0,
							'alchemists__header-search-form'             => 1,
							'alchemists__header-search-form-posiiton'    => 'header_primary',
							'alchemists__header-primary-height'          => 62,
						)
					),
					'layout-3' => array(
						'alt' => esc_html__( 'Layout 3', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/header-layout-3.png',
						'presets' => array(
							'alchemists__header-secondary-info-1'        => 0,
							'alchemists__header-secondary-info-2'        => 0,
							'alchemists__header-shopping-cart-on-action' => 'on_click',
							'alchemists__header-banner'                  => 0,
							'alchemists__header-primary-social'          => 1,
							'alchemists__header-social-position'         => 'top_bar',
							'alchemists__header-pushy-panel'             => 0,
							'alchemists__header-search-form'             => 1,
							'alchemists__header-primary-height'          => 70,
						)
					),
					'layout-4' => array(
						'alt' => esc_html__( 'Layout 4', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/header-layout-4.png',
						'presets' => array(
							'alchemists__header-secondary-info-1'        => 1,
							'alchemists__header-secondary-info-2'        => 0,
							'alchemists__header-shopping-cart-on-action' => 'on_click',
							'alchemists__header-banner'                  => 0,
							'alchemists__header-primary-social'          => 1,
							'alchemists__header-social-position'         => 'top_bar',
							'alchemists__header-pushy-panel'             => 0,
							'alchemists__header-search-form'             => 1,
							'alchemists__header-search-form-posiiton'    => 'header_secondary',
							'alchemists__header-primary-height'          => 70,
						)
					),
				),
				'default'   => 'layout-1'
			),
		)
	) );

	// Header Top Bar
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Top Bar', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-header-top-bar',
		'fields'     => array(
			array(
				'id'        => 'alchemists__header-top-bar',
				'type'      => 'switch',
				'title'     => esc_html__('Show Top Bar?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__header-top-bar-links',
				'type'      => 'switch',
				'title'     => esc_html__('Show Top Menu?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-top-bar', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-top-bar-divider-type',
				'type'      => 'select',
				'title'     => esc_html__('Top Menu Divider', 'alchemists'),
				'subtitle'  => esc_html__('Select a divider type', 'alchemists'),
				'options'   => array(
					'slash'     => esc_html__( 'Slash', 'alchemists' ),
					'backslash' => esc_html__( 'Backslash', 'alchemists' ),
					'vert_bar'  => esc_html__( 'Vertical Bar', 'alchemists' ),
					'bullet'    => esc_html__( 'Bullet', 'alchemists' ),
					'none'      => esc_html__( 'None', 'alchemists' ),
				),
				'default'   => 'slash',
				'required'  => array('alchemists__header-top-bar-links', '=', '1'),
			),
		)
	) );

	// Header Primary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Primary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-header-primary',
		'fields'     => array(
			// Header Primary Height
			array(
				'id'       => 'alchemists__header-primary-height',
				'type'     => 'slider',
				'title'    => esc_html__( 'Nav Height', 'alchemists' ),
				'desc'     => esc_html__( 'Set Header Primary/Navigation height in px', 'alchemists' ),
				'default'  => 62,
				'min'      => 30,
				'step'     => 1,
				'max'      => 120,
				'display_value' => 'text',
			),
			array(
				'id'          => 'alchemists__header-primary-nav-padding',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Nav Item Paddings?', 'alchemists' ),
				'desc'        => esc_html__( 'Turn on to customize Navigation Item paddings.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__header-primary-nav-padding-desktop',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'top'            => false,
				'bottom'         => false,
				'title'          => esc_html__( 'Nav Item Paddings - Desktop', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set paddings for Desktop.', 'alchemists' ),
				'desc'           => esc_html__( 'You can set custom Paddings for the Nav Items. Applied only for desktops.', 'alchemists' ),
				'default'            => array(
					'padding-left'   => '34px',
					'padding-right'  => '34px',
					'units'           => 'px',
				),
				'required'  => array( 'alchemists__header-primary-nav-padding', '=', '1' ),
			),
			array(
				'id'             => 'alchemists__header-primary-nav-padding-tablet',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'top'            => false,
				'bottom'         => false,
				'title'          => esc_html__( 'Nav Item Paddings - Tablet', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set paddings for Tablets.', 'alchemists' ),
				'desc'           => esc_html__( 'You can set custom Paddings for the Nav Items. Applied only for tablets.', 'alchemists' ),
				'default'            => array(
					'padding-left'   => '20px',
					'padding-right'  => '20px',
					'units'           => 'px',
				),
				'required'  => array( 'alchemists__header-primary-nav-padding', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__mobile-header-fixed',
				'type'      => 'switch',
				'title'     => esc_html__('Mobile Fixed Header', 'alchemists'),
				'desc'     => esc_html__( 'If enabled the Mobile Header will be fixed on top while scrolling', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'       => 'alchemists__mobile-nav-height',
				'type'     => 'slider',
				'title'    => esc_html__( 'Mobile Header Height', 'alchemists' ),
				'desc'     => esc_html__( 'Set Mobile Header Custom Height in px', 'alchemists' ),
				'default'  => 100,
				'min'      => 50,
				'step'     => 1,
				'max'      => 240,
				'display_value' => 'text',
			),
			array(
				'id'        => 'alchemists__mobile-nav-fullwidth',
				'type'      => 'switch',
				'title'     => esc_html__('Mobile Nav Full Width', 'alchemists'),
				'desc'     => esc_html__( 'If enabled the Mobile Nav will be full width (100% width)', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'       => 'alchemists__mobile-nav-width',
				'type'     => 'slider',
				'title'    => esc_html__( 'Mobile Nav Width', 'alchemists' ),
				'desc'     => esc_html__( 'Set Mobile Nav Custom Width in px', 'alchemists' ),
				'default'  => 270,
				'min'      => 200,
				'step'     => 1,
				'max'      => 320,
				'display_value' => 'text',
				'required'  => array('alchemists__mobile-nav-fullwidth', '=', '0'),
			),
		)
	) );

	// Header Secondary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Secondary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists_subsection-header-secondary',
		'fields'     => array(
			array(
				'id'        => 'alchemists__header-secondary',
				'type'      => 'switch',
				'title'     => esc_html__('Show Header Secondary?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			// Email - Primary
			array(
				'id'        => 'alchemists__header-secondary-info-1',
				'type'      => 'switch',
				'title'     => esc_html__('Show Primary Email?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-secondary', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-1-label',
				'type'      => 'text',
				'title'     => esc_html__( 'Label for Primary Email Address', 'alchemists' ),
				'default'   => esc_html__( 'Join Our Team!', 'alchemists' ),
				'required'  => array('alchemists__header-secondary-info-1', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-1-email',
				'type'      => 'text',
				'title'     => esc_html__('Primary Email address', 'alchemists'),
				'default'   => 'tryouts@alchemists.com',
				'required'  => array('alchemists__header-secondary-info-1', '=', '1'),
				'desc'      => esc_html__( 'As an option you can also enter site url (e.g. http://yoursite.com) or a telephone number.', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-1-txt',
				'type'      => 'text',
				'title'     => esc_html__( 'Text for Primary Email Address', 'alchemists' ),
				'default'   => '',
				'desc'      => esc_html__( 'This text replaces email address, site uro or telephone number.', 'alchemists'),
				'required'  => array('alchemists__header-secondary-info-1', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-1-icon-custom',
				'type'      => 'text',
				'title'     => esc_html__('Primary Email Custom Icon', 'alchemists'),
				'desc'      => __( 'Add your custom icon, e.g. <code>&lt;i class="fa fa-user"&gt;&lt;/i&gt;</code> or <code>&lt;img src="PATH_TO_IMAGE" /&gt;</code>', 'alchemists'),
				'default'   => '',
				'required'  => array('alchemists__header-secondary-info-1', '=', '1'),
			),

			// Email - Secondary
			array(
				'id'        => 'alchemists__header-secondary-info-2',
				'type'      => 'switch',
				'title'     => esc_html__('Show Secondary Email?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-secondary', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-2-label',
				'type'      => 'text',
				'title'     => esc_html__( 'Label for Secondary Email address', 'alchemists' ),
				'default'   => esc_html__( 'Contact Us', 'alchemists' ),
				'required'  => array('alchemists__header-secondary-info-2', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-2-email',
				'type'      => 'text',
				'title'     => esc_html__('Secondary Email address', 'alchemists'),
				'default'   => 'info@alchemists.com',
				'required'  => array('alchemists__header-secondary-info-2', '=', '1'),
				'desc'      => esc_html__( 'As an option you can also enter site url (e.g. http://yoursite.com) or a telephone number.', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-2-txt',
				'type'      => 'text',
				'title'     => esc_html__( 'Text for Secondary Email address', 'alchemists' ),
				'default'   => '',
				'required'  => array('alchemists__header-secondary-info-2', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-secondary-info-2-icon-custom',
				'type'      => 'text',
				'title'     => esc_html__('Secondary Email Custom Icon', 'alchemists'),
				'desc'      => __( 'Add your custom icon, e.g. <code>&lt;i class="fa fa-user"&gt;&lt;/i&gt;</code> or <code>&lt;img src="PATH_TO_IMAGE" /&gt;</code>', 'alchemists'),
				'default'   => '',
				'required'  => array('alchemists__header-secondary-info-2', '=', '1'),
			),


			// Shopping Cart
			array(
				'id'        => 'alchemists__header-shopping-cart-notice',
				'type'      => 'info',
				'notice'    => true,
				'icon'      => 'el el-icon-warning-sign',
				'style'     => 'warning',
				'title'     => esc_html__('WooCommerce Options', 'alchemists'),
				'desc'      => esc_html__('The following options are available if WooCommerce installed.', 'alchemists'),
				'required'  => array('alchemists__header-secondary', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-shopping-cart',
				'type'      => 'switch',
				'title'     => esc_html__('Show Shopping Cart?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-secondary', '=', '1'),
			),
			// Header Shopping Cart Type
			array(
				'id'        => 'alchemists__header-shopping-cart-on-action',
				'type'      => 'select',
				'title'     => esc_html__('Shopping Cart Action', 'alchemists'),
				'subtitle'  => esc_html__('Select a trigger for the cart', 'alchemists'),
				'desc'      => esc_html__('Depends on selected option the cart has different styling.', 'alchemists'),
				'options'   => array(
					'on_hover' => esc_html__( 'On hover', 'alchemists' ),
					'on_click' => esc_html__( 'On click', 'alchemists' ),
				),
				'default'   => 'on_hover',
				'required'  => array('alchemists__header-shopping-cart', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-shopping-cart-icon-custom',
				'type'      => 'text',
				'title'     => esc_html__('Shopping Cart Custom Icon', 'alchemists'),
				'desc'      => __( 'Add your custom icon, e.g. <code>&lt;i class="fa fa-user"&gt;&lt;/i&gt;</code> or <code>&lt;img src="PATH_TO_IMAGE" /&gt;</code>', 'alchemists'),
				'default'   => '',
				'required'  => array('alchemists__header-shopping-cart', '=', '1'),
			),


			// Banner
			array(
				'id'        => 'alchemists__header-banner',
				'type'      => 'switch',
				'title'     => esc_html__( 'Show Banner?', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-secondary', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-banner-image',
				'type'      => 'media',
				'url'       => true,
				'title'     => esc_html__( 'Banner Image', 'alchemists' ),
				'compiler'  => 'true',
				'desc'      => esc_html__( 'Recommended image size is 420x60 ', 'alchemists'),
				'required'  => array('alchemists__header-banner', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-banner-link',
				'type'      => 'text',
				'title'     => esc_html__( 'Banner Link', 'alchemists'),
				'default'   => '#',
				'desc'      => esc_html__( 'Enter a link for the banner.', 'alchemists'),
				'required'  => array('alchemists__header-banner', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-banner-target',
				'type'      => 'switch',
				'title'     => esc_html__( 'Open link a new tab?', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array('alchemists__header-banner', '=', '1'),
			),

		)
	) );



	// Header Tertiary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Tertiary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists_subsection-header-tertiary',
		'fields'     => array(
			array(
				'id'        => 'alchemists__header-tertiary',
				'type'      => 'switch',
				'title'     => esc_html__( 'Show Header Tertiary?', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			// Header Tertiary Height
			array(
				'id'       => 'alchemists__header-tertiary-height',
				'type'     => 'slider',
				'title'    => esc_html__( 'Nav Height', 'alchemists' ),
				'desc'     => esc_html__( 'Set Header Tertiary/Navigation height in px', 'alchemists' ),
				'default'  => 62,
				'min'      => 0,
				'step'     => 1,
				'max'      => 120,
				'display_value' => 'text',
				'required'  => array ('alchemists__header-tertiary', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__header-tertiary-heading',
				'type'      => 'text',
				'title'     => esc_html__( 'Nav Heading', 'alchemists' ),
				'desc'      => esc_html__( 'Type your heading here.', 'alchemists' ),
				'default'   => esc_html__( 'Heading', 'alchemists' ),
				'required'  => array ('alchemists__header-tertiary', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__header-tertiary-toggle',
				'type'      => 'text',
				'title'     => esc_html__( 'Toggle Text', 'alchemists' ),
				'desc'      => esc_html__( 'Toggle text displayed on mobile devices.', 'alchemists' ),
				'default'   => esc_html__( 'More', 'alchemists' ),
				'required'  => array ('alchemists__header-tertiary', '=', '1' ),
			),

		)
	) );



	// Header Social Links
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Social Links', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-header-social',
		'fields'     => array(
			array(
				'id'        => 'alchemists__header-primary-social',
				'type'      => 'switch',
				'title'     => esc_html__('Show Social Links?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			// Social Links Position
			array(
				'id'        => 'alchemists__header-social-position',
				'type'      => 'select',
				'title'     => esc_html__('Social Links Position', 'alchemists'),
				'subtitle'  => esc_html__('Select a place to display Social Links', 'alchemists'),
				'options'   => array(
					'top_bar'        => esc_html__( 'Top Bar', 'alchemists' ),
					'header_primary' => esc_html__( 'Header Primary', 'alchemists' ),
				),
				'default'   => 'header_primary',
				'required'  => array('alchemists__header-primary-social', '=', '1'),
			),
			array(
				'id'       => 'alchemists__header-primary-social-links',
				'type'     => 'sortable',
				'title'    => esc_html__( 'Social Media Links', 'alchemists' ),
				'subtitle' => esc_html__( 'Define and reorder these links however you want.', 'alchemists' ),
				'desc'     => esc_html__( 'Leave empty a field if you don\'t want to display particular social media link.', 'alchemists' ),
				'label'    => true,
				'mode'     => 'text',
				'options'  => array(
					'Facebook URL' => '',
					'Twitter URL'   => '',
					'LinkedIn URL' => '',
					'Instagram URL' => '',
					'Github URL' => '',
					'VK URL' => '',
					'YouTube URL' => '',
					'Pinterest URL' => '',
					'Tumblr URL' => '',
					'Dribbble URL' => '',
					'Vimeo URL' => '',
					'Flickr URL' => '',
					'Yelp URL' => '',
					'Telegram URL' => '',
					'Snapchat URL' => '',
					'Twitch URL' => '',
					'Faceit URL' => '',
					'Steam URL' => '',
					'Discord URL' => '',
					'Mixer URL' => '',
					'TikTok URL' => '',
					'Email' => '',
					'RSS' => '',
				),
				'default'  => array(
					'Facebook URL'   => '#',
					'Twitter URL'   => '#',
					'LinkedIn URL' => '',
					'Instagram URL' => '',
					'Github URL' => '',
					'VK URL' => '',
					'YouTube URL' => '',
					'Pinterest URL' => '',
					'Tumblr URL' => '',
					'Dribbble URL' => '',
					'Vimeo URL' => '',
					'Flickr URL' => '',
					'Yelp URL' => '',
					'Telegram URL' => '',
					'Snapchat URL' => '',
					'Twitch URL' => '',
					'Faceit URL' => '',
					'Steam URL' => '',
					'Discord URL' => '',
					'Mixer URL' => '',
					'TikTok URL' => '',
					'Email' => '',
					'RSS' => '',
				),
				'required'  => array('alchemists__header-primary-social', '=', '1'),
			),
		)
	) );


	// Header Search Form
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Search Form', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-search-form',
		'fields'     => array(
			// Search Form
			array(
				'id'        => 'alchemists__header-search-form',
				'type'      => 'switch',
				'title'     => esc_html__('Show Search Form?', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			// Search Form Position
			array(
				'id'        => 'alchemists__header-search-form-posiiton',
				'type'      => 'select',
				'title'     => esc_html__('Search Form Position', 'alchemists'),
				'subtitle'  => esc_html__('Select a place to display Search Form', 'alchemists'),
				'options'   => array(
					'header_secondary' => esc_html__( 'Header Secondary', 'alchemists' ),
					'header_primary'   => esc_html__( 'Header Primary', 'alchemists' ),
				),
				'default'   => 'header_secondary',
				'required'  => array('alchemists__header-search-form', '=', '1'),
			),
			// Search Form Width
			array(
				'id'        => 'alchemists__header-search-form-width',
				'type'      => 'switch',
				'title'     => esc_html__( 'Customize Search Form width?', 'alchemists'),
				'subtitle'  => esc_html__( 'Enables options to change Search Form width.', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-search-form', '=', '1'),
			),
			array(
				'id'       => 'alchemists__header-search-form-width-desktop',
				'type'     => 'slider',
				'title'    => esc_html__( 'Search Form Width - Desktop', 'alchemists' ),
				'desc'     => esc_html__( 'Set Search Form Width on Desktop, in px', 'alchemists' ),
				'default'  => 360,
				'min'      => 150,
				'step'     => 1,
				'max'      => 450,
				'display_value' => 'text',
				'required'  => array( 'alchemists__header-search-form-width', '=', '1' ),
			),
			array(
				'id'       => 'alchemists__header-search-form-width-tablet',
				'type'     => 'slider',
				'title'    => esc_html__( 'Search Form Width - Tablet', 'alchemists' ),
				'desc'     => esc_html__( 'Set Search Form Width on Tablet, in px', 'alchemists' ),
				'default'  => 200,
				'min'      => 120,
				'step'     => 1,
				'max'      => 450,
				'display_value' => 'text',
				'required'  => array( 'alchemists__header-search-form-width', '=', '1' ),
			),
		)
	) );


	// Pushy Panel
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Pushy Panel', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-header-pushy-panel',
		'fields'     => array(
			array(
				'id'        => 'alchemists__header-pushy-panel-notice',
				'type'      => 'info',
				'notice'    => true,
				'icon'      => 'el el-info-circle',
				'style'     => 'info',
				'title'     => esc_html__('Visibility', 'alchemists'),
				'desc'      => esc_html__('Pushy Panel can not be displayed in some Header Layouts.', 'alchemists')
			),
			array(
				'id'        => 'alchemists__header-pushy-panel',
				'type'      => 'switch',
				'title'     => esc_html__('Side Panel', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'        => 'alchemists__header-pushy-panel-mobile',
				'type'      => 'switch',
				'title'     => esc_html__('Side Panel on Tablets and Mobile', 'alchemists'),
				'desc'      => esc_html__('If enabled the Side Panel is displayed on Tablets and Mobile devices.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-pushy-panel', '=', '1'),
			),
			array(
				'id'        => 'alchemists__header-pushy-panel-logo',
				'type'      => 'switch',
				'title'     => esc_html__('Logo in the Side Panel', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
				'required'  => array('alchemists__header-pushy-panel', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-logo-pushy-standard',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo', 'alchemists'),
				'compiler'  => 'true',
				//'mode'      => false, // Can be set to false to allow any media type, or can also be set to any mime type.
				'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
				'required'  => array('alchemists__header-pushy-panel-logo', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-logo-pushy-retina',
				'type'      => 'media',
				'url'       => true,
				'readonly'  => false,
				'title'     => esc_html__('Custom Logo @2x', 'alchemists'),
				'compiler'  => 'true',
				//'mode'      => false, // Can be set to false to allow any media type, or can also be set to any mime type.
				'desc'      => esc_html__('Upload your image for retina displays or specify the image URL. It should be x2 time bigger than standard logo image.', 'alchemists'),
				'required'  => array('alchemists__header-pushy-panel-logo', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-logo-pushy-width',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Custom Logo Width', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your Logo width.', 'alchemists' ),
				'desc'           => esc_html__( 'Logo width can be set in px. Height will automatically calculated. Note: 94px by default.', 'alchemists' ),
				'height'         => false,
				'mode'           => array(
					'width'  => true,
					'height' => false,
				),
				'required'  => array('alchemists__header-pushy-panel-logo', '=', '1'),
			),
		)
	) );


	// Page Heading
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Page Heading', 'alchemists' ),
		'id'     => 'alchemists__section-page-heading',
		'icon'   => 'el el-website',
		'fields' => array(

		)
	) );


	// Page Heading: Title
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Title', 'alchemists' ),
		'id'     => 'alchemists__section-page-heading-title',
		'subsection' => true,
		'fields' => array(

			array(
				'id'       => 'alchemists__page-title--section-content-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Content', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Page Heading.', 'alchemists' ),
				'indent'   => true
			),

			array(
				'id'        => 'alchemists__page-title-layout',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__( 'Title Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Position of elements in the Page Heading.', 'alchemists' ),
				'desc'      => esc_html__( 'Select preferred layout for pages where Page Title is displayed.', 'alchemists' ),
				'options'   => array(
					'1' => array(
						'alt' => esc_html__('Centered', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/title-layout-1.png'
					),
					'2' => array(
						'alt' => esc_html__('Title - Left, Breadcrumbs - Right', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/title-layout-2.png'
					),
				),
				'default'   => '1'
			),
			array(
				'id'          => 'alchemists__opt-page-title-spacing-on',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Page Heading Padding?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to customize top padding.', 'alchemists'),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__opt-page-title-spacing-desktop',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Page Headings Paddings - Desktop', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Desktop.', 'alchemists'),
				'desc'           => esc_html__('You can set Top Paddings for the Page Headings. Applied only for desktops.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '110px',
					'padding-bottom'  => '106px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-page-title-spacing-on', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-page-title-spacing-tablet',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Page Headings Paddings - Tablet', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Tablets.', 'alchemists'),
				'desc'           => esc_html__('You can set Top Paddings for the Page Headings. Applied only for tablets.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '50px',
					'padding-bottom'  => '50px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-page-title-spacing-on', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-page-title-spacing-mobile',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Page Headings Paddings - Mobile', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Mobile devices.', 'alchemists'),
				'desc'           => esc_html__('You can set Top Paddings for the Page Headings. Applied only for mobile devices.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '50px',
					'padding-bottom'  => '50px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-page-title-spacing-on', '=', '1'),
			),
			array(
				'id'          => 'alchemists__opt-page-title-display',
				'type'        => 'switch',
				'title'       => esc_html__('Show Page Title?', 'alchemists'),
				'subtitle'    => esc_html__('The Page Title is displayed by default.', 'alchemists'),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__opt-page-title-tag',
				'type'           => 'select',
				'title'          => esc_html__('Page Title Tag', 'alchemists'),
				'subtitle'       => esc_html__('Select preferred tag for the title.', 'alchemists'),
				'options'        => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'p' => 'p',
				),
				'default'        => 'h1',
				'desc'           => esc_html__( 'This is a SEO option and doesn\'t affect on the Page Title styling.', 'alchemists' ),
				'required'  => array('alchemists__opt-page-title-display', '=', '1'),
			),
			array(
				'id'          => 'alchemists__opt-page-title-highlight',
				'type'        => 'switch',
				'title'       => esc_html__('Highlight Page Heading Last Word?', 'alchemists'),
				'subtitle'    => esc_html__('The last word highlighted by default.', 'alchemists'),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__opt-page-title-display', '=', '1'),
			),
			array(
				'id'          => 'alchemists__opt-page-title-breadcrumbs',
				'type'        => 'switch',
				'title'       => esc_html__('Show Breadcrumbs?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to show Breadcrumbs', 'alchemists'),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),

			array(
				'id'       => 'alchemists__page-title--section-content-end',
				'type'     => 'section',
				'indent'   => false,
			),


			array(
				'id'       => 'alchemists__page-title--section-styling-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Styling', 'alchemists' ),
				'subtitle' => esc_html__( 'Styling for the Page Heading.', 'alchemists' ),
				'indent'   => true
			),

			array(
				'id'          => 'alchemists__page-title-background',
				'type'        => 'background',
				'output'      => array('.page-heading'),
				'title'       => esc_html__('Page Heading Background', 'alchemists'),
			),
			array(
				'id'          => 'alchemists__opt-page-title-overlay-on',
				'type'        => 'switch',
				'title'       => esc_html__('Add Overlay?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to add color overlay.', 'alchemists'),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__opt-paget-title-overlay',
				'type'        => 'background',
				'output'      => array('.page-heading::before'),
				'title'       => esc_html__('Page Heading Overlay Background', 'alchemists'),
				'required'    => array('alchemists__opt-page-title-overlay-on', '=', '1'),
			),
			array(
				'id'          => 'alchemists__opt-paget-title-overlay-opacity-on',
				'type'        => 'switch',
				'title'       => esc_html__('Adjust Overlay opacity?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to add customize overlay opacity.', 'alchemists'),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'            => 'alchemists__opt-paget-title-overlay-opacity',
				'type'          => 'slider',
				'title'         => esc_html__( 'Page Heading Overlay Opacity', 'alchemists' ),
				'subtitle'      => esc_html__( 'Option to change overlay opacity.', 'alchemists' ),
				'desc'          => esc_html__( 'Drag the slider to adjust the Page Heading Overlay opacity in %', 'alchemists' ),
				'default'       => 40,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'required'      => array( 'alchemists__opt-paget-title-overlay-opacity-on', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__opt-page-title-duotone',
				'type'        => 'switch',
				'title'       => esc_html__('Add Duotone effect?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to add duotone effect.', 'alchemists'),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__opt-page-title-duotone-color',
				'type'           => 'select',
				'title'          => esc_html__( 'Duotone Colors', 'alchemists'),
				'subtitle'       => esc_html__( 'Select duotone colors', 'alchemists'),
				'desc'           => esc_html__( 'Use color scheme predefined combination or select Custom Color to create your own.', 'alchemists' ),
				'options'        => array(
					'primary'    => esc_html__( 'Primary', 'alchemists' ),
					'secondary'  => esc_html__( 'Secondary', 'alchemists' ),
					'tertiary'   => esc_html__( 'Tertiary', 'alchemists' ),
					'quaternary' => esc_html__( 'Quaternary', 'alchemists' ),
					'info'       => esc_html__( 'Info', 'alchemists' ),
					'blue'       => esc_html__( 'Blue', 'alchemists' ),
					'red'        => esc_html__( 'Red', 'alchemists' ),
					'grey'       => esc_html__( 'Grey', 'alchemists' ),
					'yellow'     => esc_html__( 'Yellow', 'alchemists' ),
					'custom'     => esc_html__( 'Custom Color', 'alchemists' ),
				),
				'default'     => 'primary',
				'required'    => array('alchemists__opt-page-title-duotone', '=', '1'),
			),
			array(
				'id'          => 'alchemists__opt-page-title-duotone-color-1',
				'type'        => 'background',
				'output'      => array( '.effect-duotone--custom .effect-duotone__layer::after' ),
				'title'       => esc_html__( 'Duotone Color 1', 'alchemists' ),
				'subtitle'    => esc_html__( 'Use dark color for this option.', 'alchemists' ),
				'desc'        => esc_html__( 'It is important to use completely black or dark color. In other case you will get unexpected result.', 'alchemists' ),
				'background-repeat'     => false,
				'background-attachment' => false,
				'background-position'   => false,
				'background-image'      => false,
				'background-size'       => false,
				'preview'               => false,
				'transparent'           => false,
				'required'              => array(
					array( 'alchemists__opt-page-title-duotone', '=', '1' ),
					array( 'alchemists__opt-page-title-duotone-color', '=', 'custom' ),
				),
			),
			array(
				'id'          => 'alchemists__opt-page-title-duotone-color-2',
				'type'        => 'background',
				'output'      => array( '.effect-duotone--custom .effect-duotone__layer-inner' ),
				'title'       => esc_html__( 'Duotone Color 2', 'alchemists' ),
				'subtitle'    => esc_html__( 'Use any color for this option.', 'alchemists' ),
				'desc'        => esc_html__( 'This color will be mixed with Duotone Color 1 and applied to the Page Heading image.', 'alchemists' ),
				'background-repeat'     => false,
				'background-attachment' => false,
				'background-position'   => false,
				'background-image'      => false,
				'background-size'       => false,
				'preview'               => false,
				'transparent'           => false,
				'required'    => array(
					array( 'alchemists__opt-page-title-duotone', '=', '1' ),
					array( 'alchemists__opt-page-title-duotone-color', '=', 'custom' ),
				),
			),
			array(
				'id'          => 'alchemists__custom_breadcrumbs',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Breadcrumbs Colors?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to change colors for Breadcrumbs.', 'alchemists'),
				'required'    => array('alchemists__opt-page-title-breadcrumbs', '=', '1'),
				'default'     => false,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__opt-page-title-breadcrumbs-color',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Breadcrumbs Link Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for Breadcrumbs links.', 'alchemists' ),
				'output'      => array( '.breadcrumbs ul.trail-items > li > a' ),
				'default'     => array(
					'regular' => '#fff',
					'hover'   => '#ffdc11',
					'active'  => '#ffdc11',
				),
				'required'    => array(
					array('alchemists__opt-page-title-breadcrumbs', '=', '1',),
					array('alchemists__custom_breadcrumbs', '=', '1',),
				),
			),
			array(
				'id'          => 'alchemists__opt-page-title-breadcrumbs-txt-color',
				'type'        => 'color',
				'output'      => array( '.breadcrumbs ul.trail-items > li.trail-end' ),
				'title'       => esc_html__( 'Breadcrumbs Current Crumb Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for Breadcrumbs Current text.', 'alchemists' ),
				'default'     => '#9a9da2',
				'required'    => array(
					array('alchemists__opt-page-title-breadcrumbs', '=', '1',),
					array('alchemists__custom_breadcrumbs', '=', '1',),
				),
			),
			array(
				'id'          => 'alchemists__opt-page-title-breadcrumbs-sep-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Breadcrumbs Separator Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for Breadcrumbs Separator.', 'alchemists' ),
				'default'     =>  '#9a9da2',
				'required'    => array(
					array('alchemists__opt-page-title-breadcrumbs', '=', '1',),
					array('alchemists__custom_breadcrumbs', '=', '1',),
				),
			),

			array(
				'id'       => 'alchemists__page-title--section-styling-end',
				'type'     => 'section',
				'indent'   => false,
			),






		)
	) );


	// Hero Unit: Static
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Hero Unit - Static', 'alchemists' ),
		'id'         => 'alchemists__subsection-hero-static',
		'subsection' => true,
		'fields'     => array(

			// Hero Static -- Content
			array(
				'id'       => 'alchemists__hero-static--section-content-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Content', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Hero Static output.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-title',
				'type'      => 'text',
				'title'     => esc_html__( 'Title', 'alchemists' ),
				'validate'  => 'html',
				'desc'      => esc_html__( 'Enter Hero Unit Title.', 'alchemists' ),
				'default'   => wp_kses_post( __( 'The <span class="text-primary">Alchemists</span>', 'alchemists' ) ),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-subtitle',
				'type'      => 'text',
				'title'     => esc_html__( 'Subtitle', 'alchemists' ),
				'desc'      => esc_html__( 'Enter Hero Unit Subtitle.', 'alchemists' ),
				'default'   => esc_html__( 'Elric Bros School', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-desc',
				'type'      => 'textarea',
				'rows'      => 2,
				'title'     => esc_html__( 'Description', 'alchemists' ),
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipisi nel elit, sed do eiusmod tempor incididunt ut labore et dolore.', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-btn',
				'type'      => 'switch',
				'title'     => esc_html__('Show Button?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to display button.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-btn-txt',
				'type'      => 'text',
				'title'     => esc_html__( 'Button Text', 'alchemists' ),
				'desc'      => esc_html__( 'Enter Button Text.', 'alchemists' ),
				'default'   => esc_html__( 'Read More', 'alchemists' ),
				'required'  => array('alchemists__opt-page-heading-hero-btn', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-btn-link',
				'type'      => 'text',
				'title'     => esc_html__( 'Button Link', 'alchemists' ),
				'desc'      => esc_html__( 'Enter Button Link.', 'alchemists' ),
				'default'   => '#',
				'required'  => array('alchemists__opt-page-heading-hero-btn', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-btn-target',
				'type'      => 'switch',
				'title'     => esc_html__('Open in a new window?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to open page in a new window.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array('alchemists__opt-page-heading-hero-btn', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-btn-type',
				'type'           => 'select',
				'title'          => esc_html__('Button Type', 'alchemists'),
				'subtitle'       => esc_html__('Select button type', 'alchemists'),
				'options'        => array(
					'outline'   => esc_html__( 'Outline', 'alchemists' ),
					'fill'      => esc_html__( 'Fill', 'alchemists' ),
				),
				'default'     => 'outline',
				'required'    => array('alchemists__opt-page-heading-hero-btn', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-btn-class',
				'type'      => 'text',
				'title'     => esc_html__( 'Button Custom Class', 'alchemists' ),
				'desc'      => esc_html__( 'Enter custom CSS class (optional).', 'alchemists' ),
				'default'   => '',
				'required'  => array('alchemists__opt-page-heading-hero-btn', '=', '1'),
			),
			array(
				'id'       => 'alchemists__hero-static--section-content-end',
				'type'     => 'section',
				'indent'   => false,
			),


			// Hero Static -- Styling
			array(
				'id'       => 'alchemists__hero-static--section-styling-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Styling', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Hero Static styling.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__opt-page-heading-hero-bg',
				'type'        => 'background',
				'output'      => array('.hero-unit'),
				'title'       => esc_html__('Hero Unit Background', 'alchemists'),
				'preview'     => true,
				'default'     => array(
					'background-color'      => '#27313b',
					'background-image'      => get_template_directory_uri() . '/assets/images/header_bg.jpg',
					'background-repeat'     => 'no-repeat',
					'background-position'   => 'center top',
					'background-attachment' => 'inherit',
					'background-size'       => 'cover',
				)
			),
			array(
				'id'          => 'alchemists__opt-page-heading-hero-title-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Title Color', 'alchemists' ),
				'desc'        => esc_html__( 'Pick a custom color for the title.', 'alchemists' ),
				'output'      => array('.hero-unit__title'),
				'transparent' => false
			),
			array(
				'id'          => 'alchemists__opt-page-heading-hero-subtitle-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Subtitle Color', 'alchemists' ),
				'desc'        => esc_html__( 'Pick a custom color for the subtitle.', 'alchemists' ),
				'output'      => array('.hero-unit__subtitle'),
				'transparent' => false
			),
			array(
				'id'          => 'alchemists__opt-page-heading-hero-desc-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Description Color', 'alchemists' ),
				'desc'        => esc_html__( 'Pick a custom color for the description text.', 'alchemists' ),
				'output'      => array('.hero-unit__desc'),
				'transparent' => false
			),
			array(
				'id'        => 'alchemists__opt-page-heading-hero-stars',
				'type'      => 'switch',
				'title'     => esc_html__('Show Stars?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to display stars.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__opt-page-heading-hero-stars-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Stars Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Option to customize stars color.', 'alchemists'),
				'desc'        => esc_html__( 'Pick a custom color for the stars.', 'alchemists' ),
				'output'      => array('.hero-unit__decor'),
				'transparent' => false,
				'required'    => array('alchemists__opt-page-heading-hero-stars', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-height',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Hero Unit Height', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 640px', 'alchemists' ),
				),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-height-sm',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Hero Unit Height (Mobile)', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 400px', 'alchemists' ),
				),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-img',
				'type'           => 'switch',
				'title'          => esc_html__('Show Hero Image?', 'alchemists'),
				'subtitle'       => esc_html__('Turn on to display Hero Image.', 'alchemists'),
				'default'        => 1,
				'on'             => esc_html__( 'Yes', 'alchemists' ),
				'off'            => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-img-upload',
				'type'           => 'media',
				'url'            => true,
				'title'          => esc_html__('Custom Hero Image', 'alchemists'),
				'compiler'       => true,
				'desc'           => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
				'required'       => array('alchemists__opt-page-heading-hero-img', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-page-heading-hero-align',
				'type'           => 'select',
				'title'          => esc_html__('Alignment', 'alchemists'),
				'subtitle'       => esc_html__('Select preferred text alignment', 'alchemists'),
				'options'        => array(
					'left'   => esc_html__( 'Left', 'alchemists' ),
					'center' => esc_html__( 'Center', 'alchemists' ),
					'right'  => esc_html__( 'Right', 'alchemists' ),
				),
				'default'   => 'left',
				'required'       => array('alchemists__opt-page-heading-hero-img', '=', '0'),
			),
			array(
				'id'       => 'alchemists__hero-static--section-styling-end',
				'type'     => 'section',
				'indent'   => false,
			),


		)
	) );


	// Hero Unit: Slider
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Hero Unit - Posts Slider', 'alchemists' ),
		'id'         => 'alchemists__subsection-hero-posts-slider',
		'subsection' => true,
		'fields'     => array(

			// Hero Post Slider -- Content
			array(
				'id'       => 'alchemists__hero-posts-slider--section-content-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Post Options', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize posts output.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--per-page',
				'type'     => 'spinner',
				'title'    => esc_html__( 'Number of Posts', 'alchemists' ),
				'subtitle' => esc_html__( 'Number of Blog Posts to display', 'alchemists' ),
				'desc'     => esc_html__( 'Use arrows to increase/decrease the number of posts to display.', 'alchemists' ),
				'default'  => '3',
				'min'      => '1',
				'step'     => '1',
				'max'      => '24',
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--orderby',
				'type'     => 'select',
				'title'    => esc_html__( 'Order By', 'alchemists' ),
				'subtitle' => esc_html__( 'Posts Order By', 'alchemists' ),
				'desc'     => esc_html__( 'Sort retrieved posts by parameter.', 'alchemists' ),
				'options'   => array(
					'date'          => esc_html__( 'Date', 'alchemists' ),
					'ID'            => esc_html__( 'ID', 'alchemists' ),
					'author'        => esc_html__( 'Author', 'alchemists' ),
					'title'         => esc_html__( 'Title', 'alchemists' ),
					'modified'      => esc_html__( 'Modified', 'alchemists' ),
					'comment_count' => esc_html__( 'Comment count', 'alchemists' ),
					'menu_order'    => esc_html__( 'Menu order', 'alchemists' ),
					'rand'          => esc_html__( 'Random', 'alchemists' ),
				),
				'default'   => 'date',
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--order',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Order', 'alchemists' ),
				'subtitle' => esc_html__( 'Posts Order', 'alchemists' ),
				'desc'     => esc_html__( 'Designates the ascending or descending order of the "orderby" parameter.', 'alchemists' ),
				'options' => array(
					'DESC' => 'Descending',
					'ASC' => 'Ascending',
				 ),
				'default' => 'DESC',
			),
			array(
				'id'          => 'alchemists__hero-posts-slider--categories',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Categories', 'alchemists' ),
				'subtitle'    => esc_html__( 'Select posts categories', 'alchemists' ),
				'desc'        => esc_html__( 'Show posts associated with certain categories.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select categories', 'alchemists' ),
				'data'        => 'categories',
			),
			array(
				'id'          => 'alchemists__hero-posts-slider--tags',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Tags', 'alchemists' ),
				'subtitle'    => esc_html__( 'Select posts tags', 'alchemists' ),
				'desc'        => esc_html__( 'Show posts associated with certain tags.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select tags', 'alchemists' ),
				'data'        => 'tags',
			),
			array(
				'id'       => 'alchemists__hero-posts-slider',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Posts', 'alchemists' ),
				'subtitle' => esc_html__( 'Specify posts to retrieve', 'alchemists' ),
				'desc'     => esc_html__( 'If selected all ordering options above will be ignored. Each selected post will be displayed as a separate slide in Hero Posts Slider if enabled.', 'alchemists' ),
				'data'     => 'posts',
				'sortable' => true,
				'placeholder' => esc_html__( 'Select posts', 'alchemists' ),
				'args' => array(
					'posts_per_page' => apply_filters( 'alchemists__hero-posts-slider__hook', 99 ),
				),
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--section-content-end',
				'type'     => 'section',
				'indent'   => false,
			),

			// Hero Post Slider -- Settings
			array(
				'id'       => 'alchemists__hero-posts-slider--section-settings-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Slider Settings', 'alchemists' ),
				'subtitle' => esc_html__( 'Setting up your Slider settings.', 'alchemists' ),
				'indent'   => true,
			),
			array(
				'id'        => 'alchemists__hero-posts-autoplay',
				'type'      => 'switch',
				'title'     => esc_html__( 'Autoplay', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables slider autoplay.', 'alchemists' ),
				'desc'      => esc_html__( 'Autoplay enabled by default.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__hero-posts-autoplay-speed',
				'type'     => 'slider',
				'title'    => esc_html__( 'Autoplay Speed', 'alchemists' ),
				'subtitle' => esc_html__( 'Delay duration between slide animation.', 'alchemists' ),
				'desc'     => esc_html__( 'Duration in seconds, Min: 3, max: 20, step: 0.5, default value: 8', 'alchemists' ),
				'default'  => 8,
				'min'      => 3,
				'step'     => 0.5,
				'max'      => 20,
				'resolution' => 0.1,
				'display_value' => 'text',
				'required'    => array(
					array('alchemists__hero-posts-autoplay', '=', '1'),
				),
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--nav',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Navigation Type', 'alchemists' ),
				'subtitle' => esc_html__( 'Select preferred navigation type', 'alchemists' ),
				'desc'     => esc_html__( 'Select slider navigation type.', 'alchemists' ),
				'options' => array(
					'thumbs'  => esc_html__( 'Thumbs', 'alchemists' ),
					'numbers' => esc_html__( 'Numbers', 'alchemists' ),
				 ),
				'default' => 'thumbs'
			),
			array(
				'id'             => 'alchemists__hero-posts-slider--nav-num',
				'type'           => 'select',
				'title'          => esc_html__( 'Number of Posts in Navigation', 'alchemists' ),
				'subtitle'       => esc_html__( 'Thumbs displayed at a time.', 'alchemists' ),
				'options'        => array(
					'2' => esc_html__( '2 posts', 'alchemists' ),
					'3' => esc_html__( '3 posts', 'alchemists' ),
					'4' => esc_html__( '4 posts', 'alchemists' ),
				),
				'default'        => '3',
				'desc'           => esc_html__( 'Number of (posts) displayed in the slider navigation.', 'alchemists' ),
				'required'    => array(
					array( 'alchemists__hero-posts-slider--nav', '=', 'thumbs' ),
				),
			),
			array(
				'id'       => 'alchemists__hero-posts-slider--section-settings-end',
				'type'     => 'section',
				'indent'   => false,
			),



			// Hero Post Slider -- Appearance
			array(
				'id'       => 'alchemists__hero-posts-slider--section-appearance-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Slider Appearance', 'alchemists' ),
				'subtitle' => esc_html__( 'Change your Slider appearance.', 'alchemists' ),
				'indent'   => true,
			),

			array(
				'id'        => 'alchemists__hero-posts-title',
				'type'      => 'switch',
				'title'     => esc_html__('Post Title', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Post Title Info.', 'alchemists'),
				'desc'      => esc_html__('The is the main text for the post.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Show', 'alchemists' ),
				'off'       => esc_html__( 'Hide', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__hero-posts-title-tag',
				'type'     => 'select',
				'title'    => esc_html__( 'Post Title Tag', 'alchemists' ),
				'subtitle' => esc_html__( 'Select preferred tag for the single post title.', 'alchemists' ),
				'options'  => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'p' => 'p',
				),
				'default'   => 'h1',
				'desc'      => esc_html__( 'This is a SEO option. Don\'t change it if you are not sure.', 'alchemists' ),
				'required'  => array( 'alchemists__hero-posts-title', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-meta',
				'type'      => 'switch',
				'title'     => esc_html__('Post Meta', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Posts Meta Info.', 'alchemists'),
				'desc'      => esc_html__('Includes date, views, likes, comments.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Show', 'alchemists' ),
				'off'       => esc_html__( 'Hide', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-author',
				'type'      => 'switch',
				'title'     => esc_html__('Post Author', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Post Author.', 'alchemists'),
				'desc'      => esc_html__('Includes avatar, author display name, author nickname.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Show', 'alchemists' ),
				'off'       => esc_html__( 'Hide', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-category',
				'type'      => 'switch',
				'title'     => esc_html__('Post Category', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Post Category.', 'alchemists'),
				'desc'      => esc_html__('Post Category displayed above the Post Title.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Show', 'alchemists' ),
				'off'       => esc_html__( 'Hide', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-thumb-category',
				'type'      => 'switch',
				'title'     => esc_html__('Post Category in Thumbs', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Category label in Thumbs.', 'alchemists'),
				'desc'      => esc_html__('Post Category label displayed above the Post Title in Thumbs controls.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Show', 'alchemists' ),
				'off'       => esc_html__( 'Hide', 'alchemists' ),
				'required'  => array( 'alchemists__hero-posts-slider--nav', '=', 'thumbs' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-link-slide',
				'type'      => 'switch',
				'title'     => esc_html__('Link Post Slide?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to link post slide.', 'alchemists'),
				'desc'      => esc_html__('By default only Post Title is linked to a post. If this option is enabled then the post slide will also be linked to the post.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-height',
				'type'      => 'switch',
				'title'     => esc_html__('Customize Height?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to to customize Posts Slider height.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__hero-posts-height-desktop',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Height - Desktop', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 720px', 'alchemists' ),
				),
				'required'       => array('alchemists__hero-posts-height', '=', '1'),
			),
			array(
				'id'             => 'alchemists__hero-posts-height-tablet-landscape',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Height - Tablet Landscape', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 640px', 'alchemists' ),
				),
				'required'       => array('alchemists__hero-posts-height', '=', '1'),
			),
			array(
				'id'             => 'alchemists__hero-posts-height-tablet-portrait',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Height - Tablet Portrait', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 480px', 'alchemists' ),
				),
				'required'       => array('alchemists__hero-posts-height', '=', '1'),
			),
			array(
				'id'             => 'alchemists__hero-posts-height-mobile',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Height - Mobile', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set your custom height.', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
				'hint'        => array(
					'content'   => esc_html__( 'Default is 320px', 'alchemists' ),
				),
				'required'       => array('alchemists__hero-posts-height', '=', '1'),
			),


			// Overlay
			array(
				'id'       => 'alchemists__hero-posts-slider--overlay',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Overlay', 'alchemists' ),
				'subtitle' => esc_html__( 'Overlay added to slider posts by default.', 'alchemists' ),
				'desc'     => esc_html__( 'Change the type of overlay or disable it.', 'alchemists' ),
				'options' => array(
					'simple'  => esc_html__( 'Simple', 'alchemists' ),
					'duotone' => esc_html__( 'Duotone', 'alchemists' ),
					'off'     => esc_html__( 'Off', 'alchemists' ),
				 ),
				'default' => 'simple'
			),
			array(
				'id'        => 'alchemists__hero-posts-slider--overlay-color',
				'type'      => 'switch',
				'title'     => esc_html__( 'Customize Overlay Color?', 'alchemists' ),
				'subtitle'  => esc_html__( 'Turn on to to customize Posts Overlay Color.', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array( 'alchemists__hero-posts-slider--overlay', '=', 'simple' ),
			),
			array(
				'id'          => 'alchemists__hero-posts-slider--overlay-color-customize',
				'type'        => 'color_rgba',
				'title'       => esc_html__( 'Overlay Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick color for overlay.', 'alchemists' ),
				'default'     => array(
					'color'     => '#000',
					'alpha'     => 0.6
				),
				'transparent' => false,
				'required'    => array( 'alchemists__hero-posts-slider--overlay-color', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__hero-posts-slider--featured-image',
				'type'      => 'switch',
				'title'     => esc_html__('Add Hero Image?', 'alchemists'),
				'subtitle'  => esc_html__('Adds front image to the first slide.', 'alchemists'),
				'desc'      => esc_html__('Hero image will be added to the first slide.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array( 'alchemists__hero-posts-slider--nav', '=', 'numbers' ),
			),
			array(
				'id'             => 'alchemists__hero-posts-slider--featured-image-upload',
				'type'           => 'media',
				'url'            => true,
				'title'          => esc_html__( 'Custom Hero Image', 'alchemists' ),
				'compiler'       => true,
				'desc'           => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
				'required'       => array('alchemists__hero-posts-slider--featured-image', '=', '1'),
			),
			array(
				'id'                    => 'alchemists__hero-posts-slider--image-position',
				'type'                  => 'background',
				'output'                => array('.hero-slider__item-image'),
				'title'                 => esc_html__( 'Background Image Position', 'alchemists' ),
				'subtitle'              => esc_html__( 'Specify the background image position', 'alchemists' ),
				'desc'                  => esc_html__( 'The slide images are centered by default. Customize the position of the slide images similar to the background position.', 'alchemists' ),
				'transparent'           => false,
				'background-color'      => false,
				'background-repeat'     => false,
				'background-attachment' => false,
				'background-image'      => false,
				'background-clip'       => false,
				'background-origin'     => false,
				'background-size'       => false,
				'preview'               => false,
			),

			array(
				'id'       => 'alchemists__hero-posts-slider--section-appearance-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );


	// Blog & Posts
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__('Blog & Posts', 'alchemists'),
		'icon'      => 'el-icon-cogs',
		'id'        => 'alchemists__section-blog-post',
		'fields'    => array(
			array(
				'id'       => 'alchemists__opt-blog-title',
				'type'     => 'text',
				'title'    => esc_html__( 'Blog Title', 'alchemists' ),
				'subtitle' => esc_html__( 'Enter your Blog Title', 'alchemists' ),
				'desc'     => esc_html__( 'This option take effect only if Settings > Reading > Your homepage displays set to Your latest posts. Otherwise, you can overwrite the blog title in Pages > Blog', 'alchemists' ),
				'default'  => esc_html__( 'Blog', 'alchemists' ),
			),
 			array(
				'id'        => 'alchemists__opt-blog-filter',
				'type'      => 'switch',
				'title'     => esc_html__('Enable Posts Filter?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show Posts Filter.', 'alchemists'),
				'desc'      => esc_html__('Posts Filter displayed by default.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__blog-filter-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Posts Filter Type', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Posts Filter Type', 'alchemists' ),
				'desc'      => esc_html__( 'Posts Filter used on Blog pages.', 'alchemists'),
				'options'   => array(
					'fullwidth' => esc_html__( 'Full Width', 'alchemists' ),
					'boxed'     => esc_html__( 'Boxed', 'alchemists' ),
				),
				'default'   => 'fullwidth',
				'required'  => array( 'alchemists__opt-blog-filter', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__posts-filter-sorter',
				'type'      => 'sorter',
				'title'     => esc_html__( 'Posts Filter', 'alchemists' ),
				'subtitle'  => esc_html__( 'Elements order in the filter.', 'alchemists' ),
				'desc'      => esc_html__( 'Organize how you want the Posts Filters element to appear on a blog page.', 'alchemists' ),
				'compiler'  => 'true',
				'options'   => array(
					'enabled'   => array(
						'filter__category' => esc_html__( 'Categories', 'alchemists' ),
						'filter__orderby'  => esc_html__( 'Order By', 'alchemists' ),
						'filter__order'    => esc_html__( 'Order', 'alchemists' ),
						'filter__author'   => esc_html__( 'Author', 'alchemists' ),
					),
					'disabled'  => array(),
				),
				'required' => array( 'alchemists__opt-blog-filter', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__blog-posts-style',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Blog Style', 'alchemists'),
				'subtitle'  => esc_html__('Posts style displayed on Blog page.', 'alchemists'),
				'desc'      => esc_html__('Select preferred Blog style.', 'alchemists'),
				'class'     => 'alchemists-field--blog-style',
				'options'   => array(
					'1' => array(
						'alt' => esc_html__('Cards (fitrows)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-1.png'
					),
					'2' => array(
						'alt' => esc_html__('List (Left Thumbnail)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-2.png'
					),
					'3' => array(
						'alt' => esc_html__('List (Large Thumbnail)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-3.png'
					),
					'4' => array(
						'alt' => esc_html__('Masonry', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-4.png'
					),
					'5' => array(
						'alt' => esc_html__('Cards (tile)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-1.png'
					),
					'6' => array(
						'alt' => esc_html__('Cards (tile)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-6.png'
					),
					'7' => array(
						'alt' => esc_html__('List (Large Thumbnail)', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-7.png'
					),
					'8' => array(
						'alt' => esc_html__('Masonry', 'alchemists'),
						'img' => get_template_directory_uri() . '/admin/images/blog-8.png'
					),
				),
				'default'   => '2'
			),
			array(
				'id'        => 'alchemists__opt-social-counters',
				'type'      => 'switch',
				'title'     => esc_html__('Add Social Blocks between Posts?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to add Social Block.', 'alchemists'),
				'desc'      => esc_html__('Social Blocks displayed for Masontry Blog Style.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required' => array(
					array('alchemists__blog-posts-style', 'not', '1'),
					array('alchemists__blog-posts-style', 'not', '2'),
					array('alchemists__blog-posts-style', 'not', '3'),
					array('alchemists__blog-posts-style', 'not', '5'),
					array('alchemists__blog-posts-style', 'not', '6'),
					array('alchemists__blog-posts-style', 'not', '7'),
				),
			),
			array(
				'id'        => 'alchemists__opt-social-counters-fb',
				'type'      => 'text',
				'validate'  => 'numeric',
				'title'     => esc_html__( 'Facebook Block Position', 'alchemists' ),
				'desc'      => esc_html__( 'Number of post where Facebook Block will be displayed. Enter 0 to not display block.', 'alchemists' ),
				'default'   => 2,
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-social-counters-twitter',
				'type'      => 'text',
				'validate'  => 'numeric',
				'title'     => esc_html__( 'Twitter Block Position', 'alchemists' ),
				'desc'      => esc_html__( 'Number of post where Twitter Block will be displayed. Enter 0 to not display block.', 'alchemists' ),
				'default'   => 4,
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-social-counters-instagram',
				'type'      => 'text',
				'validate'  => 'numeric',
				'title'     => esc_html__( 'Instagram Block Position', 'alchemists' ),
				'desc'      => esc_html__( 'Number of post where Instagram Block will be displayed. Enter 0 to not display block.', 'alchemists' ),
				'default'   => 8,
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-social-counters-twitch',
				'type'      => 'text',
				'validate'  => 'numeric',
				'title'     => esc_html__( 'Twitch Block Position', 'alchemists' ),
				'desc'      => esc_html__( 'Number of post where Twitch Block will be displayed. Enter 0 to not display block.', 'alchemists' ),
				'default'   => 0,
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-social-counters-youtube',
				'type'      => 'text',
				'validate'  => 'numeric',
				'title'     => esc_html__( 'YouTube Block Position', 'alchemists' ),
				'desc'      => esc_html__( 'Number of post where YouTube Block will be displayed. Enter 0 to not display block.', 'alchemists' ),
				'default'   => 0,
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-social-fb-url',
				'type'     => 'text',
				'title'    => esc_html__( 'Facebook URL', 'alchemists' ),
				'desc'     => esc_html__( 'Enter Facebook Page URL.', 'alchemists' ),
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-social-tw-url',
				'type'     => 'text',
				'title'    => esc_html__( 'Twitter URL', 'alchemists' ),
				'desc'     => esc_html__( 'Enter Twitter URL.', 'alchemists' ),
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-social-insta-url',
				'type'     => 'text',
				'title'    => esc_html__( 'Instagram URL', 'alchemists' ),
				'desc'     => esc_html__( 'Enter Instagram URL.', 'alchemists' ),
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-social-twitch-url',
				'type'     => 'text',
				'title'    => esc_html__( 'Twitch URL', 'alchemists' ),
				'desc'     => esc_html__( 'Enter Twitch URL.', 'alchemists' ),
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-social-youtube-url',
				'type'     => 'text',
				'title'    => esc_html__( 'YouTube URL', 'alchemists' ),
				'desc'     => esc_html__( 'Enter YouTube URL.', 'alchemists' ),
				'required' => array('alchemists__opt-social-counters', '=', '1'),
			),
			array(
				'id'        => 'alchemists__blog-post-author',
				'type'      => 'switch',
				'title'     => esc_html__( 'Post Author', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables Post Author for Blog posts.', 'alchemists' ),
				'desc'      => esc_html__( 'If the option is enabled, Post Author is displayed on posts.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__blog-post-likes',
				'type'      => 'switch',
				'title'     => esc_html__( 'Post Likes', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables Likes for Blog posts.', 'alchemists' ),
				'desc'      => esc_html__( 'If the option is enabled, Blogs posts will have Likes displayed on each post.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__blog-post-views',
				'type'      => 'switch',
				'title'     => esc_html__( 'Post Views', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables Views count for Blog posts.', 'alchemists' ),
				'desc'      => esc_html__( 'If the option is enabled, Blogs posts views are counted and Views counter displayed on each post.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__blog-post-comments',
				'type'      => 'switch',
				'title'     => esc_html__( 'Post Comments Count', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables Comments count count for Blog posts.', 'alchemists' ),
				'desc'      => esc_html__( 'If the option is enabled, Comments counter is displayed on each post.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__blog-post-og',
				'type'      => 'switch',
				'title'     => esc_html__( 'Open Graph', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables OG for Blog posts.', 'alchemists' ),
				'desc'      => esc_html__( 'The Open Graph protocol enables blog post to become a rich object in a social graph.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
			),
		)
	) );

	// Posts
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Posts', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-posts',
		'fields'     => array(
			array(
				'id'        => 'alchemists__blog-sidebar',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Blog Sidebar Position', 'alchemists'),
				'subtitle'  => esc_html__('Used on Blog pages only.', 'alchemists'),
				'desc'      => esc_html__('Select sidebar alignment for Blog page.', 'alchemists'),
				'options'   => array(
					'1' => array(
						'alt' => esc_html__('Right Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/2cr.png'
					),
					'2' => array(
						'alt' => esc_html__('Left Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/2cl.png'
					),
					'3' => array(
						'alt' => esc_html__('No Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/1c.png'
					),
				),
				'default'   => '1'
			),
			array(
				'id'             => 'alchemists__posts-categories',
				'type'           => 'switch',
				'title'          => esc_html__('Show Categories?', 'alchemists'),
				'subtitle'       => esc_html__('Posts categories displayed by default.', 'alchemists'),
				'default'        => 1,
				'on'             => esc_html__( 'Yes', 'alchemists' ),
				'off'            => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__categories-group-1',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 1', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 1', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 1.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-2',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 2', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 2', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 3.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-3',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 3', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 3', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 3.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-4',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 4', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 4', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 4.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-5',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 5', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 5', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 5.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-6',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 6', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 6', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 6.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-7',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 7', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 7', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 7.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-8',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 8', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 8', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 8.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-9',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 9', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 9', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 9.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-10',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 10', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 10', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 10.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-11',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 11', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 11', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 11.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
			array(
				'id'       => 'alchemists__categories-group-12',
				'type'     => 'select',
				'multi'    => true,
				'title'    => esc_html__( 'Categories Group 12', 'alchemists' ),
				'subtitle' => esc_html__( 'Select categories for Group 12', 'alchemists' ),
				'desc'     => esc_html__( 'Assign post categories to Group 12.', 'alchemists' ),
				'data'     => 'categories',
				'required' => array('alchemists__posts-categories', '=', '1'),
			),
		)
	) );


	// Single Post
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Single Post', 'alchemists' ),
		'id'         => 'alchemists__subsection-single-post',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'        => 'alchemists__single-post-sidebar',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Post Sidebar Position', 'alchemists'),
				'subtitle'  => esc_html__('Used on Single Posts only.', 'alchemists'),
				'desc'      => esc_html__('Select sidebar alignment for single posts. Note: not affected on some layouts designed without sidebar, e.g. Post Layout 3', 'alchemists'),
				'options'   => array(
					'1' => array(
						'alt' => esc_html__('Right Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/2cr.png'
					),
					'2' => array(
						'alt' => esc_html__('Left Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/2cl.png'
					),
					'3' => array(
						'alt' => esc_html__('No Sidebar', 'alchemists'),
						'img' => ReduxFramework::$_url . 'assets/img/1c.png'
					),
				),
				'default'   => '1'
			),
			array(
				'id'        => 'alchemists__opt-single-post-layout',
				'type'      => 'select',
				'title'     => esc_html__('Single Post Layout', 'alchemists'),
				'subtitle'  => esc_html__('Select Single Post Layout', 'alchemists'),
				'options'   => array(
					'1' => esc_html__( 'Layout 1', 'alchemists' ),
					'2' => esc_html__( 'Layout 2', 'alchemists' ),
					'3' => esc_html__( 'Layout 3', 'alchemists' ),
					'4' => esc_html__( 'Layout 4', 'alchemists' ),
					'5' => esc_html__( 'Layout 5', 'alchemists' ),
					'6' => esc_html__( 'Layout 6', 'alchemists' ),
					'7' => esc_html__( 'Layout 7', 'alchemists' ),
				),
				'default'   => '1'
			),
			array(
				'id'             => 'alchemists__opt-single-post-title-tag',
				'type'           => 'select',
				'title'          => esc_html__('Post Title Tag', 'alchemists'),
				'subtitle'       => esc_html__('Select preferred tag for the single post title.', 'alchemists'),
				'options'        => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'p' => 'p',
				),
				'default'        => 'h1',
				'desc'           => esc_html__( 'This is a SEO option. Don\'t change it if you are not sure.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__hero-single-post--overlay',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Featured Image Overlay', 'alchemists' ),
				'subtitle' => esc_html__( 'Overlay added to the featured image by default.', 'alchemists' ),
				'desc'     => esc_html__( 'Change the type of overlay or disable it.', 'alchemists' ),
				'options' => array(
					'simple'  => esc_html__( 'Simple', 'alchemists' ),
					'duotone' => esc_html__( 'Duotone', 'alchemists' ),
					'off'     => esc_html__( 'Off', 'alchemists' ),
				 ),
				'default' => 'simple',
				'required'    => array( 'alchemists__opt-single-post-layout', '=', '3' ),
			),
			array(
				'id'        => 'alchemists__hero-single-post--overlay-color',
				'type'      => 'switch',
				'title'     => esc_html__( 'Customize Overlay Color?', 'alchemists' ),
				'subtitle'  => esc_html__( 'Turn on to to customize Overlay Color.', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array( 'alchemists__hero-single-post--overlay', '=', 'simple' ),
			),
			array(
				'id'          => 'alchemists__hero-single-post--overlay-color-customize',
				'type'        => 'color_rgba',
				'title'       => esc_html__( 'Overlay Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick color for overlay.', 'alchemists' ),
				'default'     => array(
					'color'     => '#000',
					'alpha'     => 0.6
				),
				'transparent' => false,
				'required'    => array( 'alchemists__hero-single-post--overlay-color', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-featured-img',
				'type'      => 'switch',
				'title'     => esc_html__('Show Featured Image?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show single featured image.', 'alchemists'),
				'desc'      => esc_html__('Post Featured Image displayed by default on Single Post.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-tags',
				'type'      => 'switch',
				'title'     => esc_html__('Show Post Tags?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show post tags.', 'alchemists'),
				'desc'      => esc_html__('Post Tags displayed by default.', 'alchemists'),
				'default'   => 1,
				'off'       => esc_html__( 'No', 'alchemists' ),
				'on'        => esc_html__( 'Yes', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-tags-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Post Tags Type', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Post Tags type', 'alchemists' ),
				'options'   => array(
					'buttons'  => esc_html__( 'Buttons', 'alchemists' ),
					'hashtags' => esc_html__( 'Hashtags', 'alchemists' ),
				),
				'default'   => 'buttons',
				'required'  => array('alchemists__opt-single-post-tags', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-single-post-social',
				'type'      => 'switch',
				'title'     => esc_html__('Show Social Sharing?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show social sharing buttons.', 'alchemists'),
				'desc'      => esc_html__('Social sharing buttons displayed by default.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-social-sorter',
				'type'      => 'sorter',
				'title'     => esc_html__( 'Social Sharing Sorter', 'alchemists' ),
				'subtitle'  => esc_html__( 'Organize social sharing link.', 'alchemists' ),
				'desc'      => esc_html__( 'Drag and drop social sharing links from "Disabled" to "Enabled" group to display them on Single Post Page.', 'alchemists' ),
				'compiler'  => 'true',
				'options'   => array(
					'enabled'   => array(
						'social_facebook'    => esc_html__( 'Facebook', 'alchemists' ),
						'social_twitter'     => esc_html__( 'Twitter', 'alchemists' ),
					),
					'disabled'  => array(
						'social_linkedin'    => esc_html__( 'Linkedin', 'alchemists' ),
						'social_vk'          => esc_html__( 'VK', 'alchemists' ),
						'social_ok'          => esc_html__( 'Odnoklassniki', 'alchemists' ),
						'social_whatsapp'    => esc_html__( 'WhatsApp', 'alchemists' ),
						'social_viber'       => esc_html__( 'Viber', 'alchemists' ),
						'social_telegram'    => esc_html__( 'Telegram', 'alchemists' ),
					),
				),
				'required'  => array('alchemists__opt-single-post-social', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-single-post-author',
				'type'      => 'switch',
				'title'     => esc_html__('Show Post Author Box on Single Post?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show post author box.', 'alchemists'),
				'desc'      => esc_html__('Post Author Box contains name, email, avatar and description.', 'alchemists'),
				'default'   => 1,
				'off'       => esc_html__( 'No', 'alchemists' ),
				'on'        => esc_html__( 'Yes', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__single-post-author-tag',
				'type'     => 'select',
				'title'    => esc_html__( 'Author Title Tag', 'alchemists' ),
				'subtitle' => esc_html__( 'Select preferred tag for the author title.', 'alchemists' ),
				'options'  => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'p' => 'p',
				),
				'default'   => 'h4',
				'desc'      => esc_html__( 'This is a SEO option. Don\'t change it if you are not sure.', 'alchemists' ),
				'required'  => array( 'alchemists__opt-single-post-author', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-author-email',
				'type'      => 'switch',
				'title'     => esc_html__('Show Post Author Email?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show post author email.', 'alchemists'),
				'desc'      => esc_html__('Post author email displayed in the author box as an icon.', 'alchemists'),
				'default'   => 1,
				'off'       => esc_html__( 'No', 'alchemists' ),
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'required'  => array('alchemists__opt-single-post-author', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-single-post-author-site',
				'type'      => 'switch',
				'title'     => esc_html__('Show Post Author Site Link?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show post author site.', 'alchemists'),
				'desc'      => esc_html__('Post author site link displayed in the author box as an icon.', 'alchemists'),
				'default'   => 1,
				'off'       => esc_html__( 'No', 'alchemists' ),
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'required'  => array('alchemists__opt-single-post-author', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-single-post-navigation',
				'type'      => 'switch',
				'title'     => esc_html__('Show Post Navigation?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to show post navigation.', 'alchemists'),
				'desc'      => esc_html__('Post Navigation contains previous and next posts.', 'alchemists'),
				'default'   => 1,
				'off'       => esc_html__( 'No', 'alchemists' ),
				'on'        => esc_html__( 'Yes', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-single-post-navigation-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Post Navigation Type', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Post Navigation type', 'alchemists' ),
				'options'   => array(
					'default' => esc_html__( 'Default', 'alchemists' ),
					'card-sm' => esc_html__( 'Card Small', 'alchemists' ),
					'card-n'  => esc_html__( 'Card Default', 'alchemists' ),
					'card-lg'  => esc_html__( 'Card Large', 'alchemists' ),
				),
				'default'   => 'default',
				'required'  => array('alchemists__opt-single-post-navigation', '=', '1'),
			),
		)
	) );

	// Content Settings
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Content', 'alchemists' ),
		'id'     => 'alchemists__section-content',
		'icon'   => 'el el-indent-left',
		'fields' => array(
			array(
				'id'          => 'alchemists__opt-content-padding-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Content Paddings?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize the content paddings.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'             => 'alchemists__opt-content-padding-desktop',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Content Paddings - Desktop', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Desktop.', 'alchemists'),
				'desc'           => esc_html__('You can set paddings for the Content section. Applied only for desktops.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '60px',
					'padding-bottom'  => '60px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-content-padding-on', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-content-padding-tablet',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Content Paddings - Tablet', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Tablets.', 'alchemists'),
				'desc'           => esc_html__('You can set paddings for the Content section. Applied only for tablets.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '60px',
					'padding-bottom'  => '60px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-content-padding-on', '=', '1'),
			),
			array(
				'id'             => 'alchemists__opt-content-padding-mobile',
				'type'           => 'spacing',
				'mode'           => 'padding',
				'units'          => array('px'),
				'units_extended' => 'false',
				'left'           => false,
				'right'          => false,
				'title'          => esc_html__('Content Paddings - Mobile', 'alchemists'),
				'subtitle'       => esc_html__('Set paddings for Mobile devices.', 'alchemists'),
				'desc'           => esc_html__('You can set paddings for the Content section. Applied only for mobile devices.', 'alchemists'),
				'default'            => array(
					'padding-top'     => '30px',
					'padding-bottom'  => '30px',
					'units'           => 'px',
				),
				'required'  => array('alchemists__opt-content-padding-on', '=', '1'),
			),
		)
	) );


	// Footer Settings
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Footer', 'alchemists' ),
		'id'     => 'alchemists__section-footer',
		'icon'   => 'el el-arrow-down',
		'fields' => array(

		)
	) );

	/*
	* Add different Footer Options depends on sports version
	*/
	if ( alchemists_sp_preset('football')) {

		// Footer Primary (American Football)
		Redux::setSection( $opt_name, array(
			'title'      => esc_html__( 'Primary', 'alchemists' ),
			'id'         => 'alchemists__subsection-footer-primary',
			'subsection' => true,
			'fields'     => array(
				array(
					'id'        => 'alchemists__footer-primary',
					'type'      => 'switch',
					'title'     => esc_html__('Display Footer Primary?', 'alchemists'),
					'subtitle'  => esc_html__('Turn on to show the Footer Primary.', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__( 'Yes', 'alchemists' ),
					'off'       => esc_html__( 'No', 'alchemists' ),
				),
				array(
					'id'        => 'alchemists__opt-footer-logo',
					'type'      => 'switch',
					'title'     => esc_html__('Display Footer Logo?', 'alchemists'),
					'subtitle'  => esc_html__('Turn on to show the Footer Logo.', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__( 'Yes', 'alchemists' ),
					'off'       => esc_html__( 'No', 'alchemists' ),
					'required'  => array('alchemists__footer-primary', '=', '1'),
				),
				array(
					'id'        => 'alchemists__opt-logo-footer-standard',
					'type'      => 'media',
					'url'       => true,
					'readonly'  => false,
					'title'     => esc_html__('Footer Custom Logo', 'alchemists'),
					'compiler'  => 'true',
					'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
					'required'  => array(
						array('alchemists__opt-footer-logo', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'        => 'alchemists__opt-logo-footer-retina',
					'type'      => 'media',
					'url'       => true,
					'readonly'  => false,
					'title'     => esc_html__('Footer Custom Logo @2x', 'alchemists'),
					'compiler'  => 'true',
					'desc'      => esc_html__('Upload your image for retina displays or specify the image URL. It should be x2 time bigger than standard logo image.', 'alchemists'),
					'required'  => array(
						array('alchemists__opt-footer-logo', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'       => 'alchemists__opt-logo-footer-title',
					'type'     => 'text',
					'title'    => esc_html__( 'Footer Site Title', 'alchemists' ),
					'subtitle' => esc_html__( 'Replaces the default site name in the Footer.', 'alchemists' ),
					'desc'     => esc_html__( 'Displayed if applicable.', 'alchemists' ),
					'default'  => '',
					'required' => array( 'alchemists__opt-footer-logo', '=', '1' ),
				),
				array(
					'id'       => 'alchemists__opt-logo-footer-tagline',
					'type'     => 'text',
					'title'    => esc_html__( 'Footer Site Tagline', 'alchemists' ),
					'subtitle' => esc_html__( 'Displayed under the Footer Site Tagline.', 'alchemists' ),
					'desc'     => esc_html__( 'Displayed if applicable.', 'alchemists' ),
					'default'  => '',
					'required' => array( 'alchemists__opt-footer-logo', '=', '1' ),
				),
				// Email - Primary
				array(
					'id'        => 'alchemists__footer-primary-info-1',
					'type'      => 'switch',
					'title'     => esc_html__('Show Primary Email?', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__('Yes', 'alchemists'),
					'off'       => esc_html__('No', 'alchemists'),
					'required'  => array('alchemists__footer-primary', '=', '1'),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-1-label',
					'type'      => 'text',
					'title'     => esc_html__( 'Label for Primary Email Address', 'alchemists' ),
					'default'   => esc_html__( 'Join Our Team!', 'alchemists' ),
					'required'  => array(
						array('alchemists__footer-primary-info-1', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-1-email',
					'type'      => 'text',
					'title'     => esc_html__('Primary Email address', 'alchemists'),
					'default'   => 'tryouts@alchemists.com',
					'required'  => array('alchemists__footer-primary-info-1', '=', '1'),
					'desc'      => esc_html__( 'As an option you can also enter a simple link, e.g. yoursite.com', 'alchemists'),
					'required'  => array('alchemists__footer-primary', '=', '1'),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-1-icon-custom',
					'type'      => 'text',
					'title'     => esc_html__('Primary Email Custom Icon', 'alchemists'),
					'desc'      => __( 'Add your custom icon, e.g. <code>&lt;i class="fa fa-user"&gt;&lt;/i&gt;</code> or <code>&lt;img src="PATH_TO_IMAGE" /&gt;</code>', 'alchemists'),
					'default'   => '',
					'required'  => array(
						array('alchemists__footer-primary-info-1', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),

				// Email - Secondary
				array(
					'id'        => 'alchemists__footer-primary-info-2',
					'type'      => 'switch',
					'title'     => esc_html__('Show Secondary Email?', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__('Yes', 'alchemists'),
					'off'       => esc_html__('No', 'alchemists'),
					'required'  => array('alchemists__footer-primary', '=', '1'),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-2-label',
					'type'      => 'text',
					'title'     => esc_html__( 'Label for Secondary Email address', 'alchemists' ),
					'default'   => esc_html__( 'Contact Us', 'alchemists' ),
					'required'  => array(
						array('alchemists__footer-primary-info-2', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-2-email',
					'type'      => 'text',
					'title'     => esc_html__('Secondary Email address', 'alchemists'),
					'default'   => 'info@alchemists.com',
					'desc'      => esc_html__( 'As an option you can also enter a simple link, e.g. yoursite.com', 'alchemists'),
					'required'  => array(
						array('alchemists__footer-primary-info-2', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'        => 'alchemists__footer-primary-info-2-icon-custom',
					'type'      => 'text',
					'title'     => esc_html__('Secondary Email Custom Icon', 'alchemists'),
					'desc'      => __( 'Add your custom icon, e.g. <code>&lt;i class="fa fa-user"&gt;&lt;/i&gt;</code> or <code>&lt;img src="PATH_TO_IMAGE" /&gt;</code>', 'alchemists'),
					'default'   => '',
					'required'  => array(
						array('alchemists__footer-primary-info-2', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
				array(
					'id'        => 'alchemists__footer-primary-social',
					'type'      => 'switch',
					'title'     => esc_html__('Show Social Links?', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__('Yes', 'alchemists'),
					'off'       => esc_html__('No', 'alchemists'),
					'required'  => array('alchemists__footer-primary', '=', '1'),
				),
				array(
					'id'       => 'alchemists__footer-primary-social-links',
					'type'     => 'sortable',
					'title'    => esc_html__( 'Social Media Links', 'alchemists' ),
					'subtitle' => esc_html__( 'Define and reorder these links however you want.', 'alchemists' ),
					'desc'     => esc_html__( 'Leave empty a field if you don\'t want to display particular social media link.', 'alchemists' ),
					'label'    => true,
					'mode'     => 'text',
					'options'  => array(
						'Facebook' => '',
						'Twitter'   => '',
						'LinkedIn' => '',
						'Instagram' => '',
						'Github' => '',
						'VK' => '',
						'YouTube' => '',
						'Pinterest' => '',
						'Tumblr' => '',
						'Dribbble' => '',
						'Vimeo' => '',
						'Flickr' => '',
						'Yelp' => '',
						'Telegram' => '',
						'Snapchat' => '',
						'Twitch' => '',
						'Faceit' => '',
						'Steam' => '',
						'Discord' => '',
						'Mixer' => '',
						'TikTok' => '',
						'Email' => '',
						'RSS' => '',
					),
					'default'  => array(
						'Facebook'   => '#',
						'Twitter'   => '#',
						'LinkedIn' => '',
						'Instagram' => '#',
						'Github' => '',
						'VK' => '',
						'YouTube' => '',
						'Pinterest' => '',
						'Tumblr' => '',
						'Dribbble' => '',
						'Vimeo' => '',
						'Flickr' => '',
						'Yelp' => '',
						'Telegram' => '',
						'Snapchat' => '',
						'Twitch' => '',
						'Faceit' => '',
						'Steam' => '',
						'Discord' => '',
						'Mixer' => '',
						'TikTok' => '',
						'Email' => '',
						'RSS' => '',
					),
					'required'  => array(
						array('alchemists__footer-primary-social', '=', '1'),
						array('alchemists__footer-primary', '=', '1'),
					),
				),
			)
		) );

	} else {

		// Footer Primary (Basketball & Soccer)
		Redux::setSection( $opt_name, array(
			'title'      => esc_html__( 'Primary', 'alchemists' ),
			'id'         => 'alchemists__subsection-footer-primary',
			'subsection' => true,
			'fields'     => array(
				array(
					'id'        => 'alchemists__opt-footer-logo',
					'type'      => 'switch',
					'title'     => esc_html__('Display Footer Logo?', 'alchemists'),
					'subtitle'  => esc_html__('Turn on to show the Footer Logo.', 'alchemists'),
					'default'   => 1,
					'on'        => esc_html__( 'Yes', 'alchemists' ),
					'off'       => esc_html__( 'No', 'alchemists' ),
				),
				array(
					'id'        => 'alchemists__opt-logo-footer-standard',
					'type'      => 'media',
					'url'       => true,
					'readonly'  => false,
					'title'     => esc_html__('Footer Custom Logo', 'alchemists'),
					'compiler'  => 'true',
					'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
					'required'    => array('alchemists__opt-footer-logo', '=', '1'),
				),
				array(
					'id'        => 'alchemists__opt-logo-footer-retina',
					'type'      => 'media',
					'url'       => true,
					'readonly'  => false,
					'title'     => esc_html__('Footer Custom Logo @2x', 'alchemists'),
					'compiler'  => 'true',
					'desc'      => esc_html__('Upload your image for retina displays or specify the image URL. It should be x2 time bigger than standard logo image.', 'alchemists'),
					'required'    => array('alchemists__opt-footer-logo', '=', '1'),
				),
				array(
					'id'       => 'alchemists__opt-logo-footer-title',
					'type'     => 'text',
					'title'    => esc_html__( 'Footer Site Title', 'alchemists' ),
					'subtitle' => esc_html__( 'Replaces the default site name in Footer.', 'alchemists' ),
					'desc'     => esc_html__( 'Displayed if applicable.', 'alchemists' ),
					'default'  => '',
					'required' => array( 'alchemists__opt-footer-logo', '=', '1' ),
				),
				array(
					'id'       => 'alchemists__opt-logo-footer-tagline',
					'type'     => 'text',
					'title'    => esc_html__( 'Footer Site Tagline', 'alchemists' ),
					'subtitle' => esc_html__( 'Displayed under the Footer Site Tagline.', 'alchemists' ),
					'desc'     => esc_html__( 'Displayed if applicable.', 'alchemists' ),
					'default'  => '',
					'required' => array( 'alchemists__opt-footer-logo', '=', '1' ),
				),
			)
		) );
	}

	// Footer Widgets
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Widgets', 'alchemists' ),
		'id'         => 'alchemists__subsection-footer-widgets',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'        => 'alchemists__opt-footer-widgets',
				'type'      => 'switch',
				'title'     => esc_html__('Footer Widgets', 'alchemists'),
				'subtitle'  => esc_html__('Footer Widgets are displayed by default.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Enable', 'alchemists' ),
				'off'       => esc_html__( 'Disable', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-footer-widgets-layout',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Footer Widgets Layout', 'alchemists'),
				'subtitle'  => esc_html__('Select footer widgets layout (not equal or equal).', 'alchemists'),
				'options'   => array(
					'1' => array(
						'alt' => esc_html__( '3 Columns (equal)', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/footer-cols-3.png'
					),
					'2' => array(
						'alt' => esc_html__( '4 Columns (equal)', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/footer-cols-4.png'
					),
				),
				'default'   => 1,
				'required'  => array('alchemists__opt-footer-widgets', '=', '1'),
			),
		)
	) );


	// Footer Options: Secondary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Secondary', 'alchemists' ),
		'id'         => 'alchemists__subsection-footer-secondary',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'        => 'alchemists__opt-secondary',
				'type'      => 'switch',
				'title'     => esc_html__( 'Show Secondary Footer?', 'alchemists' ),
				'subtitle'  => esc_html__( 'Turn on to show Footer Secondary Area', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__footer-secondary-copyright-layout',
				'type'      => 'select',
				'title'     => esc_html__( 'Copyright Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Copyright layout', 'alchemists' ),
				'options'   => array(
					'default'     => esc_html__( 'Text and Navigation', 'alchemists' ),
					'text'        => esc_html__( 'Text', 'alchemists' ),
					'nav'         => esc_html__( 'Navigation', 'alchemists' ),
					'social'      => esc_html__( 'Social Links', 'alchemists' ),
					'text_social' => esc_html__( 'Text and Social Links', 'alchemists' ),
				),
				'default'   => 'default',
				'required'  => array('alchemists__opt-secondary', '=', '1'),
			),
			array(
				'id'        => 'alchemists__footer-secondary-copyright',
				'type'      => 'editor',
				'title'     => esc_html__( 'Copyright Text', 'alchemists' ),
				'subtitle'  => esc_html__( 'Add copyright text here.', 'alchemists' ),
				'default'   => '<a href="//bit.ly/alch-wp">The Alchemists</a> 2021 &nbsp; | &nbsp; All Rights Reserved',
				'compiler'  => true,
				'args'      => array(
					'teeny'         => true,
					'media_buttons' => false,
					'quicktags'     => true,
					'textarea_rows' => 2,
				),
				'required'  => array( 'alchemists__footer-secondary-copyright-layout', '=', array( 'default', 'text', 'text_social' ) ),
				'hint'        => array(
					'content'   => esc_html__( 'Use [year] shortcode if you want to display the current year.', 'alchemists' ),
				),
			),
			array(
				'id'       => 'alchemists__footer-secondary-social-links',
				'type'     => 'sortable',
				'title'    => esc_html__( 'Social Media Links', 'alchemists' ),
				'subtitle' => esc_html__( 'Define and reorder these links however you want.', 'alchemists' ),
				'desc'     => esc_html__( 'Leave empty a field if you don\'t want to display particular social media link.', 'alchemists' ),
				'label'    => true,
				'mode'     => 'text',
				'options'  => array(
					'Facebook' => '',
					'Twitter'   => '',
					'LinkedIn' => '',
					'Instagram' => '',
					'Github' => '',
					'VK' => '',
					'YouTube' => '',
					'Pinterest' => '',
					'Tumblr' => '',
					'Dribbble' => '',
					'Vimeo' => '',
					'Flickr' => '',
					'Yelp' => '',
					'Telegram' => '',
					'Snapchat' => '',
					'Twitch' => '',
					'Faceit' => '',
					'Steam' => '',
					'Discord' => '',
					'Mixer' => '',
					'TikTok' => '',
				),
				'default'  => array(
					'Facebook'   => '#',
					'Twitter'   => '#',
					'LinkedIn' => '',
					'Instagram' => '#',
					'Github' => '',
					'VK' => '',
					'YouTube' => '#',
					'Pinterest' => '',
					'Tumblr' => '',
					'Dribbble' => '#',
					'Vimeo' => '',
					'Flickr' => '',
					'Yelp' => '',
					'Telegram' => '',
					'Snapchat' => '',
					'Twitch' => '#',
					'Faceit' => '',
					'Steam' => '',
					'Discord' => '',
					'Mixer' => '',
					'TikTok' => '',
				),
				'required'  => array(
					array('alchemists__footer-secondary-copyright-layout', '=', array( 'social', 'text_social' ) ),
				),
			),
		)
	) );


	// Footer Sponsors
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Sponsors', 'alchemists' ),
		'icon'       => 'el-icon-briefcase',
		'id'         => 'alchemists__subsection-footer-sponsors',
		'fields'     => array(
			array(
				'id'        => 'alchemists__footer-sponsors',
				'type'      => 'switch',
				'title'     => esc_html__('Sponsors', 'alchemists'),
				'subtitle'  => esc_html__('Sponsors are hidden by default.', 'alchemists'),
				'default'   => 0,
				'on'        => esc_html__( 'Enable', 'alchemists' ),
				'off'       => esc_html__( 'Disable', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__footer-sponsors-images',
				'type'     => 'gallery',
				'title'    => esc_html__( 'Add/Edit Sponsors Images', 'alchemists' ),
				'desc'     => esc_html__( 'Create a list of Sponsors by selecting existing or uploading new images', 'alchemists' ),
				'required' => array( 'alchemists__footer-sponsors', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__footer-position',
				'type'      => 'select',
				'title'     => esc_html__( 'Position', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Sponsors position', 'alchemists' ),
				'options'   => array(
					'after_header'   => esc_html__( 'After Header', 'alchemists' ),
					'before_widgets' => esc_html__( 'Before Footer Widgets', 'alchemists' ),
					'after_widgets'  => esc_html__( 'After Footer Widgets', 'alchemists' ),
				),
				'default'   => 'after_widgets',
				'required'  => array('alchemists__footer-sponsors', '=', '1'),
			),
			array(
				'id'        => 'alchemists__footer-sponsors-layout',
				'type'      => 'select',
				'title'     => esc_html__( 'Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select Sponsors layout', 'alchemists' ),
				'options'   => array(
					'default'   => esc_html__( 'Default', 'alchemists' ),
					'carousel' => esc_html__( 'Carousel', 'alchemists' ),
				),
				'default'   => 'default',
				'desc'      => esc_html__( 'Select "Carousel" to display more than 6 items.', 'alchemists' ),
				'required'  => array('alchemists__footer-sponsors', '=', '1'),
			),
			array(
				'id'       => 'alchemists__footer-sponsors-slidestoshow',
				'type'     => 'spinner',
				'title'    => esc_html__( 'Items to show', 'alchemists' ),
				'subtitle' => esc_html__( 'Number of items to show', 'alchemists' ),
				'desc'     => esc_html__( 'Use arrows to increase/decrease the number of items to display.', 'alchemists' ),
				'default'  => '6',
				'min'      => '1',
				'step'     => '1',
				'max'      => '12',
				'required'  => array('alchemists__footer-sponsors-layout', '=', 'carousel'),
			),
			array(
				'id'        => 'alchemists__footer-sponsors-autoplay',
				'type'      => 'switch',
				'title'     => esc_html__( 'Autoplay', 'alchemists' ),
				'subtitle'  => esc_html__( 'Enables carousel autoplay.', 'alchemists' ),
				'desc'      => esc_html__( 'Autoplay enabled by default.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
				'required'  => array('alchemists__footer-sponsors-layout', '=', 'carousel'),
			),
			array(
				'id'       => 'alchemists__footer-sponsors-autoplay-speed',
				'type'     => 'slider',
				'title'    => esc_html__( 'Autoplay Speed', 'alchemists' ),
				'subtitle' => esc_html__( 'Delay duration between slide animation.', 'alchemists' ),
				'desc'     => esc_html__( 'Duration in seconds, Min: 3, max: 20, step: 0.5, default value: 8', 'alchemists' ),
				'default'  => 8,
				'min'      => 1,
				'step'     => 0.5,
				'max'      => 20,
				'resolution' => 0.1,
				'display_value' => 'text',
				'required'  => array(
					array( 'alchemists__footer-sponsors-layout', '=', 'carousel' ),
					array( 'alchemists__footer-sponsors-autoplay', '=', '1' )
				),
			),
			array(
				'id'        => 'alchemists__footer-sponsors-arrows',
				'type'      => 'switch',
				'title'     => esc_html__( 'Arrows', 'alchemists' ),
				'subtitle'  => esc_html__( 'Add arrows for navigation.', 'alchemists' ),
				'desc'      => esc_html__( 'Prev/Next Arrows.', 'alchemists' ),
				'default'   => 1,
				'on'        => esc_html__( 'On', 'alchemists' ),
				'off'       => esc_html__( 'Off', 'alchemists' ),
				'required'  => array('alchemists__footer-sponsors-layout', '=', 'carousel'),
			),
			array(
				'id'       => 'alchemists__footer-sponsors-title',
				'type'     => 'text',
				'title'    => esc_html__( 'Sponsors Title', 'alchemists' ),
				'subtitle' => esc_html__( 'Displayed before the images', 'alchemists' ),
				'default'  => esc_html__( 'Our Sponsors:', 'alchemists' ),
				'required' => array( 'alchemists__footer-sponsors', '=', '1' ),
			),
			array(
				'id'            => 'alchemists__footer-sponsors-images-opacity',
				'type'          => 'slider',
				'title'         => esc_html__( 'Image Opacity - Default', 'alchemists' ),
				'desc'          => esc_html__( 'Adjust the Sponsors Image opacity.', 'alchemists' ),
				'default'       => 0.2,
				'min'           => 0,
				'step'          => 0.1,
				'max'           => 1,
				'resolution'    => 0.1,
				'display_value' => 'text',
				'required'  => array('alchemists__footer-sponsors', '=', '1'),
			),
			array(
				'id'            => 'alchemists__footer-sponsors-images-opacity-hover',
				'type'          => 'slider',
				'title'         => esc_html__( 'Image Opacity - Hover', 'alchemists' ),
				'desc'          => esc_html__( 'Adjust the Sponsors Image opacity on hover.', 'alchemists' ),
				'default'       => 1,
				'min'           => 0,
				'step'          => 0.1,
				'max'           => 1,
				'resolution'    => 0.1,
				'display_value' => 'text',
				'required'  => array('alchemists__footer-sponsors', '=', '1'),
			),
			array(
				'id'        => 'alchemists__footer-sponsors-images-size',
				'type'      => 'select',
				'title'     => esc_html__( 'Image Size', 'alchemists' ),
				'subtitle'  => esc_html__( 'Determines size of sponsor images.', 'alchemists' ),
				'options'   => array(
					'full'                        => esc_html__( 'Full', 'alchemists' ),
					'alchemists_team-logo-fit'    => esc_html__( 'Small - 100px', 'alchemists' ),
					'alchemists_team-logo-sm-fit' => esc_html__( 'Extra Small - 70px', 'alchemists' ),
				),
				'default'   => 'full',
				'desc'      => esc_html__( 'The full sponsor image displayed by default.', 'alchemists' ),
				'required'  => array('alchemists__footer-sponsors', '=', '1'),
			),
		)
	) );


	// Styling Options
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__( 'Styling', 'alchemists' ),
		'icon'      => 'el-icon-tint',
		'id'        => 'alchemists__section-styling',
		'fields'    => array(
			array(
				'id'        => 'alchemists__color-preset',
				'type'      => 'image_select',
				'compiler'  => false,
				'presets'   => true,
				'title'     => esc_html__('Color Presets', 'alchemists'),
				'desc'      => esc_html__('Choose color preset you want to use.', 'alchemists'),
				'default'   => '1',
				'options'   => array(

					// Color Scheme: Basketball - Light
					'1' => array(
						'alt' => esc_html__( 'Basketball - Light', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-basketball-light.png',
						'presets' => array(
							'color-primary'        => '#ffdc11',
							'color-primary-darken' => '#ffcc00',
							'color-dark'           => '#1e2024',
							'color-dark-lighten'   => '#292c31',
							'color-gray'           => '#9a9da2',
							'color-2'              => '#31404b',
							'color-3'              => '#ff7e1f',
							'color-4'              => '#9a66ca',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#31404b'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#31404b',
								'hover'   => '#4f6779',
							),

							// Tables
							'alchemists__table-thead-color'     => '#31404b',
							'alchemists__table-highlight-color' => '#31404b',

							// Progress Bars
							'alchemists__progress-bars-track-color' => '#ecf0f6',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#edeff4',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#292c31',
							'alchemists__header-top-bar-dropdown-bg' => '#ffffff',

							// Header
							'alchemists__header-primary-bg' => '#292c31',
							'alchemists__header-secondary-bg' => '#1e2024',
							'alchemists__header-tertiary-bg' => '#ffffff',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => 'transparent',
							'alchemists__header-primary-submenu-bg' => '#ffffff',
							'alchemists__header-primary-submenu-border-color' => '#e4e7ed',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#31404b',
								'hover' => '#31404b'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => 'rgba(228, 231, 237, 0.2)',
							'alchemists__header-primary-megamenu-title-color' => '#31404b',
							'alchemists__header-primary-megamenu-post-title-color' => '#31404b',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => '#adb3b7',
								'hover' => '#31404b'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#31404b',

							// Card
							'alchemists__card-bg'            => '#ffffff',
							'alchemists__card-header-bg'     => '#ffffff',
							'alchemists__card-subheader-bg'  => '#f5f7f9',
							'alchemists__card-border-color'  => '#e4e7ed',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#31404b',
								'active' => '#31404b',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#ecf0f6',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#ffffff',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#31404b',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#ffffff',
								'active'  => '#ffffff',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#31404b',
								'active'  => '#31404b',
							),
							'alchemists__form-control-border' => array(
								'regular' => '#e4e7ed',
							),

							// Shop
							'alchemists__product-gradient' => 1,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#1e2024',
							),
							'alchemists__footer-info-bg' => '#1e2024',
							'alchemists__footer-secondary-bg' => '#191b1e',

							// Preloader
							'alchemists__opt-preloader-bg' => '#1e2024',
						)
					),

					// Color Scheme: Basketball - Dark
					'1-dark' => array(
						'alt' => esc_html__( 'Basketball - Dark', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-basketball-dark.png',
						'presets' => array(
							'color-primary'        => '#ffdc11',
							'color-primary-darken' => '#ffcc00',
							'color-dark'           => '#1e2024',
							'color-dark-lighten'   => '#292c31',
							'color-gray'           => '#9a9da2',
							'color-2'              => '#ffffff',
							'color-3'              => '#ff7e1f',
							'color-4'              => '#9a66ca',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#ffffff'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#ffffff',
								'hover'   => '#9a9da2',
							),

							// Tables
							'alchemists__table-thead-color'     => '#ffffff',
							'alchemists__table-highlight-color' => '#ffffff',

							// Progress Bars
							'alchemists__progress-bars-track-color' => 'rgba(255,255,255,0.1)',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#1e2024',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#292c31',
							'alchemists__header-top-bar-dropdown-bg' => '#292c31',

							// Header
							'alchemists__header-primary-bg' => '#292c31',
							'alchemists__header-secondary-bg' => '#1e2024',
							'alchemists__header-tertiary-bg' => '#1e2024',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => 'transparent',
							'alchemists__header-primary-submenu-bg' => '#292c31',
							'alchemists__header-primary-submenu-border-color' => '#34373c',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#ffffff',
								'hover' => '#ffffff'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => 'rgba(228, 231, 237, 0.2)',
							'alchemists__header-primary-megamenu-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-post-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => 'rgba(255,255,255,0.4)',
								'hover' => '#ffffff'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#ffffff',

							// Card
							'alchemists__card-bg'            => '#292c31',
							'alchemists__card-header-bg'     => '#292c31',
							'alchemists__card-subheader-bg'  => '#303337',
							'alchemists__card-border-color'  => '#34373c',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#ffffff',
								'active' => '#ffffff',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#3f4246',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#292c31',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#3f4246',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#2c2e32',
								'active'  => '#2c2e32',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#fff',
								'active'  => '#fff',
							),
							'alchemists__form-control-border' => array(
								'regular' => '#3f4145',
							),

							// Shop
							'alchemists__product-gradient' => 1,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#191b1e',
							),
							'alchemists__footer-info-bg' => '#1e2024',
							'alchemists__footer-secondary-bg' => '#191b1e',

							// Preloader
							'alchemists__opt-preloader-bg' => '#1e2024',
						),
					),

					// Color Scheme: Soccer - Light
					'2' => array(
						'alt' => esc_html__( 'Soccer - Light', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-soccer-light.png',
						'presets' => array(
							'color-primary'        => '#38a9ff',
							'color-primary-darken' => '#1892ed',
							'color-dark'           => '#1e2024',
							'color-dark-lighten'   => '#292c31',
							'color-gray'           => '#9a9da2',
							'color-2'              => '#31404b',
							'color-3'              => '#07e0c4',
							'color-4'              => '#c2ff1f',
							'color-4-darken'       => '#9fe900',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#31404b'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#31404b',
								'hover'   => '#4f6779',
							),

							// Tables
							'alchemists__table-thead-color'     => '#31404b',
							'alchemists__table-highlight-color' => '#31404b',

							// Progress Bars
							'alchemists__progress-bars-track-color' => '#ecf0f6',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#edeff4',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#292c31',
							'alchemists__header-top-bar-dropdown-bg' => '#ffffff',

							// Header
							'alchemists__header-primary-bg' => '#292c31',
							'alchemists__header-secondary-bg' => '#1e2024',
							'alchemists__header-tertiary-bg' => '#ffffff',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => 'transparent',
							'alchemists__header-primary-submenu-bg' => '#1e2024',
							'alchemists__header-primary-submenu-border-color' => '#292c31',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#ffffff',
								'hover' => '#c2ff1f'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => 'transparent',
							'alchemists__header-primary-megamenu-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-post-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => 'rgba(255,255,255,0.4)',
								'hover' => '#ffffff'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#ffffff',

							// Card
							'alchemists__card-bg'            => '#ffffff',
							'alchemists__card-header-bg'     => '#ffffff',
							'alchemists__card-subheader-bg'  => '#f5f7f9',
							'alchemists__card-border-color'  => '#e4e7ed',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#31404b',
								'active' => '#31404b',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#ecf0f6',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#ffffff',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#31404b',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#ffffff',
								'active'  => '#ffffff',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#31404b',
								'active'  => '#31404b',
							),
							'alchemists__form-control-border' => array(
								'regular' => '#e4e7ed',
							),

							// Shop
							'alchemists__product-gradient' => 0,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#1e2024',
							),
							'alchemists__footer-info-bg' => '#1e2024',
							'alchemists__footer-secondary-bg' => '#16171a',

							// Preloader
							'alchemists__opt-preloader-bg' => '#1e2024',
						)
					),

					// Color Scheme: Soccer - Dark
					'2-dark' => array(
						'alt' => esc_html__( 'Soccer - Dark', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-soccer-dark.png',
						'presets' => array(
							'color-primary'        => '#38a9ff',
							'color-primary-darken' => '#1892ed',
							'color-dark'           => '#1e2024',
							'color-dark-lighten'   => '#292c31',
							'color-gray'           => '#9a9da2',
							'color-2'              => '#ffffff',
							'color-3'              => '#07e0c4',
							'color-4'              => '#c2ff1f',
							'color-4-darken'       => '#9fe900',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#ffffff'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#ffffff',
								'hover'   => '#9a9da2',
							),

							// Tables
							'alchemists__table-thead-color'     => '#ffffff',
							'alchemists__table-highlight-color' => '#ffffff',

							// Progress Bars
							'alchemists__progress-bars-track-color' => 'rgba(255,255,255,0.1)',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#1e2024',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#292c31',
							'alchemists__header-top-bar-dropdown-bg' => '#292c31',

							// Header
							'alchemists__header-primary-bg' => '#292c31',
							'alchemists__header-secondary-bg' => '#1e2024',
							'alchemists__header-tertiary-bg' => '#1e2024',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => 'transparent',
							'alchemists__header-primary-submenu-bg' => '#1e2024',
							'alchemists__header-primary-submenu-border-color' => '#292c31',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#ffffff',
								'hover' => '#c2ff1f'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => 'transparent',
							'alchemists__header-primary-megamenu-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-post-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => 'rgba(255,255,255,0.4)',
								'hover' => '#ffffff'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#ffffff',

							// Card
							'alchemists__card-bg'            => '#292c31',
							'alchemists__card-header-bg'     => '#292c31',
							'alchemists__card-subheader-bg'  => '#303337',
							'alchemists__card-border-color'  => '#34373c',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#ffffff',
								'active' => '#ffffff',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#3f4246',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#292c31',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#3f4246',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#2c2e32',
								'active'  => '#2c2e32',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#fff',
								'active'  => '#fff',
							),
							'alchemists__form-control-border' => array(
								'regular' => '#3f4145',
							),

							// Shop
							'alchemists__product-gradient' => 0,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#191b1e',
							),
							'alchemists__footer-info-bg' => '#1e2024',
							'alchemists__footer-secondary-bg' => '#16171a',

							// Preloader
							'alchemists__opt-preloader-bg' => '#1e2024',
						)
					),

					// Color Scheme: American Football
					'3' => array(
						'alt' => esc_html__( 'Football', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-football-dark.png',
						'presets' => array(
							'color-primary'        => '#f92552',
							'color-primary-darken' => '#f92552',
							'color-dark'           => '#323150',
							'color-dark-2'         => '#282840',
							'color-dark-lighten'   => '#383759',
							'color-gray'           => '#9e9caa',
							'color-2'              => '#3c3b5b',
							'color-3'              => '#9e69ee',
							'color-4'              => '#3ffeca',
							'color-4-darken'       => '#0fe3ab',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#ffffff'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#ffffff',
								'hover'   => '#fafafa',
							),

							// Tables
							'alchemists__table-thead-color'     => '#ffffff',
							'alchemists__table-highlight-color' => '#ffffff',

							// Progress Bars
							'alchemists__progress-bars-track-color' => '#484773',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#1e202f',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#323150',
							'alchemists__header-top-bar-dropdown-bg' => '#383759',

							// Header
							'alchemists__header-primary-bg' => '#323150',
							'alchemists__header-secondary-bg' => '#282840',
							'alchemists__header-tertiary-bg' => '#ffffff',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => '#3c3b5b',
							'alchemists__header-primary-submenu-bg' => '#383759',
							'alchemists__header-primary-submenu-border-color' => '#3c3b5b',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#ffffff',
								'hover' => '#3ffeca'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => '#323150',
							'alchemists__header-primary-megamenu-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-post-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => '#9e9caa',
								'hover' => '#3ffeca'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#ffffff',

							// Card
							'alchemists__card-bg'            => '#323150',
							'alchemists__card-header-bg'     => '#383759',
							'alchemists__card-subheader-bg'  => '#363555',
							'alchemists__card-border-color'  => '#3c3b5b',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#ffffff',
								'active' => '#ffffff',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#4e4d73',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#ffffff',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#3c3b5b',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#383759',
								'active'  => '#383759',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#ffffff',
								'active'  => '#ffffff',
							),
							'alchemists__form-control-border' => array(
								'regular' => 'rgba(255,255,255,.05)',
							),

							// Shop
							'alchemists__product-gradient' => 1,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#282840',
							),
							'alchemists__footer-info-bg' => '#282840',
							'alchemists__footer-secondary-bg' => '#282840',

							// Preloader
							'alchemists__opt-preloader-bg' => '#282840',
						)
					),

					// Color Scheme: eSport
					'4' => array(
						'alt' => esc_html__( 'eSports', 'alchemists' ),
						'img' => get_template_directory_uri() . '/admin/images/color-pallete-esports-dark.png',
						'presets' => array(
							'color-primary'        => '#00ff5b',
							'color-primary-darken' => '#1bd75e',
							'color-dark'           => '#362b45',
							'color-dark-2'         => '#403351',
							'color-dark-lighten'   => '#392d49',
							'color-gray'           => '#a59cae',
							'color-2'              => '#6a3bc0',
							'color-3'              => '#f92552',
							'color-4'              => '#ffb400',
							'color-4-darken'       => '#ffb400',

							// Typography
							'alchemists__custom_heading_font' => 1,
							'headings-typography' => array(
								'color' => '#ffffff'
							),
							'alchemists__custom_heading_link_color' => array(
								'regular' => '#ffffff',
								'hover'   => '#00ff5b',
							),

							// Tables
							'alchemists__table-thead-color'     => '#ffffff',
							'alchemists__table-highlight-color' => '#ffffff',

							// Progress Bars
							'alchemists__progress-bars-track-color' => '#4b3c5f',

							// Body
							'alchemists__body-bg'  => array(
								'background-color' => '#2b2236',
							),

							// Top Bar
							'alchemists__header-top-bar-bg' => '#362b45',
							'alchemists__header-top-bar-dropdown-bg' => '#392d49',

							// Header
							'alchemists__header-primary-bg' => '#362b45',
							'alchemists__header-secondary-bg' => '#403351',
							'alchemists__header-tertiary-bg' => '#ffffff',

							// Pushy Panel
							'alchemists__pushy-panel-color' => 'dark',

							// Nav
							'alchemists__header-primary-divider-color' => 'transparent',
							'alchemists__header-primary-submenu-bg' => '#392d49',
							'alchemists__header-primary-submenu-border-color' => '#4b3b60',
							'alchemists__header-primary-submenu-link-color' => array(
								'regular' => '#ffffff',
								'hover' => '#00ff5b'
							),
							'alchemists__header-primary-submenu-hover-bg-color' => '#362b45',
							'alchemists__header-primary-megamenu-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-post-title-color' => '#ffffff',
							'alchemists__header-primary-megamenu-link-color' => array(
								'regular' => '#a59cae',
								'hover' => '#00ff5b'
							),
							'alchemists__header-primary-submenu-dropdown-caret-color' => '#ffffff',

							// Card
							'alchemists__card-bg'            => '#392d49',
							'alchemists__card-header-bg'     => '#403351',
							'alchemists__card-subheader-bg'  => '#403351',
							'alchemists__card-border-color'  => '#4b3b60',

							// Filter
							'alchemists__content-content-filter' => array(
								'hover'  => '#7a7283',
								'active' => '#ffffff',
							),

							// Circular Bar
							'alchemists__circular-bars-track-color' => '#4b3b60',

							// Buttons
							'alchemists__button_primary_txt_color' => array(
								'regular' => '#ffffff',
							),
							'alchemists__button_default_alt_bg_color' => array(
								'regular' => '#4b3b60',
							),

							// Form Controls
							'alchemists__form-control' => array(
								'regular' => '#362b45',
								'active'  => '#362b45',
							),
							'alchemists__form-control-border' => array(
								'regular' => '#4b3b60',
							),
							'alchemists__form-control-txt' => array(
								'regular' => '#a59cae',
								'active'  => '#a59cae',
							),

							// Shop
							'alchemists__product-gradient' => 0,

							// Footer
							'alchemists__footer-widgets-bg'  => array(
								'background-color' => '#3b2f4c',
							),
							'alchemists__footer-info-bg' => '#403351',
							'alchemists__footer-secondary-bg' => '#362b45',

							// Preloader
							'alchemists__opt-preloader-bg' => '#403351',
						)
					),
				),
			),

			array(
				'id'          => 'color-primary',
				'type'        => 'color',
				'title'       => esc_html__( 'Primary Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a primary color.', 'alchemists' ),
				'default'     => '#ffdc11',
				'transparent' => false
			),
			array(
				'id'          => 'color-primary-darken',
				'type'        => 'color',
				'title'       => esc_html__( 'Primary Color - Alt', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a primary alt color.', 'alchemists' ),
				'default'     => '#ffcc00',
				'transparent' => false
			),
			array(
				'id'          => 'color-dark',
				'type'        => 'color',
				'title'       => esc_html__( 'Dark Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a dark color.', 'alchemists' ),
				'default'     => '#1e2024',
				'transparent' => false
			),
			array(
				'id'          => 'color-dark-2',
				'type'        => 'color',
				'title'       => esc_html__( 'Dark Color 2', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a dark color 2.', 'alchemists' ),
				'default'     => '#282840',
				'class'       => 'alchemists-field alchemists-field--football alchemists-field--football-only alchemists-field--color-dark-2',
				'transparent' => false
			),
			array(
				'id'          => 'color-dark-lighten',
				'type'        => 'color',
				'title'       => esc_html__( 'Dark Color - Lighten', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a dark lighten color.', 'alchemists' ),
				'default'     => '#292c31',
				'transparent' => false
			),
			array(
				'id'          => 'color-gray',
				'type'        => 'color',
				'title'       => esc_html__( 'Gray Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a gray color.', 'alchemists' ),
				'default'     => '#9a9da2',
				'transparent' => false
			),
			array(
				'id'          => 'color-2',
				'type'        => 'color',
				'title'       => esc_html__( 'Secondary Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a secondary color.', 'alchemists' ),
				'default'     => '#31404b',
				'transparent' => false
			),
			array(
				'id'          => 'color-3',
				'type'        => 'color',
				'title'       => esc_html__( 'Tertiary Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a tertiary color.', 'alchemists' ),
				'default'     => '#ff7e1f',
				'transparent' => false
			),
			array(
				'id'          => 'color-4',
				'type'        => 'color',
				'title'       => esc_html__( 'Quaternary Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a quaternary color.', 'alchemists' ),
				'default'     => '#9a66ca',
				'transparent' => false
			),
			array(
				'id'          => 'color-4-darken',
				'type'        => 'color',
				'title'       => esc_html__( 'Quaternary Color - Alt', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick a quaternary alt color.', 'alchemists' ),
				'transparent' => false,
				'class'       => 'alchemists-field alchemists-field--football-only alchemists-field--soccer-only',
			),

		)
	) );


	// Styling: Body
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Base', 'alchemists' ),
		'id'         => 'alchemists__subsection-styling-body',
		'subsection' => true,
		'fields'     => array(
			array(
				'id'          => 'alchemists__body-bg',
				'type'        => 'background',
				'transparent' => false,
				'output'      => array('body'),
				'title'       => esc_html__( 'Body Background', 'alchemists' ),
				'subtitle'    => esc_html__( 'Body styling options', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Body Background with color, background image, background position etc.', 'alchemists' ),
			),
		)
	) );


	// Styling: Card
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Cards', 'alchemists' ),
		'subsection' => true,
		'desc'       => esc_html__( 'Card - is a most used building block of the theme content. It is used for widgets, shortcodes, elements, filter, etc.', 'alchemists' ),
		'id'         => 'alchemists__subsection-styling-card',
		'fields'     => array(
			// Card - Background Color
			array(
				'id'          => 'alchemists__card-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Card - Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color of Card element', 'alchemists' ),
			),
			// Card - Header Background Color
			array(
				'id'          => 'alchemists__card-header-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Card - Header Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color of Header inside Card element', 'alchemists' ),
			),
			// Card - Subheader Background Color
			array(
				'id'          => 'alchemists__card-subheader-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Card - Subheader Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color of Subheader inside Card element', 'alchemists' ),
			),
			// Card - Border Color
			array(
				'id'          => 'alchemists__card-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Card - Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize border color or Card element', 'alchemists' ),
			),
		)
	) );


	// Styling: Links & Buttons
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Links & Buttons', 'alchemists' ),
		'id'         => 'alchemists__subsection-styling-links-buttons',
		'subsection' => true,
		'fields'     => array(
			// Links
			array(
				'id'       => 'alchemists__subsection-styling-links-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Links', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Links colors.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Link Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for links.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__custom_heading_link_color',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Headings Link Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Choose color for Heading Links.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for links inside Headings. Note: Hover state will not be applied for some Headings, e.g. in American Football version.', 'alchemists' ),
				'active'      => false,
			),
			array(
				'id'       => 'alchemists__subsection-styling-links-end',
				'type'     => 'section',
				'indent'   => false,
			),


			// Buttons
			array(
				'id'       => 'alchemists__subsection-styling-buttons-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Buttons', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Buttons colors.', 'alchemists' ),
				'indent'   => true
			),

			// Outline
			array(
				'id'          => 'alchemists__button_outline_txt_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Outline Buttons Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize text color for Outline Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for text inside outline buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_outline_bg_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Outline Buttons Background Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize background color for Outline Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for background inside outline buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_outline_border_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Outline Buttons Border Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize border color for Outline Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for border color inside outline buttons.', 'alchemists' ),
			),

			// Button: Default
			array(
				'id'          => 'alchemists__button_default_txt_color',
				'type'        => 'link_color',
				'hover'       => false,
				'active'      => false,
				'title'       => esc_html__( 'Button Default - Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize text color for Default Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for text inside default buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_default_bg_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Button Default - Background Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize background color for Default Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for background inside default buttons.', 'alchemists' ),
			),

			// Button: Default Alt
			array(
				'id'          => 'alchemists__button_default_alt_txt_color',
				'type'        => 'link_color',
				'hover'       => false,
				'active'      => false,
				'title'       => esc_html__( 'Button Default Alt - Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize text color for Default Alt Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for text inside default alt buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_default_alt_bg_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Button Default Alt - Background Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize background color for Default alt Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for background inside default alt buttons.', 'alchemists' ),
			),

			// Button: Primary
			array(
				'id'          => 'alchemists__button_primary_txt_color',
				'type'        => 'link_color',
				'hover'       => false,
				'active'      => false,
				'title'       => esc_html__( 'Button Primary - Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize text color for Primary Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for text inside primary buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_primary_bg_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Button Primary - Background Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize background color for Primary Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for background inside primary buttons.', 'alchemists' ),
			),

			// Button: Primary Inverse
			array(
				'id'          => 'alchemists__button_primary_inverse_txt_color',
				'type'        => 'link_color',
				'hover'       => false,
				'active'      => false,
				'title'       => esc_html__( 'Button Primary Inverse - Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize text color for Primary Inverse Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for text inside primary inverse buttons.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__button_primary_inverse_bg_color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Button Primary Inverse - Background Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Customize background color for Primary Inverse Buttons.', 'alchemists' ),
				'desc'        => esc_html__( 'This option aplied for background inside primary inverse buttons.', 'alchemists' ),
			),

			array(
				'id'       => 'alchemists__subsection-styling-buttons-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );


	// Styling: Form Controls
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Form Controls', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-form-controls',
		'fields'     => array(
			// Form Controls: Background Color
			array(
				'id'          => 'alchemists__form-control',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Form Control Background Colors', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Form Control Background Color.', 'alchemists' ),
				'hover'       => false,
			),
			// Form Controls: Border Color
			array(
				'id'          => 'alchemists__form-control-border',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Form Control Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Form Control Border Color.', 'alchemists' ),
				'hover'       => false,
			),
			// Form Controls: Placeholder Color
			array(
				'id'          => 'alchemists__form-control-placeholder',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Form Control Placeholder Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Form Control Placeholder Text Color.', 'alchemists' ),
			),
			// Form Controls: Text Color
			array(
				'id'          => 'alchemists__form-control-txt',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Form Control Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Form Control Text Color.', 'alchemists' ),
				'hover'       => false,
			),
		)
	) );


	// Styling: Table
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Tables', 'alchemists' ),
		'desc'       => esc_html__( 'By default tables colors are relied on Cards colors. For example, if you change Card Border Color, then that color will be applied for tables. However, you can specify different colors for tables by using options below.', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-table',
		'fields'     => array(
			// Table: Background color
			array(
				'id'          => 'alchemists__table-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Background Color.', 'alchemists' ),
			),
			// Table: Background color on Hover
			array(
				'id'          => 'alchemists__table-bg-hover',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Background Color - Hover', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Background Color on Hover.', 'alchemists' ),
			),
			// Table: Background color Active
			array(
				'id'          => 'alchemists__table-bg-active',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Background Color - Active', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Background Active Color.', 'alchemists' ),
			),
			// Table: Border color
			array(
				'id'          => 'alchemists__table-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Border Color.', 'alchemists' ),
			),
			// Table: Heading background color
			array(
				'id'          => 'alchemists__table-thead-bg-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Heading Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Heading Background Color.', 'alchemists' ),
			),
			// Table: Heading background color
			array(
				'id'          => 'alchemists__table-thead-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Heading Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Heading Color.', 'alchemists' ),
			),
			// Table: Highlight color
			array(
				'id'          => 'alchemists__table-highlight-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Table Highlight Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Table Highlight Color.', 'alchemists' ),
			),
		)
	) );


	// Styling: Progress Bars
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Bars', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-bars',
		'fields'     => array(

			// Progress Bars: Track color
			array(
				'id'       => 'alchemists__progress-bars--section-styling-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Progress Bars', 'alchemists' ),
				'subtitle' => esc_html__( 'Styling for progress bars.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__progress-bars-track-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Track Color', 'alchemists' ),
				'desc'        => esc_html__( 'The color of the circular bar tracks.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__progress-bars--section-content-end',
				'type'     => 'section',
				'indent'   => false,
			),

			// Circular Bars: Track color
			array(
				'id'       => 'alchemists__circular-bars--section-styling-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Circular Bars', 'alchemists' ),
				'subtitle' => esc_html__( 'Styling for circular bars.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__circular-bars-track-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Track Color', 'alchemists' ),
				'desc'        => esc_html__( 'The color of the circular bar tracks.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__circular-bars--section-content-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );


	// Styling: Top Bar
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Top Bar', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-top-bar',
		'fields'     => array(
			// Top Bar Background
			array(
				'id'          => 'alchemists__header-top-bar-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Top Bar Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Top Bar Background Color.', 'alchemists' ),
			),
			// Top Bar Link Color
			array(
				'id'          => 'alchemists__header-top-bar-link-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Header Top Bar links color.', 'alchemists' ),
			),
			// Top Bar Social Links Color
			array(
				'id'          => 'alchemists__header-top-bar-social-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Social Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Social Links displayed in the Top Bar.', 'alchemists' ),
			),
			// Top Bar Social Opacity
			array(
				'id'            => 'alchemists__header-top-bar-social-link-opacity',
				'type'          => 'slider',
				'title'         => esc_html__( 'Social Links Opacity', 'alchemists' ),
				'desc'          => esc_html__( 'Drag the slider to adjust the Social Links opacity in %', 'alchemists' ),
				'default'       => 60,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
			),
			// Top Bar Highlight
			array(
				'id'          => 'alchemists__header-top-bar-highlight',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Hover & Highlight Color', 'alchemists' ),
				'desc'        => esc_html__( 'Header Top Bar hover & highlight color.', 'alchemists' ),
			),
			// Top Bar Text Color
			array(
				'id'          => 'alchemists__header-top-bar-text-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Base & Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Header Top Bar base & text color.', 'alchemists' ),
			),
			// Top Bar Divider Color
			array(
				'id'          => 'alchemists__header-top-bar-divider-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Text Divider Color', 'alchemists' ),
				'desc'        => esc_html__( 'Header Top Bar text divider color.', 'alchemists' ),
			),
			// Top Bar caret color
			array(
				'id'          => 'alchemists__header-top-bar-dropdown-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Dropdown Caret Color', 'alchemists' ),
				'desc'        => esc_html__( 'Dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
				'output'      => array( '.nav-account .nav-account__item.has-children::after' ),
			),
			// Top Bar Dropdown Background
			array(
				'id'          => 'alchemists__header-top-bar-dropdown-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Top Bar Dropdown Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Top Bar Dropdown Background Color.', 'alchemists' ),
			),
			// Top Bar Dropdown Border Color
			array(
				'id'          => 'alchemists__header-top-bar-dropdown-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Top Bar Dropdown Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Top Bar Dropdown Border Color.', 'alchemists' ),
			),
			// Top Bar Dropdown Links color
			array(
				'id'          => 'alchemists__header-top-bar-dropdown-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Top Bar Dropdown Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Top Bar Dropdown Links Color.', 'alchemists' ),
			),

		)
	) );


	// Styling: Header Primary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Header Primary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-header-primary',
		'fields'     => array(
			// Header Primary Background
			array(
				'id'          => 'alchemists__header-primary-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Header Primary Background Color.', 'alchemists' ),
			),
			// Header Primary Font Color
			array(
				'id'          => 'alchemists__header-primary-font-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Header Primary Links Color.', 'alchemists' ),
			),
			// Header Primary Border Height
			array(
				'id'             => 'alchemists__header-primary-border-height',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Link Border Height', 'alchemists' ),
				'desc'           => esc_html__( 'Set Border Height for hovered and activate Navigation items in px (2px by default).', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
			),
			// Header Primary Border Color
			array(
				'id'          => 'alchemists__header-primary-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Link Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Appeared on hover and active Navigation items.', 'alchemists' ),
			),
			// Header Primary Divider Color
			array(
				'id'          => 'alchemists__header-primary-divider-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Divider Color', 'alchemists' ),
				'desc'        => esc_html__( 'Appeared between Navigation items in some versions.', 'alchemists' ),
			),
			// Nav dropdown caret color
			array(
				'id'          => 'alchemists__header-primary-dropdown-caret-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Dropdown Caret Color', 'alchemists' ),
				'desc'        => esc_html__( 'Dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
				'output'      => array( '.main-nav__list > li.menu-item-has-children > a::after' ),
			),
			// Submenu Background
			array(
				'id'          => 'alchemists__header-primary-submenu-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu & Megamenu Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Dropdown Menu Background Color.', 'alchemists' ),
			),
			// Submenu Border Color
			array(
				'id'          => 'alchemists__header-primary-submenu-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Border color for Submenu.', 'alchemists' ),
			),
			// Submenu Link Color
			array(
				'id'          => 'alchemists__header-primary-submenu-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Submenu Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Submenu Links Color.', 'alchemists' ),
			),
			// Submenu Background Color
			array(
				'id'          => 'alchemists__header-primary-submenu-hover-bg-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu Background Color on Hover', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Submenu Background Color on Hover.', 'alchemists' ),
			),
			// Submenu caret color
			array(
				'id'          => 'alchemists__header-primary-submenu-dropdown-caret-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu Dropdown Caret Color', 'alchemists' ),
				'desc'        => esc_html__( 'Submenu dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
			),
			// Megamenu Text Color
			array(
				'id'          => 'alchemists__header-primary-megamenu-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Megamenu Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Text color mostly used in widgets.', 'alchemists' ),
			),
			// Megamenu Widget Meta Link Color
			array(
				'id'          => 'alchemists__header-primary-megamenu-widget-meta-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Megamenu Widget Meta Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Text color used in widgets meta links.', 'alchemists' ),
			),
			// Megamenu Link Color
			array(
				'id'          => 'alchemists__header-primary-megamenu-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Megamenu Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Megamenu Links Color.', 'alchemists' ),
			),
			// Megamenu Title Color
			array(
				'id'          => 'alchemists__header-primary-megamenu-title-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Megamenu Column Title Color', 'alchemists' ),
				'desc'        => esc_html__( 'Title displayed for each column in Megamenu.', 'alchemists' ),
			),
			// Megamenu Post Title Color
			array(
				'id'          => 'alchemists__header-primary-megamenu-post-title-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Megamenu Post Title Color', 'alchemists' ),
				'desc'        => esc_html__( 'Title displayed for posts in Megamenu.', 'alchemists' ),
			),
			// Social Links
			array(
				'id'          => 'alchemists__header-primary-social-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Social Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Social Links displayed in the Header Primary.', 'alchemists' ),
			),
		)
	) );


	// Styling: Header Secondary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Header Secondary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-header-secondary',
		'fields'     => array(
			// Header Secondary Background
			array(
				'id'          => 'alchemists__header-secondary-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Header Secondary Background Color.', 'alchemists' ),
			),
			// Header Info Block Color
			array(
				'id'          => 'alchemists__header-info-block-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Icons Color', 'alchemists' ),
				'desc'        => esc_html__( 'Used to change color for icons in Header Secondary.', 'alchemists' ),
			),
			// Header Info Block Title Color
			array(
				'id'          => 'alchemists__header-info-block-title-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Titles Color', 'alchemists' ),
				'desc'        => esc_html__( 'Used to change color for title in Header Secondary.', 'alchemists' ),
			),
			// Header Info Block Link Color
			array(
				'id'          => 'alchemists__header-info-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Links Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for links in the Header Secondary.', 'alchemists' ),
			),
			// Header Info Block Cart Summary Color
			array(
				'id'          => 'alchemists__header-info-block-cart-sum-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Cart Summary Color', 'alchemists' ),
				'desc'        => esc_html__( 'Changes cart summary color in the Header Secondary.', 'alchemists' ),
			),
		)
	) );


	// Styling: Header Tertiary
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Header Tertiary', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-header-tertiary',
		'fields'     => array(
			// Header Tertiary Background
			array(
				'id'          => 'alchemists__header-tertiary-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Header Tertiary Background Color.', 'alchemists' ),
			),
			// Header Tertiary Heading Color
			array(
				'id'          => 'alchemists__header-tertiary-heading-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Heading Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Heading Color.', 'alchemists' ),
			),
			// Header Tertiary Toggle Color
			array(
				'id'          => 'alchemists__header-tertiary-toggle-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Toggle Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Toggle Color.', 'alchemists' ),
			),
			// Header Tertiary Link Color
			array(
				'id'          => 'alchemists__header-tertiary-nav-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Color for links Secondary Navigation Links.', 'alchemists' ),
			),
			// Header Tertiary Border Height
			array(
				'id'             => 'alchemists__header-tertiary-border-height',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Link Border Height', 'alchemists' ),
				'desc'           => esc_html__( 'Set Border Height for hovered and activate Navigation items in px (2px by default).', 'alchemists' ),
				'width'          => false,
				'mode'           => array(
					'width'  => false,
					'height' => true,
				),
			),
			// Header Tertiary Border Color
			array(
				'id'          => 'alchemists__header-tertiary-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Link Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Appeared on hover and active Navigation items.', 'alchemists' ),
			),
			// Header Tertiary Nav Color
			array(
				'id'          => 'alchemists__header-tertiary-background-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Active Link Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Appeared on hover and active Navigation items.', 'alchemists' ),
			),
			// Submenu Background
			array(
				'id'          => 'alchemists__header-tertiary-submenu-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Dropdown Menu Background Color.', 'alchemists' ),
			),
			// Submenu Border Color
			array(
				'id'          => 'alchemists__header-tertiary-submenu-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Submenu Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Border color for Submenu.', 'alchemists' ),
			),
			// Submenu Link Color
			array(
				'id'          => 'alchemists__header-tertiary-submenu-link-color',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Submenu Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Submenu Links Color.', 'alchemists' ),
			),
			// Mobile Nav Links Color
			array(
				'id'          => 'alchemists__header-tertiary-mobile-link-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Link Color', 'alchemists' ),
				'desc'        => esc_html__( 'Links color in Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Submenu Border Color
			array(
				'id'          => 'alchemists__header-tertiary-mobile-border',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Border color in Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Submenu Background
			array(
				'id'          => 'alchemists__header-tertiary-mobile-sub-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Submenu Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Submenu background color in Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Submenu Links Color
			array(
				'id'          => 'alchemists__header-tertiary-mobile-sub-link-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Submenu Link Color', 'alchemists' ),
				'desc'        => esc_html__( 'Submenu Links color in Navigation on mobile devices.', 'alchemists' ),
			),
		)
	) );


	// Styling: Search Form
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Search Form', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-search-form',
		'fields'     => array(
			// Search Form Styling - Desktop
			array(
				'id'       => 'alchemists__header-search-form-desktop-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Desktop colors', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Search Form on Desktop.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__header-search-form-desktop-bg',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Background Color - Desktop', 'alchemists' ),
				'desc'        => esc_html__( 'Background color for the Search Form in the Header on desktop.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-desktop-border',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Border Color - Desktop', 'alchemists' ),
				'desc'        => esc_html__( 'Border color for the Search Form in the Header on desktop.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-desktop-txt',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Text Color - Desktop', 'alchemists' ),
				'desc'        => esc_html__( 'Text color for the Search Form in the Header on desktop.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-submit-icon-color',
				'type'        => 'link_color',
				'transparent' => false,
				'active'      => false,
				'title'       => esc_html__( 'Search Submit Icon Color - Desktop', 'alchemists' ),
				'desc'        => esc_html__( 'Submit Icon color for the Search Form in the Header on desktop.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__header-search-form-desktop-end',
				'type'     => 'section',
				'indent'   => false,
			),

			// Search Form Styling - Mobile
			array(
				'id'       => 'alchemists__header-search-form-mobile-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Mobile colors', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Search Form on Mobile devices.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__header-search-form-mobile-bg',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Background Color - Mobile', 'alchemists' ),
				'desc'        => esc_html__( 'Background color for the Search Form in the Header on mobile devices.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-mobile-border',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Border Color - Mobile', 'alchemists' ),
				'desc'        => esc_html__( 'Border color for the Search Form in the Header on mobile devices.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-mobile-txt',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Form Text Color - Mobile', 'alchemists' ),
				'desc'        => esc_html__( 'Text color for the Search Form in the Header on mobile devices.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-mobile-submit-icon-color',
				'type'        => 'link_color',
				'transparent' => false,
				'hover'       => false,
				'title'       => esc_html__( 'Search Submit Icon Color - Mobile', 'alchemists' ),
				'desc'        => esc_html__( 'Submit Icon color for the Search Form in the Header on mobile devices.', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__header-search-form-mobile-submit-trigger-icon-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Search Icon Trigger Color - Mobile', 'alchemists' ),
				'desc'        => esc_html__( 'Search Icon Trigger color for the Search Form in the Header on mobile devices.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__header-search-form-mobile-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );

	// Styling: Mobile Header
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Mobile Header', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-mobile-header',
		'fields'     => array(
			// Mobile Header Background Color
			array(
				'id'          => 'alchemists__mobile-header-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Header Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Background color for Header on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Background Color
			array(
				'id'          => 'alchemists__header-primary-mobile-nav-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Background color for Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Burger Menu Color
			array(
				'id'          => 'alchemists__header-primary-mobile-burger-icon-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Burger Icon Color', 'alchemists' ),
				'desc'        => esc_html__( 'Burger Icon displayed on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Links Color
			array(
				'id'          => 'alchemists__header-primary-mobile-link-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Link Color', 'alchemists' ),
				'desc'        => esc_html__( 'Links color in Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Border Color
			array(
				'id'          => 'alchemists__header-primary-mobile-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Border color for Navigation on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav dropdown caret color
			array(
				'id'          => 'alchemists__header-primary-mobile-dropdown-caret-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Dropdown Caret Color', 'alchemists' ),
				'desc'        => esc_html__( 'Dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
				'output'      => array( '.main-nav__toggle::before' ),
			),
			// Mobile Nav dropdown caret background color
			array(
				'id'          => 'alchemists__header-primary-mobile-dropdown-caret-background-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Dropdown Caret Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
				'output'      => array( 'background-color' => '.main-nav__toggle, .main-nav__toggle-2' ),
			),
			// Mobile Nav Submenu Background Color
			array(
				'id'          => 'alchemists__header-primary-mobile-sub-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Submenu Background', 'alchemists' ),
				'desc'        => esc_html__( 'Backgroud color for Navigation Submenu on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Submenu Link Color
			array(
				'id'          => 'alchemists__header-primary-mobile-sub-link-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Submenu Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Links color for Navigation Submenu on mobile devices.', 'alchemists' ),
			),
			// Mobile Nav Submenu dropdown caret color
			array(
				'id'          => 'alchemists__header-primary-mobile-sub-dropdown-caret-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Mobile Nav Submenu Dropdown Caret Color', 'alchemists' ),
				'desc'        => esc_html__( 'Dropdown caret added to navigation item if it has submenu.', 'alchemists' ),
				'output'      => array( '.main-nav__toggle-2::before' ),
			),
			// Header Info Block Link Color - Mobile
			array(
				'id'          => 'alchemists__header-info-link-color-mobile',
				'type'        => 'link_color',
				'active'      => false,
				'title'       => esc_html__( 'Links Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for links in the Header Secondary on mobile devices.', 'alchemists' ),
				'desc'        => esc_html__( 'Changes the color links in the Header on mobile devices.', 'alchemists' ),
			),
			// Header Info Block Cart Summary Color - Mobile
			array(
				'id'          => 'alchemists__header-info-block-cart-sum-color-mobile',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Cart Summary Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Color for the summary in the Header on mobile devices.', 'alchemists' ),
				'desc'        => esc_html__( 'Changes cart summary color in the Header Secondary on mobile devices.', 'alchemists' ),
			),
		)
	) );


	// Styling: Pushy Panel
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Pushy Panel', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-pushy-panel',
		'fields'     => array(
			// Pushy Panel Color
			array(
				'id'        => 'alchemists__pushy-panel-color',
				'type'      => 'select',
				'title'     => esc_html__('Color Scheme', 'alchemists'),
				'subtitle'  => esc_html__('Select Pushy Panel Color Scheme', 'alchemists'),
				'options'   => array(
					'light' => esc_html__( 'Light', 'alchemists' ),
					'dark'  => esc_html__( 'Dark', 'alchemists' ),
				),
				'default'   => 'light'
			),
			// Pushy Panel Custom Color
			array(
				'id'          => 'alchemists__pushy-panel-custom-bg-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Custom Background color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Select your color.', 'alchemists' ),
				'desc'        => esc_html__( 'Selected color changes background color of Pushy Panel.', 'alchemists' ),
				'output'      => array( 'background-color' => '.pushy-panel__inner, .pushy-panel--dark .pushy-panel__inner' ),

			),
		)
	) );


	// Styling: Content
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Content', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-content',
		'fields'     => array(
			// Content Filter
			array(
				'id'          => 'alchemists__content-content-filter',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Content Filter Text Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Set custom color color.', 'alchemists' ),
				'desc'        => esc_html__( 'Content Filter is displayed on Player and Team pages.', 'alchemists' ),
			),
		)
	) );


	// Styling: Blog
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Blog', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-blog',
		'fields'     => array(
			// Categories Group 1 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-1',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 1 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 1 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 1 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-1-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 1 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 1 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-1' ),
				'transparent' => false,
			),
			array(
				'id'          => 'alchemists__blog-cat-group-1-divide',
				'type'        => 'divide',
			),

			// Categories Group 2 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-2',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 2 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 2 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 2 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-2-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 2 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 2 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-2' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-2-divide',
				'type'        => 'divide',
			),

			// Categories Group 3 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-3',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 3 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 3 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 3 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-3-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 3 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 3 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-3' ),
				'transparent' => false,
			),
			array(
				'id'          => 'alchemists__blog-cat-group-3-divide',
				'type'        => 'divide',
			),

			// Categories Group 4 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-4',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 4 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 4 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 4 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-4-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 4 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 4 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-4' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-4-divide',
				'type'        => 'divide',
			),

			// Categories Group 5 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-5',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 5 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 5 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 5 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-5-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 5 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 5 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-5' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-5-divide',
				'type'        => 'divide',
			),

			// Categories Group 6 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-6',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 6 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 6 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 6 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-6-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 6 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 6 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-6' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-6-divide',
				'type'        => 'divide',
			),

			// Categories Group 7 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-7',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 7 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 7 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 7 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-7-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 7 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 7 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-7' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-7-divide',
				'type'        => 'divide',
			),

			// Categories Group 8 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-8',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 8 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 8 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 8 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-8-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 8 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 8 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-8' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-8-divide',
				'type'        => 'divide',
			),

			// Categories Group 9 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-9',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 9 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 9 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 9 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-9-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 9 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 9 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-9' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-9-divide',
				'type'        => 'divide',
			),

			// Categories Group 10 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-10',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 10 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 10 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 10 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-10-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 10 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 10 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-10' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-10-divide',
				'type'        => 'divide',
			),

			// Categories Group 11 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-11',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 11 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 11 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 11 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-11-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 11 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 11 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-11' ),
			),
			array(
				'id'          => 'alchemists__blog-cat-group-11-divide',
				'type'        => 'divide',
			),

			// Categories Group 12 - Background Color
			array(
				'id'          => 'alchemists__blog-cat-group-12',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 12 - Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize background color for Categories Group 12 (post category label and floating action button)', 'alchemists' ),
			),
			// Categories Group 12 - Text Color
			array(
				'id'          => 'alchemists__blog-cat-group-12-txt-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Categories Group 12 - Text Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize text color for Categories Group 12 (post category label)', 'alchemists' ),
				'output'      => array( '.posts__cat-label--category-12' ),
			),
		)
	) );


	// Styling: Footer
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Footer', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__subsection-styling-footer',
		'fields'     => array(
			// Footer Widgets Background
			array(
				'id'          => 'alchemists__footer-widgets-bg',
				'type'        => 'background',
				'transparent' => false,
				'output'      => array('.footer-widgets'),
				'title'       => esc_html__( 'Footer Widgets Background', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Widgets Background.', 'alchemists' ),
			),
			// Footer Widgets Overlay
			array(
				'id'       => 'alchemists__footer-widgets--overlay',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Overlay', 'alchemists' ),
				'subtitle' => esc_html__( 'Adds an additional layer over the background.', 'alchemists' ),
				'desc'     => esc_html__( 'Make sure you added a background image for Footer Widgets if overlay is set to Simple or Duotone.', 'alchemists' ),
				'options' => array(
					'simple'  => esc_html__( 'Simple', 'alchemists' ),
					'duotone' => esc_html__( 'Duotone', 'alchemists' ),
					'off'     => esc_html__( 'Off', 'alchemists' ),
				 ),
				'default' => 'off'
			),
			array(
				'id'        => 'alchemists__footer-widgets--overlay-color',
				'type'      => 'switch',
				'title'     => esc_html__( 'Customize Overlay Color?', 'alchemists' ),
				'subtitle'  => esc_html__( 'Turn on to to customize Posts Overlay Color.', 'alchemists' ),
				'default'   => 0,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array( 'alchemists__footer-widgets--overlay', '=', 'simple' ),
			),
			array(
				'id'          => 'alchemists__footer-widgets--overlay-color-customize',
				'type'        => 'color_rgba',
				'title'       => esc_html__( 'Overlay Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Pick color for overlay.', 'alchemists' ),
				'default'     => array(
					'color'     => '#000',
					'alpha'     => 0.6
				),
				'transparent' => false,
				'required'    => array( 'alchemists__footer-widgets--overlay-color', '=', '1' ),
			),
			// Footer Secondary Background
			array(
				'id'          => 'alchemists__footer-secondary-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Footer Secondary Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Secondary Background Color.', 'alchemists' ),
			),
			// Footer Info Background
			array(
				'id'          => 'alchemists__footer-info-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Footer Info Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Info Background Color.', 'alchemists' ),
			),
			// Footer Side Decoration Background
			array(
				'id'          => 'alchemists__footer-side-decoration-bg',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Footer Side Decoration Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Secondary Background Color.', 'alchemists' ),
			),
			// Footer Secondary Border Top Color
			array(
				'id'          => 'alchemists__footer-secondary-border-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Footer Secondary Top Border Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Secondary Border Top Color.', 'alchemists' ),
				'required'    => array( 'alchemists__footer-secondary-copyright-layout', '=', 'default' ),
			),
			array(
				'id'          => 'alchemists__footer-sponsors-bg-color',
				'type'        => 'color',
				'transparent' => false,
				'title'       => esc_html__( 'Footer Sponsors Background Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize Footer Background Color.', 'alchemists' ),
				'output'      => array( 'background-color' => '.sponsors-wrapper' ),
				'required'  => array( 'alchemists__footer-position', '=', 'before_widgets' ),
			),
			array(
				'id'          => 'alchemists__footer-widgets-list-link-color',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Footer List Widgets Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize link color for Footer List Widgets (Archive, Categories, Meta, Pages, and others).', 'alchemists' ),
				'active'      => false,
			),
			array(
				'id'          => 'alchemists__footer-contact-info-social-link-color',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Contact Info Social Links Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize social link colors for Contact Info Widget.', 'alchemists' ),
				'active'      => false,
			),
			array(
				'id'          => 'alchemists__footer-contact-info-social-icon-color',
				'type'        => 'link_color',
				'title'       => esc_html__( 'Contact Info Social Icon Color', 'alchemists' ),
				'desc'        => esc_html__( 'Customize social info icon colors for Contact Info Widget.', 'alchemists' ),
				'active'      => false,
			),
		)
	) );


	// Typography
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Typography', 'alchemists' ),
		'id'     => 'alchemists__section-typography',
		'icon'   => 'el-icon-font'
	) );


	// Typography: General
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'General', 'alchemists' ),
		'id'     => 'alchemists__subsection-typography-general',
		'subsection' => true,
		'fields' => array(
			array(
				'id'          => 'alchemists__custom_body_font',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Body font?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for the theme main text.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'         => 'alchemists__typography-body',
				'type'       => 'typography',
				'title'      => esc_html__('Body Font', 'alchemists'),
				'subtitle'   => esc_html__('Specify the body font properties.', 'alchemists'),
				'google'     => true,
				'text-align' => false,
				'fonts'      => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'units'      => 'px',
				'required'   => array('alchemists__custom_body_font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_body_font_mobile',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Body font on mobile devices?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for the theme on mobile devices.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__typography-body-mobile',
				'type'        => 'typography',
				'title'       => esc_html__('Body Font - Mobile', 'alchemists'),
				'subtitle'    => esc_html__('Specify the body font properties.', 'alchemists'),
				'google'      => true,
				'text-align'  => false,
				'color'       => false,
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'units'       => 'px',
				'required'    => array('alchemists__custom_body_font_mobile', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_heading_font',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Headings?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts, change color etc. for the theme Headings.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'font-family-accent',
				'type'        => 'typography',
				'title'       => esc_html__('Font Family Accent', 'alchemists'),
				'subtitle'    => esc_html__('Used for headings, titles, tabs, labels etc.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'font-size'   => false,
				'font-weight' => true,
				'font-style'  => true,
				'color'       => false,
				'default'     => array(
					'google'        => true,
				),
				'required'    => array('alchemists__custom_heading_font', '=', '1'),
			),
			array(
				'id'          => 'headings-typography',
				'type'        => 'typography',
				'title'       => esc_html__('Headings', 'alchemists'),
				'subtitle'    => esc_html__('Specify H1-H6 headings font properties.', 'alchemists'),
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'google'      => true,
				'line-height' => false,
				'text-align'  => false,
				'font-size'   => false,
				'font-weight' => true,
				'font-style'  => true,
				'default'     => array(
					'google'        => true,
				),
				'required'    => array('alchemists__custom_heading_font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_heading_fonts_separately',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Headings separately?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to customze each Heading separately.', 'alchemists'),
				'default'     => false,
				'required'    => array('alchemists__custom_heading_font', '=', '1'),
			),
			array(
				'id'          => 'typography-h1',
				'type'        => 'typography',
				'title'       => esc_html__('H1 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H1 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h1'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
			array(
				'id'          => 'typography-h2',
				'type'        => 'typography',
				'title'       => esc_html__('H2 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H2 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h2'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
			array(
				'id'          => 'typography-h3',
				'type'        => 'typography',
				'title'       => esc_html__('H3 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H3 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h3'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
			array(
				'id'          => 'typography-h4',
				'type'        => 'typography',
				'title'       => esc_html__('H4 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H4 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h4'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
			array(
				'id'          => 'typography-h5',
				'type'        => 'typography',
				'title'       => esc_html__('H5 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H5 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h5'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
			array(
				'id'          => 'typography-h6',
				'type'        => 'typography',
				'title'       => esc_html__('H6 Heading', 'alchemists'),
				'subtitle'    => esc_html__('Specify the H6 heading font properties.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'output'      => array('h6'),
				'units'       => 'px',
				'line-height' => false,
				'text-align'  => false,
				'text-transform' => true,
				'required'    => array( 'alchemists__custom_heading_fonts_separately', '=', '1' ),
			),
		)
	) );


	// Typography: Header
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Header', 'alchemists' ),
		'id'     => 'alchemists__subsection-typography-header',
		'subsection' => true,
		'fields' => array(
			// Nav Typography
			array(
				'id'          => 'alchemists__custom_nav-font',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Navigation?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Navigation.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__nav-font',
				'type'        => 'typography',
				'title'       => esc_html__('Navigation Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the main navigation.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'required'    => array('alchemists__custom_nav-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__nav-font-sub',
				'type'        => 'typography',
				'title'       => esc_html__('Navigation Dropdown Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the main navigation.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'required'    => array('alchemists__custom_nav-font', '=', '1'),
			),
			// Mobile Nav Typography
			array(
				'id'          => 'alchemists__custom_nav-mobile-font',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Nav Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the main navigation on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_nav-mobile-font-submenu',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Nav Sub Menu Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the main navigation submenu on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_nav-mobile-font-subsubmenu',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Nav Sub-Sub Menu Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the main navigation subsubmenu on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-font', '=', '1'),
			),

			// Nav Secondary Typography
			array(
				'id'          => 'alchemists__custom_nav-secondary-font',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Secondary Navigation?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Navigation.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__nav-secondary-font-heading',
				'type'        => 'typography',
				'title'       => esc_html__('Secondary Navigation Heading Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation heading.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'output'      => array( '.secondary-nav__heading' ),
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__nav-secondary-font-toggle',
				'type'        => 'typography',
				'title'       => esc_html__('Secondary Navigation Toggle Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation toggle. Displayed only on mobile and tablet devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'output'      => array( '.secondary-nav__toggle' ),
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__nav-secondary-font',
				'type'        => 'typography',
				'title'       => esc_html__('Secondary Navigation Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__nav-secondary-font-sub',
				'type'        => 'typography',
				'title'       => esc_html__('Secondary Navigation Dropdown Typography', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => true,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			// Mobile Nav Secondary Typography
			array(
				'id'          => 'alchemists__custom_nav-secondary-heading-mobile-font',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Secondary Nav Heading Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the heading in secondary navigation on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			// Mobile Nav Typography
			array(
				'id'          => 'alchemists__custom_nav-secondary-mobile-font',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Secondary Nav Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_nav-secondary-mobile-font-submenu',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Secondary Nav Sub Menu Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation submenu on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom_nav-secondary-mobile-font-subsubmenu',
				'type'        => 'typography',
				'title'       => esc_html__('Mobile Secondary Nav Sub-Sub Menu Font Size', 'alchemists'),
				'subtitle'    => esc_html__('Used for the secondary navigation subsubmenu on mobile devices.', 'alchemists'),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'font-family' => false,
				'font-style'  => false,
				'font-weight' => false,
				'line-height' => false,
				'text-align'  => false,
				'color'       => false,
				'text-transform' => false,
				'preview'     => false,
				'required'    => array('alchemists__custom_nav-secondary-font', '=', '1'),
			),
		)
	) );

	// Typography: Page Heading
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Page Heading', 'alchemists' ),
		'id'     => 'alchemists__subsection-typography-page-heading',
		'subsection' => true,
		'fields' => array(
			// Page Heading: Title
			array(
				'id'       => 'alchemists__custom-page-title-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Title', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize the Title typography inside Page Heading.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__custom-page-title-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Page Title?', 'alchemists'),
				'subtitle'    => esc_html__( 'Turn on to customize Page Title typography.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__custom-page-title',
				'type'        => 'typography',
				'title'       => esc_html__('Page Title', 'alchemists'),
				'subtitle'    => esc_html__('Used on page between Header and Content.', 'alchemists'),
				'output'      => array( '.page-heading__title' ),
				'font-family' => false,
				'google'      => false,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-transform' => true,
				'required'    => array('alchemists__custom-page-title-on', '=', '1'),
			),
			array(
				'id'       => 'alchemists__custom-page-title-end',
				'type'     => 'section',
				'indent'   => false,
			),

			// Page Heading: Hero Unit - Static
			array(
				'id'       => 'alchemists__typography-hero-unit-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Hero Unit - Static', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize typgoraphy for the Hero Unit - Static.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__typography-hero-unit-title',
				'type'        => 'typography',
				'title'       => esc_html__('Title', 'alchemists'),
				'subtitle'    => esc_html__( 'Customize typography for the Title.', 'alchemists' ),
				'output'      => array( '.hero-unit__title' ),
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'color'       => false,
				'google'      => true,
				'text-transform' => true,
			),
			array(
				'id'          => 'alchemists__typography-hero-unit-subtitle',
				'type'        => 'typography',
				'title'       => esc_html__('Subtitle', 'alchemists'),
				'subtitle'    => esc_html__( 'Customize typography for the Subtitle.', 'alchemists' ),
				'output'      => array( '.hero-unit__subtitle' ),
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'color'       => false,
				'google'      => true,
				'text-transform' => true,
			),
			array(
				'id'          => 'alchemists__typography-hero-unit-desc',
				'type'        => 'typography',
				'title'       => esc_html__('Description', 'alchemists'),
				'subtitle'    => esc_html__( 'Customize typography for the Description.', 'alchemists' ),
				'output'      => array( '.hero-unit__desc' ),
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'color'       => false,
				'google'      => true,
				'text-transform' => true,
			),
			array(
				'id'       => 'alchemists__typography-hero-unit-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );

	// Typography: Content
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Content', 'alchemists' ),
		'id'     => 'alchemists__subsection-typography-content',
		'subsection' => true,
		'fields' => array(
			// Post Titles
			array(
				'id'          => 'alchemists__custom-post-titles-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Post Titles?', 'alchemists'),
				'subtitle'    => esc_html__( 'Enables option to customize Post Titles.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__custom-post-titles',
				'type'        => 'typography',
				'title'       => esc_html__( 'Post Titles', 'alchemists' ),
				'subtitle'    => esc_html__( 'Post Titles used in various widgets and elements.', 'alchemists' ),
				'output'      => array( '.posts__title' ),
				'font-family' => false,
				'text-align'  => false,
				'color'       => false,
				'font-size'   => false,
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-transform' => true,
				'required'    => array('alchemists__custom-post-titles-on', '=', '1'),
			),

			// Card Titles
			array(
				'id'          => 'alchemists__custom-card-titles-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Card Titles?', 'alchemists'),
				'subtitle'    => esc_html__( 'Enables option to customize Card Titles.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__custom-card-titles',
				'type'        => 'typography',
				'title'       => esc_html__( 'Card Titles', 'alchemists' ),
				'subtitle'    => esc_html__( 'Card Titles used in boxes, widgets and elements.', 'alchemists' ),
				'output'      => array( '.card__header > h4' ),
				'text-align'  => false,
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-transform' => true,
				'required'    => array('alchemists__custom-card-titles-on', '=', '1'),
			),

			// Table
			array(
				'id'          => 'alchemists__custom-table-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Table Base Font?', 'alchemists'),
				'subtitle'    => esc_html__( 'Enables option to customize Table Font.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__custom-table',
				'type'        => 'typography',
				'title'       => esc_html__( 'Table Base Font', 'alchemists' ),
				'subtitle'    => esc_html__( 'Sets the base typography options for tablets and mobiles.', 'alchemists' ),
				'output'      => array( 'table, .table, .table > thead > tr > th, table > thead > tr > td, table > tbody > tr > th, table > tbody > tr > td, table > tfoot > tr > th, table > tfoot > tr > td, .table > thead > tr > th, .table > thead > tr > td, .table > tbody > tr > th, .table > tbody > tr > td, .table > tfoot > tr > th, .table > tfoot > tr > td '),
				'text-align'  => false,
				'line-height' => false,
				'color'       => false,
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-transform' => true,
				'required'    => array('alchemists__custom-table-on', '=', '1'),
			),
			array(
				'id'          => 'alchemists__custom-table-desktop',
				'type'        => 'typography',
				'title'       => esc_html__( 'Table Font Size - Desktop', 'alchemists' ),
				'subtitle'    => esc_html__( 'Sets the font size on desktop.', 'alchemists' ),
				'text-align'  => false,
				'font-family' => false,
				'font-weight' => false,
				'font-style'  => false,
				'line-height' => false,
				'color'       => false,
				'google'      => true,
				'text-transform' => false,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'required'    => array('alchemists__custom-table-on', '=', '1'),
			),
		)
	) );

	// Typography: Footer
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Footer', 'alchemists' ),
		'id'     => 'alchemists__subsection-typography-footer',
		'subsection' => true,
		'fields' => array(
			// Footer Widget Title
			array(
				'id'          => 'alchemists__custom_footer-widget-titles',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Footer Widget Titles?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Footer Widget Titles.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__footer-widget-titles',
				'type'        => 'typography',
				'title'       => esc_html__( 'Footer Widget Title', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used for the Widget Titles in the Footer.', 'alchemists'),
				'output'      => array( '.widget--footer .widget__title' ),
				'google'      => true,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-transform' => true,
				'required'    => array('alchemists__custom_footer-widget-titles', '=', '1'),
			),
			// Footer Widget Text
			array(
				'id'          => 'alchemists__custom_footer-widget-txt',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Footer Widget Text?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Footer Widget Text.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'             => 'alchemists__footer-widget-txt',
				'type'           => 'typography',
				'title'          => esc_html__( 'Footer Widget Text', 'alchemists' ),
				'subtitle'       => esc_html__( 'Used for Footer Widget text.', 'alchemists'),
				'output'         => array( '.widget--footer .widget__content' ),
				'google'         => true,
				'fonts'          => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-align'     => false,
				'text-transform' => true,
				'required'       => array('alchemists__custom_footer-widget-txt', '=', '1'),
			),
			// Footer Widget List
			array(
				'id'          => 'alchemists__custom_footer-widget-lists',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Footer Widget List Widgets?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Footer Widget List Widgets such as Archive, Navigation, Meta, Pages, and others.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'          => 'alchemists__footer-widget-list',
				'type'        => 'typography',
				'title'       => esc_html__( 'Footer Widget List Widgets', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used for Footer Widget Lists.', 'alchemists'),
				'output'      => array( '.widget_archive.widget--footer ul li, .widget_nav_menu.widget--footer ul > li, .widget_meta.widget--footer ul > li, .widget_pages.widget--footer ul > li, .widget_recent_comments.widget--footer ul > li, .widget_recent_entries.widget--footer ul > li, .widget_categories.widget--footer ul > li, .widget_rss.widget--footer ul > li, .widget_product_categories.widget--footer ul > li' ),
				'google'      => true,
				'text-align'  => false,
				'color'       => false,
				'fonts'       => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-align' => false,
				'text-transform' => true,
				'required'    => array('alchemists__custom_footer-widget-lists', '=', '1'),
			),
			// Footer Copyright Text
			array(
				'id'          => 'alchemists__custom_footer-copyright-txt',
				'type'        => 'switch',
				'title'       => esc_html__('Customize Copyright Text?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to use custom fonts for Footer Copyright Text.', 'alchemists'),
				'default'     => false,
			),
			array(
				'id'             => 'alchemists__footer-copyright-txt',
				'type'           => 'typography',
				'title'          => esc_html__( 'Copyright Text', 'alchemists' ),
				'subtitle'       => esc_html__( 'Used for Copyright text.', 'alchemists'),
				'output'         => array( '.footer-copyright' ),
				'google'         => true,
				'fonts'          => apply_filters( 'alchemists_typography_custom_fonts', array() ),
				'text-align'     => false,
				'text-transform' => true,
				'required'       => array('alchemists__custom_footer-copyright-txt', '=', '1'),
			),
		)
	) );


	// Page Preloader Options
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Preloader', 'alchemists' ),
		'id'     => 'alchemists__section-pageloader',
		'icon'   => 'el el-repeat',
		'fields' => array(
			array(
				'id'        => 'alchemists__opt-pageloader',
				'type'      => 'switch',
				'title'     => esc_html__('Use Preloader?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to use page pre-loader.', 'alchemists'),
				'desc'      => esc_html__('If turned on you will see spinner before content will be shown.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'                    => 'alchemists__opt-preloader-bg',
				'type'                  => 'color',
				'title'                 => esc_html__('Preloader Background Color', 'alchemists'),
				'subtitle'              => esc_html__('Choose background color your pre-loader screen.', 'alchemists'),
				'transparent'           => false,
				'required'              => array(
					array('alchemists__opt-pageloader', '=', '1'),
				),
			),
			array(
				'id'             => 'alchemists__opt-preloader-size',
				'type'           => 'dimensions',
				'units'          => array( 'px' ),
				'units_extended' => 'false',
				'title'          => esc_html__( 'Spinner Size', 'alchemists' ),
				'subtitle'       => esc_html__( 'Set up the spinner size.', 'alchemists' ),
				'desc'           => esc_html__( 'Spinner size can be set in px.', 'alchemists' ),
				'height'         => false,
				'mode'           => array(
					'width'  => true,
					'height' => false,
				),
				'required'  => array(
					array('alchemists__opt-pageloader', '=', '1'),
				),
			),
			array(
				'id'          => 'alchemists__opt-preloader-color',
				'type'        => 'color',
				'title'       => esc_html__( 'Spinning Part Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Choose color for spinning part.', 'alchemists' ),
				'transparent' => false,
				'required'    => array(
					array('alchemists__opt-pageloader', '=', '1'),
					array('alchemists__opt-preloader-img', '!=', '1'),
				),
			),
			array(
				'id'          => 'alchemists__opt-preloader-color-secondary',
				'type'        => 'color',
				'title'       => esc_html__( 'Spinner Bar Color', 'alchemists' ),
				'subtitle'    => esc_html__( 'Choose color for spinning bar.', 'alchemists' ),
				'transparent' => false,
				'required'    => array(
					array('alchemists__opt-pageloader', '=', '1'),
					array('alchemists__opt-preloader-img', '!=', '1'),
				),
			),
			array(
				'id'       => 'alchemists__opt-preloader-spin-duration',
				'type'     => 'slider',
				'title'    => esc_html__( 'Spinner Animation Duration', 'alchemists' ),
				'desc'     => esc_html__( 'Duration in seconds, Min: 0, max: 2, step: .1, default value: .8.', 'alchemists' ),
				'default'  => 0.8,
				'min'      => 0,
				'step'     => 0.1,
				'max'      => 2,
				'resolution' => 0.1,
				'display_value' => 'text',
				'required'    => array(
					array('alchemists__opt-pageloader', '=', '1'),
					array('alchemists__opt-preloader-img', '!=', '1'),
				),
			),
			array(
				'id'        => 'alchemists__opt-preloader-img',
				'type'      => 'switch',
				'title'     => esc_html__('Use Image Spinner?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to upload image spinner.', 'alchemists'),
				'default'   => false,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
				'required'  => array(
					array('alchemists__opt-pageloader', '=', '1'),
				),
			),
			array(
				'id'        => 'alchemists__opt-preloader-img-url',
				'type'      => 'media',
				'url'       => true,
				'title'     => esc_html__('Spinner Image', 'alchemists'),
				'subtitle'  => esc_html__('Used for animated image (e.g. GIF)', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
				'required'  => array(
					array('alchemists__opt-pageloader', '=', '1'),
					array('alchemists__opt-preloader-img', '=', '1'),
				),
			),
		)
	) );


	// 404 Page Options
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( '404 Page', 'alchemists' ),
		'id'     => 'alchemists__section-404-page',
		'icon'   => 'el el-error',
		'fields' => array(
			array(
				'id'        => 'alchemists__opt-404-image',
				'type'      => 'media',
				'url'       => true,
				'title'     => esc_html__('Page 404 Image', 'alchemists'),
				'subtitle'  => esc_html__('This image displayed only on Page 404.', 'alchemists'),
				'compiler'  => 'true',
				'desc'      => esc_html__('Upload your image or specify the image URL.', 'alchemists'),
			),
			array(
				'id'       => 'alchemists__opt-404-page-heading',
				'type'     => 'text',
				'title'    => esc_html__( 'Page Header Title', 'alchemists' ),
				'subtitle' => esc_html__( 'Change 404 Page Header Title.', 'alchemists' ),
				'desc'     => esc_html__( 'This text appears below the Header.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__opt-404-page-title',
				'type'     => 'text',
				'title'    => esc_html__( 'Title', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Page 404 Title.', 'alchemists' ),
				'desc'     => esc_html__( 'This text appears above 404 image.', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__opt-404-page-subtitle',
				'type'     => 'text',
				'title'    => esc_html__( 'Subtitle', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Page 404 Subtitle.', 'alchemists' ),
				'desc'     => esc_html__( 'This text appears above 404 Title.', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-404-desc',
				'type'      => 'textarea',
				'title'     => esc_html__( 'Description', 'alchemists' ),
				'subtitle'  => esc_html__( 'Add some description. HTML tags are allowed.', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__opt-404-btn',
				'type'      => 'switch',
				'title'     => esc_html__('Show Primary Button?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to display primary button.', 'alchemists'),
				'desc'      => esc_html__('This button linked to main page.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__opt-404-btn-txt',
				'type'     => 'text',
				'title'    => esc_html__( 'Primary Button Text', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Primary Button text.', 'alchemists' ),
				'desc'     => esc_html__( 'Primary button linked to the Home page.', 'alchemists' ),
				'required' => array('alchemists__opt-404-btn', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-404-btn-link',
				'type'     => 'text',
				'title'    => esc_html__( 'Primary Button URL', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Primary Button URL.', 'alchemists' ),
				'required' => array('alchemists__opt-404-btn', '=', '1'),
			),
			array(
				'id'        => 'alchemists__opt-404-btn-secondary',
				'type'      => 'switch',
				'title'     => esc_html__('Show Secondary Button?', 'alchemists'),
				'subtitle'  => esc_html__('Turn on to display secondary button.', 'alchemists'),
				'desc'      => esc_html__('You can link this button any page.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__( 'Yes', 'alchemists' ),
				'off'       => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'       => 'alchemists__opt-404-btn-secondary-txt',
				'type'     => 'text',
				'title'    => esc_html__( 'Secondary Button Text', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Secondary Button text.', 'alchemists' ),
				'desc'     => esc_html__( 'Secondary button linked to the Blog page.', 'alchemists' ),
				'required' => array('alchemists__opt-404-btn-secondary', '=', '1'),
			),
			array(
				'id'       => 'alchemists__opt-404-btn-secondary-link',
				'type'     => 'text',
				'title'    => esc_html__( 'Secondary Button URL', 'alchemists' ),
				'subtitle' => esc_html__( 'Change Secondary Button URL.', 'alchemists' ),
				'required' => array('alchemists__opt-404-btn-secondary', '=', '1'),
			),
		)
	) );

	// SportsPress Options
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__( 'SportsPress', 'alchemists' ),
		'id'        => 'alchemists__section-sportspress',
		'icon_type' => 'image',
		'icon'      => get_template_directory_uri() . '/admin/images/icon-sportspress.svg',
	) );


	// Event
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Event', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__section-sp-event',
		'fields' => array(
			array(
				'id'       => 'alchemists__player-sp-event-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Event Scoreboard', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize event scoreboard.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'          => 'alchemists__player-sp-event-progress-bars',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Progress Bars?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize progress bars.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize event results displayed as progress bars.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__player-sp-event-progress-bars-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Progress Bars', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display progress bars', 'alchemists' ),
				'placeholder' => esc_html__( 'Select performances...', 'alchemists' ),
				'desc'        => esc_html__( 'Select performances to display as progress bars.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_performance',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-sp-event-progress-bars', '=', '1' ),
			),

			array(
				'id'          => 'alchemists__player-sp-event-circular-bars',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Circular Bars?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize circular bars.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize event results displayed as circular bars.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__player-sp-event-circular-bars-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Circular Bars', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display circular bars', 'alchemists' ),
				'placeholder' => esc_html__( 'Select performances...', 'alchemists' ),
				'desc'        => esc_html__( 'Select performances to display as circular bars.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_performance',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-sp-event-circular-bars', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__player-sp-event-circular-bars-custom-format',
				'type'        => 'select',
				'title'       => esc_html__( 'Circular Bars Field Format', 'alchemists' ),
				'subtitle'    => esc_html__( 'Select a proper field format.', 'alchemists' ),
				'desc'        => esc_html__( 'This helps to recognize field format and display a proper circular bar. For example, if you want to display percent based value such accuracy etc. select Percentage and for other field types select Number.', 'alchemists' ),
				'options'   => array(
					'number'     => esc_html__( 'Number', 'alchemists' ),
					'percentage' => esc_html__( 'Percentage', 'alchemists' ),
				),
				'required'    => array( 'alchemists__player-sp-event-circular-bars', '=', '1' ),
			),

			array(
				'id'          => 'alchemists__player-sp-event-game-stats',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Stats Table?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize event stats table.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize event stats table.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only alchemists-field--football-only',
			),
			array(
				'id'          => 'alchemists__player-sp-event-game-stats-title',
				'type'        => 'text',
				'title'       => esc_html__( 'Stats Table Title', 'alchemists' ),
				'desc'        => esc_html__( 'Enter your title.', 'alchemists' ),
				'required'    => array( 'alchemists__player-sp-event-game-stats', '=', '1' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only alchemists-field--football-only',
			),
			array(
				'id'          => 'alchemists__player-sp-event-game-stats-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Stats Table', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats table', 'alchemists' ),
				'placeholder' => esc_html__( 'Select performances...', 'alchemists' ),
				'desc'        => esc_html__( 'Select performances to display in the stats table.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_performance',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-sp-event-game-stats', '=', '1' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only alchemists-field--football-only',
			),
			array(
				'id'       => 'alchemists__player-sp-event-end',
				'type'     => 'section',
				'indent'   => false,
			),
			array(
				'id'       => 'alchemists__player-sp-event-timeline-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Event Timeline', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize event timeline.', 'alchemists' ),
				'indent'   => true,
				'class'       => 'alchemists-field alchemists-field--soccer-only',
			),
			array(
				'id'          => 'alchemists__player-sp-event-timeline',
				'type'        => 'switch',
				'title'       => esc_html__( 'Display Timeline?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to display Event Timeline.', 'alchemists' ),
				'desc'        => esc_html__( 'Event Timeline displayed on single event page.', 'alchemists' ),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only',
			),
			array(
				'id'        => 'alchemists__player-sp-event-game-timeline-type',
				'type'      => 'select',
				'title'     => esc_html__('Timeline Layout', 'alchemists'),
				'subtitle'  => esc_html__('Select the timeline layout.', 'alchemists'),
				'desc'      => esc_html__('This general option and used on Single Event pages.', 'alchemists'),
				'options'   => array(
					'horizontal' => esc_html__( 'Horizontal', 'alchemists' ),
					'vertical'   => esc_html__( 'Vertical', 'alchemists' ),
				),
				'default'   => 'horizontal',
				'class'     => 'alchemists-field alchemists-field--soccer-only',
				'required'  => array( 'alchemists__player-sp-event-timeline', '=', '1' ),
			),
			array(
				'id'       => 'alchemists__player-sp-event-timeline-end',
				'type'     => 'section',
				'indent'   => false,
				'class'       => 'alchemists-field alchemists-field--soccer-only',
			),

		)
	) );

	// Team
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Team', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__section-sp-team',
		'fields' => array(
			array(
				'id'       => 'alchemists__team-nav-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Team Navigation', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize team subpages.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__team-nav-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Navigation Type', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select type for the team navigation.', 'alchemists' ),
				'options'   => array(
					'fullwidth' => esc_html__( 'Full Width', 'alchemists' ),
					'boxed'     => esc_html__( 'Boxed', 'alchemists' ),
				),
				'default'   => 'fullwidth'
			),
			array(
				'id'        => 'alchemists__team-subpages',
				'type'      => 'sorter',
				'title'     => esc_html__( 'Team Subpages Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Organize Team Subpages order.', 'alchemists' ),
				'desc'      => esc_html__( 'Organize how you want the team subpages to appear on a single team page.', 'alchemists' ),
				'compiler'  => 'true',
				'options'   => array(
					'enabled'   => array(
						'overview'  => esc_html__( 'Overview', 'alchemists' ),
						'roster'    => esc_html__( 'Roster', 'alchemists' ),
						'standings' => esc_html__( 'Standings', 'alchemists' ),
						'results'   => esc_html__( 'Latest Results', 'alchemists' ),
						'schedule'  => esc_html__( 'Schedule', 'alchemists' ),
						'gallery'   => esc_html__( 'Gallery', 'alchemists' ),
					),
					'disabled'  => array(),
				),
			),
			array(
				'id'        => 'alchemists__team-subpages-label--overview',
				'type'      => 'text',
				'title'     => esc_html__( 'Overview - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Overview Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Overview', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--overview',
				'type'      => 'text',
				'title'     => esc_html__( 'Overview - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Overview Team subpage.', 'alchemists' ),
				'default'   => 'overview',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__team-subpages-label--roster',
				'type'      => 'text',
				'title'     => esc_html__( 'Roster - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Roster Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Roster', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--roster',
				'type'      => 'text',
				'title'     => esc_html__( 'Roster - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Roster Team subpage.', 'alchemists' ),
				'default'   => 'roster',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__team-subpages-label--standings',
				'type'      => 'text',
				'title'     => esc_html__( 'Standings - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Standings Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Standings', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--standings',
				'type'      => 'text',
				'title'     => esc_html__( 'Standings - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Standings Team subpage.', 'alchemists' ),
				'default'   => 'standings',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__team-subpages-label--results',
				'type'      => 'text',
				'title'     => esc_html__( 'Latest Results - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Latest Results Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Latest Results', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--results',
				'type'      => 'text',
				'title'     => esc_html__( 'Results - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Results Team subpage.', 'alchemists' ),
				'default'   => 'results',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__team-subpages-label--schedule',
				'type'      => 'text',
				'title'     => esc_html__( 'Schedule - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Schedule Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Schedule', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--schedule',
				'type'      => 'text',
				'title'     => esc_html__( 'Schedule - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Schedule Team subpage.', 'alchemists' ),
				'default'   => 'schedule',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__team-subpages-label--gallery',
				'type'      => 'text',
				'title'     => esc_html__( 'Gallery - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Gallery Team subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Gallery', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__team-subpages-slug--gallery',
				'type'      => 'text',
				'title'     => esc_html__( 'Gallery - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Gallery Team subpage.', 'alchemists' ),
				'default'   => 'gallery',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'          => 'alchemists__team-subpages-label--subtitle',
				'type'        => 'switch',
				'title'       => esc_html__( 'Display Secondary Label?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Secondary label displayed by default.', 'alchemists' ),
				'desc'        => esc_html__( 'Short text appears above each subpage label.', 'alchemists' ),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__team-subpages-label--subtitle-custom',
				'type'        => 'text',
				'title'       => esc_html__( 'Secondary Label', 'alchemists' ),
				'desc'        => esc_html__( 'Enter your secondary label, \'The Team\' by default.', 'alchemists' ),
				'required'    => array( 'alchemists__team-subpages-label--subtitle', '=', '1' ),
			),
			array(
				'id'       => 'alchemists__team-nav-end',
				'type'     => 'section',
				'indent'   => false,
			),
			// Permalinks Notice
			array(
				'id'        => 'alchemists__team-subpages-notice--slug',
				'type'      => 'info',
				'notice'    => true,
				'icon'      => 'el el-icon-warning-sign',
				'style'     => 'warning',
				'title'     => esc_html__('Change Permalinks', 'alchemists'),
				'desc'      => __('After changing slugs go to <strong>Settings > Permalinks</strong> and click on <strong>Save Changes</strong> button.', 'alchemists')
			),
			array(
				'id'       => 'alchemists__team-results-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Team Results', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Team Results subpages.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__team-results-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Team Results Layout', 'alchemists'),
				'subtitle'  => esc_html__( 'Select layout for Results on the Team Results pages.', 'alchemists'),
				'options'   => array(
					'list'    => esc_html__( 'List', 'alchemists' ),
					'blocks'  => esc_html__( 'Blocks', 'alchemists' ),
					'blocks-custom' => esc_html__( 'Blocks Small', 'alchemists' ),
				),
				'default'   => 'list'
			),
			array(
				'id'       => 'alchemists__team-results-end',
				'type'     => 'section',
				'indent'   => false,
			),
			array(
				'id'       => 'alchemists__team-schedule-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Team Schedule', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Team Schedule subpages.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__team-schedule-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Team Schedule Layout', 'alchemists'),
				'subtitle'  => esc_html__( 'Select layout for Schedule on the Team Schedule pages.', 'alchemists'),
				'options'   => array(
					'list'    => esc_html__( 'List', 'alchemists' ),
					'blocks'  => esc_html__( 'Blocks', 'alchemists' ),
					'blocks-custom' => esc_html__( 'Blocks Small', 'alchemists' ),
				),
				'default'   => 'list'
			),
			array(
				'id'       => 'alchemists__team-schedule-end',
				'type'     => 'section',
				'indent'   => false,
			),
			array(
				'id'       => 'alchemists__team-gallery-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Team Gallery', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Team Gallery subpages.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__team-gallery-type',
				'type'      => 'select',
				'title'     => esc_html__( 'Team Gallery Layout', 'alchemists'),
				'subtitle'  => esc_html__( 'Select layout for Gallery on the Team Gallery pages.', 'alchemists'),
				'options'   => array(
					'img_top'    => esc_html__( 'Image Top + Title Bottom', 'alchemists' ),
					'img_bottom' => esc_html__( 'Title Top + Image Bottom', 'alchemists' ),
					'img_thumb'  => esc_html__( 'Image Top + Thumbs + Title Bottom', 'alchemists' ),
				),
				'default'   => 'img_top'
			),
			array(
				'id'       => 'alchemists__team-gallery-end',
				'type'     => 'section',
				'indent'   => false,
			),
		)
	) );

	// Player
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Player', 'alchemists' ),
		'subsection' => true,
		'id'         => 'alchemists__section-sp-player',
		'fields' => array(

			// Player -- Header
			array(
				'id'       => 'alchemists__player-title-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Single Player Header', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Single Player Header.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__player-page-header-layout',
				'type'      => 'select',
				'title'     => esc_html__( 'Player Header Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Select the player header layout.', 'alchemists' ),
				'options'   => array(
					'fullwidth' => esc_html__( 'Full Width', 'alchemists' ),
					'boxed'     => esc_html__( 'Boxed', 'alchemists' ),
					'none'      => esc_html__( 'None', 'alchemists' ),
				),
				'default'   => 'fullwidth'
			),

			// Player Header Layout - Boxed
			array(
				'id'          => 'alchemists__player-title-player-bg-on',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Player Background Image?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to custom background image.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables options to customize Single Player Background Image.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'boxed'),
			),
			array(
				'id'               => 'alchemists__player-title-player-bg',
				'title'            => esc_html__( 'Single Player Background', 'alchemists' ),
				'type'             => 'background',
				'background-color' => false,
				'output'           => array('.team-roster--boxed .team-roster__player-shape--default .team-roster__player-shape-inner'),
				'required'         => array('alchemists__player-title-player-bg-on', '=', '1'),
			),
			array(
				'id'          => 'alchemists__player-title-stats-boxed',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Circular Bar Statistics?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize player circular bar stats.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize stats for Single Player Page.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'boxed'),
			),
			array(
				'id'          => 'alchemists__player-title-stats-custom-boxed',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Statistics Circular Bars', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display circular bars etc.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select player statistics to display in the Single Player Header.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_statistic',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-stats-boxed', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__player-title-progress-bars-boxed',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Progress Bars?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize progress bar statistics.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize progress bar statistics for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'boxed'),
			),
			array(
				'id'          => 'alchemists__player-title-progress-bars-custom-boxed',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Progress Bars Statistics', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats with progress bars.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select 2-4 items. Select only percentage statistic to get proper bars.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_statistic',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-progress-bars-boxed', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__player-title-stat-cards-boxed',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Statistic Cards?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize statistic cards.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize statistic cards under the primary Player Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'boxed'),
			),
			array(
				'id'          => 'alchemists__player-title-stat-cards-custom-boxed',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Statistic Cards', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats with cards.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select 2-4 items to display them as cards.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_statistic',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-stat-cards-boxed', '=', '1' ),
			),
			array(
				'id'        => 'alchemists__player-title-stat-cards-cols-boxed',
				'type'      => 'select',
				'title'     => esc_html__('Statistic Cards Columns', 'alchemists'),
				'subtitle'  => esc_html__('Select the numbe of columns for the Statistic Cards.', 'alchemists'),
				'options'   => array(
					'2cols' => esc_html__( '2 Columns', 'alchemists' ),
					'3cols' => esc_html__( '3 Columns', 'alchemists' ),
					'4cols' => esc_html__( '4 Columns', 'alchemists' ),
				),
				'default'   => '4cols',
				'required'    => array('alchemists__player-page-header-layout', '=', 'boxed'),
			),

			// Player Header Layout - Full Width
			array(
				'id'          => 'alchemists__player-title-custom',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Page Heading Background?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to custom background image, colors.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables options to customize Single Player Page Heading Background.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-background',
				'type'        => 'background',
				'output'      => array('.player-heading'),
				'title'       => esc_html__('Single Player Page Heading Background', 'alchemists'),
				'required'    => array('alchemists__player-title-custom', '=', '1'),
			),
			array(
				'id'          => 'alchemists__player-title-overlay-on',
				'type'        => 'switch',
				'title'       => esc_html__('Add Overlay on Single Player Page Heading?', 'alchemists'),
				'subtitle'    => esc_html__('Turn on to add color overlay.', 'alchemists'),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-title-custom', '=', '1'),
			),
			array(
				'id'          => 'alchemists__player-title-overlay',
				'type'        => 'background',
				'output'      => array('.player-heading::after'),
				'title'       => esc_html__('Single Player Page Heading Overlay', 'alchemists'),
				'subtitle'    => esc_html__('Adds color or/and image overlay.', 'alchemists'),
				'required'    => array(
					'alchemists__player-title-overlay-on', '=', '1',
				),
			),
			array(
				'id'       => 'alchemists__player-title-overlay-opacity',
				'type'     => 'slider',
				'title'    => esc_html__( 'Single Player Page Heading Overlay Opacity', 'alchemists' ),
				'subtitle' => esc_html__( 'Option to change logo opacity.', 'alchemists' ),
				'desc'     => esc_html__( 'Drag the slider to adjust the overlay opacity in %', 'alchemists' ),
				'default'  => 70,
				'min'      => 5,
				'step'     => 5,
				'max'      => 100,
				'display_value' => 'text',
				'required'    => array(
					'alchemists__player-title-overlay-on', '=', '1'
				),
			),
			array(
				'id'        => 'alchemists__player-info-layout',
				'type'      => 'select',
				'title'     => esc_html__('Player Header Info Details Layout', 'alchemists'),
				'subtitle'  => esc_html__('Select the player info layout displayed in the Page Heading.', 'alchemists'),
				'options'   => array(
					'horizontal' => esc_html__( 'Horizontal', 'alchemists' ),
					'vertical'   => esc_html__( 'Vertical', 'alchemists' ),
				),
				'default'   => 'horizontal',
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-metrics',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Player Metrics?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize player metrics.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize metrics for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-metrics-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Metrics', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display player metrics', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select player metrics to display in the Single Player Header.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_metric',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array(
					'alchemists__player-title-metrics', '=', '1',
				),
			),

			array(
				'id'          => 'alchemists__player-title-stats',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Circular Bar Statistics?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize player circular bar stats.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize stats for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-stats-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Statistics Circular Bars', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display circular bars etc.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select player statistics to display in the Single Player Header.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => 'sp_statistic',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-stats', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__player-title-performance',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Performances & Stats?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize player performances & stats.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize performances & stats for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'class'       => 'alchemists-field alchemists-field--soccer alchemists-field--soccer-only',
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-performance-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Performances & Stats', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats in simple format (label, value).', 'alchemists' ),
				'placeholder' => esc_html__( 'Select performance...', 'alchemists' ),
				'desc'        => esc_html__( 'Select player performance to display in the Single Player Header.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => array( 'sp_performance', 'sp_statistic' ),
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-performance', '=', '1' ),
				'class'       => 'alchemists-field alchemists-field--soccer alchemists-field--soccer-only',
			),

			array(
				'id'          => 'alchemists__player-title-radar',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Radar Chart Statistics?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize radar chart performances.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize radar chart performances for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
				'class'       => 'alchemists-field alchemists-field--basketball alchemists-field--basketball-only alchemists-field--esports-only',
			),
			array(
				'id'          => 'alchemists__player-title-radar-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Radar Performances & Statistics', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats with radar chart.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select performance...', 'alchemists' ),
				'desc'        => esc_html__( 'Recommended to select 5 or more items. Depends on the number of selected items the radar\'s shape will be different (pentagon, hexagon etc).', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => array( 'sp_performance', 'sp_statistic' ),
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-radar', '=', '1' ),
				'class'       => 'alchemists-field alchemists-field--basketball alchemists-field--basketball-only alchemists-field--esports-only',
			),

			array(
				'id'          => 'alchemists__player-title-progress-bars',
				'type'        => 'switch',
				'title'       => esc_html__( 'Customize Progress Bars?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Turn on to customize progress bar statistics.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables option to customize progress bar statistics for Single Player Page Header.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only alchemists-field--football-only',
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'          => 'alchemists__player-title-progress-bars-custom',
				'type'        => 'select',
				'multi'       => true,
				'title'       => esc_html__( 'Player Progress Bars Statistics', 'alchemists' ),
				'subtitle'    => esc_html__( 'Used to display stats with progress bars.', 'alchemists' ),
				'placeholder' => esc_html__( 'Select statistics...', 'alchemists' ),
				'desc'        => esc_html__( 'Select 4-6 items.', 'alchemists' ),
				'data'        => 'posts',
				'sortable'    => true,
				'args'        => array(
					'post_type'      => array( 'sp_performance', 'sp_statistic' ),
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				),
				'required'    => array( 'alchemists__player-title-progress-bars', '=', '1' ),
				'class'       => 'alchemists-field alchemists-field--soccer-only alchemists-field--football-only',
			),
			array(
				'id'          => 'alchemists__player-title-progress-bars-custom-format',
				'type'        => 'select',
				'title'       => esc_html__( 'Progress Bars Format', 'alchemists' ),
				'subtitle'    => esc_html__( 'Select a proper format.', 'alchemists' ),
				'desc'        => esc_html__( 'To display absolute values such as accuracy, various percentages, etc., select the "Absolute (Percentage)" option. To display relative values, select the "Relative" option. Relative values are built based on comparing themselves with other players in the select player list on the player page.', 'alchemists' ),
				'options'     => array(
					'relative'   => esc_html__( 'Relative', 'alchemists' ),
					'percentage' => esc_html__( 'Absolute (Percentage)', 'alchemists' ),
				),
				'default'     => 'percentage',
				'required'    => array( 'alchemists__player-title-progress-bars', '=', '1' ),
			),
			array(
				'id'          => 'alchemists__player-team-logo',
				'type'        => 'switch',
				'title'       => esc_html__( 'Team Logo', 'alchemists' ),
				'subtitle'    => esc_html__( 'Display Team Logo.', 'alchemists' ),
				'desc'        => esc_html__( 'Enables Team Logo on Single Player pages.', 'alchemists' ),
				'default'     => 0,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
				'required'    => array('alchemists__player-page-header-layout', '=', 'fullwidth'),
			),
			array(
				'id'       => 'alchemists__player-team-logo-opacity',
				'type'     => 'slider',
				'title'    => esc_html__( 'Team Logo Opacity', 'alchemists' ),
				'subtitle' => esc_html__( 'Option to change logo opacity.', 'alchemists' ),
				'desc'     => esc_html__( 'Drag the slider to adjust the Team Logo opacity in %', 'alchemists' ),
				'default'  => 10,
				'min'      => 5,
				'step'     => 5,
				'max'      => 100,
				'display_value' => 'text',
				'required'    => array( 'alchemists__player-team-logo', '=', '1' ),
			),
			array(
				'id'       => 'alchemists__player-title-end',
				'type'     => 'section',
				'indent'   => false
			),

			// Permalinks Notice
			array(
				'id'        => 'alchemists__player-subpages-notice--slug',
				'type'      => 'info',
				'notice'    => true,
				'icon'      => 'el el-icon-warning-sign',
				'style'     => 'warning',
				'title'     => esc_html__('Change Permalinks', 'alchemists'),
				'desc'      => __('After changing slugs go to <strong>Settings > Permalinks</strong> and click on <strong>Save Changes</strong> button.', 'alchemists')
			),

			// Player -- Subpages
			array(
				'id'       => 'alchemists__player-subpages-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Single Player Navigation', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Single Player navigation.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__player-subpages',
				'type'      => 'sorter',
				'title'     => esc_html__( 'Player Subpages Layout', 'alchemists' ),
				'subtitle'  => esc_html__( 'Organize Player Subpages order.', 'alchemists' ),
				'desc'      => esc_html__( 'Organize how you want the team subpages to appear on a single player page.', 'alchemists' ),
				'compiler'  => 'true',
				'options'   => array(
					'enabled'   => array(
						'overview' => esc_html__( 'Overview', 'alchemists' ),
						'stats'    => esc_html__( 'Statistics', 'alchemists' ),
						'bio'      => esc_html__( 'Biography', 'alchemists' ),
						'news'     => esc_html__( 'Related News', 'alchemists' ),
						'gallery'  => esc_html__( 'Gallery', 'alchemists' ),
					),
					'disabled'  => array(),
				),
			),

			array(
				'id'        => 'alchemists__player-subpages-label--overview',
				'type'      => 'text',
				'title'     => esc_html__( 'Overview - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Overview Player subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Overview', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__player-subpages-slug--overview',
				'type'      => 'text',
				'title'     => esc_html__( 'Overview - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Overview Player subpage.', 'alchemists' ),
				'default'   => 'overview',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__player-subpages-label--stats',
				'type'      => 'text',
				'title'     => esc_html__( 'Statistics - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Statistics Player subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Full Statistics', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__player-subpages-slug--stats',
				'type'      => 'text',
				'title'     => esc_html__( 'Statistics - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Statistics Player subpage.', 'alchemists' ),
				'default'   => 'stats',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__player-subpages-label--bio',
				'type'      => 'text',
				'title'     => esc_html__( 'Biography - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Biography Player subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Biography', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__player-subpages-slug--bio',
				'type'      => 'text',
				'title'     => esc_html__( 'Biography - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Biography Player subpage.', 'alchemists' ),
				'default'   => 'bio',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__player-subpages-label--news',
				'type'      => 'text',
				'title'     => esc_html__( 'Related News - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Related News Player subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Related News', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__player-subpages-slug--news',
				'type'      => 'text',
				'title'     => esc_html__( 'Related News - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Related News Player subpage.', 'alchemists' ),
				'default'   => 'news',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'        => 'alchemists__player-subpages-label--gallery',
				'type'      => 'text',
				'title'     => esc_html__( 'Gallery - Label', 'alchemists' ),
				'desc'      => esc_html__( 'Label for Gallery Player subpage.', 'alchemists' ),
				'default'   => esc_html__( 'Gallery', 'alchemists' ),
			),
			array(
				'id'        => 'alchemists__player-subpages-slug--gallery',
				'type'      => 'text',
				'title'     => esc_html__( 'Gallery - Slug', 'alchemists' ),
				'desc'      => esc_html__( 'Slug for Gallery Player subpage.', 'alchemists' ),
				'default'   => 'gallery',
				'validate'  => 'no_special_chars',
			),
			array(
				'id'          => 'alchemists__player-subpages-label--subtitle',
				'type'        => 'switch',
				'title'       => esc_html__( 'Display Secondary Label?', 'alchemists' ),
				'subtitle'    => esc_html__( 'Secondary label displayed by default.', 'alchemists' ),
				'desc'        => esc_html__( 'Short text appears above each subpage label.', 'alchemists' ),
				'default'     => 1,
				'on'          => esc_html__( 'Yes', 'alchemists' ),
				'off'         => esc_html__( 'No', 'alchemists' ),
			),
			array(
				'id'          => 'alchemists__player-subpages-label--subtitle-custom',
				'type'        => 'text',
				'title'       => esc_html__( 'Secondary Label', 'alchemists' ),
				'desc'        => esc_html__( 'Enter your secondary label, \'Player\' by default.', 'alchemists' ),
				'required'    => array( 'alchemists__player-subpages-label--subtitle', '=', '1' ),
			),
			array(
				'id'       => 'alchemists__player-subpages-end',
				'type'     => 'section',
				'indent'   => false
			),

			// Player -- Gallery
			array(
				'id'       => 'alchemists__player-gallery-start',
				'type'     => 'section',
				'title'    => esc_html__( 'Player Gallery', 'alchemists' ),
				'subtitle' => esc_html__( 'Customize Single Player Gallery subpage.', 'alchemists' ),
				'indent'   => true
			),
			array(
				'id'        => 'alchemists__player-gallery-layout',
				'type'      => 'select',
				'title'     => esc_html__( 'Player Gallery Layout', 'alchemists'),
				'subtitle'  => esc_html__( 'Select layout for Gallery on the Player Gallery pages.', 'alchemists'),
				'options'   => array(
					'fixed'     => esc_html__( 'Fixed Width', 'alchemists' ),
					'fullwidth' => esc_html__( 'Full Width', 'alchemists' ),
				),
				'default'   => 'fixed'
			),
			array(
				'id'       => 'alchemists__player-gallery-end',
				'type'     => 'section',
				'indent'   => false
			),
		)
	) );

	// Albums Options
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'Albums', 'alchemists' ),
		'id'     => 'alchemists__section-albums',
		'icon'   => 'el el-picture',
		'fields' => array(
			array(
				'id'        => 'alchemists__album-layout',
				'type'      => 'select',
				'title'     => esc_html__( 'Album Layout Stretch', 'alchemists'),
				'subtitle'  => esc_html__( 'Select layout for Single Album page.', 'alchemists'),
				'options'   => array(
					'fixed'     => esc_html__( 'Fixed Width', 'alchemists' ),
					'fullwidth' => esc_html__( 'Full Width', 'alchemists' ),
				),
				'default'   => 'fixed'
			),
		)
	) );

	// Video Posts
	Redux::setSection( $opt_name, array(
		'title'      => esc_html__( 'Videos', 'alchemists' ),
		'id'         => 'alchemists__subsection-video-post-formats',
		'icon'       => 'el el-video',
		'fields'     => array(
			array(
				'id'             => 'alchemists__video-post-formats-separation',
				'type'           => 'switch',
				'title'          => esc_html__( 'Separate video from the main content?', 'alchemists' ),
				'subtitle'       => esc_html__( 'Enables additional fields for video posts.', 'alchemists' ),
				'desc'           => esc_html__( 'Enable this option if you are going to have text content for your video posts. Note that this option can be changed  for each video post individually.', 'alchemists' ),
				'default'        => 0,
				'on'             => esc_html__( 'Yes', 'alchemists' ),
				'off'            => esc_html__( 'No', 'alchemists' ),
			),
		)
	) );

	// WooCommerce Options
	Redux::setSection( $opt_name, array(
		'title'  => esc_html__( 'WooCommerce', 'alchemists' ),
		'id'     => 'alchemists__section-woocommerce',
		'icon'   => 'el el-shopping-cart',
		'fields' => array(
			array(
				'id'        => 'alchemists__shop-account-nav-type',
				'type'      => 'select',
				'title'     => esc_html__( 'My Account Nav Type', 'alchemists' ),
				'subtitle'  => esc_html__( 'Changes Navigation on My Account page', 'alchemists' ),
				'desc'      => esc_html__( 'Select type of Navigation on the My Account page', 'alchemists' ),
				'options'   => array(
					'vertical'   => esc_html__( 'Vertical', 'alchemists' ),
					'horizontal' => esc_html__( 'Horizontal', 'alchemists' ),
				),
				'default'   => 'vertical'
			),
			array(
				'id'        => 'alchemists__shop-sidebar',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Shop Sidebar', 'alchemists'),
				'desc'      => esc_html__('Select sidebar position for Shop.', 'alchemists'),
				'options'   => array(
					'left_sidebar' => array(
						'alt' => esc_html__( 'Left Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/2cl.png'),
					'right_sidebar' => array(
						'alt' => esc_html__( 'Right Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/2cr.png'),
					'no_sidebar' => array(
						'alt' => esc_html__( 'No Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/1c.png'),
				),
				'default'   => 'left_sidebar'
			),
			array(
				'id'        => 'alchemists__single-shop-sidebar',
				'type'      => 'image_select',
				'compiler'  => true,
				'title'     => esc_html__('Single Product Sidebar', 'alchemists'),
				'desc'      => esc_html__('Select sidebar position for Single Product.', 'alchemists'),
				'options'   => array(
					'left_sidebar' => array(
						'alt' => esc_html__( 'Left Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/2cl.png'),
					'right_sidebar' => array(
						'alt' => esc_html__( 'Right Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/2cr.png'),
					'no_sidebar' => array(
						'alt' => esc_html__( 'No Sidebar', 'alchemists' ),
						'img' => ReduxFramework::$_url . 'assets/img/1c.png'),
				),
				'default'   => 'no_sidebar'
			),
			array(
				'id'        => 'alchemists__shop-related-columns',
				'type'      => 'select',
				'title'     => esc_html__('Related Products Columns', 'alchemists'),
				'subtitle'  => esc_html__('Select the number of columns for the related products', 'alchemists'),
				'options'   => array(
					'2' => '2',
					'3' => '3',
					'4' => '4'
				),
				'default'   => '4'
			),
			array(
				'id'        => 'alchemists__product-gradient',
				'type'      => 'switch',
				'title'     => esc_html__( 'Product gradients', 'alchemists'),
				'subtitle'  => esc_html__( 'Adds gradient option on products.', 'alchemists'),
				'desc'      => esc_html__( 'Used for set gradient on products.', 'alchemists'),
				'default'   => 1,
				'on'        => esc_html__('Yes', 'alchemists'),
				'off'       => esc_html__('No', 'alchemists'),
			),
			array(
				'id'       => 'alchemists__shop-related-per-page',
				'type'     => 'text',
				'title'    => esc_html__( 'Related products per page', 'alchemists' ),
				'subtitle' => esc_html__( 'Number of products of Related Products.', 'alchemists' ),
				'desc'     => esc_html__( 'Change the number of products fore Related Products.', 'alchemists' ),
				'default'  => '4'
			),
		)
	) );

	// Custom CSS
	Redux::setSection( $opt_name, array(
		'title'     => esc_html__('Custom CSS', 'alchemists'),
		'icon'      => 'el-icon-css',
		'id'        => 'alchemists__section-custom-css',
		'fields'    => array(
			array(
				'id'        => 'alchemists__custom-css',
				'type'      => 'ace_editor',
				'title'     => esc_html__( 'CSS Code', 'alchemists' ),
				'subtitle'  => esc_html__( 'Paste your CSS code here.', 'alchemists' ),
				'mode'      => 'css',
				'theme'     => 'monokai',
				'desc'      => esc_html__( 'Any custom CSS can be added here, it will override the theme CSS.', 'alchemists' ),
				'default'   => ""
			),
		)
	) );

	Redux::setSection( $opt_name, array(
		'title'     => esc_html__('Import / Export', 'alchemists'),
		'desc'      => esc_html__('Import and Export your theme settings from file, text or URL.', 'alchemists'),
		'icon'      => 'el-icon-refresh',
		'id'        => 'alchemists__section-import-export',
		'fields'    => array(
			array(
				'id'            => 'opt-import-export',
				'type'          => 'import_export',
				'full_width'    => false,
			),
		),
	) );

	if ( file_exists( get_parent_theme_file_path( 'readme.txt' ) ) ) {
		Redux::setSection( $opt_name, array(
			'icon'      => 'el-icon-list-alt',
			'id'        => 'alchemists__section-theme-info',
			'title'     => esc_html__( 'Theme Information', 'alchemists' ),
			'fields'    => array(
				array(
					'id'        => '17',
					'type'      => 'raw',
					'markdown'  => true,
					'content'   => file_get_contents( get_parent_theme_file_path( 'readme.txt' ) )
				),
			),
		) );
	}
