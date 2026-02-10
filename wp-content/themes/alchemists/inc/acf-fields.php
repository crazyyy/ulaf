<?php
/**
 * Programmatic registration of Advanced Custom Fields fields
 *
 * @see http://www.advancedcustomfields.com/resources/register-fields-via-php/
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   4.7.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( function_exists( 'acf_add_local_field_group' ) ) {
	function alchemists_acf_add_local_field_groups() {

		$alchemists_data = get_option( 'alchemists_data' );

		acf_add_local_field_group(array(
			'key' => 'group_58ff529930c76',
			'title' => esc_html__( 'Albums Photos', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58ff52b4656af',
					'label' => esc_html__( 'Album Photos', 'alchemists' ),
					'name' => 'album_photos',
					'type' => 'gallery',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'insert' => 'append',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'albums',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_5924aece2672e',
			'title' => esc_html__( 'Page Options', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5924aed817453',
					'label' => esc_html__( 'Page Heading', 'alchemists' ),
					'name' => 'page_heading',
					'type' => 'radio',
					'instructions' => esc_html__( 'Select Page Heading Type', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'page_default' => esc_html__( 'Title', 'alchemists' ),
						'page_hero' => esc_html__( 'Hero Unit - Static', 'alchemists' ),
						'page_hero_posts_slider' => esc_html__( 'Hero Unit - Posts Slider', 'alchemists' ),
						'page_none' => esc_html__( 'None', 'alchemists' ),
					),
					'allow_null' => 0,
					'other_choice' => 0,
					'save_other_choice' => 0,
					'default_value' => 'page_default : Default',
					'layout' => 'vertical',
					'return_format' => 'value',
				),
				array(
					'key' => 'field_5a033537c16f6',
					'label' => esc_html__( 'Customize Page Heading?', 'alchemists' ),
					'name' => 'page_heading_customize',
					'type' => 'true_false',
					'instructions' => esc_html__( 'Enables customization options.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5924aed817453',
								'operator' => '==',
								'value' => 'page_default',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a03339124ad4',
					'label' => esc_html__( 'Custom Background Image', 'alchemists' ),
					'name' => 'page_heading_custom_background_img',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'url',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_5a0336f7d7c68',
					'label' => esc_html__( 'Custom Background Color', 'alchemists' ),
					'name' => 'page_heading_custom_background_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5a03379a1b077',
					'label' => esc_html__( 'Add Overlay?', 'alchemists' ),
					'name' => 'page_heading_add_overlay_on',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a036662eacd6',
					'label' => esc_html__( 'Remove Overlay Pattern?', 'alchemists' ),
					'name' => 'page_heading_remove_overlay_pattern',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a03379a1b077',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a0339208dda6',
					'label' => esc_html__( 'Custom Overlay Color', 'alchemists' ),
					'name' => 'page_heading_custom_overlay_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a03379a1b077',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5a033a068d85a',
					'label' => esc_html__( 'Custom Overlay Opacity', 'alchemists' ),
					'name' => 'page_heading_custom_overlay_opacity',
					'type' => 'range',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a03379a1b077',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 40,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'prepend' => '',
					'append' => '',
				),
				array(
					'key' => 'field_644be2a685dbb',
					'label' => 'Add Duotone effect?',
					'name' => 'page_heading_add_duotone_effect',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a033537c16f6',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_644be30b85dbc',
					'label' => 'Duotone Colors',
					'name' => 'page_heading_duotone_colors',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_644be2a685dbb',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'primary' => 'Primary',
						'secondary' => 'Secondary',
						'tertiary' => 'Tertiary',
						'quaternary' => 'Quaternary',
						'info' => 'Info',
						'blue' => 'Blue',
						'red' => 'Red',
						'grey' => 'Grey',
						'yellow' => 'Yellow',
						'custom' => 'Custom Color',
					),
					'default_value' => 'primary',
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'return_format' => 'value',
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_644be39685dbd',
					'label' => 'Duotone Color 1',
					'name' => 'page_heading_duotone_color_1',
					'type' => 'color_picker',
					'instructions' => 'It is important to use completely black or dark color. In other case you will get unexpected result.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_644be30b85dbc',
								'operator' => '==',
								'value' => 'custom',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '#000000',
					'enable_opacity' => 0,
					'return_format' => 'string',
				),
				array(
					'key' => 'field_644be3e285dbe',
					'label' => 'Duotone Color 2',
					'name' => 'page_heading_duotone_color_2',
					'type' => 'color_picker',
					'instructions' => 'This color will be mixed with Duotone Color 1 and applied to the Page Heading image.',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_644be30b85dbc',
								'operator' => '==',
								'value' => 'custom',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'enable_opacity' => 0,
					'return_format' => 'string',
				),
				array(
					'key' => 'field_592d75db746a5',
					'label' => esc_html__( 'Content Top Padding', 'alchemists' ),
					'name' => 'page_content_top_padding',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'default' => esc_html__( 'Default', 'alchemists' ),
						'none' => esc_html__( 'None', 'alchemists' ),
					),
					'default_value' => array(
						0 => 'default',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'return_format' => 'value',
					'placeholder' => '',
				),
				array(
					'key' => 'field_592d76ad746a6',
					'label' => esc_html__( 'Content Bottom Padding', 'alchemists' ),
					'name' => 'page_content_bottom_padding',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'default' => esc_html__( 'Default', 'alchemists' ),
						'none' => esc_html__( 'None', 'alchemists' ),
					),
					'default_value' => array(
						0 => 'default',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'return_format' => 'value',
					'placeholder' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_directory',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_tournament',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_event',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_table',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_calendar',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'albums',
					),
				),
			),
			'menu_order' => 100,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_58f3ecce6e041',
			'title' => esc_html__( 'Player Bio', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58f3f4ac21e0c',
					'label' => esc_html__( 'Player Image', 'alchemists' ),
					'name' => 'player_image',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '12',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'array',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_58f3ecdb1d6cb',
					'label' => esc_html__( 'Player Bio Content', 'alchemists' ),
					'name' => 'player_bio_content',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '53',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 0,
				),
				array(
					'key' => 'field_58f3ed471d6cc',
					'label' => esc_html__( 'Player Bio Events', 'alchemists' ),
					'name' => 'player_bio_events',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '35',
						'class' => '',
						'id' => '',
					),
					'collapsed' => '',
					'min' => 0,
					'max' => 0,
					'layout' => 'row',
					'button_label' => esc_html__( 'Add Event', 'alchemists' ),
					'sub_fields' => array(
						array(
							'key' => 'field_58f3ede1d832c',
							'label' => esc_html__( 'Type', 'alchemists' ),
							'name' => 'event_type',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								'Injury' => esc_html__( 'Injury', 'alchemists' ),
								'Join' => esc_html__( 'Join', 'alchemists' ),
								'Award' => esc_html__( 'Award', 'alchemists' ),
								'Exit' => esc_html__( 'Exit', 'alchemists' ),
								'Oth-Pos' => esc_html__( 'Other Positive', 'alchemists' ),
								'Oth-Neg' => esc_html__( 'Other Negative', 'alchemists' ),
							),
							'default_value' => array(
							),
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'ajax' => 0,
							'return_format' => 'value',
							'placeholder' => '',
						),
						array(
							'key' => 'field_58f3ee12d832d',
							'label' => esc_html__( 'Content', 'alchemists' ),
							'name' => 'event_content',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 3,
							'new_lines' => '',
						),
						array(
							'key' => 'field_58f3ee23d832e',
							'label' => esc_html__( 'Date', 'alchemists' ),
							'name' => 'event_date',
							'type' => 'date_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'display_format' => 'F j, Y',
							'return_format' => 'F j, Y',
							'first_day' => 1,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_58fcbfbd2560e',
			'title' => esc_html__( 'Player Heading Photo', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58fcc00499d46',
					'label' => esc_html__( 'Alternative Photo', 'alchemists' ),
					'name' => 'heading_player_photo',
					'type' => 'image',
					'instructions' => esc_html__( 'This photo displayed in the Page Header and Featured Player widgets.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'id',
					'preview_size' => 'medium',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));


		// Add Product gradient options if enabled
		$product_gradient = isset( $alchemists_data['alchemists__product-gradient'] ) ? $alchemists_data['alchemists__product-gradient'] : 1;

		if ( $product_gradient ) {
			acf_add_local_field_group(array(
				'key' => 'group_5940438ed751f',
				'title' => esc_html__( 'Product Options', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_59404484e31db',
						'label' => esc_html__( 'Product Gradient Color 1', 'alchemists' ),
						'name' => 'product_grad_color_1',
						'type' => 'color_picker',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '#fe2b00',
					),
					array(
						'key' => 'field_59404507e31dc',
						'label' => esc_html__( 'Product Gradient Color 2', 'alchemists' ),
						'name' => 'product_grad_color_2',
						'type' => 'color_picker',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '#f7d500',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'product',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'left',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => 1,
				'description' => '',
			));
		}


		// Team
		acf_add_local_field_group(array(
			'key' => 'group_5d5d650d1f149',
			'title' => esc_html__( 'Team Colors', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5d5d65131416b',
					'label' => esc_html__( 'Primary', 'alchemists' ),
					'name' => 'team_color_primary',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '25',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5d5d729675d1b',
					'label' => esc_html__( 'Secondary', 'alchemists' ),
					'name' => 'team_color_secondary',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '25',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5d5d65f61416c',
					'label' => esc_html__( 'Card Accent', 'alchemists' ),
					'name' => 'team_color_heading',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '25',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));


		acf_add_local_field_group(array(
			'key' => 'group_58ff538a7cb77',
			'title' => esc_html__( 'Team Gallery', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58ff5392bec33',
					'label' => esc_html__( 'Albums', 'alchemists' ),
					'name' => 'team_gallery_albums',
					'type' => 'post_object',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'albums',
					),
					'taxonomy' => array(
					),
					'allow_null' => 0,
					'multiple' => 1,
					'return_format' => 'object',
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));


		if ( alchemists_sp_preset('soccer') ) {

			// Team Roster Option - Soccer
			acf_add_local_field_group(array(
				'key' => 'group_58fca7b5b53ec',
				'title' => esc_html__( 'Team Roster', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_58fca7bbfbeb5',
						'label' => esc_html__( 'Featured Gallery Roster', 'alchemists' ),
						'name' => 'gallery_roster_show',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fca8b1aadab',
						'label' => esc_html__( 'Select Gallery Roster', 'alchemists' ),
						'name' => 'gallery_roster',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fca7bbfbeb5',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_list',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5901f759b8d8c',
						'label' => esc_html__( 'Gallery Roster Type', 'alchemists' ),
						'name' => 'gallery_roster_type',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fca7bbfbeb5',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'grid'            => esc_html__( 'Grid', 'alchemists' ),
							'blocks'          => esc_html__( 'Blocks', 'alchemists' ),
							'slider'          => esc_html__( 'Slider Soccer', 'alchemists' ),
							'slider-card'     => esc_html__( 'Slider Default', 'alchemists' ),
							'cards'           => esc_html__( 'Cards', 'alchemists' ),
							'carousel_thumbs' => esc_html__( 'Carousel + Thumbs', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'grid',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5901fc6bda17d',
						'label' => esc_html__( 'Slider Custom Background Image', 'alchemists' ),
						'name' => 'roster_slider_background_image',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5901f759b8d8c',
									'operator' => '==',
									'value' => 'slider-card',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'url',
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
					array(
						'key' => 'field_5901fa1554dbb',
						'label' => esc_html__( 'Slider Autoplay', 'alchemists' ),
						'name' => 'roster_slider_autoplay',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5901f759b8d8c',
									'operator' => '==',
									'value' => 'slider',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fcafde0456f',
						'label' => esc_html__( 'List Roster(s)', 'alchemists' ),
						'name' => 'list_roster_show',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fcb01004570',
						'label' => esc_html__( 'Select List Roster(s)', 'alchemists' ),
						'name' => 'list_roster',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcafde0456f',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_list',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 1,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_58fcd61f40f10',
						'label' => esc_html__( 'Featured Player', 'alchemists' ),
						'name' => 'featured_player',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fcd5ddd46f3',
						'label' => esc_html__( 'Select Featured Player', 'alchemists' ),
						'name' => 'team_featured_player',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcd61f40f10',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_player',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'id',
						'ui' => 1,
					),
					array(
						'key' => 'field_62dfd9f0abc57',
						'label' => esc_html__( 'Featured Player Shortcode', 'alchemists' ),
						'name' => 'team_roster_featured_player',
						'type' => 'textarea',
						'instructions' => esc_html__( 'Use this option if you want to customize the Featured Player statistics. Insert shortcode of ALC: Player Stats element generated with WPBakery Page Builder. Note, this option overwrites the Select Featured Player option.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcd61f40f10',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 4,
						'new_lines' => '',
					),
					array(
						'key' => 'field_5d4edd94751d9',
						'label' => esc_html__( 'Staff', 'alchemists' ),
						'name' => 'team_staff',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => esc_html__( 'On', 'alchemists' ),
						'ui_off_text' => esc_html__( 'Off', 'alchemists' ),
					),
					array(
						'key' => 'field_5d4edeef751dc',
						'label' => esc_html__( 'Staff Display Type', 'alchemists' ),
						'name' => 'team_staff_display_type',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'list' => esc_html__( 'List', 'alchemists' ),
							'gallery' => esc_html__( 'Grid', 'alchemists' ),
							'blocks' => esc_html__( 'Blocks', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'gallery',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5d4edfd5751dd',
						'label' => esc_html__( 'Staff Position', 'alchemists' ),
						'name' => 'team_staff_position',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'before' => esc_html__( 'Before Players', 'alchemists' ),
							'after' => esc_html__( 'After Players', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'before',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5d4ef2e111b41',
						'label' => esc_html( 'Staff Heading', 'alchemists' ),
						'name' => 'team_staff_heading',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'sp_team',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'left',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => 1,
				'description' => '',
			));

		} else {

			// Team Roster Option - Basketball & American Football
			acf_add_local_field_group(array(
				'key' => 'group_58fca7b5b53ec',
				'title' => esc_html__( 'Team Roster', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_58fca7bbfbeb5',
						'label' => esc_html__( 'Featured Gallery Roster', 'alchemists' ),
						'name' => 'gallery_roster_show',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fca8b1aadab',
						'label' => esc_html__( 'Select Gallery Roster', 'alchemists' ),
						'name' => 'gallery_roster',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fca7bbfbeb5',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_list',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5901f759b8d8c',
						'label' => esc_html__( 'Gallery Roster Type', 'alchemists' ),
						'name' => 'gallery_roster_type',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fca7bbfbeb5',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'grid'            => esc_html__( 'Grid', 'alchemists' ),
							'blocks'          => esc_html__( 'Blocks', 'alchemists' ),
							'slider'          => esc_html__( 'Slider', 'alchemists' ),
							'carousel_thumbs' => esc_html__( 'Carousel + Thumbs', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'grid',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5901fa1554dbb',
						'label' => esc_html__( 'Slider Autoplay', 'alchemists' ),
						'name' => 'roster_slider_autoplay',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5901f759b8d8c',
									'operator' => '==',
									'value' => 'slider',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 0,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_5901fc6bda17d',
						'label' => esc_html__( 'Slider Custom Background Image', 'alchemists' ),
						'name' => 'roster_slider_background_image',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5901f759b8d8c',
									'operator' => '==',
									'value' => 'slider',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'url',
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
					array(
						'key' => 'field_58fcafde0456f',
						'label' => esc_html__( 'List Roster(s)', 'alchemists' ),
						'name' => 'list_roster_show',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fcb01004570',
						'label' => esc_html__( 'Select List Roster(s)', 'alchemists' ),
						'name' => 'list_roster',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcafde0456f',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_list',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 1,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_58fcd61f40f10',
						'label' => esc_html__( 'Featured Player', 'alchemists' ),
						'name' => 'featured_player',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 1,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
					array(
						'key' => 'field_58fcd5ddd46f3',
						'label' => esc_html__( 'Select Featured Player', 'alchemists' ),
						'name' => 'team_featured_player',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcd61f40f10',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'sp_player',
						),
						'taxonomy' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'id',
						'ui' => 1,
					),
					array(
						'key' => 'field_62dfd9f0abc57',
						'label' => esc_html__( 'Featured Player Shortcode', 'alchemists' ),
						'name' => 'team_roster_featured_player',
						'type' => 'textarea',
						'instructions' => esc_html__( 'Use this option if you want to customize the Featured Player statistics. Insert shortcode of ALC: Player Stats element generated with WPBakery Page Builder. Note, this option overwrites the Select Featured Player option.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_58fcd61f40f10',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 4,
						'new_lines' => '',
					),
					array(
						'key' => 'field_5d4edd94751d9',
						'label' => esc_html__( 'Staff', 'alchemists' ),
						'name' => 'team_staff',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => esc_html__( 'On', 'alchemists' ),
						'ui_off_text' => esc_html__( 'Off', 'alchemists' ),
					),
					array(
						'key' => 'field_5d4edeef751dc',
						'label' => esc_html__( 'Staff Display Type', 'alchemists' ),
						'name' => 'team_staff_display_type',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'list' => esc_html__( 'List', 'alchemists' ),
							'gallery' => esc_html__( 'Grid', 'alchemists' ),
							'blocks' => esc_html__( 'Blocks', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'gallery',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5d4edfd5751dd',
						'label' => esc_html__( 'Staff Position', 'alchemists' ),
						'name' => 'team_staff_position',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'before' => esc_html__( 'Before Players', 'alchemists' ),
							'after' => esc_html__( 'After Players', 'alchemists' ),
						),
						'default_value' => array(
							0 => 'before',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 1,
						'ajax' => 0,
						'return_format' => 'value',
						'placeholder' => '',
					),
					array(
						'key' => 'field_5d4ef2e111b41',
						'label' => esc_html( 'Staff Heading', 'alchemists' ),
						'name' => 'team_staff_heading',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5d4edd94751d9',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => '',
						'ui_off_text' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'sp_team',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'left',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => 1,
				'description' => '',
			));
		}

		acf_add_local_field_group(array(
			'key' => 'group_591dc7aae82be',
			'title' => esc_html__( 'Team Standings', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_591dc7dcfa595',
					'label' => esc_html__( 'Team Leagues', 'alchemists' ),
					'name' => 'team_leagues',
					'type' => 'post_object',
					'instructions' => esc_html__( 'Select Leagues you want to display on Team Standings page.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'sp_table',
					),
					'taxonomy' => array(
					),
					'allow_null' => 0,
					'multiple' => 1,
					'return_format' => 'id',
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		// Team Latest Results
		acf_add_local_field_group(array(
			'key' => 'group_5d5710517eff9',
			'title' => esc_html__( 'Team Latest Results', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5d57105182796',
					'label' => esc_html__( 'Select Calendars', 'alchemists' ),
					'name' => 'team_results_calendar_select',
					'type' => 'post_object',
					'instructions' => esc_html__( 'Select Calendars you want to display on Team Results page.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'sp_calendar',
					),
					'taxonomy' => '',
					'allow_null' => 0,
					'multiple' => 1,
					'return_format' => 'id',
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

		// Team Schedule
		acf_add_local_field_group(array(
			'key' => 'group_5d56e74fdf6f2',
			'title' => esc_html__( 'Team Schedule', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5d56e8de91e0b',
					'label' => esc_html__( 'Select Calendars', 'alchemists' ),
					'name' => 'team_schedule_calendar_select',
					'type' => 'post_object',
					'instructions' => esc_html__( 'Select Calendars you want to display on Team Schedule page.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'sp_calendar',
					),
					'taxonomy' => '',
					'allow_null' => 0,
					'multiple' => 1,
					'return_format' => 'id',
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_team',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_58f3a3264df65',
			'title' => esc_html__( 'Player Gallery', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58f3a330f283b',
					'label' => esc_html__( 'Images', 'alchemists' ),
					'name' => 'images',
					'type' => 'gallery',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => '',
					'max' => '',
					'insert' => 'append',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 1,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_58f50e018853f',
			'title' => esc_html__( 'Player Related News', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_58f50e0bab60a',
					'label' => esc_html__( 'Post Tags', 'alchemists' ),
					'name' => 'post_tags',
					'type' => 'taxonomy',
					'instructions' => esc_html__( 'Selected Posts tag that are related to the Player.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'post_tag',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'add_term' => 1,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'id',
					'multiple' => 0,
				),
				array(
					'key' => 'field_58f518c888fa2',
					'label' => esc_html__( 'Number of Posts', 'alchemists' ),
					'name' => 'number_of_posts',
					'type' => 'number',
					'instructions' => esc_html__( 'Enter number of displayed Posts.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 5,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => 1,
					'max' => '',
					'step' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 2,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_59d3aa54eafe0',
			'title' => esc_html__( 'Player Header', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_59d3ac2622f31',
					'label' => esc_html__( 'Player List', 'alchemists' ),
					'name' => 'player_header_list',
					'type' => 'post_object',
					'instructions' => esc_html__( 'Select Player List for statistics comparing.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'sp_list',
					),
					'taxonomy' => array(
					),
					'allow_null' => 1,
					'multiple' => 0,
					'return_format' => 'id',
					'ui' => 1,
				),
				array(
					'key' => 'field_5a086c7655435',
					'label' => esc_html__( 'Show Advanced Stats?', 'alchemists' ),
					'name' => 'player_page_heading_advanced_stats',
					'type' => 'true_false',
					'instructions' => esc_html__( 'Progress bars, radar charts etc.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a037bf1115c5',
					'label' => esc_html__( 'Customize Page Heading?', 'alchemists' ),
					'name' => 'player_page_heading_customize',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a037c4f115c6',
					'label' => esc_html__( 'Custom Background Image', 'alchemists' ),
					'name' => 'player_page_heading_custom_background_img',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'url',
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array(
					'key' => 'field_5a037cdf115c7',
					'label' => esc_html__( 'Custom Background Color', 'alchemists' ),
					'name' => 'player_page_heading_custom_background_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5a037d46cd50d',
					'label' => esc_html__( 'Add Overlay?', 'alchemists' ),
					'name' => 'player_page_heading_add_overlay_on',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a037d7acd50e',
					'label' => esc_html__( 'Remove Overlay Pattern?', 'alchemists' ),
					'name' => 'player_page_heading_remove_overlay_pattern',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a037d46cd50d',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => '',
					'ui_off_text' => '',
				),
				array(
					'key' => 'field_5a037dbb01a83',
					'label' => esc_html__( 'Custom Overlay Color', 'alchemists' ),
					'name' => 'player_page_heading_custom_overlay_color',
					'type' => 'color_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a037d46cd50d',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
				),
				array(
					'key' => 'field_5a037de501a85',
					'label' => esc_html__( 'Custom Overlay Opacity', 'alchemists' ),
					'name' => 'player_page_heading_custom_overlay_opacity',
					'type' => 'range',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5a037bf1115c5',
								'operator' => '==',
								'value' => '1',
							),
							array(
								'field' => 'field_5a037d46cd50d',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 40,
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'prepend' => '',
					'append' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 3,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		acf_add_local_field_group(array(
			'key' => 'group_5e165d4f50fd6',
			'title' => esc_html__( 'Links', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5e19e5b1b4870',
					'name' => 'player_social_networks',
					'type' => 'repeater',
					'instructions' => esc_html__( 'Add full links to social networks or services.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'collapsed' => 'field_5e19e5d7b4871',
					'min' => 0,
					'max' => 0,
					'layout' => 'row',
					'button_label' => esc_html__( 'Add Link', 'alchemists' ),
					'sub_fields' => array(
						array(
							'key' => 'field_5e19e5d7b4871',
							'label' => esc_html__( 'Link', 'alchemists' ),
							'name' => 'player_social_networks__link',
							'type' => 'url',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'sp_player',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => esc_html__( 'Enter links to Social Networks', 'alchemists' )
		));



		if ( class_exists( 'SportsPress_Pro' ) ) {

			acf_add_local_field_group(array(
				'key' => 'group_Xfq5HS9Z8XRUH',
				'title' => esc_html__( 'Links', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_AcFr6nJ3WrTbF',
						'name' => 'sponsor_social_networks',
						'type' => 'repeater',
						'instructions' => esc_html__( 'Add full links to social networks or services.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'collapsed' => 'field_J9uydX79UbuGs',
						'min' => 0,
						'max' => 0,
						'layout' => 'row',
						'button_label' => esc_html__( 'Add Link', 'alchemists' ),
						'sub_fields' => array(
							array(
								'key' => 'field_J9uydX79UbuGs',
								'label' => esc_html__( 'Link', 'alchemists' ),
								'name' => 'sponsor_social_networks__link',
								'type' => 'url',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'sp_sponsor',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'side',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => esc_html__( 'Enter links to Social Networks', 'alchemists' )
			));

		}


		if ( alchemists_sp_preset( 'esports' ) ) {
			acf_add_local_field_group(array(
				'key' => 'group_5e173a1c0cc36',
				'title' => esc_html__( 'Character Options', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_5e1b43ad187a0',
						'label' => esc_html__( 'Image', 'alchemists' ),
						'name' => 'character_img',
						'type' => 'image',
						'instructions' => esc_html__( 'Upload image for the character.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'url',
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
					array(
						'key' => 'field_5e2b54e444a6c',
						'label' => esc_html__( 'Icon', 'alchemists' ),
						'name' => 'character_icon',
						'type' => 'svg_icon',
						'instructions' => esc_html__( 'Selected icon used to represent type of character in various parts.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'allow_clear' => 0,
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'taxonomy',
							'operator' => '==',
							'value' => 'sp_position',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));
		} else {
			acf_add_local_field_group(array(
				'key' => 'group_5e173a1c0cc36',
				'title' => esc_html__( 'Position Options', 'alchemists' ),
				'fields' => array(
					array(
						'key' => 'field_5e1b43ad187a0',
						'label' => esc_html__( 'Image', 'alchemists' ),
						'name' => 'character_img',
						'type' => 'image',
						'instructions' => esc_html__( 'Upload image for the position.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'url',
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
					array(
						'key' => 'field_5e2b54e444a6c',
						'label' => esc_html__( 'Icon', 'alchemists' ),
						'name' => 'character_icon',
						'type' => 'svg_icon',
						'instructions' => esc_html__( 'Selected icon used to represent type of position in various parts.', 'alchemists' ),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'allow_clear' => 0,
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'taxonomy',
							'operator' => '==',
							'value' => 'sp_position',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));
		}

		acf_add_local_field_group(array(
			'key' => 'group_5a078cb3065eb',
			'title' => esc_html__( 'Post Options', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5a078cc4e78ab',
					'label' => esc_html__( 'Post Layout', 'alchemists' ),
					'name' => 'post_layout',
					'type' => 'select',
					'instructions' => esc_html__( 'This option overrides the general post layout.', 'alchemists' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'layout_1' => esc_html__( 'Post Layout 1', 'alchemists' ),
						'layout_2' => esc_html__( 'Post Layout 2', 'alchemists' ),
						'layout_3' => esc_html__( 'Post Layout 3', 'alchemists' ),
						'layout_4' => esc_html__( 'Post Layout 4', 'alchemists' ),
						'layout_5' => esc_html__( 'Post Layout 5', 'alchemists' ),
						'layout_6' => esc_html__( 'Post Layout 6', 'alchemists' ),
						'layout_7' => esc_html__( 'Post Layout 7', 'alchemists' ),
					),
					'default_value' => array(
					),
					'allow_null' => 1,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'return_format' => 'value',
					'placeholder' => '',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'field',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));


		// set video separation option globally
		$video_post_formats_separation_toggle = isset( $alchemists_data['alchemists__video-post-formats-separation'] ) ? $alchemists_data['alchemists__video-post-formats-separation'] : 0;
		acf_add_local_field_group(array(
			'key' => 'group_5e3668fc1695b',
			'title' => esc_html__( 'Video Options', 'alchemists' ),
			'fields' => array(
				array(
					'key' => 'field_5e3671cdf502d',
					'label' => esc_html__( 'Separate video from the main content?', 'alchemists' ),
					'name' => 'alc-posts-videos-enabled',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => $video_post_formats_separation_toggle,
					'ui' => 1,
					'ui_on_text' => 'Yes',
					'ui_off_text' => 'No',
				),
				array(
					'key' => 'field_5e366d677e636',
					'label' => '',
					'name' => 'alc-posts-videos',
					'type' => 'group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_5e3671cdf502d',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'layout' => 'row',
					'sub_fields' => array(
						array(
							'key' => 'field_5e366915e7daf',
							'label' => esc_html__( 'Type', 'alchemists' ),
							'name' => 'alc-posts-videos-type',
							'type' => 'button_group',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								'link' => 'Link',
								'embed' => 'Embed',
							),
							'allow_null' => 0,
							'default_value' => 'link',
							'layout' => 'horizontal',
							'return_format' => 'value',
						),
						array(
							'key' => 'field_5e366961e7db0',
							'label' => esc_html__( 'Link', 'alchemists' ),
							'name' => 'alc-posts-videos-embed',
							'type' => 'oembed',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_5e366915e7daf',
										'operator' => '==',
										'value' => 'Link',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'width' => 740,
							'height' => 415,
						),
						array(
							'key' => 'field_5e3669c5e7db1',
							'label' => esc_html__( 'Code', 'alchemists' ),
							'name' => 'alc-posts-videos-embed-code',
							'type' => 'textarea',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => array(
								array(
									array(
										'field' => 'field_5e366915e7daf',
										'operator' => '==',
										'value' => 'Embed',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'maxlength' => '',
							'rows' => 6,
							'new_lines' => '',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'videos',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'post',
					),
					array(
						'param' => 'post_format',
						'operator' => '==',
						'value' => 'video',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
		));

	}
}

add_action('acf/init', 'alchemists_acf_add_local_field_groups');
