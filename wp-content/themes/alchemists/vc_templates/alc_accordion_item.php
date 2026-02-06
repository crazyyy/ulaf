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
 * @var $panel_is_active
 * @var $el_id
 * @var $content
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Newslog_Item
 */

$title = $panel_is_active = $el_id = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$uniqid = rand();
?>

<div class="card">
	<div class="card__header" id="heading-<?php echo esc_attr( $uniqid ); ?>">
		<h5 class="accordion__title mb-0">
			<button class="btn btn-link accordion__header-link <?php echo esc_attr( ! $panel_is_active ? 'collapsed' : '' ); ?>" type="button" data-toggle="collapse" data-target="#collapse-<?php echo esc_attr( $uniqid ); ?>" aria-expanded="<?php echo esc_attr( $panel_is_active ? 'true' : 'false' ); ?>" aria-controls="collapse-<?php echo esc_attr( $uniqid ); ?>">
				<?php echo esc_html( $title ); ?>
				<span class="accordion__header-link-icon"></span>
			</button>
		</h5>
	</div>
	<div id="collapse-<?php echo esc_attr( $uniqid ); ?>" class="collapse <?php echo esc_attr( $panel_is_active ? 'show' : '' ); ?>" aria-labelledby="heading-<?php echo esc_attr( $uniqid ); ?>" data-parent="#<?php echo esc_attr( $el_id ); ?>">
		<div class="card__content">
			<?php echo wpb_js_remove_wpautop( $content ); ?>
		</div>
	</div>
</div>
