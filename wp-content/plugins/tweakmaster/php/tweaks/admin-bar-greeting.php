<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

// we need init for is_wp_admin_bar_showing, plugins_loaded is too early
add_action( 'init', __NAMESPACE__ . '\set_admin_bar_greeting' );

function set_admin_bar_greeting(): void {

	if ( is_admin_bar_showing() ) {
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_bar_greeting_module' );
		add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_bar_greeting_module' );
		add_filter( 'wp_script_attributes', __NAMESPACE__ . '\add_greeting_data_attribute' );
	}
}

function enqueue_admin_bar_greeting_module(): void {

	wp_enqueue_script_module(
		'tweakmaster-admin-bar-greeting',
		plugins_url( 'php/tweaks/admin-bar-greeting.js', PLUGIN_FILE ),
		array(),
		VERSION
	);
}

/**
 * @param array<string, string> $attributes HTML tag attributes
 *
 * @return array<string, string>            HTML tag attributes (modified)
 */
function add_greeting_data_attribute( array $attributes ): array {

	if ( 'tweakmaster-admin-bar-greeting-js-module' === $attributes['id'] ) {
		$attributes['data-tweakmaster-greeting'] = trim( options()['admin-bar-greeting'] ) . ' ';
	}

	return $attributes;
}
