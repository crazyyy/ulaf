<?php
/**
 * Office365.php
 *
 * Office 365 / Outlook provider tester for Authority Mailer onboarding.
 *
 * Uses SMTP connection with Office 365 servers to test email delivery.
 * Supports both authentication and test transmission.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/common.php';

global $AUTHORITY_MAILER_STRINGS;

if ( ! function_exists( 'authority_mailer_smtp_s' ) ) {
	/**
	 * Safe localized accessor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The string key to look up in AUTHORITY_MAILER_STRINGS array.
	 * @return string The localized string value, or empty string if not found.
	 */
	function authority_mailer_smtp_s( $key ) {
		global $AUTHORITY_MAILER_STRINGS;
		if ( ! isset( $AUTHORITY_MAILER_STRINGS ) || ! is_array( $AUTHORITY_MAILER_STRINGS ) ) {
			return '';
		}
		return isset( $AUTHORITY_MAILER_STRINGS[ $key ] ) ? $AUTHORITY_MAILER_STRINGS[ $key ] : '';
	}
}

/**
 * Run Office 365 SMTP diagnostics and test transmission.
 *
 * Tests Office 365 SMTP connection, validates credentials, performs DNS resolution,
 * and sends a test email using SMTP protocol.
 *
 * @since 1.0.0
 *
 * @param array $settings Optional. Array of Office 365 settings including smtp_host, username, password, etc. Default empty array.
 * @return array Array of diagnostic steps with status, message, and details for each step.
 */
function authority_mailer_smtp_test_office365( $settings = array() ) {
	// Delegate to generic SMTP tester with Office 365 defaults.
	if ( ! function_exists( 'authority_mailer_smtp_test_other_smtp' ) ) {
		// Include the other/generic SMTP tester.
		$other_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/other.php';
		if ( file_exists( $other_file ) ) {
			require_once $other_file;
		}
	}

	if ( ! function_exists( 'authority_mailer_smtp_test_other_smtp' ) ) {
		return array(
			array(
				'status'  => 'error',
				'message' => 'Generic SMTP tester not available',
			),
		);
	}

	// Merge Office 365-specific defaults.
	$office365_settings = array_merge(
		array(
			'office365_smtp_host'       => 'smtp.office365.com',
			'office365_smtp_port'       => '587',
			'office365_smtp_encryption' => 'tls',
		),
		$settings
	);

	// Map Office 365 fields to generic SMTP fields.
	$mapped_settings = array();
	foreach ( $office365_settings as $key => $value ) {
		if ( 0 === strpos( $key, 'office365_' ) ) {
			$generic_key                     = str_replace( 'office365_', 'other_', $key );
			$mapped_settings[ $generic_key ] = $value;
		} else {
			$mapped_settings[ $key ] = $value;
		}
	}

	// Also map non-prefixed fields.
	if ( ! empty( $settings['smtp_host'] ) ) {
		$mapped_settings['other_smtp_host'] = $settings['smtp_host'];
	}
	if ( ! empty( $settings['smtp_port'] ) ) {
		$mapped_settings['other_smtp_port'] = $settings['smtp_port'];
	}
	if ( ! empty( $settings['smtp_username'] ) ) {
		$mapped_settings['other_smtp_username'] = $settings['smtp_username'];
	}
	if ( ! empty( $settings['smtp_password'] ) ) {
		$mapped_settings['other_smtp_password'] = $settings['smtp_password'];
	}
	if ( ! empty( $settings['smtp_encryption'] ) ) {
		$mapped_settings['other_smtp_encryption'] = $settings['smtp_encryption'];
	}

	// Preserve Office 365 provider identity for logging.
	$mapped_settings['provider'] = 'office365';

	$steps = authority_mailer_smtp_test_other_smtp( $mapped_settings );

	// Prepend Office 365-specific header.
	array_unshift(
		$steps,
		array(
			'status'  => 'info',
			'message' => 'Starting Office 365 SMTP diagnostics',
		)
	);

	return $steps;
}

/**
 * Send email via Office 365 SMTP.
 *
 * Sends an email through the Office 365 SMTP service.
 * Handles authentication, SMTP connection, and transmission.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_office365( $email ) {
	// Delegate to generic SMTP sender.
	if ( ! function_exists( 'authority_mailer_smtp_send_other_smtp' ) ) {
		$other_file = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/other.php';
		if ( file_exists( $other_file ) ) {
			require_once $other_file;
		}
	}

	if ( ! function_exists( 'authority_mailer_smtp_send_other_smtp' ) ) {
		return new WP_Error( 'missing_function', 'Generic SMTP sender not available' );
	}

	$options = get_option( 'authority_mailer_smtp_options', array() );

	// Map Office 365 settings to generic SMTP format.
	$mapped_email = array_merge(
		$email,
		array(
			'provider'        => 'office365',
			'smtp_host'       => ! empty( $options['office365_smtp_host'] ) ? $options['office365_smtp_host'] : 'smtp.office365.com',
			'smtp_port'       => ! empty( $options['office365_smtp_port'] ) ? $options['office365_smtp_port'] : 587,
			'smtp_username'   => ! empty( $options['office365_smtp_username'] ) ? $options['office365_smtp_username'] : '',
			'smtp_password'   => ! empty( $options['office365_smtp_password'] ) ? $options['office365_smtp_password'] : '',
			'smtp_encryption' => ! empty( $options['office365_smtp_encryption'] ) ? $options['office365_smtp_encryption'] : 'tls',
			'from_email'      => ! empty( $options['office365_from_email'] ) ? $options['office365_from_email'] : get_option( 'admin_email' ),
			'from_name'       => ! empty( $options['office365_from_name'] ) ? $options['office365_from_name'] : get_bloginfo( 'name' ),
		)
	);

	return authority_mailer_smtp_send_other_smtp( $mapped_email );
}
