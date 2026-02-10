<?php
/**
 * Player Cards Statistics for Single Player Header - Boxed
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @version   4.0.0
 * @version   4.7.0
 */

// Skip if there are no rows in the table
if ( empty( $data ) ) {
	return;
}

unset( $data[0] );

$defaults = array(
	'current_season' => get_option( 'sportspress_season', '' ),
	'show_total'     => get_option( 'sportspress_player_show_total', 'yes' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );

$card_stat_desc = esc_html__( 'in all time', 'alchemists' );

// Display stats for Current Season
if ( ! empty( $current_season ) && ! $show_total ) {
	if ( isset( $data[ $current_season ] ) ) {
		$data = $data[ $current_season ];
		$card_stat_desc = esc_html__( 'in current season', 'alchemists' );
	}
} else {
	if ( isset( $data[-1] )) {
		$data = $data[-1];
	}
}


// Theme Options
$alchemists_data = get_option( 'alchemists_data' );
$cards_stats_on     = isset( $alchemists_data['alchemists__player-title-stat-cards-boxed'] ) ? $alchemists_data['alchemists__player-title-stat-cards-boxed'] : 0;
$cards_stats_custom = isset( $alchemists_data['alchemists__player-title-stat-cards-custom-boxed'] ) ? $alchemists_data['alchemists__player-title-stat-cards-custom-boxed'] : array();
$cards_stats_cols   = isset( $alchemists_data['alchemists__player-title-stat-cards-cols-boxed'] ) ? $alchemists_data['alchemists__player-title-stat-cards-cols-boxed'] : '4cols';

// Define CSS class for Card Stat column
$card_col_classes = array(
	'player-general-stats__item',
	'col-sm-6',
);
if ( '3cols' == $cards_stats_cols ) {
	$card_col_classes[] = 'col-lg-4';
} elseif ( '2cols' == $cards_stats_cols ) {
	$card_col_classes[] = 'col-lg-6';
} else {
	// 4 cols by default
	$card_col_classes[] = 'col-lg-3';
}

$card_col_classes = implode( ' ', $card_col_classes );

$cards_stats_custom_array = array();
if ( $cards_stats_custom ) {
	foreach ( $cards_stats_custom as $cards_stat_key => $cards_stat_value) {
		$cards_stats_custom_array[ get_post_field( 'post_name', $cards_stats_custom[ $cards_stat_key ] ) ] = get_post_field( 'post_title', $cards_stats_custom[ $cards_stat_key ] );
	}
}

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

// echo '<pre>' . var_export($statistic_ids, true). '</pre>';

$cards_stats_defaults = array(
	'basketball' => array(
		'g',
		'rpg',
		'apg',
		'spg',
	),
	'soccer' => array(
		'appearances',
		'winratio',
		'drawratio',
		'lossratio',
	),
	'football' => array(
		'g',
		'avg',
		'tdavg',
		'fgpercent',
	),
	'esports' => array(
		'avgkills',
		'avgdeaths',
		'avgassists',
		'avgdmg',
	)
);

$cards_stats = array();
if ( alchemists_sp_preset( 'soccer' ) ) {
	$cards_stats = alchemists_sp_filter_array( $statistic_array, $cards_stats_defaults['soccer'] );
} elseif ( alchemists_sp_preset( 'football' ) ) {
	$cards_stats = alchemists_sp_filter_array( $statistic_array, $cards_stats_defaults['football'] );
} elseif ( alchemists_sp_preset( 'basketball' ) ) {
	$cards_stats = alchemists_sp_filter_array( $statistic_array, $cards_stats_defaults['basketball'] );
} elseif ( alchemists_sp_preset( 'esports' ) ) {
	$cards_stats = alchemists_sp_filter_array( $statistic_array, $cards_stats_defaults['esports'] );
}

// Check if Custom Stats is enabled
if ( $cards_stats_on ) {
	$cards_stats = $cards_stats_custom_array;
}

// CSS classes
$card_classes = array(
	'player-general-stats__icon',
	'alc-icon',
	'alc-icon--circle',
	'alc-icon--sm',
	'alc-icon--outline',
	'alc-icon--outline-md'
);

$card_border_color = '';
if ( $team_color_secondary ) {
	$card_border_color = 'border-color: ' . $team_color_secondary . ';';
}

$card_classes = implode( ' ', $card_classes );
?>

<!-- Single Player - General Stats -->
<div class="player-general-stats row">

	<?php
	foreach ( $cards_stats as $cards_stat_key => $cards_stat_value ) :

		$title        = $cards_stat_value;
		$value        = alchemists_format_big_number( $data[ $cards_stat_key ], 1, true );
		$statistic_id = sp_array_value( $statistic_ids, $cards_stat_key, null );
		?>

		<!-- Card Stat: <?php echo esc_html( $title ); ?> -->
		<div class="<?php echo esc_attr( $card_col_classes ); ?>">
			<div class="player-general-stats__card card">
				<div class="player-general-stats__card-content card__content">
					<div class="<?php echo esc_attr( $card_classes ); ?>" <?php echo ! empty( $card_border_color ) ? 'style="' . $card_border_color . '"' : ''; ?>>
						<?php alc_sp_performance_icon( $statistic_id, '#fff', false ); ?>
					</div>
					<div class="player-general-stats__body">
						<h5 class="player-general-stats__value"><?php echo esc_html( $value ); ?></h5>
						<div class="player-general-stats__meta">
							<h6 class="player-general-stats__label"><?php echo esc_html( $title ); ?></h6>
							<div class="player-general-stats__sublabel"><?php echo esc_html( $card_stat_desc ); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Card Stat: <?php echo esc_html( $title ); ?> / End -->

	<?php endforeach; ?>

</div>
<!-- Single Player - General Stats / End -->
