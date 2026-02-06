<?php
/**
 * The template for displaying Single Team
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.2.5
 */
?>

<?php
while ( have_posts() ) : the_post();

	the_content();

endwhile;
?>
