<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     3.0.7
 * @version   4.7.0
 */

$float = is_rtl() ? 'right' : 'left';
?>

<!-- Timeline -->
<div class="game-timeline-wrapper game-timeline-wrapper--vertical">
	<div class="game-timeline">

		<?php foreach ( $timeline as $minutes => $details ) : ?>
			<?php
			$time = sp_array_value( $details, 'time', false );

			if ( false === $time ) continue;

			$icon = sp_array_value( $details, 'icon', '' );
			$side = sp_array_value( $details, 'side', 'home' );

			if ( $time < 0 ) {
				$name = sp_array_value( $details, 'name', esc_html__( 'Team', 'alchemists' ) );
				?>
				<div class="game-timeline__event game-timeline__event--kickoff game-timeline__event--side-<?php echo esc_attr( $side ); ?>" title="<?php esc_attr_e( 'Kick Off', 'alchemists' ); ?>">
					<?php if ( $icon ) : ?>
						<?php if ( $link_teams ) : ?>
							<?php $team = sp_array_value( $details, 'id' ); ?>
							<a href="<?php echo get_post_permalink( $team ); ?>" class="game-timeline__team-logo" title="<?php echo esc_attr( $name ); ?>"><?php echo wp_kses_post( $icon ); ?></a>
						<?php else : ?>
							<span class="game-timeline__team-logo" title="<?php echo esc_attr( $name ); ?>"><?php echo wp_kses_post( $icon ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
					<div class="game-timeline__time game-timeline__time--kickoff game-timeline__time--kickoff-<?php echo esc_attr( $side ); ?>"><?php esc_html_e( 'KO', 'alchemists' ); ?></div>
				</div>
				<?php
			} else {
				$name = sp_array_value( $details, 'name', esc_html__( 'Player', 'alchemists' ) );
				$number = sp_array_value( $details, 'number', '' );

				if ( '' !== $number ) $name = $number . '. ' . $name;

				$offset = floor( $time / ( $event_minutes + 4 ) * 100 );
				if ( $offset - $previous <= 4 ) $offset = $previous + 4;
				$previous = $offset;
				?>
				<div class="game-timeline__event game-timeline__event--side-<?php echo esc_attr( $side ); ?>" style="<?php echo esc_attr( $float ); ?>: <?php echo esc_attr( $offset ); ?>%;">
					<div class="game-timeline__event-info game-timeline__event-info--side-<?php echo esc_attr( $side ); ?>">
						<div class="game-timeline__event-name"><?php echo wp_kses_post( $name ); ?></div>
						<div class="game-timeline__icon game-timeline__icon--<?php echo esc_attr( $side ); ?>">
							<?php echo wp_kses_post( $icon ); ?>
						</div>
					</div>
					<div class="game-timeline__time" title="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $time ) . "'"; ?></div>
				</div>
			<?php } ?>

		<?php endforeach; ?>

		<div class="game-timeline__event game-timeline__event--ft" title="<?php esc_attr_e( 'Full Time', 'alchemists' ); ?>">
			<div class="game-timeline__time"><?php esc_html_e( 'FT', 'alchemists' ); ?></div>
		</div>

	</div>
</div>
<!-- Timeline / End -->
