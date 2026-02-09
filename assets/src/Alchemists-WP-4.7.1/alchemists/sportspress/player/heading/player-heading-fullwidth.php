<?php
/**
 * The template for displaying Single Player Header - Full Width
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */

// Custom Page Heading Options
$page_heading_customize      = get_field( 'player_page_heading_customize' );
$page_heading_style          = array();
$page_heading_styles_output  = array();

if ( $page_heading_customize ) {
	// Page Heading Background Image
	$page_heading_custom_background_img = get_field('player_page_heading_custom_background_img');

	if ( $page_heading_custom_background_img ) {
		// if background image selected display it
		$page_heading_style[] = 'background-image: url(' . $page_heading_custom_background_img . ');';
	} else {
		// if not, remove the default one
		$page_heading_style[] = 'background-image: none;';
	}

	// Page Heading Background Color
	$page_heading_custom_background_color = get_field('player_page_heading_custom_background_color');
	if ( $page_heading_custom_background_color ) {
		$page_heading_style[] = 'background-color: ' . $page_heading_custom_background_color . ';';
	}

	// Overlay
	$page_heading_add_overlay_on = get_field('player_page_heading_add_overlay_on');
	// hide pseudoelement if overlay disabled
	if ( empty( $page_heading_add_overlay_on ) ) {
		$page_heading_overlay = 'player-heading--no-bg';
	}

	$page_heading_custom_overlay_color   = get_field( 'player_page_heading_custom_overlay_color') ? get_field('player_page_heading_custom_overlay_color') : 'transparent';
	$page_heading_custom_overlay_opacity = get_field( 'player_page_heading_custom_overlay_opacity' );
	$page_heading_remove_overlay_pattern = get_field( 'player_page_heading_remove_overlay_pattern' );

	if ( $page_heading_add_overlay_on ) {
		echo '<style>';
			echo '.player-heading::after {';
				echo 'background-color: ' . $page_heading_custom_overlay_color . ';';
				echo 'opacity: ' . $page_heading_custom_overlay_opacity / 100 . ';';
				if ( $page_heading_remove_overlay_pattern ) {
					echo 'background-image: none;';
				}
			echo '}';
		echo '</style>';
	}
}

// combine all custom inline properties into one string
if ( $page_heading_style ) {
	$page_heading_styles_output[] = 'style="' . implode( ' ', $page_heading_style ). '"';
}
?>

<div class="player-heading <?php echo esc_attr( $page_heading_overlay ); ?>" <?php echo implode( ' ', $page_heading_styles_output ); ?>>
	<div class="container">

		<?php
		if ( ! empty( $current_team_id ) && $team_logo ) :
			$player_team_logo = alchemists_get_thumbnail_url( $current_team_id, '0', 'full' );
			if ( ! empty( $player_team_logo ) ) :
			?>
				<div class="player-info__team-logo">
					<img src="<?php echo esc_url( $player_team_logo ); ?>" alt=""/>
				</div>
			<?php
			endif;
		endif;
		?>

		<div class="player-info__title player-info__title--mobile">

			<?php if ( $show_number ) : ?>
				<div class="player-info__number"><?php echo esc_html( $player->number ); ?></div>
			<?php endif; ?>

			<?php if ( $show_name ) : ?>
			<h1 class="player-info__name">
				<?php echo wp_kses_post( $player->post->post_title ); ?>
			</h1>
			<?php endif; ?>

		</div>

		<div class="player-info">

			<!-- Player Photo -->
			<div class="player-info__item player-info__item--photo">
				<figure class="player-info__photo">
					<?php echo wp_kses_post( $player_image ); ?>
				</figure>
			</div>
			<!-- Player Photo / End -->

			<!-- Player Details -->
			<div class="player-info__item player-info__item--details player-info__item--details-<?php echo esc_attr( $player_info_layout_class ); ?>">

				<div class="player-info__title player-info__title--desktop">

					<?php if ( $show_number ) : ?>
						<div class="player-info__number"><?php echo esc_html( $player->number ); ?></div>
					<?php endif; ?>

					<?php if ( $show_name ) : ?>
					<h1 class="player-info__name">
						<?php echo wp_kses_post( $player->post->post_title ); ?>
					</h1>
					<?php endif; ?>

				</div>

				<div class="player-info-details">

					<?php foreach ( $player_metrics as $player_metric_key => $player_metric_value ) : ?>
						<?php if ( ! empty( $player_metrics_data[ $player_metric_key ] ) ) : ?>
							<div class="player-info-details__item player-info-details__item--<?php echo esc_attr( $player_metric_key ); ?>">
								<h6 class="player-info-details__title"><?php echo esc_html( $player_metric_value ); ?></h6>
								<div class="player-info-details__value"><?php echo esc_html( $player_metrics_data[ $player_metric_key ] ); ?></div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if ( $show_age ) : ?>
						<div class="player-info-details__item player-info-details__item--age">
							<h6 class="player-info-details__title"><?php echo esc_html( $sp_text_age ); ?></h6>
							<div class="player-info-details__value"><?php echo esc_html( alchemists_get_age( get_the_date( 'm-d-Y', $player_id ) ) ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( $show_player_birthday ) : ?>
						<div class="player-info-details__item player-info-details__item--birthday">
							<h6 class="player-info-details__title"><?php echo esc_html( $sp_text_birthday ); ?></h6>
							<div class="player-info-details__value"><?php echo esc_html( get_the_date( get_option( 'date_format'), $player_id ) ); ?></div>
						</div>
					<?php endif; ?>

					<?php
					if ( $show_current_teams ):
						if ( $current_teams ):
							echo '<div class="player-info-details__item player-info-details__item--current-team">';
								echo '<h6 class="player-info-details__title">' . $sp_text_team_current . '</h6>';
								$teams = array();
								foreach ( $current_teams as $team ):
									$team_name = sp_team_short_name( $team );
									if ( $link_teams ) {
										$team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
									}
									$teams[] = $team_name;
								endforeach;
								$team_names_string = implode( ', ', $teams );
								echo '<div class="player-info-details__value">' . $team_names_string . '</div>';
							echo '</div>';
						endif;
					endif;

					if ( $show_past_teams ):

						$past_teams = $player->past_teams();
						if ( $past_teams ):
							echo '<div class="player-info-details__item player-info-details__item--past-team">';
								echo '<h6 class="player-info-details__title">' . $sp_text_teams_past . '</h6>';
								$teams = array();
								foreach ( $past_teams as $team ):
									$team_name = sp_team_short_name( $team );
									if ( $link_teams ) {
										$team_name = '<a href="' . get_post_permalink( $team ) . '">' . $team_name . '</a>';
									}
									$teams[] = $team_name;
								endforeach;
								$team_names_string = implode( ', ', $teams );
								echo '<div class="player-info-details__value">' . $team_names_string . '</div>';
							echo '</div>';
						endif;
					endif;

					if ( $show_leagues ):
						echo '<div class="player-info-details__item player-info-details__item--leagues">';
						echo '<h6 class="player-info-details__title">' . esc_html__( 'Competitions', 'alchemists' ) . '</h6>';
						$leagues = $player->leagues();
						if ( $leagues && ! is_wp_error( $leagues ) ):
							$terms = array();
							foreach ( $leagues as $league ):
								$terms[] = $league->name;
							endforeach;
							$terms_leagues_string = implode( ', ', $terms );
							echo '<div class="player-info-details__value">' . $terms_leagues_string . '</div>';
						endif;
						echo '</div>';
					endif;

					if ( $show_seasons ):
						echo '<div class="player-info-details__item player-info-details__item--seasons">';
						echo '<h6 class="player-info-details__title">' . esc_html__( 'Seasons', 'alchemists' ) . '</h6>';
						$seasons = $player->seasons();
						if ( $seasons && ! is_wp_error( $seasons ) ):
							$terms = array();
							foreach ( $seasons as $season ):
								$terms[] = $season->name;
							endforeach;
							$terms_seasons_string = implode( ', ', $terms );
							echo '<div class="player-info-details__value">' . $terms_seasons_string . '</div>';
						endif;
						echo '</div>';
					endif;

					if ( $show_nationality ):
						echo '<div class="player-info-details__item player-info-details__item--nationality">';
						echo '<h6 class="player-info-details__title">' . $sp_text_nationality . '</h6>';

						$nationalities = $player->nationalities();
						if ( $nationalities && is_array( $nationalities ) ) {
							$values = array();
							foreach ( $nationalities as $nationality ):
								$country_name = sp_array_value( $countries, $nationality, null );
								$values[] = $country_name ? ( $show_nationality_flags ? '<img src="' . plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/images/flags/' . strtolower( $nationality ) . '.png" class="player-info-details__flag" alt="' . $nationality . '"> ' : '' ) . $country_name : '&mdash;';
							endforeach;
							$country_names_string = implode( ', ', $values );
							echo '<div class="player-info-details__value">' . $country_names_string . '</div>';
						} else {
							echo '<div class="player-info-details__value">' . esc_html__( 'n/a', 'alchemists' ) . '</div>';
						}

						echo '</div>';
					endif;

					if ( $show_positions ):
						echo '<div class="player-info-details__item player-info-details__item--position">';
						echo '<h6 class="player-info-details__title">' . $sp_text_position . '</h6>';
						$positions = $player->positions();
						if ( $positions && is_array( $positions ) ) {
							$position_names = array();
							foreach ( $positions as $position ):
								$position_names[] = $position->name;
							endforeach;
							$position_names_string = implode( ', ', $position_names );
							echo '<div class="player-info-details__value">' . $position_names_string . '</div>';
						} else {
							echo '<div class="player-info-details__value">' . esc_html__( 'n/a', 'alchemists' ) . '</div>';
						}
						echo '</div>';
					endif;
					?>

					<?php
					// Social Networks
					if ( have_rows( 'player_social_networks' ) ) :
						?>
						<div class="player-info-details__item player-info-details__item--social-links">
							<ul class="team-roster__player-social social-links social-links--circle-filled social-links--circle-filled-sm">

								<?php while ( have_rows( 'player_social_networks' ) ) : the_row(); ?>
								<li class="social-links__item">
									<a href="<?php echo esc_url( the_sub_field( 'player_social_networks__link' ) ); ?>" class="social-links__link social-link-url" target="_blank"></a>
								</li>
								<?php endwhile; ?>
							</ul>
						</div>
						<?php
					endif;
					?>

				</div>


				<?php
				// Average Player Statistics
				sp_get_template( 'player-statistics-avg.php', array(
					'data'                 => $player->data(0),
					'team_color_primary'   => $team_color_primary,
					'team_color_secondary' => $team_color_secondary,
				) );
				?>


			</div>
			<!-- Player Details / End -->

			<?php if ( $player_header_advanced_stats || is_null( $player_header_advanced_stats ) ) :
				// Player Stats
				if ( alchemists_sp_preset( 'soccer' ) || alchemists_sp_preset( 'football' ) ) {
					// Soccer: display progress bars
					sp_get_template( 'player-statistics-bars.php', array(
						'data' => $player->data(0),
						'team_color_primary'   => $team_color_primary,
						'team_color_secondary' => $team_color_secondary,
					) );
				} else {
					// Basketball: display radar
					sp_get_template( 'player-statistics-radar.php', array(
						'data' => $player->data(0),
						'team_color_primary'   => $team_color_primary,
					) );
				}
				// Player Stats / End
			endif; ?>


		</div>
	</div>
</div>
