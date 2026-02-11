<?php
/**
 * Functions for compatibility with Rank Math SEO plugin
 * 
 * @link https://wordpress.org/plugins/seo-by-rank-math/
 *
 * @since 2.0 
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Exit if Rank Math is not active.
if ( ! in_array( 'seo-by-rank-math/rank-math.php', get_option( 'active_plugins' ) ) ) {
	return;
}

/**
 * Add custom attribute tag %rankmathfocuskw%
 * 
 * @since 3.0
 * 
 * @param $available_tags (array) Array containing all custom attribute tags.
 * 
 * @return $available_tags (array) Array with rankmathfocuskw added to the custom attribute tags. 
 */
function iaffpro_add_custom_attribute_tag_rankmathfocuskw( $available_tags ) {

	$available_tags[ 'rankmathfocuskw' ] = __( 'Rank Math Focus Keyword', 'auto-image-attributes-pro' );

	return $available_tags;

}
add_filter( 'iaff_custom_attribute_tags', 'iaffpro_add_custom_attribute_tag_rankmathfocuskw' );

/**
 * Return Rank Math Focus Keyword if it's available. 
 * For %rankmathfocuskw%
 * 
 * @since 2.0
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return String Focus keyword set in Rank Math SEO plugin if available. Empty string otherwise. 
 */
function iaffpro_get_custom_attribute_tag_rankmathfocuskw( $image_id, $parent_post_id, $bulk = false ) {
	
	/**
	 * Possible return values from get_post_meta: 
	 * - Value of the meta field.
	 * - Empty string if $key isn’t found for the given $post_id.
	 * - False for an invalid $post_id.
	 */
	$rank_math_focus_keyword = get_post_meta( $parent_post_id, 'rank_math_focus_keyword', true );

	// Return empty string if get_post_meta returns false.
	if ( $rank_math_focus_keyword === false ) {
		return '';
	}
	
	return str_replace( ',', ', ', $rank_math_focus_keyword );
}