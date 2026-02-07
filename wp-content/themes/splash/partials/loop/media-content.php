<?php
function stm_single_media_output( $id, $count, $style = 'style_1', $sidebar_position = 'none', $disable_masonry='', $image_id = array() ) {
	$id        = intval( $id );
	$count     = intval( $count );
	$post_type = get_post_meta( $id, 'media_type', true );


	/*IMAGE SIZE*/
	$small      = 'stm-540-500';
	$horizontal = 'stm-570-250';
	$vertical   = 'stm-270-530';

	$size = $small;


	if ( $sidebar_position == 'none' ) {
		$styles = array(
			'style_1' => array( 2, 3 ),
			'style_2' => array( 1, 3 ),
			'style_3' => array( 3, 2 ),
		);
	} else {
		$styles = array(
			'style_1' => array( 1, 2 ),
			'style_2' => array( 1, 2 ),
			'style_3' => array( 1, 2 ),
		);
	}

	$per_load = 6;
	if ( $sidebar_position != 'none' ) {
		$per_load = 7;
	}

	/*Make vertical size every (2,8,14,20,..)*/
	if ( $count == $styles[ $style ][0] or ( ( $count - $styles[ $style ][0] ) % $per_load ) == 0 ) {
		$size = $vertical;
	}

	/*Make horizontal size every (3,9,15,21,..)*/
	if ( $count == $styles[ $style ][1] or ( ( $count - $styles[ $style ][1] ) % $per_load ) == 0 ) {
		$size = $horizontal;
	}

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
							<a href="<?php echo esc_url( $fancy_link ); ?>" class="stm-fancybox"
							   title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_photos">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
						<?php else: ?>
							<a href="#" data-url="<?php echo esc_url( $fancy_link ); ?>" class="stm-iframe"
							   title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_<?php echo esc_attr($post_type); ?>">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
						<?php endif; ?>
							<div class="icon"><?php if(splash_is_layout("baseball")) echo esc_html($post_type); ?></div>
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
					<a href="<?php echo esc_url( $fancy_link ); ?>" class="stm-fancybox"
					   title="<?php echo esc_attr( get_the_title( $id ) ); ?>" data-fancybox-group="stm_photos">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_id['text'] ); ?>" />
						<?php else: ?>
						<a href="#" data-url="<?php echo esc_url( $fancy_link ); ?>" class="stm-iframe"
						   title="<?php echo esc_attr( $image_id['text'] ); ?>" data-fancybox-group="stm_<?php echo esc_attr($image_id['type']); ?>">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" />
							<?php endif; ?>
							<div class="icon"><?php if(splash_is_layout("baseball")) echo esc_html($post_type); ?></div>
							<div class="title">
								<?php echo esc_attr( $image_id['text'] ); ?>
							</div>
						</a>
				</div>
			<?php endif; ?>
		</div>
	<?php endif;
}