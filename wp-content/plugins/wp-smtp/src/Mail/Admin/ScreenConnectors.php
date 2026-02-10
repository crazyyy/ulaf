<?php
/**
 * ConnectionScreenController.php
 *
 * This file contains the ConnectionScreenController class which handles the connection screen logic
 * for the Solid SMTP plugin.
 *
 * @package Solid_SMTP\Controller
 */

namespace SolidWP\Mail\Admin;

use SolidWP\Mail\Container;
use SolidWP\Mail\AbstractController;
use SolidWP\Mail\Hooks\PHPMailer;
use SolidWP\Mail\Service\ConnectionService;

/**
 * Class ConnectionScreenController
 *
 * Handles the connection screen logic for the Solid SMTP plugin.
 */
class ScreenConnectors extends AbstractController {

	/**
	 * Nonce name for this screen.
	 *
	 * @var string
	 */
	protected string $nonce_name = 'solidwp_mail_connections_nonce';

	/**
	 * Store the email error if any.
	 *
	 * @var string
	 */
	protected string $email_error = '';

	/**
	 * The service for managing SMTP connections.
	 *
	 * @var ConnectionService
	 */
	protected ConnectionService $connection_service;

	/**
	 * Container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Holds the test from email if provided.
	 *
	 * @var string|null
	 */
	private ?string $test_from_email = null;

	/**
	 * ConnectionScreenController constructor.
	 *
	 * @param ConnectionService $connection_service The service for managing SMTP connections.
	 */
	public function __construct( ConnectionService $connection_service, Container $container ) {
		$this->connection_service = $connection_service;
		$this->container          = $container;
	}

	/**
	 * Registers the AJAX hooks for the connection screen.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// record error for debug.
		add_action( 'wp_mail_failed', [ $this, 'record_error' ] );

		// to test different outgoing emails
		add_filter( 'wp_mail_from', [ $this, 'configure_outgoing_test_from_email' ] );

		// ajax functions.
		add_action( 'wp_ajax_solidwp_mail_send_test_email', [ $this, 'send_test_email' ] );
	}

	/**
	 * Records an error message.
	 *
	 * This method handles recording an error message from a `WP_Error` object.
	 * It extracts the error message from the `WP_Error` object and stores it in
	 * the `email_error` property.
	 *
	 * @param \WP_Error $wp_error The `WP_Error` object containing the error message.
	 *
	 * @return void
	 */
	public function record_error( \WP_Error $wp_error ) {
		$this->email_error = $wp_error->get_error_message();
	}

	/**
	 * Sends a test email.
	 *
	 * This method handles the AJAX request to send a test email. It validates the input,
	 * attempts to send the email using the `wp_mail` function, and returns a JSON response
	 * indicating success or failure.
	 *
	 * @return void
	 */
	public function send_test_email() {
		// Check if the current user has permission to perform this action.
		if ( ! $this->able_to_perform( 'send_test_email' ) ) {
			$this->bail_out_generic_error( __( 'User cannot send test emails.', 'LION' ) );
		}

		// Sanitize and retrieve input data.
		$data = [
			'from_email' => $this->get_and_sanitize_input( 'from_email' ),
			'to_email'   => $this->get_and_sanitize_input( 'to_email' ),
			'subject'    => $this->get_and_sanitize_input( 'subject' ),
			'message'    => $this->get_and_sanitize_textarea( 'message' ),
		];

		$result = $this->connection_service->validate_test_email_input( $data );
		if ( is_array( $result ) ) {
			wp_send_json_error(
				[
					'validation' => $result,
				]
			);
		}

		if ( $data['from_email'] ) {
			$this->test_from_email = $data['from_email'];
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
		$sent = wp_mail( $data['to_email'], $data['subject'], $data['message'] );

		$this->test_from_email = null;

		if ( ! PHPMailer::is_solid_mail_configured() ) {
			wp_send_json_error(
				[
					'message' => __( 'Solid Mail did not process the test email.', 'LION' ),
				]
			);
		}

		// Return a JSON response indicating success or failure.
		if ( $sent ) {
			wp_send_json_success( [ 'message' => __( 'Test email sent successfully.', 'LION' ) ] );
		} else {
			$wp_error = $this->container->get( 'phpmailer_send_error' );
			if ( is_wp_error( $wp_error ) ) {
				// error found, send more detailed version.
				$error_message = $wp_error->get_error_message();
				wp_send_json_error(
					[
						/* translators: %s: PHPMailer error */
						'message' => sprintf( __( 'Failed to send test email. Error: %s', 'LION' ), $error_message ),
					]
				);
			}
			// falling back.
			wp_send_json_error(
				[
					'message' => __( 'Failed to send test email.', 'LION' ),
				]
			);
		}
	}

	/**
	 * Use a test "from email" if provided by the user or preserve the value provided by WordPress.
	 *
	 * @param $wordpress_provided_from_email
	 *
	 * @return string
	 */
	public function configure_outgoing_test_from_email( $wordpress_provided_from_email ): string {
		return $this->test_from_email ?? $wordpress_provided_from_email;
	}
}
