<?php
/**
 * Wordpress SVG Removal
 * Remove Wordpress default SVGs from the body.
 * 
 * @since 1.0.0 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if accessed directly. 
}

remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );