<?php
/**
 * Player Percentage Statistics for Single Player
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   1.0.0
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
$bars_enable       = isset( $alchemists_data['alchemists__player-title-progress-bars'] ) ? $alchemists_data['alchemists__player-title-progress-bars'] : 0;
$bars_stats_custom = isset( $alchemists_data['alchemists__player-title-progress-bars-custom'] ) ? $alchemists_data['alchemists__player-title-progress-bars-custom'] : array();
$bars_format       = isset( $alchemists_data['alchemists__player-title-progress-bars-custom-format'] ) ? $alchemists_data['alchemists__player-title-progress-bars-custom-format'] : 'percentage';

$bars_stats_custom_array = array();
if ( $bars_stats_custom ) {
	foreach ( $bars_stats_custom as $bars_stat_key => $bars_stat_value) {
		$bars_stats_custom_array[ get_post_field( 'post_name', $bars_stats_custom[ $bars_stat_key ] ) ] = get_post_field( 'post_excerpt', $bars_stats_custom[ $bars_stat_key ] );
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
		$statistic_array[ $statistic->post_name ] = $statistic->post_excerpt;
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
$bars_stats_defaults = array(
	'soccer' => array(
		'winratio',
		'shpercent',
		'passpercent',
		'perf',
		'penpercent',
	),
	'football' => array(
		'cmppercent',
		'tdpercent',
		'eventsplayedpercent',
		'fgpercent',
	)
);

$bars_stats = array();
if ( alchemists_sp_preset( 'soccer' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['soccer'] );
} else if ( alchemists_sp_preset( 'football' ) ) {
	$bars_stats = alchemists_sp_filter_array( $statistic_array, $bars_stats_defaults['football'] );
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
}
?>

<!-- Player Stats -->
<div class="player-info__item player-info__item--stats">
	<div class="player-info__item--stats-inner">

		<?php
		// check if Player List selected
		if ( $bars_enable && $player_list_id && $bars_format === 'relative') :

			// Player List selected, so we can display progress bars with relative values
			foreach ( $bars_stats as $bars_stat_key => $bars_stat_value ) :

				$excerpt = $bars_stat_value;

				$player_list->priorities = array(
					array(
						'key' => $bars_stat_key,
						'order' => 'DESC',
					),
				);
				uasort( $player_list_data, array( $player_list, 'sort' ) );

				$player_top = array_slice( $player_list_data, 0, 1, true );
				$player_top = current( $player_top );
				$player_top_value = isset( $player_top[ $bars_stat_key ] ) ? $player_top[ $bars_stat_key ] : 0;

				if ( isset( $data[ $bars_stat_key ] ) ) {
					if ( $player_top_value > 0 ) {
						$bar_percent = $data[ $bars_stat_key ] / $player_top_value * 100;
					} else {
						$bar_percent = 0;
					}
					$bar_value = $data[ $bars_stat_key ];
				} else {
					$bar_percent = 0;
					$bar_value   = esc_html__( 'n/a', 'alchemists' );
				}
				?>
				<!-- Progress: <?php echo esc_html( $excerpt ); ?> -->
				<div class="progress-stats progress-stats--top-labels">
					<div class="progress__label"><?php echo esc_html( $excerpt ); ?></div>
					<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
						<div class="<?php echo esc_attr( implode( ' ', $progress_bar_classes ) ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $bar_percent ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $bar_percent ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?> "></div>
					</div>
					<div class="progress__number">
						<span class="progress__number-value"><?php echo esc_html( $bar_value ); ?></span><?php echo wp_kses_post( $bars_format === 'percentage' ? '<span class="progress__number-symbol">%</span>' : ''); ?>
					</div>
				</div>
				<!-- Progress: <?php echo esc_html( $excerpt ); ?> / End -->
				<?php

			endforeach;

		else :

			// Player List is not selected, display default percentage values
			foreach ( $bars_stats as $bars_stat_key => $bars_stat_value ) :

				$excerpt = $bars_stat_value;
				$value   = isset( $data[ $bars_stat_key ] ) ? $data[ $bars_stat_key ] : 0;

				if ( $value > 10) {
					$value = round( $value, 0 );
				}
				?>
				<!-- Progress: <?php echo esc_html( $excerpt ); ?> -->
				<div class="progress-stats progress-stats--top-labels">
					<div class="progress__label"><?php echo esc_html( $excerpt ); ?></div>
					<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
						<div class="<?php echo esc_attr( implode( ' ', $progress_bar_classes ) ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $value ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $value ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?> "></div>
					</div>
					<div class="progress__number">
						<span class="progress__number-value"><?php echo esc_html( $value ); ?></span><span class="progress__number-symbol">%</span>
					</div>
				</div>
				<!-- Progress: <?php echo esc_html( $excerpt ); ?> / End -->

				<?php
			endforeach;
		endif;
		?>

	</div>
</div>
<!-- Player Stats / End -->
