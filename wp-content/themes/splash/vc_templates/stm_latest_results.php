<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);

include(locate_template("partials/vc_templates_views/stm-latest-results-" . $atts['lr_view_style'] . ".php"));

wp_reset_postdata();
?>