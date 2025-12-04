<?php
/**
 * Author: Vitalii A | @knaipa
 * URL: https://github.com/crazyyy/wp-framework
 *
 * Enqueue scripts and styles.
 *
 * @package WPEB
 */

/**
 * Enqueue styles and scripts for WP Console Editor (admin only).
 *
 * @return void
 */
function wpeb_enqueue_editor_assets() {
	wp_enqueue_style(
		'wpeb-wp-console-style',
		WPEB_TEMPLATE_URL . '/css/wpeb-wp-console-style.css',
		array(),
		WPEB_VERSION
	);

	wp_enqueue_style(
		'wpeb-editor-style',
		WPEB_TEMPLATE_URL . '/css/wpeb-editor-style.css',
		array(),
		WPEB_VERSION
	);

	wp_enqueue_script(
		'wpeb-editor-script',
		WPEB_TEMPLATE_URL . '/js/wpeb-editor-scripts.js',
		array(),
		WPEB_VERSION,
		true // Load in footer for admin pages as well.
	);
}
add_action( 'admin_enqueue_scripts', 'wpeb_enqueue_editor_assets' );

/**
 * Enqueue Bootstrap CSS in the head + theme styles.
 *
 * @return void
 */
function wpeb_enqueue_theme_styles() {
	// Remove conflicting/unused style if it exists.
	wp_dequeue_style( 'fancybox' );

	// Bootstrap CSS should be in <head>. WP prints styles in head by default.
	wp_enqueue_style(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		array(),
		'5.3.3',
		'all'
	);

	// Theme main stylesheet after Bootstrap so you can override Bootstrap styles.
	wp_enqueue_style(
		'wpeb-style',
		WPEB_TEMPLATE_URL . '/css/main.css',
		array( 'bootstrap' ),
		WPEB_VERSION,
		'all'
	);
}
add_action( 'wp_enqueue_scripts', 'wpeb_enqueue_theme_styles', 10 );

/**
 * Enqueue theme header scripts (libraries) if needed.
 *
 * @return void
 */
function wpeb_enqueue_theme_header_scripts() {
	// If a plugin/theme registered these handles, deregistering is safe.
	wp_deregister_script( 'modernizr' );
	wp_deregister_script( 'jquery-form' );

	// Load jQuery from CDN (note: this replaces WP's bundled jQuery).
	// Keep it only if you are sure your site relies on this specific version.
	wp_enqueue_script(
		'jquery',
		'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js',
		array(),
		'3.7.1',
		false // Header load.
	);

	wp_enqueue_script(
		'jquery-migrate',
		'https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.4.1/jquery-migrate.min.js',
		array( 'jquery' ),
		'3.4.1',
		false // Header load.
	);

	// Modernizr usually should be in head (feature detection before render).
	wp_enqueue_script(
		'modernizr',
		'https://cdn.jsdelivr.net/npm/modernizr@3.12.0/modernizr.min.js',
		array(),
		'3.12.0',
		false // Header load.
	);
}
add_action( 'wp_enqueue_scripts', 'wpeb_enqueue_theme_header_scripts', 20 );

/**
 * Enqueue theme footer scripts (Bootstrap JS + theme scripts).
 *
 * @return void
 */
function wpeb_enqueue_theme_footer_scripts() {
	// Bootstrap JS (bundle includes Popper) in footer.
	wp_enqueue_script(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
		array(),
		'5.3.3',
		true // Footer.
	);

	// Your theme scripts in footer.
	wp_register_script(
		'wpeb-scripts',
		WPEB_TEMPLATE_URL . '/js/scripts.js',
		array( 'jquery', 'bootstrap' ), // Depends on jQuery + Bootstrap (if you use both).
		WPEB_VERSION,
		true
	);

	// Add 'defer' to improve performance without blocking rendering.
	// Note: Don't defer if your script must run before DOM is ready.
	wp_script_add_data( 'wpeb-scripts', 'defer', true );

	wp_enqueue_script( 'wpeb-scripts' );

	// Localize variables for JS (safe output by WP).
	wp_localize_script(
		'wpeb-scripts',
		'adminAjax',
		array(
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'templatePath'   => WPEB_TEMPLATE_URL,
			'posts_per_page' => (int) get_option( 'posts_per_page' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wpeb_enqueue_theme_footer_scripts', 30 );
