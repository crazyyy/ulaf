<?php
/**
 * ConnectorPostmark class for managing Postmark SMTP connection settings.
 *
 * @since   1.2.0
 * @package SolidWP\Mail\Connectors
 */

namespace SolidWP\Mail\Connectors;

use SolidWP\Mail\Contracts\Api_Connector;
use WP_Error;

class ConnectorPostmark extends ConnectorSMTP implements Api_Connector {

	/**
	 * Postmark API endpoint for sending emails.
	 *
	 * @since 2.1.3
	 * @var string
	 */
	const API_ENDPOINT = 'https://api.postmarkapp.com/email';

	/**
	 * ConnectorPostmark constructor.
	 * Initializes the Postmark connector with default SMTP settings.
	 *
	 * @param array $data Optional. Configuration data for the connector.
	 *
	 * @since 1.2.0
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		// Prefill the needed data for Postmark.
		$this->host           = 'smtp.postmarkapp.com';
		$this->port           = 587;
		$this->authentication = 'yes';
		$this->secure         = 'tls';
		$this->name           = 'postmark';
		// update the deliver method
		$this->delivery_method = 'api';
	}

	/**
	 * Processes the data for SMTP configuration.
	 * Sets up the necessary credentials and settings for the Postmark connection.
	 *
	 * @param array $data The data array containing the email, name, and API key.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function process_data( array $data ) {
		$this->from_email = $data['from_email'] ?? '';
		$this->from_name  = $data['from_name'] ?? '';
		// this provider uses the API key for both username and password.
		$this->smtp_username = $data['smtp_username'] ?? '';
		$this->smtp_password = $data['smtp_username'] ?? '';
		$this->description   = 'Postmark';
	}

	/**
	 * Sends an email using the Postmark API.
	 *
	 * @param array $email_data   {
	 *                            Array of email data.
	 *
	 * @type array  $to           Required. Array of recipient email addresses.
	 * @type array  $cc           Optional. Array of CC recipient email addresses.
	 * @type array  $bcc          Optional. Array of BCC recipient email addresses.
	 * @type array  $reply_to     Optional. Array of reply-to email addresses.
	 * @type string $subject      Required. Email subject.
	 * @type string $raw_body     Required. Email body content.
	 * @type string $message_type Optional. Message type ('text/plain' or 'text/html').
	 * @type array  $attachments  Optional. Array of attachments.
	 *                            }
	 * @since 2.1.3
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function send_use_api( array $email_data ) {
		if ( empty( $this->smtp_username ) ) {
			return new WP_Error( 'postmark_error', 'Missing Postmark API key' );
		}

		try {
			// Format recipients
			$to       = $this->format_address_list( $email_data['to'] );
			$cc       = ! empty( $email_data['cc'] ) ? $this->format_address_list( $email_data['cc'] ) : null;
			$bcc      = ! empty( $email_data['bcc'] ) ? $this->format_address_list( $email_data['bcc'] ) : null;
			$reply_to = ! empty( $email_data['reply_to'] ) ? array_keys( $email_data['reply_to'] )[0] : null;

			// Format from address
			$from = $this->from_name ?
				sprintf( '%s <%s>', $this->from_name, $this->from_email ) :
				$this->from_email;

			// Process attachments
			$attachments = [];
			if ( ! empty( $email_data['attachments'] ) ) {
				foreach ( $email_data['attachments'] as $attachment ) {
					$attachment_file = $attachment[0];
					if ( file_exists( $attachment_file ) ) {
						$file_content = file_get_contents( $attachment_file );
						if ( $file_content !== false ) {
							$attachments[] = [
								'Name'        => basename( $attachment_file ),
								'Content'     => base64_encode( $file_content ),
								'ContentType' => mime_content_type( $attachment_file ),
							];
						}
					}
				}
			}

			// Prepare request body
			$body = [
				'From'          => $from,
				'To'            => $to,
				'Subject'       => $email_data['subject'],
				'HtmlBody'      => $email_data['raw_body'],
				'TextBody'      => wp_strip_all_tags( $email_data['raw_body'] ),
				'MessageStream' => 'outbound',
			];

			// if this is plain text then force to remove the HTML.
			if ( $email_data['message_type'] === 'text/plain' ) {
				unset( $body['HtmlBody'] );
			}

			if ( $reply_to ) {
				$body['ReplyTo'] = $reply_to;
			}
			if ( $cc ) {
				$body['Cc'] = $cc;
			}
			if ( $bcc ) {
				$body['Bcc'] = $bcc;
			}
			if ( ! empty( $attachments ) ) {
				$body['Attachments'] = $attachments;
			}

			// Make API request
			$response = wp_remote_post(
				self::API_ENDPOINT,
				[
					'headers' => [
						'Accept'                  => 'application/json',
						'Content-Type'            => 'application/json',
						'X-Postmark-Server-Token' => $this->smtp_username,
					],
					'body'    => wp_json_encode( $body ),
					// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
					'timeout' => 15, // @TODO: can we decrease timeout?
				]
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $response_body, true );

			if ( $response_code !== 200 ) {
				$error_message = $response_data['Message'] ?? 'Unknown error occurred';

				return new WP_Error( 'postmark_api_error', $error_message );
			}

			return ! empty( $response_data['MessageID'] );

		} catch ( \Exception $e ) {
			return new WP_Error( 'postmark_error', $e->getMessage() );
		}
	}

	/**
	 * Formats an array of email addresses into a comma-separated string.
	 *
	 * @param array $addresses Array of email addresses. Each element can be either
	 *                         a simple email address or an array containing email
	 *                         and name.
	 *
	 * @since 2.1.3
	 *
	 * @return string Formatted string of email addresses.
	 */
	private function format_address_list( array $addresses ): string {
		return implode(
			',',
			array_map(
				function ( $address ) {
					return ! empty( $address[1] ) ?
					sprintf( '%s <%s>', $address[1], $address[0] ) :
					$address[0];
				},
				$addresses 
			) 
		);
	}
}
