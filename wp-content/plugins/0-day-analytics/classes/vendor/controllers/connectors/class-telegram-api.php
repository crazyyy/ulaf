<?php
/**
 * Telegram API class
 *
 * @package advanced-analytics
 *
 * @since 1.8.5
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/*
 * Api class for Telegram
 */
if ( ! class_exists( '\ADVAN\Controllers\Telegram_API' ) ) {
	/**
	 * Responsible for communication with the Telegram API.
	 *
	 * @since 1.8.5
	 */
	class Telegram_API {

		/**
		 * Error message
		 *
		 * @var string
		 *
		 * @since 1.8.5
		 */
		public static $error = null;

		/**
		 * Response valid message
		 *
		 * @var string
		 *
		 * @since 1.8.5
		 */
		public static $valid_message = null;

		/**
		 * Validate basic Telegram bot token format.
		 * Format example: 123456789:ABCDEFghIjklMNopQRstuVWxyz-1234567890
		 *
		 * @param string|null $token The bot token to validate.
		 * 
		 * @return bool True if token looks valid, false otherwise.
		 *
		 * @since 1.8.6
		 */
		private static function is_valid_token( ?string $token ): bool {
			if ( empty( $token ) ) {
				return false;
			}

			return (bool) \preg_match( '/^[0-9]{6,}:[A-Za-z0-9_\-]{20,}$/', (string) $token );
		}

		/**
		 * Send Telegram message to a specific channel.
		 *
		 * @param string|null $bot_token   API Auth token to use (falls back to settings if null).
		 * @param string|null $channel_id  The ID of the channel (falls back to settings if null).
		 * @param string      $text        Text body to send.
		 * @param array       $extra_body  Additional body params for Telegram API (e.g. reply_markup).
		 *
		 * @since 1.8.5
		 */
		public static function send_telegram_message_via_api( ?string $bot_token, ?string $channel_id, string $text, array $extra_body = array() ) {

			if ( empty( $bot_token ) ) {
				$bot_token = Telegram::get_telegram_auth_key();
			}

			// Validate token format early.
			if ( ! self::is_valid_token( $bot_token ) ) {
				self::$error = __( 'Invalid Telegram bot token format', 'advanced-analytics' );
				return false;
			}

			if ( empty( $channel_id ) ) {
				$channel_id = Telegram::get_telegram_channel();
			}

			$parse_mode = 'Markdown';

			$url = sprintf(
				'https://api.telegram.org/bot%s/sendMessage',
				\rawurlencode( $bot_token )
			);

			$args = array(
				'body'    => array(
					'chat_id'                  => $channel_id,
					'text'                     => $text,
					'parse_mode'               => '', // parse_mode intentionally disabled.
					'disable_web_page_preview' => true,
				),
				'timeout' => 15,
			);

			// Merge any extra body parameters (e.g. reply_markup).
			if ( ! empty( $extra_body ) && \is_array( $extra_body ) ) {
				$args['body'] = array_merge( $args['body'], $extra_body );
			}

			$response = \wp_remote_post( \esc_url_raw( $url ), $args );

			if ( \is_wp_error( $response ) ) {
				self::$error = $response->get_error_message();
				return false;
			}

			$code = (int) \wp_remote_retrieve_response_code( $response );
			$body = (string) \wp_remote_retrieve_body( $response );
			$data = null;
			if ( '' !== $body ) {
				$data = \json_decode( $body, true );
			}

			if ( 200 !== $code || ! \is_array( $data ) || empty( $data['ok'] ) ) {
				self::$error = $data['description'] ?? sprintf( /* translators: %d is HTTP status code */ __( 'Telegram API error (HTTP %d)', 'advanced-analytics' ), $code );
				return false;
			}

			return true;
		}

		/**
		 * Verify the Telegram token.
		 *
		 * @param string $token - The token to verify.
		 *
		 * @return bool
		 *
		 * @since 1.8.5
		 */
		/**
		 * Verify the Telegram token with the Telegram API.
		 *
		 * @param string $token The bot token to verify.
		 * @return bool|\WP_Error True if valid, false on transport/API error, or WP_Error for config/format issues.
		 */
		public static function verify_telegram_token( $token ) {
			if ( empty( $token ) ) {
				return new \WP_Error( 'telegram_config', 'Bot token not configured' );
			}

			if ( ! self::is_valid_token( (string) $token ) ) {
				return new \WP_Error( 'telegram_config', 'Invalid Telegram bot token format' );
			}

			$api_url = sprintf(
				'https://api.telegram.org/bot%s/getMe',
				\rawurlencode( (string) $token )
			);

			$response = \wp_remote_get( \esc_url_raw( $api_url ), array( 'timeout' => 15 ) );

			if ( \is_wp_error( $response ) ) {
				self::$error = $response->get_error_message();

				return false;
			} elseif ( ! empty( $response['body'] ) ) {
				$code          = (int) \wp_remote_retrieve_response_code( $response );
				$response_data = \json_decode( \wp_remote_retrieve_body( $response ), true );
				if ( 200 === $code && ! empty( $response_data['ok'] ) && ! empty( $response_data['result']['is_bot'] ) && true === $response_data['result']['is_bot'] ) {
					self::$valid_message = $response_data;
					return true;
				}

				self::$error = $response_data['description'] ?? __( 'Unknown Telegram API error', 'advanced-analytics' );
				return false;
			}

			self::$error = 'Unknown error';

			return false;
		}

		/**
		 * Returns the error stored from Telegram.
		 *
		 * @since 1.8.5
		 */
		public static function get_telegram_error(): string {
			$error = self::$error;
			if ( \is_array( self::$error ) ) {
				$error = \wp_json_encode( self::$error );
			}

			return (string) $error;
		}

		/**
		 * Inline message button.
		 *
		 * @param string $text - The message text to send.
		 * @param string $button_text - The button libel text.
		 * @param string $url - The url to open when the button is clicked.
		 *
		 * @return bool
		 *
		 * @since 1.8.5
		 */
		public static function send_with_button( $text, $button_text, $url ) {
			$markup = array(
				'inline_keyboard' => array(
					array(
						array(
							'text' => \sanitize_text_field( $button_text ),
							'url'  => \esc_url_raw( $url ),
						),
					),
				),
			);

			$extra_body = array(
				'reply_markup' => \wp_json_encode( $markup ),
			);

			return self::send_telegram_message_via_api( null, null, $text, $extra_body );
		}
	}
}
