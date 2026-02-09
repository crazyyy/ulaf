<?php
/**
 * Event List
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
	'show_team_logo'       => get_option( 'sportspress_event_list_show_logos', 'no' ) == 'yes' ? true : false,
	'link_events'          => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'link_teams'           => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_venues'          => get_option( 'sportspress_link_venues', 'yes' ) == 'yes' ? true : false,
	'responsive'           => get_option( 'sportspress_enable_responsive_tables', 'no' ) == 'yes' ? true : false,
	'sortable'             => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable'           => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'paginated'            => get_option( 'sportspress_event_list_paginated', 'yes' ) == 'yes' ? true : false,
	'rows'                 => get_option( 'sportspress_event_list_rows', 10 ),
	'order'                => 'default',
	'columns'              => null,
	'show_all_events_link' => false,
	'show_title'           => get_option( 'sportspress_event_list_show_title', 'yes' ) == 'yes' ? true : false,
	'title_format'         => get_option( 'sportspress_event_list_title_format', 'title' ),
	'time_format'          => get_option( 'sportspress_event_list_time_format', 'combined' ),
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

if ( $show_title && false === $title && $id ) :
	$caption = $calendar->caption;
	if ( $caption ) {
		$title = $caption;
	} else {
		$title = get_the_title( $id );
	}
endif;
$labels = array();
// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'eventlist_' );

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
<div class="sp-template sp-template-event-list card card--has-table">
	<?php if ( $title ) { ?>
		<header class="card__header">
			<h4 class="sp-table-caption"><?php echo esc_html( $title ); ?></h4>
			<?php
			if ( $id && $show_all_events_link )
				echo '<a href="' . get_permalink( $id ) . '" class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '">' . esc_html__( 'View all events', 'alchemists' ) . '</a>';
			?>
		</header>
	<?php } ?>
	<div class="card__content">
		<div class="sp-table-wrapper table-responsive">
			<table class="table table-hover team-schedule team-schedule--full sp-event-list sp-event-list-format-<?php echo esc_attr( $title_format ); ?> sp-data-table<?php if ( $paginated ) { ?> sp-paginated-table<?php } if ( $sortable ) { ?> sp-sortable-table<?php } if ( $responsive ) { echo ' sp-responsive-table '.$identifier; } if ( $scrollable ) { ?> sp-scrollable-table <?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
				<thead>
					<tr>
						<?php
						echo '<th class="data-date">' . esc_html__( 'Date', 'alchemists' ) . '</th>';

						switch ( $title_format ) {
							case 'homeaway':
								if ( sp_column_active( $usecolumns, 'event' ) ) {
									echo '<th class="data-home">' . esc_html__( 'Home', 'alchemists' ) . '</th>';
								}

								if ( 'combined' == $time_format && sp_column_active( $usecolumns, 'time' ) ) {
									echo '<th class="data-time">' . esc_html__( 'Time/Results', 'alchemists' ) . '</th>';
									$labels[] = esc_html__( 'Time/Results', 'alchemists' );
								} elseif ( in_array( $time_format, array( 'separate', 'results' ) ) && sp_column_active( $usecolumns, 'results' ) ) {
									echo '<th class="data-results">' . esc_html__( 'Results', 'alchemists' ) . '</th>';
								}

								if ( sp_column_active( $usecolumns, 'event' ) ) {
									echo '<th class="data-away">' . esc_html__( 'Away', 'alchemists' ) . '</th>';
								}

								if ( in_array( $time_format, array( 'separate', 'time' ) ) && sp_column_active( $usecolumns, 'time' ) ) {
									echo '<th class="data-time">' . esc_html__( 'Time', 'alchemists' ) . '</th>';
								}
								break;
							default:
								if ( sp_column_active( $usecolumns, 'event' ) ) {
									if ( $title_format == 'teams' ) {
										echo '<th class="data-teams">' . esc_html__( 'Teams', 'alchemists' ) . '</th>';
									} else {
										echo '<th class="data-event">' . esc_html__( 'Event', 'alchemists' ) . '</th>';
									}
								}

								switch ( $time_format ) {
									case 'separate':
										if ( sp_column_active( $usecolumns, 'time' ) )
											echo '<th class="data-time">' . esc_html__( 'Time', 'alchemists' ) . '</th>';
										if ( sp_column_active( $usecolumns, 'results' ) )
											echo '<th class="data-results">' . esc_html__( 'Results', 'alchemists' ) . '</th>';
										break;
									case 'time':
										if ( sp_column_active( $usecolumns, 'time' ) )
											echo '<th class="data-time">' . esc_html__( 'Time', 'alchemists' ) . '</th>';
										break;
									case 'results':
										if ( sp_column_active( $usecolumns, 'results' ) )
											echo '<th class="data-results">' . esc_html__( 'Results', 'alchemists' ) . '</th>';
										break;
									default:
										if ( sp_column_active( $usecolumns, 'time' ) )
											echo '<th class="data-time">' . esc_html__( 'Time/Results', 'alchemists' ) . '</th>';
								}
						}

						if ( sp_column_active( $usecolumns, 'league' ) )
							echo '<th class="data-league">' . esc_html__( 'League', 'alchemists' ) . '</th>';

						if ( sp_column_active( $usecolumns, 'season' ) )
							echo '<th class="data-season">' . esc_html__( 'Season', 'alchemists' ) . '</th>';

							if ( sp_column_active( $usecolumns, 'venue' ) ) {
								echo '<th class="data-venue">' . __( 'Venue', 'alchemists' ) . '</th>';
							}else{
								echo '<th style="display:none;" class="data-venue">' . __( 'Venue', 'alchemists' ) . '</th>';
							}

						if ( sp_column_active( $usecolumns, 'article' ) )
							echo '<th class="data-article">' . esc_html__( 'Article', 'alchemists' ) . '</th>';

						if ( sp_column_active( $usecolumns, 'day' ) )
							echo '<th class="data-day">' . esc_html__( 'Match Day', 'alchemists' ) . '</th>';

						do_action( 'sportspress_event_list_head_row', $usecolumns );
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 0;

					if ( is_numeric( $number ) && $number > 0 ) {
						$limit = $number;
					}

					foreach ( $data as $event ) :
						if ( isset( $limit ) && $i >= $limit ) {
							continue;
						}

						$teams  = get_post_meta( $event->ID, 'sp_team' );
						$video  = get_post_meta( $event->ID, 'sp_video', true );
						$status = get_post_meta( $event->ID, 'sp_status', true );

						$main_results = apply_filters( 'sportspress_event_list_main_results', sp_get_main_results( $event ), $event->ID );

						$reverse_teams = get_option( 'sportspress_event_reverse_teams', 'no' ) === 'yes' ? true : false;
						if ( $reverse_teams ) {
							$main_results = array_reverse( $main_results, true );
						}

						$teams_output = '';
						$team_class   = '';
						$teams_array  = array();
						$team_logos   = array();

						if ( $teams ) :
							foreach ( $teams as $t => $team ) :
								$name = sp_team_short_name( $team );
								if ( $name ) :

									$name = '<meta itemprop="name" content="' . $name . '">' . $name;

									if ( $show_team_logo ):
										if ( has_post_thumbnail( $team ) ):
											$logo         = '<span class="team-logo">' . sp_get_logo( $team, 'mini', array( 'itemprop' => 'url' ) ) . '</span>';
											$team_logos[] = $logo;
											$team_class  .= ' has-logo';

											if ( $t ) :
												$name = $logo . ' ' . $name;
											else :
												$name .= ' ' . $logo;
											endif;
										endif;
									endif;

									if ( $link_teams ) :
										$team_output = '<a href="' . get_post_permalink( $team ) . '" itemprop="url">' . $name . '</a>';
									else :
										$team_output = $name;
									endif;

									$team_result = sp_array_value( $main_results, $team, null );

									if ( $team_result != null ) :
										if ( $usecolumns != null && ! in_array( 'time', $usecolumns ) ) :
											$team_output .= ' (' . $team_result . ')';
										endif;
									endif;

									$teams_array[] = $team_output;

									$teams_output .= $team_output . '<br>';
								endif;
							endforeach;
						else :
							$teams_output .= '&mdash;';
						endif;

						echo '<tr class="sp-row sp-post' . ( $i % 2 == 0 ? ' alternate' : '' ) . ' sp-row-no-' . esc_attr( $i ) . '" itemscope itemtype="http://schema.org/SportsEvent">';

						$date_html = '<date>' . get_post_time( 'Y-m-d H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_date', get_post_time( get_option( 'date_format' ), false, $event, true ), $event->ID );

						if ( $link_events ) {
							$date_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '" itemprop="url">' . $date_html . '</a>';
						}

						echo '<td class="data-date" itemprop="startDate" content="' . esc_attr( mysql2date( 'Y-m-d\TH:i:sP', $event->post_date ) ) . '" data-label="' . esc_attr__( 'Date', 'alchemists' ) . '">' . wp_kses( $date_html, array( 'a' => array( 'href' => array(), 'itemprop' => array() ), 'date' => array() ) ) . '</td>';

							// Check if the reverse_teams option is selected and alter the teams order
							if ( $reverse_teams ) {
								$teams_array = array_reverse( $teams_array, true );
							}

							switch ( $title_format ) {
								case 'homeaway':
									if ( sp_column_active( $usecolumns, 'event' ) ) {
										$team = array_shift( $teams_array );
										echo '<td class="data-home' . esc_attr( $team_class ) . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_attr__( 'Home', 'alchemists' ) . '">' . wp_kses_post( $team ) . '</td>';
									}

									if ( 'combined' == $time_format && sp_column_active( $usecolumns, 'time' ) ) {
										echo '<td class="data-time ' . esc_attr( $status ) . '" data-label="' . esc_attr__( 'Time/Results', 'alchemists' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
										}
										if ( ! empty( $main_results ) ) :
											echo wp_kses_post( implode( ' - ', $main_results ) );
										else:
											echo '<date>&nbsp;' . wp_kses_post( get_post_time( 'H:i:s', false, $event ) ) . '</date>' . wp_kses_post( apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID ) );
										endif;
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									} elseif ( in_array( $time_format, array( 'separate', 'results' ) ) && sp_column_active( $usecolumns, 'results' ) ) {
										echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'alchemists' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
										}
										if ( ! empty( $main_results ) ) :
											echo wp_kses_post( implode( ' - ', $main_results ) );
										else :
											echo '-';
										endif;
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									}

									if ( sp_column_active( $usecolumns, 'event' ) ) {
										$team = array_shift( $teams_array );
										echo '<td class="data-away' . esc_attr( $team_class ) . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_attr__( 'Away', 'alchemists' ) . '">' . wp_kses_post( $team ) . '</td>';
									}

									if ( in_array( $time_format, array( 'separate', 'time' ) ) && sp_column_active( $usecolumns, 'time' ) ) {
										echo '<td class="data-time ' . esc_attr( $status ) . '" data-label="' . esc_attr__( 'Time', 'alchemists' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
										}
										echo '<date>&nbsp;' . wp_kses_post( get_post_time( 'H:i:s', false, $event ) ) . '</date>' . wp_kses_post( apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID ) );
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									}
									break;
								default:
									if ( sp_column_active( $usecolumns, 'event' ) ) {
										if ( $title_format == 'teams' ) {
											echo '<td class="data-event data-teams" data-label="' . esc_attr__( 'Teams', 'alchemists' ) . '">' . wp_kses_post( $teams_output ) . '</td>';
										} else {
											$title_html = implode( ' ', $team_logos ) . ' ' . $event->post_title;
											if ( $link_events ) {
												$title_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '" itemprop="url name">' . $title_html . '</a>';
											}
											echo '<td class="data-event" data-label="' . esc_attr__( 'Event', 'alchemists' ) . '">' . wp_kses_post( $title_html ) . '</td>';
										}
									}

									switch ( $time_format ) {
										case 'separate':
											if ( sp_column_active( $usecolumns, 'time' ) ) {
												echo '<td class="data-time ' . esc_attr( $status ) . '" data-label="' . esc_attr__( 'Time', 'alchemists' ) . '">';
												if ( $link_events ) {
													echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
												}
												echo '<date>&nbsp;' . wp_kses_post( get_post_time( 'H:i:s', false, $event ) ) . '</date>' . wp_kses_post( apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID ) );
												if ( $link_events ) {
													echo '</a>';
												}
												echo '</td>';
											}
											if ( sp_column_active( $usecolumns, 'results' ) ) {
												echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'alchemists' ) . '">';
												if ( $link_events ) {
													echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
												}
												if ( ! empty( $main_results ) ) :
													echo wp_kses_post( implode( ' - ', $main_results ) );
													else :
														echo '-';
													endif;
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
											}
											break;
										case 'time':
											if ( sp_column_active( $usecolumns, 'time' ) ) {
												echo '<td class="data-time ' . esc_attr( $status ) . '" data-label="' . esc_attr__( 'Time', 'alchemists' ) . '">';
												if ( $link_events ) {
													echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
												}
												echo '<date>&nbsp;' . wp_kses_post( get_post_time( 'H:i:s', false, $event ) ) . '</date>' . wp_kses_post( apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID ) );
												if ( $link_events ) {
													echo '</a>';
												}
												echo '</td>';
											}
											break;
										case 'results':
											if ( sp_column_active( $usecolumns, 'results' ) ) {
												echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'alchemists' ) . '">';
												if ( $link_events ) {
													echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
												}
												if ( ! empty( $main_results ) ) :
													echo wp_kses_post( implode( ' - ', $main_results ) );
													else :
														echo '-';
													endif;
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
											}
											break;
										default:
											if ( sp_column_active( $usecolumns, 'time' ) ) {
												echo '<td class="data-time ' . esc_attr( $status ) . '" data-label="' . esc_attr__( 'Time/Results', 'alchemists' ) . '">';
												if ( $link_events ) {
													echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
												}
												if ( ! empty( $main_results ) ) :
													echo wp_kses_post( implode( ' - ', $main_results ) );
													else :
														echo '<date>&nbsp;' . wp_kses_post( get_post_time( 'H:i:s', false, $event ) ) . '</date>' . wp_kses_post( apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID ) );
													endif;
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
											}
									}
							}

							if ( sp_column_active( $usecolumns, 'league' ) ) :
								echo '<td class="data-league" data-label="' . esc_attr__( 'League', 'alchemists' ) . '">';
								$leagues = get_the_terms( $event->ID, 'sp_league' );
								if ( $leagues ):
									echo wp_kses_post( implode( ', ', wp_list_pluck( $leagues, 'name' ) ) );
								endif;
								echo '</td>';
							endif;

							if ( sp_column_active( $usecolumns, 'season' ) ) :
								echo '<td class="data-season" data-label="' . esc_attr__( 'Season', 'alchemists' ) . '">';
								$seasons = get_the_terms( $event->ID, 'sp_season' );
								if ( $seasons ) :
									echo wp_kses_post( implode( ', ', wp_list_pluck( $seasons, 'name' ) ) );
								endif;
								echo '</td>';
							endif;

							if ( sp_column_active( $usecolumns, 'venue' ) ) :
								echo '<td class="data-venue" data-label="' . esc_attr__( 'Venue', 'alchemists' ) . '" itemprop="location" itemscope itemtype="http://schema.org/Place">';
								echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
								if ( $link_venues ) :
									the_terms( $event->ID, 'sp_venue' );
									else :
										$venues = get_the_terms( $event->ID, 'sp_venue' );
										if ( $venues ) :
											echo wp_kses_post( implode( ', ', wp_list_pluck( $venues, 'name' ) ) );
										endif;
									endif;
									echo '</div>';
									echo '</td>';
							else:
								echo '<td class="d-none" class="data-venue" data-label="' . esc_attr__( 'Venue', 'alchemists' ) . '" itemprop="location" itemscope itemtype="http://schema.org/Place">';
									echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
									esc_attr_e( 'N/A', 'alchemists' );
									echo '</div>';
								echo '</td>';
							endif;

							if ( sp_column_active( $usecolumns, 'article' ) ) :
								echo '<td class="data-article" data-label="' . esc_attr__( 'Article', 'alchemists' ) . '">';
								if ( $link_events ) {
									echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url" class="btn btn-xs btn-default-alt">';
								}

									if ( $video ):
										echo '<i class="fas fa-video"></i>';
									elseif ( has_post_thumbnail( $event->ID ) ):
										echo '<i class="fas fa-camera"></i>';
									endif;

									if ( $event->post_content !== null ):
										if ( $event->post_status == 'publish' ):
											esc_html_e( 'Recap', 'alchemists' );
										else:
											esc_html_e( 'Preview', 'alchemists' );
										endif;
									endif;

									if ( $link_events ) {
										echo '</a>';
									}
								echo '</td>';
							endif;

							if ( sp_column_active( $usecolumns, 'day' ) ) :
								echo '<td class="data-day" data-label="' . esc_attr__( 'Match Day', 'alchemists' ) . '">';
								$day = get_post_meta( $event->ID, 'sp_day', true );
								if ( '' == $day ) {
									echo '-';
								} else {
									echo wp_kses_post( $day );
								}
								echo '</td>';
							endif;

							do_action( 'sportspress_event_list_row', $event, $usecolumns );

						echo '</tr>';

						$i++;
					endforeach;
					?>
				</tbody>
			</table>
		</div>
		<?php
		// If responsive tables are enabled then load the inline css code
		if ( $responsive ){
			sportspress_responsive_tables_css( $identifier );
		}
		?>
	</div>
</div>
