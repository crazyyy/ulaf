<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

?>

<div class="stm-info-block-wrap">
	<div class="stm-ib-title">
		<h4><?php echo esc_html($block_title); ?></h4>
	</div>
	<div class="stm-ib-list">
		<ul>
			<?php foreach(json_decode(urldecode($atts["info_blocks"])) as $k => $val): ?>
				<li>
					<div class="stm-ib-item">
						<?php if(isset($val->info_icon)): ?>
						<i class="<?php echo esc_attr($val->info_icon); ?>"></i>
						<?php endif; ?>
						<span class="stm-ib-text"><?php echo esc_html($val->info_content);?></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
