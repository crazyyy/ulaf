<?php

namespace Unit\Connectors;

use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Connectors\ConnectorSendGrid;

class ConnectorSendGridTest extends WPTestCase {

	public function testDefaultSettings() {
		$provider = new ConnectorSendGrid();

		self::assertEquals( 'smtp.sendgrid.net', $provider->get_host() );
		self::assertEquals( 587, $provider->get_port() );
		self::assertTrue( $provider->is_authentication() );
		self::assertEquals( 'tls', $provider->get_secure() );
		self::assertEquals( 'sendgrid', $provider->get_name() );
		self::assertEquals( 'apikey', $provider->get_username() );
	}

	public function testCustomData(): void {
		$data = [
			'from_email'    => 'custom@example.com',
			'from_name'     => 'Custom Sender',
			'smtp_password' => 'custom_pass',
		];

		$provider = new ConnectorSendGrid( $data );

		self::assertEquals( 'smtp.sendgrid.net', $provider->get_host() );
		self::assertEquals( 587, $provider->get_port() );
		self::assertTrue( $provider->is_authentication() );
		self::assertEquals( 'tls', $provider->get_secure() );
		self::assertEquals( 'sendgrid', $provider->get_name() );
		self::assertEquals( 'apikey', $provider->get_username() );
		self::assertEquals( 'custom@example.com', $provider->get_from_email() );
		self::assertEquals( 'Custom Sender', $provider->get_from_name() );
		self::assertEquals( 'custom_pass', $provider->get_password() );
	}

	public function testProcessData(): void {
		$data = [
			'from_email'    => 'process@example.com',
			'from_name'     => 'Process Sender',
			'smtp_password' => 'process_pass',
		];

		$provider = new ConnectorSendGrid( [] );
		$provider->process_data( $data );

		self::assertEquals( 'process@example.com', $provider->get_from_email() );
		self::assertEquals( 'Process Sender', $provider->get_from_name() );
		self::assertEquals( 'process_pass', $provider->get_password() );
	}
}
