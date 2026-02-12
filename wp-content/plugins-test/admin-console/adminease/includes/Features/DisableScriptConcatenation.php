<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the disabling of script concatenation in WordPress based on plugin security settings.
 */
class DisableScriptConcatenation {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and returns the settings fields for the admin panel, adding a security-related field option.
	 *
	 * @param array $fields The array of existing settings fields.
	 *
	 * @return array The modified array of settings fields, including the added 'disable_script_concatenation' field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-script-concatenation',
			'name'        => 'adminease[security][disable_script_concatenation]',
			'value'       => $this->settings['disable_script_concatenation'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable scripts concatenation in WordPress admin panel', 'adminease' ),
			'description' => __( "This security option turns off concatenation of scripts running in the WordPress admin panel, preventing your website from being affected by certain DoS attacks. Turning off concatenation of scripts might slightly affect the performance of WordPress admin panel, but it should not affect visitors' experience on your WordPress website.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Updates and stacks WordPress configuration constants based on provided settings.
	 *
	 * @param array $sanitized_settings The settings array containing configuration options, specifically 'security' related settings.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'CONCATENATE_SCRIPTS', !(bool) $sanitized_settings['security']['disable_script_concatenation'] );
	}
}