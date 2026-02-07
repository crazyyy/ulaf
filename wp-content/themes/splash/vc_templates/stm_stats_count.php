<?php
$atts   = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$icon = wp_get_attachment_image_src($atts["stat_icon"], 'full');
if ($view_style != 'default')
    splash_enqueue_modul_scripts_styles('stm_stats_count_' . $view_style);

?>

<div class="stm-stats-wrapp <?php echo esc_attr($view_style) ?>">
    <?php if(!empty($icon)): ?>
    <img src="<?php echo esc_url($icon[0]);?>" />
    <?php endif; ?>

    <div class="stm-stat-info-wrapp">
        <span class="stm-stat-points heading-font"><?php echo esc_html($atts["stat_points"]); ?></span>
        <span class="stm-stat-title normal_font"><?php echo esc_html($atts["stat_title"]); ?></span>
    </div>
</div>
