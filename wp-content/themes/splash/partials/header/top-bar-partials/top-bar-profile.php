<?php
$isActive = get_theme_mod("top_bar_enable_profile", false);
if($isActive == 'enable'):
?>

<div class="stm-profile-wrapp">
	<div class="stm-profile-img <?php if(get_current_user_id() == 0) echo 'icon-mg-icon-ball'; ?>">
		<?php if(get_current_user_id() != 0) echo get_avatar(get_current_user_id()); ?>
	</div>
	<a class="normal_font" href='<?php echo site_url(); ?>/my-account'><?php if(get_current_user_id() == 0) : esc_html_e('Log In', 'splash');?></a><span class="vertical-divider"></span><a class="normal_font" href='<?php echo site_url(); ?>/my-account'><?php esc_html_e('Sign Up', 'splash'); else : esc_html_e('Profile', 'splash'); endif;?></a>
</div>
<?php endif; ?>