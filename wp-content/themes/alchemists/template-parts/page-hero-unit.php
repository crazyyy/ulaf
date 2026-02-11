<?php
/**
 * Page Header - Hero Unit
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );

$hero_title      = isset( $alchemists_data['alchemists__opt-page-heading-hero-title'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-title'] : '';
$hero_subtitle   = isset( $alchemists_data['alchemists__opt-page-heading-hero-subtitle'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-subtitle'] : '';
$hero_desc       = isset( $alchemists_data['alchemists__opt-page-heading-hero-desc'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-desc'] : '';
$hero_btn        = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn'] : '';
$hero_btn_txt    = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn-txt'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn-txt'] : '';
$hero_btn_link   = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn-link'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn-link'] : '';
$hero_btn_class  = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn-class'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn-class'] : '';
$hero_btn_type   = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn-type'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn-type'] : '';
$hero_btn_target = isset( $alchemists_data['alchemists__opt-page-heading-hero-btn-target'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-btn-target'] : '';
$hero_stars      = isset( $alchemists_data['alchemists__opt-page-heading-hero-stars'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-stars'] : '';
$hero_img        = isset( $alchemists_data['alchemists__opt-page-heading-hero-img'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-img'] : '';
$hero_img_url    = isset( $alchemists_data['alchemists__opt-page-heading-hero-img-upload']['url'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-img-upload']['url'] : '';
$hero_align      = isset( $alchemists_data['alchemists__opt-page-heading-hero-align'] ) ? $alchemists_data['alchemists__opt-page-heading-hero-align'] : 'left';

// Hero Button Attributes
$hero_btn_attr = array();
if ( 1 == $hero_btn_target ) {
	$hero_btn_attr[] = 'target="_blank"';
}
$hero_btn_attr = implode( ' ', $hero_btn_attr );

// Hero Button Default Classes
$hero_btn_default_classes = array(
	'btn',
	'btn-inverse',
	'btn-sm',
	'btn-icon-right',
	'btn-condensed',
	'hero-unit__btn'
);
if ( 'outline' == $hero_btn_type ) {
	$hero_btn_default_classes[] = 'btn-outline';
} else {
	$hero_btn_default_classes[] = 'btn-primary-inverse';
}
$hero_btn_default_classes = implode( ' ', $hero_btn_default_classes );
?>

<!-- Hero Unit
================================================== -->
<div class="hero-unit">
	<div class="container hero-unit__container">
		<div class="hero-unit__content hero-unit__content--<?php echo esc_attr( $hero_align ); ?>-center">

			<?php if ( $hero_stars ) : ?>
			<span class="hero-unit__decor">
				<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
			</span>
			<?php endif; ?>

			<?php if ( $hero_subtitle ) : ?>
			<h5 class="hero-unit__subtitle"><?php echo esc_html( $hero_subtitle ); ?></h5>
			<?php endif; ?>

			<?php if ( $hero_title ) : ?>
			<h1 class="hero-unit__title"><?php echo wp_kses_post( $hero_title ); ?></h1>
			<?php endif; ?>

			<?php if ( $hero_desc ) : ?>
			<div class="hero-unit__desc"><?php echo esc_html( $hero_desc ); ?></div>
			<?php endif; ?>

			<?php if ( $hero_btn ) : ?>
			<a href="<?php echo esc_url( $hero_btn_link ); ?>" class="<?php echo esc_attr( $hero_btn_default_classes ); ?> <?php echo esc_attr( $hero_btn_class ); ?>" <?php echo esc_attr( $hero_btn_attr ); ?>><?php echo esc_html( $hero_btn_txt ); ?> <i class="fa fa-plus text-primary"></i></a>
			<?php endif; ?>

		</div>

		<?php if ( $hero_img ) : ?>
			<figure class="hero-unit__img">
				<?php if ( !empty( $hero_img_url ) ) : ?>
					<img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php esc_attr_e( 'Hero Image', 'alchemists' ); ?>">
				<?php else : ?>
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/samples/header_player.png" alt="<?php esc_attr_e( 'Hero Image', 'alchemists' ); ?>">
				<?php endif; ?>
			</figure>
		<?php endif; ?>

	</div>
</div>
