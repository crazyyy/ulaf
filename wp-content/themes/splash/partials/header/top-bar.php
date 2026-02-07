<?php
	$top_bar_bg_color = get_theme_mod('top_bar_bg_color');
	$top_bar_text_color = get_theme_mod('top_bar_text_color');

	$style_opt = array();

	if(!empty($top_bar_bg_color)) {
		$style_opt['background-color'] = $top_bar_bg_color;
	}

	if(!empty($top_bar_text_color)) {
		$style_opt['color'] = $top_bar_text_color;
	}

	$style = splash_generate_inline_style($style_opt);

?>

<div id="stm-top-bar" <?php echo sanitize_text_field($style); ?>>
    <?php if(splash_is_layout('bb')): ?>
        <?php get_template_part('partials/header/top-bar-basketball'); ?>
    <?php elseif(splash_is_layout("af")): ?>
        <?php get_template_part('partials/header/top-bar-americanfootball'); ?>
    <?php elseif(splash_is_layout('sccr')) : ?>
        <?php get_template_part('partials/header/top-bar-soccer'); ?>
    <?php elseif(splash_is_layout("baseball")): ?>
        <?php get_template_part('partials/header/top-bar-baseball'); ?>
    <?php elseif(splash_is_layout("magazine_one")): ?>
        <?php get_template_part('partials/header/top-bar-magazine-one'); ?>
	<?php elseif(splash_is_layout("magazine_two")): ?>
		<?php get_template_part('partials/header/top-bar-magazine-two'); ?>
    <?php elseif(splash_is_layout("soccer_two")): ?>
	    <?php get_template_part('partials/header/top-bar-soccer-two'); ?>
    <?php elseif(splash_is_layout("soccer_news")): ?>
        <?php get_template_part('partials/header/top-bar-soccer_news'); ?>
    <?php elseif(splash_is_layout("basketball_two")): ?>
        <?php get_template_part('partials/header/top-bar-basketball-two'); ?>
    <?php elseif(splash_is_layout("hockey")): ?>
        <?php get_template_part('partials/header/top-bar-hockey'); ?>
    <?php else: ?>
        <?php get_template_part('partials/header/top-bar-basketball'); ?>
	<?php endif;?>
</div>