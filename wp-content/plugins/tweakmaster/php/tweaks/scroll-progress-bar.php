<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nextgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scroll_assets' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scroll_assets' );

add_action( 'wp_body_open', __NAMESPACE__ . '\scroll_progress_bar' );
add_action( 'admin_footer', __NAMESPACE__ . '\scroll_progress_bar' );

function scroll_progress_bar(): void {
	echo '<div class="tweakmaster-scroll"><div class="tweakmaster-scroll__progress"></div></div>';
}

function enqueue_scroll_assets(): void {

	wp_enqueue_style(
		'tweakmaster-scroll-progress-bar',
		plugins_url( 'php/tweaks/scroll-progress-bar.css', PLUGIN_FILE ),
		array(),
		VERSION
	);

	$bg              = options()['scroll-progress-bar-bg-color'];
	$progress_bg     = options()['scroll-progress-bar-progress-bg-color'];
	$progress_height = options()['scroll-progress-bar-progress-height'];
	$css             = '
		.tweakmaster-scroll{
			background-color:' . esc_html( $bg ) . '
		}
		.tweakmaster-scroll__progress{
			width:0%;
			height:' . esc_html( $progress_height ) . ';
			background-color:' . esc_html( $progress_bg ) . ';
		}';

	$css = str_replace( array( "\n", "\r", "\t" ), '', $css );

	wp_add_inline_style( 'tweakmaster-scroll-progress-bar', wp_strip_all_tags( $css ) );

	wp_enqueue_script_module(
		'tweakmaster-scroll-progress-bar',
		plugins_url( 'php/tweaks/scroll-progress-bar.js', PLUGIN_FILE ),
		array(),
		VERSION
	);
}
