<?php
/**
 * Event Results
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.3.0
 * @version   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'title' => false,
	'status' => 'default',
	'date' => 'default',
	'date_from' => 'default',
	'date_to' => 'default',
	'date_past' => 'default',
	'date_future' => 'default',
	'date_relative' => 'default',
	'day' => 'default',
	'league' => null,
	'season' => null,
	'venue' => null,
	'team' => null,
	'player' => null,
	'number' => -1,
	'show_team_logo' => get_option( 'sportspress_event_blocks_show_logos', 'yes' ) == 'yes' ? true : false,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_events' => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'paginated' => get_option( 'sportspress_event_blocks_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => get_option( 'sportspress_event_blocks_rows', 5 ),
	'orderby' => 'default',
	'order' => 'default',
	'show_all_events_link' => false,
	'show_title' => get_option( 'sportspress_event_blocks_show_title', 'no' ) == 'yes' ? true : false,
	'show_league' => get_option( 'sportspress_event_blocks_show_league', 'no' ) == 'yes' ? true : false,
	'show_season' => get_option( 'sportspress_event_blocks_show_season', 'no' ) == 'yes' ? true : false,
	'show_venue' => get_option( 'sportspress_event_blocks_show_venue', 'no' ) == 'yes' ? true : false,
	'hide_if_empty' => false,
	'layout_style' => 'horizontal',
	'show_stats' => true,
	'performance' => null,
	'expand_btn' => false
);

if ( alchemists_sp_preset( 'soccer' ) ) {
	$defaults['show_timeline'] = false;
}

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if ( $status != 'default' )
	$calendar->status = $status;
if ( $date != 'default' )
	$calendar->date = $date;
if ( $date_from != 'default' )
	$calendar->from = $date_from;
if ( $date_to != 'default' )
	$calendar->to = $date_to;
if ( $date_past != 'default' )
	$calendar->past = $date_past;
if ( $date_future != 'default' )
	$calendar->future = $date_future;
if ( $date_relative != 'default' )
	$calendar->relative = $date_relative;
if ( $league )
	$calendar->league = $league;
if ( $season )
	$calendar->season = $season;
if ( $venue )
	$calendar->venue = $venue;
if ( $team )
	$calendar->team = $team;
if ( $player )
	$calendar->player = $player;
if ( $order != 'default' )
	$calendar->order = $order;
if ( $orderby != 'default' )
	$calendar->orderby = $orderby;
if ( $day != 'default' )
	$calendar->day = $day;
$data = $calendar->data();

if ( $hide_if_empty && empty( $data ) ) return false;

if ( $show_title && false === $title && $id ):
	$caption = $calendar->caption;
	if ( $caption )
		$title = $caption;
	else
		$title = get_the_title( $id );
endif;

// Heading type
$heading_type_classes     = array( 'widget-game-result__header' );
if ( alchemists_sp_preset( 'football' ) || alchemists_sp_preset( 'esports' ) ) {
	$heading_type_classes[] = 'widget-game-result__header--alt';
}

$game_result_main_classes = array( 'widget-game-result__main' );
$game_result_template = 'horizontal';
if ( 'vertical' == $layout_style ) {
	$game_result_main_classes[] = 'widget-game-result__main--vertical';
	$game_result_template = 'vertical';
}
?>


<div class="card card--no-paddings">

	<?php
	if ( $title ) {
		$header_output = '<header class="card__header">';
			$header_output .= '<h4>';
				$header_output .= esc_html( $title );
			$header_output .= '</h4>';

			if ( $expand_btn ) {
				$inputId = uniqid( 'expand-stats-' );
				$header_output .= '<div class="switch">';
					$header_output .= '<span class="switch__label-txt" data-text-expand="' . esc_attr__( 'Expand Stats', 'alc-advanced-posts' ) . '" data-text-shrink="' . esc_attr__( 'Shrink Stats', 'alc-advanced-posts' ) . '">' . esc_html__( 'Expand Stats', 'alc-advanced-posts' ) . '</span>';
					$header_output .= '<input id="' . $inputId . '" class="alc-switch-toggle" type="checkbox">';
					$header_output .= '<label for="' . $inputId . '"></label>';
				$header_output .= '</div>';
			}

		$header_output .= '</header>';

		echo $header_output;
	}
	?>

	<div class="card__content">

		<?php
		$i = 0;

		if ( intval( $number ) > 0 ) {
			$limit = $number;
		}

		foreach ( $data as $event ):
			if ( isset( $limit ) && $i >= $limit ) continue;

			$event_obj      = new SP_Event( $event->ID );
			$permalink      = get_post_permalink( $event, false, true );
			$results        = $event_obj->results();
			$primary_result = alchemists_sportspress_primary_result();
			$event_date     = $event->post_date;
			$teams          = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
			$teams          = array_filter( $teams, 'sp_filter_positive' );

			// The first row should be column labels
			$labels = $results[0];

			// Remove the first row to leave us with the actual data
			unset( $results[0] );

			$results = array_filter( $results );

			$event_id       = $event;

			$team1 = null;
			$team2 = null;

			if (count($teams) > 1) {
				$team1 = $teams[0];
				$team2 = $teams[1];
			}

			$team1_color_primary   = get_field( 'team_color_primary', $team1 );
			$team1_color_secondary = get_field( 'team_color_secondary', $team1 );
			$team2_color_primary   = get_field( 'team_color_primary', $team2 );
			$team2_color_secondary = get_field( 'team_color_secondary', $team2 );

			$venue1_desc = wp_get_post_terms($team1, 'sp_venue');
			$venue2_desc = wp_get_post_terms($team2, 'sp_venue');

			?>

			<!-- Game Score -->
			<div class="widget-game-result__section">
				<div class="widget-game-result__section-inner">

					<?php if ( $link_events ) : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="widget-game-result__item">
					<?php else : ?>
						<div class="widget-game-result__item">
					<?php endif; ?>

					<header class="<?php echo esc_attr( implode( ' ', $heading_type_classes ) ); ?>">
						<?php if ( $show_league ): $leagues = get_the_terms( $event, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
							<h3 class="widget-game-result__title">
								<?php echo esc_html( $league->name ); ?>

								<?php if ( $show_season ): $seasons = get_the_terms( $event, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons ); ?>
									<?php echo esc_html( $season->name ); ?>
								<?php endif; endif; ?>

							</h3>
						<?php endif; endif; ?>

						<time class="widget-game-result__date" datetime="<?php echo esc_attr( $event_date ); ?>"><?php echo esc_html( get_the_time( sp_date_format() . ' - ' . sp_time_format(), $event ) ); ?></time>

					</header>

					<?php if ( alchemists_sp_preset( 'esports' ) ) : ?>
						<header class="widget-game-result__header">
							<?php if ( $show_league ): $leagues = get_the_terms( $event, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
								<h3 class="widget-game-result__title">
									<?php echo esc_html( $league->name ); ?>

									<?php if ( $show_season ): $seasons = get_the_terms( $event, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons ); ?>
										<?php echo esc_html( $season->name ); ?>
									<?php endif; endif; ?>

								</h3>
							<?php endif; endif; ?>

							<?php
							if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) :
								$matchday = get_post_meta( $event->ID, 'sp_day', true );
								if ( $matchday != '' ) :
								?>
								<div class="widget-game-result__subtitle"><?php echo $matchday; ?></div>
								<?php
								endif;
							endif;
							?>

						</header>
					<?php endif; ?>


					<div class="<?php echo esc_attr( implode( ' ', $game_result_main_classes ) ); ?>">
						<?php
						// 1st Team
						$team1_class = 'widget-game-result__score-result--loser';
						if (!empty($results)) {
							if (!empty($results[$team1])) {
								if (isset($results[$team1]['outcome']) && !empty($results[$team1]['outcome'][0])) {
									if ( $results[$team1]['outcome'][0] == 'win' ) {
										$team1_class = 'widget-game-result__score-result--winner';
									}
								}
							}
						}

						// 2nd Team
						$team2_class = 'widget-game-result__score-result--loser';
						if (!empty($results)) {
							if (!empty($results[$team2])) {
								if (isset($results[$team2]['outcome']) && !empty($results[$team2]['outcome'][0])) {
									if ( $results[$team2]['outcome'][0] == 'win' ) {
										$team2_class = 'widget-game-result__score-result--winner';
									}
								}
							}
						}

						?>

						<?php
						$j = 0;
						$team_img_size = alchemists_sp_preset( 'esports' ) ? 'alchemists_team-logo-fit' : 'alchemists_team-logo-sm-fit';
						foreach( $teams as $team ):
							$j++;

							include( ALCADVPOSTS_PLUGIN_DIR . '/sportspress/widgets/widget-alc-game-result/widget-alc-game-result-' . $game_result_template . '.php' );

						endforeach;
						?>

						<?php if ( $game_result_template != 'vertical' ) : ?>
							<div class="widget-game-result__score-wrap">
								<div class="widget-game-result__score">

									<!-- 1st Team -->
									<span class="widget-game-result__score-result <?php echo esc_attr( $team1_class ); ?>">
										<?php if (!empty($results)) {
											if (!empty($results[$team1]) && !empty($results[$team2])) {
												if (isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result])) {
													echo esc_html( $results[$team1][$primary_result] );
												}
											}
										} ?>
									</span>
									<!-- 1st Team / End -->

									<span class="widget-game-result__score-dash">-</span>

									<!-- 2nd Team -->
									<span class="widget-game-result__score-result <?php echo esc_attr( $team2_class ); ?>">
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

								<?php
								if ( $event->post_status === 'publish' ) :
									if ( alchemists_sp_preset( 'esports' ) ) {
										// Game Video
										$video_url = get_post_meta( $event->ID, 'sp_video', true );
										if ( $video_url ) {
											echo '<span href="' . esc_url( $video_url ) . '" class="game-result__score-video-icon mp_iframe" data-toggle="tooltip" data-placement="bottom" title="' . esc_attr( 'Watch Replay', 'alc-advanced-posts' ) . '"><i class="fa fa-play"></i></span>';
										}
									} else {
										echo '<div class="widget-game-result__score-label">' . esc_html__( 'Final Score', 'alc-advanced-posts' ) . '</div>';
									}
								else:
									if ( $show_venue ) {
										$venues = get_the_terms( $event, 'sp_venue' );
										if ( $venues ) {
											$venue = array_shift( $venues );
											echo '<div class="widget-game-result__score-label">' . esc_html( $venue->name ) . '</div>';
										}
									}
								endif;
								?>
							</div>

						<?php endif; ?>

					</div>

					<?php
					$i++; ?>

					<?php if ( $link_events ) : ?>
						</a><!-- .widget-game-result__item -->
					<?php else : ?>
						</div><!-- .widget-game-result__item -->
					<?php endif; ?>

				</div>
			</div>
			<!-- Game Score / End -->


			<?php if ( alchemists_sp_preset( 'soccer' ) && $show_timeline ) : ?>
				<?php
				// Timeline
				$event_timeline_type = 'vertical';
				if ( ! empty( $results ) ) :
					if ( ! empty( $results[ $team1 ] ) && ! empty( $results[ $team2 ] ) ) :
						// Get linear timeline from event
						$event = new SP_Event( $event_id );
						$timeline = $event->timeline( false, true );

						// Return if timeline is empty
						if ( ! empty( $timeline ) ) :

							// Get full time of event
							$event_minutes = $event->minutes();

							// Initialize spacer
							$previous = 0;
							?>

					<div class="widget-game-result__section">

						<?php include( ALCADVPOSTS_PLUGIN_DIR . '/sportspress/event/alc-event-timeline-' . $event_timeline_type . '.php' ); ?>

					</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>


		<?php if ( alchemists_sp_preset( 'football' ) || alchemists_sp_preset('basketball' ) ) : ?>
			<?php if (!empty($results)) : ?>
				<?php if (!empty($results[$team1]) && !empty($results[$team2])) : ?>
					<?php if ( isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result]) ) : ?>
						<!-- Scoreboard -->
						<div class="widget-game-result__section">
							<div class="widget-game-result__table-stats">
								<?php echo alchemists_sp_event_results( $results, $labels ); ?>
							</div>
						</div>
						<!-- Scoreboard / End -->
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>


		<?php if ( $show_stats ) :

			if ( alchemists_sp_preset( 'soccer' ) ) :

			// Sport - Soccer

			$event_performance = sp_get_performance( $event );

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

			// Progress Bar

			// Team #1
			$bar_color_1 = '';
			if ( $team1_color_primary ) {
				$bar_color_1 = 'background-color: ' . $team1_color_primary . ';';
			}

			// Team #2
			$bar_color_2 = '';
			if ( $team2_color_primary ) {
				$bar_color_2 = 'background-color: ' . $team2_color_primary . ';';
			}

			// Game Stats
			$game_stats = array();
			if ( null !== $performance ) {
				$game_stats = array_values( $performance );
			} else {
				$game_stats = array( 'sh', 'sog', 'ck', 'f', 'yellowcards', 'redcards' );
			}

			$game_stats_array = array();
			$game_stats_array = alchemists_sp_filter_array( $performances_posts_array, $game_stats );
			?>

			<!-- Game Statistics -->
			<div class="widget-game-result__section">
				<header class="widget-game-result__subheader card__subheader card__subheader--sm card__subheader--nomargins">
					<h5 class="widget-game-result__subtitle"><?php esc_html_e( 'Game Statistics', 'alc-advanced-posts' ); ?></h5>
				</header>
				<div class="widget-game-result__section-inner">

					<?php
					$k = 0;
					$game_stats_array_length = count( $game_stats_array );
					foreach ($game_stats_array as $game_stat_key => $game_stat_excerpt) :

						// Event Stats
						if (isset( $event_performance[$team1][0][$game_stat_key] ) && !empty( $event_performance[$team1][0][$game_stat_key] )) {
							$event_team1_stat = $event_performance[$team1][0][$game_stat_key];
						} else {
							$event_team1_stat = 0;
						}

						if (isset( $event_performance[$team2][0][$game_stat_key] ) && !empty( $event_performance[$team2][0][$game_stat_key] )) {
							$event_team2_stat = $event_performance[$team2][0][$game_stat_key];
						} else {
							$event_team2_stat = 0;
						}

						$event_total_stat = $event_team1_stat + $event_team2_stat;
						if ( $event_total_stat <= '0' ) {
							$event_total_stat = '1';
						}
						$event_team1_stat_pct = round( ( $event_team1_stat / $event_total_stat ) * 100 );
						$event_team2_stat_pct = round( ( $event_team2_stat / $event_total_stat ) * 100 );

						$event_team1_stat_highlight = '';
						$event_team2_stat_highlight = '';

						if ( $event_team1_stat > $event_team2_stat ) {
							$event_team1_stat_highlight = 'progress__digit--highlight';
						} else {
							$event_team2_stat_highlight = 'progress__digit--highlight';
						}

						if ( 1 == $k && $expand_btn ) {
							echo '<div class="widget-game-result__extra-stats">';
						}
						?>

						<div class="progress-double-wrapper">
							<h6 class="progress-title"><?php echo esc_html( $game_stat_excerpt['excerpt'] ); ?></h6>
							<div class="progress-inner-holder">
								<div class="progress__digit progress__digit--left <?php echo esc_attr( $event_team1_stat_highlight ); ?>">
									<?php echo esc_html( $event_team1_stat ); ?>
								</div>
								<div class="progress__double">
									<div class="progress">
										<div class="progress__bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team1_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $event_team1_stat_pct ); ?>%; <?php echo ! empty( $bar_color_1 ) ? $bar_color_1 : ''; ?>"></div>
									</div>
									<div class="progress">
										<div class="progress__bar progress__bar--success" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo ! empty( $bar_color_2 ) ? $bar_color_2 : ''; ?>"></div>
									</div>
								</div>
								<div class="progress__digit progress__digit--right <?php echo esc_attr( $event_team2_stat_highlight ); ?>">
									<?php echo esc_html( $event_team2_stat ); ?>
								</div>
							</div>
						</div>

					<?php
					if ( $game_stats_array_length - 1 == $k && $expand_btn ) {
						echo '</div>';
					}

					$k++;

					endforeach;
					?>

				</div>
			</div>
			<!-- Game Statistics / End -->

			<?php else : ?>

			<?php

			// Sport - Basketball and American Football
			$event_performance = sp_get_performance( $event );

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

			// Game Stats
			$game_stats = array();
			if ( null !== $performance ) {
				$game_stats = array_values( $performance );
			} else {
				if ( alchemists_sp_preset( 'football' ) ) {
					$game_stats = array( 'td', 'recyds', 'yds', 'att' );
				} elseif ( alchemists_sp_preset( 'basketball' ) ) {
					$game_stats = array( 'ast', 'reb', 'stl' );
				} elseif ( alchemists_sp_preset( 'esports' ) ) {
					$game_stats = array( 'kills', 'deaths', 'assists', 'gold' );
				}
			}

			// Progress Bar
			// Team #1
			$progress_bars_classes_1 = array( 'progress' );
			if ( alchemists_sp_preset( 'football' ) ) {
				$progress_bars_classes_1[] = 'progress--battery';
				if ( $team1_color_primary ) {
					$progress_bars_classes_1[] = 'progress--battery-custom';
				}
			}
			$progress_bars_classes_1 = implode( ' ', $progress_bars_classes_1 );

			$progress_bar_1_classes = array( 'progress__bar' );
			$bar_color_1 = '';

			if ( alchemists_sp_preset( 'football' ) ) {
				if ( $team1_color_primary ) {
					$bar_color_1 = 'color: ' . $team1_color_primary . ';';
				}
			} else {
				if ( $team1_color_primary ) {
					$bar_color_1 = 'background-color: ' . $team1_color_primary . ';';
				}
			}
			$progress_bar_1_classes = implode( ' ', $progress_bar_1_classes );


			// Team #2
			$progress_bars_classes_2 = array( 'progress' );
			if ( alchemists_sp_preset( 'football' ) ) {
				$progress_bars_classes_2[] = 'progress--battery';
				if ( $team2_color_primary ) {
					$progress_bars_classes_2[] = 'progress--battery-custom';
				}
			}
			$progress_bars_classes_2 = implode( ' ', $progress_bars_classes_2 );

			$progress_bar_2_classes = array( 'progress__bar' );
			$bar_color_2 = '';

			if ( alchemists_sp_preset( 'football' ) ) {
				$progress_bar_2_classes[] = 'progress__bar--success';
				if ( $team2_color_primary ) {
					$bar_color_2 = 'color: ' . $team2_color_primary . ';';
				}
			} else {
				$progress_bar_2_classes[] = 'progress__bar--info';
				if ( $team2_color_primary ) {
					$bar_color_2 = 'background-color: ' . $team2_color_primary . ';';
				}
			}
			$progress_bar_2_classes = implode( ' ', $progress_bar_2_classes );


			$game_stats_array = array();
			$game_stats_array = alchemists_sp_filter_array( $performances_posts_array, $game_stats );
			?>

			<!-- Game Statistics -->
			<div class="widget-game-result__section">
				<header class="widget-game-result__subheader card__subheader card__subheader--sm card__subheader--nomargins">
					<h5 class="widget-game-result__subtitle"><?php esc_html_e( 'Game Statistics', 'alc-advanced-posts' ); ?></h5>
				</header>
				<div class="widget-game-result__section-inner">

					<?php
					$k = 0;
					$game_stats_array_length = count( $game_stats_array );

					if ( alchemists_sp_preset( 'esports' ) ) :
						?>

						<!-- Progress Stats Table -->
						<table class="progress-table progress-table--sm-space">
							<tbody>

								<?php
								// eSports
								foreach ( $game_stats_array as $game_stat_key => $game_stat_label ) :

									// if ( 1 == $k && $expand_btn ) {
									// 	echo '<div class="widget-game-result__extra-stats">';
									// }

									$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
									$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
									$event_team1_stat     = isset( $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_key ] ) ? $event_performance[ $team1 ][ $event_team1_first_array ][ $game_stat_key ] : 0;
									$event_team2_stat     = isset( $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_key ] ) ? $event_performance[ $team2 ][ $event_team2_first_array ][ $game_stat_key ] : 0;
									$event_team1_stat_pct = alchemists_sp_performance_percent( $event_team1_stat, $event_team2_stat );
									$event_team2_stat_pct = alchemists_sp_performance_percent( $event_team2_stat, $event_team1_stat );
									?>

									<tr>
										<td class="progress-table__progress-label <?php echo esc_attr( $event_team1_stat > $event_team2_stat ? 'progress-table__progress-label--highlight' : '' ); ?>"><?php echo esc_html( alchemists_format_big_number( $event_team1_stat ), 1, true ); ?></td>
										<td class="progress-table__progress-bar progress-table__progress-bar--first">
											<div class="progress progress--lines">
												<div class="progress__bar" style="width: <?php echo esc_attr( $event_team1_stat_pct ); ?>%; <?php echo ! empty( $bar_color_1 ) ? $bar_color_1 : ''; ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team1_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</td>
										<td class="progress-table__title"><?php echo esc_html( $game_stat_label['label'] ); ?></td>
										<td class="progress-table__progress-bar progress-table__progress-bar--second">
											<div class="progress progress--lines">
												<div class="progress__bar progress__bar--info" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo ! empty( $bar_color_2 ) ? $bar_color_2 : ''; ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</td>
										<td class="progress-table__progress-label <?php echo esc_attr( $event_team2_stat > $event_team1_stat ? 'progress-table__progress-label--highlight' : '' ); ?>"><?php echo esc_html( alchemists_format_big_number( $event_team2_stat ), 1, true ); ?></td>
									</tr>

									<?php
									// if ( $game_stats_array_length - 1 == $k && $expand_btn ) {
									// 	echo '</div>';
									// }

									$k++;

								endforeach;
								?>

							</tbody>
						</table>
						<!-- Progress Stats Table / End -->

					<?php
					else :

						// Basketball, Soccer, American Football
						foreach ( $game_stats_array as $game_stat_key => $game_stat_excerpt ) :

							// Event Stats
							if (isset( $event_performance[$team1][0][$game_stat_key] ) && !empty( $event_performance[$team1][0][$game_stat_key] )) {
								$event_team1_stat = $event_performance[$team1][0][$game_stat_key];
							} else {
								$event_team1_stat = 0;
							}

							if (isset( $event_performance[$team2][0][$game_stat_key] ) && !empty( $event_performance[$team2][0][$game_stat_key] )) {
								$event_team2_stat = $event_performance[$team2][0][$game_stat_key];
							} else {
								$event_team2_stat = 0;
							}

							$event_total_stat = $event_team1_stat + $event_team2_stat;
							if ( $event_total_stat <= '0' ) {
								$event_total_stat = '1';
							}
							$event_team1_stat_pct = round( ( $event_team1_stat / $event_total_stat ) * 100 );
							$event_team2_stat_pct = round( ( $event_team2_stat / $event_total_stat ) * 100 );

							$event_team1_stat_highlight = '';
							$event_team2_stat_highlight = '';

							if ( $event_team1_stat > $event_team2_stat ) {
								$event_team1_stat_highlight = 'progress__digit--highlight';
							} else {
								$event_team2_stat_highlight = 'progress__digit--highlight';
							}

							if ( 1 == $k && $expand_btn ) {
								echo '<div class="widget-game-result__extra-stats">';
							}
							?>

							<div class="progress-double-wrapper">
								<h6 class="progress-title"><?php echo esc_html( $game_stat_excerpt['excerpt'] ); ?></h6>
								<div class="progress-inner-holder">
									<div class="progress__digit progress__digit--left <?php echo esc_attr( $event_team1_stat_highlight ); ?>">
										<?php echo esc_html( $event_team1_stat ); ?>
									</div>
									<div class="progress__double">
										<div class="<?php echo esc_attr( $progress_bars_classes_1 ); ?>">
											<div class="<?php echo esc_attr( $progress_bar_1_classes ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team1_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $event_team1_stat_pct ); ?>%; <?php echo ! empty( $bar_color_1 ) ? $bar_color_1 : ''; ?>"></div>
										</div>
										<div class="<?php echo esc_attr( $progress_bars_classes_2 ); ?>">
											<div class="<?php echo esc_attr( $progress_bar_2_classes ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $event_team2_stat_pct ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $event_team2_stat_pct ); ?>%; <?php echo ! empty( $bar_color_2 ) ? $bar_color_2 : ''; ?>"></div>
										</div>
									</div>
									<div class="progress__digit progress__digit--right <?php echo esc_attr( $event_team2_stat_highlight ); ?>">
										<?php echo esc_html( $event_team2_stat ); ?>
									</div>
								</div>
							</div>

							<?php
							if ( $game_stats_array_length - 1 == $k && $expand_btn ) {
								echo '</div>';
							}

							$k++;

						endforeach;
					endif;
					?>

				</div>
			</div>
			<!-- Game Statistics / End -->

			<?php endif; ?>
		<?php endif; ?>


		<?php endforeach; ?>


	</div>
</div>
