<?php
/**
 * The template for displaying Single Player News
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   3.3.0
 */

$post_tags = get_field('post_tags');
$post_num  = get_field('number_of_posts');
?>

<div class="container">
	<div class="row">

		<div id="primary" class="content-area col-lg-12">
			<main id="main" class="site-main">

				<?php

				$args_player = array(
					'post_type'           => 'post',
					'tag__in'             => $post_tags,
					'posts_per_page'      => $post_num,
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true
				);

				$wp_query_player = new WP_Query( $args_player );

				if ( $wp_query_player->have_posts() ) : ?>

				<div class="posts posts--cards post-grid row">

					<?php while ($wp_query_player->have_posts()) : $wp_query_player->the_post(); ?>

					<?php get_template_part( 'template-parts/content', 'blog-1-3cols' ); ?>

					<?php endwhile; ?>

					<?php // Reset the global $the_post as this query will have stomped on it
					wp_reset_postdata(); ?>

				</div>

				<?php endif; ?>

			</main><!-- #main -->
		</div><!-- #primary -->

	</div>
</div>
