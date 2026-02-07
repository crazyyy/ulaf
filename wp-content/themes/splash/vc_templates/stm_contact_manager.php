<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 15.09.2017
 * Time: 14:06
 */

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if(!empty($avatar)) {
	$post_thumbnail = wpb_getImageBySize( array(
		'attach_id'  => $avatar,
		'thumb_size' => 'full'
	) );
}

?>
<div class="stm-contact-manager-block">
	<h4><?php echo esc_html($title); ?></h4>
	<div class="subtitle"><?php echo esc_html($subtitle); ?></div>
	<div class="stm-cm-info">
		<?php if(!empty($post_thumbnail['thumbnail'])): ?>
			<div class="image">
				<?php echo wp_kses_post($post_thumbnail['thumbnail']); ?>
			</div>
		<?php endif; ?>
		<div class="stm-cm-data">
			<div class="stm-cm-name heading-font"><?php echo esc_html($name_lname); ?></div>
			<div class="stm-cm-position"><?php echo esc_html($position); ?></div>
			<?php if(!empty($phone_one) || !empty($phone_two) || !empty($phone_three)) : ?>
				<ul>

					<?php if(!empty($phone_one)): ?>
						<li><?php echo esc_html($phone_one); ?></li>
					<?php endif; ?>

					<?php if(!empty($phone_two)): ?>
						<li><?php echo esc_html($phone_two); ?></li>
					<?php endif; ?>

					<?php if(!empty($phone_three)): ?>
						<li><?php echo esc_html($phone_three); ?></li>
					<?php endif; ?>

				</ul>
			<?php endif; ?>
			<?php if(!empty($email)):?>
				<div class="stm-cm-email normal_font">
                    <?php if(splash_is_layout('magazine_one')) : ?>
                        <?php echo esc_html__('Email: ', 'splash'); ?>
                        <a href="mailto:<?php echo esc_html($email); ?>">
                    <?php endif; ?>
					    <?php echo esc_html($email); ?>
                    <?php if(splash_is_layout('magazine_one')) : ?>
                        </a>
                    <?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

