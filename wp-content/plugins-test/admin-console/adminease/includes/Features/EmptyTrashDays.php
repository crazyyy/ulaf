<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages WordPress trash retention period via EMPTY_TRASH_DAYS constant
 */
class EmptyTrashDays {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'posts' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds or modifies the "security" settings fields to include an option for configuring the trash emptying duration.
	 *
	 * @param array $fields The existing fields array
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['posts']['fields'][] = [
			'type'              => 'select',
			'id'                => 'empty-trash-days',
			'name'              => 'adminease[posts][empty_trash_days]',
			'value'             => $this->settings ['empty_trash_days'] ?? '',
			'options'           => [
				''    => __( 'Select', 'adminease' ),
				'0'   => __( 'Disable Trash (Delete Permanently)', 'adminease' ),
				'1'   => __( '1 Day', 'adminease' ),
				'7'   => __( '7 Days', 'adminease' ),
				'14'  => __( '14 Days', 'adminease' ),
				'30'  => __( '30 Days (Default)', 'adminease' ),
				'60'  => __( '60 Days', 'adminease' ),
				'90'  => __( '90 Days', 'adminease' ),
				'180' => __( '180 Days', 'adminease' ),
				'365' => __( '365 Days', 'adminease' ),
			],
			'input_class'       => 'form-control adminease-choices',
			'label_class'       => 'adminease-label',
			'label'             => __( 'Empty Trash Days', 'adminease' ),
			'description'       => __( 'Controls how many days WordPress keeps deleted content in trash before permanently deleting it. Set to "Disable Trash" to permanently delete items immediately without using trash.', 'adminease' ),
			'field_description' => __( 'By default, WordPress keeps deleted posts, pages, and comments in the trash for 30 days. After that, they are permanently deleted.', 'adminease' ),
			'placeholder'       => __( 'Select', 'adminease' ),
			'attributes'        => [
				'data-allow_clear' => true,
			],
		];
		
		return $fields;
	}
	
	/**
	 * Handles saving of AdminEase settings and updates configuration constants.
	 *
	 * @param array $sanitized_settings An associative array of settings where each key corresponds to a configuration option.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		Plugin::$FileHandler->stack_wp_config_constant( 'EMPTY_TRASH_DAYS', $sanitized_settings['posts']['empty_trash_days'] );
	}
}