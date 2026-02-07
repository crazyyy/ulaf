<?php get_header();?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="stm-single-donation stm-default-page">
			<div class="container">
				<?php if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						get_template_part('partials/global/donation-content');
					endwhile;
				endif; ?>
			</div>
		</div>
	</div>
<?php get_footer();?>