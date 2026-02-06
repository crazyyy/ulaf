<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.5
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $player_id
 * @var $player_id_on
 * @var $player_id_num
 * @var $chart_type
 * @var $sum_color
 * @var $customize_primary_stats
 * @var $values_primary
 * @var $values
 * @var $values_equation
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Player_Doughnut_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

$title = $player_id = $player_id_on = $player_id_num = $chart_type = $sum_color = $customize_primary_stats = $values_primary = $values = $values_equation = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

// enqueue chart-js
wp_enqueue_script( 'alchemists-chartjs' );

$class_to_filter = 'widget widget-player-doughnut-stats card';

$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$id = $player_id;
// Check if we're on Single Team page and Team is not selected
if ( is_singular('sp_player') && ( $player_id == 'default' || $player_id_on != 1 ) ) {
	$id = get_the_ID();
}

if ( $player_id_on == 1 && ! empty( $player_id_num ) ) {
	$id = $player_id_num;
}

$player = new SP_Player( $id );
if ( is_null( $player->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Player found with Player ID %s', 'alchemists' ), '<strong>' . $id . '</strong>' ). '</div>';
	return;
}

// Customized Statistics (primary - numbers)
$values_primary = (array) vc_param_group_parse_atts( $values_primary );
$values_primary_array = array();
foreach ($values_primary as $value) {
	$custom_stat = $value;
	$custom_stat['stat_heading'] = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
	$custom_stat['stat_color']   = isset( $value['stat_color'] ) ? $value['stat_color'] : '';
	$custom_stat['stat_value']   = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

	$values_primary_array[] = $custom_stat;
}

$data = $player->data( 0, false );

// Remove the first row to leave us with the actual data
unset( $data[0] );

$defaults = array(
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );

if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $data[ $current_season ] ) ) {
		$data = $data[ $current_season ];
	}
} else {
	if ( isset( $data[-1] )) {
		$data = $data[-1];
	}
}

// Create a unique identifier based on the current time in microseconds
$identifier        = uniqid( 'player-stats-chart-' );
$identifier_legend = uniqid( 'player-stats-chart-legend-' );

// Player Name
$player_url  = get_the_permalink( $id );

// Player Stats - default
if ( alchemists_sp_preset( 'basketball' ) ) {
	$stats = array(
		'pts' => esc_html__( 'Points', 'alchemists' ),
		'blk' => esc_html__( 'Blocks', 'alchemists' ),
		'ast' => esc_html__( 'Assists', 'alchemists' ),
	);
} elseif ( alchemists_sp_preset( 'soccer' ) ) {
	$stats = array(
		'goals'   => esc_html__( 'Goals', 'alchemists' ),
		'assists' => esc_html__( 'Assists', 'alchemists' ),
		'ck'      => esc_html__( 'Corner Kicks', 'alchemists' ),
	);
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$stats = array(
		'yds'    => esc_html__( 'Rushing yards', 'alchemists' ),
		'rec'    => esc_html__( 'Total receptions', 'alchemists' ),
		'recyds' => esc_html__( 'Receiving yards', 'alchemists' ),
	);
} else {
	$stats = array(
		'kills'   => esc_html__( 'Kills', 'alchemists' ),
		'deaths'  => esc_html__( 'Deaths', 'alchemists' ),
		'assists' => esc_html__( 'Assists', 'alchemists' ),
	);
}

// Default colors
$color_primary   = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#00ff5b';
$color_secondary = isset( $alchemists_data['color-2'] ) ? $alchemists_data['color-2'] : '#6a3bc0';
if ( alchemists_sp_preset( 'esports' ) ) {
	$color_tertiary = '#fff600';
} else {
	$color_tertiary  = isset( $alchemists_data['color-4'] ) ? $alchemists_data['color-4'] : '#fff600';
}

// Defaults values
$stats_labels = array();
$stats_values = array();

if ( $customize_primary_stats && ! empty( $values_primary_array ) ) {
	// set array based on custom values
	foreach ( $values_primary_array as $stat_primary_item ) {
		if ( ! empty( $stat_primary_item ) ) {
			$statistic_primary = get_post_field( 'post_name', $stat_primary_item['stat_value']);
			$stats_values[] = isset( $data[ $statistic_primary ] ) ? $data[ $statistic_primary ] : '';
			$stats_labels[] = $stat_primary_item['stat_heading'] ? $stat_primary_item['stat_heading'] : '';
			$colors_array[] = ! empty( $stat_primary_item['stat_color'] ) ? $stat_primary_item['stat_color'] : '';
		}
	}
} else {
	// set array based on predefined values
	foreach ( $stats as $stat_primary_default_key => $stat_primary_default_value ) {
		$stats_values[] = isset( $data[ $stat_primary_default_key ] ) ? $data[ $stat_primary_default_key ] : '';
		$stats_labels[] = $stat_primary_default_value;
	}
	// set default colors array
	$colors_array = array(
		$color_secondary,
		$color_primary,
		$color_tertiary,
	);
}

$stats_sum    = alchemists_format_big_number( array_sum( $stats_values ) );
$stats_labels = implode(',', array_map( 'alchemists_add_quotes', $stats_labels ));
$stats_values = implode(',', $stats_values );
$colors_array = implode(',', array_map( 'alchemists_add_quotes', $colors_array ));
?>

<!-- Widget: Player Doughnut Stats -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<?php if ( $title ) { ?>
		<div class="widget__title card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php } ?>

	<div class="widget__content card__content">

		<?php if ( 'horizontal_bars' == $chart_type ) : ?>

		<canvas id="<?php echo esc_attr( $identifier ); ?>" height="230"></canvas>

		<script type="text/javascript">
			(function($){
				$(function() {

					var data = {
						type: 'horizontalBar',
						data: {
							labels: [<?php print_r( $stats_labels ); ?>],
							datasets: [{
								data: [<?php echo esc_js( $stats_values ); ?>],
								backgroundColor: [<?php print_r( $colors_array ); ?>],
								hoverBackgroundColor: [<?php print_r( $colors_array ); ?>],
								barThickness: 24,
							}]
						},
						options: {
							legend: {
								display: false,
								labels: {
									boxWidth: 8,
									fontSize: 9,
									fontColor: '#31404b',
									fontFamily: 'Open Sans, sans-serif',
									padding: 20,
								}
							},
							tooltips: {
								backgroundColor: "rgba(0,0,0,0.8)",
								titleFontSize: 0,
								titleSpacing: 0,
								titleMarginBottom: 0,
								bodyFontFamily: 'Open Sans, sans-serif',
								bodyFontSize: 9,
								bodySpacing: 0,
								cornerRadius: 2,
								xPadding: 10,
								displayColors: false,
								callbacks: {
									title: function() {} // removes title inside label
								}
							},
							scales: {
								xAxes: [{
									gridLines: {
										display: false,
										color: "rgba(255,255,255,0)",
									},
									ticks: {
										fontColor: '#8c8297',
										fontFamily: 'Open Sans, sans-serif',
										fontSize: 10,
										min: 0,
									},
								}],
								yAxes: [{
									gridLines: {
										display: false,
										color: "rgba(255,255,255,0)",
									},
									ticks: {
										fontColor: '#8c8297',
										fontFamily: 'Open Sans, sans-serif',
										fontSize: 10,
										padding: 20,
									}
								}]
							}
						},
					};

					var ctx = $('#<?php echo esc_js( $identifier ); ?>');
					var gamesHistory = new Chart(ctx, data);
				});
			})(jQuery);

		</script>

		<?php else : ?>

		<canvas id="<?php echo esc_attr( $identifier ); ?>" height="200"></canvas>
		<div id="<?php echo esc_attr( $identifier_legend ); ?>" class="chart-legend chart-legend--center"></div>

		<script type="text/javascript">
			(function($){
				$(function() {

					Chart.pluginService.register({
						beforeDraw: function (chart) {
							if (chart.config.options.elements.center) {
								//Get ctx from string
								var ctx = chart.chart.ctx;

								//Get options from the center object in options
								var centerConfig = chart.config.options.elements.center;
								var fontStyle = centerConfig.fontStyle || 'Roboto Condensed, sans-serif';
								var txt = centerConfig.text;
								var color = centerConfig.color || '<?php echo esc_js( $sum_color ); ?>';
								var fontWeight = centerConfig.fontWeight || 'bold';
								var sidePadding = centerConfig.sidePadding || 20;
								var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2);
								//Start with a base font of 56px
								ctx.font = "56px " + fontStyle;

								//Get the width of the string and also the width of the element minus 10 to give it 5px side padding
								var stringWidth = ctx.measureText(txt).width;
								var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

								// Find out how much the font can grow in width.
								var widthRatio = elementWidth / stringWidth;
								var newFontSize = Math.floor(30 * widthRatio);
								var elementHeight = (chart.innerRadius * 2);

								// Pick a new font size so it will not be larger than the height of label.
								var fontSizeToUse = Math.min(newFontSize, elementHeight);

								//Set font settings to draw it correctly.
								ctx.textAlign = 'center';
								ctx.textBaseline = 'middle';
								var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
								var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
								ctx.font = fontWeight + " " + fontSizeToUse + "px " + fontStyle;
								ctx.fillStyle = color;

								//Draw text in center
								ctx.fillText(txt, centerX, centerY);
							}
						}
					});

					var data = {
						type: 'doughnut',
						data: {
							labels: [<?php print_r( $stats_labels ); ?>],
							datasets: [{
								data: [<?php echo esc_js( $stats_values ); ?>],
								backgroundColor: [<?php print_r( $colors_array ); ?>],
								hoverBackgroundColor: [<?php print_r( $colors_array ); ?>],
								borderWidth: 0
							}]
						},
						options: {
							legend: {
								display: false,
								labels: {
									boxWidth: 8,
									fontSize: 12,
									fontColor: '#fff',
									fontStyle: 'bold',
									padding: 20,
								}
							},
							tooltips: {
								backgroundColor: "rgba(0,0,0,0.8)",
								bodyFontSize: 10,
								bodySpacing: 0,
								cornerRadius: 2,
								xPadding: 10,
							},
							cutoutPercentage: 90,
							elements: {
								center: {
									text: '<?php echo esc_js( $stats_sum ); ?>',
									sidePadding: 30,
									fontWeight: 'bold',
								}
							}
						}
					};

					var ctx = $('#<?php echo esc_js( $identifier ); ?>');
					var gamesHistory = new Chart(ctx, data);

					document.getElementById('<?php echo esc_js( $identifier_legend ); ?>').innerHTML = gamesHistory.generateLegend();
				});
			})(jQuery);

		</script>

		<?php endif; ?>

	</div>

</div>
<!-- Widget: Player Doughnut Stats / End -->
