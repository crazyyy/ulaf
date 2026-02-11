<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

// don't show Footer if if Canvas template used
if ( ! is_page_template( 'template-canvas.php' ) ) :

	$alchemists_data = get_option( 'alchemists_data' );

	// Footer Logo
	$logo_footer           = isset( $alchemists_data['alchemists__opt-footer-logo'] ) ? esc_html( $alchemists_data['alchemists__opt-footer-logo'] ) : '';
	$logo_footer_standard  = isset( $alchemists_data['alchemists__opt-logo-footer-standard']['url'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-footer-standard']['url'] ) : '';
	$logo_footer_retina    = isset( $alchemists_data['alchemists__opt-logo-footer-retina']['url'] ) ? esc_html( $alchemists_data['alchemists__opt-logo-footer-retina']['url'] ) : '';
	$footer_title          = isset( $alchemists_data['alchemists__opt-logo-footer-title'] ) ? $alchemists_data['alchemists__opt-logo-footer-title'] : '';
	$footer_tagline        = isset( $alchemists_data['alchemists__opt-logo-footer-tagline'] ) ? $alchemists_data['alchemists__opt-logo-footer-tagline'] : '';

	// Footer Widgets
	$footer_widgets         = isset( $alchemists_data['alchemists__opt-footer-widgets'] ) ? esc_html( $alchemists_data['alchemists__opt-footer-widgets'] ) : '';
	$footer_widgets_layout  = isset( $alchemists_data['alchemists__opt-footer-widgets-layout'] ) ? esc_html( $alchemists_data['alchemists__opt-footer-widgets-layout'] ) : '';
	$footer_widgets_overlay = isset( $alchemists_data['alchemists__footer-widgets--overlay'] ) ? esc_html( $alchemists_data['alchemists__footer-widgets--overlay'] ) : 'off';

	// Footer Secondary
	$footer_secondary      = isset( $alchemists_data['alchemists__opt-secondary'] ) ? esc_html( $alchemists_data['alchemists__opt-secondary'] ) : '';

	// Copyright
	$footer_copyright      = isset( $alchemists_data['alchemists__footer-secondary-copyright'] ) ? $alchemists_data['alchemists__footer-secondary-copyright'] : '';
	$footer_copyright      = do_shortcode( wp_kses_post( str_replace( '[year]', date_i18n( 'Y' ), $footer_copyright ) ) );
	$footer_layout         = isset( $alchemists_data['alchemists__footer-secondary-copyright-layout'] ) ? $alchemists_data['alchemists__footer-secondary-copyright-layout'] : 'default';


	$footer_logo_width = 'col-sm-12 col-lg-3';
	if ( $footer_widgets == 0 ) {
		$footer_logo_width = 'col-sm-12';
	}

	// Footer Widgets Overlay
	$footer_widgets_classes = array();

	if ( 'duotone' == $footer_widgets_overlay ) {
		array_push( $footer_widgets_classes, 'effect-duotone', 'effect-duotone--base' );
	} elseif ( 'simple' == $footer_widgets_overlay ) {
		$footer_widgets_classes[] = 'footer-widgets--overlay';
	}

	$footer_widgets_classes = implode( ' ', $footer_widgets_classes );


	// Widgets Columns Width
	$footer_widgets_col = 'col-sm-4 col-lg-3';

	// Soccer and American Football
	if ( alchemists_sp_preset( 'soccer' ) || alchemists_sp_preset( 'football' ) || alchemists_sp_preset( 'esports' ) ) {
		if ( $footer_widgets_layout == 1 ) {
			$footer_widgets_col = 'col-sm-4 col-lg-4';
		} elseif ( $footer_widgets_layout == 2 ) {
			$footer_widgets_col = 'col-sm-6 col-lg-3';
		}
	} else {
		// Basketball
		if ( $logo_footer == 0 ) {
			// Logo disabled
			if ( $footer_widgets_layout == 1 ) {
				$footer_widgets_col = 'col-sm-4 col-lg-4';
			} elseif ( $footer_widgets_layout == 2 ) {
				$footer_widgets_col = 'col-sm-6 col-lg-3';
			}
		} else {
			// Logo enabled
			if ( $footer_widgets_layout == 1 ) {
				$footer_widgets_col = 'col-sm-4 col-lg-3';
			} elseif ( $footer_widgets_layout == 2 ) {
				$footer_widgets_col = 'col-sm-4 col-lg-3';
			}
		}
	}
	?>

		<!-- Footer
		================================================== -->
		<footer id="footer" class="footer">

			<?php do_action( 'alc_site_footer_widgets_before' ); ?>

			<?php if ( alchemists_sp_preset('football') ) : ?>
			<?php $footer_primary = isset( $alchemists_data['alchemists__footer-primary'] ) ? esc_html( $alchemists_data['alchemists__footer-primary'] ) : ''; ?>
				<?php if ( $footer_primary != 0 ) : ?>
				<!-- Footer Info -->
				<div class="footer-info">
					<div class="container">

						<div class="footer-info__inner">

							<?php if ( $logo_footer != 0 ) : ?>
							<!-- Footer Logo -->
							<div class="footer-logo footer-logo--has-txt">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<?php if ( !empty( $logo_footer_standard ) ) { ?>
										<img src="<?php echo esc_url( $logo_footer_standard ); ?>" alt="<?php bloginfo('name'); ?>" <?php if ( !empty( $logo_footer_retina ) ) { ?> srcset="<?php echo esc_url( $logo_footer_retina ); ?> 2x" <?php } ?> class="footer-logo__img">
									<?php } else { ?>
										<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/logo-footer.png" class="footer-logo__img" srcset="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/logo-footer@2x.png 2x" alt="<?php esc_attr( bloginfo('name') ); ?>">
									<?php } ?>

									<div class="footer-logo__heading">
										<h5 class="footer-logo__txt">
											<?php
											if ( $footer_title ) {
												echo esc_html( $footer_title );
											} else {
												esc_html( bloginfo('name') );
											}
											?>
										</h5>
										<?php if ( get_bloginfo( 'description' ) || $footer_tagline ) : ?>
											<span class="footer-logo__tagline">
												<?php
												if ( $footer_tagline ) {
													echo esc_html( $footer_tagline );
												} else {
													bloginfo( 'description' );
												}
												?>
											</span>
										<?php endif; ?>
									</div>
								</a>
							</div>
							<!-- Footer Logo / End -->
							<?php endif; ?>

							<!-- Info Block -->
							<?php
							$icon_custom_primary  = isset( $alchemists_data['alchemists__footer-primary-info-1-icon-custom'] ) ? $alchemists_data['alchemists__footer-primary-info-1-icon-custom'] : '';
							$icon_custom_secondary = isset( $alchemists_data['alchemists__footer-primary-info-2-icon-custom'] ) ? $alchemists_data['alchemists__footer-primary-info-2-icon-custom'] : '';

							$email_1 = isset( $alchemists_data['alchemists__footer-primary-info-1-email'] ) ? $alchemists_data['alchemists__footer-primary-info-1-email'] : '';
							$email_2 = isset( $alchemists_data['alchemists__footer-primary-info-2-email'] ) ? $alchemists_data['alchemists__footer-primary-info-2-email'] : '';

							$email_1_label = isset( $alchemists_data['alchemists__footer-primary-info-1-label'] ) ? $alchemists_data['alchemists__footer-primary-info-1-label'] : '';
							$email_2_label = isset( $alchemists_data['alchemists__footer-primary-info-2-label'] ) ? $alchemists_data['alchemists__footer-primary-info-2-label'] : '';

							// check if Primary Email Address is an email address or link
							if ( filter_var( $email_1, FILTER_VALIDATE_EMAIL ) ) {
								$email_1_attr = 'mailto:' . $email_1;
							} elseif ( filter_var( $email_1, FILTER_VALIDATE_URL ) ) {
								$email_1_attr = esc_url( $email_1 );
							} else {
								$email_1_attr = 'tel:' . $email_1;
							}

							// check if Secondary Email Address is an email address or link
							if ( filter_var( $email_2, FILTER_VALIDATE_EMAIL ) ) {
								$email_2_attr = 'mailto:' . $email_2;
							} elseif ( filter_var( $email_2, FILTER_VALIDATE_URL ) ) {
								$email_2_attr = esc_url( $email_2 );
							} else {
								$email_2_attr = 'tel:' . $email_2;
							}
							?>

							<div class="info-block info-block--horizontal">

								<?php // Primary Email
								if ( isset( $alchemists_data['alchemists__footer-primary-info-1'] ) && $alchemists_data['alchemists__footer-primary-info-1'] == 1 ) : ?>
								<li class="info-block__item info-block__item--helmet">

									<?php if ( !empty( $icon_custom_primary ) ) : ?>
										<span class="df-icon-custom"><?php echo wp_kses_post( $icon_custom_primary ); ?></span>
									<?php else : ?>
										<svg role="img" class="df-icon df-icon--football-helmet">
											<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/icons-football.svg#football-helmet"/>
										</svg>
									<?php endif; ?>

									<h6 class="info-block__heading"><?php echo esc_html( $email_1_label ); ?></h6>
									<a class="info-block__link" href="<?php echo esc_url( $email_1_attr ); ?>"><?php echo esc_html( alchemists_remove_protocol( $email_1 ) ); ?></a>
								</li>
								<?php endif; ?>

								<?php // Secondary Email
								if ( isset( $alchemists_data['alchemists__footer-primary-info-2'] ) && $alchemists_data['alchemists__footer-primary-info-2'] == 1 ) : ?>
								<li class="info-block__item">

									<?php if ( !empty( $icon_custom_secondary ) ) : ?>
										<span class="df-icon-custom"><?php echo wp_kses_post( $icon_custom_secondary ); ?></span>
									<?php else : ?>
										<svg role="img" class="df-icon df-icon--football-ball">
											<use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/football/icons-football.svg#football-ball"/>
										</svg>
									<?php endif; ?>

									<h6 class="info-block__heading"><?php echo esc_html( $email_2_label ); ?></h6>
									<a class="info-block__link" href="<?php echo esc_url( $email_2_attr ); ?>"><?php echo esc_html( alchemists_remove_protocol( $email_2 ) ); ?></a>
								</li>
								<?php endif; ?>


								<?php if ( isset ( $alchemists_data['alchemists__footer-primary-social'] ) && $alchemists_data['alchemists__footer-primary-social'] == 1 ) :

								// Get all social media links
								$social_media = is_array( $alchemists_data['alchemists__footer-primary-social-links'] ) ? $alchemists_data['alchemists__footer-primary-social-links'] : array();
								?>
								<!-- Social Links -->
								<div class="info-block__item info-block__item--social">
									<ul class="social-links social-links--circle">
										<?php foreach ( array_filter( $social_media ) as $key => $value) {

											echo '<li class="social-links__item">';
												$title = $key;
												$prefix = 'fab';

												if ( strtolower( $key ) == 'email' ) {
													$key = 'envelope';
													$prefix = 'fa';
													$value = 'mailto:' . sanitize_email( $value );
												} elseif ( strtolower( $key ) == 'twitter' ) {
													$key = 'x-twitter';
												} elseif ( strtolower( $key ) == 'rss' ) {
													$prefix = 'fa';
												} elseif ( strtolower( $key ) == 'telegram' ) {
													$key = 'telegram';
												} elseif ( strtolower( $key ) == 'snapchat' ) {
													$key = 'snapchat-ghost';
												}

												echo '<a href="' . esc_url( $value ) . '" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="' . esc_attr( $title ) . '" target="_blank">';
													if ( strtolower( $key ) == 'faceit' ) {
														echo '<svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24"><title>FACEIT icon</title><path d="M24 2.7c0-.1-.1-.2-.1-.2-.1 0-.1 0-.2.1-2 3.1-4.1 6.2-6.1 9.4H.2c-.2 0-.3.3-.1.4 7.2 2.7 17.7 6.8 23.5 9.1.2.1.4-.1.4-.2V2.7z"/></svg>';
													} else {
														echo '<i class="' . $prefix . ' fa-' . strtolower( $key ) . '"></i>';
													}
												echo '</a>';

											echo '</li>';

										} ?>
									</ul>
								</div>
								<!-- Social Links / End -->
								<?php endif; ?>
							</div>
							<!-- Info Block / End -->

						</div>
					</div>
				</div>
				<!-- Footer Info / End -->
				<?php endif; ?>
			<?php endif; ?>

			<?php // Don't display if Footer Widgets and Footer Logo disabled
			if ( ( $logo_footer == '' && $footer_widgets == '' ) || ( ( $logo_footer != 0 ) || ( $footer_widgets != 0 ) ) ) { ?>
			<!-- Footer Widgets -->
			<div class="footer-widgets <?php echo esc_attr( $footer_widgets_classes ); ?>">
				<div class="footer-widgets__inner">
					<div class="container">

						<div class="row">

							<?php if ( $logo_footer != 0 && alchemists_sp_preset( 'basketball' )) { ?>
								<div class="<?php echo esc_attr( $footer_logo_width ); ?>">
									<div class="footer-col-inner">

										<!-- Footer Logo -->
										<div class="footer-logo">

											<?php if ( !empty( $logo_footer_standard ) ) { ?>
												<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
													<img src="<?php echo esc_url( $logo_footer_standard ); ?>" alt="<?php bloginfo('name'); ?>" <?php if ( !empty( $logo_footer_retina ) ) { ?> srcset="<?php echo esc_url( $logo_footer_retina ); ?> 2x" <?php } ?> class="footer-logo__img">
												</a>
											<?php } else { ?>
												<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
													<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/logo.png" srcset="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/logo@2x.png 2x" alt="<?php bloginfo('name'); ?>" class="footer-logo__img">
												</a>
											<?php } ?>

										</div>
										<!-- Footer Logo / End -->

									</div>
								</div>
							<?php } ?>


							<?php if ( $footer_widgets == 1 ) { ?>

								<?php if ( is_active_sidebar( 'alchemists-footer-widget-1' ) ) { ?>
								<div class="<?php echo esc_attr( $footer_widgets_col ); ?>">

									<?php if ( alchemists_sp_preset( 'soccer' ) ) : ?>
										<?php if ( $logo_footer != 0 ) : ?>
										<!-- Footer Logo -->
										<div class="footer-logo footer-logo--has-txt">

											<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
												<?php if ( !empty( $logo_footer_standard ) ) { ?>
													<img src="<?php echo esc_url( $logo_footer_standard ); ?>" alt="<?php bloginfo('name'); ?>" <?php if ( !empty( $logo_footer_retina ) ) { ?> srcset="<?php echo esc_url( $logo_footer_retina ); ?> 2x" <?php } ?> class="footer-logo__img">
												<?php } else { ?>
													<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/soccer/logo-footer.png" class="footer-logo__img" srcset="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/soccer/logo-footer@2x.png 2x" alt="<?php esc_attr( bloginfo('name') ); ?>">
												<?php } ?>
											</a>

											<div class="footer-logo__heading">
												<h5 class="footer-logo__txt">
													<?php
													if ( $footer_title ) {
														echo esc_html( $footer_title );
													} else {
														esc_html( bloginfo('name') );
													}
													?>
												</h5>
												<?php if ( get_bloginfo( 'description' ) || $footer_tagline ) : ?>
													<span class="footer-logo__tagline">
														<?php
														if ( $footer_tagline ) {
															echo esc_html( $footer_tagline );
														} else {
															bloginfo( 'description' );
														}
														?>
													</span>
												<?php endif; ?>
											</div>

										</div>
										<!-- Footer Logo / End -->
										<?php endif; ?>
									<?php endif; ?>

									<div class="footer-col-inner">
										<?php dynamic_sidebar('alchemists-footer-widget-1'); ?>
									</div>
								</div>
								<?php } ?>

								<?php if ( is_active_sidebar( 'alchemists-footer-widget-2' ) ) { ?>
								<div class="<?php echo esc_attr( $footer_widgets_col ); ?>">
									<div class="footer-col-inner">
										<?php dynamic_sidebar('alchemists-footer-widget-2'); ?>
									</div>
								</div>
								<?php } ?>

								<?php if ( is_active_sidebar( 'alchemists-footer-widget-3' ) ) { ?>
								<div class="<?php echo esc_attr( $footer_widgets_col ); ?>">
									<div class="footer-col-inner">
										<?php dynamic_sidebar('alchemists-footer-widget-3'); ?>
									</div>
								</div>
								<?php } ?>

								<?php // Display this widget area if Footer Widgets Layout set to 4 columns
								if ( $footer_widgets_layout == 2 && is_active_sidebar( 'alchemists-footer-widget-4' ) ) : ?>
									<div class="<?php echo esc_attr( $footer_widgets_col ); ?>">
										<div class="footer-col-inner">
											<?php dynamic_sidebar('alchemists-footer-widget-4'); ?>
										</div>
									</div>
								<?php endif; ?>

							<?php } ?>

						</div>
					</div>
				</div>

				<?php do_action( 'alc_site_footer_widgets_after' ); ?>

			</div>
			<!-- Footer Widgets / End -->
			<?php } ?>

			<!-- Footer Secondary -->
			<?php if ( $footer_secondary == '' || $footer_secondary == 1 ) : ?>

				<?php if ( 'default' == $footer_layout ) : ?>

					<div class="footer-secondary">
						<div class="container">
							<div class="footer-secondary__inner">
								<div class="row">
									<?php if ( ! empty( $footer_copyright ) ) : ?>
									<div class="col-lg-4">
										<div class="footer-copyright">
											<?php echo wp_kses_post( $footer_copyright ); ?>
										</div>
									</div>
									<?php endif; ?>
									<div class="col-lg-8">
										<?php // Footer navigation
										if ( has_nav_menu('footer_menu') ) {
											wp_nav_menu(
												array(
													'theme_location'  => 'footer_menu',
													'container'       => false,
													'menu_class'      => 'footer-nav footer-nav--right footer-nav--condensed footer-nav--sm',
													'echo'            => true,
													'fallback_cb'     => false,
													'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
													'depth'           => 0,
												)
											);
										} ?>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php elseif ( 'text' == $footer_layout ) : ?>

					<div class="footer-secondary">
						<div class="container">
							<div class="footer-secondary__inner">
								<div class="row">
									<?php if ( ! empty( $footer_copyright ) ) : ?>
										<div class="col-lg-12">
											<div class="footer-copyright">
												<?php echo wp_kses_post( $footer_copyright ); ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

				<?php elseif ( 'nav' == $footer_layout ) : ?>

					<div class="footer-secondary footer-secondary--has-decor">
						<div class="container">
							<div class="footer-secondary__inner">
								<div class="row">
									<div class="col-lg-10 offset-lg-1">
										<?php // Footer navigation
										if ( has_nav_menu('footer_menu') ) {
											wp_nav_menu(
												array(
													'theme_location'  => 'footer_menu',
													'container'       => false,
													'menu_class'      => 'footer-nav',
													'echo'            => true,
													'fallback_cb'     => false,
													'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
													'depth'           => 0,
												)
											);
										} ?>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php elseif ( 'social' == $footer_layout ) : ?>

					<!-- Footer Social Links -->
					<div class="footer-social">
						<div class="container">

							<?php
							// Get all social media links
							$social_links_secondary = is_array( $alchemists_data['alchemists__footer-secondary-social-links'] ) ? $alchemists_data['alchemists__footer-secondary-social-links'] : array();
							?>

							<ul class="footer-social__list list-unstyled">
								<?php
								foreach ( array_filter( $social_links_secondary ) as $key => $value ) {

									echo '<li class="footer-social__item">';
										$title = $key;

										if ( strtolower( $key ) == 'telegram' ) {
											$key = 'telegram-plane';
											$title = 'Telegram';
										} elseif ( strtolower( $key ) == 'twitter' ) {
											$key = 'x-twitter';
										} elseif ( strtolower( $key ) == 'snapchat' ) {
											$key = 'snapchat-ghost';
											$title = 'Snapchat';
										}

										$value = rtrim( $value, '/' );;
										$last_word = substr( strrchr( $value, '/' ), 1 );

										echo '<a href="' . esc_url( $value ) . '" class="footer-social__link" title="' . esc_attr( $title ) . '" target="_blank">';
											echo '<span class="footer-social__icon">';
												if ( strtolower( $key ) == 'faceit' ) {
													echo '<svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24"><title>FACEIT icon</title><path d="M24 2.7c0-.1-.1-.2-.1-.2-.1 0-.1 0-.2.1-2 3.1-4.1 6.2-6.1 9.4H.2c-.2 0-.3.3-.1.4 7.2 2.7 17.7 6.8 23.5 9.1.2.1.4-.1.4-.2V2.7z"/></svg>';
												} else {
													echo '<i class="fab fa-' . strtolower( $key ) . '"></i>';
												}
											echo '</span>';
											echo '<div class="footer-social__txt">';
												echo '<span class="footer-social__name">' . $title . '</span>';
												echo '<span class="footer-social__user">' . $last_word . '</span>';
											echo '</div>';
										echo '</a>';

									echo '</li>';
								}
								?>
							</ul>

						</div>
					</div>
					<!-- Footer Social Links / End -->

				<?php elseif ( 'text_social' == $footer_layout ) : ?>

					<!-- Footer Social Links -->
					<div class="footer-social">
						<div class="container">

							<?php
							// Get all social media links
							$social_links_secondary = is_array( $alchemists_data['alchemists__footer-secondary-social-links'] ) ? $alchemists_data['alchemists__footer-secondary-social-links'] : array();
							?>

							<ul class="footer-social__list list-unstyled">
								<?php
								foreach ( array_filter( $social_links_secondary ) as $key => $value ) {

									echo '<li class="footer-social__item">';
										$title = $key;

										if ( strtolower( $key ) == 'telegram' ) {
											$key = 'telegram-plane';
											$title = 'Telegram';
										} elseif ( strtolower( $key ) == 'twitter' ) {
											$key = 'x-twitter';
										} elseif ( strtolower( $key ) == 'snapchat' ) {
											$key = 'snapchat-ghost';
											$title = 'Snapchat';
										}

										$value = rtrim( $value, '/' );;
										$last_word = substr( strrchr( $value, '/' ), 1 );

										echo '<a href="' . esc_url( $value ) . '" class="footer-social__link" title="' . esc_attr( $title ) . '" target="_blank">';
											echo '<span class="footer-social__icon">';
												if ( strtolower( $key ) == 'faceit' ) {
													echo '<svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24"><title>FACEIT icon</title><path d="M24 2.7c0-.1-.1-.2-.1-.2-.1 0-.1 0-.2.1-2 3.1-4.1 6.2-6.1 9.4H.2c-.2 0-.3.3-.1.4 7.2 2.7 17.7 6.8 23.5 9.1.2.1.4-.1.4-.2V2.7z"/></svg>';
												} else {
													echo '<i class="fab fa-' . strtolower( $key ) . '"></i>';
												}
											echo '</span>';
											echo '<div class="footer-social__txt">';
												echo '<span class="footer-social__name">' . $title . '</span>';
												echo '<span class="footer-social__user">' . $last_word . '</span>';
											echo '</div>';
										echo '</a>';

									echo '</li>';
								}
								?>
							</ul>

						</div>
					</div>
					<!-- Footer Social Links / End -->

					<?php if ( ! empty( $footer_copyright ) ) : ?>
						<!-- Footer Copyright -->
						<div class="footer-secondary">
							<div class="container">
								<div class="d-flex justify-content-center pb-4">
									<div class="footer-copyright">
										<?php echo wp_kses_post( $footer_copyright ); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<!-- Footer Copyright / End -->

				<?php endif; ?>

			<!-- Footer Secondary / End -->
			<?php endif; ?>

		</footer>
		<!-- Footer / End -->

	</div><!-- .site-wrapper -->
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
