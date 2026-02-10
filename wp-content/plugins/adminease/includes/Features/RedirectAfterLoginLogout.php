<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Handles redirect functionality after user login and logout.
 * Allows customization of redirect URLs for both login and logout actions.
 */
class RedirectAfterLoginLogout {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'users' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['redirect_after_login'] ) ) {
			add_action( 'wp_login', [ $this, 'handle_login_redirect' ], 999, 2 );
		}
		
		if( !empty( $this->settings['redirect_after_logout'] ) ) {
			add_action( 'wp_logout', [ $this, 'handle_logout_redirect' ], 1 );
		}
	}
	
	/**
	 * Adds settings fields for login/logout redirect configuration.
	 *
	 * @param array $fields The existing settings fields structured by categories.
	 *
	 * @return array The modified settings fields with added redirect configurations.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['users']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'redirect-after-login',
			'name'         => 'adminease[users][redirect_after_login]',
			'value'        => $this->settings['redirect_after_login'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Redirect after login', 'adminease' ),
			'description'  => __( 'Control where users are redirected after successfully logging in. By default, WordPress redirects users to the admin dashboard, but you can customize this to send them to any page on your site, such as their profile, a welcome page, or the homepage.', 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'text',
					'id'                => 'redirect-after-login-url',
					'name'              => 'adminease[users][redirect_after_login_url]',
					'value'             => $this->settings['redirect_after_login_url'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Login redirect URL', 'adminease' ),
					'field_description' => __( 'Enter the full URL where users should be redirected after login. Leave empty to use WordPress default.', 'adminease' ),
					'attributes'        => [
						'placeholder' => home_url(),
						'data-parent' => 'redirect-after-login',
					],
				],
			],
		];
		
		$fields['users']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'redirect-after-logout',
			'name'         => 'adminease[users][redirect_after_logout]',
			'value'        => $this->settings['redirect_after_logout'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Redirect after logout', 'adminease' ),
			'description'  => __( 'Control where users are redirected after logging out. By default, WordPress redirects users to the login page, but you can customize this to send them to any page, such as the homepage or a custom goodbye page.', 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'text',
					'id'                => 'redirect-after-logout-url',
					'name'              => 'adminease[users][redirect_after_logout_url]',
					'value'             => $this->settings['redirect_after_logout_url'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Logout redirect URL', 'adminease' ),
					'field_description' => __( 'Enter the full URL where users should be redirected after logout. Leave empty to use WordPress default.', 'adminease' ),
					'attributes'        => [
						'placeholder' => home_url(),
						'data-parent' => 'redirect-after-logout',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Handle redirect after login.
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user WP_User object.
	 */
	public function handle_login_redirect( string $user_login, WP_User $user ) {
		$custom_redirect = $this->settings['redirect_after_login_url'] ?? '';
		
		if( !empty( $custom_redirect ) ) {
			wp_redirect( esc_url_raw( $custom_redirect ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}
	
	/**
	 * Handle redirect after logout.
	 */
	public function handle_logout_redirect() {
		$custom_redirect = $this->settings['redirect_after_logout_url'] ?? '';
		
		if( !empty( $custom_redirect ) ) {
			wp_redirect( esc_url_raw( $custom_redirect ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}
}