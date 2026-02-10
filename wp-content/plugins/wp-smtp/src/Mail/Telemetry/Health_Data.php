<?php

/**
 * Handles adding a new section to the site health data screen.
 *
 * @since 2.0.0
 */

namespace SolidWP\Mail\Telemetry;

use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\LogsRepository;
use SolidWP\Mail\Repository\ProvidersRepository;

class Health_Data {

	private LogsRepository $logs_repository;

	private ProvidersRepository $providers_repository;

	/**
	 * @param LogsRepository      $logs_repository
	 * @param ProvidersRepository $providers_repository
	 */
	public function __construct( LogsRepository $logs_repository, ProvidersRepository $providers_repository ) {
		$this->logs_repository      = $logs_repository;
		$this->providers_repository = $providers_repository;
	}

	/**
	 * Adds a new Solid Mail section to the site health data.
	 *
	 * @since  2.0.0
	 *
	 * @filter debug_information
	 *
	 * @param array $info The array of site health data.
	 *
	 * @return array
	 */
	public function add_summary_to_telemetry( array $info ): array {
		$active_providers     = $this->providers_repository->get_active_providers();
		$brevo_connections    = 0;
		$mailgun_connections  = 0;
		$sendgrid_connections = 0;
		$ses_connections      = 0;
		$smtp_connections     = 0;
		$all_providers        = $this->providers_repository->get_all_providers();
		foreach ( $all_providers as $provider ) {
			if ( $provider->get_name() === 'brevo' ) {
				++$brevo_connections;
			} elseif ( $provider->get_name() === 'mailgun' ) {
				++$mailgun_connections;
			} elseif ( $provider->get_name() === 'sendgrid' ) {
				++$sendgrid_connections;
			} elseif ( $provider->get_name() === 'amazon_ses' ) {
				++$ses_connections;
			} elseif ( $provider->get_name() === 'other' ) {
				++$smtp_connections;
			}
		}

		// Get count of all logs for mail sends
		$number_of_logs = $this->logs_repository->count_all_logs();

		$number_of_active_connections = count( $active_providers );
		$active_connections_names     = implode(
			', ',
			array_map( static fn ( ConnectorSMTP $connection ) => $connection->get_name(), $active_providers )
		);

		$info['solid-mail'] = [
			'label'  => esc_html__( 'Solid Mail', 'LION' ),
			'fields' => [
				'active_connections_number' => [
					'label' => esc_html__( 'Number of active connections', 'LION' ),
					'value' => $number_of_active_connections,
					'debug' => $number_of_active_connections,
				],
				'active_connections_names'  => [
					'label' => esc_html__( 'Active connections', 'LION' ),
					'value' => $active_connections_names,
					'debug' => $active_connections_names,
				],
				'brevo_connections'         => [
					'label' => esc_html__( 'Number of Brevo connections', 'LION' ),
					'value' => $brevo_connections,
					'debug' => $brevo_connections,
				],
				'mailgun_connections'       => [
					'label' => esc_html__( 'Number of MailGun connections', 'LION' ),
					'value' => $mailgun_connections,
					'debug' => $mailgun_connections,
				],
				'sendgrid_connections'      => [
					'label' => esc_html__( 'Number of SendGrid connections', 'LION' ),
					'value' => $sendgrid_connections,
					'debug' => $sendgrid_connections,
				],
				'ses_connections'           => [
					'label' => esc_html__( 'Number of SES connections', 'LION' ),
					'value' => $ses_connections,
					'debug' => $ses_connections,
				],
				'smtp_connections'          => [
					'label' => esc_html__( 'Number of SMTP connections', 'LION' ),
					'value' => $smtp_connections,
					'debug' => $smtp_connections,
				],
				'sent_emails'               => [
					'label' => esc_html__( 'Number of sent emails', 'LION' ),
					'value' => $number_of_logs,
					'debug' => $number_of_logs,
				],
			],
		];

		return $info;
	}
}
