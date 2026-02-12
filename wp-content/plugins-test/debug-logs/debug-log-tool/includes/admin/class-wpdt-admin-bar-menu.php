<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPDT_Admin_Bar_Menu' ) ) :
	final class WPDT_Admin_Bar_Menu {

		/**
		 * Initalize the class.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_menu' ), 100 );
		}

		/**
		 * Add the main menu and submenus to the admin bar.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
		 */
		public static function add_admin_bar_menu( $wp_admin_bar ) {
			// Add the main menu.
			$wp_admin_bar->add_node(
				array(
					'id'    => 'wpdt_debug_log_menu',
					'title' => 'BugTrace',
					'href'  => admin_url( 'admin.php?page=wpdt-debugging' ),
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id'     => 'view_log',
					'parent' => 'wpdt_debug_log_menu',
					'title'  => 'View Log',
					'href'   => admin_url( 'admin.php?page=wpdt-debugging' ),
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id'     => 'download_log',
					'parent' => 'wpdt_debug_log_menu',
					'title'  => 'Download Log',
					'href'   => admin_url( 'admin.php?page=wpdt-debugging&action=download&nonce=' . wp_create_nonce( 'wpdt_download_nonce' ) ),
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id'     => 'clear_log',
					'parent' => 'wpdt_debug_log_menu',
					'title'  => 'Clear Log',
					'href'   => admin_url( 'admin.php?page=wpdt-debugging&action=reset&nonce=' . wp_create_nonce( 'wpdt_reset_nonce' ) ),
				)
			);
		}
	}
endif;
WPDT_Admin_Bar_Menu::init();
