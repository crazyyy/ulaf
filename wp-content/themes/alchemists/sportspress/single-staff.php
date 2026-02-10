<?php
/**
 * The template for displaying Single Staff
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   1.0.0
 * @version   3.3.0
 */

get_header();
?>

<?php do_action( 'alc_site_content_before' ); ?>
<div class="site-content" id="content">
	<div class="container">

		<?php while ( have_posts() ) : the_post();

			the_content();

		endwhile; // end of the loop. ?>

	</div>
</div>
<?php do_action( 'alc_site_content_after' ); ?>

<?php
get_footer();
