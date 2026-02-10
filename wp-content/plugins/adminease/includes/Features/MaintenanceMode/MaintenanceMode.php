<?php
namespace AdminEase\Features\MaintenanceMode;

use AdminEase\Features\MaintenanceMode\Themes\Classic;
use AdminEase\Plugin;

if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maintenance Mode Feature
 * Provides functionality to enable a maintenance mode for the website. Visitors will be presented
 * with a maintenance page while administrators retain access to the site.
 * This class integrates with the AdminEase plugin to allow configuration of maintenance mode settings.
 */
class MaintenanceMode {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'debug' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( empty( $this->settings['maintenance_mode_enabled'] ) ) {
			return;
		}
		
		add_action( 'template_redirect', [ $this, 'template_redirect' ], 0 );
	}
	
	/**
	 * Configures and adds settings fields related to maintenance mode in the AdminEase plugin.
	 *
	 * @param array $fields The existing settings fields to which new maintenance mode fields will be appended.
	 *
	 * @return array The modified settings fields with maintenance mode options included.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$blog_name = get_bloginfo( 'name' );
		
		$fields['debug']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'maintenance-mode-enabled',
			'name'         => 'adminease[debug][maintenance_mode_enabled]',
			'value'        => $this->settings['maintenance_mode_enabled'] ?? false,
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Enable Maintenance Mode', 'adminease' ),
			'description'  => __( 'Display a professional maintenance page to visitors during site updates or scheduled maintenance while allowing administrators to access the site normally. Customize the page with your logo, headlines, and HTML-formatted messages. Includes SEO-friendly 503 headers and Retry-After instructions for search engines.', 'adminease' ),
			'child_fields' => [
				[
					'type'          => 'text',
					'id'            => 'maintenance_mode_page_title',
					'name'          => 'adminease[debug][maintenance_mode_page_title]',
					'value'         => $this->settings['maintenance_mode_page_title'] ?? $blog_name . ' - ' . __( 'Maintenance Mode', 'adminease' ),
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Page Title', 'adminease' ),
					'description'   => __( 'The title that appears in the browser tab.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'          => 'text',
					'id'            => 'maintenance_mode_headline',
					'name'          => 'adminease[debug][maintenance_mode_headline]',
					'value'         => $this->settings['maintenance_mode_headline'] ?? $blog_name,
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Headline', 'adminease' ),
					'description'   => __( 'The main heading displayed on the maintenance page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'              => 'textarea',
					'id'                => 'maintenance_mode_message',
					'name'              => 'adminease[debug][maintenance_mode_message]',
					'value'             => $this->settings['maintenance_mode_message'] ?? __( 'We are currently performing scheduled maintenance. Please check back soon!', 'adminease' ),
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Maintenance Message', 'adminease' ),
					'description'       => __( 'Message to display to visitors during maintenance.', 'adminease' ),
					'field_description' => __( 'You can use basic HTML tags.', 'adminease' ),
					'attributes'        => [
						'rows'        => 4,
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'maintenance_mode_show_logo',
					'name'          => 'adminease[debug][maintenance_mode_show_logo]',
					'value'         => $this->settings['maintenance_mode_show_logo'] ?? true,
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Show Site Logo', 'adminease' ),
					'description'   => __( 'Display the site logo from Customizer settings.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'maintenance_mode_primary_color',
					'name'          => 'adminease[debug][maintenance_mode_primary_color]',
					'value'         => $this->settings['maintenance_mode_primary_color'] ?? '#0073aa',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Primary Color', 'adminease' ),
					'description'   => __( 'Select the primary color for the maintenance mode page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'maintenance_mode_secondary_color',
					'name'          => 'adminease[debug][maintenance_mode_secondary_color]',
					'value'         => $this->settings['maintenance_mode_secondary_color'] ?? '#23282d',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Secondary Color', 'adminease' ),
					'description'   => __( 'Select the secondary color for the maintenance mode page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'maintenance_mode_text_color',
					'name'          => 'adminease[debug][maintenance_mode_text_color]',
					'value'         => $this->settings['maintenance_mode_text_color'] ?? '#333333',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Text Color', 'adminease' ),
					'description'   => __( 'Select the text color for the maintenance mode page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
				[
					'type'              => 'number',
					'id'                => 'maintenance_mode_retry_after',
					'name'              => 'adminease[debug][maintenance_mode_retry_after]',
					'value'             => $this->settings['maintenance_mode_retry_after'] ?? 3600,
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'SEO Retry-After (seconds)', 'adminease' ),
					'field_description' => __( "Tells search engines when to check back (SEO).<br>Default: 3600 (1 hour). Use 0 to disable.", 'adminease' ),
					'attributes'        => [
						'min'         => 0,
						'step'        => 60,
						'data-parent' => 'maintenance-mode-enabled',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Handles the template redirect during maintenance mode.
	 * Ensures proper output headers, disables caching, and displays the maintenance mode page.
	 * Prevents execution in the admin area or during AJAX requests and respects user access filters.
	 * Allows bypassing maintenance mode for specific roles or via custom filters.
	 * Defines constants to signal caching and minification plugins to bypass processing.
	 * @return void
	 */
	public function template_redirect() {
		// Skip if in the admin area, admin-ajax or user is logged in
		if( is_admin() || wp_doing_ajax() || is_user_logged_in() ) {
			return;
		}
		
		// Allow filtering to bypass maintenance mode
		if( apply_filters( 'adminease_maintenance_mode_check_access', false ) ) {
			return;
		}
		
		// Check if user has access (administrators can bypass)
		if( current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		// Tell common WordPress cache plugins / hosts not to cache this page
		if( !defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard caching constant used by W3 Total Cache and other caching plugins
		if( !defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard caching constant used by Autoptimize and other minification plugins
		if( !defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		// Set maintenance mode HTTP status headers
		header( 'HTTP/1.1 503 Service Temporarily Unavailable' );
		header( 'Status: 503 Service Temporarily Unavailable' );
		
		// Set Retry-After header if configured
		$retry_after = isset( $this->settings['maintenance_mode_retry_after'] ) ? absint( $this->settings['maintenance_mode_retry_after'] ) : 3600;
		
		if( $retry_after > 0 ) {
			header( 'Retry-After: ' . $retry_after );
		}
		
		// Set cache control headers
		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		
		// Show maintenance mode page
		$this->show_maintenance_page();
		exit;
	}
	
	/**
	 * Display the maintenance mode page
	 * @return void
	 */
	private function show_maintenance_page(): void {
		$theme          = 'classic';
		$show_logo      = $this->settings['maintenance_mode_show_logo'] ?? true;
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		
		$theme_args = [
			'page_title'      => $this->settings['maintenance_mode_page_title'] ?? get_bloginfo( 'name' ) . ' - ' . __( 'Maintenance Mode', 'adminease' ),
			'headline'        => $this->settings['maintenance_mode_headline'] ?? get_bloginfo( 'name' ),
			'logo_url'        => $show_logo ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '',
			'message'         => $this->settings['maintenance_mode_message'] ?? '',
			'primary_color'   => $this->settings['maintenance_mode_primary_color'] ?? '#7d50f9',
			'secondary_color' => $this->settings['maintenance_mode_secondary_color'] ?? '#845af9',
			'text_color'      => $this->settings['maintenance_mode_text_color'] ?? '#333333',
		];
		
		switch( $theme ) {
			case 'classic':
			default:
				$html = Classic::render( $theme_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
		}
		
		/**
		 * Filter the maintenance mode HTML
		 *
		 * @param string $html The complete HTML output
		 * @param array  $settings Current settings
		 */
		$html = apply_filters( 'adminease_maintenance_mode_html', $html, $this->settings );
		
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}