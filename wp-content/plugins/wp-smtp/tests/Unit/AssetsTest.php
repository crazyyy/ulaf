<?php

namespace Unit;

use lucatume\WPBrowser\TestCase\WPTestCase;
use SolidWP\Mail\Assets;
use SolidWP\Mail\Container;
use SolidWP\Mail\StellarWP\Assets\Assets as StellarWpAssets;

class AssetsTest extends WPTestCase {

	/**
	 * @dataProvider dataScriptLocalize
	 */
	public function testAssetsDataLoading( string $assetSlug, string $objectName ): void {
		$mockContainer = $this->make(
			Container::class,
			[
				'get'    => static fn () => true,
				'__call' => static fn ( string $name ) => $name === 'callback' ? static fn() => true : true,
			]
		);
		$sut           = new Assets( $mockContainer );
		$sut->register_assets();

		$mailAdminScript = StellarWpAssets::instance()->get( $assetSlug );
		self::assertIsCallable( $mailAdminScript->get_localize_scripts()[ $objectName ] );
	}

	public static function dataScriptLocalize(): iterable {
		yield 'admin' => [ 'solidwp-mail-admin', 'SolidWPMail' ];
		yield 'log' => [ 'solidwp-mail-logs', 'SolidWPMail' ];
		yield 'settings' => [ 'solidwp-mail-settings', 'solidMailSettings' ];
	}
}
