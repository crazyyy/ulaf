<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for enforcing strong password requirements for WordPress users.
 * Provides methods for setting up password validation rules and integrating them with
 * WordPress actions and filters for user registration, password reset, and profile updates.
 */
class ForceStrongPasswords {
	private array $settings;
	private int $min_length;
	private int $max_length;
	private bool $require_uppercase;
	private bool $require_lowercase;
	private bool $require_number;
	private bool $require_special;
	
	/**
	 * Initializes password-related settings and validation hooks.
	 * Sets default or configured password constraints such as length, character requirements,
	 * and attaches validation methods to appropriate WordPress actions and filters.
	 * @return void
	 */
	public function __construct() {
		$this->settings = Plugin::get_settings( 'users' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( isset( $this->settings['force_strong_passwords'] ) && true === $this->settings['force_strong_passwords'] ) {
			$this->min_length        = isset( $this->settings['password_minimum_length'] ) ? intval( $this->settings['password_minimum_length'] ) : 8;
			$this->max_length        = isset( $this->settings['password_maximum_length'] ) ? intval( $this->settings['password_maximum_length'] ) : 64;
			$this->require_uppercase = isset( $this->settings['password_minimum_uppercase_letters'] ) ? (bool) $this->settings['password_minimum_uppercase_letters'] : true;
			$this->require_lowercase = isset( $this->settings['password_minimum_lowercase_letters'] ) ? (bool) $this->settings['password_minimum_lowercase_letters'] : true;
			$this->require_number    = isset( $this->settings['password_minimum_numbers'] ) ? (bool) $this->settings['password_minimum_numbers'] : true;
			$this->require_special   = isset( $this->settings['password_minimum_special_characters'] ) ? (bool) $this->settings['password_minimum_special_characters'] : true;
			
			add_action( 'user_profile_update_errors', [ $this, 'validate_profile_password' ], 10, 3 );
			add_action( 'validate_password_reset', [ $this, 'validate_password_reset' ], 10, 2 );
			add_filter( 'registration_errors', [ $this, 'validate_registration_password' ], 10, 3 );
		}
	}
	
	public function adminease_settings_fields( array $fields ): array {
		$fields['users']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'force-strong-passwords',
			'name'         => 'adminease[users][force_strong_passwords]',
			'value'        => $this->settings['force_strong_passwords'] ?? '',
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Force strong passwords', 'adminease' ),
			'description'  => __( '<p>Enforcing the use of <strong>strong passwords</strong> in WordPress helps protect your site from brute force attacks and unauthorized access. By requiring users to create passwords with a mix of uppercase and lowercase letters, numbers, and special characters, you ensure better account security for both administrators and regular users.</p><p>When enforced via PHP, this rule applies across the entire site — during registration, password resets, and profile updates. Weak passwords are rejected, and users are prompted to choose a stronger one. This level of control helps <strong>standardize security practices</strong> and ensures even third-party plugins follow your password policy.</p>', 'adminease' ),
			'child_fields' => [
				[
					'type'              => 'number',
					'id'                => 'minimum-length',
					'name'              => 'adminease[users][password_minimum_length]',
					'value'             => $this->settings['password_minimum_length'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Minimum length', 'adminease' ),
					'field_description' => __( 'e.g., 8 or more characters', 'adminease' ),
					'attributes'        => [
						'min'         => 8,
						'max'         => 64,
						'step'        => 1,
						'data-parent' => 'force-strong-passwords',
					],
				],
				[
					'type'              => 'number',
					'id'                => 'maximum-length',
					'name'              => 'adminease[users][password_maximum_length]',
					'value'             => $this->settings['password_maximum_length'] ?? '',
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Maximum length', 'adminease' ),
					'field_description' => __( 'e.g., 8 or more characters', 'adminease' ),
					'attributes'        => [
						'min'         => 8,
						'max'         => 64,
						'step'        => 1,
						'data-parent' => 'force-strong-passwords',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'minimum-uppercase-letters',
					'name'          => 'adminease[users][password_minimum_uppercase_letters]',
					'value'         => $this->settings['password_minimum_uppercase_letters'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'At least one uppercase letter', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'force-strong-passwords',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'minimum-lowercase-letters',
					'name'          => 'adminease[users][password_minimum_lowercase_letters]',
					'value'         => $this->settings['password_minimum_lowercase_letters'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'At least one lowercase letter', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'force-strong-passwords',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'minimum-numbers',
					'name'          => 'adminease[users][password_minimum_numbers]',
					'value'         => $this->settings['password_minimum_numbers'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'At least one number', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'force-strong-passwords',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'minimum-special-characters',
					'name'          => 'adminease[users][password_minimum_special_characters]',
					'value'         => $this->settings['password_minimum_special_characters'] ?? '',
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'At least one special character', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'force-strong-passwords',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Validates the password provided during user registration against defined strength requirements.
	 * Checks the strength of the password submitted in the registration form, and if it does not meet
	 * the criteria, an error is added to the registration errors object.
	 *
	 * @param WP_Error $errors Object containing validation errors during registration.
	 * @param string   $sanitized_user_login The sanitized username.
	 * @param string   $user_email The sanitized email address of the user.
	 *
	 * @return WP_Error The updated errors object with potential password validation errors.
	 */
	public function validate_registration_password( WP_Error $errors, string $sanitized_user_login, string $user_email ): WP_Error {
		// Check if this is a legitimate registration request
		if( !isset( $_POST['user_pass'] ) || !is_user_logged_in() === false ) {
			// For registration forms, verify the registration nonce if available
			if( isset( $_POST['_wpnonce'] ) && !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'create-user' ) ) {
				$errors->add( 'nonce_verification_failed', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) );
				
				return $errors;
			}
		}
		
		$password = isset( $_POST['user_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['user_pass'] ) ) : null;
		
		if( null !== $password ) {
			$error = $this->check_password_strength( $password );
			
			if( $error ) {
				$errors->add( 'weak_password', $error );
			}
		}
		
		return $errors;
	}
	
	/**
	 * Validates the strength of a reset password during the password reset process.
	 * Checks the submitted password against defined strength requirements and adds an error if the password is weak.
	 *
	 * @param WP_Error $errors An object containing any encountered validation errors.
	 * @param WP_User  $user The user object for the current password reset request.
	 *
	 * @return void
	 */
	public function validate_password_reset( WP_Error $errors, WP_User $user ): void {
		if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'reset-password' ) ) {
			$errors->add( 'nonce_verification_failed', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) );
			
			return;
		}
		
		if( isset( $_POST['pass1'] ) ) {
			$password = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );
			$error    = $this->check_password_strength( $password );
			
			if( $error ) {
				$errors->add( 'weak_password', $error );
			}
		}
	}
	
	/**
	 * Validates the user's profile password during a profile update action.
	 * Checks if the new password meets the defined strength requirements
	 * and adds an error if the password is considered weak.
	 *
	 * @param WP_Error $errors Object to collect validation errors.
	 * @param bool     $update Indicates if the user is being updated (true) or created (false).
	 * @param WP_User  $user The user object for the profile being updated.
	 *
	 * @return void
	 */
	public function validate_profile_password( WP_Error $errors, bool $update, WP_User $user ): void {
		if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user->ID ) ) {
			$errors->add( 'nonce_verification_failed', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) );
			
			return;
		}
		
		if( !empty( $_POST['pass1'] ) ) {
			$password = sanitize_text_field( wp_unslash( $_POST['pass1'] ) );
			$error    = $this->check_password_strength( $password );
			
			if( $error ) {
				$errors->add( 'weak_password', $error );
			}
		}
	}
	
	/**
	 * Validates the strength of a given password against configured criteria.
	 * Checks include minimum and maximum length, presence of uppercase and lowercase letters,
	 * numerical digits, and special characters based on the class configurations.
	 *
	 * @param string $password The password to validate.
	 *
	 * @return string|false Returns an error message string if the password fails a specific check,
	 *                      or false if the password meets all strength requirements.
	 */
	private function check_password_strength( string $password ) {
		if( strlen( $password ) < $this->min_length ) {
			/* translators: %d is a placeholder for minimum password length */
			return sprintf( esc_html__( 'Password must be at least %d characters long.', 'adminease' ), $this->min_length );
		}
		if( strlen( $password ) > $this->max_length ) {
			/* translators: %d is a placeholder for maximum password length */
			return sprintf( esc_html__( 'Password must not exceed %d characters.', 'adminease' ), $this->max_length );
		}
		if( $this->require_uppercase && !preg_match( '/[A-Z]/', $password ) ) {
			return esc_html__( 'Password must include at least one uppercase letter.', 'adminease' );
		}
		if( $this->require_lowercase && !preg_match( '/[a-z]/', $password ) ) {
			return esc_html__( 'Password must include at least one lowercase letter.', 'adminease' );
		}
		if( $this->require_number && !preg_match( '/[0-9]/', $password ) ) {
			return esc_html__( 'Password must include at least one number.', 'adminease' );
		}
		if( $this->require_special && !preg_match( '/[\W_]/', $password ) ) {
			return esc_html__( 'Password must include at least one special character.', 'adminease' );
		}
		
		return false;
	}
}