<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$el_class = vc_shortcode_custom_css_class( $css, ' ' ) ;

$base_color = get_theme_mod("site_style_base_color", "");
splash_enqueue_modul_scripts_styles('stm_latest_tweets');
if(!empty($base_color)) :
?>

<style type="text/css">
    .stm-tweets-wrapp .stm-latest-tweets .latest-tweets ul li:before, .stm-tweets-wrapp .stm-latest-tweets .latest-tweets ul li .tweet-details a{
        color: <?php echo esc_attr($base_color); ?>;
    }
</style>

<?php endif; ?>

<div class="container stm_latest_tweets <?php if($carousel) echo 'style_carousel'; ?> <?php if($el_class) echo esc_attr( $el_class ); ?>">
	<div class="stm-tweets-wrapp">
		<div class="clearfix">
			<<?php echo esc_html(getHTag()); ?>><?php echo esc_html($atts["latest_tweets_title"]); ?></<?php echo esc_html(getHTag()); ?>>
		</div>
		<div class="stm-latest-tweets normal_font">
			<?php
                if ( shortcode_exists( 'custom-twitter-feeds' ) ) {
                    echo do_shortcode('[custom-twitter-feeds screenname="'.esc_html($atts['latest_tweets_name']).'"]');
                }
			?>
		</div>
	</div>
</div>
