<?php
/**
 * Settings page for HookTrace.
 *
 * @package HookTrace\UI
 */

namespace HookTrace\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin settings page.
 */
class Settings {

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'hooktrace_settings';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'hooktrace_settings';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'hooktrace-settings';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );
	}

	/**
	 * Enqueue settings page scripts.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_settings_scripts( string $hook_suffix ): void {
		if ( 'tools_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		$plugin_url  = HOOKTRACE_PLUGIN_URL;
		$plugin_path = HOOKTRACE_PLUGIN_DIR;

		wp_enqueue_script(
			'hooktrace-settings',
			$plugin_url . 'assets/settings.js',
			array(),
			filemtime( $plugin_path . 'assets/settings.js' ),
			true
		);
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_management_page(
			__( 'HookTrace Settings', 'hooktrace' ),
			__( 'HookTrace', 'hooktrace' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'editor_settings',
			__( 'Local Editor Settings', 'hooktrace' ),
			array( $this, 'render_editor_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'editor_enabled',
			__( 'Enable Local Editor', 'hooktrace' ),
			array( $this, 'render_editor_enabled_field' ),
			self::PAGE_SLUG,
			'editor_settings'
		);

		add_settings_field(
			'editor_type',
			__( 'Editor Type', 'hooktrace' ),
			array( $this, 'render_editor_type_field' ),
			self::PAGE_SLUG,
			'editor_settings'
		);

		add_settings_field(
			'local_base_path',
			__( 'Local Base Path', 'hooktrace' ),
			array( $this, 'render_local_base_path_field' ),
			self::PAGE_SLUG,
			'editor_settings'
		);

		add_settings_field(
			'custom_protocol',
			__( 'Custom Protocol', 'hooktrace' ),
			array( $this, 'render_custom_protocol_field' ),
			self::PAGE_SLUG,
			'editor_settings'
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		$sanitized['editor_enabled']  = isset( $input['editor_enabled'] ) ? 1 : 0;
		$sanitized['editor_type']      = isset( $input['editor_type'] ) ? sanitize_text_field( $input['editor_type'] ) : 'vscode';
		$sanitized['local_base_path']  = isset( $input['local_base_path'] ) ? sanitize_text_field( $input['local_base_path'] ) : '';
		$sanitized['custom_protocol']  = isset( $input['custom_protocol'] ) ? sanitize_text_field( $input['custom_protocol'] ) : '';
		
		// Add trailing slash if not present (for both Windows and Unix paths)
		if ( ! empty( $sanitized['local_base_path'] ) ) {
			$sanitized['local_base_path'] = rtrim( $sanitized['local_base_path'], '/\\' );
			// Add appropriate separator based on OS (detect from path)
			if ( strpos( $sanitized['local_base_path'], '\\' ) !== false || preg_match( '/^[A-Za-z]:/', $sanitized['local_base_path'] ) ) {
				// Windows path
				$sanitized['local_base_path'] .= '\\';
			} else {
				// Unix/Mac path
				$sanitized['local_base_path'] .= '/';
			}
		}

		return $sanitized;
	}

	/**
	 * Render editor section description.
	 *
	 * @return void
	 */
	public function render_editor_section(): void {
		echo '<p>' . esc_html__( 'Configure your local editor to open files directly from HookTrace. This is useful when developing on a local machine.', 'hooktrace' ) . '</p>';
	}

	/**
	 * Render editor enabled field.
	 *
	 * @return void
	 */
	public function render_editor_enabled_field(): void {
		$settings = $this->get_settings();
		$enabled  = $settings['editor_enabled'] ?? 0;
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[editor_enabled]" value="1" <?php checked( $enabled, 1 ); ?>>
			<?php esc_html_e( 'Enable local editor links', 'hooktrace' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'When enabled, file paths will open in your configured local editor instead of WordPress editor.', 'hooktrace' ); ?></p>
		<?php
	}

	/**
	 * Render editor type field.
	 *
	 * @return void
	 */
	public function render_editor_type_field(): void {
		$settings    = $this->get_settings();
		$editor_type = $settings['editor_type'] ?? 'vscode';

		$editors = array(
			'vscode'     => __( 'Visual Studio Code', 'hooktrace' ),
			'cursor'     => __( 'Cursor', 'hooktrace' ),
			'phpstorm'   => __( 'PhpStorm', 'hooktrace' ),
			'sublime'    => __( 'Sublime Text', 'hooktrace' ),
			'atom'       => __( 'Atom', 'hooktrace' ),
			'antigravity' => __( 'Antigravity', 'hooktrace' ),
			'custom'     => __( 'Custom Protocol', 'hooktrace' ),
		);
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[editor_type]" id="hooktrace-editor-type">
			<?php foreach ( $editors as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $editor_type, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Select your preferred code editor.', 'hooktrace' ); ?></p>
		<?php
	}

	/**
	 * Render custom protocol field.
	 *
	 * @return void
	 */
	public function render_custom_protocol_field(): void {
		$settings        = $this->get_settings();
		$custom_protocol = $settings['custom_protocol'] ?? '';
		$editor_type     = $settings['editor_type'] ?? 'vscode';
		?>
		<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_protocol]" id="hooktrace-custom-protocol" value="<?php echo esc_attr( $custom_protocol ); ?>" class="regular-text" placeholder="myapp://file/{path}:{line}" <?php echo ( 'custom' !== $editor_type ) ? 'style="display: none;"' : ''; ?>>
		<p class="description">
			<?php
			esc_html_e( 'Enter the custom protocol URL pattern. Use {path} for file path and {line} for line number.', 'hooktrace' );
			echo '<br>';
			printf(
				/* translators: %s: Example protocol */
				esc_html__( 'Example: %s', 'hooktrace' ),
				'<code>myapp://file/{path}:{line}</code> or <code>myapp://open?file={path}&line={line}</code>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render local base path field.
	 *
	 * @return void
	 */
	public function render_local_base_path_field(): void {
		$settings        = $this->get_settings();
		$local_base_path = $settings['local_base_path'] ?? '';
		?>
		<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[local_base_path]" id="hooktrace-local-base-path" value="<?php echo esc_attr( $local_base_path ); ?>" class="regular-text" placeholder="/Users/username/path/to/wp or C:\Users\username\path\to\wp">
		<p class="description">
			<?php esc_html_e( 'Base path on your local machine that corresponds to the server base path above.', 'hooktrace' ); ?>
		</p>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show settings updated message
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'hooktrace_settings', 'settings_updated', __( 'Settings saved.', 'hooktrace' ), 'success' );
		}

		settings_errors( 'hooktrace_settings' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$defaults = array(
			'editor_enabled'  => 0,
			'editor_type'     => 'vscode',
			'local_base_path' => '',
			'custom_protocol' => '',
		);

		$settings = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get editor URL for a file.
	 *
	 * @param string $file_path Full file path.
	 * @param int    $line      Line number.
	 * @return string Editor URL or empty string.
	 */
	public static function get_editor_url( string $file_path, int $line ): string {
		$settings = self::get_settings();

		// Check if local editor is enabled
		if ( empty( $settings['editor_enabled'] ) ) {
			return '';
		}

		$file_path = wp_normalize_path( $file_path );

		// Get local base path
		$local_base = ! empty( $settings['local_base_path'] ) ? trim( $settings['local_base_path'] ) : '';

		// If no local base path, can't map
		if ( empty( $local_base ) ) {
			return '';
		}

		// Ensure local base path has trailing slash
		// Detect OS from path format
		$is_windows = ( strpos( $local_base, '\\' ) !== false || preg_match( '/^[A-Za-z]:/', $local_base ) );
		if ( $is_windows ) {
			$local_base = rtrim( $local_base, '/\\' ) . '\\';
		} else {
			$local_base = rtrim( $local_base, '/' ) . '/';
		}

		// Get server base path (WordPress root)
		$server_base = wp_normalize_path( ABSPATH );

		// Check if file is within WordPress root
		if ( strpos( $file_path, $server_base ) !== 0 ) {
			return '';
		}

		// Map to local path by replacing server base with local base
		$relative_path = str_replace( $server_base, '', $file_path );
		// Remove leading slash from relative path if present (since local_base already has trailing slash)
		$relative_path = ltrim( $relative_path, '/' );
		$local_path    = $local_base . $relative_path;

		// Normalize path separators based on OS (detect from local_base_path)
		// If local_base_path contains backslashes, assume Windows
		$is_windows = ( strpos( $local_base, '\\' ) !== false || preg_match( '/^[A-Za-z]:/', $local_base ) );
		
		if ( $is_windows ) {
			// Windows: use backslashes, but editors usually accept forward slashes too
			// For better compatibility, convert to forward slashes for URL encoding
			$local_path = str_replace( '\\', '/', $local_path );
		} else {
			// Unix/Mac: ensure forward slashes
			$local_path = str_replace( '\\', '/', $local_path );
		}

		// Build editor URL based on type
		$editor_type = $settings['editor_type'] ?? 'vscode';

		switch ( $editor_type ) {
			case 'vscode':
				// VS Code accepts both forward and backslashes, but forward is more reliable
				$url = 'vscode://file/' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= ':' . $line;
				}
				break;

			case 'cursor':
				// Cursor uses similar protocol to VS Code
				$url = 'cursor://file/' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= ':' . $line;
				}
				break;

			case 'phpstorm':
				// PhpStorm prefers forward slashes
				$url = 'phpstorm://open?file=' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= '&line=' . $line;
				}
				break;

			case 'sublime':
				// Sublime uses file:// protocol
				$url = 'subl://open?url=file://' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= '&line=' . $line;
				}
				break;

			case 'atom':
				$url = 'atom://core/open/file?filename=' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= '&line=' . $line;
				}
				break;

			case 'antigravity':
				// Antigravity uses similar protocol to VS Code/Cursor
				$url = 'antigravity://file/' . rawurlencode( $local_path );
				if ( $line > 0 ) {
					$url .= ':' . $line;
				}
				break;

			case 'custom':
				// Use custom protocol pattern from settings
				$custom_protocol = $settings['custom_protocol'] ?? '';
				if ( empty( $custom_protocol ) ) {
					return '';
				}
				// Replace placeholders: {path} and {line}
				$url = str_replace( '{path}', rawurlencode( $local_path ), $custom_protocol );
				$url = str_replace( '{line}', (string) $line, $url );
				break;

			default:
				return '';
		}

		return $url;
	}
}

