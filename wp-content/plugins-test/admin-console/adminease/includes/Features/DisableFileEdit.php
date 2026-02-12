<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for disabling file editing in WordPress through the wp-config.php file.
 * It hooks into specific plugin settings to adjust the configuration dynamically.
 */
class DisableFileEdit {
	private array $settings;
	
	public function __construct() {
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		$this->settings = Plugin::get_settings( 'security' );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and returns the Adminease settings fields array.
	 *
	 * @param array $fields The existing settings fields array to be modified, typically organized by sections such as 'security'.
	 *
	 * @return array The modified settings fields array with additional configuration options for 'disable_file_edit'.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-file-edit',
			'name'        => 'adminease[security][disable_file_edit]',
			'value'       => $this->settings['disable_file_edit'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable file edit', 'adminease' ),
			'description' => __( "WordPress includes a built-in code editor that lets administrators edit theme and plugin files directly from the admin panel. While convenient, this feature can be risky—especially if someone gains access to your dashboard or makes accidental changes. Disabling file editing helps protect your site’s code from unwanted modifications. It’s a common security best practice and can be done easily by adding a single line of code to your wp-config.php file.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Saves the settings for the Adminease plugin.
	 *
	 * @param array $settings The settings array containing configuration options, including security settings for 'disable_file_edit'.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'DISALLOW_FILE_EDIT', (bool) $sanitized_settings['security']['disable_file_edit'] );
	}
}