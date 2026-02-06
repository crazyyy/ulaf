<?php
/**
 * Featured Player - Soccer
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.2.5
 * @version   4.6.0
 */

if ( $team_color_heading || $team_color_primary || $team_color_secondary ) {
	$output .= '<style>';
		if ( $team_color_primary ) {
			$output .= '.' . $identifier . ' .widget-player__last-name {';
				$output .= 'color: ' . $team_color_primary . ';';
			$output .= '}';

			$output .= '.' . $identifier . ' .widget-player__number {';
				$output .= 'background-color: ' . $team_color_primary . ';';
			$output .= '}';
		}

		if ( $team_color_secondary ) {
			$output .= '.' . $identifier . ' .progress__bar--success {';
				$output .= 'background-color: ' . $team_color_secondary . ';';
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

// Bars
$shpercent   = isset( $data['shpercent'] ) ? $data['shpercent'] : '';
$passpercent = isset( $data['passpercent'] ) ? $data['passpercent'] : '';

if ( $stat_type == 'stat_extended' ) {

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
}
?>


<!-- Widget: Featured Player -->
<div class="widget-player widget-player--soccer card <?php echo esc_attr( $style_type ); ?> <?php echo esc_attr( $identifier ); ?>">
	<?php if ( $caption ) {
		echo '<header class="card__header"><h4>' . esc_html( $caption ) . '</h4></header>';
	} ?>
	<div class="widget-player__inner">

		<?php if ( $add_link ) : ?>
			<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>
		<?php endif; ?>

		<div class="widget-player__ribbon">
			<div class="fa fa-star"></div>
		</div>

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
									<div class="widget-player__stat-number"><?php echo esc_html( $stat_value ); ?></div>
									<?php if ( isset( $labels[$stat_key] ) ) : ?>
									<h6 class="widget-player__stat-label"><?php echo $labels[ $stat_key ]; ?></h6>
									<?php endif; ?>
								</div>
							<?php }
						endforeach;
					endif;

				endif; ?>

			</div>

			<?php // Progress Bars (statistics)
			if ( is_array( $progress_bars ) ) :
				?>
				<div class="widget-player__content-alt">
					<?php
					foreach ( $data as $stat_key => $stat_value ) :

						if ( in_array( $stat_key, $progress_bars ) ) :
							?>

							<div class="progress-stats">
								<?php
								// label
								if ( isset( $labels[ $stat_key ] ) ) :
									?>
									<div class="progress__label"> <?php echo esc_html( $labels[ $stat_key ] ); ?></div>
									<?php
								endif;
								?>
								<div class="progress">
									<div class="progress__bar progress__bar--success" role="progressbar" aria-valuenow="<?php echo esc_attr( $stat_value ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $stat_value ); ?>%;"></div>
								</div>
								<div class="progress__number"><?php echo esc_attr( $stat_value ); ?></div>
							</div>

							<?php
						endif;
					endforeach;
					?>
				</div>

			<?php endif; ?>

		</div>

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
	<?php endif; ?>

</div>
<!-- Widget: Featured Player / End -->
