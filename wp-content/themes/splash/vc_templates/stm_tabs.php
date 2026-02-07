<?php
$atts  = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$items = json_decode(urldecode($tab_item));
?>

<div class="stm-tabs-wrap">
	<?php

	$tabs = "";
	$contents = "";
	foreach ($items as $k => $val) {
		$id = "tab_id_" . uniqid ('');
		$class = ($k == 0) ? 'class="active"' : "";
		$contentClass = ($k == 0) ? 'in active' : "";

		$tabs .= '<li ' . $class . '><a class="heading-font" data-toggle="tab" href="#' . $id . '">' . $val->tab_title . '</a></li>';
		$contents .= '<div id="' . $id . '" class="tab-pane fade ' . $contentClass . '">';
		$contents .= '<span class="tab-content">' . $val->tab_content . '</span>';
		$contents .= '</div>';
	}
	?>
	<ul class="nav nav-tabs">
		<?php echo splash_sanitize_text_field($tabs); ?>
	</ul>
	<div class="tab-content tab-content-wrapp">
		<?php echo apply_filters("the_content", $contents); ?>
	</div>
</div>