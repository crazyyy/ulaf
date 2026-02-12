<?php
/**
 * Functions to add metabox to Posts and Pages.
 * 
 * @since 4.1
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

add_action( 'add_meta_boxes_post', 'iaffpro_add_meta_box_posts_pages' );
add_action( 'add_meta_boxes_page', 'iaffpro_add_meta_box_posts_pages' );
add_action( 'admin_post_iaffpro_meta_box_posts_pages_update_image_attributes', 'iaffpro_meta_box_posts_pages_update_image_attributes' );
add_action( 'admin_notices', 'iaffpro_meta_box_posts_pages_update_image_attributes_notice' );

/**
 * Add a metabox to Posts and Pages edit view.
 * 
 * @param $post Current post object.
 * 
 * @since 4.1
 */
function iaffpro_add_meta_box_posts_pages( $post ) {
	add_meta_box( 'iaffpro-meta-box-posts-pages', __( 'Image Attributes Pro', 'auto-image-attributes-pro' ), 'iaffpro_add_meta_box_posts_pages_callback', null, 'side' );
}

/**
 * Prints the HTML for the metabox. 
 * Callback function for iaffpro_add_meta_box_posts_pages()
 * 
 * @param $post Current post object.
 * 
 * @since 4.1
 */
function iaffpro_add_meta_box_posts_pages_callback( $post ) {

	// Update image attributes button.

	_e( '<p><strong>Update Image Attributes</strong></p>', 'auto-image-attributes-pro' );
	_e( '<p>Update attributes for this post as per the <code>Bulk Updater Settings</code>.</p>', 'auto-image-attributes-pro' );
	echo '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=iaffpro_meta_box_posts_pages_update_image_attributes&post=' . $post->ID ), 'update_image_attributes', 'iaffpro_meta_box_posts_pages_nonce_name' ) . '"><button type="button" class="button">' . __( 'Update image attributes', 'auto-image-attributes-pro' ) . '</button></a>';
}

/**
 * Handle admin_post and update image attributes of current post.
 * 
 * Meta box in Posts and Pages has a button that updates image attributes of the current post. 
 * Post ID is passed as query string and will be available in $_GET['post'].
 * 
 * @since 4.1
 */
function iaffpro_meta_box_posts_pages_update_image_attributes() {

	// Add a fallback if wp_get_referer() returns false.
	$redirect_url = wp_get_referer() === false ? admin_url( 'post.php?post=' . $_GET['post'] . '&action=edit' ) : wp_get_referer();

	// Authentication
	if ( 
		! current_user_can( 'manage_options' ) || 
		! ( isset( $_GET['iaffpro_meta_box_posts_pages_nonce_name'] ) && wp_verify_nonce( $_GET['iaffpro_meta_box_posts_pages_nonce_name'], 'update_image_attributes' ) )
	) {
		
		// Return to referer if authentication fails.
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Action hook that is fired at the start of the Bulk Updater before updating any image.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_before_bulk_updater/
	 */
	do_action( 'iaff_before_bulk_updater' );

	// Update image attributes of all images in a given post.
	iaffpro_update_attributes_in_post_by_post_id( $_GET['post'] );

	/**
	 * Action hook that is fired at the end of the Bulk Updater after updating all images.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_after_bulk_updater/
	 */
	do_action( 'iaff_after_bulk_updater' );

	// Show admin notice to inform that the operation was successful. 
	set_transient( 'iaffpro_meta_box_posts_pages_update_image_attributes_complete', true, 100 );

	wp_redirect( $redirect_url );
	exit;
}

/**
 * Admin notice for Media Library > Meta Box > Update image attributes.
 * 
 * Notices:
 * - Notice when operation is successful. 
 * 
 * @since 4.1
 */
function iaffpro_meta_box_posts_pages_update_image_attributes_notice() {

	if ( get_transient( 'iaffpro_meta_box_posts_pages_update_image_attributes_complete' ) ) {

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<strong>Image Attributes Pro:</strong> Attributes updated as per <code>Bulk Updater Settings</code> of <a href="%s" target="_blank">Image Attributes Pro</a>.', 'auto-image-attributes-pro' ), admin_url( 'options-general.php?page=image-attributes-from-filename' ) ) . '</p></div>';

		// Delete transient. 
		delete_transient( 'iaffpro_meta_box_posts_pages_update_image_attributes_complete' );
	}
}