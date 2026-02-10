<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto Open Advanced class
 */
class Auto_Open_Advanced {
	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'auto-open-advanced/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'auto-open-advanced/';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_open_advanced_script' ] );
	}

	/**
	 * Enqueue admin script
	 *
	 * @return void
	 */
	public function enqueue_open_advanced_script() {
		$screen = get_current_screen();

		if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
			wp_enqueue_script( 'adminoptim_auto_open_advanced', self::MODULE_URL . 'assets/js/open-advanced.min.js', [], filemtime( self::MODULE_PATH . 'assets/js/open-advanced.min.js' ), true );
		}
	}
}
