<?php
/**
 * Event Logos Inline
 *
 * @author    ThemeBoy
 * @package   SportsPress/Templates
 * @version   2.6
 */

if ( get_post_status( $id ) == 'publish') {
	$game_title = esc_html__( 'Recap', 'alchemists' );
} else {
	$game_title = esc_html__( 'Preview', 'alchemists' );
}

$permalink      = get_post_permalink( $id, false, true );
$results        = get_post_meta( $id, 'sp_results', true );
$primary_result = alchemists_sportspress_primary_result();
$teams          = array_unique( get_post_meta( $id, 'sp_team' ) );
$teams          = array_filter( $teams, 'sp_filter_positive' );

if ( count( $teams ) > 1 ) {
	$team1 = $teams[0];
	$team2 = $teams[1];
}

$venue1_desc = wp_get_post_terms( $team1, 'sp_venue' );
$venue2_desc = wp_get_post_terms( $team2, 'sp_venue' );

$alc_sports_args = alchemists_sp_preset_options();
$alc_sport = $alc_sports_args['preset'];

include( locate_template( 'sportspress/event/event-logos/' . $alc_sport . '-event-logos-inline.php' ) );
