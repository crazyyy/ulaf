<?php

namespace SolidWP\Mail\Service;

use PHPMailer\PHPMailer\PHPMailer;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\StellarWP\Validation\Validator;

/**
 * Class ConnectionService
 *
 * This class handles the business logic for managing SMTP connections.
 *
 * @package Solid_SMTP\Service
 */
class ConnectionService {

	/**
	 * The repository for managing SMTP mailers.
	 *
	 * @var ProvidersRepository
	 */
	protected ProvidersRepository $providers_repository;

	/**
	 * Constructor for the class.
	 *
	 * @param ProvidersRepository $providers_repository The repository instance for managing providers.
	 */
	public function __construct( ProvidersRepository $providers_repository ) {
		$this->providers_repository = $providers_repository;
	}

	/**
	 * Saves a new SMTP connection.
	 *
	 * @param array $data The data for the new SMTP connection.
	 *
	 * @return array|\WP_Error The result of the save operation, either the connector data or validation errors.
	 */
	public function save_connection( array $data ) {
		$input     = $data;
		$name      = $data['name'] ?? '';
		$is_update = ! empty( $data['id'] );

		if ( $is_update ) {
			$model = $this->providers_repository->get_provider_by_id( $data['id'] );
			if ( ! $model ) {
				$wp_error = new \WP_Error();
				$wp_error->add( 'connector_not_found', __( 'Connector not found.', 'wp-smtp' ), [ 'status' => 404 ] );
				return $wp_error;
			}
			// For updates, merge with existing data
			$existing_data = $model->to_array();
			$data          = array_merge( $existing_data, $data );
			
			// Use existing name if not provided
			if ( empty( $name ) ) {
				$name         = $model->get_name();
				$data['name'] = $name;
			}
		} else {
			// For new connectors, name is required
			if ( empty( $name ) ) {
				$wp_error = new \WP_Error();
				$wp_error->add( 'missing_name', __( 'Connector name is required.', 'wp-smtp' ), [ 'status' => 400 ] );
				return $wp_error;
			}
			$model = $this->providers_repository->factory( $name );
		}

		if ( ! is_object( $model ) ) {
			$wp_error = new \WP_Error();
			$wp_error->add( 'invalid_connector_type', __( 'Invalid connector type.', 'wp-smtp' ), [ 'status' => 400 ] );
			return $wp_error;
		}

		$model->process_data( $data );

		if ( $model->validation() ) {
			$this->providers_repository->save( $model );

			if ( isset( $input['is_active'] ) ) {
				$model->set_is_active( (bool) $input['is_active'] );
				if ( ! $model->is_active() ) {
					// Inactive connection can't be a default
					$model->set_is_default( false );
				} elseif ( ! $this->providers_repository->get_default_provider() ) {
					// If we activate the connection and there is no default connection, let's make this connection default
					$model->set_is_default( true );
					$this->providers_repository->set_default_provider( $model->get_id() );
				}
			}

			if ( isset( $input['is_default'] ) ) {
				$model->set_is_default( (bool) $input['is_default'] );
				if ( $model->is_default() ) {
					// Default connection must be active
					$model->set_is_active( true );
					$this->providers_repository->set_default_provider( $model->get_id() );
				}
			}

			$this->providers_repository->save( $model );
			return $model->to_array();
		}

		// Convert validation errors to WP_Error.
		$errors   = $model->get_errors();
		$wp_error = new \WP_Error( 'invalid_connection_data', 'Invalid connection data.', [ 'status' => 400 ] );

		foreach ( $errors as $field => $error_message ) {
			$wp_error->add( $field, $error_message );
		}

		return $wp_error;
	}

	/**
	 * Deletes an SMTP connection.
	 *
	 * @param string $provider_id The ID of the provider to delete.
	 *
	 * @return array The updated list of providers.
	 */
	public function delete_connection( string $provider_id ): array {
		$this->providers_repository->delete_provider_by_id( $provider_id );

		return $this->providers_repository->get_all_providers_as_array();
	}

	/**
	 * Validates the test email input.
	 *
	 * @param array $data The data for the test email.
	 *
	 * @return bool|array The result of the validation, either validated data or errors.
	 */
	public function validate_test_email_input( array $data ) {
		$rules = [
			'from_email' => [ 'optional', 'email' ],
			'to_email'   => [ 'required', 'email' ],
			'subject'    => [ 'required' ],
			'message'    => [ 'required' ],
		];

		$labels = [
			'from_email' => 'From Email',
			'to_email'   => 'To Email',
			'subject'    => 'Subject',
			'message'    => 'Message',
		];

		$validator = new Validator( $rules, $data, $labels );

		if ( $validator->passes() ) {
			return true;
		}

		return $validator->errors();
	}

	/**
	 * Tests the SMTP connection using the provided connector.
	 *
	 * @param ConnectorSMTP $connector An instance of the ConnectorSMTP class containing SMTP credentials and settings.
	 *
	 * @global PHPMailer $phpmailer The PHPMailer instance used to send emails.
	 *
	 * @return bool|\WP_Error True if the connection is successful; WP_Error if the connection fails or an exception is thrown.
	 */
	public function test_smtp_connection( ConnectorSMTP $connector ) {
		global $phpmailer;

		if ( ! ( $phpmailer instanceof PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new PHPMailer( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		try {
			// SMTP configuration
			$phpmailer->isSMTP();
			$phpmailer->Timeout    = 5;
			$phpmailer->Host       = $connector->get_host();
			$phpmailer->SMTPAuth   = $connector->is_authentication();
			$phpmailer->Username   = $connector->get_username();
			$phpmailer->Password   = $connector->get_password();
			$phpmailer->SMTPSecure = $connector->get_secure();
			$phpmailer->Port       = $connector->get_port();

			// Attempt to connect to SMTP server
			if ( $phpmailer->smtpConnect() ) {
				$phpmailer->smtpClose();

				return true;
			} else {
				return new \WP_Error( 'smtp_creds_fail', $phpmailer->ErrorInfo );
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'smtp_exception', $e->getMessage() );
		}
	}
}
