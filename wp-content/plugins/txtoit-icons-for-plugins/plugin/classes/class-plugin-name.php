<?php
/**
 * Icons for Plugins - Plugin name
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

if ( ! class_exists( 'TxToIT\IFP\Plugin_Name' ) ) {

	class Plugin_Name {
		public static function guess_plugin_name_by_after_dash( $plugin_full_name ) {
			$plugin_name_with_php = strstr( $plugin_full_name, '/' );
			$plugin_name_with_php = str_replace( '/', '', $plugin_name_with_php );
			$plugin_name          = str_replace( '.php', '', $plugin_name_with_php );
			return $plugin_name;
		}

		public static function guess_plugin_name_by_before_dash( $plugin_full_name ) {
			$plugin_name = substr( $plugin_full_name, 0, strpos( $plugin_full_name, '/' ) );
			return $plugin_name;
		}
	}
}

