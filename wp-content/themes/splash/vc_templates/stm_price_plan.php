<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if ( isset( $atts['feature'] ) && strlen( $atts['feature'] ) > 0 ) {
	$feature = vc_param_group_parse_atts( $atts['feature'] );
	if ( ! is_array( $feature ) ) {
		$temp = explode( ',', $atts['feature'] );
		$paramValues = array();
		foreach ( $temp as $value ) {
			$data = explode( '|', $value );
			$newLine = array();
			$newLine['title'] = isset( $data[0] ) ? $data[0] : 0;
			$newLine['sub_title'] = isset( $data[1] ) ? $data[1] : '';
			if ( isset( $data[1] ) && preg_match( '/^\d{1,3}\%$/', $data[1] ) ) {
				$colorIndex += 1;
				$newLine['title'] = (float) str_replace( '%', '', $data[1] );
				$newLine['sub_title'] = isset( $data[2] ) ? $data[2] : '';
			}
			$paramValues[] = $newLine;
		}
		$atts['feature'] = urlencode( json_encode( $paramValues ) );
	}
}

$link = vc_build_link( $link );

?>

<div class="stm-price-plan-unit">
	<div class="stm-price-plan">
		<?php if(!empty($badge)): ?>
			<div class="badge heading-font"><?php echo esc_attr($badge); ?></div>
		<?php endif; ?>

		<header>
			<?php if(!empty($title)): ?>
				<div class="title <?php echo (splash_is_layout("bb")) ? "h4" : "normal_font"; ?>">
					<?php echo esc_attr($title); ?>
				</div>
			<?php endif; ?>
			<?php if(!empty($price)): ?>
				<div class="price heading-font">
					<?php echo esc_attr($price); ?>
				</div>
			<?php endif; ?>
			<?php if(!empty($price_label)): ?>
				<div class="price-label <?php echo (!splash_is_layout("af") && !splash_is_layout("baseball")) ? "heading-font" : "normal_font"; ?>">
					<?php echo esc_attr($price_label); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if(!empty($feature)): ?>
			<div class="body <?php echo (!splash_is_layout("af") && !splash_is_layout("baseball")) ? "heading-font" : "normal_font"; ?>">
				<?php foreach($feature as $feature_single): ?>
					<div class="single-feature"><?php echo esc_attr($feature_single['feature_item']); ?></div>
				<?php endforeach; ?>
			</div>

			<?php if(!empty($link['url']) and !empty($link['title'])): ?>
				<div class="button-unit">
					<a
						class="button only_border"
						href="<?php echo esc_url($link['url']) ?>"
						title="<?php echo esc_attr($link['title']); ?>"
						<?php if(!empty($link['target'])): ?>target="_blank" <?php endif; ?>
						><?php echo esc_attr($link['title']); ?></a>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</div>