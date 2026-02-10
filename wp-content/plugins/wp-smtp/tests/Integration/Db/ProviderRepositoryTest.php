<?php

namespace Integration\Db;

use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Connectors\ConnectorBrevo;
use SolidWP\Mail\Connectors\ConnectorMailGun;
use SolidWP\Mail\Connectors\ConnectorSendGrid;
use SolidWP\Mail\Connectors\ConnectorSES;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;

class ProviderRepositoryTest extends WPTestCase {

	/** @var list<ConnectorSMTP> */
	protected array $providers;
	protected ProvidersRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$this->repository = new ProvidersRepository();
		$this->initializeProviders();
		$this->saveProviders();
	}

	protected function initializeProviders(): void {
		$this->providers = [
			new ConnectorSendGrid(
				[
					'id'         => 'provider_sendgrid',
					'name'       => 'sendgrid',
					'from_email' => 'sendgrid@example.com',
					'from_name'  => 'SendGrid Sender',
					'is_active'  => false,
					'is_default' => false,
				]
			),
			new ConnectorMailGun(
				[
					'id'         => 'provider_mailgun',
					'name'       => 'mailgun',
					'from_email' => 'mailgun@example.com',
					'from_name'  => 'MailGun Sender',
					'is_active'  => false,
					'is_default' => false,
				]
			),
			new ConnectorSES(
				[
					'id'         => 'provider_ses',
					'name'       => 'ses',
					'from_email' => 'ses@example.com',
					'from_name'  => 'SES Sender',
					'is_active'  => false,
					'is_default' => false,
				]
			),
			new ConnectorBrevo(
				[
					'id'         => 'provider_brevo',
					'name'       => 'brevo',
					'from_email' => 'brevo@example.com',
					'from_name'  => 'Brevo Sender',
					'is_active'  => true,
					'is_default' => false,
				]
			),
			new ConnectorSMTP(
				[
					'id'         => 'provider_smtp',
					'name'       => 'other',
					'from_email' => 'smtp@example.com',
					'from_name'  => 'SMTP Sender',
					'is_active'  => true,
					'is_default' => true,
				]
			),
		];
	}

	protected function saveProviders(): void {
		foreach ( $this->providers as $provider ) {
			$this->repository->save( $provider );
		}
	}

	public function testGetActiveProviders(): void {
		$activeProviders = $this->repository->get_active_providers();
		$this->assertCount( 2, $activeProviders );
		$this->assertInstanceOf( ConnectorSMTP::class, $activeProviders['provider_smtp'] );
		$this->assertEquals( 'provider_smtp', $activeProviders['provider_smtp']->get_id() );
		$this->assertInstanceOf( ConnectorSMTP::class, $activeProviders['provider_brevo'] );
		$this->assertEquals( 'provider_brevo', $activeProviders['provider_brevo']->get_id() );
	}

	public function testSave(): void {
		$savedProviders = get_option( ProvidersRepository::OPTION_NAME, [] );

		foreach ( $this->providers as $provider ) {
			$providerId = $provider->get_id();
			$this->assertArrayHasKey( $providerId, $savedProviders );
			$this->assertEquals( $provider->get_from_email(), $savedProviders[ $providerId ]['from_email'] );
			$this->assertEquals( $provider->get_from_name(), $savedProviders[ $providerId ]['from_name'] );
		}
	}

	public function testGetAllProviders(): void {
		$retrievedProviders = $this->repository->get_all_providers();

		foreach ( $this->providers as $provider ) {
			$providerId = $provider->get_id();
			$this->assertArrayHasKey( $providerId, $retrievedProviders );
			$this->assertInstanceOf( get_class( $provider ), $retrievedProviders[ $providerId ] );
		}
	}

	public function testGetAllProvidersAsArray(): void {
		$providersArray = $this->repository->get_all_providers_as_array();

		foreach ( $this->providers as $provider ) {
			$providerId = $provider->get_id();
			$this->assertArrayHasKey( $providerId, $providersArray );
			$this->assertEquals( $provider->get_name(), $providersArray[ $providerId ]['name'] );
			$this->assertEquals( $provider->get_from_email(), $providersArray[ $providerId ]['from_email'] );
			$this->assertEquals( $provider->get_from_name(), $providersArray[ $providerId ]['from_name'] );
		}
	}

	public function testGetProviderById(): void {
		foreach ( $this->providers as $provider ) {
			$retrievedProvider = $this->repository->get_provider_by_id( $provider->get_id() );
			$this->assertInstanceOf( get_class( $provider ), $retrievedProvider );
			$this->assertEquals( $provider->get_id(), $retrievedProvider->get_id() );
		}
	}

	public function testSetDefaultProvider(): void {
		$this->repository->set_default_provider( 'provider_sendgrid' );

		foreach ( $this->providers as $provider ) {
			$retrievedProvider = $this->repository->get_provider_by_id( $provider->get_id() );
			if ( $provider->get_id() === 'provider_sendgrid' ) {
				$this->assertTrue( $retrievedProvider->is_default() );
			} else {
				$this->assertFalse( $retrievedProvider->is_default() );
			}
		}
	}

	public function testDeleteProviderById(): void {
		$this->repository->delete_provider_by_id( 'provider_smtp' );

		$providers = get_option( ProvidersRepository::OPTION_NAME, [] );
		$this->assertArrayNotHasKey( 'provider_smtp', $providers );
	}
}
