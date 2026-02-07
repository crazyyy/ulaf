<?php
/**
 * Event Logos
 *
 * @author        ThemeBoy
 * @package    SportsPress/Templates
 * @version   2.6.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$event_result_as_header = get_post_meta( get_the_ID(), 'event_as_header', true );

if ( get_option( 'sportspress_event_show_logos', 'yes' ) === 'no' ) {
	return;
}

if ( ! empty( $event_result_as_header ) && 'on' === $event_result_as_header ) {
	return;
}

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}
?>
<div class="stm-next-match-units">
	<?php if ( ! splash_is_layout( 'sccr' ) ) : ?>
		<?php
		/*Check if two team exist in derby*/
		$teams = get_post_meta( $id, 'sp_team', false );
		if ( count( $teams ) > 1 ) :
			?>
			<?php
			/* Get league names */
			$leagues = wp_get_post_terms( get_the_id(), 'sp_league' );

			$leagues_names = array();
			if ( ! empty( $leagues ) ) {
				foreach ( $leagues as $league ) {
					$leagues_names[] = $league->name;
				}
			}

			/*Get venue name*/
			$venue      = wp_get_post_terms( get_the_id(), 'sp_venue' );
			$venue_name = '';
			if ( ! empty( $venue ) && ! is_wp_error( $venue ) ) {
				$venue_name = $venue[0]->name;
			}

			/*Get AF data*/
			$teams        = array_filter( $teams, 'sp_filter_positive' );
			$team_1_id    = $teams[0];
			$team_2_id    = $teams[1];
			$city_1       = wp_get_post_terms( $team_1_id, 'sp_venue' );
			$city_2       = wp_get_post_terms( $team_2_id, 'sp_venue' );
			$team_results = get_post_meta( get_the_ID(), 'sp_results', false );
			$permalink    = get_post_permalink( get_the_ID(), false, true );
			$results      = get_post_meta( get_the_ID(), 'sp_results', true );
			$point_system = splash_get_sportpress_points_system();

			$team_results_1 = ( isset( $team_results[0] ) && isset( $team_results[0][ $team_1_id ] ) ) ? $team_results[0][ $team_1_id ] : array();
			$team_results_2 = ( isset( $team_results[0] ) && isset( $team_results[0][ $team_2_id ] ) ) ? $team_results[0][ $team_2_id ] : array();
			?>

			<div class="stm-next-match-unit">
				<div class="stm-next-match-time">
					<?php
					$date      = new DateTime( get_the_time( 'Y/m/d H:i:s' ) );
					$date_show = get_post_time( sp_date_format() . ' - ' . sp_time_format(), false, get_the_ID(), true );
					$date      = $date->format( 'Y-m-d H:i:s' );
					$d         = gmdate( 'Y-m-d H:i:s', strtotime( splash_add_timezone() . ' minute', strtotime( get_the_date( 'Y-m-d H:s:i' ) ) ) );
					?>
					<time class="heading-font" datetime="<?php echo esc_attr( $d ); ?>" data-countdown="<?php echo esc_attr( str_replace( '-', '/', $d ) ); ?>"></time>
				</div>

				<div class="stm-next-match-main-meta">

					<div class="stm-next-matches_bg" style="background-image: url(<?php echo esc_url( get_theme_mod( 'sp_event_bg' ) ); ?>);"></div>

					<div class="stm-next-match-opponents-units">


						<!--hockey-->
						<?php if ( splash_is_layout( 'hockey' ) ) : ?>
							<div class='stm-next-match-info-wrapp'>
								<div class="stm-next-match-info normal-font">
									<?php echo esc_html( implode( ',', $leagues_names ) . ' ' . $date_show ); ?>
								</div>
								<?php if ( ! empty( $venue_name ) ) : ?>
									<div class="stm-next-match-venue normal-font">
										<?php echo esc_html( $venue_name ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<!--hockey-->


						<div class="stm-next-match-opponents 
						<?php
						if ( empty( $team_results_1[ $point_system ] ) && empty( $team_results_2[ $point_system ] ) ) {
							echo 'stm_event_future ';}
						?>
						">
							<?php
							/*Get teams meta*/

							$img_size = ( splash_is_layout( 'baseball' ) ) ? 'full' : 'stm-200-200';

							$team_1_title = get_the_title( $teams[0] );
							$team_1_image = splash_get_thumbnail_url( $teams[0], '', $img_size );
							$team_1_url   = get_permalink( $teams[0] );

							$team_2_title = get_the_title( $teams[1] );
							$team_2_image = splash_get_thumbnail_url( $teams[1], '', $img_size );
							$team_2_url   = get_permalink( $teams[1] );
							?>

							<div class="stm-command">
								<?php if ( ! empty( $team_1_image ) ) : ?>
									<div class="stm-command-logo">
										<a href="<?php echo esc_url( $team_1_url ); ?>">
											<?php
											if ( splash_is_layout( 'af' ) ) :
												$post_meta  = get_post_meta( $teams[0], 'team_helm_image' );
												$attach_img = ( $post_meta ) ? wp_get_attachment_image_src( $post_meta[0], 'full' ) : 0;
												?>
												<img src="<?php echo ( count( $attach_img ) > 0 ) ? esc_url( $attach_img[0] ) : ''; ?>" alt="" />
											<?php endif; ?>
											<img src="<?php echo esc_url( $team_1_image ); ?>" alt="" />
										</a>
									</div>
								<?php endif; ?>
								<?php if ( ! splash_is_layout( 'af' ) && ! splash_is_layout( 'basketball_two' ) && ! splash_is_layout( 'hockey' ) && ! splash_is_layout( 'baseball' ) ) : ?>
									<div class="stm-command-title">
										<h4>
											<a href="<?php echo esc_url( $team_1_url ); ?>">
												<?php echo esc_html( $team_1_title ); ?>
											</a>
										</h4>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( ! splash_is_layout( 'af' ) && ! splash_is_layout( 'basketball_two' ) && ! splash_is_layout( 'hockey' ) && ! splash_is_layout( 'baseball' ) ) : ?>

								<?php if ( ! empty( $team_results_1 ) ) : ?>
									<?php if ( isset( $team_results_1['outcome'] ) && ! empty( $team_results_1['outcome'][0] ) ) : ?>
										<div class="stm-command-results">
											<span>
												<?php
												if ( splash_is_layout( 'esport' ) ) {
													$team_1_score = '-';
													$team_2_score = '-';
													$team_1_label = '';
													$team_2_label = '';

													if ( ! empty( $team_results_1[ $point_system ] ) || '0' === $team_results_1[ $point_system ] ) {
														$team_1_score = $team_results_1[ $point_system ];
													}
													if ( ! empty( $team_results_2[ $point_system ] ) || '0' === $team_results_2[ $point_system ] ) {
														$team_2_score = $team_results_2[ $point_system ];
													}
													if ( ! empty( intval( $team_1_score ) ) && ! empty( intval( $team_2_score ) ) ) {
														if ( intval( $team_1_score ) > intval( $team_2_score ) ) {
															$team_1_label = 'win-label';
														} elseif ( intval( $team_1_score ) < intval( $team_2_score ) ) {
															$team_2_label = 'win-label';
														}
													}
													?>
													<span class="value <?php echo esc_attr( $team_1_label ); ?>">
														<span class="score">
															<?php echo esc_html( $team_1_score ); ?>
														</span>
													</span>
													<span class="value <?php echo esc_attr( $team_2_label ); ?>">
														<span class="score">
															<?php echo esc_html( $team_2_score ); ?>
														</span>
													</span>
													<?php
												} else {
													if ( ! empty( $team_results_1[ $point_system ] ) || '0' === $team_results_1[ $point_system ] ) {
														echo esc_html( $team_results_1[ $point_system ] );
													} else {
														echo '-';
													}
													?>
													: 
													<?php
													if ( ! empty( $team_results_2[ $point_system ] ) || '0' === $team_results_2[ $point_system ] ) {
														echo esc_html( $team_results_2[ $point_system ] );
													} else {
														echo '-';
													}
												}

												?>
											</span>
										</div>
									<?php else : ?>
										<div class="stm-command-vs"><span><?php esc_html_e( 'vs', 'splash' ); ?></span>
										</div>
									<?php endif; ?>
								<?php else : ?>
									<div class="stm-command-vs"><span><?php esc_html_e( 'vs', 'splash' ); ?></span>
									</div>
								<?php endif; ?>

							<?php else : ?>
								<div class="stm-teams-info heading-font">
									<div class="stm-command-title-left">
										<?php if ( splash_is_layout( 'af' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'baseball' ) ) : ?>
											<span class="stm-team-city"><?php echo esc_html( $city_1[0]->name ); ?></span>
										<?php endif; ?>
										<h4>
											<a href="<?php echo esc_url( $team_1_url ); ?>">
												<?php echo esc_html( $team_1_title ); ?>
											</a>
										</h4>
										<?php if ( splash_is_layout( 'baseball' ) && ! empty( $team_results[0] ) && ! empty( $team_results_1 ) && isset( $team_results_1['outcome'] ) && ! empty( $team_results_1['outcome'][0] ) ) : ?>
											<div class="
											<?php
											if ( 'win' === $team_results_1['outcome'][0] ) {
												echo esc_attr( 'stm-latest-result-win-label' );
											} else {
												echo esc_attr( 'stm-latest-result-lose-label' );
											}
														$team_results_1_outcome = $team_results_1['outcome'][0];
														// translators: %s: Outcome for the team
														$outcome = sprintf( esc_html_x( 'Outcome: %s', 'Outcome for team', 'splash' ), esc_html( $team_results_1_outcome ) );
											?>
											heading-font"><?php echo esc_html( $outcome ); ?></div>
										<?php endif; ?>
									</div>
									<div class="stm-team-results-outer">
										<?php if ( ! empty( $team_results[0] ) ) : ?>
											<?php if ( ! empty( $team_results_1 ) && ! empty( $team_results_2 ) ) : ?>
												<?php if ( isset( $team_results_1[ $point_system ] ) && isset( $team_results_2[ $point_system ] ) ) : ?>
													<?php if ( empty( $team_results_1[ $point_system ] ) && empty( $team_results_2[ $point_system ] ) ) : ?>
														<div class="stm-latest-result_result"><?php esc_html_e( 'VS', 'splash' ); ?></div>
													<?php else : ?>
														<div class="stm-latest-result_result">
															<?php if ( ! splash_is_layout( 'baseball' ) ) : ?>
																<span class="stm-res-left">
																	<?php if ( strlen( $team_results_1[ $point_system ] ) ) : ?>
																		<span class="
																		<?php
																		if ( isset( $team_results_1['outcome'] ) && ( 'win' === $team_results_1['outcome'][0] ) ) {
																			echo esc_attr( 'stm-win' );
																		} else {
																			echo esc_attr( 'stm-lose' );
																		}
																		?>
																		"><?php echo esc_html( $team_results_1[ $point_system ] ); ?></span>
																		<div class="
																		<?php
																		if ( isset( $team_results_1['outcome'] ) && ( 'win' === $team_results_1['outcome'][0] ) ) {
																			echo esc_attr( 'stm-latest-result-win-label' );
																		} else {
																			echo esc_attr( 'stm-latest-result-lose-label' );
																		}
																		if ( splash_is_layout( 'af' ) || splash_is_layout( 'baseball' ) ) {
																			echo esc_attr( ' heading-font' );
																		} else {
																			echo esc_attr( ' normal-font' );
																		}
																		?>
																		">
																			<?php
																			if ( isset( $team_results_1['outcome'] ) ) {
																				if ( 'win' === $team_results_1['outcome'][0] ) {
																					esc_html_e( 'win', 'splash' );
																				} else {
																					esc_html_e( 'loss', 'splash' );
																				}
																			}
																			?>
																		</div>
																	<?php endif; ?>
																</span>
																<!-- hockey-->
																<?php echo esc_html( ( splash_is_layout( 'hockey' ) ) ? ' : ' : '' ); ?>
																<!-- hockey end-->
																<span class="stm-res-right">

																<?php if ( strlen( $team_results_2[ $point_system ] ) ) : ?>
																	<span class="
																	<?php
																	if ( isset( $team_results_2['outcome'] ) && ( 'win' === $team_results_2['outcome'][0] ) ) {
																		echo esc_attr( 'stm-win' );
																	} else {
																		echo esc_attr( 'stm-lose' );
																	}
																	?>
																	"><?php echo esc_html( $team_results_2[ $point_system ] ); ?></span>
																	<div class="
																	<?php
																	if ( isset( $team_results_2['outcome'] ) && ( 'win' === $team_results_2['outcome'][0] ) ) {
																		echo esc_attr( 'stm-latest-result-win-label' );
																	} else {
																		echo esc_attr( 'stm-latest-result-lose-label' );
																	}
																	echo esc_attr( ( splash_is_layout( 'af' ) || splash_is_layout( 'baseball' ) ) ) ? ' heading-font' : ' normal-font';
																	?>
																	">
																		<?php
																		if ( isset( $team_results_2['outcome'] ) ) {
																			if ( 'win' === $team_results_2['outcome'][0] ) {
																				esc_html_e( 'win', 'splash' );
																			} else {
																				esc_html_e( 'loss', 'splash' );
																			}
																		}
																		?>
																		</div>
																<?php else : ?>
																	<div class="stm-latest-result-lose-label"><?php esc_html_e( '- -', 'splash' ); ?></div>
																<?php endif; ?>

																</span>
															<?php else : ?>
																<span class="stm-res-outer">
																	<?php if ( isset( $team_results_1['outcome'] ) && ! empty( $team_results_1['outcome'][0] ) ) : ?>
																		<span class="<?php echo esc_attr( ( 'win' === $team_results_1['outcome'][0] ) ? 'stm-win' : 'stm-lose' ); ?>">
																			<?php echo esc_html( $team_results_1[ $point_system ] ); ?> : <?php echo esc_html( $team_results_2[ $point_system ] ); ?>
																		</span>
																	<?php endif; ?>
																</span>
															<?php endif; ?>
														</div>
													<?php endif; ?>
													<!-- hockey -->
												<?php elseif ( splash_is_layout( 'hockey' ) ) : ?>
													<?php if ( empty( $team_results_1[ $point_system ] ) && empty( $team_results_2[ $point_system ] ) ) : ?>
														<div class="stm-latest-result_result_vs"><?php esc_html_e( 'VS', 'splash' ); ?></div>
													<?php endif; ?>
												<?php endif; ?>
												<!-- hockey end -->
											<?php endif; ?>
										<?php else : ?>
											<div class="stm-latest-result_result"><?php esc_html_e( '- VS -', 'splash' ); ?></div>
										<?php endif; ?>
									</div>
									<div class="stm-command-title-right">
										<?php if ( splash_is_layout( 'af' ) || splash_is_layout( 'af' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'baseball' ) ) : ?>
											<span class="stm-team-city"><?php echo esc_html( $city_2[0]->name ); ?></span>
										<?php endif; ?>
										<h4>
											<a href="<?php echo esc_url( $team_2_url ); ?>">
												<?php echo esc_html( $team_2_title ); ?>
											</a>
										</h4>
										<?php if ( splash_is_layout( 'baseball' ) && ! empty( $team_results[0] ) && ! empty( $team_results_2 ) && isset( $team_results_2['outcome'] ) && ! empty( $team_results_2['outcome'][0] ) ) : ?>
											<?php
												$team_results_2_outcome = $team_results_2['outcome'][0];
												// translators: %s: Outcome for the team
												$outcome = sprintf( esc_html_x( 'Outcome: %s', 'Outcome for team', 'splash' ), esc_html( $team_results_2_outcome ) );
											?>
											<div class="<?php echo esc_attr( ( 'win' === $team_results_2['outcome'][0] ) ) ? 'stm-latest-result-win-label' : 'stm-latest-result-lose-label'; ?>
											heading-font"><?php echo esc_html( $outcome ); ?></div>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="stm-command stm-command-right">
								<?php if ( ! splash_is_layout( 'af' ) && ! splash_is_layout( 'basketball_two' ) && ! splash_is_layout( 'hockey' ) && ! splash_is_layout( 'baseball' ) ) : ?>
									<div class="stm-command-title">
										<h4>
											<a href="<?php echo esc_url( $team_2_url ); ?>">
												<?php echo esc_html( $team_2_title ); ?>
											</a>
										</h4>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $team_2_image ) ) : ?>
									<div class="stm-command-logo">
										<a href="<?php echo esc_url( $team_2_url ); ?>">
											<?php
											if ( splash_is_layout( 'af' ) ) :
												$post_meta  = get_post_meta( $teams[1], 'team_helm_image' );
												$attach_img = wp_get_attachment_image_src( $post_meta[0], 'full' );
												?>
												<img src="<?php echo esc_url( $attach_img[0] ); ?>" alt="" />
											<?php endif; ?>
											<img src="<?php echo esc_url( $team_2_image ); ?>" alt="" />
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<?php if ( ! splash_is_layout( 'hockey' ) ) : ?>
						<?php
						if ( splash_is_layout( 'af' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'baseball' ) ) {
							echo "<div class='stm-next-match-info-wrapp'>";}
						?>
						<div class="stm-next-match-info  <?php echo ( splash_is_layout( 'bb' ) ) ? 'heading-font' : 'normal-font'; ?>">
							<?php echo esc_html( implode( ', ', $leagues_names ) . ' - ' . $date_show ); ?>
						</div>
						<?php if ( ! empty( $venue_name ) ) : ?>
							<div class="stm-next-match-venue  <?php echo ( splash_is_layout( 'bb' ) ) ? 'heading-font' : 'normal-font'; ?>">
								<?php echo esc_html( $venue_name ); ?>
							</div>
						<?php endif; ?>
						<?php
						if ( splash_is_layout( 'af' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'baseball' ) ) {
							echo '</div>';}
						?>
					<?php endif; ?>
				</div>

			</div>
		<?php endif; /*Two team exists*/ ?>
		<?php
	else :
		/**
		 * Created by PhpStorm.
		 * User: NDimaA
		 * Date: 16.02.2017
		 * Time: 14:36
		 */


		$teams = get_post_meta( $id, 'sp_team', false );

		/* Get league names */
		$leagues = wp_get_post_terms( get_the_id(), 'sp_league' );

		$leagues_names = array();
		if ( ! empty( $leagues ) ) {
			foreach ( $leagues as $league ) {
				$leagues_names[] = $league->name;
			}
		}

		/*Get venue name*/
		$venue      = wp_get_post_terms( get_the_id(), 'sp_venue' );
		$venue_name = '';
		if ( ! empty( $venue ) && ! is_wp_error( $venue ) ) {
			$venue_name = $venue[0]->name;
		}

		$performance  = sp_get_performance( get_the_ID() );
		$team_1_id    = $teams[0];
		$team_2_id    = $teams[1];
		$city_1       = wp_get_post_terms( $team_1_id, 'sp_venue' );
		$city_2       = wp_get_post_terms( $team_2_id, 'sp_venue' );
		$team_results = get_post_meta( get_the_ID(), 'sp_results', false );
		$permalink    = get_post_permalink( get_the_ID(), false, true );
		$results      = get_post_meta( get_the_ID(), 'sp_results', true );
		$point_system = splash_get_sportpress_points_system();

		$sportspress_primary_result = get_option( 'sportspress_primary_result', null );

		if ( ! empty( $sportspress_primary_result ) ) {
			$goals = $sportspress_primary_result;
		} else {
			$goals = 'goals';
		}

		?>

		<div class="container">
			<div class="stm-event-results-as-header-wrapper 
			<?php
			if ( empty( $team_results_1['outcome'][0] ) ) {
				echo 'stm-event-feature';}
			?>
			">
				<div class="fixture_detail clearfix">
					<div class="command_left">
						<div class="command_info">
							<div class="logo">
								<a href="<?php echo esc_url( get_the_permalink( $team_1_id ) ); ?>"><?php echo get_the_post_thumbnail( $team_1_id, 'team_logo' ); ?></a>
							</div>
							<?php if ( ! empty( $team_results_1 ) ) : ?>
								<?php if ( isset( $team_results_1['outcome'] ) && ! empty( $team_results_1['outcome'][0] ) ) : ?>
									<div class="score heading-font"><?php echo esc_html( $team_results_1[ $point_system ] ); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="goals">
							<h2>
								<a href="<?php echo esc_url( get_the_permalink( $team_1_id ) ); ?>"><?php echo esc_html( get_the_title( $team_1_id ) ); ?></a>
							</h2>
							<?php if ( ! empty( $team_results[0] ) ) : ?>
								<?php if ( ! empty( $team_results_1 ) ) : ?>
									<?php if ( isset( $team_results_1['outcome'] ) && ! empty( $team_results_1['outcome'][0] ) ) : ?>
										<?php
											$team_results_1_outcome = $team_results_1['outcome'][0];
											// translators: %s: Outcome for the team
											$outcome = sprintf( esc_html_x( 'Outcome: %s', 'Outcome for team', 'splash' ), esc_html( $team_results_1_outcome ) );
										?>
										<?php if ( 'win' === $team_results_1_outcome ) : ?>
											<div class="stm-latest-result-win-label heading-font"><?php esc_html_e( 'win', 'splash' ); ?></div>
										<?php else : ?>
											<div class="stm-latest-result-lose-label heading-font"><?php echo esc_html( $outcome ); ?></div>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
							<ul class="players">
								<?php
								if ( ! empty( $performance[ $team_1_id ] ) ) {
									foreach ( $performance[ $team_1_id ] as $player_id => $player ) {
										if ( splash_is_layout( 'af' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'hockey' ) ) {
											$goals_list = 'td';
											$title      = esc_html__( 'touchdown(s)', 'splash' );
										} else {
											$goals_list = $goals;
											$title      = esc_html__( 'goal(s)', 'splash' );
										}

										if ( isset( $player[ $goals_list ] ) ) {
											if ( $player[ $goals_list ] >= 1 ) {
												?>
												<li>
													<?php echo esc_html( get_the_title( $player_id ) ); ?> -
													<span><?php echo esc_html( $player[ $goals_list ] ) . ' ' . esc_html( $title ); ?></span>
												</li>
												<?php
											}
										}
									}
								}
								?>
							</ul>
						</div>
					</div>
					<?php if ( empty( $team_results_1['outcome'][0] ) ) : ?>
						<div class="stm-event-vs heading-font">vs</div>
					<?php endif; ?>
					<div class="command_right">
						<div class="command_info">
							<div class="logo">
								<a href="<?php echo esc_url( get_the_permalink( $team_2_id ) ); ?>"><?php echo get_the_post_thumbnail( $team_2_id, 'team_logo' ); ?></a>
							</div>
							<?php if ( ! empty( $team_results_2 ) ) : ?>
								<?php if ( isset( $team_results_2['outcome'] ) && ! empty( $team_results_2['outcome'][0] ) ) : ?>
									<span class="score heading-font"><?php echo esc_html( $team_results_2[ $point_system ] ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="goals">
							<h2>
								<a href="<?php echo esc_url( get_the_permalink( $team_2_id ) ); ?>"><?php echo esc_html( get_the_title( $team_2_id ) ); ?></a>
							</h2>
							<?php if ( ! empty( $team_results[0] ) ) : ?>
								<?php if ( ! empty( $team_results_2 ) ) : ?>
									<?php if ( isset( $team_results_2['outcome'] ) && ! empty( $team_results_2['outcome'][0] ) ) : ?>
										<?php
										$team_results_2_outcome = $team_results_2['outcome'][0];
										// translators: %s: Outcome for the team
										$outcome = sprintf( esc_html_x( 'Outcome: %s', 'Outcome for team', 'splash' ), esc_html( $team_results_2_outcome ) );
										?>
										<?php if ( 'win' === $team_results_2_outcome ) : ?>
											<div class="stm-latest-result-win-label heading-font"><?php esc_html_e( 'win', 'splash' ); ?></div>
										<?php else : ?>
											<div class="stm-latest-result-lose-label heading-font"><?php echo esc_html( $outcome ); ?></div>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
							<ul class="players">
								<?php
								if ( ! empty( $performance[ $team_2_id ] ) ) {
									foreach ( $performance[ $team_2_id ] as $player_id => $player ) {
										if ( splash_is_layout( 'af' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'hockey' ) ) {
											$goals_list = 'td';
											$title      = esc_html__( 'touchdown(s)', 'splash' );
										} else {
											$goals_list = $goals;
											$title      = esc_html__( 'goal(s)', 'splash' );
										}

										if ( isset( $player[ $goals_list ] ) ) {
											if ( $player[ $goals_list ] >= 1 ) {
												?>
												<li>
													<?php echo esc_html( get_the_title( $player_id ) ); ?> -
													<span><?php echo esc_html( $player[ $goals_list ] ) . ' ' . esc_html( $title ); ?></span>
												</li>
												<?php
											}
										}
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>

				<?php
				$date = get_the_time( get_option( 'date_format' ), get_the_ID() );
				$time = get_the_time( get_option( 'time_format' ), get_the_ID() );
				?>
				<div class="fixture_info_publish">
					<div class="stm-event-as-header-date-publish-wrapp">
						<div class="stm-flex-column">
							<div class="date"><?php echo esc_html( $date ); ?> | <?php echo esc_html( $time ); ?></div>
						</div>
						<div class="venue"><?php echo esc_html( $venue_name ); ?></div>
					</div>
				</div>
			</div>
		</div>

	<?php endif; ?>
</div>
