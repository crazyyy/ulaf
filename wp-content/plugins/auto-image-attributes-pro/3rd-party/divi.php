<?php
/**
 * Functions for compatibility with Divi Theme
 * 
 * @link https://www.elegantthemes.com/gallery/divi/
 *
 * @since 4.2
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Get current theme object and get the name of the parent theme if a parent theme exist.
$current_theme  = wp_get_theme();
$parent_theme   = $current_theme->parent() !== false ? $current_theme->parent()->get( 'Name' ) : '';

// Exit if Divi or a child theme of Divi is not active.
if ( ! ( $current_theme->get( 'Name' ) === 'Divi' || $parent_theme === 'Divi' ) ) {
    return;
}

// Update attributes of images in Divi's custom markup while updating the attributes of a post.
add_action( 'iaffpro_after_update_attributes_in_post', 'iaffpro_divi_update_attributes_in_post' );

/**
 * Update attributes in Divi's image markup.
 * 
 * This function is hooked on to iaffpro_after_update_attributes_in_post action in iaffpro_update_attributes_in_post_by_post_id().
 * 
 * @since 4.2
 * 
 * @param $post_id (integer) ID of the post that is being updated in iaffpro_update_attributes_in_post_by_post_id()
 */
function iaffpro_divi_update_attributes_in_post( $post_id ) {

    // Retrieve the post
	$post = get_post( $post_id );

	if ( $post === NULL ) {
		return;
	}

	/**
	 * Store $post_id in a transient to send to iaffpro_update_attributes_in_post_helper().
	 * $post_id is required to generate image attributes using iaffpro_generate_image_attributes() in iaffpro_update_attributes_in_post_helper().
	 */
	set_transient( 'iaffpro_current_post_id', $post_id );

	// Update the post content
	$updated_content = preg_replace_callback( '/\[et_pb_image[^\]]+/', 'iaffpro_divi_update_attributes_in_post_helper_pre_process' , $post->post_content );

	delete_transient( 'iaffpro_current_post_id' );

	// Update post back into the database
	$updated_post = array(
		'ID'           	=> $post->ID,
		'post_content'	=> $updated_content,
	);

	// Update the post into the database
	wp_update_post( wp_slash( $updated_post ) );
    
    delete_transient( 'iaffpro_current_post_id' );
}

/**
 * Replace Divi's proprietary image tags with standard ones before sending for processing.
 * 
 * - Divi uses [et_pb_image tag instead of <img tag
 * - And title_text instead of title for image title.
 * 
 * Example: [et_pb_image src="../example.jpeg" alt="Alt" title_text="Title" _builder_version="4.20.2" _module_preset="default" global_colors_info="{}"
 * 
 * @since 4.2
 * 
 * @param $match (array) $match passed from preg_replace_callback() function.
 * @return $updated_content (string) Image markup with image attributes updated. Markup is reverted to Divi's proprietary format.
 */
function iaffpro_divi_update_attributes_in_post_helper_pre_process( $match ) {

    $match[0] = str_replace( '[et_pb_image', '<img', $match[0] );
    $match[0] = str_replace( 'title_text=', 'title=', $match[0] );

    $updated_content = iaffpro_update_attributes_in_post_helper( $match );

    $updated_content = str_replace( '<img', '[et_pb_image', $updated_content );
    $updated_content = str_replace( 'title=', 'title_text=', $updated_content );

    return $updated_content;
}