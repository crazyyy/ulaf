<?php

namespace Integration\Mail;

use IntegrationTester;
use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\SolidMailer;

/**
 * @property IntegrationTester $tester
 */
class SolidMailerReplyToTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		reset_phpmailer_instance();
	}

	public function testPreservesExistingReplyToHeaders(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		$headers = [ 'Reply-To: support@example.com' ];
		wp_mail( 'test@test.com', 'Subject', 'Test', $headers );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'support@example.com' => [
					'support@example.com',
					'',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testDoesNotAddReplyToForDefaultWordPressFrom(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertCount( 0, $php_mailer->getReplyToAddresses() );
	}

	public function testDoesNotAddReplyToWhenFromMatchesConnectionEmail(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'noreply@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertCount( 0, $php_mailer->getReplyToAddresses() );
	}

	public function testAddsReplyToWhenFromDiffersFromConnectionEmail(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'custom@example.com' => [
					'custom@example.com',
					'Test Service',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testPreservesCustomFromName(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
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

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'custom@example.com' => [
					'custom@example.com',
					'Custom Name',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testMultipleReplyToHeaders(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		$headers = [
			'Reply-To: support@example.com',
			'Reply-To: sales@example.com',
		];
		wp_mail( 'test@test.com', 'Subject', 'Test', $headers );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'support@example.com' => [
					'support@example.com',
					'',
				],
				'sales@example.com'   => [
					'sales@example.com',
					'',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testReplyToWithNameInHeader(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		$headers = [ 'Reply-To: Support Team <support@example.com>' ];
		wp_mail( 'test@test.com', 'Subject', 'Test', $headers );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'support@example.com' => [
					'support@example.com',
					'Support Team',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testPreservesReplyToWithIdnDomain(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		$headers = [ 'Reply-To: support@müller.de' ];
		wp_mail( 'test@test.com', 'Subject', 'Test', $headers );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'support@xn--mller-kva.de' => [
					'support@xn--mller-kva.de',
					'',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testAddsReplyToWithIdnFromEmail(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@café.com';
			}
		);

		wp_mail( 'test@test.com', 'Subject', 'Test' );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'custom@xn--caf-dma.com' => [
					'custom@xn--caf-dma.com',
					'Test Service',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}

	public function testHandlesUnicodeFromName(): void {
		$this->tester->haveSuccessfulConnection(
			[
				'is_active'  => true,
				'is_default' => true,
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Test Service',
			]
		);

		add_filter(
			'wp_mail_from',
			static function () {
				return 'custom@example.com';
			}
		);

		$headers = [ 'Reply-To: Müller Support <support@example.com>' ];
		wp_mail( 'test@test.com', 'Subject', 'Test', $headers );

		/** @var SolidMailer $php_mailer */
		$php_mailer = tests_retrieve_phpmailer_instance();

		$this->assertSame( 'noreply@example.com', $php_mailer->From );
		$this->assertSame(
			[
				'support@example.com' => [
					'support@example.com',
					'Müller Support',
				],
			],
			$php_mailer->getReplyToAddresses()
		);
	}
}
