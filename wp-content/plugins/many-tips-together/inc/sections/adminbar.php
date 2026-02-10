<?php
/**
 * Section Admin Bar config
 * 
 * @package Admin Tweaks
 * @subpackage Redux Framework
 */

defined( 'ABSPATH' ) || exit;

\Redux::set_section(
	$adtw_option,
	array(
		'title' => esc_html__( 'Admin Bar', 'mtt' ),
		'id'    => 'adminbar',
        'icon' => 'el el-arrow-up',
        'fields' => [
            array( # remove items
				'id'       => 'adminbar_remove',
				'type'     => 'button_set',
				'title'    => esc_html__('Remove default items', 'mtt'),
				'multi'    => true,

				// Must provide key => value pairs for radio options.
				'options'  => ADTW()->getAdminBar(),
				'default'  => [],
			),
            # ENABLE SITE NAME + ICON
            array( # enable
                'id'       => 'adminbar_sitename_enable',
                'type'     => 'switch',
                'title'    => __('Add Site Name with Icon', 'mtt'),
                'desc' => __('Add a custom link with title and icon', 'mtt'),
                'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
            ),
            array( ## title
                'id'       => 'adminbar_sitename_title',
                'type'     => 'text',
                'title'    => esc_html__( 'Title', 'mtt' ),
                'required' => array( 'adminbar_sitename_enable', '=', true ),
            ),
			array( ## icon
                'id'       => 'adminbar_sitename_img',
				'type'     => 'media',
				'title'    => esc_html__( 'Icon', 'mtt' ),
				'url'      => false,
				'preview'  => true,
                'required' => array( 'adminbar_sitename_enable', '=', true ),
			),
			array( ## url
                'id'       => 'adminbar_sitename_url',
				'type'     => 'text',
				'title'    => esc_html__( 'URL', 'mtt' ),
				'validate' => 'url',
				'placeholder'  => esc_html__( 'full URL with http://, leave empty for default (swap back and frontend)', 'mtt' ),
                'required' => array( 'adminbar_sitename_enable', '=', true ),
			),
            # ENABLE ADTW MENU
            array( # adtw menu
                'id'       => 'adminbar_adtw_enable',
                'type'     => 'switch',
                'title'    => esc_html__('Add Admin Tweaks shortcut', 'mtt'),
                'desc' => esc_html__('Useful when configuring this plugin', 'mtt'),
                'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
            ),
            array( # howdy
                'id'       => 'adminbar_howdy_enable',
                'type'     => 'switch',
                'title'    => esc_html__('Remove or change "Howdy"', 'mtt'),
                'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
            ),
            array( ## howdy text
                'id'       => 'adminbar_howdy_text',
                'type'     => 'text',
                'title'    => esc_html__( 'Replace with', 'mtt' ),
                'desc' => esc_html__('Leave empty for complete removal', 'mtt'),
                'required' => array( 'adminbar_howdy_enable', '=', true ),
            ),
            array( ## howdy text
                'id'       => 'adminbar_howdy_original_text',
                'type'     => 'text',
                'title'    => esc_html__( 'Howdy in your language', 'mtt' ),
                'desc' => esc_html__('Leave empty if WP is in English', 'mtt'),
                'class' => 'howdy-translated',
                'required' => [
                    ['adminbar_howdy_enable', '=', true]
                ],
            ),
            # ENABLE CUSTOM MENU
            array( # custom menu
                'id'       => 'adminbar_custom_enable',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Custom Menu', 'mtt' ),
                'desc'     => esc_html__('Save options to configure the menu', 'mtt'),
                'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
            ),
			array( ## repeater
                'id'          => 'adminbar_custom_items',
				'type'        => 'repeater',
				'title'       => '',
				'full_width'  => false,
				'desc'    => '',
				'item_name'   => '',
				'sortable'    => true,
				'active'      => false,
				'collapsible' => false,
                'class'       => 'sub-section',
                'required'    => array( 'adminbar_custom_enable', '=', true ),
				'fields'      => array(
					array(
						'id'          => 'adminbar_custom_item_name',
						'type'        => 'text',
						'placeholder' => esc_html__( 'Menu name', 'mtt' ),
					),
					array(
						'id'          => 'adminbar_custom_item_url',
						'type'        => 'text',
						'placeholder' => esc_html__( 'Menu link', 'mtt' ),
                        //'validate_callback'    => [\ADTW(), 'validateURL']
					),
				),
			),
            array( # disable admin bar
				'id'       => 'adminbar_completely_disable',
				'type'     => 'switch',
				'title'    => esc_html__('Completely remove the Admin Bar', 'mtt'),
				'desc'     => sprintf(
                    esc_html__('Remove from back and front end. Creates a "Visit Site" link in the Dashboard menu item.%s Tip via: %s', 'mtt'),
                    '<br />',
                    '<a href="https://wordpress.stackexchange.com/a/77648/12615">WordPress Answers</a>'
                ),
				'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
			),
			array( # disable adminbar frontend
				'id'       => 'adminbar_disable',
				'type'     => 'switch',
				'title'    => esc_html__('Disable Admin Bar for all users in Frontend', 'mtt'),
				'default'  => false,
                'on' => esc_html__('On', 'mtt'),
                'off' => esc_html__('Off', 'mtt')
			),
        ]
	)
);
