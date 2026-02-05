<?php
/**
 * Template part for displaying page header
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 */
 
	$site_header_style_attr = ""; 
	$site_header_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--responsive-menu-primary-color', 'css_var2' => '--responsive-menu-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'responsive_menu_color_scheme' ) ] ); 
	
	$site_branding_style_attr = ""; 
	$site_branding_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'site_branding_bar_color_scheme' ) ] );  
	$site_branding_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--sticky-primary-color', 'css_var2' => '--sticky-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'sticky_site_branding_bar_color_scheme' ) ] ); 
	
	$site_navigation_style_attr = ""; 
	$site_navigation_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'menu_bar_color_scheme' ) ] );  
	$site_navigation_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--sticky-primary-color', 'css_var2' => '--sticky-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'sticky_menu_bar_color_scheme' ) ] );  
	$site_navigation_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--hover-primary-color', 'css_var2' => '--hover-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'menu_hover_color_scheme' ) ] );   
	$site_navigation_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--dropdown-primary-color', 'css_var2' => '--dropdown-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'menu_dropdown_color_scheme' ) ] ); 
	$site_navigation_style_attr .= boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--dropdown-hover-primary-color', 'css_var2' => '--dropdown-hover-secondary-color', 'color_scheme_id' => boldthemes_get_option( 'menu_dropdown_hover_color_scheme' ) ] ); 
	
	if ( boldthemes_get_option( 'display_branding_text' ) || is_customize_preview() ) {
		$title = wp_kses_post( get_bloginfo( 'name' ) );
		$home_url = esc_url( home_url( '/' ) );
		$description = wp_kses_post( get_bloginfo( 'description', 'display' ) );
		$branding_text_html_tag = boldthemes_get_option( 'branding_text_html_tag' );		
	}
	
?>

	<header id="masthead" class="site-header" style="<?php echo esc_attr( $site_header_style_attr ); ?>">
		<?php if ( is_active_sidebar( 'header_left_widgets' ) || is_active_sidebar( 'header_right_widgets' ) ) : ?>
		<div class="site-header-top-bar" style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'top_bar_color_scheme' ) ] ) ); ?>">
			<div class="site-header-top-bar-inner">
				<?php if ( is_active_sidebar( 'header_left_widgets' ) ) : ?>
				<div class="site-header-top-bar-inner-left">
					<?php dynamic_sidebar( 'header_left_widgets' ); ?>
				</div><!-- /site-header-top-bar-inner-left -->
				<?php endif; // header_left_widgets ?>
				<?php if ( is_active_sidebar( 'header_right_widgets' ) ) : ?>
				<div class="site-header-top-bar-inner-right">
					<?php dynamic_sidebar( 'header_right_widgets' ); ?>
				</div><!-- /site-header-top-bar-inner-right -->
				<?php endif; // header_right_widgets ?>
			</div><!-- .site-header-top-bar-inner -->
		</div><!-- .site-header-top-bar -->
		<?php endif; // header_left_widgets || header_right_widgets ?>
		<div class="site-branding" style="<?php echo esc_attr( $site_branding_style_attr ); ?>">
			<div class="site-branding-inner">
				<div class="site-branding-logo-text">
					<div class="site-branding-logo">
						<?php
						echo ( boldthemes_logo( 'logo' ) );
						echo ( boldthemes_logo( 'sticky_logo' ) );
						echo ( boldthemes_logo( 'responsive_menu_logo' ) );
						?>
					</div><!-- .site-branding-logo -->
					<?php if ( boldthemes_get_option( 'display_branding_text' ) || is_customize_preview() ) { ?>
					<div class="site-branding-text">	
					<?php			
						printf ('<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>', $branding_text_html_tag, $home_url, $title );
						printf ('<p class="site-description">%1$s</p>', $description ); 
					?>
					</div><!-- .site-branding-text -->
					<?php } ?>
				</div><!-- .site-branding-logo-text -->
				<?php if ( is_active_sidebar( 'header_logo_widgets' ) ) { ?>
				<div class="site-branding-widgets">
					<?php dynamic_sidebar( 'header_logo_widgets' ); ?>
				</div><!-- .site-branding-widgets -->
				<?php } ?>
				<?php if ( substr( boldthemes_get_option( 'primary_menu_position' ), 0, 4 ) == 'logo'  ) : ?>
				<div id="site-navigation" class="main-navigation main-navigation-logo-area" style="<?php echo esc_attr( $site_navigation_style_attr ); ?>">
					<!--button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'campo' ); ?></button-->
					<?php
					boldthemes_nav_menu();
					?>
					<?php if ( is_active_sidebar( 'header_menu_widgets' ) ) { ?>
					<div class="site-navigation-widgets">
						<?php dynamic_sidebar( 'header_menu_widgets' ); ?>
					</div><!-- .site-navigation-widgets -->
					<?php } ?>
				</div><!-- .main-navigation -->
				<?php endif; ?>
			</div><!-- .site-branding-inner -->
		</div><!-- .site-branding -->
		<?php if ( substr( boldthemes_get_option( 'primary_menu_position' ), 0, 4 ) != 'logo'  ) : ?>
		<div id="site-navigation" class="main-navigation" style="<?php echo esc_attr( $site_navigation_style_attr ); ?>">
			<div class="main-navigation-inner">
				<!--button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'campo' ); ?></button-->
				<?php
				boldthemes_nav_menu();
				?>
				<?php if ( is_active_sidebar( 'header_menu_widgets' ) ) { ?>
				<div class="site-navigation-widgets">
					<?php dynamic_sidebar( 'header_menu_widgets' ); ?>
				</div><!-- .site-navigation-widgets -->
				<?php } ?>
			</div>
		</div><!-- .main-navigation -->
		<?php endif; ?>
		<?php if ( is_active_sidebar( 'menu_responsive_widgets' ) ) : ?>
		<div class="site-menu-responsive-widgets">
			<div class="site-menu-responsive-widgets-inner">
				<?php dynamic_sidebar( 'menu_responsive_widgets' ); ?>
			</div><!-- .site-menu-responsive-widgets-inner -->
		</div><!-- .site-menu-responsive-widgets -->
		<?php endif; ?>
	</header><!-- .site-header -->
	<div id="masthead-responsive" class="site-header-responsive" style="<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-color', 'css_var2' => '--secondary-color', 'color_scheme_id' => boldthemes_get_option( 'responsive_header_color_scheme' ) ] ) ); ?>;<?php echo esc_attr( boldthemes_get_style_from_color_scheme( [ 'css_var1' => '--primary-sticky-color', 'css_var2' => '--secondary-sticky-color', 'color_scheme_id' => boldthemes_get_option( 'responsive_sticky_header_color_scheme' ) ] ) ); ?>">
		<div class="site-header-responsive-inner">
			<div class="site-header-responsive-trigger">
				<div class="site-header-responsive-trigger-icon">
					<div class="trigger-line-1"></div>
					<div class="trigger-line-2"></div>
					<div class="trigger-line-3"></div>
					<div class="trigger-line-4"></div>
				</div>
			</div>
			<div class="site-header-responsive-logo-text">
				<div class="site-header-responsive-logo">
					<?php	echo ( boldthemes_logo( 'responsive_logo' ) ); ?>
					<?php	echo ( boldthemes_logo( 'responsive_sticky_logo' ) ); ?>
				</div><!-- .site-header-responsive-logo -->
				<?php if ( boldthemes_get_option( 'display_branding_text' ) || is_customize_preview() ) { ?>
				<div class="site-header-responsive-branding-text">	
				<?php	
					printf ('<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>', $branding_text_html_tag, $home_url, $title );
					printf ('<p class="site-description">%1$s</p>', $description ); 
				?>
				</div><!-- .site-branding-text -->
				<?php } ?>
			</div><!-- .site-header-responsive-logo -->
			<?php if ( is_active_sidebar( 'header_logo_responsive_widgets' ) ) { ?>
			<div class="site-header-responsive-widgets">
				<div class="site-header-responsive-widgets-inner">
					<?php dynamic_sidebar( 'header_logo_responsive_widgets' ); ?>
				</div><!-- .site-branding-responsive-widgets-inner -->
			</div><!-- .site-branding-responsive-widgets -->
			<?php } ?>
		</div><!-- .site-header-responsive-inner -->
	</div><!-- .site-header-responsive -->

