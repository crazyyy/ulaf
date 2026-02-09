<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     4.0.0
 * @version   4.0.0
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $player_id
 * @var $player_id_on
 * @var $player_id_num
 * @var $customize_player_stats
 * @var $player_stats
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Player_Stats_Grid
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$title = $player_id = $player_id_on = $player_id_num = $customize_player_stats = $player_stats = $el_class = $el_id = $css = $css_animation = '';

$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'widget card card--has-table widget-team-stats';

$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$id = $player_id;
// Check if we're on Single Team page and Team is not selected
if ( is_singular('sp_player') && ( $player_id == 'default' || $player_id_on != 1 ) ) {
	$id = get_the_ID();
}

if ( $player_id_on == 1 && ! empty( $player_id_num ) ) {
	$id = $player_id_num;
}

$player = new SP_Player( $id );
if ( is_null( $player->post ) ) {
	echo '<div class="alert alert-warning">' . sprintf( esc_html__( 'No Player found with Player ID %s', 'alchemists' ), '<strong>' . $id . '</strong>' ). '</div>';
	return;
}

$data = $player->data( 0, false );

// Remove the first row to leave us with the actual data
unset( $data[0] );

$defaults = array(
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );

$player_stat_desc = esc_html__( 'in all time', 'alchemists' );
if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $data[ $current_season ] ) ) {
		$data = $data[ $current_season ];
		$player_stat_desc = esc_html__( 'in current season', 'alchemists' );
	}
} else {
	if ( isset( $data[-1] )) {
		$data = $data[-1];
	}
}

// Player Statistics - Customized
if ( $customize_player_stats ) {

	$player_stats = (array) vc_param_group_parse_atts( $player_stats );
	$player_stats_array = array();
	foreach ( $player_stats as $value ) {
		$custom_stat = $value;
		$custom_stat['stat_heading']    = isset( $value['stat_heading'] ) ? $value['stat_heading'] : '';
		$custom_stat['stat_subheading'] = isset( $value['stat_subheading'] ) ? $value['stat_subheading'] : '';
		$custom_stat['stat_value']      = isset( $value['stat_value'] ) ? $value['stat_value'] : '';

		$player_stats_array[] = $custom_stat;
	}

} else {

	// Player Statistics - Default

	// Get Player Statistics posts
	$statistics = get_posts( array(
		'post_type'      => 'sp_statistic',
		'posts_per_page' => 9999
	));

	// New array based on Player Statistics posts
	$statistic_array = array();
	$statistic_ids = array();
	if ( $statistics ) {
		foreach( $statistics as $statistic ){
			$statistic_array[ $statistic->post_name ] = $statistic->post_title;
			// build Performance IDS array
			$statistic_ids[ $statistic->post_name ] = $statistic->ID;
		}
	}

	if ( alchemists_sp_preset( 'basketball' ) ) {
		$player_stats_array = array(
			array(
				'stat_heading'    => esc_html__( 'Field Goal %', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'fgpercent', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Free throw %', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'ftpercent', null ),
			),
			array(
				'stat_heading'    => esc_html__( '3-pointer %', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'threeppercent', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Efficiency rating', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'eff', null ),
			),
		);
	} elseif ( alchemists_sp_preset( 'soccer' ) ) {
		$player_stats_array = array(
			array(
				'stat_heading'    => esc_html__( 'Win Ratio', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'winratio', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Draw Ratio', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'drawratio', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Loss Ratio', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'lossratio', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Goals per Game', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'gpg', null ),
			),
		);
	} elseif ( alchemists_sp_preset( 'football' ) ) {
		$player_stats_array = array(
			array(
				'stat_heading'    => esc_html__( 'Yards per carry', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'avg', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Yards per reception', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'recavg', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Played %', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'eventsplayedpercent', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Field Goal %', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'fgpercent', null ),
			),
		);
	} elseif ( alchemists_sp_preset( 'esports' ) ) {
		$player_stats_array = array(
			array(
				'stat_heading'    => esc_html__( 'Win Rate', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'winrate', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Kills Participation', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'killsp', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Avg. KDA Ratio', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'avgkdaratio', null ),
			),
			array(
				'stat_heading'    => esc_html__( 'Avg. Gold per Min', 'alchemists' ),
				'stat_subheading' => $player_stat_desc,
				'stat_value'      => sp_array_value( $statistic_ids, 'avggoldpermin', null ),
			),
		);
	}
}


// echo '<pre style="background-color: #fff;">' . var_export( $statistic_ids, true ). '</pre>';
?>

<!-- Widget: Player Stats - Grid -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php if ( $title ) : ?>
		<div class="widget__title card__header">
			<?php echo wpb_widget_title( array( 'title' => $title ) ) ?>
		</div>
	<?php endif; ?>

	<div class="widget__content card__content">
		<ul class="team-stats-box">

			<?php // Player Stats
			if ( ! empty( $player_stats_array ) ) :
				foreach ( $player_stats_array as $stat_item ) :
					if ( ! empty ( $stat_item['stat_value'] ) ) :

						$statistic = get_post_field( 'post_name', $stat_item['stat_value'] );

						if ( isset( $data[ $statistic ] ) ) :

							$statistic_id    = $stat_item['stat_value']; // id
							$stat_value      = $data[ $statistic ]; // value
							$stat_heading    = $stat_item['stat_heading']; // label
							$stat_subheading = $stat_item['stat_subheading']; // sublabel
							?>

							<li class="team-stats__item team-stats__item--clean">
								<div class="team-stats__icon team-stats__icon--circle-outline team-stats__icon--size-sm team-stats__icon--centered">
									<?php alc_sp_performance_icon( $statistic_id, '#fff', false ); ?>
								</div>
								<div class="team-stats__value"><?php echo esc_html( $stat_value ); ?></div>
								<div class="team-stats__label"><?php echo esc_html( $stat_heading ); ?></div>
								<div class="team-stats__sublabel"><?php echo esc_html( $stat_subheading ); ?></div>
							</li>

						<?php
						endif;
					endif;
				endforeach;
				wp_reset_postdata();
			endif; ?>

		</ul>
	</div>
</div>
<!-- Widget: Player Stats - Grid / End -->
