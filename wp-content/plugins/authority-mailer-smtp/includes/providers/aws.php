<?php
/**
 * Aws.php
 *
 * AWS SES provider tester for Authority Mailer onboarding.
 *
 * Uses SMTP connection with AWS SES servers to test email delivery.
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
 * Get AWS SES SMTP endpoint for a given region.
 *
 * @since 1.0.0
 *
 * @param string $region AWS region code. Default 'us-east-1'.
 * @return string SMTP endpoint hostname.
 */
function authority_mailer_smtp_get_aws_smtp_endpoint( $region = 'us-east-1' ) {
	$region = sanitize_text_field( $region );
	return sprintf( 'email-smtp.%s.amazonaws.com', $region );
}

/**
 * Run AWS SES SMTP diagnostics and test transmission.
 *
 * Tests AWS SES SMTP connection, validates credentials, performs DNS resolution,
 * and sends a test email using SMTP protocol.
 *
 * @since 1.0.0
 *
 * @param array $settings Optional. Array of AWS SES settings including smtp_host, username, password, region, etc. Default empty array.
 * @return array Array of diagnostic steps with status, message, and details for each step.
 */
function authority_mailer_smtp_test_aws( $settings = array() ) {
	// Delegate to generic SMTP tester with AWS SES defaults.
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

	// Determine region and SMTP endpoint.
	$region       = ! empty( $settings['aws_region'] ) ? $settings['aws_region'] : 'us-east-1';
	$default_host = authority_mailer_smtp_get_aws_smtp_endpoint( $region );

	// Merge AWS SES-specific defaults.
	$aws_settings = array_merge(
		array(
			'aws_smtp_host'       => $default_host,
			'aws_smtp_port'       => '587',
			'aws_smtp_encryption' => 'tls',
			'aws_region'          => $region,
		),
		$settings
	);

	// If smtp_host is not set but region is, set the correct endpoint.
	if ( empty( $aws_settings['aws_smtp_host'] ) || $aws_settings['aws_smtp_host'] === $default_host ) {
		$aws_settings['aws_smtp_host'] = authority_mailer_smtp_get_aws_smtp_endpoint( $aws_settings['aws_region'] );
	}

	// Map AWS fields to generic SMTP fields.
	$mapped_settings = array();
	foreach ( $aws_settings as $key => $value ) {
		if ( 0 === strpos( $key, 'aws_' ) ) {
			$generic_key                     = str_replace( 'aws_', 'other_', $key );
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

	// Preserve AWS provider identity for logging.
	$mapped_settings['provider'] = 'aws';

	$steps = authority_mailer_smtp_test_other_smtp( $mapped_settings );

	// Prepend AWS SES-specific header.
	array_unshift(
		$steps,
		array(
			'status'  => 'info',
			'message' => sprintf( 'Starting AWS SES SMTP diagnostics (Region: %s)', $region ),
		)
	);

	return $steps;
}

/**
 * Send email via AWS SES SMTP.
 *
 * Sends an email through the AWS SES SMTP service.
 * Handles authentication, SMTP connection, and transmission.
 *
 * @since 1.0.0
 *
 * @param array $email Email data from wp_mail containing to, subject, message, headers, and attachments.
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function authority_mailer_smtp_send_aws( $email ) {
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

	// Determine region and SMTP endpoint.
	$region    = ! empty( $options['aws_region'] ) ? $options['aws_region'] : 'us-east-1';
	$smtp_host = ! empty( $options['aws_smtp_host'] ) ? $options['aws_smtp_host'] : authority_mailer_smtp_get_aws_smtp_endpoint( $region );

	// Map AWS SES settings to generic SMTP format.
	$mapped_email = array_merge(
		$email,
		array(
			'provider'        => 'aws',
			'smtp_host'       => $smtp_host,
			'smtp_port'       => ! empty( $options['aws_smtp_port'] ) ? $options['aws_smtp_port'] : 587,
			'smtp_username'   => ! empty( $options['aws_smtp_username'] ) ? $options['aws_smtp_username'] : '',
			'smtp_password'   => ! empty( $options['aws_smtp_password'] ) ? $options['aws_smtp_password'] : '',
			'smtp_encryption' => ! empty( $options['aws_smtp_encryption'] ) ? $options['aws_smtp_encryption'] : 'tls',
			'from_email'      => ! empty( $options['aws_from_email'] ) ? $options['aws_from_email'] : get_option( 'admin_email' ),
			'from_name'       => ! empty( $options['aws_from_name'] ) ? $options['aws_from_name'] : get_bloginfo( 'name' ),
		)
	);

	return authority_mailer_smtp_send_other_smtp( $mapped_email );
}
