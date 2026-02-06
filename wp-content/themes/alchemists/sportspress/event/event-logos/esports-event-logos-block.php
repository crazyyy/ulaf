<?php
/**
 * The template for displaying Event Score (Block) on Single Event page
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
?>

<!-- ALC Event Result -->
<div class="alc-event-result-box card card--no-paddings" itemscope itemtype="http://schema.org/SportsEvent">

	<!-- Header -->
	<header class="alc-event-result-box__header alc-event-result-box__header--center card__header card__header--no-highlight" itemprop="name" content="<?php echo esc_attr( get_the_title( $id ) ); ?>">
		<time class="alc-event-result-box__header-date" itemprop="startDate" datetime="<?php echo get_the_time( 'Y-m-d H:i:s', $id ); ?>">
			<?php
			// Event Date and Time Status (Postponed, TBD, Canceled)
			echo esc_html( get_the_time( sp_date_format(), $id ) ) . alchemists_event_time_status_badge( $id, false, false );
			?>
		</time>
		<div class="alc-event-result-box__header-heading">
			<?php
			$leagues = get_the_terms( $id, 'sp_league' );
			if ( $leagues ) : $league = array_shift( $leagues );
			?>
			<h5 class="alc-event-result-box__header-title">
				<?php
				// League
				echo esc_html( $league->name );
				?>

				<?php
				// Season
				$seasons = get_the_terms( $id, 'sp_season' );
				if ( $seasons ) : $season = array_shift( $seasons );
					echo esc_html( $season->name );
				endif;
				?>
			</h5>
			<?php
			endif;

			// Matchday
			if ( 'yes' === get_option( 'sportspress_event_show_day', 'yes' ) ) :
				$matchday = get_post_meta( $id, 'sp_day', true );
				if ( $matchday != '' ) : ?>
				<span class="alc-event-result-box__header-subtitle"><?php echo esc_html( $matchday ); ?></span>
				<?php endif;
			endif;
			?>
		</div>

		<?php
		// Venue
		$venues = get_the_terms( $id, 'sp_venue' );
		if ( $venues ) :
			?>

			<div class="alc-event-result-box__header-venue" itemprop="location" itemscope itemtype="http://schema.org/Place">
				<?php
				$venue_names = array();

				foreach ( $venues as $venue ) {
					$t_id = $venue->term_id;
					$t_name = '<span itemprop="name">' . $venue->name . '</span>';
					$meta = get_option( "taxonomy_$t_id" );
					$address = '<span class="d-none" itemprop="address" itemtype="http://schema.org/PostalAddress">' . sp_array_value( $meta, 'sp_address', null ) . '</span>';

					if ( $link_venues ) {
						$t_name = '<a href="'. get_term_link( $t_id, $venue->taxonomy ) .'" itemprop="url">' . $t_name . '</a>';
					}

					$venue_names[] = $t_name . $address;
				}
				echo implode( '/', $venue_names );
				?>
			</div>

			<?php
		endif;
		?>
	</header>
	<!-- Header / End -->

	<!-- Team & Result -->
	<div class="alc-event-result-box__content card__content">
		<div class="alc-event-result-box__teams alc-event-result-box__teams--default">
			<?php
			$j = 0;
			foreach( $teams as $team ):
				$j++;
				?>

				<div class="alc-event-result-box__team alc-event-result-box__team--<?php echo esc_attr( $j % 2 ? 'odd' : 'even' ); ?>">
					<div class="alc-event-result-box__team-img">
						<?php
						// Event Thumbnail
						if ( has_post_thumbnail( $id ) ) {
							echo '<div class="alc-event-result-box__event-bg">';
								echo get_the_post_thumbnail( $id, 'alchemists_thumbnail-sm', array( 'class' => 'alc-event-result-box__event-bg-img' ) );
							echo '</div>';
						}

						// Team Logos On Background
						if ( has_post_thumbnail ( $team ) ) {
							if ( $link_teams ) {
								echo '<a href="' . get_permalink( $team, false, true ) . '" title="' . get_the_title( $team ) . '">';
									echo get_the_post_thumbnail( $team, 'full', array( 'class' => 'alc-event-result-box__event-team-logo-img' ) );
								echo '</a>';
							} else {
								echo get_the_post_thumbnail( $team, 'full', array( 'class' => 'alc-event-result-box__event-team-logo-img' ) );
							}
						}
						?>
					</div>
					<div class="alc-event-result-box__team-body">
						<figure class="alc-event-result-box__team-logo">
							<?php
							if ( has_post_thumbnail ( $team ) ) :
								if ( $link_teams ) :
									echo '<a href="' . get_permalink( $team, false, true ) . '" title="' . get_the_title( $team ) . '">';
										echo get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit' );
									echo '</a>';
								else:
									echo get_the_post_thumbnail( $team, 'alchemists_team-logo-sm-fit' );
								endif;
							endif;
							?>
						</figure>

						<?php if ( $show_team_names ) : ?>
						<div class="alc-event-result-box__team-meta">
							<h5 class="alc-event-result-box__team-name"><?php echo esc_html( get_the_title( $team ) ); ?></h5>
							<span class="alc-event-result-box__team-subtitle">
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
							</span>
						</div>
						<?php endif; ?>
					</div>
				</div>

			<?php endforeach; ?>

			<!-- Score -->
			<div class="alc-event-result-box__team-score">
				<?php
				$status = esc_html__( 'Preview', 'alchemists' );

				if ( $show_stats ) :

					$status = esc_html__( 'Final Score', 'alchemists' );

					// 1st Team
					$team1_class = 'loss';
					$team1_outcome_abbr = $outcome_abbr_loss; // set loss by default
					if ( isset( $results[ $team1 ]['outcome'] ) && ! empty( $results[ $team1 ]['outcome'][0] ) ) {
						if ( $results[ $team1 ]['outcome'][0] == 'win' ) {
							$team1_class = 'win';
							$team1_outcome_abbr = $outcome_abbr_win;
						}
					}

					// 2nd Team
					$team2_class = 'loss';
					$team2_outcome_abbr = $outcome_abbr_loss; // set loss by default
					if ( isset( $results[ $team2 ]['outcome'] ) && ! empty( $results[ $team2 ]['outcome'][0] ) ) {
						if ( $results[ $team2]['outcome'][0] == 'win' ) {
							$team2_class = 'win';
							$team2_outcome_abbr = $outcome_abbr_win;
						}
					}


					if ( isset( $results[ $team1 ][ $primary_result ]) && isset( $results[ $team2 ][ $primary_result ] ) ) :
						?>
						<!-- 1st Team -->
						<span class="<?php echo esc_attr( $team1_class ); ?>">
							<?php
							if ( $show_results ) {
								echo esc_html( $results[ $team1 ][ $primary_result ] );
							} else {
								echo esc_html( $team1_outcome_abbr );
							}
							?>
						</span>
						<!-- 1st Team / End -->
						<span>-</span>
						<!-- 2nd Team -->
						<span class="<?php echo esc_attr( $team2_class ); ?>">
							<?php
							if ( $show_results ) {
								echo esc_html( $results[ $team2 ][ $primary_result ] );
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
			<!-- Score / End -->

		</div>

	</div>
	<!-- Team & Result / End -->

	<?php if ( $show_progress_bars && $show_stats ) : ?>
	<!-- Event Statistics -->
	<div class="alc-event-result-box__section">
		<header class="alc-event-result-box__subheader card__subheader card__subheader--sm card__subheader--nomargins">
			<h5 class="alc-event-result-box__subtitle"><?php echo esc_html( $event_stats_table_title ); ?></h5>
		</header>
		<div class="alc-event-result-box__section-inner">

			<?php
			// Get Performance
			$event_performance = sp_get_performance( $id );

			// Remove the first row to leave us with the actual data
			unset( $event_performance[0] );

			// Player Performance
			$performances_posts = get_posts(array(
				'post_type'      => 'sp_performance',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'DESC'
			));

			$performances_posts_array = array();
			$performance_ids = array();
			if ( $performances_posts ) {
				foreach( $performances_posts as $performance_post ) {
					// build Performance Posts array
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

			// Stats - Progress Bars
			$game_stats_array = array();
			$game_stats_array = alchemists_sp_filter_array( $performances_posts_array, $event_progress_bars_stats );
			?>

			<!-- Progress Stats Table -->
			<table class="progress-table progress-table--sm-space progress-table--fullwidth">
				<tbody>

				<?php
				// Progress Bars output
				foreach ( $game_stats_array as $game_stat_key => $game_stat_label ) :

					// Event Stats
					// must get the first array for Player-vs-Player mode compatibility
					$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
					$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
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
	</div>
	<!-- Event Statistics / End -->
	<?php endif; ?>

	<?php if ( $show_circular_bars && $show_stats ) : ?>
	<!-- Event Additional Stats -->
	<div class="alc-event-result-box__section">
		<header class="alc-event-result-box__subheader card__subheader card__subheader--sm card__subheader--nomargins">
			<h5 class="alc-event-result-box__subtitle"><?php esc_html_e( 'Additional Stats', 'alchemists' ); ?></h5>
		</header>
		<div class="alc-event-result-box__section-inner">

			<!-- Circular Bars -->
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
							$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
							$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
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
						$full_time = get_post_meta( $id, 'sp_minutes', true );
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
							$event_team1_first_array = isset( $event_performance[ $team1 ] ) ? key( $event_performance[ $team1 ] ) : 0;
							$event_team2_first_array = isset( $event_performance[ $team2 ] ) ? key( $event_performance[ $team2 ] ) : 0;
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
			<!-- Circular Bars / End -->

		</div>
	</div>
	<!-- Event Additional Stats / End -->
	<?php endif; ?>

</div>
<!-- ALC Event Result / End -->
