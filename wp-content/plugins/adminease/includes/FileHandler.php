<?php

namespace AdminEase;

use Exception;
use WP_Filesystem_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class FileHandler
 * Manages safe file operations for wp-config.php and .htaccess files
 * with atomic testing, backup creation, and restoration capabilities.
 * Uses a change stack system to batch multiple changes before writing.
 */
class FileHandler {
	public static ?FileHandler $instance;
	/**
	 * WordPress Filesystem API instance
	 * @var WP_Filesystem_Base
	 */
	private WP_Filesystem_Base $filesystem;
	/**
	 * Path to wp-config.php file
	 * @var string
	 */
	private string $wp_config_path;
	/**
	 * Path to .htaccess file
	 * @var string
	 */
	private string $htaccess_path;
	/**
	 * Path to wp-config.php backup file
	 * @var string
	 */
	private string $wp_config_backup_path;
	/**
	 * Path to .htaccess backup file
	 * @var string
	 */
	private string $htaccess_backup_path;
	/**
	 * Error messages from operations
	 * @var array
	 */
	private array $errors = [];
	/**
	 * Change stack for wp-config.php
	 * @var array
	 */
	private array $wp_config_stack = [];
	/**
	 * Change stack for .htaccess
	 * @var array
	 */
	private array $htaccess_stack = [];
	/**
	 * Stack processing modes
	 */
	const STACK_MODE_REPLACE = 'replace';
	const STACK_MODE_APPEND  = 'append';
	const STACK_MODE_REMOVE  = 'remove';
	
	/**
	 * Constructor
	 * Initializes the FileHandler with WordPress Filesystem API
	 * and sets up file paths.
	 */
	public function __construct() {
		$this->init_filesystem();
		$this->set_file_paths();
		
		add_action( 'adminease_settings_saved', [ $this, 'adminease_settings_saved' ], 999 );
	}
	
	/**
	 * Retrieves the singleton instance of the class. If the instance does not already exist, it is created.
	 * @return self The singleton instance of the class.
	 */
	public static function get_instance(): self {
		static $instance = null;
		
		if( is_null( $instance ) ) {
			$instance = new self();
		}
		
		return $instance;
	}
	
	/**
	 * Handles the saving of Adminease settings and processes the stack if valid.
	 * @param array $sanitized_settings The sanitized settings data to be saved.
	 * @return void
	 */
	public function adminease_settings_saved( array $sanitized_settings ) {
		if( $this->validate_stack() ) {
			$this->execute_stack();
		}
	}
	
	/**
	 * Initialize WordPress Filesystem API
	 * Shows admin notice and stops execution if initialization fails
	 * @return void
	 */
	private function init_filesystem(): void {
		global $wp_filesystem;
		
		if( !function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		
		$credentials = request_filesystem_credentials( '', '', false, false, null );
		
		if( !WP_Filesystem( $credentials ) || !is_object( $wp_filesystem ) || !( $wp_filesystem instanceof WP_Filesystem_Base ) ) {
			add_action( 'admin_notices', function() {
				?>
				<div class="notice notice-error">
					<p>
						<strong><?php esc_html_e( 'AdminEase - Critical Error', 'adminease' ); ?></strong><br>
						<?php esc_html_e( 'Failed to initialize WordPress Filesystem API. The plugin cannot function without it. Please check your file permissions and server configuration.', 'adminease' ); ?>
					</p>
				</div>
				<?php
			} );
			
			// Log the error
			error_log( 'AdminEase: Failed to initialize WordPress Filesystem API' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			
			// Stop further execution by throwing an exception
			wp_die(
				esc_html__( 'AdminEase cannot initialize the WordPress Filesystem API. Please contact your hosting provider.', 'adminease' ),
				esc_html__( 'AdminEase - Critical Error', 'adminease' ),
				[ 'back_link' => true ]
			);
		}
		
		$this->filesystem = $wp_filesystem;
	}
	
	/**
	 * Set file paths for wp-config.php and .htaccess
	 */
	private function set_file_paths() {
		$this->wp_config_path = $this->locate_wp_config();
		$this->htaccess_path  = ABSPATH . '.htaccess';
		
		$config_dir                  = dirname( $this->wp_config_path );
		$this->wp_config_backup_path = $config_dir . '/wp-config-adminease-backup.php';
		$this->htaccess_backup_path  = ABSPATH . '.htaccess_adminease_backup';
	}
	
	/**
	 * Locate wp-config.php file
	 * Searches for wp-config.php in the WordPress root directory
	 * and one level up, following WordPress conventions.
	 * @return string Path to wp-config.php
	 */
	private function locate_wp_config() {
		$config_file = ABSPATH . 'wp-config.php';
		
		if( $this->filesystem->exists( $config_file ) ) {
			return $config_file;
		}
		
		// Check one level up
		$config_file = dirname( ABSPATH ) . '/wp-config.php';
		if( $this->filesystem->exists( $config_file ) ) {
			return $config_file;
		}
		
		// Fallback to standard location
		return ABSPATH . 'wp-config.php';
	}
	
	/**
	 * Add wp-config.php constant to the change stack
	 * @param string $constant Constant name
	 * @param mixed  $value Constant value (null to remove)
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_wp_config_constant( $constant, $value = null, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_constant_name( $constant ) ) {
			return false;
		}
		
		$this->wp_config_stack[ $constant ] = [
			'value'     => $value,
			'mode'      => $mode,
			'timestamp' => time(),
		];
		
		return true;
	}
	
	/**
	 * Add multiple wp-config.php constants to the change stack
	 * @param array  $constants Array of constants ['CONSTANT_NAME' => 'value']
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_wp_config_constants( $constants, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_constants( $constants ) ) {
			return false;
		}
		
		foreach( $constants as $constant => $value ) {
			$this->stack_wp_config_constant( $constant, $value, $mode );
		}
		
		return true;
	}
	
	/**
	 * Add PHP ini directive to the wp-config.php change stack
	 * @param string $directive PHP ini directive name (e.g., 'max_execution_time')
	 * @param mixed  $value Directive value (null to remove)
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_wp_config_ini_directive( $directive, $value = null, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_ini_directive_name( $directive ) ) {
			return false;
		}
		
		$stack_key = 'INI_' . strtoupper( $directive );
		
		// If value is empty, set mode to remove
		if( $this->is_empty_value( $value ) ) {
			$mode  = self::STACK_MODE_REMOVE;
			$value = null;
		}
		
		$this->wp_config_stack[ $stack_key ] = [
			'value'          => $value,
			'mode'           => $mode,
			'timestamp'      => time(),
			'type'           => 'ini_directive',
			'directive_name' => $directive,
		];
		
		return true;
	}
	
	/**
	 * Add multiple PHP ini directives to the wp-config.php change stack
	 * @param array  $directives Array of directives ['directive_name' => 'value']
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_wp_config_ini_directives( $directives, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_ini_directives( $directives ) ) {
			return false;
		}
		
		foreach( $directives as $directive => $value ) {
			$this->stack_wp_config_ini_directive( $directive, $value, $mode );
		}
		
		return true;
	}
	
	/**
	 * Remove an ini directive from the wp-config stack
	 * @param string $directive Directive name
	 * @return bool True on success, false on failure
	 */
	public function remove_wp_config_ini_from_stack( $directive ) {
		$stack_key = 'INI_' . strtoupper( $directive );
		
		if( !isset( $this->wp_config_stack[ $stack_key ] ) ) {
			$this->add_error( "Ini directive {$directive} not found in stack" );
			
			return false;
		}
		
		unset( $this->wp_config_stack[ $stack_key ] );
		
		return true;
	}
	
	/**
	 * Validate ini directive name
	 * @param string $directive Directive name
	 * @return bool True if valid, false otherwise
	 */
	private function validate_ini_directive_name( $directive ) {
		if( !is_string( $directive ) || empty( $directive ) ) {
			$this->add_error( 'Invalid ini directive name' );
			
			return false;
		}
		
		// Allow alphanumeric characters, underscores, dots, and hyphens
		if( !preg_match( '/^[a-zA-Z0-9_.-]+$/', $directive ) ) {
			$this->add_error( 'Ini directive name contains invalid characters' );
			
			return false;
		}
		
		// Check against a whitelist of common and safe ini directives
		$allowed_directives = [
			'max_execution_time',
			'post_max_size',
			'upload_max_filesize',
		];
		
		if( !in_array( $directive, $allowed_directives ) ) {
			$this->add_error( "Ini directive '{$directive}' is not in the allowed list" );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validate ini directives array
	 * @param array $directives Directives to validate
	 * @return bool True if valid, false otherwise
	 */
	private function validate_ini_directives( $directives ) {
		if( !is_array( $directives ) ) {
			$this->add_error( 'Directives must be an array' );
			
			return false;
		}
		
		foreach( $directives as $directive => $value ) {
			if( !$this->validate_ini_directive_name( $directive ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Remove legacy ini_set() calls
	 * @param string $content File content
	 * @param string $directive Directive name
	 * @return string Content with legacy ini_set calls removed
	 */
	private function remove_legacy_ini_set( $content, $directive ) {
		// Remove ini_set() statements for this directive
		$pattern = '/\n?ini_set\s*\(\s*[\'"]' . preg_quote( $directive, '/' ) . '[\'"]\s*,.*?\)\s*;?\n?/i';
		
		return preg_replace( $pattern, '', $content );
	}
	
	/**
	 * Generate wp-config.php ini directive block
	 * @param string $directive Directive name
	 * @param mixed  $value Directive value
	 * @param string $start_marker Start marker
	 * @param string $end_marker End marker
	 * @return string Generated block
	 */
	private function generate_wp_config_ini_block( $directive, $value, $start_marker, $end_marker ) {
		$sanitized_value = $this->sanitize_ini_value( $value );
		
		$block = $start_marker . "\n";
		$block .= "ini_set('" . $directive . "', " . $sanitized_value . ");\n";
		$block .= $end_marker;
		
		return $block;
	}
	
	/**
	 * Sanitize ini directive value
	 * @param mixed $value Value to sanitize
	 * @return string Sanitized value
	 */
	private function sanitize_ini_value( $value ) {
		if( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}
		else if( is_numeric( $value ) ) {
			return (string) $value;
		}
		else if( is_string( $value ) ) {
			// Handle common PHP ini value formats
			$value = trim( $value );
			
			// If it's a size value (like '256M', '2G'), keep it as string
			if( preg_match( '/^\d+[KMGT]?$/i', $value ) ) {
				return "'" . $value . "'";
			}
			
			// If it's a time value (like '30s', '5m'), keep it as string
			if( preg_match( '/^\d+[smhd]?$/i', $value ) ) {
				return "'" . $value . "'";
			}
			
			// If it's a boolean-like string
			if( in_array( strtolower( $value ), [ 'on', 'off', 'true', 'false', 'yes', 'no' ] ) ) {
				return "'" . $value . "'";
			}
			
			// Default string handling
			return "'" . addslashes( $value ) . "'";
		}
		else {
			return "'" . addslashes( (string) $value ) . "'";
		}
	}
	
	/**
	 * Get ini directives from the current stack
	 * @return array Array of ini directives in the stack
	 */
	public function get_wp_config_ini_stack() {
		$ini_stack = [];
		
		foreach( $this->wp_config_stack as $stack_key => $change ) {
			if( isset( $change['type'] ) && $change['type'] === 'ini_directive' ) {
				$ini_stack[ $change['directive_name'] ] = $change;
			}
		}
		
		return $ini_stack;
	}
	
	/**
	 * Get constants from the current stack (excluding ini directives)
	 * @return array Array of constants in the stack
	 */
	public function get_wp_config_constants_stack() {
		$constants_stack = [];
		
		foreach( $this->wp_config_stack as $stack_key => $change ) {
			if( !isset( $change['type'] ) || $change['type'] !== 'ini_directive' ) {
				$constants_stack[ $stack_key ] = $change;
			}
		}
		
		return $constants_stack;
	}
	
	/**
	 * Add .htaccess rule to the change stack
	 * @param string $rule_name Rule name
	 * @param string $content Rule content (null to remove)
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_htaccess_rule( $rule_name, $content = null, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_rule_name( $rule_name ) ) {
			return false;
		}
		
		$this->htaccess_stack[ $rule_name ] = [
			'content'   => $content,
			'mode'      => $mode,
			'timestamp' => time(),
		];
		
		return true;
	}
	
	/**
	 * Add multiple .htaccess rules to the change stack
	 * @param array  $rules Array of rules ['RULE_NAME' => 'content']
	 * @param string $mode Stack mode (replace, append, remove)
	 * @return bool True on success, false on failure
	 */
	public function stack_htaccess_rules( $rules, $mode = self::STACK_MODE_REPLACE ) {
		if( !$this->validate_htaccess_rules( $rules ) ) {
			return false;
		}
		
		foreach( $rules as $rule_name => $content ) {
			$this->stack_htaccess_rule( $rule_name, $content, $mode );
		}
		
		return true;
	}
	
	/**
	 * Remove a constant from the wp-config stack
	 * @param string $constant Constant name
	 * @return bool True on success, false on failure
	 */
	public function remove_wp_config_from_stack( $constant ) {
		if( !isset( $this->wp_config_stack[ $constant ] ) ) {
			$this->add_error( "Constant {$constant} not found in stack" );
			
			return false;
		}
		
		unset( $this->wp_config_stack[ $constant ] );
		
		return true;
	}
	
	/**
	 * Remove a rule from the htaccess stack
	 * @param string $rule_name Rule name
	 * @return bool True on success, false on failure
	 */
	public function remove_htaccess_from_stack( $rule_name ) {
		if( !isset( $this->htaccess_stack[ $rule_name ] ) ) {
			$this->add_error( "Rule {$rule_name} not found in stack" );
			
			return false;
		}
		
		unset( $this->htaccess_stack[ $rule_name ] );
		
		return true;
	}
	
	/**
	 * Enhanced PHP configuration management with fallback methods
	 */
	public function apply_php_configuration( $directive, $value ) {
		// If value is empty, null, or whitespace only, remove the directive
		if( $this->is_empty_value( $value ) ) {
			return $this->remove_php_configuration( $directive );
		}
		
		$success = false;
		
		// Method 1: Try .user.ini first (modern shared hosting)
		if( $this->can_use_user_ini() ) {
			$success = $this->set_user_ini_directive( $directive, $value );
			if( $success ) {
				return true;
			}
		}
		
		// Method 2: Fallback to ini_set() in wp-config.php
		if( $this->can_use_ini_set( $directive ) ) {
			$success = $this->stack_wp_config_ini_directive( $directive, $value );
			if( $success ) {
				return true;
			}
		}
		
		// Method 3: Fallback to .htaccess php_value (Apache only)
		if( $this->can_use_htaccess_php_value() ) {
			$success = $this->set_htaccess_php_value( $directive, $value );
			if( $success ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Remove PHP configuration directive from all possible locations
	 */
	public function remove_php_configuration( $directive ) {
		$success = false;
		
		// Remove from .user.ini
		if( $this->can_use_user_ini() ) {
			if( $this->remove_user_ini_directive( $directive ) ) {
				$success = true;
			}
		}
		
		// Remove from wp-config.php
		if( $this->remove_wp_config_ini_from_stack( $directive ) ) {
			$success = true;
		}
		
		// Remove from .htaccess
		if( $this->can_use_htaccess_php_value() ) {
			$rule_name = 'PHP_' . strtoupper( $directive );
			if( $this->remove_htaccess_from_stack( $rule_name ) ) {
				$success = true;
			}
		}
		
		return $success;
	}
	
	/**
	 * Remove directive from .user.ini file
	 */
	private function remove_user_ini_directive( $directive ) {
		$user_ini_path = ABSPATH . '.user.ini';
		
		if( !file_exists( $user_ini_path ) ) {
			return true; // Nothing to remove
		}
		
		$marker_start = "; ADMINEASE_" . strtoupper( $directive ) . " START";
		$marker_end   = "; ADMINEASE_" . strtoupper( $directive ) . " END";
		
		$content = file_get_contents( $user_ini_path );
		$content = $this->remove_marker_block( $content, $marker_start, $marker_end );
		$content = $this->cleanup_file_content( $content );
		
		// If content is empty, remove the file
		if( trim( $content ) === '' ) {
			wp_delete_file( $user_ini_path );
			
			return !file_exists( $user_ini_path );
		}
		
		return file_put_contents( $user_ini_path, $content ) !== false;
	}
	
	/**
	 * Clean up file content by removing excessive newlines
	 */
	private function cleanup_file_content( $content ) {
		// Remove excessive newlines but keep some structure
		$content = preg_replace( '/\n{3,}/', "\n\n", $content );
		
		// Remove leading/trailing whitespace
		$content = trim( $content );
		
		// Ensure single newline at end if content exists
		if( !empty( $content ) ) {
			$content .= "\n";
		}
		
		return $content;
	}
	
	/**
	 * Check if a value is considered empty for PHP configuration
	 */
	private function is_empty_value( $value ) {
		// Consider null, empty string, or whitespace-only as empty
		return $value === null || $value === '' || ( is_string( $value ) && trim( $value ) === '' );
	}
	
	/**
	 * Check if .user.ini is supported and writable
	 */
	private function can_use_user_ini() {
		$user_ini_path = ABSPATH . '.user.ini';
		
		// Check if PHP supports .user.ini
		if( !ini_get( 'user_ini.filename' ) ) {
			return false;
		}
		
		// Check if we can write to the directory
		return $this->filesystem->is_writable( ABSPATH );
	}
	
	/**
	 * Set directive in .user.ini file
	 */
	private function set_user_ini_directive( $directive, $value ) {
		$user_ini_path = ABSPATH . '.user.ini';
		$marker_start  = "; ADMINEASE_" . strtoupper( $directive ) . " START";
		$marker_end    = "; ADMINEASE_" . strtoupper( $directive ) . " END";
		
		// Read existing content
		$content = file_exists( $user_ini_path ) ? file_get_contents( $user_ini_path ) : '';
		
		// Remove existing block
		$content = $this->remove_marker_block( $content, $marker_start, $marker_end );
		
		// Only add new block if value is not empty
		if( !$this->is_empty_value( $value ) ) {
			$new_block = $marker_start . "\n";
			$new_block .= $directive . " = " . $this->sanitize_ini_value_for_file( $value ) . "\n";
			$new_block .= $marker_end . "\n";
			
			$content .= "\n" . $new_block;
		}
		
		// Clean up any extra newlines
		$content = $this->cleanup_file_content( $content );
		
		// If content is empty, remove the file
		if( trim( $content ) === '' ) {
			wp_delete_file( $user_ini_path );
			
			return !file_exists( $user_ini_path );
		}
		
		return file_put_contents( $user_ini_path, $content ) !== false;
	}
	
	/**
	 * Check if directive can be set via ini_set()
	 */
	private function can_use_ini_set( $directive ) {
		// Check if ini_set is disabled
		if( !function_exists( 'ini_set' ) ) {
			return false;
		}
		
		// Check if directive is changeable at runtime
		$changeable_directives = [
			'max_execution_time'  => true,
			'memory_limit'        => true,
			'max_input_vars'      => false,  // PHP_INI_PERDIR only
			'post_max_size'       => false,  // PHP_INI_PERDIR only
			'upload_max_filesize' => false,  // PHP_INI_PERDIR only
		];
		
		return isset( $changeable_directives[ $directive ] ) ? $changeable_directives[ $directive ] : false;
	}
	
	/**
	 * Set PHP value in .htaccess
	 */
	private function set_htaccess_php_value( $directive, $value ) {
		$htaccess_directives = [
			'post_max_size',
			'upload_max_filesize',
			'max_input_vars',
			'max_file_uploads',
		];
		
		if( !in_array( $directive, $htaccess_directives ) ) {
			return false;
		}
		
		$rule_name = 'PHP_' . strtoupper( $directive );
		
		// If value is empty, remove the rule
		if( $this->is_empty_value( $value ) ) {
			return $this->stack_htaccess_rule( $rule_name, null, self::STACK_MODE_REMOVE );
		}
		
		$content = "php_value " . $directive . " " . $value;
		
		return $this->stack_htaccess_rule( $rule_name, $content );
	}
	
	/**
	 * Check if server supports .htaccess php_value
	 */
	private function can_use_htaccess_php_value() {
		// Check if running on Apache with mod_php
		if( !isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}
		
		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		$is_apache       = stripos( $server_software, 'apache' ) !== false;
		$is_mod_php      = php_sapi_name() === 'apache2handler';
		
		return $is_apache && $is_mod_php;
	}
	
	/**
	 * Sanitize value for .user.ini file format
	 */
	private function sanitize_ini_value_for_file( $value ) {
		if( is_bool( $value ) ) {
			return $value ? 'On' : 'Off';
		}
		
		if( is_numeric( $value ) ) {
			return (string) $value;
		}
		
		// For string values, no quotes needed in .user.ini
		return (string) $value;
	}
	
	/**
	 * Get current wp-config stack contents
	 * @return array Current stack contents
	 */
	public function get_wp_config_stack() {
		return $this->wp_config_stack;
	}
	
	/**
	 * Get current htaccess stack contents
	 * @return array Current stack contents
	 */
	public function get_htaccess_stack() {
		return $this->htaccess_stack;
	}
	
	/**
	 * Check if wp-config stack has changes
	 * @return bool True if stack has changes, false otherwise
	 */
	public function has_wp_config_changes() {
		return !empty( $this->wp_config_stack );
	}
	
	/**
	 * Check if htaccess stack has changes
	 * @return bool True if stack has changes, false otherwise
	 */
	public function has_htaccess_changes() {
		return !empty( $this->htaccess_stack );
	}
	
	/**
	 * Check if any stack has changes
	 * @return bool True if any stack has changes, false otherwise
	 */
	public function has_pending_changes() {
		return $this->has_wp_config_changes() || $this->has_htaccess_changes();
	}
	
	/**
	 * Clear the wp-config stack
	 */
	public function clear_wp_config_stack() {
		$this->wp_config_stack = [];
	}
	
	/**
	 * Clear the htaccess stack
	 */
	public function clear_htaccess_stack() {
		$this->htaccess_stack = [];
	}
	
	/**
	 * Clear all stacks
	 */
	public function clear_all_stacks() {
		$this->clear_wp_config_stack();
		$this->clear_htaccess_stack();
	}
	
	/**
	 * Execute all pending changes in the stacks
	 * @param bool $create_backups Whether to create backups before applying changes
	 * @return bool True on success, false on failure
	 */
	public function execute_stack( $create_backups = true ) {
		$success = true;
		
		// Clear any previous errors
		$this->clear_errors();
		
		// Create backups if requested
		if( $create_backups ) {
			if( $this->has_wp_config_changes() ) {
				if( !$this->create_backup( 'wp-config' ) ) {
					return false;
				}
			}
			
			if( $this->has_htaccess_changes() ) {
				if( !$this->create_backup( 'htaccess' ) ) {
					return false;
				}
			}
		}
		
		// Execute wp-config changes
		if( $this->has_wp_config_changes() ) {
			if( !$this->execute_wp_config_stack() ) {
				$success = false;
			}
		}
		
		// Execute htaccess changes
		if( $this->has_htaccess_changes() ) {
			if( !$this->execute_htaccess_stack() ) {
				$success = false;
			}
		}
		
		// Clear stacks on success
		if( $success ) {
			$this->clear_all_stacks();
		}
		
		return $success;
	}
	
	/**
	 * Execute wp-config stack changes
	 * @return bool True on success, false on failure
	 */
	private function execute_wp_config_stack() {
		// Read current content
		$current_content = $this->filesystem->get_contents( $this->wp_config_path );
		if( $current_content === false ) {
			$this->add_error( 'Failed to read wp-config.php' );
			
			return false;
		}
		
		// Process all stacked changes
		$new_content = $this->process_wp_config_stack( $current_content );
		
		// Atomic test and write
		return $this->atomic_write( $this->wp_config_path, $new_content, 'wp-config' );
	}
	
	/**
	 * Execute htaccess stack changes
	 * @return bool True on success, false on failure
	 */
	private function execute_htaccess_stack() {
		// Read current content (create empty if doesn't exist)
		$current_content = '';
		
		if( $this->filesystem->exists( $this->htaccess_path ) ) {
			$current_content = $this->filesystem->get_contents( $this->htaccess_path );
			
			if( $current_content === false ) {
				$this->add_error( 'Failed to read .htaccess' );
				
				return false;
			}
		}
		
		// Process all stacked changes
		$new_content = $this->process_htaccess_stack( $current_content );
		
		// Atomic test and write
		return $this->atomic_write( $this->htaccess_path, $new_content, 'htaccess' );
	}
	
	/**
	 * Process wp-config stack and generate new content
	 * @param string $content Current file content
	 * @return string Updated content
	 */
	private function process_wp_config_stack( $content ) {
		// Sort stack by timestamp to ensure proper order
		uasort(
			$this->wp_config_stack, function( $a, $b ) {
			return $a['timestamp'] - $b['timestamp'];
		}
		);
		
		foreach( $this->wp_config_stack as $stack_key => $change ) {
			$marker_start = "// ADMINEASE_" . strtoupper( $stack_key ) . " START";
			$marker_end   = "// ADMINEASE_" . strtoupper( $stack_key ) . " END";
			
			// Always remove existing block first
			$content = $this->remove_marker_block( $content, $marker_start, $marker_end );
			
			// Handle different types of changes
			if( isset( $change['type'] ) && $change['type'] === 'ini_directive' ) {
				$directive_name = $change['directive_name'];
				
				// Clean up legacy ini_set() calls
				$content = $this->remove_legacy_ini_set( $content, $directive_name );
				
				// Only add new block if mode is not remove and value is not empty
				if( $change['mode'] !== self::STACK_MODE_REMOVE && !$this->is_empty_value( $change['value'] ) ) {
					$new_block = $this->generate_wp_config_ini_block( $directive_name, $change['value'], $marker_start, $marker_end );
					$content   = $this->insert_wp_config_block( $content, $new_block );
				}
			}
			else {
				// Handle regular constant
				$constant = $stack_key;
				
				// Clean up legacy definitions
				$content = $this->remove_legacy_constant_definitions( $content, $constant );
				
				// Only add new block if mode is not remove and value is not empty
				if( $change['mode'] !== self::STACK_MODE_REMOVE && !$this->is_empty_value( $change['value'] ) ) {
					$new_block = $this->generate_wp_config_block( $constant, $change['value'], $marker_start, $marker_end );
					$content   = $this->insert_wp_config_block( $content, $new_block );
				}
			}
		}
		
		return $content;
	}
	
	/**
	 * Process htaccess stack and generate new content
	 * @param string $content Current file content
	 * @return string Updated content
	 */
	private function process_htaccess_stack( $content ) {
		// Sort stack by timestamp to ensure proper order
		uasort(
			$this->htaccess_stack, function( $a, $b ) {
			return $a['timestamp'] - $b['timestamp'];
		}
		);
		
		$new_blocks = [];
		
		foreach( $this->htaccess_stack as $rule_name => $change ) {
			$marker_start = "# ADMINEASE_" . strtoupper( $rule_name ) . " START";
			$marker_end   = "# ADMINEASE_" . strtoupper( $rule_name ) . " END";
			
			// Always remove existing block first
			$content = $this->remove_marker_block( $content, $marker_start, $marker_end );
			
			// Only add new block if mode is not remove and content is not empty
			if( $change['mode'] !== self::STACK_MODE_REMOVE && !$this->is_empty_value( $change['content'] ) ) {
				$new_block    = $this->generate_htaccess_block( $change['content'], $marker_start, $marker_end );
				$new_blocks[] = $new_block;
			}
		}
		
		// Prepend all new blocks at the top of the file
		if( !empty( $new_blocks ) ) {
			$blocks_content = implode( "\n\n", $new_blocks );
			
			// Clean up any leading whitespace from existing content
			$content = ltrim( $content );
			
			// Add new blocks at the top with proper spacing
			$content = $blocks_content . "\n\n" . $content;
		}
		
		// Clean up excessive newlines but maintain structure
		$content = $this->cleanup_htaccess_content( $content );
		
		return $content;
	}
	
	/**
	 * Clean up htaccess content by removing excessive newlines while maintaining structure
	 * @param string $content Content to clean up
	 * @return string Cleaned content
	 */
	private function cleanup_htaccess_content( $content ) {
		// Remove excessive newlines but keep some structure
		$content = preg_replace( '/\n{4,}/', "\n\n\n", $content );
		
		// Remove leading/trailing whitespace
		$content = trim( $content );
		
		// Ensure single newline at end if content exists
		if( !empty( $content ) ) {
			$content .= "\n";
		}
		
		return $content;
	}
	
	/**
	 * Check if a value is considered empty for htaccess rules
	 * @param mixed $value Value to check
	 * @return bool True if empty, false otherwise
	 */
	private function is_empty_htaccess_value( $value ) {
		return $value === null || $value === '' || ( is_string( $value ) && trim( $value ) === '' );
	}
	
	/**
	 * Validate a stack before execution
	 * @return bool True if valid, false otherwise
	 */
	public function validate_stack() {
		$this->clear_errors();
		
		// Validate wp-config stack
		if( $this->has_wp_config_changes() ) {
			foreach( $this->wp_config_stack as $stack_key => $change ) {
				if( isset( $change['type'] ) && $change['type'] === 'ini_directive' ) {
					// Validate ini directive
					if( !$this->validate_ini_directive_name( $change['directive_name'] ) ) {
						return false;
					}
				}
				else {
					// Validate constant
					if( !$this->validate_constant_name( $stack_key ) ) {
						return false;
					}
				}
				
				if( !in_array( $change['mode'], [ self::STACK_MODE_REPLACE, self::STACK_MODE_APPEND, self::STACK_MODE_REMOVE ] ) ) {
					$this->add_error( "Invalid mode '{$change['mode']}' for {$stack_key}" );
					
					return false;
				}
			}
		}
		
		// Validate htaccess stack (existing logic)
		if( $this->has_htaccess_changes() ) {
			foreach( $this->htaccess_stack as $rule_name => $change ) {
				if( !$this->validate_rule_name( $rule_name ) ) {
					return false;
				}
				
				if( !in_array( $change['mode'], [ self::STACK_MODE_REPLACE, self::STACK_MODE_APPEND, self::STACK_MODE_REMOVE ] ) ) {
					$this->add_error( "Invalid mode '{$change['mode']}' for rule {$rule_name}" );
					
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Remove content between markers
	 * @param string $content File content
	 * @param string $start_marker Start marker
	 * @param string $end_marker End marker
	 * @return string Content with block removed
	 */
	private function remove_marker_block( $content, $start_marker, $end_marker ) {
		$pattern = '/\n?' . preg_quote( $start_marker, '/' ) . '.*?' . preg_quote( $end_marker, '/' ) . '\n?/s';
		
		return preg_replace( $pattern, '', $content );
	}
	
	/**
	 * Remove legacy constant definitions
	 * @param string $content File content
	 * @param string $constant Constant name
	 * @return string Content with legacy definitions removed
	 */
	private function remove_legacy_constant_definitions( $content, $constant ) {
		// Remove define() statements
		$pattern = '/\n?define\s*\(\s*[\'"]' . preg_quote( $constant, '/' ) . '[\'"]\s*,.*?\)\s*;?\n?/i';
		$content = preg_replace( $pattern, '', $content );
		
		// Remove ini_set() for relevant constants
		if( in_array( $constant, [ 'WP_MEMORY_LIMIT', 'WP_MAX_MEMORY_LIMIT' ] ) ) {
			$ini_name = $constant === 'WP_MEMORY_LIMIT' ? 'memory_limit' : 'memory_limit';
			$pattern  = '/\n?ini_set\s*\(\s*[\'"]' . preg_quote( $ini_name, '/' ) . '[\'"]\s*,.*?\)\s*;?\n?/i';
			$content  = preg_replace( $pattern, '', $content );
		}
		
		return $content;
	}
	
	/**
	 * Generate wp-config.php block
	 * @param string $constant Constant name
	 * @param mixed  $value Constant value
	 * @param string $start_marker Start marker
	 * @param string $end_marker End marker
	 * @return string Generated block
	 */
	private function generate_wp_config_block( $constant, $value, $start_marker, $end_marker ) {
		$sanitized_value = $this->sanitize_constant_value( $value );
		
		$block = $start_marker . "\n";
		$block .= "define('" . $constant . "', " . $sanitized_value . ");\n";
		$block .= $end_marker;
		
		return $block;
	}
	
	/**
	 * Generates a formatted .htaccess block with specific start and end markers.
	 * @param string $rule_content The content to be placed within the .htaccess block.
	 * @param string $start_marker The marker that denotes the beginning of the block.
	 * @param string $end_marker The marker that denotes the end of the block.
	 * @return string Returns the complete .htaccess block as a formatted string.
	 */
	private function generate_htaccess_block( $rule_content, string $start_marker, string $end_marker ): string {
		$sanitized_content = $this->sanitize_htaccess_content( $rule_content );
		
		$block = $start_marker . "\n";
		$block .= trim( $sanitized_content ) . "\n";
		$block .= $end_marker;
		
		return $block;
	}
	
	/**
	 * Insert wp-config block into content
	 * @param string $content Current content
	 * @param string $block Block to insert
	 * @return string Updated content
	 */
	private function insert_wp_config_block( $content, $block ) {
		// Find the position to insert (before the last occurrence of "<?php" or at the end)
		$insert_pos = strrpos( $content, '<?php' );
		if( $insert_pos === false ) {
			// If no <?php found, insert at the beginning
			return "<?php\n" . $block . "\n" . $content;
		}
		
		// Insert after the opening <?php tag
		$insert_pos += 5; // Length of "<?php"
		
		return substr( $content, 0, $insert_pos ) . "\n" . $block . "\n" . substr( $content, $insert_pos );
	}
	
	/**
	 * Validate constant name
	 * @param string $constant Constant name
	 * @return bool True if valid, false otherwise
	 */
	private function validate_constant_name( $constant ) {
		if( !is_string( $constant ) || empty( $constant ) ) {
			$this->add_error( 'Invalid constant name' );
			
			return false;
		}
		
		if( !preg_match( '/^[A-Z_][A-Z0-9_]*$/', $constant ) ) {
			$this->add_error( 'Constant name must contain only uppercase letters, numbers, and underscores' );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validate rule name
	 * @param string $rule_name Rule name
	 * @return bool True if valid, false otherwise
	 */
	private function validate_rule_name( $rule_name ) {
		if( !is_string( $rule_name ) || empty( $rule_name ) ) {
			$this->add_error( 'Invalid rule name' );
			
			return false;
		}
		
		if( !preg_match( '/^[A-Z_][A-Z0-9_]*$/', $rule_name ) ) {
			$this->add_error( 'Rule name must contain only uppercase letters, numbers, and underscores' );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validate constants array
	 * @param array $constants Constants to validate
	 * @return bool True if valid, false otherwise
	 */
	private function validate_constants( $constants ) {
		if( !is_array( $constants ) ) {
			$this->add_error( 'Constants must be an array' );
			
			return false;
		}
		
		foreach( $constants as $constant => $value ) {
			if( !$this->validate_constant_name( $constant ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Validate htaccess rules array
	 * @param array $rules Rules to validate
	 * @return bool True if valid, false otherwise
	 */
	private function validate_htaccess_rules( $rules ) {
		if( !is_array( $rules ) ) {
			$this->add_error( 'Rules must be an array' );
			
			return false;
		}
		
		foreach( $rules as $rule_name => $content ) {
			if( !$this->validate_rule_name( $rule_name ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Sanitize constant value for wp-config.php
	 * @param mixed $value Value to sanitize
	 * @return string Sanitized value
	 */
	private function sanitize_constant_value( $value ) {
		if( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}
		else if( is_numeric( $value ) ) {
			return (string) $value;
		}
		else if( is_string( $value ) ) {
			return "'" . addslashes( $value ) . "'";
		}
		else {
			return "'" . addslashes( (string) $value ) . "'";
		}
	}
	
	/**
	 * Sanitize .htaccess content
	 * @param string $content Content to sanitize
	 * @return string Sanitized content
	 */
	private function sanitize_htaccess_content( $content ) {
		if( !is_string( $content ) ) {
			return '';
		}
		
		// Remove potential PHP code injection
		$content = preg_replace( '/<\?php.*?\?>/si', '', $content );
		$content = preg_replace( '/<\?.*?\?>/si', '', $content );
		
		// Remove HTML tags that are NOT Apache directives
		// This regex matches HTML tags but excludes Apache directive syntax
		$content = preg_replace( '/<(?!\/?(?:Files|Directory|Location|VirtualHost|IfModule|IfDefine|Limit|LimitExcept|RequireAll|RequireAny|RequireNone)(?:\s|>))[^>]*>/i', '', $content );
		
		// Remove any remaining script tags or dangerous content
		$content = preg_replace( '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content );
		$content = preg_replace( '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content );
		
		// Remove null bytes and other control characters except newlines and tabs
		$content = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content );
		
		return trim( $content );
	}
	
	/**
	 * Perform atomic write operation
	 * @param string $file_path Path to file
	 * @param string $content Content to write
	 * @param string $file_type Type of file (wp-config or htaccess)
	 * @return bool True on success, false on failure
	 */
	private function atomic_write( $file_path, $content, $file_type ) {
		// Create temporary file
		$temp_file = $file_path . '.tmp.' . uniqid();
		
		// Write to temporary file
		if( !$this->filesystem->put_contents( $temp_file, $content, FS_CHMOD_FILE ) ) {
			$this->add_error( 'Failed to write temporary file' );
			
			return false;
		}
		
		// Validate temporary file
		if( !$this->validate_file( $temp_file, $file_type ) ) {
			$this->filesystem->delete( $temp_file );
			
			return false;
		}
		
		// Atomic move
		if( !$this->filesystem->move( $temp_file, $file_path, true ) ) {
			$this->add_error( 'Failed to move temporary file to final location' );
			$this->filesystem->delete( $temp_file );
			
			// Restore from backup on failure
			$this->restore_from_backup( $file_type );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validate file content
	 * @param string $file_path Path to file
	 * @param string $file_type Type of file
	 * @return bool True if valid, false otherwise
	 */
	private function validate_file( $file_path, $file_type ) {
		// Check if file exists and is not empty
		if( !$this->filesystem->exists( $file_path ) || $this->filesystem->size( $file_path ) === 0 ) {
			$this->add_error( 'File is empty or does not exist' );
			
			return false;
		}
		
		if( $file_type === 'wp-config' ) {
			return $this->validate_wp_config_file( $file_path );
		}
		else if( $file_type === 'htaccess' ) {
			return $this->validate_htaccess_file( $file_path );
		}
		
		return true;
	}
	
	/**
	 * Validate wp-config.php file
	 * @param string $file_path Path to file
	 * @return bool True if valid, false otherwise
	 */
	private function validate_wp_config_file( $file_path ) {
		// Check PHP syntax
		if( !$this->check_php_syntax( $file_path ) ) {
			return false;
		}
		
		// Check for essential WordPress constants
		$content             = $this->filesystem->get_contents( $file_path );
		$essential_constants = [ 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ];
		
		foreach( $essential_constants as $constant ) {
			if( strpos( $content, $constant ) === false ) {
				$this->add_error( "Essential constant {$constant} not found in wp-config.php" );
				
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Validate .htaccess file
	 * @param string $file_path Path to file
	 * @return bool True if valid, false otherwise
	 */
	private function validate_htaccess_file( $file_path ) {
		$content = $this->filesystem->get_contents( $file_path );
		
		// Basic syntax validation for .htaccess
		$lines = explode( "\n", $content );
		foreach( $lines as $line_num => $line ) {
			$line = trim( $line );
			if( empty( $line ) || $line[0] === '#' ) {
				continue;
			}
			
			// Check for common syntax errors
			if( strpos( $line, '<' ) === 0 && strpos( $line, '>' ) === false ) {
				$this->add_error( "Unclosed directive on line " . ( $line_num + 1 ) );
				
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check PHP syntax using multiple fallback methods
	 * @param string $file_path Path to PHP file
	 * @return bool True if syntax is valid, false otherwise
	 */
	private function check_php_syntax( $file_path ) {
		// Method 1: Try shell_exec if available
		if( function_exists( 'shell_exec' ) && !$this->is_function_disabled( 'shell_exec' ) ) {
			$output = shell_exec( "php -l " . escapeshellarg( $file_path ) . " 2>&1" );
			
			if( $output !== null && strpos( $output, 'No syntax errors' ) !== false ) {
				return true;
			}
			else if( $output !== null && strpos( $output, 'Parse error' ) !== false ) {
				$this->add_error( 'PHP syntax error detected via shell_exec: ' . $output );
				
				return false;
			}
		}
		
		// Method 2: Try exec if available
		if( function_exists( 'exec' ) && !$this->is_function_disabled( 'exec' ) ) {
			$output     = [];
			$return_var = 0;
			exec( "php -l " . escapeshellarg( $file_path ) . " 2>&1", $output, $return_var );
			
			if( $return_var === 0 && !empty( $output ) ) {
				$output_string = implode( ' ', $output );
				if( strpos( $output_string, 'No syntax errors' ) !== false ) {
					return true;
				}
				else if( strpos( $output_string, 'Parse error' ) !== false ) {
					$this->add_error( 'PHP syntax error detected via exec: ' . $output_string );
					
					return false;
				}
			}
		}
		
		// Method 3: Use PHP's tokenizer as fallback
		if( function_exists( 'token_get_all' ) ) {
			return $this->check_php_syntax_with_tokenizer( $file_path );
		}
		
		// Method 4: Basic regex-based syntax check as last resort
		return $this->check_php_syntax_basic( $file_path );
	}
	
	/**
	 * Check if a function is disabled
	 * @param string $function_name Function name to check
	 * @return bool True if disabled, false if available
	 */
	private function is_function_disabled( $function_name ) {
		$disabled_functions = explode( ',', ini_get( 'disable_functions' ) );
		$disabled_functions = array_map( 'trim', $disabled_functions );
		
		return in_array( $function_name, $disabled_functions );
	}
	
	/**
	 * Check PHP syntax using tokenizer
	 * @param string $file_path Path to PHP file
	 * @return bool True if syntax appears valid, false otherwise
	 */
	private function check_php_syntax_with_tokenizer( $file_path ) {
		$content = $this->filesystem->get_contents( $file_path );
		
		if( $content === false ) {
			$this->add_error( 'Failed to read file for tokenizer syntax check' );
			
			return false;
		}
		
		$old_error_reporting = error_reporting( 0 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
		
		try {
			$tokens = @token_get_all( $content );
			
			if( $tokens === false ) {
				$this->add_error( 'PHP tokenizer failed to parse file' );
				error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
				
				return false;
			}
			
			// Check for basic syntax issues
			$brace_count   = 0;
			$paren_count   = 0;
			$bracket_count = 0;
			
			foreach( $tokens as $token ) {
				if( is_array( $token ) ) {
					if( $token[0] === T_BAD_CHARACTER ) {
						$this->add_error( 'Bad character found in PHP file' );
						error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
						
						return false;
					}
				}
				else {
					switch( $token ) {
						case '{':
							$brace_count++;
							break;
						case '}':
							$brace_count--;
							break;
						case '(':
							$paren_count++;
							break;
						case ')':
							$paren_count--;
							break;
						case '[':
							$bracket_count++;
							break;
						case ']':
							$bracket_count--;
							break;
					}
				}
			}
			
			if( $brace_count !== 0 || $paren_count !== 0 || $bracket_count !== 0 ) {
				$this->add_error( 'Unmatched braces, parentheses, or brackets in PHP file' );
				error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
				
				return false;
			}
			
			error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
			
			return true;
		}
		catch( \Exception $e ) {
			$this->add_error( 'Error checking PHP syntax: ' . $e->getMessage() );
			error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
			
			return false;
		}
	}
	
	/**
	 * Basic regex-based PHP syntax check (last resort)
	 * @param string $file_path Path to PHP file
	 * @return bool True if basic syntax appears valid, false otherwise
	 */
	private function check_php_syntax_basic( $file_path ) {
		$content = $this->filesystem->get_contents( $file_path );
		if( $content === false ) {
			$this->add_error( 'Failed to read file for basic syntax check' );
			
			return false;
		}
		
		// Check for PHP opening tag
		if( strpos( $content, '<?php' ) === false && strpos( $content, '<?' ) === false ) {
			$this->add_error( 'No PHP opening tag found' );
			
			return false;
		}
		
		// Check for balanced quotes, parentheses, and braces
		$checks = [
			"'" => 'single quotes',
			'"' => 'double quotes',
			'(' => 'parentheses',
			'{' => 'braces',
		];
		
		foreach( $checks as $char => $name ) {
			$open_count  = substr_count( $content, $char );
			$close_char  = $char === '(' ? ')' : ( $char === '{' ? '}' : $char );
			$close_count = substr_count( $content, $close_char );
			
			if( $open_count !== $close_count ) {
				$this->add_error( "Unmatched {$name} detected" );
				
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Create backup of specified file
	 * @param string $file_type Type of file (wp-config or htaccess)
	 * @return bool True on success, false on failure
	 */
	public function create_backup( $file_type ) {
		if( $file_type === 'wp-config' ) {
			$source_path = $this->wp_config_path;
			$backup_path = $this->wp_config_backup_path;
		}
		else if( $file_type === 'htaccess' ) {
			$source_path = $this->htaccess_path;
			$backup_path = $this->htaccess_backup_path;
		}
		else {
			$this->add_error( 'Invalid file type for backup' );
			
			return false;
		}
		
		// Don't overwrite existing backup
		if( $this->filesystem->exists( $backup_path ) ) {
			return true;
		}
		
		// Create backup only if source file exists
		if( !$this->filesystem->exists( $source_path ) ) {
			if( $file_type === 'htaccess' ) {
				// .htaccess might not exist, that's okay
				return true;
			}
			else {
				$this->add_error( "Source file {$source_path} does not exist" );
				
				return false;
			}
		}
		
		if( !$this->filesystem->copy( $source_path, $backup_path ) ) {
			$this->add_error( "Failed to create backup for {$file_type}" );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Restore file from backup
	 * @param string $file_type Type of file (wp-config or htaccess)
	 * @return bool True on success, false on failure
	 */
	public function restore_from_backup( $file_type ) {
		if( $file_type === 'wp-config' ) {
			$backup_path = $this->wp_config_backup_path;
			$target_path = $this->wp_config_path;
		}
		else if( $file_type === 'htaccess' ) {
			$backup_path = $this->htaccess_backup_path;
			$target_path = $this->htaccess_path;
		}
		else {
			$this->add_error( 'Invalid file type for restoration' );
			
			return false;
		}
		
		// Check if backup exists
		if( !$this->filesystem->exists( $backup_path ) ) {
			$this->add_error( "Backup file {$backup_path} does not exist" );
			
			return false;
		}
		
		// Read backup content
		$backup_content = $this->filesystem->get_contents( $backup_path );
		if( $backup_content === false ) {
			$this->add_error( "Failed to read backup file {$backup_path}" );
			
			return false;
		}
		
		// Use atomic write to update the target file with backup content
		return $this->atomic_write( $target_path, $backup_content, $file_type );
	}
	
	/**
	 * Restore all files from backup and clean up
	 * @return bool True on success, false on failure
	 */
	public function restore_all_from_backup() {
		$success = true;
		
		// Restore wp-config.php if backup exists
		if( $this->filesystem->exists( $this->wp_config_backup_path ) ) {
			if( !$this->restore_from_backup( 'wp-config' ) ) {
				$success = false;
				error_log( 'AdminEase: Failed to restore wp-config.php from backup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			else {
				error_log( 'AdminEase: wp-config.php restored successfully from backup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
		else {
			error_log( 'AdminEase: wp-config.php backup not found, skipping restoration' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
		
		// Clear errors before next operation
		$this->clear_errors();
		
		// Restore .htaccess if backup exists
		if( $this->filesystem->exists( $this->htaccess_backup_path ) ) {
			if( !$this->restore_from_backup( 'htaccess' ) ) {
				$success = false;
				error_log( 'AdminEase: Failed to restore .htaccess from backup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			else {
				error_log( 'AdminEase: .htaccess restored successfully from backup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
		else {
			error_log( 'AdminEase: .htaccess backup not found, skipping restoration' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
		
		return $success;
	}
	
	/**
	 * Clean up all AdminEase markers and blocks
	 * @return bool True on success, false on failure
	 */
	public function cleanup_all_markers() {
		$success = true;
		
		// Method 1: Try to restore from backup first (preferred)
		if( $this->filesystem->exists( $this->wp_config_backup_path ) || $this->filesystem->exists( $this->htaccess_backup_path ) ) {
			error_log( 'AdminEase: Attempting to restore from backup during cleanup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			$restore_success = $this->restore_all_from_backup();
			
			if( $restore_success ) {
				error_log( 'AdminEase: Successfully restored from backup during cleanup' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				
				return true;
			}
			else {
				error_log( 'AdminEase: Backup restoration failed, falling back to marker removal' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				$success = false;
			}
		}
		
		// Method 2: Fallback to marker removal if backup restoration fails
		// Clean wp-config.php
		if( $this->filesystem->exists( $this->wp_config_path ) ) {
			$content = $this->filesystem->get_contents( $this->wp_config_path );
			if( $content !== false ) {
				$cleaned_content = $this->remove_all_adminease_markers( $content );
				if( !$this->atomic_write( $this->wp_config_path, $cleaned_content, 'wp-config' ) ) {
					$success = false;
					error_log( 'AdminEase: Failed to clean wp-config.php markers' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				else {
					error_log( 'AdminEase: wp-config.php markers cleaned successfully' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}
		
		// Clean .htaccess
		if( $this->filesystem->exists( $this->htaccess_path ) ) {
			$content = $this->filesystem->get_contents( $this->htaccess_path );
			if( $content !== false ) {
				$cleaned_content = $this->remove_all_adminease_markers( $content );
				if( !$this->atomic_write( $this->htaccess_path, $cleaned_content, 'htaccess' ) ) {
					$success = false;
					error_log( 'AdminEase: Failed to clean .htaccess markers' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				else {
					error_log( 'AdminEase: .htaccess markers cleaned successfully' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}
		
		return $success;
	}
	
	/**
	 * Safe restoration method that validates backup before restoration
	 * @param string $file_type Type of file (wp-config or htaccess)
	 * @return bool True on success, false on failure
	 */
	public function safe_restore_from_backup( $file_type ) {
		if( $file_type === 'wp-config' ) {
			$backup_path = $this->wp_config_backup_path;
			$target_path = $this->wp_config_path;
		}
		else if( $file_type === 'htaccess' ) {
			$backup_path = $this->htaccess_backup_path;
			$target_path = $this->htaccess_path;
		}
		else {
			$this->add_error( 'Invalid file type for restoration' );
			
			return false;
		}
		
		// Check if backup exists
		if( !$this->filesystem->exists( $backup_path ) ) {
			$this->add_error( "Backup file {$backup_path} does not exist" );
			
			return false;
		}
		
		// Read and validate backup content
		$backup_content = $this->filesystem->get_contents( $backup_path );
		if( $backup_content === false ) {
			$this->add_error( "Failed to read backup file {$backup_path}" );
			
			return false;
		}
		
		// Validate backup content before restoration
		if( $file_type === 'wp-config' ) {
			if( !$this->validate_wp_config_content( $backup_content ) ) {
				$this->add_error( "Backup wp-config.php content validation failed" );
				
				return false;
			}
		}
		else if( $file_type === 'htaccess' ) {
			if( !$this->validate_htaccess_content( $backup_content ) ) {
				$this->add_error( "Backup .htaccess content validation failed" );
				
				return false;
			}
		}
		
		// Create a temporary backup of current file before restoration
		$temp_backup_path = $target_path . '.pre_restore_backup.' . time();
		$current_content  = $this->filesystem->get_contents( $target_path );
		if( $current_content !== false ) {
			$this->filesystem->put_contents( $temp_backup_path, $current_content );
		}
		
		// Attempt restoration using atomic write
		$restore_success = $this->atomic_write( $target_path, $backup_content, $file_type );
		
		if( $restore_success ) {
			// Clean up temporary backup on success
			if( $this->filesystem->exists( $temp_backup_path ) ) {
				$this->filesystem->delete( $temp_backup_path );
			}
			
			return true;
		}
		else {
			// Restore from temporary backup on failure
			if( $this->filesystem->exists( $temp_backup_path ) ) {
				$temp_content = $this->filesystem->get_contents( $temp_backup_path );
				if( $temp_content !== false ) {
					$this->filesystem->put_contents( $target_path, $temp_content );
				}
				$this->filesystem->delete( $temp_backup_path );
			}
			
			return false;
		}
	}
	
	/**
	 * Validate wp-config.php content
	 * @param string $content Content to validate
	 * @return bool True if valid, false otherwise
	 */
	private function validate_wp_config_content( $content ) {
		// Check for essential WordPress constants
		$essential_constants = [ 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ];
		
		foreach( $essential_constants as $constant ) {
			if( strpos( $content, $constant ) === false ) {
				$this->add_error( "Essential constant {$constant} not found in wp-config.php content" );
				
				return false;
			}
		}
		
		// Check for PHP opening tag
		if( strpos( $content, '<?php' ) === false ) {
			$this->add_error( 'No PHP opening tag found in wp-config.php content' );
			
			return false;
		}
		
		// Basic syntax check using tokenizer if available
		if( function_exists( 'token_get_all' ) ) {
			$old_error_reporting = error_reporting( 0 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
			
			try {
				$tokens = @token_get_all( $content );
				if( $tokens === false ) {
					$this->add_error( 'PHP tokenizer failed to parse wp-config.php content' );
					error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
					
					return false;
				}
			}
			catch( Exception $e ) {
				$this->add_error( 'Error validating wp-config.php content: ' . $e->getMessage() );
				error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
				
				return false;
			}
			
			error_reporting( $old_error_reporting ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
		}
		
		return true;
	}
	
	/**
	 * Validate .htaccess content
	 * @param string $content Content to validate
	 * @return bool True if valid, false otherwise
	 */
	private function validate_htaccess_content( $content ) {
		// Basic syntax validation for .htaccess
		$lines = explode( "\n", $content );
		foreach( $lines as $line_num => $line ) {
			$line = trim( $line );
			if( empty( $line ) || $line[0] === '#' ) {
				continue;
			}
			
			// Check for common syntax errors
			if( strpos( $line, '<' ) === 0 && strpos( $line, '>' ) === false ) {
				$this->add_error( "Unclosed directive on line " . ( $line_num + 1 ) . " in .htaccess content" );
				
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Force restoration method for emergency situations
	 * This method bypasses some safety checks for critical restoration
	 * @param string $file_type Type of file (wp-config or htaccess)
	 * @return bool True on success, false on failure
	 */
	public function force_restore_from_backup( $file_type ) {
		if( $file_type === 'wp-config' ) {
			$backup_path = $this->wp_config_backup_path;
			$target_path = $this->wp_config_path;
		}
		else if( $file_type === 'htaccess' ) {
			$backup_path = $this->htaccess_backup_path;
			$target_path = $this->htaccess_path;
		}
		else {
			$this->add_error( 'Invalid file type for restoration' );
			
			return false;
		}
		
		// Check if backup exists
		if( !$this->filesystem->exists( $backup_path ) ) {
			$this->add_error( "Backup file {$backup_path} does not exist" );
			
			return false;
		}
		
		// Read backup content
		$backup_content = $this->filesystem->get_contents( $backup_path );
		if( $backup_content === false ) {
			$this->add_error( "Failed to read backup file {$backup_path}" );
			
			return false;
		}
		
		// Force write backup content to target file
		if( !$this->filesystem->put_contents( $target_path, $backup_content, FS_CHMOD_FILE ) ) {
			$this->add_error( "Failed to write backup content to {$target_path}" );
			
			return false;
		}
		
		error_log( "AdminEase: Force restored {$file_type} from backup" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		
		return true;
	}
	
	/**
	 * Check if backups exist and are readable
	 * @return array Status of backup files
	 */
	public function check_backup_status() {
		$status = [
			'wp-config' => [
				'exists'   => false,
				'readable' => false,
				'size'     => 0,
				'modified' => null,
			],
			'htaccess'  => [
				'exists'   => false,
				'readable' => false,
				'size'     => 0,
				'modified' => null,
			],
		];
		
		// Check wp-config backup
		if( $this->filesystem->exists( $this->wp_config_backup_path ) ) {
			$status['wp-config']['exists']   = true;
			$status['wp-config']['readable'] = $this->filesystem->is_readable( $this->wp_config_backup_path );
			$status['wp-config']['size']     = $this->filesystem->size( $this->wp_config_backup_path );
			$status['wp-config']['modified'] = $this->filesystem->mtime( $this->wp_config_backup_path );
		}
		
		// Check htaccess backup
		if( $this->filesystem->exists( $this->htaccess_backup_path ) ) {
			$status['htaccess']['exists']   = true;
			$status['htaccess']['readable'] = $this->filesystem->is_readable( $this->htaccess_backup_path );
			$status['htaccess']['size']     = $this->filesystem->size( $this->htaccess_backup_path );
			$status['htaccess']['modified'] = $this->filesystem->mtime( $this->htaccess_backup_path );
		}
		
		return $status;
	}
	
	/**
	 * Remove all AdminEase markers from content
	 * @param string $content File content
	 * @return string Cleaned content
	 */
	private function remove_all_adminease_markers( $content ) {
		$pattern = '/\n?(?:\/\/|#)\s*ADMINEASE_.*?(?:\/\/|#)\s*ADMINEASE_.*?\n?/s';
		
		return preg_replace( $pattern, '', $content );
	}
	
	/**
	 * Delete backup files
	 * @return bool True on success, false on failure
	 */
	public function delete_backups() {
		$success = true;
		
		if( $this->filesystem->exists( $this->wp_config_backup_path ) ) {
			if( !$this->filesystem->delete( $this->wp_config_backup_path ) ) {
				$success = false;
			}
		}
		
		if( $this->filesystem->exists( $this->htaccess_backup_path ) ) {
			if( !$this->filesystem->delete( $this->htaccess_backup_path ) ) {
				$success = false;
			}
		}
		
		return $success;
	}
	
	/**
	 * Add error message
	 * @param string $message Error message
	 */
	private function add_error( $message ) {
		$this->errors[] = sanitize_text_field( $message );
	}
	
	/**
	 * Get all error messages
	 * @return array Array of error messages
	 */
	public function get_errors() {
		return $this->errors;
	}
	
	/**
	 * Check if there are any errors
	 * @return bool True if there are errors, false otherwise
	 */
	public function has_errors() {
		return !empty( $this->errors );
	}
	
	/**
	 * Clear all error messages
	 */
	public function clear_errors() {
		$this->errors = [];
	}
	
	/**
	 * Get the last error message
	 * @return string|null Last error message or null if no errors
	 */
	public function get_last_error() {
		return end( $this->errors ) ? : null;
	}
	
	/**
	 * Get the WordPress debug log path
	 * @return string Path to debug.log file
	 */
	public function get_debug_log_path() {
		return WP_CONTENT_DIR . '/debug.log';
	}
	
	/**
	 * Read the contents of the debug log file
	 * @return string|false Contents of the debug log or false on failure
	 */
	public function read_debug_log() {
		$debug_log_path = $this->get_debug_log_path();
		
		if( !$this->filesystem->exists( $debug_log_path ) ) {
			return false;
		}
		
		return $this->filesystem->get_contents( $debug_log_path );
	}
	
	/**
	 * Handles the process of downloading the debug log file.
	 * This method retrieves the debug log file's content and triggers a file download
	 * in the browser. It verifies the existence of the file and ensures its contents
	 * are accessible before sending appropriate headers and outputting the file.
	 * @return bool Returns false if the debug log file does not exist or its contents cannot be retrieved.
	 */
	public function download_debug_log(): bool {
		$debug_log_path = $this->get_debug_log_path();
		
		if( !$this->filesystem->exists( $debug_log_path ) ) {
			return false;
		}
		
		$content = $this->filesystem->get_contents( $debug_log_path );
		
		if( false === $content ) {
			return false;
		}
		
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="debug.log"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $content ) );
		
		return $content;
	}
	
	/**
	 * Clear the contents of the debug log file
	 * @return bool True on success, false on failure
	 */
	public function clear_debug_log() {
		$debug_log_path = $this->get_debug_log_path();
		
		if( !$this->filesystem->exists( $debug_log_path ) ) {
			// File doesn't exist, nothing to clear
			return true;
		}
		
		// Clear the file by writing an empty string
		return $this->filesystem->put_contents( $debug_log_path, '', FS_CHMOD_FILE );
	}
	
	/**
	 * Safe generic file reader using WP_Filesystem
	 * * @param string $file_path Full path to the file.
	 * @return string|false File content or false on failure.
	 */
	public function get_file_content( string $file_path ) {
		// Ensure filesystem is initialized
		if( !isset( $this->filesystem ) && !$this->init_filesystem() ) {
			return false;
		}
		
		if( !$this->filesystem->exists( $file_path ) || !$this->filesystem->is_readable( $file_path ) ) {
			return false;
		}
		
		return $this->filesystem->get_contents( $file_path );
	}
	
	/**
	 * Parse PHP shorthand byte notation (K, M, G) to bytes.
	 * @param string $value The shorthand value (e.g., '256M', '1G').
	 * @return int The value in bytes.
	 */
	public function parse_size_to_bytes( string $value ): int {
		$value = trim( $value );
		$last  = strtolower( $value[ strlen( $value ) - 1 ] );
		$value = (int) $value;
		
		switch( $last ) {
			case 'g':
				$value *= 1024;
			// Fall through.
			case 'm':
				$value *= 1024;
			// Fall through.
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}
	
	/**
	 * Get server limits including memory_limit.
	 * @return array Array containing parsed server limits.
	 */
	public function get_server_limits(): array {
		$memory_limit = ini_get( 'memory_limit' );
		
		// Handle unlimited memory (-1)
		if( '-1' === $memory_limit || -1 === (int) $memory_limit ) {
			$memory_limit_bytes = PHP_INT_MAX;
		}
		else if( false === $memory_limit || '' === $memory_limit ) {
			// Fallback if ini_get fails (rare, but possible on some restrictive hosts)
			$memory_limit_bytes = 128 * 1024 * 1024; // Default to 128MB
			$memory_limit       = '128M';
		}
		else {
			$memory_limit_bytes = $this->parse_size_to_bytes( $memory_limit );
		}
		
		return [
			'memory_limit'     => $memory_limit_bytes,
			'memory_limit_raw' => $memory_limit,
		];
	}
	
	/**
	 * Read last N lines of debug log efficiently (for large files).
	 * Uses reverse file reading to get last lines without loading entire file.
	 * @param int $lines Number of lines to read from end of file (default: 1000).
	 * @return array Array with content, file size, and line count.
	 */
	public function read_debug_log_tail( int $lines = 1000 ): array {
		$path = $this->get_debug_log_path();
		
		if( !file_exists( $path ) || !is_readable( $path ) ) {
			return [
				'content'     => '',
				'file_size'   => 0,
				'lines'       => 0,
				'truncated'   => false,
				'total_lines' => 0,
			];
		}
		
		$file_size = filesize( $path );
		
		if( 0 === $file_size ) {
			return [
				'content'     => '',
				'file_size'   => 0,
				'lines'       => 0,
				'truncated'   => false,
				'total_lines' => 0,
			];
		}
		
		$handle = fopen( $path, 'rb' );
		
		if( false === $handle ) {
			return [
				'content'     => '',
				'file_size'   => $file_size,
				'lines'       => 0,
				'truncated'   => false,
				'total_lines' => 0,
			];
		}
		
		$buffer      = 4096;
		$chunk       = '';
		$lines_found = 0;
		
		// Start from end of file
		fseek( $handle, -1, SEEK_END );
		
		// Read backwards until we have enough lines
		for( $pos = ftell( $handle ); $pos >= 0 && $lines_found < $lines; $pos -= $buffer ) {
			$read_size = min( $buffer, $pos + 1 );
			
			fseek( $handle, max( 0, $pos - $buffer + 1 ), SEEK_SET );
			
			$chunk       = fread( $handle, $read_size ) . $chunk;
			$lines_found = substr_count( $chunk, "\n" );
		}
		
		fclose( $handle );
		
		// Get total line count for truncated indicator
		$total_lines = substr_count( file_get_contents( $path ), "\n" );
		$truncated   = $total_lines > $lines;
		
		// Get last N lines
		$lines_array = explode( "\n", $chunk );
		$output      = implode( "\n", array_slice( $lines_array, -$lines ) );
		
		return [
			'content'     => $output,
			'file_size'   => $file_size,
			'lines'       => min( $lines, $lines_found ),
			'truncated'   => $truncated,
			'total_lines' => $total_lines,
		];
	}
	
	/**
	 * Get debug log file information including size and warnings.
	 * @return array File information with warning flags.
	 */
	public function get_debug_log_info(): array {
		$path = $this->get_debug_log_path();
		
		if( !file_exists( $path ) ) {
			return [
				'exists'         => false,
				'size'           => 0,
				'size_formatted' => '0 B',
				'percentage'     => 0,
				'warning'        => false,
				'critical'       => false,
			];
		}
		
		$file_size     = filesize( $path );
		$server_limits = $this->get_server_limits();
		$memory_limit  = $server_limits['memory_limit'];
		
		// Calculate percentage of memory limit
		$percentage = ( $memory_limit > 0 ) ? ( $file_size / $memory_limit ) * 100 : 0;
		
		// Warning at 70%, critical at 90%
		$warning  = $percentage >= 70;
		$critical = $percentage >= 90;
		
		return [
			'exists'         => true,
			'size'           => $file_size,
			'size_formatted' => size_format( $file_size ),
			'percentage'     => $percentage,
			'warning'        => $warning,
			'critical'       => $critical,
		];
	}
	
	/**
	 * Read debug log with pagination support (chunk-based reading).
	 * @param int $page Current page number (1-based).
	 * @param int $chunk_size Size of each chunk in bytes (default: 51200 = 50KB).
	 * @return array|false Array with content and pagination metadata, or false on failure.
	 */
	public function read_debug_log_paginated( int $page = 1, int $chunk_size = 51200 ) {
		$path = $this->get_debug_log_path();
		
		if( !file_exists( $path ) || !is_readable( $path ) ) {
			return false;
		}
		
		$file_size = filesize( $path );
		
		if( 0 === $file_size ) {
			return [
				'content'      => '',
				'current_page' => 1,
				'total_pages'  => 1,
				'file_size'    => 0,
				'chunk_size'   => $chunk_size,
			];
		}
		
		// Calculate total pages
		$total_pages = (int) ceil( $file_size / $chunk_size );
		
		// Validate page number
		$page = max( 1, min( $page, $total_pages ) );
		
		// Calculate offset
		$offset = ( $page - 1 ) * $chunk_size;
		
		// Open file for reading
		$handle = fopen( $path, 'rb' );
		
		if( false === $handle ) {
			return false;
		}
		
		// Seek to offset
		if( fseek( $handle, $offset ) !== 0 ) {
			fclose( $handle );
			
			return false;
		}
		
		// Read chunk
		$content = fread( $handle, $chunk_size );
		fclose( $handle );
		
		if( false === $content ) {
			return false;
		}
		
		return [
			'content'      => $content,
			'current_page' => $page,
			'total_pages'  => $total_pages,
			'file_size'    => $file_size,
			'chunk_size'   => $chunk_size,
		];
	}
}