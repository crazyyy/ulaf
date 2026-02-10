<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.2.6
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $values
 * * @var $content - shortcode footer
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Dl
 */

$title = $el_class = $values = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$box_classes = array(
	'card',
	'card--no-paddings',
	'widget-team-info',
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
foreach ( $values as $data ) {
	$item = $data;
	$item['el_title']          = isset( $data['el_title'] ) ? $data['el_title'] : '';
	$item['el_desc']           = isset( $data['el_desc'] ) ? $data['el_desc'] : '';
	$item['el_is_active']      = isset( $data['el_is_active'] ) ? $data['el_is_active'] : '';
	$item['el_link_is_active'] = isset( $data['el_link_is_active'] ) ? $data['el_link_is_active'] : '';
	$item['el_link']           = isset( $data['el_link'] ) ? $data['el_link'] : '';
	$values_array[] = $item;
}
?>

<!-- Card -->
<div class="<?php echo trim( esc_attr( $css_class ) ) ?>" <?php echo implode( ' ', $wrapper_attributes ); ?>>

	<?php if ( $title ) : ?>
	<div class="card__header">
		<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
	</div>
	<?php endif; ?>

	<div class="card__content widget__content">
		<?php if ( ! empty( $values_array ) ) : ?>
			<ul class="team-info-list list-unstyled">
				<?php
				foreach ( $values_array as $dl_item ) :
					if ( ! empty( $dl_item['el_title'] ) ) :

						$el_title          = $dl_item['el_title'];
						$el_desc           = $dl_item['el_desc'];
						$el_is_active      = $dl_item['el_is_active'];
						$el_link_is_active = $dl_item['el_link_is_active'];
						$el_link           = $dl_item['el_link'];

						// parse link
						$attributes = array();
						$el_link = ( '||' === $el_link ) ? '' : $el_link;
						$el_link = vc_build_link( $el_link );
						$use_link = false;
						if ( strlen( $el_link['url'] ) > 0 ) {
							$use_link = true;
							$a_href = $el_link['url'];
							$a_title = $el_link['title'];
							$a_target = $el_link['target'];
							$a_rel = $el_link['rel'];
						}

						if ( $use_link ) {
							$attributes[] = 'href="' . trim( $a_href ) . '"';
							$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
							if ( ! empty( $a_target ) ) {
								$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
							}
							if ( ! empty( $a_rel ) ) {
								$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
							}
						}
						$attributes = implode( ' ', $attributes );
						?>

						<li class="team-info__item">
							<span class="team-info__label"><?php echo esc_html( $el_title ); ?></span>
							<span class="team-info__value <?php echo esc_attr( $el_is_active ? 'team-info__value--active' : '' ); ?>">
								<?php
								if ( $el_link_is_active && $use_link ) {
									echo '<a ' . $attributes . '>' . esc_html( $el_desc ) . '</a>';
								} else {
									echo esc_html( $el_desc );
								}
								?>
							</span>
						</li>

					<?php
					endif;
				endforeach;
				?>

				<?php if ( $content ) : ?>
				<li class="team-info__item team-info__item--desc">
					<span class="team-info__desc"><?php echo wpb_js_remove_wpautop( $content, true ); ?></span>
				</li>
				<?php endif; ?>

			</ul>
		<?php endif; ?>
	</div>

</div>
<!-- Card / End -->
