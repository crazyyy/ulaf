<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plugin Name: Solid Mail
 * Description: Solid Mail can help us to send emails via SMTP instead of the PHP mail() function and email logger built-in.
 * Version: 2.2.2
 * Author: SolidWP
 * Author URI: https://www.solidwp.com/
 * Text Domain: wp-smtp
 * Domain Path: /lang
 * License: GPLv3 or Later
 *
 * Copyright 2012-2022 Yehuda Hassine yehudahas@gmail.com
 * Copyright 2022-2022 WPChill heyyy@wpchill.com
 * Copyright 2023 SolidWP contact@so.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
 * The plugin was originally created by BoLiQuan
 */

define( 'WPSMTP__FILE__', __FILE__ );
define( 'WPSMTP_PLUGIN_BASE', plugin_basename( WPSMTP__FILE__ ) );
define( 'WPSMTP_PATH', plugin_dir_path( WPSMTP__FILE__ ) );
define( 'WPSMTP_URL', plugins_url( '/', WPSMTP__FILE__ ) );
define( 'WPSMTP_ASSETS_PATH', WPSMTP_PATH . 'assets/' );
define( 'WPSMTP_ASSETS_URL', WPSMTP_URL . 'assets/' );
define( 'WPSMTP_VERSION', '2.2.2' );

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/vendor-prefixed/autoload.php';

class WP_SMTP {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		register_activation_hook( __FILE__, [ $this, 'wp_smtp_activate' ] );

		add_filter( 'plugin_action_links', [ $this, 'wp_smtp_settings_link' ], 10, 2 );
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'wp_loaded', [ $this, 'load_admin_requirements' ], 16 );

		// add the new code bootstrap.
		require_once __DIR__ . '/src/functions/boot.php';
		$core = solid_mail_plugin();
		add_action(
			'plugins_loaded',
			static function () use ( $core ): void {
				$core->init();
			}
		);
	}

	public function load_admin_requirements() {
		new WPSMTP\Admin();
		new WPSMTP\Logger\Process();
	}

	public function wp_smtp_activate() {
		// @TODO: should we init solid 'solid_mail_settings' here?
		\WPSMTP\Logger\Table::install();
		// @TODO: table should be deleted on uninstalling
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wp-smtp', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	public function wp_smtp_settings_link( $action_links, $plugin_file ) {
		if ( $plugin_file === plugin_basename( __FILE__ ) ) {

			$ws_settings_link = '<a href="admin.php?page=solidwp-mail-logs">' . __( 'Logs', 'LION' ) . '</a>';
			array_unshift( $action_links, $ws_settings_link );

			$ws_settings_link = '<a href="admin.php?page=solidwp-mail">' . __( 'Settings', 'LION' ) . '</a>';
			array_unshift( $action_links, $ws_settings_link );
		}

		return $action_links;
	}
}

new WP_SMTP();
