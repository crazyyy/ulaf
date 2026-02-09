<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $caption
 * @var $calendar
 * @var $player_id
 * @var $player_id_on
 * @var $player_id_num
 * @var $customize_player_stats
 * @var $values_primary
 * @var $only_played
 * @var $number
 * @var $order
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Player_Stats_Graph_Log
 */

$caption = $calendar = $player_id = $player_id_on = $player_id_num = $customize_player_stats = $values_primary = $only_played = $number = $order = $show_all_events_link = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

wp_enqueue_script( 'alchemists-chartjs' );

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

$alchemists_data = get_option( 'alchemists_data' );

// Chart colors
$grid_lines_color = '';
$grid_txt_color = '';
if ( alchemists_sp_preset( 'basketball' ) ) {
	$grid_lines_color = isset( $alchemists_data[ 'alchemists__card-border-color' ] ) && !empty( $alchemists_data[ 'alchemists__card-border-color' ] ) ? $alchemists_data[ 'alchemists__card-border-color' ] : '#e4e7ed';
	$grid_txt_color   = '#9a9da2';
} elseif ( alchemists_sp_preset( 'soccer' ) ) {
	$grid_lines_color = isset( $alchemists_data[ 'alchemists__card-border-color' ] ) && !empty( $alchemists_data[ 'alchemists__card-border-color' ] ) ? $alchemists_data[ 'alchemists__card-border-color' ] : '#e4e7ed';
	$grid_txt_color   = '#9a9da2';
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$grid_lines_color = isset( $alchemists_data[ 'alchemists__card-border-color' ] ) && !empty( $alchemists_data[ 'alchemists__card-border-color' ] ) ? $alchemists_data[ 'alchemists__card-border-color' ] : '#3c3b5b';
	$grid_txt_color   = '#9e9caa';
} elseif ( alchemists_sp_preset( 'esports' ) ) {
	$grid_lines_color = isset( $alchemists_data[ 'alchemists__card-border-color' ] ) && !empty( $alchemists_data[ 'alchemists__card-border-color' ] ) ? $alchemists_data[ 'alchemists__card-border-color' ] : '#3f3251';
	$grid_txt_color   = '#8c8297';
}

// Customized Statistics (primary - numbers)
$values_primary = (array) vc_param_group_parse_atts( $values_primary );
$player_stats = array();
foreach ($values_primary as $value) {
	$custom_stat = $value;
	$custom_stat['stat_heading'] = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
	$custom_stat['stat_color']   = isset( $value['stat_color'] ) ? $value['stat_color'] : '';
	$custom_stat['stat_value']   = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

	$player_stats[] = $custom_stat;
}

// Get player's current team
$teams = get_post_meta( $player_id, 'sp_current_team' );
$team = 'default';
if ( !empty( $teams[0]) ) {
	$team = $teams[0];
}

sp_get_template( 'player-event-graph-log.php', array(
	'id'                   => $calendar,
	'title'                => $title,
	'status'               => 'publish',
	'date'                 => 'default',
	'date_from'            => 'default',
	'date_to'              => 'default',
	'day'                  => 'default',
	'number'               => $number,
	'order'                => $order,
	'team'                 => $team,
	'player_id'            => $player_id,
	'player_stats'         => $player_stats,
	'customize_player_stats' => $customize_player_stats,
	'only_played'          => $only_played,
	'grid_lines_color'     => $grid_lines_color,
	'grid_txt_color'       => $grid_txt_color,
));
