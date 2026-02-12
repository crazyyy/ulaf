<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the logic to disable XML-RPC functionality in WordPress for improved security.
 * - Hooks into the admin settings save action to manage `.htaccess` file changes for XML-RPC.
 * - Dynamically generates `.htaccess` rules based on configured settings to block specific countries.
 */
class DisableXmlRpc {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ] );
	}
	
	/**
	 * Adds a new settings field for disabling XML-RPC functionality to the provided fields array.
	 * The setting is included in the security section with various configuration options, allowing users
	 * to enable or disable XML-RPC based on their needs to enhance site security.
	 *
	 * @param array $fields The array of existing settings fields to which the new field will be added.
	 *
	 * @return array The modified array of settings fields including the new XML-RPC disable option.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-xmlrpc',
			'name'        => 'adminease[security][disable_xmlrpc]',
			'value'       => $this->settings['disable_xmlrpc'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control toggle-field',
			'label'       => __( 'Disable XML-RPC', 'adminease' ),
			'description' => __( "<p>XML-RPC is a feature in WordPress that allows external apps and services to connect to your site—for example, to publish posts remotely or manage content via mobile apps. However, it's also a common target for brute force and DDoS attacks. If you're not using any tools that require XML-RPC, it's a good idea to disable it to improve your site's security.</p><p>If you need XML-RPC access from specific locations, you can add their IP addresses. One IP address per line, supporting both IPv4 and IPv6 formats.</p><p><strong>Examples:</strong><br>IPv4: 192.168.1.1<br>IPv6: 2001:db8:85a3::8a2e:370:7334</p><p><strong>Important:</strong> When XML-RPC is disabled, only requests from these IPs will be allowed, while all other requests will be denied. Leave empty to block all access to xmlrpc.php.</p>", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Processes and saves the admin ease settings, specifically managing the XML-RPC access rules.
	 *
	 * @param array $sanitized_settings An associative array containing the sanitized settings.
	 *                                  Expects 'disable_xmlrpc' key to determine whether XML-RPC should be disabled.
	 *
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ): void {
		if( !empty( $sanitized_settings['security']['disable_xmlrpc'] ) ) {
			$code = "<Files xmlrpc.php>\n";
			$code .= "order deny,allow\n";
			$code .= "deny from all\n";
			$code .= "</Files>";
		} else {
			$code = '';
		}
		
		$code = apply_filters( 'adminease_disable_xmlrpc_code', $code );
		
		Plugin::$FileHandler->stack_htaccess_rule( 'DISABLE_XMLRPC', $code );
	}
}