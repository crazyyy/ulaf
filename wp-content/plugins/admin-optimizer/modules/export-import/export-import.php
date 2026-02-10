<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Import class
 */
class Export_Import {
	const OPTION_NAME = 'adminoptim_export_import';

	const MENU_SLUG = 'adminoptimizer-export-import';

	const MODULE_URL = ADMINOPTIMIZER_MODULES_URI . 'export-import/';

	const MODULE_PATH = ADMINOPTIMIZER_MODULES_PATH . 'export-import/';

	/**
	 * Settings Class
	 *
	 * @var Custom_Login_Url_Settings
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		$this->settings = new Export_Import_Settings();
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ], 20 );
		add_action( 'admin_init', [ $this, 'export_settings' ] );
		add_action( 'wp_ajax_adminoptim_import_settings', [ $this, 'import_settings' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Export / Import', 'admin-optimizer' ),
			__( 'Export / Import', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Export Settings
	 */
	public function export_settings() {
		if ( isset( $_REQUEST['action'] ) && 'adminoptim_export_settings' === $_REQUEST['action'] ) {
			include_once 'includes/option-names.php';
			if ( ! empty( $_REQUEST['nonce'] ) ) {
				if ( wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'adminoptim-export-settings' ) ) {
					$export_options = $this->get_export_arr();
					header( 'Content-Description: File Transfer' );
					header( 'Content-Type: application/json' );
					header( 'Content-Disposition: attachment; filename="admin-optimizer-settings-' . wp_date( 'Ymd' ) . '.json"' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate' );
					header( 'Pragma: public' );
					echo wp_json_encode( $export_options );
					die();
				}
			}
		}
	}

	/**
	 * Get export array
	 *
	 * @return array
	 */
	private function get_export_arr() {
		$export_arr = [];
		foreach ( AO_OPTION_NAMES as $option_name ) {
			$option = get_option( $option_name );
			if ( ! empty( $option ) && is_array( $option ) ) {
				$export_arr[ $option_name ] = $option;
			}
		}
		return $export_arr;
	}

	/**
	 * Import Settings
	 */
	public function import_settings() {
		check_ajax_referer( 'adminoptim-import-settings', 'nonce' );

		include_once 'includes/option-names.php';

		$file         = isset( $_FILES['file']['name'] ) ? sanitize_file_name( $_FILES['file']['name'] ) : '';
		$filename_arr = explode( '.', $file );
		$extension    = end( $filename_arr );

		if ( 'json' !== $extension ) {
			wp_send_json_error( [ 'message' => __( 'Please upload a valid .json file', 'admin-optimizer' ) ] );
		}

		$import_file = isset( $_FILES['file']['tmp_name'] ) ? $_FILES['file']['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $import_file ) ) {
			wp_send_json_error( [ 'message' => __( 'Please upload a file to import', 'admin-optimizer' ) ] );
		}

		// Retrieve the settings from the file and convert the json object to an array.
	    $settings = json_decode( file_get_contents( $import_file ), true ); // phpcs:ignore -- file_get_contents() is fine here.

		if ( ! empty( $settings ) && is_array( $settings ) ) {
			foreach ( $settings as $key => $setting ) {
				if ( in_array( $key, AO_OPTION_NAMES, true ) ) {
					array_walk_recursive( $setting, 'sanitize_text_field' );
					update_option( $key, $setting, false );
				}
			}
		}
		wp_send_json_success(
			[
				'status'  => 'success',
				'message' => __( 'Settings imported', 'admin-optimizer' ),
			]
		);
	}
}
