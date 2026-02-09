<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.0.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $content - shortcode content
 * @var $values
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Box_Info
 */

$title = $el_class = $values = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$box_classes = array(
	'card',
	'card--info',
	$this->getExtraClass( $el_class ),
	$this->getCSSAnimation( $css_animation ),
);

$class_to_filter = implode( ' ', array_filter( $box_classes ) );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$values = (array) vc_param_group_parse_atts( $values );
$values_array = array();
foreach ($values as $data) {
	$new_link = $data;
	$new_link['el_title'] = isset( $data['el_title'] ) ? $data['el_title'] : '';
	$new_link['el_link']  = isset( $data['el_link'] ) ? $data['el_link'] : '';
	$new_link['i_type'] = isset( $data['i_type'] ) ? $data['i_type'] : '';
	$new_link['i_icon_fontawesome'] = isset( $data['i_icon_fontawesome'] ) ? $data['i_icon_fontawesome'] : '';
	$new_link['i_icon_openiconic'] = isset( $data['i_icon_openiconic'] ) ? $data['i_icon_openiconic'] : '';
	$new_link['i_icon_typicons'] = isset( $data['i_icon_typicons'] ) ? $data['i_icon_typicons'] : '';
	$new_link['i_icon_entypo'] = isset( $data['i_icon_entypo'] ) ? $data['i_icon_entypo'] : '';
	$new_link['i_icon_linecons'] = isset( $data['i_icon_linecons'] ) ? $data['i_icon_linecons'] : '';
	$new_link['i_icon_monosocial'] = isset( $data['i_icon_monosocial'] ) ? $data['i_icon_monosocial'] : '';
	$new_link['i_icon_material'] = isset( $data['i_icon_material'] ) ? $data['i_icon_material'] : '';
	$new_link['i_icon_simpleline'] = isset( $data['i_icon_simpleline'] ) ? $data['i_icon_simpleline'] : '';

	$values_array[] = $new_link;
}

?>

<!-- Card -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>

	<?php if ( $title ) { ?>
		<div class="card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php } ?>

	<div class="card__content">
		<?php echo wpb_js_remove_wpautop( $content, true ); ?>
	</div>

	<?php if ( ! empty( $values_array ) ) : ?>
		<div class="card__footer contact-info">
		<?php
		foreach ( $values_array as $card_item ) :
			if ( ! empty($card_item['el_title']) ) :

				$el_title = $card_item['el_title'];
				$el_link  = $card_item['el_link'];

				$iconClass = 'fa fa-adjust';
				$i_type = $card_item['i_type'];
				// Enqueue needed icon font
				vc_icon_element_fonts_enqueue( $i_type );
				$iconClass = isset( $card_item[ 'i_icon_' . $i_type ] ) ? $card_item[ 'i_icon_' . $i_type ] : 'fa fa-adjust';
				?>

				<address class="contact-info__item">
					<div class="contact-info__icon">
						<i class="<?php echo esc_html( $iconClass ); ?>"></i>
					</div>
					<div class="contact-info__label">
						<?php if ( $el_link ) : ?>
							<a href="<?php echo esc_url( $el_link ); ?>"><?php echo esc_html( $el_title ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $el_title ); ?>
						<?php endif; ?>
					</div>
				</address>

			<?php
			endif;
		endforeach; ?>
		</div>
	<?php endif; ?>

</div>
<!-- Card / End -->
