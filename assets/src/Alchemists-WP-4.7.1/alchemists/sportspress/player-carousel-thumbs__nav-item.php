<?php
/**
 * Player Slider Thumbnail
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id'             => null,
	'link_posts'     => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

$player = new SP_Player( $id );
$player_data = $player->data(0);

unset( $player_data[0] );

if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $player_data[ $current_season ] ) ) {
		$player_data = $player_data[ $current_season ];
	}
} else {
	if ( isset( $player_data[-1] )) {
		$player_data = $player_data[-1];
	}
}

// Player Name
$player_metrics_data = (array)get_post_meta( $id, 'sp_metrics', true );
$player_name = isset( $player_metrics_data['fname'] ) ? $player_metrics_data['fname'] : ''; // get first name based on player metrics

// Position (Character)
$player_terms = get_the_terms( $id, 'sp_position' );
$player_position_id = 0;
$player_position_name = '';
if ( is_array( $player_terms ) && ! empty( $player_terms ) ) {
	$player_position_id   = $player_terms[0]->term_id;
	$player_position_name = $player_terms[0]->name;
}
$player_position_icon = get_term_meta( $player_position_id, 'character_icon', true );

// Player Image (Alt)
$player_image_head  = get_post_meta( $id, 'heading_player_photo', true );
$player_image_size  = 'alchemists_thumbnail-player-block';
if( $player_image_head ) {
	$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
} else {
	$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-140x210.png' ) . '" alt="" />';
}

$output = '<div class="team-roster-nav__item">';
	$output .= '<div class="team-roster-nav__hexagon">';
		$output .= '<figure class="team-roster-nav__img">';
			$output .= wp_kses_post( $image_url );
			$output .= '<span class="team-roster-nav__triangle"></span>';
			$output .= '<span class="team-roster-nav__triangle-border"></span>';

			if ( $player_position_icon ) {
				$output .= '<span class="team-roster-nav__icon">';
					$output .= '<svg role="img" class="df-icon df-icon--' . esc_attr( $player_position_icon ) . '">';
						$output .= '<use xlink:href="' . get_template_directory_uri() . '/assets/images/esports/icons-esports.svg#' . esc_attr( $player_position_icon ) . '"/>';
					$output .= '</svg>';
				$output .= '</span>';
			}
		$output .= '</figure>';
	$output .= '</div>';
	$output .= '<div class="team-roster-nav__meta">';
		if ( ! empty( $player_name ) ) {
			$output .= '<h6 class="team-roster-nav__firstname"> ' . $player_name . ' </h6>';
		}
		$output .= '<h5 class="team-roster-nav__nickname">' . $title . '</h5>';
	$output .= '</div>';
$output .= '</div>';

echo wp_kses_post( $output );
