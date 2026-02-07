<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

splash_enqueue_modul_scripts_styles('stm_results_table_ajax_' . esc_attr($results_type));

$taxList = get_terms(array('taxonomy' => 'sp_league'));

?>

<div class="stm-events-wrap <?php echo esc_attr($css_class) ?>">
    <div class="stm-single-events-title_box clearfix">
        <h4 class="sp-table-caption"><?php echo splash_firstWordBold($title); ?><a href="?sp_calendar=fixtures-list" class="stm-link-all stm-link-all-<?php echo esc_attr($results_type); ?> normal-font"><?php echo esc_html__('Full table', 'splash'); ?></a></h4>
    </div>
    <?php if(!is_null($taxList)): ?>
        <div class="select-events-wrap">
            <span class="select-label normal_font">
                <?php echo esc_html__('Select League', 'splash');?>
            </span>
            <select name="events_league_list_<?php echo esc_attr($results_type); ?>">
                <?php
                    foreach ($taxList as $k => $val) {
                        ?>
                        <option value="<?php echo esc_attr($val->term_id); ?>"><?php echo esc_html($val->name); ?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
    <?php endif;?>
    <div id="stm-events-league-ajax-content-<?php echo esc_attr($results_type); ?>" class="stm-events-result-units" data-posts="<?php echo esc_attr($count); ?>">

    </div>
</div>