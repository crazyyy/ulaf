<?php
/**
 * The template for displaying ALC: Team Points History (American Football)
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.4.5
 */

// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'points-history-' );

$grid_line_color  = isset( $alchemists_data['color-2'] ) ? $alchemists_data['color-2'] : '#3c3b5b';
$ticks_color      = isset( $alchemists_data['color-gray'] ) ? $alchemists_data['color-gray'] : '#9e9caa';

// Tooltips Background color
$tooltip_bg_color = hex2rgba( $grid_line_color, 0.8);
?>

<?php if ( $title ) { ?>
	<div class="widget__title card__header">
		<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
	</div>
<?php } ?>
<div class="card__content">
	<canvas id="<?php echo esc_attr( $identifier ); ?>" class="points-history-chart" height="95"></canvas>

	<script type="text/javascript">
		(function($){
			$(function() {
				var data = {
					type: 'line',
					data: {
						labels: [<?php print_r( $dates_by_event); ?>],
						datasets: [{
							label: '<?php esc_html_e( 'POINTS', 'alchemists' ); ?>',
							fill: false,
							lineTension: 0,
							borderWidth: 4,
							backgroundColor: "<?php echo esc_js( $chart_line_color ); ?>",
							borderColor: "<?php echo esc_js( $chart_line_color ); ?>",
							borderCapStyle: 'butt',
							borderDashOffset: 0.0,
							borderJoinStyle: 'bevel',
							pointRadius: 5,
							pointBorderWidth: 5,
							pointBackgroundColor: "#fff",
							pointHoverRadius: 5,
							pointHoverBackgroundColor: "#fff",
							pointHoverBorderColor: "<?php echo esc_js( $chart_point_color ); ?>",
							pointHoverBorderWidth: 5,
							pointHitRadius: 10,
							data: [<?php echo esc_js( $results_by_event ); ?>],
							spanGaps: false,
						}]
					},
					options: {
						legend: {
							display: false,
							labels: {
								boxWidth: 8,
								fontSize: 9,
								fontColor: '#31404b',
								fontFamily: 'Montserrat, sans-serif',
								padding: 20,
							}
						},
						tooltips: {
							backgroundColor: "<?php echo esc_js( $tooltip_bg_color ); ?>",
							titleFontSize: 0,
							titleSpacing: 0,
							titleMarginBottom: 0,
							bodyFontFamily: 'Montserrat, sans-serif',
							bodyFontSize: 9,
							bodySpacing: 0,
							cornerRadius: 2,
							xPadding: 10,
							displayColors: false,
							callbacks: {
								title: function() {} // removes title inside label
							}
						},
						scales: {
							xAxes: [{
								gridLines: {
									color: "<?php echo esc_js( $grid_line_color ); ?>",
								},
								ticks: {
									fontColor: '<?php echo esc_js( $ticks_color ); ?>',
									fontFamily: 'Montserrat, sans-serif',
									fontSize: 10,
								},
							}],
							yAxes: [{
								gridLines: {
									color: "<?php echo esc_js( $grid_line_color ); ?>",
								},
								ticks: {
									beginAtZero: true,
									fontColor: '<?php echo esc_js( $ticks_color ); ?>',
									fontFamily: 'Montserrat, sans-serif',
									fontSize: 10,
									padding: 20
								}
							}]
						}
					},
				};

				var ctx = $('#<?php echo esc_js( $identifier ); ?>');
				var gamesHistory = new Chart(ctx, data);

				// document.getElementById('gamesPoinstsLegendFootball').innerHTML = gamesHistory.generateLegend();
			});
		})(jQuery);

	</script>
</div>
