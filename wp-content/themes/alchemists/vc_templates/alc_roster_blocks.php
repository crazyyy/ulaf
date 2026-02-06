<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   3.4.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $player_lists_id
 * @var $player_lists_id_on
 * @var $player_lists_id_num
 * @var $columns
 * @var $metrics_customize
 * @var $values
 * @var $number
 * @var $squad_number
 * @var $age_display
 * @var $nationality_display
 * @var $nationality_flags_display
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Roster_Blocks
 */

$player_lists_id = $player_lists_id_on = $player_lists_id_num = $columns = $metrics_customize = $values = $number = $squad_number = $age_display = $nationality_display = $nationality_flags_display = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = '';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

if ( $player_lists_id_on == 1 && ! empty( $player_lists_id_num ) ) {
	$player_lists_id = $player_lists_id_num;
}

if ( ! isset( $squad_number ) ) {
	$squad_number = 1;
}

$values = (array) vc_param_group_parse_atts( $values );
$metrics_array = array();

foreach ( $values as $values_data ) {
	$new_metric = $values_data;
	$new_metric['metric_id'] = isset( $values_data['metric_id'] ) ? $values_data['metric_id'] : '';

	$metrics_array[] = $new_metric;
}

// Create array with Metrics
$metric_custom = array();
foreach ( $metrics_array as $metric_item ) {
	$metric_obj = get_post( $metric_item['metric_id'] );
	$metric_name = isset( $metric_obj->post_name ) ? $metric_obj->post_name : '';
	$metric_title = isset( $metric_obj->post_title ) ? $metric_obj->post_title : '';
	$metric_custom[ $metric_name ] = $metric_title;
}
// remove empty elements
$metric_custom = array_filter( $metric_custom );
?>

<!-- Roster Blocks -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php
		sp_get_template( 'player-blocks.php', array(
			'id'                        => $player_lists_id,
			'columns'                   => $columns,
			'number'                    => $number,
			'squad_number'              => $squad_number,
			'metric_custom'             => $metric_custom,
			'age_display'               => $age_display,
			'nationality_display'       => $nationality_display,
			'nationality_flags_display' => $nationality_flags_display,
			'metrics_customize'         => $metrics_customize,
		) );
	?>

</div>
<!-- Roster Blocks / End -->
