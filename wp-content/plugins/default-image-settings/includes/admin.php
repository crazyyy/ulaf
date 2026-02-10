<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit of accessed directly

/**
 * Admin class
 *
 * @since 1.0
 */
class DEFIS_Admin {

	/**
	 * Plugin class instance
	 *
	 * @var DEFIS
	 * @since 1.0
	 */
	public $defis;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct( DEFIS $defis ) {
		$this->defis = $defis;

		// Hooks
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	/**
	 * Initialize the settings sections and fields
	 * Should be called on admin_init
	 *
	 * @since 1.0
	 */
	public function init_settings() {
		add_settings_section( 'defis-default-image-settings', __( 'Default Image Settings', 'defis' ), NULL, 'media' );

		add_settings_field( 'defis-image-default-size', __( 'Default Size', 'defis' ), array( $this, 'settings_field_image_default_size' ), 'media', 'defis-default-image-settings' );
		add_settings_field( 'defis-image-default-link-type', __( 'Default Link Type', 'defis' ), array( $this, 'settings_field_image_default_link_type' ), 'media', 'defis-default-image-settings' );
		add_settings_field( 'defis-image-default-align', __( 'Default Align', 'defis' ), array( $this, 'settings_field_image_default_align' ), 'media', 'defis-default-image-settings' );
	}

	/**
	 * Display callback for the default image size settings field
	 *
	 * @since 1.0
	 */
	public function settings_field_image_default_size() {
		global $_wp_additional_image_sizes;

		// Available sizes
		$sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __( 'Thumbnail' ),
			'medium'    => __( 'Medium' ),
			'large'     => __( 'Large' ),
			'full'      => __( 'Full Size' )
		) );

		foreach ( $sizes as $size => $label ) {
			// Width
			if ( ! empty( $_wp_additional_image_sizes[ $size ]['width'] ) ) {
				$width = $_wp_additional_image_sizes[ $size ]['width'];
			}
			else {
				$width = get_option( $size . '_size_w' );
			}

			// Height
			if ( ! empty( $_wp_additional_image_sizes[ $size ]['height'] ) ) {
				$height = $_wp_additional_image_sizes[ $size ]['height'];
			}
			else {
				$height = get_option( $size . '_size_h' );
			}

			// Crop
			if ( ! empty( $_wp_additional_image_sizes[ $size ]['crop'] ) ) {
				$crop = $_wp_additional_image_sizes[ $size ]['crop'];
			}
			else {
				$crop = get_option( $size . '_crop' );
			}

			// Construct label from image data
			$parts = array();

			if ( $width || $height ) {
				$parts[] = absint( $width ) . ' &times; ' . absint( $height );
			}

			if ( $crop ) {
				$parts[] = __( 'cropped', 'defis' );
			}

			if ( ! empty( $parts ) ) {
				$sizes[ $size ] .= ' &ndash; ' . implode( ', ', $parts );
			}
		}

		// Current size
		$current_size = get_option( 'image_default_size' );
		?>
		<select name="image_default_size" id="defis-image-default-size">
			<?php foreach ( $sizes as $size => $label ) : ?>
				<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $size, $current_size ); ?>><?php echo esc_html_e( $label, 'defis' ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Display callback for the default image link type settings field
	 *
	 * @since 1.0
	 */
	public function settings_field_image_default_link_type() {
		$options = array(
			'file' => __( 'Media File' ),
			'post' => __( 'Attachment Page' ),
			'custom' => __( 'Custom URL' ),
			'none' => __( 'None' )
		);

		// Current type
		$current_type = get_option( 'image_default_link_type', 'file' );
		?>
		<select name="image_default_link_type" id="defis-image-default-link-type">
			<?php foreach ( $options as $option => $label ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $current_type ); ?>><?php echo esc_html_e( $label, 'defis' ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Display callback for the default image alignment settings field
	 *
	 * @since 1.0
	 */
	public function settings_field_image_default_align() {
		$options = array(
			'left' => __( 'Left' ),
			'center' => __( 'Center' ),
			'right' => __( 'Right' ),
			'' => __( 'None' )
		);

		// Current type
		$current_align = get_option( 'image_default_align' );
		?>
		<select name="image_default_align" id="defis-image-default-align">
			<?php foreach ( $options as $option => $label ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $current_align ); ?>><?php echo esc_html_e( $label, 'defis' ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}
