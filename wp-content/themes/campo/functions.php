<?php
/**
 * campo functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 */

if ( ! function_exists( 'campo_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function campo_setup() {
		
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on campo, use a find and replace
		 * to change 'campo' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'campo', get_template_directory() . '/languages' );
		
	}
endif;
add_action( 'after_setup_theme', 'campo_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function campo_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'campo_content_width', 640 );
}
add_action( 'after_setup_theme', 'campo_content_width', 0 );

/**
 * Register the required plugins for this theme
 */
if ( ! function_exists( 'campo_register_plugins' ) ) {
	function campo_register_plugins() {

		$plugins = array(
	 
			array(
				'name'               => esc_html__( 'Campo', 'campo' ), // The plugin name.
				'slug'               => 'campo', // The plugin slug (typically the folder name).
				'source'             => get_parent_theme_file_path( 'plugins/campo.zip' ), // The plugin source.
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'version'            => '1.0.8', ///!do not change this comment! E.g. 1.0.0. If set, the active plugin must be this version or higher.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			),
			array(
				'name'               => esc_html__( 'Cost Calculator', 'campo' ), // The plugin name.
				'slug'               => 'bt' . '_cost_calculator', // The plugin slug (typically the folder name).
				'source'             => get_parent_theme_file_path( 'plugins/' . 'bt' . '_cost_calculator.zip' ), // The plugin source.
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'version'            => '2.2.3', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			),
			array(
				'name'               => esc_html__( 'Bold Timeline Lite', 'campo' ), // The plugin name.
				'slug'               => 'bold-timeline-lite', // The plugin slug (typically the folder name).
				'required'           => false, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			),
			array(
				'name'               => esc_html__( 'Bold Builder', 'campo' ), // The plugin name.
				'slug'               => 'bold-page-builder', // The plugin slug (typically the folder name).
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			),
			array(
				'name'               => esc_html__( 'BoldThemes WordPress Importer', 'campo' ), // The plugin name.
				'slug'               => 'bt' . '_wordpress_importer', // The plugin slug (typically the folder name).
				'source'             => get_parent_theme_file_path( 'plugins/' . 'bt' . '_wordpress_importer.zip' ), // The plugin source.
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'version'            => '2.8.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			),
			array(
				'name'               => esc_html__( 'Meta Box', 'campo' ), // The plugin name.
				'slug'               => 'meta-box', // The plugin slug (typically the folder name).
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			),
			array(
				'name'               => esc_html__( 'Contact Form 7', 'campo' ), // The plugin name.
				'slug'               => 'contact-form-7', // The plugin slug (typically the folder name).
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			),
			array(
				'name'               => esc_html__( 'Lightweight Sidebar Manager', 'campo' ), // The plugin name.
				'slug'               => 'sidebar-manager', // The plugin slug (typically the folder name).
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			)
		);
	 
		$config = array(
			'default_path' => '',                      // Default absolute path to pre-packaged plugins.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.
			'strings'      => array(
				'page_title'                      => esc_html__( 'Install Required Plugins', 'campo' ),
				'menu_title'                      => esc_html__( 'Install Plugins', 'campo' ),
				'installing'                      => esc_html__( 'Installing Plugin: %s', 'campo' ), // %s = plugin name.
				'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'campo' ),
				'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'campo' ), // %1$s = plugin name(s).
				'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'campo' ), // %1$s = plugin name(s).
				'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'campo' ), // %1$s = plugin name(s).
				'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'campo' ), // %1$s = plugin name(s).
				'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'campo' ), // %1$s = plugin name(s).
				'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'campo' ), // %1$s = plugin name(s).
				'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'campo' ), // %1$s = plugin name(s).
				'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'campo' ), // %1$s = plugin name(s).
				'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'campo' ),
				'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'campo' ),
				'return'                          => esc_html__( 'Return to Required Plugins Installer', 'campo' ),
				'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'campo' ),
				'complete'                        => esc_html__( 'All plugins installed and activated successfully. %s', 'campo' ), // %s = dashboard link.
				'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
			)
		);
	 
		tgmpa( $plugins, $config );
	 
	}
}
add_action( 'tgmpa_register', 'campo_register_plugins' );

/**
 * Enqueue scripts and styles.
 */
function campo_scripts() {
	wp_enqueue_style( 'campo-style', get_stylesheet_uri() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	
	if ( file_exists( get_parent_theme_file_path( 'assets/icon-sets/icons.php' ) ) ) {
		require_once( get_parent_theme_file_path( 'assets/icon-sets/icons.php' ) );
		wp_add_inline_style( 'campo-style', $icons );
	}
}
add_action( 'wp_enqueue_scripts', 'campo_scripts' );

/**
 * Admin style
 */
if ( ! function_exists( 'campo_load_admin_style' ) ) {
	function campo_load_admin_style() {
		
		wp_enqueue_style( 'campo-admin-style', get_parent_theme_file_uri( 'admin-style.css' ) );
		
		
		wp_register_style( 'campo-icons-admin', false );
		wp_enqueue_style( 'campo-icons-admin' );
		require_once( get_parent_theme_file_path( 'assets/icon-sets/icons-admin-style.php' ) );
		wp_add_inline_style( 'campo-icons-admin', $admin_style );
	}
}
add_action( 'admin_enqueue_scripts', 'campo_load_admin_style' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function campo_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'campo_pingback_header' );

/**
 * Load BoldThemes framework
 */

require_once( get_parent_theme_file_path( 'assets/php/before_framework.php' ) );
require_once( get_parent_theme_file_path( 'framework/framework.php' ) );
// require_once( get_parent_theme_file_path( 'assets/php/config.php' ) );
require_once( get_parent_theme_file_path( 'assets/php/after_framework.php' ) );
