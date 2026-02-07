<?php
$top_bar_enable_socials = get_theme_mod('top_bar_enable_socials', true);

if(!$top_bar_enable_socials) {
	return false;
}

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
