<?php
/*TOP BAR*/
$top_bar_enable = get_theme_mod('top_bar_enable', true);
if($top_bar_enable) {
	get_template_part('partials/header/top-bar');
}

/*Header Settings*/
$logo_main = get_theme_mod('logo', '');

$menu_top_margin = get_theme_mod('menu_top_margin', 0);
$logo_top_margin = get_theme_mod('logo_top_margin', 22);

$header_enable_search = get_theme_mod('header_enable_search', true);

$search_enabled = 'stm-search-enabled';
if(!$header_enable_search) {
	$search_enabled = '';
}

$header_background = get_theme_mod('header_background');

$transparent_header = 'stm-non-transparent-header';
$transparent_header_on = get_post_meta(get_the_id(), 'transparent_header', true);

if(!empty($transparent_header_on)) {
	$transparent_header = 'stm-transparent-header';
}

$header_position = 'stm-header-static';
$header_position_mode = get_theme_mod('header_position', false);

if($header_position_mode) {
	$header_position = 'stm-header-fixed-mode';
}
?>

<div class="stm-header <?php echo esc_attr($transparent_header); ?> <?php echo esc_attr($header_position); ?> stm-header-five">
	<div class="stm-header-inner">
		<?php if(!empty($header_background)): ?>
			<div class="stm-header-background" style="background-image: url('<?php echo esc_url($header_background); ?>')"></div>
		<?php endif; ?>
		<div class="stm-header-container">
			<!--Logo -->
			<div class="logo-main" style="margin-top: <?php echo intval($logo_top_margin); ?>px;">
				<?php if(empty($logo_main)): ?>
					<a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_html_e('Home', 'splash'); ?>">
						<h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
					</a>
				<?php else: ?>
					<a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
						<img
							src="<?php echo esc_url( $logo_main ); ?>"
							style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
							title="<?php esc_html_e('Home', 'splash'); ?>"
							alt="<?php esc_html_e('Logo', 'splash'); ?>"
						/>
					</a>
				<?php endif; ?>
			</div>
			
			<div class="stm-main-menu">
				<div class="stm-main-menu-unit <?php echo esc_attr($search_enabled); ?>" style="margin-top: <?php echo intval($menu_top_margin); ?>px;">
					<ul class="header-menu stm-list-duty heading-font clearfix">
						<?php
						wp_nav_menu( array(
								'menu'              => 'primary',
								'theme_location'    => 'primary',
								'depth'             => 5,
								'container'         => false,
								'menu_class'        => 'header-menu clearfix',
								'items_wrap'        => '%3$s',
								'link_before'            => '<span>',
								'link_after'             => '</span>',
								'fallback_cb' => false,
								'stm_megamenu' => true
							)
						);
						?>
                        <?php if(splash_is_layout('esport')): ?>
                        <li class="cart-list-item">
                            <?php get_template_part('partials/header/top-bar-partials/top-bar-cart'); ?>
                        </li>
                        <?php endif; ?>
					</ul>
					<?php
						if($header_enable_search) {
							get_template_part('partials/header/search');
						}
					?>
				</div>
			</div>

			<div class="stm-top-cart-holder">
				<?php get_template_part('partials/header/top-bar-partials/top-bar-cart'); ?>
			</div>

			<div class="stm-top-socials-holder">
				<?php
				$top_bar_text_color = get_theme_mod('top_bar_text_color');
				$style_opt = array();
				if(!empty($top_bar_text_color)) {
					$style_opt['color'] = $top_bar_text_color;
				}
				$style = splash_generate_inline_style($style_opt);

				$stm_socials = splash_socials();

				if(!empty($stm_socials)): ?>
					<ul class="top-bar-socials stm-list-duty">
						<?php foreach($stm_socials as $key => $value): ?>
							<li>
								<a href="<?php echo esc_attr($value); ?>" target="_blank" <?php echo sanitize_text_field($style); ?>>
									<i class="fa fa-<?php echo esc_attr($key); ?>"></i>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>

				<?php endif; ?>
			</div>
		</div>
	</div>
	<!--MOBILE HEADER-->
	<div class="stm-header-mobile clearfix">
		<div class="logo-main" style="margin-top: <?php echo intval($logo_top_margin); ?>px;">
			<?php if(empty($logo_main)): ?>
				<a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_html_e('Home', 'splash'); ?>">
					<h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
				</a>
			<?php else: ?>
				<a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
					<img
						src="<?php echo esc_url( $logo_main ); ?>"
						style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
						title="<?php esc_html_e('Home', 'splash'); ?>"
						alt="<?php esc_html_e('Logo', 'splash'); ?>"
						/>
				</a>
			<?php endif; ?>
		</div>
		<div class="stm-mobile-right">
			<div class="clearfix">
				<div class="stm-menu-toggle">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>
		</div>

		<div class="stm-mobile-menu-unit">
			<div class="inner">
				<div class="stm-top clearfix">
					<div class="stm-switcher pull-left">
						<?php get_template_part('partials/header/top-bar-partials/top-bar-switcher-mobile'); ?>
					</div>
					<div class="stm-top-right">
						<div class="clearfix">
							<div class="stm-top-search">
								<?php
									if($header_enable_search) {
										get_template_part('partials/header/top-bar-partials/search');
									}
								?>
							</div>
							<div class="stm-top-socials">
								<?php get_template_part('partials/header/top-bar-partials/top-bar-cart'); ?>
								<?php get_template_part('partials/header/top-bar-partials/top-bar-socials'); ?>
							</div>
						</div>
					</div>
				</div>
				<ul class="stm-mobile-menu-list heading-font">
					<?php
					wp_nav_menu( array(
							'menu'              => 'primary',
							'theme_location'    => 'primary',
							'depth'             => 5,
							'container'         => false,
							'menu_class'        => 'header-menu clearfix',
							'items_wrap'        => '%3$s',
							'link_before'            => '<span>',
							'link_after'             => '</span>',
							'fallback_cb' => false
						)
					);
					?>
				</ul>
			</div>
		</div>
	</div>

</div>