<?php
/**
 * Uninstall Script for Mini Gallery Plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom post type data
$mgwpp_sowar = get_posts(array(
    'post_type' => 'mgwpp_soora',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($mgwpp_sowar as $mgwpp_gallery_image) {
    wp_delete_post(intval($mgwpp_gallery_image->ID), true);
}
