<?php
/**
 * Widget Game Result template - vertical
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   3.0.0
 */

$current_team = 'team' . $j;
$current_team_status = $current_team . '_class';

echo '<div class="widget-game-result__team widget-game-result__team--' . ( $j % 2 ? 'odd' : 'even' ) . '">';
	echo '<figure class="widget-game-result__team-logo">';
		if ( has_post_thumbnail ( $team ) ):
			echo get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit' );
		endif;
	echo '</figure>';
	echo '<div class="widget-game-result__team-info">';
		echo '<h5 class="widget-game-result__team-name">' . esc_html( get_the_title( $team ) ) . '</h5>';
		echo '<div class="widget-game-result__team-desc">';
			if ( $j == 1 ) {
				if ( isset( $venue1_desc[0] )) {
					echo esc_html( $venue1_desc[0]->description );
				}
			} elseif ( $j == 2 ) {
				if ( isset( $venue2_desc[0] )) {
					echo esc_html( $venue2_desc[0]->description );
				}
			}
		echo '</div>';
	echo '</div>';
	echo '<div class="widget-game-result__team-score ' . ${"$current_team_status"}. '">';
		if (!empty($results)) {
			if (!empty($results[$team1]) && !empty($results[$team2])) {
				if (isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result])) {
					echo esc_html( $results[${"$current_team"}][$primary_result] );
				}
			}
		}
	echo '</div>';
echo '</div>';
