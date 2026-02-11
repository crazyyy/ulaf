<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use AdminEase\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * The HideAdminBar class provides functionality to hide the WordPress admin bar
 * for specific user roles, helping create a cleaner frontend experience.
 */
class HideAdminBar {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'users' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( empty( $this->settings['hide_admin_bar'] ) ) {
			return;
		}
		
		// Hook early to hide admin bar before it's rendered
		add_action( 'init', [ $this, 'hide_admin_bar' ] );
		
		// Additional hooks to ensure admin bar is completely hidden
		add_filter( 'show_admin_bar', [ $this, 'filter_show_admin_bar' ] );
		
		// Remove admin bar related CSS and scripts
		add_action( 'wp_head', [ $this, 'remove_admin_bar_styles' ], 99 );
		
		// Remove admin bar margin from body
		add_action( 'wp_head', [ $this, 'remove_admin_bar_margin' ], 99 );
	}
	
	/**
	 * Adds specific settings fields to the provided settings array.
	 * This method integrates additional fields, such as options to hide the admin bar
	 * for specific user roles, into the security section of the plugin's settings.
	 * The fields include options for toggling the feature and selecting user roles.
	 *
	 * @param array $fields The existing settings fields array to which new fields will be added.
	 *
	 * @return array The modified settings fields array containing the new field configurations.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['users']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'hide-admin-bar',
			'name'         => 'adminease[users][hide_admin_bar]',
			'value'        => $this->settings ['hide_admin_bar'] ?? false,
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Hide Admin Bar', 'adminease' ),
			'description'  => __( 'Hide the WordPress admin bar for specific user roles. This helps create a cleaner frontend experience for non-administrative users while maintaining admin functionality where needed.', 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'select',
					'id'                => 'hide-admin-bar-user-roles',
					'name'              => 'adminease[users][hide_admin_bar_user_roles][]',
					'value'             => $this->settings ['hide_admin_bar_user_roles'] ?? [],
					'options'           => Utils::get_user_roles_options(),
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control adminease-choices',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'User roles to hide admin bar for', 'adminease' ),
					'field_description' => __( 'Select which user roles should have the admin bar hidden. Leave empty to hide for all non-admin users.', 'adminease' ),
					'attributes'        => [
						'multiple'              => 'multiple',
						'data-allow_clear'      => true,
						'data-allow_select_all' => true,
						'data-parent'           => 'hide-admin-bar',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Hides the WordPress admin bar for the current user.
	 * This method disables the admin bar for the current user by calling
	 * the appropriate WordPress functions. Additionally, it removes any
	 * related hooks to ensure the admin bar is not displayed or adds unnecessary
	 * styles or markup to the page.
	 * @return void
	 */
	public function hide_admin_bar(): void {
		if( !$this->should_hide_admin_bar() ) {
			return;
		}
		
		// Remove admin bar for the current user
		show_admin_bar( false );
		
		// Remove admin bar hooks
		remove_action( 'wp_head', '_admin_bar_bump_cb' );
	}
	
	/**
	 * Filters the visibility of the admin bar.
	 *
	 * @param bool $show_admin_bar The current state of admin bar visibility.
	 *
	 * @return bool Returns false if the admin bar should be hidden, otherwise returns the original state.
	 */
	public function filter_show_admin_bar( bool $show_admin_bar ): bool {
		if( $this->should_hide_admin_bar() ) {
			return false;
		}
		
		return $show_admin_bar;
	}
	
	/**
	 * Removes the admin bar related styles and scripts.
	 * This method ensures that the admin bar's associated CSS and JavaScript files
	 * are dequeued and deregistered to prevent them from being loaded on the front-end.
	 * @return void
	 */
	public function remove_admin_bar_styles(): void {
		if( !$this->should_hide_admin_bar() ) {
			return;
		}
		
		// Remove admin bar CSS
		wp_dequeue_style( 'admin-bar' );
		wp_deregister_style( 'admin-bar' );
		
		// Remove admin bar related scripts
		wp_dequeue_script( 'admin-bar' );
		wp_deregister_script( 'admin-bar' );
	}
	
	/**
	 * Outputs inline CSS to remove the default admin bar margin at the top of the page.
	 * This method checks whether the admin bar should be hidden and, if so, generates
	 * CSS rules to eliminate the space introduced by the admin bar.
	 * @return void
	 */
	public function remove_admin_bar_margin(): void {
		if( !$this->should_hide_admin_bar() ) {
			return;
		}
		
		// Output CSS to remove the admin bar margin
		echo '<style type="text/css">
            html { margin-top: 0 !important; }
            * html body { margin-top: 0 !important; }
            @media screen and ( max-width: 782px ) {
                html { margin-top: 0 !important; }
                * html body { margin-top: 0 !important; }
            }
        </style>';
	}
	
	/**
	 * Determines whether the admin bar should be hidden for the current user.
	 * This method checks various conditions including whether the user is
	 * on an admin page, their capabilities, and specified roles from the
	 * plugin's settings to decide if the admin bar should be displayed.
	 * @return bool True if the admin bar should be hidden, false otherwise.
	 */
	private function should_hide_admin_bar(): bool {
		// Don't hide on admin pages
		if( is_admin() ) {
			return false;
		}
		
		// Don't hide for users who can't access the admin
		if( !current_user_can( 'read' ) ) {
			return false;
		}
		
		$hide_for_roles = $this->settings['hide_admin_bar_user_roles'] ?? [];
		
		// If no specific roles are set, hide for all non-admin users
		if( empty( $hide_for_roles ) ) {
			return !current_user_can( 'manage_options' );
		}
		
		// Check if the current user has any of the specified roles
		$current_user = wp_get_current_user();
		
		if( empty( $current_user->roles ) ) {
			return false;
		}
		
		// Check if any of the user's roles are in the hide list
		foreach( $current_user->roles as $role ) {
			if( in_array( $role, $hide_for_roles, true ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determines if a given user role is hidden based on the settings.
	 *
	 * @param string $role The user role to check.
	 *
	 * @return bool True if the role is hidden, false otherwise.
	 */
	public function is_role_hidden( string $role ): bool {
		$hide_for_roles = $this->settings['hide_admin_bar_user_roles'] ?? [];
		
		return in_array( $role, $hide_for_roles, true );
	}
}