<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yipresser\AdminOptimizer\Vendor\Yipresser\WpSettingsApiHelper\WP_Settings_API_Helper;

/**
 * Limit_Image_Size_Settings class
 */
class Limit_Image_Size_Settings extends WP_Settings_API_Helper {
	/**
	 * User options
	 *
	 * @var array
	 */
	private $option;

	/**
	 * Constructor
	 *
	 * @param array $option User Options.
	 */
	public function __construct( $option ) {
		$this->option = $option;
		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_options = [
			[
				'option_group' => Limit_Image_Size::OPTION_NAME,
				'option_name'  => Limit_Image_Size::OPTION_NAME,
				'args'         => [ 'sanitize_callback' => [ $this, 'sanitize_settings' ] ],
			],
		];

		$this->settings_sections = [
			[
				'id'          => 'adminoptimizer-limit-image-size',
				'title'       => '',
				'description' => '',
				'menu_slug'   => Limit_Image_Size::OPTION_NAME,
				'option_name' => Limit_Image_Size::OPTION_NAME,
				'fields'      => [
					[
						'type'     => 'callback',
						'title'    => __( 'Set maximum file upload size', 'admin-optimizer' ),
						'id'       => 'image-upload-file-limit',
						'callback' => [ $this, 'render_setting_field' ],
					],
				],
			],
		];
		$this->setup();
	}

	/**
	 * Render Settings fields
	 *
	 * @return void
	 */
	public function render_setting_field() {
		$attrs              = [];
		$attrs['name']      = Limit_Image_Size::OPTION_NAME . '[file_limit]';
		$attrs['id']        = 'image-upload-file-limit';
		$attrs['value']     = $this->option['file_limit'] ?? '';
		$attrs['size']      = 4;
		$attrs['maxlength'] = 5;
		?>
		<p><input
		<?php
		foreach ( $attrs as $key => $value ) {
			echo esc_html( $key ) . '="' . esc_attr( $value ) . '" ';
		}
		?>
		/> <?php echo esc_html( 'KB (1MB = 1000KB)' ); ?>
		<br>
		<span class="description"><?php esc_html_e( 'Server maximum:', 'admin-optimizer' ); ?> <?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></span>
		</p>
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
		if ( is_array( $options ) && isset( $options['file_limit'] ) ) {
			$limit        = absint( intval( $options['file_limit'] ) );
			$server_limit = (int) $this->get_server_upload_limit();
			if ( $limit > $server_limit ) {
				$limit = $server_limit;
			}
			$options['file_limit'] = $limit;
		}
		return $options;
	}

	/**
	 * Get server upload limit
	 *
	 * @return float
	 */
	public function get_server_upload_limit() {
		$server_limit = wp_max_upload_size();  // returned data in bytes.
		$server_limit = round( $server_limit / 1024, -2 ); // convert to kilobytes.
		return $server_limit;
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Limit Image Upload Size Settings', 'admin-optimizer' ); ?></h1>
			<?php settings_errors(); ?>
			<?php $this->render_settings_on_page( Limit_Image_Size::OPTION_NAME ); ?>
		</div>
		<?php
	}
}