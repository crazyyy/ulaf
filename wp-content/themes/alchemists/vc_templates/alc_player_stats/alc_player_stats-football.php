<?php
/**
 * Template for displaying ALC: Players Stats - American Football
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.4.0
 * @version   4.7.0
 */

if ( $background_image ) {
	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__inner::before { background-image: url("' . wp_get_attachment_url( $background_image ) .'"); background-size: cover; background-repeat: no-repeat; background-position: 50% 0; }';
}

if ( $team_color_primary ) {
	$style .= '.' . $identifier . ' .widget-player__inner::before {';
		$style .= 'background-color: ' . $team_color_primary . ';';
	$style .= '}';
}
if ( $team_color_secondary ) {
	$style .= '.' . $identifier . ' .widget-player__last-name {';
		$style .= 'color: ' . $team_color_secondary . ';';
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

$bar_color = '';
$progress_classes = array(
	'progress',
	'progress--battery',
);

if ( $team_color_secondary ) {
	$progress_classes[] = 'progress--battery-custom';
	$bar_color = 'color: ' . $team_color_secondary . ';';
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
	$g      = isset( $data['g'] ) ? $data['g'] : esc_html__( 'n/a', 'alchemists' );
	$avg    = isset( $data['avg'] ) ? $data['avg'] : esc_html__( 'n/a', 'alchemists' );
	$recavg = isset( $data['recavg'] ) ? $data['recavg'] : esc_html__( 'n/a', 'alchemists' );

	$stats_primary_default_array = array(
		'g'      => esc_html__( 'Games', 'alchemists' ),
		'avg'    => esc_html__( 'Avg', 'alchemists' ),
		'recavg' => esc_html__( 'Rec Avg', 'alchemists' ),
	);

	// Secondary Performances
	if ( $display_detailed_stats_secondary ) {
		// bars
		$cmppercent   = isset( $data['cmppercent'] ) ? $data['cmppercent'] : '';
		$tdpercent    = isset( $data['tdpercent'] ) ? $data['tdpercent'] : '';

		// Equation Stats - predefined
		$equation_default_array = array(
			'cmppercent' => esc_html__( 'CMP %', 'alchemists' ),
			'tdpercent'  => esc_html__( 'TD%', 'alchemists' ),
		);
	}

endif;


// Player Detailed Stats
if ( $display_detailed_stats ) : // if requested
	$comp   = isset( $data['comp'] ) ? $data['comp'] : esc_html__( 'n/a', 'alchemists' );
	$att    = isset( $data['att'] ) ? $data['att'] : esc_html__( 'n/a', 'alchemists' );
	$yds    = isset( $data['yds'] ) ? $data['yds'] : esc_html__( 'n/a', 'alchemists' );
	$rec    = isset( $data['rec'] ) ? $data['rec'] : esc_html__( 'n/a', 'alchemists' );
	$recyds = isset( $data['recyds'] ) ? $data['recyds'] : esc_html__( 'n/a', 'alchemists' );
	$td     = isset( $data['td'] ) ? $data['td'] : esc_html__( 'n/a', 'alchemists' );
	$int    = isset( $data['int'] ) ? $data['int'] : esc_html__( 'n/a', 'alchemists' );
	$lng    = isset( $data['lng'] ) ? $data['lng'] : esc_html__( 'n/a', 'alchemists' );
	$fum    = isset( $data['fum'] ) ? $data['fum'] : esc_html__( 'n/a', 'alchemists' );
	$lost   = isset( $data['lost'] ) ? $data['lost'] : esc_html__( 'n/a', 'alchemists' );
endif;

// Detailed Stats - predefined
$stats_default_array = array(
	'comp'   => esc_html__( 'Completions', 'alchemists' ),
	'att'    => esc_html__( 'Attempts', 'alchemists' ),
	'yds'    => esc_html__( 'Rushing yards', 'alchemists' ),
	'rec'    => esc_html__( 'Total receptions', 'alchemists' ),
	'recyds' => esc_html__( 'Receiving yards', 'alchemists' ),
	'td'     => esc_html__( 'Touchdowns', 'alchemists' ),
	'int'    => esc_html__( 'Interceptions thrown', 'alchemists' ),
	'lng'    => esc_html__( 'Longest', 'alchemists' ),
	'fum'    => esc_html__( 'Total fumbles', 'alchemists' ),
	'lost'   => esc_html__( 'Fumbles lost', 'alchemists' ),
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
			<i class="fa fa-star"></i> <?php esc_html_e( 'Featured Player', 'alchemists' ); ?>
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
											<div class="progress__label">
												<?php echo esc_html( $stat_heading ); ?>
												<div class="progress__number"><?php echo esc_html( $stat_value ); ?>%</div>
											</div>
											<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
												<div class="progress__bar progress__bar--secondary" role="progressbar" aria-valuenow="<?php echo esc_attr( $stat_value ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( $stat_value ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?>"></div>
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

							<div class="progress-stats">
								<div class="progress__label">
									<?php echo esc_html( $equation_default_value ); ?>
									<div class="progress__number"><?php echo esc_html( ${"$equation_default_key"} ); ?>%</div>
								</div>
								<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
									<div class="progress__bar progress__bar--secondary" role="progressbar" aria-valuenow="<?php echo esc_attr( ${"$equation_default_key"} ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( ${"$equation_default_key"} ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?>"></div>
								</div>
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

							if ( isset( $data[ $performance ] ) ) :

								// value
								$stat_value = strip_tags( $data[ $performance ] );

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
	<?php endif; ?>

</div>
<!-- Widget: Player Stats / End -->
