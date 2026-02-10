<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package daext-interlinks-manager
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextinma_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the shared class.
	 *
	 * @var Daexthrmal_Shared|null
	 */
	private $shared = null;

	/**
	 * The screen id of the "Dashboard" menu.
	 *
	 * @var null
	 */
	private $screen_id_dashboard = null;

	/**
	 * The screen id of the "Juice" menu.
	 *
	 * @var null
	 */
	private $screen_id_juice = null;

	/**
	 * The screen id of the "Options" menu.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// assign an instance of the plugin info.
		$this->shared = Daextinma_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Add the meta box.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		// Save the meta box.
		add_action( 'save_post', array( $this, 'daextinma_save_meta_interlinks_options' ) );

		// this hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Require and instantiate the classes used to handle the menus.
		add_action( 'init', array( $this, 'handle_menus' ) );

	}

	/**
	 * Return an istance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * If we are in one of the plugin back-end menus require and instantiate the class used to handle the specific menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Dashboard', 'daext-interlinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextinma-dashboard' ),
							'icon'      => 'line-chart-up-03',
							'menu_slug' => 'daextinma-dashboard',
						),
						array(
							'link_text' => __( 'Juice', 'daext-interlinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextinma-juice' ),
							'icon'      => 'link-03',
							'menu_slug' => 'daextinma-juice',
						),
						array(
							'link_text' => __( 'Options', 'daext-interlinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextinma-options' ),
							'icon'      => 'settings-01',
							'menu_slug' => 'daextinma-options',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'HTTP Status', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Click Tracking', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Bulk Actions', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Automatic Links', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Links Categories', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Term Groups', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Tools', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Maintenance', 'daext-interlinks-manager' ),
							'link_url'  => 'https://daext.com/interlinks-manager/',
							'pro_badge' => true,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daextinma-menu-elements.php';

			// Use the appropriate child class based on the page query parameter.
			if ( 'daextinma-dashboard' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextinma-dashboard-menu-elements.php';
				$this->menu_elements = new Daextinma_Dashboard_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextinma-juice' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextinma-juice-menu-elements.php';
				$this->menu_elements = new Daextinma_Juice_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextinma-options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextinma-options-menu-elements.php';
				$this->menu_elements = new Daextinma_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}

	}

	/**
	 * Enqueue admin-specific stylesheets.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu Dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Juice.
		if ( $screen->id === $this->screen_id_juice ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}

		/**
		 * Load the post editor CSS if at least one of the three meta box is
		 * enabled with the current $screen->id.
		 */
		$load_post_editor_css = false;

		$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
		if ( is_array( $interlinks_options_post_types_a ) && in_array( $screen->id, $interlinks_options_post_types_a, true ) ) {
			$load_post_editor_css = true;
		}

		$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
		if ( is_array( $interlinks_optimization_post_types_a ) && in_array( $screen->id, $interlinks_optimization_post_types_a, true ) ) {
			$load_post_editor_css = true;
		}

		if ( $load_post_editor_css ) {

			// Post Editor CSS.
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-post-editor', $this->shared->get( 'url' ) . 'admin/assets/css/post-editor.css', array(), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific javascript.
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu Dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAEXTINMA_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'internal_links_data_last_update: "' . get_option( $this->shared->get( 'slug' ) . '_internal_links_data_last_update' ) . '",';
			$initialization_script .= 'internal_links_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_internal_links_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '",';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-dashboard-menu',
				$this->shared->get( 'url' ) . 'admin/react/dashboard-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-dashboard-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Juice.
		if ( $screen->id === $this->screen_id_juice ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAEXTINMA_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'juice_data_last_update: "' . get_option( $this->shared->get( 'slug' ) . '_juice_data_last_update' ) . '",';
			$initialization_script .= 'juice_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_juice_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '",';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-juice-menu',
				$this->shared->get( 'url' ) . 'admin/react/juice-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-juice-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTINMA_PARAMETERS object.
			$initialization_script  = 'window.DAEXTINMA_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';

			require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextinma-menu-options.php';
			$daextinma_menu_options = new Daextinma_Menu_Options();
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $daextinma_menu_options->menu_options_configuration() );

			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options-new',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options-new', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		/**
		 * Load the post editor JS if at least one of the two meta boxes is enabled with the current $screen->id.
		 */
		$load_post_editor_js = false;

		$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
		if ( is_array( $interlinks_options_post_types_a ) && in_array( $screen->id, $interlinks_options_post_types_a, true ) ) {
			$load_post_editor_js = true;
		}

		$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
		if ( is_array( $interlinks_optimization_post_types_a ) && in_array( $screen->id, $interlinks_optimization_post_types_a, true ) ) {
			$load_post_editor_js = true;
		}

		if ( $load_post_editor_js ) {

			// Store the JavaScript parameters in the window.DAEXTREVOP_PARAMETERS object.
			$initialization_script  = 'window.DAEXTINMA_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'nonce: "' . wp_create_nonce( 'daextinma' ) . '"';
			$initialization_script .= '};';

			// Post Editor Js.
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-post-editor', $this->shared->get( 'url' ) . 'admin/assets/js/post-editor.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-post-editor', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	 static public function ac_activate( $networkwide ) {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			/**
			 * If this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// swith to the iterated blog.
					switch_to_blog( $blog_id );

					// create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * If this is not a multisite installation create options and
			 * tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		/**
		 * If the plugin is "Network Active" create the options and tables for
		 *  this new blog.
		 */
		if ( is_plugin_active_for_network( 'interlinks-manager/init.php' ) ) {

			// get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The ID of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 */
	static public function ac_initialize_options() {

		if ( intval( get_option( 'daextinma_options_version' ), 10 ) < 1 ) {

			// assign an instance of Daextinma_Shared.
			$shared = Daextinma_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextinma_options_version', '1' );

		}

	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	static public function ac_create_database_tables() {

		// check database version and create the database.
		if ( intval( get_option( 'daextinma_database_version' ), 10 ) < 7 ) {

			global $wpdb;

			// Get the database character collate that will be appended at the end of each query.
			$charset_collate = $wpdb->get_charset_collate();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// create *prefix*_archive.
			$sql = "CREATE TABLE {$wpdb->prefix}daextinma_archive (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                post_type varchar(20) NOT NULL DEFAULT '',
                post_date datetime DEFAULT NULL,
                manual_interlinks bigint(20) NOT NULL DEFAULT '0',
                iil bigint(20) NOT NULL DEFAULT '0',
                content_length bigint(20) NOT NULL DEFAULT '0',
                recommended_interlinks bigint(20) NOT NULL DEFAULT '0',
                optimization tinyint(1) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_juice.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daextinma_juice (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                iil bigint(20) NOT NULL DEFAULT '0',
                juice bigint(20) NOT NULL DEFAULT '0',
                juice_relative bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_anchors.
			global $wpdb;
			$sql = "CREATE TABLE {$wpdb->prefix}daextinma_anchors (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                anchor longtext NOT NULL DEFAULT '',
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                juice bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			/**
			 * Delete the statistics. This is done to avoid the statistics with
			 * previous db fields to be displayed in latest UI.
			 */
			self::delete_statistics();

			// Update database version.
			update_option( 'daextinma_database_version', '7' );

		}
	}

	/**
	 * Plugin delete.
	 */
	public static function un_delete() {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// create an array with all the blog ids.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// swith to the iterated blog.
				switch_to_blog( $blog_id );

				// create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			/**
			 * If this is not a multisite installation delete options and
			 * tables only for the current blog.
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 */
	public static function un_delete_options() {

		// assign an instance of Daextamp_Shared.
		$shared = Daextinma_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 */
	public static function un_delete_database_tables() {

		// Assign an instance of Daextinma_Shared.
		$shared = Daextinma_Shared::get_instance();

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextinma_archive" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextinma_juice" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextinma_anchors" );
	}

	/**
	 * Register the admin menu.
	 */
	public function me_add_admin_menu() {

		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1NiAyNTYiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgICAgc3Ryb2tlLXdpZHRoOiAwcHg7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxnIGlkPSJMYXllcl8xIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgPHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTI4LDE2YzI5LjkyLDAsNTguMDQsMTEuNjUsNzkuMiwzMi44LDIxLjE1LDIxLjE1LDMyLjgsNDkuMjgsMzIuOCw3OS4ycy0xMS42NSw1OC4wNC0zMi44LDc5LjJjLTIxLjE1LDIxLjE1LTQ5LjI4LDMyLjgtNzkuMiwzMi44cy01OC4wNC0xMS42NS03OS4yLTMyLjhjLTIxLjE1LTIxLjE1LTMyLjgtNDkuMjgtMzIuOC03OS4yczExLjY1LTU4LjA0LDMyLjgtNzkuMmMyMS4xNS0yMS4xNSw0OS4yOC0zMi44LDc5LjItMzIuOE0xMjgsMEM1Ny4zMSwwLDAsNTcuMzEsMCwxMjhzNTcuMzEsMTI4LDEyOCwxMjgsMTI4LTU3LjMxLDEyOC0xMjhTMTk4LjY5LDAsMTI4LDBoMFoiLz4KICA8L2c+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjgsNTZjLTE3LjY3LDAtMzIsMTQuMzMtMzIsMzJ2OGgxNnYtOGMwLTguODIsNy4xOC0xNiwxNi0xNnMxNiw3LjE4LDE2LDE2djMyYzAsOC44Mi03LjE4LDE2LTE2LDE2djE2YzE3LjY3LDAsMzItMTQuMzMsMzItMzJ2LTMyYzAtMTcuNjctMTQuMzMtMzItMzItMzJaIi8+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xNDQsMTYwdjhjMCw4LjgyLTcuMTgsMTYtMTYsMTZzLTE2LTcuMTgtMTYtMTZ2LTMyYzAtOC44Miw3LjE4LTE2LDE2LTE2di0xNmMtMTcuNjcsMC0zMiwxNC4zMy0zMiwzMnYzMmMwLDE3LjY3LDE0LjMzLDMyLDMyLDMyczMyLTE0LjMzLDMyLTMydi04aC0xNloiLz4KICA8L2c+Cjwvc3ZnPg==';

		add_menu_page(
			esc_html__( 'IM', 'daext-interlinks-manager' ),
			esc_html__( 'Interlinks', 'daext-interlinks-manager' ),
			'edit_posts',
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' ),
			$icon_svg
		);

		$this->screen_id_dashboard = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Dashboard', 'daext-interlinks-manager' ),
			esc_html__( 'Dashboard', 'daext-interlinks-manager' ),
			'edit_posts',
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' )
		);

		$this->screen_id_juice = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Juice', 'daext-interlinks-manager' ),
			esc_html__( 'Juice', 'daext-interlinks-manager' ),
			'edit_posts',
			$this->shared->get( 'slug' ) . '-juice',
			array( $this, 'me_display_menu_juice' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'IM - Options', 'daext-interlinks-manager' ),
			esc_html__( 'Options', 'daext-interlinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'Help & Support', 'daext-interlinks-manager' ),
			esc_html__( 'Help & Support', 'daext-interlinks-manager' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'edit_posts',
			'https://daext.com/kb-category/interlinks-manager/',
		);
	}

	/**
	 * Includes the dashboard view.
	 */
	public function me_display_menu_dashboard() {
		include_once 'view/dashboard.php';
	}

	/**
	 * Includes the juice view.
	 */
	public function me_display_menu_juice() {
		include_once 'view/juice.php';
	}

	/**
	 * Includes the options view.
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	/**
	 * Add the meta boxes.
	 *
	 * @return void
	 */
	public function create_meta_box() {

		if ( current_user_can( 'edit_posts' ) ) {

			/**
			 * Load the "Interlinks Options" meta box only in the post types defined
			 * with the "Interlinks Options Post Types" option
			 */
			$interlinks_options_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_options_post_types' ) );
			if ( is_array( $interlinks_options_post_types_a ) ) {
				foreach ( $interlinks_options_post_types_a as $key => $post_type ) {
					add_meta_box(
						'daextinma-meta-options',
						esc_html__( 'Interlinks Options', 'daext-interlinks-manager' ),
						array( $this, 'create_options_meta_box_callback' ),
						$post_type,
						'side',
						'high'
					);
				}
			}
		}

		if ( current_user_can( 'edit_posts' ) ) {

			/**
			 * Load the "Interlinks Optimization" meta box only in the post types
			 * defined with the "Interlinks Optimization Post Types" option.
			 */
			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) ) {
				foreach ( $interlinks_optimization_post_types_a as $key => $post_type ) {
					add_meta_box(
						'daextinma-meta-optimization',
						esc_html__( 'Interlinks Optimization', 'daext-interlinks-manager' ),
						array( $this, 'create_optimization_meta_box_callback' ),
						$post_type,
						'side',
						'default'
					);
				}
			}
		}
	}

	/**
	 * Display the Interlinks Options meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function create_options_meta_box_callback( $post ) {

		// Retrieve the Interlinks Manager data values.
		$seo_power = get_post_meta( $post->ID, '_daextinma_seo_power', true );
		if ( strlen( trim( $seo_power ) ) === 0 ) {
			$seo_power = intval( get_option( $this->shared->get( 'slug' ) . '_default_seo_power' ), 10 );
		}

		?>

		<label for="daextinma-seo-power"><?php esc_html_e( 'SEO Power', 'daext-interlinks-manager' ); ?></label>
		<input type="text" name="daextinma_seo_power" id="daextinma-seo-power"
				value="<?php echo esc_attr( $seo_power ); ?>" class="regular-text" maxlength="7">

		<?php

		// Use nonce for verification.
		wp_nonce_field( plugin_basename( __FILE__ ), 'daextinma_nonce' );
	}

	/**
	 * Display the Interlinks Optimization meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function create_optimization_meta_box_callback( $post ) {

		$this->shared->generate_interlinks_optimization_metabox_html( $post );
	}

	/**
	 * Save the Interlinks Options metadata.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function daextinma_save_meta_interlinks_options( $post_id ) {

		// Security verifications -----------------------------------------------.

		// Verify if this is an auto save routine.
		// If it is our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/*
		 * Verify this came from our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		if ( ! isset( $_POST['daextinma_nonce'] ) || ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['daextinma_nonce'] ) ),
			plugin_basename( __FILE__ )
		) ) {
			return;
		}

		// Verify the capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// End security verifications -------------------------------------------.

		// Save the "SEO Power" only if it's included in the allowed values.
		if ( isset( $_POST['daextinma_seo_power'] ) && intval( $_POST['daextinma_seo_power'], 10 ) !== 0 && intval(
			$_POST['daextinma_seo_power'],
			10
		) <= 1000000 ) {
			update_post_meta( $post_id, '_daextinma_seo_power', intval( $_POST['daextinma_seo_power'], 10 ) );
		}
	}

	/**
	 * Delete the statistics available in the following db tables:
	 *
	 * - wp_daextinma_anchors
	 * - wp_daextinma_archive
	 * - wp_daextinma_juice
	 *
	 * @return void
	 */
	static public function delete_statistics() {

		global $wpdb;

		// Delete the anchors db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_anchors" );

		// Delete the archive db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_archive" );

		// Delete the juice db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextinma_juice" );
	}

}