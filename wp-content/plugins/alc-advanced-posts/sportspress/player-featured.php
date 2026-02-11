<?php
/**
 * Featured Player
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.3.0
 * @version   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

// Set defaults and merge with passed variables
$defaults = array(
	'id'             => null,
	'caption'        => false,
	'style_type'     => false,
	'stat_type'      => false,
	'columns'        => null,
	'progress_bars'  => null,
	'add_link'       => false,
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);

// Merge with any passed arguments (for backward compatibility)
if ( isset( $args ) && is_array( $args ) ) {
	extract( array_merge( $defaults, $args ), EXTR_SKIP );
} else {
	extract( $defaults, EXTR_SKIP );
}

$output = '';
$identifier = uniqid( 'widget-player-' );

if ( $style_type == 'style_type2' ) {
	$style_type = 'widget-player--alt';
} else {
	$style_type = '';
}

// Player
$player = new ALC_SP_PlayerWrapper( $id );

// Statistics - explode into array
if ( null !== $columns && ! is_array( $columns ) ) {
	$columns = explode( ',', $columns );
}
if ( isset( $columns ) && null !== $columns ):
	$player->columns = $columns;
endif;

// Progress Bars (statistics) - explode into array
if ( null !== $progress_bars && ! is_array( $progress_bars ) ) {
	$progress_bars = explode( ',', $progress_bars );
}
if ( isset( $progress_bars ) && null !== $progress_bars ):
	$player->columns = $progress_bars;
endif;

$data = $player->data( 0, false );

// The first row should be column labels
$labels = $data[0];

// Remove the first row to leave us with the actual data
unset( $data[0] );

$performance_desc = esc_html__( 'In career', 'alc-advanced-posts' );

// Display stats for Current Season
if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $data[ $current_season ] ) ) {
		$data = $data[ $current_season ];
		$performance_desc = esc_html__( 'In current season', 'alc-advanced-posts' );
	} else {
		return;
	}
} else {
	// or Total
	if ( isset( $data[-1] )) {
		$data = $data[-1];
	}
}

// Get current team
$sp_current_teams = get_post_meta( $id, 'sp_current_team' );
$sp_current_team = '';
if ( ! empty( $sp_current_teams[0] ) ) {
	$sp_current_team = $sp_current_teams[0];
}

// get Team colors for player
$team_color_primary   = get_field( 'team_color_primary', $sp_current_team );
$team_color_secondary = get_field( 'team_color_secondary', $sp_current_team );
$team_color_heading   = get_field( 'team_color_heading', $sp_current_team );

if ( $team_color_primary ) {
	$color_primary   = $team_color_primary;
} else {
	$color_primary   = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}


$player_template_slug = 'default';

if ( alchemists_sp_preset( 'soccer') ) {
	$player_template_slug = 'soccer';
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$player_template_slug = 'football';
}

// Include template
include( ALCADVPOSTS_PLUGIN_DIR . "/sportspress/widgets/widget-alc-featured-player/$player_template_slug-player-featured.php" );

echo $output;
