<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class HideWordpressVersion
 * This class provides functionality to hide the WordPress version from being exposed
 * in various parts of a WordPress site, such as RSS feeds and asset URLs. By filtering
 * and modifying the output in these areas, it enhances the security of the site by
 * preventing the disclosure of the WordPress version.
 * The constructor activates the necessary filters based on user-defined settings to ensure
 * that the WordPress version is removed from the RSS feeds and asset URLs if this feature
 * is enabled in the security settings.
 */
class HideWordpressVersion {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['hide_wordpress_version'] ) ) {
			// Remove WordPress version from RSS feeds
			add_filter( 'the_generator', '__return_empty_string' );
			
			// Remove only WordPress version from asset URLs
			add_filter( 'style_loader_src', [ $this, 'filter_asset_version' ], 9999 );
			add_filter( 'script_loader_src', [ $this, 'filter_asset_version' ], 9999 );
		}
	}
	
	/**
	 * Modifies and returns the array of settings fields for the AdminEase plugin by adding a field to hide the WordPress version.
	 *
	 * @param array $fields The existing settings fields.
	 *
	 * @return array The modified settings fields, including the hide WordPress version field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'hide-wordpress-version',
			'name'        => 'adminease[security][hide_wordpress_version]',
			'value'       => $this->settings['hide_wordpress_version'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Hide WordPress version', 'adminease' ),
			'description' => __( "By default, WordPress includes its <strong>version number</strong> in your site's source code, RSS feeds, and sometimes in script URLs. While this seems harmless, it can give attackers clues about potential vulnerabilities if you're running an outdated version. Hiding the WordPress version helps <strong>improve your site's security</strong> by making it less obvious which version you're using. This can be done with a simple code snippet added to your theme or a security plugin.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Filters the asset URL to remove the WordPress version query parameter.
	 * This helps to hide the WordPress version from being exposed in asset URLs.
	 *
	 * @param string $src The source URL of the asset.
	 *
	 * @return string The filtered asset URL with the version parameter removed, if applicable.
	 */
	public function filter_asset_version( string $src ): string {
		$wp_version = get_bloginfo( 'version' );
		
		if( false !== strpos( $src, 'ver=' . $wp_version ) ) {
			return remove_query_arg( 'ver', $src );
		}
		
		return $src;
	}
}