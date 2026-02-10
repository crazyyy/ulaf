<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.6.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $layout_style
 * @var $values
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Social_Buttons
 */

$layout_style = $values = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if ( $layout_style == 'columns') {
	$layout_style = 'widget-social--condensed';
} elseif ( $layout_style == 'grid') {
	$layout_style = 'widget-social--grid';
} else {
	$layout_style = '';
}

$class_to_filter = 'widget widget--sidebar widget-social ' . $layout_style;
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$values = (array) vc_param_group_parse_atts( $values );
$values_array = array();
foreach ($values as $data) {
	$new_link = $data;
	$new_link['btn_label'] = isset( $data['btn_label'] ) ? $data['btn_label'] : '';
	$new_link['btn_label_2'] = isset( $data['btn_label_2'] ) ? $data['btn_label_2'] : '';
	$new_link['btn_link'] = isset( $data['btn_link'] ) ? $data['btn_link'] : '';
	$new_link['btn_type'] = isset( $data['btn_type'] ) ? $data['btn_type'] : '';

	$values_array[] = $new_link;
}

?>

<!-- Widget: Social Buttons -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if (!empty($values_array)) : ?>
		<?php foreach ( $values_array as $btn_line ) : ?>
			<?php if( !empty($btn_line['btn_label']) ) : ?>

				<?php
				$btn_label = $btn_line['btn_label'];
				$btn_label_2 = $btn_line['btn_label_2'];
				$btn_link = $btn_line['btn_link'];
				$btn_type = $btn_line['btn_type'];


				$btn_class = '';
				$icon_class = '';

				if ( $btn_type == 'twitter' ) {
					$btn_class = 'btn-social-counter--twitter';
					$icon_class = 'fa-x-twitter';
				} elseif ( $btn_type == 'instagram' ) {
					$btn_class = 'btn-social-counter--instagram';
					$icon_class = 'fa-instagram';
				} elseif ( $btn_type == 'youtube' ) {
					$btn_class = 'btn-social-counter--youtube';
					$icon_class = 'fa-youtube';
				} else {
					$btn_class = 'btn-social-counter--fb';
					$icon_class = 'fa-facebook';
				}

				?>

				<a href="<?php echo esc_url( $btn_link ); ?>" class="btn-social-counter <?php echo esc_attr( $btn_class ); ?>" target="_blank">
					<div class="btn-social-counter__icon">
						<i class="fab <?php echo esc_attr( $icon_class ); ?>"></i>
					</div>
					<h6 class="btn-social-counter__title"><?php echo esc_html( $btn_label ); ?></h6>
					<span class="btn-social-counter__count"><?php echo esc_html( $btn_label_2 ); ?></span>
					<span class="btn-social-counter__add-icon"></span>
				</a>

			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<!-- Widget: Social Buttons / End -->
