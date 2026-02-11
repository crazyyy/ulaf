<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.5
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $team_id
 * @var $team_id_on
 * @var $team_id_num
 * @var $label_won
 * @var $label_lost
 * @var $color_won
 * @var $color_lost
 * @var $chart_height
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Games_History
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$title = $team_id = $team_id_on = $team_id_num = $label_won = $label_lost = $label_draw = $color_won = $color_lost = $color_draw = $chart_height = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget card widget-games-history';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

wp_enqueue_script( 'alchemists-chartjs' );

// Create a unique identifier based on the current time in microseconds
$identifier        = uniqid( 'games-history-' );
$identifier_legend = uniqid( 'games-history-legend-' );

// Check if we're on Single Team page and Team is not selected
if ( is_singular('sp_team') && ( $team_id == 'default' || $team_id_on != 1 ) ) {
	if ( ! isset( $id ) ) {
		$id = get_the_ID();
	}
} else {
	$id = intval( $team_id );
}

if ( $team_id_on == 1 && ! empty( $team_id_num ) ) {
	$id = $team_id_num;
}

$chart_height = ! empty( $chart_height ) ? $chart_height : '230';

// Get Team Colors
$team_color_primary   = get_field( 'team_color_primary', $id );
$team_color_secondary = get_field( 'team_color_secondary', $id );
$team_color_tertiary  = get_field( 'team_color_heading', $id );

// Select the Team
$team = new SP_Team( $id );
if ( is_null( $team->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Team found with Team ID %s', 'alchemists' ), '<strong>' . $id . '</strong>' ). '</div>';
	return;
}
$tables = $team->tables();

$team_wins    = array();
$team_loses   = array();
$team_draws   = array();
$season_years = array();

$is_soccer = alchemists_sp_preset( 'soccer' ) ? true : false;

// Loop all League Tables
foreach ( $tables as $table ) {
	if ( ! $table ) continue;

	// get League Table ID
	$table_id = $table->ID;

	// check for the Seasons
	$seasons = (array)get_the_terms( $table_id, 'sp_season' );
	$season_names = array();
	foreach ( $seasons as $season ):
		if ( is_object( $season ) && property_exists( $season, 'term_id' ) && property_exists( $season, 'name' ) ):
			$season_names[] = $season->name;
		endif;
	endforeach;

	$season_years[] = $season_names[0];

	// jump into League Table data
	$table = new SP_League_Table( $table_id );
	$data = $table->data();

	// Remove the first row to leave us with the actual data
	unset( $data[0] );

	// and find Win games
	if ( alchemists_sp_preset( 'esports' ) || ( get_option( 'sportspress_sport' ) === 'tennis' ) ) {
		$team_wins[] = $data[$id]['wins'];
	} else {
		$team_wins[] = $data[$id]['w'];
	}

	// and Lost games
	if ( alchemists_sp_preset( 'esports' ) || ( get_option( 'sportspress_sport' ) === 'tennis' ) ) {
		$team_loses[] = $data[$id]['losses'];
	} else {
		$team_loses[] = $data[$id]['l'];
	}

	if ( $is_soccer ) {
		// and Draw games
		$team_draws[] = $data[$id]['d'];
	}
}

// Convert arrays
$team_wins    = implode(",", $team_wins);
$team_loses   = implode(",", $team_loses);
if ( $is_soccer ) {
	$team_draws   = implode(",", $team_draws);
}
$season_years = "'" . implode("','", $season_years) . "'";

// WON Bar color
$alchemists_data = get_option( 'alchemists_data' );

if ( ! $color_won ) {
	if ( alchemists_sp_preset( 'soccer' ) ) {
		if ( $team_color_secondary ) {
			$bar_won_color = $team_color_secondary;
		} else {
			$bar_won_color = isset( $alchemists_data['color-4'] ) ? $alchemists_data['color-4'] : '#c2ff1f';
		}
	} elseif ( alchemists_sp_preset( 'football' ) ) {
		if ( $team_color_secondary ) {
			$bar_won_color = $team_color_secondary;
		} else {
			$bar_won_color = isset( $alchemists_data['color-4'] ) ? $alchemists_data['color-4'] : '#3ffeca';
		}
	} else {
		if ( $team_color_primary ) {
			$bar_won_color = $team_color_primary;
		} else {
			$bar_won_color = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
		}
	}
} else {
	$bar_won_color = $color_won;
}


// LOST Bar color
if ( ! $color_lost ) {
	if ( alchemists_sp_preset( 'soccer' ) ) {
		if ( $team_color_primary ) {
			$bar_lost_color = $team_color_primary;
		} else {
			$bar_lost_color = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#38a9ff';
		}
	} elseif ( alchemists_sp_preset( 'football' ) ) {
		if ( $team_color_primary ) {
			$bar_lost_color = $team_color_primary;
		} else {
			$bar_lost_color = isset( $alchemists_data['color-3'] ) ? $alchemists_data['color-3'] : '#9e69ee';
		}
	} elseif ( alchemists_sp_preset( 'esports' ) ) {
		if ( $team_color_secondary ) {
			$bar_lost_color = $team_color_secondary;
		} else {
			$bar_lost_color = isset( $alchemists_data['color-2'] ) ? $alchemists_data['color-2'] : '#6a3bc0';
		}
	} else {
		$bar_lost_color = ! empty( $team_color_secondary ) ? $team_color_secondary : '#ff8429';
	}
} else {
	$bar_lost_color = $color_lost;
}


if ( alchemists_sp_preset( 'soccer' ) ) {
	// DRAW Bar color
	if ( ! $color_draw ) {
		if ( $team_color_tertiary ) {
			$bar_draw_color = $team_color_tertiary;
		} else {
			$bar_draw_color = isset( $alchemists_data['color-3'] ) ? $alchemists_data['color-3'] : '#07e0c4';
		}
	} else {
		$bar_draw_color = $color_draw;
	}
}
?>

<!-- Widget: Games History -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( alchemists_sp_preset( 'soccer' ) ) : ?>

		<?php if ( $title ) { ?>
			<div class="widget__title card__header card__header--has-legend">
				<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
				<div id="<?php echo esc_attr( $identifier_legend ); ?>" class="chart-legend chart-legend--games-history"></div>
			</div>
		<?php } ?>

		<div class="widget__content card__content">
			<canvas id="<?php echo esc_attr( $identifier ); ?>" class="games-history-chart" height="<?php echo esc_attr( $chart_height ); ?>"></canvas>

			<script type="text/javascript">
				(function($){
					$(function() {
						var data = {
							type: 'bar',
							data: {
								labels: [<?php print_r( $season_years ); ?>],
								datasets: [{
									label: "<?php echo esc_js( $label_won ); ?>",
									data: [<?php print_r( $team_wins ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_won_color ); ?>",
								}, {
									label: '<?php echo esc_js( $label_draw ); ?>',
									data: [<?php print_r( $team_draws ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_draw_color ); ?>",
								}, {
									label: '<?php echo esc_js( $label_lost ); ?>',
									data: [<?php print_r( $team_loses ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_lost_color ); ?>",
								}]
							},
							options: {
								legend: {
									display: false,
									labels: {
										boxWidth: 8,
										fontSize: 9,
										fontColor: '#31404b',
										fontFamily: 'Montserrat, sans-serif',
										padding: 20,
									}
								},
								tooltips: {
									backgroundColor: "rgba(49,64,75,0.8)",
									titleFontSize: 0,
									titleSpacing: 0,
									titleMarginBottom: 0,
									bodyFontFamily: 'Montserrat, sans-serif',
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
										stacked: true,
										barThickness: 34,
										gridLines: {
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											fontColor: '#9a9da2',
											fontFamily: 'Montserrat, sans-serif',
											fontSize: 10,
										},
									}],
									yAxes: [{
										stacked: true,
										gridLines: {
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											fontColor: '#9a9da2',
											fontFamily: 'Montserrat, sans-serif',
											fontSize: 10,
											padding: 20,
											userCallback: function(label, index, labels) {
												// when the floored value is the same as the value we have a whole number
												if (Math.floor(label) === label) {
													return label;
												}
											},
										}
									}]
								}
							},
						};

						var ctx = $('#<?php echo esc_js( $identifier ); ?>');
						var gamesHistory = new Chart(ctx, data);
						document.getElementById('<?php echo esc_js( $identifier_legend ); ?>').innerHTML = gamesHistory.generateLegend();
					});
				})(jQuery);

			</script>
		</div>

	<?php elseif ( alchemists_sp_preset( 'esports' ) ) : ?>

		<?php if ( $title ) : ?>
		<div class="widget__title card__header card__header--has-legend">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
			<div id="<?php echo esc_attr( $identifier_legend ); ?>" class="chart-legend chart-legend--games-history"></div>
		</div>
		<?php endif; ?>

		<div class="widget__content card__content">
			<canvas id="<?php echo esc_attr( $identifier ); ?>" class="games-history-chart" height="<?php echo esc_attr( $chart_height ); ?>"></canvas>

			<script type="text/javascript">
				(function($){
					$(function() {
						var data = {
							type: 'bar',
							data: {
								labels: [<?php print_r( $season_years ); ?>],
								datasets: [{
									label: "<?php echo esc_js( $label_won ); ?>",
									data: [<?php print_r( $team_wins ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_won_color ); ?>",
									// barThickness: 14,
									barPercentage: 0.7,
									categoryPercentage: 0.7
								}, {
									label: '<?php echo esc_js( $label_lost ); ?>',
									data: [<?php print_r( $team_loses ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_lost_color ); ?>",
									// barThickness: 14,
									barPercentage: 0.7,
									categoryPercentage: 0.7
								}]
							},
							options: {
								legend: {
									display: false,
									labels: {
										boxWidth: 8,
										fontSize: 9,
										fontColor: '#a59cae',
										fontFamily: 'Open Sans, sans-serif',
										padding: 20,
									}
								},
								tooltips: {
									backgroundColor: "<?php echo esc_js( $bar_lost_color ); ?>",
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
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											fontColor: '#a59cae',
											fontFamily: 'Open Sans, sans-serif',
											fontSize: 10,
										},
									}],
									yAxes: [{
										gridLines: {
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											beginAtZero: true,
											fontColor: '#a59cae',
											fontFamily: 'Open Sans, sans-serif',
											fontSize: 10,
											padding: 20,
											userCallback: function(label, index, labels) {
												// when the floored value is the same as the value we have a whole number
												if (Math.floor(label) === label) {
													return label;
												}
											},
										}
									}]
								}
							},
						};

						var ctx = $('#<?php echo esc_js( $identifier ); ?>');
						var gamesHistory = new Chart(ctx, data);
						document.getElementById('<?php echo esc_js( $identifier_legend ); ?>').innerHTML = gamesHistory.generateLegend();
					});
				})(jQuery);

			</script>
		</div>

	<?php else : ?>

		<?php if ( $title ) { ?>
			<div class="widget__title card__header card__header--has-legend">
				<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
				<div id="<?php echo esc_attr( $identifier_legend ); ?>" class="chart-legend chart-legend--games-history"></div>
			</div>
		<?php } ?>

		<div class="widget__content card__content">
			<canvas id="<?php echo esc_attr( $identifier ); ?>" class="games-history-chart" height="<?php echo esc_attr( $chart_height ); ?>"></canvas>

			<script type="text/javascript">
				(function($){
					$(function() {
						var data = {
							type: 'bar',
							data: {
								labels: [<?php print_r( $season_years ); ?>],
								datasets: [{
									label: "<?php echo esc_js( $label_won ); ?>",
									data: [<?php print_r( $team_wins ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_won_color ); ?>",
								}, {
									label: '<?php echo esc_js( $label_lost ); ?>',
									data: [<?php print_r( $team_loses ); ?>],
									backgroundColor: "<?php echo esc_js( $bar_lost_color ); ?>"
								}]
							},
							options: {
								legend: {
									display: false,
									labels: {
										boxWidth: 8,
										fontSize: 9,
										fontColor: '#31404b',
										fontFamily: 'Montserrat, sans-serif',
										padding: 20,
									}
								},
								tooltips: {
									backgroundColor: "rgba(49,64,75,0.8)",
									titleFontSize: 0,
									titleSpacing: 0,
									titleMarginBottom: 0,
									bodyFontFamily: 'Montserrat, sans-serif',
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
										barThickness: 14,
										gridLines: {
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											fontColor: '#9a9da2',
											fontFamily: 'Montserrat, sans-serif',
											fontSize: 10,
										},
									}],
									yAxes: [{
										gridLines: {
											display:false,
											color: "rgba(255,255,255,0)",
										},
										ticks: {
											beginAtZero: true,
											fontColor: '#9a9da2',
											fontFamily: 'Montserrat, sans-serif',
											fontSize: 10,
											padding: 20,
											userCallback: function(label, index, labels) {
												// when the floored value is the same as the value we have a whole number
												if (Math.floor(label) === label) {
													return label;
												}
											},
										}
									}]
								}
							},
						};

						var ctx = $('#<?php echo esc_js( $identifier ); ?>');
						var gamesHistory = new Chart(ctx, data);
						document.getElementById('<?php echo esc_js( $identifier_legend ); ?>').innerHTML = gamesHistory.generateLegend();
					});
				})(jQuery);

			</script>
		</div>

	<?php endif; ?>

</div>
<!-- Widget: Games History / End -->
