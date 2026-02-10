<?php
/**
 * Based on SporsPress Event Blocks
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$otfs_status    = isset( $otfs_status ) ? $otfs_status : 'default';
$format         = isset( $format ) ? $format : 'default';
$official_id    = isset( $official_id ) ? $official_id : null;
$number         = isset( $number ) ? $number : -1;
$otfs_order     = isset( $otfs_order ) ? $otfs_order : 'default';
$otfs_orderby   = isset( $otfs_orderby ) ? $otfs_orderby : 'default';
$columns        = isset( $columns ) ? $columns : null;
$hide_if_empty  = isset( $hide_if_empty ) ? $hide_if_empty : false;
$rows           = get_option( 'sportspress_event_blocks_rows', 5 );
$show_team_logo = 'yes' === get_option( 'sportspress_event_blocks_show_logos', 'yes' ) ? true : false;
$link_teams     = 'yes' === get_option( 'sportspress_link_teams', 'no' ) ? true : false;
$link_events    = 'yes' === get_option( 'sportspress_link_events', 'yes' ) ? true : false;
$paginated      = 'yes' === get_option( 'sportspress_event_blocks_paginated', 'yes' ) ? true : false;
$show_title     = 'yes' === get_option( 'sportspress_event_blocks_show_title', 'no' ) ? true : false;
$show_league    = 'yes' === get_option( 'sportspress_event_blocks_show_league', 'no' ) ? true : false;
$show_season    = 'yes' === get_option( 'sportspress_event_blocks_show_season', 'no' ) ? true : false;
$show_matchday  = 'yes' === get_option( 'sportspress_event_blocks_show_matchday', 'no' ) ? true : false;
$show_venue     = 'yes' === get_option( 'sportspress_event_blocks_show_venue', 'no' ) ? true : false;

$official         = new OTFS_Officials( $official_id );
$official->status = $otfs_status;
$official->order  = $otfs_order;
$data             = $official->events();
$usecolumns       = array();

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

if ( $show_title && false === $otfs_title && $id ) :
	$caption = $calendar->caption;
	if ( $caption ) {
		$otfs_title = $caption;
	} else {
		$otfs_title = get_the_title( $id );
	}
endif;

if ( $otfs_title ) {
	echo '<h4 class="sp-table-caption">' . wp_kses_post( $otfs_title ) . '</h4>';
}
?>
<div class="sp-template sp-template-event-blocks">
	<div class="sp-table-wrapper">
		<table class="sp-event-blocks sp-data-table
		<?php
		if ( $paginated ) {
			?>
			sp-paginated-table<?php } ?>" data-sp-rows="<?php echo esc_attr( $rows ); ?>">
			<thead><tr><th></th></tr></thead> <?php // Required for DataTables. ?>
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

					$permalink = get_post_permalink( $event, false, true );
					$results   = sp_get_main_results_or_time( $event );

					$teams        = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
					$teams        = array_filter( $teams, 'sp_filter_positive' );
					$logos        = array();
					$event_status = get_post_meta( $event->ID, 'sp_status', true );

					if ( get_option( 'sportspress_event_reverse_teams', 'no' ) === 'yes' ) {
						$teams   = array_reverse( $teams, true );
						$results = array_reverse( $results, true );
					}

					if ( $show_team_logo ) :
						$j = 0;
						foreach ( $teams as $team ) :
							++$j;
							$team_name = get_the_title( $team );
							if ( has_post_thumbnail( $team ) ) :
								$logo = get_the_post_thumbnail( $team, 'sportspress-fit-icon', array( 'itemprop' => 'logo' ) );

								if ( $link_teams ) :
									$team_permalink = get_permalink( $team, false, true );
									$logo           = '<a href="' . $team_permalink . '" itemprop="url" content="' . $team_permalink . '">' . $logo . '</a>';
								endif;

								$logo = '<span class="team-logo logo-' . ( $j % 2 ? 'odd' : 'even' ) . '" title="' . $team_name . '" itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam"><meta itemprop="name" content="' . $team_name . '">' . $logo . '</span>';
							else :
								$logo = '<span itemprop="competitor" itemscope itemtype="http://schema.org/SportsTeam"><meta itemprop="name" content="' . $team_name . '"></span>';
							endif;

							$logos[] = $logo;
						endforeach;
					endif;
					?>
					<tr class="sp-row sp-post<?php echo ( 0 === $i % 2 ? ' alternate' : '' ); ?>" itemscope itemtype="http://schema.org/SportsEvent">
						<td>
							<?php do_action( 'sportspress_event_blocks_before', $event, $usecolumns ); ?>
							<?php echo wp_kses_post( implode( ' ', $logos ) ); ?>
							<time class="sp-event-date" datetime="<?php echo esc_attr( $event->post_date ); ?>" itemprop="startDate" content="<?php echo esc_attr( mysql2date( 'Y-m-d\TH:iP', $event->post_date ) ); ?>">
								<?php echo wp_kses_post( sp_add_link( get_the_time( get_option( 'date_format' ), $event ), $permalink, $link_events ) ); ?>
							</time>
							<?php
							if ( $show_matchday ) :
								$matchday = get_post_meta( $event->ID, 'sp_day', true ); if ( '' !== $matchday ) :
									?>
								<div class="sp-event-matchday">(<?php echo wp_kses_post( $matchday ); ?>)</div>
															<?php
							endif;
endif;
							?>
							<h5 class="sp-event-results">
								<?php echo wp_kses_post( sp_add_link( '<span class="sp-result ' . $event_status . '">' . implode( '</span> - <span class="sp-result">', apply_filters( 'sportspress_event_blocks_team_result_or_time', $results, $event->ID ) ) . '</span>', $permalink, $link_events ) ); ?>
							</h5>
							<?php
							if ( $show_league ) :
								$leagues = get_the_terms( $event, 'sp_league' );
								if ( $leagues ) :
									$league = array_shift( $leagues );
									?>
								<div class="sp-event-league"><?php echo wp_kses_post( $league->name ); ?></div>
															<?php
															endif;
endif;
							?>
							<?php
							if ( $show_season ) :
								$seasons = get_the_terms( $event, 'sp_season' );
								if ( $seasons ) :
									$season = array_shift( $seasons );
									?>
								<div class="sp-event-season"><?php echo wp_kses_post( $season->name ); ?></div>
															<?php
															endif;
endif;
							?>
							<?php
							if ( $show_venue ) :
								$venues = get_the_terms( $event, 'sp_venue' );
								if ( $venues ) :
									$venue = array_shift( $venues );
									?>
								<div class="sp-event-venue" itemprop="location" itemscope itemtype="http://schema.org/Place"><div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><?php echo wp_kses_post( $venue->name ); ?></div></div>
															<?php
															endif;
endif;
							?>
							<h4 class="sp-event-title" itemprop="name">
								<?php echo wp_kses_post( sp_add_link( $event->post_title, $permalink, $link_events ) ); ?>
							</h4>
							<?php do_action( 'sportspress_event_blocks_after', $event, $usecolumns ); ?>

						</td>
					</tr>
					<?php
					++$i;
				endforeach;
				?>
			</tbody>
		</table>
	</div>
</div>
