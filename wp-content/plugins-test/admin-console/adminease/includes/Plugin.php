<?php
namespace AdminEase;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Adds additional action links to the plugin on the plugins page.
 *
 * @param array $links Default plugin action links.
 *
 * @return array Modified list of plugin action links.
 */
final class Plugin {
	private static ?Plugin $instance = null;
	private static $settings;
	public static ?FileHandler $FileHandler = null;
	
	/**
	 * Handles cloning of the object in a restricted manner.
	 * This method prevents cloning of the object by triggering a warning or error.
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'adminease' ), ADMINEASE_VERSION ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	/**
	 * Handles attempts to unserialize an instance of the class.
	 * This method is used to prevent unserializing the class, ensuring the integrity and controlled lifecycle of the object.
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'adminease' ), ADMINEASE_VERSION ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	/**
	 * Handles plugin uninstallation.
	 * Currently does nothing - plugin cleanup is handled during deactivation.
	 *
	 * @return void
	 */
	public function uninstall() {
		// Do nothing - all cleanup is handled during deactivation
	}
	
	private function __construct() {
		self::$settings = get_option( 'adminease_settings', [] );
		
		if( is_admin() ) {
			self::$FileHandler = FileHandler::get_instance();
		}
		
		Features::get_instance();
		
		$this->add_actions();
		$this->add_filters();
	}
	
	/**
	 * Retrieve the singleton instance of the class.
	 *
	 * @return self The single instance of the class.
	 */
	public static function get_instance(): ?Plugin {
		if( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Handles the deactivation process of the plugin, which includes restoring files,
	 * cleaning up markers, and optionally deleting backup files.
	 * This method performs the following actions:
	 * - Restores all files from backup.
	 * - Cleans up any remaining markers left by the plugin.
	 * - Deletes backup files if the operation is successful (optional step).
	 * - Logs errors in case any process during deactivation fails.
	 *
	 * @return void
	 */
	
	/**
	 * Registers WordPress actions for the admin interface.
	 *
	 * @return void
	 */
	private function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		
		add_action( 'wp_ajax_adminease_save_settings', [ $this, 'ajax_save_settings' ] );
		add_action( 'wp_ajax_adminease_toggle_menu_sidebar', [ $this, 'ajax_toggle_menu_sidebar' ] );
		add_action( 'wp_ajax_adminease_toggle_menu_sidebar_minmax', [ $this, 'ajax_toggle_menu_sidebar_minmax' ] );
	}
	
	/**
	 * Adds custom filters for the plugin.
	 *
	 * @return void
	 */
	private function add_filters(): void {
		add_filter( 'adminease_dashboard_section_classes', [ $this, 'adminease_dashboard_section_classes' ] );
		add_filter( 'plugin_action_links_' . ADMINEASE_BASENAME, [ $this, 'action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 4 );
	}
	
	/**
	 * Adds the admin menu for the plugin.
	 *
	 * @return void
	 */
	public function admin_menu(): void {
		add_menu_page(
			ADMINEASE_NAME,
			ADMINEASE_NAME,
			'manage_options',
			ADMINEASE_SLUG,
			function() {
				// Additional permission check if needed
				if( !current_user_can( 'manage_options' ) ) {
					wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'adminease' ) );
				}
				
				include ADMINEASE_DIR . 'partials/dashboard.php';
			},
			ADMINEASE_PLUGIN_URL . 'assets/img/favicon.svg',
			2
		);
	}
	
	/**
	 * Enqueues scripts and styles for the admin dashboard.
	 * This method handles the inclusion of CSS and JavaScript files required
	 * for the plugin's functionality in the WordPress admin area. It ensures
	 * assets are properly versioned using file modification times and adds
	 * localized script data for client-side use.
	 *
	 * @param string $hook The current admin page hook suffix, used to conditionally load assets
	 *                     specific to the plugin's pages or globally applicable assets.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ): void {
		wp_enqueue_style(
			ADMINEASE_NAME . 'Global',
			ADMINEASE_PLUGIN_URL . 'assets/css/AdminEaseGlobal.css',
			[],
			filemtime( ADMINEASE_DIR . 'assets/css/AdminEaseGlobal.css' )
		);
		
		$additional_css = apply_filters( 'adminease_global_inline_css', '' );
		
		if( !empty( $additional_css ) ) {
			// WordPress's recommended approach for CSS sanitization
			$sanitized_css = wp_strip_all_tags( $additional_css );
			
			// Additional security: Remove potentially dangerous CSS constructs
			$sanitized_css = preg_replace( '/javascript\s*:/i', '', $sanitized_css );
			$sanitized_css = preg_replace( '/expression\s*\(/i', '', $sanitized_css );
			$sanitized_css = preg_replace( '/@import\s+/i', '', $sanitized_css );
			
			wp_add_inline_style( ADMINEASE_NAME . 'Global', $sanitized_css );
		}
		
		if( 'toplevel_page_adminease' === $hook ) {
			wp_enqueue_style(
				ADMINEASE_NAME . 'Flexboxgrid',
				ADMINEASE_PLUGIN_URL . 'assets/css/AdminEaseFlexboxgrid.min.css',
				[],
				filemtime( ADMINEASE_DIR . 'assets/css/AdminEaseFlexboxgrid.min.css' )
			);
			
			wp_enqueue_style(
				ADMINEASE_NAME . 'Choices',
				ADMINEASE_PLUGIN_URL . 'assets/css/AdminEaseChoices.min.css',
				[],
				'11.1.0'
			);
			
			wp_enqueue_style(
				ADMINEASE_NAME,
				ADMINEASE_PLUGIN_URL . 'assets/css/AdminEase.css',
				[],
				filemtime( ADMINEASE_DIR . 'assets/css/AdminEase.css' )
			);
			
			wp_enqueue_script(
				ADMINEASE_NAME . 'Choices',
				ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseChoices.min.js',
				[ 'jquery' ],
				'11.1.0',
				true
			);
			
			wp_enqueue_script(
				ADMINEASE_NAME,
				ADMINEASE_PLUGIN_URL . 'assets/js/AdminEase.js',
				[ 'jquery', ADMINEASE_NAME . 'Choices' ],
				filemtime( ADMINEASE_DIR . 'assets/js/AdminEase.js' ),
				true
			);
			
			wp_localize_script(
				ADMINEASE_NAME,
				ADMINEASE_NAME . 'AjaxObj',
				apply_filters( 'adminease_localize_script',
					[
						'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
						'isMobile' => wp_is_mobile(),
						'security' => [
							'saveSettings'            => wp_create_nonce( 'save_settings' ),
							'toggleMenuSidebar'       => wp_create_nonce( 'toggle_menu_sidebar' ),
							'toggleMenuSidebarMinMax' => wp_create_nonce( 'toggle_menu_sidebar_minmax' ),
						],
						'i18n'     => [
							'selectChoicesPlaceholder'       => esc_html__( 'Select countries', 'adminease' ),
							'selectChoicesLoadingText'       => esc_html__( 'Loading...', 'adminease' ),
							'selectChoicesNoResultsText'     => esc_html__( 'No results found', 'adminease' ),
							'selectChoicesNoChoicesText'     => esc_html__( 'No choices to choose from', 'adminease' ),
							'selectChoicesItemSelectText'    => esc_html__( 'Press to select', 'adminease' ),
							'selectChoicesUniqueItemText'    => esc_html__( 'Only unique values can be added', 'adminease' ),
							'selectChoicesCustomAddItemText' => esc_html__( 'Only values matching specific conditions can be added', 'adminease' ),
							'unknownError'                   => esc_html__( 'An unknown error occurred. Refresh the page and try again.', 'adminease' ),
						],
					]
				)
			);
		}
	}
	
	/**
	 * Handles saving settings via AJAX.
	 * This method verifies the security nonce and user permissions, processes the provided data,
	 * sanitizes the settings, and updates the options in the database. Responds with a JSON success or error message.
	 *
	 * @return void
	 */
	public function ajax_save_settings(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'save_settings' ) ) {
			wp_send_json_error( new WP_Error( 'security', esc_html__( 'Error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is validated elsewhere in the method
		parse_str( $_POST['data'], $parsed_data );
		
		if( empty( $parsed_data['adminease'] ) ) {
			wp_send_json_error( new WP_Error( 'data', esc_html__( 'No data was provided', 'adminease' ) ) );
		}
		
		$sanitized_settings = $this->wp_recursive_sanitize( $parsed_data['adminease'] );
		$sanitized_settings = $this->validate_settings( $sanitized_settings );
		
		if( is_wp_error( $sanitized_settings ) ) {
			wp_send_json_error( $sanitized_settings );
		}
		
		update_option( 'adminease_settings', $sanitized_settings );
		
		$reload = $this->save_settings_maybe_reload( self::$settings, $sanitized_settings );
		
		self::$settings = $sanitized_settings;
		
		do_action( 'adminease_settings_saved', $sanitized_settings );
		
		wp_send_json_success( [ 'message' => esc_html__( 'Settings saved successfully', 'adminease' ), 'reload' => $reload ] );
	}
	
	private function save_settings_maybe_reload( array $original_settings, array $sanitized_settings ): bool {
		$reload_required_fields = [
			'posts' => [
				'bulk_delete_posts_enabled',
			],
			'debug' => [
				'network_viewer_enabled',
			],
		];
		
		foreach( $reload_required_fields as $section => $fields ) {
			foreach( $fields as $field ) {
				if( ( $original_settings[ $section ][ $field ] ?? null ) !== ( $sanitized_settings[ $section ][ $field ] ?? null ) ) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Handles the AJAX request to toggle the state of the menu sidebar.
	 * This method validates the request for security, ensures the user has sufficient
	 * permissions, and updates the user metadata to save the sidebar state.
	 *
	 * @return void This method does not return any value. Instead, it responds with
	 *              a JSON success or error message depending on the execution outcome.
	 */
	public function ajax_toggle_menu_sidebar(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'toggle_menu_sidebar' ) ) {
			wp_send_json_error( new WP_Error( 'security', esc_html__( 'Error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$is_active = absint( sanitize_key( wp_unslash( $_POST['data']['isActive'] ?? 0 ) ) );
		
		$user_id = get_current_user_id();
		
		if( empty( $user_id ) ) {
			wp_send_json_error( new WP_Error( 'user', esc_html__( 'Unable to determine current user', 'adminease' ) ) );
		}
		
		update_user_meta( $user_id, 'adminease_menu_sidebar_active', $is_active );
		
		wp_send_json_success();
	}
	
	/**
	 * Handles AJAX requests to toggle the menu sidebar min/max state for a user.
	 * This method verifies the request's security, checks user permissions, and updates
	 * the user meta data to save the menu sidebar's minimized or maximized state.
	 *
	 * @return void Outputs a JSON response indicating success or failure.
	 */
	public function ajax_toggle_menu_sidebar_minmax(): void {
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'toggle_menu_sidebar_minmax' ) ) {
			wp_send_json_error( new WP_Error( 'security', esc_html__( 'Error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$user_id = get_current_user_id();
		
		if( empty( $user_id ) ) {
			wp_send_json_error( new WP_Error( 'user', esc_html__( 'Unable to determine current user', 'adminease' ) ) );
		}
		
		$is_minmax_active = absint( sanitize_key( wp_unslash( $_POST['data']['isMinMaxActive'] ?? 0 ) ) );
		
		update_user_meta( $user_id, 'adminease_menu_sidebar_minmax_active', $is_minmax_active );
		
		wp_send_json_success();
	}
	
	/**
	 * Retrieves the settings or a specific section of settings.
	 *
	 * @param string $section Optional. The section of settings to retrieve. Defaults to an empty string.
	 *
	 * @return mixed Returns the entire settings array if no section is specified, or the specific section of settings if provided. If the section does not exist, an empty array is returned.
	 */
	public static function get_settings( string $section = '' ) {
		if( !empty( $section ) ) {
			return self::$settings[ $section ] ?? [];
		}
		
		return self::$settings;
	}
	
	/**
	 * Recursively sanitize all types of values.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return mixed The sanitized value.
	 */
	public function wp_recursive_sanitize( $value, array $context = [] ) {
		if( is_array( $value ) ) {
			foreach( $value as $key => $item ) {
				$new_context   = array_merge( $context, [ $key ] );
				$value[ $key ] = $this->wp_recursive_sanitize( $item, $new_context );
			}
			
			return $value;
		}
		else if( is_bool( $value ) ) {
			return (bool) $value;
		}
		else if( is_int( $value ) ) {
			return intval( $value );
		}
		else if( is_float( $value ) ) {
			return floatval( $value );
		}
		else if( is_string( $value ) ) {
			if( $this->should_allow_html( $context ) ) {
				$allowed_tags = [
					'br'     => [],
					'b'      => [],
					'strong' => [],
					'em'     => [],
					'i'      => [],
					'u'      => [],
					'p'      => [],
					'span'   => [ 'class' => [] ],
					'a'      => [ 'href' => [], 'target' => [], 'rel' => [] ],
				];
				
				return wp_kses( $value, $allowed_tags );
			}
			
			return sanitize_text_field( $value );
		}
		else {
			return $value;
		}
	}
	
	/**
	 * Determines if a field should allow HTML based on its context path.
	 *
	 * @param array $context The field path (e.g., ['debug', 'maintenance_mode_message']).
	 *
	 * @return bool True if HTML should be allowed, false otherwise.
	 */
	private function should_allow_html( array $context ): bool {
		$html_fields = [
			'maintenance_mode_message',
			'password_protect_site_entry_message',
		];
		
		if( !empty( array_intersect( $html_fields, $context ) ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates and ensures the settings provided are consistent and adhere to predefined rules.
	 *
	 * @param array $sanitized_settings An array of sanitized settings, including memory limit configurations.
	 *
	 * @return array|WP_Error Returns the sanitized settings if validation passes. Returns a WP_Error object if there is a validation error.
	 */
	public function validate_settings( array $sanitized_settings ) {
		foreach( $sanitized_settings as $key => $fields ) {
			switch( $key ) {
				case 'updates-and-notifications':
				{
					$customize_recovery_mode_recipient_email = $fields['customize_recovery_mode_recipient_email'] ?? '';
					
					if( !empty( $customize_recovery_mode_recipient_email ) ) {
						$recovery_mode_recipient_email = $fields['recovery_mode_recipient_email'] ?? '';
						
						if( str_contains( $recovery_mode_recipient_email, ',' ) ) {
							$recovery_mode_recipient_email = array_map( 'trim', explode( ',', $recovery_mode_recipient_email ) );
						}
						
						if( is_array( $recovery_mode_recipient_email ) ) {
							foreach( $recovery_mode_recipient_email as $email ) {
								if( !is_email( $email ) ) {
									return new WP_Error( 'recovery_mode_recipient_email', esc_html__( 'Please enter valid email addresses for recovery mode notifications.', 'adminease' ) );
								}
							}
						}
						else {
							if( !is_email( $recovery_mode_recipient_email ) ) {
								return new WP_Error( 'recovery_mode_recipient_email', esc_html__( 'Please enter a valid email address for recovery mode notifications.', 'adminease' ) );
							}
						}
					}
					
					break;
				}
				case 'performance':
				{
					if( 'other' === $fields['wp_memory_limit'] && '' === $fields['wp_memory_limit_other'] ) {
						return new WP_Error( 'wp_memory_limit_other', esc_html__( 'Memory limit cannot be empty', 'adminease' ) );
					}
					
					if( 'other' === $fields['wp_max_memory_limit'] && '' === $fields['wp_max_memory_limit_other'] ) {
						return new WP_Error( 'wp_max_memory_limit_other', esc_html__( 'Maximum memory limit cannot be empty', 'adminease' ) );
					}
					
					$memory_limit     = Utils::parse_memory_limit( $fields['wp_memory_limit'] );
					$max_memory_limit = Utils::parse_memory_limit( $fields['wp_max_memory_limit'] );
					
					if( $max_memory_limit < $memory_limit && 0 !== $max_memory_limit ) {
						return new WP_Error( 'wp_max_memory_limit', esc_html__( 'Maximum memory limit cannot be smaller than memory limit', 'adminease' ) );
					}

					if( 0 == $fields['max_execution_time'] ) {
						return new WP_Error( 'max_execution_time', esc_html__( 'Max execution time cannot be zero', 'adminease' ) );
					}
					
					break;
				}
				case 'posts':
				{
					if( 'other' === $fields['number_posts_revisions'] && '' === $fields['number_posts_revisions_other'] ) {
						return new WP_Error( 'number_posts_revisions_other', esc_html__( 'Number of revisions cannot be empty', 'adminease' ) );
					}
				}
				case 'users':
				{
					if(
						!empty( $sanitized_settings['users']['force_strong_passwords'] ) &&
						(
							empty( $sanitized_settings['users']['password_minimum_length'] ) ||
							$sanitized_settings['users']['password_minimum_length'] < 8
						)
					) {
						return new WP_Error( 'password_minimum_length', esc_html__( 'Password minimum length must be larger than 8 characters.', 'adminease' ) );
					}
					
					if(
						!empty( $sanitized_settings['users']['force_strong_passwords'] ) &&
						(
							empty( $sanitized_settings['users']['password_maximum_length'] ) ||
							$sanitized_settings['users']['password_maximum_length'] > 64
						)
					) {
						return new WP_Error( 'password_maximum_length', esc_html__( 'Password maximum length must be smaller than 64 characters.', 'adminease' ) );
					}
					
					if( !empty( $sanitized_settings['users']['auto_logout_user'] ) && empty( $sanitized_settings['users']['auto_logout_user_time'] ) ) {
						return new WP_Error( 'auto_logout_user_time', esc_html__( 'Auto-logout user after X seconds cannot be empty', 'adminease' ) );
					}
					
					break;
				}
				case 'security':
				{
					if( !empty( $sanitized_settings['security']['password_protect_site_enabled'] ) && empty( $sanitized_settings['security']['password_protect_site_password'] ) ) {
						return new WP_Error( 'password_protect_site_password', esc_html__( 'Site password cannot be empty', 'adminease' ) );
					}
					
					if( !empty( $sanitized_settings['security']['network_viewer_max_entries'] ) && $sanitized_settings['security']['network_viewer_max_entries'] > 100000 ) {
						return new WP_Error( 'network_viewer_max_entries', esc_html__( 'Max log entries must be less than or equal to 100000', 'adminease' ) );
					}
					
					break;
				}
			}
		}
		
		return apply_filters( 'adminease_validate_settings', $sanitized_settings );
	}
	
	/**
	 * Appends additional CSS classes to the dashboard section based on user preferences.
	 * This method checks the current user's metadata to determine whether specific
	 * UI features, such as a sidebar, should be active, and adjusts the provided
	 * classes accordingly.
	 *
	 * @param array $classes An array of existing CSS classes for the dashboard section.
	 *
	 * @return array The modified array of CSS classes, potentially including additional
	 *               classes based on the current user's settings.
	 */
	public function adminease_dashboard_section_classes( array $classes ): array {
		$user_id = get_current_user_id();
		
		if( empty( $user_id ) ) {
			return $classes;
		}
		
		if( get_user_meta( $user_id, 'adminease_menu_sidebar_active', true ) ) {
			$classes[] = 'has-sidebar';
		}
		
		if( get_user_meta( $user_id, 'adminease_menu_sidebar_minmax_active', true ) ) {
			$classes[] = 'minmax-sidebar';
		}
		
		return $classes;
	}
	
	/**
	 * Add a settings link to plugin action links.
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array Modified plugin action links.
	 */
	public function action_links( $links ): array {
		$link = '<a href="' . esc_url( admin_url( '/admin.php?page=' . ADMINEASE_SLUG ) ) . '">' . esc_html__( 'Settings', 'adminease' ) . '</a>';
		
		return array_merge( [ $link ], $links );
	}
	
	/**
	 * Retrieves an array of plugin fields based on the provided settings key.
	 * This method defines various configurable fields for managing
	 * updates, security, and other aspects of the plugin. Each field contains
	 * information about its type, ID, name, value, and other relevant attributes.
	 *
	 * @param string $key Optional. The specific key within the settings array to retrieve.
	 *                     Defaults to an empty string, which returns all fields.
	 *
	 * @return array An array of fields grouped into categories,
	 *               where each category contains its title and a list of field definitions.
	 */
	public static function get_plugin_fields( string $key = '' ): array {
		$fields = [
			'updates-and-notifications' => [
				'title'  => __( 'Updates and Notifications', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-update" aria-hidden="true"></span>',
				'fields' => [],
			],
			'security'                  => [
				'title'  => __( 'Security', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-shield" aria-hidden="true"></span>',
				'fields' => [],
			],
			'performance'               => [
				'title'  => __( 'Performance', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-performance" aria-hidden="true"></span>',
				'fields' => [],
			],
			'posts'                     => [
				'title'  => __( 'Posts', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-admin-post" aria-hidden="true"></span>',
				'fields' => [],
			],
			'taxonomies'                => [
				'title'  => __( 'Taxonomies', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-tag" aria-hidden="true"></span>',
				'fields' => [],
			],
			'users'                     => [
				'title'  => __( 'Users', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-admin-users" aria-hidden="true"></span>',
				'fields' => [],
			],
			'debug'                     => [
				'title'  => __( 'Debug', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-clock" aria-hidden="true"></span>',
				'fields' => [],
			],
			'media'                     => [
				'title'  => __( 'Media', 'adminease' ),
				'icon'   => '<span class="dashicons dashicons-admin-media" aria-hidden="true"></span>',
				'fields' => [],
			],
		];
		
		$fields = apply_filters( 'adminease_settings_fields', $fields );
		
		return $fields[ $key ] ?? $fields;
	}
	
	/**
	 * Modifies the row meta displayed for the plugin on the plugins page.
	 *
	 * @param array  $plugin_meta Array of the plugin's metadata.
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param array  $plugin_data Array of plugin data.
	 * @param string $status Plugin status (e.g., 'active', 'inactive').
	 *
	 * @return array Modified array of plugin metadata.
	 */
	public function plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
		if( in_array( $plugin_file, [ ADMINEASE_BASENAME, 'adminease-pro/adminease-pro.php' ] ) ) {
			$plugin_meta[1] = 'By <img src="' . ADMINEASE_PLUGIN_URL . 'assets/img/adminease-logo.png' . '" class="adminease-icon" alt="" /> PrecisionWP'; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
		}
		
		if( ADMINEASE_BASENAME === $plugin_file ) {
			$plugin_meta[] = '<a href="https://precisionwp.net/product/adminease/" target="_blank" aria-label="' . esc_attr__( 'Upgrade to Pro', 'adminease' ) . '">' . esc_attr__( 'Upgrade to Pro', 'adminease' ) . '</a>';
		}
		
		return $plugin_meta;
	}
	
	/**
	 * Searches for the settings key within a specified group and field.
	 *
	 * @param string $group_key The key of the group to search within.
	 * @param string $field_key The partial or complete key to search for in the fields.
	 * @param array  $all_fields An associative array containing all fields grouped by their keys.
	 *
	 * @return int|null The matching key if found, or null if no match is found.
	 */
	public static function find_settings_key( string $group_key, string $field_key, array $all_fields ): ?int {
		foreach( $all_fields[ $group_key ]['fields'] as $key => $field ) {
			if( false !== strpos( $field['name'], $field_key ) ) {
				return (int) $key;
			}
		}
		
		return null;
	}
}