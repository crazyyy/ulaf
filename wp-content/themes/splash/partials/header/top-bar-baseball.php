<?php
$logo_main = get_theme_mod('logo', '');
$logo_sticky = get_theme_mod('sticky_logo', '');

$logo_top_margin = get_theme_mod('logo_top_margin', 0);
?>
<?php
if(get_theme_mod("header_type", 'header_4') == 'header_4'):
?>
<div class="stm-menu-toggle-baseball">
    <span></span>
    <span></span>
    <span></span>
</div>
<?php endif; ?>
<div class="container">
    <div class="row stm-ipad-block">
        <div class="col-md-5 col-sm-5">

            <div class="stm-top-ticker-holder">
                <?php get_template_part('partials/header/top-bar-partials/top-bar-ticker'); ?>
            </div>

        </div>
        <div class="col-md-2 col-sm-2">
			<?php
			if(get_theme_mod("header_type", 'header_4') == 'header_4'):
			?>
            <div class="logo-main" style="margin-top: <?php echo intval($logo_top_margin); ?>px;">
                <?php if(empty($logo_main)): ?>
                    <a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_html_e('Home', 'splash'); ?>">
                        <h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
                    </a>
                <?php else: ?>
                    <a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
                        <img
							class="stm-main-logo"
                            src="<?php echo esc_url( $logo_main ); ?>"
                            style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
                            title="<?php esc_html_e('Home', 'splash'); ?>"
                            alt="<?php esc_html_e('Logo', 'splash'); ?>"
                        />
						<img
							class="stm-sticky-logo"
                            src="<?php echo esc_url( $logo_sticky ); ?>"
                            style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
                            title="<?php esc_html_e('Home', 'splash'); ?>"
                            alt="<?php esc_html_e('Logo', 'splash'); ?>"
                        />

                    </a>
                <?php endif; ?>
            </div>
			<?php endif; ?>
        </div>
        <div class="col-md-5 col-sm-5">

            <div class="clearfix">
                <div class="stm-top-bar_right">
                    <div class="clearfix">
                        <div class="stm-top-switcher-holder">
                            <?php get_template_part('partials/header/top-bar-partials/top-bar-switcher'); ?>
                        </div>

                        <div class="stm-top-cart-holder">
                            <?php get_template_part('partials/header/top-bar-partials/top-bar-cart'); ?>
                        </div>

                        <div class="stm-top-profile-holder">
                            <?php get_template_part('partials/header/top-bar-partials/top-bar-profile'); ?>
                        </div>
                    </div>
                </div>

                <div class="stm-top-socials-holder">
                    <?php get_template_part('partials/header/top-bar-partials/top-bar-socials'); ?>
                </div>

            </div>

        </div>
    </div>
	<div class="row stm-ipad-none">
		<div class="col-12">
			<div class="logo-main" >
				<?php if(empty($logo_main)): ?>
					<a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_html_e('Home', 'splash'); ?>">
						<h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
					</a>
				<?php else: ?>
					<a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
						<img
							class="stm-main-logo"
							src="<?php echo esc_url( $logo_main ); ?>"
							style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
							title="<?php esc_html_e('Home', 'splash'); ?>"
							alt="<?php esc_html_e('Logo', 'splash'); ?>"
						/>
						<img
							class="stm-sticky-logo"
							src="<?php echo esc_url( $logo_sticky ); ?>"
							style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
							title="<?php esc_html_e('Home', 'splash'); ?>"
							alt="<?php esc_html_e('Logo', 'splash'); ?>"
						/>

					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>