<?php
/**
 * The template for displaying Single Team Gallery Item
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */
?>

<?php setup_postdata( $post ); ?>

<div class="gallery__item col-6 col-sm-4">
	<a href="<?php echo get_permalink( ); ?>" class="gallery__item-inner card">

		<header class="gallery__header card__header">
			<div class="gallery__details">
				<h4 class="gallery__name"><?php echo get_the_title(); ?></h4>
				<div class="gallery__date"><?php the_time( get_option('date_format') ); ?></div>
			</div>
		</header>
		<figure class="gallery__thumb">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'alchemists_thumbnail' );
			} else {
				echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' ) . '" alt="" />';
			}
			?>
			<span class="btn-fab btn-fab--clean gallery__btn-fab"></span>
		</figure>
	</a>
</div>
