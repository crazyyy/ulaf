<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the logic to block access to sensitive files for improved security.
 * - Hooks into the admin settings save action to manage `.htaccess` file changes.
 * - Dynamically generates `.htaccess` rules based on configured settings to block sensitive files.
 */
class BlockAccessSensitiveFiles {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and adds specific security-related fields to the "Adminease" plugin settings.
	 *
	 * @param array $fields An array of existing settings fields to which the new security field will be added.
	 *
	 * @return array The updated settings fields array including the security configuration field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'block-access-sensitive-files',
			'name'        => 'adminease[security][block_access_sensitive_files]',
			'value'       => $this->settings['block_access_sensitive_files'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Block access to sensitive files', 'adminease' ),
			'description' => __( 'Block access to configuration files, development files, backups, logs, and version control files', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Handles the settings saved for the "Adminease" plugin and updates the .htaccess rules based on security settings.
	 *
	 * @param array $sanitized_settings An array of settings containing security configurations.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		$code = '';
		
		if( !empty( $sanitized_settings['security']['block_access_sensitive_files'] ) ) {
			$code = "# .ht* files (htaccess, htpasswd, etc.)\n";
			
			$code .= "<Files ~ \"^\\.ht\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Environment files (any file ending with .env)
			$code .= "# Environment files\n";
			$code .= "<Files ~ \"\\.env$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Specific config files
			$code .= "# Config files\n";
			$code .= "<Files \"wp-config.php\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			$code .= "<Files \"config.php\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			$code .= "<Files \"configuration.php\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Development files
			$code .= "# Development files\n";
			$code .= "<Files ~ \"^composer\\.(json|lock)$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			$code .= "<Files ~ \"^package(-lock)?\\.json$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			$code .= "<Files \"yarn.lock\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Git files
			$code .= "# Git files\n";
			$code .= "<Files ~ \"^\\.git\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Documentation files
			$code .= "# Documentation files\n";
			$code .= "<Files ~ \"^(README|readme|CHANGELOG|changelog)\\.(md|txt)$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Backup and temp files
			$code .= "# Backup and temporary files\n";
			$code .= "<Files ~ \"\\.(ini|bak|backup|old|orig|tmp|log|sql)$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Files ending with ~ (temporary files)
			$code .= "<Files ~ \"~$\">\n";
			$code .= "\tRequire all denied\n";
			$code .= "</Files>\n\n";
			
			// Version control directories
			$code .= "# Version control directories\n";
			$code .= "<IfModule mod_rewrite.c>\n";
			$code .= "\tRewriteEngine On\n";
			$code .= "\tRewriteRule ^\\.git - [F,L]\n";
			$code .= "\tRewriteRule ^\\.svn - [F,L]\n";
			$code .= "\tRewriteRule ^\\.hg - [F,L]\n";
			$code .= "</IfModule>\n\n";
		}
		
		Plugin::$FileHandler->stack_htaccess_rule( 'BLOCK_ACCESS_SENSITIVE_FILES', $code );
	}
}