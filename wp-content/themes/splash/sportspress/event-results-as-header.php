<?php
/**
 * Created by PhpStorm.
 * User: NDimaA
 * Date: 16.02.2017
 * Time: 14:36
 */


$teams = get_post_meta(get_the_ID(), 'sp_team', false);
$showBtn = get_post_meta(get_the_ID(), 'show_ticket_button', true);
$btnTicketLink = get_post_meta(get_the_ID(), 'ticket_link', "");
$btnTicketLink = (count($btnTicketLink) > 0) ? $btnTicketLink[0] : "";

/* Get league names */
$leagues = wp_get_post_terms(get_the_id(), 'sp_league');

$leagues_names = array();
if (!empty($leagues)) {
	foreach ($leagues as $league) {
		$leagues_names[] = $league->name;
	}
}

/*Get venue name*/
$venue = wp_get_post_terms(get_the_id(), 'sp_venue');
$venue_name = '';
if(!empty($venue) and !is_wp_error($venue)) {
	$venue_name = $venue[0]->name;
}

$performance = sp_get_performance(get_the_ID());
$team_1_id = $teams[0];
$team_2_id = $teams[1];
$city_1 = wp_get_post_terms($team_1_id, 'sp_venue');
$city_2 = wp_get_post_terms($team_2_id, 'sp_venue');
$team_results = get_post_meta(get_the_ID(), 'sp_results', false);
$permalink = get_post_permalink( get_the_ID(), false, true );
$results = get_post_meta( get_the_ID(), 'sp_results', true );
$point_system = splash_get_sportpress_points_system();

$sportspress_primary_result = get_option( 'sportspress_primary_result', null );

if( !empty( $sportspress_primary_result ) )
	$goals = $sportspress_primary_result;
else
	$goals = "goals" ;

?>

<div class="container">
	<div class="stm-event-results-as-header-wrapper <?php if(empty($team_results[0][$team_1_id]['outcome'][0])) echo "stm-event-feature"; ?>">
		<h2><?php echo get_the_title(); ?></h2>
        <?php if(!empty($leagues_names[0])): ?>
		    <h4><?php echo esc_html($leagues_names[0]); ?></h4>
        <?php endif; ?>
		<div class="fixture_detail clearfix">
			<div class="command_left">
				<div class="command_info">
					<div class="logo">
						<a href="<?php echo esc_url( get_the_permalink($team_1_id) ); ?>"><?php echo get_the_post_thumbnail($team_1_id, "team_logo"); ?></a>
					</div>
					<?php if(!empty($team_results[0][$team_1_id])): ?>
						<?php if(isset($team_results[0][$team_1_id]['outcome']) and !empty($team_results[0][$team_1_id]['outcome'][0])): ?>
							<div class="score heading-font"><?php echo (isset($team_results[0][$team_1_id][$point_system])) ? esc_attr($team_results[0][$team_1_id][$point_system]) : 0;?></div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="goals">
					<h2>
						<a href="<?php echo esc_url( get_the_permalink($team_1_id) ); ?>"><?php echo esc_html( get_the_title( $team_1_id ) ); ?></a>
					</h2>
					<?php if(!empty($team_results[0])): ?>
						<?php if(!empty($team_results[0][$team_1_id])): ?>
							<?php if(isset($team_results[0][$team_1_id]['outcome']) and !empty($team_results[0][$team_1_id]['outcome'][0])): ?>
								<?php if($team_results[0][$team_1_id]['outcome'][0] == 'win'): ?>
									<div class="stm-latest-result-win-label heading-font"><?php esc_html_e('win', 'splash') ?></div>
								<?php else: ?>
									<div class="stm-latest-result-lose-label heading-font"><?php printf(_x('%s', 'Outcome for team', 'splash'), $team_results[0][$team_1_id]['outcome'][0]); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
					<ul class="players">
						<?php
						if($performance[$team_1_id] != null) {
							foreach ($performance[$team_1_id] as $player_id => $player) {
								if (splash_is_layout("af")) {
									$goalsList = 'td';
									$title = esc_html__('touchdown(s)', 'splash');
								} else {
									$goalsList = $goals;
									$title = esc_html__('goal(s)', 'splash');
								}
								
								if (isset($player[$goalsList])) {
									if ($player[$goalsList] >= 1) {
										?>
										<li>
											<?php echo esc_html(get_the_title($player_id)) ?> -
											<span><?php echo esc_html($player[$goalsList]) . ' ' . $title; ?></span>
										</li>
										<?php
									}
								}
							}
						}
						
						?>
					</ul>
				</div>
			</div>
			<?php if(empty($team_results[0][$team_1_id]['outcome'][0])): ?>
				<div class="stm-event-vs heading-font">vs</div>
			<?php endif; ?>
			<div class="command_right">
				<div class="command_info">
					<div class="logo">
						<a href="<?php echo esc_url( get_the_permalink($team_2_id) ); ?>"><?php echo get_the_post_thumbnail($team_2_id, "team_logo"); ?></a>
					</div>
					<?php if(!empty($team_results[0][$team_2_id])): ?>
						<?php if(isset($team_results[0][$team_2_id]['outcome']) and !empty($team_results[0][$team_2_id]['outcome'][0])): ?>
							<span class="score heading-font"><?php echo (isset($team_results[0][$team_2_id][$point_system])) ? esc_attr($team_results[0][$team_2_id][$point_system]) : 0; ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="goals">
					<h2>
						<a href="<?php echo esc_url( get_the_permalink($team_2_id) ); ?>"><?php echo esc_html( get_the_title( $team_2_id ) ); ?></a>
					</h2>
					<?php if(!empty($team_results[0])): ?>
						<?php if(!empty($team_results[0][$team_2_id])): ?>
							<?php if(isset($team_results[0][$team_2_id]['outcome']) and !empty($team_results[0][$team_2_id]['outcome'][0])): ?>
								<?php if($team_results[0][$team_2_id]['outcome'][0] == 'win'): ?>
									<div class="stm-latest-result-win-label heading-font"><?php esc_html_e('win', 'splash') ?></div>
								<?php else: ?>
									<div class="stm-latest-result-lose-label heading-font"><?php printf(_x('%s', 'Outcome for team', 'splash'), $team_results[0][$team_2_id]['outcome'][0]); ?></div>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
					<ul class="players">
					<?php
					if($performance[$team_2_id] != null) {
						foreach ($performance[$team_2_id] as $player_id => $player) {
							if (splash_is_layout("af")) {
								$goalsList = 'td';
								$title = esc_html__('touchdown(s)', 'splash');
							} else {
								$goalsList = $goals;
								$title = esc_html__('goal(s)', 'splash');
							}
							
							if (isset($player[$goalsList])) {
								if ($player[$goalsList] >= 1) {
									?>
									<li>
										<?php echo esc_html(get_the_title($player_id)) ?> -
										<span><?php echo esc_html($player[$goalsList]) . ' ' . $title; ?></span>
									</li>
									<?php
								}
							}
						}
					}
						
					?>
					</ul>
				</div>
			</div>
		</div>
		
		<?php
		$date_counter = get_the_date("Y-m-d H:s:i");
		$date = get_the_time( get_option('date_format'), get_the_ID() );
		$time = get_the_time(get_option('time_format'), get_the_ID());
		?>
		
		<?php if(empty($team_results[0][$team_1_id]['outcome'][0])) : ?>
		<div class="fixture_info_future">
			<div class="stm-event-as-header-date-future-wrapp">
				<div class="date"><i class="fa fa-calendar-o" aria-hidden="true"></i><?php echo esc_html( $date ); ?></div>
				<div class="time">
					<i class="fa fa-clock-o" aria-hidden="true"></i>
					<?php echo esc_html($time); ?>
				</div>
				<div class="venue"><i class="fa fa-location-arrow" aria-hidden="true"></i><?php echo esc_html( $venue_name ); ?></div>
			</div>
			<div class="stm-countdown-wrapper">
				<time class="heading-font" datetime="<?php echo esc_attr($date_counter) ?>"  data-countdown="<?php echo esc_attr( str_replace( "-", "/", $date_counter ) ) ?>"></time>
			</div>
			<?php if($showBtn != ""): ?>
				<a class="button btn-md" href="<?php echo esc_url($btnTicketLink); ?>">
					<i class="fa fa-video-camera" aria-hidden="true"></i>
					<span><?php echo esc_html__('Buy Tickets', 'splash'); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<?php else: ?>
		<div class="fixture_info_publish">
			<div class="stm-event-as-header-date-publish-wrapp">
				<div class="stm-flex-column">
					<div class="date"><?php echo esc_html( $date ); ?> | <?php echo esc_html($time); ?></div>
				</div>
				<div class="venue"><?php echo esc_html( $venue_name ); ?></div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
