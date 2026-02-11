<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * The CorsHeader class adds Cross-Origin Resource Sharing (CORS) headers
 * to responses via .htaccess rules for better performance and compatibility.
 * This feature adds the necessary CORS headers at the server level.
 */
class CorsHeader {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds security-related fields, including the CORS header switch, to the settings fields array.
	 *
	 * @param array $fields An array of existing settings fields.
	 *
	 * @return array An updated array including CORS header field settings.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'cors-header',
			'name'        => 'adminease[security][cors_header]',
			'value'       => $this->settings['cors_header'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'CORS Header', 'adminease' ),
			'description' => __( "Allow Cross-Origin Resource Sharing. Add the 'Access-Control-Allow-Origin:' header to responses", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Handles the settings saved for CORS configuration and updates the .htaccess rules.
	 *
	 * @param array $sanitized_settings An array of settings containing CORS configurations.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$code = '';
		
		if( !empty( $sanitized_settings['security']['cors_header'] ) ) {
			$code = "# CORS Headers - Allow Cross-Origin Resource Sharing\n";
			$code .= "<IfModule mod_headers.c>\n";
			
			// Main CORS headers
			$code .= "\t# Allow all origins (change * to specific domain for better security)\n";
			$code .= "\tHeader always set Access-Control-Allow-Origin \"*\"\n";
			
			$code .= "\t# Allow common HTTP methods\n";
			$code .= "\tHeader always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"\n";
			
			$code .= "\t# Allow common headers\n";
			$code .= "\tHeader always set Access-Control-Allow-Headers \"Content-Type, Authorization, X-Requested-With, X-WP-Nonce, Accept, Origin\"\n";
			
			$code .= "\t# Allow credentials\n";
			$code .= "\tHeader always set Access-Control-Allow-Credentials \"true\"\n";
			
			$code .= "\t# Cache preflight requests for 1 hour\n";
			$code .= "\tHeader always set Access-Control-Max-Age \"3600\"\n";
			
			$code .= "</IfModule>\n\n";
			
			// Handle preflight OPTIONS requests
			$code .= "# Handle preflight OPTIONS requests\n";
			$code .= "<IfModule mod_rewrite.c>\n";
			$code .= "\tRewriteEngine On\n";
			$code .= "\tRewriteCond %{REQUEST_METHOD} OPTIONS\n";
			$code .= "\tRewriteRule ^(.*)$ $1 [R=200,L]\n";
			$code .= "</IfModule>\n\n";
		}
		
		Plugin::$FileHandler->stack_htaccess_rule( 'CORS_HEADERS', $code );
	}
}