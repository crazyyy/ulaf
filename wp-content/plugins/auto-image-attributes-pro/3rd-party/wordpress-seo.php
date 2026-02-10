<?php
/**
 * Functions related to Yoast plugin
 * 
 * @link https://wordpress.org/plugins/wordpress-seo/
 *
 * @since 2.0 
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Exit if Yoast is not active.
if ( ! in_array( 'wordpress-seo/wp-seo.php', get_option( 'active_plugins' ) ) ) {
	return;
}

/**
 * Add custom attribute tag %yoastfocuskw%
 * 
 * @since 3.0
 * 
 * @param $available_tags (array) Array containing all custom attribute tags.
 * 
 * @return $available_tags (array) Array with yoastfocuskw added to the custom attribute tags. 
 */
function iaffpro_add_custom_attribute_tag_yoastfocuskw( $available_tags ) {

	$available_tags[ 'yoastfocuskw' ] 	= __( 'Yoast Focus Keyword', 'auto-image-attributes-pro' );
	$available_tags[ 'yoastseotitle' ] 	= __( 'Yoast SEO Title', 'auto-image-attributes-pro' );

	return $available_tags;

}
add_filter( 'iaff_custom_attribute_tags', 'iaffpro_add_custom_attribute_tag_yoastfocuskw' );

/**
 * Return Yoast Focus Keyword if it's available. 
 * For %yoastfocuskw%
 * 
 * @since 2.0
 * 
 * @param $image_id (integer) The ID of the image that is being updated.
 * @param $parent_post_id (integer) Post to which the image is attached (uploaded) to. 0 if the image is not attached to any post.
 * @param $bulk (boolean) True when called from Bulk Updater. False by default.
 * 
 * @return String Focus keyword set in Yoast SEO plugin if available. Empty string otherwise. 
 */
function iaffpro_get_custom_attribute_tag_yoastfocuskw( $image_id, $parent_post_id, $bulk = false ) {
	
	/**
	 * Possible return values from get_post_meta: 
	 * - Value of the meta field.
	 * - Empty string if $key isn’t found for the given $post_id.
	 * - False for an invalid $post_id.
	 */
	$yoast_focus_keyword = get_post_meta( $parent_post_id, '_yoast_wpseo_focuskw', true );
	
	// Return empty string if get_post_meta returns false.
	if ( $yoast_focus_keyword === false ) {
		return '';
	}
	
	return $yoast_focus_keyword;
}

/**
 * Return Yoast SEO Title.
 * 
 * @since 4.2
 * 
 * @link https://yoast.com/developer-blog/yoast-seo-14-0-using-yoast-seo-surfaces/
 * 
 * @param $image_id (integer) The ID of the image that is being updated.
 * @param $parent_post_id (integer) Post to which the image is attached (uploaded) to. 0 if the image is not attached to any post.
 * @param $bulk (boolean) True when called from Bulk Updater. False by default.
 * 
 * @return (string) Yoast SEO Title if available. Empty string otherwise.
 */
function iaffpro_get_custom_attribute_tag_yoastseotitle( $image_id, $parent_post_id, $bulk = false ) {

	if ( $parent_post_id == 0 ) {
		return;
	}

	if ( ! function_exists( 'YoastSEO' ) ) {
		return '';
	}

	return YoastSEO()->meta->for_post( $parent_post_id )->title;
}