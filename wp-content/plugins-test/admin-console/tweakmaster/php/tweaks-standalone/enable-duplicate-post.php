<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Disable Duplicate Post
 * Description:      Adds links to the posts overview screen to duplicate a post
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

const DP_ACTION_NAME = 'tweakmaster_duplicate_post';
const DP_NONCE_NAME  = 'tweakmaster_duplicate_post_nonce';

add_action( 'admin_action_' . DP_ACTION_NAME, __NAMESPACE__ . '\duplicate_post' );

add_filter( 'post_row_actions', __NAMESPACE__ . '\duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', __NAMESPACE__ . '\duplicate_post_link', 10, 2 );
/**
 * @param  array<int|string, string> $actions The actions added as links to the admin.
 * @param  \WP_Post                  $post    The post object.
 * @return array<int|string, string>          The actions added as links to the admin.
 */
function duplicate_post_link( array $actions, \WP_Post $post ): array {

	$post_type_object = get_post_type_object( $post->post_type );

	if ( null === $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action'  => DP_ACTION_NAME,
				'post_id' => $post->ID,
			),
			'admin.php'
		),
		'tweakmaster_duplicate_post_' . $post->ID,
		DP_NONCE_NAME
	);

	$actions['tweakmaster_duplicate'] = '<a href="' . $url . '" title="Duplicate item" rel="permalink">Duplicate</a>';

	return $actions;
}

function duplicate_post(): void {

	if ( empty( $_GET['post_id'] ) ) {
		wp_die( 'No post id set for the duplicate action.' );
	}

	// Check the nonce specific to the post we are duplicating.
	if ( ! isset( $_GET[ DP_NONCE_NAME ] )
		|| ! wp_verify_nonce(
			sanitize_key( wp_unslash( $_GET[ DP_NONCE_NAME ] ) ),
			'tweakmaster_duplicate_post_' . (int) sanitize_key( wp_unslash( $_GET['post_id'] ) )
		)
	) {
		// Display a message if the nonce is invalid, may it expired.
		wp_die( esc_html__( 'The link you followed has expired, please try again.', 'tweakmaster' ) );
	}

	$post_id = (int) sanitize_key( wp_unslash( $_GET['post_id'] ) );
	$post    = get_post( $post_id );

	if ( ! $post ) {
		wp_die( esc_html__( 'Post to duplicate not found.', 'tweakmaster' ) );
	}

	$current_user = wp_get_current_user();
	$new_post     = array(
		'comment_status' => $post->comment_status,
		'menu_order'     => $post->menu_order,
		'ping_status'    => $post->ping_status,
		'post_author'    => $current_user->ID,
		'post_content'   => $post->post_content,
		'post_excerpt'   => $post->post_excerpt,
		'post_name'      => $post->post_name,
		'post_parent'    => $post->post_parent,
		'post_password'  => $post->post_password,
		'post_status'    => 'draft',
		'post_title'     => $post->post_title . ' (copy)', // Add "(copy)" to the title.
		'post_type'      => $post->post_type,
		'to_ping'        => $post->to_ping,
	);

	$duplicate_id = wp_insert_post( $new_post );
	$taxonomies   = get_object_taxonomies( get_post_type( $post ) );

	if ( $taxonomies ) {
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $duplicate_id, $post_terms, $taxonomy );
		}
	}

	// Copy all the custom fields.
	$post_meta = get_post_meta( $post_id );
	if ( $post_meta ) {

		foreach ( $post_meta as $meta_key => $meta_values ) {
			if ( '_wp_old_slug' === $meta_key ) { // skip old slug.
				continue;
			}
			foreach ( $meta_values as $meta_value ) {
				add_post_meta( $duplicate_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'action' => 'edit',
				'post'   => $duplicate_id,
			),
			admin_url( 'post.php' )
		)
	);
	exit;
}
