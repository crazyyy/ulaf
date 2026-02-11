<?php
/**
 * @package   Nextgenthemes\TweakMaster
 * @link      https://nexgenthemes.com
 * @copyright 2025 Nicolas Jonas
 * @license   GPL-3.0
 */

declare(strict_types = 1);

namespace Nextgenthemes\TweakMaster;

add_filter( 'wp_revisions_to_keep', __NAMESPACE__ . '\limit_revisions', 15, 2 );
add_filter( 'wp_revisions_to_keep', __NAMESPACE__ . '\limit_all_revisions', 20 );

/**
 * Filters the number of revisions to save for the given post.
 *
 * Overrides the value of WP_POST_REVISIONS.
 *
 * @param int      $num  Number of revisions to store.
 * @param \WP_Post $post Post object.
 */
function limit_revisions( int $num, \WP_Post $post ): int {

	foreach ( options() as $option_key => $option_value ) {

		if ( 'revisions-limit-' . $post->post_type === $option_key
			&& $option_value >= 0
		) {
			return $option_value;
		}
	}

	return $num;
}

/**
 * Filters the number of revisions to save for everything.
 *
 * Overrides the value of WP_POST_REVISIONS and other revisions_limit options.
 *
 * @param int      $num  Number of revisions to store.
 */
function limit_all_revisions( int $num ): int {

	if ( options()['revisions-limit-all'] >= 0 ) {
		return options()['revisions-limit-all'];
	}

	return $num;
}
