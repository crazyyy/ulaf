<?php
/**
 * Player Slider Thumbnail
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// Player Number
$player_number = get_post_meta( $id, 'sp_number', true );
if ( '' == $player_number ) {
	$player_number = "-";
}

// Player Position
$player_position = get_the_term_list( $id, 'sp_position', '', ', ', '');

$caption_class = 'team-roster__player-name--no-link';
if ( $link_posts ) {
	$caption = '<a href="' . get_permalink( $id ) . '">' . $caption . '</a>';
	$caption_class = 'team-roster__player-name--has-link';
}

// Player Image (Alt)
$player_image_head  = get_post_meta( $id, 'heading_player_photo', true );
$player_image_size  = 'alchemists_thumbnail-player';
if( $player_image_head ) {
	$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
} else {
	$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-single-370x400.png' ) . '" alt="" />';
}

echo '<div class="team-roster__item">';

	echo '<figure class="team-roster__img">';
		echo wp_kses_post( $image_url );
		echo '<div class="team-roster__img-ring-top"></div>';
		echo '<div class="team-roster__img-ring-bottom"></div>';
	echo '</figure>';

	echo '<div class="team-roster__player-details">';
		echo '<div class="team-roster__player-number">' . esc_html( $player_number ) . '</div>';
		echo '<div class="team-roster__player-info">';
			echo '<h3 class="team-roster__player-name ' . esc_attr( $caption_class ) . '">' . $caption . '</h3>';
			echo '<div class="team-roster__player-position">' . strip_tags( $player_position ) . '</div>';
		echo '</div>';
	echo '</div>';


	echo '<div class="team-roster__player-fab">';
		echo '<a href="' . get_permalink( $id ) . '" class="team-roster__player-more">';
			echo '<span class="btn-fab btn-fab--sm"></span>';
			echo '<span class="team-roster__player-fab-txt">' . __( 'Check the<br>Player Profile', 'alchemists' ) . '</span>';
		echo '</a>';
	echo '</div>';

echo '</div>';
