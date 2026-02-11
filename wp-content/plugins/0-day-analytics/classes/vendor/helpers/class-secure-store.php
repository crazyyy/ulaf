<?php
/**
 * Secure_Store helper: provides lightweight encryption/decryption for sensitive plugin options.
 *
 * Uses libsodium when available, otherwise falls back to OpenSSL AES-256-GCM.
 * Key material is derived from WordPress salts to avoid storing a separate key.
 *
 * @package advanced-analytics
 *
 * @since 4.1.1
 */

declare(strict_types=1);

namespace ADVAN\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secure_Store class
 */
class Secure_Store {

	/**
	 * Prefix used to mark encrypted strings
	 *
	 * @var string
	 *
	 * @since 4.1.0
	 */
	private const PREFIX = 'enc:v1:'; // versioned for future upgrades.

	/**
	 * Derive a stable key from WP salts.
	 *
	 * @return string Raw binary key (32 bytes)
	 *
	 * @since 4.1.0
	 */
	private static function get_key(): string {
		$material = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' ) .
			( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '' ) .
			( defined( 'LOGGED_IN_KEY' ) ? LOGGED_IN_KEY : '' ) .
			( defined( 'NONCE_KEY' ) ? NONCE_KEY : '' ) .
			( defined( 'AUTH_SALT' ) ? AUTH_SALT : '' ) .
			( defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '' ) .
			( defined( 'LOGGED_IN_SALT' ) ? LOGGED_IN_SALT : '' ) .
			( defined( 'NONCE_SALT' ) ? NONCE_SALT : '' );

		// Hash material to fixed size key.
		if ( function_exists( 'sodium_crypto_generichash' ) ) {
			return sodium_crypto_generichash( $material, '', 32 );
		}
		return hash( 'sha256', $material, true ); // 32 bytes.
	}

	/**
	 * Check if a value is already encrypted by this helper.
	 *
	 * @param string|null $value Value to check.
	 *
	 * @return bool
	 *
	 * @since 4.1.0
	 */
	public static function is_encrypted( ?string $value ): bool {
		return is_string( $value ) && strpos( $value, self::PREFIX ) === 0;
	}

	/**
	 * Encrypt plaintext.
	 *
	 * @param string $plaintext Plain value.
	 *
	 * @return string Encrypted representation or original if empty.
	 *
	 * @since 4.1.0
	 */
	public static function encrypt( string $plaintext ): string {
		if ( '' === $plaintext ) {
			return '';
		}
		// Don't double encrypt.
		if ( self::is_encrypted( $plaintext ) ) {
			return $plaintext;
		}

		$key = self::get_key();

		if ( function_exists( 'sodium_crypto_secretbox' ) ) {
			$nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$ciphertext = sodium_crypto_secretbox( $plaintext, $nonce, $key );
			$payload    = array(
				'm' => 'sodium', // method.
				'n' => base64_encode( $nonce ),
				'c' => base64_encode( $ciphertext ),
			);
			return self::PREFIX . base64_encode( wp_json_encode( $payload ) );
		}

		// OpenSSL fallback AES-256-GCM.
		$iv_length  = 12; // Recommended length for GCM.
		$iv         = random_bytes( $iv_length );
		$tag        = '';
		$cipher     = 'aes-256-gcm';
		$ciphertext = openssl_encrypt( $plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag );
		if ( false === $ciphertext ) {
			// Fallback: return plaintext (better than storing unusable data). Could log error.
			return $plaintext;
		}
		$payload = array(
			'm' => 'openssl',
			'a' => base64_encode( $ciphertext ),
			'i' => base64_encode( $iv ),
			't' => base64_encode( $tag ),
			'c' => $cipher,
		);
		return self::PREFIX . base64_encode( wp_json_encode( $payload ) );
	}

	/**
	 * Decrypt a value if encrypted; otherwise return as-is.
	 *
	 * @param string|null $value Value from options.
	 *
	 * @return string Decrypted plaintext or original.
	 *
	 * @since 4.1.0
	 */
	public static function decrypt( ?string $value ): string {
		if ( ! self::is_encrypted( $value ) ) {
			return (string) $value;
		}
		$encoded = substr( (string) $value, strlen( self::PREFIX ) );
		$json    = base64_decode( $encoded, true );
		if ( false === $json ) {
			return (string) $value; // Corrupted.
		}
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return (string) $value;
		}
		$key = self::get_key();
		if ( isset( $data['m'] ) && 'sodium' === $data['m'] && isset( $data['n'], $data['c'] ) && function_exists( 'sodium_crypto_secretbox_open' ) ) {
			$nonce      = base64_decode( $data['n'], true );
			$ciphertext = base64_decode( $data['c'], true );
			if ( false === $nonce || false === $ciphertext ) {
				return (string) $value;
			}
			$plain = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
			return ( false === $plain ) ? (string) $value : $plain;
		}
		if ( isset( $data['m'] ) && 'openssl' === $data['m'] && isset( $data['a'], $data['i'], $data['t'], $data['c'] ) ) {
			$ciphertext = base64_decode( $data['a'], true );
			$iv         = base64_decode( $data['i'], true );
			$tag        = base64_decode( $data['t'], true );
			if ( false === $ciphertext || false === $iv || false === $tag ) {
				return (string) $value;
			}
			$plain = openssl_decrypt( $ciphertext, $data['c'], $key, OPENSSL_RAW_DATA, $iv, $tag );
			return ( false === $plain ) ? (string) $value : $plain;
		}
		return (string) $value; // Unknown method.
	}

	/**
	 * Encrypt sensitive fields inside the options array (in-place) before storage.
	 *
	 * @param array $options Reference to full options array.
	 *
	 * @return void
	 *
	 * @since 4.1.0
	 */
	public static function encrypt_sensitive_fields( array &$options ): void {
		// SMTP password.
		if ( isset( $options['smtp_password'] ) && is_string( $options['smtp_password'] ) && '' !== $options['smtp_password'] ) {
			$options['smtp_password'] = self::encrypt( $options['smtp_password'] );
		}
		// Slack token.
		if ( isset( $options['slack_notifications']['all']['auth_token'] ) && is_string( $options['slack_notifications']['all']['auth_token'] ) && '' !== $options['slack_notifications']['all']['auth_token'] ) {
			$options['slack_notifications']['all']['auth_token'] = self::encrypt( $options['slack_notifications']['all']['auth_token'] );
		}
		// Telegram token.
		if ( isset( $options['telegram_notifications']['all']['auth_token'] ) && is_string( $options['telegram_notifications']['all']['auth_token'] ) && '' !== $options['telegram_notifications']['all']['auth_token'] ) {
			$options['telegram_notifications']['all']['auth_token'] = self::encrypt( $options['telegram_notifications']['all']['auth_token'] );
		}
	}

	/**
	 * Decrypt sensitive fields (in-place) for runtime use.
	 *
	 * @param array $options Reference to options array.
	 *
	 * @return bool True if any field migrated (was plaintext and now encrypted in DB trigger needed).
	 *
	 * @since 4.1.0
	 */
	public static function decrypt_sensitive_fields( array &$options ): bool {
		$migrated = false;

		// SMTP password.
		if ( isset( $options['smtp_password'] ) && is_string( $options['smtp_password'] ) && '' !== $options['smtp_password'] ) {
			if ( ! self::is_encrypted( $options['smtp_password'] ) ) {
				$migrated = true; // Will need re-store.
			} else {
				$options['smtp_password'] = self::decrypt( $options['smtp_password'] );
			}
		}
		// Slack token.
		if ( isset( $options['slack_notifications']['all']['auth_token'] ) && is_string( $options['slack_notifications']['all']['auth_token'] ) && '' !== $options['slack_notifications']['all']['auth_token'] ) {
			if ( ! self::is_encrypted( $options['slack_notifications']['all']['auth_token'] ) ) {
				$migrated = true;
			} else {
				$options['slack_notifications']['all']['auth_token'] = self::decrypt( $options['slack_notifications']['all']['auth_token'] );
			}
		}
		// Telegram token.
		if ( isset( $options['telegram_notifications']['all']['auth_token'] ) && is_string( $options['telegram_notifications']['all']['auth_token'] ) && '' !== $options['telegram_notifications']['all']['auth_token'] ) {
			if ( ! self::is_encrypted( $options['telegram_notifications']['all']['auth_token'] ) ) {
				$migrated = true;
			} else {
				$options['telegram_notifications']['all']['auth_token'] = self::decrypt( $options['telegram_notifications']['all']['auth_token'] );
			}
		}
		return $migrated;
	}
}
