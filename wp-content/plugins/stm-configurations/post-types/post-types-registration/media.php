<?php

add_action( 'init', 'stm_media_gallery_init' );
/**
 * Register a Media Gallery post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function stm_media_gallery_init() {
	$labels = array(
		'name'               => _x( 'Media Galleries', 'post type general name', 'splash' ),
		'singular_name'      => _x( 'Media Gallery', 'post type singular name', 'splash' ),
		'menu_name'          => _x( 'Media Galleries', 'admin menu', 'splash' ),
		'name_admin_bar'     => _x( 'Media Gallery', 'add new on admin bar', 'splash' ),
		'add_new'            => _x( 'Add New', 'Media Gallery', 'splash' ),
		'add_new_item'       => __( 'Add New Media Gallery', 'splash' ),
		'new_item'           => __( 'New Media Gallery', 'splash' ),
		'edit_item'          => __( 'Edit Media Gallery', 'splash' ),
		'view_item'          => __( 'View Media Gallery', 'splash' ),
		'all_items'          => __( 'All Media Galleries', 'splash' ),
		'search_items'       => __( 'Search Media Galleries', 'splash' ),
		'parent_item_colon'  => __( 'Parent Media Galleries:', 'splash' ),
		'not_found'          => __( 'No Media Galleries found.', 'splash' ),
		'not_found_in_trash' => __( 'No Media Galleries found in Trash.', 'splash' )
	);

	$args = array(
		'labels'             => $labels,
		'menu_icon'          => 'dashicons-camera',
		'description'        => __( 'Media Gallery post type.', 'splash' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => false,
		'rewrite'            => array( 'slug' => 'medias' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'thumbnail' )
	);

	register_post_type( 'media_gallery', $args );
}