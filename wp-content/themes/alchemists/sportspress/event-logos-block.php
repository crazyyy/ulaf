<?php
/**
 * Event Logos Block
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   2.7.1
 */

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

// Progress Bars
$event_progress_bars_stats_enable = isset( $alchemists_data['alchemists__player-sp-event-progress-bars'] ) ? $alchemists_data['alchemists__player-sp-event-progress-bars'] : 0;
$event_progress_bars_stats_custom = isset( $alchemists_data['alchemists__player-sp-event-progress-bars-custom'] ) ? $alchemists_data['alchemists__player-sp-event-progress-bars-custom'] : array();

$show_progress_bars = true;
if ( 1 == $event_progress_bars_stats_enable && empty( $event_progress_bars_stats_custom ) ) {
	$show_progress_bars = false;
}

// Circular Bars
$event_circular_bars_stats_enable = isset( $alchemists_data['alchemists__player-sp-event-circular-bars'] ) ? $alchemists_data['alchemists__player-sp-event-circular-bars'] : 0;
$event_circular_bars_stats_custom = isset( $alchemists_data['alchemists__player-sp-event-circular-bars-custom'] ) ? $alchemists_data['alchemists__player-sp-event-circular-bars-custom'] : array();
if ( alchemists_sp_preset( 'football' ) || alchemists_sp_preset( 'esports' ) ) {
	$event_circular_bars_stats_format_default = 'number';
} else {
	$event_circular_bars_stats_format_default = 'percentage';
}
$event_circular_bars_stats_format = ( isset( $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] ) && ! empty( $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] ) ) ? $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] : $event_circular_bars_stats_format_default;

$show_circular_bars = true;
if ( 1 == $event_circular_bars_stats_enable && empty( $event_circular_bars_stats_custom ) ) {
	$show_circular_bars = false;
}

// Game Stats
$event_stats_table_enable = isset( $alchemists_data['alchemists__player-sp-event-game-stats'] ) ? $alchemists_data['alchemists__player-sp-event-game-stats'] : 0;
$event_stats_table_custom = isset( $alchemists_data['alchemists__player-sp-event-game-stats-custom'] ) ? $alchemists_data['alchemists__player-sp-event-game-stats-custom'] : array();
if ( alchemists_sp_preset( 'football' ) ) {
	$event_stats_table_title_default = esc_html__( 'Matchup', 'alchemists' );
} else {
	$event_stats_table_title_default = esc_html__( 'Game Statistics', 'alchemists' );
}
$event_stats_table_title  = ( isset( $alchemists_data['alchemists__player-sp-event-game-stats-title'] ) && ! empty( $alchemists_data['alchemists__player-sp-event-game-stats-title'] ) ) ? $alchemists_data['alchemists__player-sp-event-game-stats-title'] : $event_stats_table_title_default;

// Timeline
$event_timeline      = isset( $alchemists_data['alchemists__player-sp-event-timeline'] ) ? $alchemists_data['alchemists__player-sp-event-timeline'] : 1;
$event_timeline_type = isset( $alchemists_data['alchemists__player-sp-event-game-timeline-type'] ) ? $alchemists_data['alchemists__player-sp-event-game-timeline-type'] : 'horizontal';

// Custom Progress Bars
$event_progress_bars_stats_custom_array = array();
$event_progress_bars_stats_custom_array = alchemists_sp_get_performances_array( $event_progress_bars_stats_custom, $event_progress_bars_stats_custom_array );

// Custom Circular Bars
$event_circular_bars_stats_custom_array = array();
$event_circular_bars_stats_custom_array = alchemists_sp_get_performances_array( $event_circular_bars_stats_custom, $event_circular_bars_stats_custom_array );

// Custom Stats Table
$event_stats_table_custom_array = array();
$event_stats_table_custom_array = alchemists_sp_get_performances_array( $event_stats_table_custom, $event_stats_table_custom_array );


if ( get_post_status( $id ) == 'publish') {
	$game_title = esc_html__( 'Recap', 'alchemists' );
} else {
	$game_title = esc_html__( 'Preview', 'alchemists' );
}

$defaults = array(
	'abbreviate_teams' => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false,
	'link_teams'       => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );

// Results
$event_obj      = new SP_Event( $id );
$permalink      = get_post_permalink( $id, false, true );
$results        = $event_obj->results();
$primary_result = alchemists_sportspress_primary_result();
$teams          = array_unique( get_post_meta( $id, 'sp_team' ) );
$teams          = array_filter( $teams, 'sp_filter_positive' );

// The first row should be column labels
$labels = $results[0];

// Remove the first row to leave us with the actual data
unset( $results[0] );

$results = array_filter( $results );

$team1 = null;
$team2 = null;

if ( count( $teams ) > 1) {
	$team1 = $teams[0];
	$team2 = $teams[1];
}

// Venue Description
$venue1_desc = wp_get_post_terms( $team1, 'sp_venue' );
$venue2_desc = wp_get_post_terms( $team2, 'sp_venue' );

$team1_color_primary   = get_field( 'team_color_primary', $team1 );
$team1_color_secondary = get_field( 'team_color_secondary', $team1 );
$team2_color_primary   = get_field( 'team_color_primary', $team2 );
$team2_color_secondary = get_field( 'team_color_secondary', $team2 );

if ( $team1_color_primary ) {
	$color_primary = $team1_color_primary;
} else {
	$color_primary = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}

// 1st Team Color
$color_team_1_bar_output = '';
$color_team_1_progress_bar_output = '';
if ( alchemists_sp_preset( 'esports' ) ) {
	$color_team_1_bar_output = 'data-track-color=' . $color_primary;
} else {
	$color_team_1_bar_output = 'data-bar-color=' . $color_primary;
}

if ( alchemists_sp_preset( 'football' ) ) {
	$color_team_1_progress_bar_output = 'background-image: radial-gradient(circle, ' . $color_primary . ', ' . $color_primary . ' 2px, transparent 2px, transparent), radial-gradient(circle, ' . $color_primary . ', ' . $color_primary . ' 2px, transparent 2px, transparent), linear-gradient(to right, ' . $color_primary . ', ' . $color_primary . ' 4px, transparent 4px, transparent 8px);';
} else {
	$color_team_1_progress_bar_output = 'background-color:' . $color_primary;
}

// 2nd Team Color
$color_team_2_bar_output = '';
$color_team_2 = '';
if ( $team2_color_primary ) {
	$color_team_2 = $team2_color_primary;
	$color_team_2_bar_output = 'data-bar-color=' . $team2_color_primary .'';
} else {
	if ( alchemists_sp_preset( 'football') ) {
		$color_team_2_bar_output = 'data-bar-color=#aaf20e';
	} elseif ( alchemists_sp_preset( 'soccer') ) {
		$color_team_2_bar_output = 'data-bar-color=#9fe900';
	} else {
		$color_team_2_bar_output = 'data-bar-color=#0cb2e2';
	}
}

$color_team_2_progress_bar_output = '';
if ( $color_team_2 ) {
	$color_team_2_bar_output = 'data-bar-color=' . $color_team_2;

	if ( alchemists_sp_preset( 'football' ) ) {
		$color_team_2_progress_bar_output = 'background-image: radial-gradient(circle, ' . $color_team_2 . ', ' . $color_team_2 . ' 2px, transparent 2px, transparent), radial-gradient(circle, ' . $color_team_2 . ', ' . $color_team_2 . ' 2px, transparent 2px, transparent), linear-gradient(to right, ' . $color_team_2 . ', ' . $color_team_2 . ' 4px, transparent 4px, transparent 8px);';
	} else {
		$color_team_2_progress_bar_output = 'background-color:' . $color_team_2;
	}
}

$stats_default = array();

// Sports specifics
if ( alchemists_sp_preset( 'soccer') ) {

	// Soccer

	// Default Event Stats
	$stats_default = array(
		'progress_bars'  => array( 'sh', 'f', 'off' ),
		'event_percents' => array( 'shpercent', 'passpercent' ),
		'event_stats'    => array( 'sh', 'sog', 'ck', 's', 'yellowcards', 'redcards' )
	);

	// Stats
	$event_stats               = $stats_default['event_stats'];
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

	$second_team_circular_bar_color = ( isset( $alchemists_data['color-4-darken'] ) && ! empty( $alchemists_data['color-4-darken'] ) ) ? $alchemists_data['color-4-darken'] : '#9fe900';

} elseif ( alchemists_sp_preset( 'football') ) {

	// American Football

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'att', 'yds', 'rec' ),
		'event_percents' => array( 'comp', 'recyds', 'int' ),
		'event_stats'    => array( 'yds', 'td', 'lng', 'fum', 'lost' )
	);

	// Stats
	$event_stats               = $stats_default['event_stats'];
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

} elseif ( alchemists_sp_preset( 'esports') ) {

	// eSports

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'kills', 'deaths', 'assists', 'gold' ),
		'event_percents' => array( 'kills', 'deaths', 'assists', 'gold', 'cs', 'dmg' ),
	);

	// Stats
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

} else {

	// Basketball

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'ast', 'reb', 'stl', 'blk', 'pf' ),
		'event_percents' => array( 'fgpercent', 'threeppercent', 'ftpercent' ),
	);

	// Stats
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];
}


// Custom Stats - Progress Bars
if ( $event_progress_bars_stats_enable && $event_progress_bars_stats_custom_array ) {
	$event_progress_bars_stats = $event_progress_bars_stats_custom_array;
}

// Custom Stats - Circular Bars
if ( $event_circular_bars_stats_enable && $event_circular_bars_stats_custom_array ) {
	$event_percents_stats = $event_circular_bars_stats_custom_array;
}

// Custom Stats - Stats Table
if ( $event_stats_table_enable && $event_stats_table_custom_array ) {
	$event_stats = $event_stats_table_custom_array;
}

// Show stats
$show_stats = false;
if ( ! empty( $results ) ) :
	if ( ! empty( $results[ $team1 ] ) && ! empty( $results[ $team2 ] ) ) :
		if ( isset($results[ $team1 ]['outcome'] ) && isset( $results[ $team2 ]['outcome'] ) ) :
			$show_stats = true;
		endif;
	endif;
endif;


$alc_sports_args = alchemists_sp_preset_options();
$alc_sport = $alc_sports_args['preset'];

include( locate_template( 'sportspress/event/event-logos/' . $alc_sport . '-event-logos-block.php' ) );
