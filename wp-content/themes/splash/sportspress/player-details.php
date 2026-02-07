<?php
/**
 * Player Details
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version   2.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( get_option( 'sportspress_player_show_details', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $id ) ) {
	$id = get_the_ID();
}

$defaults = array(
	'show_number'            => get_option( 'sportspress_player_show_number', 'no' ) === 'yes',
	'show_name'              => get_option( 'sportspress_player_show_name', 'no' ) === 'yes',
	'show_nationality'       => get_option( 'sportspress_player_show_nationality', 'yes' ) === 'yes',
	'show_positions'         => get_option( 'sportspress_player_show_positions', 'yes' ) === 'yes',
	'show_current_teams'     => get_option( 'sportspress_player_show_current_teams', 'yes' ) === 'yes',
	'show_past_teams'        => get_option( 'sportspress_player_show_past_teams', 'yes' ) === 'yes',
	'show_leagues'           => get_option( 'sportspress_player_show_leagues', 'no' ) === 'yes',
	'show_seasons'           => get_option( 'sportspress_player_show_seasons', 'no' ) === 'yes',
	'show_nationality_flags' => get_option( 'sportspress_player_show_flags', 'yes' ) === 'yes',
	'abbreviate_teams'       => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes',
	'link_teams'             => get_option( 'sportspress_link_teams', 'no' ) === 'yes',
);

extract( $defaults, EXTR_SKIP );

$countries = SP()->countries->countries;

$player = new SP_Player( $id );

$nationalities  = $player->nationalities();
$positions      = $player->positions();
$current_teams  = $player->current_teams();
$past_teams     = $player->past_teams();
$leagues        = $player->leagues();
$seasons        = $player->seasons();
$metrics_before = $player->metrics( true );
$metrics_after  = $player->metrics( false );

$common = array();

if ( $show_number ) :
	$common['#'] = $player->number;
endif;

if ( $show_name ) :
	$common[ esc_html__( 'Name', 'splash' ) ] = $player->post->post_title;
endif;

if ( $show_nationality ) :
	$nationalities = $player->nationalities();
	if ( $nationalities && is_array( $nationalities ) ) :
		$values = array();
		foreach ( $nationalities as $nationality ) :
			$country_name = sp_array_value( $countries, $nationality, null );
			$values[]     = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
		endforeach;
		$common[ esc_html__( 'Nationality', 'splash' ) ] = implode( '<br>', $values );
	endif;
endif;

if ( $show_positions ) :
	$positions = $player->positions();
	if ( $positions && is_array( $positions ) ) :
		$position_names = array();
		foreach ( $positions as $position ) :
			$position_names[] = $position->name;
		endforeach;
		$common[ esc_html__( 'Position', 'splash' ) ] = implode( ', ', $position_names );
	endif;
endif;

$data = array_merge( $metrics_before, $common, $metrics_after );

if ( $show_current_teams ) :
	$current_teams = array_filter( $player->current_teams() );
	if ( $current_teams ) :
		$teams = array();
		foreach ( $current_teams as $team ) :
			$team_name = sp_team_short_name( $team );
			if ( $link_teams ) {
				$team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
			}
			$teams[] = $team_name;
		endforeach;
		$data[ esc_html__( 'Current Team', 'splash' ) ] = implode( ', ', $teams );
	endif;
endif;

if ( $show_past_teams && ! splash_is_layout( 'baseball' ) ) :
	$past_teams = array_filter( $player->past_teams() );
	if ( $past_teams ) :
		$teams = array();
		foreach ( $past_teams as $team ) :
			$team_name = sp_team_short_name( $team );
			if ( $link_teams ) {
				$team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
			}
			$teams[] = $team_name;
		endforeach;
		$data[ esc_html__( 'Past Teams', 'splash' ) ] = implode( ', ', $teams );
	endif;
endif;

if ( $show_leagues ) :
	$leagues = $player->leagues();
	if ( $leagues && ! is_wp_error( $leagues ) ) :
		$terms = array();
		foreach ( $leagues as $league ) {
			$terms[] = $league->name;
		}
		$data[ esc_html__( 'Leagues', 'splash' ) ] = implode( ', ', $terms );
	endif;
endif;

if ( $show_seasons ) :
	$seasons = $player->seasons();
	if ( $seasons && ! is_wp_error( $seasons ) ) :
		$terms = array();
		foreach ( $seasons as $season ) {
			$terms[] = $season->name;
		}
		$data[ esc_html__( 'Seasons', 'splash' ) ] = implode( ', ', $terms );
	endif;
endif;

$data          = apply_filters( 'sportspress_player_details', $data, $id );
$player_number = get_post_meta( $id, 'sp_number', true );

$socials = array( 'facebook', 'twitter', 'instagram', 'dribbble' );
if ( empty( $data ) ) {
	return;
} ?>
	<div class="sp-template sp-template-player-details sp-template-details">
		<div class="sp-list-wrapper">
			<!-- basketball -->
			<?php if ( splash_is_layout( 'bb' ) ) : ?>
				<?php if ( $show_name ) : ?>
					<h1 class="stm-main-title-unit"><?php echo esc_html( get_the_title( $id ) ); ?></h1>
				<?php endif; ?>
				<?php if ( ! empty( $player_number ) && $show_number ) : ?>
					<div class="stm-player-number">
						<div class="inner">
							<span class="stm-grey"><?php echo esc_html__( 'N:', 'splash' ); ?></span><span class="stm-red"><?php echo esc_attr( $player_number ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			<!--  basketball end -->
			<!--  af -->
			<?php elseif ( splash_is_layout( 'af' ) && splash_is_layout( 'rugby' ) ) : ?>
				<div class="stm-player-position-number-wrapp heading-font">
					<div class="stm-player-number"><?php echo esc_attr( $player_number ); ?></div>
					<div class="stm-player-position"><?php echo esc_html( $positions[0]->name ); ?></div>
				</div>
			<!--  af end -->
		<!--  baseball && hockey -->
			<?php elseif ( splash_is_layout( 'baseball' ) || splash_is_layout( 'hockey' ) ) : ?>
				<!-- hockey -->
				<?php if ( splash_is_layout( 'hockey' ) ) : ?>
					<div class="player-details_">
				<?php endif; ?>
				<!-- hockey -->
				<div class="stm-player-position-wrapp heading-font">
					<div class="stm-player-position"><?php echo esc_html( $positions[0]->name ); ?></div>
				</div>
				<?php endif; ?>
				<!--  baseball && hockey end -->
				<div class="sp-player-details heading-font">
					<?php
					foreach ( $data as $label => $value ) :
						if ( '0' !== $value && '' !== $value ) :
							?>
						<div class="single-info">
							<div class="st-label normal_font"><?php echo wp_kses_post( $label ); ?></div>
							<div class="st-value"><?php echo wp_kses_post( $value ); ?></div>
						</div>
											<?php
					endif;
endforeach;
					?>
					<!--  sccr -->
					<?php if ( splash_is_layout( 'sccr' ) ) : ?>
						<div class="single-info">
							<div class="st-label normal_font">
								<div class="player_like">
									<?php $like = ( get_post_meta( $id, 'stm_like', true ) === '' ) ? 0 : get_post_meta( $id, 'stm_like', true ); ?>
									<a href="#" class="like_button" data-id="<?php echo esc_attr( $id ); ?>" onclick="stm_like(jQuery(this)); return false;">
										<i class="fa fa-heart-o"></i> <span><?php echo esc_html( $like ); ?></span>
									</a>
								</div>
							</div>
							<div class="st-value"></div>
						</div>
					<?php endif; ?>
				<!-- sccr end -->
				</div>
				<!-- !sccr && !soccer_two && !hockey -->
				<?php
				if ( ! splash_is_layout( 'rugby' ) && ! splash_is_layout( 'sccr' ) && ! splash_is_layout( 'soccer_two' ) && ! splash_is_layout( 'hockey' ) && count( $socials ) > 0 ) :
					?>
					<ul class="player-socials">
						<?php foreach ( $socials as $social ) : ?>
							<?php
							$soc = get_post_meta( $id, $social, true );
							if ( ! empty( $soc ) ) :
								?>
								<li class="<?php echo esc_attr( $social ); ?>">
									<a href="<?php echo esc_url( $soc ); ?>" target="_blank">
										<i class="fa fa-<?php echo esc_attr( $social ); ?>"></i>
									</a>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<!-- !sccr && !soccer_two && !hockey End -->
			<!-- hockey -->
				<?php if ( splash_is_layout( 'hockey' ) ) : ?>
					</div>
				<?php endif; ?>
			<!-- hockey End-->
			<!-- hockey -->
			<?php if ( splash_is_layout( 'hockey' ) ) : ?>
				<div class="quick_stats_">
					<?php
						$equations = array_keys( sp_get_var_equations( 'sp_statistic' ) );
						$season    = get_theme_mod( 'single_player_season_stats' );
						$league    = get_theme_mod( 'single_player_league_stats' );
					if ( ! empty( $season ) && ! empty( $league ) ) {
						$season_name = get_term_by( 'id', $season, 'sp_season' );
						if ( $season_name ) {
							$season_name = $season_name->name;
						} else {
							$season_name = '';
						}
						$player = new SP_Player( $id );
						if ( null !== $player ) {
							$player_data = $player->data( $league );
							$params      = $player_data[0];
							if ( isset( $player_data[ $season ] ) ) {
								$statistics = $player_data[ $season ];
								if ( null !== $statistics ) {
									?>
									<div class="stm-quick-sts">
										<?php esc_html_e( 'Quick stats', 'splash' ) . ' (' . esc_html( $season_name ) . ')'; ?>
									</div>
									<div class="stm-metric-wrapp">
										<table class="stm-metric">
											<tbody>
												<?php
												if ( null !== $statistics ) :
													$q = 0;
													foreach ( $statistics as $k => $val ) :
														if ( in_array( $k, $equations, true ) ) {
															if ( 0 !== $val && '' !== $val && '-' !== $val && 'name' !== $k && 'team' !== $k ) :
																if ( empty( $params[ $k ] ) ) {
																	continue;
																}
																?>
																<tr class="
																<?php
																if ( 0 === $q % 2 ) {
																	echo 'odd';
																} else {
																	echo 'even';
																}
																?>
																">
																	<td><span class="stm-metric-title"><?php echo esc_html( $params[ $k ] ); ?></span></td>
																	<td><span class="stm-metric-data"><?php echo esc_html( $val ); ?></span></td>
																</tr>
																<?php
																$q++;
															endif;
														}
													endforeach;
												endif;
												?>
											</tbody>
										</table>
									</div>
									<?php
								}
							}
						}
					}
					?>
				</div>
			<?php endif; ?>
		<!-- hockey End-->
		</div>
	</div>
	<!-- bb -->
	<?php if ( splash_is_layout( 'bb' ) ) : ?>
		<div class="stm_player_right_details">
			<?php get_template_part( 'sportspress/player-details-right' ); ?>
		</div>
	<?php endif; ?>
	<!-- bb End-->

