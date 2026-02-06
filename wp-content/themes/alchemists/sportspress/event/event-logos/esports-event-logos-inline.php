<?php
/**
 * The template for displaying Event Score (Inline) on Single Event page
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.4.5
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
$outcome_abbr_win  = isset( $event_outcome_array['win'] ) ? sp_get_post_abbreviation( $event_outcome_array['win'] ) : esc_html( 'W', 'alchemists' );
$outcome_abbr_loss = isset( $event_outcome_array['loss'] ) ? sp_get_post_abbreviation( $event_outcome_array['loss'] ) : esc_html( 'L', 'alchemists' );
?>

<!-- ALC Event Result -->
<div class="alc-event-result-box card card--no-paddings" itemscope itemtype="http://schema.org/SportsEvent">

	<!-- Header -->
	<header class="alc-event-result-box__header alc-event-result-box__header--center card__header card__header--no-highlight" itemprop="name" content="<?php echo esc_attr( get_the_title( $id ) ); ?>">
		<time class="alc-event-result-box__header-date" itemprop="startDate" datetime="<?php echo get_the_time( 'Y-m-d H:i:s' ); ?>">
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
								echo get_the_post_thumbnail( $id, 'alchemists_thumbnail-sm' );
							echo '</div>';
						}

						// Team Logos On Background
						if ( has_post_thumbnail ( $team ) ) {
							if ( $link_teams ) {
								echo '<a href="' . get_permalink( $team, false, true ) . '" title="' . get_the_title( $team ) . '">';
									echo get_the_post_thumbnail( $team, 'full' );
								echo '</a>';
							} else {
								echo get_the_post_thumbnail( $team, 'full' );
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

				if ( ! empty( $results ) ) :

					$status = esc_html__( 'Final Score', 'alchemists' );

					// 1st Team
					$team1_class = 'loss';
					$team1_outcome_abbr = $outcome_abbr_loss; // set loss by default
					if ( ! empty( $results ) ) {
						if ( ! empty( $results[ $team1 ] ) ) {
							if ( isset( $results[ $team1 ]['outcome'] ) && ! empty( $results[ $team1 ]['outcome'][0] ) ) {
								if ( $results[ $team1 ]['outcome'][0] == 'win' ) {
									$team1_class = 'win';
									$team1_outcome_abbr = $outcome_abbr_win;
								}
							}
						}
					}

					// 2nd Team
					$team2_class = 'loss';
					$team2_outcome_abbr = $outcome_abbr_loss; // set loss by default
					if ( ! empty( $results ) ) {
						if ( ! empty( $results[ $team2 ] ) ) {
							if ( isset( $results[ $team2 ]['outcome'] ) && ! empty( $results[ $team2 ]['outcome'][0] ) ) {
								if ( $results[ $team2]['outcome'][0] == 'win' ) {
									$team2_class = 'win';
									$team2_outcome_abbr = $outcome_abbr_win;
								}
							}
						}
					}


					if ( ! empty ( $results ) ) :
						if ( ! empty( $results[ $team1 ] ) && ! empty( $results[ $team2 ] ) ) :
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
						endif;
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

</div>
<!-- ALC Event Result / End -->
