<?php
/*
Plugin Name: Players
Plugin URI: http://wp.tutsplus.com/
Description: PLayers base
Version: 1.0
Author: Misha Husak
Author URI: http://wp.tutsplus.com/
License: GPLv2
*/
add_action( 'init', 'create_players_stats' );
function create_movie_review() {
    register_post_type( 'movie_reviews',
        array(
            'labels' => array(
                'name' => 'Players',
                'singular_name' => 'Players',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Player',
                'edit' => 'Edit',
                'edit_item' => 'Edit Player',
                'new_item' => 'New Player',
                'view' => 'View',
                'view_item' => 'View Player',
                'search_items' => 'Search Player',
                'not_found' => 'No Players found',
                'not_found_in_trash' => 'No Players found in Trash',
                'parent' => 'Parent Movie Review'
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'img/logo.png', __FILE__ ),
            'has_archive' => true
        )
    );
}
?>
