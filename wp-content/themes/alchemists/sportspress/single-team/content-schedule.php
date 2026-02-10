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
$schedule_layout = isset( $alchemists_data['alchemists__team-schedule-type'] ) ? $alchemists_data['alchemists__team-schedule-type'] : 'list';

$team_calendars = get_field( 'team_schedule_calendar_select' );

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

if ( $team_calendars ) {
	foreach ( $team_calendars as $team_calendar ) {
		sp_get_template( 'event-' . $schedule_layout . '.php', array(
			'id'   => $team_calendar,
			'team' => $id,
		));
	}
} else {
	sp_get_template( 'event-' . $schedule_layout . '.php', array(
		'team' => $id,
		'title' => esc_html__( 'Schedule', 'alchemists' ),
		'order' => 'ASC',
		'time_format' => 'time',
		'status' => 'future',
		'columns' => array(
			'event',
			'teams',
			'time',
			'league',
			'season',
			'venue',
		),
	));
}
