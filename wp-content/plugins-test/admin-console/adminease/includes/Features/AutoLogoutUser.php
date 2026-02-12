<?php
namespace AdminEase\Features;

use AdminEase\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles automatic user logout functionality by customizing the authentication
 * cookie expiration time based on specified roles and timeout settings.
 */
class AutoLogoutUser {
	private array $settings;
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'users' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !empty( $this->settings['auto_logout_user'] ) ) {
			add_filter( 'auth_cookie_expiration', [ $this, 'auth_cookie_expiration' ], 10, 3 );
		}
	}
	
	/**
	 * Adds or modifies the settings fields for the AdminEase settings page.
	 *
	 * @param array $fields The existing settings fields structured by categories, with each category containing field definitions.
	 *
	 * @return array The modified settings fields with added or updated configurations for user auto-logout options.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['users']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'auto-logout-user',
			'name'         => 'adminease[users][auto_logout_user]',
			'value'        => $this->settings['auto_logout_user'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Auto-logout user', 'adminease' ),
			'description'  => __( "The <strong>Login Session Timeout</strong> setting controls how long a user stays logged in before WordPress automatically logs them out. For example, if it’s set to 30 minutes, a user will need to log in again after that time — even if they’re still active. This helps improve your site's security by limiting how long login sessions remain open. You can choose a shorter timeout for better protection or a longer one for convenience.", 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'select',
					'id'                => 'auto-logout-user-time',
					'name'              => 'adminease[users][auto_logout_user_time]',
					'value'             => $this->settings['auto_logout_user_time'] ?? '',
					'options'           => [
						''        => __( 'Select', 'adminease' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'60'      => sprintf( __( '%s minute', 'adminease' ), '1' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'300'     => sprintf( __( '%s minutes', 'adminease' ), '5' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'600'     => sprintf( __( '%s minutes', 'adminease' ), '10' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'900'     => sprintf( __( '%s minutes', 'adminease' ), '15' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'1200'    => sprintf( __( '%s minutes', 'adminease' ), '20' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'1800'    => sprintf( __( '%s minutes', 'adminease' ), '30' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'2700'    => sprintf( __( '%s minutes', 'adminease' ), '45' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'3600'    => sprintf( __( '%s hour', 'adminease' ), '1' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'7200'    => sprintf( __( '%s hours', 'adminease' ), '2' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'14400'   => sprintf( __( '%s hours', 'adminease' ), '4' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'28800'   => sprintf( __( '%s hours', 'adminease' ), '8' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'43200'   => sprintf( __( '%s hours', 'adminease' ), '12' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'86400'   => sprintf( __( '%s day', 'adminease' ), '1' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'604800'  => sprintf( __( '%s week', 'adminease' ), '1' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'2592000' => sprintf( __( '%s month', 'adminease' ), '1' ),
						/* translators: %d: number of seconds for the auto-logout interval */
						'other'   => __( 'Other', 'adminease' ),
					],
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control toggle-field',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Auto-logout user after X seconds', 'adminease' ),
					'field_description' => __( 'Select the login session limit.', 'adminease' ),
					'attributes'        => [
						'data-parent' => 'auto-logout-user',
					],
				],
				[
					'type'              => 'number',
					'id'                => 'auto-logout-user-time-other',
					'name'              => 'adminease[users][auto_logout_user_time_other]',
					'value'             => $this->settings['auto_logout_user_time_other'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Custom time', 'adminease' ),
					'field_description' => __( 'Set login session limit in seconds.', 'adminease' ),
					'attributes'        => [
						'min'         => 0,
						'data-parent' => 'auto-logout-user-time',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Filters the expiration time of an authentication cookie based on user roles and the "remember me" setting.
	 *
	 * @param int  $expiration The default expiration time of the authentication cookie in seconds.
	 * @param int  $user_id The ID of the user for whom the cookie expiration is being modified.
	 * @param bool $remember Whether the "remember me" checkbox was checked when the user logged in.
	 *
	 * @return int The modified or original expiration time of the authentication cookie in seconds.
	 */
	public function auth_cookie_expiration( int $expiration, int $user_id, bool $remember ): int {
		$timeout = $this->settings['auto_logout_user_time'] ?? '';
		
		if( 'other' === $timeout ) {
			$timeout = $this->settings['auto_logout_user_time_other'] ?? '';
		}
		
		if( empty( $timeout ) ) {
			return $expiration;
		}
		
		return apply_filters( 'adminease_auth_cookie_expiration', (int) $timeout, $expiration, $user_id );
	}
}