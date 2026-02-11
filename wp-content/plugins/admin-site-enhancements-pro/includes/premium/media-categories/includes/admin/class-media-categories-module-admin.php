<?php
/**
 * Admin class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Handles the settings screen.
 *
 * @since   1.0.0
 */
class Media_Categories_Module_Admin {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.0.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Bail early if Media Categories is restricted for the current user.
		if ( function_exists( 'asenha_media_categories_current_user_has_access__premium_only' )
			&& ! asenha_media_categories_current_user_has_access__premium_only()
		) {
			return;
		}

		// Maybe request review.
		// add_action( 'wp_loaded', array( $this, 'maybe_request_review' ) );

		// Admin CSS, JS and Menu.
		add_filter( 'wpzinc_admin_body_class', array( $this, 'admin_body_class' ) ); // WordPress Admin.
		add_filter( 'body_class', array( $this, 'body_class' ) ); // Frontend Editors.

		// Actions.
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_css' ) ); // WordPress Admin.
		add_action( 'admin_head', array( $this, 'side_panel_css' ) ); // WordPress Admin CSS overrides for categories tree width / display.
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_css' ) ); // Frontend Editors.

		// Body class.
		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class_tree_view' ) );

		// Menu.
		// add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Settings Screen.
		// add_action( 'media_categories_module_admin_scripts_js_general', array( $this, 'enqueue_js_settings' ), 10, 4 );
		// add_action( 'media_categories_module_admin_scripts_css_general', array( $this, 'enqueue_css_settings' ) );

		// Addon Screens.
		// add_action( 'media_categories_module_admin_output_settings_panel_general', array( $this, 'output_addon_settings_panel_general' ) );
		// add_action( 'media_categories_module_admin_output_settings_panels', array( $this, 'output_addon_panels' ) );

	}

	/**
	 * Adds body classes on the Media Library screen for Tree View and list/grid view modes.
	 *
	 * @since 7.6.0
	 *
	 * @param string $classes Admin body classes.
	 * @return string
	 */
	public function add_admin_body_class_tree_view( $classes ) {

		// Bail if we can't get the current admin screen, or we're not viewing a screen belonging to this plugin.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $classes;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'upload' !== $screen->id ) {
			return $classes;
		}

		// Add tree-view class if Tree View is enabled.
		if ( Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			$classes .= ' asenha-media-categories-tree-view';
		}

		// Add list/grid view class based on current media view mode.
		$media_view = Media_Categories_Module()->get_class( 'common' )->get_media_view();
		$classes   .= ' asenha-media-categories-' . $media_view . '-view';

		return $classes;
	}

	/**
	 * Maybe request a review
	 *
	 * Won't do this if Pro with Whitelabelling is enabled
	 *
	 * The review notice will display 3 days after this request
	 *
	 * @since   1.2.4
	 */
	// public function maybe_request_review() {

	// 	if ( ! function_exists( 'Media_Categories_Module_Pro' ) ) {
	// 		Media_Categories_Module()->dashboard->request_review();
	// 	} elseif ( ! Media_Categories_Module_Pro()->licensing->has_feature( 'whitelabelling' ) ) {
	// 		Media_Categories_Module()->dashboard->request_review();
	// 	}

	// }

	/**
	 * Registers screen names that should add the wpzinc class to the <body> tag
	 *
	 * @since   1.1.0
	 *
	 * @param   array $screens    Screen Names.
	 * @return  array               Screen Names
	 */
	public function admin_body_class( $screens ) {

		/**
		 * Registers screen names that should add the wpzinc class to the <body> tag
		 *
		 * @since   2.5.7
		 *
		 * @param   array   $screens    Screen Names.
		 * @return  array               Screen Names.
		 */
		$screens = apply_filters( 'media_categories_module_admin_body_class', $screens );

		// Return.
		return $screens;

	}

	/**
	 * Defines CSS classes for the frontend output
	 *
	 * @since   1.1.0
	 *
	 * @param   array $classes    CSS Classes.
	 * @return  array               CSS Classes
	 */
	public function body_class( $classes ) {

		$classes[] = 'wpzinc';

		return $classes;

	}

	/**
	 * Enqueues JS and CSS depending on the screen that's being viewed
	 *
	 * @since   1.0.0
	 */
	public function scripts_css() {

		// Bail if we can't get the current admin screen, or we're not viewing a screen
		// belonging to this plugin.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// Always load non-minified JS (the plugin no longer ships minified JS assets).
		$ext = '';

		// JS: Register Selectize.
		$this->base->get_class( 'media' )->register_selectize_js_css( $ext );

		// Get current screen and the media view (list or grid).
		$screen  = get_current_screen();
		$screens = array();
		$mode    = $this->base->get_class( 'common' )->get_media_view();

		// If we're on the Media screen, enqueue.
		if ( $screen->id === 'upload' || $screen->id === 'media' ) {
			// Add New.
			if ( $screen->action === 'add' ) {
				$this->enqueue_scripts_css( 'media_add_new', $screen, $screens, $mode, $ext );
				return;
			}

			// List or Grid View.
			$this->enqueue_scripts_css( 'media', $screen, $screens, $mode, $ext );
			return;
		}

		// If we're on the Edit Attachment screen, enqueue.
		if ( $screen->id === 'attachment' ) {
			$this->enqueue_scripts_css( 'attachment', $screen, $screens, $mode, $ext );
			return;
		}

	}
	
	/**
	 * CSS override to set the width of the categories tree panel, even to hide it.
	 * 
	 * @since 7.6.0
	 */
	public function side_panel_css() {
		// Bail if we can't get the current admin screen, or we're not viewing a screen
		// belonging to this plugin.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// Get current screen.
		$screen = get_current_screen();

		// Only apply on the Media Library screen.
		if ( ! $screen || 'upload' !== $screen->id ) {
			return;
		}

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return;
		}

        $options = get_option( ASENHA_SLUG_U, array() );

        if ( isset( $options['media_categories_side_panel'] ) ) {
        	if ( is_numeric( $options['media_categories_side_panel'] ) ) {
        		$media_categories_side_panel = intval( $options['media_categories_side_panel'] );
        	} else {
        		$media_categories_side_panel = $options['media_categories_side_panel'];	
        	}
        } else {
        	$media_categories_side_panel = 20;
        }

		$tree_width = '20%';
		$wrap_width = '80%';
		$title_left = '';

		if ( is_numeric( $media_categories_side_panel ) ) {
			if ( 25 === $media_categories_side_panel ) {
				$tree_width = '25%';
				$wrap_width = '75%';
				$title_left = '-33.4%';
			} elseif ( 30 === $media_categories_side_panel ) {
				$tree_width = '30%';
				$wrap_width = '70%';
				$title_left = '-42.8%';
			} elseif ( 35 === $media_categories_side_panel ) {
				$tree_width = '35%';
				$wrap_width = '65%';
				$title_left = '-53.8%';
			} elseif ( 40 === $media_categories_side_panel ) {
				$tree_width = '40%';
				$wrap_width = '60%';
				$title_left = '-66.5%';
			}
		}

		?>
		<style type="text/css" id="media-categories-side-panel-css">
			body.asenha-media-categories-tree-view {
				--asenha-media-categories-tree-width: <?php echo esc_html( $tree_width ); ?>;
				--asenha-media-categories-wrap-width: <?php echo esc_html( $wrap_width ); ?>;
			}

			/* Layout: keep notices/meta full width; lay out Tree View + .wrap side-by-side from first paint. */
			body.asenha-media-categories-tree-view.upload-php #wpbody-content {
				position: relative;
				display: flex;
				flex-wrap: wrap;
				align-items: flex-start;
			}

			/* Restore core positioning for Screen Options / Help tabs (floats don't apply to flex items). */
			body.asenha-media-categories-tree-view.upload-php #wpbody-content > #screen-meta-links {
				position: absolute;
				top: 0;
				right: 20px;
				left: auto;
				width: auto;
				flex: 0 0 auto;
				margin: 0;
				z-index: 1;
			}

			/* WP core sometimes inserts a spacer for the Media Library header layout; hide it in our layout. */
			body.asenha-media-categories-tree-view.upload-php #wpbody-content > .media-library-vertical-spacer {
				display: none;
			}
			body.asenha-media-categories-tree-view.upload-php #wpbody-content > :not(#media-categories-module-tree-view):not(.wrap):not(#screen-meta-links):not(.media-library-vertical-spacer) {
				flex: 0 0 100%;
				width: 100%;
			}

			body.asenha-media-categories-tree-view.upload-php #wpbody-content > #media-categories-module-tree-view {
				display: block;
				flex: 0 0 var(--asenha-media-categories-tree-width);
				width: var(--asenha-media-categories-tree-width);
				box-sizing: border-box;
				padding: 0 10px 0 0;
				margin-top: 53px;
			}

			body.asenha-media-categories-tree-view.upload-php #wpbody-content > .wrap {
				/*
				 * Keep the classic WP right gutter (normally provided by .wrap margin-right),
				 * but compensate for it so the row doesn't wrap next to the Tree View.
				 */
				flex: 0 0 calc(var(--asenha-media-categories-wrap-width) - 20px);
				width: calc(var(--asenha-media-categories-wrap-width) - 20px);
				box-sizing: border-box;
				margin: 0 20px 0 0;
				min-width: 0;
			}

			<?php if ( 'hide' === $media_categories_side_panel ) : ?>
			body.asenha-media-categories-tree-view.upload-php #wpbody-content > #media-categories-module-tree-view {
				display: none;
				width: 0;
				flex: 0 0 0;
			}
			body.asenha-media-categories-tree-view.upload-php #wpbody-content > .wrap {
				width: 100%;
				flex: 0 0 100%;
				margin: 10px 20px 0 2px;
			}
			<?php endif; ?>

			<?php if ( '' !== $title_left ) : ?>
			@media only screen and (min-width: 1301px) {
				body.asenha-media-categories-tree-view.upload-php .wrap h1.wp-heading-inline,
				body.asenha-media-categories-tree-view.upload-php .wrap .wp-heading-inline + .page-title-action {
					position: relative;
					left: <?php echo esc_html( $title_left ); ?>;
				}
			}
			<?php endif; ?>

			@media only screen and (max-width: 768px) {
				body.asenha-media-categories-tree-view.upload-php #wpbody-content {
					display: block;
				}
				body.asenha-media-categories-tree-view.upload-php #wpbody-content > #media-categories-module-tree-view,
				body.asenha-media-categories-tree-view.upload-php #wpbody-content > .wrap {
					width: 100%;
				}
				body.asenha-media-categories-tree-view.upload-php #wpbody-content > #media-categories-module-tree-view {
					padding: 0;
					margin-bottom: 32px;
				}
			}
		</style>
		<?php
	}

	/**
	 * Enqueues scripts and CSS.
	 *
	 * @since   1.0.0
	 *
	 * @param   string       $plugin_screen_name     Plugin Screen Name (general|media).
	 * @param   WP_Screen    $screen                 Current WordPress Screen object.
	 * @param   string|array $screens                Registered Plugin Screens (optional).
	 * @param   string       $mode                   Media View Mode (list|grid).
	 * @param   string       $ext                    If defined, load minified JS.
	 */
	public function enqueue_scripts_css( $plugin_screen_name, $screen, $screens = '', $mode = 'list', $ext = '' ) {

		global $post;

		// Enqueue JS.
		// These scripts are registered in the Dashboard module.
		wp_enqueue_script( 'wpzinc-admin-conditional' );
		wp_enqueue_script( 'wpzinc-admin-tabs' );
		wp_enqueue_script( 'wpzinc-admin' );

		/**
		 * Enqueue Javascript for the given screen and Media View mode.
		 *
		 * @since   1.0.7
		 *
		 * @param   WP_Screen       $screen                 Current WordPress Screen object.
		 * @param   string|array    $screens                Registered Plugin Screens (optional).
		 * @param   string          $mode                   Media View Mode (list|grid).
		 * @param   string          $ext                    If defined, load minified JS.
		 */
		do_action( 'media_categories_module_admin_scripts_js', $screen, $screens, $mode, $ext );

		/**
		 * Enqueue Javascript for the given screen and Media View mode by Plugin
		 * Screen Name.
		 *
		 * @since   1.0.7
		 *
		 * @param   WP_Screen       $screen                 Current WordPress Screen object.
		 * @param   string|array    $screens                Registered Plugin Screens (optional).
		 * @param   string          $mode                   Media View Mode (list|grid).
		 * @param   string          $ext                    If defined, load minified JS
		 */
		do_action( 'media_categories_module_admin_scripts_js_' . $plugin_screen_name, $screen, $screens, $mode, $ext );

		/**
		 * Enqueue Stylesheets (CSS) for the given screen and Media View mode.
		 *
		 * @since   1.0.7
		 *
		 * @param   WP_Screen   $screen                     Current WordPress Screen object.
		 * @param   string|array    $screens                Registered Plugin Screens (optional).
		 * @param   string      $mode                       Media View Mode (list|grid).
		 */
		do_action( 'media_categories_module_admin_scripts_css', $screen, $screens, $mode );

		/**
		 * Enqueue Stylesheets (CSS) for the given screen and Media View mode.
		 *
		 * @since   1.0.7
		 *
		 * @param   WP_Screen       $screen                 Current WordPress Screen object.
		 * @param   string|array    $screens                Registered Plugin Screens (optional).
		 * @param   string          $mode                   Media View Mode (list|grid).
		 */
		do_action( 'media_categories_module_admin_scripts_css_' . $plugin_screen_name, $screen, $screens, $mode );

	}

}
