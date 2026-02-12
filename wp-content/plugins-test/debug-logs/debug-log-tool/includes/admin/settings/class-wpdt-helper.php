<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPDT_Helper' ) ) :

	final class WPDT_Helper {


		/**
		 * Print a notice if WP Cron is not working
		 *
		 * @return void
		 */
		public static function maybe_show_cron_notice() {
			if ( 'UTC' !== date_default_timezone_get() ) {
				echo '<div class="notice notice-warning wpdt-cron-notice"><p>';
				echo esc_html__( 'Warning: PHP default timezone is not set to UTC. This might affect WP-Cron schedules.', 'debug-log-tool' );
				echo '</p></div>';
			}

			$status = self::check_cron_status();

			if ( is_wp_error( $status ) ) {
				$code = $status->get_error_code();
				$message = $status->get_error_message();
				if ( 'custom_info' === $code ) {
					echo '<div class="notice notice-info wpdt-cron-notice"><p>' . esc_html( $message ) . '</p></div>';
				} else {
					echo '<div class="notice notice-error wpdt-cron-notice"><p>' . esc_html( $message ) . '</p></div>';
				}
			}
		}

		/**
		 * Check if WP Cron is working
		 *
		 * @return bool|WP_Error
		 */
		public static function check_cron_status() {

			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				return new WP_Error( 'custom_info', 'WP-Cron is disabled via DISABLE_WP_CRON constant.' );
			}

			if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
				return new WP_Error( 'custom_info', 'ALTERNATE_WP_CRON is enabled. Cron behavior may differ.' );
			}

			$cached = get_transient( 'wpdt_cron_status_ok' );
			if ( $cached ) {
				return true;
			}

			$key = sprintf( '%.22F', microtime( true ) );
			$url = add_query_arg( 'doing_wp_cron', $key, site_url( 'wp-cron.php' ) );

			$response = wp_remote_post(
				$url,
				array(
					'timeout'   => 3,
					'blocking'  => true,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'cron_error', 'WP-Cron request failed: ' . $response->get_error_message() );
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( $code >= 300 ) {
				return new WP_Error( 'cron_error', 'Unexpected HTTP response code: ' . $code );
			}

			set_transient( 'wpdt_cron_status_ok', 1, 3600 );
			return true;
		}
	}
endif;
