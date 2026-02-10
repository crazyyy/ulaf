<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages WordPress trash retention period via EMPTY_TRASH_DAYS constant
 */
class AutosaveInterval {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'performance' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds additional settings fields to the provided settings array.
	 * Specifically, it includes a field for configuring the "Autosave Interval"
	 * in the "performance" section of the settings.
	 *
	 * @param array $fields The existing settings fields array to which new fields will be added.
	 *
	 * @return array The modified settings fields array with the added "Autosave Interval" field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['performance']['fields'][] = [
			'type'              => 'number',
			'id'                => 'autosave-interval',
			'name'              => 'adminease[performance][autosave_interval]',
			'value'             => $this->settings['autosave_interval'] ?? '',
			'label_class'       => 'adminease-label',
			'input_class'       => 'form-control',
			'label'             => __( 'Autosave Interval', 'adminease' ),
			'description'       => __( "The <strong>Autosave Interval</strong> is how often WordPress automatically saves your posts and pages while you're editing them. This feature helps prevent data loss if your browser crashes or you accidentally close the tab. By default, WordPress autosaves every 60 seconds (1 minute). You can change this interval to save more frequently or less often, depending on your needs.", 'adminease' ),
			'field_description' => __( 'By default, WordPress autosaves every <strong>60 seconds</strong>.', 'adminease' ),
			'attributes'        => [
				'min'  => 5,
				'max'  => 3600,
				'step' => 5,
			],
		];
		
		return $fields;
	}
	
	/**
	 * Saves the adminease plugin settings and updates wp-config constants accordingly.
	 *
	 * @param array $sanitized_settings The sanitized settings array containing performance configurations.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'AUTOSAVE_INTERVAL', $sanitized_settings['performance']['autosave_interval'] );
	}
}