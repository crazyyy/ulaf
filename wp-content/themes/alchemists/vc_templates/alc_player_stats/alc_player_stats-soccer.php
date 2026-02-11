<?php
/**
 * Template for displaying ALC: Players Stats - Soccer
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.4.0
 * @version   4.7.0
 */

if ( $background_image ) {
	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__number { background-image: url("' . wp_get_attachment_url( $background_image ) .'"); background-size: cover; background-repeat: no-repeat; background-position: 50% 0; }';
}
if ( $team_color_primary ) {
	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__last-name {';
		$style .= 'color: ' . $team_color_primary . ';';
	$style .= '}';

	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__number {';
		$style .= 'background-color: ' . $team_color_primary . ';';
	$style .= '}';
}

if ( $team_color_secondary ) {
	$style .= '.' . esc_attr( $identifier ) . ' .progress__bar--success {';
		$style .= 'background-color: ' . $team_color_secondary . ';';
	$style .= '}';
}

if ( $team_color_heading ) {
	$style .= '.' . esc_attr( $identifier ) . ' .card__header::before {';
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

	// Player Stats - numbers
	$goals       = isset( $data['goals'] ) ? $data['goals'] : esc_html__( 'n/a', 'alchemists' );
	$shots       = isset( $data['sh'] ) ? $data['sh'] : esc_html__( 'n/a', 'alchemists' );
	$assists     = isset( $data['assists'] ) ? $data['assists'] : esc_html__( 'n/a', 'alchemists' );
	$appearances = isset( $data['appearances'] ) ? $data['appearances'] : esc_html__( 'n/a', 'alchemists' );

	$stats_primary_default_array = array(
		'goals'       => esc_html__( 'Goals', 'alchemists' ),
		'shots'       => esc_html__( 'Shots', 'alchemists' ),
		'assists'     => esc_html__( 'Assists', 'alchemists' ),
		'appearances' => esc_html__( 'Games', 'alchemists' ),
	);

	// Secondary Performances
	if ( $display_detailed_stats_secondary ) {
		// bars
		$shpercent   = isset( $data['shpercent'] ) ? $data['shpercent'] : '';
		$passpercent = isset( $data['passpercent'] ) ? $data['passpercent'] : '';

		// Equation Stats - predefined
		$equation_default_array = array(
			'shpercent'   => esc_html__( 'SHOT ACC', 'alchemists' ),
			'passpercent' => esc_html__( 'PASS ACC', 'alchemists' ),
		);
	}

endif;


// Player Detailed Stats
if ( $display_detailed_stats ) : // if requested
	$goals       = isset( $data['goals'] ) ? $data['goals'] : esc_html__( 'n/a', 'alchemists' );
	$shots       = isset( $data['sh'] ) ? $data['sh'] : esc_html__( 'n/a', 'alchemists' );
	$assists     = isset( $data['assists'] ) ? $data['assists'] : esc_html__( 'n/a', 'alchemists' );
	$appearances = isset( $data['appearances'] ) ? $data['appearances'] : esc_html__( 'n/a', 'alchemists' );
	$yellowcards     = isset( $data['yellowcards'] ) ? $data['yellowcards'] : esc_html__( 'n/a', 'alchemists' );
	$redcards        = isset( $data['redcards'] ) ? $data['redcards'] : esc_html__( 'n/a', 'alchemists' );
	$shots_on_target = isset( $data['sog'] ) ? $data['sog'] : esc_html__( 'n/a', 'alchemists' );
	$pka             = isset( $data['pka'] ) ? $data['pka'] : esc_html__( 'n/a', 'alchemists' );
	$pkg             = isset( $data['pkg'] ) ? $data['pkg'] : esc_html__( 'n/a', 'alchemists' );
	$drb             = isset( $data['drb'] ) ? $data['drb'] : esc_html__( 'n/a', 'alchemists' );
	$fouls           = isset( $data['f'] ) ? $data['f'] : esc_html__( 'n/a', 'alchemists' );
	$off             = isset( $data['off'] ) ? $data['off'] : esc_html__( 'n/a', 'alchemists' );
endif;

// Detailed Stats - predefined
$stats_default_array = array(
	'goals'           => esc_html__( 'Goals', 'alchemists' ),
	'assists'         => esc_html__( 'Assists', 'alchemists' ),
	'shots'           => esc_html__( 'Shots', 'alchemists' ),
	'shots_on_target' => esc_html__( 'Shots on Target', 'alchemists' ),
	'pka'             => esc_html__( 'P.Kick Attempts', 'alchemists' ),
	'pkg'             => esc_html__( 'P.Kick Goals', 'alchemists' ),
	'drb'             => esc_html__( 'Dribbles', 'alchemists' ),
	'fouls'           => esc_html__( 'Fouls', 'alchemists' ),
	'off'             => esc_html__( 'Offsides', 'alchemists' ),
	'appearances'     => esc_html__( 'Games Played', 'alchemists' ),
	'yellowcards'     => esc_html__( 'Yellow Cards', 'alchemists' ),
	'redcards'        => esc_html__( 'Red Cards', 'alchemists' ),
);
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

		<div class="widget-player__ribbon">
			<div class="fa fa-star"></div>
		</div>

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
									$statistic_primary = get_post_field( 'post_name', $stat_primary_item['stat_value']);

									if ( isset( $data[$statistic_primary]) ) :

										// value
										$stat_primary_value = strip_tags( $data[$statistic_primary] );

										// heading
										$stat_primary_heading = $stat_primary_item['stat_heading']; ?>

									<div class="widget-player__stat">
										<div class="widget-player__stat-number"><?php echo esc_html( $stat_primary_value ); ?></div>
										<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_heading ); ?></h6>
									</div>

								<?php endif; ?>

							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>

					<?php foreach( $stats_primary_default_array as $stat_primary_default_key => $stat_primary_default_value ) : ?>
						<div class="widget-player__stat">
							<div class="widget-player__stat-number"><?php echo esc_html( ${"$stat_primary_default_key"} ); ?></div>
							<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_default_value ); ?></h6>
						</div>
					<?php endforeach; ?>

				<?php endif; ?>
			</div>

			<?php if ( $display_detailed_stats_secondary ) : ?>
				<div class="widget-player__content-alt">

					<?php if ( $customize_detailed_stats_secondary ) : ?>

						<?php // Customized stats
						if ( !empty( $values_equation_array ) ) :
							foreach ( $values_equation_array as $stat_item ) :
								if ( !empty ( $stat_item['stat_value']) ) :

									$performance = get_post_field( 'post_name', $stat_item['stat_value'] );

									if ( isset( $data[$performance]) ) :

										// value
										$stat_value = strip_tags( $data[$performance] );

										// heading
										$stat_heading = $stat_item['stat_heading']; ?>

										<div class="progress-stats">
											<div class="progress__label"><?php echo esc_html( $stat_heading ); ?></div>
											<div class="progress">
												<div class="progress__bar progress__bar--success" role="progressbar" aria-valuenow="<?php echo esc_attr( $stat_value ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $stat_value ); ?>%"></div>
											</div>
											<div class="progress__number"><?php echo esc_html( $stat_value ); ?>%</div>
										</div>

									<?php endif;
								endif;
							endforeach;
							wp_reset_postdata();
						endif; ?>

					<?php else : ?>
						<?php // Predefined stats
							foreach ( $equation_default_array as $equation_default_key => $equation_default_value ) : ?>

							<div class="progress-stats">
								<div class="progress__label"><?php echo esc_html( $equation_default_value ); ?></div>
								<div class="progress">
									<div class="progress__bar progress__bar--success" role="progressbar" aria-valuenow="<?php echo esc_attr( ${"$equation_default_key"} ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( ${"$equation_default_key"} ); ?>%"></div>
								</div>
								<div class="progress__number"><?php echo esc_html( ${"$equation_default_key"} ); ?>%</div>
							</div>

						<?php endforeach; ?>
					<?php endif; ?>

				</div>
			<?php endif; ?>

		</div>

	</div>
	<?php endif; ?>


	<?php if ( $display_detailed_stats ) : ?>
	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php if ( $customize_detailed_stats ) : ?>

				<?php // Customized stats
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
										<span class="widget-player__details-value"><?php echo esc_html( $stat_value ); ?></span>
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

	<div class="widget__content-tertiary widget__content--bottom-decor">
		<div class="widget__content-inner"></div>
	</div>
	<?php endif; ?>

</div>
<!-- Widget: Player Stats / End -->
