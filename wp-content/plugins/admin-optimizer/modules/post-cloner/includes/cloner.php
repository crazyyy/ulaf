<?php
namespace Yipresser\AdminOptimizer\Modules\Post_Cloner;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Cloner {

	/**
	 * Function for cloning post
	 *
	 * @param int $parent_id Post id.
	 *
	 * @return int|\WP_Error
	 */
	private function clone_post( $parent_id ) {
		$parent       = get_post( $parent_id );
		$parent_metas = get_post_meta( $parent_id );

		$clone_meta = [];

		if ( is_array( $parent_metas ) ) {
			foreach ( $parent_metas as $key => $value ) {
				$clone_meta[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		$clone_meta = apply_filters( 'adminoptim_clone_post_meta', $clone_meta );

		$clone                          = [];
		$clone['post_title']            = $parent->post_title . ' (Clone)';
		$clone['post_content']          = $parent->post_content;
		$clone['post_author']           = $parent->post_author;
		$clone['post_content_filtered'] = $parent->post_content_filtered;
		$clone['post_excerpt']          = $parent->post_excerpt;
		$clone['post_type']             = $parent->post_type;
		$clone['post_parent']           = $parent->post_parent;
		$clone['meta_input']            = $clone_meta;
		$clone_id                       = wp_insert_post( wp_slash( $clone ) );

		if ( 0 < $clone_id && ! is_wp_error( $clone_id ) ) {
			// copy the taxonomies over.
			$this->clone_taxonomies( $clone_id, $parent );

			do_action( 'adminoptim_after_clone', $clone_id, $parent_id );
		}

		return $clone_id;
	}

	/**
	 * Function for cloning taxonomies
	 *
	 * @param int      $new_post_id   Post id.
	 * @param \WP_Post $old_post Post object.
	 *
	 * @return void
	 */
	private function clone_taxonomies( $new_post_id, $old_post ): void {
		$taxonomies = get_object_taxonomies( $old_post->post_type );

		foreach ( $taxonomies as $taxonomy ) {
			// remove taxonomy on original post first.
			wp_set_object_terms( $new_post_id, null, $taxonomy );

			$post_terms = wp_get_object_terms( $old_post->ID, $taxonomy, [ 'orderby' => 'term_order' ] );
			$terms      = [];
			$count      = count( $post_terms );
			for ( $i = 0; $i < $count; $i++ ) {
				$terms[] = $post_terms[ $i ]->slug;
			}

			wp_set_object_terms( $new_post_id, $terms, $taxonomy );
		}
	}

	/**
	 * Function for cloning post metas
	 *
	 * @param int $new_id Post id.
	 * @param int $old_id Post id.
	 *
	 * @return void
	 */
	public function clone_post_metas( $new_id, $old_id ): void {

		$post_metas = get_post_meta( $old_id );
		if ( is_array( $post_metas ) ) {
			foreach ( $post_metas as $key => $value ) {
				if ( str_contains( $key, '_republish_' ) ) {
					continue;
				}
				update_post_meta( $new_id, $key, maybe_unserialize( $value[0] ) );
			}
		}
	}
}
