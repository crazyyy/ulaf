<?php
/**
 * The template for displaying Event Score (Block) on Single Event page (Basketball)
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

			<section class="game-result__section">
				<header class="game-result__header">

					<?php $leagues = get_the_terms( $id, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
						<h3 class="game-result__title">
							<?php echo esc_html( $league->name ); ?>

							<?php $seasons = get_the_terms( $id, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons ); ?>
								<?php echo esc_html( $season->name ); ?>
							<?php endif; ?>

						</h3>
					<?php endif; ?>

					<?php
					$venues = get_the_terms( $id, 'sp_venue' );
					if ( $venues ) :
						?>

						<h6 class="d-none" itemprop="location" itemscope itemtype="http://schema.org/Place">
							<?php
							$venue_names = array();

							foreach ( $venues as $venue ) {
								$t_id = $venue->term_id;
								$t_name = '<span itemprop="name">' . $venue->name . '</span>';
								$meta = get_option( "taxonomy_$t_id" );
								$address = '<span itemprop="address" itemtype="http://schema.org/PostalAddress">' . sp_array_value( $meta, 'sp_address', null ) . '</span>';

								if ( $link_venues ) {
									$t_name = '<a href="'. get_term_link( $t_id, $venue->taxonomy ) .'" itemprop="url">' . $t_name . '</a>';
								}

								$venue_names[] = $t_name . $address;
							}
							echo implode( '/', $venue_names );
							?>
						</h6>

						<?php
					endif;
					?>

					<time class="game-result__date" itemprop="startDate" datetime="<?php echo get_the_time( 'Y-m-d H:i:s', $id ); ?>">
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
											echo get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' );
										echo '</a>';
									else:
										echo get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' );
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
						<div class="game-result__score">

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

				<?php if ( $show_stats ) : ?>

					<!-- Game Stats -->
					<div class="game-result__stats">
						<div class="row">
							<div class="<?php echo esc_attr( $show_progress_bars ? 'col-12 col-lg-6 order-lg-2' : 'col' ); ?> game-result__stats-scoreboard">
								<div class="game-result__table-stats">
									<?php echo alchemists_sp_event_results( $results, $labels ); ?>
								</div>
							</div>

							<?php
							// Get Performance
							$event_performance = sp_get_performance( $id );

							// Remove the first row to leave us with the actual data
							unset( $event_performance[0] );

							// Player Performance
							$performances_posts = get_posts(array(
								'post_type'      => 'sp_performance',
								'posts_per_page' => 9999,
								'orderby'        => 'menu_order',
								'order'          => 'DESC'
							));

							$performances_posts_array = array();
							if($performances_posts){
								foreach($performances_posts as $performance_post){
									$performances_posts_array[$performance_post->post_name] = array(
										'label'   => $performance_post->post_title,
										'value'   => $performance_post->post_name,
										'excerpt' => $performance_post->post_excerpt
									);
								}
								wp_reset_postdata();
							}

							// Custom Stats
							$event_stats_array = array();
							$event_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_progress_bars_stats );
							?>

							<?php
							if ( $show_progress_bars ) :
								// 1st Team
								?>
								<div class="col-6 col-lg-3 order-lg-1 game-result__stats-team-1">

									<table class="progress-table progress-table--xs-space progress-table--fullwidth progress-table--bar-fullwidth">
										<tbody>
											<?php
											foreach ( $event_stats_array as $event_stat_bar_key => $event_stat_bar_label ) :

												// Event Stats
												// must get the first array for Player-vs-Player mode compatibility
												$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
												$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
												$event_team1_stat = alchemists_check_exists_not_empty( $event_performance[ $team1 ][ $event_team1_first_array ][ $event_stat_bar_key ] );
												$event_team2_stat = alchemists_check_exists_not_empty( $event_performance[ $team2 ][ $event_team2_first_array ][ $event_stat_bar_key ] );
												$event_team1_stat_pct = alchemists_sp_performance_percent( $event_team1_stat, $event_team2_stat );
												?>

												<tr>
													<td class="progress-table__progress-label progress-table__progress-label--highlight">
														<?php echo esc_html( $event_stat_bar_label['label'] ); ?>
													</td>
													<td class="progress-table__progress-bar">
														<div class="progress">
															<div class="progress__bar" style="width: <?php echo esc_attr( $event_team1_stat_pct ); ?>%; <?php echo esc_attr( $color_team_1_progress_bar_output ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team1_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
														</div>
													</td>
													<td class="progress-table__progress-label font-weight-normal">
														<?php echo esc_html( $event_team1_stat ); ?>
													</td>
												</tr>

												<?php
											endforeach;
											?>
										</tbody>
									</table>

								</div>
								<?php
								// 2nd Team
								?>
								<div class="col-6 col-lg-3 order-lg-3 game-result__stats-team-2">

									<table class="progress-table progress-table--xs-space progress-table--fullwidth progress-table--bar-fullwidth">
										<tbody>
											<?php
											foreach ( $event_stats_array as $event_stat_bar_key => $event_stat_bar_label ) :

												// Event Stats
												// must get the first array for Player-vs-Player mode compatibility
												$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
												$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
												$event_team1_stat = alchemists_check_exists_not_empty( $event_performance[ $team1 ][ $event_team1_first_array ][ $event_stat_bar_key ] );
												$event_team2_stat = alchemists_check_exists_not_empty( $event_performance[ $team2 ][ $event_team2_first_array ][ $event_stat_bar_key ] );

												$event_team2_stat_pct = alchemists_sp_performance_percent( $event_team2_stat, $event_team1_stat );
												?>

												<tr>
													<td class="progress-table__progress-label font-weight-normal">
														<?php echo esc_html( $event_team2_stat ); ?>
													</td>
													<td class="progress-table__progress-bar progress-table__progress-bar--inverse">
														<div class="progress">
															<div class="progress__bar progress__bar--info" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo esc_attr( $color_team_2_progress_bar_output ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
														</div>
													</td>
													<td class="progress-table__progress-label progress-table__progress-label--highlight">
														<?php echo esc_html( $event_stat_bar_label['label'] ); ?>
													</td>
												</tr>

												<?php
											endforeach;
											?>
										</tbody>
									</table>

								</div>
								<?php
							endif;
							?>

						</div>
					</div>
					<!-- Game Stats / End -->

				<?php endif; ?>
			</section>


			<?php
			if ( $show_stats && $show_circular_bars ) :
			?>
				<!-- Game Percentage -->
				<section class="game-result__section">
					<header class="game-result__subheader card__subheader">
						<h5 class="game-result__subtitle"><?php echo esc_html( $event_stats_table_title ); ?></h5>
					</header>
					<div class="game-result__content-alt mb-0">
						<div class="row">
							<div class="col-12 col-lg-6">
								<div class="row">

									<?php
									// Percentage
									$game_stats_percentage_array = array();
									$game_stats_percentage_array = alchemists_sp_filter_array( $performances_posts_array, $event_percents_stats );
									?>

									<?php // 1st Team
									foreach ( $game_stats_percentage_array as $event_percent_key => $event_percent_label ) :

										// Performance - Value
										// must get the first array for Player-vs-Player mode compatibility
										$event_team1_first_array = key( $event_performance[ $team1 ] );
										$event_team2_first_array = key( $event_performance[ $team2 ] );
										$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][ $event_team1_first_array ][ $event_percent_key ] );
										$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][ $event_team2_first_array ][ $event_percent_key ] );

										// Performance - Percent
										$event_team1_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team1_value, $event_team2_value );
										?>

										<div class="col-4">
											<div class="circular">
												<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team1_percent ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
													<span class="circular__percents"><?php echo esc_html( $event_team1_value ); ?><?php echo wp_kses_post( ( $event_circular_bars_stats_format == 'percentage' ) ? '<small>%</small>' : '' ); ?></span>
												</div>
												<span class="circular__label"><?php echo esc_html( $event_percent_label['excerpt'] ); ?></span>
											</div>
										</div>

									<?php endforeach; ?>

								</div>
							</div>
							<div class="col-12 col-lg-6">
								<div class="row">

									<?php // 2nd Team
									foreach ( $game_stats_percentage_array as $game_percent_key => $game_percent_label ) :

										// Performance - Value
										// must get the first array for Player-vs-Player mode compatibility
										$event_team1_first_array = key( $event_performance[ $team1 ] );
										$event_team2_first_array = key( $event_performance[ $team2 ] );
										$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][ $event_team1_first_array ][ $game_percent_key ] );
										$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][ $event_team2_first_array ][ $game_percent_key ] );

										// Performance - Percent
										$event_team2_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team2_value, $event_team1_value );
										?>

										<div class="col-4">
											<div class="circular">
												<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team2_percent ); ?>" <?php echo esc_attr( $color_team_2_bar_output ); ?>>
													<span class="circular__percents"><?php echo esc_html( $event_team2_value ); ?><?php echo wp_kses_post( ( $event_circular_bars_stats_format == 'percentage' ) ? '<small>%</small>' : '' ); ?></span>
												</div>
												<span class="circular__label"><?php echo esc_html( $game_percent_label['excerpt'] ); ?></span>
											</div>
										</div>

									<?php endforeach; ?>

								</div>
							</div>
						</div>
					</div>
				</section>
				<!-- Game Percentage / End -->
			<?php endif; ?>

		</div>
		<!-- Game Result / End -->
	</div>
</div>
