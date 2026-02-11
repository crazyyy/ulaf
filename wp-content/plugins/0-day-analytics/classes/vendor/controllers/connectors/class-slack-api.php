<?php
/**
 * Slack API class
 *
 * @package advanced-analytics
 *
 * @since 1.8.0
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/*
 * Api class for slack
 */
if ( ! class_exists( '\ADVAN\Controllers\Slack_API' ) ) {
	/**
	 * Responsible for communication with the Slack API.
	 *
	 * @since 1.8.0
	 */
	class Slack_API {

		/**
		 * Error message
		 *
		 * @var string
		 *
		 * @since 1.8.0
		 */
		public static $error = null;

		/**
		 * Response valid message
		 *
		 * @var string
		 *
		 * @since 1.8.0
		 */
		public static $valid_message = null;

		/**
		 * Send Slack message to a specific channel.
		 *
		 * @param string $bot_token - API Auth token to use.
		 * @param string $channel_name   - The name of the channel.
		 * @param string $text   - Text body to send.
		 *
		 * @since 1.8.0
		 */
		public static function send_slack_message_via_api( ?string $bot_token, ?string $channel_name, string $text ) {

			// Fallback to configured values when not provided.
			if ( empty( $bot_token ) ) {
				$bot_token = Slack::get_slack_auth_key();
			}

			if ( empty( $channel_name ) ) {
				$channel_name = Slack::get_slack_channel();
			}

			// Basic hardening: trim and strip control chars to prevent header injection.
			$bot_token    = is_string( $bot_token ) ? trim( preg_replace( '/[\x00-\x1F\x7F]/u', '', $bot_token ) ) : '';
			$channel_name = is_string( $channel_name ) ? trim( preg_replace( '/[\x00-\x1F\x7F]/u', '', $channel_name ) ) : '';

			if ( empty( $bot_token ) ) {
				self::$error = 'Slack bot token is missing or invalid.';
				return false;
			}

			if ( empty( $channel_name ) ) {
				self::$error = 'Slack channel is missing or invalid.';
				return false;
			}

			$url = 'https://slack.com/api/chat.postMessage';
			// Sanitize text to avoid accidentally sending control characters; Slack expects plain text.
			$clean_text = preg_replace( '/[\x00-\x1F\x7F]/u', '', (string) $text );
			$data       = array(
				'channel'      => $channel_name,
				'text'         => ':warning: ' . $clean_text,
				'unfurl_links' => false,
				'unfurl_media' => false,
			);

			$headers = array(
				'Content-Type'  => 'application/json; charset=utf-8',
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $bot_token,
			);

			$body_json = \wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			if ( false === $body_json ) {
				self::$error = 'Failed to encode Slack payload.';
				return false;
			}

			$args = array(
				'method'      => 'POST',
				'headers'     => $headers,
				'body'        => $body_json,
				'timeout'     => 10,
				'redirection' => 3,
			);

			$response = \wp_remote_post( \esc_url_raw( $url ), $args );

			if ( \is_wp_error( $response ) ) {
				self::$error = $response->get_error_message();

				return false;
			}

			// Validate Slack HTTP response and API JSON body.
			$code = \wp_remote_retrieve_response_code( $response );
			$body = \wp_remote_retrieve_body( $response );
			if ( $code < 200 || $code >= 300 ) {
				self::$error = 'Slack HTTP error: ' . (int) $code;
				return false;
			}

			if ( ! empty( $body ) ) {
				$decoded = json_decode( $body, true );
				if ( is_array( $decoded ) && array_key_exists( 'ok', $decoded ) ) {
					if ( ! $decoded['ok'] ) {
						self::$error = isset( $decoded['error'] ) ? (string) $decoded['error'] : 'Slack API returned ok=false';
						return false;
					}
					return true;
				}
			}

			// If we can't parse JSON but HTTP is 2xx, assume success but keep a soft warning.
			self::$valid_message = 'Slack response could not be parsed';
			return true;
		}

		/**
		 * Verify the Slack token.
		 *
		 * @param string $token - The token to verify.
		 *
		 * @return bool|\WP_Error
		 *
		 * @since 1.8.0
		 */
		public static function verify_slack_token( $token ) {
			if ( empty( $token ) ) {
				return new \WP_Error( 'slack_config', 'Bot token not configured' );
			}

			// Basic hardening: trim and strip control chars to prevent header injection.
			$token = is_string( $token ) ? trim( preg_replace( '/[\x00-\x1F\x7F]/u', '', $token ) ) : '';
			if ( empty( $token ) ) {
				return new \WP_Error( 'slack_config', 'Bot token not configured' );
			}

			$url     = 'https://slack.com/api/auth.test';
			$headers = array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			);

			$args = array(
				'method'  => 'POST',
				'headers' => $headers,
				'timeout' => 10,
			);

			$response = \wp_remote_post( \esc_url_raw( $url ), $args );

			if ( \is_wp_error( $response ) ) {
				self::$error = $response->get_error_message();

				return false;
			} elseif ( ! empty( $response['body'] ) ) {
				$response_data = json_decode( $response['body'], true );
				if ( is_array( $response_data ) && ! empty( $response_data['ok'] ) ) {
					self::$valid_message = $response_data;
					return true;
				}
				if ( is_array( $response_data ) && isset( $response_data['error'] ) ) {
					self::$error = (string) $response_data['error'];
					return false;
				}

				// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- alignment not critical for readability here.
				$code = \wp_remote_retrieve_response_code( $response );
				self::$error = 'Slack token verify failed. HTTP ' . (int) $code;
				return false;
			}

			self::$error = 'Unknown error';

			return false;
		}

		/**
		 * Returns the error stored from Slack.
		 *
		 * @since 1.8.0
		 */
		public static function get_slack_error(): string {
			$error = self::$error;
			if ( \is_array( self::$error ) ) {
				$encoded = \wp_json_encode( self::$error, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
				$error   = false !== $encoded ? $encoded : 'Unknown error';
			}

			return (string) $error;
		}
	}
}
