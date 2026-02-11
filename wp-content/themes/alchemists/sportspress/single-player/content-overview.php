<?php
/**
 * The template for displaying Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.5
 */
?>

<div class="container">
	<main id="main" class="site-main">

		<?php
		while ( have_posts() ) : the_post();

			the_content();

		endwhile;
		?>

	</main><!-- #main -->
</div>
