<?php
/**
 * Event List
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version   2.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*Default template or sportpress */
$event_list_template = get_theme_mod( 'event_list_template', 'theme' );
$defaults            = array(
	'id'                   => null,
	'title'                => false,
	'status'               => 'default',
	'format'               => 'all',
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
	'show_team_logo'       => get_option( 'sportspress_event_list_show_logos', 'no' ) === 'yes',
	'link_events'          => get_option( 'sportspress_link_events', 'yes' ) === 'yes',
	'link_teams'           => get_option( 'sportspress_link_teams', 'no' ) === 'yes',
	'link_venues'          => get_option( 'sportspress_link_venues', 'yes' ) === 'yes',
	'abbreviate_teams'     => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes',
	'responsive'           => get_option( 'sportspress_enable_responsive_tables', 'no' ) === 'yes',
	'sortable'             => get_option( 'sportspress_enable_sortable_tables', 'yes' ) === 'yes',
	'scrollable'           => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) === 'yes',
	'paginated'            => get_option( 'sportspress_event_list_paginated', 'yes' ) === 'yes',
	'rows'                 => get_option( 'sportspress_event_list_rows', 10 ),
	'order'                => 'default',
	'columns'              => null,
	'show_all_events_link' => false,
	'show_title'           => get_option( 'sportspress_event_list_show_title', 'yes' ) === 'yes',
	'title_format'         => get_option( 'sportspress_event_list_title_format', 'title' ),
	'time_format'          => get_option( 'sportspress_event_list_time_format', 'combined' ),
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if ( 'default' !== $status ) {
	$calendar->status = $status;
}
if ( 'default' !== $format ) {
	$calendar->event_format = $format;
}
if ( 'default' !== $date ) {
	$calendar->date = $date;
}
if ( 'default' !== $date_from ) {
	$calendar->from = $date_from;
}
if ( 'default' !== $date_to ) {
	$calendar->to = $date_to;
}
if ( 'default' !== $date_past ) {
	$calendar->past = $date_past;
}
if ( 'default' !== $date_future ) {
	$calendar->future = $date_future;
}
if ( 'default' !== $date_relative ) {
	$calendar->relative = $date_relative;
}
if ( $league ) {
	$calendar->league = $league;
}
if ( $number ) {
	$calendar->number = $number;
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
if ( 'default' !== $order ) {
	$calendar->order = $order;
}
if ( 'default' !== $day ) {
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

$bsb_normal_class = ( splash_is_layout( 'baseball' ) ) ? 'normal_font' : '';
?>
<?php if ( 'theme' === $event_list_template ) { ?>
	<div class="stm-upcoming-events_list 
	<?php
	if ( $paginated ) {
		echo esc_attr( 'paginated-list' );}
	?>
	"
		data-rows="<?php echo esc_attr( $rows ); ?>">
		<?php if ( splash_is_layout( 'volleyball' ) || splash_is_layout( 'bb' ) || splash_is_layout( 'magazine_one' ) || splash_is_layout( 'magazine_two' ) || splash_is_layout( 'soccer_news' ) ) : ?>
			<ul>
				<?php foreach ( $data as $event ) { ?>
					<?php
					$teams   = get_post_meta( $event->ID, 'sp_team' );
					$results = get_post_meta( $event->ID, 'sp_results', true );
					$result  = '';
					$i       = 0;
					if ( $results ) {
						foreach ( $results as $val ) {
							$i++;
							if ( 1 === $i ) {
								if ( '' !== $val[ splash_get_sportpress_points_system() ] ) {
									$result .= $val[ splash_get_sportpress_points_system() ] . ' : ';
								}
							} else {
								if ( '' === $val[ splash_get_sportpress_points_system() ] ) {
									$result .= '';
								} else {
									$result .= $val[ splash_get_sportpress_points_system() ];
								}
							}
						}
					}
					if ( empty( $result ) ) {
						$result = esc_html__( '- vs -', 'splash' );
					}
					$teams_output = '';
					$teams_array  = '';
					?>
					<li class="clearfix stm-event-item">
						<div class="event_date heading-font">
							<div class="date">
								<div class="stm-top">
									<span><?php echo esc_html( get_post_time( 'j', false, $event, true ) ); ?></span>
									<figure>/</figure><?php echo esc_html( get_post_time( 'm', false, $event, true ) ); ?>
								</div>
								<div class="stm-middle"><?php echo esc_html( get_post_time( 'l', false, $event, true ) ); ?></div>
								<div class="stm-bottom"><?php echo esc_html( get_post_time( get_option( 'time_format' ), false, $event, true ) ); ?></div>
							</div>
						</div>
						<div class="clearfix">
							<div class="commands">
								<?php if ( $teams ) { ?>
									<h3>
										<a href="<?php echo esc_url( get_the_permalink( $teams[0] ) ); ?>"><?php echo esc_html( get_the_title( $teams[0] ) ); ?></a>
										<span class="stm-red"><?php echo wp_kses_post( $result ); ?></span> <a
												href="<?php echo esc_url( get_the_permalink( $teams[1] ) ); ?>"><?php echo esc_html( get_the_title( $teams[1] ) ); ?></a>
									</h3>
								<?php } ?>
								<?php
								if ( sp_column_active( $usecolumns, 'venue' ) ) {
									$venues = get_the_terms( $event->ID, 'sp_venue' );
									if ( $venues ) {
										foreach ( $venues as $venue ) {
											echo '<div class="stadium"><i class="stm-icon-pin"></i> ' . esc_html( $venue->name ) . '</div>';
										}
									}
								}
								if ( sp_column_active( $usecolumns, 'league' ) ) {
									$leagues = get_the_terms( $event->ID, 'sp_league' );
									if ( $leagues ) {
										foreach ( $leagues as $league ) {
											echo '<div class="league"><i class="stm-icon-trophy"></i> ' . esc_html( $league->name ) . '</div>';
										}
									}
								}
								if ( sp_column_active( $usecolumns, 'season' ) ) {
									$seasons = get_the_terms( $event->ID, 'sp_season' );
									if ( $seasons && ! empty( $seasons ) ) {
										printf(
											'<div class="season">%1$s: %2$s</div>',
											__( 'Season', 'splash' ),
											implode( ', ', wp_list_pluck( $seasons, 'name' ) )
										);
									}
								}
								?>
							</div>
							<?php if ( sp_column_active( $usecolumns, 'article' ) ) : ?>
								<div class="read_more">
									<a class="button btn-secondary" href="<?php echo esc_url( get_post_permalink( $event->ID, false, true ) ); ?>"><span><?php esc_html_e( 'read more', 'splash' ); ?></span></a>
								</div>
							<?php endif; ?>
						</div>
					</li>
				<?php } ?>
			</ul>
		<?php elseif ( splash_is_layout( 'af' ) || splash_is_layout( 'baseball' ) || splash_is_layout( 'basketball_two' ) || splash_is_layout( 'hockey' ) || splash_is_layout( 'esport' ) ) : /*==========================================================        AMERICAN FOOTBALL or BASEBALL  ========================================================= */ ?>
			<div class="stm-af-template stm-af-template-event-list">
				<?php if ( $title ) { ?>
					<h4 class="sp-table-caption"><?php echo esc_attr( sanitize_text_field( $title ) ); ?></h4>
				<?php } ?>
				<div class="sp-table-wrapper">
					<div class="stm-ipad-block">
						<table class="sp-event-list sp-data-table
						<?php
						if ( $paginated ) {
							?>
							sp-paginated-table
							<?php
						}
						if ( $sortable ) {
							?>
						sp-sortable-table
							<?php
						}
						if ( $scrollable ) {
							?>
						sp-scrollable-table<?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
							<?php if ( ! splash_is_layout( 'hockey' ) ) : ?>
								<thead>
								<tr>
									<?php
									echo '<th class="data-date">' . esc_html__( 'Date', 'splash' ) . '</th>';

									switch ( $title_format ) {
										case 'homeaway':
											if ( sp_column_active( $usecolumns, 'event' ) ) {
												echo '<th class="data-home">' . esc_html__( 'Home', 'splash' ) . '</th>';

												if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
													echo '<th class="data-time">' . esc_html__( 'Time/Results', 'splash' ) . '</th>';
												} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
													echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
												}

												echo '<th class="data-away">' . esc_html__( 'Away', 'splash' ) . '</th>';

												if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
													echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
												}
											}
											break;
										default:
											if ( sp_column_active( $usecolumns, 'event' ) ) {
												if ( 'teams' === $title_format ) {
													echo '<th class="data-teams">' . esc_html__( 'Teams', 'splash' ) . '</th>';
												} else {
													echo '<th class="data-event">' . esc_html__( 'Event', 'splash' ) . '</th>';
												}
											}

											switch ( $time_format ) {
												case 'separate':
													if ( sp_column_active( $usecolumns, 'time' ) ) {
														echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
													}
													if ( sp_column_active( $usecolumns, 'results' ) ) {
														echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
													}
													break;
												case 'time':
													if ( sp_column_active( $usecolumns, 'time' ) ) {
														echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
													}
													break;
												case 'results':
													if ( sp_column_active( $usecolumns, 'results' ) ) {
														echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
													}
													break;
												default:
													if ( sp_column_active( $usecolumns, 'time' ) ) {
														echo '<th class="data-time">' . esc_html__( 'Time/Results', 'splash' ) . '</th>';
													}
											}
									}

									if ( sp_column_active( $usecolumns, 'league' ) ) {
										echo '<th class="data-league">' . esc_html__( 'Competition', 'splash' ) . '</th>';
									}

									if ( sp_column_active( $usecolumns, 'season' ) ) {
										echo '<th class="data-season">' . esc_html__( 'Season', 'splash' ) . '</th>';
									}

									if ( sp_column_active( $usecolumns, 'venue' ) ) {
										echo '<th class="data-venue">' . esc_html__( 'Venue', 'splash' ) . '</th>';
									}

									if ( sp_column_active( $usecolumns, 'article' ) ) {
										echo '<th class="data-article">' . esc_html__( 'Article', 'splash' ) . '</th>';
									}

									if ( sp_column_active( $usecolumns, 'day' ) ) {
										echo '<th class="data-day">' . esc_html__( 'Match Day', 'splash' ) . '</th>';
									}
									?>
								</tr>
								</thead>
							<?php endif; ?>
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

								$teams = get_post_meta( $event->ID, 'sp_team' );
								$video = get_post_meta( $event->ID, 'sp_video', true );

								$main_results = apply_filters( 'sportspress_event_list_main_results', sp_get_main_results( $event ), $event->ID );

								$teams_output = '';
								$teams_array  = array();
								$team_logos   = array();

								if ( $teams ) :
									foreach ( $teams as $team ) :
										$name = sp_get_team_name( $team, $abbreviate_teams );
										if ( $name ) :

											if ( $show_team_logo ) :
												$name         = sp_get_logo( $team, 'mini' ) . ' ' . $name;
												$team_logos[] = sp_get_logo( $team, 'full' );
											endif;

											if ( $link_teams ) :
												$team_output = '<a href="' . get_post_permalink( $team ) . '">' . $name . '</a>';
											else :
												$team_output = $name;
											endif;

											$team_result = sp_array_value( $main_results, $team, null );

											if ( $team_result !== null ) :
												if ( $usecolumns !== null && ! in_array( 'time', $usecolumns, true ) ) :
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

								echo '<tr class="sp-row sp-post' . ( $i % 2 === 0 ? ' alternate' : '' ) . ' sp-row-no-' . $i . '">';

								echo "<td class='data-date'>
								<div class='stm-date-event-wrapp'>
									<div class='stm-top heading-font'>
										<span>" . get_post_time( 'j', false, $event, true ) . "</span>
									</div>
									<div class='stm-middle normal_font'>" . get_post_time( 'M', false, $event, true ) . '</div>
								</div>
							</td>';

								switch ( $title_format ) {
									case 'homeaway':
										if ( sp_column_active( $usecolumns, 'event' ) ) {
											$team = array_shift( $teams_array );
											echo '<td class="data-home">' . $team . '</td>';

											if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
												echo '<td class="data-time">';
												if ( $link_events ) {
													echo '<a class="' . $bsb_normal_class . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
												}
												if ( ! empty( $main_results ) ) :
													echo implode( ' - ', $main_results );
												else :
													echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . sp_get_time( $event );
												endif;
												if ( $link_events ) {
													echo '</a>';
												}
												echo '</td>';
											} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
												echo '<td class="data-results">';
												if ( $link_events ) {
													echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
												}
												if ( ! empty( $main_results ) ) :
													echo implode( ' - ', $main_results );
												else :
													echo '-';
												endif;
												if ( $link_events ) {
													echo '</a>';
												}
												echo '</td>';
											}

											$team = array_shift( $teams_array );
											echo '<td class="data-away">' . $team . '</td>';

											if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
												echo '<td class="data-time">';
												if ( $link_events ) {
													echo '<a class="' . $bsb_normal_class . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
												}
												echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . sp_get_time( $event );
												if ( $link_events ) {
													echo '</a>';
												}
												echo '</td>';
											}
										}
										break;
									default:
										if ( sp_column_active( $usecolumns, 'event' ) ) {
											if ( $title_format === 'teams' ) {
												echo '<td class="data-event data-teams">' . $teams_output . '</td>';
											} else {
												$title_html = implode( ' ', $team_logos ) . ' ' . $event->post_title;
												if ( $link_events ) {
													$title_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '">' . str_replace( ' vs ', '<span> -vs- </span>', $title_html ) . '</a>';
												}
												echo '<td class="data-event heading-font">' . $title_html . '</td>';
											}
										}

										switch ( $time_format ) {
											case 'separate':
												if ( sp_column_active( $usecolumns, 'time' ) ) {
													echo '<td class="data-time">';
													if ( $link_events ) {
														echo '<a class="' . $bsb_normal_class . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
													}
													echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . sp_get_time( $event );
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
												}
												if ( sp_column_active( $usecolumns, 'results' ) ) {
													echo '<td class="data-results">';
													if ( $link_events ) {
														echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
													}
													if ( ! empty( $main_results ) ) :
														echo implode( ' - ', $main_results );
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
													echo '<td class="data-time">';
													if ( $link_events ) {
														echo '<a class="' . $bsb_normal_class . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
													}
													echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . sp_get_time( $event );
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
												}
												break;
											case 'results':
												if ( sp_column_active( $usecolumns, 'results' ) ) {
													echo '<td class="data-results">';
													if ( $link_events ) {
														echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
													}
													if ( ! empty( $main_results ) ) :
														echo implode( ' - ', $main_results );
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
													echo '<td class="data-time">';
													if ( $link_events ) {
														echo '<a class="' . $bsb_normal_class . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
													}
													if ( ! empty( $main_results ) ) :
														echo implode( ' - ', $main_results );
													else :
														echo '<date>&nbsp;' . get_post_time( 'H:i:s T', false, $event ) . '</date>' . sp_get_time( $event );
													endif;
													if ( $link_events ) {
														echo '</a>';
													}
													echo '</td>';
												}
										}
								}

								if ( sp_column_active( $usecolumns, 'league' ) ) :
									echo '<td class="data-league">';
									$leagues = get_the_terms( $event->ID, 'sp_league' );
									if ( $leagues ) :
										echo implode( ', ', wp_list_pluck( $leagues, 'name' ) );
									endif;
									echo '</td>';
								endif;

								if ( sp_column_active( $usecolumns, 'season' ) ) :
									echo '<td class="data-season">';
									$seasons = get_the_terms( $event->ID, 'sp_season' );
									if ( $seasons ) :
										echo implode( ', ', wp_list_pluck( $seasons, 'name' ) );
									endif;
									echo '</td>';
								endif;

								if ( sp_column_active( $usecolumns, 'venue' ) ) :
									echo '<td class="data-venue">';
									if ( $link_venues ) :
										the_terms( $event->ID, 'sp_venue' );
									else :
										$venues = get_the_terms( $event->ID, 'sp_venue' );
										if ( $venues ) :
											foreach ( $venues as $venue ) :
												echo esc_html( $venue->name );
										endforeach;
endif;
									endif;
									echo '</td>';
								endif;

								if ( sp_column_active( $usecolumns, 'article' ) ) :

									$classArticle = ( splash_is_layout( 'baseball' ) ) ? 'normal_font' : 'heading-font';
									$btn          = '';
									if ( splash_is_layout( 'hockey' ) ) {
										$btn = 'btn';
									}
									echo '<td class="data-article ' . $classArticle . '">';
									if ( $link_events ) {
										echo '<a class="' . $btn . '" href="' . get_post_permalink( $event->ID, false, true ) . '">';
									}

									if ( $event->post_content !== null ) :
										if ( $event->post_status === 'publish' ) :
											esc_html_e( 'Recap', 'splash' );
										else :
											esc_html_e( 'Preview', 'splash' );
										endif;
									endif;
									echo '<i class="fa fa-arrow-right" aria-hidden="true"></i>';
									if ( $link_events ) {
										echo '</a>';
									}
									echo '</td>';
								endif;

								if ( sp_column_active( $usecolumns, 'day' ) ) :
									echo '<td class="data-day" data-label="' . esc_html__( 'Match Day', 'splash' ) . '">';
									$day = get_post_meta( $event->ID, 'sp_day', true );
									if ( '' === $day ) {
										echo '-';
									} else {
										echo esc_html( $day );
									}
									echo '</td>';
								endif;

								echo '</tr>';

								$i++;
							endforeach;
							?>
							</tbody>
						</table>
					</div>
					<div class="stm-ipad-none">
						<ul>
							<?php foreach ( $data as $event ) { ?>
								<?php
								$teams   = get_post_meta( $event->ID, 'sp_team' );
								$results = get_post_meta( $event->ID, 'sp_results', true );
								$result  = '';
								$i       = 0;
								if ( $results ) {
									foreach ( $results as $val ) {
										$i++;
										if ( $i === 1 ) {
											if ( ! empty( $val[ splash_get_sportpress_points_system() ] ) || $val[ splash_get_sportpress_points_system() ] === '0' ) {
												$result .= $val[ splash_get_sportpress_points_system() ] . ' : ';
											}
										} else {
											if ( empty( $val[ splash_get_sportpress_points_system() ] ) && $val[ splash_get_sportpress_points_system() ] !== '0' ) {
												$result .= '';
											} else {
												$result .= $val[ splash_get_sportpress_points_system() ];
											}
										}
									}
								}
								if ( empty( $result ) ) {
									$result = esc_html__( '- vs -', 'splash' );
								}
								$teams_output = '';
								$teams_array  = '';
								?>
								<li class="clearfix stm-event-item">
									<div class="event_date heading-font">
										<div class="date">
											<div class="stm-top">
												<span><?php echo get_post_time( 'j', false, $event, true ); ?></span>
												<figure>/
												</figure><?php echo get_post_time( 'm', false, $event, true ); ?>
											</div>
											<div class="stm-middle"><?php echo get_post_time( 'l', false, $event, true ); ?></div>
											<div class="stm-bottom"><?php echo get_post_time( get_option( 'time_format' ), false, $event, true ); ?></div>
										</div>
									</div>
									<div class="clearfix">
										<div class="commands">
											<?php if ( $teams ) { ?>
												<h3>
													<a href="<?php echo get_the_permalink( $teams[0] ); ?>"><?php echo get_the_title( $teams[0] ); ?></a>
													<span class="stm-red"><?php echo wp_kses_post( $result ); ?></span>
													<a
															href="<?php echo get_the_permalink( $teams[1] ); ?>"><?php echo get_the_title( $teams[1] ); ?></a>
												</h3>
											<?php } ?>
											<?php
											$venues = get_the_terms( $event->ID, 'sp_venue' );
											if ( $venues ) {
												foreach ( $venues as $venue ) {
													echo '<div class="stadium"><i class="stm-icon-pin"></i> ' . $venue->name . '</div>';
												}
											}
											?>
											<?php
											$leagues = get_the_terms( $event->ID, 'sp_league' );
											if ( $leagues ) {
												foreach ( $leagues as $league ) {
													echo '<div class="league"><i class="stm-icon-trophy"></i> ' . $league->name . '</div>';
												}
											}
											?>
										</div>
										<div class="read_more">
											<a class="button btn-secondary" href="<?php echo get_post_permalink( $event->ID, false, true ); ?>"><span><?php esc_html_e( 'read more', 'splash' ); ?></span></a>
										</div>
									</div>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php
				if ( $id && $show_all_events_link ) {
					echo '<div class="sp-calendar-link sp-view-all-link heading-font"><a href="' . get_permalink( $id ) . '">' . esc_html__( 'View all events', 'splash' ) . '</a></div>';
				}
				?>
			</div>
			<?php
		elseif ( splash_is_layout( 'sccr' ) || splash_is_layout( 'soccer_two' ) ) : /*=======================   SOCCER   ======================== */
			?>
			<div class="stm-sccr-template stm-sccr-template-event-list">
				<?php if ( $title ) { ?>
					<h4 class="sp-table-caption"><?php echo sanitize_text_field( $title ); ?></h4>
				<?php } ?>
				<div class="sp-table-wrapper">
					<ul>
						<?php foreach ( $data as $event ) { ?>
							<?php
							$teams   = get_post_meta( $event->ID, 'sp_team' );
							$results = get_post_meta( $event->ID, 'sp_results', true );
							$result  = '';
							$i       = 0;
							if ( $results ) {
								foreach ( $results as $val ) {
									$i++;
									if ( $i === 1 ) {
										if ( $val[ splash_get_sportpress_points_system() ] !== '' ) {
											$result .= $val[ splash_get_sportpress_points_system() ] . ' : ';
										}
									} else {
										if ( $val[ splash_get_sportpress_points_system() ] === '' ) {
											$result .= '';
										} else {
											$result .= $val[ splash_get_sportpress_points_system() ];
										}
									}
								}
							}
							$teams_output = '';
							$teams_array  = '';
							?>
							<li class="clearfix stm-event-item">
								<div class="event_date">
									<div class="date">
										<div class="stm-top">
											<span class="date-day-num heading-font"><?php echo get_post_time( 'j', false, $event, true ); ?></span>
											<span class="date-month normal_font"><?php echo get_post_time( 'F', false, $event, true ); ?></span>
										</div>
									</div>
								</div>
								<div class="clearfix">
									<div class="stm-event-data-wrap">
										<div class="commands <?php echo ! $result ? 'no-result' : ''; ?>">
											<?php if ( $teams ) { ?>
												<h3>
													<a href="<?php echo get_the_permalink( $teams[0] ); ?>">
														<?php echo get_the_title( $teams[0] ); ?>
													</a>
													<span class="event-vs">vs</span>
													<span class="event-vs-mobile" style="display: none;"><?php echo ( '' !== $result ) ? wp_kses_post( $result ) : 'vs'; ?></span>
													<a href="<?php echo get_the_permalink( $teams[1] ); ?>">
														<?php echo get_the_title( $teams[1] ); ?>
													</a>
												</h3>
											<?php } ?>
											<div class="stm-event-bottom-info">
												<div class="date-time">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
													<span class="mobile-hide"><?php echo get_post_time( 'h:i A', false, $event, true ); ?></span>
													<span class="mobile-show" style="display: none;"><?php echo get_post_time( 'j F, h:i A', false, $event, true ); ?></span>
												</div>
												<?php
												$venues = get_the_terms( $event->ID, 'sp_venue' );
												if ( $venues ) {
													foreach ( $venues as $venue ) {
														echo '<div class="stadium"><i class="fa fa-location-arrow"></i> ' . $venue->name . '</div>';
													}
												}
												?>
											</div>
										</div>
										<?php if ( $result ) : ?>
											<div class="stm-event-result">
												<?php echo wp_kses_post( $result ); ?>
											</div>
										<?php endif; ?>
										<div class="read_more">
											<a class="button button-gray btn-only-border" href="<?php echo get_post_permalink( $event->ID, false, true ); ?>">
												<span><?php echo ( empty( $result ) ) ? esc_html__( 'PREVIEW', 'splash' ) : esc_html__( 'RECAP', 'splash' ); ?></span>
											</a>
										</div>
									</div>
								</div>
							</li>
						<?php } ?>
					</ul>
				</div>
				<?php
				if ( $id && $show_all_events_link ) {
					echo '<div class="sp-calendar-link sp-view-all-link heading-font"><a href="' . get_permalink( $id ) . '">' . esc_html__( 'View all events', 'splash' ) . '</a></div>';
				}
				?>
			</div>

		<?php endif; ?>
	</div>
<?php } else { ?>
	<div class="sp-template sp-template-event-list">
		<?php if ( $title ) { ?>
			<h4 class="sp-table-caption"><?php echo sanitize_text_field( $title ); ?></h4>
		<?php } ?>
		<div class="sp-table-wrapper">
			<table class="sp-event-list sp-event-list-format-<?php echo esc_attr( $title_format ); ?> sp-data-table
																		<?php
																		if ( $paginated ) {
																			?>
				sp-paginated-table
																			<?php
																		}
																		if ( $sortable ) {
																			?>
			sp-sortable-table
																			<?php
																		}
																		if ( $scrollable ) {
																			?>
			sp-scrollable-table<?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
				<thead>
				<tr>
					<?php
					echo '<th class="data-date">' . esc_html__( 'Date', 'splash' ) . '</th>';

					switch ( $title_format ) {
						case 'homeaway':
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								echo '<th class="data-home">' . esc_html__( 'Home', 'splash' ) . '</th>';
							}
							if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<th class="data-time">' . esc_html__( 'Time/Results', 'splash' ) . '</th>';
							} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
								echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
							}

							if ( sp_column_active( $usecolumns, 'event' ) ) {
								echo '<th class="data-away">' . esc_html__( 'Away', 'splash' ) . '</th>';
							}

							if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
							}
							break;
						default:
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								if ( $title_format === 'teams' ) {
									echo '<th class="data-teams">' . esc_html__( 'Teams', 'splash' ) . '</th>';
								} else {
									echo '<th class="data-event">' . esc_html__( 'Event', 'splash' ) . '</th>';
								}
							}

							switch ( $time_format ) {
								case 'separate':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
									}
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
									}
									break;
								case 'time':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_html__( 'Time', 'splash' ) . '</th>';
									}
									break;
								case 'results':
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<th class="data-results">' . esc_html__( 'Results', 'splash' ) . '</th>';
									}
									break;
								default:
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_html__( 'Time/Results', 'splash' ) . '</th>';
									}
							}
					}

					if ( sp_column_active( $usecolumns, 'league' ) ) {
						echo '<th class="data-league">' . esc_html__( 'Competition', 'splash' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'season' ) ) {
						echo '<th class="data-season">' . esc_html__( 'Season', 'splash' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'venue' ) ) {
						echo '<th class="data-venue">' . esc_html__( 'Venue', 'splash' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'article' ) ) {
						echo '<th class="data-article">' . esc_html__( 'Article', 'splash' ) . '</th>';
					}
					if ( sp_column_active( $usecolumns, 'day' ) ) {
						echo '<th class="data-day">' . esc_html__( 'Match Day', 'splash' ) . '</th>';
					}
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

					$teams = get_post_meta( $event->ID, 'sp_team' );
					$video = get_post_meta( $event->ID, 'sp_video', true );

					$main_results = apply_filters( 'sportspress_event_list_main_results', sp_get_main_results( $event ), $event->ID );

					$teams_output = '';
					$teams_array  = array();
					$team_logos   = array();
					$team_class   = '';

					if ( $teams ) :
						foreach ( $teams as $t => $team ) :
							$name = sp_get_team_name( $team, $abbreviate_teams );
							if ( $name ) :

								if ( $show_team_logo ) :
									if ( has_post_thumbnail( $team ) ) :
										$logo         = '<span class="team-logo">' . sp_get_logo( $team, 'mini' ) . '</span>';
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
									$team_output = '<a href="' . get_post_permalink( $team ) . '">' . $name . '</a>';
								else :
									$team_output = $name;
								endif;

								$team_result = sp_array_value( $main_results, $team, null );

								if ( $team_result !== null ) :
									if ( $usecolumns !== null && ! in_array( 'time', $usecolumns, true ) ) :
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

					echo '<tr class="sp-row sp-post' . ( $i % 2 === 0 ? ' alternate' : '' ) . ' sp-row-no-' . $i . '">';

					$date_html = '<date>' . get_post_time( 'Y-m-d H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_date', get_post_time( get_option( 'date_format' ), false, $event, true ), $event->ID );

					if ( $link_events ) {
						$date_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '">' . $date_html . '</a>';
					}

					echo '<td class="data-date" itemprop="startDate" content="' . mysql2date( 'Y-m-d\TH:iP', $event->post_date ) . '" data-label="' . esc_html__( 'Date', 'splash' ) . '">' . $date_html . '</td>';

					switch ( $title_format ) {
						case 'homeaway':
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								$team = array_shift( $teams_array );
								echo '<td class="data-home' . $team_class . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_html__( 'Home', 'splash' ) . '">' . $team . '</td>';
							}
							if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<td class="data-time" data-label="' . esc_html__( 'Time/Results', 'splash' ) . '">';
								if ( $link_events ) {
									echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
								}
								if ( ! empty( $main_results ) ) :
									echo implode( ' - ', $main_results );
								else :
									echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID );
								endif;
								if ( $link_events ) {
									echo '</a>';
								}
								echo '</td>';
							} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
								echo '<td class="data-results" data-label="' . esc_html__( 'Results', 'splash' ) . '">';
								if ( $link_events ) {
									echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
								}
								if ( ! empty( $main_results ) ) :
									echo implode( ' - ', $main_results );
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
								echo '<td class="data-away' . $team_class . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_html__( 'Away', 'splash' ) . '">' . $team . '</td>';
							}

							if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<td class="data-time" data-label="' . esc_html__( 'Time', 'splash' ) . '">';
								if ( $link_events ) {
									echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
								}
								echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . sp_get_time( $event );
								if ( $link_events ) {
									echo '</a>';
								}
								echo '</td>';
							}
							break;
						default:
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								if ( 'teams' === $title_format ) {
									echo '<td class="data-event data-teams" data-label="' . esc_html__( 'Teams', 'splash' ) . '">' . $teams_output . '</td>';
								} else {
									$title_html = implode( ' ', $team_logos ) . ' ' . $event->post_title;
									if ( $link_events ) {
										$title_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '">' . $title_html . '</a>';
									}
									echo '<td class="data-event heading-font" data-label="' . esc_html__( 'Event', 'splash' ) . '">' . $title_html . '</td>';
								}
							}

							switch ( $time_format ) {
								case 'separate':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<td class="data-time" data-label="' . esc_html__( 'Time', 'splash' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
										}
										echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID );
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									}
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<td class="data-results" data-label="' . esc_html__( 'Results', 'splash' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
										}
										if ( ! empty( $main_results ) ) :
											echo implode( ' - ', $main_results );
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
										echo '<td class="data-time" data-label="' . esc_html__( 'Time', 'splash' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
										}
										echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID );
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									}
									break;
								case 'results':
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<td class="data-results" data-label="' . esc_html__( 'Results', 'splash' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
										}
										if ( ! empty( $main_results ) ) :
											echo implode( ' - ', $main_results );
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
										echo '<td class="data-time" data-label="' . esc_html__( 'Time/Results', 'splash' ) . '">';
										if ( $link_events ) {
											echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '" itemprop="url">';
										}
										if ( ! empty( $main_results ) ) :
											echo implode( ' - ', $main_results );
										else :
											echo '<date>&nbsp;' . get_post_time( 'H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_time', sp_get_time( $event ), $event->ID );
										endif;
										if ( $link_events ) {
											echo '</a>';
										}
										echo '</td>';
									}
							}
					}

					if ( sp_column_active( $usecolumns, 'league' ) ) :
						echo '<td class="data-league" data-label="' . esc_html__( 'League', 'splash' ) . '">';
						$leagues = get_the_terms( $event->ID, 'sp_league' );
						if ( $leagues ) :
							echo implode( ', ', wp_list_pluck( $leagues, 'name' ) );
						endif;
						echo '</td>';
					endif;

					if ( sp_column_active( $usecolumns, 'season' ) ) :
						echo '<td class="data-season" data-label="' . esc_html__( 'Season', 'splash' ) . '">';
						$seasons = get_the_terms( $event->ID, 'sp_season' );
						if ( $seasons ) :
							echo implode( ', ', wp_list_pluck( $seasons, 'name' ) );
						endif;
						echo '</td>';
					endif;

					if ( sp_column_active( $usecolumns, 'venue' ) ) :
						echo '<td class="data-venue" data-label="' . esc_html__( 'Venue', 'splash' ) . '">';
						if ( $link_venues ) :
							the_terms( $event->ID, 'sp_venue' );
						else :
							$venues = get_the_terms( $event->ID, 'sp_venue' );
							if ( $venues ) :
								foreach ( $venues as $venue ) :
									echo esc_html( $venue->name );
							endforeach;
endif;
						endif;
						echo '</td>';
					endif;

					if ( sp_column_active( $usecolumns, 'article' ) ) :

						$classArticle = ( splash_is_layout( 'baseball' ) ) ? 'normal_font' : 'heading-font';

						echo '<td class="data-article ' . $classArticle . '" data-label="' . esc_html__( 'Article', 'splash' ) . '">';
						if ( $link_events ) {
							echo '<a href="' . get_post_permalink( $event->ID, false, true ) . '">';
						}

						if ( $video ) :
							echo '<div class="dashicons dashicons-video-alt"></div>';
						elseif ( has_post_thumbnail( $event->ID ) ) :
							echo '<div class="dashicons dashicons-camera"></div>';
						endif;
						if ( null !== $event->post_content ) :
							if ( 'publish' === $event->post_status ) :
								esc_html_e( 'Recap', 'splash' );
							else :
								esc_html_e( 'Preview', 'splash' );
							endif;
						endif;

						if ( $link_events ) {
							echo '</a>';
						}
						echo '</td>';
					endif;

					if ( sp_column_active( $usecolumns, 'day' ) ) :
						echo '<td class="data-day" data-label="' . esc_html__( 'Match Day', 'splash' ) . '">';
						$day = get_post_meta( $event->ID, 'sp_day', true );
						if ( '' === $day ) {
							echo '-';
						} else {
							echo esc_html( $day );
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
		if ( $id && $show_all_events_link ) {
			echo '<div class="sp-calendar-link sp-view-all-link heading-font"><a href="' . get_permalink( $id ) . '">' . esc_html__( 'View all events', 'splash' ) . '</a></div>';
		}
		?>
	</div>
	<?php
}
