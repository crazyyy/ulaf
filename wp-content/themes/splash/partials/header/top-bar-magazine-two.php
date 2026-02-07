<?php
$phone = get_theme_mod('top_bar_phone', '');
$email = get_theme_mod('top_bar_email', '');
?>
<div class="container">
        <div class="row">
            <div class="col-md-5">
                <div class="stm-top-switcher-holder">
                    <?php get_template_part('partials/header/top-bar-partials/top-bar-switcher'); ?>
                </div>
                <?php if(!empty($phone)): ?>
                    <div class="stm-top-phone-holder">
                        <i class="icon-mg-icon-whistle"></i>
                        <?php echo splash_sanitize_text_field($phone); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-2">
                <?php if(!empty($email)): ?>
                    <div class="stm-top-email-holder">
                        <i class="icon-football-ball"></i>
                        <?php echo esc_html($email); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <?php if(get_theme_mod('top_bar_enable_cart', true)) : ?>
                <div class="stm-top-cart-holder">
                    <?php get_template_part('partials/header/top-bar-partials/top-bar-cart'); ?>
                </div>
                <?php endif; ?>
                <div class="stm-top-profile-holder">
                    <?php get_template_part('partials/header/top-bar-partials/top-bar-profile-magazine-two'); ?>
                </div>
            </div>
        </div>
</div>