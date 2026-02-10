<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Hide PHP Version Feature
 * Removes X-Powered-By header for security
 */
class HidePhpVersion {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds a new settings field for hiding the PHP version to the provided settings array.
	 *
	 * @param array $fields The existing array of settings fields.
	 *
	 * @return array The modified array of settings fields including the 'hide_php_version' field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'          => 'switch',
			'id'            => 'hide-php-version',
			'name'          => 'adminease[security][hide_php_version]',
			'value'         => $this->settings['hide_php_version'] ?? false,
			'label_class'   => 'adminease-switch',
			'input_class'   => 'form-control',
			'wrapper_class' => 'form-group',
			'label'         => __( 'Hide PHP Version', 'adminease' ),
			'description'   => __( 'Remove X-Powered-By header that reveals your PHP version to potential attackers.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Handles the saving of AdminEase settings and applies or removes the PHP version hiding rule based on configuration.
	 *
	 * @param array $sanitized_settings The array of settings containing configuration options, including the 'security' section with the 'hide_php_version' flag.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$hide_php_version = $sanitized_settings['security']['hide_php_version'] ?? false;
		
		if( $hide_php_version ) {
			$this->apply_php_version_hiding();
		} else {
			$this->remove_php_version_hiding();
		}
	}
	
	/**
	 * Applies PHP version hiding by updating htaccess rules to unset the X-Powered-By header.
	 * @return void
	 */
	private function apply_php_version_hiding(): void {
		$htaccess_code = "<IfModule mod_headers.c>\n";
		$htaccess_code .= "\tHeader unset X-Powered-By\n";
		$htaccess_code .= "\tHeader always unset X-Powered-By\n";
		$htaccess_code .= "</IfModule>\n";
		
		Plugin::$FileHandler->stack_htaccess_rule( 'HIDE_PHP_VERSION', $htaccess_code );
	}
	
	/**
	 * Removes the PHP version hiding rule from the .htaccess file.
	 * @return void
	 */
	private function remove_php_version_hiding(): void {
		Plugin::$FileHandler->stack_htaccess_rule( 'HIDE_PHP_VERSION', '' );
	}
}