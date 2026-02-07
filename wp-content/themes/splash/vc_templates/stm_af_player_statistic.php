<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
include(locate_template("partials/vc_templates_views/stm-af-player-statistic-" . $atts['af_ps_view_style'] . ".php"));