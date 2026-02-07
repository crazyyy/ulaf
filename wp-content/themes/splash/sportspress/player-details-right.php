<?php
/**
 * Player Details
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     2.0
 */
$positions =  wp_get_post_terms( get_the_ID(), 'sp_position' );
if(!splash_is_af() && splash_is_layout('esport')):
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	if ( get_option( 'sportspress_player_show_details', 'yes' ) === 'no' ) return;

	if ( ! isset( $id ) )
		$id = get_the_ID();

	$sp_current_teams = get_post_meta($id,'sp_current_team');
	$sp_current_team = '';
	if(!empty($sp_current_teams[0])) {
		$sp_current_team = $sp_current_teams[0];
	} ?>
	<div class="stm-player-details-right">
		<?php if(!empty($sp_current_team)):
			$player_team_logo = splash_get_thumbnail_url($sp_current_team,'0', 'full');
			if(!empty($player_team_logo)): ?>
				<div class="stm-player-team-logo">
					<img src="<?php echo esc_url($player_team_logo); ?>" />
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<?php
		$single_player_stats = get_theme_mod('single_player_stats', '264,265,266,267');
		$single_player_season_stats = get_theme_mod('single_player_season_stats', 11);
		$single_player_league_stats = get_theme_mod('single_player_league_stats', 8);

		/*SIngle player different stats*/
		$single_player_stats_meta = get_post_meta($id, 'single_player_stats', true);
		if(!empty($single_player_stats_meta)) {
			$new_array = array();
			foreach($single_player_stats_meta as $meta_id => $meta_on) {
				$new_array[] = $meta_id;
			}
			$single_player_stats = implode(',',$new_array);
		}

		$player_season_stats = get_post_meta($id,'single_player_season_stats',true);
		if(!empty($player_season_stats) and $player_season_stats !== 'none') {
			$single_player_season_stats = $player_season_stats;
		}

		$player_league_stats = get_post_meta($id,'single_player_league_stats',true);
		if(!empty($player_league_stats) and $player_league_stats !== 'none') {
			$single_player_league_stats = $player_league_stats;
		}

		$player = new SP_Player( $id );
		$player_stats = $player->data($single_player_league_stats);
		unset( $player_stats[0] );

		if(!empty($single_player_stats) and !empty($single_player_season_stats)):
			$single_player_stats = explode(',', $single_player_stats);
			?>
			<div class="stm-player-stats">
				<h6><?php esc_html_e('Season', 'splash'); ?>: <?php echo esc_attr(get_cat_name($single_player_season_stats)); ?></h6>
				<?php foreach($single_player_stats as $single_player_stat): ?>
					<?php
					$field = get_post($single_player_stat);
					$single_player_stat = $field->post_name;
					?>

					<?php if(!empty($player_stats) and !empty($player_stats[$single_player_season_stats]) and !empty($player_stats[$single_player_season_stats][$single_player_stat])): ?>
						<div class="stm-player-stat clearfix">
							<div class="stm-player-stat-label heading-font"><?php echo esc_attr($single_player_stat); ?></div>
							<div class="stm-player-stat-value stm-player-stat-<?php echo esc_attr($single_player_stat); ?>">
								<?php echo esc_attr($player_stats[$single_player_season_stats][$single_player_stat]); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>