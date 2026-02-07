<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($image)) {
	$post_thumbnail = wpb_getImageBySize( array(
		'attach_id'  => $image,
		'thumb_size' => $image_size
	) );
}

?>

<div class="stm-contact-info">
	<?php if(!empty($post_thumbnail['thumbnail'])): ?>
		<div class="image">
			<?php echo wp_kses_post($post_thumbnail['thumbnail']); ?>
		</div>
	<?php endif; ?>

	<?php if(!empty($title)): ?>
		<div class="title h5"><?php echo esc_attr($title); ?></div>
	<?php endif; ?>

	<?php if(!empty($subtitle) && !splash_is_af()): ?>
		<div class="subtitle h6"><?php echo esc_attr($subtitle); ?></div>
	<?php endif; ?>

	<div class="stm-contacts">
		<?php if(splash_is_af()): ?>
			<?php if(!empty($subtitle)): ?>
				<div class="stm-single-contact stm-location">
					<i class="icon-ico_pin"></i>
					<div class="contact-value <?php echo (splash_is_layout("af")) ? "normal_font" : "h4"; ?>"><?php echo esc_attr($subtitle); ?></div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if(!empty($address)): ?>
			<div class="stm-single-contact stm-phone">
				<i class="icon-ico_pin"></i>
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<div class="contact-label">' . esc_html__('Address', 'splash') . '</div>' : ''; ?>
				<div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>"><?php echo esc_attr($address); ?></div>
			</div>
		<?php endif; ?>

		<?php if(!empty($phone)): ?>
			<div class="stm-single-contact stm-phone">
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<i class="fa fa-phone"></i>' : '<i class="icon-ico_phone"></i>'; ?>
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<div class="contact-label">' . esc_html__('Phone', 'splash') . '</div>' : ''; ?>
				<div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>"><?php echo esc_attr($phone); ?></div>
			</div>
		<?php endif; ?>

		<?php if(!empty($fax)): ?>
			<div class="stm-single-contact stm-fax">
				<?php echo (!splash_is_af()) ? '<i class="fa fa-fax"></i>' : '<i class="icon-ico_print"></i>'; ?>
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<div class="contact-label">' .esc_html__('Fax', 'splash') . '</div>' : ''; ?>
				<div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>"><?php echo esc_attr($fax); ?></div>
			</div>
		<?php endif; ?>

		<?php if(!empty($email)): ?>
			<div class="stm-single-contact stm-email">
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<i class="fa fa-envelope"></i>' : '<i class="icon-ico_message"></i>'; ?>
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<div class="contact-label">' .esc_html__('Email', 'splash') . '</div>' : ''; ?>
				<div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>">
					<a href="mailto:<?php echo esc_attr($email) ?>">
						<?php echo esc_attr($email); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<?php if(!empty($url)): ?>
			<div class="stm-single-contact stm-url">
				<i class="fa fa-link"></i>
				<div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>">
					<a href="<?php echo esc_url($url) ?>" target="_blank">
						<?php echo esc_attr($url); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<?php if(!empty($schedule)): ?>
		    <div class="stm-single-contact stm-schedule">
                <i class="icon-ico_clock"></i>
				<?php echo (!splash_is_af() && !splash_is_layout("baseball")) ? '<div class="contact-label">' .esc_html__('Schedule', 'splash') . '</div>' : ''; ?>
                <div class="contact-value <?php echo (splash_is_layout("af") || splash_is_layout("baseball")) ? "normal_font" : "h4"; ?>"><?php echo esc_attr($schedule); ?></div>
            </div>
		<?php endif; ?>

	</div>
	<?php
	$stm_socials = splash_socials('footer_socials');

	if(splash_is_layout("baseball") && !empty($stm_socials)){
		?>
        <ul class="footer-bottom-socials stm-list-duty">
			<?php foreach($stm_socials as $key => $value): ?>
                <li class="stm-social-<?php echo esc_attr($key); ?>">
                    <a href="<?php echo esc_attr($value); ?>" target="_blank">
                        <i class="fa fa-<?php echo esc_attr($key); ?>"></i>
                    </a>
                </li>
			<?php endforeach; ?>
        </ul>
		<?php
	}
	?>
</div>