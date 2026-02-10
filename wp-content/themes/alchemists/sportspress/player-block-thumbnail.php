<?php
/**
 * Player Block Thumbnail
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.2.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'link_posts' => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'show_age' => get_option( 'sportspress_player_show_age', 'yes' ) == 'yes' ? true : false,
	'show_birthday' => get_option( 'sportspress_player_show_birthday', 'yes' ) == 'yes' ? true : false,
	'show_nationality' => get_option( 'sportspress_player_show_nationality', 'yes' ) == 'yes' ? true : false,
	'show_nationality_flags' => get_option( 'sportspress_player_show_flags', 'yes' ) == 'yes' ? true : false,
	'squad_number' => 1,
	'metric_custom' => $metric_custom,
	'age_display'         => true,
	'nationality_display' => true,
	'nationality_flags_display' => true,
	'metrics_customize' => false
);

extract( $defaults, EXTR_SKIP );

if ( $age_display != 'default' ) {
	$show_age = $age_display == 'show' ? true : false;
}

if ( $nationality_display != 'default' ) {
	$show_nationality = $nationality_display == 'show' ? true : false;
}

if ( $nationality_flags_display != 'default' ) {
	$show_nationality_flags = $nationality_flags_display == 'show' ? true : false;
}

// Player Number
$player_number = get_post_meta( $id, 'sp_number', true );
if ( '' == $player_number ) {
	$player_number = "-";
}

$countries = SP()->countries->countries;

$player = new SP_Player( $id );

// Player Position
$player_position = get_the_term_list( $id, 'sp_position', '', ', ', '');

$caption_class = 'team-roster__member-name--no-link';

// Get used metrics
$player_metrics = (array)get_post_meta( $id, 'sp_metrics', true );

// Player Metrics
$player_metrics_array = array();
if ( ! empty( $metric_custom ) && $metrics_customize ) {
	// custom
	$player_metrics_array = $metric_custom;
} elseif ( empty( $metric_custom ) && $metrics_customize ) {
	$player_metrics_array = array();
} else {
	// default
	$player_metrics_array = array(
		'height' => esc_html__( 'Height', 'alchemists' ),
		'weight' => esc_html__( 'Weight', 'alchemists' ),
	);
}

if ( has_post_thumbnail( $id ) ) {
	$thumbnail = get_the_post_thumbnail( $id, 'alchemists_thumbnail-player-block' );
} else {
	$thumbnail = '<img src="' . get_theme_file_uri( '/assets/images/placeholder-140x210.jpg' ) . '" alt="">';
}

echo "<{$itemtag} class='team-roster__item'>";
	echo "<div class='team-roster__holder'>";
		echo "
			<{$icontag} class='team-roster__img'>"
				. '<a href="' . get_permalink( $id ) . '">' . $thumbnail
					. '<span class="btn-fab"></span>'
				. '</a>'
			. "</{$icontag}>";
		echo "
			<div class='team-roster__content'>"
				. "<header class='team-roster__member-header'>";
						if ( $squad_number ) {
							echo "<div class='team-roster__member-number'>" . $player_number . "</div>";
						}
						echo "<h2 class='team-roster__member-name " . esc_attr( $caption_class ) . "'>" . $caption . "</h2>"
				. "</header>"
				. "<div class='team-roster__member-subheader'>"
					. "<div class='team-roster__member-position'>" . strip_tags( $player_position ) . "</div>"
				. "</div>"
				. "<ul class='team-roster__member-details list-unstyled'>";
						foreach ( $player_metrics_array as $player_metric_key => $player_metric_value ) {
							if ( isset( $player_metrics[ $player_metric_key ] ) && ! empty( $player_metrics[ $player_metric_key ] ) ) {
								echo '<li class="team-roster__member-details-item team-roster__member-details-item--' . esc_attr( strtolower( $player_metric_value ) ). '"><span class="item-title">' . $player_metric_value . '</span> <span class="item-desc">' . $player_metrics[ $player_metric_key ] . '</span></li>';
							}
						}
						if ( $show_birthday ) {
							echo "<li class='team-roster__member-details-item team-roster__member-details-item--birthday'><span class='item-title'>" . esc_html__( 'Birthday', 'alchemists' ) . "</span> <span class='item-desc'>" . get_the_date( get_option( 'date_format' ), $id ) . "</span></li>";
						}
						if ( $show_age ) {
							echo "<li class='team-roster__member-details-item team-roster__member-details-item--age'><span class='item-title'>" . esc_html__( 'Age', 'alchemists' ) . "</span> <span class='item-desc'>" . esc_html( alchemists_get_age( get_the_date( 'm-d-Y', $id ) ) ) . "</span></li>";
						}
						if ( $show_nationality ):
							echo '<li class="team-roster__member-details-item team-roster__member-details-item--nationality">';
							echo '<span class="item-title">' . esc_html__( 'Nationality', 'alchemists' ) . '</span>' . '&nbsp;';

							$nationalities = $player->nationalities();
							if ( $nationalities && is_array( $nationalities ) ) {
								$values = array();
								foreach ( $nationalities as $nationality ):
									$country_name = sp_array_value( $countries, $nationality, null );
									$values[] = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" class="item-title-flag" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
								endforeach;
								$country_names_string = implode( ', ', $values );
								echo wp_kses_post( $country_names_string );
							} else {
								echo esc_html__( 'n/a', 'alchemists' );
							}

							echo '</li>';
						endif;
				echo "</ul>"
			. "</div>";
		echo "<a href='" . get_permalink( $id ) ."' class='btn-fab'></a>";
	echo "</div>";
echo "</{$itemtag}>";
