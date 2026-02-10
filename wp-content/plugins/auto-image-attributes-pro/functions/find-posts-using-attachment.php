<?php
/**
 * Functions to find where an image is used.
 * 
 * A good portion of the following code is from the "Find Posts Using Attachment" WordPress plugin
 * by Sergey Biryukov (http://profiles.wordpress.org/sergeybiryukov/)
 *
 * @since 3.0
 * 
 * @link https://wordpress.org/plugins/find-posts-using-attachment/
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Find posts and WooCommerce products where an image is used. 
 * 
 * @since 1.0 (revised and moved to separate file in 3.0).
 * 
 * @param $attachment_id (integer) ID of the image.
 * 
 * @return $posts (array|boolean) An array of all the post ID's that use the image. False when attachment is not an image.
 */
function iaffpro_get_posts_by_attachment_id( $attachment_id ) {

	// Determines whether an attachment is an image.
	if ( ! wp_attachment_is_image( $attachment_id ) ) {
		return false;
	}

	/**
	 * Password protected posts and private posts are only available when logged in.
	 * We set the current user to 1 (admin) and then change it back after we are done.
	 * 
	 * @link https://developer.wordpress.org/reference/classes/wp_query/#status-parameters
	 */
	$current_user_ID = get_current_user_id();
	wp_set_current_user(1);

	/**
	 * Check if the image is used as Featured image on a post or Product image on a WooCommerce product.
	 * This will also check work for any custom post type that uses the Featured Image. 
	 */

	$used_as_thumbnail = array();

	$thumbnail_query = new WP_Query( array(
		'meta_key'       => '_thumbnail_id',
		'meta_value'     => $attachment_id,
		'post_type'      => 'any',
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'posts_per_page' => -1,
	) );

	$used_as_thumbnail = $thumbnail_query->posts;
	$used_as_thumbnail = array_unique( $used_as_thumbnail );

	/**
	 * Check if the image is used in any post within the post HTML.
	 * To do so, all the image sizes are gathered and searched recursively on all post types. 
	 */

	// An array to hold all the image sizes for the given image.
	$attachment_urls = array( wp_get_attachment_url( $attachment_id ) );

	// Extracting url's of all the image sizes for the given image.
	foreach ( get_intermediate_image_sizes() as $size ) {
		$intermediate = image_get_intermediate_size( $attachment_id, $size );
		if ( $intermediate ) {
			$attachment_urls[] = $intermediate['url'];
		}
	}

	$used_in_content = array();

	// Search query to find post id's that use each image url.
	foreach ( $attachment_urls as $attachment_url ) {
		$content_query = new WP_Query( array(
			's'              => $attachment_url,
			'post_type'      => 'any',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'posts_per_page' => -1,
		) );

		$used_in_content = array_merge( $used_in_content, $content_query->posts );
	}

	$used_in_content = array_unique( $used_in_content );

	/**
	 * Check if the image is added to a WooCommerce Product Gallery.
	 */
	$used_as_product_gallery = array();

	$product_gallery_query = new WP_Query( array(
		'meta_key'       => '_product_image_gallery',
		'meta_value'     => '\b' . $attachment_id . '\b',
		'meta_compare'   => 'REGEXP',
		'post_type'      => 'product',
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'posts_per_page' => -1,
	) );

	$used_as_product_gallery = $product_gallery_query->posts;
	$used_as_product_gallery = array_unique( $used_as_product_gallery );

	$posts = array(
		'thumbnail' 			=> $used_as_thumbnail,
		'content'   			=> $used_in_content,
		'product_image_gallery' => $used_as_product_gallery,
	);

	wp_set_current_user( $current_user_ID );

	return $posts;
}

/**
 * Combines the output of iaffpro_get_posts_by_attachment_id() into one single array.
 * 
 * Image Attributes Pro only needs the combined array. 
 * Separate results are stored for display in Media library and was part of the "Find Posts Using Attachment" plugin.
 * 
 * @since 3.0
 * 
 * @param $attachment_id (integer) ID of the image.
 * 
 * @return (array|boolean) Array containing all the post ID's where the image is used. False if iaffpro_get_posts_by_attachment_id() returns false.
 */
function iaffpro_get_all_posts_by_attachment_id_combined( $attachment_id ) {
	
	$post_ids = iaffpro_get_posts_by_attachment_id( $attachment_id );

	if ( $post_ids === false ) {
		return false;
	}

	/**
	 * Image Attributes Pro uses the first post from this array to genearte image attributes when an image is not attached to a post. 
	 * For example, if Bulk Updater is configured to use Post Title as image attribute, the post title of the first post from this array
	 * will be used in the media library. 
	 * 
	 * So the order here is important. 
	 * - If the image is used as a featured image, then that has the higest preference.
	 * - Second preference goes to product where the image is part of the product gallery. 
	 * - Post ID where image is used within the content has the lowest preference of the three.
	 * 
	 * @link https://imageattributespro.com/image-attributes-pro-behavior-same-image-on-multiple-posts/
	 */
	$all_posts_with_image = array_merge( $post_ids['thumbnail'], $post_ids['product_image_gallery'], $post_ids['content'] );
	$all_posts_with_image = array_unique( $all_posts_with_image );

	return $all_posts_with_image;
}

/**
 * Returns HTML output listing posts where the image is used. 
 * 
 * Two context is available to use depending on where the HTML is used. 
 * Context: column. When output is in media library. (Not used by Image Attributes Pro as of 3.1)
 * Context: details. When output is used elsewhere. Image Attributes Pro 3.1 uses this. 
 * 
 * @since 3.0 (and used by Image Attributes Pro since 3.1)
 * 
 * @param $attachment_id (integer) ID of the image. 
 * @param $context (string) 'column' or 'details'.
 * 
 * @return (string) HTML output that lists posts where the image is used. 
 */
function iaffpro_get_posts_using_attachment( $attachment_id, $context ) {
	
	$post_ids 	= iaffpro_get_posts_by_attachment_id( $attachment_id );
	$posts 		= array();

	if ( $post_ids !== false ) {
		$posts = array_merge( $post_ids['thumbnail'], $post_ids['product_image_gallery'], $post_ids['content'] );
		$posts = array_unique( $posts );
	}

	switch ( $context ) {
		case 'column':
			$item_format   = '<strong>%1$s</strong>, %2$s %3$s<br />';
			$output_format = '%s';
			break;
		case 'details':
		default:
			$item_format   = '%1$s %3$s<br />';
			$output_format = '<div>%s</div>';
			break;
	}

	$output = '';

	foreach ( $posts as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			continue;
		}

		$post_title = _draft_or_post_title( $post );
		$post_type  = get_post_type_object( $post->post_type );

		if ( $post_type && $post_type->show_ui && current_user_can( 'edit_post', $post_id ) ) {
			$link = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $post_id ), $post_title );
		} else {
			$link = $post_title;
		}

		if ( in_array( $post_id, $post_ids['thumbnail'] ) && in_array( $post_id, $post_ids['content'] ) ) {
			$usage_context = __( '(as Featured Image and in content)', 'auto-image-attributes-pro' );
		} elseif ( in_array( $post_id, $post_ids['thumbnail'] ) ) {
			$usage_context = __( '(as Featured Image)', 'auto-image-attributes-pro' );
		} elseif ( in_array( $post_id, $post_ids['product_image_gallery'] ) ) {
			$usage_context = __( '(in WooCommerce Product Gallery)', 'auto-image-attributes-pro' );
		} else {
			$usage_context = __( '(in content)', 'auto-image-attributes-pro' );
		}

		$output .= sprintf( $item_format, $link, get_the_time( __( 'Y/m/d', 'auto-image-attributes-pro' ) ), $usage_context );
	}

	if ( ! $output ) {
		$output = __( '(Unused)', 'auto-image-attributes-pro' );
	}

	$output = sprintf( $output_format, $output );

	return $output;
}

/**
 * The following code from "Find Posts Using Attachment" WordPress plugin is left here for debug purposes only. 
 * The plugin adds a column in the WordPress Media Library titled "Used In" which lists the posts and products where the image is used. 
 */

// add_filter( 'attachment_fields_to_edit', 'iaffpro_attachment_fields_to_edit', 10, 2 );
// add_filter( 'manage_media_columns', 'iaffpro_manage_media_columns' );
// add_action( 'manage_media_custom_column', 'iaffpro_manage_media_custom_column', 10, 2 );

function iaffpro_attachment_fields_to_edit( $form_fields, $attachment ) {
	$form_fields['used_in'] = array(
		'label' => __( 'Used In', 'auto-image-attributes-pro' ),
		'input' => 'html',
		'html'  => iaffpro_get_posts_using_attachment( $attachment->ID, 'details' ),
	);

	return $form_fields;
}

function iaffpro_manage_media_columns( $columns ) {
	$filtered_columns = array();

	foreach ( $columns as $key => $column ) {
		$filtered_columns[ $key ] = $column;

		if ( 'parent' === $key ) {
			$filtered_columns['used_in'] = __( 'Used In', 'auto-image-attributes-pro' );
		}
	}

	return $filtered_columns;
}

function iaffpro_manage_media_custom_column( $column_name, $attachment_id ) {
	switch ( $column_name ) {
		case 'used_in':
			echo iaffpro_get_posts_using_attachment( $attachment_id, 'column' );
			break;
	}
}