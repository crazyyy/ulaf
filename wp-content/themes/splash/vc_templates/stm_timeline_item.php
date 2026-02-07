<?php
$img_id = '';
$img_size = '';
$title = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
?>

<div class="stm-timeline__image" data-hash="<?php echo esc_attr( $title ); ?>">
	<?php if( $img_id ) : ?>
		<?php
			if( empty( $img_size ) ) {
				$img_size = '635x450';
			}

			$img_data = wpb_getImageBySize( array(
				'attach_id' => $img_id,
				'thumb_size' => $img_size
			) );
		?>
		<?php echo wp_kses_post( $img_data['thumbnail'] ); ?>
	<?php endif; ?>

	<div class="stm-timeline__caption" data-hash="<?php echo esc_attr( $title ); ?>">
		<div class="stm-timeline__caption-inner">
			<?php if( !empty( $title ) ) : ?>
				<h3 class="stm-timeline__caption-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>

			<?php if( !empty( $content ) ) : ?>
				<div class="stm-timeline__caption-text"><?php echo wpb_js_remove_wpautop( $content, true ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
