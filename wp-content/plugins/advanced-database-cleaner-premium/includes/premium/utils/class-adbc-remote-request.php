<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ADBC remote request class.
 * 
 * This class contains the methods to send wp remote requests.
 */
class ADBC_Remote_Request {

	/**
	 * Send a remote request to the ADBC remote API.
	 * 
	 * @param string $url The URL to send the request to.
	 * @param array $data The data to send in the request.
	 * @param string $method The request method. Default is 'GET'.
	 * @param bool $blocking Whether to wait for the response or not. Default is true (wait for the response).
	 * @param int $timeout The timeout for the request. Default is 120 seconds.
	 * 
	 * @return array The response from the remote API.
	 */
	public static function send_request( $url, $data = [], $method = 'GET', $blocking = true, $timeout = 5, $stream = false, $filename = null ) {

		$args = [ 
			'timeout' => $blocking ? $timeout : 0.01, // Set to a very low number to not wait for the response if non-blocking
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => $blocking,
			'stream' => $stream,
			'filename' => $filename,
			'headers' => [ 
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . ADBC_License_Manager::get_license_data( false )['key'],
			],
			'sslverify' => true, // TO-CHECK: true in production and false in dev
		];

		$url = ADBC_API_REMOTE_URL . $url;

		// Add the body data for POST requests
		if ( $method === 'POST' ) {
			$args['body'] = json_encode( $data );
			$response = wp_remote_post( $url, $args );
		} else { // For GET requests
			$response = wp_remote_get( $url, $args );
		}

		// If the request is non-blocking, return immediately
		if ( ! $blocking )
			return;

		if ( is_wp_error( $response ) ) {

			return [ 
				'success' => false,
				'message' => $response->get_error_message(),
				'status_code' => wp_remote_retrieve_response_code( $response )
			];

		} else {

			$body = wp_remote_retrieve_body( $response );
			$status_code = wp_remote_retrieve_response_code( $response );

			$decoded_body = json_decode( $body, true );

			// Check for a non-200 status code
			if ( $status_code !== 200 ) {

				$failure_response = [ 
					'success' => false,
					'message' => $decoded_body !== null ? $decoded_body["message"] : 'Server responded with error code: ' . $status_code,
					'status_code' => $status_code
				];

				if ( isset( $decoded_body["failure_code"] ) )
					$failure_response['failure_code'] = $decoded_body["failure_code"];

				if ( isset( $decoded_body["balance"] ) )
					$failure_response['balance'] = $decoded_body["balance"];

				return $failure_response;

			}

			return $decoded_body;

		}

	}

}