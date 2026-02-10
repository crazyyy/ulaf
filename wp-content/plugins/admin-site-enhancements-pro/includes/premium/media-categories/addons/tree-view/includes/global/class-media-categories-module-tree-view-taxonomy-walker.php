<?php
/**
 * Tree View Taxonomy Walker class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Taxonomy Walker for Tree View
 *
 * @version   1.1.1
 */
class Media_Categories_Module_Tree_View_Taxonomy_Walker extends Walker_Category {

	/**
	 * Wraps Taxonomy Term counts in a span, that can be styled using CSS.
	 *
	 * @since   1.1.1
	 *
	 * @param   string $output     Passed by reference. Used to append additional content.
	 * @param   object $category   The current term object.
	 * @param   int    $depth      Depth of the term in reference to parents. Default 0.
	 * @param   array  $args       An array of arguments. @see wp_terms_checklist().
	 * @param   int    $id         ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		// Record current output length so we can limit string manipulation to the
		// chunk that was just added for this term (prevents accidental changes to
		// previously-rendered terms).
		$start_length = strlen( $output );

		// Get output from parent Walker.
		parent::start_el( $output, $category, $depth, $args, $id );

		// Extract the chunk added by parent walker and modify only that chunk.
		$chunk = substr( $output, $start_length );

		// Add a stable DOM id so jsTree's state plugin can persist open/closed state.
		$chunk = $this->add_li_id( $chunk, $category );

		// Wrap (n) in a span.
		$chunk = $this->wrap_count( $chunk, $category );

		// Change link.
		$chunk = $this->change_filter_link( $chunk, $category );

		// Reassemble output.
		$output = substr( $output, 0, $start_length ) . $chunk;

	}

	/**
	 * Adds a stable id attribute to the <li> node for this term.
	 *
	 * jsTree relies on stable node IDs to persist and restore state.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $output Output chunk for the current term only.
	 * @param WP_Term $term   Term.
	 * @return string Output chunk.
	 */
	private function add_li_id( $output, $term ) {

		$term_id = absint( $term->term_id );
		if ( ! $term_id ) {
			return $output;
		}

		$li_id = 'asenha-media-cat-' . $term_id;

		// Insert id attribute right after "<li ".
		// Core walker output begins with something like:
		// <li class="cat-item cat-item-123">...
		$output = preg_replace(
			'/<li\\s+class=([\'"])/',
			'<li id="' . esc_attr( $li_id ) . '" class=$1',
			$output,
			1
		);

		return $output;

	}

	/**
	 * Wrap the Attachment Count in a <span> for styling
	 *
	 * @since   1.1.1
	 *
	 * @param   string  $output     Output.
	 * @param   WP_Term $term       Term.
	 * @return  string                  Output
	 */
	private function wrap_count( $output, $term ) {

		$output  = str_replace( '</a> (', ' <span class="count" data-term-id="' . $term->term_id . '">', $output );
		$output .= '</span></a>';
		$output  = str_replace( ")\n</span>", '</span>', $output );

		return $output;

	}

	/**
	 * Replace the Term's link with a contextual link, depending on the screen
	 * we're on.
	 *
	 * @since   1.1.1
	 *
	 * @param   string  $output     Output.
	 * @param   WP_Term $term       Term.
	 * @return  string                  Output
	 */
	private function change_filter_link( $output, $term ) {

		// Replace URL with upload.php version.
		$output = str_replace( get_term_link( $term ), $this->get_term_filter_link( $term ), $output );

		// Return.
		return $output;

	}

	/**
	 * Returns the Taxonomy Term's URL for the given Media View in the WordPress Administration
	 *
	 * @since   1.1.1
	 *
	 * @param   WP_Term $term   Taxonomy Term.
	 * @return  string              URL
	 */
	private function get_term_filter_link( $term ) {

		// Build URL arguments.
		$args = array(
			'mode'          => Media_Categories_Module()->get_class( 'common' )->get_media_view(),
			$term->taxonomy => $term->slug,
		);

		// Define the filters to append to the URL.
		$conditions = array(
			'attachment-filter',
			'm',
			'orderby',
			'order',
		);

		// Sanitize request.
		$request = map_deep( $_REQUEST, 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification

		foreach ( $conditions as $condition ) {
			if ( isset( $request[ $condition ] ) ) {
				$args[ $condition ] = $request[ $condition ];
			}
		}

		// Build and return the URL.
		$url = add_query_arg( $args, 'upload.php' );

		return $url;

	}

}
