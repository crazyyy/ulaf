<?php
/**
 * Sportspress Functions
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.0
 */

/**
 * Games - Remove Tabs
 */
if ( ! function_exists( 'alchemists_remove_event_tabs' ) ) {
	function alchemists_remove_event_tabs( $options = array() ) {
		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'type' ), array( 'event_tabs' ) ) ) {
				unset( $options[ $index ] );
			}
		}
		return $options;
	}
	add_filter( 'sportspress_event_options', 'alchemists_remove_event_tabs' );
}


/**
 * Team - Remove Layout Builder
 */
if ( ! function_exists( 'alchemists_remove_team_layout' ) ) {
	function alchemists_remove_team_layout( $options = array() ) {
		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'type' ), array( 'team_layout', 'team_tabs' ) ) ) {
				unset( $options[ $index ] );
			}
		}
		return $options;
	}
	add_filter( 'sportspress_team_options', 'alchemists_remove_team_layout' );
}


/**
 * Styling - Remove SportsPress Color schemes
 */
if ( ! function_exists( 'alchemists_remove_script_styling_options' ) ) {
	function alchemists_remove_script_styling_options( $options = array() ) {
		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'type' ), array( 'colors' ) ) ) {
				unset( $options[ $index ] );
			}
		}
		return $options;
	}
	add_filter( 'sportspress_script_styling_options', 'alchemists_remove_script_styling_options' );
}


/**
 * Players - Remove Layout Builder
 */
if ( ! function_exists( 'alchemists_remove_player_layout' ) ) {
	function alchemists_remove_player_layout( $options = array() ) {
		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'type' ), array( 'player_layout', 'player_tabs' ) ) ) {
				unset( $options[ $index ] );
			}
		}
		return $options;
	}
	add_filter( 'sportspress_player_options', 'alchemists_remove_player_layout' );
}


/**
 * Staff - Remove Layout Builder
 */
if ( ! function_exists( 'alchemists_remove_staff_layout' ) ) {
	function alchemists_remove_staff_layout( $options = array() ) {
		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'type' ), array( 'staff_layout', 'staff_tabs' ) ) ) {
				unset( $options[ $index ] );
			}
		}
		return $options;
	}
	add_filter( 'sportspress_staff_options', 'alchemists_remove_staff_layout' );
}


/**
 * Event - Replace formant strings
 */
if ( ! function_exists( 'alchemists_event_logos_format_text' ) ) {
	function alchemists_event_logos_format_text( $options = array() ) {

		foreach ( $options as $index => $option ) {
			if ( in_array( sp_array_value( $option, 'id' ), array( 'sportspress_event_logos_format' ) ) ) {
				$options[ $index ]['options'] = array (
					'inline' => __( 'Default', 'alchemists' ),
					'block'  => __( 'Extended', 'alchemists' ),
				);
			}
		}

		return $options;
	}
	add_filter( 'sportspress_event_logo_options', 'alchemists_event_logos_format_text' );
}


/**
 * Convert date to Age
 */
if ( ! function_exists( 'alchemists_get_age' ) ) {
	function alchemists_get_age( $date, $show_years = false ) {
		$date = explode( '-', $date );
		$age = ( date( 'md', date( 'U', mktime( 0, 0, 0, $date[0], $date[1], $date[2] ) ) ) > date('md')
			? ( ( date( 'Y' ) - $date[2] ) - 1 )
			: ( date( 'Y' ) - $date[2] ) );

		if ( $show_years ) {
			$age = $age . ' ' . _n( 'Year', 'Years', $age, 'alchemists' );
		}

		return $age;
	}
}


/**
 * Single Player permalinks and titles
 */
if ( ! function_exists( 'alchemists_insertrules' ) ) {
	add_filter('rewrite_rules_array', 'alchemists_insertrules');

	// Adding fake pages' rewrite rules
	function alchemists_insertrules( $rules ) {
		$alchemists_data = get_option( 'alchemists_data' );
		$single_player_pages = isset( $alchemists_data['alchemists__player-subpages']['enabled'] ) ? $alchemists_data['alchemists__player-subpages']['enabled'] : array( 'overview' => esc_html__( 'Overview', 'alchemists' ), 'stats' => esc_html__( 'Statistics', 'alchemists' ), 'bio' => esc_html__( 'Biography', 'alchemists' ), 'news' => esc_html__( 'Related News', 'alchemists' ), 'gallery' => esc_html__( 'Gallery', 'alchemists' ));

		// Player subpages slugs
		$overview_slug       = isset( $alchemists_data['alchemists__player-subpages-slug--overview'] ) ? $alchemists_data['alchemists__player-subpages-slug--overview'] : 'overview';
		$stats_slug          = isset( $alchemists_data['alchemists__player-subpages-slug--stats'] ) ? $alchemists_data['alchemists__player-subpages-slug--stats'] : 'stats';
		$bio_slug            = isset( $alchemists_data['alchemists__player-subpages-slug--bio'] ) ? $alchemists_data['alchemists__player-subpages-slug--bio'] : 'bio';
		$news_slug           = isset( $alchemists_data['alchemists__player-subpages-slug--news'] ) ? $alchemists_data['alchemists__player-subpages-slug--news'] : 'news';
		$gallery_slug        = isset( $alchemists_data['alchemists__player-subpages-slug--gallery'] ) ? $alchemists_data['alchemists__player-subpages-slug--gallery'] : 'gallery';

		// Player page slug
		$player_slug = apply_filters( 'alchemists_player_permalinks', get_option( 'sportspress_player_slug', 'player' ) );

		$newrules = array();
		foreach ( $single_player_pages as $slug => $title ) {
			if ( 'placebo' === $slug ) {
				continue;
			}
			$newrules[ $player_slug . '/([^/]+)/' . strtolower( trim( ${$slug . '_slug'} ) ) . '/?$'] = 'index.php?sp_player=$matches[1]&fpage=' . $slug;
		}
		return $newrules + $rules;
	}
}

if ( ! function_exists( 'alchemists_insertqv' ) ) {
	add_filter('query_vars', 'alchemists_insertqv');

	// Tell WordPress to accept our custom query variable
	function alchemists_insertqv($vars) {
		array_push($vars, 'fpage');
		return $vars;
	}
}

if ( ! function_exists( 'alchemists_wpseo_canonical_exclude' ) ) {
	/* Yoast Canonical Removal from Single Player pages */
	add_filter( 'wpseo_canonical', 'alchemists_wpseo_canonical_exclude' );

	function alchemists_wpseo_canonical_exclude( $canonical ) {
		global $post;
		if ( is_singular('sp_player')) {
			$canonical = false;
		}
		return $canonical;
	}
}


/**
 * Single Team permalinks and titles
 */

if ( ! function_exists( 'alchemists_team_insertrules' ) ) {
	add_filter('rewrite_rules_array', 'alchemists_team_insertrules');

	// Adding sub pages' rewrite rules
	function alchemists_team_insertrules( $rules ) {

		$alchemists_data = get_option( 'alchemists_data' );
		$single_team_pages = isset( $alchemists_data['alchemists__team-subpages']['enabled'] ) ? $alchemists_data['alchemists__team-subpages']['enabled'] : array( 'Overview' => esc_html__( 'Overview', 'alchemists' ), 'roster' => esc_html__( 'Roster', 'alchemists' ), 'standings' => esc_html__( 'Standings', 'alchemists' ), 'results' => esc_html__( 'Latest Results', 'alchemists' ), 'schedule' => esc_html__( 'Schedule', 'alchemists' ), 'gallery' => esc_html__( 'Gallery', 'alchemists' ));

		// Team subpages slugs
		$overview_slug     = isset( $alchemists_data['alchemists__team-subpages-slug--overview'] ) ? $alchemists_data['alchemists__team-subpages-slug--overview'] : 'overview';
		$roster_slug       = isset( $alchemists_data['alchemists__team-subpages-slug--roster'] ) ? $alchemists_data['alchemists__team-subpages-slug--roster'] : 'roster';
		$standings_slug    = isset( $alchemists_data['alchemists__team-subpages-slug--standings'] ) ? $alchemists_data['alchemists__team-subpages-slug--standings'] : 'standings';
		$results_slug      = isset( $alchemists_data['alchemists__team-subpages-slug--results'] ) ? $alchemists_data['alchemists__team-subpages-slug--results'] : 'results';
		$schedule_slug     = isset( $alchemists_data['alchemists__team-subpages-slug--schedule'] ) ? $alchemists_data['alchemists__team-subpages-slug--schedule'] : 'schedule';
		$gallery_slug      = isset( $alchemists_data['alchemists__team-subpages-slug--gallery'] ) ? $alchemists_data['alchemists__team-subpages-slug--gallery'] : 'gallery';

		// Team page slug
		$team_slug = apply_filters( 'alchemists_team_permalinks', get_option( 'sportspress_team_slug', 'team' ) );

		$newrules = array();
		foreach ( $single_team_pages as $slug => $title ) {
			if ( 'placebo' === $slug ) {
				continue;
			}
			$newrules[ $team_slug . '/([^/]+)/' . strtolower( trim( ${$slug . '_slug'} ) ) . '/?$'] = 'index.php?sp_team=$matches[1]&teampage=' . $slug;
		}
		return $newrules + $rules;
	}
}

if ( ! function_exists( 'alchemists_team_insertqv' ) ) {
	add_filter('query_vars', 'alchemists_team_insertqv');

	// Tell WordPress to accept our custom query variable
	function alchemists_team_insertqv($vars) {
		array_push($vars, 'teampage');
		return $vars;
	}
}


/**
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
if ( ! function_exists( 'remove_filters_for_anonymous_class' ) ) {
	function remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 ) {
		global $wp_filter;

		// Take only filters on right hook name and priority
		if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
			return false;

		// Loop on filters registered
		foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
						// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
						if( is_a( $wp_filter[$hook_name], 'WP_Hook' ) ) {
								unset( $wp_filter[$hook_name]->callbacks[$priority][$unique_id] );
						}
						else {
							unset($wp_filter[$hook_name][$priority][$unique_id]);
						}
				}
			}

		}

		return false;
	}
}


/**
 * Remove additional content added by Sportspress
 */

// Single Team
remove_filters_for_anonymous_class( 'the_content', 'SP_Template_Loader', 'team_content', 10 );

// Single Staff
remove_filters_for_anonymous_class( 'the_content', 'SP_Template_Loader', 'staff_content', 10 );

// Single Player
remove_filters_for_anonymous_class( 'the_content', 'SP_Template_Loader', 'player_content', 10 );


/**
 * Get main result option
 */
if ( ! function_exists( 'alchemists_sportspress_primary_result' ) ) {
	function alchemists_sportspress_primary_result() {
		$primary_result = 'points';
		if ( get_option( 'sportspress_primary_result' ) != null ) {
			$primary_result = get_option( 'sportspress_primary_result' );
		}

		return $primary_result;
	}
}


/**
 * Adds plugins depends on selected Sport preset
 */
if ( ! function_exists( 'alchemists_sp_extension_plugins' ) ) {
	function alchemists_sp_extension_plugins() {
		$sport = sp_array_value( $_POST, 'sportspress_sport', get_option( 'sportspress_sport', null ) );
		if ( ! $sport ) return;

		$plugins = array();

		if ( 'soccer' == $sport ) {
			$plugins[] = array(
				'name'        => 'Alchemists Soccer for SportsPress',
				'slug'        => 'alc-soccer',
				'source'      => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-soccer.zip',
				'required'    => true,
				'version'     => '0.1.1',
			);
		} elseif ( 'football' == $sport ) {
			$plugins[] = array(
				'name'        => 'Alchemists American Football for SportsPress',
				'slug'        => 'alc-football',
				'source'      => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-football.zip',
				'required'    => true,
				'version'     => '0.2.0',
			);
		} elseif ( 'lol' == $sport || 'csgo' == $sport || 'dota2' == $sport ) {
			$plugins = array(
				array(
					'name'        => 'Alchemists League of Legends for SportsPress',
					'slug'        => 'alc-lol',
					'source'      => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/tMABqQzK5gdF/alc-lol.zip',
					'required'    => true,
					'version'     => '1.0.0',
				),
				array(
					'name'         => 'Twitch for WordPress',
					'slug'         => 'tomparisde-twitchtv-widget',
					'source'       => 'https://danfisher-bucket-1.s3.us-east-2.amazonaws.com/plugins/qCvb5TpVbYC5/tomparisde-twitchtv-widget.zip',
					'required'     => true,
				),
				array(
					'name'         => 'Advanced Custom Fields: SVG Icon',
					'slug'         => 'acf-svg-icon',
					'source'       => 'https://github.com/BeAPI/acf-svg-icon/archive/2.0.4.zip',
					'external_url' => 'https://github.com/BeAPI/acf-svg-icon',
					'required'     => false,
					'version'      => '2.0.4'
				),
			);
		}

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}
}
add_action( 'tgmpa_register', 'alchemists_sp_extension_plugins' );



/**
 * Player: Circular Bar on Player Header Stats (player-statistics-avg.php)
 */
 if ( ! function_exists( 'alchemists_sp_player_circular_bar' ) ) {
	function alchemists_sp_player_circular_bar(
		$class = 'player-info-stats__item',
		$percent = 100,
		$line_width = 8,
		$track_color = '',
		$bar_color = '',
		$stat_value = '',
		$stat_label = '',
		$stat_sublabel = '',
		$is_avg = '',
		$size = '' ) {

		$output = '<div class="' . esc_attr( $class ) . '">';
			$output .= '<div class="circular circular--size-' . $size . '">';
				$output .= '<div class="circular__bar" data-percent="' . round( $percent ) . '" data-line-width="' . $line_width . '" data-track-color="' . $track_color . '" data-bar-color="' . $bar_color . '">';
					$output .= '<span class="circular__percents">';
						$output .= $stat_value;
						if ( 'team-roster__player-stats-circular-bar' == $class ) {
							$output .= '<span class="circular__label">';
								$output .= $stat_label;
							$output .= '</span>';
						} else {
							if ( $is_avg ) {
								$output .= '<small>' . esc_html__( 'avg', 'alchemists' ) . '</small>';
							} else {
								$output .= '<small>' . esc_html__( 'tot', 'alchemists' ) . '</small>';
							}
						}
					$output .= '</span>';
				$output .= '</div>';

				if ( 'team-roster__player-stats-circular-bar' != $class ) {
					$output .= '<span class="circular__label">';
						$output .= $stat_label;
					$output .= '</span>';
				}
			$output .= '</div>';
		$output .= '</div>';

		echo wp_kses_post( $output );
	}
}


/**
 * Filter SportsPress Statistics array
 */
if ( ! function_exists( 'alchemists_sp_filter_array' ) ) {
	function alchemists_sp_filter_array( $array_default = array(), $array_filter = array() ) {
		return array_reverse( array_intersect_key( $array_default, array_flip( $array_filter ) ) );
	}
}


/**
 * Get Icon of SportsPress variable
 */
if ( ! function_exists( 'alc_sp_get_icon' ) ) {
	function alc_sp_get_icon( $post = 0 ) {
		$icon = get_post_meta( $post, 'sp_icon', true );
		if ( '' !== $icon ) {
			return $icon;
		} else {
			return 'marker';
		}
	}
}


/**
 * Get Icon Color of SportsPress variable
 */
if ( ! function_exists( 'alc_sp_get_icon_color' ) ) {
	function alc_sp_get_icon_color( $post = 0, $default_color = '#222222' ) {
		$icon_color = get_post_meta( $post, 'sp_color', true );
		if ( '' !== $icon_color ) {
			return $icon_color;
		} else {
			return $default_color;
		}
	}
}


/**
 * Get array of Performance IDs
 */
if ( ! function_exists( 'alc_get_performance_ids' ) ) {
	function alc_get_performance_ids() {
		$performance_ids = array();
		$performance_posts = get_posts( array(
			'posts_per_page' => -1,
			'post_type'      => 'sp_performance'
		) );
		foreach ( $performance_posts as $post ):
			$performance_ids[ $post->post_name ] = $post->ID;
		endforeach;

		return $performance_ids;
	}
}


/**
 * Sort Timed stats, e.g. Goals (Soccer)
 */
if ( ! function_exists( 'alc_sort_timed_stats' ) ) {
	function alc_sort_timed_stats( $a, $b, $stat = 'goals' ) {
		if ( isset( $a[ $stat ] ) && isset( $b[ $stat ] ) ) {
			return strnatcmp( strstr( $a[ $stat ], "(" ), strstr( $b[ $stat ], "(") );
		}
	}
}

/**
 * Get goals by player (Soccer)
 */
if ( ! function_exists( 'alchemists_sp_player_goal_output' ) ) {
	function alchemists_sp_player_goal_output( $goals = array(), $event_team_performances = array(), $performance_ids = array() ) {
		$output = '';
		$event_performance = $event_team_performances;

		$performance_id = sp_array_value( $performance_ids, $goals, null );

		if ( ! empty( $event_performance ) && isset( $event_performance ) ) {

			// remove total array
			unset( $event_performance[0] );

			// sort event performances array by time
			uasort( $event_performance, 'alc_sort_timed_stats' );

			foreach( $event_performance as $player_id => $player ) {
				if ( isset( $player[ $goals ] ) ) {
					if ( $player[ $goals ] >= 1 ) {
						$output .= '<span class="game-result__goal">';
							$output .= get_the_title( $player_id ) . ' - ' . $player[ $goals ];

							if ( has_post_thumbnail( $performance_id ) ) {
								$output .= get_the_post_thumbnail(
									$performance_id,
									'sportspress-fit-mini',
									array(
										'title' => sp_get_singular_name( $performance_id ),
										'class' => 'game-result__icon-img'
									)
								);
							} else {
								$output .= '<i class="game-result__icon sp-icon-' . alc_sp_get_icon( $performance_id ) . '" style="color: ' . alc_sp_get_icon_color( $performance_id ) . '"></i>';
							}

						$output .= '</span>';
					}
				}
			}
		}

		echo wp_kses_post( $output );
	}
}



/**
 * Display Performance Icon
 */
if ( ! function_exists( 'alc_sp_performance_icon' ) ) {
	function alc_sp_performance_icon( $performance_id = 0, $icon_color = '#222222', $is_tooltip = true, $tootip_value1 = '', $tootip_value2 = '' ) {
		$output = '';
		$tooltip_icon_output = '';
		if ( $is_tooltip ) {
			$tooltip_icon_output = 'data-toggle="tooltip" data-placement="top" title="' . $tootip_value1 . ' - ' . $tootip_value2 . '"';
		}
		if ( has_post_thumbnail( $performance_id ) ) {
			if ( $is_tooltip ) {
				$output .= get_the_post_thumbnail(
					$performance_id,
					'sportspress-fit-mini',
					array(
						'data-toggle'    => 'tooltip',
						'data-placement' => 'top',
						'title'          => $tootip_value1 . ' - ' . $tootip_value2,
						'class'          => 'df-icon-img',
					)
				);
			} else {
				$output .= get_the_post_thumbnail(
					$performance_id,
					'sportspress-fit-mini',
					array(
						'title' => sp_get_singular_name( $performance_id ),
						'class' => 'df-icon-img',
					)
				);
			}
		} else {
			$output .= '<i ' . $tooltip_icon_output . 'class="df-icon-font sp-icon-' . alc_sp_get_icon( $performance_id ) . '" style="color: ' . alc_sp_get_icon_color( $performance_id, $icon_color ) . '"></i>';
		}

		echo wp_kses_post( $output );
	}
}


/**
 * Create a new Performances array based on custom values from Theme Options
 */
if ( ! function_exists( 'alchemists_sp_get_performances_array' ) ) {
	function alchemists_sp_get_performances_array( $array_custom = array(), $array_output = array() ) {
		if ( $array_custom ) {
			foreach ( $array_custom as $key => $value) {
				$array_output[] = get_post_field( 'post_name', $array_custom[ $key ] );
			}
		}
		return $array_output;
	}
}


/**
 * Display percent value for Player Performance
 */
if ( ! function_exists( 'alchemists_sp_performance_percent' ) ) {
	function alchemists_sp_performance_percent( $team1_key = '', $team2_key = '' ) {

		if ( isset( $team1_key ) && !empty( $team1_key ) ) {
			$event_team1_percent = $team1_key;
		} else {
			$event_team1_percent = 0;
		}

		if ( isset( $team2_key ) && !empty( $team2_key ) ) {
			$event_team2_percent = $team2_key;
		} else {
			$event_team2_percent = 0;
		}

		$event_total_percent = $event_team1_percent + $event_team2_percent;
		if ( $event_total_percent <= 0 ) {
			$event_total_percent = 1;
		}

		$output = round( ( $event_team1_percent / $event_total_percent ) * 100 );

		return $output;
	}
}


/**
 * Check if field exists and not empty
 */
if ( ! function_exists( 'alchemists_check_exists_not_empty' ) ) {
	function alchemists_check_exists_not_empty( $field = '' ) {

		if ( isset( $field ) && !empty( $field ) ) {
			$field_output = $field;
		} else {
			$field_output = 0;
		}

		return $field_output;
	}
}


/**
 * Display proper value depends on type of Player Performance
 */
if ( ! function_exists( 'alchemists_sp_get_performances_based_on_format' ) ) {
	function alchemists_sp_get_performances_based_on_format( $format = '', $team1_key = '', $team2_key = '' ) {

		if ( $format == 'number' ) {
			$output = alchemists_sp_performance_percent( $team1_key, $team2_key );
		} else {
			if ( isset( $team1_key ) && ! empty( $team1_key ) ) {
				$output = $team1_key;
			} else {
				$output = '';
			}
		}

		return $output;
	}
}


/**
 * Dequeue scripts
 */
if( !function_exists( 'alchemists_remove_sp_pro_scripts' ) ) {
	function alchemists_remove_sp_pro_scripts() {
		wp_dequeue_script('sportspress-scoreboard');
		wp_dequeue_style('sportspress-scoreboard');
		wp_dequeue_style('sportspress-scoreboard-rtl');
		wp_dequeue_style('sportspress-scoreboard-ltr');
	}
	add_action( 'wp_enqueue_scripts', 'alchemists_remove_sp_pro_scripts' );
}


/**
 * Removes Team Colors
 */
if ( class_exists('SportsPress_Team_Colors')) {
	if( !function_exists( 'alchemists_remove_teamcolors_metaboxes' ) ) {
		function alchemists_remove_teamcolors_metaboxes() {
			remove_meta_box( 'sp_colorssdiv', 'sp_team', 'normal' );
		}
		add_action( 'do_meta_boxes' , 'alchemists_remove_teamcolors_metaboxes' );
	}
}


/**
 * Get last event
 */
if ( ! function_exists( 'alchemists_sp_last_event_id' ) ) {
	function alchemists_sp_last_event_id( $team_id = null ) {
		$events = get_posts( array(
			'post_type'      => 'sp_event',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query' => array(
				array(
					'key'     => 'sp_team',
					'value'   => $team_id,
					'compare' => '=',
				)
			)
		));

		$event_id = '';
		if ( ! empty( $events ) ) {
			$event_id = $events[0];
		}

		return $event_id;
	}
}


/**
 * Adds a class with current Team ID on Single Player pages
 */
if ( ! function_exists( 'alchemists_add_team_id_to_player' ) ) {
	function alchemists_add_team_id_to_player( $classes ) {
		if ( is_singular( 'sp_player' ) ) {
				global $post;
				$current_teams = get_post_meta( $post->ID, 'sp_current_team', false );
				$current_team_id = false;
				if ( $current_teams && is_array( $current_teams ) ) {
					$current_team_id = $current_teams[0];
				}
				$classes[] = 'alc-current-team-id-' . $current_team_id;
		}
		return $classes;
	}
	add_filter( 'body_class', 'alchemists_add_team_id_to_player' );
}



/**
 * Adds SVG sprite to ACF icon picker
 */
add_filter( 'acf_svg_icon_filepath', 'alchemists_svg_icon_filepath' );
function alchemists_svg_icon_filepath( $filepath ) {
	if ( is_file( get_template_directory() . '/assets/images/esports/icons.svg' ) ) {
		$filepath[] = get_template_directory() . '/assets/images/esports/icons.svg';
	}
	return $filepath;
}


/**
 * Event Time Status
 *
 * @param int  $post_id       Post ID.
 * @param bool $show_time     True if show time, false otherwise.
 * @param bool $show_divider  True if show divider, false otherwise.
 *
 * @return string
 */

if ( ! function_exists( 'alchemists_event_time_status_badge' ) ) {
	function alchemists_event_time_status_badge( $post_id = 0, $show_time = true, $show_divider = true ) {

		if ( ! $post_id ) $post_id = get_the_ID();

		$output = '';
		$time = sp_get_time( $post_id );

		$status        = get_post_meta( $post_id, 'sp_status', true );
		$status_output = apply_filters( 'sportspress_event_time', $time, $post_id );

		$badge_classes = array(
			'badge',
			'event-time-status__badge'
		);

		$schema_status = 'EventScheduled';

		if ( 'tbd' === $status ) {
			$badge_classes[] = 'badge-info';
			$schema_status = 'EventRescheduled';
		} elseif ( 'postponed' === $status ) {
			$badge_classes[] = 'badge-warning';
			$schema_status = 'EventPostponed';
		} elseif ( $status === 'cancelled' ) {
			$badge_classes[] = 'badge-danger';
			$schema_status = 'EventCancelled';
		}

		$is_divider = $show_divider ? 'event-time-status--has-divider' : 'event-time-status--no-divider';

		if ( 'tbd' === $status || 'postponed' === $status || $status === 'cancelled' ) {
			$output .= '<span class="event-time-status ' . $is_divider .'">';
				$output .= '<span class="' . implode( ' ', $badge_classes ) . '">' . $status_output . '</span>';
			$output .= '</span>';
		} else {
			if ( $show_time ) {
				$output .= '<span class="event-time-status ' . $is_divider .'">' . $time . '</span>';
			} else {
				$output = false;
			}
		}

		$output .= '<meta itemprop="eventStatus" content="http://schema.org/' . $schema_status .'">';

		return $output;
	}
}


/**
 * Event Scoreboard (Event Results)
 *
 * @param array $results    Results array.
 * @param array $labels     True if show time, false otherwise.
 *
 * @return string
 */

if ( ! function_exists( 'alchemists_sp_event_results' ) ) {
	function alchemists_sp_event_results( $results = array(), $labels = array() ) {

		$output = '';

		if ( ! empty ( $results ) ) {

			// Initialize
			$output = '';
			$table_rows = '';

			foreach( $results as $team_id => $result ):

				$table_rows .= '<tr>';

				$team_name = sp_team_short_name( $team_id );

				$table_rows .= '<th class="data-name">' . $team_name . '</th>';

				foreach( $labels as $key => $label ):
					if ( in_array( $key, array( 'name', 'outcome', 'poss' ) ) )
						continue;
					if ( array_key_exists( $key, $result ) && $result[ $key ] != '' ):
						$value = $result[ $key ];
					else:
						$value = apply_filters( 'sportspress_event_empty_result_string', '&mdash;' );
					endif;
					$table_rows .= '<td class="data-' . $key . '">' . $value . '</td>';
				endforeach;

				$table_rows .= '</tr>';
			endforeach;

			if ( ! empty( $table_rows ) ) {
				$output .= '<div class="table-responsive">' . '<table class="table table__cell-center table-wrap-bordered table-thead-color"><thead>' . '<tr>' .
					'<th class="data-name">' . esc_html__( 'Team', 'alchemists' ) . '</th>';

				foreach( $labels as $key => $label ) {
					if ( in_array( $key, array( 'name', 'outcome', 'poss' ) ) ) {
						continue;
					} else {
						$output .= '<th class="data-' . $key . '">' . $label . '</th>';
					}
				}

				$output .= '</tr>' . '</thead>' . '<tbody>';
					$output .= $table_rows;
				$output .= '</tbody>' . '</table>' . '</div>';
			}
		}

		return $output;
	}
}

/**
 * Add 'left' and 'right' attributes as safe for style
 * Absolutely needed because sanitization and escaping for [event_full]
 */
if ( ! function_exists( 'alchemists_add_style_attributes' ) ) {
	function alchemists_add_style_attributes( $styles ) {
		$styles[] = 'left';
		$styles[] = 'right';

		return $styles;
	}
}
add_filter( 'safe_style_css', 'alchemists_add_style_attributes' );


/**
 * Class ALC_SP_PlayerWrapper
 *
 * A wrapper class for SP_Player that allows dynamic properties to be set and retrieved.
 *
 * @package Alchemists
 * @since   4.6.0
 */
class ALC_SP_PlayerWrapper extends SP_Player {

	/**
	 * Array to store dynamic properties.
	 *
	 * @var array
	 */
	private $dynamicProperties = [];

	/**
	 * Magic method to set a dynamic property.
	 *
	 * @param string $name  The name of the property.
	 * @param mixed  $value The value of the property.
	 */
	public function __set($name, $value) {
		$this->dynamicProperties[$name] = $value;
	}
	/**
	 * * Magic method to get a dynamic property.
	 *
	 * @param string $name The name of the property.
	 * @return mixed|null The value of the property if it exists, null otherwise.
	 */
	public function __get($name) {
		return $this->dynamicProperties[$name] ?? null;
	}
}
