<?php
if(!is_page_template('coming-soon.php')):
	$footer_image = get_theme_mod('footer_image');
	$footer_style = get_theme_mod('footer_style');

    $stm_socials = splash_socials('footer_socials');

	$footer_image_page = get_post_meta(get_the_ID(), 'footer_image', true);
	if(!empty($footer_image_page)) {
		$footer_image_page = wp_get_attachment_image_src($footer_image_page, 'full');
		if(!empty($footer_image_page[0])) {
			$footer_image = $footer_image_page[0];
		}
	}

	$footerHideImg = get_post_meta(get_the_ID(), 'page_footer_hide', false);

	$footer_ca_text = get_theme_mod('footer_ca_text', '');
	$footer_ca_link = get_theme_mod('footer_ca_link', '');
	$footer_ca_link_text = get_theme_mod('footer_ca_link_text', '');
	$footer_after_btn_text = get_theme_mod('footer_after_btn_text', '');
	$footer_ca_position = get_theme_mod('footer_ca_position', 'center');

	$footer_ca_text_page = get_post_meta(get_the_id(), 'footer_ca_text', true);
	$footer_ca_link_page = get_post_meta(get_the_id(), 'footer_ca_link', true);
	$footer_ca_link_text_page = get_post_meta(get_the_id(), 'footer_ca_link_text', true);
	$footer_ca_position_page = get_post_meta(get_the_id(), 'footer_ca_position', true);

	if(!empty($footer_ca_text_page)) {
		$footer_ca_text = $footer_ca_text_page;
	}

	if(!empty($footer_ca_link_page)) {
		$footer_ca_link = $footer_ca_link_page;
	}

	if(!empty($footer_ca_link_text_page)) {
		$footer_ca_link_text = $footer_ca_link_text_page;
	}

	if(!empty($footer_ca_position_page) and $footer_ca_position_page != 'customizer_default') {
		$footer_ca_position = $footer_ca_position_page;
	}

	$first_word = '';

	if(!empty($footer_ca_text)) {
		$footer_ca_text = explode(' ', $footer_ca_text);
		if(!empty($footer_ca_text[0])) {
			if($footer_style == "footer_style_two" || splash_is_layout("sccr")){
				$firstArray = array();
				$secondArray = array();
				
				for($q=0;$q<count($footer_ca_text);$q++) {
					if(ceil(count($footer_ca_text)/2) > $q) {
						$firstArray[$q] = $footer_ca_text[$q];
					} else {
						$secondArray[$q] = $footer_ca_text[$q];
					}
				}
				$first_word = implode(" ", $firstArray);
				$footer_ca_text = implode( ' ', $secondArray );
			} else {
				$first_word = $footer_ca_text[0];
				
				array_shift($footer_ca_text);
				if(!empty($footer_ca_text)) {
					$footer_ca_text = implode( ' ', $footer_ca_text );
				}
			}
		}
	}



	if(!empty($footer_image) && !$footerHideImg): ?>
		<div class="stm-footer-image" style="background-image: url('<?php echo esc_url($footer_image); ?>'); <?php if($footer_style == "footer_style_two") echo esc_attr("height: 497px;"); ?>">

			<div class="inner text-<?php echo esc_attr($footer_ca_position); ?>">
				<div class="container">
					<div class="heading-font title">
						<?php if(!empty($first_word)): ?>
							<span class="stm-red"><?php echo esc_attr($first_word); ?> </span>
						<?php endif; ?>
						<?php if(!empty($footer_ca_text)): ?>
							<span class="stm-text"><?php echo esc_attr($footer_ca_text); ?></span>
						<?php endif; ?>
					</div>
					<div class="clearfix"></div>
					<div class="stm-btn-text-wrapper">
						<?php if(!empty($footer_ca_link) and !empty($footer_ca_link_text)): ?>
							<a href="<?php echo esc_url($footer_ca_link); ?>" class="button btn-md with_bg" target="_blank">
								<?php echo esc_attr($footer_ca_link_text); ?>
							</a>
						<?php endif; ?>
						<?php if(!empty($footer_after_btn_text)): ?>
							<span class="stm-after-btn-text">
								<?php echo esc_html($footer_after_btn_text);?>
							</span>
						<?php endif;?>
					</div>
				</div>
			</div>
		</div>
	<?php endif;
    if(get_theme_mod("show_socials_after_footer_img", false)) : ?>
        <div id="stm-footer-socials-top" class="stm-footer-socials-top">
            <ul class="footer-bottom-socials stm-list-duty">
                <?php foreach($stm_socials as $key => $value): ?>
                    <li class="stm-social-<?php echo esc_attr($key); ?>">
                        <a href="<?php echo esc_attr($value); ?>" target="_blank">
                            <i class="fa fa-<?php echo esc_attr($key); ?>"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
<?php endif;
endif; ?>