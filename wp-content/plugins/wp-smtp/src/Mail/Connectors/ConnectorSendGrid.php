<?php

namespace SolidWP\Mail\Connectors;

/**
 * Class ConnectorSendGrid
 *
 * This class provides the configuration for the SendGrid SMTP service.
 * It extends the ProviderSMTP class and sets the necessary parameters
 * for connecting to the SendGrid SMTP relay.
 *
 * @package Solid_SMTP\Providers
 */
class ConnectorSendGrid extends ConnectorSMTP {

	/**
	 * ConnectorSendGrid constructor.
	 *
	 * Initializes the SendGrid SMTP provider with default settings.
	 *
	 * @param array $data Optional configuration data.
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		// Prefill the needed data for Brevo.
		$this->host           = 'smtp.sendgrid.net';
		$this->port           = 587;
		$this->authentication = 'yes';
		$this->secure         = 'tls';
		$this->name           = 'sendgrid';
		$this->smtp_username  = 'apikey';
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function process_data( array $data ) {
		$this->from_email    = $data['from_email'] ?? '';
		$this->from_name     = $data['from_name'] ?? '';
		$this->smtp_password = $data['smtp_password'] ?? '';
		$this->description   = 'SendGrid';
	}
}
