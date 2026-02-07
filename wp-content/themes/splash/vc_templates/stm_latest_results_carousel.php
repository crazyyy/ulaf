<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);
switch ($view_style) {
    case "style_1":
        include(locate_template("partials/vc_templates_views/stm_latest_results_carousel_" . $view_style . ".php"));
    break;
    case "style_2":
        include(locate_template("partials/vc_templates_views/stm_latest_results_carousel_" . $view_style . ".php"));
    break;
    default:
        include(locate_template("partials/vc_templates_views/stm_latest_results_carousel.php"));
}

wp_reset_postdata();
?>