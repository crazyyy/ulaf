<?php
/**
 * Event Blocks
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'title' => false,
	'status' => 'default',
	'date' => 'default',
	'date_from' => 'default',
	'date_to' => 'default',
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
);

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

		<ul class="widget-results__list">

			<?php
			$i = 0;

			if ( intval( $number ) > 0 ) {
				$limit = $number;
			}

			foreach ( $data as $event ):
				if ( isset( $limit ) && $i >= $limit ) continue;

				$permalink      = get_post_permalink( $event, false, true );
				$results        = get_post_meta( $event->ID, 'sp_results', true );
				$primary_result = alchemists_sportspress_primary_result();
				$event_date     = $event->post_date;
				$teams          = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
				$teams          = array_filter( $teams, 'sp_filter_positive' );

				if (count($teams) > 1) {
					$team1 = $teams[0];
					$team2 = $teams[1];
				}

				?>

				<li class="widget-results__item">

					<?php if ( $link_events ) : ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="widget-results__item-link">
					<?php endif; ?>

						<h5 class="widget-results__title">
							<time datetime="<?php echo esc_attr( $event_date ); ?>">
								<?php
								// Event Date and Time or Time Status (OK, Postponed, TBD, Canceled)
								echo esc_html( get_the_time( sp_date_format(), $event->ID ) ) . alchemists_event_time_status_badge( $event->ID );
								?>
							</time>
						</h5>

						<div class="widget-results__content">

							<?php
							$j = 0;
							foreach( $teams as $team ):
								$j++;

								echo '<div class="widget-results__team widget-results__team--' . ( $j % 2 ? 'odd' : 'even' ) . '">';
									echo '<figure class="widget-results__team-logo">';
										if ( has_post_thumbnail ( $team ) ):
											echo get_the_post_thumbnail( $team, 'sportspress-fit-mini' );
										endif;
									echo '</figure>';
									echo '<div class="widget-results__team-details">';
										echo '<h5 class="widget-results__team-name">' . esc_html( get_the_title( $team ) ) . '</h5>';
									echo '</div>';
								echo '</div>';

							endforeach;
							?>

							<div class="widget-results__result">
								<div class="widget-results__score">

									<?php

									// 1st Team
									$team1_class = 'widget-results__score-loser';
									if (!empty($results)) {
										if (!empty($results[$team1])) {
											if (isset($results[$team1]['outcome']) && !empty($results[$team1]['outcome'][0])) {
												if ( $results[$team1]['outcome'][0] == 'win' ) {
													$team1_class = 'widget-results__score-winner';
												}
											}
										}
									}

									// 2nd Team
									$team2_class = 'widget-results__score-loser';
									if (!empty($results)) {
										if (!empty($results[$team2])) {
											if (isset($results[$team2]['outcome']) && !empty($results[$team2]['outcome'][0])) {
												if ( $results[$team2]['outcome'][0] == 'win' ) {
													$team2_class = 'widget-results__score-winner';
												}
											}
										}
									}

									?>

									<!-- 1st Team -->
									<span class="<?php echo esc_attr( $team1_class ); ?>">
										<?php if (!empty($results)) {
											if (!empty($results[$team1]) && !empty($results[$team2])) {
												if (isset($results[$team1][$primary_result]) && isset($results[$team2][$primary_result])) {
													echo esc_html( $results[$team1][$primary_result] );
												}
											}
										} ?>
									</span>
									<!-- 1st Team / End -->

									-

									<!-- 2nd Team -->
									<span class="<?php echo esc_attr( $team2_class ); ?>">
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
							</div>

						</div>

						<?php if ( $show_venue ): $venues = get_the_terms( $event, 'sp_venue' ); if ( $venues ): $venue = array_shift( $venues ); ?>
							<div class="widget-results__status"><?php echo esc_html( $venue->name ); ?></div>
						<?php endif; endif; ?>

					<?php
					$i++; ?>

				<?php if ( $link_events ) : ?>
				</a><!-- .widget-results__item-link -->
				<?php endif; ?>

			</li><!-- .widget-results__item -->

			<?php endforeach; ?>

		</ul>

	</div>
</div>
