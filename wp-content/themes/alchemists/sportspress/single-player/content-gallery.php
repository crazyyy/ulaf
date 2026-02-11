<?php
/**
 * The template for displaying Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$gallery_layout   = isset( $alchemists_data['alchemists__player-gallery-layout'] ) ? $alchemists_data['alchemists__player-gallery-layout'] : 'fixed';

$container_class = '';
$album_wrapper_classes = array( 'album-wrapper' );
$album_classes = array( 'album' );

if ( 'fixed' == $gallery_layout ) {
	$container_class = 'container';
	$album_classes[] = 'row';
}
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main <?php echo esc_attr( $container_class ); ?>">

		<?php
		$images = get_field('images');

		if ( $images ): ?>

		<!-- Gallery Album -->
		<div class="<?php echo esc_attr( implode( ' ', $album_wrapper_classes ) ); ?>">
			<div class="<?php echo esc_attr( implode( ' ', $album_classes ) ); ?>">

				<?php
				foreach ( $images as $image ) :
					include( locate_template( 'sportspress/single-player/gallery/gallery-' . $gallery_layout . '.php' ) );
				endforeach;
				?>

			</div>
		</div>
		<!-- Gallery Album / End -->
		<?php endif; ?>

	</main><!-- #main -->
</div><!-- #primary -->
