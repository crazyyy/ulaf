<?php
/**
 * Functions to add metabox to the Media Library.
 * 
 * @since 2.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

add_action( 'add_meta_boxes_attachment', 'iaffpro_add_meta_box_media_library' );
add_action( 'edit_attachment', 'iaffpro_media_library_meta_box_handle_save' );
add_action( 'admin_post_iaffpro_meta_box_attachment_update_image_attributes', 'iaffpro_meta_box_attachment_update_image_attributes' );
add_action( 'admin_notices', 'iaffpro_meta_box_attachment_update_image_attributes_notice' );

/**
 * Add a metabox to Media Library > Edit Media screen.
 * 
 * @param $post Current post object.
 * 
 * @since 2.0
 */
function iaffpro_add_meta_box_media_library( $post ) {

	if ( ! wp_attachment_is_image( $post->ID ) ) {
		return false;
	}

	add_meta_box( 'iaffpro-meta-box-attachment', __( 'Image Attributes Pro', 'auto-image-attributes-pro' ), 'iaffpro_add_meta_box_media_library_callback', 'attachment', 'side','default' );
}

/**
 * Prints the HTML for the metabox. 
 * Callback function for iaffpro_add_meta_box_media_library()
 * 
 * @param $post Current post object.
 * 
 * @since 2.0
 */
function iaffpro_add_meta_box_media_library_callback( $post ) {
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'iaffpro_meta_box_attachment_nonce_action', 'iaffpro_meta_box_attachment_nonce_name' );

	// Add checkbox to "Skip this image."

	_e( '<p><strong>Exclude Image</strong></p>', 'auto-image-attributes-pro' );
	
	if ( iaffpro_is_skip_image( $post->ID ) === true ) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}
	
	echo '<label for="iaffpro_skip_image"><input type="checkbox" name="iaffpro_skip_image" id="iaffpro_skip_image" value="1"' . $checked . '><span>' . __( 'Skip this image. When checked, the bulk updater will not update the attributes of this image in the media library and in posts or products where the image is used.', 'auto-image-attributes-pro' ) . '</span></label>';

	// Prints list of posts where the image is used.

	_e( '<p><strong>Image Used In</strong></p>', 'auto-image-attributes-pro' );
	echo iaffpro_get_posts_using_attachment( $post->ID, 'details' );

	// Update image attributes button.

	_e( '<p><strong>Update Image Attributes</strong></p>', 'auto-image-attributes-pro' );
	_e( '<p>Update attributes for current image as per the <code>Bulk Updater Settings</code>.</p>', 'auto-image-attributes-pro' );
	echo '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=iaffpro_meta_box_attachment_update_image_attributes&post=' . $post->ID ), 'update_image_attributes', 'iaffpro_meta_box_attachment_nonce_name' ) . '"><button type="button" class="button">' . __( 'Update image attributes', 'auto-image-attributes-pro' ) . '</button></a>';
}

/**
 * Saves the options in the metabox into the database.
 * 
 * @since 2.0
 */
function iaffpro_media_library_meta_box_handle_save( $post_id ) {
	
	// Nonce validation, checking for autosave, check user premissions
	if (
		( ! isset( $_POST['iaffpro_meta_box_attachment_nonce_name'] ) ) ||
		( ! wp_verify_nonce( $_POST['iaffpro_meta_box_attachment_nonce_name'], 'iaffpro_meta_box_attachment_nonce_action' ) ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		( ! current_user_can( 'edit_post', $post_id ) )
	) {
		return;
	}

	// Save if 'iaffpro_skip_image' checkbox is ticked. 
	if ( isset( $_POST['iaffpro_skip_image'] ) && ( (int) $_POST['iaffpro_skip_image'] === 1 ) ) {
		update_post_meta( $post_id, 'iaffpro_skip_image', 1, 1 );
	} else {
		delete_post_meta( $post_id, 'iaffpro_skip_image' );
	}
}

/**
 * Check if an image is marked to be skipped
 * Images can be skipped using the meta box in the Media Library > Edit Media
 * 
 * @since 2.0
 * 
 * @param int $post_id The ID of the image
 * 
 * @return bool True if the image is marked to be skipped. False otherwise. 
 */
function iaffpro_is_skip_image( $post_id ) {
	
	$skip_image = get_post_meta( $post_id, 'iaffpro_skip_image', true );
	$skip_image = (int) $skip_image;
	
	if ( $skip_image === 1 ) {
		return true;
	}
	
	return false;
}

/**
 * Handle admin_post and update image attributes of current image.
 * 
 * Meta box in Media Library > Edit Media has a button that updates image attributes of the current image. 
 * Image ID is passed as query string and will be available in $_GET['post'].
 * 
 * @since 3.1
 */
function iaffpro_meta_box_attachment_update_image_attributes() {

	// Add a fallback if wp_get_referer() returns false.
	$redirect_url = wp_get_referer() === false ? admin_url( 'post.php?post=' . $_GET['post'] . '&action=edit' ) : wp_get_referer();

	// Authentication
	if ( 
		! current_user_can( 'manage_options' ) || 
		! ( isset( $_GET['iaffpro_meta_box_attachment_nonce_name'] ) && wp_verify_nonce( $_GET['iaffpro_meta_box_attachment_nonce_name'], 'update_image_attributes' ) )
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

	// Get the object of the image form image ID.
	$image_object = get_post( $_GET['post'] );

	// Run the pro module. 
	iaffpro_auto_image_attributes_pro( $image_object, true );

	/**
	 * Action hook that is fired at the end of the Bulk Updater after updating all images.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_after_bulk_updater/
	 */
	do_action( 'iaff_after_bulk_updater' );

	// Show admin notice to inform that the operation was successful. 
	set_transient( 'iaffpro_meta_box_attachment_update_image_attributes_complete', true, 100 );

	wp_redirect( $redirect_url );
	exit;
}

/**
 * Admin notice for Media Library > Meta Box > Update image attributes.
 * 
 * Notices:
 * - Notice when operation is successful. 
 * 
 * @since 3.1
 */
function iaffpro_meta_box_attachment_update_image_attributes_notice() {

	if ( get_transient( 'iaffpro_meta_box_attachment_update_image_attributes_complete' ) ) {

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<strong>Image Attributes Pro:</strong> Attributes updated as per <code>Bulk Updater Settings</code> of <a href="%s" target="_blank">Image Attributes Pro</a>.', 'auto-image-attributes-pro' ), admin_url( 'options-general.php?page=image-attributes-from-filename' ) ) . '</p></div>';

		// Delete transient. 
		delete_transient( 'iaffpro_meta_box_attachment_update_image_attributes_complete' );
	}
}