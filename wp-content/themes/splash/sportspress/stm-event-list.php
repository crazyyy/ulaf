<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$primary_result = get_option( 'sportspress_primary_result', null );

$defaults = array(
	'id' => null,
	'status' => 'default',
	'date' => 'default',
	'number' => -1,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_venues' => get_option( 'sportspress_link_venues', 'yes' ) == 'yes' ? true : false,
	'sortable' => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable' => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'responsive' => get_option( 'sportspress_enable_responsive_tables', 'yes' ) == 'yes' ? true : false,
	'paginated' => get_option( 'sportspress_event_list_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => get_option( 'sportspress_event_list_rows', 10 ),
	'order' => 'default',
	'columns' => null,
	'show_all_events_link' => false,
);

extract( $defaults, EXTR_SKIP );

$calendar = new SP_Calendar( $id );
if ( $status != 'default' )
	$calendar->status = $status;
if ( $date != 'default' )
	$calendar->date = $date;
if ( $order != 'default' )
	$calendar->order = $order;
$data = $calendar->data();
$usecolumns = $calendar->columns;
$title_format = $calendar->title_format;

if ( isset( $columns ) ):
	if ( is_array( $columns ) )
		$usecolumns = $columns;
	else
		$usecolumns = explode( ',', $columns );
endif;
?>

<div class="stm-upcoming-events_list">
	<ul>
		<?php foreach ( $data as $event ){ ?>
			<?php
			$teams = get_post_meta( $event->ID, 'sp_team' );
			$results = get_post_meta( $event->ID, 'sp_results', true );
			$result = '';
			$i = 0;
			if($results){
				foreach($results as $val){ $i++;
					if($i == 1){
						if($val[splash_get_sportpress_points_system()] != ''){
							$result .= $val[splash_get_sportpress_points_system()].' / ';
						}
					}else{
						if($val[splash_get_sportpress_points_system()] == '') {
							$result .= '';
						}else{
							$result .= $val[splash_get_sportpress_points_system()];
						}
					}
				}
			}
			if(empty($result)) {
				$result = esc_html__('- vs -','splash');
			}
			$teams_output = '';
			$teams_array = '';
			?>
			<li class="clearfix">
				<div class="event_date heading-font">
					<div class="date">
						<div class="stm-top">
							<span><?php echo get_post_time( 'j', false, $event, true ); ?></span><figure>/</figure><?php echo get_post_time( 'm', false, $event, true ); ?>
						</div>
						<div class="stm-middle"><?php echo get_post_time( 'l', false, $event, true ); ?></div>
						<div class="stm-bottom"><?php echo get_post_time(get_option('time_format'), false, $event, true); ?></div>
					</div>
				</div>
				<div class="clearfix">
					<div class="commands">
						<?php if($teams){ ?>
							<h3><a href="<?php echo esc_url(get_the_permalink($teams[0])); ?>"><?php echo get_the_title($teams[0]); ?></a> <span class="stm-red"><?php echo wp_kses_post( $result ); ?></span> <a href="<?php echo esc_url(get_the_permalink($teams[1])); ?>"><?php echo esc_html( get_the_title($teams[1]) ); ?></a></h3>
						<?php } ?>
						<?php
						$venues = get_the_terms( $event->ID, 'sp_venue' );
						if ( $venues ){
							foreach ( $venues as $venue ) {
								echo '<div class="stadium"><i class="stm-icon-pin"></i> ' . $venue->name . '</div>';
							}
						}
						?>
						<?php
						$leagues = get_the_terms( $event->ID, 'sp_league' );
						if ( $leagues ){
							foreach ( $leagues as $league ) {
								echo '<div class="league"><i class="stm-icon-trophy"></i> ' . $league->name . '</div>';
							}
						}
						?>
					</div>
					<div class="read_more">
						<a class="button btn-secondary" href="<?php echo esc_url( get_post_permalink( $event->ID, false, true ) ); ?>"><span><?php esc_html_e('read more', 'splash'); ?></span></a>
					</div>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>