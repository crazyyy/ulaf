<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC License Endpoints.
 * 
 * This class provides the endpoints (controllers) for the license.
 */
class ADBC_License_Endpoints {

	/**
	 * Activate license.
	 *
	 * @param WP_REST_Request $request The request object.
	 * 
	 * @return WP_REST_Response The response containing the license action response.
	 */
	public static function activate_license( WP_REST_Request $request ) {

		try {

			$license_key = trim( sanitize_text_field( $request->get_param( 'license_key' ) ) );

			if ( empty( $license_key ) )
				return ADBC_Rest::error( 'Please provide a license key to activate.', ADBC_Rest::BAD_REQUEST );

			// Send request calls to the API and get the response.
			$response = ADBC_License_Manager::activate_license( $license_key );

			if ( $response['success'] === false )
				return ADBC_Rest::error( $response['message'], ADBC_Rest::BAD_REQUEST );

			return ADBC_Rest::success( $response["message"], $response["data"] );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Deactivate license.
	 *
	 * @param WP_REST_Request $request The request object.
	 * 
	 * @return WP_REST_Response The response containing the license action response.
	 */
	public static function deactivate_license( WP_REST_Request $request ) {

		try {

			// Send request calls to the API and get the response.
			$response = ADBC_License_Manager::deactivate_license();

			if ( $response['success'] === false )
				return ADBC_Rest::error( $response['message'], ADBC_Rest::BAD_REQUEST );

			return ADBC_Rest::success( $response["message"], $response["data"] );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

	/**
	 * Refresh license info.
	 *
	 * @param WP_REST_Request $request The request object.
	 * 
	 * @return WP_REST_Response The response containing the license action response.
	 */
	public static function refresh_license( WP_REST_Request $request ) {

		try {

			// Send request calls to the API and get the response.
			$response = ADBC_License_Manager::refresh_license();

			if ( $response['success'] === false )
				return ADBC_Rest::error( $response['message'], ADBC_Rest::BAD_REQUEST );

			return ADBC_Rest::success( $response["message"], $response["data"] );

		} catch (Exception $e) {

			return ADBC_Rest::error_for_uncaught_exception( __METHOD__, $e );

		}

	}

}