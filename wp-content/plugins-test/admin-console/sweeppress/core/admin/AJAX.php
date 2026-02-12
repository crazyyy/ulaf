<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\Plugin\SweepPress\Basic\Results;
use Dev4Press\Plugin\SweepPress\Basic\Sweep;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AJAX {
	public function __construct() {
		add_action( 'wp_ajax_sweeppress-run-auto', array( $this, 'auto' ) );
		add_action( 'wp_ajax_sweeppress-run-quick', array( $this, 'quick' ) );
		add_action( 'wp_ajax_sweeppress-run-sweep', array( $this, 'sweep' ) );
		add_action( 'wp_ajax_sweeppress-manual-estimate', array( $this, 'estimate' ) );
	}

	public static function instance() : AJAX {
		static $instance = null;

		if ( ! isset( $instance ) ) {
			$instance = new AJAX();
		}

		return $instance;
	}

	public function auto() {
		wp_raise_memory_limit();

		@set_time_limit( 3000 );

		$_nonce = $_REQUEST['_wpnonce'] ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput

		if ( empty( $_nonce ) || ! wp_verify_nonce( $_nonce, 'sweeppress-dashboard-auto-sweep' ) ) {
			$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
		} else if ( ! current_user_can( 'activate_plugins' ) ) {
			$error = new WP_Error( 401, __( 'Unauthorized Request', 'sweeppress' ) );
		} else {
			$results = sweeppress_core()->auto_sweep( 'auto' );

			status_header( 200 );
			die( $this->_render_results_as_html( $results ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( is_wp_error( $error ) ) {
			status_header( $error->get_error_code(), $error->get_error_message() );

			die( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function quick() {
		$this->_the_sweeper( 'sweeppress-dashboard-quick-sweep', 'quick' );
	}

	public function sweep() {
		$this->_the_sweeper( 'sweeppress-sweep-panel-sweeper', 'panel' );
	}

	public function estimate() {
		wp_raise_memory_limit();

		@set_time_limit( 3000 );

		$_sweep = $_REQUEST['sweeper'] ? sanitize_key( $_REQUEST['sweeper'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		$_cache = $_REQUEST['cache'] ? sanitize_key( $_REQUEST['cache'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		$_nonce = $_REQUEST['_wpnonce'] ? sanitize_key( $_REQUEST['_wpnonce'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput

		if ( empty( $_sweep ) || empty( $_nonce ) ) {
			$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
		} else if ( ! wp_verify_nonce( $_nonce, 'sweeppress-run-estimate-' . $_sweep ) ) {
			$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
		} else if ( ! current_user_can( 'activate_plugins' ) ) {
			$error = new WP_Error( 401, __( 'Unauthorized Request', 'sweeppress' ) );
		} else {
			if ( $_cache == 'clear' ) {
				sweeppress_settings()->delete_sweeper_cache( $_sweep );
			}

			$sweeper = Sweep::instance()->sweeper( $_sweep );

			if ( $sweeper ) {
				$force_cache = true;

				include SWEEPPRESS_PATH . 'forms/misc-sweep-single.php';

				die();
			} else {
				$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
			}
		}

		if ( is_wp_error( $error ) ) {
			status_header( $error->get_error_code(), $error->get_error_message() );

			die( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	private function _the_sweeper( $nonce_action, $source ) {
		wp_raise_memory_limit();

		@set_time_limit( 3000 );

		$request = $_REQUEST['sweeppress'] ? sweeppress_sanitize_keys_based_array( (array) $_REQUEST['sweeppress'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput

		if ( empty( $request ) ) {
			$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
		} else {
			$_nonce = $request['nonce'] ?? '';

			if ( empty( $_nonce ) || ! wp_verify_nonce( $_nonce, $nonce_action ) ) {
				$error = new WP_Error( 400, __( 'Invalid Request', 'sweeppress' ) );
			} else if ( ! current_user_can( 'activate_plugins' ) ) {
				$error = new WP_Error( 401, __( 'Unauthorized Request', 'sweeppress' ) );
			} else {
				$args    = $this->_prepare_sweeper_args( $request );
				$results = sweeppress_core()->sweep( $args, $source );

				status_header( 200 );
				die( $this->_render_results_as_html( $results ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.ValidatedSanitizedInput
			}
		}

		if ( is_wp_error( $error ) ) {
			status_header( $error->get_error_code(), $error->get_error_message() );

			die( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	private function _prepare_sweeper_args( $request ) : array {
		$sweeper = $request['sweeper'] ?? array();
		$args    = array();

		foreach ( $sweeper as $sweepers ) {
			foreach ( $sweepers as $sweep => $items ) {
				if ( Sweep::instance()->is_sweeper_valid( $sweep ) ) {
					if ( ! isset( $args[ $sweep ] ) ) {
						$args[ $sweep ] = array();
					}

					foreach ( $items as $item => $val ) {
						if ( $val == 'sweep' ) {
							$args[ $sweep ][] = $item;
						}
					}
				}
			}
		}

		$args = array_filter( $args );

		foreach ( $args as $sweep => &$items ) {
			$items = array_unique( $items );
			$items = array_filter( $items );
			$items = array_values( $items );

			if ( count( $items ) == 1 && $items[0] == $sweep ) {
				$items = true;
			}
		}

		return $args;
	}

	private function _render_results_as_html( array $results ) : string {
		return Results::instance()->as_html( $results );
	}
}
