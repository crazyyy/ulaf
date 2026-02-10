<?php

namespace SolidWP\Mail\Connectors;

/**
 * Class ConnectorBrevo
 *
 * This class provides the configuration for the Brevo SMTP service.
 * It extends the ProviderSMTP class and sets the necessary parameters
 * for connecting to the Brevo SMTP relay.
 *
 * @package Solid_SMTP\Providers
 */
class ConnectorBrevo extends ConnectorSMTP {

	/**
	 * ConnectorBrevo constructor.
	 *
	 * Initializes the Brevo SMTP provider with default settings.
	 *
	 * @param array $data Optional configuration data.
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		// Prefill the needed data for Brevo.
		$this->host           = 'smtp-relay.brevo.com';
		$this->port           = 587;
		$this->authentication = 'yes';
		$this->secure         = 'tls';
		$this->name           = 'brevo';
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function process_data( array $data ) {
		$this->from_email    = $data['from_email'] ?? '';
		$this->from_name     = $data['from_name'] ?? '';
		$this->smtp_username = $data['smtp_username'] ?? '';
		$this->smtp_password = $data['smtp_password'] ?? '';
		$this->description   = 'Brevo';
	}
}
