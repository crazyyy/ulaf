<?php

namespace Integration\Db;

use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Admin\SettingsScreen;
use SolidWP\Mail\Repository\SettingsRepository;

class SettingsRepositoryTest extends WPTestCase {

	private SettingsRepository $repository;

	public function setUp(): void {
		parent::setUp();
		$settings_screen  = new SettingsScreen();
		$this->repository = new SettingsRepository( $settings_screen );
	}

	/**
	 * @dataProvider settingsDataProvider
	 * @param array $settings The settings to set
	 * @param bool $expectedLogsDisabled The expected result for logs_disabled()
	 * @param bool $expectedUseUnmatched The expected result for use_unmatched_connections()
	 */
	public function testSettings( array $settings, bool $expectedLogsDisabled, bool $expectedUseUnmatched ): void {
		update_option( SettingsScreen::SETTINGS_SLUG, $settings );

		$logsDisabled = $this->repository->logs_disabled();
		$useUnmatched = $this->repository->use_unmatched_connections();

		$this->assertSame( $expectedLogsDisabled, $logsDisabled );
		$this->assertSame( $expectedUseUnmatched, $useUnmatched );
	}

	/**
	 * Data provider for combined settings test cases.
	 *
	 * @return array<string, array{settings: array, expectedLogsDisabled: bool, expectedUseUnmatched: bool}>
	 */
	public function settingsDataProvider(): array {
		return [
			'both disabled'                        => [
				'settings'             => [
					'disable_logs'              => 'no',
					'use_unmatched_connections' => 'no',
				],
				'expectedLogsDisabled' => false,
				'expectedUseUnmatched' => false,
			],
			'both enabled'                         => [
				'settings'             => [
					'disable_logs'              => 'yes',
					'use_unmatched_connections' => 'yes',
				],
				'expectedLogsDisabled' => true,
				'expectedUseUnmatched' => true,
			],
			'mixed settings'                       => [
				'settings'             => [
					'disable_logs'              => 'yes',
					'use_unmatched_connections' => 'no',
				],
				'expectedLogsDisabled' => true,
				'expectedUseUnmatched' => false,
			],
			'partial settings (only disable_logs)' => [
				'settings'             => [
					'disable_logs' => 'yes',
				],
				'expectedLogsDisabled' => true,
				'expectedUseUnmatched' => false,
			],
			'partial settings (only use_unmatched_connections)' => [
				'settings'             => [
					'use_unmatched_connections' => 'yes',
				],
				'expectedLogsDisabled' => false,
				'expectedUseUnmatched' => true,
			],
			'mixed valid and invalid values'       => [
				'settings'             => [
					'disable_logs'              => 'yes',
					'use_unmatched_connections' => 'invalid',
				],
				'expectedLogsDisabled' => true,
				'expectedUseUnmatched' => false,
			],
		];
	}
}
