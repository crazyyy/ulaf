<?php
/**
 * Game-by-game Stats
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     2.0.0
 * @version   4.4.15
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
	'team' => 'default',
	'player' => null,
	'number' => $number,
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
	'player_id' => null,
	'player_stats' => null,
);

extract( $defaults, EXTR_SKIP );

$datas = [];

foreach ( $team as $current_team ) {
	$calendar = new SP_Calendar( $id );
	if ( $status != 'default' ) {
		$calendar->status = $status;
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
	if ( $league ) {
		$calendar->league = $league;
	}
	if ( $season ) {
		$calendar->season = $season;
	}
	if ( $venue ) {
		$calendar->venue = $venue;
	}
	if ( $current_team ) {
		$calendar->team = $current_team;
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

	$datas[] = $calendar->data();
}

// echo '<pre>' . var_export( $datas, true ). '</pre>';

if ( $hide_if_empty && empty( $datas ) ) return false;

if ( $show_title && false === $title && $id ):
	$caption = $calendar->caption;
	if ( $caption )
		$title = $caption;
	else
		$title = get_the_title( $id );
endif;


// Player Stats
$player_stats_array = array();

// convert stats string to array
if ( ! empty( $player_stats ) ) {
	$player_stats_array = array_map( 'trim', explode(',', $player_stats ) );
}

// and get labels
$performances_array = array();
foreach ( $player_stats_array as $player_stat_single ) {
	$performances = get_post( $player_stat_single );
	if ( isset( $performances->post_name ) ) {
		$performances_array[ $performances->post_name ] = array(
			'title'   => $performances->post_title,
			'excerpt' => $performances->post_excerpt
		);
	}
}

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


<!-- Last Game Log -->
<div class="card card--has-table">

	<?php if ( $title ) {
		echo '<header class="card__header"><h4 class="sp-table-caption">' . esc_html( $title ) . '</h4>';

		if ( $id && $show_all_events_link ) {
			echo '<a href="' . get_permalink( $id ) . '" class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '">' . esc_html__( 'View all events', 'alchemists' ) . '</a>';
		}

		echo '</header>';
	} ?>

	<div class="card__content">
		<div class="table-responsive">
			<table class="table table-hover game-player-result">
				<thead>
					<tr>
						<th class="game-player-result__date"><?php esc_html_e( 'Date', 'alchemists' ); ?></th>
						<th class="game-player-result__vs"><?php esc_html_e( 'Versus', 'alchemists' ); ?></th>
						<th class="game-player-result__stat"><?php esc_html_e( 'Results', 'alchemists' ); ?></th>

						<?php foreach ( $performances_array as $performances_key => $performances_label ) : ?>
							<th class="game-player-result__stat data-<?php echo esc_attr( $performances_key ); ?>" title="<?php echo esc_attr( $performances_label['excerpt'] ); ?>">
								<?php echo esc_html( $performances_label['title'] ); ?>
							</th>
						<?php endforeach; ?>

					</tr>
				</thead>
				<tbody>

					<?php
					$j = 0;
					foreach ( $datas as $data ) :
						$current_team = $team[ $j ];

						if ( sizeof( $datas ) > 1 ) :
							?>
							<tr class="highlighted">
								<th class="game-player-result__team" colspan="<?php echo esc_attr( sizeof( $performances_array ) + 3 ); ?>">
									<div class="game-player-result__team-inner">
										<?php
										if ( has_post_thumbnail( $current_team ) ) :
											?>
											<div class="game-player-result__team-logo">
												<?php echo get_the_post_thumbnail( $current_team, 'sportspress-fit-mini' ); ?>
											</div>
											<?php
										endif;
										?>
										<div class="game-player-result__team-name"><?php echo sp_team_name( $current_team ); ?></div>
									</div>
								</th>
							</tr>
							<?php
						endif;
						$i = 0;

						if ( intval( $number ) > 0 ) {
							$limit = $number;
						}


						foreach ( $data as $event ) :
							if ( isset( $limit ) && $i >= $limit ) continue;

							// Check if Player is a current team member
							$event_players = get_post_meta( $event->ID, 'sp_players' );
							if ( isset( $event_players[0][ $current_team ] ) ) {
								$event_players_current_team = array_keys( $event_players[0][ $current_team ] );
								if ( ! in_array( $player_id, $event_players_current_team ) ) {
									continue;
								}
							}

							// Check if Player took a part in the event
							if ( $only_played ) {
								$event_players = get_post_meta( $event->ID, 'sp_player' );
								if ( ! in_array( $player_id, $event_players ) ) {
									continue;
								}
							}

							$permalink      = get_post_permalink( $event, false, true );
							$results        = get_post_meta( $event->ID, 'sp_results', true );
							$primary_result = alchemists_sportspress_primary_result();
							$event_date     = $event->post_date;
							$teams          = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
							$teams          = array_filter( $teams, 'sp_filter_positive' );
							?>

							<tr>
								<td class="game-player-result__date">
									<time datetime="<?php echo esc_attr( $event_date ); ?>"><?php echo get_the_time( get_option('date_format'), $event ); ?></time>
								</td>
								<td class="game-player-result__vs">
									<?php foreach( $teams as $team_single ):

										// find only the opponent team
										if ( $current_team != $team_single ) :

											// venue
											$venue = wp_get_post_terms($team_single, 'sp_venue');
											$venue_desc = false;
											if ( $venue ) {
												$venue_desc = $venue[0]->description;
											}

											echo '<div class="team-meta">';
												echo '<figure class="team-meta__logo">';
													if ( has_post_thumbnail ( $team_single ) ):
														echo get_the_post_thumbnail( $team_single, 'sportspress-fit-mini' );
													endif;
												echo '</figure>';
												echo '<div class="team-meta__info">';
													echo '<h6 class="team-meta__name">' . esc_html( get_the_title( $team_single ) ) . '</h6>';
													if ( $venue_desc ) {
														echo '<span class="team-meta__place">' . $venue_desc . '</span>';
													}
												echo '</div>';
											echo '</div>';
										endif;
									endforeach; ?>

								</td>
								<td class="game-player-result__score">
									<span class="game-player-result__game">
										<?php
										foreach( $teams as $team_single ):
											if ( $current_team == $team_single ) :
												if ( !empty( $results ) ) {
													if ( !empty( $results[ $current_team ] ) ) {
														if ( isset( $results[ $current_team ]['outcome']) && !empty( $results[ $current_team ]['outcome'][0])) {
															if ( $results[ $current_team ]['outcome'][0] == 'win' ) {
																echo '<span class="game-player-result__score-win">' . esc_html( 'W', 'alchemists' ) . '</span>';
															} elseif ( $results[ $current_team ]['outcome'][0] == 'loss' ) {
																echo '<span class="game-player-result__score-lose">' . esc_html( 'L', 'alchemists' ) . '</span>';
															} elseif ( $results[ $current_team ]['outcome'][0] == 'draw' ) {
																esc_html_e( 'D', 'alchemists' );
															}
														}
													}
												}
											endif;
										endforeach;
										?>
									</span>
									<?php echo sp_add_link( '<span class="sp-result">' . implode( '</span> - <span class="sp-result">', apply_filters( 'sportspress_event_blocks_team_result_or_time', sp_get_main_results_or_time( $event ), $event->ID ) ) . '</span>', $permalink, $link_events ); ?>
								</td>

								<?php
								$event_performance = sp_get_performance( $event );

								// Remove the first row to leave us with the actual data
								unset( $event_performance[0] );


								// Display selected stats
								foreach ( $performances_array as $performances_key => $performances_label ) :

									if ( isset( $event_performance[$current_team][$player_id][$performances_key] ) ) {
										$player_stat = $event_performance[$current_team][$player_id][$performances_key];
									} else {
										$player_stat = '0';
									}

									?>

									<td class="game-player-result__stat data-<?php echo esc_attr( $performances_key ); ?>"><?php echo esc_html( $player_stat ); ?></td>

								<?php endforeach; ?>


							</tr>

						<?php
						$i++;
						endforeach;
						$j++;
					endforeach;
					?>

				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- Last Game Log / End -->
