<?php
$atts  = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

/*
 * [features_bg_img] => 329
    [features_bg_color] =>
    [features_line_color] => #ec1919
    [features_icon] => 324
    [features_title] => Best Players
    [features_content] => Spilled sensationally that sanctimoniously gawked dear misheard inside
 * */
$imgBg = wp_get_attachment_image_src($atts["features_bg_img"]);
$icon = wp_get_attachment_image_src($atts["features_icon"]);
$colorBg = $atts["features_bg_color"];
$colorLine = ($atts["features_line_color"] != "") ? $atts["features_line_color"] : "#ffffff";
$title = $atts["features_title"];
$content = $atts["features_content"];

?>

<div class="stm-features-wrapp" style="background: url('<?php echo esc_url($imgBg[0]); ?>'); border-bottom: 5px solid <?php echo esc_attr($colorLine); ?>;">
	<div class="stm-features-icon">
		<img src="<?php echo esc_url($icon[0]); ?>" />
	</div>
	<div class="stm-feature-title heading-font"><?php echo esc_html($title); ?></div>
	<div class="stm-feature-content"><?php echo esc_html($content); ?></div>
</div>
