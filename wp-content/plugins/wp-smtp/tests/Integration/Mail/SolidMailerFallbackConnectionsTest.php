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
class SolidMailerFallbackConnectionsTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		reset_phpmailer_instance();
	}

	public function testUseMatchedConnection(): void {
		$this->tester->haveFailedConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection1@example.com',
				'from_name'  => 'Main Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection1@example.com',
				'from_name'  => 'Alternative Connection',
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
				return 'connection1@example.com';
			} 
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertSame( 'connection1@example.com', $php_mailer->From );
		$this->assertSame( 'Alternative Connection', $php_mailer->FromName );
		$this->assertCount( 0, $php_mailer->getReplyToAddresses() );
	}

	public function testUseDefaultConnectionFirst(): void {
		update_option(
			SettingsScreen::SETTINGS_SLUG,
			[
				'use_unmatched_connections' => 'yes',
			]
		);

		$this->tester->haveFailedConnection(
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
				'is_default' => false,
				'from_email' => 'connection2@example.com',
				'from_name'  => 'Connection 2',
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
				return 'connection1@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );
		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertInstanceOf( SolidMailer::class, $php_mailer );
		$this->assertTrue( PHPMailer::is_solid_mail_configured() );
		$this->assertSame( 'default@example.com', $php_mailer->From );
		$this->assertSame( 'Default Connection', $php_mailer->FromName );
		$this->assertSame(
			[
				'connection1@example.com' => [
					'connection1@example.com',
					'Default Connection',
				],
			],
			$php_mailer->getReplyToAddresses() 
		);
	}

	public function testUseFirstSuccessfulConnection(): void {
		update_option(
			SettingsScreen::SETTINGS_SLUG,
			[
				'use_unmatched_connections' => 'yes',
			]
		);

		$this->tester->haveFailedConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection1@example.com',
				'from_name'  => 'Connection 1',
			]
		);

		$this->tester->haveFailedConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'default@example.com',
				'from_name'  => 'Default Connection',
			]
		);

		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => false,
				'from_email' => 'connection2@example.com',
				'from_name'  => 'Connection 2',
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
		$this->assertSame( 'connection2@example.com', $php_mailer->From );
		$this->assertSame( 'Connection 2', $php_mailer->FromName );
		$this->assertSame(
			[
				'unmatched@example.com' => [
					'unmatched@example.com',
					'Connection 2',
				],
			],
			$php_mailer->getReplyToAddresses() 
		);
	}
}
