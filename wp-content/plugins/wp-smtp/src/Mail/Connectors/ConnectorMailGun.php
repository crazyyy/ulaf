<?php

namespace SolidWP\Mail\Connectors;

/**
 * Class ConnectorMailGun
 *
 * This class provides the configuration for the MailGun SMTP service.
 * It extends the ProviderSMTP class and sets the necessary parameters
 * for connecting to the MailGun SMTP relay.
 *
 * @package Solid_SMTP\Providers
 */
class ConnectorMailGun extends ConnectorSMTP {
	/**
	 * ConnectorMailGun constructor.
	 *
	 * Initializes the MailGun SMTP provider with default settings.
	 *
	 * @param array $data Optional configuration data.
	 */
	public function __construct( array $data = [] ) {
		parent::__construct( $data );
		// prefill the needed data for mailgun.
		$this->host           = 'smtp.mailgun.org';
		$this->port           = 587;
		$this->authentication = 'yes';
		$this->secure         = 'tls';
		$this->name           = 'mailgun';
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
		$this->description   = 'MailGun';
	}
}
