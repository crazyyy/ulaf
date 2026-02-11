<?php
/**
 * Compatibility with Advanced Custom Fields.
 * 
 * @link https://wordpress.org/plugins/advanced-custom-fields/
 *
 * @since 4.2
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Exit if ACF or ACF Pro is not active.
if ( ! ( in_array( 'advanced-custom-fields/acf.php', get_option( 'active_plugins' ) ) || in_array( 'advanced-custom-fields-pro/acf.php', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// Update attributes of images in ACF's WYSIWYG Editor while updating the attributes of a post.
add_action( 'iaffpro_after_update_attributes_in_post', 'iaffpro_acf_update_attributes_in_wysiwg_editor' );

/**
 * Update attributes of images in ACF's WYSIWYG Editor
 * 
 * This function is hooked on to iaffpro_after_update_attributes_in_post action in iaffpro_update_attributes_in_post_by_post_id().
 * 
 * @since 4.2
 * 
 * @link https://www.advancedcustomfields.com/resources/#functions
 * 
 * @param $post_id (integer) ID of the post that is being updated in iaffpro_update_attributes_in_post_by_post_id()
 */
function iaffpro_acf_update_attributes_in_wysiwg_editor( $post_id ) {

    /**
	 * Store $post_id in a transient to send to iaffpro_update_attributes_in_post_helper().
	 * $post_id is required to generate image attributes using iaffpro_generate_image_attributes() in iaffpro_update_attributes_in_post_helper().
	 */
	set_transient( 'iaffpro_current_post_id', $post_id );

    // Get all ACF field objects of current post. 
    $acf_field_objects = get_field_objects( $post_id, false );

    // Return if there are no field objects for current post.
    if ( $acf_field_objects === false ) {
        return;
    }

    foreach ( $acf_field_objects as $key => $acf_field_object ) {

        if ( $acf_field_object[ 'type' ] === 'wysiwyg' ) {

            // Update the post content
	        $updated_content = preg_replace_callback( '/<img[^>]+/', 'iaffpro_update_attributes_in_post_helper', $acf_field_object[ 'value' ] );

            // Update the field.
            update_field( $key, $updated_content, $post_id );
        }
    }

    delete_transient( 'iaffpro_current_post_id' );
}