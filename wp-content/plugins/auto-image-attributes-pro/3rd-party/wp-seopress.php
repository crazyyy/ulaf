<?php
/**
 * Functions related to SEOPress plugin.
 * 
 * @link https://wordpress.org/plugins/wp-seopress/
 *
 * @since 3.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Exit if SEOPress is not active.
if ( ! in_array( 'wp-seopress/seopress.php', get_option( 'active_plugins' ) ) ) {
	return;
}

/**
 * Add custom attribute tag %seopresstargetkw%.
 * 
 * @since 3.0
 * 
 * @param $available_tags (array) Array containing all custom attribute tags.
 * 
 * @return $available_tags (array) Array with seopresstargetkw added to the custom attribute tags. 
 */
function iaffpro_add_custom_attribute_tag_seopresstargetkw( $available_tags ) {

	$available_tags[ 'seopresstargetkw' ] = __( 'SEOPress Target Keyword', 'auto-image-attributes-pro' );

	return $available_tags;

}
add_filter( 'iaff_custom_attribute_tags', 'iaffpro_add_custom_attribute_tag_seopresstargetkw' );

/**
 * Return SEOPress Target Keyword if it's available. 
 * For %seopresstargetkw%
 * 
 * @since 3.0
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return (string) Target keyword set in SEOPress plugin if available. Empty string otherwise. 
 */
function iaffpro_get_custom_attribute_tag_seopresstargetkw( $image_id, $parent_post_id, $bulk = false ) {
	
	/**
	 * Possible return values from get_post_meta: 
	 * - Value of the meta field.
	 * - Empty string if $key isn’t found for the given $post_id.
	 * - False for an invalid $post_id.
	 */
	$seopress_target_keyword = get_post_meta( $parent_post_id, '_seopress_analysis_target_kw', true );
	
	// Return empty string if get_post_meta returns false.
	if ( $seopress_target_keyword === false ) {
		return '';
	}
	
	return $seopress_target_keyword;
}