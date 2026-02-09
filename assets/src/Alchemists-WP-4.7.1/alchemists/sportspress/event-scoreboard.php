<?php
/**
 * Event Scoreboard
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version   2.6.15
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$defaults = array(
	'id' => null,
	'status' => 'default',
	'date' => 'default',
	'date_from' => 'default',
	'date_to' => 'default',
	'date_past' => 'default',
	'date_future' => 'default',
	'date_relative' => 'default',
	'day' => 'default',
	'league' => null,
	'season' => null,
	'date_format' => get_option( 'sportspress_scoreboard_date_format', 'M j' ),
	'number' => -1,
	'width' => get_option( 'sportspress_scoreboard_width', 180 ),
	'step' => get_option( 'sportspress_scoreboard_step', 2 ),
	'show_team_logo' => get_option( 'sportspress_scoreboard_show_logos', 'no' ) == 'yes' ? true : false,
	'link_events' => get_option( 'sportspress_link_events', 'yes' ) == 'yes' ? true : false,
	'order' => 'default',
	'show_all_events_link' => false,
	'show_date' => get_option( 'sportspress_scoreboard_show_date', 'no' ) == 'yes' ? true : false,
	'show_time' => get_option( 'sportspress_scoreboard_show_time', 'yes' ) == 'yes' ? true : false,
	'show_league' => get_option( 'sportspress_scoreboard_show_league', 'no' ) == 'yes' ? true : false,
	'show_season' => get_option( 'sportspress_scoreboard_show_season', 'no' ) == 'yes' ? true : false,
	'show_venue' => get_option( 'sportspress_scoreboard_show_venue', 'no' ) == 'yes' ? true : false,
);

extract( $defaults, EXTR_SKIP );

// check if RTL
$is_rtl = is_rtl() ? 'true' : 'false';

$calendar = new SP_Calendar( $id );
if ( $id ) {
	$calendar->number = $number;
} else {
	$calendar->number = get_option( 'sportspress_scoreboard_limit', 0 );
}
$calendar->date = 'auto';
if ( $status != 'default' )
	$calendar->status = $status;
if ( $date != 'default' )
	$calendar->date = $date;
if ( $date_from != 'default' )
	$calendar->from = $date_from;
if ( $date_to != 'default' )
	$calendar->to = $date_to;
if ( $date_past != 'default' )
	$calendar->past = $date_past;
if ( $date_future != 'default' )
	$calendar->future = $date_future;
if ( $date_relative != 'default' )
	$calendar->relative = $date_relative;
if ( $league )
	$calendar->league = $league;
if ( $season )
	$calendar->season = $season;
if ( $order != 'default' )
	$calendar->order = $order;
if ( $day != 'default' )
	$calendar->day = $day;
$data = $calendar->data();

if ( ! $data ) return;

$post_id = get_the_ID();

$reverse_teams = get_option( 'sportspress_event_reverse_teams', 'no' ) === 'yes' ? true : false;

// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'alc-scoreboard-' );
?>

<div class="alc-template-scoreboard">
	<div class="alc-scoreboard-wrapper">
		<div class="alc-scoreboard-content alc-scoreboard-content--alt-color">
			<div class="alc-scoreboard js-alc-scoreboard" id="<?php echo esc_attr( $identifier ); ?>">
				<?php
				$i = 0;

				if ( intval( $number ) > 0 )
					$limit = $number;

				foreach ( $data as $event ):
					if ( ! $event ) continue;
					if ( isset( $limit ) && $i >= $limit ) continue;

					$teams = sp_get_teams( $event->ID );
					if ( ! $teams ) continue;
					if ( $reverse_teams ) {
						$teams = array_reverse( $teams );
					}

					$permalink = get_post_permalink( $event, false, true );
					$status = sp_get_status( $event->ID );
					?>
					<div class="alc-scoreboard__event<?php if ( $post_id === $event->ID ) { ?> alc-scoreboard__event--highlight<?php } ?>" style="width:<?php echo esc_attr( $width ); ?>px;">
						<a href="<?php echo esc_url( $permalink ); ?>" class="alc-scoreboard__event-wrapper">

							<span class="alc-scoreboard__meta">
								<?php if ( $show_date ) { ?>
									<span class="alc-scoreboard__date"><?php sp_the_date( $event->ID, $date_format ); ?></span>
								<?php } ?>

								<?php if ( $show_time ) { ?>
									<span class="alc-scoreboard__time"><?php echo apply_filters( 'sportspress_event_time', sp_get_time( $event->ID ), $event->ID ); ?></span>
								<?php } ?>

								<span class="alc-scoreboard__league-season-wrapper">
									<?php if ( $show_league ) { ?>
										<span class="alc-scoreboard__league"><?php sp_the_leagues( $event->ID ); ?></span>
									<?php } ?>

									<?php if ( $show_season ) { ?>
										<span class="alc-scoreboard__season"><?php sp_the_seasons( $event->ID ); ?></span>
									<?php } ?>
								</span>

								<?php if ( $show_venue ) { ?>
									<span class="alc-scoreboard__venue"><?php sp_the_venues( $event->ID ); ?></span>
								<?php } ?>
							</span>

							<ul class="alc-scoreboard__teams list-unstyled">
								<?php
								if ( 'results' === $status ) {
									$results = apply_filters( 'sportspress_main_results', sp_get_main_results( $event->ID ), $event->ID );
									if ( $reverse_teams ) {
										$results = array_reverse( $results );
									}

									foreach ( $teams as $index => $team ) {
										$name = sp_team_short_name( $team );
										if ( ! $name ) continue;

										$name = '<span class="alc-scoreboard__team-name">' . $name . '</span>';

										if ( $show_team_logo ) {
											$name = '<span class="alc-scoreboard__team-logo">' . sp_get_logo( $team, 'mini' ) . '</span> ' . $name;
										}

										$result = sp_array_value( $results, $index, null );

										if ( $result != null ):
											$name .= ' <span class="alc-scoreboard__result">' . $result . '</span>';
										endif;

										echo '<li class="alc-scoreboard__team">' . $name . '</li>';
									}
								} else {
									foreach ( $teams as $index => $team ) {
										$name = sp_team_short_name( $team );
										if ( ! $name ) continue;

										$name = '<span class="alc-scoreboard__team-name">' . $name . '</span>';

										if ( $show_team_logo ) {
											$name = '<span class="alc-scoreboard__team-logo">' . sp_get_logo( $team, 'mini' ) . '</span> ' . $name;
										}

										echo '<li class="alc-scoreboard__team">' . $name . '<li>';
									}
								}
								?>
							</ul>
						</a>
					</div>
					<?php
					$i++;
				endforeach;
				?>
			</div>
		</div>
	</div>
	<?php
	if ( $id && $show_all_events_link )
		echo '<div class="alc-scoreboard__link-to-all"><a href="' . get_permalink( $id ) . '">' . esc_html__( 'View all events', 'alchemists' ) . '</a></div>';
	?>
</div>

<script>
	(function($){
		$(function() {

			var windowWidth = $(window).width();
			var numberToDisplay = Math.round( windowWidth / <?php echo esc_js( $width ); ?>);

			$('#<?php echo esc_js( $identifier ); ?>').slick({
				slidesToShow: numberToDisplay,
				autoplay: false,
				arrows: true,
				dots: false,
				infinite: false,
				rtl: <?php echo esc_js( $is_rtl ); ?>,
				rows: 0,
				slidesToScroll: <?php echo esc_js( $step ); ?>,
				responsive: [
					{
						breakpoint: 1200,
						settings: {
							slidesToShow: numberToDisplay,
						}
					},
					{
						breakpoint: 992,
						settings: {
							slidesToShow: 4,
						}
					},
					{
						breakpoint: 769,
						settings: {
							arrows: false,
							slidesToShow: 3,
						}
					},
					{
						breakpoint: 480,
						settings: {
							arrows: false,
							slidesToShow: 2
						}
					}
				]
			});
		});
	})(jQuery);
</script>
