<?php
/**
 * Functions to handle custom attributes.
 * 
 * All functions are named in the format iaffpro_get_custom_attribute_tag_{%tagname%}
 * 
 * Attributes related to third party are located in /3rd-party/ folder
 *
 * @since 2.0 
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Extract the custom attribute structure and decode it. 
 * 
 * @since 2.0
 * 
 * @param $attribute (String) The attribute that the bulk updater is trying to update. 
 * @param $image_id (Integer) The ID of the image that is being updated. 
 * @param $parent_post_id (Integer) The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk (Boolean) True when called from Bulk Updater. False by default.
 * @param $image_url (String) Optionally pass image url. Used when generating attributes for external images.
 * 
 * @return String The decoded custom attribute.
 */
function iaffpro_decode_custom_attribute( $attribute, $image_id, $parent_post_id = 0, $bulk = false, $image_url = '' ) {
	
	// Get Settings
	$settings = iaff_get_settings();
	
	$bu_prefix = '';
	
	if( $bulk === true ) {
		$bu_prefix = 'bu_';
	}
	
	// Read custom attribute
	$custom_attribute = $settings['custom_attribute_' . $attribute ];
	
	preg_match_all( '/%(.+?)%/', $custom_attribute, $tags );
	
	foreach( $tags[1] as $tagname ) {
		
		/**
		 * PHP supports variable functions!
		 * 
		 * Using variable functions will allow users to define their own tagnames and write custom functions.
		 * If a tag %my_custom_tag% is added, all they have to do is create a function name `iaffpro_get_custom_attribute_tag_my_custom_tag`.
		 */
		$decoder_function = 'iaffpro_get_custom_attribute_tag_' . $tagname;
		
		if ( function_exists( $decoder_function ) ) {

			if ( $decoder_function === 'iaffpro_get_custom_attribute_tag_filename' ) {

				/**
				 * Exception for %filename% tag added in version 3.2.
				 * 
				 * The $image_url is passed for external images so that filename could be generated directly from the filename
				 * instead of the image_id. 
				 */
				$decoded_tag = iaffpro_get_custom_attribute_tag_filename( $image_id, $parent_post_id, $bulk, $image_url );
			} else {
				$decoded_tag = $decoder_function( $image_id, $parent_post_id, $bulk );
			}

			$custom_attribute = str_ireplace( '%' . $tagname . '%', $decoded_tag, $custom_attribute );
		}
	}
	
	return trim( $custom_attribute );
}

/**
 * Return Image Filename.
 * For %filename%
 * 
 * This is a wrapper to iaffpro_image_name_from_filename()
 * 
 * @since 2.0
 * @since 3.2 Added $image_url as param.
 * 
 * @param $image_id (Integer) The ID of the image that is being updated. 
 * @param $parent_post_id (Integer) The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk (Boolean) True when called from Bulk Updater. False by default.
 * @param $image_url (String) Optionally pass image url. Used when generating attributes for external images.
 * 
 * @return String Name of the image extracted from filename
 */
function iaffpro_get_custom_attribute_tag_filename( $image_id, $parent_post_id, $bulk = false, $image_url = '' ) {
	return iaffpro_image_name_from_filename( $image_id, $bulk, $image_url );
}

/**
 * Return title of the post where the image is uploaded to. 
 * For %posttitle%
 * 
 * This is a wrapper to iaffpro_image_name_from_filename()
 * 
 * @since 2.0
 *
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return String Title of the post where the image is uploaded to. 
 */
function iaffpro_get_custom_attribute_tag_posttitle( $image_id, $parent_post_id, $bulk = false ) {
	
	if ( (int) $parent_post_id === 0 ) {
		return '';
	}
	
	return get_the_title( $parent_post_id );	
}

/**
 * Return Site Title defined in WordPress General Settings.
 * For %sitetitle%
 * 
 * @since 2.0
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return String Site Title from WordPress General Settings. 
 */
function iaffpro_get_custom_attribute_tag_sitetitle( $image_id, $parent_post_id, $bulk = false ) {
	return get_bloginfo( 'name' );
}

/**
 * Return Category name for Posts.
 * For %category%
 * 
 * Can be extended to other post types using 'iaffpro_custom_attribute_tag_category_taxonomy' filter. 
 * Returns first category name by default. Can be altered using 'iaffpro_custom_attribute_tag_category_names' filter. 
 * 
 * @since 3.0
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return (string) The first category of the post. Can be modified using iaffpro_custom_attribute_tag_category_names filter. Empty string otherwise.
 */
function iaffpro_get_custom_attribute_tag_category( $image_id, $parent_post_id, $bulk = false ) {

	// Check post type of parent post where the image is used. 
	$post_type = get_post_type( $parent_post_id );

	if ( $post_type === false ) {
		return '';
	}

	switch ( $post_type ) {
		
		// WordPress posts.
		case 'post':
			$category_taxonomy_name = 'category';
			break;
			
		default:
			$category_taxonomy_name = false;
			break;
	}

	/**
	 * Filter $category_taxonomy_name to extend %category% custom attribute tag to other post types. 
	 * 
	 * For example, if you have a custom post type named 'library' where the category taxnomy name is 'genre',
	 * You can return the category taxonomy name so that the name of the genre can be retrieved. 
	 * 
	 * Refer 3rd-party/woocommerce.php for example code.
	 * 
	 * @since 3.0
	 * 
	 * @param $category_taxonomy_name (string) Name of the taxonomy.
	 * @param $post_type (string) will have the post type of the parent post where the image is used.
	 */
	$category_taxonomy_name = apply_filters( 'iaffpro_custom_attribute_tag_category_taxonomy', $category_taxonomy_name, $post_type );

	if ( $category_taxonomy_name === false ) {
		return '';
	}
	
	// Extract the names of categories associated with the post or product.
	$terms = get_the_terms( $parent_post_id, $category_taxonomy_name );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	
	$categories = wp_list_pluck( $terms, 'name' );

	/**
	 * Filter the list of categories returned by %category% custom attribute tag. 
	 * Default value is first category name.
	 * 
	 * @since 3.0
	 * 
	 * @param $categories[0] (string) The first category available. This is the default value.
	 * @param $categories (array) Contains the names of all categories associated with $parent_post_id.
	 */
	return apply_filters( 'iaffpro_custom_attribute_tag_category_names', $categories[0], $categories );
}

/**
 * Return Tag name for Posts.
 * For %tag%
 * 
 * Can be extended to other post types using 'iaffpro_custom_attribute_tag_tag_taxonomy' filter. 
 * Returns first tag name by default. Can be altered using 'iaffpro_custom_attribute_tag_tag_names' filter. 
 * 
 * @since 3.0
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return (string) The first tag of the post. Can be modified using iaffpro_custom_attribute_tag_tag_names filter. Empty string otherwise.
 */
function iaffpro_get_custom_attribute_tag_tag( $image_id, $parent_post_id, $bulk = false ) {

	// Check post type of parent post where the image is used. 
	$post_type = get_post_type( $parent_post_id );

	if ( $post_type === false ) {
		return '';
	}

	switch ( $post_type ) {
		
		// WordPress posts.
		case 'post':
			$tag_taxonomy_name = 'post_tag';
			break;
			
		default:
			$tag_taxonomy_name = false;
			break;
	}

	/**
	 * Filter $tag_taxonomy_name to extend %tag% custom attribute tag to other post types. 
	 * 
	 * Refer 3rd-party/woocommerce.php for example code.
	 * 
	 * @since 3.0
	 * 
	 * @param $tag_taxonomy_name (string) Name of the taxonomy.
	 * @param $post_type (string) will have the post type of the parent post where the image is used. 
	 */
	$tag_taxonomy_name = apply_filters( 'iaffpro_custom_attribute_tag_tag_taxonomy', $tag_taxonomy_name, $post_type );

	if ( $tag_taxonomy_name === false ) {
		return '';
	}
	
	// Extract the names of categories associated with the post or product.
	$terms = get_the_terms( $parent_post_id, $tag_taxonomy_name );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	
	$tags = wp_list_pluck( $terms, 'name' );

	/**
	 * Filter the list of tags returned by %tag% custom attribute tag. 
	 * Default value is first tag name.
	 * 
	 * @since 3.0
	 * 
	 * @param $tags[0] (string) The first tag available. This is the default value.
	 * @param $tags (array) Contains the names of all tags associated with $parent_post_id.
	 */
	return apply_filters( 'iaffpro_custom_attribute_tag_tag_names', $tags[0], $tags );
}

/**
 * Return post excerpt.
 * For %excerpt%
 * 
 * WooCommerce product short description is saved as excerpt.
 * 
 * @since 3.2
 * 
 * @param $image_id The ID of the image that is being updated. 
 * @param $parent_post_id The post to which the image is attached (uploaded) to. 0 if the image is not attached to any post. 
 * @param $bulk True when called from Bulk Updater. False by default.
 * 
 * @return String Post excerpt.
 */
function iaffpro_get_custom_attribute_tag_excerpt( $image_id, $parent_post_id, $bulk = false ) {
	
	if ( $parent_post_id === 0 ) {
		return '';
	}

	/**
	 * Fetching post_excerpt directly instead of get_the_excerpt().
	 * get_the_excerpt() checks for post_password_required() and also filters the retrieved post excerpt.
	 */
	$post = get_post( $parent_post_id );

	if ( $post === NULL ) {
		return '';
	}
	
	return $post->post_excerpt;
}