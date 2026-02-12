<?php
/**
 * Functions to add Bulk Actions in the Posts and Pages.
 * 
 * @since 3.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Add Bulk Actions to Posts.
add_filter( 'bulk_actions-edit-post', 'iaffpro_add_bulk_action_update_image_attributes_to_posts' );
add_filter( 'handle_bulk_actions-edit-post', 'iaffpro_handle_posts_bulk_action_update_image_attributes', 10, 3 );

// Add Bulk Actions to Pages.
add_filter( 'bulk_actions-edit-page', 'iaffpro_add_bulk_action_update_image_attributes_to_posts' );
add_filter( 'handle_bulk_actions-edit-page', 'iaffpro_handle_posts_bulk_action_update_image_attributes', 10, 3 );

// Admin Notice.
add_action( 'admin_notices', 'iaffpro_posts_bulk_action_update_image_attributes_notice' );

/**
 * Add Bulk action "Update image attributes" to Posts and Pages.
 * Bulk action will show up in the "Bulk action" drop down.
 * 
 * @since 3.0
 * 
 * @link https://developer.wordpress.org/reference/hooks/bulk_actions-this-screen-id/
 * 
 * @param $actions (array) List of available Bulk actions.
 * 
 * @return $actions (array) List of Bulk actions with custom bulk action added to it. 
 */
function iaffpro_add_bulk_action_update_image_attributes_to_posts( $actions ) {
	
	$actions[ 'iaffpro_update_image_attributes_bulk_action' ] = __( 'Update image attributes', 'auto-image-attributes-pro' );

	return $actions;
}

/**
 * Handle Bulk action for "Update image attributes" from Posts and Pages.
 * 
 * @since 3.0
 * 
 * @link https://developer.wordpress.org/reference/hooks/handle_bulk_actions-screen/
 * 
 * @param $sendback (string) The redirect URL. 
 * @param $doaction (string) The action being taken. Bulk action defined as iaffpro_update_image_attributes_bulk_action in this case. 
 * @param $items (array) The items to take the action on. Page, Posts or WooCommerce Product ID's in this case. 
 * 
 * @return $sendback (string) The redirect URL. 
 */
function iaffpro_handle_posts_bulk_action_update_image_attributes( $sendback, $doaction, $items ) {

	// Proceed only for Image Attributes Pro Bulk Action.
	if ( $doaction !== 'iaffpro_update_image_attributes_bulk_action' ) {
		return $sendback;
	}

	if ( empty( $items ) ) {

		// Show admin notice to warn that no items were selected. 
		set_transient( 'iaffpro_posts_bulk_action_update_image_attributes_empty_items', true, 100 );

		return $sendback;
	}

	/**
	 * Action hook that is fired at the start of the Bulk Updater before updating any image.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_before_bulk_updater/
	 */
	do_action('iaff_before_bulk_updater');

	/**
	 * Loop through each post ID sent to the filter.
	 * 
	 * refer iaffpro_update_attributes_in_post() in functions/do.php.
	 */
	foreach( $items as $post_id ) {

		// Update image attributes of all images in a given post.
		iaffpro_update_attributes_in_post_by_post_id( $post_id );
	}

	/**
	 * Action hook that is fired at the end of the Bulk Updater after updating all images.
	 * 
	 * @link https://imageattributespro.com/codex/iaff_after_bulk_updater/
	 */
	do_action('iaff_after_bulk_updater');

	// Show admin notice to inform that the operation was successful. 
	set_transient( 'iaffpro_posts_bulk_action_update_image_attributes_complete', true, 100 );

	return $sendback;
}

/**
 * Admin notices for Update image attributes Bulk action.
 * 
 * Notices:
 * - Notice to warn when no items are selected. 
 * - Notice when Bulk action is successful. 
 */
function iaffpro_posts_bulk_action_update_image_attributes_notice() {

	// Show admin notice to warn that no itmes were selected. 
	// Note: WordPress behaves differently on Pages, Posts and WooCommerce products as compared to Media Library. This notice is never displayed. 
	if ( get_transient( 'iaffpro_posts_bulk_action_update_image_attributes_empty_items' ) ) {
		
		// Display admin notice.
		echo '<div class="notice notice-warning is-dismissible"><p>' . __( '<strong>Image Attributes Pro:</strong> No items were selected. Please select one or more items and repeat the Bulk action.', 'auto-image-attributes-pro' ) . '</p></div>';

		// Delete transient.
		delete_transient( 'iaffpro_posts_bulk_action_update_image_attributes_empty_items' );
		
		// No more notices.
		return;
	}

	if ( get_transient( 'iaffpro_posts_bulk_action_update_image_attributes_complete' ) ) {

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<strong>Image Attributes Pro:</strong> Attributes of images in selected items were updated as per <code>Bulk Updater Settings</code> of <a href="%s" target="_blank">Image Attributes Pro</a>.', 'auto-image-attributes-pro' ), admin_url( 'options-general.php?page=image-attributes-from-filename' ) ) . '</p></div>';

		// Delete transient. 
		delete_transient( 'iaffpro_posts_bulk_action_update_image_attributes_complete' );
	}
}