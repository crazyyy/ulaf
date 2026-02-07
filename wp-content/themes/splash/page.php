<?php get_header(); ?>

	<?php if(!splash_is_layout('magazine_one') && !splash_is_layout('soccer_two') && !splash_is_layout('hockey')) get_template_part('partials/global/title-box'); ?>
	<?php get_template_part('partials/global/page-color'); ?>

	<div class="container">
		<?php if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
		endif; ?>

		<?php splash_pages_pagination(); ?>

		<div class="clearfix">
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</div>

<?php get_footer(); ?>