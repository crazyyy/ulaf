<?php
/**
 * Player Slider Thumbnail
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
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

// Player Number
$player_number = get_post_meta( $id, 'sp_number', true );
if ( '' == $player_number ) {
	$player_number = "-";
}

// Player Position
$player_position = strip_tags( get_the_term_list( $id, 'sp_position', '', ', ', '') );

$caption_class = 'team-roster__player-name--no-link';

// Player Image
if ( has_post_thumbnail( $id ) ) {
	$thumbnail = get_the_post_thumbnail( $id, 'alchemists_thumbnail-ver' );
} else {
	$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-380x490.jpg' ) . '" alt="">';
}

// Add links if enabled
if ( $link_posts ) {
	$caption = '<a href="' . get_permalink( $id ) . '">' . $caption . '</a>';
	$caption_class = 'team-roster__player-name--has-link';
	$thumbnail = '<a href="' . get_permalink( $id ) . '">' . $thumbnail . '</a>';
}

// Player Aside Stats
$tdavg = isset( $player_data['tdavg'] ) ? $player_data['tdavg'] : esc_html__( 'n/a', 'alchemists' );
$avg   = isset( $player_data['avg'] ) ? $player_data['avg'] : esc_html__( 'n/a', 'alchemists' );

echo '<div class="team-roster__item card card--no-paddings">';

	echo '<div class="card__content">';
		echo '<div class="team-roster__content-wrapper">';

			echo '<figure class="team-roster__player-img">';
				echo wp_kses_post( $thumbnail );
			echo '</figure>';

			echo '<div class="card__header team-roster__player-details">';
				echo '<div class="team-roster__player-info">';
					echo '<h3 class="team-roster__player-name ' . esc_attr( $caption_class ) . '">' . wp_kses_post( $caption ) . '</h3>';
					echo '<h6 class="team-roster__player-position">' . esc_html( $player_position ) . '</h6>';
				echo '</div>';
			echo '</div>';

			echo '<aside class="team-roster__meta">';

				echo '<div class="team-roster__meta-item">';
					echo '<div class="team-roster__meta-value team-roster__meta-value--accent">' . esc_html( $player_number ) . '</div>';
					echo '<div class="team-roster__meta-label">' . esc_html( 'Number', 'alchemists' ) . '</div>';
				echo '</div>';

				echo '<div class="team-roster__meta-item">';
					echo '<div class="team-roster__meta-value">' . esc_html( $tdavg ) . '</div>';
					echo '<div class="team-roster__meta-label">' . esc_html( 'TD Avg', 'alchemists' ) . '</div>';
				echo '</div>';

				echo '<div class="team-roster__meta-item">';
					echo '<div class="team-roster__meta-value">' . esc_html( $avg ) . '</div>';
					echo '<div class="team-roster__meta-label">' . esc_html( 'Yds per carry', 'alchemists' ) . '</div>';
				echo '</div>';

			echo '</aside>';

		echo '</div>';
	echo '</div>';

echo '</div>';
