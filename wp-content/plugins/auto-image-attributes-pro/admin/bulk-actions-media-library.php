<?php
/**
 * Functions to add Bulk Actions in the Media Library.
 * 
 * @since 3.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

add_filter( 'bulk_actions-upload', 'iaffpro_add_bulk_action_update_image_attributes_to_media_library' );
add_filter( 'handle_bulk_actions-upload', 'iaffpro_handle_media_library_bulk_action_update_image_attributes', 10, 3 );
add_action( 'admin_notices', 'iaffpro_media_library_bulk_action_update_image_attributes_notice' );

/**
 * Add Bulk action "Update image attributes" to WordPress Media Library. 
 * Bulk action will show up in the "Bulk action" drop down in the Media Library (List view).
 * 
 * @since 3.0
 * 
 * @link https://developer.wordpress.org/reference/hooks/bulk_actions-this-screen-id/
 * 
 * @param $actions (array) List of available Bulk actions.
 * 
 * @return $actions (array) List of Bulk actions with custom bulk action added to it. 
 */
function iaffpro_add_bulk_action_update_image_attributes_to_media_library( $actions ) {
	
	$actions[ 'iaffpro_update_image_attributes_bulk_action' ] = __( 'Update image attributes', 'auto-image-attributes-pro' );

	return $actions;
}

/**
 * Handle Bulk action for "Update image attributes" from WordPress Media Library. 
 * 
 * @since 3.0
 * 
 * @link https://developer.wordpress.org/reference/hooks/handle_bulk_actions-screen/
 * 
 * @param $sendback (string) The redirect URL. /wp-admin/upload.php by default in this case. 
 * @param $doaction (string) The action being taken. Bulk action defined as iaffpro_update_image_attributes_bulk_action in this case. 
 * @param $items (array) The items to take the action on. Image ID's in this case. 
 * 
 * @return $sendback (string) The redirect URL. 
 */
function iaffpro_handle_media_library_bulk_action_update_image_attributes( $sendback, $doaction, $items ) {

	// Proceed only for Image Attributes Pro Bulk Action.
	if ( $doaction !== 'iaffpro_update_image_attributes_bulk_action' ) {
		return $sendback;
	}

	if ( empty( $items ) ) {
		// Show admin notice to warn that no images were selected. 
		set_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_empty_items', true, 100 );

		return $sendback;
	}

	/**
	 * Action hook that is fired at the start of the Bulk Updater before updating any image.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_before_bulk_updater/
	 */
	do_action( 'iaff_before_bulk_updater' );

	// Loop through each image ID sent to the filter. 
	foreach( $items as $image_id ) {

		// Get the object of the image form image ID.
		$image_object = get_post( $image_id );

		// Run the pro module. 
		iaffpro_auto_image_attributes_pro( $image_object, true );
	}

	/**
	 * Action hook that is fired at the end of the Bulk Updater after updating all images.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_after_bulk_updater/
	 */
	do_action( 'iaff_after_bulk_updater' );

	// Show admin notice to inform that the operation was successful. 
	set_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_complete', true, 100 );

	return $sendback;
}

/**
 * Admin notices for Update image attributes Bulk action.
 * 
 * Notices:
 * - Notice to warn when no images are selected. 
 * - Notice when Bulk action is successful. 
 * 
 * @since 3.0
 */
function iaffpro_media_library_bulk_action_update_image_attributes_notice() {

	// Show admin notice to warn that no images were selected. 
	if ( get_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_empty_items' ) ) {
		
		// Display admin notice.
		echo '<div class="notice notice-warning is-dismissible"><p>' . __( '<strong>Image Attributes Pro:</strong> No images were selected. Please select one or more images and repeat the Bulk action.', 'auto-image-attributes-pro' ) . '</p></div>';

		// Delete transient.
		delete_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_empty_items' );
		
		// No more notices.
		return;
	}

	if ( get_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_complete' ) ) {

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<strong>Image Attributes Pro:</strong> Attributes of selected images were updated as per <code>Bulk Updater Settings</code> of <a href="%s" target="_blank">Image Attributes Pro</a>.', 'auto-image-attributes-pro' ), admin_url( 'options-general.php?page=image-attributes-from-filename' ) ) . '</p></div>';

		// Delete transient. 
		delete_transient( 'iaffpro_media_library_bulk_action_update_image_attributes_complete' );
	}
}