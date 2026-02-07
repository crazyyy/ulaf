<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

$justifyContent = "flex-start";
if($position == "center") $justifyContent = "center";
if($position == "right") $justifyContent = "flex-end";

?>

<div class="stm-share-this-wrapp <?php echo esc_attr($share_style); ?>" style="justify-content: <?php echo esc_attr($justifyContent); ?>;">
	<span><?php echo esc_html($atts['title']); ?></span>
	<span class="stm-share-btn-wrapp">
		<?php if(function_exists('A2A_SHARE_SAVE_pre_get_posts')) echo A2A_SHARE_SAVE_add_to_content(""); ?>
	</span>
</div>
