<?php
/**
 * Event List
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*Default template or sportpress */
$event_list_template = get_theme_mod('event_list_template', 'theme');

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
	'show_team_logo' => get_option( 'sportspress_event_list_show_logos', 'no' ) == 'yes' ? true : false,
	'link_events' => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'link_teams' => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'link_venues' => get_option( 'sportspress_link_venues', 'yes' ) == 'yes' ? true : false,
	'abbreviate_teams' => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false,
	'sortable' => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable' => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'paginated' => get_option( 'sportspress_event_list_paginated', 'yes' ) == 'yes' ? true : false,
	'rows' => get_option( 'sportspress_event_list_rows', 10 ),
	'order' => 'default',
	'columns' => null,
	'show_all_events_link' => false,
	'show_title' => get_option( 'sportspress_event_list_show_title', 'yes' ) == 'yes' ? true : false,
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
if ( $number )
    $calendar->number = $number;
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
if ( $day != 'default' )
	$calendar->day = $day;
$data = $calendar->data();
$usecolumns = $calendar->columns;
$title_format = get_option( 'sportspress_event_list_title_format', 'title' );
$time_format = get_option( 'sportspress_event_list_time_format', 'combined' );

if ( isset( $columns ) ):
	if ( is_array( $columns ) )
		$usecolumns = $columns;
	else
		$usecolumns = explode( ',', $columns );
endif;

if ( $show_title && false === $title && $id ):
	$caption = $calendar->caption;
	if ( $caption )
		$title = $caption;
	else
		$title = get_the_title( $id );
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
								$result .= $val[splash_get_sportpress_points_system()].' : ';
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
					<a href="<?php echo esc_url( get_post_permalink( $event->ID, false, true ) ); ?>">
						<div class="commands">
							<?php if($teams){ ?>
								<h3>
									<span><?php echo get_the_title($teams[0]); ?></span>
									<span class="stm-red"><?php echo wp_kses_post( $result ); ?></span>
									<span><?php echo esc_html( get_the_title($teams[1]) ); ?></span>
								</h3>
							<?php } ?>
						</div>
						<div class="stm-event_date heading-font">
							<div class="date">
								<div class="stm-middle"><?php echo get_post_time( 'F d, Y | h:m', false, $event, true ); ?></div>
							</div>
							<div class="stm-el-venue">
								<?php
								$venues = get_the_terms( $event->ID, 'sp_venue' );
								if ( $venues ){
									foreach ( $venues as $venue ) {
										echo '<div class="stadium">' . $venue->name . '</div>';
									}
								}
								?>
							</div>
						</div>
					</a>
				</li>
			<?php } ?>
		</ul>		
	</div>
