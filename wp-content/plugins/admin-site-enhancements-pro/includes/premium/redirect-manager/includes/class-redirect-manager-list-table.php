<?php
/**
 * Redirect Manager List Table
 *
 * Handles columns, filters, sorting, and search for the redirects listing
 *
 * @since 8.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for managing redirect list table
 */
class ASENHA_Redirect_Manager_List_Table {

	/**
	 * Initialize the class
	 *
	 * @since 8.1.0
	 */
	public function init() {
		// Modify columns
		add_filter( 'manage_asenha_redirect_posts_columns', array( $this, 'set_custom_columns' ) );
		add_action( 'manage_asenha_redirect_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );
		
		// Make columns sortable
		add_filter( 'manage_edit-asenha_redirect_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_column_sorting' ) );
		
		// Add filters
		add_action( 'restrict_manage_posts', array( $this, 'add_table_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_table_filters' ) );
		
		// Custom search
		add_filter( 'posts_search', array( $this, 'custom_search' ), 10, 2 );
		
		// Customize row actions
		add_filter( 'post_row_actions', array( $this, 'customize_row_actions' ), 10, 2 );
		
		// Add inline data for quick edit
		add_action( 'manage_asenha_redirect_posts_custom_column', array( $this, 'add_inline_data' ), 10, 2 );
	}

	/**
	 * Define custom columns for redirects
	 *
	 * @since 8.1.0
	 * @param array $columns The default columns
	 * @return array Modified columns
	 */
	public function set_custom_columns( $columns ) {
		// Remove default columns
		unset( $columns['date'] );
		unset( $columns['title'] );
		
		// Add custom columns
		$new_columns = array(
			'cb'                => $columns['cb'],
			'redirect_from'     => __( 'Redirect From', 'admin-site-enhancements' ),
			'redirect_to'       => __( 'Redirect To', 'admin-site-enhancements' ),
			'status_code'       => __( 'HTTP Status Code', 'admin-site-enhancements' ),
			'group'             => __( 'Group', 'admin-site-enhancements' ),
			'notes'             => __( 'Notes', 'admin-site-enhancements' ),
			'custom_message'    => __( 'Custom Message', 'admin-site-enhancements' ),
			'date'              => __( 'Date', 'admin-site-enhancements' ),
		);
		
		return $new_columns;
	}

	/**
	 * Render custom column content
	 *
	 * @since 8.1.0
	 * @param string $column The column name
	 * @param int $post_id The post ID
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'redirect_from':
				$redirect_from = get_post_meta( $post_id, '_redirect_from', true );
				$is_regex = get_post_meta( $post_id, '_redirect_from_regex', true );
				
				if ( ! empty( $redirect_from ) ) {
					if ( $is_regex ) {
						echo ' <span class="asenha-redirect-badge">' . esc_html__( 'Regex', 'admin-site-enhancements' ) . '</span>';
					}
					
					// Check if redirect_from contains wildcard or regex pattern symbols: *, $, ^, [, ], (, ), {, }, \, |, ?, +
					$has_pattern_symbols = preg_match( '/[\*\$\^\[\]\(\)\{\}\\\|\?\+]/', $redirect_from );
					
					// If regex mode is enabled or contains pattern symbols, use code tag
					if ( $is_regex || $has_pattern_symbols ) {
						echo '<code>' . esc_html( $redirect_from ) . '</code>';
					} else {
						// Regular path - create clickable link
						// If it's already a full URL, use as-is; otherwise prepend home_url()
						if ( preg_match( '/^https?:\/\//', $redirect_from ) ) {
							$full_url = $redirect_from;
						} else {
							$full_url = home_url( $redirect_from );
						}
						
						$display_path = strlen( $redirect_from ) > 50 ? substr( $redirect_from, 0, 50 ) . '...' : $redirect_from;
						echo '<a href="' . esc_url( $full_url ) . '" target="_blank" rel="noopener" title="' . esc_attr( $redirect_from ) . '">' . esc_html( $display_path ) . '</a>';
					}
				} else {
					echo '<span class="asenha-redirect-empty-value">—</span>';
				}
				break;
				
			case 'redirect_to':
				$status_code = get_post_meta( $post_id, '_redirect_http_status_code', true );
				$status_code = $status_code ? absint( $status_code ) : 302;
				
				// For status codes that don't redirect, show empty value
				if ( in_array( $status_code, array( 400, 401, 403, 404, 410, 500, 501, 503 ), true ) ) {
					echo '<span class="asenha-redirect-empty-value">—</span>';
				} else {
					$redirect_to = get_post_meta( $post_id, '_redirect_to', true );
					if ( ! empty( $redirect_to ) ) {
						// Check if redirect_to contains wildcard or regex pattern symbols
						$has_pattern_symbols = preg_match( '/[\*\$\^\[\]\(\)\{\}\\\|\?\+]/', $redirect_to );
						
						// If contains pattern symbols, use code tag instead of hyperlink
						if ( $has_pattern_symbols ) {
							echo '<code>' . esc_html( $redirect_to ) . '</code>';
						} else {
							// Regular hyperlink
							$display_url = strlen( $redirect_to ) > 50 ? substr( $redirect_to, 0, 50 ) . '...' : $redirect_to;
							echo '<a href="' . esc_url( $redirect_to ) . '" target="_blank" rel="noopener" title="' . esc_attr( $redirect_to ) . '">' . esc_html( $display_url ) . '</a>';
						}
						
						// Show "URL parameters not passed" indicator if enabled
						$strip_query_params = get_post_meta( $post_id, '_redirect_strip_query_params', true );
						$redirect_codes = array( 301, 302, 303, 304, 307, 308 );
						if ( $strip_query_params && in_array( $status_code, $redirect_codes, true ) ) {
							echo '<span class="asenha-query-params-status-label">' . esc_html__( 'URL parameters not passed', 'admin-site-enhancements' ) . '</span>';
						}
					} else {
						echo '<span class="asenha-redirect-empty-value">—</span>';
					}
				}
				break;
				
				case 'status_code':
					$status_code = get_post_meta( $post_id, '_redirect_http_status_code', true );
					$status_code = $status_code ? absint( $status_code ) : 302;
					
					// Color code based on status type
					$status_class = ( $status_code >= 400 ) ? 'is-error' : 'is-redirect';
					
					echo '<span class="asenha-redirect-status-code ' . esc_attr( $status_class ) . '">' . esc_html( $status_code ) . '</span> ';
					echo '<span class="asenha-redirect-status-label">' . esc_html( $this->get_status_code_label( $status_code ) ) . '</span>';
					break;
				
				case 'group':
					$group = get_post_meta( $post_id, '_redirect_group', true );
					if ( ! empty( $group ) ) {
						echo esc_html( $group );
					} else {
						echo '<span class="asenha-redirect-empty-value">—</span>';
					}
					break;
				
				case 'notes':
					$notes = get_post_meta( $post_id, '_redirect_notes', true );
					if ( ! empty( $notes ) ) {
						// Truncate long notes
						$display_notes = strlen( $notes ) > 50 ? substr( $notes, 0, 50 ) . '...' : $notes;
						echo '<span title="' . esc_attr( $notes ) . '">' . wp_kses_post( $display_notes ) . '</span>';
					} else {
						echo '<span class="asenha-redirect-empty-value">—</span>';
					}
					break;
				
			case 'custom_message':
				$status_code = get_post_meta( $post_id, '_redirect_http_status_code', true );
				$status_code = $status_code ? absint( $status_code ) : 302;
				
				// Define which status codes should show custom message
				// Show for error codes except 404, and not for redirect codes (3xx)
				$error_codes_with_message = array( 400, 401, 403, 410, 500, 501, 503 );
				
				// Show message only for specific error codes (exclude 404 and redirect codes)
				if ( ! in_array( $status_code, $error_codes_with_message, true ) ) {
					echo '<span class="asenha-redirect-empty-value">—</span>';
				} else {
					$message = get_post_meta( $post_id, '_redirect_message', true );
					if ( ! empty( $message ) ) {
						// Truncate long messages
						$display_message = strlen( $message ) > 50 ? substr( $message, 0, 50 ) . '...' : $message;
						echo '<span title="' . esc_attr( $message ) . '">' . wp_kses_post( $display_message ) . '</span>';
					} else {
						echo '<span class="asenha-redirect-empty-value">—</span>';
					}
				}
				break;
		}
	}

	/**
	 * Get status code label
	 *
	 * @since 8.1.0
	 * @param int $code The HTTP status code
	 * @return string The label
	 */
	private function get_status_code_label( $code ) {
		$labels = array(
			301 => __( 'Moved Permanently', 'admin-site-enhancements' ),
			302 => __( 'Found', 'admin-site-enhancements' ),
			303 => __( 'See Other', 'admin-site-enhancements' ),
			304 => __( 'Not Modified', 'admin-site-enhancements' ),
			307 => __( 'Temporary Redirect', 'admin-site-enhancements' ),
			308 => __( 'Permanent Redirect', 'admin-site-enhancements' ),
			400 => __( 'Bad Request', 'admin-site-enhancements' ),
			401 => __( 'Unauthorized', 'admin-site-enhancements' ),
			403 => __( 'Forbidden', 'admin-site-enhancements' ),
			404 => __( 'Not Found', 'admin-site-enhancements' ),
			410 => __( 'Gone', 'admin-site-enhancements' ),
			500 => __( 'Internal Server Error', 'admin-site-enhancements' ),
			501 => __( 'Not Implemented', 'admin-site-enhancements' ),
			503 => __( 'Service Unavailable', 'admin-site-enhancements' ),
		);
		
		return isset( $labels[ $code ] ) ? $labels[ $code ] : '';
	}

	/**
	 * Make columns sortable
	 *
	 * @since 8.1.0
	 * @param array $columns The sortable columns
	 * @return array Modified sortable columns
	 */
	public function set_sortable_columns( $columns ) {
		$columns['redirect_from'] = 'redirect_from';
		$columns['redirect_to'] = 'redirect_to';
		$columns['status_code'] = 'status_code';
		$columns['group'] = 'group';
		$columns['notes'] = 'notes';
		$columns['custom_message'] = 'custom_message';
		
		return $columns;
	}

	/**
	 * Handle column sorting
	 *
	 * @since 8.1.0
	 * @param WP_Query $query The query object
	 */
	public function handle_column_sorting( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		
		if ( 'asenha_redirect' !== $query->get( 'post_type' ) ) {
			return;
		}
		
		$orderby = $query->get( 'orderby' );
		
		$meta_keys = array(
			'redirect_from' => '_redirect_from',
			'redirect_to'   => '_redirect_to',
			'status_code'   => '_redirect_http_status_code',
			'group'         => '_redirect_group',
			'notes'         => '_redirect_notes',
			'custom_message' => '_redirect_message',
		);
		
		if ( isset( $meta_keys[ $orderby ] ) ) {
			$query->set( 'meta_key', $meta_keys[ $orderby ] );
			$query->set( 'orderby', 'meta_value' );
			
			// Use numeric ordering for status code
			if ( 'status_code' === $orderby ) {
				$query->set( 'orderby', 'meta_value_num' );
			}
		}
	}

	/**
	 * Add filter dropdowns
	 *
	 * @since 8.1.0
	 * @param string $post_type The post type
	 */
	public function add_table_filters( $post_type ) {
		if ( 'asenha_redirect' !== $post_type ) {
			return;
		}
		
		// HTTP Status Code filter
		$this->render_status_code_filter();
		
		// Group filter
		$this->render_group_filter();
	}

	/**
	 * Render HTTP status code filter dropdown
	 *
	 * @since 8.1.0
	 */
	private function render_status_code_filter() {
		$current_status = isset( $_GET['redirect_status_code'] ) ? absint( $_GET['redirect_status_code'] ) : '';
		
		$status_codes = array(
			301 => '301 ' . __( 'Moved Permanently', 'admin-site-enhancements' ),
			302 => '302 ' . __( 'Found', 'admin-site-enhancements' ),
			303 => '303 ' . __( 'See Other', 'admin-site-enhancements' ),
			304 => '304 ' . __( 'Not Modified', 'admin-site-enhancements' ),
			307 => '307 ' . __( 'Temporary Redirect', 'admin-site-enhancements' ),
			308 => '308 ' . __( 'Permanent Redirect', 'admin-site-enhancements' ),
			400 => '400 ' . __( 'Bad Request', 'admin-site-enhancements' ),
			401 => '401 ' . __( 'Unauthorized', 'admin-site-enhancements' ),
			403 => '403 ' . __( 'Forbidden', 'admin-site-enhancements' ),
			404 => '404 ' . __( 'Not Found', 'admin-site-enhancements' ),
			410 => '410 ' . __( 'Gone', 'admin-site-enhancements' ),
			500 => '500 ' . __( 'Internal Server Error', 'admin-site-enhancements' ),
			501 => '501 ' . __( 'Not Implemented', 'admin-site-enhancements' ),
			503 => '503 ' . __( 'Service Unavailable', 'admin-site-enhancements' ),
		);
		
		echo '<select name="redirect_status_code" id="redirect_status_code">';
		echo '<option value="">' . esc_html__( 'All Status Codes', 'admin-site-enhancements' ) . '</option>';
		
		foreach ( $status_codes as $code => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $code ),
				selected( $current_status, $code, false ),
				esc_html( $label )
			);
		}
		
		echo '</select>';
	}

	/**
	 * Render group filter dropdown
	 *
	 * @since 8.1.0
	 */
	private function render_group_filter() {
		$current_group = isset( $_GET['redirect_group'] ) ? sanitize_text_field( $_GET['redirect_group'] ) : '';
		
		// Get all groups from options
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$groups = isset( $options_extra['redirect_groups'] ) ? $options_extra['redirect_groups'] : array();
		
		if ( empty( $groups ) ) {
			return;
		}
		
		echo '<select name="redirect_group" id="redirect_group">';
		echo '<option value="">' . esc_html__( 'All Groups', 'admin-site-enhancements' ) . '</option>';
		
		foreach ( $groups as $group ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $group ),
				selected( $current_group, $group, false ),
				esc_html( $group )
			);
		}
		
		echo '</select>';
	}

	/**
	 * Handle table filters
	 *
	 * @since 8.1.0
	 * @param WP_Query $query The query object
	 */
	public function handle_table_filters( $query ) {
		global $pagenow;
		
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}
		
		if ( 'asenha_redirect' !== $query->get( 'post_type' ) ) {
			return;
		}
		
		$meta_query = array();
		
		// Handle status code filter
		if ( isset( $_GET['redirect_status_code'] ) && '' !== $_GET['redirect_status_code'] ) {
			$status_code = absint( $_GET['redirect_status_code'] );
			$meta_query[] = array(
				'key'     => '_redirect_http_status_code',
				'value'   => $status_code,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}
		
		// Handle group filter
		if ( isset( $_GET['redirect_group'] ) && '' !== $_GET['redirect_group'] ) {
			$group = sanitize_text_field( $_GET['redirect_group'] );
			$meta_query[] = array(
				'key'     => '_redirect_group',
				'value'   => $group,
				'compare' => '=',
			);
		}
		
		if ( ! empty( $meta_query ) ) {
			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'AND';
			}
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Custom search for meta fields
	 *
	 * @since 8.1.0
	 * @param string $search The search SQL
	 * @param WP_Query $query The query object
	 * @return string Modified search SQL
	 */
	public function custom_search( $search, $query ) {
		global $wpdb;
		
		if ( ! is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return $search;
		}
		
		if ( 'asenha_redirect' !== $query->get( 'post_type' ) ) {
			return $search;
		}
		
		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $search;
		}
		
		// Search in title, _redirect_from, _redirect_to, _redirect_notes, _redirect_message
		$search = " AND (
			{$wpdb->posts}.post_title LIKE '%" . $wpdb->esc_like( $search_term ) . "%'
			OR EXISTS (
				SELECT 1 FROM {$wpdb->postmeta}
				WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
				AND {$wpdb->postmeta}.meta_key IN ('_redirect_from', '_redirect_to', '_redirect_notes', '_redirect_message')
				AND {$wpdb->postmeta}.meta_value LIKE '%" . $wpdb->esc_like( $search_term ) . "%'
			)
		)";
		
		return $search;
	}

	/**
	 * Customize row actions
	 *
	 * @since 8.1.0
	 * @param array $actions The row actions
	 * @param WP_Post $post The post object
	 * @return array Modified actions
	 */
	public function customize_row_actions( $actions, $post ) {
		if ( 'asenha_redirect' !== $post->post_type ) {
			return $actions;
		}
		
		// Remove Quick Edit - we'll add it back via our custom implementation
		// unset( $actions['inline hide-if-no-js'] );
		
		// Remove View action as redirects are not viewable
		unset( $actions['view'] );
		
		return $actions;
	}

	/**
	 * Add inline data for quick edit
	 *
	 * @since 8.1.0
	 * @param string $column The column name
	 * @param int $post_id The post ID
	 */
	public function add_inline_data( $column, $post_id ) {
		// We're only adding the inline data to the 'redirect_from' column, so we check for that.
		if ( 'redirect_from' !== $column ) {
			return;
		}
		
		$redirect_from = get_post_meta( $post_id, '_redirect_from', true );
		$redirect_to = get_post_meta( $post_id, '_redirect_to', true );
		$status_code = get_post_meta( $post_id, '_redirect_http_status_code', true ) ?: 302;
		$regex_enabled = get_post_meta( $post_id, '_redirect_from_regex', true );
		$strip_query_params = get_post_meta( $post_id, '_redirect_strip_query_params', true );
		$group = get_post_meta( $post_id, '_redirect_group', true );
		$notes = get_post_meta( $post_id, '_redirect_notes', true );
		$message = get_post_meta( $post_id, '_redirect_message', true );
		
		echo '<div class="hidden asenha-redirect-inline-data" id="asenha_redirect_data_' . esc_attr( $post_id ) . '">';
		echo '<div class="redirect_from">' . esc_html( $redirect_from ) . '</div>';
		echo '<div class="redirect_to">' . esc_html( $redirect_to ) . '</div>';
		echo '<div class="status_code">' . esc_html( $status_code ) . '</div>';
		echo '<div class="regex_enabled">' . esc_html( $regex_enabled ? '1' : '0' ) . '</div>';
		echo '<div class="strip_params">' . esc_html( $strip_query_params ? '1' : '0' ) . '</div>';
		echo '<div class="group">' . esc_html( $group ) . '</div>';
		echo '<div class="notes">' . esc_html( $notes ) . '</div>';
		echo '<div class="message">' . esc_html( $message ) . '</div>';
		echo '</div>';
	}
}

