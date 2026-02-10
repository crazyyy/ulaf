<?php
namespace AdminEase\Features\PasswordProtectSite;

use AdminEase\Features\PasswordProtectSite\Themes\Classic;
use AdminEase\Plugin;
use AdminEase\Utils;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * The PasswordProtectSite class is a singleton that implements site-wide password protection.
 * It handles authentication, access control, and configuration of settings related to protecting the site.
 */
class PasswordProtectSite {
	private array $settings;
	private string $session_key = 'adminease_password_protect_site';
	private int $session_timeout = 60;
	private string $attempts_key = 'adminease_password_protect_site_attempts';
	private int $max_attempts = 5;
	private int $lockout_duration = 30;
	private string $client_ip_address;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings          = Plugin::get_settings( 'security' );
		$this->client_ip_address = Utils::get_client_ip();
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		add_filter( 'adminease_localize_script', [ $this, 'adminease_localize_script' ] );
		add_action( 'adminease_after_field_render', [ $this, 'adminease_after_field_render' ] );
		
		add_action( 'wp_ajax_adminease_get_password_protection_log', [ $this, 'ajax_get_password_protection_log' ] );
		add_action( 'wp_ajax_adminease_download_password_protection_log', [ $this, 'ajax_download_password_protection_log' ] );
		
		if( empty( $this->settings['password_protect_site_enabled'] ) ) {
			return;
		}
		
		$this->session_timeout = 3600;
		
		if( !empty( $this->settings['password_protect_site_remember_device'] ) && 'other' === $this->settings['password_protect_site_remember_device'] && !empty( $this->settings['password_protect_site_remember_device_other'] ) ) {
			$this->session_timeout = (int) $this->settings['password_protect_site_remember_device_other'];
		}
		else if( !empty( $this->settings['password_protect_site_remember_device'] ) ) {
			$remember_value = $this->settings['password_protect_site_remember_device'];
			
			if( is_numeric( $remember_value ) ) {
				$this->session_timeout = (int) $remember_value;
			}
			else if( defined( $remember_value ) ) {
				$this->session_timeout = (int) constant( $remember_value );
			}
		}
		
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'template_redirect', [ $this, 'check_access' ], 0 );
		add_action( 'wp_ajax_adminease_site_password_check', [ $this, 'ajax_password_check' ] );
		add_action( 'wp_ajax_nopriv_adminease_site_password_check', [ $this, 'ajax_password_check' ] );
		
		// Add cron event for cleanup
		add_action( 'adminease_cleanup_failed_attempts', [ $this, 'cleanup_failed_attempts' ] );
		
		// Schedule cleanup if not already scheduled
		if( !wp_next_scheduled( 'adminease_cleanup_failed_attempts' ) ) {
			wp_schedule_event( time(), 'hourly', 'adminease_cleanup_failed_attempts' );
		}
	}
	
	/**
	 * Add password protection settings fields to the security section
	 *
	 * @param array $fields Existing security settings fields
	 *
	 * @return array Modified fields with password protection options
	 */
	public function adminease_settings_fields( array $fields ): array {
		$blog_name = get_bloginfo( 'name' );
		
		$fields['security']['fields'][] = [
			'type'         => 'switch',
			'id'           => 'password-protect-site-enabled',
			'name'         => 'adminease[security][password_protect_site_enabled]',
			'value'        => $this->settings['password_protect_site_enabled'] ?? false,
			'label_class'  => 'adminease-switch',
			'input_class'  => 'form-control toggle-field',
			'label'        => __( 'Enable Password Protection', 'adminease' ),
			'description'  => __( 'Enable password protection for the entire site.', 'adminease' ),
			'child_fields' => [
				[
					'type'          => 'text',
					'id'            => 'password-protect-site-password',
					'name'          => 'adminease[security][password_protect_site_password]',
					'value'         => $this->settings['password_protect_site_password'] ?? '',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Site Password', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'text',
					'id'            => 'password-protect-site-page-title',
					'name'          => 'adminease[security][password_protect_site_page_title]',
					'value'         => $this->settings['password_protect_site_page_title'] ?? $blog_name . ' - ' . __( 'Password Protected', 'adminease' ),
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Page Title', 'adminease' ),
					'description'   => __( 'The title that appears in the browser tab.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'text',
					'id'            => 'password-protect-site-headline',
					'name'          => 'adminease[security][password_protect_site_headline]',
					'value'         => $this->settings['password_protect_site_headline'] ?? $blog_name,
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Headline', 'adminease' ),
					'description'   => __( 'The main heading displayed on the maintenance page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'              => 'textarea',
					'id'                => 'password-protect-site-entry-message',
					'name'              => 'adminease[security][password_protect_site_entry_message]',
					'value'             => $this->settings['password_protect_site_entry_message'] ?? __( 'This site is password protected. Please enter the password to access the site.', 'adminease' ),
					'label_class'       => 'adminease-label',
					'input_class'       => 'form-control',
					'wrapper_class'     => 'form-group-child',
					'label'             => __( 'Message', 'adminease' ),
					'description'       => __( 'The message displayed on the password protected page.', 'adminease' ),
					'field_description' => __( 'You can use basic HTML tags.', 'adminease' ),
					'attributes'        => [
						'rows'        => 4,
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'password-protect-site-show-logo',
					'name'          => 'adminease[security][password_protect_site_show_logo]',
					'value'         => $this->settings['password_protect_site_show_logo'] ?? true,
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Show Site Logo', 'adminease' ),
					'description'   => __( 'Display the site logo from Customizer settings.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'password-protect-site-primary-color',
					'name'          => 'adminease[security][password_protect_site_primary_color]',
					'value'         => $this->settings['password_protect_site_primary_color'] ?? '#0073aa',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Primary Color', 'adminease' ),
					'description'   => __( 'Select the primary color for the password protected page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'password-protect-site-secondary-color',
					'name'          => 'adminease[security][password_protect_site_secondary_color]',
					'value'         => $this->settings['password_protect_site_secondary_color'] ?? '#23282d',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Secondary Color', 'adminease' ),
					'description'   => __( 'Select the secondary color for the password protected page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'colorpicker',
					'id'            => 'password-protect-site-text-color',
					'name'          => 'adminease[security][password_protect_site_text_color]',
					'value'         => $this->settings['password_protect_site_text_color'] ?? '#333333',
					'label_class'   => 'adminease-label',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Text Color', 'adminease' ),
					'description'   => __( 'Select the text color for the password protected page.', 'adminease' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
				[
					'type'          => 'switch',
					'id'            => 'password-protect-site-auto-load-log',
					'name'          => 'adminease[security][password_protect_site_auto_load_log]',
					'value'         => $this->settings['password_protect_site_auto_load_log'] ?? false,
					'label_class'   => 'adminease-switch',
					'input_class'   => 'form-control',
					'wrapper_class' => 'form-group-child',
					'label'         => __( 'Auto-load Access Log', 'adminease-pro' ),
					'description'   => __( 'Automatically load the password protection access log when the page loads.', 'adminease-pro' ),
					'attributes'    => [
						'data-parent' => 'password-protect-site-enabled',
					],
				],
			],
		];
		
		return $fields;
	}
	
	/**
	 * Initializes a session if one is not already started and headers have not been sent.
	 *
	 * @return void
	 */
	public function init(): void {
		// Don't start session for REST API requests or loopback requests
		if( $this->is_rest_api_request() ) {
			return;
		}
		
		// Don't start session for cron requests
		if( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}
		
		if( !session_id() && !headers_sent() ) {
			session_start();
			
			// Close session immediately after starting to avoid blocking
			// We'll reopen it only when needed for authentication
			session_write_close();
		}
	}
	
	/**
	 * Determines whether the current user has access to the site and handles access control.
	 * Skips checks if in the admin area or during an AJAX request. If access is not granted and the user is not authenticated, displays a password form and terminates further execution.
	 *
	 * @return void
	 */
	public function check_access(): void {
		// Skip if in the admin area, admin-ajax, REST API, or user is logged in
		if( is_admin() || wp_doing_ajax() || is_user_logged_in() ) {
			return;
		}
		
		// Skip for REST API requests
		if( $this->is_rest_api_request() ) {
			return;
		}
		
		// Skip for WordPress loopback requests and cron
		if( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}
		
		if( apply_filters( 'adminease_password_protect_site_check_access', false ) ) {
			return;
		}
		
		// Check if already authenticated
		if( $this->is_authenticated() ) {
			return;
		}
		
		// Tell common WordPress cache plugins / hosts not to cache this page
		if( !defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard caching constant used by W3 Total Cache and other caching plugins
		if( !defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard caching constant used by Autoptimize and other minification plugins
		if( !defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}
		
		nocache_headers();
		
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		
		// Show password form
		$this->show_password_form();
		exit;
	}
	
	/**
	 * Determines whether the current user is authenticated based on session and optional "remember device" cookie.
	 * The method verifies session data or checks a secure cookie for authentication validity within defined time constraints.
	 * If a valid session or cookie is found, the session is updated or refreshed.
	 *
	 * @return bool Returns true if the user is authenticated, false otherwise.
	 */
	private function is_authenticated(): bool {
		// Reopen session to read data (it was closed in init())
		if( session_id() && session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
		
		// Check session
		if( !empty( $_SESSION[ $this->session_key ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Session data is set and controlled by this class
			$auth_data = $_SESSION[ $this->session_key ];
			
			if( time() - $auth_data['timestamp'] < $this->session_timeout ) {
				return true;
			}
			else {
				unset( $_SESSION[ $this->session_key ] );
			}
		}
		
		// Check cookie for "remember device" - but don't refresh session
		// This way the session timeout is respected
		$cookie_name = 'adminease_remember_' . hash( 'md5', home_url() );
		
		if( !empty( $_COOKIE[ $cookie_name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cookie value is sanitized via wp_unslash and validated by json_decode
			$cookie_data = json_decode( wp_unslash( $_COOKIE[ $cookie_name ] ), true );
			
			if( is_array( $cookie_data ) && !empty( $cookie_data['hash'] ) && !empty( $cookie_data['time'] ) ) {
				$expected_hash = hash( 'sha256', $this->settings['password_protect_site_password'] . $this->client_ip_address . wp_salt() );
				
				// Check if cookie is still valid and hasn't been tampered with
				if( hash_equals( $expected_hash, $cookie_data['hash'] ) ) {
					// Create new session with original cookie timestamp
					// This ensures session timeout is still respected
					$_SESSION[ $this->session_key ] = [
						'authenticated' => true,
						'timestamp'     => $cookie_data['time'], // Use cookie creation time, not current time
						'ip'            => $this->client_ip_address,
					];
					
					// Check if session based on cookie time has expired
					if( time() - $cookie_data['time'] < $this->session_timeout ) {
						return true;
					}
					else {
						// Session has expired, clear everything
						unset( $_SESSION[ $this->session_key ] );
						// Also clear the cookie
						setcookie(
							$cookie_name,
							'',
							time() - 3600,
							'/',
							wp_parse_url( home_url(), PHP_URL_HOST ),
							is_ssl(),
							true
						);
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Displays the password input form for site protection, utilizing the specified theme
	 * and settings for customization.
	 *
	 * @return void
	 */
	private function show_password_form(): void {
		$theme          = 'classic';
		$show_logo      = $this->settings['password_protect_site_show_logo'] ?? true;
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$theme_args     = [
			'page_title'      => $this->settings['password_protect_site_page_title'] ?? '',
			'headline'        => $this->settings['password_protect_site_headline'] ?? '',
			'entry_message'   => $this->settings['password_protect_site_entry_message'] ?? '',
			'logo_url'        => $show_logo ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '',
			'primary_color'   => $this->settings['password_protect_site_primary_color'] ?? '#0073aa',
			'secondary_color' => $this->settings['password_protect_site_secondary_color'] ?? '#23282d',
			'text_color'      => $this->settings['password_protect_site_text_color'] ?? '#333333',
			'remember_device' => $this->settings['password_protect_site_remember_device'] ?? true,
		];
		
		switch( $theme ) {
			case 'classic':
			default:
				$html = Classic::render( $theme_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
		}
		
		/**
		 * Filter the maintenance page HTML
		 *
		 * @param string $html The complete HTML output
		 * @param array  $settings Current settings
		 */
		$html = apply_filters( 'adminease_password_protect_site_html', $html, $this->settings );
		
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	/**
	 * Handles the AJAX request to verify the site password and authenticate the user.
	 * Validates the provided password against the site password settings, checks for brute force attempts,
	 * and manages user authentication. Provides an appropriate response based on the outcome.
	 *
	 * @return void
	 */
	public function ajax_password_check(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'adminease_site_password' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password should not be sanitized, only validated
		$password        = wp_unslash( $_POST['data']['password'] ?? '' );
		$remember_device = !empty( $_POST['data']['remember_device'] ?? false );
		$current_url     = sanitize_url( wp_unslash( $_POST['data']['current_url'] ?? '' ) );
		
		// Check for brute force attempts
		if( $this->is_ip_locked() ) {
			wp_send_json_error( new WP_Error( 'too_many_attempts', esc_html__( 'You have made too many attempts. Please try again later.', 'adminease' ) ) );
		}
		
		// Validate password
		$site_password = $this->settings['password_protect_site_password'] ?? '';
		
		if( empty( $password ) || empty( $site_password ) ) {
			$this->record_failed_attempt();
			
			wp_send_json_error( new WP_Error( 'wrong_password_message', esc_html__( 'Incorrect password. Please try again.', 'adminease' ) ) );
		}
		
		if( !hash_equals( $site_password, $password ) ) {
			$this->record_failed_attempt();
			$this->log_authentication_attempt( false, $password );
			
			wp_send_json_error( new WP_Error( 'wrong_password_message', esc_html__( 'Incorrect password. Please try again.', 'adminease' ) ) );
		}
		
		// Password is correct - authenticate user
		$this->authenticate_user( $remember_device );
		$this->log_authentication_attempt( true );
		
		wp_send_json_success( [
			'message'      => esc_html__( 'Access granted. Redirecting...', 'adminease' ),
			'redirect_url' => !empty( $current_url ) ? $current_url : home_url( '/' ),
		] );
	}
	
	/**
	 * Authenticates the user by setting a session and optionally setting a "remember device" cookie.
	 *
	 * @param bool $remember_device Whether to remember the device by setting a persistent cookie.
	 *
	 * @return void
	 */
	public function authenticate_user( bool $remember_device ): void {
		// Set session
		$_SESSION[ $this->session_key ] = [
			'authenticated' => true,
			'timestamp'     => time(),
			'ip'            => $this->client_ip_address,
		];
		
		$expiration_seconds = apply_filters( 'adminease_password_protect_site_remember_device_expiration', DAY_IN_SECONDS );
		
		if( $remember_device && $expiration_seconds ) {
			$cookie_name = 'adminease_remember_' . hash( 'md5', home_url() );
			$cookie_data = [
				'hash' => hash( 'sha256', $this->settings['password_protect_site_password'] . $this->client_ip_address . wp_salt() ),
				'time' => time(),
			];
			
			setcookie(
				$cookie_name,
				wp_json_encode( $cookie_data ),
				time() + $expiration_seconds,
				'/',
				wp_parse_url( home_url(), PHP_URL_HOST ),
				is_ssl(),
				true
			);
		}
		
		// Clear failed attempts
		$this->clear_failed_attempts();
	}
	
	/**
	 * Checks if the client IP address is locked due to exceeding the maximum allowed login attempts.
	 *
	 * @return bool Returns true if the IP address is locked, false otherwise.
	 */
	private function is_ip_locked(): bool {
		$attempts_data = get_transient( $this->attempts_key . '_' . hash( 'md5', $this->client_ip_address ) );
		
		if( !$attempts_data ) {
			return false;
		}
		
		return $attempts_data['count'] >= $this->max_attempts;
	}
	
	/**
	 * Records a failed login attempt for the current client IP address.
	 * If no prior record exists, initializes the attempt data.
	 * Updates the count, timestamp of the last attempt,
	 * and stores the information with a specified expiration time.
	 *
	 * @return void
	 */
	private function record_failed_attempt(): void {
		$ip_hash       = hash( 'md5', $this->client_ip_address );
		$attempts_data = get_transient( $this->attempts_key . '_' . $ip_hash );
		
		if( !$attempts_data ) {
			$attempts_data = [
				'count'         => 0,
				'first_attempt' => time(),
			];
		}
		
		$attempts_data['count']++;
		$attempts_data['last_attempt'] = time();
		
		set_transient( $this->attempts_key . '_' . $ip_hash, $attempts_data, $this->lockout_duration * 60 );
	}
	
	/**
	 * Clears the record of failed login attempts associated with the current client IP address.
	 *
	 * @return void
	 */
	private function clear_failed_attempts(): void {
		$ip_hash = hash( 'md5', $this->client_ip_address );
		
		delete_transient( $this->attempts_key . '_' . $ip_hash );
	}
	
	/**
	 * Logs an authentication attempt with details such as timestamp, IP address, success status, user agent,
	 * and the hashed attempted password if the attempt failed.
	 *
	 * @param bool   $success Indicates whether the authentication attempt was successful.
	 * @param string $attempted_password The entered password during the attempt. It is hashed and stored only if the attempt failed.
	 *
	 * @return void
	 */
	private function log_authentication_attempt( bool $success, string $attempted_password = '' ): void {
		$log_entry = [
			'timestamp'          => current_time( 'mysql' ),
			'ip'                 => $this->client_ip_address,
			'success'            => $success,
			'user_agent'         => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'attempted_password' => $success ? '' : hash( 'sha256', $attempted_password ), // Only hash of failed attempts
		];
		
		// Store in option (limit to last 1000 entries)
		$logs   = get_option( 'adminease_password_protection_logs', [] );
		$logs[] = $log_entry;
		
		// Keep only last 1000 entries
		if( count( $logs ) > 1000 ) {
			$logs = array_slice( $logs, -1000 );
		}
		
		update_option( 'adminease_password_protection_logs', $logs );
	}
	
	/**
	 * Cleans up expired failed login attempts stored as transients for all tracked IPs.
	 * Transients are deleted for IP hashes where the timestamp exceeds the configured lockout duration.
	 *
	 * @return void
	 */
	public function cleanup_failed_attempts(): void {
		$ip_hashes = get_transient( 'adminease_failed_ip_hashes' );
		
		if( !$ip_hashes ) {
			return;
		}
		
		$current_time = time();
		
		foreach( $ip_hashes as $ip_hash => $timestamp ) {
			if( $current_time - $timestamp > $this->lockout_duration * 60 ) {
				delete_transient( $this->attempts_key . '_' . $ip_hash );
			}
		}
	}
	
	/**
	 * Check if current request is a REST API request
	 *
	 * @return bool True if REST API request, false otherwise
	 */
	private function is_rest_api_request(): bool {
		// Check if REST_REQUEST is defined
		if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		
		// Check request URI for /wp-json/ endpoint
		if( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			if( strpos( $request_uri, '/wp-json/' ) !== false ) {
				return true;
			}
		}
		
		// Check if rest_route query parameter exists
		if( isset( $_GET['rest_route'] ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Localizes script data by adding security nonces and internationalized strings.
	 *
	 * @param array $data An array used for script localization.
	 *
	 * @return array Returns an array containing localized data including security nonces and internationalized strings.
	 */
	public function adminease_localize_script( array $data ): array {
		$data['security']['refreshPasswordProtectionLog']  = wp_create_nonce( 'adminease_get_password_protection_log' );
		$data['security']['downloadPasswordProtectionLog'] = wp_create_nonce( 'adminease_download_password_protection_log' );
		
		$data['i18n']['passwordProtectionLogEmpty']          = esc_html__( 'No password protection attempts logged yet.', 'adminease' );
		$data['i18n']['passwordProtectionLogRefreshError']   = esc_html__( 'Failed to get password protection log. Refresh the page and try again.', 'adminease' );
		$data['i18n']['passwordProtectionLogDownloadFailed'] = esc_html__( 'Failed to download password protection log. Please try again.', 'adminease' );
		
		return $data;
	}
	
	/**
	 * Handles the after render logic for the specified field.
	 *
	 * @param array $field The field information, including the 'id' property used for conditional logic.
	 *
	 * @return void
	 */
	public function adminease_after_field_render( array $field ): void {
		if( 'password-protect-site-enabled' !== $field['id'] ) {
			return;
		}
		
		$settings = $this->settings;
		
		include_once ADMINEASE_DIR . 'partials/password-protect-site-log.php';
	}
	
	/**
	 * Handles the AJAX request to get and retrieve the password protection log.
	 * Verifies user permissions and nonce for security.
	 *
	 * @return void
	 */
	public function ajax_get_password_protection_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_get_password_protection_log' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ) ) );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$logs = get_option( 'adminease_password_protection_logs', [] );
		
		// Reverse to show newest first
		$logs = array_reverse( $logs );
		
		wp_send_json_success( [
			'logs'      => $logs,
			'total'     => count( $logs ),
			'log_limit' => 1000,
		] );
	}
	
	/**
	 * Handles the AJAX request to download password protection log as CSV.
	 *
	 * @return void
	 */
	public function ajax_download_password_protection_log(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_download_password_protection_log' ) ) {
			wp_send_json_error( esc_html__( 'An error occurred. Refresh the page and try again.', 'adminease' ), 403 );
		}
		
		// Check user capabilities
		if( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'permissions', esc_html__( 'You do not have sufficient permissions to perform this action', 'adminease' ) ) );
		}
		
		$logs = get_option( 'adminease_password_protection_logs', [] );
		
		// Reverse to show newest first
		$logs = array_reverse( $logs );
		
		// Generate CSV content
		$csv_content = "Timestamp,IP Address,Status,User Agent,Attempted Password Hash\n";
		
		foreach( $logs as $log ) {
			$status      = $log['success'] ? 'Success' : 'Failed';
			$csv_content .= sprintf(
				'"%s","%s","%s","%s","%s"' . "\n",
				$log['timestamp'] ?? '',
				$log['ip'] ?? '',
				$status,
				str_replace( '"', '""', $log['user_agent'] ?? '' ),
				$log['attempted_password'] ?? ''
			);
		}
		
		// Send the file content directly with proper headers
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="password-protection-log-' . gmdate( 'Y-m-d-His' ) . '.csv"' );
		header( 'Content-Length: ' . strlen( $csv_content ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		
		echo $csv_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}