<?php
/**
 * Player Gallery Thumbnail
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   2.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'team_color_primary' => null,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'squad_number' => 1
);

extract( $defaults, EXTR_SKIP );

// Player Number
$player_number = get_post_meta( $id, 'sp_number', true );
if ( '' == $player_number ) {
	$player_number = "-";
}

// Player Position
$player_position = get_the_term_list( $id, 'sp_position', '', ', ', '');

$caption_class = 'team-roster__member-name--no-link';
if ( $link_posts ) {
	$caption = '<a href="' . get_permalink( $id ) . '">' . $caption . '</a>';
	$caption_class = 'team-roster__member-name--has-link';
}

if ( has_post_thumbnail( $id ) ) {
	$thumbnail = get_the_post_thumbnail( $id, 'alchemists_thumbnail-player-lg' );
} else {
	$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-380x570.jpg' ) . '" alt="">';
}

echo "<{$itemtag} class='team-roster__item'>";
	echo "<div class='team-roster__holder'>";
		echo "
			<{$icontag} class='team-roster__img'>"
				. '<a href="' . get_permalink( $id ) . '">' . $thumbnail . '</a>'
			. "</{$icontag}>";
		echo "
			<div class='team-roster__content'>"
				. "<div class='team-roster__content-inner'>";
					if ( $squad_number ) {
						echo "<div class='team-roster__member-number'>" . $player_number . "</div>";
					}
					echo "<div class='team-roster__member-info'>"
						. "<h2 class='team-roster__member-name " . esc_attr( $caption_class ) . "'>" . $caption . "</h2>"
						. "<span class='team-roster__member-position'>" . strip_tags( $player_position ) . "</span>"
					. "</div>"
				. "</div>"
			. "</div>";
		echo "<a href='" . get_permalink( $id ) ."' class='btn-fab'></a>";
	echo "</div>";
echo "</{$itemtag}>";
