<?php
/**
 * Helper class for Data Debug Tool plugin
 *
 * @package data-debug-tool
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class DATA_DEBUG_TOOL_HELPER
 * Handles all REST API functionality for the Data Debug Tool plugin
 *
 * @since 0.1.0
 */
class DATA_DEBUG_TOOL_HELPER {

	/**
	 * Sanitize data
	 *
	 * @param string|array $data
	 * @param string       $type
	 * @return string
	 */
	public function sanitize_data( $data, $type = 'text' ) {
		if ( ! empty( $data ) ) {
			$data = wp_unslash( $data );
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data[ $key ] = $this->sanitize_data( $value, $type );
				}
			} elseif ( 'text' === $type ) {
				$data = sanitize_text_field( $data );
			} elseif ( 'number' === $type ) {
				$data = intval( sanitize_key( $data ) );
			} elseif ( 'textarea' === $type ) {
				$data = sanitize_textarea_field( $data );
			} elseif ( 'post' === $type ) {
				$data = wp_kses_post( $data );
			} else {
				$data = sanitize_text_field( $data );
			}
		}

		return $data;
	}


	/**
	 * Check if id is invalid
	 *
	 * @param array        $data
	 * @param array|string $ids
	 * @return boolean
	 */
	public function is_invalid_for_id( $data, $ids = array() ) {
		if ( empty( $data ) || empty( $ids ) ) {
			return true;
		}
		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				if ( ! is_numeric( $id ) || empty( $data[ $id ] ) || 0 >= intval( $data[ $id ] ) ) {
					return true;
				}
			}
		} elseif ( empty( $data[ $ids ] ) || 0 >= intval( $data[ $ids ] ) ) {
				return true;
		}

		return false;
	}

	/**
	 * Check if string is empty
	 *
	 * @param array        $str_arr
	 * @param array|string $keys
	 * @return boolean
	 */
	public function is_empty_string( $str_arr = array(), $keys = array() ) {
		if ( empty( $str_arr ) || empty( $keys ) ) {
			return true;
		}
		if ( is_array( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( empty( $str_arr[ $key ] ) || ! is_string( $str_arr[ $key ] ) ) {
					return true;
				}
			}
		} elseif ( empty( $str_arr[ $keys ] ) || ! is_string( $str_arr[ $keys ] ) ) {
				return true;
		}

		return false;
	}

	/**
	 * Decode data
	 *
	 * @param string $data Data to decode.
	 * @param array  $params Parameters.
	 * @return string
	 */
	public function decode_data( $data, $params = array() ) {
		if ( empty( $params['is_decode'] ) || empty( $data ) || ! is_string( $data ) ) {
			return $data;
		}

		// Parse blocks
		if ( has_blocks( $data ) ) {
			return parse_blocks( $data );
		}

		// JSON
		$json_decoded = json_decode( $data, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $json_decoded;
		}

		return maybe_unserialize( $data );
	}
}
