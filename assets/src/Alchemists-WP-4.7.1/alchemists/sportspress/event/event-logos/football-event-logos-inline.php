<?php
/**
 * The template for displaying Event Score (Inline) on Single Event page (American Football)
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.2.10
 * @version   4.7.0
 */
?>


<div class="card" itemscope itemtype="http://schema.org/SportsEvent">
	<div class="card__header">
		<h4 itemprop="name" content="<?php echo esc_attr( get_the_title( $id ) ); ?>"><?php echo esc_html( $game_title ); ?></h4>
	</div>
	<div class="card__content">

		<!-- Game Result -->
		<div class="game-result">

			<section class="game-result__section pt-0">
				<header class="game-result__header game-result__header--alt">

					<?php $leagues = get_the_terms( $id, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
						<span class="game-result__league">
							<?php echo esc_html( $league->name ); ?>

							<?php $seasons = get_the_terms( $id, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons ); ?>
								<?php echo esc_html( $season->name ); ?>
							<?php endif; ?>

						</span>
					<?php endif; ?>

					<?php
					$venues = get_the_terms( $id, 'sp_venue' );
					if ( $venues ) :
						?>

						<h3 class="game-result__title" itemprop="location" itemscope itemtype="http://schema.org/Place">
							<?php
							$venue_names = array();

							foreach ( $venues as $venue ) {
								$t_id = $venue->term_id;
								$t_name = '<span itemprop="name">' . $venue->name . '</span>';
								$meta = get_option( "taxonomy_$t_id" );
								$address = '<span class="d-none" itemprop="address" itemtype="http://schema.org/PostalAddress">' . sp_array_value( $meta, 'sp_address', null ) . '</span>';

								if ( $link_venues ) {
									$t_name = '<a href="'. get_term_link( $t_id, $venue->taxonomy ) .'" itemprop="url">' . $t_name . '</a>';
								}

								$venue_names[] = $t_name . $address;
							}
							echo implode( '/', $venue_names );
							?>
						</h3>

						<?php
					endif;
					?>

					<time class="game-result__date" itemprop="startDate" datetime="<?php echo get_the_time( 'Y-m-d H:i:s' ); ?>">
						<?php
						// Event Date and Time or Time Status (OK, Postponed, TBD, Canceled)
						echo esc_html( get_the_time( sp_date_format(), $id ) ) . alchemists_event_time_status_badge( $id );
						?>
					</time>

					<?php
					if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) :
						$matchday = get_post_meta( $id, 'sp_day', true );
						if ( $matchday != '' ) : ?>
						<div class="game-result__matchday">(<?php echo esc_html( $matchday ); ?>)</div>
						<?php endif;
					endif;
					?>
				</header>

				<!-- Team Logos + Game Result -->
				<div class="game-result__content">
					<?php
					$j = 0;
					foreach( $teams as $team ):
						$j++;

						echo '<div class="game-result__team game-result__team--' . ( $j % 2 ? 'odd' : 'even' ) . '">';
							echo '<figure class="game-result__team-logo">';
								if ( has_post_thumbnail ( $team ) ):

									if ( $link_teams ) :
										echo '<a href="' . get_permalink( $team, false, true ) . '" title="' . get_the_title( $team ) . '">';
											echo get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit' );
										echo '</a>';
									else:
										echo get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit' );
									endif;
								endif;

							echo '</figure>';

							if ( $show_team_names ) {
								echo '<div class="game-result__team-info" itemprop="performer" itemscope itemtype="http://schema.org/Organization">';
									echo '<h5 class="game-result__team-name" itemprop="name">' . esc_html( get_the_title( $team ) ) . '</h5>';
									echo '<div class="game-result__team-desc">';
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
							}
						echo '</div>';

					endforeach;
					?>

					<!-- Game Score -->
					<div class="game-result__score-wrap">
						<div class="game-result__score game-result__score--lg">

							<?php

							$status = esc_html__( 'Preview', 'alchemists' );

							if ( $show_results && ! empty( $results ) ) :

								$status = esc_html__( 'Final Score', 'alchemists' ); ?>

								<?php
								// 1st Team
								$team1_class = 'game-result__score-result--loser';
								if (!empty($results)) {
									if (!empty($results[$team1])) {
										if (isset($results[$team1]['outcome']) && !empty($results[$team1]['outcome'][0])) {
											if ( $results[$team1]['outcome'][0] == 'win' ) {
												$team1_class = 'game-result__score-result--winner';
											}
										}
									}
								}

								// 2nd Team
								$team2_class = 'game-result__score-result--loser';
								if (!empty($results)) {
									if (!empty($results[$team2])) {
										if (isset($results[$team2]['outcome']) && !empty($results[$team2]['outcome'][0])) {
											if ( $results[$team2]['outcome'][0] == 'win' ) {
												$team2_class = 'game-result__score-result--winner';
											}
										}
									}
								}

								?>

								<!-- 1st Team -->
								<span class="game-result__score-result <?php echo esc_attr( $team1_class ); ?>">
									<?php if (!empty($results)) {
										if (!empty($results[$team1]) && !empty($results[$team2])) {
											if (isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result])) {
												echo esc_html( $results[$team1][$primary_result] );
											}
										}
									} ?>
								</span>
								<!-- 1st Team / End -->

								<span class="game-result__score-dash">-</span>

								<!-- 2nd Team -->
								<span class="game-result__score-result <?php echo esc_attr( $team2_class ); ?>">
									<?php if (!empty($results)) {
										if (!empty($results[$team1]) && !empty($results[$team2])) {
											if (isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result])) {
												echo esc_html( $results[$team2][$primary_result] );
											}
										}
									} ?>
								</span>
								<!-- 2nd Team / End -->

						<?php else : ?>

							<span class="game-result__score-dash">&ndash;</span>

						<?php endif; ?>

						</div>

						<div class="game-result__score-label">
							<?php echo apply_filters( 'sportspress_event_logos_status', $status, $id ); ?>
						</div>

					</div>
					<!-- Game Score / End -->

				</div>
				<!-- Team Logos + Game Result / End -->
			</section>

		</div>
		<!-- Game Result / End -->
	</div>
</div>
