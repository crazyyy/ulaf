<?php
/**
 * Countdown
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version     2.7.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$defaults = array(
	'team'           => null,
	'calendar'       => null,
	'order'          => null,
	'orderby'        => null,
	'league'         => null,
	'season'         => null,
	'id'             => null,
	'title'          => null,
	'live'           => get_option( 'sportspress_enable_live_countdowns', 'yes' ) == 'yes' ? true : false,
	'link_events'    => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'link_teams'     => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_venues'    => get_option( 'sportspress_link_venues', 'no' ) == 'yes' ? true : false,
	'show_logos'     => get_option( 'sportspress_countdown_show_logos', 'no' ) == 'yes' ? true : false,
	'show_thumbnail' => get_option( 'sportspress_countdown_show_thumbnail', 'no' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

if ( isset( $show_excluded ) && $show_excluded ) {
	$excluded_statuses = array();
} else {
	$excluded_statuses = apply_filters(
		'sp_countdown_excluded_statuses',
		array(
			'postponed',
			'cancelled',
		)
	);
}

if ( isset( $id ) ) :
	$post = get_post( $id );
elseif ( $calendar ) :
	$calendar = new SP_Calendar( $calendar );
	if ( $team ) {
		$calendar->team = $team;
	}
	$calendar->status = 'future';
	if ( $order ) {
		$calendar->order = $order;
	} else {
		$calendar->order = 'ASC';
	}
	if ( $orderby ) {
		$calendar->orderby = $orderby;
	}
	$data = $calendar->data();

	/**
	 * Exclude postponed or cancelled events.
	 */
	while ( $post = array_shift( $data ) ) {
		$sp_status = get_post_meta( $post->ID, 'sp_status', true );
		if ( ! in_array( $sp_status, $excluded_statuses ) ) {
			break;
		}
	}
else :
	$args = array();
	if ( isset( $team ) ) {
		$args['meta_query'] = array(
			array(
				'key'   => 'sp_team',
				'value' => $team,
			),
		);
	}
	if ( isset( $league ) || isset( $season ) ) {
		$args['tax_query'] = array( 'relation' => 'AND' );

		if ( isset( $league ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'sp_league',
				'terms'    => $league,
			);
		}

		if ( isset( $season ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'sp_season',
				'terms'    => $season,
			);
		}
	}

	/**
	 * Exclude postponed or cancelled events.
	 */
	$args['meta_query'][] = array(
		'relation' => 'OR',
		array(
			'key'     => 'sp_status',
			'compare' => 'NOT IN',
			'value'   => $excluded_statuses,
		),
		array(
			'key'     => 'sp_status',
			'compare' => 'NOT EXISTS',
		),
	);

	$post = sp_get_next_event( $args );
endif;

if ( ! isset( $post ) || ! $post ) {
	return;
}


echo '<div class="card">';

	echo '<div class="card__content">'; ?>

		<?php
		if ( $show_thumbnail && has_post_thumbnail( $post ) ) :
		?>
		<div class="event-image sp-event-image">
			<?php echo get_the_post_thumbnail( $post, 'alchemists_thumbnail-alt' ); ?>
		</div>
		<?php endif; ?>

		<?php
		echo '<header class="match-preview__header">';

			// Heading
			if ( $title ) {
				echo '<h2 class="match-preview__heading">' . wp_kses_post( $title ) . '</h2>';
			}

			// Event League
			if ( isset( $show_league ) && $show_league ):
				$leagues = get_the_terms( $post->ID, 'sp_league' );
				if ( $leagues ):
					foreach( $leagues as $league ):
						$term = get_term( $league->term_id, 'sp_league' );
						?>
						<h3 class="match-preview__title"><?php echo esc_html( $term->name ); ?></h3>
						<?php
					endforeach;
				endif;
			endif;

			if ( isset( $show_date ) && $show_date ):
				// Event Date
				echo '<time class="match-preview__date" datetime="' . esc_attr( $post->post_date ) . '">' . get_the_time( get_option( 'date_format' ), $post ) . '</time>';
			endif;

			$matchday = get_post_meta( $post->ID, 'sp_day', true );
			if ( $matchday != '' ) :
				?>
				<div class="widget-game-result__matchday">(<?php echo esc_html( $matchday ); ?>)</div>
				<?php
			endif;

		echo '</header>';
		?>
		<div class="match-preview">
			<section class="match-preview__body">
				<div class="match-preview__content">
					<?php
					$teams = array_unique( (array) get_post_meta( $post->ID, 'sp_team' ) );
					$i = 0;

					if ( is_array( $teams ) ) {
						foreach ( $teams as $team ) {
							$i++;

							echo '<div class="match-preview__team match-preview__team--' . ( $i % 2 ? 'odd' : 'even' ) . '">';
								if ( has_post_thumbnail ( $team ) ) {
									echo '<figure class="match-preview__team-logo">';
										if ( $link_teams ) {
											echo '<a class="team-logo" href="' . esc_url( get_post_permalink( $team ) ) . '" title="' . esc_attr( get_the_title( $team ) ) . '">' . get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' ) . '</a>';
										} else {
											echo get_the_post_thumbnail( $team, 'alchemists_team-logo-fit' );
										}
									echo '</figure>';
								}
								echo '<h5 class="match-preview__team-name">' . esc_html( get_the_title( $team ) ) . '</h5>';
							echo '</div>';

						}

					}
					?>



					<div class="match-preview__vs">
						<div class="match-preview__conj"><?php esc_html_e( 'VS', 'alchemists' ); ?></div>
						<div class="match-preview__match-info">
							<?php if ( isset( $show_date ) && $show_date ) : ?>
								<time class="match-preview__match-time" datetime="<?php echo esc_attr( $post->post_date ); ?>">
									<?php
									if ( isset( $show_status ) && $show_status ) {
										echo alchemists_event_time_status_badge( $post->ID, true, false );
									}
									?>
								</time>
							<?php endif; ?>
							<?php
							if ( isset( $show_venue ) && $show_venue ) :
								$venues = get_the_terms( $post->ID, 'sp_venue' );
								if ( $venues ) :
									?>

									<div class="match-preview__match-place">
										<?php
										if ( $link_venues ) {
											the_terms( $post->ID, 'sp_venue' );
										} else {
											$venue_names = array();
											foreach ( $venues as $venue ) {
												$venue_names[] = $venue->name;
											}
											echo wp_kses_post( implode( '/', $venue_names ) );
										}
										?>
									</div>

									<?php
								endif;
							endif; ?>

						</div>
					</div>

					<?php
					$now      = new DateTime( current_time( 'mysql', 0 ) );
					$date     = new DateTime( $post->post_date );
					$interval = date_diff( $now, $date );

					$days = $interval->invert ? 0 : $interval->days;
					$h    = $interval->invert ? 0 : $interval->h;
					$i    = $interval->invert ? 0 : $interval->i;
					$s    = $interval->invert ? 0 : $interval->s;
					?>
				</div>

				<?php if ( alchemists_sp_preset( 'basketball' ) ) : ?>
					<?php if ( $link_events ) : ?>
						<div class="match-preview__action">
							<a href="<?php echo get_post_permalink( $post->ID, false, true ); ?>" class="btn btn-default btn-block"><?php echo esc_html_x( 'Read More', 'event countdown', 'alchemists' ); ?></a>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</section>

			<?php if ( alchemists_sp_preset( 'basketball' ) ) : ?>
			<section class="match-preview__countdown countdown">
				<h4 class="countdown__title"><?php esc_html_e( 'Game Countdown', 'alchemists' ); ?></h4>
			<?php endif; ?>

				<div class="countdown__content">
					<div class="countdown sp-countdown
						<?php if ( $days >= 10 ) : ?> long-countdown<?php endif; ?>">
						<time class="countdown-counter" datetime="<?php echo esc_attr( $post->post_date ); ?>"<?php if ( $live ): ?> data-countdown="<?php echo esc_attr( str_replace( '-', '/', get_gmt_from_date( $post->post_date ) ) ); ?>"<?php endif; ?>>
							<span><?php echo wp_kses_post( sprintf( '%02s', $days ) ); ?> <small><?php esc_html_e( 'days', 'alchemists' ); ?></small></span>
							<span><?php echo wp_kses_post( sprintf( '%02s', $h ) ); ?> <small><?php esc_html_e( 'hrs', 'alchemists' ); ?></small></span>
							<span><?php echo wp_kses_post( sprintf( '%02s', $i ) ); ?> <small><?php esc_html_e( 'mins', 'alchemists' ); ?></small></span>
							<span><?php echo wp_kses_post( sprintf( '%02s', $s ) ); ?> <small><?php esc_html_e( 'secs', 'alchemists' ); ?></small></span>
						</time>
					</div>
				</div>

			<?php if ( alchemists_sp_preset( 'basketball' ) ) : ?>
			</section>
			<?php endif; ?>

			<?php if ( alchemists_sp_preset( 'soccer' ) || alchemists_sp_preset( 'football' ) || alchemists_sp_preset( 'esports' ) ) : ?>
				<?php if ( $link_events ) : ?>
					<div class="match-preview__action match-preview__action--ticket <?php echo esc_attr( alchemists_sp_preset( 'esports' ) ? 'text-center' : ''); ?>">
						<a href="<?php echo get_post_permalink( $post->ID, false, true ); ?>" class="<?php echo esc_attr( alchemists_sp_preset( 'esports' ) ? 'btn btn-primary-inverse' : 'btn btn-primary-inverse btn-lg btn-block'); ?>"><?php echo esc_html_x( 'Read More', 'event countdown', 'alchemists' ); ?></a>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		</div>
	</div>
</div>
