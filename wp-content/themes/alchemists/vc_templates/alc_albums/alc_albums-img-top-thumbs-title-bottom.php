<?php
/**
 * The template for displaying Album
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.2.8
 * @version   4.4.3
 */

$images = get_field( 'album_photos' );
?>

<div class="<?php echo esc_attr( $posts_classes ); ?>">
	<a href="<?php echo get_permalink( ); ?>" class="gallery__item-inner card">

		<figure class="gallery__thumb gallery__thumb--cropped">
			<?php if ( has_post_thumbnail() ) {
				the_post_thumbnail('alchemists_thumbnail');
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' ) . '" alt="" />';
			} ?>
			<span class="btn-fab gallery__btn-fab"></span>
		</figure>

		<?php if ( $images ) : ?>
			<ul class="gallery__thumb-list list-unstyled">
				<?php foreach ( array_slice( $images, 0, 3 ) as $image ) : ?>
					<li class="gallery__thumb-item">
						<img src="<?php echo esc_url( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="gallery__content card__content">
			<span class="gallery__icon gallery__icon--circle">
				<span class="icon-picture"></span>
			</span>
			<div class="gallery__details">
				<h4 class="gallery__name"><?php echo get_the_title(); ?></h4>
				<div class="gallery__date"><?php the_time( get_option('date_format') ); ?></div>
			</div>
		</div>
	</a>
</div>
