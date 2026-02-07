<?php
$list_type = 'marked';
$title = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$circleClass = "";

if($circle_bg == "enable") {
	$circleClass = "circle";
}

$hf = (!splash_is_layout("af") && !splash_is_layout("baseball") && !splash_is_layout('soccer_two') && !splash_is_layout('basketball_two')) ? "heading-font" : "normal_font";

if($list_type == 'font' and !empty($title)) {
	$content = str_replace('<ul>', '<ul class="stm-list-icon ' . $circleClass . '">', $content);
	$content = str_replace('<li>', '<li class="' . $hf . '"><i class="'.$title. ' ' .$circleClass. ' "></i>', $content);
}
?>
<?php echo wpb_js_remove_wpautop($content, true); ?>