<?php

add_action( 'init', 'stm_sidebar_init' );
/**
 * Register a Sidebar post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function stm_Sidebar_init() {
	$labels = array(
		'name'               => _x( 'Sidebars', 'post type general name', 'splash' ),
		'singular_name'      => _x( 'Sidebar', 'post type singular name', 'splash' ),
		'menu_name'          => _x( 'Sidebars', 'admin menu', 'splash' ),
		'name_admin_bar'     => _x( 'Sidebar', 'add new on admin bar', 'splash' ),
		'add_new'            => _x( 'Add New', 'Sidebar', 'splash' ),
		'add_new_item'       => __( 'Add New Sidebar', 'splash' ),
		'new_item'           => __( 'New Sidebar', 'splash' ),
		'edit_item'          => __( 'Edit Sidebar', 'splash' ),
		'view_item'          => __( 'View Sidebar', 'splash' ),
		'all_items'          => __( 'All Sidebars', 'splash' ),
		'search_items'       => __( 'Search Sidebars', 'splash' ),
		'parent_item_colon'  => __( 'Parent Sidebars:', 'splash' ),
		'not_found'          => __( 'No Sidebars found.', 'splash' ),
		'not_found_in_trash' => __( 'No Sidebars found in Trash.', 'splash' )
	);

	$args = array(
		'labels'             => $labels,
		'menu_icon'          => 'dashicons-schedule',
		'description'        => __( 'Sidebar Post type.', 'splash' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => false,
		'rewrite'            => array( 'slug' => 'sidebars' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail' )
	);

	register_post_type( 'vc_sidebar', $args );
}