<?php
/**
 * Event Statistics
 *
 * @author 		ThemeBoy
 * @package 	SportsPress_Match_Stats
 * @version     1.9
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( get_option( 'sportspress_event_show_statistics', 'yes' ) === 'no' ) return;

if ( ! isset( $id ) )
	$id = get_the_ID();

$event = new SP_Event( $id );

// Return if no teams
$teams = get_post_meta( $id, 'sp_team', false );
if ( empty( $teams ) )
	return;

$team1 = null;
$team2 = null;

if ( count( $teams ) > 1) {
	$team1 = $teams[0];
	$team2 = $teams[1];
}

$team1_color_primary   = get_field( 'team_color_primary', $team1 );
$team2_color_primary   = get_field( 'team_color_primary', $team2 );

// 1st Team Color
$color_team_1_progress_bar_output = '';
if ( $team1_color_primary ) {
	$color_team_1_progress_bar_output = 'background-color:' . $team1_color_primary;
}

// 2nd Team Color
$color_team_2_progress_bar_output = '';
if ( $team2_color_primary ) {
	$color_team_2_progress_bar_output = 'background-color:' . $team2_color_primary;
}

// Return if no results
$status = $event->status();
if ( 'results' !== $status )
	return;

// Get performance
$performance = $event->performance();

// The first row should be column labels
$labels = apply_filters( 'sportspress_match_stats_labels', $performance[0] );

// Remove position column label
unset( $labels['position'] );

// Create statistics template
$template = array_fill_keys( array_flip( $labels ), 0 );

// Remove the first row to leave us with the actual data
unset( $performance[0] );

// Remove empty teams from performance
$performance = array_filter( $performance );

// Initialize statistics array
$statistics = array();

// Loop through performance
foreach ( $performance as $team => $players ) {

	// Continue if not a team
	if ( ! $team ) continue;

	// Get totals row
	$totals = sp_array_value( $players, 0, array() );

	// Add to statistics
	$statistics[ $team ] = $template;

	foreach ( $labels as $key => $label ) {

		if ( array_key_exists( $key, $totals ) && $totals[ $key ] !== '' ) {

			// Get value from totals row
			$statistics[ $team ][ $key ] = $totals[ $key ];

		} else {

			// Loop through players
			foreach ( $players as $values ) {
				if ( array_key_exists( $key, $template ) ) {
					$statistics[ $team ][ $key ] += (float) sp_array_value( $values, $key, 0 );
				}
			}
		}
	}
}

?>

<div class="card alc-event-stats">
	<div class="card__header">
		<h4><?php esc_html_e('Match Stats', 'alchemists'); ?></h4>
	</div>
	<div class="card__content">
		<div class="sp-template sp-template-event-statistics">
			<?php
			$home = array_shift( $statistics );
			$away = array_shift( $statistics );

			$i = 0;

			foreach ( $labels as $key => $label ):
				if ( ! isset( $home[ $key ] ) || ! isset( $away[ $key ] ) )
					continue;

				if ( ! is_numeric( $home[ $key ] ) || ! is_numeric( $away[ $key ] ) )
					continue;

				$first = empty( $home[ $key ] ) ? 0 : $home[ $key ];
				$last = empty( $away[ $key ] ) ? 0 : $away[ $key ];

				$total = $first + $last;
				if ( $total == 0 ):
					$ratio = 0.5;
				else:
					$ratio = $first / $total;
				endif;
				$percentage = round( $ratio * 100 );
				?>
				<strong class="sp-statistic-label"><?php echo wp_kses_post( $label ); ?></strong>
				<table class="sp-event-statistics sp-data-table">
					<tbody>
						<tr>
							<td class="sp-statistic-value"><?php echo esc_html( $first ); ?></td>
							<td class="sp-statistic-ratio">
								<div class="sp-statistic-bar" title="<?php echo esc_attr( 100 - $percentage ); ?>%" style="<?php echo esc_attr( $color_team_2_progress_bar_output ); ?>">
									<div class="sp-statistic-bar-fill sp-smoothbar" title="<?php echo esc_attr( $percentage ); ?>%" data-sp-percentage="<?php echo esc_attr( $percentage ); ?>" style="width: <?php echo esc_attr( $percentage ); ?>%; <?php echo esc_attr( $color_team_1_progress_bar_output ); ?>"></div>
								</div>
							</td>
							<td class="sp-statistic-value"><?php echo esc_html( $last ); ?></td>
						</tr>
					</tbody>
				</table>
				<?php $i++; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
