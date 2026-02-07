<?php get_header(); ?>
	<div class="stm-default-page stm-default-page-404">
		<div class="container">
			<div class="text-center heading-font">
				<?php if(!splash_is_af() && !splash_is_layout("baseball")): ?>
					<div class="stm-red stm-404-warning">!</div>
				<?php else: ?>
					<div class="stm-notfound-logo">
						<a href="<?php echo get_site_url(); ?>"><img src="<?php echo get_theme_mod('logo', ''); ?>" /></a>
					</div>
				<?php endif; ?>
				<div class="stm-red stm-404-warning"><?php esc_html_e('404', 'splash'); ?></div>
				<div class="h1 text-transform"><?php esc_html_e('Page not found', 'splash'); ?></div>
				<?php if(!splash_is_af() && !splash_is_layout("baseball")): ?>
				<div class="h5 text-transform">
					<?php esc_html_e('It looks like nothing was found at this location. Maybe try a search?', 'splash'); ?>
				</div>
				<?php else: ?>
					<a class="button btn-md with_bg" href="<?php echo get_site_url(); ?>"><?php esc_html_e("go to home", 'splash')?></a>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php if(splash_is_af() || splash_is_layout("baseball") && !empty(get_theme_mod("bg_img", ''))): ?>
	<style>
		#wrapper {
			background-image: url("<?php echo get_theme_mod('bg_img', ''); ?>");
            background-size: cover;
		}
	</style>
<?php endif; ?>

<?php get_footer(); ?>