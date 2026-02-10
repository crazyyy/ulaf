<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Two-Factor Authentication Settings Page class
 */
class Two_Factor_Authentication_Settings extends WP_Settings_API_Helper {
	/**
	 * The options value stored in the database
	 *
	 * @var false|array|null
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param array $options The options value passed to the Settings page.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_adminoptim_2fa_user_settings', [ $this, 'ajax_mange_user_2fa' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Two_Factor_Authentication::MENU_SLUG,
				'option_name'  => Two_Factor_Authentication::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			'free' => [
				'id'          => 'adminoptimizer-2fa-admin-settings',
				'title'       => '',
				'description' => __( 'By default, every user can enable two-factor authentication on their User profile page. You can change this behavior by enabling the following settings.', 'admin-optimizer' ),
				'menu_slug'   => Two_Factor_Authentication::MENU_SLUG,
				'option_name' => Two_Factor_Authentication::OPTION_NAME,
				'fields'      => [
					[
						'type'  => 'checkbox',
						'id'    => 'recovery-code',
						'title' => __( 'Recovery Code', 'admin-optimizer' ),
						'name'  => 'enable_recovery_code',
						'label' => __( 'Enable backup recovery code', 'admin-optimizer' ),
					],
					[
						'type'  => 'checkbox',
						'id'    => 'user-2fa-column',
						'title' => __( 'User 2FA Column', 'admin-optimizer' ),
						'name'  => 'add_user_2fa_column',
						'label' => __( 'Add a "2FA Enabled" column in All Users page to get an overview of who has enabled/not enabled 2FA.', 'admin-optimizer' ),
					],
				],
			],
			'pro'  => [
				'id'          => 'adminoptimizer-2fa-admin-settings-pro',
				'title'       => __( 'Pro Options', 'admin-optimizer' ),
				/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
				'description' => sprintf( __( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' ),
				'menu_slug'   => Two_Factor_Authentication::MENU_SLUG . '_Pro',
				'option_name' => Two_Factor_Authentication::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'id'       => '2fa-compulsory-roles',
						'title'    => __( 'Make Two-factor Authentication compulsory for these roles', 'admin-optimizer' ),
						'callback' => [ $this, 'render_compulsory_roles_checkboxes' ],
					],
					[
						'type'     => 'callback',
						'id'       => '2fa-trusted-devices',
						'title'    => __( 'Allow these roles to set up trusted device', 'admin-optimizer' ),
						'name'     => 'enable_trusted_devices',
						'desc'     => __( '2FA will be temporary disabled on a trusted device. This feature requires browser cookies and an https (i.e. SSL) connection to the website to work.', 'admin-optimizer' ),
						'callback' => [ $this, 'render_trusted_devices_settings' ],
					],
				],
			],
		];
		$this->settings_sections = apply_filters( 'adminoptim_2fa_sections', $this->settings_sections );
		$this->setup();
	}

	/**
	 * Render User Roles Settings field
	 *
	 * @param array $field Settings field.
	 *
	 * @return void
	 */
	public function render_compulsory_roles_checkboxes( $field ) {
		$roles = get_editable_roles();

		if ( ! empty( $field['desc'] ) ) {
			echo '<p>' . esc_html( $field['desc'] ) . '<br/></p>';
		}

		$disabled = 'disabled="disabled"';

		foreach ( $roles as $role => $role_details ) {
			$name = translate_user_role( $role_details['name'] );
			?>
			<label for="<?php echo esc_attr( $role ); ?>"><input id="<?php echo esc_attr( $role ); ?>" name="<?php echo esc_attr( Two_Factor_Authentication::OPTION_NAME ); ?>[user_roles][]" type="checkbox" <?php echo esc_attr( $disabled ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></label><br/>
			<?php
		}
		$block_users_day = 10;
		?>
		<p><label for="block-users-days"><input id="block-users-login" name="<?php echo esc_attr( Two_Factor_Authentication::OPTION_NAME ); ?>[block-users-login]" type="checkbox" value="1" <?php echo esc_attr( $disabled ); ?>>
				<?php
				/* translators: %s: day interval */
				printf( esc_html__( 'For users who didn\'t set up 2FA, block them from logging in %s days after their next login', 'admin-optimizer' ), '<input type="number" name="' . esc_attr( Two_Factor_Authentication::OPTION_NAME ) . '[block_users_days]" id="block-users-days" size="2" disabled="disabled" value="' . esc_attr( $block_users_day ) . '">' );
				?>
			</label></p>
		<p><input type="checkbox" name="<?php echo esc_attr( Two_Factor_Authentication::OPTION_NAME ); ?>[show_admin_notice]" id="show-admin-notice" value="1" <?php echo esc_attr( $disabled ); ?>> <?php esc_html_e( 'Add a persistent notification to remind the user to set up 2FA.', 'admin-optimizer' ); ?></p>
		<?php
	}

	/**
	 * Render trusted device settings field
	 *
	 * @param array $field setting field.
	 *
	 * @return void
	 */
	public function render_trusted_devices_settings( $field ) {
		$roles = get_editable_roles();

		if ( ! empty( $field['desc'] ) ) {
			echo '<p>' . esc_html( $field['desc'] ) . '<br/></p>';
		}

		$disabled = 'disabled="disabled"';
		foreach ( $roles as $role => $role_details ) {
			$name = translate_user_role( $role_details['name'] );
			?>
			<label for="<?php echo esc_attr( $role ); ?>"><input id="<?php echo esc_attr( $role ); ?>" name="<?php echo esc_attr( Two_Factor_Authentication::OPTION_NAME ); ?>[trusted-roles][]" type="checkbox" <?php echo esc_attr( $disabled ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></label><br/>
			<?php
		}
		$trusted_time = 30;
		?>
		<p>
			<?php
			/* translators: %s: day interval */
			printf( esc_html__( 'Hide 2FA authentication for %s days on trusted device', 'admin-optimizer' ), '<input type="number" name="' . esc_attr( Two_Factor_Authentication::OPTION_NAME ) . '[trusted_time]" id="2fa-trusted-time" size="2" disabled="disabled" value="' . esc_attr( $trusted_time ) . '">' );
			?>
		</p>
		<?php
	}

	/**
	 * Render the Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Two-Factor Authentication Settings', 'admin-optimizer' ); ?></h1>
			<?php $tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<h2 class="nav-tab-wrapper">
				<?php $admin_url = admin_url( 'admin.php?page=' . Two_Factor_Authentication::MENU_SLUG ); ?>
				<a href="<?php echo esc_url( $admin_url ); ?>" class="nav-tab
				<?php
				if ( empty( $tab ) ) {
					echo ' nav-tab-active'; }
				?>
				"><?php esc_html_e( '2FA Settings', 'admin-optimizer' ); ?></a>
				<a href="<?php echo esc_url( $admin_url . '&tab=user' ); ?>" class="nav-tab
				<?php
				if ( 'user' === $tab ) {
					echo ' nav-tab-active'; }
				?>
				"><?php esc_html_e( 'Manage Users 2FA', 'admin-optimizer' ); ?></a>
			</h2>

			<?php if ( 'user' === $tab ) : ?>
				<p><?php esc_html_e( 'Manage user\'s 2FA settings here.', 'admin-optimizer' ); ?></p>
				<div id="settings-status"></div>
				<?php $this->render_manage_users_settings(); ?>
			<?php else : ?>
				<?php settings_errors(); ?>
				<?php $this->render_settings_on_page( Two_Factor_Authentication::MENU_SLUG ); ?>
				<div class="adminoptim-pro-options">
					<?php $this->render_settings_on_page( Two_Factor_Authentication::MENU_SLUG . '_Pro', [ 'remove_submit_button' => true ] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Callback to render the Users dropdown field
	 *
	 * @return void
	 */
	private function render_manage_users_settings() {
		wp_nonce_field( 'adminoptim_2fa_user_action', 'nonce' );
		?>
		<p>
			<?php
			wp_dropdown_users(
				[
					'name'            => 'adminoptim_2fa_user',
					'id'              => 'adminoptim-2fa-user',
					'show_option_all' => 'All Users',
					'show'            => 'display_name_with_login',
				]
			);
			?>
			<button class="button button-primary user-2fa-btn" data-action="deactivate"><?php esc_html_e( 'Deactivate 2FA', 'admin-optimizer' ); ?></button>
		</p>
		<?php
	}

	/**
	 * Callback function to sanitize the Settings fields.
	 *
	 * @param array|null $options  The options value to be sanitized.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		if ( is_array( $options ) ) {
			if ( isset( $options['enable_recovery_code'] ) ) {
				$sanitized_options['enable_recovery_code'] = 1;
			}
			if ( isset( $options['add_user_2fa_column'] ) ) {
				$sanitized_options['add_user_2fa_column'] = 1;
			}
		}
		$sanitized_options = apply_filters( 'adminoptim_sanitize_2fa_settings', $sanitized_options, $options );
		return $sanitized_options;
	}

	/**
	 * Enqueue scripts on the Settings page
	 *
	 * @param string $hook_suffix  The hook suffix to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Two_Factor_Authentication::MENU_SLUG ) ) {
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );

			if ( isset( $_GET['tab'] ) && 'user' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_script( 'adminoptim-select2', Two_Factor_Authentication::MODULE_URL . 'assets/js/select2.min.js', [ 'jquery' ], '4.1.0', false );
				wp_enqueue_script( 'adminoptim-2fa-settings', Two_Factor_Authentication::MODULE_URL . 'assets/js/settings.min.js', [ 'jquery', 'adminoptim-select2' ], filemtime( Two_Factor_Authentication::MODULE_PATH . 'assets/js/settings.min.js' ), true );
				wp_enqueue_style( 'adminoptim-select2', Two_Factor_Authentication::MODULE_URL . 'assets/css/select2.min.css', [], '4.1.0' );
			}
		}
	}

	/**
	 * Ajax action for admin to manage user's 2FA settings.
	 *
	 * @return void
	 */
	public function ajax_mange_user_2fa() {
		check_ajax_referer( 'adminoptim_2fa_user_action', 'nonce' );
		$response = [];
		$action   = ! empty( $_REQUEST['user_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_action'] ) ) : '';
		if ( 'deactivate' !== $action ) {
			$response = new \WP_Error(
				'adminoptim-no-2fa-action-found',
				wp_get_admin_notice(
					__( 'No appropriate action is found', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		}

		$user_id = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : 0;
		if ( $user_id > 0 ) {
			$user_2fa = get_user_meta( $user_id, 'adminoptim_2fa', true );
			if ( ! empty( $user_2fa ) && is_array( $user_2fa ) ) {
				if ( isset( $user_2fa['enabled'] ) ) {
					unset( $user_2fa['enabled'] );
					unset( $user_2fa['secret'] );
					update_user_meta( $user_id, 'adminoptim_2fa', $user_2fa );
					$response['message'] = wp_get_admin_notice(
						__( 'Two-factor authentication disabled for user.', 'admin-optimizer' ),
						[
							'type' => 'success',
						]
					);
					wp_send_json_success( $response );
				}
			} else {
				$response = new \WP_Error(
					'adminoptim-invalid-user-2fa',
					wp_get_admin_notice(
						__( 'Cannot deactivate 2FA. This user did not have a valid 2FA setup.', 'admin-optimizer' ),
						[
							'type' => 'error',
						]
					)
				);
				wp_send_json_error( $response );
			}
		} else {
			$response = new \WP_Error(
				'adminoptim-invalid-user',
				wp_get_admin_notice(
					__( 'Invalid user', 'admin-optimizer' ),
					[
						'type' => 'error',
					]
				)
			);
			wp_send_json_error( $response );
		}
	}
}