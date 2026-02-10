<?php
namespace WPSMTP;

use SolidWP\Mail\Admin\SettingsScreen;

class Admin {

	/**
	 * The new solid mail setting.
	 *
	 * @var false|mixed|null
	 */
	private $solidMailOptions;

	public function __construct() {
		$this->solidMailOptions = get_option( SettingsScreen::SETTINGS_SLUG );

		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu() {
		$icon_url = WPSMTP_ASSETS_URL . 'images/Solid-Mail-Icon.svg';

		add_menu_page(
			__( 'Solid Mail', 'wp-smtp' ),
			__( 'Solid Mail', 'wp-smtp' ),
			'manage_options',
			'solidwp-mail',
			[
				$this,
				'render_solidsmtp',
			],
			$icon_url 
		);

		if ( ! isset( $this->solidMailOptions['disable_logs'] ) || 'yes' !== $this->solidMailOptions['disable_logs'] ) {
			add_submenu_page(
				'solidwp-mail',
				__( 'Mail Logs', 'wp-smtp' ),
				__( 'Mail Logs', 'wp-smtp' ),
				'manage_options',
				'solidwp-mail-logs',
				[
					$this,
					'render_solidsmtp',
				] 
			);
		}

		// Add the Settings submenu
		add_submenu_page(
			'solidwp-mail',
			__( 'Settings', 'wp-smtp' ),
			__( 'Settings', 'wp-smtp' ),
			'manage_options',
			'solidwp-mail-settings',
			[
				$this,
				'render_solidsmtp',
			] 
		);
	}

	/**
	 * Render the hook point for the app.
	 *
	 * @return void
	 */
	public function render_solidsmtp() {
		require_once WPSMTP_PATH . 'src/admin-views/admin-root.php';
	}
}
