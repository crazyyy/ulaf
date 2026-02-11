<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\RemoteMerge\Totp\TotpException;
use Yipresser\AdminOptimizer\Vendor\RemoteMerge\Totp\TotpFactory;

/**
 * Two-Factor Authentication User Settings class
 */
class Two_Factor_Authentication_User_Settings {

	/**
	 * User setting values
	 *
	 * @var array
	 */
	private $user_2fa_settings = [];

	/**
	 * 2FA Settings values
	 *
	 * @var false|mixed|null
	 */
	private $twofa_settings = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->twofa_settings = get_option( Two_Factor_Authentication::OPTION_NAME, [] );

		add_action( 'show_user_profile', [ $this, 'add_user_2fa_section' ] );
		add_action( 'edit_user_profile', [ $this, 'add_user_2fa_section' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_adminoptim-setup-2fa', [ $this, 'setup_2fa' ] );
		add_action( 'wp_ajax_adminoptim-reset-2fa', [ $this, 'reset_2fa' ] );
		add_action( 'wp_ajax_adminoptim-generate-recovery-code', [ $this, 'setup_recovery_code' ] );
		add_action( 'wp_ajax_adminoptim-download-recovery-code', [ $this, 'download_recovery_code' ] );
	}

	/**
	 * Function to add user 2FA setup section
	 *
	 * @param \WP_User $user  Current user class.
	 *
	 * @return void
	 * @throws TotpException  Throw an exception if code validation fails.
	 */
	public function add_user_2fa_section( $user ) {
		$current_user  = wp_get_current_user();
		$can_setup_2fa = false;
		if ( $current_user->ID === $user->ID ) {
			$can_setup_2fa = true;
		}
		$user_2fa = get_user_meta( $user->ID, Two_Factor_Authentication::OPTION_NAME, true );
		?>
		<h2><?php esc_html_e( 'Two Factor Authentication', 'admin-optimizer' ); ?></h2>
		<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Authenticator App', 'admin-optimizer' ); ?></th>
			<?php if ( $can_setup_2fa ) : ?>
				<td>
				<?php
				wp_enqueue_script( 'adminoptim-qrcode-generator' );
				wp_nonce_field( 'user_2fa_info', 'user_2fa_nonce' );
				if ( ! empty( $user_2fa['enabled'] ) ) :
					?>
					<div style="margin-bottom:2rem;">
					<?php esc_html_e( 'Two factor authentication is enabled.', 'admin-optimizer' ); ?>
					</div>
				<?php endif; ?>
				<div id="adminoptim-qrcode-wrap">
					<?php if ( ! empty( $user_2fa['secret'] ) ) : ?>
						<p><button class="button button-secondary" id="2fa-reset-btn" data-user="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Reset and rescan QR code', 'admin-optimizer' ); ?></button></p>
						<div id="adminoptim-2fa-status"></div>
					<?php else : ?>
						<?php
						$data = get_bloginfo( 'name' ) . ':' . $user->user_login;
						// Create a new TOTP instance.
						$totp = TotpFactory::create();
						$totp->configure( [ 'algorithm' => 'sha256' ] );
						// Generate a new secret key for the user.
						$secret = $user_2fa['secret'] ?? $totp->generateSecret();
						$uri    = $totp->generateUri( $secret, $data, get_bloginfo( 'name' ) );
						?>
						<p><?php esc_html_e( 'Scan the QR code with your authenticator app, then enter the 6 digits code to the field below and click Validate.', 'admin-optimizer' ); ?></p>
						<div id="2fa-qrcode-wrap"></div>
						<code><?php echo esc_html( $secret ); ?></code>
						<p><label for="">
								<?php esc_html_e( 'Authentication code:', 'admin-optimizer' ); ?>
								<input type="number" value="" placeholder="123456" name="adminoptim_2fa_code" id="adminoptim-2fa-code">
								<button class="button button-secondary" id="2fa-validator-btn" data-secret="<?php echo esc_attr( $secret ); ?>" data-user="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Validate', 'admin-optimizer' ); ?></button>
							</label></p>
						<div id="adminoptim-2fa-status"></div>
						<?php ob_start(); ?>
							document.addEventListener("DOMContentLoaded", (event) => {
								const matrix = QrCode.generate('<?php echo esc_html( $uri ); ?>');
								const uri = QrCode.render('svg-uri', matrix);
								let img = document.createElement('img');
								img.src = uri;
								img.width = '200';
								img.height = '200';
								img.id = '2fa-qrcode';
								document.getElementById('2fa-qrcode-wrap').appendChild(img);
							});
						<?php wp_print_inline_script_tag( ob_get_clean() ); ?>
					<?php endif; ?>
				</div>
				<?php if ( isset( $this->twofa_settings['enable_recovery_code'] ) && ! empty( $user_2fa['secret'] ) ) : ?>
					<?php if ( isset( $user_2fa['recovery_codes'] ) ) : ?>
						<div id="recovery-code" style="margin-top:2rem;">
							<div id="rc-status"></div>
							<p>
								<?php
								/* translators: %d: number of unused codes */
								printf( esc_html__( '%d unused codes remaining, each recovery code can only be used once.', 'admin-optimizer' ), count( $user_2fa['recovery_codes'] ) );
								?>
							</p>
							<p><button class="button button-secondary" id="generate-rc-btn" data-user="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Generate new recovery codes', 'admin-optimizer' ); ?></button></p>
						</div>
					<?php else : ?>
						<div id="recovery-code" style="margin-top:2rem;">
							<div id="rc-status"></div>
							<p><button class="button button-secondary" id="generate-rc-btn" data-user="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Generate recovery codes', 'admin-optimizer' ); ?></button></p>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				</td>
			<?php else : ?>
				<td>
					<?php if ( ! empty( $user_2fa['enabled'] ) ) : ?>
						<p><?php esc_html_e( 'The user has enabled two-factor authentication.', 'admin-optimizer' ); ?></p>
					<?php else : ?>
						<p><?php esc_html_e( 'The user did not enable two-factor authentication.', 'admin-optimizer' ); ?></p>
					<?php endif; ?>
				</td>
			<?php endif; ?>
		</tr>
		</table>
		<?php
	}

	/**
	 * Enqueue scripts on the Settings page
	 *
	 * @param string $hook_suffix  The hook suffix to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		wp_register_script( 'adminoptim-qrcode-generator', Two_Factor_Authentication::MODULE_URL . 'assets/js/qrcode.js', [], '1.0.0', false );
		if ( 'profile.php' === $hook_suffix ) {
			wp_enqueue_script( 'adminoptim-2fa-validator', Two_Factor_Authentication::MODULE_URL . 'assets/js/validator.min.js', [ 'jquery' ], '1.0.1', true );
		}
	}


	/**
	 * Set up 2FA for the current user
	 *
	 * @throws TotpException  Throw an exception if the validation fails.
	 */
	public function setup_2fa() {
		check_ajax_referer( 'user_2fa_info', 'nonce' );
		$response = [];
		$user_id  = isset( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : get_current_user_id();
		$secret   = isset( $_REQUEST['secret'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['secret'] ) ) : '';
		if ( ! isset( $_REQUEST['code'] ) ) {
			$response = new \WP_Error(
				'adminoptim-no-code-found',
				wp_get_admin_notice(
					__( 'No 2FA code is found', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		} elseif ( ! is_numeric( $_REQUEST['code'] ) ) {
			$response = new \WP_Error(
				'adminoptim-code-not-digits',
				wp_get_admin_notice(
					__( 'The 2FA code is not digits', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		} elseif ( mb_strlen( sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ) ) !== 6 ) {
			$response = new \WP_Error(
				'adminoptim-code-must-be-6-digits',
				wp_get_admin_notice(
					__( 'The 2FA code must be 6 digits', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		}
		$code = trim( sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ) );
		if ( ! empty( $user_id ) && ! empty( $secret ) && ! empty( $code ) ) {
			$totp = TotpFactory::create();
			// Allow discrepancy of 1 time slice.
			if ( $totp->verifyCode( $secret, $code, 1 ) ) {
				$user_2fa_settings            = [];
				$user_2fa_settings['secret']  = $secret;
				$user_2fa_settings['enabled'] = 1;
				update_user_meta( $user_id, 'adminoptim_2fa', $user_2fa_settings );
				$response['message'] = wp_get_admin_notice(
					__( 'Two-factor authentication validated and enabled successfully.', 'admin-optimizer' ),
					[
						'type' => 'success',
					]
				);
				wp_send_json_success( $response );
			} else {
				$response = new \WP_Error(
					'adminoptim-invalid-2facode',
					wp_get_admin_notice(
						__( 'Invalid 2FA code', 'admin-optimizer' ),
						[
							'type' => 'error',
						]
					)
				);
				wp_send_json_error( $response );
			}
		} else {
			$response = new \WP_Error( 'adminoptim-invalid-2fa-info', __( 'Invalid 2FA information', 'admin-optimizer' ) );
			wp_send_json_error( $response );
		}
	}

	/**
	 * Ajax action to disable user 2FA
	 *
	 * @param int $user_id  user ID.
	 *
	 * @return void
	 */
	public function reset_2fa( $user_id ) {
		check_ajax_referer( 'user_2fa_info', 'nonce' );

		$response = [];
		$user_id  = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : 0;
		if ( $user_id < 1 ) {
			$response = new \WP_Error(
				'adminoptim-invalid-user',
				wp_get_admin_notice(
					__( 'Invalid user.', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		} else {
			delete_user_meta( $user_id, 'adminoptim_2fa' );
			$response['message'] = wp_get_admin_notice(
				__( 'The authenticator app has been disabled successfully. Please reload the page to set it up again.', 'admin-optimizer' ),
				[
					'type' => 'success',
				]
			);
			wp_send_json_success( $response );
		}
	}

	/**
	 * Setting up recovery codes settings on User Settings section
	 *
	 * @return void
	 */
	public function setup_recovery_code() {
		check_ajax_referer( 'user_2fa_info', 'nonce' );
		$response = [];
		$user_id  = isset( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : get_current_user_id();
		if ( $user_id < 1 ) {
			$response = new \WP_Error(
				'adminoptim-invalid-user',
				wp_get_admin_notice(
					__( 'Invalid user.', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		} else {
			$user_2fa                   = get_user_meta( $user_id, 'adminoptim_2fa', true );
			$recovery_codes             = $this->generate_recovery_code();
			$user_2fa['recovery_codes'] = $recovery_codes['hashed_codes'];
			update_user_meta( $user_id, 'adminoptim_2fa', $user_2fa );
			$user     = get_user_by( 'id', $user_id );
			$filename = sanitize_title_with_dashes( get_bloginfo( 'name' ) ) . '-2FA-recovery-codes-' . $user->user_login . '.txt';
			$message  = '<p>' . __( 'Write these down! Once you navigate away from this page, you will not be able to view these codes again.', 'admin-optimizer' ) . '</p>';
			$message .= '<ul>';
			foreach ( $recovery_codes['codes'] as $code ) {
				$message .= '<li>' . $code . '</li>';
			}
			$message            .= '</ul>';
			$message            .= '<p class="description">' . __( 'Download the codes here. Download link expires in 15 minutes. Once you navigate away from this page, you will not be able to download the codes again.', 'admin-optimizer' ) . '</p>';
			$message            .= '<p><a class="button button-secondary" href="' . $recovery_codes['download_link'] . '" download="' . $filename . '">' . __( 'Download Codes', 'admin-optimizer' ) . '</a></p>';
			$response['message'] = $message;
			wp_send_json_success( $response );
		}
	}

	/**
	 * Function to generate recovery codes
	 *
	 * @return array
	 * @throws \Random\RandomException  Throws error if generation fails.
	 */
	private function generate_recovery_code() {
		$code_length  = 8;
		$codes        = [];
		$hashed_codes = [];
		for ( $i = 0; $i < 10; $i++ ) {
			$codes[] = strtoupper( bin2hex( random_bytes( $code_length / 2 ) ) );
		}
		for ( $i = 0; $i < 10; $i++ ) {
			$hashed_codes[] = wp_hash_password( $codes[ $i ] );
		}
		$download_link = $this->download_rc_link( $codes );
		return [
			'codes'         => $codes,
			'hashed_codes'  => $hashed_codes,
			'download_link' => $download_link,
		];
	}

	/**
	 * Generate link for user to download recovery codes.
	 *
	 * @param string[] $codes  A list of generated recovery codes.
	 *
	 * @return string
	 */
	public function download_rc_link( $codes ) {
		$download_link = '';
		if ( ! empty( $codes ) && is_array( $codes ) ) {
			$blogname     = get_bloginfo( 'name' );
			$current_date = wp_date( 'Y-m-d H:i:s' );
			$timezone     = wp_timezone_string();
			$message      = rawurlencode( "Recovery codes for {$blogname}\n" );
			$message     .= rawurlencode( "Keep these backup codes somewhere safe but accessible.\n\n" );
			foreach ( $codes as $code ) {
				$message .= rawurlencode( "{$code}\n" );
			}
			$message      .= rawurlencode( "\n" );
			$message      .= rawurlencode( "* You can only use each backup code once.\n" );
			$message      .= rawurlencode( "* These codes were generated on {$current_date} ({$timezone})" );
			$download_link = 'data:application/text;charset=utf-8,' . $message;
		}
		return $download_link;
	}
}