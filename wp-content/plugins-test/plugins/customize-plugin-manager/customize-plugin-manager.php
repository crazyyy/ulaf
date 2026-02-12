<?php
/**
 * Plugin Name: Customize Plugin Manager
 * Plugin URI: https://core.trac.wordpress.org/ticket/40451
 * Description: Manage plugins within the customizer (experimental).
 * Version: 0.2
 * Author: Nick Halsey
 * Author URI: http://nick.halsey.co/
 * Tags: customize, plugins
 * License: GPL
 * Text Domain: customize-plugin-manager

=====================================================================================
Copyright (C) 2018 Nick Halsey

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
=====================================================================================
*/


// Settings are typically previewed on the `wp_loaded` action, immediately after the `customize_loaded` action.
// The `active_plugins` option needs to be previewed before plugins are actually loaded, which is not possible from within this plugin.
// Therefore, this plugin cannot actually preview the use of different active plugins in the customize preview; however,
// it will not activate plugins on the live site until settings are saved/published.

add_action( 'customize_controls_enqueue_scripts', 'customize_plugin_manager_enqueue' );
function customize_plugin_manager_enqueue() {
	wp_enqueue_style( 'customize-plugin-manager', plugin_dir_url( __FILE__ ) . '/customize-plugin-manager.css' );
}

// Register all settings and controls.
add_action( 'customize_register', 'customize_plugin_manager_register' );
function customize_plugin_manager_register( $wp_customize ) {

	// Add the plugins section.
	$wp_customize->add_section( 'plugins', array(
		'title'    => __( 'Plugins', 'customize-plugin-manager' ),
		'description' => __( 'Toggle the checkboxes below to activate/deactivate plugins. The preview cannot currently reflect which plugins are active; however, changes are not published to your live site until you save. You may need to reload the customizer to see plugin-specific options.' ),
	) );

	// This is the existing core active_plugins option, which determines which plugins to load.
	// The value is stored as an array of plugin ids.
	// The setting is managed in JS by the individual plugin controls.
	// This setting isn't previewed because the option is used in an action that occurs before 
	// this plugin is loaded and before customize settings are registered and previewed.
	$wp_customize->add_setting( 'active_plugins', array(
		'type' => 'option',
		'capability' => 'manage_options',
		'default' => array(),
		'transport' => 'refresh', // Explicitly require full refresh to preview a plugin change.
		'sanitize_callback' => 'customize_plugin_manager_sanitize_plugins_option',
	) );

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once( plugin_dir_path( __FILE__ ) . '/class-wp-customize-plugin-control.php' );

	$wp_customize->register_control_type( 'WP_Customize_Plugin_Control' );

	$all_plugins = get_plugins();

	foreach( $all_plugins as $id => $plugin ) {

		if ( is_plugin_active_for_network( $id ) ) {
			$status = 'network';
		} elseif ( is_plugin_active( $id ) ) {
			$status = 'active';
		} else {
			$status = 'inactive';
		}

		if ( 'customize-plugin-manager/customize-plugin-manager.php' === $id ) {
			$status = 'network'; // Don't allow this plugin to be deactived.
		}

		$plugin_id = $id;
		$id = 'cpm_' . str_replace( '/', '_', str_replace( '.', '_', $id ) );

		// Placeholder individual customize settings are not used.
		$wp_customize->add_setting( $id, array( 'type' => 'option' ) );

		$wp_customize->add_control( new WP_Customize_Plugin_Control( $wp_customize, $id, array(
			'label' => $plugin['Name'],
			'description' => wp_trim_words( $plugin['Description'], 30 ),
			'section' => 'plugins',
			'status' => $status,
			'plugin' => $plugin_id,
		) ) );
	}
}

// Sanitize customize setting value for active plugins.
function customize_plugin_manager_sanitize_plugins_option( $value ) {
	if ( ! is_array( $value ) ) {
		// Something went wrong - revert to the previous option value to minimize breakage.
		return get_option( 'active_plugins' );
	}

	// Validate each plugin in the value array and remove invalid plugins.
	foreach ( $value as $plugin ) {
		$valid = validate_plugin( $plugin );
		if ( is_wp_error( $valid ) ) {
			unset( $value[$plugin] );
		}
	}

	// Don't allow this plugin to be deactivated from here.
	if ( ! array_key_exists( 'customize-plugin-manager/customize-plugin-manager.php', $value ) ) {
		$value[] = 'customize-plugin-manager/customize-plugin-manager.php';
	}

	sort( $value ); // Resort plugins, matching behavior of `activate_plugin()`.

	return $value;
}
