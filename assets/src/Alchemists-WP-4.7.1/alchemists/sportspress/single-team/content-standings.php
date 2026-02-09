<?php
/**
 * The template for displaying Single Team
 *
 * @package Alchemists
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

$team_leagues = get_field('team_leagues');

$team = new SP_Team( $id );
$tables = $team->tables();

if ( $team_leagues ) {
	// display selected Leagues
	foreach ( $team_leagues as $team_league ) {
		sp_get_template( 'league-table.php', array(
	    'id' => $team_league,
	    'highlight' => $id,
	  ) );
	}
} else {
	// or default
	foreach ( $tables as $table ):
		if ( ! $table ) continue;

		// get League Table ID
		$table_id = $table->ID;

		sp_get_template( 'league-table.php', array(
			'id' => $table_id,
			'highlight' => $id,
		) );

	endforeach;
}
