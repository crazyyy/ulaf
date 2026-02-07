<?php
$top_bar_enable_switcher = get_theme_mod('top_bar_enable_switcher', true);

if(!$top_bar_enable_switcher) {
	return false;
}

if(function_exists('icl_get_languages')):
	$langs = icl_get_languages('skip_missing=1&orderby=id&order=asc');
endif; ?>

<?php if(!empty($langs)): ?>
	<?php
	if(count($langs) > 1){
		$langs_exist = 'dropdown_toggle';
	} else {
		$langs_exist = 'no_other_langs';
	}
	?>
	<div class="language-switcher-unit <?php echo (splash_is_layout("bb")) ? "heading-font" : "normal_font"; ?>">
		<div class="stm-current-language <?php echo esc_attr($langs_exist); ?>" <?php if(count($langs) > 1){ ?> id="lang_dropdown" data-toggle="dropdown" <?php } ?>>
			<span class="stm-language-flag"><img src="<?php echo esc_url($langs[ICL_LANGUAGE_CODE]['country_flag_url']); ?>" alt="<?php echo esc_attr(ICL_LANGUAGE_CODE); ?>" /></span>
			<span class="stm-language-code"><?php echo esc_attr(ICL_LANGUAGE_NAME); ?></span>
			<?php if(count($langs) > 1): ?>
				<div class="stm-switcher-open"></div>
			<?php endif; ?>
		</div>
		<?php if(count($langs) > 1): ?>
			<ul class="dropdown-menu lang-dropdown-menu stm-list-duty" role="menu" aria-labelledby="lang_dropdown">
				<?php foreach($langs as $lang): ?>
					<?php if(!$lang['active']): ?>
						<li role="presentation">
							<a role="menuitem" tabindex="-1" href="<?php echo esc_url($lang['url']); ?>">
								<span class="stm-language-flag"><img src="<?php echo esc_url($lang['country_flag_url']); ?>" alt="<?php echo esc_attr($lang['translated_name']); ?>" /></span>
								<span class="stm-language-code"><?php echo esc_attr($lang['translated_name']); ?></span>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
<?php endif; ?>
