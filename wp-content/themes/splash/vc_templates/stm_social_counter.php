<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

splash_enqueue_modul_scripts_styles('stm_social_counter');
$opt = get_option('apsc_settings');
if(!empty($opt)) {
?>

<div class="stm-social-counter-wrap <?php echo esc_attr($css_class) ?>">
    <div class="stm-social-counter-title_box clearfix">
        <h4><?php echo splash_firstWordBold($social_title); ?></h4>
    </div>
    <div class="stm-social-counter-content">
        <?php echo do_shortcode('[aps-counter theme="' . $opt['social_profile_theme'] . '"]') ?>
    </div>
</div>
<?php
} else {
    echo 'AccessPress Not installed';
}
?>