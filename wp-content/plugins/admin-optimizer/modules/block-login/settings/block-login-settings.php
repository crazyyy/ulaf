<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Block_Login_Settings class
 */
class Block_Login_Settings extends WP_Settings_API_Helper {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Block_Login::MENU_SLUG,
				'option_name'  => Block_Login::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			'free' => [
				'id'          => 'adminoptimizer-block-login',
				'title'       => '',
				'description' => '',
				'menu_slug'   => Block_Login::MENU_SLUG,
				'option_name' => Block_Login::OPTION_NAME,
				'fields'      => [
					[
						'type'    => 'number',
						'title'   => __( 'Failed Login Attempts', 'admin-optimizer' ),
						'id'      => 'failed-login-attempts',
						'name'    => 'failed_count',
						'min'     => '1',
						'max'     => '10',
						'default' => '3',
						'desc'    => __( 'Number of failed login attempts before 15 minutes lockout.', 'admin-optimizer' ),

					],
				],
			],
			'pro'  => [
				'id'          => 'adminoptimizer-block-login-pro',
				'title'       => __( 'Pro Options', 'admin-optimizer' ),
				/* translators: %1$s is the anchor link to the Pro version. %2$s is the closing anchor tag */
				'description' => sprintf( __( 'Upgrade to the %1$sPro version%2$s to access these features', 'admin-optimizer' ), '<a href="' . esc_url( 'https://www.adminoptimizer.com/#pricing' ) . '" target="_blank">', '</a>' ),
				'menu_slug'   => Block_Login::MENU_SLUG . '_pro',
				'option_name' => Block_Login::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'number',
						'title'    => __( 'Lockout Time', 'admin-optimizer' ),
						'id'       => 'lockout-time',
						'name'     => 'lockout-time-free-disabled',
						'min'      => '1',
						'max'      => '100',
						'default'  => '15',
						'disabled' => true,
						'desc'     => __( 'The lock out time (in minutes) before the user can log in again. Allowed value is between 1 to 100', 'admin-optimizer' ),
					],
					[
						'type'     => 'number',
						'title'    => __( 'Total Lockout', 'admin-optimizer' ),
						'id'       => 'lockout-count',
						'name'     => 'lockout-count-free-disabled',
						'min'      => '1',
						'max'      => '10',
						'disabled' => true,
						'desc'     => __( 'Number of lockouts before activating a full login block for 24 hours.', 'admin-optimizer' ),
					],
					[
						'type'     => 'checkbox',
						'title'    => __( 'Hide Login form on Lockout', 'admin-optimizer' ),
						'id'       => 'hide-login-form',
						'name'     => 'hide-login-form-free-disabled',
						'disabled' => true,
						'desc'     => '',
					],
				],
			],
		];
		$this->settings_sections = apply_filters( 'adminoptim_block_login_sections', $this->settings_sections );
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
			<h1><?php esc_html_e( 'Admin Optimizer - Block Failed Login Settings', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Block_Login::MENU_SLUG ); ?>
			<div class="adminoptim-pro-options">
				<?php $this->render_settings_on_page( Block_Login::MENU_SLUG . '_pro', [ 'remove_submit_button' => true ] ); ?>
			</div>
			<h2><?php esc_html_e( 'IP Address Lockout Log', 'admin-optimizer' ); ?></h2>
			<div id="ip-table-status"></div>
			<?php
			$lockout_table = new Block_Login_List_Table();
				$lockout_table->prepare_items();
				$lockout_table->display();
			?>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts to the Settings page
	 *
	 * @param string $hook_suffix  the hook to check if we are on the right page.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Block_Login::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim_lock_login_settings', Block_Login::MODULE_URL . 'assets/js/block-login-settings.min.js', [ 'jquery' ], filemtime( Block_Login::MODULE_PATH . 'assets/js/block-login-settings.min.js' ), true );
			wp_localize_script( 'adminoptim_lock_login_settings', 'blockLoginSettings', [ 'nonce' => wp_create_nonce( 'adminoptim_block_login_action' ) ] );
			wp_enqueue_style( 'adminoptim-modules-pro-settings' );
		}
	}

	/**
	 * Callback function to sanitize settings before storing in database
	 *
	 * @param array|null $options  Values to be sanitized.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		$sanitized_options = [];
		if ( is_array( $options ) ) {
			if ( isset( $options['failed_count'] ) ) {
				$sanitized_options['failed_count'] = absint( $options['failed_count'] );
			}
		}

		return apply_filters( 'adminoptim_sanitize_block_login_settings', $sanitized_options, $options );
	}
}