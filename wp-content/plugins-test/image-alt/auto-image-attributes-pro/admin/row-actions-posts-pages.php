<?php
/**
 * Functions to add Row Actions in the Posts and Pages.
 * 
 * @since 4.1
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Filter row actions in non-hierarchical post types. This includes Posts and WooCommerce Products.
add_filter( 'post_row_actions', 'iaffpro_add_row_action_posts_pages_update_image_attributes', 20, 2 );

// Filter row actions in hierarchical post types. By default this includes Pages.
add_filter( 'page_row_actions', 'iaffpro_add_row_action_posts_pages_update_image_attributes', 20, 2 );

/**
 * Filter row actions in Posts and Pages to add 'Update image attributes'.
 * 
 * This action borrows the 'iaffpro_meta_box_posts_pages_update_image_attributes' action. 
 * Refer iaffpro_add_meta_box_posts_pages_callback() in admin\meta-box-posts-pages.php.
 * 
 * @param $actions (array) An array of action links for each post.
 * @param $post (object) WP_Post object for the current post.
 * 
 * @return (array) Action links with 'Update image attributes' action added.
 * 
 * @since 4.1
 */
function iaffpro_add_row_action_posts_pages_update_image_attributes( $actions, $post ) {

	$actions['iaffpro_update_image_attributes'] = '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=iaffpro_meta_box_posts_pages_update_image_attributes&post=' . $post->ID ), 'update_image_attributes', 'iaffpro_meta_box_posts_pages_nonce_name' ) . '">' . __( 'Update image attributes', 'auto-image-attributes-pro' ) . '</a>';

	return $actions;
}