<?php
/**
 * Icons for Plugins - Plugin Image
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

if ( ! class_exists( 'TxToIT\IFP\Plugin_Image' ) ) {

	class Plugin_Image {


		public static function guess_plugin_image_url( $plugin_name, $extension = 'png' ) {
			return $image_url = "https://ps.w.org/{$plugin_name}/assets/icon-128x128.{$extension}";
		}

		public static function get_image_url( $plugin_infs ) {
			$image_url = '';

			if ( ! empty( $plugin_infs['icons']['1x'] ) ) {
				$image_url = $plugin_infs['icons']['1x'];
			} elseif ( ! empty( $plugin_infs['icons']['2x'] ) ) {
				$image_url = $plugin_infs['icons']['2x'];
			} elseif ( ! empty( $plugin_infs['banners']['1x'] ) ) {
				$image_url = $plugin_infs['banners']['1x'];
			} elseif ( ! empty( $plugin_infs['banners']['2x'] ) ) {
				$image_url = $plugin_infs['banners']['2x'];
			}

			$plugin_title = ! empty( $plugin_infs['Title'] ) ? $plugin_infs['Title'] : '';
			$plugin_title = ! empty( $plugin_infs['title'] ) ? $plugin_infs['title'] : $plugin_title;
			$image_url    = apply_filters( 'ifp_plugin_icon_url', $image_url, $plugin_title, $plugin_infs );
			return $image_url;
		}

		public static function guess_plugin_image_url_by_name_after_dash( $plugin_full_name, $extension = 'png' ) {
			$plugin_name_guess_1 = Plugin_Name::guess_plugin_name_by_after_dash( $plugin_full_name );
			$image_url_guess_1   = self::guess_plugin_image_url( $plugin_name_guess_1, $extension );
			return $image_url_guess_1;
		}

		public static function guess_plugin_image_url_by_name_before_dash( $plugin_full_name, $extension = 'png' ) {
			$plugin_name_guess_2 = Plugin_Name::guess_plugin_name_by_before_dash( $plugin_full_name );
			$image_url_guess_2   = self::guess_plugin_image_url( $plugin_name_guess_2, $extension );
			return $image_url_guess_2;
		}
	}
}
