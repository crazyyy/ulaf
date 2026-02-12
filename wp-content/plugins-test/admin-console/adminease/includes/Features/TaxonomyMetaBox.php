<?php
namespace AdminEase\Features;

use AdminEase\Plugin;
use WP_Error;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Enhanced Taxonomy Meta Box with AJAX loading for performance optimization
 * Replaces default WordPress taxonomy meta boxes with AJAX-powered ones
 * that support pagination, search, and lazy loading for large taxonomies.
 */
class TaxonomyMetaBox {
	private array $settings;
	private array $taxonomies = [];
	private const DEFAULT_TAXONOMIES = [ 'category' ];
	private const TERMS_PER_PAGE     = 20;
	private const PER_PAGE_OPTIONS   = [ 20, 50, 100, 150, 200 ];
	
	public function __construct() {
		$this->settings = Plugin::get_settings( 'taxonomies' );
		
		add_filter( 'adminease_settings_fields', [ $this, 'adminease_settings_fields' ] );
		
		if( !current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		if( empty( $this->settings['taxonomy_meta_box'] ) ) {
			return;
		}
		
		$this->taxonomies = apply_filters( 'adminease_taxonomy_meta_box_taxonomies', self::DEFAULT_TAXONOMIES );
		
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'add_meta_boxes', [ $this, 'replace_taxonomy_meta_boxes' ], 999 );
		
		add_action( 'wp_ajax_adminease_load_taxonomy_terms', [ $this, 'ajax_load_taxonomy_terms' ] );
		add_action( 'wp_ajax_adminease_search_taxonomy_terms', [ $this, 'ajax_search_taxonomy_terms' ] );
		add_action( 'wp_ajax_adminease_create_taxonomy_term', [ $this, 'ajax_create_taxonomy_term' ] );
		add_action( 'wp_ajax_adminease_get_term_names', [ $this, 'ajax_get_term_names' ] );
		
		add_action( 'save_post', [ $this, 'save_taxonomy_terms' ], 10, 2 );
	}
	
	/**
	 * Modifies and extends the settings fields array for the plugin.
	 * This method adds a field to enable or disable lazy loading for taxonomy terms,
	 * improving performance on post edit screens with a large number of terms.
	 *
	 * @param array $fields The existing settings fields array to be modified.
	 *
	 * @return array The updated settings fields array with the added taxonomy terms lazy loading field.
	 */
	public function adminease_settings_fields( array $fields ): array {
		$fields['taxonomies']['fields'][] = [
			'type'              => 'switch',
			'id'                => 'taxonomy-meta-box',
			'name'              => 'adminease[taxonomies][taxonomy_meta_box]',
			'value'             => $this->settings['taxonomy_meta_box'] ?? false,
			'label_class'       => 'adminease-switch',
			'input_class'       => 'form-control toggle-field',
			'label'             => __( 'Improved Taxonomy Meta Box', 'adminease' ),
			'description'       => __( 'Improve performance on the post edit screen by loading taxonomy terms (like categories or tags) dynamically via AJAX. This is especially useful when you have a large number of terms, as it prevents WordPress from loading them all at once, reducing page load time and memory usage. Enable this option to activate a searchable, paginated dropdown that loads terms on demand.', 'adminease' ),
			'field_description' => __( 'Will work when Gutenberg is disabled.', 'adminease' ),
		];
		
		return $fields;
	}
	
	/**
	 * Enqueues scripts and styles required for the taxonomy meta box on applicable admin pages.
	 * This method conditionally enqueues necessary styles and scripts only for
	 * post edit and new post pages where enabled taxonomies exist for the current post type.
	 *
	 * @param string $hook The admin page hook suffix, e.g., 'post.php' or 'post-new.php'.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( string $hook ): void {
		// Only load on post edit pages
		if( !in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) {
			return;
		}
		
		$current_screen = get_current_screen();
		
		if( !$current_screen ) {
			return;
		}
		
		// Check if current post type has any of the enabled taxonomies
		$post_type            = $current_screen->post_type;
		$post_type_taxonomies = get_object_taxonomies( $post_type );
		
		// Only enqueue if post type has enabled taxonomies
		$has_enabled_taxonomy = false;
		
		foreach( $this->taxonomies as $taxonomy ) {
			if( in_array( $taxonomy, $post_type_taxonomies ) ) {
				$has_enabled_taxonomy = true;
				break;
			}
		}
		
		if( !$has_enabled_taxonomy ) {
			return;
		}
		
		$filetime = filemtime( ADMINEASE_DIR . 'assets/css/AdminEaseTaxonomyMetaBox.css' );
		
		wp_enqueue_style(
			ADMINEASE_NAME . 'TaxonomyMetaBox',
			ADMINEASE_PLUGIN_URL . 'assets/css/AdminEaseTaxonomyMetaBox.css',
			[ ADMINEASE_NAME . 'Global' ],
			$filetime
		);
		
		$filetime = filemtime( ADMINEASE_DIR . 'assets/js/AdminEaseTaxonomyMetaBox.js' );
		
		wp_enqueue_script(
			ADMINEASE_NAME . 'TaxonomyMetaBox',
			ADMINEASE_PLUGIN_URL . 'assets/js/AdminEaseTaxonomyMetaBox.js',
			[ 'jquery' ],
			$filetime,
			true
		);
		
		wp_localize_script(
			ADMINEASE_NAME . 'TaxonomyMetaBox',
			ADMINEASE_NAME . 'TaxonomyMetaBoxAjaxObj',
			[
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'security'     => wp_create_nonce( 'adminease_taxonomy' ),
				'termsPerPage' => self::TERMS_PER_PAGE,
				'i18n'         => [
					'loadingText'      => esc_html__( 'Loading...', 'adminease' ),
					'noResultsText'    => esc_html__( 'No results found.', 'adminease' ),
					'enterNewTermText' => esc_html__( 'Enter new term name', 'adminease' ),
					/* translators: %s is the taxonomy name */
					'removeSelected'   => esc_html__( 'Remove %s', 'adminease' ),
					'clearAll'         => esc_html__( 'Clear all', 'adminease' ),
					/* translators: %d is the number of additional selected terms */
					'moreSelected'     => esc_html__( '+%d more', 'adminease' ),
				],
			]
		);
	}
	
	/**
	 * Replaces the default hierarchical taxonomy meta boxes with custom meta boxes.
	 * This method removes the default WordPress meta boxes for hierarchical taxonomies
	 * and adds custom ones with enhanced functionality for the specified taxonomies.
	 * It only processes public, UI-visible, hierarchical taxonomies for the current post type.
	 * @return void
	 */
	public function replace_taxonomy_meta_boxes(): void {
		global $wp_meta_boxes;
		
		$current_screen = get_current_screen();
		
		if( !$current_screen || !in_array( $current_screen->base, [ 'post', 'post-new' ] ) ) {
			return;
		}
		
		$post_type = $current_screen->post_type;
		
		// Check if Gutenberg is enabled for this post type
		if( $this->is_gutenberg_enabled_for_post_type( $post_type ) ) {
			return;
		}
		
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		
		foreach( $taxonomies as $taxonomy => $tax_obj ) {
			if( !empty( $this->taxonomies ) && !in_array( $taxonomy, $this->taxonomies, true ) ) {
				continue;
			}
			
			if( !$tax_obj->public || !$tax_obj->show_ui ) {
				continue;
			}
			
			if( !$tax_obj->hierarchical ) {
				continue;
			}
			
			remove_meta_box( $taxonomy . 'div', $post_type, 'side' );
			remove_meta_box( $taxonomy . 'div', $post_type, 'normal' );
			
			add_meta_box(
				'adminease-' . $taxonomy . 'div',
				$tax_obj->labels->name,
				[ $this, 'render_taxonomy_meta_box' ],
				$post_type,
				'side',
				'core',
				[ 'taxonomy' => $taxonomy ]
			);
		}
	}
	
	/**
	 * Check if Gutenberg (Block Editor) is enabled for a specific post type.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool True if Gutenberg is enabled, false otherwise.
	 */
	private function is_gutenberg_enabled_for_post_type( string $post_type ): bool {
		// Check if the block editor is supported for this post type
		if( !use_block_editor_for_post_type( $post_type ) ) {
			return false;
		}
		
		// Additional check for third-party plugins that might disable Gutenberg
		$can_edit = apply_filters( 'use_block_editor_for_post_type', true, $post_type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		
		if( !$can_edit ) {
			return false;
		}
		
		// Check for Gutenberg plugin specific filter (if the plugin is active)
		if( function_exists( 'gutenberg_can_edit_post_type' ) ) {
			$gutenberg_can_edit = apply_filters( 'gutenberg_can_edit_post_type', true, $post_type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			
			if( !$gutenberg_can_edit ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Renders the taxonomy meta box for a specific taxonomy in the post edit screen.
	 * This method outputs the HTML structure for displaying a taxonomy meta box,
	 * including search input, terms tree, pagination controls, and a section
	 * for creating a new term. It utilizes AJAX for dynamic loading and updating
	 * of taxonomy terms based on user input.
	 *
	 * @param WP_Post $post The current post object being edited.
	 * @param array   $box An array containing the meta box arguments, particularly the 'taxonomy' key.
	 *
	 * @return void Does not return a value, output is handled via echo.
	 */
	public function render_taxonomy_meta_box( WP_Post $post, array $box ): void {
		$taxonomy = $box['args']['taxonomy'];
		$tax_obj  = get_taxonomy( $taxonomy );
		
		if( !$tax_obj ) {
			return;
		}
		
		$current_terms = wp_get_object_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
		
		if( is_wp_error( $current_terms ) ) {
			$current_terms = [];
		}
		
		wp_nonce_field( 'adminease_taxonomy_' . $taxonomy, 'adminease_taxonomy_nonce_' . $taxonomy );
		?>
		<div id="adminease-taxonomy-<?php echo esc_attr( $taxonomy ); ?>"
		     class="adminease-taxonomy-meta-box"
		     data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
		     data-hierarchical="<?php echo $tax_obj->hierarchical ? 'true' : 'false'; ?>">
			
			<!-- Search Input with Spinner -->
			<div class="adminease-taxonomy-search">
				<label for="adminease-taxonomy-search-<?php echo esc_attr( $taxonomy ); ?>" class="screen-reader-text">
					<?php esc_html_e( 'Search...', 'adminease' ); ?>
				</label>
				<input type="text"
				       id="adminease-taxonomy-search-<?php echo esc_attr( $taxonomy ); ?>"
				       placeholder="<?php esc_attr_e( 'Search...', 'adminease' ); ?>"
				       class="adminease-taxonomy-search-input"/>
				<button type="button"
				        class="adminease-search-clear"
				        id="adminease-search-clear-<?php echo esc_attr( $taxonomy ); ?>"
				        title="<?php esc_attr_e( 'Clear search', 'adminease' ); ?>"
				        style="display: none;">&#10006;
				</button>
				<div class="adminease-search-spinner" id="adminease-search-spinner-<?php echo esc_attr( $taxonomy ); ?>"></div>
			</div>
			
			<!-- Terms Container -->
			<div class="adminease-taxonomy-tree" id="adminease-taxonomy-tree-<?php echo esc_attr( $taxonomy ); ?>">
				<div class="adminease-loading">
					<?php esc_html_e( 'Loading...', 'adminease' ); ?>
				</div>
			</div>
			
			<!-- Pagination and Controls Container -->
			<div class="adminease-pagination-controls">
				<!-- Pagination Container -->
				<div class="adminease-taxonomy-pagination" id="adminease-taxonomy-pagination-<?php echo esc_attr( $taxonomy ); ?>"></div>
				
				<!-- Controls Row: Per Page Selector and Page Input -->
				<div class="adminease-controls-row">
					<!-- Per Page Selector -->
					<div class="adminease-per-page-selector">
						<label for="adminease-per-page-<?php echo esc_attr( $taxonomy ); ?>">
							<?php esc_html_e( 'Show', 'adminease' ); ?>
						</label>
						<select id="adminease-per-page-<?php echo esc_attr( $taxonomy ); ?>" class="adminease-per-page-select">
							<?php foreach( self::PER_PAGE_OPTIONS as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( self::TERMS_PER_PAGE, $option ); ?>>
									<?php echo esc_html( $option ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<label>
							<?php esc_html_e( 'results', 'adminease' ); ?>
						</label>
					</div>
					
					<!-- Go to Page Input -->
					<div class="adminease-go-to-page">
						<label for="adminease-goto-page-<?php echo esc_attr( $taxonomy ); ?>">
							<?php esc_html_e( 'Page', 'adminease' ); ?>
						</label>
						<input type="number"
						       id="adminease-goto-page-<?php echo esc_attr( $taxonomy ); ?>"
						       class="adminease-goto-page-input"
						       value="1"
						       min="1"
						       max="1"
						       step="1"/>
					</div>
				</div>
			</div>
			
			<!-- Selected Terms Chips -->
			<div class="taxonomy-selected-chips"
			     id="taxonomy-selected-chips-<?php echo esc_attr( $taxonomy ); ?>"
			     role="list"
			     aria-label="<?php
			     // translators: %s is the taxonomy name
			     echo esc_attr( sprintf( __( 'Selected %s', 'adminease' ), strtolower( $tax_obj->labels->name ) ) ); ?>">
			</div>
			
			<!-- Create New Term Link -->
			<div class="adminease-taxonomy-new-term">
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . $taxonomy ) ); ?>"
				   target="_blank"
				   id="adminease-new-term-link-<?php echo esc_attr( $taxonomy ); ?>">
					+ <?php
					// translators: %s is the singular name of the taxonomy
					echo esc_html( sprintf( __( 'Create new %s', 'adminease' ), $tax_obj->labels->singular_name ) ); ?>
				</a>
			</div>
			
			<!-- Hidden inputs for selected terms -->
			<div class="adminease-taxonomy-inputs" id="adminease-taxonomy-inputs-<?php echo esc_attr( $taxonomy ); ?>">
				<?php if( !empty( $current_terms ) ) : ?>
					<?php foreach( $current_terms as $term_id ) : ?>
						<input type="hidden" name="tax_input[<?php echo esc_attr( $taxonomy ); ?>][]" value="<?php echo esc_attr( $term_id ); ?>">
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Handles the AJAX request to load taxonomy terms.
	 * This method processes incoming AJAX requests, validates the input, verifies
	 * the taxonomy, and retrieves the relevant terms, either in hierarchical or
	 * flat structures, based on the provided parameters. It also builds and
	 * returns HTML for the terms and pagination controls to the client.
	 * @return void Sends a JSON response back to the client with term data, pagination,
	 *              and other relevant information upon successful execution,
	 *              or an error message if something goes wrong.
	 */
	public function ajax_load_taxonomy_terms(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_taxonomy' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		$taxonomy = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$page     = (int) ( sanitize_text_field( wp_unslash( $_POST['page'] ?? 1 ) ) );
		$per_page = (int) ( sanitize_text_field( wp_unslash( $_POST['per_page'] ?? self::TERMS_PER_PAGE ) ) );
		$search   = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		
		if( !taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Taxonomy does not exist', 'adminease' ) ] );
		}
		
		$tax_obj = get_taxonomy( $taxonomy );
		
		if( !$tax_obj || !$tax_obj->hierarchical ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Only hierarchical taxonomies are supported', 'adminease' ) ] );
		}
		
		$is_hierarchical = $tax_obj && $tax_obj->hierarchical;
		
		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];
		
		if( !empty( $search ) ) {
			$args['name__like'] = $search;
			$args['number']     = $per_page;
			$args['offset']     = ( $page - 1 ) * $per_page;
		} else if( $is_hierarchical ) {
			$args['number'] = 0; // Get all terms
		} else {
			$args['number'] = $per_page;
			$args['offset'] = ( $page - 1 ) * $per_page;
		}
		
		$terms = get_terms( $args );
		
		$count_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'count',
		];
		
		if( !empty( $search ) ) {
			$count_args['name__like'] = $search;
		}
		
		$total_terms = get_terms( $count_args );
		
		if( is_wp_error( $terms ) ) {
			wp_send_json_error( [ 'message' => $terms->get_error_message() ] );
		}
		
		if( is_wp_error( $total_terms ) ) {
			$total_terms = 0;
		}
		
		if( $is_hierarchical && empty( $search ) ) {
			$hierarchy  = $this->build_term_hierarchy( $terms );
			$flat_terms = [];
			$this->flatten_hierarchy( $hierarchy, $flat_terms );
			$display_terms = array_slice( $flat_terms, ( $page - 1 ) * $per_page, $per_page );
		} else {
			$display_terms = $terms;
		}
		
		$post_id        = intval( wp_unslash( $_POST['post_id'] ?? 0 ) );
		$selected_terms = [];
		
		if( $post_id > 0 ) {
			$current_terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			
			if( !is_wp_error( $current_terms ) ) {
				$selected_terms = $current_terms;
			}
		}
		
		$terms_html = $this->render_terms_html( $display_terms, $taxonomy, $is_hierarchical, $selected_terms );
		
		$pagination_data = [
			'current_page' => $page,
			'total_pages'  => ceil( $total_terms / $per_page ),
			'total_items'  => $total_terms,
		];
		
		$pagination_html = $this->render_pagination_html( $pagination_data );
		
		$response_data = [
			'terms_html'      => $terms_html,
			'pagination_html' => $pagination_html,
			'is_hierarchical' => $is_hierarchical,
			'pagination'      => $pagination_data,
		];
		
		wp_send_json_success( $response_data );
	}
	
	/**
	 * Handles the AJAX request for searching taxonomy terms.
	 * This method processes incoming AJAX requests, validates the input data,
	 * verifies the existence of the specified taxonomy, and retrieves a list
	 * of matching taxonomy terms based on a search query. It also formats
	 * and returns the retrieved terms in a JSON response.
	 * @return void Sends a JSON response containing the search results, including
	 *              term details such as ID, name, slug, count, and parent, as well
	 *              as permission to create terms if applicable. Sends an error
	 *             */
	public function ajax_search_taxonomy_terms(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_taxonomy' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		$taxonomy = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$search   = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		
		if( !taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Taxonomy does not exist', 'adminease' ) ] );
		}
		
		$tax_obj = get_taxonomy( $taxonomy );
		
		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => 50,
		];
		
		if( !empty( $search ) ) {
			$args['name__like'] = $search;
		}
		
		$terms = get_terms( $args );
		
		if( is_wp_error( $terms ) ) {
			wp_send_json_error( [ 'message' => $terms->get_error_message() ] );
		}
		
		$terms_data = [];
		
		foreach( $terms as $term ) {
			$terms_data[] = [
				'id'     => $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'count'  => $term->count,
				'parent' => $term->parent,
			];
		}
		
		$response = [
			'terms'       => $terms_data,
			'search_term' => $search,
			'can_create'  => current_user_can( $tax_obj->cap->edit_terms ?? 'manage_categories' ),
		];
		
		wp_send_json_success( $response );
	}
	
	/**
	 * Handles the AJAX request to create a new taxonomy term.
	 * This method processes incoming AJAX requests, validates the input, verifies
	 * the taxonomy, checks user permissions, and creates a new term with the provided
	 * data. It returns the created term's data as a response or an error message if
	 * the creation fails.
	 * @return void Sends a JSON response with the created term's data upon successful
	 *              execution or an error message in case of failure.
	 */
	public function ajax_create_taxonomy_term(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_taxonomy' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		$taxonomy  = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$term_name = sanitize_text_field( wp_unslash( $_POST['term_name'] ?? '' ) );
		$parent_id = intval( sanitize_text_field( wp_unslash( $_POST['parent_id'] ?? 0 ) ) );
		
		if( !taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Taxonomy does not exist', 'adminease' ) ] );
		}
		
		if( empty( $term_name ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Term name is required', 'adminease' ) ] );
		}
		
		$tax_obj = get_taxonomy( $taxonomy );
		
		if( !current_user_can( $tax_obj->cap->edit_terms ?? 'manage_categories' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to create terms', 'adminease' ) ] );
		}
		
		$args   = [ 'parent' => $parent_id ];
		$result = wp_insert_term( $term_name, $taxonomy, $args );
		
		if( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}
		
		$term = get_term( $result['term_id'], $taxonomy );
		
		if( is_wp_error( $term ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to retrieve created term', 'adminease' ) ] );
		}
		
		$response = [
			'id'     => $term->term_id,
			'name'   => $term->name,
			'slug'   => $term->slug,
			'parent' => $term->parent,
		];
		
		wp_send_json_success( $response );
	}
	
	/**
	 * Handles the AJAX request to get term names for specific term IDs.
	 * This method is used to populate selected term names when the page loads.
	 * @return void Sends a JSON response with term names.
	 */
	public function ajax_get_term_names(): void {
		// Verify nonce
		if( !isset( $_POST['security'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'adminease_taxonomy' ) ) {
			wp_send_json_error( new WP_Error( 'security_error', __( 'An error occurred. Refresh the page and try again.', 'adminease' ) ), 403 );
		}
		
		$taxonomy = sanitize_text_field( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$term_ids = array_map( 'intval', wp_unslash( $_POST['term_ids'] ?? [] ) );
		
		if( !taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Taxonomy does not exist', 'adminease' ) ] );
		}
		
		if( empty( $term_ids ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No term IDs provided', 'adminease' ) ] );
		}
		
		$terms_data = [];
		
		foreach( $term_ids as $term_id ) {
			$term = get_term( $term_id, $taxonomy );
			
			if( !is_wp_error( $term ) && $term ) {
				$terms_data[] = [
					'id'   => $term->term_id,
					'name' => $term->name,
				];
			}
		}
		
		wp_send_json_success( [ 'terms' => $terms_data ] );
	}
	
	/**
	 * Generates and returns the HTML markup for a list of taxonomy terms.
	 * This method creates an HTML structure for displaying terms in either
	 * hierarchical or non-hierarchical format, including options for selecting terms
	 * and rendering depth indicators for hierarchical terms.
	 *
	 * @param array  $terms An array of term objects to be rendered.
	 * @param string $taxonomy The taxonomy slug associated with the terms.
	 * @param bool   $is_hierarchical Whether the taxonomy is hierarchical.
	 * @param array  $selected_terms Optional. An array of term IDs that should be pre-selected.
	 *                               Defaults to an empty array.
	 *
	 * @return string The generated HTML markup for the terms list.
	 */
	private function render_terms_html( array $terms, string $taxonomy, bool $is_hierarchical, array $selected_terms = [] ): string {
		ob_start();
		?>
		<ul class="adminease-terms-list">
			<?php
			if( $is_hierarchical ) {
				$hierarchy  = $this->build_term_hierarchy( $terms );
				$flat_terms = [];
				$this->flatten_hierarchy_with_depth( $hierarchy, $flat_terms, 0 );
				
				foreach( $flat_terms as $term_data ) {
					$term        = $term_data['term'];
					$depth       = $term_data['depth'];
					$is_selected = in_array( $term->term_id, $selected_terms );
					$dash_prefix = str_repeat( '- ', $depth );
					?>
					<li class="adminease-term-item" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
						<label class="adminease-term-label">
							<input type="checkbox" value="<?php echo esc_attr( $term->term_id ); ?>"<?php echo $is_selected ? ' checked' : ''; ?> />
							<span class="adminease-term-name">
								<?php if( $dash_prefix ) : ?>
									<span class="adminease-hierarchy-indicator"><?php echo esc_html( $dash_prefix ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( $term->name ); ?>
							</span>
							<?php if( $term->count > 0 ) : ?>
								<span class="adminease-term-count">(<?php echo esc_html( $term->count ); ?>)</span>
							<?php endif; ?>
						</label>
					</li>
					<?php
				}
			} else {
				// Non-hierarchical terms
				foreach( $terms as $term ) {
					$is_selected = in_array( $term->term_id, $selected_terms );
					?>
					<li class="adminease-term-item" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
						<label class="adminease-term-label">
							<input type="checkbox" value="<?php echo esc_attr( $term->term_id ); ?>"<?php echo $is_selected ? ' checked' : ''; ?> />
							<span class="adminease-term-name"><?php echo esc_html( $term->name ); ?></span>
							<?php if( $term->count > 0 ) : ?>
								<span class="adminease-term-count">(<?php echo esc_html( $term->count ); ?>)</span>
							<?php endif; ?>
						</label>
					</li>
					<?php
				}
			}
			?>
		</ul>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Recursively flattens a hierarchical structure of terms while preserving their depth levels.
	 * Iterates through a hierarchy of terms, adding each term and its associated depth
	 * to a flat array, and recursively processes any children of the current term.
	 *
	 * @param array $hierarchy An array of hierarchical terms to be flattened. Each term
	 *                           is expected to include a `children` property for nested terms.
	 * @param array $flat_terms A reference to an array where the flattened terms with depth
	 *                           will be stored. Each entry will include the term and its depth.
	 * @param int   $depth The current depth level of the term in the hierarchy,
	 *                           starting at 0 by default.
	 *
	 * @return void No value is returned, but the $flat_terms array is modified to include
	 *              the flattened terms and their depth levels.
	 */
	private function flatten_hierarchy_with_depth( array $hierarchy, array &$flat_terms, int $depth = 0 ): void {
		foreach( $hierarchy as $term ) {
			// Add current term with its depth
			$flat_terms[] = [
				'term'  => $term,
				'depth' => $depth,
			];
			
			// Recursively add children with increased depth
			if( !empty( $term->children ) ) {
				$this->flatten_hierarchy_with_depth( $term->children, $flat_terms, $depth + 1 );
			}
		}
	}
	
	/**
	 * Renders the pagination HTML based on the provided pagination data.
	 * This method generates the HTML structure for pagination, including
	 * navigation buttons (first, previous, next, and last) and the compact
	 * display of page numbers. It ensures appropriate controls are displayed
	 * based on the current page and total pages.
	 *
	 * @param array $pagination_data An associative array containing pagination details:
	 *                                - 'current_page': The current page number.
	 *                                - 'total_pages': The total number of pages.
	 *
	 * @return string Returns the generated HTML as a string for the pagination component.
	 *                If there is only one page of content, an empty string is returned.
	 */
	private function render_pagination_html( array $pagination_data ): string {
		$current_page = $pagination_data['current_page'];
		$total_pages  = $pagination_data['total_pages'];
		
		if( $total_pages <= 1 ) {
			return '';
		}
		
		ob_start();
		?>
		<div class="adminease-pagination">
			<?php
			if( $current_page > 1 ) : ?>
				<a href="#" class="adminease-page-link adminease-nav-btn" data-page="1" title="<?php esc_attr_e( 'First page', 'adminease' ); ?>">&laquo;</a>
			<?php endif;
			
			if( $current_page > 1 ) : ?>
				<a href="#" class="adminease-page-link adminease-nav-btn" data-page="<?php echo esc_attr( $current_page - 1 ); ?>" title="<?php esc_attr_e( 'Previous page', 'adminease' ); ?>">&lsaquo;</a>
			<?php endif;
			
			$pages_to_show = $this->get_compact_page_numbers( $current_page, $total_pages );
			
			foreach( $pages_to_show as $page_item ) {
				if( $page_item === '...' ) : ?>
					<span class="adminease-page-dots">...</span>
				<?php elseif( $page_item == $current_page ) : ?>
					<span class="adminease-page-current"><?php echo wp_kses_post( $page_item ); ?></span>
				<?php else : ?>
					<a href="#" class="adminease-page-link" data-page="<?php echo esc_attr( $page_item ); ?>"><?php echo wp_kses_post( $page_item ); ?></a>
				<?php endif;
			}
			
			if( $current_page < $total_pages ) : ?>
				<a href="#" class="adminease-page-link adminease-nav-btn" data-page="<?php echo esc_attr( $current_page + 1 ); ?>" title="<?php esc_attr_e( 'Next page', 'adminease' ); ?>">&rsaquo;</a>
			<?php endif;
			
			if( $current_page < $total_pages ) : ?>
				<a href="#" class="adminease-page-link adminease-nav-btn" data-page="<?php echo esc_attr( $total_pages ); ?>" title="<?php esc_attr_e( 'Last page', 'adminease' ); ?>">&raquo;</a>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Generates a compact list of page numbers for pagination.
	 * This method creates an optimized array of page numbers to be displayed,
	 * including ellipses ("...") where appropriate, based on the current page
	 * position relative to the total number of pages. It provides a balance
	 * between showing relevant page numbers and reducing clutter.
	 *
	 * @param int $current_page The current active page number.
	 * @param int $total_pages The total number of pages available.
	 *
	 * @return array A list of integers and/or ellipsis placeholders representing the compact pagination.
	 */
	private function get_compact_page_numbers( int $current_page, int $total_pages ): array {
		$pages = [];
		
		if( $total_pages <= 4 ) {
			for( $i = 1; $i <= $total_pages; $i++ ) {
				$pages[] = $i;
			}
		} else {
			if( $current_page <= 3 ) {
				$pages = [ 1, 2, 3, 4 ];
				
				if( $total_pages > 4 ) {
					$pages[] = '...';
					$pages[] = $total_pages;
				}
			} else if( $current_page >= $total_pages - 2 ) {
				$pages = [ 1, '...', $total_pages - 3, $total_pages - 2, $total_pages - 1, $total_pages ];
			} else {
				$pages = [ 1, '...', $current_page - 1, $current_page, $current_page + 1, '...', $total_pages ];
			}
		}
		
		return $pages;
	}
	
	/**
	 * Builds a hierarchical structure of taxonomy terms.
	 * This method processes a flat list of taxonomy terms, identifying parent-child
	 * relationships, and organizes them into a hierarchical format. It ensures that
	 * terms are properly nested based on their parent IDs.
	 *
	 * @param array $terms An array of term objects. Each term object should include at least
	 *                     the `term_id` and `parent` properties to establish hierarchy.
	 *
	 * @return array An array representing the hierarchical structure of terms, with each term
	 *               possibly containing a `children` property if it has child terms.
	 */
	private function build_term_hierarchy( array $terms ): array {
		$hierarchy = [];
		$term_map  = [];
		
		// First pass: create a map of all terms
		foreach( $terms as $term ) {
			$term->children             = [];
			$term_map[ $term->term_id ] = $term;
		}
		
		// Second pass: build the hierarchy
		foreach( $terms as $term ) {
			if( $term->parent == 0 ) {
				// Top-level term
				$hierarchy[] = $term;
			} else if( isset( $term_map[ $term->parent ] ) ) {
				// Child term - add to parent's children
				$term_map[ $term->parent ]->children[] = $term;
			} else {
				// Parent not found, treat as top-level
				$hierarchy[] = $term;
			}
		}
		
		return $hierarchy;
	}
	
	/**
	 * Recursively flattens a hierarchical array of terms into a single-level array.
	 * This method takes a hierarchical structure of terms, traverses through each
	 * level, and appends the terms to a flat array. If a term contains children,
	 * the method recursively processes them.
	 *
	 * @param array  $hierarchy The hierarchical array of terms to flatten.
	 * @param array &$flat_terms A reference to the array where flattened terms
	 *                           will be stored.
	 *
	 * @return void
	 */
	private function flatten_hierarchy( array $hierarchy, array &$flat_terms ): void {
		foreach( $hierarchy as $term ) {
			$flat_terms[] = $term;
			
			if( !empty( $term->children ) ) {
				$this->flatten_hierarchy( $term->children, $flat_terms );
			}
		}
	}
	
	/**
	 * Saves the taxonomy terms for a given post.
	 * This method handles the process of saving taxonomy terms submitted via POST,
	 * ensuring proper validation, sanitization, and term assignment for the post types
	 * associated with the defined taxonomies.
	 *
	 * @param int   $post_id The ID of the post being saved.
	 * @param mixed $post The post object being saved.
	 *
	 * @return void Does not return any value. Performs actions to validate input,
	 *              update object terms, and skip unsupported or unauthorized cases.
	 */
	public function save_taxonomy_terms( int $post_id, $post ): void {
		if( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		
		if( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$taxonomies = get_object_taxonomies( $post->post_type );
		
		foreach( $taxonomies as $taxonomy ) {
			if( !empty( $this->taxonomies ) && !in_array( $taxonomy, $this->taxonomies, true ) ) {
				continue;
			}
			
			$nonce_field = 'adminease_taxonomy_nonce_' . $taxonomy;
			
			if( !isset( $_POST[ $nonce_field ] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), 'adminease_taxonomy_' . $taxonomy ) ) {
				continue;
			}
			
			$selected_terms = [];
			
			if( isset( $_POST['tax_input'][ $taxonomy ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below based on data type
				$raw_terms = wp_unslash( $_POST['tax_input'][ $taxonomy ] );
				
				if( is_array( $raw_terms ) ) {
					$selected_terms = array_map( 'sanitize_text_field', $raw_terms );
					$selected_terms = array_map( 'intval', $selected_terms );
					$selected_terms = array_filter( $selected_terms );
				} else {
					$selected_terms = [ intval( sanitize_text_field( $raw_terms ) ) ];
					$selected_terms = array_filter( $selected_terms );
				}
			}
			
			wp_set_object_terms( $post_id, $selected_terms, $taxonomy );
		}
	}
}