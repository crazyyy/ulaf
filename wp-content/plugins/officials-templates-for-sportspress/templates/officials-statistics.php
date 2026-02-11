<?php
/**
 * Official Statistics.
 *
 * @author      savvasha
 * @package     OTFS/Templates
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( get_option( 'sportspress_officials_show_statistics', 'yes' ) === 'no' ) {
	return;
}

if ( ! isset( $official_id ) ) {
	$official_id = get_the_ID();
}

$official = new OTFS_Officials( $official_id );

$scrollable         = 'yes' === get_option( 'sportspress_enable_scrollable_tables', 'yes' ) ? true : false;
$show_per_league    = 'yes' === get_option( 'otfs_officials_show_per_league', 'yes' ) ? true : false;
$show_career_totals = 'yes' === get_option( 'otfs_officials_show_career_total', 'no' ) ? true : false;
$leagues            = array_filter( (array) get_the_terms( $official_id, 'sp_league' ) );

// If no Leagues are selected by the user, then all Leagues should be included.
if ( count( $leagues ) < 1 ) {
	$leagues = get_terms(
		array(
			'taxonomy'   => 'sp_league',
			'hide_empty' => true,
		)
	);
}

// Sort Leagues by User Defined Order (PHP5.2 supported).
foreach ( $leagues as $key => $league ) {
	$leagues[ $key ]->sp_order = get_term_meta( $league->term_id, 'sp_order', true );
}
if ( ! function_exists( 'otfs_sort_by_order' ) ) {
	/**
	 * Sorts an array of objects based on the 'sp_order' property.
	 *
	 * This function compares two objects based on their 'sp_order' property,
	 * casting them to integers for numerical comparison. It is suitable for
	 * use with the `usort` function to sort an array of objects.
	 *
	 * @param object $a The first object for comparison.
	 * @param object $b The second object for comparison.
	 * @return int Returns a negative value if $a->sp_order is less than $b->sp_order,
	 *             a positive value if $a->sp_order is greater than $b->sp_order,
	 *             and 0 if they are equal.
	 */
	function otfs_sort_by_order( $a, $b ) {
		return (int) $a->sp_order - (int) $b->sp_order;
	}
}
usort( $leagues, 'otfs_sort_by_order' );

$selected_duties = $official->filtered_duties();

// Loop through statistics for each league.
if ( is_array( $leagues ) ) :
	if ( $show_per_league ) :
		foreach ( $leagues as $league ) :
			if ( empty( $selected_duties ) ) {
				$caption = $league->name;

				$args = array(
					'data'       => $official->stats( $league->term_id, -1, false ),
					'caption'    => $caption,
					'scrollable' => $scrollable,
					'league_id'  => $league->term_id,
					'hide_teams' => true,
				);
				sp_get_template( 'otfs-statistics-league.php', $args, '', OTFS_PLUGIN_DIR . 'templates/' );
			} else {
				foreach ( $selected_duties as $selected_duty ) {
					$duty    = get_term_by( 'id', $selected_duty, 'sp_duty' );
					$caption = $league->name . ' ' . esc_attr__( 'as', 'officials-templates-for-sportspress' ) . ' ' . $duty->name;

					$args = array(
						'data'       => $official->stats( $league->term_id, $selected_duty, false ),
						'caption'    => $caption,
						'scrollable' => $scrollable,
						'league_id'  => $league->term_id,
						'hide_teams' => true,
					);
					sp_get_template( 'otfs-statistics-league.php', $args, '', OTFS_PLUGIN_DIR . 'templates/' );
				}
			}
		endforeach;
	endif;

	if ( $show_career_totals ) {
		if ( empty( $selected_duties ) ) {
			$caption = esc_attr__( 'Career Total', 'sportspress' );

			$args = array(
				'data'       => $official->stats( 0, -1, false ),
				'caption'    => $caption,
				'scrollable' => $scrollable,
				'hide_teams' => true,
			);
			sp_get_template( 'otfs-statistics-league.php', $args, '', OTFS_PLUGIN_DIR . 'templates/' );
		} else {
			foreach ( $selected_duties as $selected_duty ) {
				$duty    = get_term_by( 'id', $selected_duty, 'sp_duty' );
				$caption = esc_attr__( 'Career Total', 'sportspress' ) . ' ' . esc_attr__( 'as', 'officials-templates-for-sportspress' ) . ' ' . $duty->name;

				$args = array(
					'data'       => $official->stats( 0, $selected_duty, false ),
					'caption'    => $caption,
					'scrollable' => $scrollable,
					'hide_teams' => true,
				);
				sp_get_template( 'otfs-statistics-league.php', $args, '', OTFS_PLUGIN_DIR . 'templates/' );
			}
		}
	}
endif;
