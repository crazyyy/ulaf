<?php
/**
 * Functions to add Row Actions in the Media Library.
 * 
 * @since 4.1
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Filter row actions in Media Library
add_filter( 'media_row_actions', 'iaffpro_add_row_action_media_library_update_image_attributes', 10, 3 );

/**
 * Filter Media Library row actions to add 'Update image attributes'.
 * 
 * This action borrows the 'iaffpro_meta_box_attachment_update_image_attributes' action. 
 * Refer iaffpro_add_meta_box_media_library_callback() in admin\meta-box-attachment.php.
 * 
 * @param $actions (array) An array of action links for each attachment.
 * @param $post (object) WP_Post object for the current attachment.
 * @param $detached (bool) Whether the list table contains media not attached to any posts. Default true.
 * 
 * @return (array) Action links with 'Update image attributes' action added.
 * 
 * @since 4.1
 */
function iaffpro_add_row_action_media_library_update_image_attributes( $actions, $post, $detached ) {

	$actions['iaffpro_update_image_attributes'] = '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=iaffpro_meta_box_attachment_update_image_attributes&post=' . $post->ID ), 'update_image_attributes', 'iaffpro_meta_box_attachment_nonce_name' ) . '">' . __( 'Update image attributes', 'auto-image-attributes-pro' ) . '</a>';

	return $actions;
}