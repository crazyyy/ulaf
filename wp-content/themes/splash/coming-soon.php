<?php
/*
Template Name: Coming soon
*/
?>

<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php
		if ( has_post_thumbnail() ) :
			$page_bg = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		endif;
		?>
		<div class="stm-coming-soon-centered">
			<div class="container">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endwhile; ?>
<?php endif; ?>

<?php if ( splash_is_layout( 'baseball' ) ) : ?>
	<div class="stm-bsb-comingsoon-footer">
		<div class="stm-left">
			<?php
			$footer_left_text  = get_theme_mod( 'footer_left_text', esc_html__( 'Copyright (c) <a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a>', 'splash' ) );
			$footer_right_text = get_theme_mod( 'footer_right_text', esc_html__( 'Theme for WordPress by <a href="https://stylemixthemes.com/" target="_blank">StylemixThemes</a>', 'splash' ) );

			echo wp_kses_post( $footer_left_text . ' ' . $footer_right_text );
			?>
		</div>
		<div class="stm-right">
			<?php
			$stm_socials = splash_socials( 'footer_socials' );

			if ( ! empty( $stm_socials ) ) {
				?>
				<ul class="footer-bottom-socials stm-list-duty">
					<?php foreach ( $stm_socials as $key => $value ) : ?>
						<li class="stm-social-<?php echo esc_attr( $key ); ?>">
							<a href="<?php echo esc_attr( $value ); ?>" target="_blank">
								<i class="fa fa-<?php echo esc_attr( $key ); ?>"></i>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
			}
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $page_bg[0] ) ) : ?>
	<style>
		#wrapper {
			background-image: url("<?php echo esc_url( $page_bg[0] ); ?>");
			background-size: cover;
		}
	</style>
<?php endif; ?>

<?php get_footer(); ?>
