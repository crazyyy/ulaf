<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name:      Enable Maintenance Mode
 * Description:      Only users with delete_plugins can log and site frontend will be blocked
 * Plugin URI:       https://nexgenthemes.com/plugins/tweakmaster/
 * Version:          1.0.0
 * Author:           Nicolas Jonas
 * Author URI:       https://nexgenthemes.com
 * License:          GPLv3
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

const CAPABILITY = 'delete_plugins';

add_action( 'init', __NAMESPACE__ . '\block_site_access' );
add_action( 'admin_bar_menu', __NAMESPACE__ . '\add_maintenance_mode_notice', 100 );
add_filter( 'login_message', __NAMESPACE__ . '\login_message' );
add_action( 'wp_authenticate_user', __NAMESPACE__ . '\check_user_capability' );

function check_user_capability( \WP_User $user ): void {

	if ( ! user_can( $user, CAPABILITY ) ) {

		// Deny the user
		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user->ID, false );
		do_action( 'wp_logout' );
		wp_safe_redirect( wp_login_url() );
		exit;
	}
}

function block_site_access(): void {

	if ( ! current_user_can( 'delete_plugins' ) && ! is_admin() && ! is_login() /* && ! is_wp_cli() */ ) {
		wp_die(
			wp_kses(
				sprintf(
					// Translators: URL
					__( 'Site is currently under maintenance. Please check back later. <a href="%s">Login</a>', 'tweakmaster' ),
					esc_url( wp_login_url() )
				),
				array(
					'a' => array(
						'href' => true,
					),
				),
				array( 'http', 'https' )
			)
		);
	}
}

/**
 * Adds a node to the admin bar indicating that the site is in maintenance mode.
 *
 * This is only shown to users who are not logged in or do not have the CAPABILITY.
 *
 * @param \WP_Admin_Bar $wp_admin_bar The admin bar object.
 */
function add_maintenance_mode_notice( \WP_Admin_Bar $wp_admin_bar ): void {

	$wp_admin_bar->add_node(
		array(
			'id'     => 'maintenance_mode',
			'title'  => sprintf(
				'<a style="color: red; font-weight: bold;" href="%s">%s</a>',
				esc_url( admin_url( 'options-general.php?page=nextgenthemes_tweakmaster' ) ),
				__( 'MAINTENANCE MODE ACTIVE', 'tweakmaster' )
			),
			'meta'   => array(
				'html'  => true,
				'class' => 'maintenance-mode',
			),
			'parent' => false,
		)
	);
}

function is_wp_cli(): bool {
	/** @disregard P1011 */
	return defined( 'WP_CLI' ) && \WP_CLI;
}

function login_message( string $message ): string {

	$message .= sprintf(
		'<h2 style="%s">
			<strong>%s</strong>
		</h2>
		<div class="notice notice-error message">
			<p style="font-size: 1.2em;">
				%s
			</p>
		</div>',
		'color: red; text-align: center; margin-bottom: .7em;',
		esc_html__( 'Maintenance Mode Active!', 'tweakmaster' ),
		sprintf(
			// Translators: %s: user capability (delete_plugins).
			esc_html__( 'Only users who can %s (typically administrators) are allowed to login to this site while the maintenance is active. Please check back later.', 'tweakmaster' ),
			sprintf( '<code>%s</code>', esc_html( CAPABILITY ) )
		)
	);

	return $message;
}
