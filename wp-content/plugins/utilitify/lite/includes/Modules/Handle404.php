<?php

namespace Kaizencoders\Utilitify\Modules;

use Kaizencoders\Utilitify\Helper;
use Kaizencoders\Utilitify\Models\Link;

class Handle404 {

	protected $is_enable = '';

	/**
	 * Recaptcha constructor.
	 *
	 * @since 1.0.4
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_filter( 'kc_uf_filter_settings_tab', array( $this, 'add_settings_tab' ), 10, 1 );
			add_filter( 'kc_uf_filter_settings_sections', array( $this, 'add_settings' ), 10, 1 );
		} else {
			add_action( 'wp', array( $this, 'handle_404_redirect' ) );
		}
	}

	public function init() {

	}

	public function add_settings_tab( $tabs = array() ) {

		$tabs[] = array(
			'id'    => '404_redirect',
			'title' => __( '404 Redirect', 'utilitify' ),
		);

		return $tabs;
	}

	public function add_settings( $sections = array() ) {

		$default_404_options = array(

			array(
				'id'      => 'enable_404_redirect',
				'title'   => __( 'Enable 404 Redirect', 'utilitify' ),
				'desc'    => '',
				'type'    => 'switch',
				'default' => 1,
			),

			array(
				'id'      => 'redirect_all_404_requests_to',
				'title'   => __( 'Redirect All 404 Pages To', 'utilitify' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => site_url(),
			),

		);

		$sections[] = array(
			'tab_id'        => '404_redirect',
			'section_id'    => 'options',
			'section_title' => __( 'Options' ),
			'section_order' => 10,
			'fields'        => $default_404_options,
		);

		return $sections;
	}

	/**
	 * Handle 404 Request
	 *
	 * @since 1.0.1
	 */
	public function handle_404_redirect() {
		/**
		 * Detect 404 Request
		 * Check Redirection Option Is Enable?
		 * If yes, redirect to specified URL
		 * If not, say goodbye.
		 */
		if ( is_404() ) {

			$settings = KC_UF()->get_settings( 'kc_uf' );

			$enable_404_redirect = Helper::get_data( $settings, '404_redirect_options_enable_404_redirect', 1 );

			$redirect_url = Helper::get_data( $settings, '404_redirect_options_redirect_all_404_requests_to', '' );

			$current_url = Helper::get_current_url();

			if ( $current_url == $redirect_url ) {
				echo "<b>Utilitify</b> has detected that the target URL is invalid, this will cause an infinite loop redirection, please go to the plugin settings and correct the traget link! ";
				exit();
			}

			if ( 1 == $enable_404_redirect && '' != $redirect_url ) {

				$link = new Link();
				$link->insert( array(
					'link' => $current_url
				) );

				header( 'HTTP/1.1 301 Moved Permanently' );
				header( "Location: " . $redirect_url );
				exit();
			}
		}
	}


}
