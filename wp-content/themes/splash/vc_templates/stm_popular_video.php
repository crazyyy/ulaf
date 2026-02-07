<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

set_query_var('atts', $atts);
get_template_part('partials/vc_templates_views/stm_popular_video_' . $view_style);
?>
