<?php
/**
 * Template for displaying ALC: Players Stats - eSports
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.7.0
 */


// Circular Bar - track color
$circular_track_color     = isset( $alchemists_data['alchemists__circular-bars-track-color'] ) && ! empty( $alchemists_data['alchemists__circular-bars-track-color'] ) ? $alchemists_data['alchemists__circular-bars-track-color'] : '#4b3b60';

// Player Banner Data
if ( $style_type != 'style_hide_banner' ) {

	// Player Name
	$player_name = $player->post->post_title;
	$player_url  = get_the_permalink( $id );

}


// Player Detailed Stats
if ( $display_detailed_stats ) {
	$kills    = isset( $data['kills'] ) ? esc_html( $data['kills'] ) : esc_html__( 'n/a', 'alchemists' );
	$deaths   = isset( $data['deaths'] ) ? esc_html( $data['deaths'] ) : esc_html__( 'n/a', 'alchemists' );
	$assists  = isset( $data['assists'] ) ? esc_html( $data['assists'] ) : esc_html__( 'n/a', 'alchemists' );
	$gold     = isset( $data['gold'] ) ? esc_html( $data['gold'] ) : esc_html__( 'n/a', 'alchemists' );
	$dmg      = isset( $data['dmg'] ) ? esc_html( $data['dmg'] ) : esc_html__( 'n/a', 'alchemists' );
	$cs       = isset( $data['cs'] ) ? esc_html( $data['cs'] ) : esc_html__( 'n/a', 'alchemists' );

	// Detailed Stats - predefined
	$stats_default_array = array(
		'kills'      => esc_html__( 'Total Kills', 'alchemists' ),
		'deaths'     => esc_html__( 'Total Deaths', 'alchemists' ),
		'assists'    => esc_html__( 'Total Assists', 'alchemists' ),
		'gold'       => esc_html__( 'Total Gold', 'alchemists' ),
		'dmg'        => esc_html__( 'Total Damage', 'alchemists' ),
		'cs'         => esc_html__( 'Creep Score', 'alchemists' ),
	);
}

// Secondary Performances
if ( $display_detailed_stats_secondary ) {
	$avgkills   = isset( $data['avgkills'] ) ? esc_html( $data['avgkills'] ) : 0;
	$avgdeaths  = isset( $data['avgdeaths'] ) ? esc_html( $data['avgdeaths'] ) : 0;
	$avgassists = isset( $data['avgassists'] ) ? esc_html( $data['avgassists'] ) : 0;
	$winrate    = isset( $data['winrate'] ) ? esc_html( $data['winrate'] ) : 0;

	// Equation Stats - predefined
	$tertiary_default_array = array(
		'avgkills'   => esc_html__( 'Avg. Kills', 'alchemists' ),
		'avgdeaths'  => esc_html__( 'Avg. Deaths', 'alchemists' ),
		'avgassists' => esc_html__( 'Avg. Assists', 'alchemists' ),
	);

	$tertiary_featured_default_array = array(
		'winrate'   => esc_html__( 'Win Rate', 'alchemists' ),
	);
}

// Metrics
$player_metrics = array(
	'fname',
	'lname',
);
$player_metrics_data = (array)get_post_meta( $id, 'sp_metrics', true );

if ( $background_image ) {
	$style .= '.' . esc_attr( $identifier ) . ' .widget-player__overlay { background-image: url("' . wp_get_attachment_url( $background_image ) .'"); }';
}
if ( ! empty( $style ) ) {
	alc_custom_css( $style );
}
?>

<!-- Widget: Player Stats -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<?php if ( $title ) : ?>
	<div class="widget__title card__header">
		<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
	</div>
	<?php endif; ?>

	<?php if ( $style_type != 'style_hide_banner' ) : ?>
	<div class="widget__content card__content">

		<?php if ( $add_link ) : ?>
		<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>
		<?php endif; ?>

		<figure class="widget-player__photo">
			<?php echo wp_kses_post( $image_url ); ?>
		</figure>

		<header class="widget-player__header clearfix">
			<h4 class="widget-player__name">
				<?php echo wp_kses_post( $player_name ); ?>
			</h4>
			<span class="widget-player__info">
				<?php
				foreach ( $player_metrics as $player_metric_key ) :
					if ( isset( $player_metrics_data[ $player_metric_key ] ) ) :
						echo esc_html( $player_metrics_data[ $player_metric_key ] ) . ' ';
					endif;
				endforeach;
				?>
			</span>
		</header>
		<div class="widget-player__overlay effect-duotone effect-duotone--base"></div>

	</div>
	<?php endif; ?>


	<?php if ( $display_detailed_stats ) : ?>
	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php // Customized stats
			if ( $customize_detailed_stats ) :

				if ( ! empty( $values_array ) ) :
					foreach ( $values_array as $stat_item ) :
						if ( ! empty ( $stat_item['stat_value'] ) ) :

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
						<span class="widget-player__details-value"><?php echo esc_html( alchemists_format_big_number ( ${"$stat_default_key"} ) ); ?></span>
					</div>
				</div>

				<?php endforeach; ?>

			<?php endif; ?>

		</div>
		<!-- Player Details / End -->

	</div>
	<?php endif; ?>

	<?php if ( $display_detailed_stats_secondary ) : ?>
	<div class="widget__content-tertiary">
		<div class="widget__content-inner">
			<div class="widget-player__stats row align-items-center">

				<?php if ( $customize_detailed_stats_secondary ) :

					// Customized stats
					if ( ! empty( $values_equation_array ) ) :
						foreach ( $values_equation_array as $stat_item ) :
							if ( ! empty ( $stat_item['stat_value']) ) :

								$performance = get_post_field( 'post_name', $stat_item['stat_value'] );

								if ( isset( $data[ $performance ] ) ) :

									// value
									$stat_value = strip_tags( $data[ $performance ] );

									// heading
									$stat_heading = $stat_item['stat_heading']; ?>

									<div class="col">
										<div class="widget-player__stat-item">
											<div class="widget-player__stat--value"><?php echo esc_html( $stat_value ); ?></div>
											<div class="widget-player__stat--label"><?php echo esc_html( $stat_heading ); ?></div>
										</div>
									</div>

								<?php
								endif;
							endif;
						endforeach;
						wp_reset_postdata();
					endif; ?>

				<?php else : ?>

					<?php // Predefined stats
					foreach ( $tertiary_default_array as $equation_default_key => $equation_default_value ) : ?>
					<div class="col">
						<div class="widget-player__stat-item">
							<div class="widget-player__stat--value"><?php echo esc_html( ${"$equation_default_key"} ); ?></div>
							<div class="widget-player__stat--label"><?php echo esc_html( $equation_default_value ); ?></div>
							<div class="widget-player__stat--desc"><?php echo wp_kses_post( $performance_desc ); ?></div>
						</div>
					</div>
					<?php endforeach; ?>

					<?php // Predefined stats
					foreach ( $tertiary_featured_default_array as $equation_default_key => $equation_default_value ) : ?>
					<div class="col">
						<div class="widget-player__stat-circular circular circular--size-80">
							<div class="circular__bar" data-percent="<?php echo esc_attr( ${"$equation_default_key"} ); ?>" data-track-color="<?php echo esc_attr( $circular_track_color ); ?>">
								<span class="circular__percents"><?php echo esc_html( ${"$equation_default_key"} ); ?><small>%</small><span class="circular__label"><?php echo esc_html( $equation_default_value ); ?></span></span>
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
