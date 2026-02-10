<?php
/**
 * Admin UI for displaying trace timeline.
 *
 * @package HookTrace\UI
 */

namespace HookTrace\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HookTrace\Storage\RequestStorage;
use HookTrace\UI\Settings;

/**
 * Handles admin bar integration and timeline display.
 */
class AdminUI {

	/**
	 * Register hooks for UI.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Admin bar shows on both frontend and backend
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_timeline_modal' ) );
		add_action( 'wp_footer', array( $this, 'render_timeline_modal' ) );

		// Localize script data in footer (after callbacks execute)
		add_action( 'admin_footer', array( $this, 'localize_script_data' ), 5 );
		add_action( 'wp_footer', array( $this, 'localize_script_data' ), 5 );

		// Add auto-scroll to line in plugin/theme editor
		add_action( 'admin_enqueue_scripts', array( $this, 'add_editor_line_scroll' ) );
	}

	/**
	 * Add admin bar menu item.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_item( \WP_Admin_Bar $wp_admin_bar ): void {
		$hooks_list    = RequestStorage::get_hooks_list();
		$count         = count( $hooks_list );
		$selected_hook = RequestStorage::get_selected_hook();

		/* translators: %d: number of hooks recorded */
		$title = sprintf( __( 'Hook Trace (%d)', 'hooktrace' ), $count );
		if ( $selected_hook ) {
			$title .= ' - ' . esc_html( $selected_hook );
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'trace-timeline',
				'title' => $title,
				'href'  => '#',
				'meta'  => array(
					'class' => 'trace-timeline-trigger',
				),
			)
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		// Only enqueue if user has capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$plugin_url = HOOKTRACE_PLUGIN_URL;
		$plugin_path = HOOKTRACE_PLUGIN_DIR;

		// Enqueue CSS
		wp_enqueue_style(
			'hooktrace-admin',
			$plugin_url . 'assets/admin.css',
			array( 'dashicons' ),
			filemtime( $plugin_path . 'assets/admin.css' )
		);

		// Enqueue JS (data will be localized in footer after callbacks execute)
		wp_enqueue_script(
			'hooktrace-admin',
			$plugin_url . 'assets/admin.js',
			array(),
			filemtime( $plugin_path . 'assets/admin.js' ),
			true
		);
	}

	/**
	 * Prepare hooks list data for JS.
	 *
	 * @return array
	 */
	private function prepare_hooks_list_data(): array {
		$hooks_list = RequestStorage::get_hooks_list();
		$prepared = array();

		foreach ( $hooks_list as $hook ) {
			$prepared[] = array(
				'hook_name' => $hook['hook_name'] ?? '',
				'type'      => $hook['type'] ?? 'filter',
				'source'    => $hook['source'] ?? 'core',
				'timestamp' => $hook['timestamp'] ?? 0,
				'count'     => $hook['count'] ?? 1,
			);
		}

		return $prepared;
	}

	/**
	 * Prepare selected hook callbacks data for JS.
	 *
	 * @return array
	 */
	private function prepare_selected_hook_callbacks(): array {
		$callbacks = RequestStorage::get_selected_hook_callbacks();
		$prepared  = array();

		foreach ( $callbacks as $callback ) {
			$file_path = $callback['file'] ?? '';
			$line      = $callback['line'] ?? 0;
			$source    = $callback['plugin'] ?? 'unknown';

			// Build both editor URLs - local editor and WordPress editor
			$local_editor_url = '';
			$wp_editor_url    = '';
			
			if ( ! empty( $file_path ) ) {
				// Get local editor URL if configured
				$local_editor_url = Settings::get_editor_url( $file_path, $line );

				// Get WordPress editor URL if file editing is allowed
				if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ( defined( 'DISALLOW_FILE_EDIT' ) && ! DISALLOW_FILE_EDIT ) ) {
					$wp_editor_url = $this->get_editor_url( $file_path, $line, $source );
				}
			}

			$prepared[] = array(
				'hook_name'        => $callback['hook_name'] ?? '',
				'callback'         => $callback['callback'] ?? '',
				'priority'         => $callback['priority'] ?? 10,
				'accepted_args'    => $callback['accepted_args'] ?? 1,
				'execution_order'  => $callback['execution_order'] ?? 0,
				'timestamp'        => $callback['timestamp'] ?? 0,
				'duration'          => round( $callback['duration'] ?? 0, 2 ),
				'file'             => $file_path,
				'line'             => $line,
				'local_editor_url' => $local_editor_url,
				'wp_editor_url'     => $wp_editor_url,
				'plugin'           => $source,
				'type'             => $callback['type'] ?? 'unknown',
				'class'            => $callback['class'] ?? '',
				'name'             => $callback['name'] ?? '',
			);
		}

		return $prepared;
	}

	/**
	 * Get editor URL for a file path.
	 *
	 * @param string $file_path Full file path.
	 * @param int    $line      Line number.
	 * @param string $source    Source (plugin slug, 'theme', 'core', etc.).
	 * @return string Editor URL or empty string.
	 */
	private function get_editor_url( string $file_path, int $line, string $source ): string {
		$file_path = wp_normalize_path( $file_path );

		// Check if it's a theme file
		$theme_dir = wp_normalize_path( get_template_directory() );
		if ( strpos( $file_path, $theme_dir ) === 0 ) {
			$relative_path = str_replace( $theme_dir . '/', '', $file_path );
			$theme_slug    = get_template();
			$url           = admin_url( 'theme-editor.php?file=' . urlencode( $relative_path ) . '&theme=' . urlencode( $theme_slug ) );
			if ( $line > 0 ) {
				$url .= '&line=' . $line;
			}
			return $url;
		}

		// Check if it's a child theme file
		if ( is_child_theme() ) {
			$child_theme_dir = wp_normalize_path( get_stylesheet_directory() );
			if ( strpos( $file_path, $child_theme_dir ) === 0 ) {
				$relative_path = str_replace( $child_theme_dir . '/', '', $file_path );
				$theme_slug    = get_stylesheet();
				$url           = admin_url( 'theme-editor.php?file=' . urlencode( $relative_path ) . '&theme=' . urlencode( $theme_slug ) );
				if ( $line > 0 ) {
					$url .= '&line=' . $line;
				}
				return $url;
			}
		}

		// Check if it's a plugin file
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );
		if ( strpos( $file_path, $plugin_dir ) === 0 ) {
			$relative_path = str_replace( $plugin_dir . '/', '', $file_path );
			
			// Find the plugin basename by matching against registered plugins
			$plugins        = get_plugins();
			$plugin_basename = '';
			
			// First, try exact match (file is the main plugin file)
			if ( isset( $plugins[ $relative_path ] ) ) {
				$plugin_basename = $relative_path;
			} else {
				// Find which plugin this file belongs to by checking directory
				$file_parts = explode( '/', $relative_path );
				if ( ! empty( $file_parts[0] ) ) {
					$plugin_folder = $file_parts[0];
					
					// Find plugin with matching directory
					foreach ( $plugins as $plugin_file => $plugin_data ) {
						$plugin_dir_name = dirname( $plugin_file );
						
						// Match if plugin directory matches or if file is in plugin directory
						if ( $plugin_dir_name === $plugin_folder || ( '.' !== $plugin_dir_name && strpos( $relative_path, $plugin_dir_name . '/' ) === 0 ) ) {
							$plugin_basename = $plugin_file;
							break;
						}
					}
				}
			}
			
			if ( ! empty( $plugin_basename ) ) {
				$url = admin_url( 'plugin-editor.php?file=' . urlencode( $relative_path ) . '&plugin=' . urlencode( $plugin_basename ) );
				if ( $line > 0 ) {
					$url .= '&line=' . $line;
				}
				return $url;
			}
		}

		return '';
	}

	/**
	 * Add auto-scroll to line in plugin/theme editor.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function add_editor_line_scroll( string $hook_suffix ): void {
		// Only run on plugin/theme editor pages
		if ( 'plugin-editor.php' !== $hook_suffix && 'theme-editor.php' !== $hook_suffix ) {
			return;
		}

		// Check if line parameter is in URL
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$line = isset( $_GET['line'] ) ? (int) $_GET['line'] : 0;
		if ( ! $line ) {
			return;
		}

		$plugin_url = HOOKTRACE_PLUGIN_URL;
		$plugin_path = HOOKTRACE_PLUGIN_DIR;

		// Enqueue editor script
		wp_enqueue_script(
			'hooktrace-editor',
			$plugin_url . 'assets/editor.js',
			array( 'wp-theme-plugin-editor' ),
			filemtime( $plugin_path . 'assets/editor.js' ),
			true
		);

		// Localize script with line number
		wp_localize_script(
			'hooktrace-editor',
			'hookTraceEditor',
			array(
				'lineNumber' => $line,
			)
		);
	}

	/**
	 * Localize script data in footer (after all callbacks have executed).
	 *
	 * @return void
	 */
	public function localize_script_data(): void {
		$selected_hook = RequestStorage::get_selected_hook();
		$settings      = Settings::get_settings();

		// Get editor name for display
		$editor_names = array(
			'vscode'      => __( 'VS Code', 'hooktrace' ),
			'cursor'      => __( 'Cursor', 'hooktrace' ),
			'phpstorm'    => __( 'PhpStorm', 'hooktrace' ),
			'sublime'     => __( 'Sublime Text', 'hooktrace' ),
			'atom'        => __( 'Atom', 'hooktrace' ),
			'antigravity' => __( 'Antigravity', 'hooktrace' ),
			'custom'      => __( 'Custom Editor', 'hooktrace' ),
		);
		$editor_type  = $settings['editor_type'] ?? 'vscode';
		$editor_name  = $editor_names[ $editor_type ] ?? __( 'Local Editor', 'hooktrace' );
		
		// For custom editor, try to extract protocol name from custom_protocol
		if ( 'custom' === $editor_type && ! empty( $settings['custom_protocol'] ) ) {
			$custom_protocol = $settings['custom_protocol'];
			// Extract protocol name (e.g., "myapp://" -> "myapp")
			if ( preg_match( '/^([a-z0-9]+):\/\//i', $custom_protocol, $matches ) ) {
				$editor_name = ucfirst( $matches[1] );
			}
		}

		// Prepare JavaScript data (after all callbacks have executed).
		$trace_data = array(
			'hooksList'             => $this->prepare_hooks_list_data(),
			'selectedHook'          => $selected_hook,
			'selectedHookCallbacks' => $this->prepare_selected_hook_callbacks(),
			'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
			'nonce'                 => wp_create_nonce( 'hooktrace_nonce' ),
			'i18n'                  => $this->get_js_translations(),
			'editorName'            => $editor_name,
		);

		wp_localize_script(
			'hooktrace-admin',
			'hookTrace',
			$trace_data
		);
	}

	/**
	 * Render timeline modal in admin footer.
	 *
	 * @return void
	 */
	public function render_timeline_modal(): void {
		$selected_hook = RequestStorage::get_selected_hook();
		// Note: Script data is localized via localize_script_data() hook
		?>
		<div id="trace-timeline-modal" class="trace-timeline-modal" style="display: none;">
			<div class="trace-timeline-overlay"></div>
			<div class="trace-timeline-container">
				<div class="trace-timeline-header">
					<?php if ( $selected_hook ) : ?>
						<h2><?php esc_html_e( 'Tracing:', 'hooktrace' ); ?> <code><?php echo esc_html( $selected_hook ); ?></code> <span id="trace-hook-count" class="trace-hook-count"></span></h2>
						<div class="trace-header-filter">
							<label for="trace-function-filter"><?php esc_html_e( 'Filter by function:', 'hooktrace' ); ?></label>
							<select id="trace-function-filter" class="trace-filter-select"></select>
						</div>
						<button class="trace-clear-selection" aria-label="<?php esc_attr_e( 'Clear Selection', 'hooktrace' ); ?>"><?php esc_html_e( 'Clear Selection', 'hooktrace' ); ?></button>
					<?php else : ?>
						<h2><?php esc_html_e( 'Hook Trace', 'hooktrace' ); ?></h2>
					<?php endif; ?>
					<button class="trace-timeline-close" aria-label="<?php esc_attr_e( 'Close', 'hooktrace' ); ?>">&times;</button>
				</div>
				<?php if ( $selected_hook ) : ?>
					<div class="trace-selected-hook-info">
						<div id="trace-selected-hook-details"></div>
					</div>
				<?php else : ?>
					<div class="trace-filters">
						<div class="trace-search-box">
							<input type="text" id="trace-search" placeholder="<?php esc_attr_e( 'Search hooks...', 'hooktrace' ); ?>" class="trace-search-input">
						</div>
						<div class="trace-filter-group">
							<label><?php esc_html_e( 'Type:', 'hooktrace' ); ?></label>
							<select id="trace-filter-type" class="trace-filter-select">
								<option value=""><?php esc_html_e( 'All', 'hooktrace' ); ?></option>
								<option value="action"><?php esc_html_e( 'Actions', 'hooktrace' ); ?></option>
								<option value="filter"><?php esc_html_e( 'Filters', 'hooktrace' ); ?></option>
							</select>
						</div>
						<div class="trace-filter-group">
							<label><?php esc_html_e( 'Source:', 'hooktrace' ); ?></label>
							<select id="trace-filter-source" class="trace-filter-select">
								<option value=""><?php esc_html_e( 'All', 'hooktrace' ); ?></option>
								<option value="core"><?php esc_html_e( 'Core', 'hooktrace' ); ?></option>
								<option value="theme"><?php esc_html_e( 'Theme', 'hooktrace' ); ?></option>
								<option value="plugin"><?php esc_html_e( 'Plugins', 'hooktrace' ); ?></option>
							</select>
						</div>
					</div>
					<div class="trace-timeline-content">
						<div id="trace-hooks-list"></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get translations for JavaScript.
	 *
	 * @return array
	 */
	private function get_js_translations(): array {
		return array(
			'noHooksRecorded'       => __( 'No hooks recorded on this page', 'hooktrace' ),
			'noHooksMatch'          => __( 'No hooks match your filters', 'hooktrace' ),
			'noCallbacksFound'      => __( 'No callbacks found for this hook', 'hooktrace' ),
			'noCallbacksHint'       => __( 'This hook may not have any registered callbacks, or it may not have fired on this page.', 'hooktrace' ),
			'count'                 => __( 'count:', 'hooktrace' ),
			'times'                 => __( 'times', 'hooktrace' ),
			'called'                => __( 'called', 'hooktrace' ),
			'time'                  => __( 'time', 'hooktrace' ),
			'all'                   => __( 'All', 'hooktrace' ),
			'countLabel'            => __( 'Count', 'hooktrace' ),
			'total'                 => __( 'Total', 'hooktrace' ),
			'avg'                   => __( 'Avg', 'hooktrace' ),
			'min'                   => __( 'Min', 'hooktrace' ),
			'max'                   => __( 'Max', 'hooktrace' ),
			'priority'              => __( 'Priority:', 'hooktrace' ),
			'executionOrder'        => __( 'Execution Order:', 'hooktrace' ),
			'openInWpEditor'        => __( 'Open in WordPress Editor', 'hooktrace' ),
		);
	}
}

