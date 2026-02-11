<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Login class
 */
class Block_Login {
	const OPTION_NAME = 'adminoptim_block_login';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Settings class
	 *
	 * @var Block_Login_Settings
	 */
	protected $settings;

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'block-login/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'block-login/';

	/**
	 * Database class
	 *
	 * @var Block_Login_DB
	 */
	private $db;

	const MENU_SLUG = 'adminoptimizer-block-login';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option(
			self::OPTION_NAME,
			[
				'failed_count' => 3,
			]
		);
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->db       = new Block_Login_DB();
		$this->settings = new Block_Login_Settings();
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'wp_login_failed', [ $this, 'update_failed_login_record' ] );
		add_filter( 'wp_authenticate_user', [ $this, 'maybe_allow_login' ], 9999 );
		add_filter( 'shake_error_codes', [ $this, 'add_failed_login_error_code' ] );
		add_filter( 'login_errors', [ $this, 'show_login_error_messages' ] );
		add_action( 'wp_ajax_adminoptim_login_action', [ $this, 'ajax_settings_login_action' ] );
		add_filter( 'login_message', [ $this, 'show_lockout_message' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page( ADMINOPTIMIZER_MODULES_MENU_SLUG, __( 'Block Failed Login', 'admin-optimizer' ), __( 'Block Failed Login', 'admin-optimizer' ), 'manage_options', self::MENU_SLUG, [ $this->settings, 'render_settings_page' ] );
	}

	/**
	 * Handle failed login
	 *
	 * @param string $username user's username.
	 *
	 * @return void
	 */
	public function update_failed_login_record( $username ) {
		$ip_addr = $this->get_ip_address();

		if ( ! $ip_addr ) {
			// can't retrieve IP address, return.
			return;
		} else {
			$ip_number = self::ip_to_number( $ip_addr );
			$lockout   = get_transient( 'adminoptim_locked_' . $ip_number );
			if ( ! $lockout ) {
				$failed_login = get_transient( 'adminoptim_failed_login_' . $ip_number );
				if ( false === $failed_login ) {
					// 1st failed attempt. Add a new transient value
					set_transient( 'adminoptim_failed_login_' . $ip_number, 1, 0.5 * DAY_IN_SECONDS );
				} else {
					$failed_login = (int) $failed_login;
					++$failed_login;
					if ( $failed_login >= (int) $this->options['failed_count'] ) {
						$lockout_count = $this->db->get_lockout_count( $ip_addr );
						// start lockdown for 15 minutes.
						$lockout_duration = apply_filters( 'adminoptim_block_login_lockout_duration', 15 * 60, $lockout_count ); // default is 15 minutes.
						set_transient( 'adminoptim_locked_' . $ip_number, true, $lockout_duration );
						$this->db->update_record( $ip_addr, $username, 'locked', $lockout_duration );
						delete_transient( 'adminoptim_failed_login_' . $ip_number );
					} else {
						set_transient( 'adminoptim_failed_login_' . $ip_number, $failed_login, 0.5 * DAY_IN_SECONDS );
					}
				}
			}
		}
	}

	/**
	 * Get current user's IP address
	 *
	 * @return string
	 */
	public function get_ip_address() {

		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			return 'false';
		}
	}

	/**
	 * Convert IP address to a storable string
	 *
	 * @param string $ip  IP address.
	 *
	 * @return false|int|string
	 */
	public static function ip_to_number( $ip ) {
		// for ipv4 address only.
		$long = ip2long( $ip );
		if ( $long ) {
			return $long;
		} else {
			// long is false, so ip is ipv6, try another method.
			$bin = inet_pton( $ip );
			if ( $bin ) {
				return bin2hex( $bin );
			} else {
				// invalid ip address.
				return false;
			}
		}
	}

	/**
	 * Function to block user login after too many unsuccessful login attempts
	 *
	 * @param \WP_User $user WP_User class.
	 *
	 * @return \WP_User|\WP_Error
	 */
	public function maybe_allow_login( $user ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$is_lockout = $this->is_lockout();

		if ( $is_lockout ) {
			$ip_number     = self::ip_to_number( $this->get_ip_address() );
			$lockout_time  = (int) get_option( '_transient_timeout_adminoptim-locked-' . $ip_number, 0 );
			$readable_time = human_time_diff( time(), $lockout_time );
			return new \WP_Error(
				'too_many_failed_logins',
				/* translators: %s: blocked time interval */
				sprintf( __( '<strong>ERROR</strong>: Too many failed login attempts. Login blocked for %s. Please try again later.', 'admin-optimizer' ), $readable_time )
			);
		} else {
			$this->db->clean_lockout();
		}

		return $user;
	}

	/**
	 * Check if an IP address is being locked out
	 *
	 * @return bool
	 */
	public function is_lockout() {
		$is_lockout = false;

		$ip_addr = $this->get_ip_address();

		if ( $ip_addr ) {
			$ip_number = self::ip_to_number( $ip_addr );
			$lockout   = get_transient( 'adminoptim_locked_' . $ip_number );
			if ( $lockout ) {
				$is_lockout = true;
			}
		}
		return $is_lockout;
	}

	/**
	 * Check for valid login
	 *
	 * @return true|\WP_Error
	 */
	public function check_login_valid() {
		$ip_addr = $this->get_ip_address();

		if ( ! $ip_addr ) {
			// can't retrieve IP address, return.
			return true;
		}
		$ip_number    = self::ip_to_number( $ip_addr );
		$lockout      = get_transient( 'adminoptim_locked_' . $ip_number );
		$failed_login = get_transient( 'adminoptim_failed_login_' . $ip_number );
		if ( $lockout ) {
			$lockout_time  = (int) get_option( '_transient_timeout_adminoptim_locked_' . $ip_number, 0 );
			$readable_time = human_time_diff( time(), $lockout_time );
			return new \WP_Error(
				'too_many_failed_logins',
				/* translators: %s: blocked time interval */
				sprintf( __( '<strong>ERROR</strong>: Too many failed login attempts. Login blocked for %s. Please try again later.', 'admin-optimizer' ), $readable_time )
			);
		} elseif ( is_numeric( $failed_login ) && $failed_login > 0 ) {
			$remaining_attempts = (int) $this->options['failed_count'] - (int) $failed_login;
			if ( $remaining_attempts > 0 ) {
				return new \WP_Error(
					'failed_login_count',
					/* translators: %s: number of login attempts left */
					sprintf( __( '<strong>ERROR</strong>: %s login attempts left.', 'admin-optimizer' ), $remaining_attempts )
				);
			} else {
				return new \WP_Error(
					'too_many_failed_logins',
					__(
						'<strong>ERROR</strong>: Too many failed login attempts. Login blocked for 15 minutes. Please try again later.',
						'admin-optimizer'
					)
				);
			}
		}

		return true;
	}

	/**
	 * Append to failed login error codes
	 *
	 * @param array $codes  List of failed login error codes.
	 *
	 * @return array
	 */
	public function add_failed_login_error_code( $codes ) {
		$codes[] = 'failed_login_count';
		$codes[] = 'too_many_failed_logins';
		return $codes;
	}

	/**
	 * Function to check if need to show login error message
	 *
	 * @return bool
	 */
	private function should_show_login_error_msg() {
		if ( isset( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			/* reset password */
			return false;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$action_arr = [ 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register' ];
		return ! in_array( $action, $action_arr, true );
	}

	/**
	 * Function to show login error message
	 *
	 * @param string $error  error message.
	 *
	 * @return string
	 */
	public function show_login_error_messages( $error ) {

		if ( ! $this->should_show_login_error_msg() ) {
			return $error;
		}

		$login_error = $this->check_login_valid();
		if ( is_wp_error( $login_error ) ) {
			return $login_error->get_error_message();
		}

		return $error;
	}

	/**
	 * Ajax action to manage blocked logins
	 *
	 * @return void
	 */
	public function ajax_settings_login_action() {
		check_ajax_referer( 'adminoptim_block_login_action', '_wpnonce' );
		$response  = [];
		$ip_id     = ! empty( $_REQUEST['ip_id'] ) ? (int) $_REQUEST['ip_id'] : 0;
		$ip_action = ! empty( $_REQUEST['ip_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ip_action'] ) ) : '';
		if ( array_key_exists( $ip_action, $this->db->all_statuses ) ) {
			$response = $this->db->manage_ip_status( $ip_id, $ip_action );
		} else {
			$error = new \WP_Error(
				'adminoptim-invalid-action',
				wp_get_admin_notice(
					__( 'Invalid action', 'admin-optimizer' ),
					[
						'type'        => 'error',
						'dismissible' => true,
					]
				)
			);
			wp_send_json_error( $error );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response );
		} else {
			wp_send_json_success( $response );
		}
	}

	/**
	 * Function to show lockout message
	 *
	 * @param string $message lockout message.
	 *
	 * @return string
	 */
	public function show_lockout_message( $message ) {
		if ( $this->is_lockout() ) {
			$message = wp_get_admin_notice(
				__( '<strong>ERROR</strong>: Too many failed login attempts. Please try again later.', 'admin-optimizer' ),
				[
					'type'               => 'error',
					'additional_classes' => [ 'message' ],
				]
			);
		}
		return $message;
	}
}
