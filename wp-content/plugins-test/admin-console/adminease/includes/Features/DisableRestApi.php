<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Handles disabling or restricting access to the WordPress REST API based on predefined settings.
 */
class DisableRestApi {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['disable_rest_api'] ) ) {
			// Remove REST API info from head and headers
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
			
			// Add authentication check
			add_filter( 'rest_authentication_errors', [ $this, 'check_rest_access' ] );
		}
	}
	
	/**
	 * Modifies or appends custom settings fields to the existing settings array.
	 *
	 * @param array $fields The existing array of settings fields.
	 *
	 * @return array The updated array of settings fields with modifications or additions.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'disable-rest-api',
			'name'         => 'adminease[security][disable_rest_api]',
			'value'        => $this->settings['disable_rest_api'] ?? false,
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Disable REST API', 'adminease' ),
			'description'  => __( "The WordPress REST API allows external applications and services to interact with your website’s data, such as posts, users, and settings. While it’s useful for developers building custom features or apps, it can also expose sensitive information(!) especially on sites that don’t need it. Disabling the REST API helps reduce potential security risks and protect user privacy. You can disable it completely or restrict access to logged-in users using code or a security plugin.", 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'switch',
					'id'                => 'enable-rest-api-loggedin',
					'name'              => 'adminease[security][enable_rest_api_loggedin]',
					'value'             => $this->settings['enable_rest_api_loggedin'] ?? false,
					'label_class'       => 'adminease-switch',
					'input_class'       => 'form-control',
					'label'             => __( 'Enable for logged-in users', 'adminease' ),
					'description'       => __( 'Blocking Rest API also blocks communication to wordpress.org, by enabling this feature you will allow all HTTP requests to wordpress.org, including plugin/theme updates, version checks, and other API communications.', 'adminease' ),
					'field_description' => __( "Note: If you're disabling REST API but still want to edit your website using Gutenberg/Elementor or any other service that uses REST API, enable this option.", 'adminease' ),
					'attributes'        => [
						'data-parent' => 'disable-rest-api',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Checks and manages access to the WordPress REST API based on plugin settings and user authentication.
	 *
	 * @param mixed $result The current REST API access result. Default null or pre-existing authentication error.
	 *
	 * @return mixed The modified access result, which may be a WP_Error if access is disabled, or the original result if no restrictions apply.
	 */
	public function check_rest_access( $result ) {
		if( !empty( $result ) ) {
			return $result;
		}
		
		if( apply_filters( 'adminease_disable_rest_api_bypass', false ) ) {
			return $result;
		}
		
		if( !empty( $this->settings['enable_rest_api_loggedin'] ) && is_user_logged_in() ) {
			return $result;
		}
		
		if( !empty( $this->settings['disable_rest_api'] ) ) {
			return new WP_Error(
				'rest_api_disabled',
				esc_html__( 'REST API is disabled.', 'adminease' ),
				[ 'status' => 403 ]
			);
		}
		
		return $result;
	}
}