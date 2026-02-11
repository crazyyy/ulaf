<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableAdminNotices
 * Suppresses global admin notices for a cleaner dashboard experience.
 */
class DisableAdminNotices {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'updates-and-notifications' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'add_settings_fields' ] );
		
		if( empty( $this->settings['disable_admin_notices_enabled'] ) ) {
			return;
		}
		
		add_action( 'admin_print_scripts', [ $this, 'admin_print_scripts' ] );
	}
	
	/**
	 * Adds settings fields to the Updates and Notifications tab.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_settings_fields( array $fields ): array {
		$fields['updates-and-notifications']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'disable-admin-notices-enabled',
			'name'              => 'adminease[updates-and-notifications][disable_admin_notices_enabled]',
			'value'             => $this->settings['disable_admin_notices_enabled'] ?? false,
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control',
			'label'             => __( 'Disable admin notices', 'adminease' ),
			'description'       => __( 'Suppresses global admin notices from WordPress core, themes, and plugins to provide a distraction-free administration area.', 'adminease' ),
			'field_description' => __( 'Enable to hide all global admin notices.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Handles the suppression of admin notices based on a filter.
	 * Allows control over whether admin notices are displayed on specific screens
	 * or for specific user roles through the 'adminease_suppress_admin_notices' filter.
	 * @return void
	 */
	public function admin_print_scripts(): void {
		/**
		 * Filters whether admin notices should be suppressed.
		 * Pro version can use this to allow notices on specific screens
		 * or for specific user capabilities.
		 *
		 * @param bool $suppress True to hide notices.
		 */
		$suppress = apply_filters( 'adminease_suppress_admin_notices', true );
		
		if( $suppress ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
		}
	}
}