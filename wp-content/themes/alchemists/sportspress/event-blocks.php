<?php
/**
 * Event Blocks
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version     2.7.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$defaults = array(
	'id'                   => null,
	'event'                => null,
	'title'                => false,
	'status'               => 'default',
	'format'               => 'default',
	'date'                 => 'default',
	'date_from'            => 'default',
	'date_to'              => 'default',
	'date_past'            => 'default',
	'date_future'          => 'default',
	'date_relative'        => 'default',
	'day'                  => 'default',
	'league'               => null,
	'season'               => null,
	'venue'                => null,
	'team'                 => null,
	'teams_past'           => null,
	'date_before'          => null,
	'player'               => null,
	'number'               => -1,
	'show_team_logo'       => get_option( 'sportspress_event_blocks_show_logos', 'yes' ) == 'yes' ? true : false,
	'link_teams'           => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_events'          => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'paginated'            => get_option( 'sportspress_event_blocks_paginated', 'yes' ) == 'yes' ? true : false,
	'rows'                 => get_option( 'sportspress_event_blocks_rows', 5 ),
	'orderby'              => 'default',
	'order'                => 'default',
	'columns'              => null,
	'show_all_events_link' => false,
	'show_title'           => get_option( 'sportspress_event_blocks_show_title', 'no' ) == 'yes' ? true : false,
	'show_league'          => get_option( 'sportspress_event_blocks_show_league', 'no' ) == 'yes' ? true : false,
	'show_season'          => get_option( 'sportspress_event_blocks_show_season', 'no' ) == 'yes' ? true : false,
	'show_matchday'        => get_option( 'sportspress_event_blocks_show_matchday', 'no' ) == 'yes' ? true : false,
	'show_venue'           => get_option( 'sportspress_event_blocks_show_venue', 'no' ) == 'yes' ? true : false,
	'hide_if_empty'        => false,
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );

if ( $status != 'default' ) {
	$calendar->status = $status;
}
if ( $format != 'default' ) {
	$calendar->event_format = $format;
}
if ( $date != 'default' ) {
	$calendar->date = $date;
}
if ( $date_from != 'default' ) {
	$calendar->from = $date_from;
}
if ( $date_to != 'default' ) {
	$calendar->to = $date_to;
}
if ( $date_past != 'default' ) {
	$calendar->past = $date_past;
}
if ( $date_future != 'default' ) {
	$calendar->future = $date_future;
}
if ( $date_relative != 'default' ) {
	$calendar->relative = $date_relative;
}
if ( $event ) {
	$calendar->event = $event;
}
if ( $league ) {
	$calendar->league = $league;
}
if ( $season ) {
	$calendar->season = $season;
}
if ( $venue ) {
	$calendar->venue = $venue;
}
if ( $team ) {
	$calendar->team = $team;
}
if ( $teams_past ) {
	$calendar->teams_past = $teams_past;
}
if ( $date_before ) {
	$calendar->date_before = $date_before;
}
if ( $player ) {
	$calendar->player = $player;
}
if ( $order != 'default' ) {
	$calendar->order = $order;
}
if ( $orderby != 'default' ) {
	$calendar->orderby = $orderby;
}
if ( $day != 'default' ) {
	$calendar->day = $day;
}
$data       = $calendar->data();
$usecolumns = $calendar->columns;

if ( isset( $columns ) ) :
	if ( is_array( $columns ) ) {
		$usecolumns = $columns;
	} else {
		$usecolumns = explode( ',', $columns );
	}
endif;

if ( $hide_if_empty && empty( $data ) ) {
	return false;
}

if ( $show_title && false === $title && $id ) :
	$caption = $calendar->caption;
	if ( $caption ) {
		$title = $caption;
	} else {
		$title = get_the_title( $id );
	}
endif;


$card_header_btn_classes = array(
	'btn',
	'btn-xs',
	'btn-default',
	'card-header__button',
);

// make it outline
if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
	$card_header_btn_classes[] = 'btn-outline';
}
?>


<div class="card card--no-paddings">

	<?php if ( $title ) {
		echo '<header class="card__header"><h4 class="sp-table-caption">' . esc_html( $title ) . '</h4>';

		if ( $id && $show_all_events_link ) {
			echo '<a href="' . get_permalink( $id ) . '" class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '">' . esc_html__( 'View all events', 'alchemists' ) . '</a>';
		}

		echo '</header>';
	} ?>

	<div class="card__content">
		<div class="widget-game-result__section">
			<div class="widget-game-result__section-inner">

				<table class="alc-event-blocks table--no-border table--no-paddings sp-data-table<?php if ( $paginated ) { ?> sp-paginated-table<?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
					<thead><tr><th></th></tr></thead> <?php # Required for DataTables ?>
					<tbody>

						<?php
						$i = 0;

						if ( intval( $number ) > 0 ) {
							$limit = $number;
						}

						foreach ( $data as $event ) :
							if ( isset( $limit ) && $i >= $limit ) {
								continue;
							}

							$permalink      = get_post_permalink( $event, false, true );
							$results        = get_post_meta( $event->ID, 'sp_results', true );
							$primary_result = alchemists_sportspress_primary_result();
							$event_date     = $event->post_date;
							$teams          = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
							$teams          = array_filter( $teams, 'sp_filter_positive' );
							$event_status   = get_post_meta( $event->ID, 'sp_status', true );

							if ( get_option( 'sportspress_event_reverse_teams', 'no' ) === 'yes' ) {
								$teams = array_reverse( $teams , true );
								$results = array_reverse( $results , true );
							}

							if (count($teams) > 1) {
								$team1 = $teams[0];
								$team2 = $teams[1];
							}

							$venue1_desc = wp_get_post_terms($team1, 'sp_venue');
							$venue2_desc = wp_get_post_terms($team2, 'sp_venue');

							?>

							<tr class="sp-row sp-post<?php if ( $i % 2 == 0 ) { echo esc_attr( ' alternate' ); } ?>" itemscope itemtype="http://schema.org/SportsEvent">
								<td>

									<div class="widget-game-result__item widget-game-result__item--<?php echo esc_attr( $event_status ); ?>">

									<header class="widget-game-result__header">
										<?php if ( $show_league ): $leagues = get_the_terms( $event, 'sp_league' ); if ( $leagues ): $league = array_shift( $leagues ); ?>
											<h3 class="widget-game-result__title">
												<?php echo esc_html( $league->name ); ?>

												<?php if ( $show_season ): $seasons = get_the_terms( $event, 'sp_season' ); if ( $seasons ): $season = array_shift( $seasons ); ?>
													<?php echo esc_html( $season->name ); ?>
												<?php endif; endif; ?>

											</h3>
										<?php endif; endif; ?>

										<time class="widget-game-result__date" datetime="<?php echo esc_attr( $event_date ); ?>" itemprop="startDate" content="<?php echo mysql2date( 'Y-m-d\TH:i:sP', $event_date ); ?>">
											<?php
											// Event Date and Time or Time Status (OK, Postponed, TBD, Canceled)
											echo esc_html( get_the_time( sp_date_format(), $event->ID ) ) . alchemists_event_time_status_badge( $event->ID );
											?>
										</time>

										<?php if ( $show_matchday ): $matchday = get_post_meta( $event->ID, 'sp_day', true ); if ( $matchday != '' ): ?>
										<div class="widget-game-result__matchday">(<?php echo esc_html( $matchday ); ?>)</div>
										<?php endif; endif; ?>

									</header>


									<div class="widget-game-result__main">

										<?php
										$j = 0;
										foreach ( $teams as $team ) :
											$j++;
											$team_name = get_the_title( $team );

											echo '<div class="widget-game-result__team widget-game-result__team--' . ( $j % 2 ? 'odd' : 'even' ) . '">';
												echo '<figure class="widget-game-result__team-logo">';
													if ( has_post_thumbnail ( $team ) ):
														$logo = get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit', array( 'itemprop' => 'logo' ) );

														if ( $link_teams ):
															$team_permalink = get_permalink( $team, false, true );
															$logo = '<a href="' . $team_permalink . '" itemprop="url" content="' . $team_permalink . '">' . $logo . '</a>';
														endif;

														$logo = '<span class="team-logo logo-' . ( $j % 2 ? 'odd' : 'even' ) . '" title="' . esc_attr( $team_name ) . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam"><meta itemprop="name" content="' . esc_attr( $team_name ) . '">' . $logo . '</span>';
													else :
														$logo = '<span itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam"><meta itemprop="name" content="' . esc_attr( $team_name ) . '"></span>';
													endif;
													echo wp_kses_post( $logo );
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

										endforeach;
										?>

										<div class="widget-game-result__score-wrap">
											<?php if ( $link_events ) : ?>
												<a href="<?php echo esc_url( $permalink ); ?>" class="widget-game-result__score">
											<?php else : ?>
												<div class="widget-game-result__score">
											<?php endif; ?>

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

											<?php if ( $link_events ) : ?>
												</a>
											<?php else : ?>
												</div>
											<?php endif; ?>

											<?php if ( $event->post_status === 'publish' ): ?>
											<div class="widget-game-result__score-label"><?php esc_html_e( 'Final Score', 'alchemists' ); ?></div>
											<?php endif; ?>

											<?php if ( $show_venue ): $venues = get_the_terms( $event, 'sp_venue' ); if ( $venues ): $venue = array_shift( $venues ); ?>
											<div class="widget-game-result__venue"><?php echo esc_html( $venue->name ); ?></div>
											<?php endif; endif; ?>

											<?php do_action( 'sportspress_event_blocks_row', $event ); ?>
										</div>

									</div>

								</div><!-- .widget-game-result__item -->
							</td>
						</tr>
						<?php
						$i++;
					endforeach;
					?>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>
