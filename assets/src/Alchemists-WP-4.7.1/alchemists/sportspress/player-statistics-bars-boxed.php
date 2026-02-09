<?php
/**
 * Player Percentage Statistics for Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   4.0.0
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

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
$bars_enable       = isset( $alchemists_data['alchemists__player-title-progress-bars-boxed'] ) ? $alchemists_data['alchemists__player-title-progress-bars-boxed'] : 0;
$bars_stats_custom = isset( $alchemists_data['alchemists__player-title-progress-bars-custom-boxed'] ) ? $alchemists_data['alchemists__player-title-progress-bars-custom-boxed'] : array();

$bars_stats_custom_array = array();
if ( $bars_stats_custom ) {
	foreach ( $bars_stats_custom as $bars_stat_key => $bars_stat_value) {
		$bars_stats_custom_array[ get_post_field( 'post_name', $bars_stats_custom[ $bars_stat_key ] ) ] = get_post_field( 'post_title', $bars_stats_custom[ $bars_stat_key ] );
	}
}

// Get Player Statistics posts
$statistics = get_posts( array(
	'post_type'      => 'sp_statistic',
	'posts_per_page' => 9999
));

// New array based on Player Statistics posts
$statistic_array = array();
if( $statistics ){
	foreach( $statistics as $statistic ){
		$statistic_array[ $statistic->post_name ] = $statistic->post_title;
	}
}

$bars_stats_defaults = array(
	'basketball' => array(
		'threeppercent',
		'fgpercent',
	),
	'soccer' => array(
		'passpercent',
		'perf',
	),
	'football' => array(
		'cmppercent',
		'tdpercent',
	),
	'esports' => array(
		'winrate',
		'killsp',
	)
);

$bars_stats = array();
if ( alchemists_sp_preset( 'soccer' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['soccer'] );
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['football'] );
} elseif ( alchemists_sp_preset( 'basketball' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['basketball'] );
} elseif ( alchemists_sp_preset( 'esports' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['esports'] );
}

if ( $bars_enable ) {
	$bars_stats = $bars_stats_custom_array;
}

$progress_classes = array(
	'progress',
);
$progress_bar_classes = array(
	'progress__bar',
);

$bar_color = '';

// Specifies elements depends on selected sport
if ( alchemists_sp_preset( 'soccer') ) {
	// progress bar types
	$progress_bar_classes[] = 'progress__bar--success';

	// adds class for progress battery customization
	if ( $team_color_secondary ) {
		$bar_color = 'background-color: ' . $team_color_secondary . ';';
	}

} elseif ( alchemists_sp_preset( 'football') ) {

	// adds class for progress battery customization
	if ( $team_color_primary ) {
		$progress_classes[] = 'progress--battery-custom';
		$bar_color = 'color: ' . $team_color_primary . ';';
	}

	// progress bar classes
	$progress_classes[] = 'progress--battery';

} elseif ( alchemists_sp_preset( 'basketball') ) {

	// progress bar types
	$progress_bar_classes[] = 'progress__bar--primary';

	// adds class for progress battery customization
	if ( $team_color_secondary ) {
		$bar_color = 'background-color: ' . $team_color_secondary . ';';
	}

} elseif ( alchemists_sp_preset( 'esports') ) {

	// adds class for progress battery customization
	if ( $team_color_primary ) {
		$bar_color = 'background-color: ' . $team_color_primary . ';';
	}

	// progress bar classes
	$progress_classes[] = 'progress--lines';
}
?>

<div class="team-roster__player-stats-progress-bars">

	<!-- Progress Stats Table -->
	<table class="progress-table progress-table--simple">
		<tbody>
			<?php
			foreach ( $bars_stats as $bars_stat_key => $bars_stat_value ) :

				$title = $bars_stat_value;
				$value = $data[ $bars_stat_key ];

				if ( $value > 10) {
					$value = round( $value, 0 );
				}
				?>
				<!-- Progress: <?php echo esc_html( $title ); ?> -->
				<tr>
					<td class="progress-table__title"><?php echo esc_html( $title ); ?></td>
					<td class="progress-table__progress-bar">
						<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
							<div class="<?php echo esc_attr( implode( ' ', $progress_bar_classes ) ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $value ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $value ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?> "></div>
						</div>
					</td>
					<td class="progress-table__progress-label progress-table__progress-label--highlight"><?php echo esc_html( $value ); ?><span class="progress-table__progress-percent-sign">%</span></td>
				</tr>
				<!-- Progress: <?php echo esc_html( $title ); ?> / End -->
			<?php endforeach; ?>

		</tbody>
	</table>
	<!-- Progress Stats Table / End -->

</div>
