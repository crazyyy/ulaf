<?php

namespace Kaizencoders\Utilitify\Modules;

use Kaizencoders\Utilitify\Helper;
use Kaizencoders\Utilitify\Tracker;

class Recaptcha {

	protected $site_key = '';

	protected $secret_key = '';

	protected $is_enable = 0;

	/**
	 * Recaptcha constructor.
	 *
	 * @since 1.0.4
	 */
	public function __construct() {

		$settings = KC_UF()->get_settings();

		$this->is_enable = Helper::get_data( $settings, 'google_recaptcha_options_enable_recaptcha', 0 );

		$this->site_key = Helper::get_data( $settings, 'google_recaptcha_options_v2_site_key', '' );

		$this->secret_key = Helper::get_data( $settings, 'google_recaptcha_options_v2_secret_key', '' );

		if ( is_admin() ) {
			add_filter( 'kc_uf_filter_settings_tab', array( $this, 'add_settings_tab' ), 10, 1 );
			add_filter( 'kc_uf_filter_settings_sections', array( $this, 'add_settings' ), 10, 1 );
		}
	}

	/**
	 * Initialize Class
	 *
	 * @since 1.0.4
	 */
	public function init() {

		if ( $this->is_enable && $this->is_valid_key( $this->secret_key ) && $this->is_valid_key( $this->site_key ) ) {

			// Enqueue required scripts & styles
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_css' ) );

			// Render reCAPTCHA Form
			add_action( 'lostpassword_form', array( $this, 'render_recaptcha' ) );
			add_action( 'register_form', array( $this, 'render_recaptcha' ), 99 );
			add_action( 'login_form', array( $this, 'render_recaptcha' ) );
			add_action( 'signup_extra_fields', array( $this, 'render_recaptcha' ), 99 );

			// Validate Recaptcha
			add_filter( 'registration_errors', array( $this, 'validate_recaptcha' ), 10, 3 );
			add_action( 'lostpassword_post', array( $this, 'validate_recaptcha' ), 10, 1 );
			add_filter( 'authenticate', array( $this, 'validate_recaptcha' ), 30, 3 );

		}
	}

	public function add_settings_tab( $tabs = array() ) {

		$tabs[] = array(
			'id'    => 'google_recaptcha',
			'title' => __( 'Google Recaptcha', 'utilitify' ),
		);

		return $tabs;
	}

	public function add_settings( $sections = array() ) {

		$recaptcha_options = array(
			array(
				'id'      => 'enable_recaptcha',
				'title'   => __( 'Enable Recaptcha', 'utilitify' ),
				'desc'    => '',
				'type'    => 'switch',
				'default' => 0,
			),

			array(
				'id'      => 'v2_site_key',
				'title'   => __( 'Site Key (v2)', 'utilitify' ),
				'desc'    => sprintf( __( '<a href="%s" target="_blank">Click here</a> to create or view keys for Google reCAPTCHA', 'utilitify' ), 'https://www.google.com/recaptcha/admin#list' ),
				'type'    => 'text',
				'default' => '',
			),

			array(
				'id'      => 'v2_secret_key',
				'title'   => __( 'Secret Key (v2)', 'utilitify' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => '',
			),
		);

		$sections[] = array(
			'tab_id'        => 'google_recaptcha',
			'section_id'    => 'options',
			'section_title' => __( 'Options' ),
			'section_order' => 10,
			'fields'        => $recaptcha_options,
		);

		return $sections;
	}

	/**
	 * Enqueue required scripts & styles
	 *
	 * @since 1.0.4
	 */
	public function enqueue_scripts_css() {
		if ( ! wp_script_is( 'kc_uf_recaptcha_google_api', 'registered' ) ) {
			$api_url = 'https://www.google.com/recaptcha/api.js?onload=submitDisable';
			wp_register_script( 'kc_uf_recaptcha_google_api', $api_url, array(), KC_UF_PLUGIN_VERSION );
		}

		if ( ( ! empty( $GLOBALS['pagenow'] ) && ( $GLOBALS['pagenow'] == 'options-general.php' || $GLOBALS['pagenow'] == 'wp-login.php' ) ) || ( function_exists( 'is_account_page' ) && is_account_page() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			wp_enqueue_script( 'kc_uf_recaptcha_google_api' );

			// Add inline style
			wp_register_style( 'kc_uf_recaptcha_css', false );
			wp_enqueue_style( 'kc_uf_recaptcha_css' );
			wp_add_inline_style( 'kc_uf_recaptcha_css', '#lostpasswordform div.g-recaptcha, #loginform div.g-recaptcha, #registerform div.g-recaptcha { margin: 12px 0 24px -15px; }' );
		}
	}

	/**
	 * Is valid key
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @since 1.0.4
	 */
	public function is_valid_key( $key = '' ) {
		if ( strlen( $key ) === 40 ) {
			return true;
		} else {
			return false;
		}
	}

	public function validate_recaptcha( $user_or_email, $username = null, $password = null ) {

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ( isset( $_SERVER['PHP_SELF'] ) && basename( $_SERVER['PHP_SELF'] ) !== 'wp-login.php' && ! isset( $_POST['woocommerce-login-nonce'] ) && ! isset( $_POST['woocommerce-lost-password-nonce'] ) && ! isset( $_POST['woocommerce-register-nonce'] ) ) ) {
			//bypass reCaptcha checking
			return $user_or_email;
		}

		if ( isset( $_POST['g-recaptcha-response'] ) ) {

			$google_api_url = 'https://www.google.com/recaptcha/api/siteverify';

			$response = Helper::get_post_data( 'g-recaptcha-response', '' );
			$ip       = Helper::get_ip();
			$payload  = array( 'secret' => $this->secret_key, 'response' => $response, 'remoteip' => $ip );

			$result = wp_remote_post( $google_api_url, array( 'body' => $payload ) );
			if ( is_wp_error( $result ) ) { // disable SSL verification for older clients and misconfigured TLS trust certificates
				$ch = curl_init();

				curl_setopt( $ch, CURLOPT_URL, $google_api_url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

				$result   = curl_exec( $ch );
				$response = json_decode( $result );

			} else {
				$response = json_decode( $result['body'] );
			}
			if ( is_object( $response ) ) {
				if ( $response->success ) {
					update_option( 'login_nocaptcha_working', true );

					return $user_or_email; // success, let them in
				} else {
					if ( isset( $response->{'error-codes'} ) && $response->{'error-codes'} && in_array( 'missing-input-response', $response->{'error-codes'} ) ) {
						update_option( 'login_nocaptcha_working', true );
						if ( is_wp_error( $user_or_email ) ) {
							$user_or_email->add( 'no_captcha', __( '<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.', 'login-recaptcha' ) );

							return $user_or_email;
						} else {
							return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.', 'login-recaptcha' ) );
						}
					} elseif ( isset( $response->{'error-codes'} ) && $response->{'error-codes'} && ( in_array( 'missing-input-secret', $response->{'error-codes'} ) || in_array( 'invalid-input-secret', $response->{'error-codes'} ) ) ) {
						return $user_or_email; //invalid secret entered; prevent lockouts
					} elseif ( isset( $response->{'error-codes'} ) ) {
						if ( is_wp_error( $user_or_email ) ) {
							$user_or_email->add( 'invalid_captcha', __( '<strong>ERROR</strong>&nbsp;: Incorrect ReCaptcha, please try again.', 'utilitify' ) );

							return $user_or_email;
						} else {
							return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>&nbsp;: Incorrect ReCaptcha, please try again.', 'utilitify' ) );
						}
					} else {
						return $user_or_email; //reCAPTCHA not working, Prevent lockouts
					}
				}
			} else {
				return $user_or_email; //reCAPTCHA not working, Prevent lockouts
			}
		} else {
			if ( isset( $_POST['action'] ) && Helper::get_post_data( 'action', '' ) === 'lostpassword' ) {
				return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.', 'utilitify' ) );
			}

			//If you don't have 'g-recaptcha-response', return only a generic captcha error, not info about about a correct/incorrect user/password.
			return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>&nbsp;: Please check the ReCaptcha box.', 'utilitify' ) );
		}
	}

	/**
	 * Google errors
	 *
	 * @return array
	 *
	 * @since 1.0.4
	 */
	public function google_errors() {
		return array(
			'missing-input-secret'   => __( 'The secret parameter is missing.', 'utilitify' ),
			'invalid-input-secret'   => __( 'The secret parameter is invalid or malformed.', 'utilitify' ),
			'missing-input-response' => __( 'The response parameter is missing.', 'utilitify' ),
			'invalid-input-response' => __( 'The response parameter is invalid or malformed.', 'utilitify' )
		);
	}

	/**
	 * Render recaptcha form
	 *
	 * @since 1.0.4
	 */
	public function render_recaptcha() {

		$is_woocommerce_activated = Tracker::is_plugin_activated( 'woocommerce/woocommerce.php' );

		$woo_buttons = json_encode( array( '.woocommerce-form-login button', '.woocommerce-form-register button', '.woocommerce-ResetPassword button' ) );

		?>

        <div class="g-recaptcha" id="g-recaptcha" data-sitekey="<?php echo $this->site_key; ?>" data-callback="submitEnable" data-expired-callback="submitDisable"></div>
        <script>
			function submitEnable() {
				var button = document.getElementById('wp-submit');
				if (button === null) {
					button = document.getElementById('submit');
				}
				if (button !== null) {
					button.removeAttribute('disabled');
				}

				<?php if ( $is_woocommerce_activated ) { ?>
				var woo_buttons = '<?php echo $woo_buttons; ?>';
				if (typeof jQuery != 'undefined') {
					jQuery.each(woo_buttons, function (i, btn) {
						jQuery(btn).removeAttr('disabled');
					});
				}
				<?php } ?>
			}

			function submitDisable() {
				var button = document.getElementById('wp-submit');

				// do not disable button with id "submit" in admin context, as this is the settings submit button
				<?php if (! is_admin()) { ?>
				if (button === null) {
					button = document.getElementById('submit');
				}
				<?php } ?>

				if (button !== null) {
					button.setAttribute('disabled', 'disabled');
				}

				<?php if ( $is_woocommerce_activated ) { ?>
				var woo_buttons = '<?php echo $woo_buttons; ?>';
				if (typeof jQuery != 'undefined') {
					jQuery.each(woo_buttons, function (i, btn) {
						jQuery(btn).attr('disabled', 'disabled');
					});
				}
				<?php } ?>
			}
        </script>
        <noscript>
            <div style="width: 100%; height: 473px;">
                <div style="width: 100%; height: 422px; position: relative;">
                    <div style="width: 302px; height: 422px; position: relative;">
                        <iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo $this->secret_key; ?>" frameborder="0" title="captcha" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
                    </div>
                    <div style="width: 100%; height: 60px; border-style: none; bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
	            <textarea id="g-recaptcha-response" name="g-recaptcha-response" title="response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid #c1c1c1; margin: 10px 25px; padding: 0px; resize: none;" value="">
                </textarea>
                    </div>
                </div>
            </div>
            <br>
        </noscript>

	<?php }

}
