<?php

namespace Yipresser\AdminOptimizer\Modules;

use Yipresser\AdminOptimizer\Vendor\RemoteMerge\Totp\TotpException;
use Yipresser\AdminOptimizer\Vendor\RemoteMerge\Totp\TotpFactory;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two-Factor Authentication class
 */
class Two_Factor_Authentication {
	/**
	 * The options value stored in database
	 *
	 * @var false|mixed|null
	 */
	protected $options;

	/**
	 * Set up Settings fields for 2FA
	 *
	 * @var Two_Factor_Authentication_Settings
	 */
	protected $settings;

	/**
	 * Set up Settings fields in the User Profile page
	 *
	 * @var Two_Factor_Authentication_User_Settings
	 */
	protected $user_settings;

	const OPTION_NAME = 'adminoptim_2fa';

	const MENU_SLUG = 'adminoptimizer-two-factor-authentication';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . '2fa/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . '2fa/';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = get_option( self::OPTION_NAME, [ 'compulsory_roles' => [] ] );
		$user          = wp_get_current_user();
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		global $adminoptim_2fa_passed;
		$adminoptim_2fa_passed = false;
		$this->user_settings   = new Two_Factor_Authentication_User_Settings();
		$this->settings        = new Two_Factor_Authentication_Settings( $this->options );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_action( 'wp_login', [ $this, 'maybe_add_2fa_login_page' ], 1, 2 );
		add_action( 'login_form_validate_2fa', [ $this, 'validate_2fa_login_form' ] );
		add_filter( 'shake_error_codes', [ $this, 'add_shake_error_code' ] );
		if ( ! empty( $this->options['add_user_2fa_column'] ) ) {
			add_filter( 'manage_users_columns', [ $this, 'add_user_2fa_column' ] );
			add_action( 'manage_users_custom_column', [ $this, 'render_user_2fa_column' ], 10, 3 );
		}
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page( ADMINOPTIMIZER_MODULES_MENU_SLUG, __( 'Two Factor Authentication', 'admin-optimizer' ), __( 'Two Factor Authentication', 'admin-optimizer' ), 'manage_options', self::MENU_SLUG, [ $this->settings, 'render_settings_page' ] );
	}

	/**
	 * Test for the need to display the 2FA login form, after the user has logged in.
	 *
	 * @param string   $user_login user's login username.
	 * @param \WP_User $user User class for the logged in user.
	 *
	 * @return void
	 */
	public function maybe_add_2fa_login_page( $user_login, $user ) {
		if ( isset( $user->adminoptim_twofa_validated ) && $user->adminoptim_twofa_validated ) {
			return;
		}

		if ( ! $this->is_login_page() ) {
			return;
		}

		$user_2fa = get_user_meta( $user->ID, self::OPTION_NAME, true );
		if ( empty( $user_2fa ) ) {
			$user_2fa = [];
		}
		if ( ! isset( $user_2fa['enabled'] ) ) {
			return;
		}

		// ensure user cannot log in without a valid 2fa code.
		wp_clear_auth_cookie();

		$redirect_to               = ! empty( $_REQUEST['redirect_to'] ) ? sanitize_url( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$interim_login             = isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$rememberme                = intval( ! empty( $_REQUEST['rememberme'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$can_trust_device          = $this->user_can_add_trust_device( $user );
		$can_add_recovery_checkbox = $this->user_can_use_recovery_code( $user );
		include 'includes/wp-2fa-login.php';
		exit;
	}

	/**
	 * Validate the 2FA TOTP code
	 *
	 * @return void
	 * @throws TotpException  Throw error if code cannot be verified.
	 */
	public function validate_2fa_login_form() {

		$user_id           = ! empty( $_REQUEST['auth_id'] ) ? absint( $_REQUEST['auth_id'] ) : 0;
		$nonce             = ! empty( $_REQUEST['adminoptim_2fa_nonce'] ) ? sanitize_key( $_REQUEST['adminoptim_2fa_nonce'] ) : '';
		$redirect_to       = ! empty( $_REQUEST['redirect_to'] ) ? sanitize_url( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';
		$user              = get_user_by( 'id', $user_id );
		$use_recovery_code = ! empty( $_REQUEST['use_recovery_code'] );
		$recovery_code     = ( ! empty( $_REQUEST['recoverycode'] ) && self::is_recovery_code_valid( sanitize_text_field( wp_unslash( $_REQUEST['recoverycode'] ) ) ) ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['recoverycode'] ) ) ) : '';
		$authcode          = ( ! empty( $_REQUEST['authcode'] ) && self::is_code_valid( sanitize_text_field( wp_unslash( $_REQUEST['authcode'] ) ) ) ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['authcode'] ) ) ) : 0;
		$error             = '';
		$interim_login     = isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $user_id || ! $user || ! $nonce || ( ! $authcode && ! $recovery_code ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'adminoptim_2fa_login' ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; uh?', 'admin-optimizer' ) );
		}

		$user_2fa = get_user_meta( $user->ID, self::OPTION_NAME, true );
		if ( empty( $user_2fa ) ) {
			$user_2fa = [];
		}
		if ( ! isset( $user_2fa['enabled'] ) || ! isset( $user_2fa['secret'] ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; uh?', 'admin-optimizer' ) );
		}

		$verified_login = false;
		if ( $use_recovery_code && ! empty( $recovery_code ) ) {
			$user_recovery_codes = $user_2fa['recovery_codes'];

			if ( is_array( $user_recovery_codes ) && ! empty( $user_recovery_codes ) ) {
				foreach ( $user_recovery_codes as $key => $hashed_code ) {
					if ( wp_check_password( $recovery_code, $hashed_code, $user->ID ) ) {
						unset( $user_recovery_codes[ $key ] );
						$user_2fa['recovery_codes'] = $user_recovery_codes;
						update_user_meta( $user->ID, self::OPTION_NAME, $user_2fa );
						$verified_login = true;
						break;
					}
				}
			}
		} else {
			$totp = TotpFactory::create();
			if ( $totp->verifyCode( $user_2fa['secret'], $authcode, 1 ) ) {
				$verified_login = true;
			}
		}

		if ( $verified_login ) {
			$rememberme = false;
			if ( isset( $_REQUEST['rememberme'] ) ) {
				$rememberme = true;
			}

			wp_set_auth_cookie( $user->ID, $rememberme );

			do_action( 'adminoptim_2fa_after_verification', $user );

			$user->adminoptim_twofa_validated = true;

			do_action( 'wp_login', $user->user_login, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			// Must be global because that's how login_header() uses it.
			global $interim_login;
			$interim_login = isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.Security.NonceVerification.Recommended

			if ( $interim_login ) {
				$customize_login = isset( $_REQUEST['customize-login'] );
				if ( $customize_login ) {
					wp_enqueue_script( 'customize-base' );
				}
				$message       = '<p class="message">' . __( 'You have logged in successfully.', 'admin-optimizer' ) . '</p>';
				$interim_login = 'success'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				login_header( '', $message );
				?>
				</div>
				<?php
				/** This action is documented in wp-login.php */
				do_action( 'login_footer' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				?>
				<?php if ( $customize_login ) : ?>
					<?php ob_start(); ?>
					setTimeout( function(){ new wp.customize.Messenger({ url: '<?php echo esc_url( wp_customize_url() ); ?>', channel: 'login' }).send('login') }, 1000 );
					<?php wp_print_inline_script_tag( ob_get_clean() ); ?>
				<?php endif; ?>
				</body></html>
				<?php
				return;
			}

			$redirect_to = apply_filters( 'login_redirect', $redirect_to, $redirect_to, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			wp_safe_redirect( $redirect_to );
		} else {
			// authcode is not validated, send error message.
			$error = new \WP_Error( 'invalid_2fa_authcode', __( 'ERROR: Invalid verification code.', 'admin-optimizer' ) );
			do_action( 'wp_login_failed', $user->user_login, $error ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			add_filter(
				'login_errors',
				function () {
					return __( 'ERROR: Invalid verification code.', 'admin-optimizer' );
				}
			);
			$can_trust_device          = $this->user_can_add_trust_device( $user );
			$can_add_recovery_checkbox = $this->user_can_use_recovery_code( $user );
			include 'includes/wp-2fa-login.php';
		}
		exit;
	}

	/**
	 * Sanitize 2FA code
	 *
	 * @param int|string $code  The code must be numeric and 6 digits.
	 *
	 * @return bool
	 */
	public static function is_code_valid( $code ): bool {
		if ( ! is_numeric( $code ) ) {
			return false;
		} elseif ( mb_strlen( $code ) !== 6 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Sanitize the recovery code
	 *
	 * @param string $code  The recovery code must be alphanumeric and 8 characters.
	 *
	 * @return bool
	 */
	public static function is_recovery_code_valid( $code ): bool {
		if ( ! ctype_alnum( $code ) ) {
			return false;
		} elseif ( mb_strlen( $code ) !== 8 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Register the error code for the login form to "shake"
	 *
	 * @param array $shake_error_codes  list of shake error codes.
	 *
	 * @return array
	 */
	public function add_shake_error_code( $shake_error_codes ) {
		if ( ! in_array( 'invalid_2fa_authcode', $shake_error_codes, true ) ) {
			$shake_error_codes[] = 'invalid_2fa_authcode';
		}
		return $shake_error_codes;
	}


	/**
	 * Check if the current user has set up recovery codes
	 *
	 * @param \WP_User $user  WP User class.
	 *
	 * @return bool
	 */
	protected function user_can_use_recovery_code( $user ) {
		if ( isset( $this->options['enable_recovery_code'] ) ) {
			$user_2fa = get_user_meta( $user->ID, self::OPTION_NAME, true );
			if ( empty( $user_2fa ) ) {
				$user_2fa = [];
			}
			if ( ! empty( $user_2fa['recovery_codes'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user can add trust device. Default false.
	 *
	 * @param \WP_User $user User Object.
	 *
	 * @return false
	 */
	private function user_can_add_trust_device( $user ) {
		return false;
	}

	/**
	 * Add 2FA Enabled column to Users page.
	 *
	 * @param array $columns All columns.
	 *
	 * @return array
	 */
	public function add_user_2fa_column( $columns ) {
		$columns['adminoptim_2fa'] = __( '2FA Enabled', 'admin-optimizer' );
		return $columns;
	}

	/**
	 * Render 2FA column in User page
	 *
	 * @param string $value Custom column output.
	 * @param string $column_name Column name.
	 * @param int    $user_id ID of the currently-listed user.
	 *
	 * @return false|string
	 */
	public function render_user_2fa_column( $value, $column_name, $user_id ) {
		if ( 'adminoptim_2fa' === $column_name ) {
			$is_enabled = false;
			$user_2fa   = get_user_meta( $user_id, self::OPTION_NAME, true );
			if ( ! empty( $user_2fa['enabled'] ) ) {
				$is_enabled = true;
			}
			if ( $is_enabled ) {
				// checkmark svg.
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width: 20px;height: 20px;fill: #76a02a;"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>';
			} else {
				// red X svg.
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" style="width: 20px;height: 20px;"><path d="M 6.3895625,6.4195626 C 93.580437,93.610437 93.580437,93.610437 93.580437,93.610437" style="fill:none;fill-rule:evenodd;stroke:#ff0000;stroke-width:18.05195999;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/><path d="M 6.3894001,93.6106 C 93.830213,6.4194003 93.830213,6.4194003 93.830213,6.4194003" style="fill:none;fill-rule:evenodd;stroke:#ff0000;stroke-width:17.80202103;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/></svg>';
			}
		} else {
			return $value;
		}
	}

	/**
	 * Check if the current page is login page
	 *
	 * @return bool
	 */
	protected function is_login_page() {
		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : '';

		if ( (bool) apply_filters( 'adminoptim_is_login_page', false ) ) {
			return true;
		} elseif ( ( strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-login.php' ) !== false
				|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) ) ) {

			return true;

		} elseif ( ( strpos( rawurldecode( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'wp-register.php' ) !== false
					|| ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) ) ) {

			return true;
		}
		return false;
	}
}