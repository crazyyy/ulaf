<?php
/**
 * The template for displaying Single Team
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.2.10
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
$results_layout  = isset( $alchemists_data['alchemists__team-results-type'] ) ? $alchemists_data['alchemists__team-results-type'] : 'list';

$team_calendars = get_field( 'team_results_calendar_select' );

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

if ( $team_calendars ) {
	foreach ( $team_calendars as $team_calendar ) {
		sp_get_template( 'event-' . $results_layout . '.php', array(
			'id'   => $team_calendar,
			'team' => $id,
		));
	}
} else {
	sp_get_template( 'event-' . $results_layout . '.php', array(
		'team' => $id,
		'title' => esc_html__( 'Latest Results', 'alchemists' ),
		'order' => 'DESC',
		'status' => 'publish',
		'time_format' => 'results',
		'columns' => array(
			'event',
			'teams',
			'results',
			'league',
			'season',
			'venue',
		),
	));
}
