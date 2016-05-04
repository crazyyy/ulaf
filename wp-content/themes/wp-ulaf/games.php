<?php
add_action( 'init', 'post_type_games' );
function post_type_games() {

  $labels = array(
    'name' => 'Games',
    'singular_name' => 'Games',
    'add_new' => 'Add',
    'add_new_item' => 'Add',
    'edit' => 'Edit',
    'edit_item' => 'Edit',
    'new-item' => 'Add',
    'view' => 'View',
    'view_item' => 'View',
    'search_items' => 'Search',
    'not_found' => 'Not Found',
    'not_found_in_trash' => 'Not Found',
    'parent' => 'Parent'
  );

  $args = array(
    'description' => 'Games Post Type',
    'show_ui' => true,
    'menu_position' => 5,
    'exclude_from_search' => false,
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'supports' => array('title','editor','thumbnail'),
    'has_archive' => true,
    'rewrite' => array( 'slug' => 'games' ),
    // https://developer.wordpress.org/resource/dashicons/
    'menu_icon' => 'dashicons-universal-access',
    'show_in_rest' => true
  );

  register_post_type( 'games' , $args );
}

?>
