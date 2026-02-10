<?php
/**
 * File: Provider.php
 * Description: Abstract class representing an SMTP provider.
 *
 * @since      1.3.0
 * @package    Solid_SMTP
 */

namespace SolidWP\Mail\Connectors;

use SolidWP\Mail\StellarWP\Validation\Validator;

/**
 * Represents an SMTP provider.
 *
 * @since 1.3.0
 *
 * @package Solid_SMTP\Providers
 */
class ConnectorSMTP {

	/**
	 * A unique id for this provider.
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * The provider name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Provider small description.
	 *
	 * @var string
	 */
	protected string $description;

	/**
	 * The email address used as the sender's address.
	 *
	 * @var string
	 */
	protected string $from_email;

	/**
	 * The name used as the sender's name.
	 *
	 * @var string
	 */
	protected string $from_name;

	/**
	 * The SMTP server hostname or IP address.
	 *
	 * @var string
	 */
	protected string $host;

	/**
	 * The port number for the SMTP server.
	 *
	 * @var string
	 */
	protected string $port;

	/**
	 * Whether the SMTP server requires authentication. Can be yes|no
	 *
	 * @var string
	 */
	protected string $authentication;

	/**
	 * The username for authenticating with the SMTP server.
	 *
	 * @var string
	 */
	protected string $smtp_username;

	/**
	 * The password for authenticating with the SMTP server.
	 *
	 * @var string
	 */
	protected string $smtp_password;

	/**
	 * Whether to disable logging for this provider.
	 *
	 * @var bool
	 */
	protected bool $disable_logs = false;

	/**
	 * The PHPMailer Secure, can be '', ssl or tls.
	 *
	 * @var string
	 */
	protected string $secure;

	/**
	 * If this connection is active.
	 *
	 * @var bool
	 */
	protected bool $is_active = false;

	/**
	 * If this connection is set as default.
	 *
	 * @var bool
	 */
	protected bool $is_default = false;

	/**
	 * The method used for message delivery (e.g., 'smtp', 'api')
	 *
	 * @var string
	 */
	protected string $delivery_method = 'smtp';

	/**
	 * Gets the current delivery method for this connector
	 *
	 * @return string The delivery method being used ('smtp', 'api', etc.)
	 */
	public function isAPI(): string {
		return $this->delivery_method === 'api';
	}

	/**
	 * Holds validation errors.
	 *
	 * @var array
	 */
	protected array $validation_errors = [];

	/**
	 * Constructor.
	 *
	 * @param array $data
	 */
	public function __construct( array $data = [] ) {
		$this->process_data( $data );
		// if the load data is empty, generate a new id.

		$this->id         = empty( $data['id'] ) ? uniqid() : $data['id'];
		$this->is_active  = filter_var( $data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN );
		$this->is_default = filter_var( $data['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Return the ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function is_active(): bool {
		return $this->is_active;
	}

	/**
	 * Sets the email address used as the sender's address.
	 *
	 * @param string $from_email The email address used as the sender's address.
	 *
	 * @return void
	 */
	public function set_from_email( string $from_email ): void {
		$this->from_email = $from_email;
	}

	/**
	 * Gets the email address used as the sender's address.
	 *
	 * @return string The email address used as the sender's address.
	 */
	public function get_from_email(): string {
		return $this->from_email;
	}

	/**
	 * @return string
	 */
	public function get_secure(): string {
		return $this->secure;
	}

	/**
	 * Sets the name used as the sender's name.
	 *
	 * @param string $from_name The name used as the sender's name.
	 *
	 * @return void
	 */
	public function set_from_name( string $from_name ): void {
		$this->from_name = $from_name;
	}

	/**
	 * Gets the name used as the sender's name.
	 *
	 * @return string The name used as the sender's name.
	 */
	public function get_from_name(): string {
		return $this->from_name;
	}

	/**
	 * Sets the SMTP server hostname or IP address.
	 *
	 * @param string $host The SMTP server hostname or IP address.
	 *
	 * @return void
	 */
	public function set_host( string $host ): void {
		$this->host = $host;
	}

	/**
	 * Gets the SMTP server hostname or IP address.
	 *
	 * @return string The SMTP server hostname or IP address.
	 */
	public function get_host(): string {
		return $this->host;
	}

	/**
	 * Sets the port number for the SMTP server.
	 *
	 * @param string $port The port number for the SMTP server.
	 *
	 * @return void
	 */
	public function set_port( string $port ): void {
		$this->port = $port;
	}

	/**
	 * Gets the port number for the SMTP server.
	 *
	 * @return string The port number for the SMTP server.
	 */
	public function get_port(): string {
		return $this->port;
	}

	/**
	 * Sets whether the SMTP server requires authentication.
	 *
	 * @return void
	 */
	public function set_authenticate(): void {
		$this->authentication = 'yes';
	}

	/**
	 * Gets whether the SMTP server requires authentication.
	 *
	 * @return bool Whether the SMTP server requires authentication.
	 */
	public function is_authentication(): bool {
		return $this->authentication === 'yes';
	}

	/**
	 * Sets the username for authenticating with the SMTP server.
	 *
	 * @param string $username The username for authenticating with the SMTP server.
	 *
	 * @return void
	 */
	public function set_username( string $username ): void {
		$this->smtp_username = $username;
	}

	/**
	 * Gets the username for authenticating with the SMTP server.
	 *
	 * @return string The username for authenticating with the SMTP server.
	 */
	public function get_username(): string {
		return $this->smtp_username;
	}

	/**
	 * Sets the password for authenticating with the SMTP server.
	 *
	 * @param string $password The password for authenticating with the SMTP server.
	 *
	 * @return void
	 */
	public function set_password( string $password ): void {
		$this->smtp_password = $password;
	}

	/**
	 * Gets the password for authenticating with the SMTP server.
	 *
	 * @return string The password for authenticating with the SMTP server.
	 */
	public function get_password(): string {
		return $this->smtp_password;
	}

	/**
	 * @param bool $is_active
	 */
	public function set_is_active( bool $is_active ): void {
		$this->is_active = $is_active;
	}

	/**
	 * @return bool
	 */
	public function is_default(): bool {
		return $this->is_default;
	}

	/**
	 * @param bool $is_default
	 */
	public function set_is_default( bool $is_default ): void {
		$this->is_default = $is_default;
	}

	/**
	 * Sets whether to disable logging for this provider.
	 *
	 * @param bool $disable_logs Whether to disable logging for this provider.
	 *
	 * @return void
	 */
	public function set_disable_logs( bool $disable_logs ): void {
		$this->disable_logs = $disable_logs;
	}

	/**
	 * Gets whether to disable logging for this provider.
	 *
	 * @return bool Whether to disable logging for this provider.
	 */
	public function get_disable_logs(): bool {
		return $this->disable_logs;
	}

	/**
	 * Validates the input data.
	 *
	 * This method validates the input data for the SMTP provider using the Validator class.
	 * If validation passes, it returns true. If validation fails, it returns an array of validation errors.
	 *
	 * @return bool|array True if validation succeeds, an array of validation errors otherwise.
	 */
	public function validation(): bool {
		$data   = $this->to_array();
		$labels = [
			'name'           => __( 'Name', 'wp-smtp' ),
			'from_email'     => __( 'From Email', 'wp-smtp' ),
			'from_name'      => __( 'From Name', 'wp-smtp' ),
			'smtp_host'      => __( 'SMTP Host', 'wp-smtp' ),
			'smtp_port'      => __( 'SMTP Port', 'wp-smtp' ),
			'authentication' => __( 'SMTP Authentication', 'wp-smtp' ),
			'smtp_username'  => __( 'SMTP Username', 'wp-smtp' ),
			'smtp_password'  => __( 'SMTP Password', 'wp-smtp' ),
		];

		$rules = [
			'from_email' => [ 'required', 'email' ],
			'from_name'  => [ 'required' ],
			'smtp_host'  => [ 'required' ],
			'smtp_port'  => [ 'required', 'numeric' ],
		];

		if ( $this->is_authentication() ) {
			$rules = array_merge(
				$rules,
				[
					'smtp_username' => [ 'required' ],
					'smtp_password' => [ 'required' ],
				] 
			);
		}

		$validator = new Validator( $rules, $data, $labels );

		if ( true === $validator->passes() ) {
			return true;
		}

		$this->validation_errors = $validator->errors();

		return false;
	}

	/**
	 * Retrieves the validation errors.
	 *
	 * @return array An array of validation errors.
	 */
	public function get_errors(): array {
		return $this->validation_errors;
	}

	/**
	 * Processes the provided data and sets properties if possible.
	 *
	 * @param array $data The data to process.
	 *
	 * @return void
	 */
	public function process_data( array $data ) {
		$this->name           = $data['name'] ?? '';
		$this->description    = '';
		$this->from_email     = $data['from_email'] ?? '';
		$this->from_name      = $data['from_name'] ?? '';
		$this->host           = $data['smtp_host'] ?? '';
		$this->port           = $data['smtp_port'] ?? '';
		$this->authentication = $data['smtp_auth'] ?? 'no';
		$this->smtp_username  = $data['smtp_username'] ?? '';
		if ( ! empty( $data['smtp_password'] ) ) {
			$this->smtp_password = $data['smtp_password'];
		} else {
			$this->smtp_password = '';
		}
		$this->disable_logs = $data['disable_logs'] ?? false;
		$this->secure       = $data['smtp_secure'] ?? '';
	}

	/**
	 * Converts the ProviderSMTP object to an associative array.
	 *
	 * @return array The ProviderSMTP object properties as an associative array.
	 */
	public function to_array(): array {
		return [
			'id'            => $this->id,
			'name'          => $this->name,
			'description'   => $this->description,
			'from_email'    => $this->from_email,
			'from_name'     => $this->from_name,
			'smtp_host'     => $this->host,
			'smtp_port'     => absint( $this->port ),
			'smtp_auth'     => $this->authentication,
			'smtp_username' => $this->smtp_username,
			'smtp_password' => $this->smtp_password,
			'disable_logs'  => $this->disable_logs,
			'smtp_secure'   => $this->secure,
			'is_active'     => $this->is_active,
			'is_default'    => $this->is_default,
		];
	}
}
