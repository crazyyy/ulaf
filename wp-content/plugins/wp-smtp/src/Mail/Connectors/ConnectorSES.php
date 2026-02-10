<?php

namespace SolidWP\Mail\Connectors;

/**
 * Class ConnectorSES
 *
 * This class provides the configuration for the SES SMTP service.
 * It extends the ProviderSMTP class and sets the necessary parameters
 * for connecting to the SES SMTP relay.
 *
 * @package Solid_SMTP\Providers
 */
class ConnectorSES extends ConnectorSMTP {

	/**
	 * ConnectorSES constructor.
	 *
	 * Initializes the SES SMTP provider with default settings.
	 *
	 * @param array $data Optional configuration data.
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		$this->port           = 587;
		$this->authentication = 'yes';
		$this->secure         = 'tls';
		$this->name           = 'amazon_ses';
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
		$this->host          = $data['smtp_host'] ?? '';
		$this->description   = 'SES Amazon';
	}
}
