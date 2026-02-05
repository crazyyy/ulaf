<?php

get_header();
?>

	<main id="primary" class="site-main">

		<?php

			get_template_part( 'framework/template-parts/content', 'woocommerce' );

		?>

	</main><!-- main.site-main -->

<?php
get_sidebar();
get_footer();