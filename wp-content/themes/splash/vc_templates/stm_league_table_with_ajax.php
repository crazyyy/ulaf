<?php
$id = $count = $title = '';
$atts   = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );
splash_enqueue_modul_scripts_styles('stm_league_table_with_ajax');

/*Tables*/
$tables = get_posts(array('posts_per_page' => 9999, 'post_type' => 'sp_table'));
if($tables){
    $tables_array = array();
    foreach($tables as $table){
        $tables_array[$table->ID] = $table->post_title;
    }
}
$count = $count ? $count : 5;
?>
<div class="stm-league-wrap <?php echo esc_attr($css_class) ?>">
    <div class="stm-single-league-title_box clearfix">
        <h4 class="sp-table-caption"><?php echo splash_firstWordBold($title); ?><a href="" class="stm-link-all stm-link-all-league normal-font"><?php echo esc_html__('Full table', 'splash'); ?></a></h4>
    </div>
    <div class="select-league-wrap">
        <span class="select-label normal_font">
            <?php echo esc_html__('Select League', 'splash');?>
        </span>
        <select name="league_list">
            <?php
            if(!is_null($tables_array)) {
                foreach ($tables_array as $k => $val) {
            ?>
                <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($val); ?></option>
            <?php
                }
            }
            ?>
        </select>
    </div>
    <div id="stm-league-ajax-content" class="sp-template sp-template-league-table loading" data-count="<?php echo esc_attr($count); ?>">
    </div>
</div>
