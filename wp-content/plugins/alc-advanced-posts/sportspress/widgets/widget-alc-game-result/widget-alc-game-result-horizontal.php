<?php
/**
 * Widget Game Result template - horizontal
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.0
 * @version   4.0.0
 */

echo '<div class="widget-game-result__team widget-game-result__team--' . ( $j % 2 ? 'odd' : 'even' ) . '">';
	echo '<figure class="widget-game-result__team-logo">';
		if ( has_post_thumbnail ( $team ) ):
			echo get_the_post_thumbnail( $team, $team_img_size );
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
echo '</div>';
