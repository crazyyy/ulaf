<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for handling the maximum execution time settings and their application
 * within the plugin's functionality. It ensures the configuration updates are applied correctly
 * to enhance performance and stability.
 */
class MaxExecutionTime {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'performance' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
		
		add_action( 'init', [ $this, 'apply_max_execution_time' ], 1 );
	}
	
	/**
	 * Apply max execution time setting at runtime for all requests
	 * This ensures AJAX calls and all other requests respect the configured timeout
	 *
	 * @return void
	 */
	public function apply_max_execution_time(): void {
		$max_time = $this->settings['max_execution_time'] ?? null;
		
		// Only apply if a value is set and not empty
		if( !empty( $max_time ) && $max_time > 0 ) {
			ini_set( 'max_execution_time', $max_time ); // phpcs:ignore WordPress.PHP.IniSet.Risky
			set_time_limit( $max_time ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}
	
	/**
	 * Modifies and returns the admin settings fields for the plugin.
	 *
	 * @param array $fields An array containing the existing fields configuration, organized by categories such as 'security'.
	 *
	 * @return array The updated fields array with additional configuration settings, including performance-related options like 'Max Execution Time'.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['performance']['fields'][] = [
			'type'              => 'number',
			'id'                => 'max-execution-time',
			'name'              => 'adminease[performance][max_execution_time]',
			'value'             => $this->settings['max_execution_time'] ?? '',
			'label_class'       => 'adminease-label',
			'input_class'       => 'form-control',
			'label'             => __( 'Max Execution Time', 'adminease' ),
			'description'       => __( "Sets the maximum time (in seconds) a PHP script can run before timing out. This is essential for resource-intensive operations like imports, exports, and database migrations. <strong>Default is typically 30 seconds</strong>, which may be insufficient for complex tasks.<br><br><strong>⚠️ Shared Hosting Limitation:</strong> This setting configures PHP-level timeouts via <code>.user.ini</code>, <code>wp-config.php</code>, and <code>.htaccess</code>. However, <strong>many shared hosting providers enforce server-level timeout limits</strong> (commonly 120 seconds) that cannot be overridden without upgrading to VPS or dedicated hosting. If long-running operations still timeout after configuring this setting, contact your hosting provider to increase server-level timeout limits (<code>ExtAppTimeout</code>, <code>ConnectionTimeout</code> on LiteSpeed/Nginx) or consider upgrading your hosting plan.", 'adminease' ),
			'field_description' => __( 'Enter a positive integer or -1 for no limit.', 'adminease' ),
			'attributes'        => [
				'min'  => -1,
				'max'  => 3600,
				'step' => 1,
			],
		];
		
		return $fields;
	}
	
	/**
	 * Handles the saving and application of settings related to admin ease functionality.
	 *
	 * @param array $sanitized_settings An array containing configuration settings, including performance-related options such as 'max_execution_time'.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$max_time = (int) $sanitized_settings['performance']['max_execution_time'] ?? 30;
		
		// Apply to .user.ini / wp-config.php (handled by FileHandler)
		Plugin::$FileHandler->apply_php_configuration( 'max_execution_time', $max_time === 0 ? '' : $max_time );
		Plugin::$FileHandler->stack_wp_config_ini_directive( 'max_execution_time', $max_time === 0 ? '' : $max_time );
		
		// Apply FastCGI/Proxy timeout directives to .htaccess
		$this->apply_htaccess_timeout_directives( $max_time );
	}
	
	/**
	 * Apply web server timeout directives to .htaccess
	 * This handles FastCGI, PHP-FPM, and proxy timeouts that often cause 120-second limits
	 *
	 * @param int $max_time Maximum execution time in seconds
	 *
	 * @return void
	 */
	private function apply_htaccess_timeout_directives( int $max_time ): void {
		// If max_time is empty or invalid, remove the rule
		if( empty( $max_time ) || $max_time <= 0 ) {
			Plugin::$FileHandler->stack_htaccess_rule( 'MAX_EXECUTION_TIME', null, Plugin::$FileHandler::STACK_MODE_REMOVE );
			
			return;
		}
		
		// Generate timeout directives for various server configurations
		$timeout_rules = $this->generate_timeout_directives( $max_time );
		
		// Only add rules if content was generated
		if( !empty( $timeout_rules ) ) {
			Plugin::$FileHandler->stack_htaccess_rule( 'MAX_EXECUTION_TIME', $timeout_rules, Plugin::$FileHandler::STACK_MODE_REPLACE );
		}
		else {
			// Remove rule if no content generated (incompatible environment)
			Plugin::$FileHandler->stack_htaccess_rule( 'MAX_EXECUTION_TIME', null, Plugin::$FileHandler::STACK_MODE_REMOVE );
		}
	}
	
	/**
	 * Generate comprehensive timeout directives for .htaccess
	 *
	 * @param int $max_time Maximum execution time in seconds
	 *
	 * @return string Formatted .htaccess directives
	 */
	private function generate_timeout_directives( int $max_time ): string {
		// Check if running LiteSpeed
		if( !$this->is_litespeed_server() ) {
			return '';
		}
		
		$directives = [];
		
		$directives[] = '# LiteSpeed Timeout Configuration';
		$directives[] = '<IfModule Litespeed>';
		$directives[] = '    RewriteEngine On';
		$directives[] = '    RewriteRule .* - [E=noabort:1,E=noconntimeout:1]';
		$directives[] = '</IfModule>';
		$directives[] = '';
		
		$directives[] = '<IfModule mod_lsapi.c>';
		$directives[] = '    php_value max_execution_time ' . $max_time;
		$directives[] = '    php_value max_input_time ' . $max_time;
		$directives[] = '    php_value default_socket_timeout ' . $max_time;
		$directives[] = '</IfModule>';
		
		return implode( "\n", $directives );
	}
	
	/**
	 * Check if the server is running LiteSpeed
	 *
	 * @return bool True if LiteSpeed, false otherwise
	 */
	private function is_litespeed_server(): bool {
		if( !isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}
		
		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		
		return stripos( $server_software, 'litespeed' ) !== false;
	}
}