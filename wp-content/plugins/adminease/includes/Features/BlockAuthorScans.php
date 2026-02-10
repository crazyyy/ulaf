<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles blocking of author scans for improved security.
 */
class BlockAuthorScans {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds a security field to the settings array for blocking author enumeration attempts.
	 *
	 * @param array $fields The fields array containing existing settings configurations.
	 *
	 * @return array The updated fields array including the 'block_author_scans' security option.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'block-author-scans',
			'name'        => 'adminease[security][block_author_scans]',
			'value'       => $this->settings['block_author_scans'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Block author scans', 'adminease' ),
			'description' => __( 'Author scans are looking to find usernames of registered users (especially WordPress admin) and brute-force attack the login page of your website to gain access. This security option prevents such scans from exposing these usernames. Note: depending on the permalink configuration on your website this option might prevent visitors from accessing pages that list all articles written by a particular author.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Updates .htaccess rules based on security settings to block author enumeration attempts.
	 *
	 * @param array $sanitized_settings The settings array containing security configurations, including the 'block_author_scans' option.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		if( !empty( $sanitized_settings['security']['block_author_scans'] ) ) {
			$code = "<IfModule mod_rewrite.c>\n";
			$code .= "\tRewriteEngine On\n";
			$code .= "\tRewriteBase /\n\n";
			$code .= "\t# Block author enumeration attempts\n";
			$code .= "\tRewriteCond %{REQUEST_URI} !^/wp-admin/ [NC]\n";
			$code .= "\tRewriteCond %{QUERY_STRING} (author=\d+) [NC,OR]\n";
			$code .= "\tRewriteCond %{REQUEST_URI} ^.*wp-json/wp/v2/(users) [NC]\n";
			$code .= "\tRewriteRule .* - [F]\n";
			$code .= "</IfModule>";
		} else {
			$code = '';
		}
		
		Plugin::$FileHandler->stack_htaccess_rule( 'BLOCK_AUTHOR_SCANS', $code );
	}
}