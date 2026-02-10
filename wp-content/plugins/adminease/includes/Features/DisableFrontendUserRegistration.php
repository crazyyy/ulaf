<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class DisableFrontendUserRegistration
 * Handles the disabling of frontend user registration in WordPress. This includes disabling
 * the "Anyone can register" option, blocking access to the default WordPress registration form,
 * removing registration-related URLs from WordPress-generated links, and blocking REST API
 * requests for user registration.
 */
class DisableFrontendUserRegistration {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'users' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['disable_user_registration'] ) ) {
			add_action( 'init', [ $this, 'disable_registration_setting' ], 1 );
			add_action( 'login_init', [ $this, 'block_wp_registration_form' ] );
			add_filter( 'register_url', [ $this, 'remove_register_url' ] );
			add_action( 'rest_api_init', [ $this, 'block_rest_registration' ], 1 );
		}
	}
	
	/**
	 * Adds a custom settings field for disabling user registration to the provided settings fields array.
	 *
	 * @param array $fields The array of existing settings fields to which the new field will be added.
	 *
	 * @return array The modified array of settings fields including the new field for disabling user registration.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['users']['fields'][] = [
			'type'        => 'switch',
			'id'          => 'disable-user-registration',
			'name'        => 'adminease[users][disable_user_registration]',
			'value'       => $this->settings['disable_user_registration'] ?? '',
			'label_class' => 'adminease-switch',
			'input_class' => 'form-control',
			'label'       => __( 'Disable user registration', 'adminease' ),
			'description' => __( 'Disabling user registration in WordPress using PHP gives you stronger control over who can create accounts on your site. This approach <strong>removes access to the default WordPress registration form</strong> (<code>wp-login.php?action=register</code>) and blocks programmatic registration attempts. If any third-party plugin tries to register users via WordPress core functions, it will trigger an error message instead of creating an account. This method is ideal for preventing spam, hardening site security, and fully locking down user sign-ups at the code level.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Disables the user registration setting if the disable_registration property is true
	 * and the "users_can_register" option is currently enabled.
	 * @return void
	 */
	public function disable_registration_setting(): void {
		if( !empty( $this->settings['disable_user_registration'] ) && get_option( 'users_can_register' ) ) {
			update_option( 'users_can_register', 0 );
		}
	}
	
	/**
	 * Blocks the WordPress registration form by showing an error message
	 * if the "action" query parameter is set to "register".
	 * @return void
	 */
	public function block_wp_registration_form(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to display message, no form processing
		if( !isset( $_GET['action'] ) ) {
			return;
		}
		
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter to display message, no form processing
		$action = sanitize_key( wp_unslash( $_GET['action'] ) );
		
		if( 'register' === $action ) {
			wp_die(
				esc_html__( 'User registration is disabled on this site.', 'adminease' ),
				esc_html__( 'Registration Disabled', 'adminease' ),
				[ 'response' => 403 ]
			);
		}
	}
	
	/**
	 * Removes the registration URL by returning an empty string.
	 *
	 * @param string $url The original registration URL.
	 *
	 * @return string Always returns an empty string.
	 */
	public function remove_register_url( string $url ): string {
		return '';
	}
	
	/**
	 * Blocks the REST API endpoint for user registration by overriding the default behavior.
	 * Returns an error response when a registration request is made, indicating that user registration is disabled.
	 * @return void
	 */
	public function block_rest_registration(): void {
		register_rest_route(
			'wp/v2',
			'/users',
			[
				'methods'             => 'POST',
				'callback'            => function() {
					return new \WP_Error( 'registration_disabled', esc_html__( 'User registration is disabled.', 'adminease' ), [ 'status' => 403 ] );
				},
				'permission_callback' => '__return_false',
			]
		);
	}
}