<?php

namespace Integration\Logger;

use IntegrationTester;
use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Repository\LogsRepository;
use SolidWP\Mail\Repository\ProvidersRepository;

/**
 * @property IntegrationTester $tester
 */
class EmailLoggingTest extends WPTestCase {

	private LogsRepository $logs_repository;
	private ProvidersRepository $providers_repository;

	public function setUp(): void {
		parent::setUp();
		reset_phpmailer_instance();
		$this->logs_repository      = new LogsRepository();
		$this->providers_repository = new ProvidersRepository();
	}

	public function testLogEmailSent(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'solid@example.com',
				'from_name'  => 'Solid Sender',
			]
		);
		$connection = $this->providers_repository->get_default_provider();

		wp_mail( 'recipient@test.com', 'Solid Mail Test', 'Test body' );

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );
		
		$this->assertSame(
			[
				'to'            => [ 'recipient@test.com' ],
				'subject'       => 'Solid Mail Test',
				'message'       => 'Test body',
				'headers'       => [],
				'content_type'  => 'text/plain',
				'error'         => '',
				'connection_id' => $connection->get_id(),
				'from_email'    => 'solid@example.com',
				'from_name'     => 'Solid Sender',
			],
			$log 
		);
	}

	public function testLogHtmlEmail(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'solid@example.com',
				'from_name'  => 'Solid Sender',
			]
		);
		$connection = $this->providers_repository->get_default_provider();

		wp_mail(
			'recipient@test.com',
			'Solid Mail Test',
			'<h1>Hello!</h1>',
			[ 'Content-Type: text/html; charset=utf-8' ]
		);

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );

		$this->assertSame(
			[
				'to'            => [ 'recipient@test.com' ],
				'subject'       => 'Solid Mail Test',
				'message'       => '<h1>Hello!</h1>',
				'headers'       => [],
				'content_type'  => 'text/html',
				'error'         => '',
				'connection_id' => $connection->get_id(),
				'from_email'    => 'solid@example.com',
				'from_name'     => 'Solid Sender',
			],
			$log 
		);
	}

	public function testLogHtmlEmailWithoutSolidMail(): void {

		add_filter(
			'wp_mail_content_type',
			static function () {
				return 'text/html';
			}
		);

		wp_mail(
			'recipient@test.com',
			'Solid Mail Test',
			'<h1>Hello!</h1>',
		);

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );

		$this->assertSame(
			[
				'to'            => [ 'recipient@test.com' ],
				'subject'       => 'Solid Mail Test',
				'message'       => '<h1>Hello!</h1>',
				'headers'       => [],
				'content_type'  => 'text/html',
				'error'         => '',
				'connection_id' => 'external',
				'from_email'    => 'wordpress@example.org',
				'from_name'     => 'WordPress',
			],
			$log 
		);
	}

	public function testLogEmailSentWithoutSolidMailConnection(): void {
		wp_mail( 'recipient@test.com', 'Regular Mail Test', 'Test body' );

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );

		$this->assertSame(
			[
				'to'            => [ 'recipient@test.com' ],
				'subject'       => 'Regular Mail Test',
				'message'       => 'Test body',
				'headers'       => [],
				'content_type'  => 'text/plain',
				'error'         => '',
				'connection_id' => 'external',
				'from_email'    => 'wordpress@example.org',
				'from_name'     => 'WordPress',
			],
			$log 
		);
	}

	public function testLogEmailFailure(): void {
		$this->tester->haveFailedConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'solid@example.com',
				'from_name'  => 'Solid Sender',
			]
		);
		$connection = $this->providers_repository->get_default_provider();

		wp_mail( 'recipient@test.com', 'Failure Subject', 'Failure Body' );

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );

		$this->assertSame(
			[
				'to'            => [ 'recipient@test.com' ],
				'subject'       => 'Failure Subject',
				'message'       => 'Failure Body',
				'headers'       => [],
				'content_type'  => 'text/plain',
				'error'         => 'Fake Error',
				'connection_id' => $connection->get_id(),
				'from_email'    => 'solid@example.com',
				'from_name'     => 'Solid Sender',
			],
			$log 
		);
	}

	public function testLogEmailFailureWithoutSolidMailConnection(): void {
		wp_mail( 'wrong@@email-address.com', 'Failure Subject', 'Failure Body' );

		$logs = $this->logs_repository->get_email_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		unset( $log['mail_id'], $log['timestamp'] );

		$this->assertSame(
			[
				'to'            => [ 'wrong@@email-address.com' ],
				'subject'       => 'Failure Subject',
				'message'       => 'Failure Body',
				'headers'       => [],
				'content_type'  => 'text/plain',
				'error'         => 'You must provide at least one recipient email address.',
				'connection_id' => 'external',
				'from_email'    => 'wordpress@example.org',
				'from_name'     => 'WordPress',
			],
			$log 
		);
	}
}
