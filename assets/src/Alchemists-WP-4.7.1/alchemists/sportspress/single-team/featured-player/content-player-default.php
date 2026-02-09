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
$ppg = isset( $data['ppg'] ) ? esc_html( $data['ppg'] ) : esc_html__( 'n/a', 'alchemists' );
$apg = isset( $data['apg'] ) ? esc_html( $data['apg'] ) : esc_html__( 'n/a', 'alchemists' );
$rpg = isset( $data['rpg'] ) ? esc_html( $data['rpg'] ) : esc_html__( 'n/a', 'alchemists' );

$stats_primary_default_array = array(
	'ppg' => esc_html__( 'Points', 'alchemists' ),
	'apg' => esc_html__( 'Assists', 'alchemists' ),
	'rpg' => esc_html__( 'Reb', 'alchemists' ),
);


// Percentages
$fgpercent     = isset( $data['fgpercent'] ) ? esc_html( $data['fgpercent'] ) : 0;
$ftpercent     = isset( $data['ftpercent'] ) ? esc_html( $data['ftpercent'] ) : 0;
$threeppercent = isset( $data['threeppercent'] ) ? esc_html( $data['threeppercent'] ) : 0;

// Equation Stats - predefined
$equation_default_array = array(
	'fgpercent'     => esc_html__( 'Shot Accuracy', 'alchemists' ),
	'ftpercent'     => esc_html__( 'Free Throw Accuracy', 'alchemists' ),
	'threeppercent' => esc_html__( '3 Points Accuracy', 'alchemists' ),
);


// Advanced Stats
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
	'blk'     => esc_html__( 'Blocks', 'alchemists' ),
	'pf'      => esc_html__( 'Fouls', 'alchemists' ),
	'gp'      => esc_html__( 'Games Played', 'alchemists' ),
	'stl'     => esc_html__( 'Steals', 'alchemists' ),
);

?>
<!-- Widget: Featured Player - Alternative Extended -->
<div class="widget card widget--sidebar widget-player widget-player--alt">
	<div class="widget__title card__header">
		<h4><?php esc_html_e( 'Featured Player', 'alchemists' ); ?></h4>
	</div>
	<div class="widget__content card__content widget-player__inner">

		<a href="<?php echo esc_url( $player_url ); ?>" class="widget-player__link-layer"></a>

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
						<h6 class="widget-player__stat-label"><?php echo esc_html( $stat_primary_default_value ); ?></h6>
						<div class="widget-player__stat-number"><?php echo esc_html( ${"$stat_primary_default_key"} ); ?></div>
						<div class="widget-player__stat-legend"><?php esc_html_e( 'AVG', 'alchemists' ); ?></div>
					</div>
				<?php endforeach; ?>
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
	<div class="widget__content-secondary">

		<!-- Player Details -->
		<div class="widget-player__details">

			<?php // Predefined stats
			foreach ( $stats_default_array as $stat_default_key => $stat_default_value ) : ?>

				<div class="widget-player__details__item">
					<div class="widget-player__details-desc-wrapper">
						<span class="widget-player__details-holder">
							<span class="widget-player__details-label"><?php echo esc_html( $stat_default_value ); ?></span>
							<span class="widget-player__details-desc"><?php echo wp_kses_post( $performance_desc ); ?></span>
						</span>
						<span class="widget-player__details-value"><?php echo esc_html( ${"$stat_default_key"} ); ?></span>
					</div>
				</div>

			<?php endforeach; ?>

		</div>
		<!-- Player Details / End -->

	</div>

	<div class="widget__content-tertiary widget__content--bottom-decor">
		<div class="widget__content-inner">
			<div class="widget-player__stats row">

				<?php // Predefined stats
				foreach ( $equation_default_array as $equation_default_key => $equation_default_value ) : ?>

					<div class="col-4">
						<div class="widget-player__stat-item">
							<div class="widget-player__stat-circular circular">
								<div class="circular__bar" data-percent="<?php echo esc_html( ${"$equation_default_key"} ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
									<span class="circular__percents"><?php echo esc_html( number_format( ${"$equation_default_key"}, 1 ) ); ?><small>%</small></span>
								</div>
								<span class="circular__label"><?php echo wp_kses_post( $equation_default_value ); ?></span>
							</div>
						</div>
					</div>

				<?php endforeach;?>

			</div>
		</div>
	</div>
</div>
<!-- Widget: Featured Player - Alternative Extended / End -->
