<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit_Image_Size class
 */
class Limit_Image_Size {
	const OPTION_NAME = 'adminoptim_limit_image_size';

	/**
	 * User Options
	 *
	 * @var false|mixed|null
	 */
	protected $option;

	/**
	 * Settings class
	 *
	 * @var Limit_Image_Size_Settings
	 */
	protected $settings;

	const MENU_SLUG = 'adminoptimizer-limit-image-size';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->option = get_option( self::OPTION_NAME, '' );
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = new Limit_Image_Size_Settings( $this->option );
		add_action( 'adminoptimizer_add_submenu_page', [ $this, 'add_submenu_page' ] );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'check_image_upload_size' ] );
	}

	/**
	 * Add submenu to WP Menu page
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			ADMINOPTIMIZER_MODULES_MENU_SLUG,
			__( 'Limit Image Upload Size', 'admin-optimizer' ),
			__( 'Limit Image Upload Size', 'admin-optimizer' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this->settings, 'render_settings_page' ]
		);
	}

	/**
	 * Check image upload size
	 *
	 * @param array $file Uploaded file.
	 *
	 * @return array
	 */
	public function check_image_upload_size( $file ) {
		if ( ! isset( $this->option['file_limit'] ) ) {
			return $file;
		}

		$is_image = str_contains( $file['type'], 'image' );

		if ( $is_image ) {
			$upload_limit = (int) $this->option['file_limit'];
			$filesize     = $file['size'] / 1024;
			if ( $filesize > $upload_limit ) {
				$file['error'] = 'Image files must be smaller than ' . $upload_limit . 'KB';
			}
		}

		return $file;
	}
}
