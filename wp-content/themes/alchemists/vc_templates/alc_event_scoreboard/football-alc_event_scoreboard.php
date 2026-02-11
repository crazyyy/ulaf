<?php
/**
 * The template for displaying Event Scoreboard for Football
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.3.3
 * @version   4.7.0
 */
?>

<!-- Game Result -->
<div class="game-result">

	<section class="game-result__section pt-0">
		<header class="game-result__header game-result__header--alt">

			<?php
			$leagues = get_the_terms( $event_id_output, 'sp_league' );
			if ( $leagues ) :
				$league = array_shift( $leagues );
				?>
				<span class="game-result__league">
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

				</span>
				<?php
			endif;
			?>

			<?php
			$venues = get_the_terms( $event_id_output, 'sp_venue' );
			if ( $venues ) :
				?>

				<h3 class="game-result__title">
					<?php
					$venue_names = array();
					foreach ( $venues as $venue ) {
						$venue_names[] = $venue->name;
					}
					echo implode( '/', $venue_names ); ?>
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
				if ( $matchday != '' ) :
					?>
					<div class="game-result__matchday">(<?php echo esc_html( $matchday ); ?>)</div>
					<?php
				endif;
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
				<div class="game-result__score game-result__score--lg">

					<?php

					$status = esc_html__( 'Preview', 'alchemists' );

					if ( ! empty( $results ) ) :

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
					<?php echo apply_filters( 'sportspress_event_logos_status', $status, $event_id_output ); ?>
				</div>

			</div>
			<!-- Game Score / End -->

		</div>
		<!-- Team Logos + Game Result / End -->

		<?php
		if ( $display_details || $display_percentage ) :

			// Get Performance
			$event_performance = sp_get_performance( $event_id_output );

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
			if( $performances_posts ){
				foreach( $performances_posts as $performance_post ){
					$performances_posts_array[ $performance_post->post_name ] = array(
						'label'   => $performance_post->post_title,
						'value'   => $performance_post->post_name,
						'excerpt' => $performance_post->post_excerpt
					);
				}
				wp_reset_postdata();
			}
		endif;
		?>

		<?php if ( $show_stats && $display_details ) : ?>
			<!-- Game Stats -->
			<div class="game-result__stats">
				<div class="row">
					<div class="col-12 col-lg-6 order-lg-2 game-result__stats-scoreboard">
						<div class="game-result__table-stats">
							<?php echo alchemists_sp_event_results( $results, $labels ); ?>
						</div>
					</div>

					<?php
					// Stats - Progress Bars
					$event_stats_array = array();
					$event_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_progress_bars_stats );
					?>

					<div class="col-6 col-lg-3 order-lg-1 game-result__stats-team-1">

						<table class="progress-table progress-table--m-space progress-table--fullwidth progress-table--bar-fullwidth">
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
										<td class="progress-table__progress-label progress-table__progress-label--highlight text-transform-uppercase">
											<?php echo esc_html( $event_stat_bar_label['label'] ); ?>
										</td>
										<td class="progress-table__progress-bar">
											<div class="progress progress--battery">
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
					<div class="col-6 col-lg-3 order-lg-3 game-result__stats-team-2">

						<table class="progress-table progress-table--m-space progress-table--fullwidth progress-table--bar-fullwidth">
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
											<div class="progress progress--battery">
												<div class="progress__bar progress__bar--success" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo esc_attr( $color_team_2_progress_bar_output ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</td>
										<td class="progress-table__progress-label progress-table__progress-label--highlight text-transform-uppercase">
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
	</section>

	<?php if ( $display_details ) : ?>
	<?php
		if (!empty($results)) {
			if (!empty($results[$team1]) && !empty($results[$team2])) {

				// ball possession fields
				$team1_poss = isset( $results[$team1]['poss'] ) ? str_replace( '%', '', $results[$team1]['poss'] ) : '';
				$team2_poss = isset( $results[$team2]['poss'] ) ? str_replace( '%', '', $results[$team2]['poss'] ) : '';

				if (!empty($results[$team1]['poss']) && !empty($results[$team2]['poss'])) { ?>

					<!-- Ball Possession -->
					<section class="game-result__section">
						<header class="game-result__subheader card__subheader">
							<h5 class="game-result__subtitle"><?php esc_html_e( 'Ball Possession', 'alchemists' ); ?></h5>
						</header>
						<div class="game-result__content">

							<!-- Progress: Ball Possession -->
							<div class="progress-double-wrapper progress-double-wrapper--fullwidth">
								<div class="progress-inner-holder">
									<div class="progress__digit progress__digit--left progress__digit--highlight"><?php echo esc_html( $team1_poss ); ?>%</div>
									<div class="progress progress--battery">
										<div class="progress__bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $team1_poss ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $team1_poss ); ?>%; <?php echo esc_attr( $color_team_1_progress_bar_output ); ?>"></div>
										<div class="progress__bar progress__bar--success" role="progressbar" aria-valuenow="<?php echo esc_attr( $team2_poss ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $team2_poss ); ?>%; <?php echo esc_attr( $color_team_2_progress_bar_output ); ?>"></div>
									</div>
									<div class="progress__digit progress__digit--right progress__digit--highlight"><?php echo esc_html( $team2_poss ); ?>%</div>
								</div>
							</div>
							<!-- Progress: Ball Possession / End -->

						</div>
					</section>
					<!-- Ball Possession / End -->

				<?php
				}
			}
		}
		?>
	<?php endif; ?>

	<?php if ( $display_percentage ) : ?>
		<!-- Additional Stats -->
		<section class="game-result__section">
			<header class="game-result__subheader card__subheader">
				<h5 class="game-result__subtitle"><?php esc_html_e( 'Additional Stats', 'alchemists' ); ?></h5>
			</header>
			<div class="game-result__content-alt">

				<?php
				// Stats - Circular Bars
				$game_stats_additional_array = array();
				$game_stats_additional_array = alchemists_sp_filter_array( $performances_posts_array, $event_percents_stats );
				?>
				<div class="row">
					<div class="col-lg-6">
						<div class="row">
							<?php // 1st Team
							foreach ( $game_stats_additional_array as $game_stat_additional_key => $game_stat_additional_value ) :

								// Performance - Value
								$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][0][ $game_stat_additional_key ] );
								$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][0][ $game_stat_additional_key ] );

								// Performance - Percent
								$event_team1_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team1_value, $event_team2_value );
								?>

								<div class="col-4">
									<div class="circular">
										<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team1_percent ); ?>" <?php echo esc_attr( $color_team_1_bar_output ); ?>>
											<span class="circular__percents circular__percents--lg"><?php echo esc_html( $event_team1_value ); ?><small class="circular__label" title="<?php echo esc_attr( $game_stat_additional_value['excerpt'] ); ?>"><?php echo esc_html( $game_stat_additional_value['label'] ); ?></small></span>
										</div>
									</div>
								</div>

							<?php endforeach; ?>

						</div>
						<div class="spacer"></div>
						<div class="row">

							<?php // 2nd Team
							foreach ( $game_stats_additional_array as $game_stat_additional_key => $game_stat_additional_value ) :

								// Performance - Value
								$event_team1_value = alchemists_check_exists_not_empty( $event_performance[ $team1 ][0][ $game_stat_additional_key ] );
								$event_team2_value = alchemists_check_exists_not_empty( $event_performance[ $team2 ][0][ $game_stat_additional_key ] );

								// Performance - Percent
								$event_team2_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team2_value, $event_team1_value );
								?>

								<div class="col-4">
									<div class="circular">
										<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team2_percent ); ?>" <?php echo esc_attr( $color_team_2_bar_output ); ?>>
											<span class="circular__percents circular__percents--lg"><?php echo esc_html( $event_team2_value ); ?><small class="circular__label" title="<?php echo esc_attr( $game_stat_additional_value['excerpt'] ); ?>"><?php echo esc_html( $game_stat_additional_value['label'] ); ?></small></span>
										</div>
									</div>
								</div>

							<?php endforeach; ?>

						</div>
					</div>
					<div class="col-lg-5 offset-md-1">

						<?php
						$game_all_stats_array = array();
						$game_all_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_stats );
						?>

						<div class="game-result__table-additional-stats">
							<div class="table-responsive">
								<table class="table table--no-border">
									<thead>
										<tr>
											<th><?php echo esc_html( $event_stats_table_title ); ?></th>
											<th><?php echo sp_get_team_name( $team1, $abbreviate_teams ); ?></th>
											<th><?php echo sp_get_team_name( $team2, $abbreviate_teams ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $game_all_stats_array as $game_all_stat_key => $game_all_stat_value ) : ?>
											<tr>
												<th><?php echo esc_html( $game_all_stat_value['excerpt'] ); ?></th>

												<?php
												$event_team1_percent = alchemists_check_exists_not_empty( $event_performance[ $team1 ][0][ $game_all_stat_key ] );
												?>
												<td><?php echo esc_html( $event_team1_percent ); ?></td>

												<?php
												$event_team2_percent = alchemists_check_exists_not_empty( $event_performance[ $team2 ][0][ $game_all_stat_key ] );
												?>
												<td><?php echo esc_html( $event_team2_percent ); ?></td>

											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>

			</div>
		</section>
		<!-- Additional Stats / End -->
		<?php endif; ?>

</div>
<!-- Game Result / End -->
