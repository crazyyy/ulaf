<?php

namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB Queries class
 */
class DB_Queries {
	/**
	 * Remnant records
	 *
	 * @var bool
	 */
	protected $record_remnant;

	protected $batch_size = 500;

	/**
	 * Constructor
	 */
	public function __construct() {
		$options          = get_option( DB_Cleaner::OPTION_NAME, [] );
		$this->batch_size = ! empty( $options['batch_size'] ) ? (int) $options['batch_size'] : 500;
	}

	/**
	 * Function to clean revisions
	 *
	 * @return array
	 */
	public function clean_revisions() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s LIMIT %d", 'revision', $this->batch_size ) ); // phpcs:ignore

		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No revision items found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean auto draft in db
	 *
	 * @return array
	 */
	public function clean_auto_draft() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s LIMIT %d", 'auto-draft', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No auto drafts found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean trashed post in db
	 *
	 * @return array
	 */
	public function clean_trashed_post() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s LIMIT %d", 'trash', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No trashed posts found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean orphaned postmeta in db
	 *
	 * @return array
	 */
	public function clean_orphaned_postmeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts) LIMIT %d", $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No orphaned post meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean duplicate postmeta in db
	 *
	 * @return array
	 */
	public function clean_duplicate_postmeta() {
		global $wpdb;

		$query = $wpdb->get_col( "SELECT DISTINCT pm1.meta_id FROM $wpdb->postmeta pm1 INNER JOIN $wpdb->postmeta pm2 WHERE pm1.meta_id < pm2.meta_id AND pm1.meta_key = pm2.meta_key AND pm1.meta_value = pm2.meta_value AND pm1.post_id = pm2.post_id ORDER BY meta_id DESC" ); // phpcs:ignore
		if ( $query ) {
			$query_count = count( $query );
			if ( $this->batch_size < $query_count ) {
				array_splice( $query, $this->batch_size );
				$query_count = $this->batch_size;
			}
			$placeholder = implode( ',', array_fill( 0, count( $query ), '%d' ) );
			$query       = array_map( 'absint', $query );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_id IN ($placeholder)", $query ) ); // phpcs:ignore

			return [
				'status' => 'success',
				'count'  => $query_count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No duplicate post meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean empty postmeta in db
	 *
	 * @return array
	 */
	public function clean_empty_postmeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_value = %s LIMIT %d", '', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No empty post meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean oembed cache in db
	 *
	 * @return array
	 */
	public function clean_oembed_cache() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s LIMIT %d", '%_oembed_%', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No oEmbed cache found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean comments in db
	 *
	 * @param string $status Status of comment to clean.
	 *
	 * @return array
	 */
	public function clean_comments( $status = 'unapproved' ) {
		global $wpdb;

		if ( in_array( $status, [ 'unapproved', 'spam', 'trash' ], true ) ) {
			return [
				'status'  => 'error',
				// translators: %s is the status of the comment.
				'message' => esc_html( sprintf( __( 'No %s comments found.', 'admin-optimizer' ), $status ) ),
			];
		}

		$comment_status = [
			'unapproved' => '0',
			'spam'       => 'spam',
			'trash'      => 'trash',
		];
		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s LIMIT %d", $comment_status[ $status ], $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				// translators: %s is the status of the comment.
				'message' => esc_html( sprintf( __( 'No %s comments found.', 'admin-optimizer' ), $status ) ),
			];
		}
	}

	/**
	 * Function to clean duplicate commentmeta in db
	 *
	 * @return array
	 */
	public function clean_duplicate_commentmeta() {
		global $wpdb;

		$query = $wpdb->get_col( "SELECT DISTINCT cm1.meta_id FROM $wpdb->commentmeta cm1 INNER JOIN $wpdb->commentmeta cm2 WHERE cm1.meta_id > cm2.meta_id AND cm1.meta_key = cm2.meta_key AND cm1.meta_value = cm2.meta_value AND cm1.comment_id = cm2.comment_id ORDER BY meta_id DESC" ); // phpcs:ignore
		if ( $query ) {
			$query_count = count( $query );
			if ( $this->batch_size < $query_count ) {
				array_splice( $query, $this->batch_size );
				$query_count = $this->batch_size;
			}
			$placeholder = implode( ',', array_fill( 0, count( $query ), '%d' ) );
			$query       = array_map( 'absint', $query );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE meta_id IN ($placeholder)", $query ) ); // phpcs:ignore

			return [
				'status' => 'success',
				'count'  => $query_count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No duplicate comment meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean orphaned commentmeta in db
	 *
	 * @return array
	 */
	public function clean_orphaned_commentmeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments) LIMIT %d", $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No orphaned comment meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean empty commentmeta in db
	 *
	 * @return array
	 */
	public function clean_empty_commentmeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE meta_value = %s LIMIT %d", '', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No empty comment meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean pingbacks in db
	 *
	 * @return array
	 */
	public function clean_pingbacks() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_type = %s LIMIT %d", 'pingback', $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No pingbacks found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean unused terms in db
	 *
	 * @return array
	 */
	public function clean_unused_terms() {
		global $wpdb;

		$taxonomies          = [ 'post_tag', 'category' ];
		$included_taxonomies = apply_filters( 'adminoptim_included_taxonomies', $taxonomies );
		if ( ! is_array( $included_taxonomies ) ) {
			$included_taxonomies = [ 'post_tag', 'category' ];
		}

		$placeholder  = implode( ',', array_fill( 0, count( $included_taxonomies ), '%s' ) );
		$prepared_arr = array_merge( [ 0 ], $included_taxonomies );

		$excluded_termids    = $this->get_excluded_termids( $included_taxonomies );
		$termids_placeholder = '';
		if ( ! empty( $excluded_termids ) ) {
			$termids_placeholder = implode( ',', array_fill( 0, count( $excluded_termids ), '%d' ) );
			$excluded_termids    = array_map( 'absint', $excluded_termids );
			$prepared_arr        = array_merge( $prepared_arr, $excluded_termids );
		}
		$query = $wpdb->get_results( $wpdb->prepare( "SELECT tt.term_taxonomy_id, t.term_id, tt.taxonomy FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.count = %d AND tt.taxonomy IN ($placeholder) AND t.term_id NOT IN ($termids_placeholder)", $prepared_arr ) ); // phpcs:ignore

		if ( $query ) {
			$count = 0;
			foreach ( $query as $tax ) {
				$wpdb->delete( // phpcs:ignore
					$wpdb->term_taxonomy,
					[
						'term_taxonomy_id' => (int) $tax->term_taxonomy_id,
						'term_id'          => (int) $tax->term_id,
					]
				);
				$wpdb->delete( $wpdb->terms, [ 'term_id' => (int) $tax->term_id ] ); // phpcs:ignore
				$wpdb->delete( $wpdb->term_relationships, [ 'term_taxonomy_id' => (int) $tax->term_taxonomy_id ] ); // phpcs:ignore
				$wpdb->delete( $wpdb->termmeta, [ 'term_id' => (int) $tax->term_id ] ); // phpcs:ignore

				++$count;
				if ( $count >= $this->batch_size ) {
					break;
				}
			}

			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No unused terms found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Get excluded termids
	 *
	 * @param array $taxonomies Array of taxonomies.
	 * @return array
	 */
	private function get_excluded_termids( array $taxonomies ) {
		$term_ids = [];
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$default = get_option( 'default_' . $taxonomy, 0 );
				if ( $default > 0 ) {
					$term_ids[] = $default;
				}
			}
		}

		$parent_term_ids = $this->get_parent_termids();
		if ( ! is_array( $parent_term_ids ) ) {
			$parent_term_ids = [];
		}
		$term_ids = array_merge( $term_ids, $parent_term_ids );
		return apply_filters( 'adminoptimizer_excluded_termids', $term_ids );
	}

	/**
	 * Get parent termids
	 *
	 * @return array
	 */
	private function get_parent_termids() {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare( "SELECT tt.parent FROM $wpdb->term_taxonomy tt INNER JOIN $wpdb->terms t ON tt.term_id = t.term_id WHERE tt.parent > %d", 0 ) ); // phpcs:ignore
	}

	/**
	 * Function to clean duplicate termmeta in db
	 *
	 * @return array
	 */
	public function clean_duplicate_termmeta() {
		global $wpdb;

		$query = $wpdb->get_col( "SELECT DISTINCT tm1.meta_id FROM $wpdb->termmeta tm1 INNER JOIN $wpdb->termmeta tm2 WHERE tm1.meta_id < tm2.meta_id AND tm1.meta_key = tm2.meta_key AND tm1.meta_value = tm2.meta_value AND tm1.term_id = tm2.term_id ORDER BY meta_id DESC" ); // phpcs:ignore
		if ( $query ) {
			$query_count = count( $query );
			if ( $this->batch_size < $query_count ) {
				array_splice( $query, $this->batch_size );
				$query_count = $this->batch_size;
			}
			$placeholder = implode( ',', array_fill( 0, count( $query ), '%d' ) );
			$query       = array_map( 'absint', $query );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_id IN ($placeholder)", $query ) ); // phpcs:ignore

			return [
				'status' => 'success',
				'count'  => $query_count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No duplicate comment meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean orphaned termmeta in db
	 *
	 * @return array
	 */
	public function clean_orphaned_termmeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms) LIMIT %d", $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No orphaned comment meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean orphaned term relationship in db
	 *
	 * @return array
	 */
	public function clean_orphaned_term_rs() {
		global $wpdb;

		$excluded_taxonomies = apply_filters( 'adminoptimizer_excluded_taxonomies', [ 'link_category' ] );
		if ( ! is_array( $excluded_taxonomies ) ) {
			$excluded_taxonomies = [ 'link_category' ];
		}
		$placeholder = implode( ',', array_fill( 0, count( $excluded_taxonomies ), '%s' ) );

		$query = $wpdb->get_results( $wpdb->prepare( "SELECT tr.object_id, tr.term_taxonomy_id, tt.term_id, tt.taxonomy FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy NOT IN ($placeholder) AND tr.object_id NOT IN (SELECT ID FROM $wpdb->posts)", $excluded_taxonomies ) ); // phpcs:ignore
		if ( $query ) {
			$count = 0;
			foreach ( $query as $tax ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $tax->object_id, $tax->term_taxonomy_id ) ); // phpcs:ignore
				++$count;
				if ( $count >= $this->batch_size ) {
					break;
				}
			}
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No orphaned term relationship found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean duplicate usermeta in db
	 *
	 * @return array
	 */
	public function clean_duplicate_usermeta() {
		global $wpdb;

		$query = $wpdb->get_col( "SELECT DISTINCT um1.umeta_id FROM $wpdb->usermeta um1 INNER JOIN $wpdb->usermeta um2 WHERE um1.umeta_id < um2.umeta_id AND um1.meta_key = um2.meta_key AND um1.meta_value = um2.meta_value AND um1.user_id = um2.user_id ORDER BY umeta_id DESC" ); // phpcs:ignore
		if ( $query ) {
			$query_count = count( $query );
			if ( $this->batch_size < $query_count ) {
				array_splice( $query, $this->batch_size );
				$query_count = $this->batch_size;
			}
			$placeholder = implode( ',', array_fill( 0, count( $query ), '%d' ) );
			$query       = array_map( 'absint', $query );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE umeta_id IN ($placeholder)", $query ) ); // phpcs:ignore

			return [
				'status' => 'success',
				'count'  => $query_count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No duplicate user meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Function to clean orphaned usermeta in db
	 *
	 * @return array
	 */
	public function clean_orphaned_usermeta() {
		global $wpdb;

		$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT user_id FROM $wpdb->users) LIMIT %d", $this->batch_size ) ); // phpcs:ignore
		if ( is_numeric( $count ) && $count >= 1 ) {
			return [
				'status' => 'success',
				'count'  => $count,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'No orphaned user meta found.', 'admin-optimizer' ),
			];
		}
	}

	/**
	 * Optimize database
	 *
	 * @return array
	 */
	public function optimize_database() {
		global $wpdb;

		$query  = $wpdb->get_col( 'SHOW TABLES' ); // phpcs:ignore
		$result = false;
		if ( $query ) {
			$tables = implode( ',', $query );
			$result = $wpdb->query( "OPTIMIZE TABLE $tables" ); // phpcs:ignore
		}
		$time = time();
		$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time );
		update_option( 'adminoptim_last_optimize_db_time', $time, false );
		if ( $result ) {
			return [
				'status'   => 'success',
				'datetime' => $date,
			];
		} else {
			return [
				'status'  => 'error',
				'message' => __( 'Database not optimized.', 'admin-optimizer' ),
			];
		}
	}
}
