<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableEmbeds
 * A WordPress utility class for disabling oEmbed-related functionalities across
 * the site, enhancing security and reducing unnecessary overhead. This class
 * handles the removal of oEmbed REST API endpoints, scripts, rewrite rules,
 * query variables, and TinyMCE editor plugins, as well as other embedded content-related features.
 */
class DisableEmbeds {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'security' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['disable_embeds'] ) ) {
			// Remove the REST API oEmbed endpoint.
			remove_action( 'rest_api_init', 'wp_oembed_register_route' );
			
			// Remove oEmbed-specific JavaScript from front-end and back-end.
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			
			// Remove oEmbed REST API data from the header.
			remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
			
			// Remove oEmbed-specific JavaScript from the footer.
			add_action( 'wp_footer', [ $this, 'wp_footer' ], 1 );
			
			// Remove all oEmbed-related filters.
			remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
			remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
			
			// Turn off oEmbed auto-discovery.
			add_filter( 'embed_oembed_discover', '__return_false' );
			
			// Remove all embeds rewrite rules.
			add_filter( 'rewrite_rules_array', [ $this, 'rewrite_rules_array' ] );
			
			// Remove the embed query var.
			add_filter( 'query_vars', [ $this, 'query_vars' ] );
			
			// Disable TinyMCE embed plugin.
			add_filter( 'tiny_mce_plugins', [ $this, 'tiny_mce_plugins' ] );
			
			// Remove oEmbed REST API endpoint from index.
			add_filter( 'rest_endpoints', [ $this, 'rest_endpoints' ] );
		}
	}
	
	/**
	 * Adds a settings field for disabling WordPress embeds to the provided array of fields.
	 * This field allows users to toggle the embed functionality, which automatically converts
	 * certain URLs into embedded content. Disabling embeds can enhance site performance and
	 * privacy, as well as prevent other sites from embedding content without permission.
	 *
	 * @param array $fields An array of existing settings fields.
	 *
	 * @return array The modified array of settings fields, including the new 'disable embeds' option.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['security']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-embeds',
			'name'        => 'adminease[security][disable_embeds]',
			'value'       => $this->settings['disable_embeds'] ?? false,
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable embeds', 'adminease' ),
			'description' => __( "WordPress automatically converts URLs from sites like YouTube or Twitter into embedded content, such as videos or tweets. This feature, called embeds, can make pages heavier and sometimes expose your site to unnecessary external requests. Disabling embeds helps improve performance, reduce privacy concerns, and prevent other sites from embedding your content without permission. You can disable embeds using a few lines of code or a lightweight plugin.", 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Removes the WordPress embed script from the footer.
	 * This method dequeues the 'wp-embed' script, which is
	 * responsible for handling oEmbed functionality in WordPress,
	 * in order to prevent its loading on the site.
	 * @return void
	 */
	public function wp_footer(): void {
		wp_dequeue_script( 'wp-embed' );
	}
	
	/**
	 * Filters and removes rewrite rules related to embedding functionality.
	 * This method iterates through the existing WordPress rewrite rules
	 * and removes any rules that contain the 'embed=true' query parameter,
	 * effectively disabling embed-specific rewrite rules.
	 *
	 * @param array $rules The array of rewrite rules to be filtered.
	 *
	 * @return array The filtered array of rewrite rules with embed-related rules removed.
	 */
	public function rewrite_rules_array( array $rules ): array {
		foreach( $rules as $rule => $rewrite ) {
			if( false !== strpos( $rewrite, 'embed=true' ) ) {
				unset( $rules[ $rule ] );
			}
		}
		
		return $rules;
	}
	
	/**
	 * Filters the query variables to remove the 'embed' query variable.
	 * This modification ensures that the 'embed' parameter is excluded
	 * from the recognized query variables within WordPress, potentially
	 * disabling embed functionalities.
	 *
	 * @param array $vars An array of existing query variables.
	 *
	 * @return array The filtered array of query variables.
	 */
	public function query_vars( array $vars ): array {
		return array_diff( $vars, [ 'embed' ] );
	}
	
	/**
	 * Filters the list of TinyMCE plugins to remove the embed functionality.
	 * This method removes the 'wpembed' plugin from the list of TinyMCE plugins,
	 * effectively disabling embed-related features in the TinyMCE editor.
	 *
	 * @param array $plugins The list of TinyMCE plugins currently enabled.
	 *
	 * @return array The filtered list of TinyMCE plugins with 'wpembed' removed.
	 */
	public function tiny_mce_plugins( array $plugins ): array {
		return array_diff( $plugins, [ 'wpembed' ] );
	}
	
	/**
	 * Removes specific oEmbed REST API endpoints from the list of registered endpoints.
	 * This method ensures that the '/oembed/1.0/embed' and '/oembed/1.0/proxy' endpoints
	 * are unset, disabling their availability and reducing potential security risks
	 * associated with oEmbed functionality.
	 *
	 * @param array $endpoints An associative array of registered REST API endpoints.
	 *
	 * @return array The modified array of REST API endpoints with specific oEmbed endpoints removed.
	 */
	public function rest_endpoints( array $endpoints ): array {
		unset( $endpoints['/oembed/1.0/embed'] );
		unset( $endpoints['/oembed/1.0/proxy'] );
		
		return $endpoints;
	}
}