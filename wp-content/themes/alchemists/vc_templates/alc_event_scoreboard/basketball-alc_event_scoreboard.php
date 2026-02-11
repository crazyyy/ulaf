<?php
/**
 * The template for displaying Event Scoreboard for Basketball
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.3.3
 * @version   4.7.0
 */
?>

<!-- Game Result -->
<div class="game-result">

	<section class="game-result__section">
		<header class="game-result__header">
			<?php
			$leagues = get_the_terms( $event_id_output, 'sp_league' );
			if ( $leagues ) :
				$league = array_shift( $leagues );
				?>
				<h3 class="game-result__title">
					<?php
					// League
					echo esc_html( $league->name );
					?>

					<?php
					// Season
					$seasons = get_the_terms( $event_id_output, 'sp_season' );
					if ( $seasons ) {
						$season = array_shift( $seasons );
						echo esc_html( $season->name );
					}
					?>
				</h3>
				<?php
			endif;
			?>

			<time class="game-result__date" datetime="<?php echo esc_attr( $event_date ); ?>">
				<?php
				// Event Date and Time or Time Status (OK, Postponed, TBD, Canceled)
				echo esc_html( get_the_time( sp_date_format(), $event_id_output ) ) . alchemists_event_time_status_badge( $event_id_output );
				?>
			</time>

			<?php
			if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) :
				$matchday = get_post_meta( $event_id_output, 'sp_day', true );
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
							echo get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' );
						endif;
					echo '</figure>';
					echo '<div class="game-result__team-info">';
						echo '<h5 class="game-result__team-name">' . esc_html( get_the_title( $team ) ) . '</h5>';
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
				echo '</div>';

			endforeach;
			?>

			<!-- Game Score -->
			<div class="game-result__score-wrap">
				<div class="game-result__score">

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

				</div>
				<div class="game-result__score-label"><?php esc_html_e( 'Final Score', 'alchemists' ); ?></div>

			</div>
			<!-- Game Score / End -->

		</div>
		<!-- Team Logos + Game Result / End -->



		<?php if ( $display_details || $display_percentage ) :

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

		// get Performance
		$event_performance = sp_get_performance( $event_id_output );

		// Remove the first row to leave us with the actual data
		unset( $event_performance[0] );

		endif; ?>

		<?php if ( $display_details ) : ?>
			<?php if (!empty($results)) : ?>
				<?php if (!empty($results[$team1]) && !empty($results[$team2])) : ?>
					<?php if ( isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result]) ) : ?>

						<!-- Game Stats -->
						<div class="game-result__stats">
							<div class="row">
								<div class="col-12 col-lg-6 order-lg-2 game-result__stats-scoreboard">
									<div class="game-result__table-stats">
										<?php echo alchemists_sp_event_results( $results, $labels ); ?>
									</div>
								</div>

								<?php
								// Custom Stats
								$event_stats_array = array();
								$event_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_progress_bars_stats );

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
							</div>
						</div>
						<!-- Game Stats / End -->

					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</section>

	<?php if ( $display_percentage ) : ?>
		<?php if (!empty($results)) : ?>
			<?php if (!empty($results[$team1]) && !empty($results[$team2])) : ?>
				<?php if ( isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result]) ) : ?>

					<?php
					// Percentage
					$game_stats_percentage_array = array();
					$game_stats_percentage_array = alchemists_sp_filter_array( $performances_posts_array, $event_percents_stats );
					?>

					<!-- Game Percentage -->
					<section class="game-result__section">
						<header class="game-result__subheader card__subheader">
							<h5 class="game-result__subtitle"><?php esc_html_e( 'Game Statistics', 'alchemists' ); ?></h5>
						</header>
						<div class="game-result__content-alt mb-0">
							<div class="row">
								<div class="col-12 col-lg-6">
									<div class="row">

										<?php // 1st Team
										foreach ( $game_stats_percentage_array as $event_percent_key => $event_percent_label ) :

											// Performance - Value
											$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][0][ $event_percent_key ] );
											$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][0][ $event_percent_key ] );

											// Performance - Percent
											$event_team1_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team1_value, $event_team2_value );
											?>

											<div class="col-4">
												<div class="circular">
													<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team1_percent ); ?>" <?php echo esc_attr( $color_team_1_bar_output ); ?>>
														<span class="circular__percents"><?php echo esc_html( $event_team1_value ); ?><?php if ( $event_circular_bars_stats_format == 'percentage' ) { echo '<small>%</small>'; } ?></span>
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
											$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][0][ $game_percent_key ] );
											$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][0][ $game_percent_key ] );

											// Performance - Percent
											$event_team2_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team2_value, $event_team1_value );
											?>

											<div class="col-4">
												<div class="circular">
													<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team2_percent ); ?>" <?php echo esc_attr( $color_team_2_bar_output ); ?>>
														<span class="circular__percents"><?php echo esc_html( $event_team2_value ); ?><?php if ( $event_circular_bars_stats_format == 'percentage' ) { echo '<small>%</small>'; } ?></span>
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
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>


</div>
<!-- Game Result / End -->
