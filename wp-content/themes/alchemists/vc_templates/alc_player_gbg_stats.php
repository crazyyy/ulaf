<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.15
 *
 * Shortcode attributes
 * @var $atts
 * @var $caption
 * @var $calendar
 * @var $player_id
 * @var $player_id_on
 * @var $player_id_num
 * @var $player_stats
 * @var $only_played
 * @var $number
 * @var $order
 * @var $show_all_events_link
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Player_Gbg_Stats
 */

$caption = $calendar = $player_id = $player_id_on = $player_id_num = $player_stats = $only_played = $number = $order = $show_all_events_link = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

// Hide Show All Events button if all calendars displayed
if ( $calendar == 'all' ) {
	$show_all_events_link = 0;
}

// Check if we're on a Single Player page and Player is not selected
if ( is_singular('sp_player') && ( $player_id == 'default' || $player_id_on != 1 ) ) {
	$player_id = get_the_ID();
}

if ( $player_id_on == 1 && ! empty( $player_id_num ) ) {
	$player_id = $player_id_num;
}

$player = new SP_Player( $player_id );
if ( is_null( $player->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Player found with Player ID %s', 'alchemists' ), '<strong>' . $player_id . '</strong>' ). '</div>';
	return;
}

// Get player's current team(s)
$team = get_post_meta( $player_id, 'sp_current_team' );

sp_get_template( 'player-event-game-by-game.php', array(
	'id'                   => $calendar,
	'title'                => $title,
	'status'               => 'publish',
	'date'                 => 'default',
	'date_from'            => 'default',
	'date_to'              => 'default',
	'day'                  => 'default',
	'number'               => $number,
	'order'                => $order,
	'show_all_events_link' => $show_all_events_link,
	'team'                 => $team,
	'player_id'            => $player_id,
	'player_stats'         => $player_stats,
	'only_played'          => $only_played,
));
