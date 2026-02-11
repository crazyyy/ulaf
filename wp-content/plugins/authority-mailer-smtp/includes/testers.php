<?php
/**
 * testers.php
 *
 * Dispatcher that loads provider adapters and returns adapter steps.
 *
 * Adapter contract:
 * - Adapter files live in: wp-content/plugins/authority-mailer-smtp/includes/providers/
 * - Adapter must expose a function authority_mailer_smtp_test_{provider} that accepts array $settings
 *   and returns an ordered array of diagnostic steps.
 *
 * Goals for this "optimized" revision:
 * - Harden provider input sanitization and filename validation to avoid path traversal.
 * - Resolve adapters from the primary plugin folder, then limited known alternates.
 * - Provide a clear candidate-function lookup order and deterministic error reporting.
 * - Normalize common provider aliases (gmail -> google, elastic -> elasticmail, bird -> sparkpost).
 * - Keep the function simple to audit and test; fail safely with clear diagnostics.
 *
 * Notes:
 * - The provider key used from the query string is first normalized to a canonical name.
 * - Adapters and function lookups use canonical names only; legacy aliases are accepted in the query.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get spam score for test email content.
 *
 * Checks if spam checker feature is enabled and analyzes the email content.
 * Returns null if spam checking is disabled or unavailable.
 *
 * @since 1.0.0
 * @param string $subject Email subject.
 * @param string $content Email body content.
 * @return float|null Spam score (0-100) or null if unavailable.
 */
function authority_mailer_smtp_get_test_spam_score( $subject, $content ) {
	// Spam checker feature removed for free version.
	return null;
}

/**
 * Run provider diagnostics by including the provider adapter and calling its test function.
 *
 * @param string $provider Provider key (e.g. 'sendlayer','brevo','elastic','mailersend','mailjet','mailgun','mandrill','google','smtpcom','other','postmark','smtp2go','sparkpost')
 * @param array  $settings Optional settings to use for the test (api keys, from, to, attachments, etc)
 * @return array Ordered diagnostic steps
 */
function authority_mailer_smtp_test_provider( $provider, $settings = array() ) {
	// Strict normalization: lowercase, trim, allow only a-z0-9_- and limit length.
	$provider = strtolower( trim( (string) $provider ) );
	$provider = preg_replace( '#[^a-z0-9_\-]#', '', $provider );

	// Normalize common aliases (map legacy/alternate keys to canonical provider names)
	$alias_map = array(
		// SparkPost had legacy 'bird' usage in some UI skins
		'bird'    => 'sparkpost',
		// Gmail tile historically used 'gmail' but adapter is google.php
		'gmail'   => 'google',
		// Elastic alias
		'elastic' => 'elasticmail',
	);

	if ( isset( $alias_map[ $provider ] ) ) {
		$provider = $alias_map[ $provider ];
	}

	if ( '' === $provider || strlen( $provider ) > 40 ) {
		return array(
			array(
				'status'  => 'error',
				'message' => 'Invalid or missing provider key.',
				'details' => array( 'received' => (string) $provider ),
			),
		);
	}

	$steps = array();

	// Header step - concise and safe
	$steps[] = array(
		'status'  => 'info',
		'message' => sprintf( "Running diagnostics for provider '%s'", $provider ),
	);

	// Base providers directory used by adapters
	$providers_dir      = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/providers/';
	$providers_dir_real = realpath( $providers_dir );
	if ( false === $providers_dir_real ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Providers directory missing',
			'details' => $providers_dir,
		);
		return $steps;
	}

	/*
	 * Provider -> adapter filename map (filenames relative to $providers_dir).
	 * Use canonical keys only here.
	 */
	$providers_map = array(
		'sendlayer'   => 'sendlayer.php',
		'brevo'       => 'brevo.php',
		'elasticmail' => 'elasticmail.php',
		'mailersend'  => 'mailersend.php',
		'mailjet'     => 'mailjet.php',
		'mailgun'     => 'mailgun.php',
		'mandrill'    => 'mandrill.php',
		'google'      => 'google.php',
		'smtpcom'     => 'smtpcom.php',
		'postmark'    => 'postmark.php',
		'smtp2go'     => 'smtp2go.php',
		'sparkpost'   => 'sparkpost.php',
		'zoho'        => 'zoho.php',
		'aws'         => 'aws.php',
		'office365'   => 'office365.php',
		'other'       => 'other.php',
	);

	// Provider -> candidate function names map (preferred order after authority_mailer_test_{provider})
	$fn_map = array(
		'sendlayer'   => array( 'authority_mailer_smtp_test_sendlayer' ),
		'brevo'       => array( 'authority_mailer_smtp_test_brevo' ),
		'elasticmail' => array( 'authority_mailer_smtp_test_elasticmail' ),
		'mailersend'  => array( 'authority_mailer_smtp_test_mailersend' ),
		'mailjet'     => array( 'authority_mailer_smtp_test_mailjet' ),
		'mailgun'     => array( 'authority_mailer_smtp_test_mailgun' ),
		'mandrill'    => array( 'authority_mailer_smtp_test_mandrill' ),
		'google'      => array( 'authority_mailer_smtp_test_google' ),
		'smtpcom'     => array( 'authority_mailer_smtp_test_smtpcom' ),
		'postmark'    => array( 'authority_mailer_smtp_test_postmark' ),
		'smtp2go'     => array( 'authority_mailer_smtp_test_smtp2go' ),
		'sparkpost'   => array( 'authority_mailer_smtp_test_sparkpost' ),
		'zoho'        => array( 'authority_mailer_smtp_test_zoho' ),
		'aws'         => array( 'authority_mailer_smtp_test_aws' ),
		'office365'   => array( 'authority_mailer_smtp_test_office365' ),
		'other'       => array( 'authority_mailer_smtp_test_other_smtp' ),
	);

	// Determine adapter filename from canonical provider name
	$adapter_filename = isset( $providers_map[ $provider ] ) ? $providers_map[ $provider ] : ( $provider . '.php' );

	// Validate adapter filename (prevent traversal or invalid chars)
	if ( ! preg_match( '/^[a-z0-9._-]+\.php$/', $adapter_filename ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => 'Resolved adapter filename is invalid.',
			'details' => array( 'adapter_filename' => $adapter_filename ),
		);
		return $steps;
	}

	// Candidate locations to look for the adapter (primary plugin first, then known alternates)
	// Use proper WordPress plugin path detection instead of WP_PLUGIN_DIR
	$candidate_paths = array(
		$providers_dir . $adapter_filename,
	);

	// Check for pro version if it exists
	$pro_plugin_file    = WP_PLUGIN_DIR . '/authority-mailer-pro/authority-mailer-pro.php';
	$pro_plugin_exists  = file_exists( $pro_plugin_file );
	if ( $pro_plugin_exists ) {
		$candidate_paths[] = plugin_dir_path( $pro_plugin_file ) . 'includes/providers/' . $adapter_filename;
	}

	// Check for premium version if it exists
	$premium_plugin_file   = WP_PLUGIN_DIR . '/authority-mailer-premium/authority-mailer-premium.php';
	$premium_plugin_exists = file_exists( $premium_plugin_file );
	if ( $premium_plugin_exists ) {
		$candidate_paths[] = plugin_dir_path( $premium_plugin_file ) . 'includes/providers/' . $adapter_filename;
	}

	$adapter_path = '';
	foreach ( $candidate_paths as $p ) {
		$real = realpath( $p );
		if ( $real && is_file( $real ) ) {
			// Safety: ensure the realpath is within one of the allowed includes/providers directories
			$allowed_base_real = $providers_dir_real;
			$allowed_bases     = array( $allowed_base_real );

			// Add pro/premium base paths if those plugins exist
			if ( $pro_plugin_exists ) {
				$allowed_alt1_real = realpath( plugin_dir_path( $pro_plugin_file ) . 'includes' );
				if ( $allowed_alt1_real ) {
					$allowed_bases[] = $allowed_alt1_real;
				}
			}
			if ( $premium_plugin_exists ) {
				$allowed_alt2_real = realpath( plugin_dir_path( $premium_plugin_file ) . 'includes' );
				if ( $allowed_alt2_real ) {
					$allowed_bases[] = $allowed_alt2_real;
				}
			}

			// Accept if realpath starts with allowed base paths
			$accepted = false;
			foreach ( $allowed_bases as $base ) {
				if ( $base && 0 === strpos( $real, $base ) ) {
					$accepted = true;
					break;
				}
			}

			if ( $accepted ) {
				$adapter_path = $real;
				break;
			}
		}
	}

	if ( empty( $adapter_path ) ) {
		$steps[] = array(
			'status'  => 'error',
			'message' => sprintf( 'Adapter file not found for provider "%s".', $provider ),
			'details' => array( 'expected' => $candidate_paths ),
		);
		return $steps;
	}

	// Include the adapter file. Use include_once to avoid double includes; adapters should only declare functions.
	@include_once $adapter_path;

	// Build candidate function names to call, ordered:
	// 1) authority_mailer_test_{provider} (provider is canonical now)
	// 2) explicit mapping in $fn_map
	// 3) authority_mailer_test_{adapter_basename}
	$candidates   = array();
	$candidates[] = 'authority_mailer_smtp_test_' . $provider;

	if ( isset( $fn_map[ $provider ] ) && is_array( $fn_map[ $provider ] ) ) {
		foreach ( $fn_map[ $provider ] as $fn ) {
			if ( ! in_array( $fn, $candidates, true ) ) {
				$candidates[] = $fn;
			}
		}
	}

	$basename   = pathinfo( $adapter_path, PATHINFO_FILENAME );
	$fn_by_file = 'authority_mailer_smtp_test_' . $basename;
	if ( ! in_array( $fn_by_file, $candidates, true ) ) {
		$candidates[] = $fn_by_file;
	}

	// Try candidate functions
	$tried = array();
	foreach ( $candidates as $fn ) {
		$tried[] = $fn;
		if ( function_exists( $fn ) ) {
			// Call the adapter function and validate its result.
			$adapter_steps = call_user_func( $fn, is_array( $settings ) ? $settings : array() );

			if ( is_array( $adapter_steps ) ) {
				// Merge header steps created above with adapter steps and return
				return array_merge( $steps, $adapter_steps );
			}

			// Adapter returned invalid data type
			$steps[] = array(
				'status'  => 'error',
				'message' => 'Adapter returned invalid result (expected array of steps).',
				'details' => array(
					'function'    => $fn,
					'return_type' => gettype( $adapter_steps ),
				),
			);
			return $steps;
		}
	}

	// No candidate function found
	$steps[] = array(
		'status'  => 'error',
		'message' => 'Adapter function not found after including provider file',
		'details' => array(
			'included_file'        => $adapter_path,
			'tried_function_names' => $tried,
		),
	);

	return $steps;
}
