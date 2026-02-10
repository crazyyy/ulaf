<?php
/**
 * Featured Player - Basketball and Other
 *
 * @author    Dan Fisher
 * @package   Alchemists Advanced Posts
 * @since     2.3.0
 * @version   2.3.0
 */

if ( $team_color_heading || $team_color_primary || $team_color_secondary ) {
	$output .= '<style>';
		if ( $team_color_primary ) {
			if ( ! $style_type ) {
				$output .= '.' . $identifier . ' .widget-player__last-name {';
					$output .= 'color: ' . $team_color_primary . ';';
				$output .= '}';
			}
			$output .= '.template-basketball .' . $identifier . '.widget-player--alt .widget-player__stat-number {';
				$output .= 'color: ' . $team_color_primary . ';';
			$output .= '}';

			$output .= '.' . $identifier . ' .widget-player__footer,';
			$output .= '.' . $identifier . ' .widget-player__footer-txt::before {';
				$output .= 'background-color: ' . $team_color_primary . ';';
			$output .= '}';

			$output .= '.template-basketball .' . $identifier . '.widget-player--alt .widget-player__inner {';
				$output .= 'background-image: linear-gradient(to bottom, ' . $team_color_primary . ', ' . $team_color_secondary . ');';
			$output .= '}';
		}
		if ( $team_color_heading ) {
			$output .= '.' . $identifier . ' .card__header::before {';
				$output .= 'background-color: ' . $team_color_heading . ';';
			$output .= '}';
		}
	$output .= '</style>';
}

// Player Image (Alt)
$player_image_head  = get_field('heading_player_photo', $id);
$player_image_size  = 'alchemists_thumbnail-player-sm';
if( $player_image_head ) {
	$image_url = wp_get_attachment_image( $player_image_head, $player_image_size );
} else {
	$image_url = '<img src="' . get_theme_file_uri( '/assets/images/player-single-placeholder-189x198.png' ) . '" loading="lazy" alt="" />';
}

// Player Team Logo
$sp_current_teams = get_post_meta($id,'sp_current_team');
$sp_current_team = '';
if( !empty($sp_current_teams[0]) ) {
	$sp_current_team = $sp_current_teams[0];
}

// Player Name
$player_name = $player->post->post_title;
$player_url  = get_the_permalink( $id );

// Player Number
$player_number = get_post_meta( $id, 'sp_number', true );

// Player Position(s)
$positions = wp_get_post_terms( $id, 'sp_position');
$position = false;
if( $positions ) {
	$position = $positions[0]->name;
}

// Player Stats
// Explode into array
if ( null !== $performance && ! is_array( $performance ) ) {
	$performance = explode( ',', $performance );
}

$performance_excerpts = array();
$performance_posts = get_posts( array(
	'posts_per_page' => -1,
	'post_type' => 'sp_performance'
) );
foreach ( $performance_posts as $post ):
	$performance_excerpts[ $post->post_name ] = $post->post_excerpt;
endforeach;
?>


<!-- Widget: Featured Player -->
<div class="widget-player card <?php echo esc_attr( $style_type ); ?> <?php echo esc_attr( $identifier ); ?>">
	<?php if ( $caption ) {
		echo '<header class="card__header"><h4>' . esc_html( $caption ) . '</h4></header>';
	} ?>
	<div class="widget-player__inner">

		<?php if ( $add_link ) : ?>
			<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>
		<?php endif; ?>

		<?php if( !empty( $sp_current_team ) ):
			$player_team_logo = alchemists_get_thumbnail_url( $sp_current_team, '0', 'sportspress-fit-medium' );
			if( !empty($player_team_logo) ): ?>
				<div class="widget-player__team-logo">
					<img src="<?php echo esc_url( $player_team_logo ); ?>" alt="<?php esc_attr_e( 'Team Logo', 'alc-advanced-posts' ); ?>" />
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<figure class="widget-player__photo">
			<?php echo wp_kses_post( $image_url ); ?>
		</figure>

		<header class="widget-player__header clearfix">
			<?php if ( isset( $player_number ) ) : ?>
			<div class="widget-player__number"><?php echo esc_html( $player_number ); ?></div>
			<?php endif; ?>

			<h4 class="widget-player__name">
				<?php echo wp_kses_post( $player_name ); ?>
			</h4>
		</header>

		<div class="widget-player__content">
			<div class="widget-player__content-inner">

				<?php // Display Main Stats
				if ( is_array( $data ) ) :

					if ( is_array( $columns ) ) :
						foreach ( $data as $stat_key => $stat_value ) :
							if ( in_array( $stat_key, $columns )) { ?>
								<div class="widget-player__stat">
									<?php if ( isset( $labels[$stat_key] ) ) : ?>
									<h6 class="widget-player__stat-label"><?php echo $labels[ $stat_key ]; ?></h6>
									<?php endif; ?>
									<div class="widget-player__stat-number"><?php echo esc_html( $stat_value ); ?></div>
									<?php if ( ! in_array( $stat_key, array( 'g', 'gs' ) ) ) : ?>
									<div class="widget-player__stat-legend"><?php esc_html_e( 'AVG', 'alc-advanced-posts' ); ?></div>
									<?php endif; ?>
								</div>
							<?php }
						endforeach;
					endif;

				endif; ?>

			</div>
		</div>

		<?php if (!empty( $position )) : ?>
		<footer class="widget-player__footer">
			<span class="widget-player__footer-txt">
				<?php echo esc_html( $position ); ?>
			</span>
		</footer>
		<?php endif; ?>

	</div>

	<?php if ( $stat_type == 'stat_extended' ) : ?>
	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php foreach ( $data as $performance_key => $performance_value ) : ?>
				<?php if ( in_array( $performance_key, $performance ) ) : ?>
					<div class="widget-player__details__item">
						<div class="widget-player__details-desc-wrapper">
							<span class="widget-player__details-holder">
								<?php if ( isset( $performance_excerpts[$performance_key] )) : ?>
								<span class="widget-player__details-label"><?php echo esc_html( $performance_excerpts[$performance_key] ); ?></span>
								<?php endif; ?>
								<span class="widget-player__details-desc"><?php echo wp_kses_post( $performance_desc ); ?></span>
							</span>
							<span class="widget-player__details-value"><?php echo esc_html( $performance_value ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

		</div>
		<!-- Player Details / End -->

	</div>

	<?php // Progress Bars (statistics)
	if ( is_array( $progress_bars ) ) :
		?>
		<div class="widget__content-tertiary widget__content--bottom-decor">
			<div class="widget__content-inner">
				<div class="widget-player__stats row">

					<?php
					foreach ( $data as $stat_key => $stat_value ) :

						if ( in_array( $stat_key, $progress_bars ) ) :
							?>

							<div class="col-4">
								<div class="widget-player__stat-item">
									<div class="widget-player__stat-circular circular">
										<div class="circular__bar" data-percent="<?php echo esc_attr( $stat_value ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
											<span class="circular__percents"><?php echo esc_html( number_format( $stat_value, 1 ) ); ?><small>%</small></span>
										</div>
										<?php
											if ( isset( $performance_excerpts[ $stat_key ] ) ) :
												?>
												<span class="circular__label">
													<?php
													// Stat Description
													echo esc_html( $performance_excerpts[ $stat_key ] )
													?>
												</span>
												<?php
											endif;
										?>
									</div>
								</div>
							</div>

							<?php
						endif;
					endforeach;
					?>

				</div>
			</div>
		</div>
		<?php endif; ?>

	<?php endif; ?>

</div>
<!-- Widget: Featured Player / End -->
