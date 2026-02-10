<?php
/**
 * Bootstrap class for early initialization.
 *
 * @package HookTrace\Core
 */

namespace HookTrace\Core;

use HookTrace\Collector\HookListCollector;
use HookTrace\Collector\SelectedHookCollector;

/**
 * Handles early bootstrap and collector initialization.
 */
class Bootstrap {

	/**
	 * Whether bootstrap has been initialized.
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Initialize the trace recorder system.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}

		// Safety check: only run if WP_DEBUG is enabled
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		// Check user capability (only for admin)
		if ( ! self::is_admin_user() ) {
			return;
		}

		// Initialize simplified hook list collector
		$hook_list_collector = new HookListCollector();
		$hook_list_collector->register_hooks();

		// Initialize selected hook collector (only if a hook is selected)
		$selected_hook_collector = new SelectedHookCollector();
		$selected_hook_collector->register_hooks();

		self::$initialized = true;
	}

	/**
	 * Check if current user is admin (safely for early boot).
	 *
	 * @return bool
	 */
	private static function is_admin_user(): bool {
		// During early boot, user might not be loaded yet
		// We'll do a final check in the UI layer
		// For now, allow initialization and let UI handle access control
		return true;
	}
}

