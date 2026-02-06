<?php
/**
 * The template for displaying Event Scoreboard for eSports
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

// Get Event Outcome
$event_outcome = get_posts( array(
	'post_type'      => 'sp_outcome',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
));
wp_reset_postdata();

$event_outcome_array = array();
if ( $event_outcome ) {
	foreach ( $event_outcome as $outcome ) {
		$event_outcome_array[ $outcome->post_name ] = $outcome->ID;
	}
}

// Set default Event Outcome Abbreviation
$outcome_abbr_win  = isset( $event_outcome_array['win'] ) ? sp_get_post_abbreviation( $event_outcome_array['win'] ) : esc_html__( 'W', 'alchemists' );
$outcome_abbr_loss = isset( $event_outcome_array['loss'] ) ? sp_get_post_abbreviation( $event_outcome_array['loss'] ) : esc_html__( 'L', 'alchemists' );

$venue1_desc = wp_get_post_terms($team1, 'sp_venue');
$venue2_desc = wp_get_post_terms($team2, 'sp_venue');
?>

<!-- Game Result -->
<div class="game-result">

	<section class="game-result__section pt-0">
		<header class="game-result__header game-result__header--alt game-result__header--alt-compact">

			<time class="game-result__date" datetime="<?php echo esc_attr( $event_date ); ?>">
				<?php
				// Event Date and Time or Time Status (OK, Postponed, TBD, Canceled)
				echo esc_html( get_the_time( sp_date_format(), $event_id_output ) ) . alchemists_event_time_status_badge( $event_id_output );
				?>
			</time>

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
			<?php endif; ?>
		</header>

		<!-- Team Logos + Game Result -->
		<div class="game-result__content d-block">

			<div class="row">
				<div class="<?php echo esc_attr( $display_details ? 'col-sm-6' : 'col-sm-12' ); ?>">
					<header class="game-result__header">
						<?php $leagues = get_the_terms( $event_id_output, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
							<h3 class="game-result__title">
								<?php
								// League
								echo esc_html( $league->name );
								?>

								<?php
								// Season
								$seasons = get_the_terms( $event_id_output, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons );
									echo esc_html( $season->name );
								endif;
								?>
							</h3>
						<?php endif; ?>
						<?php
						if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) :
							$matchday = get_post_meta( $event_id_output, 'sp_day', true );
							if ( $matchday != '' ) : ?>
							<div class="game-result__date"><?php echo esc_html( $matchday ); ?></div>
							<?php endif;
						endif;
						?>
					</header>

					<div class="game-result__teams-wrapper game-result__teams-wrapper--flex">

						<?php
						$j = 0;
						foreach( $teams as $team ):
							$j++;
							?>

							<div class="game-result__team game-result__team--<?php echo esc_attr( $j % 2 ? 'odd' : 'even' ); ?>">
								<figure class="game-result__team-logo">
									<?php
									if ( has_post_thumbnail ( $team ) ):
										echo get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' );
									endif;
									?>
								</figure>
								<div class="game-result__team-info">
									<h5 class="game-result__team-name"><?php echo esc_html( get_the_title( $team ) ); ?></h5>
									<div class="game-result__team-desc">
										<?php
										if ( $j == 1 ) {
											if ( isset( $venue1_desc[0] )) {
												echo esc_html( $venue1_desc[0]->description );
											}
										} elseif ( $j == 2 ) {
											if ( isset( $venue2_desc[0] )) {
												echo esc_html( $venue2_desc[0]->description );
											}
										}
										?>
									</div>
								</div>
							</div>

						<?php endforeach; ?>

						<div class="game-result__score-wrap">

							<div class="game-result__score game-result__score--sm">
								<?php
								if ( $show_stats ) :

									// 1st Team
									$team1_class = 'game-result__score-result--loser';
									$team1_outcome_abbr = $outcome_abbr_loss; // set loss by default
									if ( isset( $results[ $team1 ]['outcome'] ) && ! empty( $results[ $team1 ]['outcome'][0] ) ) {
										if ( $results[ $team1 ]['outcome'][0] == 'win' ) {
											$team1_class = 'game-result__score-result--winner';
											$team1_outcome_abbr = $outcome_abbr_win;
										}
									}

									// 2nd Team
									$team2_class = 'game-result__score-result--loser';
									$team2_outcome_abbr = $outcome_abbr_loss; // set loss by default
									if ( isset( $results[ $team2 ]['outcome'] ) && ! empty( $results[ $team2 ]['outcome'][0] ) ) {
										if ( $results[ $team2]['outcome'][0] == 'win' ) {
											$team2_class = 'game-result__score-result--winner';
											$team2_outcome_abbr = $outcome_abbr_win;
										}
									}


									if ( isset( $results[ $team1 ][ $primary_result ]) && isset( $results[ $team2 ][ $primary_result ] ) ) :
										?>
										<!-- 1st Team -->
										<span class="game-result__score-result <?php echo esc_attr( $team1_class ); ?>">
											<?php
											$team1_result = $results[ $team1 ][ $primary_result ];
											if ( is_numeric( $team1_result ) ) {
												echo esc_html( $team1_result );
											} else {
												echo esc_html( $team1_outcome_abbr );
											}
											?>
										</span>
										<!-- 1st Team / End -->
										<span class="game-result__score-dash">-</span>
										<!-- 2nd Team -->
										<span class="game-result__score-result <?php echo esc_attr( $team2_class ); ?>">
											<?php
											$team2_result = $results[ $team2 ][ $primary_result ];
											if ( is_numeric( $team2_result ) ) {
												echo esc_html( $team2_result );
											} else {
												echo esc_html( $team2_outcome_abbr );
											}
											?>
										</span>
										<!-- 2nd Team / End -->
										<?php
									endif;
									?>

								<?php else : ?>

									<span>-</span>

								<?php endif; ?>
							</div>

							<?php
							// Game Video
							$video_url = get_post_meta( $event_id_output, 'sp_video', true );
							if ( $video_url ) :
								?>
								<a href="<?php echo esc_url( $video_url ); ?>" class="game-result__score-video-icon mp_iframe" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_attr( 'Watch Replay', 'alchemists' ); ?>"><i class="fa fa-play"></i></a>
							<?php endif; ?>

						</div>

					</div>

				</div>

				<?php
				if ( $display_details || $display_percentage ) {
					// Player Performance
					$performance_ids = array();
					$performances_posts = get_posts(array(
						'post_type'      => 'sp_performance',
						'posts_per_page' => 9999,
						'orderby'        => 'menu_order',
						'order'          => 'DESC'
					));

					$performances_posts_array = array();
					if ( $performances_posts ){
						foreach( $performances_posts as $performance_post ){
							$performances_posts_array[ $performance_post->post_name ] = array(
								'label'   => $performance_post->post_title,
								'value'   => $performance_post->post_name,
								'excerpt' => $performance_post->post_excerpt
							);
							// build Performance IDS array
							$performance_ids[ $performance_post->post_name ] = $performance_post->ID;
						}
						wp_reset_postdata();
					}

					// get Performance
					$event_performance = sp_get_performance( $event_id_output );

					// Remove the first row to leave us with the actual data
					unset( $event_performance[0] );
				}
				?>

				<?php if ( $display_details ) : ?>
				<div class="col-sm-6">
					<?php

					// Custom Stats
					$game_stats_array = array();
					$game_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_progress_bars_stats );
					?>

					<!-- Progress Stats Table -->
					<table class="progress-table progress-table--fullwidth">
						<tbody>

						<?php
						// Progress Bars output
						foreach ( $game_stats_array as $game_stat_key => $game_stat_label ) :

							// Event Stats
							// must get the first array for Player-vs-Player mode compatibility
							$event_team1_first_array = key( $event_performance[ $team1 ] );
							$event_team2_first_array = key( $event_performance[ $team2 ] );
							$event_team1_stat     = alchemists_check_exists_not_empty( $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_key ] );
							$event_team2_stat     = alchemists_check_exists_not_empty( $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_key ] );
							$event_team1_stat_pct = alchemists_sp_performance_percent( $event_team1_stat, $event_team2_stat );
							$event_team2_stat_pct = alchemists_sp_performance_percent( $event_team2_stat, $event_team1_stat );
							?>

							<tr>
								<td class="progress-table__progress-label <?php echo esc_attr( $event_team1_stat > $event_team2_stat ? 'progress-table__progress-label--highlight' : '' ); ?>"><?php echo esc_html( alchemists_format_big_number( $event_team1_stat ), 1, true ); ?></td>
								<td class="progress-table__progress-bar progress-table__progress-bar--first">
									<div class="progress progress--lines">
										<div class="progress__bar" style="width: <?php echo esc_attr( $event_team1_stat_pct ); ?>%; <?php echo esc_attr( $color_team_1_progress_bar_output ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team1_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</td>
								<td class="progress-table__title"><?php echo esc_html( $game_stat_label['label'] ); ?></td>
								<td class="progress-table__progress-bar progress-table__progress-bar--second">
									<div class="progress progress--lines">
										<div class="progress__bar progress__bar--info" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo esc_attr( $color_team_2_progress_bar_output ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</td>
								<td class="progress-table__progress-label <?php echo esc_attr( $event_team2_stat > $event_team1_stat ? 'progress-table__progress-label--highlight' : '' ); ?>"><?php echo esc_html( alchemists_format_big_number( $event_team2_stat ), 1, true ); ?></td>
							</tr>

						<?php endforeach; ?>

						</tbody>
					</table>
					<!-- Progress Stats Table / End -->
				</div>
				<?php endif; ?>
			</div>

		</div>
		<!-- Team Logos + Game Result / End -->
	</section>

	<?php if ( $display_percentage ) : ?>
		<?php if (!empty($results)) : ?>
			<?php if (!empty($results[$team1]) && !empty($results[$team2])) : ?>
				<?php if ( isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result]) ) : ?>

					<section class="game-result__section game-result__section--box row no-gutters">
						<div class="game-result__item col-md-12">
							<header class="game-result__subheader card__subheader">
								<h5 class="game-result__subtitle"><?php esc_html_e( 'Additional Stats', 'alchemists' ); ?></h5>
							</header>
							<div class="game-result__content mb-0">

								<!-- Game Stats -->
								<div class="alc-event-result-box__stats-circular-bars">
									<?php
									// Stats - Circular Bars
									$game_stats_additional_array = array();
									$game_stats_additional_array = alchemists_sp_filter_array( $performances_posts_array, $event_percents_stats );
									?>

									<!-- Container First -->
									<div class="alc-event-result-box__stats-circular-container">
										<?php
										$i_count = 0;
										foreach ( $game_stats_additional_array as $game_stat_additional_key => $game_stat_additional_value ) :
											$i_count++;

											if ( $i_count % 2 ) :
												// Performance - Value
												// must get the first array for Player-vs-Player mode compatibility
												$event_team1_first_array = key( $event_performance[ $team1 ] );
												$event_team2_first_array = key( $event_performance[ $team2 ] );
												$event_team1_value = isset( $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_additional_key ] ) ? $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_additional_key ] : 0;
												$event_team2_value = isset( $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_additional_key ] ) ? $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_additional_key ] : 0;

												// Performance - Percent
												$event_team1_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team1_value, $event_team2_value );
												$event_team2_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team2_value, $event_team1_value );

												$performance_id = sp_array_value( $performance_ids, $game_stat_additional_key, null );
												?>

												<div class="alc-event-result-box__stats-circular-item">
													<div class="alc-event-result-box__stats-circular-bar">
														<div class="circular circular--size-40">
															<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team2_percent ); ?>" <?php echo esc_attr( $color_team_1_bar_output ); ?> <?php echo esc_attr( $color_team_2_bar_output ); ?>>
																<?php alc_sp_performance_icon( $performance_id, '#fff', true, $event_team1_value, $event_team2_value ); ?>
															</div>
														</div>
													</div>
													<div class="alc-event-result-box__stats-meta">
														<span class="alc-event-result-box__stats-value"><?php echo esc_html( $game_stat_additional_value['label'] ); ?></span>
														<span class="alc-event-result-box__stats-label"><?php echo esc_html( $game_stat_additional_value['excerpt'] ); ?></span>
													</div>
												</div>
											<?php
											endif;
										endforeach;
										?>
									</div>
									<!-- Container First / End -->

									<div class="alc-event-result-box__stats-circular-container alc-event-result-box__stats-circular-container--center">
										<?php
										// Event duration
										if ( 'yes' === get_option( 'sportspress_event_show_full_time', 'yes' ) ) :
											$full_time = get_post_meta( $event_id_output, 'sp_minutes', true );
											if ( '' !== $full_time ) :
											?>
											<div class="alc-event-result-box__stats-circular-item alc-event-result-box__stats-circular-item--center">
												<div class="alc-event-result-box__stats-meta">
													<span class="alc-event-result-box__stats-value"><?php echo esc_html( $full_time ) . '\''; ?></span>
													<span class="alc-event-result-box__stats-label"><?php esc_html_e( 'Game Duration', 'alchemists' ); ?></span>
												</div>
											</div>
											<?php
											endif;
										endif;
										?>
									</div>

									<!-- Container Second -->
									<div class="alc-event-result-box__stats-circular-container">
										<?php
										$j_count = 0;
										foreach ( $game_stats_additional_array as $game_stat_additional_key => $game_stat_additional_value ) :
											$j_count++;

											if ( ! ( $j_count % 2 ) ) :
												// Performance - Value
												// must get the first array for Player-vs-Player mode compatibility
												$event_team1_first_array = key( $event_performance[ $team1 ] );
												$event_team2_first_array = key( $event_performance[ $team2 ] );
												$event_team1_value = isset( $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_additional_key ] ) ? $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_additional_key ] : 0;
												$event_team2_value = isset( $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_additional_key ] ) ? $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_additional_key ] : 0;

												// Performance - Percent
												$event_team1_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team1_value, $event_team2_value );
												$event_team2_percent = alchemists_sp_get_performances_based_on_format( $event_circular_bars_stats_format, $event_team2_value, $event_team1_value );

												$performance_id = sp_array_value( $performance_ids, $game_stat_additional_key, null );
												?>

												<div class="alc-event-result-box__stats-circular-item alc-event-result-box__stats-circular-item--reverse">
													<div class="alc-event-result-box__stats-circular-bar">
														<div class="circular circular--size-40">
															<div class="circular__bar" data-percent="<?php echo esc_attr( $event_team2_percent ); ?>" <?php echo esc_attr( $color_team_1_bar_output ); ?> <?php echo esc_attr( $color_team_2_bar_output ); ?>>
																<?php alc_sp_performance_icon( $performance_id, '#fff', true, $event_team1_value, $event_team2_value ); ?>
															</div>
														</div>
													</div>
													<div class="alc-event-result-box__stats-meta">
														<span class="alc-event-result-box__stats-value"><?php echo esc_html( $game_stat_additional_value['label'] ); ?></span>
														<span class="alc-event-result-box__stats-label"><?php echo esc_html( $game_stat_additional_value['excerpt'] ); ?></span>
													</div>
												</div>
											<?php
											endif;
										endforeach;
										?>
									</div>
									<!-- Container Second / End -->

								</div>
								<!-- Game Stats / End -->

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
