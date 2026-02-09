<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * @var $content
 * @var $date
 * @var $item_type
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Newslog_Item
 */

$item_type = $date = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

?>

<li class="newslog__item newslog__item--<?php echo esc_attr( $item_type ); ?>">
	<div class="newslog__item-inner">
		<div class="newslog__content"><?php echo wpb_js_remove_wpautop( $content ); ?></div>
		<?php if ( $date ) : ?>
		<div class="newslog__date"><?php echo esc_html( $date ); ?></div>
		<?php endif; ?>
	</div>
</li>
