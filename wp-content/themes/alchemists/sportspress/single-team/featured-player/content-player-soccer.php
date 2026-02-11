<?php
/**
 * The template for displaying Feature Player on Team Roster page
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

// Player Stats
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


// Percentages
$shpercent   = isset( $data['shpercent'] ) ? $data['shpercent'] : '';
$passpercent = isset( $data['passpercent'] ) ? $data['passpercent'] : '';

// Equation Stats - predefined
$equation_default_array = array(
	'shpercent'   => esc_html__( 'SHOT ACC', 'alchemists' ),
	'passpercent' => esc_html__( 'PASS ACC', 'alchemists' ),
);



// Advanced Stats
$yellowcards     = isset( $data['yellowcards'] ) ? $data['yellowcards'] : esc_html__( 'n/a', 'alchemists' );
$redcards        = isset( $data['redcards'] ) ? $data['redcards'] : esc_html__( 'n/a', 'alchemists' );
$shots_on_target = isset( $data['sog'] ) ? $data['sog'] : esc_html__( 'n/a', 'alchemists' );
$pka             = isset( $data['pka'] ) ? $data['pka'] : esc_html__( 'n/a', 'alchemists' );
$pkg             = isset( $data['pkg'] ) ? $data['pkg'] : esc_html__( 'n/a', 'alchemists' );
$drb             = isset( $data['drb'] ) ? $data['drb'] : esc_html__( 'n/a', 'alchemists' );
$fouls           = isset( $data['f'] ) ? $data['f'] : esc_html__( 'n/a', 'alchemists' );
$off             = isset( $data['off'] ) ? $data['off'] : esc_html__( 'n/a', 'alchemists' );

// Detailed Stats - predefined
$stats_default_array = array(
	'yellowcards'     => esc_html__( 'Yellow Cards', 'alchemists' ),
	'redcards'        => esc_html__( 'Red Cards', 'alchemists' ),
	'shots_on_target' => esc_html__( 'Shots on Target', 'alchemists' ),
	'pka'             => esc_html__( 'P.Kick Attempts', 'alchemists' ),
	'pkg'             => esc_html__( 'P.Kick Goals', 'alchemists' ),
	'drb'             => esc_html__( 'Dribbles', 'alchemists' ),
	'fouls'           => esc_html__( 'Fouls', 'alchemists' ),
	'off'             => esc_html__( 'Offsides', 'alchemists' ),
);

?>
<!-- Widget: Featured Player - Alternative Extended -->
<div class="widget card widget-player widget-player--soccer">
	<div class="widget__title card__header">
		<h4><?php esc_html_e( 'Featured Player', 'alchemists' ); ?></h4>
	</div>
	<div class="widget-player__inner">

		<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>

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
				<?php echo wp_kses_post( $title ); ?>
			</h4>
		</header>

		<div class="widget-player__content">

			<div class="widget-player__content-inner">
				<?php foreach( $stats_primary_default_array as $stat_primary_default_key => $stat_primary_default_value ) : ?>
					<div class="widget-player__stat">
						<div class="widget-player__stat-number"><?php echo esc_html( ${"$stat_primary_default_key"} ); ?></div>
						<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_default_value ); ?></h6>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="widget-player__content-alt">

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

			</div>


		</div>
	</div>

	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php // Predefined stats
			foreach ( $stats_default_array as $stat_default_key => $stat_default_value ) : ?>

				<div class="widget-player__details__item">
					<div class="widget-player__details-desc-wrapper">
						<span class="widget-player__details-holder">
							<span class="widget-player__details-label"><?php echo esc_html( $stat_default_value ); ?></span>
							<span class="widget-player__details-desc"><?php wp_kses_post( $performance_desc ); ?></span>
						</span>
						<span class="widget-player__details-value"><?php echo esc_html( ${"$stat_default_key"} ); ?></span>
					</div>
				</div>

			<?php endforeach; ?>

		</div>
		<!-- Player Details / End -->

	</div>
</div>
<!-- Widget: Featured Player - Alternative Extended / End -->
