<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use const Yipresser\AdminOptimizer\Admin\MODULES_OPTION;

/**
 * Images_Media class
 */
class Media_Management {
	/**
	 * User options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * List of modules
	 *
	 * @var array
	 */
	protected $modules = [];

	/**
	 * Constructor
	 *
	 * @param array $options User options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
		$this->init();
	}

	/**
	 * Start the engine running
	 *
	 * @return void
	 */
	private function init() {
		add_filter( 'adminoptimizer_settings_navtab', [ $this, 'add_settings_navtab' ] );
		add_filter( 'adminoptimizer_settings_sections', [ $this, 'settings_fields' ] );
		if ( ! empty( $this->options['enable_image_alt_text'] ) ) {
			add_action( 'add_attachment', [ $this, 'add_image_alt_tag' ] );
		}
		if ( ! empty( $this->options['enable_image-_underscore_to_hyphen'] ) ) {
			add_filter( 'sanitize_file_name', [ $this, 'convert_imagename_underscore_hyphen' ] );
		}
		if ( ! empty( $this->options['enable_svg_upload'] ) ) {
			$this->modules['svg_upload'] = new SVG_Upload();
		}
		if ( ! empty( $this->options['enable_limit_image_upload_size'] ) ) {
			$this->modules['limit_image_upload_size'] = new Limit_Image_Size();
		}
	}

	/**
	 * Add settings navtab
	 *
	 * @param array $nav_tab List of nav tabs.
	 *
	 * @return array
	 */
	public function add_settings_navtab( $nav_tab ) {
		if ( empty( $nav_tab['media'] ) ) {
			$nav_tab['media'] = [
				'title' => __( 'Media Management', 'admin-optimizer' ),
				'slug'  => 'adminoptim-media-settings',
			];
		}
		return $nav_tab;
	}

	/**
	 * List of Settings fields
	 *
	 * @param array $fields Settings fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ) {
		if ( empty( $fields['media'] ) ) {
			$fields['media'] = [
				'id'          => 'adminoptimizer-media-settings',
				'title'       => '',
				'description' => '',
				'menu_slug'   => 'adminoptim-media-settings',
				'option_name' => MODULES_OPTION,
				'fields'      => [
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Limit image upload file size', 'admin-optimizer' ),
						'id'    => 'enable-limit-image-upload-size',
						'name'  => 'enable_limit_image_upload_size',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Limit the image upload file size.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/limit-image-upload-file-size/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Set image filename as alt text', 'admin-optimizer' ),
						'id'    => 'enable-image-alt-text',
						'name'  => 'enable_image_alt_text',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Auto set image filename as alt text.%1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/set-image-filename-as-alt-text/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Convert underscore (_) in image filename to hyphen (-)', 'admin-optimizer' ),
						'id'    => 'enable-image-underscore-to-hyphen',
						'name'  => 'enable_image_underscore_to_hyphen',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Auto sanitize the image filename and convert underscore (_) to hyphen (-) during image upload. %1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/convert-underscore-in-filename-to-hyphen/' ) . '" target="_blank">', '</a>' ),
					],
					[
						'type'  => 'slider-checkbox',
						'title' => __( 'Enable SVG image upload', 'admin-optimizer' ),
						'id'    => 'enable-svg-upload',
						'name'  => 'enable_svg_upload',
						// translators: %1$s is the line break tag. %2$s is the anchor tag. %3$s is the anchor closure tag.
						'label' => sprintf( __( 'Enable upload of SVG file. %1$s%2$sLearn more%3$s', 'admin-optimizer' ), '<br/>', '<a href="' . esc_url( 'https://www.adminoptimizer.com/docs/enable-svg-image-upload/' ) . '" target="_blank">', '</a>' ),
					],
				],
			];
		}
		return $fields;
	}

	/**
	 * Add image alt tag
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function add_image_alt_tag( $post_id ) {
		// Check if uploaded file is an image, else do nothing.

		if ( wp_attachment_is_image( $post_id ) ) {

			$my_image_title = get_post( $post_id )->post_title;

			// Sanitize the title:  remove hyphens, underscores & extra spaces.
			$my_image_title = preg_replace( '%\s*[-_\s]+\s*%', ' ', $my_image_title );

			// Sanitize the title:  capitalize first letter of every word (other letters lower case).
			$my_image_title = ucwords( strtolower( $my_image_title ) );

			// Set the image Alt-Text.
			update_post_meta( $post_id, '_wp_attachment_image_alt', $my_image_title );
		}
	}

	/**
	 * Convert image name underscore to hyphen
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 */
	public function convert_imagename_underscore_hyphen( $filename ): string {

		$info = pathinfo( $filename );
		$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
		$name = basename( $filename, $ext );
		$name = str_replace( '_', '-', $name );

		return $name . $ext;
	}
}
