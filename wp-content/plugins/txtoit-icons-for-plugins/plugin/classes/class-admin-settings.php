<?php
/**
 * Icons for Plugins - Admin settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\IFP\Admin_Settings' ) ) {

	class Admin_Settings {

		/**
		 * @var Settings_API
		 */
		public static $settings_api;

		// Sections
		public static $section_general = 'ifp_general';

		// Options
		public static $option_icon_width         = 'ifp_icon_width';
		public static $option_icon_height        = 'ifp_icon_height';
		public static $option_empty_icon_url     = 'ifp_empty_icon_url';
		public static $option_empty_icon_opacity = 'ifp_empty_icon_opacity';
		public static $option_column_position    = 'ifp_col_position';
		public static $option_grayscale_icon     = 'ifp_grayscale_icon';
		public static $option_background_size    = 'ifp_background_size';
		public static $option_guess_plugin_img   = 'ifp_guess_plugin_img';

		/**
		 * Setups settings API
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function setup_settings_api() {
			$settings_api = self::get_settings_api();

			// set the settings
			$settings_api->set_sections( self::get_settings_sections() );
			$settings_api->set_fields( self::get_settings_fields() );

			// initialize settings
			$settings_api->admin_init();
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string $option  settings field name
		 * @param string $section the section name this field belongs to
		 * @param string $default default text if it's not found
		 *
		 * @return mixed
		 */
		public static function get_option( $option, $section, $default = '' ) {

			$options = get_option( $section );

			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		public static function get_option_col_position() {
			return self::get_option( self::$option_column_position, self::$section_general, 1 );
		}

		public static function get_option_icon_width() {
			return self::get_option( self::$option_icon_width, self::$section_general, 38 );
		}

		public static function get_option_icon_height() {
			return self::get_option( self::$option_icon_height, self::$section_general, 38 );
		}

		public static function get_option_empty_icon_url() {
			return self::get_option( self::$option_empty_icon_url, self::$section_general, 'https://cdn2.iconfinder.com/data/icons/picol-vector/32/image_cancel-128.png' );
		}

		public static function get_option_empty_icon_opacity() {
			return self::get_option( self::$option_empty_icon_opacity, self::$section_general, 0.15 );
		}

		public static function get_option_grayscale() {
			return self::get_option( self::$option_grayscale_icon, self::$section_general, false );
		}

		public static function get_option_icon_background_size() {
			return self::get_option( self::$option_background_size, self::$section_general, 'cover' );
		}

		public static function get_option_guess_icons() {
			return self::get_option( self::$option_guess_plugin_img, self::$section_general, 1 );
		}

		/**
		 * Get settings sections
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return array
		 */
		public static function get_settings_sections() {
			$sections = array(
				array(
					'id'    => self::$section_general,
					'title' => __( 'General Settings', 'txtoit-icons-for-plugins' ),
				),
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return array settings fields
		 */
		public static function get_settings_fields() {
			$settings_fields = array(

				// General settings
				self::$section_general => array(

					array(
						'name'  => 'ifp_general_settings',
						'label' => __( 'General Settings', 'txtoit-icons-for-plugins' ),
						'type'  => 'title',
					),

					array(
						'name'    => self::$option_grayscale_icon,
						'label'   => __( 'Grayscale icons', 'txtoit-icons-for-plugins' ),
						'desc'    => __( 'Displays icons in grayscale', 'txtoit-icons-for-plugins' ),
						'type'    => 'checkbox',
						'default' => 'no',
					),

					array(
						'name'    => self::$option_column_position,
						'label'   => __( 'Icon column', 'txtoit-icons-for-plugins' ),
						'desc'    => __( 'Column number where icons will be displayed', 'txtoit-icons-for-plugins' ),
						'type'    => 'number',
						'default' => 1,
					),

					array(
						'name'    => self::$option_guess_plugin_img,
						'label'   => __( 'Guess plugin icons', 'txtoit-icons-for-plugins' ),
						'desc'    => __( "This option will try to guess plugins icons in case authors don't specify icons properly.", 'txtoit-icons-for-plugins' ) . '<br />' . '<p class="description"><strong>' . __( 'Note:', 'txtoit-icons-for-plugins' ) . '</strong> ' . __( 'It will may generate many 404 and 503 warnings on console though', 'txtoit-icons-for-plugins' ) . '</p>',
						'type'    => 'checkbox',
						'default' => 1,
					),

					// ICON DIMENSIONS
					array(
						'name'  => 'ifp_size',
						'label' => __( 'Icon Dimensions', 'txtoit-icons-for-plugins' ),
						'type'  => 'title',
					),
					array(
						'name'    => self::$option_icon_width,
						'label'   => __( 'Width', 'txtoit-icons-for-plugins' ),
						// 'desc'    => __( 'Adds tabs in admin login form in order to display Account Kit buttons', 'txtoit-icons-for-plugins' ),
						'type'    => 'number',
						'default' => 38,
					),
					array(
						'name'    => self::$option_icon_height,
						'label'   => __( 'Height', 'txtoit-icons-for-plugins' ),
						// 'desc'    => __( 'Adds tabs in admin login form in order to display Account Kit buttons', 'txtoit-icons-for-plugins' ),
						'type'    => 'number',
						'default' => 38,
					),
					array(
						'name'    => self::$option_background_size,
						'label'   => __( 'Background size', 'txtoit-icons-for-plugins' ),
						'type'    => 'select',
						'options' => array(
							'cover'   => __( 'Cover', 'txtoit-icons-for-plugins' ),
							'contain' => __( 'Contain', 'txtoit-icons-for-plugins' ),
						),
						'default' => 'cover',
					),

					array(
						'name'  => 'ifp_empty_icon',
						'label' => __( 'Empty icon', 'txtoit-icons-for-plugins' ),
						'type'  => 'title',
					),

					array(
						'name'    => self::$option_empty_icon_url,
						'label'   => __( 'Image URL', 'txtoit-icons-for-plugins' ),
						'desc'    => __( 'Image displayed when a plugin has no icon.', 'txtoit-icons-for-plugins' ),
						'type'    => 'text',
						'default' => 'https://cdn2.iconfinder.com/data/icons/picol-vector/32/image_cancel-128.png',
					),

					array(
						'name'    => self::$option_empty_icon_opacity,
						'label'   => __( 'Opacity', 'txtoit-icons-for-plugins' ),
						'desc'    => __( 'Empty icon opacity. Min 0,  Max 1', 'txtoit-icons-for-plugins' ),
						'type'    => 'number',
						'min'     => 0,
						'max'     => 1,
						'step'    => 0.05,
						'default' => 0.15,
					),
				),

			);
			return $settings_fields;
		}

		/**
		 * Get settings api
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param bool $smart_init
		 *
		 * @return Settings_API
		 */
		public static function get_settings_api( $smart_init = true ) {
			if ( ! self::$settings_api ) {
				self::$settings_api = new Settings_API();
			}
			return self::$settings_api;
		}

		/**
		 * Outputs options page
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function output_options_page() {
			$settings_api = self::get_settings_api();

			echo '<div class="ifp-wrapper wrap">';
			echo '<h1 style="margin-bottom:7px">Icons for Plugins</h1>';
			$settings_api->show_navigation();
			$settings_api->show_forms();
			echo '</div>';
		}

		/**
		 * Get all the pages
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}
			return $pages_options;
		}

		/**
		 * Create options page
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public static function create_options_page() {
			add_options_page( 'Plugins icons', 'Plugins icons', 'delete_posts', 'txtoit-icons-for-plugins', array( __CLASS__, 'output_options_page' ) );
		}

	}
}
