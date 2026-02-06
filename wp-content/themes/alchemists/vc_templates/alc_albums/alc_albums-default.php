<?php
/**
 * The template for displaying Album
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   4.5.0
 */
?>

<div class="<?php echo esc_attr( $posts_classes ); ?>">

	<a href="<?php echo get_permalink( ); ?>" class="gallery__item-inner card">

		<figure class="gallery__thumb">
			<?php if( has_post_thumbnail( ) ) {
				the_post_thumbnail( $thumb_size );
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/' . $thumb_placeholder . '.jpg' ) . '" alt="" />';
			} ?>
			<span class="btn-fab gallery__btn-fab"></span>
		</figure>

		<div class="gallery__content card__content">
			<span class="gallery__icon">
				<span class="icon-camera"></span>
			</span>
			<div class="gallery__details">
				<h4 class="gallery__name"><?php echo get_the_title(); ?></h4>
				<div class="gallery__date"><?php the_time( get_option('date_format') ); ?></div>
			</div>
		</div>

	</a>

</div>
