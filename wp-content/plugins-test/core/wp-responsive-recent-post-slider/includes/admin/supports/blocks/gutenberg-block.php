<?php
/**
 * Blocks Initializer
 * 
 * @package WP Responsive Recent Post Slider
 * @since 2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Blocks Initializer
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * 'script_handle' will be BlockName-editor-script (/ will be replaced with dash(-) in BlockName)
 * 
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function wprps_register_guten_block() {

	$blocks = array(
						'recent-post-slider' => array(
											'callback'		=> 'wprps_recent_post_slider',
											'script_handle'	=> 'wprps-recent-post-slider-editor-script'
										),
						'recent-post-carousel' => array(
											'callback'		=> 'wprps_post_carousel',
											'script_handle'	=> 'wprps-recent-post-carousel-editor-script'
										)
					);

	foreach ($blocks as $block_key => $block_data) {

		register_block_type( __DIR__ . "/build/{$block_key}", array(
																'render_callback' => $block_data['callback'],
															));
		wp_set_script_translations( $block_data['script_handle'], 'wp-responsive-recent-post-slider', WPRPS_DIR . '/languages' );
	}
}
add_action( 'init', 'wprps_register_guten_block' );

/**
 * Adds a custom variable to the JS to allow a user in the block editor
 * to preview sensitive data.
 *
 * @since 1.0
 * @return void
 */
function wprps_editor_assets() {

	wp_localize_script( 'wp-block-editor', 'Wprpsf_Block', array(
																'pro_demo_link'		=> 'https://demo.essentialplugin.com/prodemo/post-slider-pro/',
																'free_demo_link'	=> 'https://demo.essentialplugin.com/recent-post-slider-demo/',
																'pro_link'			=> WPRPS_PLUGIN_LINK_UNLOCK,
															));
}
add_action( 'enqueue_block_editor_assets', 'wprps_editor_assets' );

/**
 * Adds an extra category to the block inserter
 *
 *  @since 1.0
 */
function wprps_add_block_category( $categories ) {

	$guten_cats = wp_list_pluck( $categories, 'slug' );

	if( ! empty( $guten_cats ) && ! in_array( 'essp_guten_block', $guten_cats ) ) {

		$categories[] = array(
							'slug'	=> 'essp_guten_block',
							'title'	=> __('Essential Plugin Blocks', 'wp-responsive-recent-post-slider'),
							'icon'	=> null,
						);
	}

	return $categories;
}
add_filter( 'block_categories_all', 'wprps_add_block_category' );