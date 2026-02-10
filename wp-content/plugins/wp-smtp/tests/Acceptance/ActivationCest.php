<?php

class ActivationCest {
	/**
	 * In the airplane mode (`slic airplane-mode on`), the plugin slug is sanitized version of its name.
	 */
	public function testPluginDeactivatesActivatesCorrectly( AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->deactivatePlugin( 'solid-mail' );
		$I->seePluginDeactivated( 'solid-mail' );
		$I->activatePlugin( 'solid-mail' );
		$I->seePluginActivated( 'solid-mail' );
	}
}
