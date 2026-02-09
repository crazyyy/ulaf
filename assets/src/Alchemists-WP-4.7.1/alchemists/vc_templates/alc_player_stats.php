<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.5.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $player_id
 * @var $player_id_on
 * @var $player_id_num
 * @var $add_link
 * @var $style_type
 * @var $background_image
 * @var $customize_primary_stats
 * @var $values_primary
 * @var $display_detailed_stats
 * @var $display_detailed_stats_secondary
 * @var $customize_detailed_stats
 * @var $customize_detailed_stats_secondary
 * @var $values
 * @var $values_equation
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Player_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

$title = $player_id = $player_id_on = $player_id_sum = $add_link = $style_type = $background_image = $customize_primary_stats = $display_detailed_stats_secondary = $values_primary = $display_detailed_stats = $customize_detailed_stats_secondary = $customize_detailed_stats = $values = $values_equation = $el_class = $el_id = $css = $css_animation = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget card widget-player ';
if ( $style_type == 'style_2' ) {
	$style_type = ' widget-player--alt ';
	$class_to_filter .= $style_type;
}

if ( alchemists_sp_preset( 'soccer') ) {
	$class_to_filter .= ' widget-player--soccer ';
} elseif ( alchemists_sp_preset( 'football') ) {
	$class_to_filter .= ' widget-player--football ';
} elseif ( alchemists_sp_preset( 'esports') ) {
	$class_to_filter .= ' widget-player--sm ';
}

// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'widget-player-' );
$class_to_filter .= $identifier;

$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$id = $player_id;
// Check if we're on Single Team page and Team is not selected
if ( is_singular('sp_player') && ( $player_id == 'default' || $player_id_on != 1 ) ) {
	$id = get_the_ID();
}

if ( $player_id_on == 1 && ! empty( $player_id_num ) ) {
	$id = $player_id_num;
}

$player = new SP_Player( $id );
if ( is_null( $player->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Player found with Player ID %s', 'alchemists' ), '<strong>' . $id . '</strong>' ). '</div>';
	return;
}

// Customized Statistics (primary - numbers)
$values_primary = (array) vc_param_group_parse_atts( $values_primary );
$values_primary_array = array();
foreach ($values_primary as $value) {
	$custom_stat = $value;
	$custom_stat['stat_heading']    = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
	$custom_stat['stat_subheading'] = isset( $value['stat_subheading'] ) ? $value['stat_subheading'] : '';
	$custom_stat['stat_value']      = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

	$values_primary_array[] = $custom_stat;
}

// Customized Statistics (numbers)
$values = (array) vc_param_group_parse_atts( $values );
$values_array = array();
foreach ($values as $value) {
	$custom_stat = $value;
	$custom_stat['stat_heading']    = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
	$custom_stat['stat_subheading'] = isset( $value['stat_subheading'] ) ? $value['stat_subheading'] : '';
	$custom_stat['stat_value']      = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

	$values_array[] = $custom_stat;
}

// Customized Statistics (equation)
$values_equation = (array) vc_param_group_parse_atts( $values_equation );
$values_equation_array = array();
foreach ($values_equation as $value) {
	$custom_stat = $value;
	$custom_stat['stat_heading']    = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
	$custom_stat['stat_value']      = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

	$values_equation_array[] = $custom_stat;
}

$data = $player->data( 0, false );

// Remove the first row to leave us with the actual data
unset( $data[0] );

$defaults = array(
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );

$performance_desc = esc_html__( 'In career', 'alchemists' );

if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $data[ $current_season ] ) ) {
		$data = $data[ $current_season ];
		$performance_desc = esc_html__( 'In current season', 'alchemists' );
	}
} else {
	if ( isset( $data[-1] )) {
		$data = $data[-1];
	}
}

if ( $style_type != 'style_hide_banner' ) {
	// Player Image (Alt)
	$player_image_head  = get_field('heading_player_photo', $id);
	$player_image_size  = 'alchemists_thumbnail-player-sm';
	if( $player_image_head ) {
		$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
	} else {
		$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-single-placeholder-189x198.png' ) . '" loading="lazy" alt="" />';
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

// Custom styles
$style = '';

if ( alchemists_sp_preset( 'soccer' ) ) {
	include( locate_template( 'vc_templates/alc_player_stats/alc_player_stats-soccer.php' ) );
} elseif ( alchemists_sp_preset( 'football' ) ) {
	include( locate_template( 'vc_templates/alc_player_stats/alc_player_stats-football.php' ) );
} elseif ( alchemists_sp_preset( 'esports' ) ) {
	include( locate_template( 'vc_templates/alc_player_stats/alc_player_stats-esports.php' ) );
} else {
	include( locate_template( 'vc_templates/alc_player_stats/alc_player_stats-basketball.php' ) );
}
