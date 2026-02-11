<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

get_header();

$alchemists_data = get_option( 'alchemists_data' );
$post_layout        = isset( $alchemists_data['alchemists__opt-single-post-layout'] ) ? esc_html( $alchemists_data['alchemists__opt-single-post-layout'] ) : '1';
$post_layout_get    = isset( $_GET['single_post'] ) ? sanitize_key( $_GET['single_post'] ) : '';
$custom_post_layout = get_field( 'post_layout' );

if ( $custom_post_layout ) {
	$custom_post_layout = preg_replace('/[^0-9.]+/', '', $custom_post_layout);
}

if ( $post_layout_get ) {
	$post_layout = $post_layout_get;
} elseif ( $custom_post_layout ) {
	$post_layout = $custom_post_layout;
}

if ( is_singular( 'post' ) ) {

	if ( 'video' == get_post_format() ) {
		// Video Post Format
		get_template_part( 'template-parts/post/video/post', 'video' );
	} else {
		// Select a layout depends on Theme Options, GET variable or Post Options
		// Use switch to prevent LFI - no user input is used directly in file paths
		switch ( $post_layout ) {
			case '2':
				get_template_part( 'template-parts/post/post', 'single-2' );
				break;
			case '3':
				get_template_part( 'template-parts/post/post', 'single-3' );
				break;
			case '4':
				get_template_part( 'template-parts/post/post', 'single-4' );
				break;
			case '5':
				get_template_part( 'template-parts/post/post', 'single-5' );
				break;
			case '1':
			default:
				get_template_part( 'template-parts/post/post', 'single-1' );
				break;
		}
	}

} else {

	get_template_part( 'template-parts/single' );

}

get_footer();
