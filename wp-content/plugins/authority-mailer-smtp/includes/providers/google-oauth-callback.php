<?php
/**
 * google-oauth-callback.php
 *
 * Google OAuth callback handler for Authority Mailer.
 */

defined( 'ABSPATH' ) || exit;

/*
 * NOTE: The Google OAuth callback route is registered in authority-mailer-smtp.php.
 * This file provides the callback handler function only to avoid duplicate route registration.
 *
 * The route uses __return_true for permission_callback because OAuth 2.0 callbacks must be
 * publicly accessible. See the security documentation in authority-mailer-smtp.php for
 * complete details on the security measures in place.
 */

/**
 * Handle OAuth callback from Google
 *
 * @param WP_REST_Request $request
 * @return WP_HTTP_Response|void
 */
function authority_mailer_smtp_google_oauth_callback( $request ) {
	// Debug test endpoint.
	if ( isset( $request['test'] ) && '1' === (string) $request['test'] ) {
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'authority-mailer google callback route is working',
				'file'    => __FILE__,
				'query'   => $request->get_query_params(),
			)
		);
	}

	$code  = $request->get_param( 'code' );
	$state = $request->get_param( 'state' );
	$error = $request->get_param( 'error' );

	if ( $error ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=' . urlencode( $error ) ) );
		exit;
	}

	// Accept both old and new state values for backwards compatibility during migration.
	if ( 'authority-mailer-smtp' !== $state ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=invalid_state' ) );
		exit;
	}

	if ( empty( $code ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=no_code' ) );
		exit;
	}

	// Determine the option key.
	$option_key = 'authority_mailer_smtp_options';
	if ( class_exists( 'Authority_Mailer_Onboarding' ) ) {
		$reflection = new ReflectionClass( 'Authority_Mailer_Onboarding' );
		if ( $reflection->hasConstant( 'OPTION_KEY' ) ) {
			$option_key = $reflection->getConstant( 'OPTION_KEY' );
		}
	}

	$options = get_option( $option_key, array() );

	$client_id     = ! empty( $options['google_client_id'] ) ? $options['google_client_id'] : '';
	$client_secret = ! empty( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
	$redirect_uri  = ! empty( $options['google_redirect_uri'] ) ? $options['google_redirect_uri'] : home_url( '/wp-json/authority-mailer-smtp/google/callback' );

	if ( empty( $client_id ) || empty( $client_secret ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=missing_credentials' ) );
		exit;
	}

	// Exchange code for tokens.
	$response = wp_remote_post(
		'https://oauth2.googleapis.com/token',
		array(
			'body'    => array(
				'code'          => $code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
				'grant_type'    => 'authorization_code',
			),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=token_exchange_failed' ) );
		exit;
	}

	$code_http = wp_remote_retrieve_response_code( $response );
	$body      = wp_remote_retrieve_body( $response );
	$data      = json_decode( $body, true );

	if ( 200 !== $code_http || empty( $data['access_token'] ) ) {
		$error_msg = ! empty( $data['error_description'] ) ? $data['error_description'] : 'token_exchange_failed';
		wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&error=' . urlencode( $error_msg ) ) );
		exit;
	}

	$options['google_access_token'] = $data['access_token'];
	if ( ! empty( $data['refresh_token'] ) ) {
		$options['google_refresh_token'] = $data['refresh_token'];
	}
	$options['google_connected']     = true;
	$options['google_token_expires'] = time() + ( ! empty( $data['expires_in'] ) ? (int) $data['expires_in'] : 3600 );

	// Fetch user info.
	$user_info_response = wp_remote_get(
		'https://www.googleapis.com/oauth2/v2/userinfo',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $data['access_token'],
			),
			'timeout' => 20,
		)
	);

	if ( ! is_wp_error( $user_info_response ) ) {
		$user_body = wp_remote_retrieve_body( $user_info_response );
		$user_data = json_decode( $user_body, true );

		if ( ! empty( $user_data['email'] ) ) {
			$options['google_from_email'] = $user_data['email'];
		}
		if ( ! empty( $user_data['name'] ) && empty( $options['google_from_name'] ) ) {
			$options['google_from_name'] = $user_data['name'];
		}
	}

	update_option( $option_key, $options );

	wp_safe_redirect( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=2&provider=gmail&connected=1' ) );
	exit;
}
