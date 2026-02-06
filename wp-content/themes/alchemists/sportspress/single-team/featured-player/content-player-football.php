<?php
/**
 * The template for displaying Feature Player on Team Roster page
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

// Sport: Default (Basketball)

// Player Stats
$g      = isset( $data['g'] ) ? $data['g'] : esc_html__( 'n/a', 'alchemists' );
$avg    = isset( $data['avg'] ) ? $data['avg'] : esc_html__( 'n/a', 'alchemists' );
$recavg = isset( $data['recavg'] ) ? $data['recavg'] : esc_html__( 'n/a', 'alchemists' );

$stats_primary_default_array = array(
	'g'      => esc_html__( 'Games', 'alchemists' ),
	'avg'    => esc_html__( 'Avg', 'alchemists' ),
	'recavg' => esc_html__( 'Rec Avg', 'alchemists' ),
);

$bar_color = '';
$progress_classes = array(
	'progress',
	'progress--battery',
);

if ( $team_color_secondary ) {
	$progress_classes[] = 'progress--battery-custom';
	$bar_color = 'color: ' . $team_color_secondary . ';';
}

// Percentages
$cmppercent   = isset( $data['cmppercent'] ) ? $data['cmppercent'] : '';
$tdpercent    = isset( $data['tdpercent'] ) ? $data['tdpercent'] : '';

// Equation Stats - predefined
$equation_default_array = array(
	'cmppercent' => esc_html__( 'CMP%', 'alchemists' ),
	'tdpercent'  => esc_html__( 'TD%', 'alchemists' ),
);


// Advanced Stats
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
<!-- Widget: Featured Player - Alternative Extended -->
<div class="widget card widget--sidebar widget-player widget-player--football">
	<div class="widget__title card__header">
		<h4><?php esc_html_e( 'Featured Player', 'alchemists' ); ?></h4>
	</div>
	<div class="widget-player__inner">

		<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>

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
						<div class="progress__label">
							<?php echo esc_html( $equation_default_value ); ?>
							<div class="progress__number"><?php echo esc_html( ${"$equation_default_key"} ); ?>%</div>
						</div>
						<div class="<?php echo esc_attr( implode( ' ', $progress_classes ) ); ?>">
							<div class="progress__bar progress__bar--secondary" role="progressbar" aria-valuenow="<?php echo esc_attr( ${"$equation_default_key"} ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr( ${"$equation_default_key"} ); ?>%; <?php echo ! empty( $bar_color ) ? $bar_color : ''; ?>"></div>
						</div>
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
