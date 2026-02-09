<?php
/**
 * Player Average Statistics for Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   1.0.0
 * @version   4.4.9
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

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
$color_primary       = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
$stats_enable        = isset( $alchemists_data['alchemists__player-title-stats'] ) ? $alchemists_data['alchemists__player-title-stats'] : 0;
$stats_custom        = isset( $alchemists_data['alchemists__player-title-stats-custom'] ) ? $alchemists_data['alchemists__player-title-stats-custom'] : array();
$performances_enable = isset( $alchemists_data['alchemists__player-title-performance'] ) ? $alchemists_data['alchemists__player-title-performance'] : 0;
$performances_custom = isset( $alchemists_data['alchemists__player-title-performance-custom'] ) ? $alchemists_data['alchemists__player-title-performance-custom'] : array();

$stats_custom_array = array();
if ( $stats_custom ) {
	foreach ( $stats_custom as $stat_key => $stat_value) {
		$stats_custom_array[ get_post_field( 'post_name', $stats_custom[ $stat_key ] ) ] = array(
			'sp_type'      => get_post_field( 'sp_type', $stats_custom[ $stat_key ] ),
			'post_excerpt' => get_post_field( 'post_excerpt', $stats_custom[ $stat_key ] )
		);
	}
}

$performances_custom_array = array();
if ( $performances_custom ) {
	foreach ( $performances_custom as $performance_key => $performance_value) {
		$performances_custom_array[ get_post_field( 'post_name', $performances_custom[$performance_key] ) ] = get_post_field( 'post_excerpt', $performances_custom[$performance_key] );
	}
}

// Player List
$player_list_id  = get_field( 'player_header_list' );

if ( $player_list_id ) {
	$player_list      = new SP_Player_List( $player_list_id );
	$player_list_data = $player_list->data();

	// Remove the first row to leave us with the actual data
	unset( $player_list_data[0] );
}

// Default Stats
$stats_default = array(
	'football' => array(
		'avg'    => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Yards per carry', 'alchemists' )
		),
		'recavg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Yards per reception', 'alchemists' )
		),
		'tdavg'  => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Touchdowns per game', 'alchemists' )
		)
	),
	'soccer' => array(
		'gpg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Goals per game', 'alchemists' )
		),
		'apg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Assists per game', 'alchemists' )
		),
	),
	'basketball' => array(
		'ppg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Points per game', 'alchemists' )
		),
		'apg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Assists per game', 'alchemists' )
		),
		'rpg' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Rebounds per game', 'alchemists' )
		),
	),
	'esports' => array(
		'winrate' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Win Rate', 'alchemists' )
		),
		'avgkdaratio' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Avg. KDA Ratio', 'alchemists' )
		),
		'killsp' => array(
			'sp_type'      => 'average',
			'post_excerpt' => esc_html__( 'Kills P.', 'alchemists' )
		),
	)
);

// Default Performances
$performances_default = array(
	'soccer' => array(
		'goals'       => esc_html__( 'T.Goals', 'alchemists' ),
		'assists'     => esc_html__( 'T.Assists', 'alchemists' ),
		'appearances' => esc_html__( 'T.Games', 'alchemists' ),
	)
);

$stats        = array();
$performances = array();

// Sports specifics
if ( alchemists_sp_preset( 'soccer') ) {

	// Soccer

	// circular bar
	$track_color     = 'rgba(255,255,255,.2)';
	if ( $team_color_secondary ) {
		$bar_color = $team_color_secondary;
	} else {
		$bar_color     = isset( $alchemists_data['color-4-darken'] ) ? $alchemists_data['color-4-darken'] : '#9fe900';
	}
	$stat_sublabel = '';
	$line_width    = 8;

	// css classes
	$stats_wrapper_class = 'player-info-stats pt-0';
	$stat_class          = 'player-info-stats__item player-info-stats__item--top-padding';

	// stats
	$stats = $stats_default['soccer'];
	$performances = $performances_default['soccer'];

} elseif ( alchemists_sp_preset( 'football') ) {

	// American Football

	// circular bar
	$track_color     = isset( $alchemists_data['alchemists__circular-bars-track-color'] ) && ! empty( $alchemists_data['alchemists__circular-bars-track-color'] ) ? $alchemists_data['alchemists__circular-bars-track-color'] : '#4e4d73';
	if ( $team_color_secondary ) {
		$bar_color = $team_color_secondary;
	} else {
		$bar_color     = isset( $alchemists_data['color-4'] ) ? $alchemists_data['color-4'] : '#3ffeca';
	}
	$stat_sublabel = '';
	$line_width    = 6;

	// css classes
	$stats_wrapper_class = 'player-info-stats';
	$stat_class          = 'player-info-stats__item';

	// stats
	$stats = $stats_default['football'];

} elseif ( alchemists_sp_preset( 'esports' ) ) {

	// eSports

	// circular bar
	$track_color     = isset( $alchemists_data['alchemists__circular-bars-track-color'] ) && ! empty( $alchemists_data['alchemists__circular-bars-track-color'] ) ? $alchemists_data['alchemists__circular-bars-track-color'] : '#4b3b60';
	$bar_color     = ! empty( $team_color_primary ) ? $team_color_primary : $color_primary;
	$stat_sublabel = '';
	$line_width    = 8;

	// css classes
	$stats_wrapper_class = 'player-info-stats';
	$stat_class          = 'player-info-stats__item';

	// stats
	$stats = $stats_default['esports'];
} else {

	// Basketball

	// circular bar
	$track_color     = 'rgba(255,255,255,.2)';
	$bar_color     = ! empty( $team_color_primary ) ? $team_color_primary : $color_primary;
	$stat_sublabel = '';
	$line_width    = 8;

	// css classes
	$stats_wrapper_class = 'player-info-stats';
	$stat_class          = 'player-info-stats__item';

	// stats
	$stats = $stats_default['basketball'];
}

// display custom stats if enabled
if ( $stats_enable ) {
	$stats = $stats_custom_array;
}

if ( $performances_enable ) {
	$performances = $performances_custom_array;
}
?>

<div class="<?php echo esc_attr( $stats_wrapper_class ); ?>">

	<?php if ( alchemists_sp_preset( 'soccer' ) ) : ?>
	<div class="player-info-stats__item">
		<div class="player-info-details player-info-details--extra-stats">

			<?php foreach ( $performances as $performance_key => $performance_value ) : ?>
				<?php if ( isset( $data[ $performance_key ] ) ) : ?>
					<div class="player-info-details__item player-info-details__item--goals">
						<h6 class="player-info-details__title"><?php echo esc_html( $performance_value ); ?></h6>
						<div class="player-info-details__value"><?php echo esc_html( $data[ $performance_key ] ); ?></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

		</div>
	</div>
	<?php endif; ?>

	<?php
	// check if Player List selected
	if ( $player_list_id ) {

		// Player List selected, so we can display circular bar with relative values
		foreach ( $stats as $stat_key => $stat_label ) :

			$player_list->priorities = array(
				array(
					'key' => $stat_key,
					'order' => 'DESC',
				),
			);
			uasort( $player_list_data, array( $player_list, 'sort' ) );

			$player_top = array_slice( $player_list_data, 0, 1, true );
			$player_top = current($player_top);
			$player_top_value = isset( $player_top[ $stat_key ] ) ? $player_top[ $stat_key ] : 0;

			if ( isset( $data[$stat_key] ) ) {
				if ( $player_top_value > 0 ) {
					$circular_percent = $data[$stat_key] / $player_top_value * 100;
				} else {
					$circular_percent = 0;
				}
				$circular_value = $data[$stat_key];
			} else {
				$circular_percent = 0;
				$circular_value = esc_html__( 'n/a', 'alchemists' );
			}

			if ( alchemists_sp_preset( 'football' ) ) {
				if ( $stat_key == 'avg') {
					$stat_sublabel = esc_html__( 'per carry', 'alchemists' );
				} elseif ( $stat_key == 'recavg' ) {
					$stat_sublabel = esc_html__( 'per reception', 'alchemists' );
				} else {
					$stat_sublabel = '';
				}
			}

			// Check for stat type (average or total)
			$is_avg = false;
			if ( 'average' == $stat_label['sp_type'] ) {
				$is_avg = true;
			}

			// output circular bar
			alchemists_sp_player_circular_bar(
				$class = $stat_class,
				$percent = $circular_percent,
				$line_width = $line_width,
				$track_color = $track_color,
				$bar_color = $bar_color,
				$stat_value = $circular_value,
				$stat_label = $stat_label['post_excerpt'],
				$stat_sublabel = $stat_sublabel,
				$is_avg = $is_avg
			);

		endforeach;

	} else {

		// Player List is not selected, display static circular bars
		foreach ( $stats as $stat_key => $stat_label ) :

			if ( isset( $data[$stat_key] ) ) {
				$circular_percent = 100;
				$circular_value = $data[$stat_key];
			} else {
				$circular_percent = 0;
				$circular_value = esc_html__( 'n/a', 'alchemists' );
			}

			if ( alchemists_sp_preset( 'football' ) ) {
				if ( $stat_key == 'avg') {
					$stat_sublabel = esc_html__( 'per carry', 'alchemists' );
				} elseif ( $stat_key == 'recavg' ) {
					$stat_sublabel = esc_html__( 'per reception', 'alchemists' );
				}
			}

			// Check for stat type (average or total)
			$is_avg = false;
			if ( 'average' == $stat_label['sp_type'] ) {
				$is_avg = true;
			}

			// output circular bar
			alchemists_sp_player_circular_bar(
				$class = $stat_class,
				$percent = $circular_percent,
				$line_width = $line_width,
				$track_color = $track_color,
				$bar_color = $bar_color,
				$stat_value = $circular_value,
				$stat_label = $stat_label['post_excerpt'],
				$stat_sublabel = $stat_sublabel,
				$is_avg = $is_avg
			);

		endforeach;

	} ?>

</div>
