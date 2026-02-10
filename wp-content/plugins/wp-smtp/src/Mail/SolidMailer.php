<?php

namespace SolidWP\Mail;

use LogicException;
use PHPMailer\PHPMailer\Exception;
use SolidWP\Mail\Connectors\ConnectionPool;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Contracts\Api_Connector;
use WP_Error;

/**
 * Extension of PHPMailer that adds API mailing capabilities.
 *
 * This class extends the base PHPMailer functionality to support sending emails
 * through API endpoints in addition to traditional SMTP methods. It provides
 * methods for configuring API connections and formatting email data for API
 * transmission.
 *
 * To support compatibility with WordPress < 6.8, this class should not be used
 * in static or regular context without requiring `inc/wp-phpmailer-compatibility.php` helper.
 *
 * @since   2.1.3
 * @package SolidWP\Mail\Pro
 * @extends \WP_PHPMailer
 */
class SolidMailer extends \WP_PHPMailer {

	/**
	 * Whether to throw exceptions for errors.
	 *
	 * @var bool
	 */
	protected $exceptions = true;

	/**
	 * Pool of connections available for the mail sending.
	 *
	 * @var ConnectionPool | null
	 */
	protected ?ConnectionPool $connection_pool = null;

	/**
	 * @var array The initial settings from wp_mail().
	 */
	private array $initial_settings = [
		'From'         => '',
		'FromName'     => '',
		'ReplyTo'      => [],
		'ReplyToQueue' => [],
	];

	public function __construct() {
		parent::__construct( true );
	}

	/**
	 * Init connection pool for sending emails.
	 *
	 * @param ConnectionPool $connection_pool Pool of connections available for the mail sending.
	 *
	 * @return void
	 */
	public function init_pool( ConnectionPool $connection_pool ): void {
		$this->connection_pool = $connection_pool;

		// This setting will be modified during the sending process. So we need to capture initial values.
		$this->initial_settings = [
			'From'         => $this->From,
			'FromName'     => $this->FromName,
			'ReplyTo'      => $this->ReplyTo,
			'ReplyToQueue' => $this->ReplyToQueue,
		];
	}

	/**
	 * Current connection.
	 *
	 * @return ConnectorSMTP|null
	 */
	public function get_connection(): ?ConnectorSMTP {
		if ( ! $this->connection_pool instanceof ConnectionPool ) {
			return null;
		}

		$current = $this->connection_pool->current();
		return $current instanceof ConnectorSMTP ? $current : null;
	}

	/**
	 * Create a message and send it.
	 * Uses the sending method specified by $Mailer.
	 *
	 * @return bool false on error - See the ErrorInfo property for details of the error
	 * @throws Exception
	 */
	public function send(): bool {
		if ( ! $this->connection_pool instanceof ConnectionPool || $this->connection_pool->count() === 0 ) {
			// Pool is empty, send email without Solid Mail
			return parent::send();
		}

		try {
			$connection = $this->get_connection();
			if ( ! $connection instanceof ConnectorSMTP ) {
				throw new LogicException( 'Connection is not defined' );
			}

			$this->configure_for_connection( $connection );

			if ( ! $this->preSend() ) {
				return false;
			}

			return $this->postSend();
		} catch ( Exception $exc ) {
			/**
			 * As we have an `exceptions` property enabled, `preSend` and `postSend` methods throw an exception on error.
			 * So we can handle a negative path only once in the catch construction.
			 */
			if ( $this->connection_pool->hasNext() ) {
				$this->connection_pool->next();
				return $this->send();
			}

			$this->mailHeader = '';
			$this->setError( $exc->getMessage() );

			throw $exc;
		}
	}

	private function configure_for_connection( ConnectorSMTP $connection ): void {
		$this->Mailer     = $connection->isAPI() ? 'api' : 'smtp';
		$this->Host       = $connection->get_host();
		$this->SMTPSecure = $connection->get_secure();
		$this->Port       = $connection->get_port();
		$this->SMTPAuth   = $connection->is_authentication();
		$this->Username   = $this->SMTPAuth ? $connection->get_username() : '';
		$this->Password   = $this->SMTPAuth ? $connection->get_password() : '';
		$this->From       = $connection->get_from_email();
		$this->Sender     = $this->From;

		$this->maybe_update_default_settings( $connection );
	}

	private function maybe_update_default_settings( ConnectorSMTP $connection ): void {
		$default_settings = $this->wordpress_default_settings();

		if ( $this->initial_settings['FromName'] === $default_settings['FromName'] ) {
			// Change the default `WordPress` to connection from name.
			$this->FromName = $connection->get_from_name();
		}

		if ( count( $this->initial_settings['ReplyTo'] ) > 0 || count( $this->initial_settings['ReplyToQueue'] ) > 0 ) {
			// The ReplyTo was provided by wp_mail already. We don't need to change it.
			return;
		}

		if ( $this->initial_settings['From'] === $default_settings['From'] ) {
			// Adding Reply-To with the default WordPress email does not make sense.
			return;
		}

		if ( $this->initial_settings['From'] === $this->From ) {
			// We don't want to add Reply-To with the same email as From.
			return;
		}

		$this->clearReplyTos();
		$this->AddReplyTo( $this->initial_settings['From'], $this->FromName );
	}

	/**
	 * Returns the default settings for WordPress.
	 *
	 * @return string[]
	 * @phpstan-return array{FromName: string, From: string}
	 * @see wp_mail()
	 */
	private function wordpress_default_settings(): array {
		$sitename   = wp_parse_url( network_home_url(), PHP_URL_HOST );
		$from_email = 'wordpress@';

		if ( null !== $sitename ) {
			if ( str_starts_with( $sitename, 'www.' ) ) {
				$sitename = substr( $sitename, 4 );
			}

			$from_email .= $sitename;
		}

		return [
			'FromName' => 'WordPress',
			'From'     => $from_email,
		];
	}

	/**
	 * Sends an email using the API connector.
	 *
	 * @param string $header The email headers
	 * @param string $body The email body
	 *
	 * @return bool | WP_Error The result from the API send operation
	 * @throws \Exception
	 * @since 2.1.3
	 */
	public function apiSend( $header, $body ) {
		$connector = $this->get_connection();
		if ( ! $connector instanceof Api_Connector ) {
			throw new Exception( 'API connector is not defined', self::STOP_CRITICAL );
		}

		$email_data = $this->getEmailData( $header, $body );
		if ( is_wp_error( $email_data ) ) {
			throw new Exception( $email_data->get_error_message(), self::STOP_CRITICAL );
		}

		$result = $connector->send_use_api( $email_data );
		if ( is_wp_error( $result ) ) {
			throw new Exception( $result->get_error_message(), self::STOP_CRITICAL );
		}

		return $result;
	}

	/**
	 * Gets formatted email data including recipients, headers, and body content.
	 * This method extracts and organizes all relevant email sending information.
	 *
	 * @param string $header The email headers.
	 * @param string $body The email body content.
	 *
	 * @return WP_Error | array{
	 *     to: array<array{0: string, 1: string}>,
	 *     cc: array<array{0: string, 1: string}>,
	 *     bcc: array<array{0: string, 1: string}>,
	 *     from: string,
	 *     sender: string,
	 *     subject: string,
	 *     headers: string,
	 *     body: string,
	 *     custom_headers: array<string, string>,
	 *     reply_to: array<array{0: string, 1: string}>,
	 *     all_recipients: array<string>
	 * }
	 * @since 2.1.3
	 */
	protected function getEmailData( string $header, string $body ): array {
		// Format header with proper line endings
		$formatted_header = static::stripTrailingWSP( $header ) . static::$LE . static::$LE;
		// Determine sender
		$sender = '' === $this->Sender ? $this->From : $this->Sender;

		// Get all custom headers
		$custom_headers = [];
		foreach ( $this->CustomHeader as $header ) {
			$custom_headers[ $header[0] ] = $header[1];
		}

		// Collect all recipients
		$all_recipients = array_merge(
			array_column( $this->to, 0 ),
			array_column( $this->cc, 0 ),
			array_column( $this->bcc, 0 )
		);

		$email_data = [
			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			// Recipients with format: [[email, name], [email, name], ...]
			'to'             => $this->to,
			'cc'             => $this->cc,
			'bcc'            => $this->bcc,

			// Sender information
			'from'           => $this->From,
			'sender'         => $sender,

			// Content
			'subject'        => $this->Subject,
			'headers'        => $formatted_header,
			'body'           => $body,
			'raw_body'       => $this->encodeString( $this->Body, $this->Encoding ),

			// Additional data
			'custom_headers' => $custom_headers,
			'reply_to'       => $this->ReplyTo,
			'all_recipients' => array_unique( $all_recipients ),

			// Optional metadata if available
			'message_type'   => $this->ContentType ?? 'text/plain',
			'charset'        => $this->CharSet ?? 'utf-8',
			'encoding'       => $this->Encoding ?? '8bit',

			// Attachments
			'attachments'    => $this->attachment,
		];

		// Validate required data
		if ( empty( $email_data['from'] ) ) {
			return new WP_Error(
				'email_missing_from',
				'From address is required',
				[ 'status' => self::STOP_CRITICAL ]
			);
		}

		if ( empty( $email_data['all_recipients'] ) ) {
			return new WP_Error(
				'email_missing_recipients',
				'At least one recipient is required',
				[ 'status' => self::STOP_CRITICAL ]
			);
		}


		return $email_data;
	}
}
