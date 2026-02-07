<?php
$atts  = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$date = new DateTime( $atts['countdown_date'] );

if($date) {
    $offset = get_option('gmt_offset');
    if(!empty(floatval($offset))){
        if(floatval($offset) <= 0){
            $date->modify('+' . abs(floatval($offset)) * 60 . ' minute');
        }
        else {
            $date->modify('-' . abs(floatval($offset)) * 60 . ' minute');
        }
    }
	$date_show = $date->format('F d, Y - H:i');
	$date = $date->format('Y-m-d H:i:s');
}
?>
<div class="stm-countdown-wrapper">
	<time class="heading-font" datetime="<?php echo esc_attr($date) ?>"  data-countdown="<?php echo esc_attr( str_replace( "-", "/", $date ) ) ?>"></time>
</div>