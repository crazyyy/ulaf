<?php
/**
 * The template for displaying Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   3.3.0
 */

$content_class = 'col-lg-12';

if ( have_rows('player_bio_events') ) {
	$content_class = 'col-lg-8';
}
?>

<div class="container">

	<div class="row">

		<!-- Content -->
		<div class="content <?php echo esc_attr( $content_class ); ?>">

			<?php if ( get_field( 'player_bio_content' ) || get_field( 'player_image' ) ) { ?>

			<!-- Article -->
			<article class="card card--lg post post--single">

				<?php

				$image = get_field('player_image');

				if( !empty($image) ): ?>

					<figure class="post__thumbnail">
						<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" />
					</figure>

				<?php endif; ?>


				<div class="card__content">
					<?php echo apply_filters('the_content', get_field('player_bio_content')); ?>
				</div>

			</article>
			<!-- Article / End -->

			<?php } else { ?>

				<div class="alert alert-warning"><?php esc_html_e( 'Nothing Found. Please check Player Bio section.', 'alchemists' ); ?></div>

			<?php } ?>

		</div>
		<!-- Content / End -->

		<?php if( have_rows('player_bio_events') ): ?>
		<!-- Player Sidebar -->
		<div class="sidebar sidebar--player col-lg-4">

			<!-- Widget: Player Newslog -->
			<aside class="widget card widget--sidebar widget-newslog">
				<div class="widget__title card__header">
					<h4><?php esc_html_e( 'Player Newslog', 'alchemists' ); ?></h4>
				</div>
				<div class="widget__content card__content">

					<?php if( have_rows('player_bio_events') ): ?>

						<ul class="newslog">

						<?php while( have_rows('player_bio_events') ): the_row();

							// vars
							$type    = strtolower(get_sub_field('event_type'));
							$content = get_sub_field('event_content');
							$date    = get_sub_field('event_date');

							?>

							<li class="newslog__item newslog__item--<?php echo esc_attr( $type ); ?>">
								<div class="newslog__item-inner">

									<div class="newslog__content">
										<?php echo wp_kses_post( $content ); ?>
									</div>

									<?php if( $date ): ?>
										<div class="newslog__date"><?php echo esc_html( $date); ?></div>
									<?php endif; ?>
								</div>

							</li>

						<?php endwhile; ?>

						</ul>

					<?php endif; ?>
				</div>
			</aside>
			<!-- Widget: Player Newslog / End -->

		</div>
		<!-- Player Sidebar / End -->
		<?php endif; ?>

	</div>

</div>
