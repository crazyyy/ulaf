<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * This class manages and adjusts the WordPress memory limits based on specified performance settings.
 * It integrates with the WordPress settings saved action to dynamically modify the WP_MEMORY_LIMIT
 * and WP_MAX_MEMORY_LIMIT values in the wp-config.php file.
 */
class WpMemoryLimit {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'performance' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Configures and extends the settings fields to include performance-related options.
	 *
	 * @param array $fields The existing fields array to which new settings will be appended.
	 *
	 * @return array The updated fields array containing additional performance settings for WP memory limits and related configurations.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['performance']['fields'][] = [
			'type'              => 'select',
			'id'                => 'wp-memory-limit',
			'name'              => 'adminease[performance][wp_memory_limit]',
			'value'             => $this->settings['wp_memory_limit'] ?? '',
			'options'           => [
				''      => __( 'Select', 'adminease' ),
				'40M'   => '40M',
				'64M'   => '64M',
				'128M'  => '128M',
				'256M'  => '256M',
				'512M'  => '512M',
				'1G'    => '1G',
				'2G'    => '2G',
				'4G'    => '4G',
				'8G'    => '8G',
				'16G'   => '16G',
				'32G'   => '32G',
				'other' => __( 'Other', 'adminease' ),
			],
			'input_class'       => 'form-control adminease-choices toggle-field',
			'label_class'       => 'adminease-label',
			'label'             => __( 'WP Memory Limit', 'adminease' ),
			'description'       => __( 'The <strong>WP Memory Limit</strong> is the maximum amount of memory that WordPress is allowed to use for running themes, plugins, and core functions. If your site runs out of memory, you may see errors or experience performance issues. Increasing the memory limit can help improve stability—especially on sites with advanced features or many plugins.', 'adminease' ),
			'field_description' => __( 'The default memory limit in WordPress is initially 32MB, but WordPress attempts to increase this to 40MB for single sites and 64MB for multisite installations.<br> WordPress defaults to a maximum memory limit of 40MB for a single site and 64MB for a WordPress Multisite network.', 'adminease' ),
			'placeholder'       => __( 'Select', 'adminease' ),
			'attributes'        => [
				'data-allow_clear' => true,
			],
			'child_fields'      => [
				[
					'type'              => 'number',
					'id'                => 'wp-memory-limit-other',
					'name'              => 'adminease[performance][wp_memory_limit_other]',
					'value'             => $this->settings['wp_memory_limit_other'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'label'             => __( 'Custom memory limit', 'adminease' ),
					'description'       => __( 'Enter the memory limit in megabytes (MB).', 'adminease' ),
					'field_description' => __( 'Set the memory limit for WordPress in megabytes (MB).', 'adminease' ),
					'attributes'        => [
						'min'         => 40,
						'data-parent' => 'wp-memory-limit',
						'step'        => 1,
						'pattern'     => '[0-9]+',
						'inputmode'   => 'numeric',
					],
				],
			],
		];
		
		$fields['performance']['fields'][] = [
			'type'              => 'select',
			'id'                => 'wp-max-memory-limit',
			'name'              => 'adminease[performance][wp_max_memory_limit]',
			'value'             => $this->settings['wp_max_memory_limit'] ?? '',
			'options'           => [
				''      => __( 'Select', 'adminease' ),
				'40M'   => '40M',
				'64M'   => '64M',
				'128M'  => '128M',
				'256M'  => '256M',
				'512M'  => '512M',
				'1G'    => '1G',
				'2G'    => '2G',
				'4G'    => '4G',
				'8G'    => '8G',
				'16G'   => '16G',
				'32G'   => '32G',
				'other' => __( 'Other', 'adminease' ),
			],
			'label_class'       => 'adminease-label',
			'input_class'       => 'form-control adminease-choices toggle-field',
			'label'             => __( 'WP Max Memory Limit', 'adminease' ),
			'description'       => __( 'The <strong>WP Max Memory Limit</strong> is the highest amount of memory WordPress can use when performing <strong>administrative tasks</strong>, such as updates, backups, or running large imports. It’s usually set higher than the regular memory limit to ensure smooth operation in the admin area. If this limit is too low, some tasks may fail or time out.', 'adminease' ),
			'field_description' => __( 'WordPress defaults to a maximum memory limit of 40MB for a single site and 64MB for a WordPress Multisite network.', 'adminease' ),
			'placeholder'       => __( 'Select', 'adminease' ),
			'attributes'        => [
				'data-allow_clear' => true,
			],
			'child_fields'      => [
				[
					'type'              => 'number',
					'id'                => 'wp-max-memory-limit-other',
					'name'              => 'adminease[performance][wp_max_memory_limit_other]',
					'value'             => $this->settings['wp_max_memory_limit_other'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'label'             => __( 'Custom memory limit', 'adminease' ),
					'description'       => __( 'Enter the memory limit in megabytes (MB).', 'adminease' ),
					'field_description' => __( 'Set the memory limit for WordPress in megabytes (MB).', 'adminease' ),
					'attributes'        => [
						'min'         => 40,
						'data-parent' => 'wp-max-memory-limit',
						'step'        => 1,
						'pattern'     => '[0-9]+',
						'inputmode'   => 'numeric',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Updates WordPress configuration constants based on the sanitized settings provided.
	 *
	 * @param array $sanitized_settings The sanitized settings array containing configuration values.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$file_handler = Plugin::$FileHandler;
		
		$file_handler->stack_wp_config_constant( 'WP_MEMORY_LIMIT', $sanitized_settings['performance']['wp_memory_limit'] );
		$file_handler->stack_wp_config_constant( 'WP_MAX_MEMORY_LIMIT', $sanitized_settings['performance']['wp_max_memory_limit'] );
	}
}