<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Comments
 * Description:      Disable Comments
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'admin_init', __NAMESPACE__ . '\redirect_comments_page' );
add_action( 'admin_init', __NAMESPACE__ . '\remove_comments_metabox' );
add_action( 'admin_init', __NAMESPACE__ . '\disable_comments_support' );

// Close comments on the front-end
add_filter( 'comments_open', '__return_false', 20, 0 );
add_filter( 'pings_open', '__return_false', 20, 0 );

// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10, 0 );

add_action( 'admin_menu',  __NAMESPACE__ . '\remove_comments_page_menu' );
add_action( 'admin_bar_menu',  __NAMESPACE__ . '\remove_comments_admin_bar_links', 0 );

/**
 * Redirect any user trying to access comments page.
 */
function redirect_comments_page(): void {

	if ( 'edit-comments.php' === $GLOBALS['pagenow'] ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}

function remove_comments_metabox(): void {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

/**
 * Disable support for comments and trackbacks in post types
 *
 * Goes through all post types and disables support for comments and trackbacks
 * if the post type supports comments.
 */
function disable_comments_support(): void {

	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

function remove_comments_page_menu(): void {
	remove_menu_page( 'edit-comments.php' );
}

function remove_comments_admin_bar_links(): void {
	remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
}
