<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for disabling file modifications in WordPress through the wp-config.php file.
 * It hooks into specific plugin settings to adjust the configuration dynamically.
 */
class DisallowFileMods {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and returns the Adminease settings fields array.
	 *
	 * @param array $fields The existing settings fields array to be modified, typically organized by sections such as 'security'.
	 *
	 * @return array The modified settings fields array with additional configuration options for 'disallow_file_mods'.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disallow-file-mods',
			'name'        => 'adminease[security][disallow_file_mods]',
			'value'       => $this->settings['disallow_file_mods'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disallow file modifications', 'adminease' ),
			'description' => __( "This setting prevents all file modifications in WordPress, including installing, updating, and deleting plugins and themes. When enabled, WordPress won't be able to write any files to your server. This is a more restrictive security measure than disabling file editing alone. It's particularly useful for production sites where updates are managed through version control or staging environments. This can be enabled by adding a single line of code to your wp-config.php file.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Saves the settings for the Adminease plugin.
	 *
	 * @param array $sanitized_settings The settings array containing configuration options, including security settings for 'disallow_file_mods'.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'DISALLOW_FILE_MODS', (bool) $sanitized_settings['security']['disallow_file_mods'] );
	}
}