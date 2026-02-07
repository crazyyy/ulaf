<div id="stm-footer-top">
	<?php
		if(!is_page_template('coming-soon.php')):
			get_template_part('partials/footer/footer-top');
		endif;
	?>
</div>


<?php
	$enable_footer_bottom = get_theme_mod('enable_footer_bottom', true);

	if($enable_footer_bottom) {
		get_template_part('partials/footer/footer-bottom' );
	}

?>
