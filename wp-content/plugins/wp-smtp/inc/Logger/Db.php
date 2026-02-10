<?php

namespace WPSMTP\Logger;

class Db {

	private $db;

	private $table;

	private static $instance;

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	private function __construct() {
		global $wpdb;

		$this->db    = $wpdb;
		$this->table = $wpdb->prefix . 'wpsmtp_logs';
	}

	public function insert( $data ) {
		$prepared   = $this->prepare_for_database( $data );
		$result_set = $this->db->insert(
			$this->table,
			$prepared,
			array_fill( 0, count( $prepared ), '%s' )
		);

		if ( ! $result_set ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'WP SMTP Log insert error: ' . $this->db->last_error );

			return false;
		}

		return $this->db->insert_id;
	}

	public function update( $data, $where = [] ): void {
		$prepared = $this->prepare_for_database( $data );

		$this->db->update(
			$this->table,
			$prepared,
			$where,
			array_fill( 0, count( $prepared ), '%s' ),
			[ '%d' ]
		);
	}

	/**
	 * This function takes the raw values passed to {@see wp_mail()}
	 * and applies similar normalization to what Core does.
	 *
	 * @param array $raw
	 *
	 * @return array
	 */
	protected function prepare_for_database( array $raw ) {
		$to      = $raw['to'] ?? [];
		$headers = $raw['headers'] ?? [];

		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}

		if ( ! is_array( $headers ) ) {
			$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
		}

		$headers = $this->parse_headers( $headers );

		return [
			'to'            => wp_json_encode( $to ),
			'subject'       => (string) ( $raw['subject'] ?? '' ),
			'message'       => (string) ( $raw['message'] ?? '' ),
			'headers'       => wp_json_encode( $headers ),
			'content_type'  => (string) ( $raw['content_type'] ?? '' ),
			'error'         => isset( $raw['error'] ) ? sanitize_text_field( $raw['error'] ) : '',
			'connection_id' => (string) ( $raw['connection_id'] ?? '' ),
			'from_email'    => (string) ( $raw['from_email'] ?? '' ),
			'from_name'     => (string) ( $raw['from_name'] ?? '' ),
		];
	}

	protected function parse_headers( array $headers ) {
		$parsed = [];

		foreach ( $headers as $header ) {
			if ( strpos( $header, ':' ) === false ) {
				continue;
			}

			[ $name, $content ] = explode( ':', trim( $header ), 2 );

			$name    = trim( $name );
			$content = trim( $content );

			$parsed[ strtolower( $name ) ] = $content;
		}

		return $parsed;
	}
}
