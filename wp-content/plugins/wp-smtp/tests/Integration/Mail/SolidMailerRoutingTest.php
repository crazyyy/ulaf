<?php

namespace Integration\Mail;

use IntegrationTester;
use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Admin\SettingsScreen;
use SolidWP\Mail\Hooks\PHPMailer;
use SolidWP\Mail\SolidMailer;

/**
 * @property IntegrationTester $tester
 */
class SolidMailerRoutingTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		reset_phpmailer_instance();
	}

	public function testConnectionDetectionByFromEmailMatch(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection1@example.com',
				'from_name'  => 'Connection 1',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'connection2@example.com',
				'from_name'  => 'Connection 2',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'connection2@example.com';
			}
		);

		add_filter(
			'wp_mail_from_name',
			static function () {
				return 'Custom Name';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();
		
		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertEquals( 'connection2@example.com', $php_mailer->From );
		$this->assertEquals( 'Custom Name', $php_mailer->FromName );
	}

	public function testConnectionDetectionFallsBackToDefault(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection1@example.com',
				'from_name'  => 'Connection 1',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'default@example.com',
				'from_name'  => 'Default Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'nomatch@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();
		
		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertEquals( 'default@example.com', $php_mailer->From );
		$this->assertEquals( 'Default Connection', $php_mailer->FromName );
	}

	public function testConnectionDetectionWithInactiveConnections(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'active@example.com',
				'from_name'  => 'Active Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => false,
				'is_default' => false,
				'from_email' => 'inactive@example.com',
				'from_name'  => 'Inactive Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			function () {
				return 'inactive@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();
		
		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertEquals( 'active@example.com', $php_mailer->From );
		$this->assertEquals( 'Active Connection', $php_mailer->FromName );
	}

	public function testConnectionDetectionWithInvalidConnections(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'valid@example.com',
				'from_name'  => 'Valid Connection',
			]
		);

		$this->tester->haveInvalidConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'invalid@example.com',
				'from_name'  => 'Invalid Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			function () {
				return 'invalid@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();
		
		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertEquals( 'valid@example.com', $php_mailer->From );
		$this->assertEquals( 'Valid Connection', $php_mailer->FromName );
	}

	public function testMultipleActiveConnectionsWithPriority(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority@example.com',
				'from_name'  => 'Priority Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority@example.com',
				'from_name'  => 'Second Priority Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'default@example.com',
				'from_name'  => 'Default Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'priority@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();
		
		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertSame( 'priority@example.com', $php_mailer->From );
		$this->assertSame( 'Priority Connection', $php_mailer->FromName );
	}

	public function testNoMatchedNoDefaultConnections(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority1@example.com',
				'from_name'  => 'Priority Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority2@example.com',
				'from_name'  => 'Second Priority Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				// Invalid email to prevent actual email sending by PHPMailer
				return 'unmatched@example...com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertFalse( PHPMailer::is_solid_mail_configured() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testFallbackToRegularWpMailWhenNoActiveConnections(): void {
		wp_mail( 'recipient@test.com', 'Test Subject', 'Test Body' );

		/** @var \MockPHPMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->tester->dontSeeSolidMailer();
		$this->assertFalse( PHPMailer::is_solid_mail_configured() );
		$this->assertSame( 'recipient@test.com', $php_mailer->get_recipient( 'to' )->address );
		$this->assertSame( 'Test Subject', $php_mailer->get_sent()->subject );
	}

	public function testUseUnmatchedConnections(): void {
		update_option(
			SettingsScreen::SETTINGS_SLUG,
			[
				'use_unmatched_connections' => 'yes',
			] 
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority1@example.com',
				'from_name'  => 'Priority Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'priority2@example.com',
				'from_name'  => 'Second Priority Connection',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'unmatched@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertSame( 'priority1@example.com', $php_mailer->From );
	}
}
