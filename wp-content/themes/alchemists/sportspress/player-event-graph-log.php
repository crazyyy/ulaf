<?php
/**
 * Player Line Graph Stats
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'title' => false,
	'status' => 'default',
	'date' => 'default',
	'date_from' => 'default',
	'date_to' => 'default',
	'day' => 'default',
	'league' => null,
	'season' => null,
	'venue' => null,
	'team' => null,
	'player' => null,
	'number' => $number,
	'link_events' => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'paginated' => get_option( 'sportspress_event_blocks_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => get_option( 'sportspress_event_blocks_rows', 5 ),
	'orderby' => 'default',
	'order' => 'default',
	'show_title' => get_option( 'sportspress_event_blocks_show_title', 'no' ) == 'yes' ? true : false,
	'show_league' => get_option( 'sportspress_event_blocks_show_league', 'no' ) == 'yes' ? true : false,
	'show_season' => get_option( 'sportspress_event_blocks_show_season', 'no' ) == 'yes' ? true : false,
	'show_venue' => get_option( 'sportspress_event_blocks_show_venue', 'no' ) == 'yes' ? true : false,
	'hide_if_empty' => false,
	'player_id' => null,
	'player_stats' => null,
	'customize_player_stats' => null,
	'only_played' => null,
	'grid_lines_color' => null,
	'grid_txt_color' => null,
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if ( $status != 'default' )
	$calendar->status = $status;
if ( $date != 'default' )
	$calendar->date = $date;
if ( $date_from != 'default' )
	$calendar->from = $date_from;
if ( $date_to != 'default' )
	$calendar->to = $date_to;
if ( $league )
	$calendar->league = $league;
if ( $season )
	$calendar->season = $season;
if ( $venue )
	$calendar->venue = $venue;
if ( $team )
	$calendar->team = $team;
if ( $player )
	$calendar->player = $player;
if ( $order != 'default' )
	$calendar->order = $order;
if ( $orderby != 'default' )
	$calendar->orderby = $orderby;
if ( $day != 'default' )
	$calendar->day = $day;
$data = $calendar->data();

if ( $hide_if_empty && empty( $data ) ) return false;

if ( $show_title && false === $title && $id ):
	$caption = $calendar->caption;
	if ( $caption )
		$title = $caption;
	else
		$title = get_the_title( $id );
endif;

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

// Create a unique identifier based on the current time in microseconds
$identifier        = uniqid( 'player-stats-history-' );
$identifier_legend = uniqid( 'player-stats-history-legend-' );
$js_uniqid         = uniqid( 'id_' );

// Default colors
$color_primary   = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#00ff5b';
$color_secondary = isset( $alchemists_data['color-2'] ) ? $alchemists_data['color-2'] : '#6a3bc0';
if ( alchemists_sp_preset( 'esports' ) ) {
	$color_tertiary = '#fff600';
} else {
	$color_tertiary  = isset( $alchemists_data['color-4'] ) ? $alchemists_data['color-4'] : '#fff600';
}

// Player Stats - default
if ( alchemists_sp_preset( 'basketball' ) ) {
	$stats = array(
		'pts' => array(
			'label' => esc_html__( 'Points', 'alchemists' ),
			'color' => $color_primary,
		),
		'blk' => array(
			'label' => esc_html__( 'Blocks', 'alchemists' ),
			'color' => $color_secondary,
		),
		'ast' => array(
			'label' => esc_html__( 'Assists', 'alchemists' ),
			'color' => $color_tertiary,
		),
	);
} elseif ( alchemists_sp_preset( 'soccer' ) ) {
	$stats = array(
		'goals'   => array(
			'label' => esc_html__( 'Goals', 'alchemists' ),
			'color' => $color_primary,
		),
		'assists' => array(
			'label' => esc_html__( 'Assists', 'alchemists' ),
			'color' => $color_secondary,
		),
		'ck'      => array(
			'label' => esc_html__( 'Corner Kicks', 'alchemists' ),
			'color' => $color_tertiary,
		),
	);
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$stats = array(
		'yds'    => array(
			'label' => esc_html__( 'Rushing yards', 'alchemists' ),
			'color' => $color_primary,
		),
		'rec'    => array(
			'label' => esc_html__( 'Total receptions', 'alchemists' ),
			'color' => $color_secondary,
		),
		'recyds' => array(
			'label' => esc_html__( 'Receiving yards', 'alchemists' ),
			'color' => $color_tertiary,
		),
	);
} else {
	$stats = array(
		'kills'   => array(
			'label' => esc_html__( 'Kills', 'alchemists' ),
			'color' => $color_primary,
		),
		'deaths'  => array(
			'label' => esc_html__( 'Deaths', 'alchemists' ),
			'color' => $color_secondary,
		),
		'assists' => array(
			'label' => esc_html__( 'Assists', 'alchemists' ),
			'color' => $color_tertiary,
		),
	);
}

// Defaults values

if ( $customize_player_stats && ! empty( $player_stats ) ) {
	$stats = array();
	// set array based on custom values
	foreach ( $player_stats as $player_stat ) {
		if ( ! empty( $player_stat ) ) {
			$stat_label = get_post_field( 'post_name', $player_stat['stat_value']);
			$stats[ $stat_label ] = array(
				'label' => $player_stat['stat_heading'],
				'color' => $player_stat['stat_color']
			);
			$colors_array[] = ! empty( $player_stat['stat_color'] ) ? $player_stat['stat_color'] : '';
		}
	}
} else {
	// set default colors array
	$colors_array = array(
		$color_secondary,
		$color_primary,
		$color_tertiary,
	);
}


$event_dates    = array();
$event_stats    = array();

// limit for loop
$i = 0;
if ( intval( $number ) > 0 ) {
	$limit = $number;
}

foreach ( $data as $event ) {
	if ( isset( $limit ) && $i >= $limit ) continue;

	// get event id
	$event_id = $event->ID;

	// Check if Player took a part in the event
	if ( $only_played ) {
		$event_players = get_post_meta( $event_id, 'sp_player' );
		if ( ! in_array( $player_id, $event_players ) ) {
			continue;
		}
	}

	$event_performance = sp_get_performance( $event );

	// Remove the first row to leave us with the actual data
	unset( $event_performance[0] );

	// get event date
	$event_date     = $event->post_date;
	$event_dates[]  = date( 'M j', strtotime( $event_date ) );

	// Display selected stats
	foreach ( $stats as $stat_key => $stat_label ) {

		if ( isset( $event_performance[ $team ][ $player_id ][ $stat_key ] ) ) {
			$event_stats[ $stat_key ][] = $event_performance[ $team ][ $player_id ][ $stat_key ];
		} else {
			$event_stats[ $stat_key ][] = '0';
		}
	}

}

// Ge Dates array (ready for JS)
$event_dates = implode(',', array_map( 'alchemists_add_quotes', $event_dates ) );
?>


<!-- Last Game Log -->
<div class="card">

	<?php if ( $title ) : ?>
	<header class="card__header">
		<h4><?php echo esc_html( $title ); ?></h4>
		<div id="<?php echo esc_attr( $identifier_legend ); ?>" class="chart-legend"></div>
	</header>
	<?php endif; ?>

	<div class="card__content">
		<canvas id="<?php echo esc_attr( $identifier ); ?>" class="points-history-chart" height="133"></canvas>

		<script type="text/javascript">
			(function($){
				$(function() {

					var data<?php echo esc_js( $js_uniqid ); ?> = {
						type: 'line',
						data: {
							labels: [<?php print_r( $event_dates ); ?>],
							datasets: [<?php
							foreach ( $event_stats as $event_stat_key => $event_stat_label ) :
								// create a temp array
								$event_stats_processed = [];
								// prepare array for JS by removing time codes (for timed performances) and spaces
								foreach ( $event_stats[ $event_stat_key ] as $event_stat_key_inner ) {
									$event_stats_processed[] = trim( strstr( $event_stat_key_inner, '(', true ) ? strstr( $event_stat_key_inner, '(', true ) : $event_stat_key_inner, ' ' );
								}
								?>
								{
									label: '<?php echo esc_js( $stats[ $event_stat_key ]['label'] ); ?>',
									fill: false,
									lineTension: 0,
									borderWidth: 4,
									backgroundColor: "<?php echo esc_js( $stats[ $event_stat_key ]['color'] ); ?>",
									borderColor: "<?php echo esc_js( $stats[ $event_stat_key ]['color'] ); ?>",
									borderCapStyle: 'butt',
									borderDashOffset: 0.0,
									borderJoinStyle: 'bevel',
									pointRadius: 5,
									pointBorderWidth: 5,
									pointBackgroundColor: "#fff",
									pointHoverRadius: 5,
									pointHoverBackgroundColor: "#fff",
									pointHoverBorderColor: "<?php echo esc_js( $stats[ $event_stat_key ]['color'] ); ?>",
									pointHoverBorderWidth: 5,
									pointHitRadius: 10,
									data: [<?php echo implode(',', $event_stats_processed ); ?>],
									spanGaps: false,
								},<?php endforeach; ?>]
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
										color: "<?php echo esc_js( $grid_lines_color ); ?>",
										zeroLineColor: '<?php echo esc_js( $grid_lines_color ); ?>'
									},
									ticks: {
										fontColor: '<?php echo esc_js( $grid_txt_color ); ?>',
										fontFamily: 'Open Sans, sans-serif',
										fontSize: 10,
									},
								}],
								yAxes: [{
									gridLines: {
										color: "<?php echo esc_js( $grid_lines_color ); ?>",
										zeroLineColor: '<?php echo esc_js( $grid_lines_color ); ?>'
									},
									ticks: {
										beginAtZero: true,
										fontColor: '<?php echo esc_js( $grid_txt_color ); ?>',
										fontFamily: 'Open Sans, sans-serif',
										fontSize: 10,
										padding: 20
									}
								}]
							}
						},
					};

					var ctx<?php echo esc_js( $js_uniqid ); ?> = $('#<?php echo esc_js( $identifier ); ?>');
					var gamesHistory<?php echo esc_js( $js_uniqid ); ?> = new Chart(ctx<?php echo esc_js( $js_uniqid ); ?>, data<?php echo esc_js( $js_uniqid ); ?>);

					document.getElementById('<?php echo esc_js( $identifier_legend ); ?>').innerHTML = gamesHistory<?php echo esc_js( $js_uniqid ); ?>.generateLegend();
				});
			})(jQuery);

		</script>
	</div>
</div>
<!-- Last Game Log / End -->
