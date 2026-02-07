			</div> <!--main-->

			<?php get_template_part('partials/footer/footer-image'); ?>

		</div> <!--wrapper-->
		<div class="stm-footer <?php echo get_theme_mod('footer_background_image') ? 'background-image' : ''; ?>" style="background: url(<?php echo esc_url(get_theme_mod('footer_background_image'));  ?>) no-repeat center; background-size: cover; background-color: #222;">
			<?php get_template_part('partials/footer/footer-default'); ?>
		</div>

		<?php get_template_part('partials/global/modals/modals-controller'); ?>

		<?php
			if ( get_theme_mod( 'frontend_customizer' ) ) {
				get_template_part( 'partials/global/frontend_customizer' );
			}
		?>
		<div class="rev-close-btn">
			<span class="close-left"></span>
			<span class="close-right"></span>
		</div>
	<?php wp_footer(); ?>
	</body>
</html>