<?php
/**
 * Request-level in-memory storage for trace data.
 *
 * @package HookTrace\Storage
 */

namespace HookTrace\Storage;

/**
 * Stores trace data in memory for the current request only.
 */
class RequestStorage {

	/**
	 * Storage buffer for hooks list (simplified - just which hooks fired).
	 *
	 * @var array
	 */
	private static $hooks_list = array();

	/**
	 * Storage buffer for detailed callbacks of selected hook.
	 *
	 * @var array
	 */
	private static $selected_hook_callbacks = array();

	/**
	 * Selected hook name.
	 *
	 * @var string|null
	 */
	private static $selected_hook = null;

	/**
	 * Add a hook to the list and increment call count.
	 *
	 * @param string $hook_name Hook name.
	 * @param string $hook_type Hook type (action/filter).
	 * @param string $source Source (core/theme/plugin).
	 * @return void
	 */
	public static function add_hook( string $hook_name, string $hook_type, string $source ): void {
		// Initialize hook entry if it doesn't exist
		if ( ! isset( self::$hooks_list[ $hook_name ] ) ) {
			self::$hooks_list[ $hook_name ] = array(
				'hook_name' => $hook_name,
				'type'      => $hook_type,
				'source'    => $source,
				'timestamp' => microtime( true ),
				'count'     => 0,
			);
		}

		// Increment call count
		self::$hooks_list[ $hook_name ]['count']++;
	}

	/**
	 * Add detailed callback for selected hook.
	 *
	 * @param array $callback_data Callback data.
	 * @return void
	 */
	public static function add_selected_hook_callback( array $callback_data ): void {
		self::$selected_hook_callbacks[] = $callback_data;
	}

	/**
	 * Set selected hook.
	 *
	 * @param string|null $hook_name Hook name or null to clear.
	 * @return void
	 */
	public static function set_selected_hook( ?string $hook_name ): void {
		self::$selected_hook = $hook_name;
		if ( null === $hook_name ) {
			self::$selected_hook_callbacks = array();
		}
	}

	/**
	 * Get selected hook.
	 *
	 * @return string|null
	 */
	public static function get_selected_hook(): ?string {
		return self::$selected_hook;
	}

	/**
	 * Get all hooks list.
	 *
	 * @return array
	 */
	public static function get_hooks_list(): array {
		return array_values( self::$hooks_list );
	}

	/**
	 * Get detailed callbacks for selected hook.
	 *
	 * @return array
	 */
	public static function get_selected_hook_callbacks(): array {
		return self::$selected_hook_callbacks;
	}

	/**
	 * Clear all stored data (for testing).
	 *
	 * @return void
	 */
	public static function clear(): void {
		self::$hooks_list = array();
		self::$selected_hook_callbacks = array();
		self::$selected_hook = null;
	}
}

