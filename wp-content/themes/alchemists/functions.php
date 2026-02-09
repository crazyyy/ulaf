<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @author    Dan Fisher
 * @package   Alchemists
 * @since     1.0.0
 * @version   4.7.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme defines
 */
// define( 'ALC_DEV_MODE', true );
define( 'ACF_LITE', ! defined( 'ALC_DEV_MODE' ) );
define( 'THEME_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'THEME_DIR', get_template_directory() );
define( 'THEME_URI', get_template_directory_uri() );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
if ( ! function_exists( 'alchemists_setup' ) ) {
	function alchemists_setup() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Let WordPress manage the document title.
		add_theme_support( 'title-tag' );

		// Enable support for WooCommerce
		add_theme_support( 'woocommerce' );
		
		// WooCommerce gallery features
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		// Enable support for Post Thumbnails
		add_theme_support( 'post-thumbnails' );
		
		// Register image sizes
		alchemists_register_image_sizes();

		// Switch default core markup to output valid HTML5
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
			)
		);

		// Enable support for Post Formats
		add_theme_support( 'post-formats', array( 'video' ) );

		// Add theme support for selective refresh for widgets
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Declare support for Sportspress
		add_theme_support( 'sportspress' );

		// Add support for responsive embeds
		add_theme_support( 'responsive-embeds' );

		// Add support for editor styles
		add_theme_support( 'editor-styles' );

		// Add support for align wide blocks
		add_theme_support( 'align-wide' );

		// Removes support for blocks in widget areas (legacy widgets)
		remove_theme_support( 'widgets-block-editor' );
	}
}
add_action( 'after_setup_theme', 'alchemists_setup' );

/**
 * Register all image sizes
 */
if ( ! function_exists( 'alchemists_register_image_sizes' ) ) {
	function alchemists_register_image_sizes() {
		$image_sizes = array(
			'alchemists_thumbnail'              => array( 380, 270, true ),
			'alchemists_thumbnail-alt'          => array( 380, 197, true ),
			'alchemists_thumbnail-alt2'         => array( 380, 320, true ),
			'alchemists_thumbnail-square'       => array( 400, 400, true ),
			'alchemists_thumbnail-xs'           => array( 80, 80, true ),
			'alchemists_thumbnail-xs-wide'      => array( 90, 68, true ),
			'alchemists_thumbnail-xs-wide-alt'  => array( 112, 84, true ),
			'alchemists_thumbnail-sm'           => array( 280, 218, true ),
			'alchemists_thumbnail-n'            => array( 500, 280, true ),
			'alchemists_thumbnail-tile-lg'      => array( 328, 396, true ),
			'alchemists_thumbnail-tile-xlg'     => array( 778, 458, true ),
			'alchemists_thumbnail-lg'           => array( 773, 380, true ),
			'alchemists_thumbnail-lg-alt'       => array( 773, 408, true ),
			'alchemists_thumbnail-ver'          => array( 380, 490, true ),
			'alchemists_thumbnail-player'       => array( 356, 400, false ),
			'alchemists_thumbnail-player-lg'    => array( 380, 570, true ),
			'alchemists_thumbnail-player-lg-fit' => array( 470, 580, false ),
			'alchemists_thumbnail-player-sm'    => array( 189, 198, array( 'left', 'top' ) ),
			'alchemists_thumbnail-player-block' => array( 140, 210, array( 'center', 'top' ) ),
			'alchemists_team-logo-sm-fit'       => array( 70, 70, false ),
			'alchemists_team-logo-fit'          => array( 100, 100, false ),
			'alchemists_player-xxs'             => array( 40, 40, array( 'center', 'top' ) ),
		);

		// Set post thumbnail size
		set_post_thumbnail_size( 380, 370, true );

		// Register all image sizes
		foreach ( $image_sizes as $name => $args ) {
			add_image_size( $name, $args[0], $args[1], $args[2] );
		}
	}
}

/**
 * Load theme textdomain for translations.
 * Hooked to 'init' to comply with WordPress 6.7.0+ requirements.
 */
if ( ! function_exists( 'alchemists_load_textdomain' ) ) {
	function alchemists_load_textdomain() {
		load_theme_textdomain( 'alchemists', THEME_DIR . '/languages' );
	}
}
add_action( 'init', 'alchemists_load_textdomain' );

/**
 * Register navigation menus.
 * Hooked to 'init' to comply with WordPress 6.7.0+ requirements.
 */
if ( ! function_exists( 'alchemists_register_nav_menus' ) ) {
	function alchemists_register_nav_menus() {
		register_nav_menus(
			array(
				'primary'     => esc_html__( 'Primary Menu', 'alchemists' ),
				'secondary'   => esc_html__( 'Secondary Menu', 'alchemists' ),
				'top_menu'    => esc_html__( 'Top Menu', 'alchemists' ),
				'footer_menu' => esc_html__( 'Footer Menu', 'alchemists' ),
			)
		);
	}
}
add_action( 'init', 'alchemists_register_nav_menus', 10 );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 */
if ( ! function_exists( 'alchemists_content_width' ) ) {
	function alchemists_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'alchemists_content_width', 1170 );
	}
}
add_action( 'after_setup_theme', 'alchemists_content_width', 0 );

/**
 * Register widget areas.
 */
if ( ! function_exists( 'alchemists_widgets_init' ) ) {
	function alchemists_widgets_init() {
		$widget_areas = array(
			array(
				'name'          => esc_html__( 'Sidebar', 'alchemists' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><div class="widget__title card__header"><h4>',
				'after_title'   => '</h4></div><div class="widget__content card__content">',
			),
			array(
				'name'          => esc_html__( 'Home - Sidebar 1', 'alchemists' ),
				'id'            => 'home-sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><div class="widget__title card__header"><h4>',
				'after_title'   => '</h4></div><div class="widget__content card__content">',
			),
			array(
				'name'          => esc_html__( 'Home - Sidebar 2', 'alchemists' ),
				'id'            => 'home-sidebar-2',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><div class="widget__title card__header"><h4>',
				'after_title'   => '</h4></div><div class="widget__content card__content">',
			),
			array(
				'name'          => esc_html__( 'Home - Sidebar 3', 'alchemists' ),
				'id'            => 'home-sidebar-3',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><div class="widget__title card__header"><h4>',
				'after_title'   => '</h4></div><div class="widget__content card__content">',
			),
			array(
				'name'          => esc_html__( 'Megamenu Widget Area 1', 'alchemists' ),
				'id'            => 'megamenu-sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			),
			array(
				'name'          => esc_html__( 'Megamenu Widget Area 2', 'alchemists' ),
				'id'            => 'megamenu-sidebar-2',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			),
			array(
				'name'          => esc_html__( 'Megamenu Widget Area 3', 'alchemists' ),
				'id'            => 'megamenu-sidebar-3',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			),
			array(
				'name'          => esc_html__( 'Megamenu Widget Area 4', 'alchemists' ),
				'id'            => 'megamenu-sidebar-4',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			),
			array(
				'name'          => esc_html__( 'Pushy Panel', 'alchemists' ),
				'id'            => 'alchemists-sidebar-pushy-panel',
				'description'   => esc_html__( 'This panel slides from right side and works only on desktop.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--side-panel %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget__title">',
				'after_title'   => '</h4>',
			),
			array(
				'name'          => esc_html__( 'Video Sidebar', 'alchemists' ),
				'id'            => 'alchemists-sidebar-video',
				'description'   => esc_html__( 'Add widgets here.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><div class="widget__title card__header"><h4>',
				'after_title'   => '</h4></div><div class="widget__content card__content">',
			),
			array(
				'name'          => esc_html__( 'Footer Widget Area 1', 'alchemists' ),
				'id'            => 'alchemists-footer-widget-1',
				'description'   => esc_html__( '1st Footer Widget Area.', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><h4 class="widget__title">',
				'after_title'   => '</h4><div class="widget__content">',
			),
			array(
				'name'          => esc_html__( 'Footer Widget Area 2', 'alchemists' ),
				'id'            => 'alchemists-footer-widget-2',
				'description'   => esc_html__( '2nd Footer Widget Area', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><h4 class="widget__title">',
				'after_title'   => '</h4><div class="widget__content">',
			),
			array(
				'name'          => esc_html__( 'Footer Widget Area 3', 'alchemists' ),
				'id'            => 'alchemists-footer-widget-3',
				'description'   => esc_html__( '3rd Footer Widget Area', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><h4 class="widget__title">',
				'after_title'   => '</h4><div class="widget__content">',
			),
			array(
				'name'          => esc_html__( 'Footer Widget Area 4', 'alchemists' ),
				'id'            => 'alchemists-footer-widget-4',
				'description'   => esc_html__( '4th Footer Widget Area', 'alchemists' ),
				'before_widget' => '<div id="%1$s" class="widget widget--footer %2$s"><div class="widget__content">',
				'after_widget'  => '</div></div>',
				'before_title'  => '</div><h4 class="widget__title">',
				'after_title'   => '</h4><div class="widget__content">',
			),
		);

		// Register all widget areas
		foreach ( $widget_areas as $widget_area ) {
			register_sidebar( $widget_area );
		}
	}
}
add_action( 'widgets_init', 'alchemists_widgets_init' );

/**
 * Register WooCommerce widget areas.
 */
if ( ! function_exists( 'alchemists_woo_widgets_init' ) ) {
	function alchemists_woo_widgets_init() {
		if ( alchemists_wc_exists() ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Shop Sidebar', 'alchemists' ),
					'id'            => 'alchemists-shop-sidebar',
					'description'   => esc_html__( 'Shop Sidebar that appears on Shop pages.', 'alchemists' ),
					'before_widget' => '<div id="%1$s" class="widget widget--sidebar card %2$s"><div class="widget__content card__content">',
					'after_widget'  => '</div></div>',
					'before_title'  => '</div><div class="widget__title card__header"><h4>',
					'after_title'   => '</h4></div><div class="widget__content card__content">',
				)
			);
		}
	}
}
add_action( 'widgets_init', 'alchemists_woo_widgets_init' );

/**
 * Add editor style
 */
add_editor_style( array( 'assets/css/editor-style.css' ) );

/**
 * Enqueue scripts and styles.
 */
if ( ! function_exists( 'alchemists_scripts' ) ) {
	function alchemists_scripts() {
		$alchemists_data = get_option( 'alchemists_data' );

		// Get active sport
		$sport = alchemists_get_active_sport();

		// Check if language is RTL
		$alchemists_dir = is_rtl() ? '-rtl' : '';

		// Vendor styles
		$vendor_styles = array(
			'bootstrap'      => array( 'path' => '/assets/vendor/bootstrap/css/bootstrap.min.css', 'version' => '4.5.3' ),
			'fontawesome'    => array( 'path' => '/assets/fonts/font-awesome/css/all.min.css', 'version' => '6.6.0' ),
			'simpleicons'    => array( 'path' => '/assets/fonts/simple-line-icons/css/simple-line-icons.css', 'version' => '2.4.0' ),
			'magnificpopup'  => array( 'path' => '/assets/vendor/magnific-popup/dist/magnific-popup.css', 'version' => '1.1.0' ),
			'slick'          => array( 'path' => '/assets/vendor/slick/slick.css', 'version' => '1.9.0' ),
		);

		foreach ( $vendor_styles as $handle => $data ) {
			wp_enqueue_style( $handle, THEME_URI . $data['path'], array(), $data['version'] );
		}

		// Main CSS
		wp_enqueue_style( 'alchemists-style', THEME_URI . '/assets/css/style-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );

		// Child theme or parent theme info
		if ( is_child_theme() ) {
			wp_enqueue_style( 'alchemists-parent-info', THEME_URI . '/style.css', array(), THEME_VERSION );
		} else {
			wp_enqueue_style( 'alchemists-info', get_stylesheet_uri(), array(), THEME_VERSION );
		}

		// WooCommerce styles
		if ( alchemists_wc_exists() ) {
			wp_enqueue_style( 'woocommerce', THEME_URI . '/assets/css/woocommerce-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );
		}

		// SportsPress styles
		if ( class_exists( 'SportsPress' ) ) {
			wp_enqueue_style( 'alchemists-sportspress', THEME_URI . '/assets/css/sportspress-' . $sport . $alchemists_dir . '.css', array(), THEME_VERSION );
		}

		// Scripts
		wp_enqueue_script( 'alchemists-bootstrap', THEME_URI . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js', array( 'jquery' ), '4.5.3', true );
		wp_enqueue_script( 'alchemists-core', THEME_URI . '/assets/js/core-min.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'alchemists-init', THEME_URI . '/assets/js/init.js', array( 'jquery' ), THEME_VERSION, true );
		wp_enqueue_script( 'alchemists-skip-link-focus-fix', THEME_URI . '/js/skip-link-focus-fix.js', array(), '20151215', true );

		// Register optional scripts
		wp_register_script( 'alchemists-chartjs', THEME_URI . '/assets/vendor/chartjs/chart-min.js', array(), '2.9.3', true );
		wp_register_script( 'alchemists-marquee', THEME_URI . '/assets/vendor/marquee/jquery.marquee.min.js', array(), '1.5.2', true );

		// Comments reply
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Localize script
		$track_color_default = alchemists_get_default_track_color( $sport );
		$track_color         = isset( $alchemists_data['alchemists__circular-bars-track-color'] ) && ! empty( $alchemists_data['alchemists__circular-bars-track-color'] ) 
			? $alchemists_data['alchemists__circular-bars-track-color'] 
			: $track_color_default;
		$color_primary       = isset( $alchemists_data['color-primary'] ) && ! empty( $alchemists_data['color-primary'] ) 
			? $alchemists_data['color-primary'] 
			: '#ffdc11';

		wp_localize_script(
			'alchemists-init',
			'alchemistsData',
			array(
				'color_primary' => $color_primary,
				'track_color'   => $track_color,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'alchemists_scripts' );

/**
 * Get active sport preset
 */
if ( ! function_exists( 'alchemists_get_active_sport' ) ) {
	function alchemists_get_active_sport() {
		if ( alchemists_sp_preset( 'soccer' ) ) {
			return 'soccer';
		} elseif ( alchemists_sp_preset( 'football' ) ) {
			return 'football';
		} elseif ( alchemists_sp_preset( 'esports' ) ) {
			return 'esports';
		}
		return 'basketball';
	}
}

/**
 * Get default track color based on sport
 */
if ( ! function_exists( 'alchemists_get_default_track_color' ) ) {
	function alchemists_get_default_track_color( $sport = 'basketball' ) {
		$colors = array(
			'football' => '#4e4d73',
			'esports'  => '#4b3b60',
			'default'  => '#ecf0f6',
		);

		return isset( $colors[ $sport ] ) ? $colors[ $sport ] : $colors['default'];
	}
}

/**
 * Load child theme styles
 */
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
		if ( empty( $css ) ) {
			return;
		}

		wp_register_style( 'alc-custom-css', false, array(), THEME_VERSION );
		wp_enqueue_style( 'alc-custom-css' );
		wp_add_inline_style( 'alc-custom-css', wp_strip_all_tags( $css ) );
	}
}

/**
 * Admin styling
 */
if ( ! function_exists( 'alchemists_custom_admin_css' ) ) {
	function alchemists_custom_admin_css() {
		if ( is_admin() ) {
			wp_enqueue_style( 'alchemists-custom-admin', THEME_URI . '/admin/assets/css/df-admin.css', array(), THEME_VERSION );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'alchemists_custom_admin_css' );

/**
 * Enable shortcodes in menu items
 */
add_filter( 'wp_nav_menu_items', 'do_shortcode' );

/**
 * Page Preloader
 */
if ( ! function_exists( 'alchemists_page_preloader' ) ) {
	function alchemists_page_preloader() {
		$alchemists_data = get_option( 'alchemists_data' );
		$preloader       = isset( $alchemists_data['alchemists__opt-pageloader'] ) ? $alchemists_data['alchemists__opt-pageloader'] : true;
		$spinner_img     = isset( $alchemists_data['alchemists__opt-preloader-img'] ) ? $alchemists_data['alchemists__opt-preloader-img'] : false;

		if ( ! $preloader ) {
			return;
		}

		$preloader_classes = array( 'preloader' );
		$preloader_style   = '';

		if ( $spinner_img && isset( $alchemists_data['alchemists__opt-preloader-img-url']['url'] ) ) {
			$preloader_classes[] = 'preloader--img';
			$spinner_img_url     = esc_url( $alchemists_data['alchemists__opt-preloader-img-url']['url'] );
			$preloader_style     = ' style="background-image: url(' . $spinner_img_url . ')"';
		}
		?>
		<div id="js-preloader-overlay" class="preloader-overlay">
			<div id="js-preloader" class="<?php echo esc_attr( implode( ' ', $preloader_classes ) ); ?>"<?php echo $preloader_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
		</div>
		<?php
	}
}
add_action( 'alchemists_before_body_content', 'alchemists_page_preloader' );

/**
 * Check if WPBakery Page Builder exists
 */
if ( ! function_exists( 'alchemists_vc_exists' ) ) {
	function alchemists_vc_exists() {
		return class_exists( 'Vc_Manager' );
	}
}

/**
 * Check if WooCommerce exists
 */
if ( ! function_exists( 'alchemists_wc_exists' ) ) {
	function alchemists_wc_exists() {
		return class_exists( 'WooCommerce' );
	}
}

/**
 * Disabled update notification for premium plugins
 */
if ( ! function_exists( 'alchemists_acf_updates' ) ) {
	function alchemists_acf_updates() {
		if ( function_exists( 'acf_update_setting' ) ) {
			acf_update_setting( 'show_updates', false );
		}
	}
}
add_action( 'acf/init', 'alchemists_acf_updates' );

/**
 * Load Menu Custom Fields on backend
 */
if ( ! function_exists( 'alchemists_load_menu_custom_fields' ) ) {
	function alchemists_load_menu_custom_fields() {
		require_once THEME_DIR . '/admin/menu-item-custom-fields/menu-item-custom-fields.php';
	}
}
add_action( 'init', 'alchemists_load_menu_custom_fields', 8 );

/**
 * Load menu scripts and styles
 */
if ( ! function_exists( 'alchemists_menus_hook' ) ) {
	function alchemists_menus_hook() {
		wp_enqueue_script( 'alchemists-menus-scripts', THEME_URI . '/admin/js/min/menus-scripts-min.js', array( 'jquery' ), THEME_VERSION, true );
		wp_enqueue_style( 'alchemists-menus-styles', THEME_URI . '/admin/css/menus-styles.css', array(), THEME_VERSION );
	}

	if ( function_exists( 'alchemists_theme_is_menus' ) && alchemists_theme_is_menus() ) {
		add_action( 'admin_init', 'alchemists_menus_hook' );
	}
}

/**
 * Redux Framework cleanup
 */
if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
	// Remove Redux demo mode link
	function alchemists_remove_demo_mode_link() {
		if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			remove_action( 'admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
		}
	}
	add_action( 'init', 'alchemists_remove_demo_mode_link' );

	// Remove Redux Dashboard meta
	function alchemists_remove_dashboard_meta() {
		remove_meta_box( 'redux_dashboard_widget', 'dashboard', 'side' );
	}
	add_action( 'wp_dashboard_setup', 'alchemists_remove_dashboard_meta' );
}

/**
 * Include required files
 */
require_once THEME_DIR . '/inc/sp-global-functions.php';
require_once THEME_DIR . '/inc/acf-fields.php';
require_once THEME_DIR . '/inc/fallbacks.php';
require_once THEME_DIR . '/inc/template-tags.php';
require_once THEME_DIR . '/inc/template-functions.php';
require_once THEME_DIR . '/admin/notices/admin-notices.php';
require_once THEME_DIR . '/inc/customizer.php';
require_once THEME_DIR . '/inc/jetpack.php';
require_once THEME_DIR . '/admin/custom-nav-walker/custom-nav-walker.php';

// Redux Framework
if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
	require_once THEME_DIR . '/admin/admin-init.php';
	require_once THEME_DIR . '/inc/custom-styling.php';
}

// WPBakery Page Builder
if ( alchemists_vc_exists() ) {
	require_once THEME_DIR . '/inc/vc-functions.php';
	require_once THEME_DIR . '/inc/vc-templates.php';
}

// SportsPress
if ( class_exists( 'SportsPress' ) ) {
	require_once THEME_DIR . '/inc/sp-functions.php';
}

// WooCommerce
if ( alchemists_wc_exists() ) {
	require_once THEME_DIR . '/inc/wc-functions.php';
}

// TGMPA (only for admins)
if ( current_user_can( 'manage_options' ) && is_admin() ) {
	require_once THEME_DIR . '/admin/tgm/tgm-init.php';
}

/**
 * Get header layout with proper sanitization
 *
 * @param array $alchemists_data Theme options data.
 * @return string Header layout
 * @since 4.6.0
 */
if ( ! function_exists( 'alchemists_get_header_layout' ) ) {
	function alchemists_get_header_layout( $alchemists_data = array() ) {
		// Check URL parameter first (for demo/testing)
		if ( isset( $_GET['header-layout'] ) && ! empty( $_GET['header-layout'] ) ) {
			$header_layout = sanitize_key( wp_unslash( $_GET['header-layout'] ) );
			
			// Validate against allowed layouts
			$allowed_layouts = array( 'layout-1', 'layout-2', 'layout-3', 'layout-4', 'layout-5' );
			if ( in_array( $header_layout, $allowed_layouts, true ) ) {
				return $header_layout;
			}
		}

		// Get from theme options
		if ( empty( $alchemists_data ) ) {
			$alchemists_data = get_option( 'alchemists_data', array() );
		}

		$header_layout = isset( $alchemists_data['alchemists__header-layout'] ) 
			? sanitize_key( $alchemists_data['alchemists__header-layout'] ) 
			: 'layout-1';

		/**
		 * Filter header layout
		 *
		 * @since 4.6.0
		 * @param string $header_layout Header layout
		 */
		return apply_filters( 'alchemists_header_layout', $header_layout );
	}
}