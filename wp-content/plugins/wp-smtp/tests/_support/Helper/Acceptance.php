<?php

namespace Helper;

use Codeception\Module;
use Codeception\TestInterface;
use lucatume\WPBrowser\Module\WPCLI;

class Acceptance extends Module {
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function _before( TestInterface $test ): void {
		parent::_before( $test );

		$this->cli()->cli( [ 'core', 'update-db' ] );
		$this->cli()->cli( [ 'plugin', 'activate', 'wp-smtp' ] );
	}

	private function cli(): WPCLI {
		/** @var WPCLI */
		return $this->getModule( 'WPCLI' );
	}
}
