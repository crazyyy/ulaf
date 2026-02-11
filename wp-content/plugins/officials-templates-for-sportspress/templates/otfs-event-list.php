<?php
/**
 * Based on SporsPress Event List
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$official_id    = isset( $official_id ) ? $official_id : null;
$number         = isset( $number ) ? $number : -1;
$otfs_order     = isset( $otfs_order ) ? $otfs_order : 'default';
$columns        = isset( $columns ) ? $columns : null;
$show_team_logo = get_option( 'sportspress_event_list_show_logos', 'no' ) === 'yes' ? true : false;
$link_events    = get_option( 'sportspress_link_events', 'yes' ) === 'yes' ? true : false;
$link_teams     = get_option( 'sportspress_link_teams', 'no' ) === 'yes' ? true : false;
$link_venues    = get_option( 'sportspress_link_venues', 'yes' ) === 'yes' ? true : false;
$responsive     = get_option( 'sportspress_enable_responsive_tables', 'no' ) === 'yes' ? true : false;
$sortable       = get_option( 'sportspress_enable_sortable_tables', 'yes' ) === 'yes' ? true : false;
$scrollable     = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) === 'yes' ? true : false;
$paginated      = get_option( 'sportspress_event_list_paginated', 'yes' ) === 'yes' ? true : false;
$rows           = get_option( 'sportspress_event_list_rows', 10 );
$title_format   = get_option( 'sportspress_event_list_title_format', 'title' );
$time_format    = get_option( 'sportspress_event_list_time_format', 'combined' );

$official        = new OTFS_Officials( $official_id );
$official->order = $otfs_order;
$data            = $official->events();
$usecolumns      = array();

if ( isset( $columns ) ) :
	if ( is_array( $columns ) ) {
		$usecolumns = $columns;
	} else {
		$usecolumns = explode( ',', $columns );
	}
endif;

$labels = array();
// Create a unique identifier based on the current time in microseconds.
$identifier = uniqid( 'eventlist_' );
?>
<div class="sp-template sp-template-event-list">
	<div class="sp-table-wrapper">
		<table class="sp-event-list sp-event-list-format-<?php echo esc_attr( $title_format ); ?> sp-data-table
																	<?php
																	if ( $paginated ) {
																		?>
			sp-paginated-table
																		<?php
																	} if ( $sortable ) {
																		?>
			sp-sortable-table
																		<?php
																	} if ( $responsive ) {
																												echo ' sp-responsive-table ' . esc_attr( $identifier ); } if ( $scrollable ) {
																		?>
													sp-scrollable-table <?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
			<thead>
				<tr>
					<?php
					echo '<th class="data-date">' . esc_attr__( 'Date', 'sportspress' ) . '</th>';

					switch ( $title_format ) {
						case 'homeaway':
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								echo '<th class="data-home">' . esc_attr__( 'Home', 'sportspress' ) . '</th>';
							}

							if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<th class="data-time">' . esc_attr__( 'Time/Results', 'sportspress' ) . '</th>';
								$labels[] = esc_attr__( 'Time/Results', 'sportspress' );
							} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
								echo '<th class="data-results">' . esc_attr__( 'Results', 'sportspress' ) . '</th>';
							}

							if ( sp_column_active( $usecolumns, 'event' ) ) {
								echo '<th class="data-away">' . esc_attr__( 'Away', 'sportspress' ) . '</th>';
							}

							if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<th class="data-time">' . esc_attr__( 'Time', 'sportspress' ) . '</th>';
							}
							break;
						default:
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								if ( 'teams' === $title_format ) {
									echo '<th class="data-teams">' . esc_attr__( 'Teams', 'sportspress' ) . '</th>';
								} else {
									echo '<th class="data-event">' . esc_attr__( 'Event', 'sportspress' ) . '</th>';
								}
							}

							switch ( $time_format ) {
								case 'separate':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_attr__( 'Time', 'sportspress' ) . '</th>';
									}
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<th class="data-results">' . esc_attr__( 'Results', 'sportspress' ) . '</th>';
									}
									break;
								case 'time':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_attr__( 'Time', 'sportspress' ) . '</th>';
									}
									break;
								case 'results':
									if ( sp_column_active( $usecolumns, 'results' ) ) {
										echo '<th class="data-results">' . esc_attr__( 'Results', 'sportspress' ) . '</th>';
									}
									break;
								default:
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<th class="data-time">' . esc_attr__( 'Time/Results', 'sportspress' ) . '</th>';
									}
							}
					}

					if ( sp_column_active( $usecolumns, 'league' ) ) {
						echo '<th class="data-league">' . esc_attr__( 'League', 'sportspress' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'season' ) ) {
						echo '<th class="data-season">' . esc_attr__( 'Season', 'sportspress' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'venue' ) ) {
						echo '<th class="data-venue">' . esc_attr__( 'Venue', 'sportspress' ) . '</th>';
					} else {
						echo '<th style="display:none;" class="data-venue">' . esc_attr__( 'Venue', 'sportspress' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'article' ) ) {
						echo '<th class="data-article">' . esc_attr__( 'Article', 'sportspress' ) . '</th>';
					}

					if ( sp_column_active( $usecolumns, 'day' ) ) {
						echo '<th class="data-day">' . esc_attr__( 'Match Day', 'sportspress' ) . '</th>';
					}

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

					$teams     = get_post_meta( $event->ID, 'sp_team' );
					$video     = get_post_meta( $event->ID, 'sp_video', true );
					$sp_status = get_post_meta( $event->ID, 'sp_status', true );

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

								if ( $show_team_logo ) :
									if ( has_post_thumbnail( $team ) ) :
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

								if ( null !== $team_result ) :
									if ( null !== $usecolumns && ! in_array( 'time', $usecolumns, true ) ) :
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

					echo '<tr class="sp-row sp-post' . ( 0 === $i % 2 ? ' alternate' : '' ) . ' sp-row-no-' . esc_attr( $i ) . '" itemscope itemtype="http://schema.org/SportsEvent">';

						$date_html = '<date>' . get_post_time( 'Y-m-d H:i:s', false, $event ) . '</date>' . apply_filters( 'sportspress_event_date', get_post_time( get_option( 'date_format' ), false, $event, true ), $event->ID );

					if ( $link_events ) {
						$date_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '" itemprop="url">' . $date_html . '</a>';
					}

						echo '<td class="data-date" itemprop="startDate" content="' . esc_attr( mysql2date( 'Y-m-d\TH:iP', $event->post_date ) ) . '" data-label="' . esc_attr__( 'Date', 'sportspress' ) . '">' . wp_kses(
							$date_html,
							array(
								'a'    => array(
									'href'     => array(),
									'itemprop' => array(),
								),
								'date' => array(),
							)
						) . '</td>';

						// Check if the reverse_teams option is selected and alter the teams order.
					if ( $reverse_teams ) {
						$teams_array = array_reverse( $teams_array, true );
					}

					switch ( $title_format ) {
						case 'homeaway':
							if ( sp_column_active( $usecolumns, 'event' ) ) {
								$team = array_shift( $teams_array );
								echo '<td class="data-home' . esc_attr( $team_class ) . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_attr__( 'Home', 'sportspress' ) . '">' . wp_kses_post( $team ) . '</td>';
							}

							if ( 'combined' === $time_format && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<td class="data-time ' . esc_attr( $sp_status ) . '" data-label="' . esc_attr__( 'Time/Results', 'sportspress' ) . '">';
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
							} elseif ( in_array( $time_format, array( 'separate', 'results' ), true ) && sp_column_active( $usecolumns, 'results' ) ) {
								echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'sportspress' ) . '">';
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
								echo '<td class="data-away' . esc_attr( $team_class ) . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam" data-label="' . esc_attr__( 'Away', 'sportspress' ) . '">' . wp_kses_post( $team ) . '</td>';
							}

							if ( in_array( $time_format, array( 'separate', 'time' ), true ) && sp_column_active( $usecolumns, 'time' ) ) {
								echo '<td class="data-time ' . esc_attr( $sp_status ) . '" data-label="' . esc_attr__( 'Time', 'sportspress' ) . '">';
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
								if ( 'teams' === $title_format ) {
									echo '<td class="data-event data-teams" data-label="' . esc_attr__( 'Teams', 'sportspress' ) . '">' . wp_kses_post( $teams_output ) . '</td>';
								} else {
									$title_html = implode( ' ', $team_logos ) . ' ' . $event->post_title;
									if ( $link_events ) {
										$title_html = '<a href="' . get_post_permalink( $event->ID, false, true ) . '" itemprop="url name">' . $title_html . '</a>';
									}
									echo '<td class="data-event" data-label="' . esc_attr__( 'Event', 'sportspress' ) . '">' . wp_kses_post( $title_html ) . '</td>';
								}
							}

							switch ( $time_format ) {
								case 'separate':
									if ( sp_column_active( $usecolumns, 'time' ) ) {
										echo '<td class="data-time ' . esc_attr( $sp_status ) . '" data-label="' . esc_attr__( 'Time', 'sportspress' ) . '">';
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
										echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'sportspress' ) . '">';
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
										echo '<td class="data-time ' . esc_attr( $sp_status ) . '" data-label="' . esc_attr__( 'Time', 'sportspress' ) . '">';
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
										echo '<td class="data-results" data-label="' . esc_attr__( 'Results', 'sportspress' ) . '">';
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
										echo '<td class="data-time ' . esc_attr( $sp_status ) . '" data-label="' . esc_attr__( 'Time/Results', 'sportspress' ) . '">';
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
						echo '<td class="data-league" data-label="' . esc_attr__( 'League', 'sportspress' ) . '">';
						$leagues = get_the_terms( $event->ID, 'sp_league' );
						if ( $leagues ) :
							echo wp_kses_post( implode( ', ', wp_list_pluck( $leagues, 'name' ) ) );
							endif;
						echo '</td>';
						endif;

					if ( sp_column_active( $usecolumns, 'season' ) ) :
						echo '<td class="data-season" data-label="' . esc_attr__( 'Season', 'sportspress' ) . '">';
						$seasons = get_the_terms( $event->ID, 'sp_season' );
						if ( $seasons ) :
							echo wp_kses_post( implode( ', ', wp_list_pluck( $seasons, 'name' ) ) );
							endif;
						echo '</td>';
						endif;

					if ( sp_column_active( $usecolumns, 'venue' ) ) :
						echo '<td class="data-venue" data-label="' . esc_attr__( 'Venue', 'sportspress' ) . '" itemprop="location" itemscope itemtype="http://schema.org/Place">';
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
						else :
							echo '<td style="display:none;" class="data-venue" data-label="' . esc_attr__( 'Venue', 'sportspress' ) . '" itemprop="location" itemscope itemtype="http://schema.org/Place">';
							echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
							esc_attr_e( 'N/A', 'sportspress' );
							echo '</div>';
							echo '</td>';
						endif;

						if ( sp_column_active( $usecolumns, 'article' ) ) :
							echo '<td class="data-article" data-label="' . esc_attr__( 'Article', 'sportspress' ) . '">';
							if ( $link_events ) {
								echo '<a href="' . esc_url( get_post_permalink( $event->ID, false, true ) ) . '" itemprop="url">';
							}

							if ( $video ) :
								echo '<div class="dashicons dashicons-video-alt"></div>';
								elseif ( has_post_thumbnail( $event->ID ) ) :
									echo '<div class="dashicons dashicons-camera"></div>';
								endif;
								if ( null !== $event->post_content ) :
									if ( 'publish' === $event->post_status ) :
										esc_attr_e( 'Recap', 'sportspress' );
									else :
										esc_attr_e( 'Preview', 'sportspress' );
									endif;
								endif;

								if ( $link_events ) {
									echo '</a>';
								}
								echo '</td>';
						endif;

						if ( sp_column_active( $usecolumns, 'day' ) ) :
							echo '<td class="data-day" data-label="' . esc_attr__( 'Match Day', 'sportspress' ) . '">';
							$day = get_post_meta( $event->ID, 'sp_day', true );
							if ( '' === $day ) {
								echo '-';
							} else {
								echo wp_kses_post( $day );
							}
							echo '</td>';
						endif;

						do_action( 'sportspress_event_list_row', $event, $usecolumns );

						echo '</tr>';

						++$i;
				endforeach;
				?>
			</tbody>
		</table>
	</div>
</div>
