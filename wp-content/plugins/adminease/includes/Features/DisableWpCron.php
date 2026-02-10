<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for managing the DISABLE_WP_CRON configuration based on Adminease settings.
 */
class DisableWpCron {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'performance' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds and configures the settings fields for Adminease, including performance options.
	 *
	 * @param array $fields The array of existing settings fields to be updated.
	 *
	 * @return array The updated array of settings fields with additional configuration.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['performance']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-wp-cron',
			'name'        => 'adminease[performance][disable_wp_cron]',
			'value'       => $this->settings['disable_wp_cron'] ?? '',
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable WP Cron', 'adminease' ),
			'description' => __( "The <strong>WordPress Cron (WP-Cron)</strong> system is what handles scheduled tasks like publishing scheduled posts, checking for updates, or sending emails. By default, WP-Cron runs every time someone visits your site—which can lead to performance issues on busy or low-traffic websites. <strong>Disabling WP-Cron</strong> allows you to take control and replace it with a real server-side cron job that runs at set intervals. This improves reliability and efficiency, especially on larger or more dynamic sites.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Handles the saving of Adminease settings by updating specific constants in the WordPress configuration.
	 *
	 * @param array $sanitized_settings The array of settings passed, including performance options.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'DISABLE_WP_CRON', (bool) $sanitized_settings['performance']['disable_wp_cron'] );
	}
}