<?php
/**
 * OTFS Officials Class File
 *
 * @class       OTFS_Officials
 * @version     1.0.0
 * @package     OTFS/Classes
 * @category    Class
 * @author      SavvasHa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * OTFS_Officials
 */
class OTFS_Officials extends SP_Custom_Post {

	/**
	 * Status.
	 *
	 * @since 1.0.0
	 * @var string $status The events status.
	 */
	public $status = 'any';

	/**
	 * Order.
	 *
	 * @since 1.0.0
	 * @var string $order The events ordering.
	 */
	public $order = 'DESC';

	/**
	 * Returns duties sorted by `sp_order`.
	 *
	 * @access public
	 * @return array
	 */
	public function duties() {
		return $this->get_terms_sorted_by_sp_order( 'sp_duty' );
	}

	/**
	 * Returns filtered duties.
	 *
	 * @access public
	 * @return array
	 */
	public function filtered_duties() {
		$duties          = $this->duties();
		$selected_duties = get_option( 'otfs_officials_duties', array() );

		if ( ! $duties ) {
			return $selected_duties; }
		// Extract term IDs from $duties.
		$duties_term_ids = array_map(
			function ( $item ) {
				return $item->term_id;
			},
			$duties
		);

		// Convert term IDs in $selected_duties to integers for comparison.
		$selected_duties_term_ids = array_map( 'intval', $selected_duties );

		// Find common term IDs between the two arrays.
		$officials_duties = array_intersect( $duties_term_ids, $selected_duties_term_ids );

		return $officials_duties;
	}

	/**
	 * Returns nationalities
	 *
	 * @access public
	 * @return array
	 */
	public function nationalities() {
		$nationalities = get_post_meta( $this->ID, 'sp_nationality', true );
		if ( empty( $nationalities ) ) {
			return array();
		}
		foreach ( $nationalities as $nationality ) :
			if ( 2 === strlen( $nationality ) ) :
				$legacy      = SP()->countries->legacy;
				$nationality = strtolower( $nationality );
				$nationality = sp_array_value( $legacy, $nationality, null );
			endif;
		endforeach;
		return $nationalities;
	}

	/**
	 * Returns formatted events.
	 *
	 * @access public
	 * @return array
	 */
	public function events() {
		$args = array(
			'post_type'      => 'sp_event',
			'posts_per_page' => -1, // Retrieve all posts.
			'post_status'    => array( 'publish', 'future' ),
			'order'          => $this->order,
			'meta_query'     => array(
				array(
					'key'     => 'sp_officials',
					'value'   => 'i:' . $this->ID . ';|"' . $this->ID . '";', // Format the search string to match the serialized array.
					'compare' => 'RLIKE',
				),
				array(
					'key'     => 'sp_format',
					'value'   => apply_filters( 'sportspress_competitive_event_formats', array( 'league' ) ),
					'compare' => 'IN',
				),
			),
		);
		if ( 'publish' === $this->status ) {
			$args['post_status'] = 'publish';
		}
		if ( 'future' === $this->status ) {
			$args['post_status'] = 'future';
		}
		$events = get_posts( $args );

		return $events;
	}

	/**
	 * Returns formatted stats.
	 *
	 * @access public
	 * @param int  $league_id The ID of the league.
	 * @param int  $duty_id   The ID of official's duty. If set to -1 then all duties will be considered.
	 * @param bool $admin     Optional. Whether the request is from an admin context. Defaults to false.
	 * @return array
	 */
	public function stats( $league_id, $duty_id = -1, $admin = false ) {
		$seasons = $this->get_terms_sorted_by_sp_order( 'sp_season' );
		// If no Seasons are selected by the user, then all Seasons should be included.
		if ( ! is_array( $seasons ) ) {
			$seasons = get_terms(
				array(
					'taxonomy'   => 'sp_season',
					'hide_empty' => true,
				)
			);
			usort( $seasons, 'sp_sort_terms' );
		}
		// TODO: Add metrics support to officials.
		$metrics = (array) get_post_meta( $this->ID, 'sp_metrics', true );
		$stats   = (array) get_post_meta( $this->ID, 'sp_statistics', true );
		$leagues = (array) sp_array_value( (array) get_post_meta( $this->ID, 'sp_leagues', true ), $league_id );
		uksort( $leagues, 'sp_sort_terms' );
		$manual_columns = 'manual' === get_option( 'otfs_officials_columns', 'auto' ) ? true : false;

		$season_ids   = array_filter( wp_list_pluck( $seasons, 'term_id' ) );
		$season_order = array_flip( $season_ids );
		foreach ( $season_order as $season_id => $val ) {
			$season_order[ $season_id ] = null;
		}

		$leagues = array_replace( $season_order, $leagues );

		// Get performance labels.
		$args = array(
			'post_type'      => array( 'sp_performance' ),
			'numberposts'    => 100,
			'posts_per_page' => 100,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'sp_format',
					'value'   => 'number',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'sp_format',
					'value'   => array( 'equation', 'text' ),
					'compare' => 'NOT IN',
				),
			),
		);

		$posts = get_posts( $args );

		if ( $manual_columns ) {
			$usecolumns     = (array) get_post_meta( $this->ID, 'sp_columns', true );
			$has_checkboxes = true;
		} else {
			$usecolumns = array();
			if ( is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					// Get visibility.
					$visible = get_post_meta( $post->ID, 'otfs_visible', true );
					if ( '' === $visible || $visible ) {
						$usecolumns[] = $post->post_name;
					}
				}
			}
			$has_checkboxes = false;
		}

		$performance_labels = array();
		$formats            = array();

		foreach ( $posts as $post ) :
			if ( get_option( 'otfs_officials_statistics_mode', 'values' ) === 'icons' ) {
				$icon = apply_filters( 'sportspress_event_performance_icons', '', $post->ID, 1 );
				if ( '' !== $icon ) {
					$performance_labels[ $post->post_name ] = $icon;
				} elseif ( has_post_thumbnail( $post ) ) {
						$icon                                   = get_the_post_thumbnail( $post, 'sportspress-fit-mini', array( 'title' => sp_get_singular_name( $post ) ) );
						$performance_labels[ $post->post_name ] = apply_filters( 'sportspress_event_performance_icons', $icon, $post->ID, 1 );
				} else {
					$performance_labels[ $post->post_name ] = $post->post_title;
				}
			} else {
				$performance_labels[ $post->post_name ] = $post->post_title;
			}
			$format = get_post_meta( $post->ID, 'sp_format', true );
			if ( '' === $format ) {
				$format = 'number';
			}
			$formats[ $post->post_name ] = $format;
		endforeach;

		// Get statistic labels.
		$args = array(
			'post_type'      => array( 'sp_statistic' ),
			'numberposts'    => 100,
			'posts_per_page' => 100,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$posts = get_posts( $args );

		if ( $manual_columns ) {
			$usecolumns = array_merge( $usecolumns, (array) get_post_meta( $this->ID, 'sp_columns', true ) );
			$usecolumns = array_filter( $usecolumns );
		} elseif ( is_array( $posts ) ) {
			foreach ( $posts as $post ) {
				// Get visibility.
				$visible = get_post_meta( $post->ID, 'otfs_visible', true );
				if ( '' === $visible || $visible ) {
					$usecolumns[] = $post->post_name;
				}
			}
		}

		// Generate array of all season ids and season names.
		$div_ids      = array();
		$season_names = array();
		foreach ( $seasons as $season ) :
			if ( is_object( $season ) && property_exists( $season, 'term_id' ) && property_exists( $season, 'name' ) ) :
				$div_ids[]                        = $season->term_id;
				$season_names[ $season->term_id ] = $season->name;
			endif;
		endforeach;

		$div_ids[]       = 0;
		$season_names[0] = esc_attr__( 'Total', 'sportspress' );

		$data = array();

		$league_stats = sp_array_value( $stats, $league_id, array() );
		$div_ids      = apply_filters( 'otfs_officials_data_season_ids', $div_ids, $league_stats );

		// Get all seasons populated with data where available.
		$data = sp_array_combine( $div_ids, $league_stats, true );

		// Get equations from statistic variables.
		$equations = sp_get_var_equations( 'sp_statistic' );

		// Initialize placeholders array.
		$placeholders = array();
		// TODO: AUTO-REMOVE SEASONS WITHOUT EVENTS.
		foreach ( $div_ids as $div_id ) :

			$totals = array(
				'eventsattended' => 0,
			);

			foreach ( $performance_labels as $key => $value ) :
				$totals[ $key ] = 0;
			endforeach;

			// Get all events involving the official in current season.
			$args = array(
				'post_type'      => 'sp_event',
				'posts_per_page' => -1, // Retrieve all posts.
				'numberposts'    => -1,
				'post_status'    => array( 'publish' ),
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => 'sp_officials',
						'value'   => 'i:' . $this->ID . ';|"' . $this->ID . '";', // Format the search string to match the serialized array.
						'compare' => 'RLIKE',
					),
					array(
						'key'     => 'sp_format',
						'value'   => apply_filters( 'sportspress_competitive_event_formats', array( 'league' ) ),
						'compare' => 'IN',
					),
				),
				'tax_query'      => array(
					'relation' => 'AND',
				),
			);

			if ( $league_id ) :
				$args['tax_query'][] = array(
					'taxonomy' => 'sp_league',
					'field'    => 'term_id',
					'terms'    => $league_id,
				);
			endif;

			if ( $div_id ) :
				$args['tax_query'][] = array(
					'taxonomy' => 'sp_season',
					'field'    => 'term_id',
					'terms'    => $div_id,
				);
			endif;

			$args = apply_filters( 'otfs_officials_data_event_args', $args, $data, $div_id );

			$events = get_posts( $args );

			// Event loop.
			foreach ( $events as $i => $event ) :
				$results          = (array) get_post_meta( $event->ID, 'sp_results', true );
				$team_performance = (array) get_post_meta( $event->ID, 'sp_players', true );
				$timeline         = (array) get_post_meta( $event->ID, 'sp_timeline', true );
				$minutes          = get_post_meta( $event->ID, 'sp_minutes', true );
				$officials        = get_post_meta( $event->ID, 'sp_officials', true );

				if ( -1 !== $duty_id && isset( $officials[ $duty_id ] ) && ! in_array( $this->ID, $officials[ $duty_id ], true ) ) {
					continue; }

				if ( '' === $minutes ) {
					$minutes = get_option( 'sportspress_event_minutes', 90 );
				}
				// Increment events attended.
				++$totals['eventsattended'];

				// Add all team performance.
				foreach ( $team_performance as $team_id => $players ) :
					if ( is_array( $players ) ) :
						foreach ( $players as $player_performance ) {
							foreach ( $player_performance as $key => $value ) :
								if ( array_key_exists( $key, $totals ) ) :
									$add             = apply_filters( 'sportspress_player_performance_add_value', floatval( $value ), $key );
									$totals[ $key ] += $add;
								endif;
							endforeach;
						}

						$team_results = sp_array_value( $results, $team_id, array() );
						unset( $results[ $team_id ] );
					endif;
				endforeach;
				++$i;
			endforeach;

			// Add metrics to totals.
			$totals = array_merge( $metrics, $totals );

			// Generate array of placeholder values for each league.
			$placeholders[ $div_id ] = array();
			foreach ( $equations as $key => $value ) :
				$placeholders[ $div_id ][ $key ] = sp_solve( $value['equation'], $totals, $value['precision'] );
			endforeach;

			foreach ( $performance_labels as $key => $label ) :
				$placeholders[ $div_id ][ $key ] = apply_filters( 'otfs_officials_performance_table_placeholder', sp_array_value( $totals, $key, 0 ), $key );
			endforeach;

		endforeach;

		// Get labels by section.
		$args = array(
			'post_type'      => 'sp_statistic',
			'numberposts'    => 100,
			'posts_per_page' => 100,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$posts = get_posts( $args );

		$stats = array();

		foreach ( $posts as $post ) :
			if ( get_option( 'otfs_officials_statistics_mode', 'values' ) === 'icons' ) {
				$icon = apply_filters( 'sportspress_event_performance_icons', '', $post->ID, 1 );
				if ( '' !== $icon ) {
					$stats[ $post->post_name ] = $icon;
				} elseif ( has_post_thumbnail( $post ) ) {
						$icon                      = get_the_post_thumbnail( $post, 'sportspress-fit-mini', array( 'title' => sp_get_singular_name( $post ) ) );
						$stats[ $post->post_name ] = apply_filters( 'sportspress_event_performance_icons', $icon, $post->ID, 1 );
				} else {
					$stats[ $post->post_name ] = $post->post_title;
				}
			} else {
				$stats[ $post->post_name ] = $post->post_title;
			}
		endforeach;

		// Merge the data and placeholders arrays.
		$merged = array();

		foreach ( $placeholders as $season_id => $season_data ) :

			if ( 0 === $season_id ) {
				continue;
			}

			$season_name = sp_array_value( $season_names, (int) $season_id, '&nbsp;' );

			// Add season name to row.
			$merged[ $season_id ] = array(
				'name' => $season_name,
			);

			foreach ( $season_data as $key => $value ) :

				// Use static data if key exists and value is not empty, else use placeholder.
				if ( array_key_exists( $season_id, $data ) && array_key_exists( $key, $data[ $season_id ] ) && '' !== $data[ $season_id ][ $key ] ) :
					$value = $data[ $season_id ][ $key ];
				endif;

				$merged[ $season_id ][ $key ] = $value;

			endforeach;

		endforeach;

		$columns = array_merge( $performance_labels, $stats );

		$formats     = array();
		$total_types = array();

		$args = array(
			'post_type'      => array( 'sp_performance', 'sp_statistic' ),
			'numberposts'    => 100,
			'posts_per_page' => 100,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$posts = get_posts( $args );

		if ( $posts ) {
			$column_order    = array();
			$usecolumn_order = array();
			foreach ( $posts as $post ) {
				if ( array_key_exists( $post->post_name, $columns ) ) {
					$column_order[ $post->post_name ] = $columns[ $post->post_name ];
				}
				if ( in_array( $post->post_name, $usecolumns, true ) ) {
					$usecolumn_order[] = $post->post_name;
				}

				$format = get_post_meta( $post->ID, 'sp_format', true );
				if ( '' === $format ) {
					$format = 'number';
				}
				$formats[ $post->post_name ] = $format;

				$total_type = get_post_meta( $post->ID, 'sp_type', true );
				if ( '' === $total_type ) {
					$total_type = 'total';
				}
				$total_types[ $post->post_name ] = $total_type;
			}
			$columns    = array_merge( $column_order, $columns );
			$usecolumns = array_merge( $usecolumn_order, $usecolumns );
		}

		// Calculate total statistics.
		$career = array(
			'name' => esc_attr__( 'Total', 'sportspress' ),
			'team' => '-',
		);

		// Add values from all seasons for total-based statistics.
		foreach ( $merged as $season => $stats ) :
			if ( ! is_array( $stats ) ) {
				continue;
			}
			foreach ( $stats as $key => $value ) :
				if ( in_array( $key, array( 'name', 'team' ), true ) ) {
					continue;
				}
				$value          = floatval( $value );
				$add            = apply_filters( 'sportspress_player_performance_add_value', floatval( $value ), $key );
				$career[ $key ] = sp_array_value( $career, $key, 0 ) + $add;
			endforeach;
		endforeach;

		// Calculate average-based statistics from performance.
		foreach ( $posts as $post ) {
			$type = get_post_meta( $post->ID, 'sp_type', 'total' );
			if ( 'average' !== $type ) {
				continue;
			}
			$value = sp_array_value( $equations, $post->post_name, null );
			if ( null === $value || ! isset( $value['equation'] ) ) {
				continue;
			}
			$precision                  = sp_array_value( $value, 'precision', 0 );
			$career[ $post->post_name ] = sp_solve( $value['equation'], $totals, $precision );
		}

		// Filter career total placeholders.
		$career = apply_filters( 'otfs_officials_performance_table_placeholders', $career );

		// Get manually entered career totals.
		$manual_career = sp_array_value( $data, 0, array() );
		$manual_career = array_filter( $manual_career, 'sp_filter_non_empty' );

		// Add career totals to merged array.
		$merged[-1] = array_merge( $career, $manual_career );

		if ( $admin ) :
			$labels = array();
			if ( is_array( $usecolumns ) ) :
				foreach ( $usecolumns as $key ) :
					if ( 'team' === $key ) :
						$labels[ $key ] = esc_attr__( 'Team', 'sportspress' );
					elseif ( array_key_exists( $key, $columns ) ) :
						$labels[ $key ] = $columns[ $key ];
					endif;
				endforeach;
			endif;
			$placeholders[0] = $merged[-1];

			return array( $labels, $data, $placeholders, $merged, $leagues, $has_checkboxes, $formats, $total_types );
		else :
			if ( is_array( $usecolumns ) ) :
				foreach ( $columns as $key => $label ) :
					if ( ! in_array( $key, $usecolumns, true ) ) :
						unset( $columns[ $key ] );
					endif;
				endforeach;
			endif;

			$labels = array();

			$labels['name'] = esc_attr__( 'Season', 'sportspress' );

			if ( 'no' === get_option( 'otfs_officials_show_total', 'no' ) ) {
				unset( $merged[-1] );
			}

			// Convert to time notation.
			if ( in_array( 'time', $formats, true ) ) :
				foreach ( $merged as $season => $season_performance ) :
					foreach ( $season_performance as $performance_key => $performance_value ) :

						// Continue if not time format.
						if ( 'time' !== sp_array_value( $formats, $performance_key ) ) {
							continue;
						}

						$merged[ $season ][ $performance_key ] = sp_time_value( $performance_value );

					endforeach;
				endforeach;
			endif;

			$merged[0] = array_merge( $labels, $columns );

			return $merged;
		endif;
	}

	/**
	 * Returns formatted data for all leagues.
	 *
	 * @access public
	 * @return array
	 */
	public function statistics() {
		$terms = get_the_terms( $this->ID, 'sp_league' );

		$statistics = array();

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$statistics[ $term->term_id ] = $this->data( $term->term_id );
			}
		}

		return $statistics;
	}
}
