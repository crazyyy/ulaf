<?php

namespace Unit\Connectors;

use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Connectors\ConnectorSMTP;

class ConnectorSMTPTest extends WPTestCase {

	public function testProcessData(): void {
		$data = [
			'name'          => 'Test Provider',
			'description'   => 'A test SMTP provider',
			'from_email'    => 'test@example.com',
			'from_name'     => 'Test Sender',
			'smtp_host'     => 'smtp.example.com',
			'smtp_port'     => '587',
			'smtp_auth'     => 'yes',
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
			'disable_logs'  => true,
			'smtp_secure'   => 'tls',
			'is_active'     => true,
		];

		$provider = new ConnectorSMTP( $data );

		self::assertEquals( 'Test Provider', $provider->get_name() );
		self::assertEquals( 'test@example.com', $provider->get_from_email() );
		self::assertEquals( 'Test Sender', $provider->get_from_name() );
		self::assertEquals( 'smtp.example.com', $provider->get_host() );
		self::assertEquals( '587', $provider->get_port() );
		self::assertTrue( $provider->is_authentication() );
		self::assertEquals( 'user', $provider->get_username() );
		self::assertEquals( 'pass', $provider->get_password() );
		self::assertTrue( $provider->get_disable_logs() );
		self::assertEquals( 'tls', $provider->get_secure() );
		self::assertTrue( $provider->is_active() );
	}

	public function testFalseDataProcessing(): void {
		$data = [
			'name'          => 'Test Provider',
			'description'   => 'A test SMTP provider',
			'from_email'    => 'test@example.com',
			'from_name'     => 'Test Sender',
			'smtp_host'     => 'smtp.example.com',
			'smtp_port'     => '587',
			'smtp_auth'     => 'no',
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
			'disable_logs'  => false,
			'smtp_secure'   => 'tls',
			'is_active'     => false,
		];

		$provider = new ConnectorSMTP( $data );

		self::assertEquals( 'Test Provider', $provider->get_name() );
		self::assertEquals( 'test@example.com', $provider->get_from_email() );
		self::assertEquals( 'Test Sender', $provider->get_from_name() );
		self::assertEquals( 'smtp.example.com', $provider->get_host() );
		self::assertEquals( '587', $provider->get_port() );
		self::assertFalse( $provider->is_authentication() );
		self::assertEquals( 'user', $provider->get_username() );
		self::assertEquals( 'pass', $provider->get_password() );
		self::assertFalse( $provider->get_disable_logs() );
		self::assertEquals( 'tls', $provider->get_secure() );
		self::assertFalse( $provider->is_active() );
	}

	public function testValidation(): void {
		$data = [
			'from_email'    => 'invalid-email',
			'from_name'     => 'Test Sender',
			'smtp_host'     => 'smtp.example.com',
			'smtp_port'     => '587',
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
		];

		$provider = new ConnectorSMTP( $data );
		$is_valid = $provider->validation();

		self::assertFalse( $is_valid );
		$errors = $provider->get_errors();
		self::assertArrayHasKey( 'from_email', $errors );
	}

	public function testToArray(): void {
		$data = [
			'id'            => 'unique_id',
			'name'          => 'Test Provider',
			'description'   => '',
			'from_email'    => 'test@example.com',
			'from_name'     => 'Test Sender',
			'smtp_host'     => 'smtp.example.com',
			'smtp_port'     => '587',
			'smtp_auth'     => 'yes',
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
			'disable_logs'  => true,
			'smtp_secure'   => 'tls',
			'is_active'     => true,
		];

		$provider = new ConnectorSMTP( $data );
		$array    = $provider->to_array();

		self::assertEquals( $data['id'], $array['id'] );
		self::assertEquals( $data['name'], $array['name'] );
		self::assertEquals( $data['description'], $array['description'] );
		self::assertEquals( $data['from_email'], $array['from_email'] );
		self::assertEquals( $data['from_name'], $array['from_name'] );
		self::assertEquals( $data['smtp_host'], $array['smtp_host'] );
		self::assertEquals( $data['smtp_port'], $array['smtp_port'] );
		self::assertEquals( $data['smtp_auth'], $array['smtp_auth'] );
		self::assertEquals( $data['smtp_username'], $array['smtp_username'] );
		self::assertEquals( $data['smtp_password'], $array['smtp_password'] );
		self::assertEquals( $data['disable_logs'], $array['disable_logs'] );
		self::assertEquals( $data['smtp_secure'], $array['smtp_secure'] );
		self::assertEquals( $data['is_active'], $array['is_active'] );
	}

	public function testEdgeCases(): void {
		$data = [
			'name'          => '',
			'description'   => '',
			'from_email'    => '',
			'from_name'     => '',
			'smtp_host'     => '',
			'smtp_port'     => '',
			'smtp_auth'     => '',
			'smtp_username' => '',
			'smtp_password' => '',
			'disable_logs'  => false,
			'smtp_secure'   => '',
			'is_active'     => false,
		];

		$provider = new ConnectorSMTP( $data );

		self::assertEquals( '', $provider->get_name() );
		self::assertEquals( '', $provider->get_from_email() );
		self::assertEquals( '', $provider->get_from_name() );
		self::assertEquals( '', $provider->get_host() );
		self::assertEquals( '', $provider->get_port() );
		self::assertFalse( $provider->is_authentication() );
		self::assertEquals( '', $provider->get_username() );
		self::assertEquals( '', $provider->get_password() );
		self::assertFalse( $provider->get_disable_logs() );
		self::assertEquals( '', $provider->get_secure() );
		self::assertFalse( $provider->is_active() );
	}
}
