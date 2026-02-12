<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisablePingbacksAndTrackbacks
 * Provides functionality to disable WordPress pingbacks and trackbacks
 * by modifying settings, disabling related XML-RPC methods, and filtering
 * headers and links. This class helps enhance the security of a WordPress
 * website by preventing the abuse of pingback features.
 */
class DisablePingbacksAndTrackbacks {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['disable_pingbacks'] ) ) {
			add_action( 'init', [ $this, 'disable_pingbacks' ] );
			add_filter( 'xmlrpc_methods', [ $this, 'disable_xmlrpc_pingback' ] );
			add_filter( 'wp_headers', [ $this, 'remove_pingback_header' ] );
			add_action( 'pre_ping', [ $this, 'disable_internal_pingbacks' ] );
		}
	}
	
	/**
	 * Modifies and extends the settings fields array with additional security options.
	 *
	 * @param array $fields An array of existing settings fields. This array will be extended
	 *                      to include new fields for administrating security-related options.
	 *                      The new field added provides an option to disable pingbacks entirely.
	 *
	 * @return array Updated settings fields array including the new field for disabling pingbacks.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-pingbacks',
			'name'        => 'adminease[security][disable_pingbacks]',
			'value'       => $this->settings['disable_pingbacks'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable pingbacks', 'adminease' ),
			'description' => __( 'Pingbacks allow other WordPress websites to automatically leave comments under your posts when these websites link to these posts. Pingbacks can be abused to use your website for DDoS attacks on other sites. This security option turns off XML-RPC pingbacks for your whole website and also disables pingbacks for previously created posts with pingbacks enabled.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Disables pingbacks by updating the relevant WordPress options.
	 * @return void
	 */
	public function disable_pingbacks(): void {
		// Disable pingback flag
		update_option( 'default_ping_status', 'closed' );
		update_option( 'default_pingback_flag', '0' );
	}
	
	/**
	 * Disables XML-RPC pingback methods to improve security by preventing
	 * potential abuse of the pingback feature in XML-RPC.
	 *
	 * @param array $methods An array of existing XML-RPC methods.
	 *
	 * @return array Modified array of XML-RPC methods with pingback methods removed.
	 */
	public function disable_xmlrpc_pingback( array $methods ): array {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		
		return $methods;
	}
	
	/**
	 * Removes the X-Pingback header from the provided headers array.
	 *
	 * @param array $headers An associative array of HTTP headers.
	 *
	 * @return array The modified headers array with the X-Pingback header removed.
	 */
	public function remove_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );
		
		return $headers;
	}
	
	/**
	 * Removes internal pingbacks from a list of links.
	 *
	 * @param array &$links The array of links to be processed.
	 *                      Each link is checked, and if it starts with the site's home URL,
	 *                      it will be removed from the array.
	 *
	 * @return void This method does not return a value. It modifies the input $links array directly.
	 */
	public function disable_internal_pingbacks( array &$links ): void {
		foreach( $links as $l => $link ) {
			if( 0 === strpos( $link, get_option( 'home' ) ) ) {
				unset( $links[ $l ] );
			}
		}
	}
}