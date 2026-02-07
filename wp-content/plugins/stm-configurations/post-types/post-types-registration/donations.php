<?php

add_action( 'init', 'stm_donation_init' );
/**
 * Register a Donation post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function stm_Donation_init() {
	$labels = array(
		'name'               => _x( 'Donations', 'post type general name', 'splash' ),
		'singular_name'      => _x( 'Donation', 'post type singular name', 'splash' ),
		'menu_name'          => _x( 'Donations', 'admin menu', 'splash' ),
		'name_admin_bar'     => _x( 'Donation', 'add new on admin bar', 'splash' ),
		'add_new'            => _x( 'Add New', 'Donation', 'splash' ),
		'add_new_item'       => __( 'Add New Donation', 'splash' ),
		'new_item'           => __( 'New Donation', 'splash' ),
		'edit_item'          => __( 'Edit Donation', 'splash' ),
		'view_item'          => __( 'View Donation', 'splash' ),
		'all_items'          => __( 'All Donations', 'splash' ),
		'search_items'       => __( 'Search Donations', 'splash' ),
		'parent_item_colon'  => __( 'Parent Donations:', 'splash' ),
		'not_found'          => __( 'No Donations found.', 'splash' ),
		'not_found_in_trash' => __( 'No Donations found in Trash.', 'splash' )
	);

	$args = array(
		'labels'             => $labels,
		'menu_icon'          => 'dashicons-smiley',
		'description'        => __( 'Donation Post type.', 'splash' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'donations' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' )
	);

	register_post_type( 'donation', $args );
}