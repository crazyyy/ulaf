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

if(!empty($feature)): ?>

	<div class="stm-team-history">

		<?php foreach($feature as $single_feature): ?>

			<div class="stm-team-history-single">
				<?php if(!empty($single_feature['year'])): ?>
					<div class="clearfix">
						<div class="year heading-font"><?php echo esc_attr($single_feature['year']); ?></div>
						<div class="stm-team-history-linked"><span></span></div>
						<div class="stm-team-history-content">

							<?php if(!empty($single_feature['title'])): ?>
								<div class="title heading-font"><?php echo esc_attr($single_feature['title']); ?></div>
							<?php endif; ?>

							<?php if(!empty($single_feature['content'])): ?>
								<div class="content"><?php echo esc_attr($single_feature['content']); ?></div>
							<?php endif; ?>

						</div>
					</div>
				<?php endif; ?>
			</div>

		<?php endforeach; ?>

	</div>

<?php endif; ?>