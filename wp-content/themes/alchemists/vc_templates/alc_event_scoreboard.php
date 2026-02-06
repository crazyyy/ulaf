<?php
/**
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.3.3
 *
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $event
 * @var $event_id_on
 * @var $event_id
 * @var $event_last
 * @var $team_id
 * @var $team_id_on
 * @var $team_id_num
 * @var $display_details
 * @var $display_percentage
 * @var $link
 * @var $color_team_1
 * @var $color_team_2
 * @var $el_class
 * @var $el_id
 * @var $css
 * @var $css_animation
 * Shortcode class
 * @var $this WPBakeryShortCode_Alc_Event_Scoreboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Theme Options
$alchemists_data = get_option( 'alchemists_data' );

// Progress Bars
$event_progress_bars_stats_enable = isset( $alchemists_data['alchemists__player-sp-event-progress-bars'] ) ? $alchemists_data['alchemists__player-sp-event-progress-bars'] : 0;
$event_progress_bars_stats_custom = isset( $alchemists_data['alchemists__player-sp-event-progress-bars-custom'] ) ? $alchemists_data['alchemists__player-sp-event-progress-bars-custom'] : array();

// Circular Bars
$event_circular_bars_stats_enable = isset( $alchemists_data['alchemists__player-sp-event-circular-bars'] ) ? $alchemists_data['alchemists__player-sp-event-circular-bars'] : 0;
$event_circular_bars_stats_custom = isset( $alchemists_data['alchemists__player-sp-event-circular-bars-custom'] ) ? $alchemists_data['alchemists__player-sp-event-circular-bars-custom'] : array();
if ( alchemists_sp_preset( 'football' ) || alchemists_sp_preset( 'esports' ) ) {
	$event_circular_bars_stats_format_default = 'number';
} else {
	$event_circular_bars_stats_format_default = 'percentage';
}
$event_circular_bars_stats_format = ( isset( $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] ) && ! empty( $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] ) ) ? $alchemists_data['alchemists__player-sp-event-circular-bars-custom-format'] : $event_circular_bars_stats_format_default;

// Game Stats
$event_stats_table_enable = isset( $alchemists_data['alchemists__player-sp-event-game-stats'] ) ? $alchemists_data['alchemists__player-sp-event-game-stats'] : 0;
$event_stats_table_custom = isset( $alchemists_data['alchemists__player-sp-event-game-stats-custom'] ) ? $alchemists_data['alchemists__player-sp-event-game-stats-custom'] : array();
if ( alchemists_sp_preset( 'football' ) ) {
	$event_stats_table_title_default = esc_html__( 'Matchup', 'alchemists' );
} else {
	$event_stats_table_title_default = esc_html__( 'Game Statistics', 'alchemists' );
}
$event_stats_table_title  = ( isset( $alchemists_data['alchemists__player-sp-event-game-stats-title'] ) && ! empty( $alchemists_data['alchemists__player-sp-event-game-stats-title'] ) ) ? $alchemists_data['alchemists__player-sp-event-game-stats-title'] : $event_stats_table_title_default;

// Timeline
$event_timeline_type = isset( $alchemists_data['alchemists__player-sp-event-game-timeline-type'] ) ? $alchemists_data['alchemists__player-sp-event-game-timeline-type'] : 'horizontal';

// Custom Progress Bars
$event_progress_bars_stats_custom_array = array();
$event_progress_bars_stats_custom_array = alchemists_sp_get_performances_array( $event_progress_bars_stats_custom, $event_progress_bars_stats_custom_array );

// Custom Circular Bars
$event_circular_bars_stats_custom_array = array();
$event_circular_bars_stats_custom_array = alchemists_sp_get_performances_array( $event_circular_bars_stats_custom, $event_circular_bars_stats_custom_array );

// Custom Stats Table
$event_stats_table_custom_array = array();
$event_stats_table_custom_array = alchemists_sp_get_performances_array( $event_stats_table_custom, $event_stats_table_custom_array );

// SportsPress default options
$defaults = array(
	'abbreviate_teams' => get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false,
	'link_teams'       => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
);
extract( $defaults, EXTR_SKIP );


$stats_default = array();

// Sports specifics
if ( alchemists_sp_preset( 'soccer') ) {

	// Soccer

	// Default Event Stats
	$stats_default = array(
		'progress_bars'  => array( 'sh', 'f', 'off' ),
		'event_percents' => array( 'shpercent', 'passpercent' ),
		'event_stats'    => array( 'sh', 'sog', 'ck', 's', 'yellowcards', 'redcards' )
	);

	// Stats
	$event_stats               = $stats_default['event_stats'];
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

} elseif ( alchemists_sp_preset( 'football') ) {

	// American Football

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'att', 'yds', 'rec' ),
		'event_percents' => array( 'comp', 'recyds', 'int' ),
		'event_stats'    => array( 'yds', 'td', 'lng', 'fum', 'lost' )
	);

	// Stats
	$event_stats               = $stats_default['event_stats'];
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

} elseif ( alchemists_sp_preset( 'esports') ) {

	// eSports

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'kills', 'deaths', 'assists', 'gold' ),
		'event_percents' => array( 'kills', 'deaths', 'cs', 'dmg' ),
	);

	// Stats
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];

} else {

	// Basketball

	// Default Game Stats
	$stats_default = array(
		'progress_bars'  => array( 'ast', 'reb', 'stl', 'blk', 'pf' ),
		'event_percents' => array( 'fgpercent', 'threeppercent', 'ftpercent' ),
	);

	// Stats
	$event_progress_bars_stats = $stats_default['progress_bars'];
	$event_percents_stats      = $stats_default['event_percents'];
}


	// Custom Stats - Progress Bars
	if ( $event_progress_bars_stats_enable && $event_progress_bars_stats_custom_array ) {
		$event_progress_bars_stats = $event_progress_bars_stats_custom_array;
	}

	// Custom Stats - Circular Bars
	if ( $event_circular_bars_stats_enable && $event_circular_bars_stats_custom_array ) {
		$event_percents_stats = $event_circular_bars_stats_custom_array;
	}

	// Custom Stats - Stats Table
	if ( $event_stats_table_enable && $event_stats_table_custom_array ) {
		$event_stats = $event_stats_table_custom_array;
	}


// Shortcode params
$title = $event = $event_id_on = $event_id = $event_last = $team_id = $team_id_on = $team_id_num = $display_percentage = $link = $event_link = $event_link_txt = $color_team_1 = $color_team_2 = $el_class = $el_id = $css = $css_animation = '';
$a_href = $a_title = $a_target = $a_rel = '';
$attributes = array();

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'card';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

//parse link
$link = ( '||' === $link ) ? '' : $link;
$link = vc_build_link( $link );
$use_link = false;
if ( strlen( $link['url'] ) > 0 ) {
	$use_link = true;
	$a_href = $link['url'];
	$a_title = $link['title'];
	$a_target = $link['target'];
	$a_rel = $link['rel'];
}

$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}

$event_id_output = '';

if ( $event_last == 1 ) {

	// If Team is not selected and selection by Team ID disabled
	if ( $team_id == 'default' && $team_id_on != 1 ) {
		// Check if we're on a Team Page
		if ( is_singular( 'sp_team' ) ) {
			global $post;
			$team_id = $post->ID;
		} else {
			// If we're not a Team Page, just stop.
			echo '<div class="alert alert-warning">' . esc_html__( 'Configure the element by selecting a Team or Event.', 'alchemists' ) . '</div>';
			return;
		}

	} elseif ( $team_id_on == 1 && ! empty( $team_id_num ) ) {
		// Team selected, selection by Team ID enabled and Team ID is not empty
		$team_id = $team_id_num;
	}

	// find last event ID
	$event_id_output = alchemists_sp_last_event_id( $team_id );

	if ( empty( $event_id_output ) ) {
		// Don't display element if there is no published event with selected Team.
		return;
	}

} else {
	// Display selected event
	if ( isset( $event ) ) {
		if ( $event_id_on == 1 && ! empty( $event_id ) ) {
			$event_id_output = $event_id;
		} else {
			$event_id_output = $event;
		}
	}
}

// get last event post object
$event_post = get_post( $event_id_output );

// Results
$event_obj      = new SP_Event( $event_post->ID );
$results        = $event_obj->results();
$primary_result = alchemists_sportspress_primary_result();
$event_date     = $event_post->post_date;
$teams          = array_unique( get_post_meta( $event_post->ID, 'sp_team' ) );
$teams          = array_filter( $teams, 'sp_filter_positive' );

// The first row should be column labels
$labels = $results[0];

// Remove the first row to leave us with the actual data
unset( $results[0] );

$results = array_filter( $results );

$team1 = null;
$team2 = null;

// get teams ID
if ( count( $teams ) > 1 ) {
	$team1 = $teams[0];
	$team2 = $teams[1];
}

// Venues
$venue1_desc = wp_get_post_terms( $team1, 'sp_venue' );
$venue2_desc = wp_get_post_terms( $team2, 'sp_venue' );


// Primary Result
$sportspress_primary_result = get_option( 'sportspress_primary_result', null );

$team1_color_primary   = get_field( 'team_color_primary', $team1 );
$team1_color_secondary = get_field( 'team_color_secondary', $team1 );
$team2_color_primary   = get_field( 'team_color_primary', $team2 );
$team2_color_secondary = get_field( 'team_color_secondary', $team2 );

if ( $team1_color_primary ) {
	$color_primary = $team1_color_primary;
} else {
	$color_primary = isset( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
}

// echo '<pre>' . var_export( $team1_color_primary, true ) . '</pre>';

// If there is no Events found stop here.
if ( is_null( $event_post ) ) {
	echo '<div class="alert alert-warning">' . esc_html__( 'No Event found.', 'alchemists' ) . '</div>';
	return;
}

if ( $use_link ) {
	$attributes[] = 'href="' . trim( $a_href ) . '"';
	$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
	if ( ! empty( $a_target ) ) {
		$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
	}
	if ( ! empty( $a_rel ) ) {
		$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
	}
}

// 1st Team Color
$color_team_1_bar_output = '';
$color_team_1_progress_bar_output = '';
if ( $color_team_1 ) {

	if ( alchemists_sp_preset( 'esports' ) ) {
		$color_team_1_bar_output = 'data-track-color=' . $color_team_1;
	} else {
		$color_team_1_bar_output = 'data-bar-color=' . $color_team_1;
	}

	if ( alchemists_sp_preset( 'football' ) ) {
		$color_team_1_progress_bar_output = 'background-image: radial-gradient(circle, ' . $color_team_1 . ', ' . $color_team_1 . ' 2px, transparent 2px, transparent), radial-gradient(circle, ' . $color_team_1 . ', ' . $color_team_1 . ' 2px, transparent 2px, transparent), linear-gradient(to right, ' . $color_team_1 . ', ' . $color_team_1 . ' 4px, transparent 4px, transparent 8px);';
	} else {
		$color_team_1_progress_bar_output = 'background-color:' . $color_team_1;
	}

} else {

	if ( alchemists_sp_preset( 'esports' ) ) {
		$color_team_1_bar_output = 'data-track-color=' . $color_primary;
	} else {
		$color_team_1_bar_output = 'data-bar-color=' . $color_primary;
	}

	if ( alchemists_sp_preset( 'football' ) ) {
		$color_team_1_progress_bar_output = 'background-image: radial-gradient(circle, ' . $color_primary . ', ' . $color_primary . ' 2px, transparent 2px, transparent), radial-gradient(circle, ' . $color_primary . ', ' . $color_primary . ' 2px, transparent 2px, transparent), linear-gradient(to right, ' . $color_primary . ', ' . $color_primary . ' 4px, transparent 4px, transparent 8px);';
	} else {
		$color_team_1_progress_bar_output = 'background-color:' . $color_primary;
	}
}

// 2nd Team Color
$color_team_2_bar_output = '';
if ( $team2_color_primary ) {
	$color_team_2 = $team2_color_primary;
	$color_team_2_bar_output = 'data-bar-color=' . $team2_color_primary .'';
} else {
	if ( alchemists_sp_preset( 'football') ) {
		$color_team_2_bar_output = 'data-bar-color=#aaf20e';
	} elseif ( alchemists_sp_preset( 'soccer') ) {
		$color_team_2_bar_output = 'data-bar-color=#9fe900';
	} else {
		$color_team_2_bar_output = 'data-bar-color=#0cb2e2';
	}
}


$color_team_2_progress_bar_output = '';
if ( $color_team_2 ) {
	$color_team_2_bar_output = 'data-bar-color=' . $color_team_2;

	if ( alchemists_sp_preset( 'football' ) ) {
		$color_team_2_progress_bar_output = 'background-image: radial-gradient(circle, ' . $color_team_2 . ', ' . $color_team_2 . ' 2px, transparent 2px, transparent), radial-gradient(circle, ' . $color_team_2 . ', ' . $color_team_2 . ' 2px, transparent 2px, transparent), linear-gradient(to right, ' . $color_team_2 . ', ' . $color_team_2 . ' 4px, transparent 4px, transparent 8px);';
	} else {
		$color_team_2_progress_bar_output = 'background-color:' . $color_team_2;
	}
}

// Event permalink
$permalink       = get_post_permalink( $event_id_output, false, true );
$event_link_attr = array();

if ( $event_link == 1 ) {
	$use_link = true;
	$attributes = $event_link_attr;
	$attributes[] = 'href="' . trim( $permalink ) . '"';
	$a_title = $event_link_txt;
}

$attributes = implode( ' ', $attributes );

// Show stats
$show_stats = false;
if ( ! empty( $results ) ) :
	if ( ! empty( $results[ $team1 ] ) && ! empty( $results[ $team2 ] ) ) :
		if ( isset($results[ $team1 ]['outcome'] ) && isset( $results[ $team2 ]['outcome'] ) ) :
			$show_stats = true;
		endif;
	endif;
endif;
?>

<!-- Game Scoreboard -->
<div <?php echo implode( ' ', $wrapper_attributes ); ?> class="<?php echo esc_attr( apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $css_class, $this->settings['base'], $atts ) ); ?>">

	<?php
	if ( $title ) :
		?>
		<div class="widget__title card__header">
			<?php
			echo wpb_widget_title( array( 'title' => $title ) );

			if ( $use_link ) :
				$card_header_btn_classes = array(
					'btn',
					'btn-xs',
					'btn-default',
					'card-header__button',
				);
				// make it outline
				if ( alchemists_sp_preset( 'basketball' ) || alchemists_sp_preset( 'soccer' ) ) {
					$card_header_btn_classes[] = 'btn-outline';
				}

				echo '<a class="' . esc_attr( implode( ' ', $card_header_btn_classes ) ) . '" ' . $attributes . '>' . $a_title . '</a>';
			endif;
			?>
		</div>
		<?php
	endif;
	?>

	<div class="card__content">

		<?php
		if ( alchemists_sp_preset( 'soccer') ) {

			// Soccer
			include( locate_template( 'vc_templates/alc_event_scoreboard/soccer-alc_event_scoreboard.php' ) );

		} elseif ( alchemists_sp_preset( 'football' ) ) {

			// American Football
			include( locate_template( 'vc_templates/alc_event_scoreboard/football-alc_event_scoreboard.php' ) );

		} elseif ( alchemists_sp_preset( 'esports' ) ) {

			// eSports
			include( locate_template( 'vc_templates/alc_event_scoreboard/esports-alc_event_scoreboard.php' ) );

		} else {

			// Basketball and default
			include( locate_template( 'vc_templates/alc_event_scoreboard/basketball-alc_event_scoreboard.php' ) );

		}
		?>

	</div>
</div>
<!-- Game Scoreboard / End -->
