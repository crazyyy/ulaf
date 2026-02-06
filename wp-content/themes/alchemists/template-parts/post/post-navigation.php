<?php
/**
 * Template part for displaying Post Navigation a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.10
 */

$alchemists_data = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;

$related_classes = array(
	'post-related',
	'row'
);

$btn_nav_classes = array(
	'btn-nav'
);

$btn_nav_arrow_prev_class = 'fa-chevron-left';
$btn_nav_arrow_next_class = 'fa-chevron-right';

if ( alchemists_sp_preset( 'football') ) {
	$related_classes[] = 'post-related--condensed';
	$btn_nav_classes[] = 'btn-nav--condensed';
	$btn_nav_arrow_prev_class = 'fa-angle-left';
	$btn_nav_arrow_next_class = 'fa-angle-right';
}
?>
<!-- Next/Prev Posts -->
<div class="<?php echo esc_attr( implode( ' ', $related_classes ) ); ?>">

	<?php
	$prevPost = get_previous_post();

	// check if prev post exists
	if(!empty( $prevPost )) {
		$args = array(
			'posts_per_page' => 1,
			'include' => $prevPost->ID
		);

		$prevPost = get_posts($args);
		foreach ($prevPost as $post) {
			setup_postdata($post);

			// get post category class
			$post_class = alchemists_post_category_class();

			$post_classes = array(
				'posts__item',
				$post_class
			);
			?>

			<div class="col-6">
				<!-- Prev Post -->
				<div class="card post-related__prev">
					<div class="card__content">

						<!-- Prev Post Links -->
						<a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( implode( ' ', $btn_nav_classes ) ); ?>">
							<i class="fa <?php echo esc_attr( $btn_nav_arrow_prev_class ); ?>"></i>
						</a>
						<!-- Prev Post Links / End -->

						<ul class="posts posts--simple-list">
							<li <?php post_class( $post_classes ); ?>>
								<div class="posts__inner">

									<?php if ( $categories_toggle ) : ?>
										<?php alchemists_post_category_labels(); ?>
									<?php endif; ?>

									<h6 class="posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
									<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php the_time( get_option('date_format') ); ?></time>
								</div>
							</li>
						</ul>

					</div>
				</div>
				<!-- Prev Post / End -->
			</div>

			<?php
			wp_reset_postdata();
		} //end foreach
	} // end if

	$nextPost = get_next_post();

	// check if next post exists
	if(!empty( $nextPost )) {
		$args = array(
			'posts_per_page' => 1,
			'include' => $nextPost->ID
		);

		$nextPost = get_posts($args);
		foreach ($nextPost as $post) {
			setup_postdata($post);

			// get post category class
			$post_class = alchemists_post_category_class();

			$post_classes = array(
				'posts__item',
				$post_class
			);
			?>

			<div class="col-6">
				<div class="card post-related__next">
					<!-- Next Post -->
					<div class="card__content">

						<ul class="posts posts--simple-list">
							<li <?php post_class( $post_classes ); ?>>
								<div class="posts__inner">

									<?php if ( $categories_toggle ) : ?>
										<?php alchemists_post_category_labels(); ?>
									<?php endif; ?>

									<h6 class="posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
									<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php the_time( get_option('date_format') ); ?></time>
								</div>
							</li>
						</ul>

						<!-- Next Post Link -->
						<a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( implode( ' ', $btn_nav_classes ) ); ?>">
							<i class="fa <?php echo esc_attr( $btn_nav_arrow_next_class ); ?>"></i>
						</a>
						<!-- Next Post Link / End -->

					</div>
					<!-- Next Post / End -->
				</div>
			</div>

			<?php
			wp_reset_postdata();
		} //end foreach
	} // end if
	?>

</div>
<!-- Next/Prev / End -->
