<?php
/**
 * Template part for Header Logo.
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

$logo_standard  = isset( $alchemists_data['alchemists__opt-logo-standard']['url'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-standard']['url'] ) : '';
$logo_retina    = isset( $alchemists_data['alchemists__opt-logo-retina']['url'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-retina']['url'] ) : '';

$default_logo_path = '';
$logo_size = [
	'width' => 148,
	'height' => 171,
];

if ( alchemists_sp_preset('soccer') ) {
	$default_logo_path = 'soccer/';
	$logo_size['height'] = 172;
} elseif ( alchemists_sp_preset('football') ) {
	$default_logo_path = 'football/';
	$logo_size['width'] = 104;
	$logo_size['height'] = 125;
} elseif ( alchemists_sp_preset('esports') ) {
	$default_logo_path = 'esports/';
	$logo_size['width'] = 104;
	$logo_size['height'] = 118;
}
?>

<!-- Header Logo -->
<div class="header-logo">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php
		if ( !empty( $logo_standard ) ) :
			$logo_width = isset( $alchemists_data['alchemists__opt-logo-standard']['width'] ) ? $alchemists_data['alchemists__opt-logo-standard']['width'] : '';
			$logo_height = $alchemists_data['alchemists__opt-logo-standard']['height'] ? $alchemists_data['alchemists__opt-logo-standard']['height'] : '';
			?>
			<img src="<?php echo esc_url( $logo_standard ); ?>" <?php if ( !empty( $logo_retina ) ) { ?> srcset="<?php echo esc_url( $logo_retina ); ?> 2x" <?php } ?> class="header-logo__img" width="<?php echo esc_attr( $logo_width ); ?>" height="<?php echo esc_attr( $logo_height); ?>" alt="<?php bloginfo('name'); ?>">
		<?php else : ?>
			<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/<?php echo esc_attr( $default_logo_path ); ?>logo.png" class="header-logo__img" srcset="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/<?php echo esc_attr( $default_logo_path ); ?>logo@2x.png 2x" width="<?php echo esc_attr( $logo_size['width'] ); ?>" height="<?php echo esc_attr( $logo_size['height'] ); ?>" alt="<?php esc_attr( bloginfo('name') ); ?>">
		<?php endif; ?>
	</a>
</div>
<!-- Header Logo / End -->
