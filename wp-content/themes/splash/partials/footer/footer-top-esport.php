<?php
$logo_main = get_theme_mod('logo', '');
$stm_socials = splash_socials('footer_socials');
?>
<div class="top-footer-wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-xs-12">
                <a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if(!empty($logo_main)): ?>
                        <img
                                src="<?php echo esc_url( $logo_main ); ?>"
                                style="width: <?php echo get_theme_mod( 'logo_width', '157' ); ?>px;"
                                title="<?php esc_html_e('Home', 'splash'); ?>"
                                alt="<?php esc_html_e('Logo', 'splash'); ?>"
                        />
                    <?php else: ?>
                        <h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
                    <?php endif; ?>
                </a>
            </div>
            <div class="col-md-6 col-xs-12">
                <?php if(!empty($stm_socials)): ?>
                    <div class="footer-socials-unit">
                        <ul class="footer-bottom-socials stm-list-duty">
                            <?php foreach($stm_socials as $key => $value): ?>
                                <li class="stm-social-<?php echo esc_attr($key); ?>">
                                    <a href="<?php echo esc_url($value); ?>" target="_blank">
                                        <i class="fa fa-<?php echo esc_attr($key); ?>"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
