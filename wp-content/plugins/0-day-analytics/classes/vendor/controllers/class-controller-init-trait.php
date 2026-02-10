<?php
/**
 * Trait for common controller initialization.
 *
 * @package advanced-analytics
 * @subpackage controllers
 * @since 4.4.1
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

declare(strict_types=1);

namespace ADVAN\Controllers;

use ADVAN\Helpers\Settings;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! trait_exists( '\ADVAN\Controllers\Controller_Init_Trait', false ) ) {
	/**
	 * Trait for conditional initialization based on settings.
	 *
	 * @since 4.4.1
	 */
	trait Controller_Init_Trait {

		/**
		 * Initialize conditionally based on a setting.
		 *
		 * @param string   $setting_key The setting key to check.
		 * @param callable $callback    The callback to execute if setting is enabled.
		 *
		 * @return void
		 *
		 * @since 4.5.2
		 */
		protected static function conditional_init( string $setting_key, callable $callback ): void {
			if ( Settings::get_option( $setting_key ) ) {
				$callback();
			}
		}
	}
}
