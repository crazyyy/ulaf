<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $player_lists_id
 * @var $player_lists_id_on
 * @var $player_lists_id_num
 * @var $values
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Team_Leaders
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

$title = $player_lists_id = $player_lists_id_on = $player_lists_id_num = $values = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget card card--has-table widget-leaders';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$values = (array) vc_param_group_parse_atts( $values );
$values_array = array();
foreach ($values as $data) {
	$new_stat = $data;
	$new_stat['stat_heading'] = isset( $data['stat_heading'] ) ? $data['stat_heading'] : '';

	$new_stat['stat_heading_total'] = isset( $data['stat_heading_total'] ) ? $data['stat_heading_total'] : esc_html__( 'T', 'alchemists' );
	$new_stat['stat_heading_games'] = isset( $data['stat_heading_games'] ) ? $data['stat_heading_games'] : esc_html__( 'GP', 'alchemists' );
	$new_stat['stat_heading_avg'] = isset( $data['stat_heading_avg'] ) ? $data['stat_heading_avg'] : esc_html__( 'AVG', 'alchemists' );

	$new_stat['stat_value'] = isset( $data['stat_value'] ) ? $data['stat_value'] : '';
	$new_stat['stat_avg'] = isset( $data['stat_avg'] ) ? $data['stat_avg'] : '';
	$new_stat['stat_number'] = isset( $data['stat_number'] ) ? $data['stat_number'] : 1;
	$new_stat['stat_orderby'] = isset( $data['stat_orderby'] ) ? $data['stat_orderby'] : 'total';
	$new_stat['stat_order'] = isset( $data['stat_order'] ) ? $data['stat_order'] : 'DESC';
	$new_stat['stat_bar_reverse'] = isset( $data['stat_bar_reverse'] ) ? $data['stat_bar_reverse'] : 0;

	$values_array[] = $new_stat;
}

if ( $player_lists_id_on == 1 && ! empty( $player_lists_id_num ) ) {
	$player_lists_id = $player_lists_id_num;
}

$list = new SP_Player_List( $player_lists_id );
if ( is_null( $list->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Player List found with Player List ID %s', 'alchemists' ), '<strong>' . $id . '</strong>' ). '</div>';
	return;
}
$data = $list->data();

// Remove the first row to leave us with the actual data
unset( $data[0] );

// FIX: Rebounds sorting issue
if ( alchemists_sp_preset( 'basketball' ) ) {
	foreach ( $data as $player_key => $player_value ) {
		if ( isset( $player_value['reb'] ) && isset( $player_value['off'] ) && isset( $player_value['def'] ) ) {
			$data[ $player_key ]['reb'] = $player_value['off'] + $player_value['def'];
		}
	}
}

// Get Team Colors
$team_id              = get_post_meta( $player_lists_id, 'sp_team', true );
$team_color_primary   = get_field( 'team_color_primary', $team_id );
$team_color_heading   = get_field( 'team_color_heading', $team_id );
if ( $team_color_primary ) {
	$color_primary = $team_color_primary;
} else {
	$color_primary      = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}

$team_header_border = '';
if ( $team_color_heading && alchemists_sp_preset( 'football' ) ) {
	$team_header_border = 'style="border-left-color: ' . $team_color_heading . ';"';
}
?>

<!-- Widget: Team Leaders -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">
	<?php if ( $title ) { ?>
		<div class="widget__title card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php } ?>
	<div class="widget__content card__content">

	<div class="table-responsive">
		<table class="table team-leader">
			<?php if ( ! empty( $values_array ) ): ?>
				<?php
					// added here as a temporary fix
					// should be 'eventsplayed' by default but it doesn't work for Basketball
					$eventsplayed = ! alchemists_sp_preset( 'basketball' ) ? 'eventsplayed' : 'eventsattended';
					$counter = 0;

					foreach ( $values_array as $stat_item ) :
						if ( isset( $stat_item['stat_value']) && ! empty( $stat_item['stat_value'] ) ) :

						$counter++;

						$bar_reverse   = $stat_item['stat_bar_reverse'];

						$performance = get_post_field( 'post_name', $stat_item['stat_value'] );
						$stat_avg = get_post_field( 'post_name', $stat_item['stat_avg'] );

						// set sorting option
						$orderby = $stat_item['stat_orderby'] == 'average' ? $stat_avg : $performance;
						$order   = $stat_item['stat_order'];

						$priorities[ $counter ] = $list;
						$priorities[ $counter ]->priorities = array(
							array(
								'key'   => $orderby,
								'order' => $order,
							),
							array(
								'key'   => $eventsplayed,
								'order' => 'DESC',
							)
						);

						$data_new[ $counter ] = $data;

						uasort( $data_new[ $counter ], array( $priorities[ $counter ], 'sort' ) );

						// display array of players
						$data_new[ $counter ] = array_slice( $data_new[ $counter ], 0, $stat_item['stat_number'], true );
						$players_array = $data_new[ $counter ];

						// slice a top player with highest stats
						$player_top = array_slice( $data_new[ $counter ], 0, 1, true );
						$player_top = current( $player_top );
						$player_top_value = isset( $player_top[ $stat_avg ] ) ? $player_top[ $stat_avg ] : '';
						?>

						<!-- Leader: <?php echo esc_html( $stat_item['stat_heading'] ); ?> -->

						<thead>
							<tr>
								<th class="team-leader__type" <?php echo wp_kses_post( $team_header_border ); ?>><?php echo esc_html( $stat_item['stat_heading'] ); ?></th>
								<th class="team-leader__total"><?php echo esc_html( $stat_item['stat_heading_total'] ); ?></th>
								<th class="team-leader__gp"><?php echo esc_html( $stat_item['stat_heading_games'] ); ?></th>
								<th class="team-leader__avg"><?php echo esc_html( $stat_item['stat_heading_avg'] ); ?></th>
							</tr>
						</thead>
						<tbody>

							<?php
							foreach ( $players_array as $player_id => $player ) :

								// Player Photo
								if ( has_post_thumbnail( $player_id ) ) {
									$player_image = get_the_post_thumbnail( $player_id, 'alchemists_player-xxs' );
								} else {
									$player_image = '<img src="' . get_theme_file_uri( '/assets/images/player-placeholder-40x40.jpg' ) . '" srcset="' . get_theme_file_uri( '/assets/images/player-placeholder-40x40@2x.jpg' ) . ' 2x" alt="">';
								}

								// Player Position
								$positions = wp_get_post_terms( $player_id, 'sp_position' );
								$player_position = false;
								if ( $positions ) {
									$player_position = $positions[0]->name;
								}

								// Player Name
								$title = get_the_title( $player_id );

								// Player Link
								$player_url = get_the_permalink( $player_id );

								// Player Name
								$player_name = $player['name'];

								// Player Circular Bar
								if ( $player_top_value > 0 ) {
									$circular_percent = $player[ $stat_avg ] / $player_top_value * 100;
									if ( $bar_reverse ) {
										$circular_percent = 100 - $circular_percent;
									}
								} else {
									$circular_percent = 0;
								}

								?>
								<tr>
									<td class="team-leader__player">
										<div class="team-leader__player-info">
											<figure class="team-leader__player-img">
												<a href="<?php echo esc_url( $player_url ); ?>">
													<?php echo wp_kses_post( $player_image ); ?>
												</a>
											</figure>
											<div class="team-leader__player-inner">
												<h5 class="team-leader__player-name">
													<a href="<?php echo esc_url( $player_url ); ?>"><?php echo wp_kses_post( $player_name ); ?></a>
												</h5>
												<?php if ( $player_position ) : ?>
												<span class="team-leader__player-position"><?php echo esc_html( $player_position ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</td>
									<td class="team-leader__total">
										<?php
										if ( isset( $player[ $performance ] ) ) {
											if ( alchemists_sp_preset( 'basketball' ) ) {
												if ( $player[ $performance ] == 'reb' ) {
													echo esc_html( $player['off'] + $player['def'] );
												} else {
													echo esc_html( $player[ $performance ] );
												}
											} else {
												echo esc_html( alchemists_format_big_number( $player[ $performance ], 1, false ) );
											}
										}
										?>
									</td>
									<td class="team-leader__gp"><?php echo esc_html( $player[ $eventsplayed ] ); ?></td>
									<td class="team-leader__avg">
										<div class="circular">
											<div class="circular__bar" data-percent="<?php echo esc_attr( $circular_percent ); ?>" data-bar-color="<?php echo esc_attr( $color_primary ); ?>">
												<span class="circular__percents">
													<?php
													if ( isset( $player[ $stat_avg ] ) ) {
														echo esc_html( alchemists_format_big_number( $player[ $stat_avg ], 1, false ) );
													}
													?>
												</span>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>

						</tbody>

						<!-- Leader: <?php echo esc_html( $stat_item['stat_heading'] ); ?> / End -->

						<?php endif;
					endforeach;
				endif; ?>

			</table>
		</div>

	</div>
</div>
<!-- Widget: Team Leaders / End -->
