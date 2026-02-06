<?php
/**
 * WPBakery Page Builder Functions
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   1.0.0
 * @version   4.7.0
 */

/**
 * Force WPBakery Page Builder to initialize as "built into the theme". This will hide certain tabs under the Settings->WPBakery Page Builder page
 */
add_action( 'vc_before_init', 'alchemists_vcSetAsTheme' );
function alchemists_vcSetAsTheme() {
	vc_set_as_theme( $disable_updater = true );
}

// Enable WPBakery Page Builder to post, page and custom post types
if( function_exists( 'vc_set_default_editor_post_types' ) ){
	vc_set_default_editor_post_types( array(
		'page',
		'post',
		'product',
		'sp_event',
		'sp_team',
		'sp_player',
		'sp_staff',
		'sp_list',
		'sp_calendar'
	) );
}

/**
 * Override default VC widget title
 */
add_filter('wpb_widget_title', 'alchemists_override_widget_title', 10, 2);
function alchemists_override_widget_title($output = '', $params = array('')) {
	$extraclass = (isset($params['extraclass'])) ? " " . $params['extraclass'] : "";
	return '<h4 class="' . $extraclass . '">' . $params['title'] . '</h4>';
}


/**
 * Customize default VC elements
 */
add_action( 'init', 'alchemists_customize_default_elements' );
if ( ! function_exists( 'alchemists_customize_default_elements' ) ) {
	function alchemists_customize_default_elements() {

		if ( function_exists( 'vc_remove_element' ) ) {
			vc_remove_element( 'vc_tta_pageable' );
			vc_remove_element( 'vc_pinterest' );
			vc_remove_element( 'vc_googleplus' );
			vc_remove_element( 'vc_facebook' );
			vc_remove_element( 'vc_tweetmeme' );
			vc_remove_element( 'vc_tta_tour' );
			vc_remove_element( 'vc_tta_tabs' );
			vc_remove_element( 'vc_toggle' );
			vc_remove_element( 'vc_flickr' );
		}
	}
}

/**
 * Add custom SimpleLine icons
 *
 * @param $icons - taken from filter - vc_map param field settings['source']
 *     provided icons (default empty array). If array categorized it will
 *     auto-enable category dropdown
 *
 * @since 1.0
 * @return array - of icons for iconpicker, can be categorized, or not.
 */
function alc_iconpicker_type_simpleline( $icons ) {
	/**
	 * @version 2.4.0
	 */
	$simpleline = array(
		array( 'icon-user' => 'user' ),
		array( 'icon-people' => 'people' ),
		array( 'icon-user-female' => 'user-female' ),
		array( 'icon-user-follow' => 'user-follow' ),
		array( 'icon-user-following' => 'user-following' ),
		array( 'icon-user-unfollow' => 'user-unfollow' ),
		array( 'icon-login' => 'login' ),
		array( 'icon-logout' => 'logout' ),
		array( 'icon-emotsmile' => 'emotsmile' ),
		array( 'icon-phone' => 'phone' ),
		array( 'icon-call-end' => 'call-end' ),
		array( 'icon-call-in' => 'call-in' ),
		array( 'icon-call-out' => 'call-out' ),
		array( 'icon-map' => 'map' ),
		array( 'icon-location-pin' => 'location-pin' ),
		array( 'icon-direction' => 'direction' ),
		array( 'icon-directions' => 'directions' ),
		array( 'icon-compass' => 'compass' ),
		array( 'icon-layers' => 'layers' ),
		array( 'icon-menu' => 'menu' ),
		array( 'icon-list' => 'list' ),
		array( 'icon-options-vertical' => 'options-vertical' ),
		array( 'icon-options' => 'options' ),
		array( 'icon-arrow-down' => 'arrow-down' ),
		array( 'icon-arrow-left' => 'arrow-left' ),
		array( 'icon-arrow-right' => 'arrow-right' ),
		array( 'icon-arrow-up' => 'arrow-up' ),
		array( 'icon-arrow-up-circle' => 'arrow-up-circle' ),
		array( 'icon-arrow-left-circle' => 'arrow-left-circle' ),
		array( 'icon-arrow-right-circle' => 'arrow-right-circle' ),
		array( 'icon-arrow-down-circle' => 'arrow-down-circle' ),
		array( 'icon-check' => 'check' ),
		array( 'icon-clock' => 'clock' ),
		array( 'icon-plus' => 'plus' ),
		array( 'icon-minus' => 'minus' ),
		array( 'icon-close' => 'close' ),
		array( 'icon-event' => 'event' ),
		array( 'icon-exclamation' => 'exclamation' ),
		array( 'icon-organization' => 'organization' ),
		array( 'icon-trophy' => 'trophy' ),
		array( 'icon-screen-smartphone' => 'screen-smartphone' ),
		array( 'icon-screen-desktop' => 'screen-desktop' ),
		array( 'icon-plane' => 'plane' ),
		array( 'icon-notebook' => 'notebook' ),
		array( 'icon-mustache' => 'mustache' ),
		array( 'icon-mouse' => 'mouse' ),
		array( 'icon-magnet' => 'magnet' ),
		array( 'icon-energy' => 'energy' ),
		array( 'icon-disc' => 'disc' ),
		array( 'icon-cursor' => 'cursor' ),
		array( 'icon-cursor-move' => 'cursor-move' ),
		array( 'icon-crop' => 'crop' ),
		array( 'icon-chemistry' => 'chemistry' ),
		array( 'icon-speedometer' => 'speedometer' ),
		array( 'icon-shield' => 'shield' ),
		array( 'icon-screen-tablet' => 'screen-tablet' ),
		array( 'icon-magic-wand' => 'magic-wand' ),
		array( 'icon-hourglass' => 'hourglass' ),
		array( 'icon-graduation' => 'graduation' ),
		array( 'icon-ghost' => 'ghost' ),
		array( 'icon-game-controller' => 'game-controller' ),
		array( 'icon-fire' => 'fire' ),
		array( 'icon-eyeglass' => 'eyeglass' ),
		array( 'icon-envelope-open' => 'envelope-open' ),
		array( 'icon-envelope-letter' => 'envelope-letter' ),
		array( 'icon-bell' => 'bell' ),
		array( 'icon-badge' => 'badge' ),
		array( 'icon-anchor' => 'anchor' ),
		array( 'icon-wallet' => 'wallet' ),
		array( 'icon-vector' => 'vector' ),
		array( 'icon-speech' => 'speech' ),
		array( 'icon-puzzle' => 'puzzle' ),
		array( 'icon-printer' => 'printer' ),
		array( 'icon-present' => 'present' ),
		array( 'icon-playlist' => 'playlist' ),
		array( 'icon-pin' => 'pin' ),
		array( 'icon-picture' => 'picture' ),
		array( 'icon-handbag' => 'handbag' ),
		array( 'icon-globe-alt' => 'globe-alt' ),
		array( 'icon-globe' => 'globe' ),
		array( 'icon-folder-alt' => 'folder-alt' ),
		array( 'icon-folder' => 'folder' ),
		array( 'icon-film' => 'film' ),
		array( 'icon-feed' => 'feed' ),
		array( 'icon-drop' => 'drop' ),
		array( 'icon-drawer' => 'drawer' ),
		array( 'icon-docs' => 'docs' ),
		array( 'icon-doc' => 'doc' ),
		array( 'icon-diamond' => 'diamond' ),
		array( 'icon-cup' => 'cup' ),
		array( 'icon-calculator' => 'calculator' ),
		array( 'icon-bubbles' => 'bubbles' ),
		array( 'icon-briefcase' => 'briefcase' ),
		array( 'icon-book-open' => 'book-open' ),
		array( 'icon-basket-loaded' => 'basket-loaded' ),
		array( 'icon-basket' => 'basket' ),
		array( 'icon-bag' => 'bag' ),
		array( 'icon-action-undo' => 'action-undo' ),
		array( 'icon-action-redo' => 'action-redo' ),
		array( 'icon-wrench' => 'wrench' ),
		array( 'icon-umbrella' => 'umbrella' ),
		array( 'icon-trash' => 'trash' ),
		array( 'icon-tag' => 'tag' ),
		array( 'icon-support' => 'support' ),
		array( 'icon-frame' => 'frame' ),
		array( 'icon-size-fullscreen' => 'size-fullscreen' ),
		array( 'icon-size-actual' => 'size-actual' ),
		array( 'icon-shuffle' => 'shuffle' ),
		array( 'icon-share-alt' => 'share-alt' ),
		array( 'icon-share' => 'share' ),
		array( 'icon-rocket' => 'rocket' ),
		array( 'icon-question' => 'question' ),
		array( 'icon-pie-chart' => 'pie-chart' ),
		array( 'icon-pencil' => 'pencil' ),
		array( 'icon-note' => 'note' ),
		array( 'icon-loop' => 'loop' ),
		array( 'icon-home' => 'home' ),
		array( 'icon-grid' => 'grid' ),
		array( 'icon-graph' => 'graph' ),
		array( 'icon-microphone' => 'microphone' ),
		array( 'icon-music-tone-alt' => 'music-tone-alt' ),
		array( 'icon-music-tone' => 'music-tone' ),
		array( 'icon-earphones-alt' => 'earphones-alt' ),
		array( 'icon-earphones' => 'earphones' ),
		array( 'icon-equalizer' => 'equalizer' ),
		array( 'icon-like' => 'like' ),
		array( 'icon-dislike' => 'dislike' ),
		array( 'icon-control-start' => 'control-start' ),
		array( 'icon-control-rewind' => 'control-rewind' ),
		array( 'icon-control-play' => 'control-play' ),
		array( 'icon-control-pause' => 'control-pause' ),
		array( 'icon-control-forward' => 'control-forward' ),
		array( 'icon-control-end' => 'control-end' ),
		array( 'icon-volume-1' => 'volume-1' ),
		array( 'icon-volume-2' => 'volume-2' ),
		array( 'icon-volume-off' => 'volume-off' ),
		array( 'icon-calendar' => 'calendar' ),
		array( 'icon-bulb' => 'bulb' ),
		array( 'icon-chart' => 'chart' ),
		array( 'icon-ban' => 'ban' ),
		array( 'icon-bubble' => 'bubble' ),
		array( 'icon-camrecorder' => 'camrecorder' ),
		array( 'icon-camera' => 'camera' ),
		array( 'icon-cloud-download' => 'cloud-download' ),
		array( 'icon-cloud-upload' => 'cloud-upload' ),
		array( 'icon-envelope' => 'envelope' ),
		array( 'icon-eye' => 'eye' ),
		array( 'icon-flag' => 'flag' ),
		array( 'icon-heart' => 'heart' ),
		array( 'icon-info' => 'info' ),
		array( 'icon-key' => 'key' ),
		array( 'icon-link' => 'link' ),
		array( 'icon-lock' => 'lock' ),
		array( 'icon-lock-open' => 'lock-open' ),
		array( 'icon-magnifier' => 'magnifier' ),
		array( 'icon-magnifier-add' => 'magnifier-add' ),
		array( 'icon-magnifier-remove' => 'magnifier-remove' ),
		array( 'icon-paper-clip' => 'paper-clip' ),
		array( 'icon-paper-plane' => 'paper-plane' ),
		array( 'icon-power' => 'power' ),
		array( 'icon-refresh' => 'refresh' ),
		array( 'icon-reload' => 'reload' ),
		array( 'icon-settings' => 'settings' ),
		array( 'icon-star' => 'star' ),
		array( 'icon-symbol-female' => 'symbol-female' ),
		array( 'icon-symbol-male' => 'symbol-male' ),
		array( 'icon-target' => 'target' ),
		array( 'icon-credit-card' => 'credit-card' ),
		array( 'icon-paypal' => 'paypal' ),
		array( 'icon-social-tumblr' => 'social-tumblr' ),
		array( 'icon-social-twitter' => 'social-twitter' ),
		array( 'icon-social-facebook' => 'social-facebook' ),
		array( 'icon-social-instagram' => 'social-instagram' ),
		array( 'icon-social-linkedin' => 'social-linkedin' ),
		array( 'icon-social-pinterest' => 'social-pinterest' ),
		array( 'icon-social-github' => 'social-github' ),
		array( 'icon-social-google' => 'social-google' ),
		array( 'icon-social-reddit' => 'social-reddit' ),
		array( 'icon-social-skype' => 'social-skype' ),
		array( 'icon-social-dribbble' => 'social-dribbble' ),
		array( 'icon-social-behance' => 'social-behance' ),
		array( 'icon-social-foursqare' => 'social-foursqare' ),
		array( 'icon-social-soundcloud' => 'social-soundcloud' ),
		array( 'icon-social-spotify' => 'social-spotify' ),
		array( 'icon-social-stumbleupon' => 'social-stumbleupon' ),
		array( 'icon-social-youtube' => 'social-youtube' ),
		array( 'icon-social-dropbox' => 'social-dropbox' ),
		array( 'icon-social-vkontakte' => 'social-vkontakte' ),
		array( 'icon-social-stea' => 'social-stea' ),
	);

	return array_merge( $icons, $simpleline );
}
add_filter( 'vc_iconpicker-type-simpleline', 'alc_iconpicker_type_simpleline' );


if ( ! function_exists( 'alc_enqueue_simpleline_icon_style_editor' ) ) {
	function alc_enqueue_simpleline_icon_style_editor() {
		wp_enqueue_style( 'alc-simpleline', get_template_directory_uri() . '/assets/fonts/simple-line-icons/css/simple-line-icons.css', array(), '2.4.0' );
	}
}
add_action( 'vc_backend_editor_enqueue_js_css', 'alc_enqueue_simpleline_icon_style_editor' );
add_action( 'vc_frontend_editor_enqueue_js_css', 'alc_enqueue_simpleline_icon_style_editor' );


/**
 * Add new elements to WPBakery Page Builder
 */


// Custom Elements
if ( function_exists( 'vc_map' ) ) {
	add_action( 'init', 'alchemists_vc_elements' );
}


// Blog Posts
if ( ! function_exists( 'alc_get_type_posts_data' ) ) {
	function alc_get_type_posts_data() {
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => apply_filters( 'alc_vc-posts-number', 99 ),
			'no_found_rows'  => true
		));
		$results = array();

		if ( $posts ) {
			foreach( $posts as $post ) {
				$results[] = array(
					'label' => $post->post_title,
					'value' => $post->ID
				);
			}
		}
		return $results;
	}
}

if ( ! function_exists( 'alchemists_vc_elements' ) ) {
	function alchemists_vc_elements() {

		// Post Categories
		$posts_categories = get_terms( array(
			'taxonomy' => 'category',
			'hide_empty' => false,
		) );
		$posts_categories_array = array();

		foreach( $posts_categories as $posts_category ) {
			$posts_categories_array[] = array(
				'label' => $posts_category->name,
				'value' => $posts_category->slug
			);
		}


		// Post Tags
		$posts_tags = get_terms( 'post_tag' );
		$posts_tags_array = array();

		foreach( $posts_tags as $posts_tag ) {
			$posts_tags_array[] = array(
				'label' => $posts_tag->name,
				'value' => $posts_tag->slug
			);
		}


		// Order by
		$order_by_array = array(
			esc_html__( 'Date', 'alchemists' )          => 'date',
			esc_html__( 'ID', 'alchemists' )            => 'ID',
			esc_html__( 'Author', 'alchemists' )        => 'author',
			esc_html__( 'Title', 'alchemists' )         => 'title',
			esc_html__( 'Modified', 'alchemists' )      => 'modified',
			esc_html__( 'Comment count', 'alchemists' ) => 'comment_count',
			esc_html__( 'Menu order', 'alchemists' )    => 'menu_order',
			esc_html__( 'Random', 'alchemists' )        => 'rand',
		);

		// Order
		$order_array = array(
			esc_html__( 'Descending', 'alchemists' ) => 'DESC',
			esc_html__( 'Ascending', 'alchemists' )  => 'ASC',
		);

		// Album Posts
		if ( ! function_exists( 'alc_get_type_albums_data' ) ) {
			function alc_get_type_albums_data() {
				$albums = get_posts( array(
					'post_type'      => 'albums',
					'posts_per_page' => apply_filters( 'alc_vc-albums-number', 99 ),
					'no_found_rows'  => true
				));
				$album_results = array();

				if ( $albums ) {
					foreach( $albums as $album ) {
						$album_results[] = array(
							'label' => $album->post_title,
							'value' => $album->ID
						);
					}
				}
				return $album_results;
			}
		}

		// Albums categories
		$album_categories = get_terms( 'catalbums' );
		$album_categories_array = array();

		if(!empty($album_categories) and !is_wp_error($album_categories)) {
			foreach ( $album_categories as $album_category ) {
				$album_categories_array[] = array(
					'label' => $album_category->name,
					'value' => $album_category->slug
				);
			}
		}


		// ALC: Images Carousel
		vc_map( array(
			'name'        => esc_html__( 'ALC: Awards Carousel', 'alchemists' ),
			'base'        => 'alc_images_carousel',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_images_carousel.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Animated carousel with images.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'attach_images',
					'heading'     => esc_html__( 'Images', 'alchemists' ),
					'param_name'  => 'images',
					'value'       => '',
					'description' => esc_html__( 'Select images from media library.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Images size', 'alchemists' ),
					'param_name'  => 'img_size',
					'value'       => 'full',
					'description' => esc_html__( 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size. If used slides per view, this will be used to define carousel wrapper size.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'On click action', 'alchemists' ),
					'param_name'  => 'onclick',
					'value'       => array(
						esc_html__( 'None', 'alchemists' ) => 'link_no',
						esc_html__( 'Open custom links', 'alchemists' ) => 'custom_link',
					),
					'description' => esc_html__( 'Select action for click event.', 'alchemists' ),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'autoplay',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'autoplay_speed',
					'description' => esc_html__( 'Autoplay Speed in ms (5000 = 5s).', 'alchemists' ),
					'value'       => '5000',
					'dependency' => array(
						'element' => 'autoplay',
						'value'   => array( '1' ),
					),
				),
				array(
					'type'        => 'exploded_textarea_safe',
					'heading'     => esc_html__( 'Custom links', 'alchemists' ),
					'param_name'  => 'custom_links',
					'description' => esc_html__( 'Enter links for each slide (Note: divide links with linebreaks (Enter)).', 'alchemists' ),
					'dependency' => array(
						'element' => 'onclick',
						'value'   => array( 'custom_link' ),
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Custom link target', 'alchemists' ),
					'param_name'  => 'custom_links_target',
					'description' => esc_html__( 'Select how to open custom links.', 'alchemists' ),
					'dependency'  => array(
						'element' => 'onclick',
						'value'   => array( 'custom_link' ),
					),
					'value'       => array(
						esc_html__( 'Same window', 'alchemists' ) => '_self',
						esc_html__( 'New window', 'alchemists' ) => '_blank',
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Achievements
		vc_map( array(
			'name'        => esc_html__( 'ALC: Achievements', 'alchemists' ),
			'base'        => 'alc_achievements',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_images_carousel.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Animated carousel for details.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'Achievement Items', 'alchemists' ),
					'param_name'  => 'items',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Add achievement', 'alchemists' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Add achievement', 'alchemists' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Add achievement', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'value'       => esc_html__( 'Title goes here', 'alchemists' ),
							'param_name'  => 'item_heading',
							'holder'      => 'div',
							'description' => esc_html__( 'Main title for achievement', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Subtitle', 'alchemists' ),
							'value'       => esc_html__( 'Subtitle', 'alchemists' ),
							'param_name'  => 'item_subheading',
							'holder'      => 'div',
							'description' => esc_html__( 'Subtitle for achievement', 'alchemists'),
						),
						array(
							'type'        => 'param_group',
							'heading'     => esc_html__( 'Achievement Info', 'alchemists' ),
							'param_name'  => 'sub_items',
							'value' => urlencode( json_encode( array(
								array(
									'label' => esc_html__( 'Add info', 'alchemists' ),
									'value' => '',
								),
								array(
									'label' => esc_html__( 'Add info', 'alchemists' ),
									'value' => '',
								),
								array(
									'label' => esc_html__( 'Add info', 'alchemists' ),
									'value' => '',
								),
							) ) ),
							'params' => array(
								array(
									'type'        => 'textfield',
									'heading'     => esc_html__( 'Info Title', 'alchemists' ),
									'value'       => esc_html__( 'Info Title', 'alchemists' ),
									'param_name'  => 'subitem_heading',
									'holder'      => 'div',
									'description' => esc_html__( 'Main title for achievement', 'alchemists'),
									'admin_label' => true,
								),
								array(
									'type'        => 'textfield',
									'heading'     => esc_html__( 'Info Subtitle', 'alchemists' ),
									'value'       => esc_html__( 'Info Subtitle', 'alchemists' ),
									'param_name'  => 'subitem_subheading',
									'holder'      => 'div',
									'description' => esc_html__( 'Subtitle for achievement', 'alchemists'),
									'admin_label' => true,
								),
							),
						),
					),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'autoplay',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'autoplay_speed',
					'description' => esc_html__( 'Autoplay Speed in ms (5000 = 5s).', 'alchemists' ),
					'value'       => '5000',
					'dependency' => array(
						'element' => 'autoplay',
						'value'   => array( '1' ),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Newslog
		vc_map( array(
			'name'        => esc_html__( 'ALC: Newslog Static', 'alchemists' ),
			'base'        => 'alc_newslog',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_newslog.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'js_view'     => 'VcColumnView',
			'as_parent'   => array(
				'only' => 'alc_newslog_item'
			),
			'description' => esc_html__( 'A list of items.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Newslog Item
		vc_map( array(
			'name'        => esc_html__( 'ALC: Newslog Item', 'alchemists' ),
			'base'        => 'alc_newslog_item',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_newslog.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'as_child'    => array(
				'only' => 'alc_newslog'
			),
			'description' => esc_html__( 'An item for log list.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Type', 'alchemists' ),
					'param_name'  => 'item_type',
					'description' => esc_html__( 'Select type of item', 'alchemists' ),
					'value'       => array(
						esc_html__( 'Injury', 'alchemists' ) => 'injury',
						esc_html__( 'Join', 'alchemists' ) => 'join',
						esc_html__( 'Exit', 'alchemists' ) => 'exit',
						esc_html__( 'Award', 'alchemists' ) => 'award',
						esc_html__( 'Other Positive', 'alchemists' ) => 'oth-pos',
						esc_html__( 'Other Negative', 'alchemists' ) => 'oth-neg',
					),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Content', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'Your description goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter a short text about event.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Date', 'alchemists' ),
					'param_name'  => 'date',
					'description' => esc_html__( 'Enter a date in any format.', 'alchemists' ),
					'admin_label' => true,
				),
			)
		) );


		// ALC: Social Buttons
		vc_map( array(
			'name'        => esc_html__( 'ALC: Social Buttons', 'alchemists' ),
			'base'        => 'alc_social_buttons',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_social_buttons.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A block with social buttons.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout Style', 'alchemists' ),
					'param_name'  => 'layout_style',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'default',
						esc_html__( 'Grid', 'alchemists' ) => 'grid',
						esc_html__( 'Columns', 'alchemists' ) => 'columns',
					),
					'description' => esc_html__( 'Select style for social buttons.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'Buttons', 'alchemists' ),
					'param_name'  => 'values',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Select Statistic', 'alchemists' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Select Statistic', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'param_name'  => 'btn_label',
							'holder'      => 'div',
							'value'       => esc_html__( 'Title goes here', 'alchemists' ),
							'description' => esc_html__( 'Enter short title.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Subtitle', 'alchemists' ),
							'param_name'  => 'btn_label_2',
							'holder'      => 'div',
							'value'       => esc_html__( 'Follow Me', 'alchemists' ),
							'description' => esc_html__( 'Enter short subtitle.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'URL', 'alchemists'),
							'param_name'  => 'btn_link',
							'holder'      => 'div',
							'description' => esc_html__( 'Enter URL to your social account.', 'alchemists' ),
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Social Network', 'alchemists'),
							'param_name'  => 'btn_type',
							'value'       => array(
								esc_html__( 'Facebook', 'alchemists' ) => 'fb',
								esc_html__( 'Twitter', 'alchemists' ) => 'twitter',
								esc_html__( 'Instagram', 'alchemists' ) => 'instagram',
								esc_html__( 'YouTube', 'alchemists' ) => 'youtube',
							),
							'holder'      => 'div',
							'description' => esc_html__( 'Select social network', 'alchemists' ),
						),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		if ( alchemists_sp_preset( 'esports' ) ) {
			// ALC: Twitch
			vc_map( array(
				'name'        => esc_html__( 'ALC: Twitch', 'alchemists' ),
				'base'        => 'alc_twitch',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_twitch_streams.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A block with social buttons.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Streamers', 'alchemists' ),
						'param_name'  => 'streamer',
						'description' => esc_html__( 'The username of a streamer. For instance: the_destroy Comma separate multiple streamers as follows: the_destroy, fayedbebop', 'alchemists' ),
						'value'       => 'the_destroy',
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Size', 'alchemists'),
						'param_name'  => 'size',
						'value'       => array(
							esc_html__( 'Large', 'alchemists' ) => 'large',
							esc_html__( 'Small', 'alchemists' ) => 'small',
							esc_html__( 'First Large, Others Small', 'alchemists' ) => 'large-first',
						),
						'description' => esc_html__( 'Select size of streams', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Preview', 'alchemists'),
						'param_name'  => 'preview',
						'value'       => array(
							esc_html__( 'Image', 'alchemists' ) => 'image',
							esc_html__( 'Video', 'alchemists' ) => 'video',
							esc_html__( 'First Video, Others Images', 'alchemists' ) => 'video-first',
						),
						'description' => esc_html__( 'Select preview type', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Maximum Amount of Streams', 'alchemists' ),
						'param_name'  => 'max',
						'description' => esc_html__( 'Limits the number of Streams', 'alchemists' ),
						'value'       => '3',
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );
		}



		// ALC: Post Loop
		vc_map( array(
			'name'        => esc_html__( 'ALC: Post Loop', 'alchemists' ),
			'base'        => 'alc_post_loop',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_loop.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Posts in grid, list etc.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Button URL (Link)', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add button to header.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'alchemists'),
					'param_name'  => 'posts_layout',
					'value'       => apply_filters( 'alc_vc-post_loop_layout', array(
						esc_html__( 'Cards - 2 cols', 'alchemists' ) => 'grid_2cols',
						esc_html__( 'Cards - 3 cols', 'alchemists' ) => 'grid_3cols',
						esc_html__( 'Cards Tile - 2 cols', 'alchemists' ) => 'grid_tile_2cols',
						esc_html__( 'List - Card', 'alchemists' ) => 'grid_1col',
						esc_html__( 'List - Card Tile', 'alchemists' ) => 'grid_1col_tile',
						esc_html__( 'List - Card Tile Small', 'alchemists' ) => 'grid_1col_tile_sm',
						esc_html__( 'List - Left Thumb', 'alchemists' ) => 'list_left_thumb',
						esc_html__( 'List - Center Thumb', 'alchemists' ) => 'list_lg_thumb',
						esc_html__( 'List - Simple', 'alchemists' ) => 'list_simple',
						esc_html__( 'List - Simple (1st Post Extented)', 'alchemists' ) => 'list_simple_1st_ext',
						esc_html__( 'List - Simple Horizontal', 'alchemists' ) => 'list_simple_hor',
						esc_html__( 'List - Small Thumb', 'alchemists' ) => 'list_thumb_sm',
						esc_html__( 'Cards - Masonry', 'alchemists' ) => 'masonry',
					) ),
					'description' => esc_html__( 'Select Post Layout', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Image Size', 'alchemists' ),
					'param_name'  => 'img_size',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'default',
					) + alchemists_get_image_sizes() + array(
						esc_html__( 'Full Image', 'alchemists' ) => 'full',
					),
					'description' => esc_html__( 'Select image size. Note: there is no post image for some layouts.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items per page', 'alchemists' ),
					'param_name' => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value' => '10',
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Add Pagination?', 'alchemists' ),
					'param_name'    => 'add_pagination',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Adds pagination after posts.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Exclude Posts by ID', 'alchemists' ),
					'param_name'  => 'exclude_posts',
					'description' => esc_html__( 'Enter Post IDs you want to exclude separated by comma.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Exclude Posts by Tags', 'alchemists' ),
					'param_name' => 'exclude_taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Posts with selected tags are excluded.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'heading'       => esc_html__( 'Ignore Sticky Posts', 'alchemists' ),
					'type'          => 'checkbox',
					'param_name'    => 'ignore_sticky_posts',
					'std'           => '1',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If enabled sticky posts will be ordered normally.', 'alchemists' ),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Disable Excerpt?', 'alchemists' ),
					'param_name'  => 'disable_excerpt',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If checked Excerpt will be hidden.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Excerpt Size (in words)', 'alchemists' ),
					'param_name'  => 'excerpt_size',
					'description' => esc_html__( 'Enter the number of words for Excerpt.', 'alchemists' ),
					'dependency' => array(
						'element' => 'disable_excerpt',
						'value_not_equal_to' => array( '1' ),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Post Grid
		vc_map( array(
			'name'        => esc_html__( 'ALC: Post Grid', 'alchemists' ),
			'base'        => 'alc_post_grid',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_loop.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Posts in grid', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Button URL (Link)', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add button to header.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'alchemists'),
					'param_name'  => 'posts_layout',
					'value'       => array(
						esc_html__( 'Layout 1-2', 'alchemists' ) => 'layout_1',
						esc_html__( 'Layout 1-2 (Card Tile)', 'alchemists' ) => 'layout_4',
						esc_html__( 'Layout 1-2-1', 'alchemists' ) => 'layout_5',
						esc_html__( 'Layout 3-4', 'alchemists' ) => 'layout_2',
						esc_html__( 'Layout 2x3-1-3', 'alchemists' ) => 'layout_3',
					),
					'description' => esc_html__( 'Select Post Layout', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Image Effect', 'alchemists'),
					'param_name'  => 'img_effect',
					'value'       => array(
						esc_html__( 'Duotone Base', 'alchemists' ) => 'duotone_base',
						esc_html__( 'Duotone Categories', 'alchemists' ) => 'duotone_cat',
						esc_html__( 'Dark Gradient Overlay', 'alchemists' ) => 'gradient',
						esc_html__( 'No Effect', 'alchemists' ) => 'no_effect',
					),
					'description' => esc_html__( 'Select Post Layout', 'alchemists' ),
					'dependency' => array(
						'element' => 'posts_layout',
						'value_not_equal_to' => array( 'layout_4', 'layout_5' ),
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items per page', 'alchemists' ),
					'param_name' => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value' => '10',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Exclude Posts by ID', 'alchemists' ),
					'param_name'  => 'exclude_posts',
					'description' => esc_html__( 'Enter Post IDs you want to exclude separated by comma.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Post Slider
		vc_map( array(
			'name'        => esc_html__( 'ALC: Post Slider', 'alchemists' ),
			'base'        => 'alc_post_slider',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_slider.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Animated filtered posts slider.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Slides to show', 'alchemists'),
					'param_name'  => 'slide_to_show',
					'value'       => array(
						esc_html__( '1 Slide', 'alchemists' ) => 'slide_1',
						esc_html__( '4 Slides', 'alchemists' ) => 'slide_4',
					),
					'description' => esc_html__( 'Select Post Layout', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Slide Image Size', 'alchemists' ),
					'param_name'  => 'img_size',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'default',
					) + alchemists_get_image_sizes() + array(
						esc_html__( 'Full Image', 'alchemists' ) => 'full',
					),
					'description' => esc_html__( 'Select image size.', 'alchemists' ),
				),
				array(
					'heading'       => esc_html__( 'Add Categories Filter?', 'alchemists' ),
					'type'          => 'checkbox',
					'param_name'    => 'display_filter',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'This filter will be shown if categories selected.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
					'dependency' => array(
						'element' => 'display_filter',
						'value'   => array( '1' ),
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items per page', 'alchemists' ),
					'param_name' => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value' => '10',
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'autoplay',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'autoplay_speed',
					'description' => esc_html__( 'Autoplay Speed (in ms).', 'alchemists' ),
					'value'       => '5000',
					'dependency' => array(
						'element' => 'autoplay',
						'value'   => array( '1' ),
					),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Prev/Nav Arrows?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'arrows',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Show next/prev buttons.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Post Carousel
		vc_map( array(
			'name'        => esc_html__( 'ALC: Post Carousel', 'alchemists' ),
			'base'        => 'alc_post_carousel',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_carousel.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Animated filtered posts carousel.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Items per page', 'alchemists' ),
					'param_name'  => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value'       => '10',
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'autoplay',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'autoplay_speed',
					'description' => esc_html__( 'Autoplay Speed (in ms)', 'alchemists' ),
					'value'       => '7000',
					'dependency' => array(
						'element' => 'autoplay',
						'value'   => array( '1' ),
					),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Prev/Nav Arrows?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'arrows',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Show next/prev buttons.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Featured Post
		vc_map( array(
			'name'        => esc_html__( 'ALC: Featured Post', 'alchemists' ),
			'base'        => 'alc_featured_post',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_featured_post.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A single post as a banner.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Style', 'alchemists'),
					'param_name'  => 'post_style',
					'value'       => array(
						esc_html__( 'Style 1', 'alchemists' ) => 'style_1',
						esc_html__( 'Style 2', 'alchemists' ) => 'style_2',
					),
					'description' => esc_html__( 'Select Post Style.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Post', 'alchemists' ),
					'param_name' => 'post_id',
					'description' => __( 'Add specific post, etc. by title. Note, post is set, then Tags and Categories filter will be ignored.', 'alchemists' ),
					'settings' => array(
						'multiple' => false,
						'min_length' => 1,
						'groups' => false,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => alc_get_type_posts_data()
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__( 'Image', 'alchemists' ),
					'param_name'  => 'image',
					'value'       => '',
					'description' => esc_html__( 'Select image from media library.', 'alchemists' ),
					'dependency' => array(
						'element' => 'post_style',
						'value'   => array( 'style_1' ),
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Disable Categories?', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'disable_cats',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If checked categories label(s) will be hidden.', 'alchemists' ),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Custom Title', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'content',
					'description' => esc_html__( 'Enter your custom title here.', 'alchemists' ),
					'value'       => '',
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Disable Excerpt?', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'disable_excerpt',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If checked Excerpt will be hidden.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Excerpt Size (in words)', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'excerpt_size',
					'description' => esc_html__( 'Enter the number of words for Excerpt.', 'alchemists' ),
					'value'       => '13',
					'dependency' => array(
						'element' => 'disable_excerpt',
						'value_not_equal_to' => array( '1' ),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Custom Excerpt', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'custom_excerpt',
					'description' => esc_html__( 'Enter your custom excerpt here.', 'alchemists' ),
					'value'       => '',
					'dependency' => array(
						'element' => 'disable_excerpt',
						'value_not_equal_to' => array( '1' ),
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Disable Button?', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'disable_btn',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If checked button will be hidden.', 'alchemists' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Custom Button', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'btn',
					'description' => esc_html__( 'Custom button link and label.', 'alchemists' ),
					'dependency'  => array(
						'element' => 'disable_btn',
						'value_not_equal_to' => array( '1' ),
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Disable Date?', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'disable_date',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'If checked date will be hidden.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Custom Date/Subtitle', 'alchemists' ),
					'group'       => esc_html__( 'Customize', 'alchemists' ),
					'param_name'  => 'custom_date',
					'description' => esc_html__( 'Enter your custom date/subtitle here.', 'alchemists' ),
					'value'       => '',
					'dependency' => array(
						'element' => 'disable_date',
						'value_not_equal_to' => array( '1' ),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Text Ticker
		vc_map( array(
			'name'        => esc_html__( 'ALC: Text Ticker', 'alchemists' ),
			'base'        => 'alc_text_ticker',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_slider.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Posts animated feed.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Number of Posts', 'alchemists' ),
					'param_name' => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value' => '10',
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Include only', 'alchemists' ),
					'param_name' => 'include',
					'description' => __( 'Add specific posts, etc. by title.', 'alchemists' ),
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => false,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => alc_get_type_posts_data()
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show Excerpt?', 'alchemists' ),
					'param_name'  => 'show_excerpt',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'std'         => '1',
					'description' => esc_html__( 'If checked Excerpt will be visible.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Excerpt Length', 'alchemists' ),
					'param_name' => 'excerpt_length',
					'description' => esc_html__( 'Number of words in the excerpt.', 'alchemists' ),
					'value' => '6',
					'dependency' => array(
						'element' => 'show_excerpt',
						'value'   => '1',
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Show Categories?', 'alchemists' ),
					'param_name'  => 'show_categories',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'std'         => '0',
					'description' => esc_html__( 'If checked Category labels will be displayed.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'group'       => esc_html__( 'Settings', 'alchemists' ),
					'heading'     => esc_html__( 'Layout', 'alchemists'),
					'param_name'  => 'layout',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'default',
						esc_html__( 'Boxed', 'alchemists' )   => 'boxed',
					),
					'description' => esc_html__( 'Select layout.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'group'       => esc_html__( 'Settings', 'alchemists'),
					'heading'     => esc_html__( 'Speed', 'alchemists' ),
					'param_name'  => 'animation_speed',
					'description' => esc_html__( 'Speed allows you to set a relatively constant marquee speed regardless of the width of the containing element. Speed is measured in pixels per second', 'alchemists' ),
					'value'       => '50',
				),
				array(
					'type'        => 'checkbox',
					'group'       => esc_html__( 'Settings', 'alchemists'),
					'heading'     => esc_html__( 'Pause on Hover', 'alchemists' ),
					'param_name'  => 'pause_on_hover',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'std'         => '1',
					'description' => esc_html__( 'On hover pause the marquee.', 'alchemists' ),
				),
				array(
					'type'        => 'checkbox',
					'group'       => esc_html__( 'Settings', 'alchemists'),
					'heading'     => esc_html__( 'Start Visible', 'alchemists' ),
					'param_name'  => 'start_visible',
					'value'       => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'std'         => '1',
					'description' => esc_html__( 'If enabled the marquee will be visible in the start.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Post Grid Slider
		vc_map( array(
			'name'        => esc_html__( 'ALC: Post Grid Slider', 'alchemists' ),
			'base'        => 'alc_post_grid_slider',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_post_slider.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Animated posts feed.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'taxonomies_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Tags', 'alchemists' ),
					'param_name' => 'taxonomies_tags',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $posts_tags_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by tags.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Number of Posts', 'alchemists' ),
					'param_name' => 'items_per_page',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					'value' => '9',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'alchemists'),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'layout',
					'value'       => array(
						esc_html__( 'Default (3-3)', 'alchemists' )      => '3-3',
						esc_html__( 'First bigger (1-2)', 'alchemists' ) => '1-2',
					),
					'description' => esc_html__( 'Select posts layout.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'autoplay',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
					'group'       => esc_html__( 'Options', 'alchemists' ),
					'param_name'  => 'autoplay_speed',
					'description' => esc_html__( 'Autoplay Speed (in ms)', 'alchemists' ),
					'value'       => '5000',
					'dependency' => array(
						'element' => 'autoplay',
						'value'   => array( '1' ),
					),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Prev/Nav Arrows?', 'alchemists' ),
					'group'         => esc_html__( 'Options', 'alchemists' ),
					'param_name'    => 'arrows',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Show next/prev buttons.', 'alchemists' ),
				),
				array(
					'type'          => 'checkbox',
					'heading'       => esc_html__( 'Add Duotone effect?', 'alchemists' ),
					'group'         => esc_html__( 'Styling', 'alchemists' ),
					'param_name'    => 'duotone_effect',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Adds duotone effect to thumbnails.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Button
		vc_map( array(
			'name'        => esc_html__( 'ALC: Button', 'alchemists' ),
			'base'        => 'alc_btn',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_btn.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Simple button.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Text', 'alchemists' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Text on the button', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'URL (Link)', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add link to button.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Type', 'alchemists' ),
					'param_name'  => 'type',
					'value'       => array(
						esc_html__( 'Fill', 'alchemists' )    => 'fill',
						esc_html__( 'Outline', 'alchemists' ) => 'outline',
					),
					'description' => esc_html__( 'Select button type.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Color', 'alchemists' ),
					'param_name'  => 'color',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' )         => 'default',
						esc_html__( 'Default Alt', 'alchemists' )     => 'default-alt',
						esc_html__( 'Primary', 'alchemists' )         => 'primary',
						esc_html__( 'Primary Inverse', 'alchemists' ) => 'primary-inverse',
						esc_html__( 'Success', 'alchemists' )         => 'success',
						esc_html__( 'Info', 'alchemists' )            => 'info',
						esc_html__( 'Warning', 'alchemists' )         => 'warning',
						esc_html__( 'Danger', 'alchemists' )          => 'danger',
					),
					'description' => esc_html__( 'Select button color style.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Size', 'alchemists' ),
					'param_name'  => 'size',
					'value'       => array(
						esc_html__( 'Large', 'alchemists' )       => 'lg',
						esc_html__( 'Medium', 'alchemists' )      => 'medium',
						esc_html__( 'Small', 'alchemists' )       => 'sm',
						esc_html__( 'Extra Small', 'alchemists' ) => 'xs',
					),
					'description' => esc_html__( 'Select button size.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Alignment', 'alchemists' ),
					'param_name'  => 'align',
					'description' => esc_html__( 'Select button alignment.', 'alchemists' ),
					'value'       => array(
						esc_html__( 'Inline', 'alchemists' ) => 'inline',
						// default as well
						esc_html__( 'Left', 'alchemists' )   => 'left',
						// default as well
						esc_html__( 'Right', 'alchemists' )  => 'right',
						esc_html__( 'Center', 'alchemists' ) => 'center',
					),
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Set full width button?', 'alchemists' ),
					'param_name'  => 'btn_block',
					'dependency' => array(
						'element'            => 'align',
						'value_not_equal_to' => 'inline',
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
			)
		) );



		// ALC: Icobox
		vc_map( array(
			'name'        => esc_html__( 'ALC: Icobox', 'alchemists' ),
			'base'        => 'alc_icobox',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_icobox.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Block with icon and text.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Title', 'alchemists' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Title of the box', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'textarea',
					'heading'     => esc_html__( 'Description', 'alchemists' ),
					'param_name'  => 'description',
					'value'       => esc_html__( 'Your description goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter a short text here.', 'alchemists' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'URL (Link)', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add link to the box.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Alignment', 'alchemists' ),
					'param_name'  => 'align',
					'description' => esc_html__( 'Select box alignment.', 'alchemists' ),
					'value'       => array(
						esc_html__( 'Center', 'alchemists' ) => 'center',
						esc_html__( 'Left', 'alchemists' )   => 'left',
						esc_html__( 'Right', 'alchemists' )  => 'right',
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Icon library', 'alchemists' ),
					'value' => array(
						esc_html__( 'Font Awesome', 'alchemists' ) => 'fontawesome',
						esc_html__( 'Open Iconic', 'alchemists' ) => 'openiconic',
						esc_html__( 'Typicons', 'alchemists' ) => 'typicons',
						esc_html__( 'Entypo', 'alchemists' ) => 'entypo',
						esc_html__( 'Linecons', 'alchemists' ) => 'linecons',
						esc_html__( 'Mono Social', 'alchemists' ) => 'monosocial',
						esc_html__( 'Material', 'alchemists' ) => 'material',
						esc_html__( 'SimpleLine', 'alchemists' ) => 'simpleline',
					),
					'admin_label' => true,
					'param_name' => 'i_type',
					'description' => esc_html__( 'Select icon library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_fontawesome',
					'value' => 'fas fa-adjust',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display, we use (big number) to display all icons in single page
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'fontawesome',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_openiconic',
					'value' => 'vc-oi vc-oi-dial',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'openiconic',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'openiconic',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_typicons',
					'value' => 'typcn typcn-adjust-brightness',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'typicons',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'typicons',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_entypo',
					'value' => 'entypo-icon entypo-icon-note',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'entypo',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'entypo',
					),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_linecons',
					'value' => 'vc_li vc_li-heart',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'linecons',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'linecons',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_monosocial',
					'value' => 'vc-mono vc-mono-fivehundredpx',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'monosocial',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'monosocial',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_material',
					'value' => 'vc-material vc-material-cake',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'material',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'material',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type' => 'iconpicker',
					'heading' => esc_html__( 'Icon', 'alchemists' ),
					'param_name' => 'i_icon_simpleline',
					'value' => 'icon-like',
					// default value to backend editor admin_label
					'settings' => array(
						'emptyIcon' => false,
						// default true, display an "EMPTY" icon?
						'type' => 'simpleline',
						'iconsPerPage' => 500,
						// default 100, how many icons per/page to display
					),
					'dependency' => array(
						'element' => 'i_type',
						'value' => 'simpleline',
					),
					'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Icon Holder Color', 'alchemists' ),
					'param_name'  => 'style',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'border',
						esc_html__( 'Primary', 'alchemists' ) => 'filled',
						esc_html__( 'Custom', 'alchemists' )  => 'custom',
					),
					'description' => esc_html__( 'Select icon holder color.', 'alchemists' ),
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Custom Icon Holder Color', 'alchemists' ),
					'param_name'  => 'custom_color_holder',
					'description' => esc_html__( 'Select custom icon holder color.', 'alchemists' ),
					'dependency'  => array(
						'element' => 'style',
						'value'   => 'custom',
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Icon Color', 'alchemists' ),
					'param_name'  => 'i_color',
					'value'       => array(
						esc_html__( 'Default', 'alchemists' ) => 'border',
						esc_html__( 'Custom', 'alchemists' )  => 'custom',
					),
					'description' => esc_html__( 'Select icon color.', 'alchemists' ),
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__( 'Custom Icon Color', 'alchemists' ),
					'param_name'  => 'custom_color_icon',
					'description' => esc_html__( 'Select custom icon color.', 'alchemists' ),
					'dependency'  => array(
						'element' => 'i_color',
						'value'   => 'custom',
					),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Icon Holder Size', 'alchemists' ),
					'param_name'  => 'size',
					'value'       => array(
						esc_html__( 'Large', 'alchemists' )       => 'lg',
						esc_html__( 'Medium', 'alchemists' )      => 'medium',
					),
					'description' => esc_html__( 'Select box size.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Icon Holder Shape', 'alchemists' ),
					'param_name'  => 'shape',
					'value'       => array(
						esc_html__( 'Circle', 'alchemists' )      => 'circle',
						esc_html__( 'Rounded', 'alchemists' )     => 'rounded',
						esc_html__( 'Square', 'alchemists' )      => 'square',
					),
					'description' => esc_html__( 'Select box shape.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Alert
		vc_map( array(
			'name'        => esc_html__( 'ALC: Alert', 'alchemists' ),
			'base'        => 'alc_alert',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_alert.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Simple Alert block.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Style', 'alchemists' ),
					'param_name'  => 'style',
					'value'       => array(
						esc_html__( 'Success', 'alchemists' )  => 'success',
						esc_html__( 'Info', 'alchemists' )     => 'info',
						esc_html__( 'Warning', 'alchemists' )  => 'warning',
						esc_html__( 'Danger', 'alchemists' )   => 'danger',
					),
					'description' => esc_html__( 'Select style type.', 'alchemists' ),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Message text', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'I am message box. Click edit button to change this text.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Dismissible?', 'alchemists' ),
					'param_name'  => 'dismissible',
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Box
		vc_map( array(
			'name'        => esc_html__( 'ALC: Box', 'alchemists' ),
			'base'        => 'alc_box',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_box.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A simple box.', 'alchemists' ),
			'js_view'     => 'VcColumnView',
			'as_parent'   => array(
				'except' => 'alc_event_blocks_sm, alc_event_scoreboard, alc_images_carousel, alc_games_history, alc_team_stats, alc_team_points_history, alc_newslog_item, alc_team_leaders, alc_staff_bio_card, alc_post_loop, alc_post_grid, alc_post_slider, alc_post_carousel, alc_player_stats, alc_player_gbg_stats, alc_newslog',
			),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Box Info
		vc_map( array(
			'name'        => esc_html__( 'ALC: Box Info', 'alchemists' ),
			'base'        => 'alc_box_info',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_box.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A simple box with info.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Content', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'Your content goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter your content.', 'alchemists' ),
				),
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'Footer', 'alchemists' ),
					'param_name'  => 'values',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Add element', 'alchemists' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Add element', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'param_name'  => 'el_title',
							'holder'      => 'div',
							'value'       => esc_html__( 'Your Title goes here', 'alchemists' ),
							'description' => esc_html__( 'Enter short title.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'URL', 'alchemists'),
							'param_name'  => 'el_link',
							'holder'      => 'div',
							'description' => esc_html__( 'Enter URL to the element.', 'alchemists' ),
						),
						array(
							'type' => 'dropdown',
							'heading' => esc_html__( 'Icon library', 'alchemists' ),
							'value' => array(
								esc_html__( 'Font Awesome', 'alchemists' ) => 'fontawesome',
								esc_html__( 'Open Iconic', 'alchemists' ) => 'openiconic',
								esc_html__( 'Typicons', 'alchemists' ) => 'typicons',
								esc_html__( 'Entypo', 'alchemists' ) => 'entypo',
								esc_html__( 'Linecons', 'alchemists' ) => 'linecons',
								esc_html__( 'Mono Social', 'alchemists' ) => 'monosocial',
								esc_html__( 'Material', 'alchemists' ) => 'material',
								esc_html__( 'SimpleLine', 'alchemists' ) => 'simpleline',
							),
							'param_name' => 'i_type',
							'description' => esc_html__( 'Select icon library.', 'alchemists' ),
							'dependency'  => array(
								'element' => 'stat_icon',
								'value'   => array( 'icon_custom_font' )
							)
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_fontawesome',
							'value' => 'fas fa-adjust',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display, we use (big number) to display all icons in single page
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'fontawesome',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_openiconic',
							'value' => 'vc-oi vc-oi-dial',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'openiconic',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'openiconic',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_typicons',
							'value' => 'typcn typcn-adjust-brightness',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'typicons',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'typicons',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_entypo',
							'value' => 'entypo-icon entypo-icon-note',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'entypo',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'entypo',
							),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_linecons',
							'value' => 'vc_li vc_li-heart',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'linecons',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'linecons',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_monosocial',
							'value' => 'vc-mono vc-mono-fivehundredpx',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'monosocial',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'monosocial',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_material',
							'value' => 'vc-material vc-material-cake',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'material',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'material',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
						array(
							'type' => 'iconpicker',
							'heading' => esc_html__( 'Icon', 'alchemists' ),
							'param_name' => 'i_icon_simpleline',
							'value' => 'icon-like',
							// default value to backend editor admin_label
							'settings' => array(
								'emptyIcon' => false,
								// default true, display an "EMPTY" icon?
								'type' => 'simpleline',
								'iconsPerPage' => 500,
								// default 100, how many icons per/page to display
							),
							'dependency' => array(
								'element' => 'i_type',
								'value' => 'simpleline',
							),
							'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
						),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Description List
		vc_map( array(
			'name'        => esc_html__( 'ALC: Description List', 'alchemists' ),
			'base'        => 'alc_dl',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_box.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A list with description.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Widget title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'List of items', 'alchemists' ),
					'param_name'  => 'values',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Add item', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'param_name'  => 'el_title',
							'holder'      => 'div',
							'value'       => esc_html__( 'Title', 'alchemists' ),
							'description' => esc_html__( 'Enter short title.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Description', 'alchemists' ),
							'param_name'  => 'el_desc',
							'holder'      => 'div',
							'value'       => esc_html__( 'Description goes here', 'alchemists' ),
							'description' => esc_html__( 'Enter description.', 'alchemists'),
						),
						array(
							'heading'       => esc_html__( 'Add Link?', 'alchemists' ),
							'type'          => 'checkbox',
							'param_name'    => 'el_link_is_active',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'If enabled the item value has a link.', 'alchemists' ),
						),
						array(
							'type'        => 'vc_link',
							'heading'     => esc_html__( 'Link', 'alchemists' ),
							'param_name'  => 'el_link',
							'description' => esc_html__( 'Add link to the item.', 'alchemists' ),
							'std'         => '0',
							'dependency' => array(
								'element' => 'el_link_is_active',
								'value'   => array( '1' ),
							),
						),
						array(
							'heading'       => esc_html__( 'Highlight?', 'alchemists' ),
							'type'          => 'checkbox',
							'param_name'    => 'el_is_active',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'If enabled the item value is highlighted.', 'alchemists' ),
						),
					),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Footer', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'Your content goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter your content.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Jumbotron
		vc_map( array(
			'name'        => esc_html__( 'ALC: Jumbotron', 'alchemists' ),
			'base'        => 'alc_jumbotron',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_featured_post.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Showcase hero unit style content.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Title', 'alchemists' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Join Our Team!', 'alchemists' ),
					'description' => esc_html__( 'Enter text used as widget title.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'heading'       => esc_html__( 'Enable Custom Title Markup', 'alchemists' ),
					'type'          => 'checkbox',
					'param_name'    => 'custom_title_markup_is_active',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Enable if you need a custom markup for the title.', 'alchemists' ),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Custom Title', 'alchemists' ),
					'param_name'  => 'custom_title',
					'value'       => __( '<span>Join</span> Our Team!', 'alchemists' ),
					'description' => esc_html__( 'Enter custom markup as widget title.', 'alchemists' ),
					'dependency' => array(
							'element'        => 'custom_title_markup_is_active',
							'value' => array( '1' ),
						),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Subtitle', 'alchemists' ),
					'param_name'  => 'subtitle',
					'value'       => esc_html__( 'Ready to be a hero?', 'alchemists' ),
					'description' => esc_html__( 'Enter text used as widget subtitle.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Description', 'alchemists' ),
					'param_name'  => 'description',
					'value'       => esc_html__( 'Your short description text goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter text used as description.', 'alchemists' ),
				),
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__( 'Hero Image', 'alchemists' ),
					'param_name'  => 'image',
					'value'       => '',
					'description' => esc_html__( 'Select image from media library.', 'alchemists' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Link', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add link to the element.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );



		// ALC: Accordion
		vc_map( array(
			'name'        => esc_html__( 'ALC: Accordion', 'alchemists' ),
			'base'        => 'alc_accordion',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_box.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'js_view'     => 'VcColumnView',
			'as_parent'   => array(
				'only' => 'alc_accordion_item'
			),
			'description' => esc_html__( 'Collapsible content panels.', 'alchemists' ),
			'params'      => array(
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'value'       => 'accordionFaqs',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Accordion Item
		vc_map( array(
			'name'        => esc_html__( 'ALC: Accordion Item', 'alchemists' ),
			'base'        => 'alc_accordion_item',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_box.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'as_child'    => array(
				'only' => 'alc_accordion'
			),
			'description' => esc_html__( 'A panel for accordion.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Panel Title', 'alchemists' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Panel Title goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter text used as panel title.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Content', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'Your content goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter content here.', 'alchemists' ),
				),
				array(
					'heading'       => esc_html__( 'Active?', 'alchemists' ),
					'type'          => 'checkbox',
					'param_name'    => 'panel_is_active',
					'value'         => array(
						esc_html__( 'Yes', 'alchemists' ) => '1'
					),
					'description' => esc_html__( 'Select currently active panel.', 'alchemists' ),
				),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Parent Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'value'       => 'accordionFaqs',
					'description' => esc_html__( 'Enter parent element ID (Note: Should be the same as parent Accordion ID).', 'alchemists' ),
				),
			)
		) );



		// ALC: Woo Banner
		vc_map( array(
			'name'        => esc_html__( 'ALC: Woo Banner', 'alchemists' ),
			'base'        => 'alc_woo_banner',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_woo_banner.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A simple banner.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Title', 'alchemists' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'Enter a short title.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Subtitle', 'alchemists' ),
					'param_name'  => 'subtitle',
					'description' => esc_html__( 'Enter a short subtitle.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Subtitle - Secondary', 'alchemists' ),
					'param_name'  => 'subtitle_2',
					'description' => esc_html__( 'Enter a short subtitle.', 'alchemists' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Button', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add link to the button.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Discount Text', 'alchemists' ),
					'param_name'  => 'discount_txt',
					'description' => esc_html__( 'Enter a short text.', 'alchemists' ),
					'value'       => esc_html__( 'Only', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Discount Price', 'alchemists' ),
					'param_name'  => 'discount_price',
					'description' => esc_html__( 'Enter your price.', 'alchemists' ),
					'value'       => '$50',
				),
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__( 'Image', 'alchemists' ),
					'param_name'  => 'image',
					'value'       => '',
					'description' => esc_html__( 'Select image from media library.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Albums
		vc_map( array(
			'name'        => esc_html__( 'ALC: Albums', 'alchemists' ),
			'base'        => 'alc_albums',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_albums.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'Albums element.', 'alchemists' ),
			'params'      => array(
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Album', 'alchemists' ),
					'param_name' => 'post_id',
					'description' => __( 'Add specific album, etc. by title. Note, post is set, then Categories filter will be ignored.', 'alchemists' ),
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => false,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => alc_get_type_albums_data()
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => esc_html__( 'Categories', 'alchemists' ),
					'param_name' => 'album_categories',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
						'sortable' => true,
						'no_hide' => true,
						'values' => $album_categories_array
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => esc_html__( 'Filter posts by categories.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Layout', 'alchemists'),
					'param_name'  => 'posts_layout',
					'value'       => array(
						esc_html__( 'Grid 2 cols', 'alchemists' ) => 'grid_2cols',
						esc_html__( 'Grid 3 cols', 'alchemists' ) => 'grid_3cols',
						esc_html__( 'Grid 4 cols', 'alchemists' ) => 'grid_4cols',
					),
					'description' => esc_html__( 'Select Albums Layout', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Album Type', 'alchemists'),
					'param_name'  => 'album_type',
					'value'       => array(
						esc_html__( 'Image Top + Title Bottom', 'alchemists' )     => 'default',
						esc_html__( 'Title Top + Image Bottom', 'alchemists' ) => 'heading_top',
						esc_html__( 'Image Top + Thumbs + Title Bottom', 'alchemists' ) => 'img_top_thumbs_title_bottom',
					),
					'description' => esc_html__( 'Select Albums Type', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Display Style', 'alchemists' ),
					'param_name' => 'display_style',
					'value' => array(
						esc_html__( 'Show all', 'alchemists' ) => 'all',
						esc_html__( 'Pagination', 'alchemists' ) => 'pagination',
					),
					'edit_field_class' => 'vc_col-sm-6',
					'description' => esc_html__( 'Select display style for albums.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Items per page', 'alchemists' ),
					'param_name' => 'items_per_page',
					'value' => '10',
					'edit_field_class' => 'vc_col-sm-6',
					'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Offset', 'alchemists' ),
					'param_name' => 'offset',
					'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order', 'alchemists'),
					'param_name'  => 'order',
					'value'       => $order_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__( 'Order By', 'alchemists'),
					'param_name'  => 'order_by',
					'value'       => $order_by_array,
					'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Content Filter
		vc_map( array(
			'name'        => esc_html__( 'ALC: Content Nav', 'alchemists' ),
			'base'        => 'alc_content_filter',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_content_filter.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A custom content filter.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'Items', 'alchemists' ),
					'param_name'  => 'values',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Select Item', 'alchemists' ),
							'value' => '',
						),
						array(
							'label' => esc_html__( 'Select Item', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'param_name'  => 'item_label',
							'holder'      => 'div',
							'value'       => esc_html__( 'Title goes here', 'alchemists' ),
							'description' => esc_html__( 'Enter short title.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Subtitle', 'alchemists' ),
							'param_name'  => 'item_label_2',
							'holder'      => 'div',
							'value'       => esc_html__( 'Subtitle', 'alchemists' ),
							'description' => esc_html__( 'Enter short subtitle.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'URL', 'alchemists'),
							'param_name'  => 'item_link',
							'holder'      => 'div',
							'value'       => '#',
							'description' => esc_html__( 'Enter URL to a page.', 'alchemists' ),
						),
						array(
							'heading'       => esc_html__( 'Active?', 'alchemists' ),
							'type'          => 'checkbox',
							'param_name'    => 'item_is_active',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'Select currently active item.', 'alchemists' ),
						),
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );


		// ALC: Sponsor
		vc_map( array(
			'name'        => esc_html__( 'ALC: Sponsor Card', 'alchemists' ),
			'base'        => 'alc_sponsor',
			'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_images_carousel.png',
			'category'    => esc_html__( 'ALC', 'alchemists' ),
			'description' => esc_html__( 'A block with image and details.', 'alchemists' ),
			'params'      => array(
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__( 'Image', 'alchemists' ),
					'param_name'  => 'image',
					'value'       => '',
					'description' => esc_html__( 'Select image from media library.', 'alchemists' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'param_group',
					'heading'     => esc_html__( 'Social Links', 'alchemists' ),
					'param_name'  => 'social_links',
					'value' => urlencode( json_encode( array(
						array(
							'label' => esc_html__( 'Add link', 'alchemists' ),
							'value' => '',
						),
					) ) ),
					'params' => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'alchemists' ),
							'param_name'  => 'item_social_title',
							'holder'      => 'div',
							'value'       => esc_html__( 'Facebook', 'alchemists'),
							'description' => esc_html__( 'Enter label for item.', 'alchemists'),
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Social Link URL (Link)', 'alchemists' ),
							'param_name'  => 'item_social_link',
							'description' => esc_html__( 'Enter URL to a social network.', 'alchemists' ),
						),
					),
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__( 'Description', 'alchemists' ),
					'param_name'  => 'content',
					'value'       => esc_html__( 'Your description goes here', 'alchemists' ),
					'description' => esc_html__( 'Enter a short text block here.', 'alchemists' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Link URL (Link)', 'alchemists' ),
					'param_name'  => 'link',
					'description' => esc_html__( 'Add link.', 'alchemists' ),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'el_id',
					'heading'     => esc_html__( 'Element ID', 'alchemists' ),
					'param_name'  => 'el_id',
					'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
				),
				array(
					'type'        => 'css_editor',
					'heading'     => esc_html__( 'CSS box', 'alchemists' ),
					'param_name'  => 'css',
					'group'       => esc_html__( 'Design Options', 'alchemists' ),
				),
			)
		) );

	}
}

if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	class WPBakeryShortCode_Alc_Newslog extends WPBakeryShortCodesContainer {
	}

	class WPBakeryShortCode_Alc_Box extends WPBakeryShortCodesContainer {
	}

	class WPBakeryShortCode_Alc_Accordion extends WPBakeryShortCodesContainer {
	}
}

if ( class_exists( 'WPBakeryShortCode' ) ) {
	class WPBakeryShortCode_Alc_Alert extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Images_Carousel extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Achievements extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Newslog_Item extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Social_Buttons extends WPBakeryShortCode {
	}

	if ( alchemists_sp_preset( 'esports' ) ) {
		class WPBakeryShortCode_Alc_Twitch extends WPBakeryShortCode {
		}
	}

	class WPBakeryShortCode_Alc_Post_Loop extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Post_Grid extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Text_Ticker extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Post_Slider extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Post_Grid_Slider extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Post_Carousel extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Featured_Post extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Btn extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Icobox extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Woo_Banner extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Albums extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Content_Filter extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Box_Info extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Dl extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Jumbotron extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Accordion_Item extends WPBakeryShortCode {
	}

	class WPBakeryShortCode_Alc_Sponsor extends WPBakeryShortCode {
	}

}



/**
 * SportsPress Elements
 */

if ( class_exists( 'SportsPress' ) ) {

	if ( function_exists( 'vc_map' ) ) {
		add_action( 'init', 'alchemists_vc_elements_sp' );
	}

	if ( ! function_exists( 'alchemists_vc_elements_sp' ) ) {
		function alchemists_vc_elements_sp() {

			// Events All
			$events = get_posts(array(
				'post_type'      => 'sp_event',
				'posts_per_page' => apply_filters('alc_vc-events-number', 10),
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			));
			$events_publish_array = array();

			if ( $events ) {
				foreach ( $events as $event ){
					$events_publish_array[$event->post_title] = $event->ID;
				}
			}

			// Players
			$players = get_posts(array(
				'post_type'      => 'sp_player',
				'posts_per_page' => apply_filters('alc_vc-players-number', 10),
				'post_status'    => 'publish',
			));

			$players_array = array();
			if ( $players ) {
				foreach ( $players as $player ) {
					$players_array[] = array(
						'label' => $player->post_title,
						'value' => $player->ID
					);
				}
			}

			// Players List
			$player_lists = get_posts( array(
				'post_type'      => 'sp_list',
				'posts_per_page' => apply_filters('alc_vc-sp_list-number', 10),
				'post_status'    => 'publish',
			));

			$player_lists_array = array();
			if ( $player_lists ) {
				foreach ( $player_lists as $list ) {
					$player_lists_array[$list->post_title] = $list->ID;
				}
			}


			// Player Stats
			$statistics = get_posts(array(
				'post_type'      => 'sp_statistic',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$statistics_array = array();
			if($statistics){
				foreach($statistics as $statistic){
					$statistics_array[] = array(
						'label' => $statistic->post_title . ' (' . $statistic->post_excerpt . ')',
						'value' => $statistic->ID
					);
				}
			}


			// Player Performance
			$performances = get_posts(array(
				'post_type'      => 'sp_performance',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$performances_array = array();
			if($performances){
				foreach($performances as $performance){
					$performances_array[] = array(
						'label' => $performance->post_title . ' (' . $performance->post_excerpt . ')',
						'value' => $performance->ID
					);
				}
			}


			// Player Performance Numbers (not equation)
			$performances_numbers = get_posts(array(
				'post_type'      => 'sp_performance',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key' => 'sp_format',
						'value' => 'number',
						'compare' => '=',
					)
				)
			));

			$performances_numbers_array = array();
			if($performances_numbers){
				foreach($performances_numbers as $performance){
					$performances_numbers_array[] = array(
						'label' => $performance->post_title . ' (' . $performance->post_excerpt . ')',
						'value' => $performance->ID
					);
				}
			}


			// Player Performance Equation (not numbers)
			$performances_equation = get_posts(array(
				'post_type'      => 'sp_performance',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key' => 'sp_format',
						'value' => 'equation',
						'compare' => '=',
					)
				)
			));

			$performances_equation_array = array();
			if($performances_equation){
				foreach($performances_equation as $performance){
					$performances_equation_array[] = array(
						'label' => $performance->post_title . ' (' . $performance->post_excerpt . ')',
						'value' => $performance->ID
					);
				}
			}


			// Metrics
			$metrics = get_posts(array(
				'post_type'      => 'sp_metric',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$metrics_array = array();
			if( $metrics ) {
				foreach( $metrics as $metric ) {
					$metrics_array[] = array(
						'label' => $metric->post_title,
						'value' => $metric->ID
					);
				}
			}


			// Teams List
			$teams = get_posts( array(
				'post_type'      => 'sp_team',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'posts_per_page' => apply_filters('alc_vc-teams-number', 30),
				'post_status'    => 'publish',
			));

			$teams_array = array();
			if ( $teams ) {
				foreach ( $teams as $team ) {
					$teams_array[$team->post_title] = $team->ID;
				}
			}


			// Staff
			$staffs = get_posts(array(
				'post_type'      => 'sp_staff',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$staffs_array = array();
			if( $staffs ){
				foreach( $staffs as $staff ){
					$staffs_array[] = array(
						'label' => $staff->post_title,
						'value' => $staff->ID
					);
				}
			}


			// Calendar
			$calendars = get_posts(array(
				'post_type'      => 'sp_calendar',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$calendars_array = array();
			if( $calendars ){
				foreach($calendars as $calendar){
					$calendars_array[$calendar->post_title] = $calendar->ID;
				}
			}


			// League Tables
			$tables = get_posts(array(
				'post_type'      => 'sp_table',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));
			$tables_array = array(
				'0' => esc_html__('Empty', 'alchemists')
			);
			if( $tables ){
				$tables_array = array();
				foreach($tables as $table){
					$tables_array[$table->post_title] = $table->ID;
				}
			}


			// League Tables Stats
			$league_tables = get_posts(array(
				'post_type'      => 'sp_column',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			));

			$league_tables_array = array();
			if($league_tables){
				foreach($league_tables as $league_table){
					$league_tables_array[] = array(
						'label' => $league_table->post_title . ' (' . $league_table->post_excerpt . ')',
						'value' => $league_table->ID
					);
				}
			}

			// Seasons
			$seasons = get_terms( 'sp_season' );
			$seasons_array = array();

			if(!empty($seasons) and !is_wp_error($seasons)) {
				foreach ( $seasons as $season ) {
					$seasons_array[] = array(
						'label' => $season->name,
						'value' => $season->term_id
					);
				}
			}


			// ALC: Event Block - Small
			vc_map( array(
				'name'        => esc_html__( 'ALC: Event Blocks - Small', 'alchemists' ),
				'base'        => 'alc_event_blocks_sm',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_event_block_sm.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A list of events.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Heading', 'alchemists' ),
						'param_name'  => 'caption',
						'value'       => esc_html__( 'Events', 'alchemists' ),
						'description' => esc_html__( 'Add heading text here.', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number of events to show', 'alchemists' ),
						'param_name'  => 'number',
						'value'       => '5',
						'description' => esc_html__( 'Enter a number of events to show.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Status', 'alchemists' ),
						'param_name'  => 'status',
						'value'       => array(
							esc_html__( 'All', 'alchemists' )       => 'any',
							esc_html__( 'Published', 'alchemists' ) => 'publish',
							esc_html__( 'Scheduled', 'alchemists' ) => 'future',
						),
						'description' => esc_html__( 'Select events status to display.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Calendar:', 'alchemists' ),
						'param_name'  => 'calendar',
						'value'       => array(
							esc_html__( 'All', 'alchemists' ) => 'all',
						) + $calendars_array,
						'description' => esc_html__( 'Pick a calendar to display.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Sort Order:', 'alchemists' ),
						'param_name'  => 'order',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'ASC', 'alchemists' )     => 'asc',
							esc_html__( 'DESC', 'alchemists' )    => 'desc',
						),
						'description' => esc_html__( 'Select events sorting order.', 'alchemists' ),
					),
					array(
						'heading'       => esc_html__( 'Display link to view all events', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'show_all_events_link',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Adds a button to the header (Note: make sure you selected a calendar to get it working).', 'alchemists' ),
					),
				)
			) );



			// ALC: Event Scoreboard
			vc_map( array(
				'name'        => esc_html__( 'ALC: Event Scoreboard', 'alchemists' ),
				'base'        => 'alc_event_scoreboard',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_event_scoreboard.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A list of events.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Display last event?', 'alchemists' ),
						'param_name'    => 'event_last',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display last event. Note: use it on a Team page.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Team', 'alchemists' ),
						'param_name'  => 'team_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $teams_array,
						'description' => sprintf(
							esc_html__( 'Select a team to display event. Leave it "Default" if you place this element on a Team page. Note: only %s teams displayed by default. Use Team ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-teams-number', 30)
						),
						'dependency' => array(
							'element' => 'team_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Team by ID?', 'alchemists' ),
						'param_name'    => 'team_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Team by ID.', 'alchemists' ),
						'dependency' => array(
							'element' => 'event_last',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Team ID', 'alchemists' ),
						'param_name'  => 'team_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Team ID. Leave it empty if you place this element on Team page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'team_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Event', 'alchemists' ),
						'param_name'  => 'event',
						'value'       => $events_publish_array,
						'description' => esc_html__( 'Pick event to display. Note: only 10 last events displayed by default. Use Event ID field to display older events.', 'alchemists' ),
						'description' => sprintf(
							esc_html__( 'Pick event to display. Note: only %s last events displayed by default. Use Event ID field to display older events.', 'alchemists' ),
							apply_filters('alc_vc-events-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'event_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Event by ID?', 'alchemists' ),
						'param_name'    => 'event_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'dependency' => array(
							'element' => 'event_last',
							'value_not_equal_to' => array( '1' ),
						),
						'description' => esc_html__( 'Enable this option to display Event by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Event ID', 'alchemists' ),
						'param_name'  => 'event_id',
						'value'       => '',
						'description' => esc_html__( 'Enter Event ID to display.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'event_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'group'         => esc_html__( 'Additional', 'alchemists'),
						'heading'       => esc_html__( 'Event Details', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'display_details',
						'std'           => '1',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Detailed statistics will be shown if enabled.', 'alchemists' ),
					),
					array(
						'group'         => esc_html__( 'Additional', 'alchemists'),
						'heading'       => esc_html__( 'Event Advanced Details', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'display_percentage',
						'std'           => '1',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Additional statistics will be shown if enabled.', 'alchemists' ),
					),
					array(
						'group'         => esc_html__( 'Additional', 'alchemists'),
						'heading'       => esc_html__( 'Link Button to Event', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'event_link',
						'std'           => '0',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Event button link will be added to the header.', 'alchemists' ),
					),
					array(
						'group'       => esc_html__( 'Additional', 'alchemists'),
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Button Label', 'alchemists' ),
						'param_name'  => 'event_link_txt',
						'description' => esc_html__( 'Enter button Label', 'alchemists' ),
						'value'       => esc_html__( 'View Event', 'alchemists' ),
						'dependency' => array(
							'element'  => 'event_link',
							'value' => '1',
						),
					),
					array(
						'group'       => esc_html__( 'Additional', 'alchemists'),
						'type'        => 'vc_link',
						'heading'     => esc_html__( 'Custom Button URL (Link)', 'alchemists' ),
						'param_name'  => 'link',
						'description' => esc_html__( 'Add custom button to the header element.', 'alchemists' ),
						'dependency' => array(
							'element'            => 'event_link',
							'value_not_equal_to' => '1',
						),
					),
					array(
						'group'       => esc_html__( 'Styling', 'alchemists'),
						'type'        => 'colorpicker',
						'heading'     => esc_html__( '1st Team Color', 'alchemists' ),
						'param_name'  => 'color_team_1',
						'description' => esc_html__( 'Color for progress and circular bars of the 1st Team.', 'alchemists' ),
					),
					array(
						'group'       => esc_html__( 'Styling', 'alchemists'),
						'type'        => 'colorpicker',
						'heading'     => esc_html__( '2nd Team Color', 'alchemists' ),
						'param_name'  => 'color_team_2',
						'description' => esc_html__( 'Color for progress and circular bars of the 2nd Team.', 'alchemists' ),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );




			if ( alchemists_sp_preset( 'soccer' ) ) {
				// ALC: Games History (Soccer)
				vc_map( array(
					'name'        => esc_html__( 'ALC: Games History', 'alchemists' ),
					'base'        => 'alc_games_history',
					'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_games_history.png',
					'category'    => esc_html__( 'ALC', 'alchemists' ),
					'description' => esc_html__( 'A chart with games history.', 'alchemists' ),
					'params'      => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Widget title', 'alchemists' ),
							'param_name'  => 'title',
							'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
							'admin_label' => true,
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Select Team', 'alchemists' ),
							'param_name'  => 'team_id',
							'value'       => array(
								esc_html__( 'Default', 'alchemists' ) => 'default',
							) + $teams_array,
							'description' => sprintf(
								esc_html__( 'Select team to display games history. Leave it "Default" if you place this element on Team page. Note: only %s teams displayed by default. Use Team ID field to display the rest.', 'alchemists' ),
								apply_filters('alc_vc-teams-number', 30)
							),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'team_id_on',
								'value_not_equal_to' => array( '1' ),
							),
						),
						array(
							'type'          => 'checkbox',
							'heading'       => esc_html__( 'Specify Team by ID?', 'alchemists' ),
							'param_name'    => 'team_id_on',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'Enable this option to display Team by ID.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Team ID', 'alchemists' ),
							'param_name'  => 'team_id_num',
							'value'       => '',
							'description' => esc_html__( 'Enter Team ID. Leave it empty if you place this element on Team page.', 'alchemists' ),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'team_id_on',
								'value'   => array( '1' ),
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'WON Label', 'alchemists' ),
							'param_name'  => 'label_won',
							'value'       => esc_html__( 'Won', 'alchemists' ),
							'description' => esc_html__( 'Enter text for WON label.', 'alchemists' ),
							'param_holder_class' => 'vc_col-xs-4',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'LOST Label', 'alchemists' ),
							'param_name'  => 'label_lost',
							'value'       => esc_html__( 'Lost', 'alchemists' ),
							'description' => esc_html__( 'Enter text for LOST label.', 'alchemists' ),
							'param_holder_class' => 'vc_col-xs-4',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'DRAW Label', 'alchemists' ),
							'param_name'  => 'label_draw',
							'value'       => esc_html__( 'Draw', 'alchemists' ),
							'description' => esc_html__( 'Enter text for DRAW label.', 'alchemists' ),
							'param_holder_class' => 'vc_col-xs-4',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Height', 'alchemists' ),
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'param_name'  => 'chart_height',
							'value'       => '230',
							'description' => esc_html__( 'Set height for the chart.', 'alchemists' ),
						),
						array(
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'type'        => 'colorpicker',
							'heading'     => esc_html__( 'WON Bars Color', 'alchemists' ),
							'param_name'  => 'color_won',
							'description' => esc_html__( 'Select a custom color for WON bars.', 'alchemists' ),
						),
						array(
							'type'        => 'colorpicker',
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'heading'     => esc_html__( 'LOST Bars Color', 'alchemists' ),
							'param_name'  => 'color_lost',
							'description' => esc_html__( 'Select a custom color for LOST bars.', 'alchemists' ),
						),
						array(
							'type'        => 'colorpicker',
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'heading'     => esc_html__( 'DRAW Bars Color', 'alchemists' ),
							'param_name'  => 'color_draw',
							'description' => esc_html__( 'Select a custom color for DRAW bars.', 'alchemists' ),
						),
						vc_map_add_css_animation(),
						array(
							'type'        => 'el_id',
							'heading'     => esc_html__( 'Element ID', 'alchemists' ),
							'param_name'  => 'el_id',
							'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
						),
						array(
							'type'        => 'css_editor',
							'heading'     => esc_html__( 'CSS box', 'alchemists' ),
							'param_name'  => 'css',
							'group'       => esc_html__( 'Design Options', 'alchemists' ),
						),
					)
				) );
			} else {
				// ALC: Games History (Basketball, American Football, eSports)
				vc_map( array(
					'name'        => esc_html__( 'ALC: Games History', 'alchemists' ),
					'base'        => 'alc_games_history',
					'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_games_history.png',
					'category'    => esc_html__( 'ALC', 'alchemists' ),
					'description' => esc_html__( 'A chart with games history.', 'alchemists' ),
					'params'      => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Widget title', 'alchemists' ),
							'param_name'  => 'title',
							'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
							'admin_label' => true,
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Select Team', 'alchemists' ),
							'param_name'  => 'team_id',
							'value'       => array(
								esc_html__( 'Default', 'alchemists' ) => 'default',
							) + $teams_array,
							'description' => sprintf(
								esc_html__( 'Select team to display games history. Leave it "Default" if you place this element on Team page. Note: only %s teams displayed by default. Use Team ID field to display the rest.', 'alchemists' ),
								apply_filters('alc_vc-teams-number', 30)
							),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'team_id_on',
								'value_not_equal_to' => array( '1' ),
							),
						),
						array(
							'type'          => 'checkbox',
							'heading'       => esc_html__( 'Specify Team by ID?', 'alchemists' ),
							'param_name'    => 'team_id_on',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'Enable this option to display Team by ID.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Team ID', 'alchemists' ),
							'param_name'  => 'team_id_num',
							'value'       => '',
							'description' => esc_html__( 'Enter Team ID. Leave it empty if you place this element on Team page.', 'alchemists' ),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'team_id_on',
								'value'   => array( '1' ),
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'WON Label', 'alchemists' ),
							'param_name'  => 'label_won',
							'value'       => esc_html__( 'Won', 'alchemists' ),
							'description' => esc_html__( 'Enter text for WON label.', 'alchemists' ),
							'param_holder_class' => 'vc_col-xs-6',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'LOST Label', 'alchemists' ),
							'param_name'  => 'label_lost',
							'value'       => esc_html__( 'Lost', 'alchemists' ),
							'description' => esc_html__( 'Enter text for LOST label.', 'alchemists' ),
							'param_holder_class' => 'vc_col-xs-6',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Height', 'alchemists' ),
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'param_name'  => 'chart_height',
							'value'       => '230',
							'description' => esc_html__( 'Set height for the chart.', 'alchemists' ),
						),
						array(
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'type'        => 'colorpicker',
							'heading'     => esc_html__( 'WON Bars Color', 'alchemists' ),
							'param_name'  => 'color_won',
							'description' => esc_html__( 'Select a custom color for WON bars.', 'alchemists' ),
						),
						array(
							'type'        => 'colorpicker',
							'group'       => esc_html__( 'Styling', 'alchemists'),
							'heading'     => esc_html__( 'LOST Bars Color', 'alchemists' ),
							'param_name'  => 'color_lost',
							'description' => esc_html__( 'Select a custom color for LOST bars.', 'alchemists' ),
						),
						vc_map_add_css_animation(),
						array(
							'type'        => 'el_id',
							'heading'     => esc_html__( 'Element ID', 'alchemists' ),
							'param_name'  => 'el_id',
							'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
						),
						array(
							'type'        => 'css_editor',
							'heading'     => esc_html__( 'CSS box', 'alchemists' ),
							'param_name'  => 'css',
							'group'       => esc_html__( 'Design Options', 'alchemists' ),
						),
					)
				) );
			}



			// ALC: Team Stats
			vc_map( array(
				'name'        => esc_html__( 'ALC: Team Stats', 'alchemists' ),
				'base'        => 'alc_team_stats',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_team_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A block with team stats.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Team', 'alchemists' ),
						'param_name'  => 'team_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $teams_array,
						'description' => sprintf(
							esc_html__( 'Select team to display games history. Leave it "Default" if you place this element on Team page. Note: only %s teams displayed by default. Use Team ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-teams-number', 30)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'team_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Team by ID?', 'alchemists' ),
						'param_name'    => 'team_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Team by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Team ID', 'alchemists' ),
						'param_name'  => 'team_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Team ID. Leave it empty if you place this element on Team page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'team_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select League Table:', 'alchemists' ),
						'param_name'  => 'league_table_id',
						'value'       => $tables_array ,
						'description' => esc_html__( 'Select league table.', 'alchemists' ),
					),

					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Team Stats', 'alchemists' ),
						'param_name'  => 'values',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter label for statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => $league_tables_array,
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Icon Type', 'alchemists'),
								'param_name'  => 'stat_icon',
								'value'       => array(
									esc_html__( 'Icon 1', 'alchemists' ) => 'icon_1',
									esc_html__( 'Icon 2', 'alchemists' ) => 'icon_2',
									esc_html__( 'Icon 3', 'alchemists' ) => 'icon_3',
									esc_html__( 'Icon with Custom Label', 'alchemists' ) => 'icon_custom',
									esc_html__( 'Custom Icon Font', 'alchemists' ) => 'icon_custom_font',
									esc_html__( 'Custom Icon Image', 'alchemists' ) => 'icon_custom_img',
								),
								'holder'      => 'div',
								'description' => esc_html__( 'Select an icon', 'alchemists' ),
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Icon Symbol', 'alchemists' ),
								'param_name'  => 'stat_icon_symbol',
								'holder'      => 'div',
								'value'       => '3',
								'description' => esc_html__( 'Enter icon symbol, e.g. W, L, % etc.', 'alchemists'),
								'dependency'  => array(
									'element' => 'stat_icon',
									'value'   => array( 'icon_custom' )
								)
							),
							array(
								'type' => 'dropdown',
								'heading' => esc_html__( 'Icon library', 'alchemists' ),
								'value' => array(
									esc_html__( 'Font Awesome', 'alchemists' ) => 'fontawesome',
									esc_html__( 'Open Iconic', 'alchemists' ) => 'openiconic',
									esc_html__( 'Typicons', 'alchemists' ) => 'typicons',
									esc_html__( 'Entypo', 'alchemists' ) => 'entypo',
									esc_html__( 'Linecons', 'alchemists' ) => 'linecons',
									esc_html__( 'Mono Social', 'alchemists' ) => 'monosocial',
									esc_html__( 'Material', 'alchemists' ) => 'material',
									esc_html__( 'SimpleLine', 'alchemists' ) => 'simpleline',
								),
								'param_name' => 'i_type',
								'description' => esc_html__( 'Select icon library.', 'alchemists' ),
								'dependency'  => array(
									'element' => 'stat_icon',
									'value'   => array( 'icon_custom_font' )
								)
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_fontawesome',
								'value' => 'fas fa-adjust',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display, we use (big number) to display all icons in single page
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'fontawesome',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_openiconic',
								'value' => 'vc-oi vc-oi-dial',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'openiconic',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'openiconic',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_typicons',
								'value' => 'typcn typcn-adjust-brightness',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'typicons',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'typicons',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_entypo',
								'value' => 'entypo-icon entypo-icon-note',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'entypo',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'entypo',
								),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_linecons',
								'value' => 'vc_li vc_li-heart',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'linecons',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'linecons',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_monosocial',
								'value' => 'vc-mono vc-mono-fivehundredpx',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'monosocial',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'monosocial',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_material',
								'value' => 'vc-material vc-material-cake',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'material',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'material',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type' => 'iconpicker',
								'heading' => esc_html__( 'Icon', 'alchemists' ),
								'param_name' => 'i_icon_simpleline',
								'value' => 'icon-like',
								// default value to backend editor admin_label
								'settings' => array(
									'emptyIcon' => false,
									// default true, display an "EMPTY" icon?
									'type' => 'simpleline',
									'iconsPerPage' => 500,
									// default 100, how many icons per/page to display
								),
								'dependency' => array(
									'element' => 'i_type',
									'value' => 'simpleline',
								),
								'description' => esc_html__( 'Select icon from library.', 'alchemists' ),
							),
							array(
								'type'        => 'colorpicker',
								'heading'     => esc_html__( 'Icon Color', 'alchemists' ),
								'param_name'  => 'icon_color',
								'description' => esc_html__( 'Select a custom color for the icon.', 'alchemists' ),
								'dependency'  => array(
									'element' => 'stat_icon',
									'value'   => array( 'icon_custom_font' )
								)
							),
							array(
								'type'        => 'attach_image',
								'heading'     => esc_html__( 'Icon Image', 'alchemists' ),
								'param_name'  => 'icon_img',
								'value'       => '',
								'description' => esc_html__( 'Select image from media library or upload your image.', 'alchemists' ),
								'dependency'  => array(
									'element' => 'stat_icon',
									'value'   => array( 'icon_custom_img' )
								)
							),
						),
					),

					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Team Points History
			vc_map( array(
				'name'        => esc_html__( 'ALC: Team Points History', 'alchemists' ),
				'base'        => 'alc_team_points_history',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_team_points_history.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A chart displayed team points.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Team', 'alchemists' ),
						'param_name'  => 'team_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $teams_array,
						'description' => sprintf(
							esc_html__( 'Select team to display team points history. Leave it "Default" if you place this element on Team page. Note: only %s teams displayed by default. Use Team ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-teams-number', 30)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'team_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Team by ID?', 'alchemists' ),
						'param_name'    => 'team_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Team by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Team ID', 'alchemists' ),
						'param_name'  => 'team_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Team ID. Leave it empty if you place this element on Team page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'team_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Calendar:', 'alchemists' ),
						'param_name'  => 'calendar_id',
						'value'       => array(
							esc_html__( 'All', 'alchemists' ) => 'all',
						) + $calendars_array,
						'description' => esc_html__( 'Pick a calendar to display.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Date:', 'alchemists' ),
						'param_name'  => 'date',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'All', 'alchemists' ) => '0',
							esc_html__( 'This Week', 'alchemists' ) => 'w',
							esc_html__( 'Date Range', 'alchemists' ) => 'range',
						),
						'description' => esc_html__( 'Select a date to display.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Date From:', 'alchemists' ),
						'param_name'  => 'date_from',
						'description' => esc_html__( 'Date Format yyyy-mm-dd, e.g. 2017-01-30', 'alchemists' ),
						'dependency' => array(
							'element' => 'date',
							'value'   => array( 'range' ),
						),
						'param_holder_class' => 'vc_col-xs-6',
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Date To:', 'alchemists' ),
						'param_name'  => 'date_to',
						'description' => esc_html__( 'Date Format yyyy-mm-dd, e.g. 2017-03-31', 'alchemists' ),
						'dependency' => array(
							'element' => 'date',
							'value'   => array( 'range' ),
						),
						'param_holder_class' => 'vc_col-xs-6',
					),
					array(
						'group'       => esc_html__( 'Styling', 'alchemists'),
						'type'        => 'colorpicker',
						'heading'     => esc_html__( 'Chart Line Color', 'alchemists' ),
						'param_name'  => 'color_line',
						'description' => esc_html__( 'Select a custom color for chart line.', 'alchemists' ),
					),
					array(
						'group'       => esc_html__( 'Styling', 'alchemists'),
						'type'        => 'colorpicker',
						'heading'     => esc_html__( 'Chart Point Color', 'alchemists' ),
						'param_name'  => 'color_point',
						'description' => esc_html__( 'Select a custom color for chart point.', 'alchemists' ),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );



			// ALC: Team Leaders
			vc_map( array(
				'name'        => esc_html__( 'ALC: Team Leaders', 'alchemists' ),
				'base'        => 'alc_team_leaders',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_team_leaders.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A block for displaying team leaders.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player List:', 'alchemists' ),
						'param_name'  => 'player_lists_id',
						'value'       => $player_lists_array,
						'description' => sprintf(
							esc_html__( 'Note: only %s player lists displayed by default. Use Player List ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-sp_list-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player List by ID?', 'alchemists' ),
						'param_name'    => 'player_lists_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player List by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player List ID', 'alchemists' ),
						'param_name'  => 'player_lists_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player List ID.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Players Stats', 'alchemists' ),
						'param_name'  => 'values',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Performance', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => $performances_array,
								'holder'      => 'div',
								'description' => esc_html__( 'Select a performance', 'alchemists' ),
								'admin_label' => true,
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'AVG Statistic', 'alchemists'),
								'param_name'  => 'stat_avg',
								'value'       => $statistics_array,
								'holder'      => 'div',
								'description' => esc_html__( 'Select an AVG statistic', 'alchemists' ),
								'admin_label' => true,
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Number of Players', 'alchemists'),
								'param_name'  => 'stat_number',
								'value'       => '1',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter number of players to display', 'alchemists' ),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Order by', 'alchemists' ),
								'param_name'  => 'stat_orderby',
								'value'       => array(
									esc_html__( 'Total', 'alchemists' ) => 'total',
									esc_html__( 'Average', 'alchemists' ) => 'average',
								),
								'description' => esc_html__( 'Sort retrieved players by parameter.', 'alchemists' ),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Order', 'alchemists' ),
								'param_name'  => 'stat_order',
								'value'       => array(
									'DESC' => 'DESC',
									'ASC' => 'ASC',
								),
								'description' => esc_html__( 'Designates the ascending or descending order.', 'alchemists' ),
							),
							array(
								'heading'       => esc_html__( 'Reverse Circular Bar', 'alchemists' ),
								'type'          => 'checkbox',
								'param_name'    => 'stat_bar_reverse',
								'value'         => array(
									esc_html__( 'Yes', 'alchemists' ) => '1'
								),
								'description'   => esc_html__( 'Reverses the circular bar percentage.', 'alchemists' ),
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Label - Total column', 'alchemists' ),
								'param_name'  => 'stat_heading_total',
								'holder'      => 'div',
								'value'       => esc_html__( 'T', 'alchemists'),
								'description' => esc_html__( 'Enter Heading for Total column', 'alchemists'),
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Label - Games Played column', 'alchemists' ),
								'param_name'  => 'stat_heading_games',
								'holder'      => 'div',
								'value'       => esc_html__( 'GP', 'alchemists'),
								'description' => esc_html__( 'Enter Heading for Games Played column', 'alchemists'),
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Label - Average column', 'alchemists' ),
								'param_name'  => 'stat_heading_avg',
								'holder'      => 'div',
								'value'       => esc_html__( 'AVG', 'alchemists'),
								'description' => esc_html__( 'Enter Heading for Average column', 'alchemists'),
							),
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );



			// ALC: Staff Bio Card
			vc_map( array(
				'name'        => esc_html__( 'ALC: Staff Bio Card', 'alchemists' ),
				'base'        => 'alc_staff_bio_card',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_staff_bio_card.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A staff info card.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Staff Member', 'alchemists' ),
						'param_name'  => 'staff_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $staffs_array,
						'description' => esc_html__( 'Select staff member you want to display.', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Image Size', 'alchemists' ),
						'param_name'  => 'img_size',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + alchemists_get_image_sizes() + array(
							esc_html__( 'Full Image', 'alchemists' ) => 'full',
						),
						'description' => esc_html__( 'Select image size. Note: there is no post image for some layouts.', 'alchemists' ),
					),
					array(
						'type'        => 'vc_link',
						'heading'     => esc_html__( 'Button URL (Link)', 'alchemists' ),
						'param_name'  => 'link',
						'description' => esc_html__( 'Add link to header.', 'alchemists' ),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );





			// ALC: Player Stats
			vc_map( array(
				'name'        => esc_html__( 'ALC: Player Stats', 'alchemists' ),
				'base'        => 'alc_player_stats',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_player_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A block shows player stats.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player', 'alchemists' ),
						'param_name'  => 'player_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $players_array,
						'description' => sprintf(
							esc_html__( 'Leave it "Default" if you place this element on Single Player page. Note: only %s players displayed by default. Use Player ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-players-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player by ID?', 'alchemists' ),
						'param_name'    => 'player_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player ID', 'alchemists' ),
						'param_name'  => 'player_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player ID. Leave it empty if you place this element on Single Player page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'heading'       => esc_html__( 'Add link to Player Page?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'add_link',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Adds link to a single player page.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Style', 'alchemists'),
						'param_name'  => 'style_type',
						'value'       => array(
							esc_html__( 'Banner - Style 1', 'alchemists' ) => 'style_1',
							esc_html__( 'Banner - Style 2', 'alchemists' ) => 'style_2',
							esc_html__( 'Hide Banner', 'alchemists' ) => 'style_hide_banner',
						),
						'description' => esc_html__( 'Select style or hide player banner.', 'alchemists' ),
					),
					array(
						'type'        => 'attach_image',
						'heading'     => esc_html__( 'Background Image', 'alchemists' ),
						'param_name'  => 'background_image',
						'value'       => '',
						'description' => esc_html__( 'Select image from media library.', 'alchemists' ),
						'dependency' => array(
							'element' => 'style_type',
							'value_not_equal_to' => array( 'style_hide_banner' ),
						),
					),
					array(
						'group'         => esc_html__( 'Stats & Performances', 'alchemists' ),
						'heading'       => esc_html__( 'Customize Primary Stats?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_primary_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize primary statistics.', 'alchemists' ),
						'dependency' => array(
							'element'   => 'style_type',
							'value_not_equal_to' => array( 'style_hide_banner' ),
						),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Primary Stats', 'alchemists' ),
						'param_name'  => 'values_primary',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Subheading', 'alchemists' ),
								'value'       => esc_html__( 'avg', 'alchemists' ),
								'param_name'  => 'stat_subheading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Subheading for Statistic', 'alchemists'),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => array_merge( $statistics_array, $performances_numbers_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_primary_stats',
							'not_empty' => true,
						),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'heading'       => esc_html__( 'Display Performances?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'display_detailed_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Detailed statistics will be shown if enabled.', 'alchemists' ),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'heading'       => esc_html__( 'Customize Performances?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_detailed_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize detailed statistics.', 'alchemists' ),
						'dependency' => array(
							'element'   => 'display_detailed_stats',
							'not_empty' => true,
						),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Statistic - Number', 'alchemists' ),
						'param_name'  => 'values',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Subheading', 'alchemists' ),
								'value'       => esc_html__( 'In his career', 'alchemists' ),
								'param_name'  => 'stat_subheading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Subheading for Statistic', 'alchemists'),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => array_merge( array( esc_html__( '- Select -', 'alchemists' ) => '' ), $performances_numbers_array, $statistics_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_detailed_stats',
							'not_empty' => true,
						),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'heading'       => esc_html__( 'Display Secondary Performances?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'display_detailed_stats_secondary',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Detailed statistics will be shown if enabled.', 'alchemists' ),
					),
					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'heading'       => esc_html__( 'Customize Secondary Performances?', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_detailed_stats_secondary',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize detailed statistics.', 'alchemists' ),
						'dependency' => array(
							'element'   => 'display_detailed_stats_secondary',
							'not_empty' => true,
						),
					),

					array(
						'group'       => esc_html__( 'Stats & Performances', 'alchemists' ),
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Performance - Equation', 'alchemists' ),
						'param_name'  => 'values_equation',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => array_merge( array( esc_html__( '- Select -', 'alchemists' ) => '' ), $performances_equation_array, $statistics_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_detailed_stats_secondary',
							'not_empty' => true,
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Player Stats - Chart
			vc_map( array(
				'name'        => esc_html__( 'ALC: Player Stats - Chart', 'alchemists' ),
				'base'        => 'alc_player_stats_chart',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_player_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Player stats as charts.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player', 'alchemists' ),
						'param_name'  => 'player_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $players_array,
						'description' => sprintf(
							esc_html__( 'Leave it "Default" if you place this element on Single Player page. Note: only %s players displayed by default. Use Player ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-players-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player by ID?', 'alchemists' ),
						'param_name'    => 'player_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player ID', 'alchemists' ),
						'param_name'  => 'player_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player ID. Leave it empty if you place this element on Single Player page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Chart Type', 'alchemists' ),
						'description' => esc_html__( 'Select chart type.', 'alchemists' ),
						'param_name'  => 'chart_type',
						'value'       => array(
							esc_html__( 'Doughnut', 'alchemists' ) => 'doughnut',
							esc_html__( 'Horizontal Bars', 'alchemists' ) => 'horizontal_bars',
						),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__( 'Sum Font Color', 'alchemists' ),
						'value'       => '#fff',
						'param_name'  => 'sum_color',
						'description' => esc_html__( 'Select color for Summary element.', 'alchemists'),
						'dependency' => array(
							'element' => 'chart_type',
							'value'   => array( 'doughnut' ),
						),
					),
					array(
						'heading'       => esc_html__( 'Customize Stats?', 'alchemists' ),
						'group'         => esc_html__( 'Statistics', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_primary_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize statistics.', 'alchemists' ),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Custom Stats', 'alchemists' ),
						'group'       => esc_html__( 'Statistics', 'alchemists' ),
						'param_name'  => 'values_primary',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'colorpicker',
								'heading'     => esc_html__( 'Color', 'alchemists' ),
								'value'       => '#00ff5b',
								'param_name'  => 'stat_color',
								'holder'      => 'div',
								'description' => esc_html__( 'Selecto color for Statistic', 'alchemists'),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => array_merge( $statistics_array, $performances_numbers_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_primary_stats',
							'not_empty' => true,
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Player Stats - Grid
			vc_map( array(
				'name'        => esc_html__( 'ALC: Player Stats - Grid', 'alchemists' ),
				'base'        => 'alc_player_stats_grid',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_player_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Stats as grid.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player', 'alchemists' ),
						'param_name'  => 'player_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $players_array,
						'description' => sprintf(
							esc_html__( 'Leave it "Default" if you place this element on Single Player page. Note: only %s players displayed by default. Use Player ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-players-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player by ID?', 'alchemists' ),
						'param_name'    => 'player_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player ID', 'alchemists' ),
						'param_name'  => 'player_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player ID. Leave it empty if you place this element on Single Player page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'heading'       => esc_html__( 'Customize Stats?', 'alchemists' ),
						'group'         => esc_html__( 'Statistics', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_player_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize statistics.', 'alchemists' ),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Custom Stats', 'alchemists' ),
						'group'       => esc_html__( 'Statistics', 'alchemists' ),
						'param_name'  => 'player_stats',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Subheading', 'alchemists' ),
								'value'       => esc_html__( 'Sublabel', 'alchemists' ),
								'param_name'  => 'stat_subheading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Subheading for Statistic', 'alchemists'),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => array_merge( $statistics_array, $performances_numbers_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_player_stats',
							'not_empty' => true,
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Player Game-by-Game Stats
			vc_map( array(
				'name'        => esc_html__( 'ALC: Player Game-by-Game Stats', 'alchemists' ),
				'base'        => 'alc_player_gbg_stats',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_player_gbg_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Game-by-game stat for player.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Calendar', 'alchemists' ),
						'param_name'  => 'calendar',
						'value'       => array(
							esc_html__( 'All', 'alchemists' ) => 'all',
						) + $calendars_array,
						'description' => esc_html__( 'Pick a calendar to display.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number of events to show', 'alchemists' ),
						'param_name'  => 'number',
						'value'       => '5',
						'description' => esc_html__( 'Enter a number of events to show.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Sort Order', 'alchemists' ),
						'param_name'  => 'order',
						'value'       => array(
							esc_html__( 'DESC', 'alchemists' )    => 'desc',
							esc_html__( 'ASC', 'alchemists' )     => 'asc',
						),
						'description' => esc_html__( 'Select events sorting order.', 'alchemists' ),
					),
					array(
						'heading'       => esc_html__( 'Display only played events', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'only_played',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'std'           => '1',
						'description'   => esc_html__( 'Display only those events where the player took a part.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'group'       => esc_html__( 'Player', 'alchemists' ),
						'heading'     => esc_html__( 'Select Player', 'alchemists' ),
						'param_name'  => 'player_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $players_array,
						'description' => sprintf(
							esc_html__( 'Leave it "Default" if you place this element on Single Player page. Note: only %s players displayed by default. Use Player ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-players-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'group'         => esc_html__( 'Player', 'alchemists' ),
						'heading'       => esc_html__( 'Specify Player by ID?', 'alchemists' ),
						'param_name'    => 'player_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'group'       => esc_html__( 'Player', 'alchemists' ),
						'heading'     => esc_html__( 'Player ID', 'alchemists' ),
						'param_name'  => 'player_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player ID. Leave it empty if you place this element on Single Player page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type' => 'autocomplete',
						'heading' => esc_html__( 'Statistics', 'alchemists' ),
						'param_name' => 'player_stats',
						'settings' => array(
							'multiple' => true,
							'min_length' => 1,
							'groups' => true,
							// In UI show results grouped by groups, default false
							'unique_values' => true,
							// In UI show results except selected. NB! You should manually check values in backend, default false
							'sortable' => true,
							'no_hide' => true,
							'values' => $performances_array
						),
						'param_holder_class' => 'vc_not-for-custom',
						'description' => esc_html__( 'Type player statistic label. (Note: Order can be changed with drag & drop).', 'alchemists' ),
					),
					array(
						'heading'       => esc_html__( 'Display link to view all events', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'show_all_events_link',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Adds a button to all events the widget header.', 'alchemists' ),
						'dependency' => array(
							'element' => 'calendar',
							'value_not_equal_to' => array( 'all' ),
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Player Stats Graph Log
			vc_map( array(
				'name'        => esc_html__( 'ALC: Player Stats Graph Log', 'alchemists' ),
				'base'        => 'alc_player_stats_graph_log',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_player_gbg_stats.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Player stats as a graph.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Calendar', 'alchemists' ),
						'param_name'  => 'calendar',
						'value'       => array(
							esc_html__( 'All', 'alchemists' ) => 'all',
						) + $calendars_array,
						'description' => esc_html__( 'Pick a calendar to display.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number of events to show', 'alchemists' ),
						'param_name'  => 'number',
						'value'       => '10',
						'description' => esc_html__( 'Enter a number of events to show.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Sort Order', 'alchemists' ),
						'param_name'  => 'order',
						'value'       => array(
							esc_html__( 'DESC', 'alchemists' )    => 'desc',
							esc_html__( 'ASC', 'alchemists' )     => 'asc',
						),
						'description' => esc_html__( 'Select events sorting order.', 'alchemists' ),
					),
					array(
						'heading'       => esc_html__( 'Display only played events', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'only_played',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'std'           => '1',
						'description'   => esc_html__( 'Display only those events where the player took a part.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'group'       => esc_html__( 'Player', 'alchemists' ),
						'heading'     => esc_html__( 'Select Player', 'alchemists' ),
						'param_name'  => 'player_id',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
						) + $players_array,
						'description' => sprintf(
							esc_html__( 'Leave it "Default" if you place this element on Single Player page. Note: only %s players displayed by default. Use Player ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-players-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'group'         => esc_html__( 'Player', 'alchemists' ),
						'heading'       => esc_html__( 'Specify Player by ID?', 'alchemists' ),
						'param_name'    => 'player_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'group'       => esc_html__( 'Player', 'alchemists' ),
						'heading'     => esc_html__( 'Player ID', 'alchemists' ),
						'param_name'  => 'player_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player ID. Leave it empty if you place this element on Single Player page.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'heading'       => esc_html__( 'Customize Stats?', 'alchemists' ),
						'group'         => esc_html__( 'Statistics', 'alchemists' ),
						'type'          => 'checkbox',
						'param_name'    => 'customize_player_stats',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable to customize statistics.', 'alchemists' ),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Custom Stats', 'alchemists' ),
						'group'       => esc_html__( 'Statistics', 'alchemists' ),
						'param_name'  => 'values_primary',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
							array(
								'label' => esc_html__( 'Select Statistic', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'textfield',
								'heading'     => esc_html__( 'Heading', 'alchemists' ),
								'value'       => esc_html__( 'Label', 'alchemists' ),
								'param_name'  => 'stat_heading',
								'holder'      => 'div',
								'description' => esc_html__( 'Enter Heading for Statistic', 'alchemists'),
								'admin_label' => true,
							),
							array(
								'type'        => 'colorpicker',
								'heading'     => esc_html__( 'Color', 'alchemists' ),
								'value'       => '#00ff5b',
								'param_name'  => 'stat_color',
								'holder'      => 'div',
								'description' => esc_html__( 'Select color for Statistic', 'alchemists'),
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Statistic', 'alchemists'),
								'param_name'  => 'stat_value',
								'value'       => $performances_numbers_array,
								'holder'      => 'div',
								'description' => esc_html__( 'Select a statistic', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element'   => 'customize_player_stats',
							'not_empty' => true,
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Roster Slider
			vc_map( array(
				'name'        => esc_html__( 'ALC: Roster Slider', 'alchemists' ),
				'base'        => 'alc_roster_slider',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_roster_slider.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'A slider for team players.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player List:', 'alchemists' ),
						'param_name'  => 'player_lists_id',
						'value'       => $player_lists_array,
						'description' => sprintf(
							esc_html__( 'Note: only %s player lists displayed by default. Use Player List ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-sp_list-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player List by ID?', 'alchemists' ),
						'param_name'    => 'player_lists_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player List by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player List ID', 'alchemists' ),
						'param_name'  => 'player_lists_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player List ID.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'group'         => esc_html__( 'Options', 'alchemists' ),
						'heading'       => esc_html__( 'Autoplay', 'alchemists' ),
						'param_name'    => 'autoplay',
						'value'         => array(
							esc_html__( 'On', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Automatically rotate players.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'param_name'  => 'autoplay_speed',
						'description' => esc_html__( 'Autoplay Speed in ms (5000 = 5s).', 'alchemists' ),
						'value'       => '5000',
						'dependency' => array(
							'element' => 'autoplay',
							'value'   => array( '1' ),
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Roster Blocks
			vc_map( array(
				'name'        => esc_html__( 'ALC: Roster Blocks', 'alchemists' ),
				'base'        => 'alc_roster_blocks',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_roster_blocks.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Team players blocks.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player List:', 'alchemists' ),
						'param_name'  => 'player_lists_id',
						'value'       => $player_lists_array,
						'description' => sprintf(
							esc_html__( 'Note: only %s player lists displayed by default. Use Player List ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-sp_list-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player List by ID?', 'alchemists' ),
						'param_name'    => 'player_lists_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player List by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player List ID', 'alchemists' ),
						'param_name'  => 'player_lists_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player List ID.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Number of Columns', 'alchemists' ),
						'param_name'  => 'columns',
						'value'       => array(
							esc_html__( '3 columns', 'alchemists' ) => '3',
							esc_html__( '2 columns', 'alchemists' ) => '2',
						),
						'description' => esc_html__( 'Select a number of columns.', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Players', 'alchemists' ),
						'param_name' => 'number',
						'description' => esc_html__( 'Number of players to display. Note: if set -1 then all players from the selected Player List will be displayed.', 'alchemists' ),
						'value' => '-1',
						'admin_label' => true,
					),
					array(
						'type'        => 'checkbox',
						'heading'     => esc_html__( 'Show Player Number?', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'param_name'  => 'squad_number',
						'value'       => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'std'         => '1',
						'description' => esc_html__( 'If checked Player Number is displayed.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Age', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'description' => esc_html__( 'Age is displayed by default.', 'alchemists' ),
						'param_name'  => 'age_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Nationality', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'description' => esc_html__( 'Nationality is displayed by default.', 'alchemists' ),
						'param_name'  => 'nationality_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Nationality Flag', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'description' => esc_html__( 'Nationality Flag is displayed by default.', 'alchemists' ),
						'param_name'  => 'nationality_flags_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
						'dependency' => array(
							'element' => 'nationality_display',
							'value_not_equal_to' => array( 'hide' ),
						),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => esc_html__( 'Customize Player Metrics?', 'alchemists' ),
						'param_name'  => 'metrics_customize',
						'value'       => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to customize Player Metrics output.', 'alchemists' ),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Custom Player Metrics', 'alchemists' ),
						'description' => esc_html__( 'Select Player Metrics to display.', 'alchemists' ),
						'param_name'  => 'values',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Metric', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Player Metric', 'alchemists'),
								'param_name'  => 'metric_id',
								'value'       => array_merge( array( esc_html__( '- Select -', 'alchemists' ) => 'empty' ), $metrics_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a metric', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element' => 'metrics_customize',
							'value' => array( '1' ),
						),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Roster Carousel
			vc_map( array(
				'name'        => esc_html__( 'ALC: Roster Carousel', 'alchemists' ),
				'base'        => 'alc_roster_carousel',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_roster_cards.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Team players cards.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Widget title', 'alchemists' ),
						'param_name'  => 'title',
						'value'       => esc_html__( 'Title goes here', 'alchemists' ),
						'description' => esc_html__( 'Enter text used as widget title (Note: located above content element).', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Select Player List:', 'alchemists' ),
						'param_name'  => 'player_lists_id',
						'value'       => $player_lists_array,
						'description' => sprintf(
							esc_html__( 'Note: only %s player lists displayed by default. Use Player List ID field to display the rest.', 'alchemists' ),
							apply_filters('alc_vc-sp_list-number', 10)
						),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value_not_equal_to' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Specify Player List by ID?', 'alchemists' ),
						'param_name'    => 'player_lists_id_on',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to display Player List by ID.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Player List ID', 'alchemists' ),
						'param_name'  => 'player_lists_id_num',
						'value'       => '',
						'description' => esc_html__( 'Enter Player List ID.', 'alchemists' ),
						'admin_label' => true,
						'dependency' => array(
							'element' => 'player_lists_id_on',
							'value'   => array( '1' ),
						),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Players', 'alchemists' ),
						'param_name' => 'number',
						'description' => esc_html__( 'Number of players to display. Note: if set -1 then all players from the selected Player List will be displayed.', 'alchemists' ),
						'value' => '-1',
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'heading'     => esc_html__( 'Layout', 'alchemists' ),
						'param_name'  => 'layout',
						'value'       => array(
							esc_html__( 'Grid', 'alchemists' ) => 'gallery',
							esc_html__( 'Blocks', 'alchemists' ) => 'blocks',
						),
						'description' => esc_html__( 'Select a layout of the Carousel.', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'heading'     => esc_html__( 'Number of Columns', 'alchemists' ),
						'param_name'  => 'columns',
						'value'       => array(
							esc_html__( '3 columns', 'alchemists' ) => '3',
							esc_html__( '2 columns', 'alchemists' ) => '2',
						),
						'description' => esc_html__( 'Select a number of columns.', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type'        => 'checkbox',
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'heading'     => esc_html__( 'Show Player Number?', 'alchemists' ),
						'param_name'  => 'squad_number',
						'value'       => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'std'         => '1',
						'description' => esc_html__( 'If checked Player Number is displayed.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Age', 'alchemists' ),
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'description' => esc_html__( 'Age is displayed by default.', 'alchemists' ),
						'param_name'  => 'age_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
						'dependency' => array(
							'element' => 'layout',
							'value'   => array( 'blocks' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Nationality', 'alchemists' ),
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'description' => esc_html__( 'Nationality is displayed by default.', 'alchemists' ),
						'param_name'  => 'nationality_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
						'dependency' => array(
							'element' => 'layout',
							'value'   => array( 'blocks' ),
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Nationality Flag', 'alchemists' ),
						'group'       => esc_html__( 'Styling', 'alchemists' ),
						'description' => esc_html__( 'Nationality Flag is displayed by default.', 'alchemists' ),
						'param_name'  => 'nationality_flags_display',
						'value'       => array(
							esc_html__( 'Default', 'alchemists' ) => 'default',
							esc_html__( 'Show', 'alchemists' ) => 'show',
							esc_html__( 'Hide', 'alchemists' ) => 'hide',
						),
						'edit_field_class' => 'vc_col-sm-6',
						'dependency' => array(
							'element' => 'nationality_display',
							'value_not_equal_to' => array( 'hide' ),
						),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => esc_html__( 'Customize Player Metrics?', 'alchemists' ),
						'param_name'  => 'metrics_customize',
						'value'       => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Enable this option to customize Player Metrics output.', 'alchemists' ),
					),
					array(
						'type'        => 'param_group',
						'heading'     => esc_html__( 'Custom Player Metrics', 'alchemists' ),
						'description' => esc_html__( 'Select Player Metrics to display.', 'alchemists' ),
						'param_name'  => 'values',
						'value' => urlencode( json_encode( array(
							array(
								'label' => esc_html__( 'Select Metric', 'alchemists' ),
								'value' => '',
							),
						) ) ),
						'params' => array(
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__( 'Player Metric', 'alchemists'),
								'param_name'  => 'metric_id',
								'value'       => array_merge( array( esc_html__( '- Select -', 'alchemists' ) => 'empty' ), $metrics_array ),
								'holder'      => 'div',
								'description' => esc_html__( 'Select a metric', 'alchemists' ),
								'admin_label' => true,
							),
						),
						'dependency' => array(
							'element' => 'metrics_customize',
							'value' => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Autoplay?', 'alchemists' ),
						'group'         => esc_html__( 'Options', 'alchemists' ),
						'param_name'    => 'autoplay',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Automatically starts animating.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Autoplay Speed', 'alchemists' ),
						'group'       => esc_html__( 'Options', 'alchemists' ),
						'param_name'  => 'autoplay_speed',
						'description' => esc_html__( 'Autoplay Speed (in ms)', 'alchemists' ),
						'value'       => '5000',
						'dependency' => array(
							'element' => 'autoplay',
							'value'   => array( '1' ),
						),
					),
					array(
						'type'          => 'checkbox',
						'heading'       => esc_html__( 'Prev/Nav Arrows?', 'alchemists' ),
						'group'         => esc_html__( 'Options', 'alchemists' ),
						'param_name'    => 'arrows',
						'value'         => array(
							esc_html__( 'Yes', 'alchemists' ) => '1'
						),
						'description' => esc_html__( 'Show next/prev buttons.', 'alchemists' ),
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			// ALC: Sponsor Cards
			vc_map( array(
				'name'        => esc_html__( 'ALC: Sponsor Cards', 'alchemists' ),
				'base'        => 'alc_sponsor_cards',
				'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_images_carousel.png',
				'category'    => esc_html__( 'ALC', 'alchemists' ),
				'description' => esc_html__( 'Displays sponsor posts.', 'alchemists' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Layout', 'alchemists'),
						'param_name'  => 'layout',
						'value'       => array(
							esc_html__( 'Grid 2 cols', 'alchemists' ) => 'grid_2cols',
							esc_html__( 'Grid 3 cols', 'alchemists' ) => 'grid_3cols',
							esc_html__( 'Grid 4 cols', 'alchemists' ) => 'grid_4cols',
						),
						'description' => esc_html__( 'Select Layout', 'alchemists' ),
						'admin_label' => true,
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Items per page', 'alchemists' ),
						'param_name' => 'items_per_page',
						'value' => '10',
						'edit_field_class' => 'vc_col-sm-6',
						'description' => esc_html__( 'Number of items to show per page.', 'alchemists' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Offset', 'alchemists' ),
						'param_name' => 'offset',
						'description' => esc_html__( 'Number of post to displace or pass over.', 'alchemists' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Order', 'alchemists'),
						'param_name'  => 'order',
						'value'       => array(
							esc_html__( 'Descending', 'alchemists' ) => 'DESC',
							esc_html__( 'Ascending', 'alchemists' )  => 'ASC',
						),
						'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
						'edit_field_class' => 'vc_col-sm-6',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Order By', 'alchemists'),
						'param_name'  => 'order_by',
						'value'       => array(
							esc_html__( 'Date', 'alchemists' )          => 'date',
							esc_html__( 'ID', 'alchemists' )            => 'ID',
							esc_html__( 'Author', 'alchemists' )        => 'author',
							esc_html__( 'Title', 'alchemists' )         => 'title',
							esc_html__( 'Modified', 'alchemists' )      => 'modified',
							esc_html__( 'Comment count', 'alchemists' ) => 'comment_count',
							esc_html__( 'Menu order', 'alchemists' )    => 'menu_order',
							esc_html__( 'Random', 'alchemists' )        => 'rand',
						),
						'description' => esc_html__( 'Sort retrieved posts.', 'alchemists' ),
						'edit_field_class' => 'vc_col-sm-6',
					),
					vc_map_add_css_animation(),
					array(
						'type'        => 'el_id',
						'heading'     => esc_html__( 'Element ID', 'alchemists' ),
						'param_name'  => 'el_id',
						'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
						'param_name'  => 'el_class',
						'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
					),
					array(
						'type'        => 'css_editor',
						'heading'     => esc_html__( 'CSS box', 'alchemists' ),
						'param_name'  => 'css',
						'group'       => esc_html__( 'Design Options', 'alchemists' ),
					),
				)
			) );


			if ( alchemists_sp_preset('soccer') ) {
				// ALC: Roster Cards
				vc_map( array(
					'name'        => esc_html__( 'ALC: Roster Cards', 'alchemists' ),
					'base'        => 'alc_roster_cards',
					'icon'        => get_template_directory_uri() . '/admin/images/js_composer/alc_roster_cards.png',
					'category'    => esc_html__( 'ALC', 'alchemists' ),
					'description' => esc_html__( 'Team players cards.', 'alchemists' ),
					'params'      => array(
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Select Player List:', 'alchemists' ),
							'param_name'  => 'player_lists_id',
							'value'       => $player_lists_array,
							'description' => sprintf(
								esc_html__( 'Note: only %s player lists displayed by default. Use Player List ID field to display the rest.', 'alchemists' ),
								apply_filters('alc_vc-sp_list-number', 10)
							),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'player_lists_id_on',
								'value_not_equal_to' => array( '1' ),
							),
						),
						array(
							'type'          => 'checkbox',
							'heading'       => esc_html__( 'Specify Player List by ID?', 'alchemists' ),
							'param_name'    => 'player_lists_id_on',
							'value'         => array(
								esc_html__( 'Yes', 'alchemists' ) => '1'
							),
							'description' => esc_html__( 'Enable this option to display Player List by ID.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Player List ID', 'alchemists' ),
							'param_name'  => 'player_lists_id_num',
							'value'       => '',
							'description' => esc_html__( 'Enter Player List ID.', 'alchemists' ),
							'admin_label' => true,
							'dependency' => array(
								'element' => 'player_lists_id_on',
								'value'   => array( '1' ),
							),
						),
						vc_map_add_css_animation(),
						array(
							'type'        => 'el_id',
							'heading'     => esc_html__( 'Element ID', 'alchemists' ),
							'param_name'  => 'el_id',
							'description' => esc_html__( 'Enter element ID (Note: make sure it is unique and valid.', 'alchemists' ),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Extra class name', 'alchemists' ),
							'param_name'  => 'el_class',
							'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'alchemists' ),
						),
						array(
							'type'        => 'css_editor',
							'heading'     => esc_html__( 'CSS box', 'alchemists' ),
							'param_name'  => 'css',
							'group'       => esc_html__( 'Design Options', 'alchemists' ),
						),
					)
				) );
			}


		}
	}


	if ( class_exists( 'WPBakeryShortCode' ) ) {

		class WPBakeryShortCode_Alc_Event_Blocks_Sm extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Event_Scoreboard extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Games_History extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Team_Stats extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Team_Points_History extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Team_Leaders extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Staff_Bio_Card extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Player_Stats extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Player_Stats_Chart extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Player_Stats_Grid extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Player_Gbg_Stats extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Player_Stats_Graph_Log extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Roster_Slider extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Roster_Blocks extends WPBakeryShortCode {
		}

		class WPBakeryShortCode_Alc_Roster_Carousel extends WPBakeryShortCode {
		}

		if ( alchemists_sp_preset( 'soccer' ) ) {
			class WPBakeryShortCode_Alc_Roster_Cards extends WPBakeryShortCode {
			}
		}

		if ( class_exists( 'SportsPress_Pro' ) ) {
			class WPBakeryShortCode_Alc_Sponsor_Cards extends WPBakeryShortCode {
			}
		}

	}

}
