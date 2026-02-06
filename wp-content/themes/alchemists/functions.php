<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.1
 */

/**
 * Theme defines
 */
// define( 'ALC_DEV_MODE', true );
define( 'ACF_LITE', ! defined( 'ALC_DEV_MODE' ) );
define( 'THEME_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );


if ( ! function_exists( 'alchemists_setup' ) ) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function alchemists_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
		add_theme_support( 'title-tag' );

		/*
		* Enable support for WooCommerce
		*/
		add_theme_support( 'woocommerce' );

		/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 380, 370, true ); // Normal post thumbnails
		add_image_size('alchemists_thumbnail', 380, 270, true); // Thumbnail Normal
		add_image_size('alchemists_thumbnail-alt', 380, 197, true); // Thumbnail Normal
		add_image_size('alchemists_thumbnail-alt2', 380, 320, true); // Thumbnail Normal
		add_image_size('alchemists_thumbnail-square', 400, 400, true); // Thumbnail Square
		add_image_size('alchemists_thumbnail-xs', 80, 80, true); // Thumbnail XS
		add_image_size('alchemists_thumbnail-xs-wide', 90, 68, true); // Thumbnail XS Wide
		add_image_size('alchemists_thumbnail-xs-wide-alt', 112, 84, true); // Thumbnail XS Wide
		add_image_size('alchemists_thumbnail-sm', 280, 218, true); // Thumbnail SM
		add_image_size('alchemists_thumbnail-n', 500, 280, true); // Thumbnail Normal
		add_image_size('alchemists_thumbnail-tile-lg', 328, 396, true); // Thumbnail Tile
		add_image_size('alchemists_thumbnail-tile-xlg', 778, 458, true); // Thumbnail Tile XL
		add_image_size('alchemists_thumbnail-lg', 773, 380, true); // Thumbnail Large
		add_image_size('alchemists_thumbnail-lg-alt', 773, 408, true); // Thumbnail Large
		add_image_size('alchemists_thumbnail-ver', 380, 490, true); // Thumbnail Large
		add_image_size('alchemists_thumbnail-player', 356, 400, false); // Player Normal
		add_image_size('alchemists_thumbnail-player-lg', 380, 570, true); // Player Large
		add_image_size('alchemists_thumbnail-player-lg-fit', 470, 580, false); // Player Large - fit
		add_image_size('alchemists_thumbnail-player-sm', 189, 198, array('left', 'top')); // Player Small
		add_image_size('alchemists_thumbnail-player-block', 140, 210, [ 'center', 'top' ]); // Player Small (Team Blocks)
		add_image_size('alchemists_team-logo-sm-fit', 70, 70, false ); // Team Logo Small - fit
		add_image_size('alchemists_team-logo-fit', 100, 100, false ); // Team Logo Normal - fit
		add_image_size('alchemists_player-xxs', 40, 40, array('center', 'top')); // Thumbnail XXS

		/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
		) );

		/*
		* Enable support for Post Formats.
		* See http://codex.wordpress.org/Post_Formats
		*/
		add_theme_support( 'post-formats', array(
			'video',
		) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		* Declare support for Sportspress.
		*/
		add_theme_support( 'sportspress' );

		/**
		 * Removes support for blocks in widget areas.
		 */
		remove_theme_support( 'widgets-block-editor' );

	}
}
add_action( 'after_setup_theme', 'alchemists_setup' );

/**
 * Load theme textdomain for translations.
 * This is hooked to 'init' to comply with WordPress 6.7.0+ requirements.
 */
if ( ! function_exists( 'alchemists_load_textdomain' ) ) {
	function alchemists_load_textdomain() {
		/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on alchemists, use a find and replace
		* to change 'alchemists' to the name of your theme in all the template files.
		*/
		load_theme_textdomain( 'alchemists', get_template_directory() . '/languages' );
	}
}
add_action( 'init', 'alchemists_load_textdomain', 1 );

/**
 * Register navigation menus.
 * This is hooked to 'init' to comply with WordPress 6.7.0+ requirements for translation loading.
 */
if ( ! function_exists( 'alchemists_register_nav_menus' ) ) {
	function alchemists_register_nav_menus() {
		// This theme uses wp_nav_menu() in four locations.
		register_nav_menus( array(
			'primary'       => esc_html__( 'Primary Menu', 'alchemists' ),
			'secondary'     => esc_html__( 'Secondary Menu', 'alchemists' ),
			'top_menu'      => esc_html__( 'Top Menu', 'alchemists' ),
			'footer_menu'   => esc_html__( 'Footer Menu', 'alchemists' ),
		) );
	}
}
add_action( 'init', 'alchemists_register_nav_menus', 10 );


/**
 * SportsPress global functions
 */
include get_template_directory() . '/inc/sp-global-functions.php';

/**
 * Load ACF fields
 */
require_once get_template_directory() . '/inc/acf-fields.php';


/**
 * Add Redux Framework & extras
 */
if ( class_exists('ReduxFrameworkPlugin') ) {

	// Init Redux Framework
	require get_template_directory() . '/admin/admin-init.php';

	// Remove Redux demo mode link
	function alchemists_remove_demo_mode_link() {
		remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
	}
	add_action('init', 'alchemists_remove_demo_mode_link');

	// Remove Redux Dashboard meta
	function alchemists_remove_dashboard_meta() {
		remove_meta_box( 'redux_dashboard_widget', 'dashboard', 'side', 'high' );
	}
	add_action( 'admin_init', 'alchemists_remove_dashboard_meta' );

	/**
	 * Load theme styling
	 */
	include_once get_template_directory() . '/inc/custom-styling.php';
}


/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function alchemists_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'alchemists_content_width', 640 );
}
add_action( 'after_setup_theme', 'alchemists_content_width', 0 );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function alchemists_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'alchemists' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><div class="widget__title card__header"><h4>',
		'after_title'   => '</h4></div><div class="widget__content card__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Home - Sidebar 1', 'alchemists' ),
		'id'            => 'home-sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><div class="widget__title card__header"><h4>',
		'after_title'   => '</h4></div><div class="widget__content card__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Home - Sidebar 2', 'alchemists' ),
		'id'            => 'home-sidebar-2',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><div class="widget__title card__header"><h4>',
		'after_title'   => '</h4></div><div class="widget__content card__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Home - Sidebar 3', 'alchemists' ),
		'id'            => 'home-sidebar-3',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><div class="widget__title card__header"><h4>',
		'after_title'   => '</h4></div><div class="widget__content card__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Megamenu Widget Area 1', 'alchemists' ),
		'id'            => 'megamenu-sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Megamenu Widget Area 2', 'alchemists' ),
		'id'            => 'megamenu-sidebar-2',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Megamenu Widget Area 3', 'alchemists' ),
		'id'            => 'megamenu-sidebar-3',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Megamenu Widget Area 4', 'alchemists' ),
		'id'            => 'megamenu-sidebar-4',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Pushy Panel', 'alchemists' ),
		'id'            => 'alchemists-sidebar-pushy-panel',
		'description'   => esc_html__( 'This panel slides from right side and works only on desktop.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--side-panel %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="widget__title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Video Sidebar', 'alchemists' ),
		'id'            => 'alchemists-sidebar-video',
		'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><div class="widget__title card__header"><h4>',
		'after_title'   => '</h4></div><div class="widget__content card__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widget Area 1', 'alchemists' ),
		'id'            => 'alchemists-footer-widget-1',
		'description'   => esc_html__( '1st Footer Widget Area.', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><h4 class="widget__title">',
		'after_title'   => '</h4><div class="widget__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widget Area 2', 'alchemists' ),
		'id'            => 'alchemists-footer-widget-2',
		'description'   => esc_html__( '2nd Footer Widget Area', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><h4 class="widget__title">',
		'after_title'   => '</h4><div class="widget__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widget Area 3', 'alchemists' ),
		'id'            => 'alchemists-footer-widget-3',
		'description'   => esc_html__( '3rd Footer Widget Area', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><h4 class="widget__title">',
		'after_title'   => '</h4><div class="widget__content">',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widget Area 4', 'alchemists' ),
		'id'            => 'alchemists-footer-widget-4',
		'description'   => esc_html__( '4th Footer Widget Area', 'alchemists' ),
		'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
		'after_widget'  => '</div></div>',
		'before_title'  => '</div><h4 class="widget__title">',
		'after_title'   => '</h4><div class="widget__content">',
	) );
}
add_action( 'widgets_init', 'alchemists_widgets_init' );

function alchemists_woo_widgets_init() {
	// Woocommerce Shop Sidebar
	if( alchemists_wc_exists() ){
		register_sidebar( array(
			'name'          => esc_html__( 'Shop Sidebar', 'alchemists' ),
			'id'            => 'alchemists-shop-sidebar',
			'description'   => esc_html__( 'Shop Sidebar that appears on Shop pages.', 'alchemists' ),
			'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
			'after_widget'  => '</div></div>',
			'before_title'  => '</div><div class="widget__title card__header"><h4>',
			'after_title'   => '</h4></div><div class="widget__content card__content">',
		));
	}
}
add_action( 'widgets_init', 'alchemists_woo_widgets_init' );


/*
	* This theme styles the visual editor to resemble the theme style,
	* specifically font, colors, icons, and column width.
	*/
add_editor_style( array( 'assets/css/editor-style.css') );


/**
 * Enqueue scripts and styles.
 */
if( !function_exists( 'alchemists_scripts' ) ) {
	function alchemists_scripts() {

		$alchemists_data = get_option( 'alchemists_data' );

		// Get active sport
		$sport = 'basketball';

		if ( alchemists_sp_preset('soccer') ) {
			$sport = 'soccer';
		} elseif ( alchemists_sp_preset('football') ) {
			$sport = 'football';
		} elseif ( alchemists_sp_preset('esports') ) {
			$sport = 'esports';
		}

		// Check if language is RTL
		$alchemists_dir = '';
		if ( is_rtl() ) {
			$alchemists_dir = '-rtl';
		}

		// Styles
		// Vendors CSS
		wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/css/bootstrap.min.css', array(), '4.5.3' );
		wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/assets/fonts/font-awesome/css/all.min.css', array(), '6.6.0' );
		wp_enqueue_style( 'simpleicons', get_template_directory_uri() . '/assets/fonts/simple-line-icons/css/simple-line-icons.css', array(), '2.4.0' );
		wp_enqueue_style( 'magnificpopup', get_template_directory_uri() . '/assets/vendor/magnific-popup/dist/magnific-popup.css', array(), '1.1.0' );
		wp_enqueue_style( 'slick', get_template_directory_uri() . '/assets/vendor/slick/slick.css', array(), '1.9.0' );

		// Main CSS
		wp_enqueue_style( 'alchemists-style', get_template_directory_uri() . '/assets/css/style-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );

		// If using a child theme, auto-load the parent theme style.
		if ( is_child_theme() ) {
			wp_enqueue_style( 'alchemists-parent-info', trailingslashit( get_template_directory_uri() ) . 'style.css', array(), THEME_VERSION );
		} else {
			wp_enqueue_style( 'alchemists-info', get_stylesheet_uri(), array(), THEME_VERSION );
		}

		// Add styles if WooCommerce installed
		if ( alchemists_wc_exists() ) {
			wp_enqueue_style( 'woocommerce', get_template_directory_uri() . '/assets/css/woocommerce-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );
		}

		// Add styles if Sporspress installed
		if ( class_exists( 'SportsPress' ) ) {
			wp_enqueue_style( 'alchemists-sportspress', get_template_directory_uri() . '/assets/css/sportspress-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );
		}


		//Scripts
		wp_enqueue_script( 'alchemists-bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js', array('jquery'), '4.5.3', true );
		wp_enqueue_script( 'alchemists-core', get_template_directory_uri() . '/assets/js/core-min.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'alchemists-init', get_template_directory_uri() . '/assets/js/init.js', array('jquery'), THEME_VERSION, true );
		wp_enqueue_script( 'alchemists-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

		wp_register_script( 'alchemists-chartjs', get_template_directory_uri() . '/assets/vendor/chartjs/chart-min.js', array(), '2.9.3', true );
		wp_register_script( 'alchemists-marquee', get_template_directory_uri() . '/assets/vendor/marquee/jquery.marquee.min.js', array(), '1.5.2', true );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Set default track color depends on sport version
		$track_color_default = '#ecf0f6';
		if ( alchemists_sp_preset( 'football' ) ) {
			$track_color_default = '#4e4d73';
		} elseif ( alchemists_sp_preset( 'esports' ) ) {
			$track_color_default = '#4b3b60';
		}

		// set Track Color
		$track_color     = isset( $alchemists_data['alchemists__circular-bars-track-color'] ) && ! empty( $alchemists_data['alchemists__circular-bars-track-color'] ) ? $alchemists_data['alchemists__circular-bars-track-color'] : $track_color_default;

		$color_primary   = isset( $alchemists_data['color-primary'] ) && ! empty( $alchemists_data['color-primary'] ) ? $alchemists_data['color-primary'] : '#ffdc11';
		wp_localize_script( 'alchemists-init', 'alchemistsData', array(
			'color_primary' => $color_primary,
			'track_color' => $track_color,
		));
	}
	add_action( 'wp_enqueue_scripts', 'alchemists_scripts' );
}

// Load child style.css and enqueue it after the all styles
if ( is_child_theme() ) {
	function alchemists_load_child_theme_styles() {
		$child_theme_version = wp_get_theme( get_stylesheet() )->get( 'Version' );
		wp_enqueue_style( 'alchemists-child', get_stylesheet_uri(), array(), $child_theme_version );
	}
	add_action( 'wp_enqueue_scripts', 'alchemists_load_child_theme_styles', 99 );
}

/**
 * Custom CSS
 */
if ( ! function_exists( 'alc_custom_css' ) ) {
	function alc_custom_css( $css ) {
		wp_register_style( 'alc-custom-css', false );
		wp_enqueue_style( 'alc-custom-css' );

		wp_add_inline_style( 'alc-custom-css', $css );
	}
}

/**
 * Admin styling
 */
if ( ! function_exists('alchemists_custom_admin_css') ) {
	function alchemists_custom_admin_css(){
		if ( is_admin() ) {
			wp_enqueue_style( 'alchemists-custom-admin', get_template_directory_uri(). '/admin/assets/css/df-admin.css', array(), THEME_VERSION);
		}
	}
}
add_action( 'admin_enqueue_scripts', 'alchemists_custom_admin_css' );


// Shortcodes in Menu
add_filter('wp_nav_menu_items', 'do_shortcode');


/**
 * Page Preloader
 */
if ( ! function_exists('alchemists_page_preloader') ) {
	function alchemists_page_preloader() {
		$alchemists_data = get_option( 'alchemists_data' );
		$preloader       = isset( $alchemists_data['alchemists__opt-pageloader'] ) ? $alchemists_data['alchemists__opt-pageloader'] : true;
		$spinner_img     = isset( $alchemists_data['alchemists__opt-preloader-img'] ) ? $alchemists_data['alchemists__opt-preloader-img'] : false;

		// Default classes and styles
		$preloader_classes = array( 'preloader' );
		$preloader_style   = array();

		// Check for image based spinner
		if ( $spinner_img ) {
			$preloader_classes[] = 'preloader--img';
			$spinner_img_url     = isset( $alchemists_data['alchemists__opt-preloader-img-url']['url'] ) ? $alchemists_data['alchemists__opt-preloader-img-url']['url'] : false;
			$preloader_style[]   = 'style="background-image: url(' . esc_url( $spinner_img_url ) . ')"';
		}

		// Check if Preloader enabled
		if ( $preloader ) : ?>
			<div id="js-preloader-overlay" class="preloader-overlay">
				<div id="js-preloader" class="<?php echo implode( ' ', $preloader_classes ); ?>" <?php echo implode( ' ', $preloader_style ); ?>></div>
			</div>
		<?php endif;
	}
}
add_action( 'alchemists_before_body_content', 'alchemists_page_preloader' );


/**
 * WPBakery Page Builder (formerly Visual Composer) Functions
 */
if ( ! function_exists( 'alchemists_vc_exists' ) ) {
	function alchemists_vc_exists() {
		if ( class_exists( 'Vc_Manager' ) ) {
			return true;
		} else {
			return false;
		}
	}
}


// Include Visual Composer custom functions
if ( alchemists_vc_exists() == true ) {
	require get_template_directory() . '/inc/vc-functions.php';
	require get_template_directory() . '/inc/vc-templates.php';
}

/**
 * SportsPress functions
 */
if ( class_exists( 'SportsPress' ) ) {
	include_once get_template_directory() . '/inc/sp-functions.php';
}


/**
 * WooCommerce functions
 */
if ( ! function_exists( 'alchemists_wc_exists' ) ) {
	function alchemists_wc_exists() {
		if ( class_exists( 'woocommerce' ) ) {
			return true;
		} else {
			return false;
		}
	}
}
if ( alchemists_wc_exists() == true ) {
	include_once get_template_directory() . '/inc/wc-functions.php';
}


/**
 * Disabled update notification for premium plugins
 */
add_action( 'acf/init', 'alchemists_acf_updates' );
function alchemists_acf_updates() {
	acf_update_setting( 'show_updates', false );
}


/**
 * Fallbacks
 */
include_once get_template_directory() . '/inc/fallbacks.php';

/**
 * Custom template tags for this theme.
 */
include get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
include get_template_directory() . '/inc/template-functions.php';

/**
 * Admin dashboard notices
 */
include get_template_directory() . '/admin/notices/admin-notices.php';

/**
 * Customizer additions.
 */
include get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
include get_template_directory() . '/inc/jetpack.php';


/**
 * Load Menu Custom Fields on backend
 * Hooked to init to comply with WordPress 6.7.0+ translation requirements
 */
function alchemists_load_menu_custom_fields() {
	require_once get_template_directory() . '/admin/menu-item-custom-fields/menu-item-custom-fields.php';
}
add_action( 'init', 'alchemists_load_menu_custom_fields', 8 );

if(!function_exists('alchemists_menus_hook')) {
	function alchemists_menus_hook() {
		wp_enqueue_script( 'alchemists-menus-scripts', get_template_directory_uri() . '/admin/js/min/menus-scripts-min.js', array( 'jquery' ), false, true );
		wp_enqueue_style( 'alchemists-menus-styles', get_template_directory_uri() . '/admin/css/menus-styles.css' );
	}

	if ( alchemists_theme_is_menus() ) {
		add_action( 'admin_init', 'alchemists_menus_hook' );
	}
}

/**
 * Load Menu Custom Fields on frontend
 */
require_once get_template_directory() . '/admin/custom-nav-walker/custom-nav-walker.php';


/**
 * Load TGMPA
 * only for admins ('manage_options' capability)
 */
if ( current_user_can( 'manage_options' ) ) {
	include_once get_template_directory() . '/admin/tgm/tgm-init.php';
}
