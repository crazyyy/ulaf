<?php
/**
 * Main plugin class.
 *
 * @package HookTrace\Core
 */

namespace HookTrace\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HookTrace\UI\AdminUI;
use HookTrace\UI\Settings;

/**
 * Main plugin controller.
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance (Singleton).
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Constructor only assigns state - no hooks here
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		// Safety checks
		if ( ! $this->should_run() ) {
			return;
		}

		// Register plugin action links
		$this->register_plugin_action_links();

		// Handle selected hook from URL parameter
		$this->handle_selected_hook();

		// Initialize bootstrap for early hook capture
		Bootstrap::init();

		// Initialize UI
		$admin_ui = new AdminUI();
		$admin_ui->register_hooks();

		// Initialize Settings
		$settings = new Settings();
		$settings->register_hooks();
	}

	/**
	 * Register plugin action links.
	 *
	 * @return void
	 */
	public function register_plugin_action_links(): void {
		$plugin_file = HOOKTRACE_PLUGIN_DIR . 'hooktrace.php';
		add_filter(
			'plugin_action_links_' . plugin_basename( $plugin_file ),
			array( $this, 'add_settings_link' )
		);
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=hooktrace-settings' ) ),
			esc_html__( 'Settings', 'hooktrace' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Handle selected hook from URL parameter.
	 *
	 * @return void
	 */
	private function handle_selected_hook(): void {
		// Check for selected hook in URL parameter
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['trace_hook'] ) && ! empty( $_GET['trace_hook'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$hook_name = sanitize_text_field( wp_unslash( $_GET['trace_hook'] ) );
			\HookTrace\Storage\RequestStorage::set_selected_hook( $hook_name );
		}
	}

	/**
	 * Check if plugin should run.
	 *
	 * @return bool
	 */
	private function should_run(): bool {
		// Must have WP_DEBUG enabled
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return false;
		}

		// Must be admin user (check when user is available)
		if ( did_action( 'init' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
		}

		return true;
	}
}

