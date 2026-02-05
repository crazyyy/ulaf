<?php
/**
 * Blocks Initializer
 * 
 * @package WP Logo Showcase Responsive Slider
 * @since 2.5
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
function wpnw_register_guten_block() {

	$blocks = array(
						'sp-news' => array(
											'callback'		=> 'wpnw_get_news_shortcode',
											'script_handle'	=> 'wpnw-sp-news-editor-script'
										)
					);

	foreach ($blocks as $block_key => $block_data) {

		register_block_type( __DIR__ . "/build/{$block_key}", array(
																'render_callback' => $block_data['callback'],
															));

		wp_set_script_translations( $block_data['script_handle'], 'sp-news-and-widget', WPNW_DIR . '/languages' );

	}
}
add_action( 'init', 'wpnw_register_guten_block' );

/**
 * Adds a custom variable to the JS to allow a user in the block editor
 * to preview sensitive data.
 *
 * @since 1.0
 * @return void
 */
function wpnw_editor_assets() {

	wp_localize_script( 'wp-block-editor', 'Wpnwf_Block', array(
																'pro_demo_link'		=> 'https://demo.essentialplugin.com/prodemo/news-plugin-pro/',
																'free_demo_link'	=> 'https://demo.essentialplugin.com/sp-news/',
																'pro_link'			=> WPNW_PLUGIN_LINK_UNLOCK,
															));
}
add_action( 'enqueue_block_editor_assets', 'wpnw_editor_assets' );

/**
 * Adds an extra category to the block inserter
 *
 *  @since 1.0
 */
function wpnw_add_block_category( $categories ) {

	$guten_cats = wp_list_pluck( $categories, 'slug' );

	if( ! empty( $guten_cats ) && ! in_array( 'essp_guten_block', $guten_cats ) ) {

		$categories[] = array(
							'slug'	=> 'essp_guten_block',
							'title'	=> __('Essential Plugin Blocks', 'sp-news-and-widget'),
							'icon'	=> null,
						);
	}

	return $categories;
}
add_filter( 'block_categories_all', 'wpnw_add_block_category' );