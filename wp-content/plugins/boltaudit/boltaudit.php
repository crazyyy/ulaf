<?php

defined( 'ABSPATH' ) || exit;

use BoltAudit\WpMVC\App;

/**
 * Plugin Name:       BoltAudit – Performance Audit Advisor
 * Description:       Get a clear, no-risk performance review of your WordPress site. BoltAudit scans for common slowdowns and gives smart, easy-to-follow suggestions — without touching your site or installing bloat.
 * Version:           0.0.8
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      6.8
 * Author:            HeyMehedi
 * Author URI:        https://heymehedi.com
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       boltaudit
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/vendor-src/autoload.php';
require_once __DIR__ . '/app/Helpers/helper.php';

final class BoltAudit {
	public static BoltAudit $instance;

	public static function instance(): BoltAudit {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function load() {
		register_activation_hook( __FILE__, [$this, 'on_activation'] );

		$application = App::instance();

		$application->boot( __FILE__, __DIR__ );

		/**
		 * Fires once activated plugins have loaded.
		 *
		 */
		add_action(
			'plugins_loaded', function () use ( $application ): void {

				do_action( 'boltaudit_before_load' );

				$application->load();

				do_action( 'boltaudit_after_load' );
			}
		);
	}

	public function on_activation(): void {
		new BoltAudit\App\Setup\Activation();
	}
}

BoltAudit::instance()->load();