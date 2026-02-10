<?php
/**
 * Template for displaying ALC: Players Stats - Basketball
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.4.0
 * @version   4.7.0
 */

if ( $background_image ) {
	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__inner { background-image: url("' . wp_get_attachment_url( $background_image ) .'"); background-size: cover; background-repeat: no-repeat; background-position: 50% 0; }';
}

if ( $team_color_primary ) {
	if ( 'style_1' == $style_type ) {
		$style .= '.' . $identifier . ' .widget-player__last-name {';
			$style .= 'color: ' . $team_color_primary . ';';
		$style .= '}';
	}
	$style .= '.template-basketball .' . $identifier . '.widget-player--alt .widget-player__stat-number {';
		$style .= 'color: ' . $team_color_primary . ';';
	$style .= '}';

	$style .= '.' . $identifier . ' .widget-player__footer,';
	$style .= '.' . $identifier . ' .widget-player__footer-txt::before {';
		$style .= 'background-color: ' . $team_color_primary . ';';
	$style .= '}';

	$style .= '.template-basketball .' . $identifier . '.widget-player--alt .widget-player__inner {';
		$style .= 'background-image: linear-gradient(to bottom, ' . $team_color_primary . ', ' . $team_color_secondary . ');';
	$style .= '}';
}
if ( $team_color_heading ) {
	$style .= '.' . $identifier . ' .card__header::before {';
		$style .= 'background-color: ' . $team_color_heading . ';';
	$style .= '}';
}

if ( ! empty( $style ) ) {
	alc_custom_css( $style );
}

// Player Banner Data
if ( $style_type != 'style_hide_banner' ) : // if requested

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
	$ppg = isset( $data['ppg'] ) ? esc_html( $data['ppg'] ) : esc_html__( 'n/a', 'alchemists' );
	$apg = isset( $data['apg'] ) ? esc_html( $data['apg'] ) : esc_html__( 'n/a', 'alchemists' );
	$rpg = isset( $data['rpg'] ) ? esc_html( $data['rpg'] ) : esc_html__( 'n/a', 'alchemists' );

	$stats_primary_default_array = array(
		'ppg' => esc_html__( 'Points', 'alchemists' ),
		'apg' => esc_html__( 'Assists', 'alchemists' ),
		'rpg' => esc_html__( 'Rebs', 'alchemists' ),
	);

endif;


// Player Detailed Stats
if ( $display_detailed_stats ) {
	$ast     = isset( $data['ast'] ) ? esc_html( $data['ast'] ) : esc_html__( 'n/a', 'alchemists' );
	$threepm = isset( $data['threepm'] ) ? esc_html( $data['threepm'] ) : esc_html__( 'n/a', 'alchemists' );
	$blk     = isset( $data['blk'] ) ? esc_html( $data['blk'] ) : esc_html__( 'n/a', 'alchemists' );
	$pf      = isset( $data['pf'] ) ? esc_html( $data['pf'] ) : esc_html__( 'n/a', 'alchemists' );
	$gp      = isset( $data['g'] ) ? esc_html( $data['g'] ) : esc_html__( 'n/a', 'alchemists' );
	$fgm     = isset( $data['fgm'] ) ? esc_html( $data['fgm'] ) : esc_html__( 'n/a', 'alchemists' );
	$def     = isset( $data['def'] ) ? esc_html( $data['def'] ) : esc_html__( 'n/a', 'alchemists' );
	$off     = isset( $data['off'] ) ? esc_html( $data['off'] ) : esc_html__( 'n/a', 'alchemists' );
	$stl     = isset( $data['stl'] ) ? esc_html( $data['stl'] ) : esc_html__( 'n/a', 'alchemists' );

	if ( is_numeric( $def ) && is_numeric( $off ) ) {
		$rebs = ( $def + $off );
	} else {
		$rebs = esc_html__( 'n/a', 'alchemists' );
	}

	// Detailed Stats - predefined
	$stats_default_array = array(
		'fgm'     => esc_html__( 'Field Goals', 'alchemists' ),
		'threepm' => esc_html__( '3 Points', 'alchemists' ),
		'rebs'    => esc_html__( 'Rebounds', 'alchemists' ),
		'ast'     => esc_html__( 'Assists', 'alchemists' ),
		'stl'     => esc_html__( 'Steals', 'alchemists' ),
		'blk'     => esc_html__( 'Blocks', 'alchemists' ),
		'pf'      => esc_html__( 'Fouls', 'alchemists' ),
		'gp'      => esc_html__( 'Games Played', 'alchemists' ),
	);
}

// Secondary Performances
if ( $display_detailed_stats_secondary ) {
	$fgpercent     = isset( $data['fgpercent'] ) ? esc_html( $data['fgpercent'] ) : 0;
	$ftpercent     = isset( $data['ftpercent'] ) ? esc_html( $data['ftpercent'] ) : 0;
	$threeppercent = isset( $data['threeppercent'] ) ? esc_html( $data['threeppercent'] ) : 0;

	// Equation Stats - predefined
	$equation_default_array = array(
		'fgpercent'     => esc_html__( 'Field Goal Accuracy', 'alchemists' ),
		'ftpercent'     => esc_html__( 'Free Throw Accuracy', 'alchemists' ),
		'threeppercent' => esc_html__( '3 Points Accuracy', 'alchemists' ),
	);
}
?>

<!-- Widget: Player Stats -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<?php if ( $title ) { ?>
		<div class="widget__title card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php } ?>

	<?php if ( $style_type != 'style_hide_banner' ) : ?>
	<div class="widget-player__inner">

		<?php if ( $add_link ) : ?>
			<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>
		<?php endif; ?>

		<?php if( !empty( $sp_current_team ) ):
			$player_team_logo = alchemists_get_thumbnail_url( $sp_current_team, '0', 'full' );
			if( !empty($player_team_logo) ): ?>
				<div class="widget-player__team-logo">
					<img src="<?php echo esc_url( $player_team_logo ); ?>" alt="<?php esc_attr_e( 'Team Logo', 'alchemists' ); ?>" />
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<figure class="widget-player__photo">
			<?php echo wp_kses_post( $image_url ); ?>
		</figure>

		<header class="widget-player__header clearfix">
			<?php if ( isset( $player_number )) : ?>
			<div class="widget-player__number"><?php echo esc_html( $player_number ); ?></div>
			<?php endif; ?>

			<h4 class="widget-player__name">
				<?php echo wp_kses_post( $player_name ); ?>
			</h4>
		</header>

		<div class="widget-player__content">
			<div class="widget-player__content-inner">

				<?php if ( $customize_primary_stats ) : ?>
					<?php if ( !empty( $values_primary_array) ) : ?>
						<?php foreach ( $values_primary_array as $stat_primary_item ) : ?>
							<?php if ( !empty( $stat_primary_item ) ) : ?>
								<?php
									$statistic_primary = get_post_field( 'post_name', $stat_primary_item['stat_value'] );

									if ( isset( $data[$statistic_primary]) ) :

										// value
										$stat_primary_value = strip_tags( $data[$statistic_primary] );

										// heading
										$stat_primary_heading = $stat_primary_item['stat_heading'];

										// subheading
										$stat_primary_subheading = $stat_primary_item['stat_subheading']; ?>

									<div class="widget-player__stat">
										<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_heading ); ?></h6>
										<div class="widget-player__stat-number"><?php echo esc_html( $stat_primary_value ); ?></div>
										<div class="widget-player__stat-legend"><?php echo esc_html( $stat_primary_subheading ); ?></div>
									</div>
								<?php endif; ?>

							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>

					<?php foreach( $stats_primary_default_array as $stat_primary_default_key => $stat_primary_default_value ) : ?>
						<div class="widget-player__stat">
							<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_default_value ); ?></h6>
							<div class="widget-player__stat-number"><?php echo esc_html( ${"$stat_primary_default_key"} ); ?></div>
							<div class="widget-player__stat-legend"><?php esc_html_e( 'AVG', 'alchemists' ); ?></div>
						</div>
					<?php endforeach; ?>

				<?php endif; ?>
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
	<?php endif; ?>


	<?php if ( $display_detailed_stats ) : ?>
	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php // Customized stats
			if ( $customize_detailed_stats ) :

				if ( !empty( $values_array ) ) :
					foreach ( $values_array as $stat_item ) :
						if ( !empty ( $stat_item['stat_value']) ) :

							$performance = get_post_field( 'post_name', $stat_item['stat_value'] );

							if ( isset( $data[$performance]) ) :

								// value
								$stat_value = strip_tags( $data[$performance] );

								// heading
								$stat_heading = $stat_item['stat_heading'];

								// subheading
								$stat_subheading = $stat_item['stat_subheading']; ?>

								<div class="widget-player__details__item">
									<div class="widget-player__details-desc-wrapper">
										<span class="widget-player__details-holder">
											<span class="widget-player__details-label"><?php echo esc_html( $stat_heading ); ?></span>
											<span class="widget-player__details-desc"><?php echo esc_html( $stat_subheading ); ?></span>
										</span>
										<span class="widget-player__details-value"><?php echo esc_html( alchemists_format_big_number( $stat_value ) ); ?></span>
									</div>
								</div>

							<?php endif;
						endif;
					endforeach;
					wp_reset_postdata();
				endif; ?>

			<?php else : ?>

				<?php // Predefined stats
				foreach ( $stats_default_array as $stat_default_key => $stat_default_value ) : ?>

				<div class="widget-player__details__item">
					<div class="widget-player__details-desc-wrapper">
						<span class="widget-player__details-holder">
							<span class="widget-player__details-label"><?php echo esc_html( $stat_default_value ) ; ?></span>
							<span class="widget-player__details-desc"><?php echo wp_kses_post( $performance_desc ); ?></span>
						</span>
						<span class="widget-player__details-value"><?php echo esc_html( ${"$stat_default_key"} ); ?></span>
					</div>
				</div>

				<?php endforeach; ?>

			<?php endif; ?>

		</div>
		<!-- Player Details / End -->

	</div>
	<?php endif; ?>

	<?php if ( $display_detailed_stats_secondary ) : ?>
	<div class="widget__content-tertiary widget__content--bottom-decor">
		<div class="widget__content-inner">
			<div class="widget-player__stats row">

				<?php if ( $customize_detailed_stats_secondary ) :

					// Customized stats
					if ( !empty( $values_equation_array ) ) :
						foreach ( $values_equation_array as $stat_item ) :
							if ( !empty ( $stat_item['stat_value']) ) :

								$performance = get_post_field( 'post_name', $stat_item['stat_value'] );

								if ( isset( $data[$performance]) ) :

									// value
									$stat_value = strip_tags( $data[$performance] );

									// heading
									$stat_heading = $stat_item['stat_heading']; ?>

									<div class="col-4">
										<div class="widget-player__stat-item">
											<div class="widget-player__stat-circular circular">
												<div class="circular__bar" data-percent="<?php echo esc_attr( $stat_value ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
													<span class="circular__percents"><?php echo esc_html( number_format( $stat_value, 1 ) ); ?><small>%</small></span>
												</div>
												<span class="circular__label"><?php echo esc_html( $stat_heading ); ?></span>
											</div>
										</div>
									</div>

								<?php endif;
							endif;
						endforeach;
						wp_reset_postdata();
					endif; ?>

				<?php else : ?>

					<?php // Predefined stats
					foreach ( $equation_default_array as $equation_default_key => $equation_default_value ) : ?>

					<div class="col-4">
						<div class="widget-player__stat-item">
							<div class="widget-player__stat-circular circular">
								<div class="circular__bar" data-percent="<?php echo esc_attr( ${"$equation_default_key"} ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
									<span class="circular__percents"><?php echo esc_html( number_format( ${"$equation_default_key"}, 1 ) ); ?><small>%</small></span>
								</div>
								<span class="circular__label"><?php echo esc_html( $equation_default_value ); ?></span>
							</div>
						</div>
					</div>

					<?php endforeach; ?>

				<?php endif; ?>

			</div>
		</div>
	</div>
	<?php endif; ?>

</div>
<!-- Widget: Player Stats / End -->
