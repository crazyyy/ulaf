<?php
/**
 * Functions related WooCommerce plugin.
 * 
 * @link https://wordpress.org/plugins/woocommerce/
 *
 * @since 3.0
 */

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

// Exit if WooCommerce is not active.
if ( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
	return;
}

/**
 * Add Bulk Actions to WooCommerce Products > All Products list view in WordPress admin.
 * Adds "Update image attributes" Bulk action. 
 * 
 * edit-product is the screen ID for WooCommerce product pages in WordPress admin.
 * 
 * @since 3.0
 * 
 * Refer admin/bulk-actions-posts-pages.php for function definitions.
 */ 
add_filter( 'bulk_actions-edit-product', 'iaffpro_add_bulk_action_update_image_attributes_to_posts' );
add_filter( 'handle_bulk_actions-edit-product', 'iaffpro_handle_posts_bulk_action_update_image_attributes', 10, 3 );

// Add WooCommerce Product category taxonomy to custom attribute tag %category%.
add_filter( 'iaffpro_custom_attribute_tag_category_taxonomy', 'iaffpro_add_wc_product_category_to_custom_attribute_tag_category', 10, 2 );

// Add WooCommerce Product tag taxonomy to custom attribute tag %tag%.
add_filter( 'iaffpro_custom_attribute_tag_tag_taxonomy', 'iaffpro_add_wc_product_tag_to_custom_attribute_tag_tag', 10, 2 );

// Update attributes of images in Product Gallery while updating attributes of a product.
add_action( 'iaffpro_after_update_attributes_in_post', 'iaffpro_wc_update_product_gallery_image_attributes' );

// Update image attributes after product import from CSV file.
add_action( 'woocommerce_product_import_inserted_product_object', 'iaffpro_wc_update_image_attributes_after_product_import_from_csv', 10, 2 );

// Add Meta Box to WooCommerce products.
add_action( 'add_meta_boxes_product', 'iaffpro_add_meta_box_posts_pages' );

/**
 * Add WooCommerce Product category taxonomy to custom attribute tag %category%.
 * 
 * @since 3.0
 * 
 * @param $category_taxonomy_name (string) Name of the taxonomy.
 * @param $post_type (string) will have the post type of the parent post where the image is used.
 * 
 * @return $category_taxonomy_name (string) WooCommerce product category taxonomy name ('product_cat') if $post_type is 'product'.
 */
function iaffpro_add_wc_product_category_to_custom_attribute_tag_category ( $category_taxonomy_name, $post_type ) {

	// Check if post type is WooCommerce product. 
	if ( strcmp( $post_type, 'product' ) === 0 ) {
		return 'product_cat';
	}

	return $category_taxonomy_name;
}

/**
 * Add WooCommerce Product tag taxonomy to custom attribute tag %tag%.
 * 
 * @since 3.0
 * 
 * @param $tag_taxonomy_name (string) Name of the taxonomy.
 * @param $post_type (string) will have the post type of the parent post where the image is used.
 * 
 * @return $tag_taxonomy_name (string) WooCommerce product tag taxonomy name ('product_tag') if $post_type is 'product'.
 */
function iaffpro_add_wc_product_tag_to_custom_attribute_tag_tag ( $tag_taxonomy_name, $post_type ) {

	// Check if post type is WooCommerce product. 
	if ( strcmp( $post_type, 'product' ) === 0 ) {
		return 'product_tag';
	}

	return $tag_taxonomy_name;
}

/**
 * Update attributes of images in Product Gallery while updating attributes of a product.
 * 
 * This function is hooked on to iaffpro_after_update_attributes_in_post action in iaffpro_update_attributes_in_post_by_post_id().
 * Attributes of images in Product Gallery are updated in the Media Library.
 * 
 * @since 3.1
 * 
 * @param $post_id (integer) ID of the post that is being updated in iaffpro_update_attributes_in_post_by_post_id()
 */
function iaffpro_wc_update_product_gallery_image_attributes( $post_id ) {
	
	// Retrieve post type of the post.
	$post_type = get_post_type( $post_id );

	// Check if post type is WooCommerce product.
	if ( strcmp( $post_type, 'product' ) !== 0 ) {
		return;
	}

	// Retrieve Product Gallery image ID's.
	$product = new WC_product( $post_id );
    $product_gallery_image_ids = $product->get_gallery_image_ids();

    // Update every image in Product Gallery.
	foreach( $product_gallery_image_ids as $gallery_image ) {

		$parent_post_id = iaffpro_get_parent_post_of_image( $gallery_image );

		if ( $parent_post_id === 0 ) {
			$parent_post_id = $post_id;
		}

		$attributes = iaffpro_generate_image_attributes( $gallery_image, $parent_post_id, true );
		
		iaffpro_update_image( $gallery_image, $attributes, true );
	}
}

/**
 * Update image attributes after product import from CSV file.
 * 
 * WooCommerce has an inbuilt feature to import products in bulk from a CSV file. Here we are updating the image attributes 
 * after product import using iaffpro_update_attributes_in_post_by_post_id().
 * 
 * @since 4.1
 * 
 * @param $object (object) Object containing product data of the newly created product from CSV import.
 * @param $data (array) Product data from the CSV file.
 */
function iaffpro_wc_update_image_attributes_after_product_import_from_csv( $object, $data ) {
	
	// Update image attributes of all images in a given post.
	iaffpro_update_attributes_in_post_by_post_id( $object->get_id() );
}