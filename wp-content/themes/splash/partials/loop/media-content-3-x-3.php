<?php
function stm_single_media_output_3x3( $id, $count, $style = 'style_1', $sidebar_position, $disable_masonry = '', $image_id = array() ) {
	$id        = intval( $id );
	$count     = intval( $count );
	$post_type = get_post_meta( $id, 'media_type', true );


	/*IMAGE SIZE*/
	$small      = 'stm-360-240';
	$horizontal = 'stm-735-240';
	if(splash_is_layout('esport')){
	    $horizontal = 'stm-445-400';
    }
	$vertical   = 'stm-360-495';

	$size = $small;


	if($count == 1 || $count == 4) $size = $vertical;
	else if($count == 2) $size = $horizontal;


	if(!empty($disable_masonry) and $disable_masonry == 'disable') {
		$size = $small;
	}

	if(empty($image_id)):
		if ( ! empty( $post_type ) ): ?>
			<div <?php post_class( $size . ' stm-media-single-unit stm-media-single-unit-' . $post_type, $id ); ?>>
				<?php
				$image_url = splash_get_thumbnail_url( $id, 0, $size );
				if ( ! empty( $image_url ) ):
					$fancy_link = '';
					if ( $post_type == 'image' ) {
						$fancy_link = splash_get_thumbnail_url( $id, 0, 'full' );
					} else {
						$fancy_link = get_post_meta( $id, 'embed_link', true );
					}
					?>
					<div class="stm-media-preview">
						<?php if ($post_type == 'image'): ?>
							<?php if(splash_is_layout("baseball")): ?><div class="icon"><?php echo esc_html($post_type); ?></div><?php endif; ?>
                            <a href="<?php echo esc_url( $fancy_link ); ?>" class="stm-fancybox"
                               title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_photos">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
                        <?php else: ?>
						    <?php if(splash_is_layout("baseball")): ?><div class="icon"><?php echo esc_html($post_type); ?></div><?php endif; ?>
                            <a href="#" data-url="<?php echo esc_url( $fancy_link ); ?>" class="stm-iframe"
                               title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_<?php echo esc_attr($post_type); ?>">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
                        <?php endif; ?>
								<?php if(!splash_is_layout("baseball")): ?><div class="icon"></div><?php endif; ?>
                                <div class="title">
                                    <?php echo esc_attr( get_the_title() ); ?>
                                </div>
                            </a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif;
	else: ?>
		<div <?php post_class( $size . ' stm-media-single-unit stm-media-single-unit-' . $image_id['type'], $id ); ?>>
			<?php
			$image_url = splash_get_thumbnail_url( 0, $image_id['image'], $size );
			if ( ! empty( $image_url ) ):
				$fancy_link = '';
				if ( $image_id['type'] == 'image' ) {
					$fancy_link = splash_get_thumbnail_url( 0, $image_id['image'], 'full' );
				} else {
					$fancy_link = $image_id['url'];
				}
				?>
				<div class="stm-media-preview">
					<?php if ($image_id['type'] == 'image'): ?>
						<?php if(splash_is_layout("baseball")): ?> <div class="icon"><?php echo splash_sanitize_text_field($image_id['type']); ?></div><?php endif; ?>
                        <a href="<?php echo esc_url( $fancy_link ); ?>" class="stm-fancybox"
                           title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_photos">
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_id['text'] ); ?>" />
                    <?php else: ?>
                        <?php if(splash_is_layout("baseball")): ?> <div class="icon"><?php echo splash_sanitize_text_field($image_id['type']); ?></div><?php endif; ?>
                        <a href="#" data-url="<?php echo esc_url( $fancy_link ); ?>" class="stm-iframe"
                           title="<?php echo esc_attr( $image_id['text'] ); ?>" data-fancybox-group="stm_<?php echo esc_attr($image_id['type']); ?>">
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
                    <?php endif; ?>
                            <?php if(!splash_is_layout("baseball")): ?><div class="icon"></div><?php endif; ?>
                            <div class="title">
                                <?php echo esc_attr( $image_id['text'] ); ?>
                            </div>
                        </a>
				</div>
			<?php endif; ?>
		</div>
	<?php endif;
}