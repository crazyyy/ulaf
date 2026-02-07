<?php
$footer_bottom_menu = get_theme_mod( 'enable_footer_bottom_menu', false );
$footer_style       = get_theme_mod( 'footer_style' );
$footer_left_text   = get_theme_mod( 'footer_left_text', 'Copyright (c) ' . date( 'Y' ) . ' <a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a>' );
$footer_right_text  = get_theme_mod( 'footer_right_text', 'Theme for WordPress by <a href="https://stylemixthemes.com/" target="_blank">StylemixThemes</a>' );
$footer_center_text = get_theme_mod( 'footer_center_text', 'Copyright (c) ' . date( 'Y' ) . ' <a href="https://splash.stylemixthemes.com/" target="_blank">Splash</a> Theme for WordPress by <a href="https://stylemixthemes.com/">StylemixThemes</a>' );

$stm_socials         = splash_socials( 'footer_socials' );
$footer_socials_text = get_theme_mod( 'footer_socials_text', esc_html__( 'Follow Us:', 'splash' ) );
?>
<div id="stm-footer-bottom">
	<div class="container">
		<div class="clearfix">

			<?php if ( 'footer_style_three' == $footer_style ) { ?>
				<div class="footer-bottom-center">
					<?php if ( ! empty( $footer_center_text ) ) { ?>
						<div class="footer-bottom-center-text">
							<?php echo wp_kses_post( $footer_center_text ); ?>
						</div>
					<?php } ?>
				</div>

			<?php } else { ?>

				<div class="footer-bottom-left">
					<?php if ( ! empty( $footer_left_text ) ) { ?>
						<div class="footer-bottom-left-text">
							<?php echo wp_kses_post( $footer_left_text ); ?>
						</div>
					<?php } ?>
				</div>

				<div class="footer-bottom-right">
					<div class="clearfix">

						<?php if ( ! empty( $footer_right_text ) ) { ?>
							<div class="footer-bottom-right-text">
								<?php echo wp_kses_post( $footer_right_text ); ?>
							</div>
						<?php } ?>

						<div class="footer-bottom-right-navs">

							<?php if ( $footer_bottom_menu && ! splash_is_layout( 'soccer_two' ) ) { ?>
								<div class="stm-bottom-menu">
									<ul class="footer-menu stm-list-duty heading-font clearfix
								<?php
								if ( ! empty( $footer_right_text ) ) {
										echo esc_attr( 'centered' );
								}
								?>
								">
										<?php
										wp_nav_menu(
											array(
												'theme_location' => 'bottom_menu',
												'depth' => 1,
												'container' => false,
												'menu_class' => 'header-menu clearfix',
												'items_wrap' => '%3$s',
												'link_before' => '<span>',
												'link_after' => '</span>',
												'fallback_cb' => false,
												'stm_megamenu' => false,
											)
										);
										?>
									</ul>
								</div>
							<?php } ?>
							<?php if ( ! empty( $stm_socials ) && ! get_theme_mod( 'show_socials_after_footer_img', false ) && ! splash_is_layout( 'esport' ) ) { ?>

								<div class="footer-socials-unit
								<?php
								if ( empty( $footer_right_text ) ) {
									echo esc_attr( 'right-side' );
								}
								?>
								">
									<?php if ( ! empty( $footer_socials_text ) ) { ?>
										<div class="h6 footer-socials-title">
											<?php echo esc_html( $footer_socials_text ); ?>
										</div>
									<?php } ?>
									<ul class="footer-bottom-socials stm-list-duty">
										<?php foreach ( $stm_socials as $key => $value ) { ?>
											<li class="stm-social-<?php echo esc_attr( $key ); ?>">
												<a href="<?php echo esc_url( $value ); ?>" target="_blank">
													<i class="fa fa-<?php echo esc_attr( $key ); ?>"></i>
												</a>
											</li>
										<?php } ?>
									</ul>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
