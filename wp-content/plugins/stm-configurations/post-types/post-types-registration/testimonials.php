<?php

add_action( 'init', 'stm_testimonial_init' );
/**
 * Register a Testimonial post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function stm_testimonial_init() {
	$labels = array(
		'name'               => _x( 'Testimonials', 'post type general name', 'splash' ),
		'singular_name'      => _x( 'Testimonial', 'post type singular name', 'splash' ),
		'menu_name'          => _x( 'Testimonials', 'admin menu', 'splash' ),
		'name_admin_bar'     => _x( 'Testimonial', 'add new on admin bar', 'splash' ),
		'add_new'            => _x( 'Add New', 'Testimonial', 'splash' ),
		'add_new_item'       => __( 'Add New Testimonial', 'splash' ),
		'new_item'           => __( 'New Testimonial', 'splash' ),
		'edit_item'          => __( 'Edit Testimonial', 'splash' ),
		'view_item'          => __( 'View Testimonial', 'splash' ),
		'all_items'          => __( 'All Testimonials', 'splash' ),
		'search_items'       => __( 'Search Testimonials', 'splash' ),
		'parent_item_colon'  => __( 'Parent Testimonials:', 'splash' ),
		'not_found'          => __( 'No Testimonials found.', 'splash' ),
		'not_found_in_trash' => __( 'No Testimonials found in Trash.', 'splash' )
	);

	$args = array(
		'labels'             => $labels,
		'menu_icon'          => 'dashicons-format-chat',
		'description'        => __( 'Testimonial Post type.', 'splash' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => false,
		'rewrite'            => array( 'slug' => 'testimonials' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail' )
	);

	register_post_type( 'testimonial', $args );
}