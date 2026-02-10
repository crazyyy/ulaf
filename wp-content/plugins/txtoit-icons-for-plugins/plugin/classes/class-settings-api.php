<?php
/**
 * Icons for Plugins - Settings API
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
 */

namespace TxToIT\IFP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'TxToIT\IFP\Settings_API' ) ) {

	class Settings_API extends \WeDevs_Settings_API {

		/**
		 * Displays a title field
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args settings field args
		 */
		function callback_title( $args ) {
			$html = $this->get_field_description( $args );
			echo $html;
		}

		/**
		 * Displays a description field
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $args settings field args
		 */
		function callback_description( $args ) {
			$html = $this->get_field_description( $args );
			echo $html;
		}

		/**
		 * Fixes style
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function _style_fix() {
			parent::_style_fix();
			?>
			<style>
				#wpbody-content .metabox-holder h2{ padding: 0 !important; }
				.wedevs-title th {
					color: #23282d;
				}
				.wedevs-title label{
					font-size: 1.3em !important;
				}
				.wedevs-title * {
					cursor: auto;
				}
				[id*="ifp_"] h2{
					font-weight:bold;
					display:none;
				}
				[id*="ifp_"] form div:last-child{
					 padding-left:0 !important;
				 }
				.ifp-wrapper .metabox-holder{
					padding-top:0 !important;
				}
			</style>
			<?php
		}

		/**
		 * Set settings fields
		 *
		 * @param array $fields
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return $this
		 */
		function set_fields( $fields ) {
			foreach ( $fields as $section => $field ) {
				foreach ( $field as $key => $option ) {
					if ( $option['type'] == 'title' ) {
						$fields[ $section ][ $key ]['class'] = 'wedevs-title';
						// $fields[ $section ][ $key ]['name'] = '';
					}
				}
			}
			return parent::set_fields( $fields );
		}
	}
}
