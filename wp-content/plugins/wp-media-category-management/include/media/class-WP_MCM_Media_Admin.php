<?php
/**
 * WP Media Category Management Media_Admin class
 * 
 * @since  2.0.0
 * @author DeBAAT
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WP_MCM_Media_Admin' ) ) {

	class WP_MCM_Media_Admin {

		/**
		 * Class constructor
		 */
		function __construct() {

			$this->includes();
			$this->add_hooks_and_filters();
		}

		/**
		 * Include the required files.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function includes() {

		}

		/**
		 * Add cross-element hooks & filters.
		 *
		 * Haven't yet moved all items to the AJAX and UI classes.
		 */
		function add_hooks_and_filters() {
			// $this->debugMP('msg', __FUNCTION__ . ' started.');

			// Some filters and action to process categories
			add_action( 'restrict_manage_posts',                    array( $this, 'mcm_restrict_manage_posts'        )       );

			add_action( 'wp_enqueue_media',                         array( $this, 'mcm_admin_wp_enqueue_media'       )       );

			add_action( 'add_attachment',                           array( $this, 'mcm_set_attachment_category'      )       );
			add_action( 'edit_attachment',                          array( $this, 'mcm_set_attachment_category'      )       );

			add_filter( 'ajax_query_attachments_args',              array( $this, 'mcm_ajax_query_attachments_args'  )       );

		}

		/**
		 * Enqueue the media-category-management scripts to filter categories
		 *
		 * @since 2.3.2
		 * @return void
		 */
		function mcm_admin_wp_enqueue_media() {
			global $current_screen;
			global $pagenow;
			global $wp_mcm_options;
			global $wp_mcm_taxonomy;
			global $wp_mcm_walker_category_mediagrid_filter;
			$this->debugMP('msg',__FUNCTION__ . ' pagenow = ' . $pagenow . ', wp_script_is( media-editor ) = ' . wp_script_is( 'media-editor' ));
			$this->debugMP('pr',__FUNCTION__ . ' current_screen = !', $current_screen );


			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' taxonomy = ' . $media_taxonomy);

			// Only show_count when no Post or Tag taxonomy
			if (( $media_taxonomy == WP_MCM_POST_TAXONOMY ) || ( $media_taxonomy == WP_MCM_TAGS_TAXONOMY )) {
				$show_count = false;
			} else {
				$show_count = true;
			}
			$show_count = true;

			$dropdown_options = array(
				'taxonomy'        => $media_taxonomy,
				'hide_empty'      => false,
				'hierarchical'    => true,
				'orderby'         => 'name',
				'show_count'      => $show_count,
				'walker'          => $wp_mcm_walker_category_mediagrid_filter,
				'value'           => 'id',
				'echo'            => false
			);
			$attachment_terms_list = get_terms( $dropdown_options );
			// $this->debugMP('pr',__FUNCTION__ . ' attachment_terms_list = !', $attachment_terms_list );

			// Add an attachment_terms_list for All and No category
			$mcm_terms_label         = $this->mcm_get_media_category_label($media_taxonomy);
			$mcm_terms_label_all     = esc_html__( 'Show all', 'wp-media-category-management' ) . ' ' . $mcm_terms_label;
			$mcm_terms_label_none    = esc_html__( 'No',       'wp-media-category-management' ) . ' ' . $mcm_terms_label;

			// create my own version codes for taxonomies
			wp_enqueue_script( 'mcm-media-views', WP_MCM_PLUGIN_URL . '/js/wp-mcm-media-views-post-terms.js', array( 'media-views' ), WP_MCM_VERSION_NUM . WP_MCM_JS_DEV_VERSION, false );

			wp_localize_script(
				'mcm-media-views',
				'wpmcm_admin_js_terms',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'spinner_url'          => includes_url() . '/images/spinner.gif',
					'mcm_terms_key'        => $media_taxonomy,
					'mcm_terms_label'      => $mcm_terms_label,
					'mcm_terms_label_all'  => $mcm_terms_label_all,
					'mcm_terms_label_none' => $mcm_terms_label_none,
					'mcm_terms_list'       => $attachment_terms_list,
				)
			);

		}

		/**
		 * Add a category filter
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_add_category_filter( $media_taxonomy = '') {

			// Validate input
			if ($media_taxonomy == '') {
				return;
			}

			global $pagenow;
			if ( 'upload.php' == $pagenow ) {

				// Set options depending on type of taxonomy chosen
				switch ($media_taxonomy) {
					case WP_MCM_POST_TAXONOMY:
						$selected_value = esc_attr( isset( $_GET['cat'] ) ? $_GET['cat'] : '' );
						break;
					default:
						$selected_value = esc_attr( isset( $_GET[$media_taxonomy] ) ? $_GET[$media_taxonomy] : '' );
						break;
				}

				// echo '<label for="filter-by-' . esc_attr( $media_taxonomy ) . '" class="screen-reader-text">' . esc_html__('Filter by', 'wp-media-category-management') . ' ' . esc_html( $media_taxonomy ) . '</label>';
				echo '<label for="' . esc_attr( $media_taxonomy ) . '" class="screen-reader-text">' . esc_html__('Filter by', 'wp-media-category-management') . ' ' . esc_html( $media_taxonomy ) . '</label>';

				$dropdown_options = $this->mcm_get_media_category_options($media_taxonomy, $selected_value);
				wp_dropdown_categories( $dropdown_options );
			}
		}

		/**
		 * Get the label to show in the list of media_category
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_get_media_category_label( $media_taxonomy = '' ) {

			switch ($media_taxonomy) {
				case WP_MCM_TAGS_TAXONOMY:
					return esc_html__( 'tags', 'wp-media-category-management' );
					break;
				case WP_MCM_POST_TAXONOMY:
					return esc_html__( 'Post categories', 'wp-media-category-management' );
					break;
				default:
					return esc_html__( 'MCM categories', 'wp-media-category-management' );
					break;
			}
		}

		/**
		 * Get the options to determine the list of media_category
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_get_media_category_options( $media_taxonomy = '', $selected_value = '') {
			global $wp_mcm_walker_category_filter;

			// Set options depending on type of taxonomy chosen
			$dropdown_options = array(
				'taxonomy'           => $media_taxonomy,
				'option_none_value'  => WP_MCM_OPTION_NO_CAT,
				'selected'           => $selected_value,
				'hide_empty'         => false,
				'hierarchical'       => true,
				'orderby'            => 'name',
				'walker'             => $wp_mcm_walker_category_filter,
			);

			// Get some labels
			$mcm_label     = $this->mcm_get_media_category_label($media_taxonomy);
			$mcm_label_all = esc_html__( 'View all', 'wp-media-category-management' ) . ' ' . $mcm_label;
			$mcm_label_no  = esc_html__( 'No',       'wp-media-category-management' ) . ' ' . $mcm_label;

			switch ($media_taxonomy) {
				case WP_MCM_TAGS_TAXONOMY:
					$dropdown_options_extra = array(
						// 'name'               => 'filter-by-' . $media_taxonomy,
						'name'               => $media_taxonomy,
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => false,
						'value'              => 'slug'
					);
					break;
				case WP_MCM_POST_TAXONOMY:
					$dropdown_options_extra = array(
						// 'name'               => 'filter-by-' . $media_taxonomy,
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => false,
						'value'              => 'id'
					);
					break;
				default:
					$dropdown_options_extra = array(
						// 'name'               => 'filter-by-' . $media_taxonomy,
						'name'               => $media_taxonomy,
						'show_option_all'    => $mcm_label_all,
						'show_option_none'   => $mcm_label_no,
						'show_count'         => true,
						'value'              => 'slug'
					);
					break;
			}
			$this->debugMP('pr',__FUNCTION__ . ' selected_value = ' . $selected_value . ', dropdown_options', array_merge($dropdown_options, $dropdown_options_extra));
			return array_merge($dropdown_options, $dropdown_options_extra);
		}

		/**
		 * Add a filter for restrict_manage_posts to add a filter for categories and process the toggle actions
		 *
		 * @since 2.0.0
		 * @return void
		 */
		function mcm_restrict_manage_posts() {
			global $wp_mcm_taxonomy;
			global $wp_mcm_options;

			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' taxonomy = ' . $media_taxonomy);

			// Add a filter for the WP_MCM_POST_TAXONOMY
			if (($media_taxonomy != WP_MCM_POST_TAXONOMY) && ($wp_mcm_options->is_true('wp_mcm_use_post_taxonomy'))) {
				$this->mcm_add_category_filter( WP_MCM_POST_TAXONOMY );
			}

			// Add a filter for the selected category
			$this->mcm_add_category_filter( $media_taxonomy );

		}

		/**
		 * Handle default category of attachments without category
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		function mcm_set_attachment_category( $post_ID ) {
			global $wp_mcm_options;
			global $wp_mcm_taxonomy;

			// Check whether this user can edit this post
			if ( ! current_user_can( 'edit_post', $post_ID ) ) {
				return;
			}

			// Check whether to use the default or not
			if ( ! $wp_mcm_options->is_true( 'wp_mcm_use_default_category' )) {
				return;
			}

			// Check $media_taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();

			// Only add default if attachment doesn't have WP_MCM_MEDIA_TAXONOMY categories
			if ( ! wp_get_object_terms( $post_ID, $media_taxonomy ) ) {

				// Get the default value
				$default_category = $wp_mcm_options->get_value('wp_mcm_default_media_category');

				// Check for valid $default_category
				if ($default_category != WP_MCM_OPTION_NONE) {

					// Not set so add the $media_category taxonomy to this media post
					$add_result = wp_set_object_terms($post_ID, $default_category, $media_taxonomy, true);

					// Check for error
					if ( is_wp_error( $add_result ) ) {
						$this->debugMP('pr',__FUNCTION__ . ' for post_ID(' . $post_ID . '), media_taxonomy(' . $media_taxonomy . '), default_category(' . $default_category . ') => is_wp_error:', $add_result);
						return;
					}
				}

				$this->debugMP('msg',__FUNCTION__ . ' for post_ID(' . $post_ID . '), media_taxonomy(' . $media_taxonomy . '), default_category(' . $default_category . ')!!!' );

			}

		}

		/**
		 *  Get an array of term values, which type is determined by the parameter
		 *
		 *  @since    2.0.0
		 */
		function mcm_get_terms_values( $keys = 'ids') { 
			global $wp_mcm_taxonomy;

			// Get media taxonomy
			$media_taxonomy = $wp_mcm_taxonomy->mcm_get_media_taxonomy();
			$this->debugMP('msg',__FUNCTION__ . ' media_taxonomy = ' . $media_taxonomy);

			$media_terms = get_terms( array(
				'taxonomy'         => $media_taxonomy,
				'hide_empty'       => 0,
				'fields'           => 'id=>slug',
				));
			// $this->debugMP('pr', __FUNCTION__ . ' media_terms for :' . $media_taxonomy, $media_terms);

			$media_values = array();
			foreach ($media_terms as $key => $value) {
				if ($keys == 'ids') {
					$media_values[] = $key;
				} else {
					$media_values[] = $value;
				}
			}
			return $media_values;

		}

		/**
		 * Changing categories in the 'grid view'
		 *
		 * @since 2.0.0
		 *
		 * @action ajax_query_attachments_args
		 * @param array $mcm_query_args
		 */
		function mcm_ajax_query_attachments_args( $mcm_query_args = array() ) {
			$this->debugMP('pr', __FUNCTION__ . ' Started with mcm_query_args: ', $mcm_query_args );

			// grab original mcm_query_args, the given mcm_query_args has already been filtered by WordPress
			$taxquery = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();

			$taxonomies = get_object_taxonomies( 'attachment', 'names' );
			// $this->debugMP('pr', __FUNCTION__ . ' Continued with taxonomies: ', $taxonomies );
			// $this->debugMP('pr', __FUNCTION__ . ' Continued with _REQUEST: ', $_REQUEST );

			$taxquery = array_intersect_key( $taxquery, array_flip( $taxonomies ) );

			// merge our mcm_query_args into the WordPress query_args
			$mcm_query_args = array_merge( $mcm_query_args, $taxquery );

			// Check all taxonomies to filter
			$mcm_query_args['tax_query'] = array();
			foreach ( $taxonomies as $taxonomy ) {
				if ( isset( $mcm_query_args[$taxonomy] ) ) {
					// Filter a specific category
					if ( is_numeric( $mcm_query_args[$taxonomy] ) ) {
						array_push( $mcm_query_args['tax_query'], array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => $mcm_query_args[$taxonomy]
						));	
					}
					// Filter No category
					if ( $mcm_query_args[$taxonomy] == WP_MCM_OPTION_NO_CAT ) {
						$all_terms_ids = $this->mcm_get_terms_values('ids');
						array_push( $mcm_query_args['tax_query'], array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => $all_terms_ids,
							'operator' => 'NOT IN',
						));	
					}
				}
				unset ( $mcm_query_args[$taxonomy] );
			}

			// If multiple taxonomies to filter found, then add the AND function
			if ( count( $mcm_query_args['tax_query'] ) > 1 ) {
				$mcm_query_args['tax_query'] = array_push( $mcm_query_args['tax_query'], array( 'relation' => 'AND' ) );
			}

			// Remove $mcm_query_args['tax_query'] when not used
			if ( count( $mcm_query_args['tax_query'] ) == 0 ) {
				unset ( $mcm_query_args['tax_query'] );
				$this->debugMP('pr', __FUNCTION__ . ' Continued with unset (tax_query) as it is not part of mcm_query_args: ', $mcm_query_args );
			} else {
				$this->debugMP('pr', __FUNCTION__ . ' Continued with (' . count( $mcm_query_args['tax_query'] ) . ') tax_query as part of mcm_query_args: ', $mcm_query_args );
			}

			return $mcm_query_args;
		}

		/**
		 * Simplify the plugin debugMP interface.
		 *
		 * Typical start of function call: $this->debugMP('msg',__FUNCTION__);
		 *
		 * @param string $type
		 * @param string $hdr
		 * @param string $msg
		 */
		function debugMP($type,$hdr,$msg='') {
			if (($type === 'msg') && ($msg!=='')) {
				$msg = esc_html($msg);
			}
			if (($hdr!=='')) {   // Adding __CLASS__ to non-empty hdr
				$hdr = __CLASS__ . '::' . $hdr;
			}

			WP_MCM_debugMP($type,$hdr,$msg,NULL,NULL,true);
		}

	}

}
