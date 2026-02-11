<?php
/**
 * Icons for Plugins - Core class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

use TxToIT\IFP\Plugins_Page;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\IFP\Core' ) ) {

	class Core extends WP_Plugin {

		public function init() {
			parent::init();
			$this->handle_admin_plugins_page();
			$this->handle_admin_settings();
		}

		private function handle_admin_plugins_page() {
			add_filter( 'manage_plugins_columns', array( '\TxToIT\IFP\Plugins_Page', 'add_icons_column_header' ) );
			add_action( 'manage_plugins_custom_column', array( '\TxToIT\IFP\Plugins_Page', 'add_icons_column_content' ), 10, 3 );
			add_action( 'admin_head-plugins.php', array( '\TxToIT\IFP\Plugins_Page', 'add_style' ) );
			add_action( 'admin_head-plugins.php', array( '\TxToIT\IFP\Plugins_Page', 'add_js' ) );
		}

		/**
		 * Handles admin settings
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		private function handle_admin_settings() {
			add_action( 'admin_init', array( '\TxToIT\IFP\Admin_Settings', 'setup_settings_api' ) );
			add_action( 'admin_menu', array( '\TxToIT\IFP\Admin_Settings', 'create_options_page' ) );
		}
	}
}
