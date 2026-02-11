<?php
/**
 * Player Radar Graph Statistics for Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

// Skip if there are no rows in the table
if ( empty( $data ) ) {
	return;
}

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

// FIX: Rebounds sorting issue
// adds a new element REB into array based on OFF and DEF rebounds
if ( alchemists_sp_preset( 'basketball' ) ) {
	foreach ( $data as $player_key => $player_value ) {
		if ( isset( $data['off'] ) && isset( $data['def'] ) ) {
			$data['reb'] = $data['off'] + $data['def'];
		}
	}
}

wp_enqueue_script( 'alchemists-chartjs' );

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
if ( $team_color_primary ) {
	$color_primary           = $team_color_primary;
} else {
	$color_primary           = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}

$color_primary_radar       = hex2rgba( $color_primary, 0.8 );
$radar_enable              = isset( $alchemists_data['alchemists__player-title-radar'] ) ? $alchemists_data['alchemists__player-title-radar'] : 0;
$radar_performances_custom = isset( $alchemists_data['alchemists__player-title-radar-custom'] ) ? $alchemists_data['alchemists__player-title-radar-custom'] : array();

$radar_performances_custom_array = array();
if ( $radar_performances_custom ) {
	foreach ( $radar_performances_custom as $radar_performance_key => $radar_performance_value) {
		$radar_performances_custom_array[ get_post_field( 'post_name', $radar_performances_custom[$radar_performance_key] ) ] = get_post_field( 'post_title', $radar_performances_custom[$radar_performance_key] );
	}
}

// Player List
$player_list_id  = get_field( 'player_header_list' );

if ( $player_list_id ) {
	$player_list      = new SP_Player_List( $player_list_id );
	$player_list_data = $player_list->data();

	// Remove the first row to leave us with the actual data
	unset( $player_list_data[0] );

	// FIX: Rebounds sorting issue
	if ( alchemists_sp_preset( 'basketball' ) ) {
		foreach ( $player_list_data as $player_key => $player_value ) {
			if ( isset( $player_value['reb'] ) && isset( $player_value['off'] ) && isset( $player_value['def'] ) ) {
				$player_list_data[ $player_key ]['reb'] = $player_value['off'] + $player_value['def'];
			}
		}
	}
}

$radar_stats_defaults = array(
	'basketball' => array(
		'off'     => esc_html( 'OFF', 'alchemists' ),
		'ast'     => esc_html( 'AST', 'alchemists' ),
		'threepm' => esc_html( '3PT', 'alchemists' ),
		'fgm'     => esc_html( 'FGM', 'alchemists' ),
		'def'     => esc_html( 'DEF', 'alchemists' ),
	),
	'esports' => array(
		'kills'   => esc_html( 'Kills', 'alchemists' ),
		'deaths'  => esc_html( 'Deaths', 'alchemists' ),
		'assists' => esc_html( 'Assists', 'alchemists' ),
	)
);

$radar_stats = array();
if ( alchemists_sp_preset( 'basketball' ) ) {
	$radar_stats = $radar_stats_defaults['basketball'];
} elseif ( alchemists_sp_preset( 'esports' ) ) {
	$radar_stats = $radar_stats_defaults['esports'];
}

if ( $radar_enable ) {
	$radar_stats = $radar_performances_custom_array;
}

// get stats labels
$radar_stats_labels = array_values( $radar_stats );

// get stats values
$radar_stats_output = array();
$radar_stats_real_values   = array();

// check if Player List selected
if ( $player_list_id ) {

	/* Player List is selected
	 * display Radar chart with relative values comparing the statistics for player from the Player List
	 */
	foreach ( $radar_stats as $radar_stat_key => $radar_stat_label ) :

		$player_list->priorities = array(
			array(
				'key' => $radar_stat_key,
				'order' => 'DESC',
			),
		);
		uasort( $player_list_data, array( $player_list, 'sort' ) );

		$player_top = array_slice( $player_list_data, 0, 1, true );
		$player_top = current( $player_top );
		$player_top_value = $player_top[ $radar_stat_key ];

		if ( isset( $data[ $radar_stat_key ] ) ) {
			/*
			* If top player has '0', set '0' real and relative values
			* for the respective player without calculation
			*/
			if ( $player_top_value == 0 ) {
				$radar_stats_output[ $radar_stat_key ] = 0;
				$radar_stats_real_values[ $radar_stat_key ] = 0;
			} else {
				$radar_stats_output[ $radar_stat_key ] = apply_filters( 'alc_player_statistics_radar_value', round( $data[ $radar_stat_key ] / $player_top_value * 100 ) );
				$radar_stats_real_values[ $radar_stat_key ] = $data[ $radar_stat_key ];
			}
		} else {
			$radar_stats_output[ $radar_stat_key ] = 0;
		}

	endforeach;

	$radar_stats_output_values   = array_values( $radar_stats_output );
	$radar_stats_real_values_arr = array_values( $radar_stats_real_values );

} else {

	/* Player List is not selected
	 * display static Radar chart comparing the statistics of the respective player
	 */
	foreach ( $radar_stats as $radar_stat_key => $radar_stat_value ) {
		if ( isset( $data[ $radar_stat_key ] ) ) {
			$radar_stats_output[ $radar_stat_key ] = apply_filters( 'alc_player_statistics_radar_value', $data[ $radar_stat_key ] );
		}
	}

	$radar_stats_output_values   = array_values( $radar_stats_output );
	$radar_stats_real_values_arr = $radar_stats_output_values;
}
?>

<div class="player-info__item player-info__item--stats">
	<canvas id="player-stats" class="player-info-chart" height="290"></canvas>

	<script type="text/javascript">
		(function($){
			$(function() {

				var real_data = [
					<?php echo wp_json_encode( $radar_stats_real_values_arr ); ?>
				];

				var radar_data = {
					type: 'radar',
					data: {
						labels: <?php echo wp_json_encode( $radar_stats_labels ); ?>,
						datasets: [{
							data: <?php echo wp_json_encode( $radar_stats_output_values ); ?>,
							backgroundColor: "<?php echo esc_html( $color_primary_radar ); ?>",
							borderColor: "<?php echo esc_html( $color_primary ); ?>",
							pointBorderColor: "rgba(255,255,255,0)",
							pointBackgroundColor: "rgba(255,255,255,0)",
							pointBorderWidth: 0
						}]
					},
					options: {
						legend: {
							display: false,
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
								title: function() {}, // removes title inside label
								label: function(t, d) {
									let title = d.datasets[t.datasetIndex].label;
									let value = real_data[t.datasetIndex][t.index];
									return value;
								}
							}
						},
						scale: {
							angleLines: {
								color: "rgba(255,255,255,0.025)",
							},
							pointLabels: {
								fontColor: "#9a9da2",
								fontFamily: 'Montserrat, sans-serif',
							},
							ticks: {
								beginAtZero: true,
								display: false,
							},
							gridLines: {
								color: "rgba(255,255,255,0.05)",
								lineWidth: 2,
							},
							labels: {
								display: false
							}
						}
					},
				};

				var ctx = $("#player-stats");
				var playerInfo = new Chart(ctx, radar_data);
			});
		})(jQuery);

	</script>
</div>
