<?php

namespace SolidWP\Mail;

/**
 * Interface InterfaceController
 *
 * This interface defines the contract that controllers in the SolidWP\Mail namespace should adhere to.
 * Controllers implementing this interface should provide a method to register hooks.
 *
 * @package Solid_SMTP
 */
abstract class AbstractController {

	/**
	 * Nonce name for each controller, default is a generic
	 *
	 * @var string
	 */
	protected string $nonce_name = 'solid-wp-nonce';

	/**
	 * Register hooks.
	 *
	 * Implementing classes should use this method to register hooks with WordPress or other systems.
	 *
	 * @return void
	 */
	abstract public function register_hooks();

	/**
	 * Checks if the current user has the required capability and verifies nonce for a specific action.
	 *
	 * @param string $action The nonce action.
	 *
	 * @return bool True if the user has the capability and nonce is valid, false otherwise.
	 */
	protected function able_to_perform( string $action = '' ): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonce_name ] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves input data, sanitizes it, and processes it by setting properties if possible.
	 *
	 * @param string $key The key of the input data.
	 * @param string $default_value
	 *
	 * @return string
	 */
	protected function get_and_sanitize_input( string $key, string $default_value = '' ): string {
		//phpcs:ignore
		return sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ?? $default_value ) );
	}

	/**
	 * Sanitize value from textarea.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function get_and_sanitize_textarea( string $key ): string {
		//phpcs:ignore
		return sanitize_textarea_field( wp_unslash( $_REQUEST[ $key ] ?? '' ) );
	}

	/**
	 * Converts a WP_Error object to an associative array.
	 *
	 * This method takes a WP_Error object and converts its error codes and messages
	 * into an associative array where the keys are the error codes and the values
	 * are the corresponding error messages.
	 *
	 * @param \WP_Error $error The WP_Error object to convert.
	 *
	 * @return array The associative array of error codes and messages.
	 */
	protected function wp_error_to_array( \WP_Error $error ): array {
		$errors = [];
		foreach ( $error->get_error_codes() as $code ) {
			$errors[ $code ] = $error->get_error_message( $code );
		}

		return $errors;
	}

	/**
	 * Just return generic error on ajax handling.
	 *
	 * @param string $error_message
	 *
	 * @return void
	 */
	protected function bail_out_generic_error( string $error_message ): void {
		wp_send_json_error(
			[
				'message' => $error_message,
			]
		);
	}
}
