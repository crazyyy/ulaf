<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * SMTP_Email_Settings class
 */
class SMTP_Email_Settings extends WP_Settings_API_Helper {
	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_adminoptim_smtp_email_test', [ $this, 'send_email_test' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => SMTP_Email::OPTION_NAME,
				'option_name'  => SMTP_Email::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-smtp-email-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => SMTP_Email::OPTION_NAME,
				'option_name' => SMTP_Email::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'text',
						'title' => __( 'SMTP Server', 'admin-optimizer' ),
						'id'    => 'smtp-server',
						'name'  => 'smtp_server',
						'desc'  => '',
					],
					[
						'type'  => 'checkbox',
						'title' => __( 'Enable SMTP Authentication', 'admin-optimizer' ),
						'id'    => 'smtp-auth',
						'name'  => 'smtp_auth',
						'label' => 'Enable mail server authentication. This option should be enabled in most cases. ',
					],
					[
						'type'    => 'select',
						'title'   => __( 'SMTP Encryption', 'admin-optimizer' ),
						'id'      => 'smtp-secure',
						'name'    => 'smtp_secure',
						'choices' => [
							'none' => 'None',
							'tls'  => 'TLS',
							'ssl'  => 'SSL',
						],
						'desc'    => '',
					],
					[
						'type'  => 'number',
						'title' => __( 'SMTP Port', 'admin-optimizer' ),
						'id'    => 'smtp-port',
						'name'  => 'smtp_port',
						'desc'  => '',
					],
					[
						'type'  => 'text',
						'title' => __( 'SMTP Username', 'admin-optimizer' ),
						'id'    => 'smtp-username',
						'name'  => 'smtp_username',
						'desc'  => '',
					],
					[
						'type'  => 'password',
						'title' => __( 'SMTP Password', 'admin-optimizer' ),
						'id'    => 'smtp-password',
						'name'  => 'smtp_password',
						'desc'  => '',
					],
					[
						'type'  => 'text',
						'title' => __( 'Send From', 'admin-optimizer' ),
						'id'    => 'send-from',
						'name'  => 'send_from',
						'desc'  => '',
					],
					[
						'type'  => 'email',
						'title' => __( 'Email From', 'admin-optimizer' ),
						'id'    => 'email-from',
						'name'  => 'email_from',
						'desc'  => '',
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - SMTP Email Settings', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( SMTP_Email::OPTION_NAME ); ?>
			<div>
				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Send a Test Email:', 'admin-optimizer' ); ?></th>
						<td>
							<label>
								<input type="email" name="test-smtp-email" id="test-smtp-email"
										value="" class="regular-text"
										placeholder="Enter an email address to send test email">
							</label>
							<?php wp_nonce_field( 'test_smtp_email', 'test_smtp_nonce' ); ?>
						</td>
					</tr>
					</tbody>
				</table>
				<div id="email-status"></div>
				<?php submit_button( 'Send Test Email', 'secondary', 'send-email-test' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Callback function to sanitize user options
	 *
	 * @param array $options User options.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		$protocol          = [ 'none', 'tls', 'ssl' ];
		if ( is_array( $options ) ) {
			if ( isset( $options['smtp_server'] ) ) {
				$sanitized_options['smtp_server'] = sanitize_text_field( $options['smtp_server'] );
			}
			if ( isset( $options['smtp_auth'] ) ) {
				$sanitized_options['smtp_auth'] = 1;
			}
			if ( isset( $options['smtp_secure'] ) ) {
				$sanitized_options['smtp_secure'] = in_array( $options['smtp_secure'], $protocol, true ) ? sanitize_text_field( $options['smtp_secure'] ) : 'none';
			}
			if ( isset( $options['smtp_port'] ) ) {
				$sanitized_options['smtp_port'] = absint( $options['smtp_port'] );
			}
			if ( isset( $options['smtp_username'] ) ) {
				$sanitized_options['smtp_username'] = sanitize_text_field( $options['smtp_username'] );
			}
			if ( isset( $options['smtp_password'] ) ) {
				$sanitized_options['smtp_password'] = wp_strip_all_tags( $options['smtp_password'] );
			}
			if ( isset( $options['send_from'] ) ) {
				$sanitized_options['send_from'] = sanitize_text_field( $options['send_from'] );
			}
			if ( isset( $options['email_from'] ) ) {
				$sanitized_options['email_from'] = sanitize_email( $options['email_from'] );
			}
		}
		return $sanitized_options;
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix Page hook.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, SMTP_Email::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-smtp', SMTP_Email::MODULE_URL . 'assets/js/smtp.min.js', [ 'jquery' ], filemtime( SMTP_Email::MODULE_PATH . 'assets/js/smtp.min.js' ), true );
		}
	}

	/**
	 * Function to send test email
	 *
	 * @return void
	 */
	public function send_email_test() {
		check_ajax_referer( 'test_smtp_email', 'nonce' );

		$response = [];
		$success  = false;
		$email_to = isset( $_POST['email_to'] ) && is_email( wp_unslash( $_POST['email_to'] ) ) ? sanitize_email( wp_unslash( $_POST['email_to'] ) ) : '';
		if ( ! empty( $email_to ) ) {
			$subject = get_bloginfo( 'name' ) . ' Email Test';
			$body    = "Congrats, test email was sent successfully. \n\nYour Faithful servant";
			$sent    = wp_mail( $email_to, $subject, $body );
			if ( $sent ) {
				$success = true;
			} else {
				$response['message'] = 'Sending is not successful.';
			}
		} else {
			$response['message'] = 'Email address is empty.';
		}
		if ( $success ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}
	}
}