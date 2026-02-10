<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles blocking of author scans for improved security.
 */
class BlockDirectoryBrowsing {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Modifies and returns the settings fields for admin security features.
	 *
	 * @param array $fields The existing configuration fields to be updated with additional options.
	 *
	 * @return array The updated configuration fields, including the option to block directory browsing.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'block-directory-browsing',
			'name'        => 'adminease[security][block_directory_browsing]',
			'value'       => $this->settings['block_directory_browsing'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Block directory browsing', 'adminease' ),
			'description' => __( 'If directory browsing is turned on, hackers can obtain various information about your website that can potentially compromise its security. Directory browsing is usually turned off by default, but if it is turned on, this security option can block it.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Saves and applies security settings related to directory browsing.
	 *
	 * @param array $sanitized_settings The configuration settings, including the 'security' options.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		if( !empty( $sanitized_settings['security']['block_directory_browsing'] ) ) {
			$code = "Options -Indexes";
		} else {
			$code = '';
		}
		
		Plugin::$FileHandler->stack_htaccess_rule( 'BLOCK_DIRECTORY_BROWSING', $code );
	}
}