<?php
/**
 * Template part for displaying Post Navigation a Single Post Page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */

$alchemists_data   = get_option( 'alchemists_data' );
$categories_toggle = isset( $alchemists_data['alchemists__posts-categories'] ) ? $alchemists_data['alchemists__posts-categories'] : 1;
$post_author       = isset( $alchemists_data['alchemists__blog-post-author'] ) ? $alchemists_data['alchemists__blog-post-author'] : true;

// check if prev, next post exist
$prevPost = get_previous_post();
$nextPost = get_next_post();
?>
<!-- Next/Prev Posts -->
<div class="post-related">

	<div class="card card--clean">
		<div class="card__header">
			<h4><?php esc_html_e( 'Other Articles', 'alchemists' ); ?></h4>
		</div>
	</div>

	<div class="row posts--cards">

		<?php $prevPost = get_previous_post();

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
					'posts__item--card',
					'card',
					$post_class
				);
				?>

				<div class="col-lg-6">
					<!-- Prev Post -->
					<div <?php post_class( $post_classes ); ?>>
						<figure class="posts__thumb">

							<?php if ( $categories_toggle ) : ?>
								<?php alchemists_post_category_labels(); ?>
							<?php endif; ?>

							<a href="<?php the_permalink(); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'alchemists_thumbnail', array( 'class' => '' ));
								} else {
									echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' ) . '" alt="" />';
								}
								?>
							</a>
						</figure>
						<div class="posts__inner card__content">
							<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
							<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php the_time( get_option('date_format') ); ?></time>
							<h6 class="posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
						</div>
						<footer class="posts__footer card__footer">
							<?php if ( $post_author ) : ?>
								<div class="post-author">
									<figure class="post-author__avatar">
										<?php echo get_avatar( get_the_author_meta('email'), '24' ); ?>
									</figure>
									<div class="post-author__info">
										<h4 class="post-author__name"><?php the_author(); ?></h4>
									</div>
								</div>
							<?php endif; ?>
							<?php alchemists_entry_meta_single( $date = 'off' ); ?>
						</footer>
					</div>
					<!-- Prev Post / End -->
				</div>

				<?php
				wp_reset_postdata();
			} //end foreach
		} // end if

		$nextPost = get_next_post();

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
					'posts__item--card',
					'card',
					$post_class
				);
				?>

				<div class="col-lg-6">
					<!-- Next Post -->
					<div <?php post_class( $post_classes ); ?>>
						<figure class="posts__thumb">

							<?php if ( $categories_toggle ) : ?>
								<?php alchemists_post_category_labels(); ?>
							<?php endif; ?>

							<a href="<?php the_permalink(); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'alchemists_thumbnail', array( 'class' => '' ));
								} else {
									echo '<img src="' . get_theme_file_uri( '/assets/images/placeholder-380x270.jpg' ) . '" alt="" />';
								}
								?>
							</a>
						</figure>
						<div class="posts__inner card__content">
							<a href="<?php the_permalink(); ?>" class="posts__cta"></a>
							<time datetime="<?php echo esc_attr( get_the_time('c') ); ?>" class="posts__date"><?php the_time( get_option('date_format') ); ?></time>
							<h6 class="posts__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h6>
						</div>
						<footer class="posts__footer card__footer">
							<?php if ( $post_author ) : ?>
								<div class="post-author">
									<figure class="post-author__avatar">
										<?php echo get_avatar( get_the_author_meta('email'), '24' ); ?>
									</figure>
									<div class="post-author__info">
										<h4 class="post-author__name"><?php the_author(); ?></h4>
									</div>
								</div>
							<?php endif; ?>
							<?php alchemists_entry_meta_single( $date = 'off' ); ?>
						</footer>
					</div>
					<!-- Next Post / End -->
				</div>

				<?php
				wp_reset_postdata();
			} //end foreach
		} // end if
		?>
	</div>

</div>
<!-- Next/Prev / End -->
