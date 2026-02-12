<?php
/**
 * Plugin Name: Officials Templates for SportsPress
 * Description: Add templates to Officials pages
 * Version: 1.6
 * Author: Savvas
 * Author URI: https://profiles.wordpress.org/savvasha/
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl.html
 *
 * @package officials-templates-for-sportspress
 * @category Core
 * @author savvasha
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
if ( ! defined( 'OTFS_PLUGIN_BASE' ) ) {
	define( 'OTFS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'OTFS_PLUGIN_DIR' ) ) {
	define( 'OTFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'OTFS_PLUGIN_URL' ) ) {
	define( 'OTFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Define plugin version.
if ( ! defined( 'OTFS_VERSION' ) ) {
	define( 'OTFS_VERSION', '1.6' ); // Update this when needed
}

// Hooks.
add_filter( 'sportspress_get_settings_pages', 'otfs_add_settings_page' );
add_action( 'sportspress_init', 'otfs_add_officials_templates' );
add_action( 'sportspress_include_post_type_handlers', 'otfs_include_post_type_handlers' );
add_action( 'plugins_loaded', 'otfs_load_officials_class', 99 );
add_action( 'sportspress_meta_box_performance_details', 'otfs_add_visibility_option' );
add_action( 'sportspress_meta_box_statistic_details', 'otfs_add_visibility_option' );
add_action( 'sportspress_process_sp_performance_meta', 'otfs_save_visibility_option', 15, 2 );
add_action( 'sportspress_process_sp_statistic_meta', 'otfs_save_visibility_option', 15, 2 );

/**
 * Make sure that all plugins are loaded before extend SP_Custom_Post Class.
 */
function otfs_load_officials_class() {
	// Exit if SportsPress is not installed and activated.
	if ( class_exists( 'SP_Custom_Post' ) ) {
		// Load needed class functions.
		include_once 'includes/class-otfs-officials.php';
	}
}

/**
 * Add settings page.
 *
 * @param array $settings An array of SportsPress settings pages.
 * @return array
 */
function otfs_add_settings_page( $settings = array() ) {
	$settings[] = include 'includes/class-otfs-settings-officials.php';
	return $settings;
}

/**
 * Include required files used on the backend.
 */
function otfs_add_officials_templates() {
	include_once 'includes/class-otfs-templates.php';
}

/**
 * Include required files used on the frontend.
 */
if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
	include_once 'includes/class-otfs-template-loader.php';
}

/**
 * Conditonally load classes and functions only needed when viewing the post type.
 */
function otfs_include_post_type_handlers() {
	include_once 'includes/class-otfs-meta-boxes.php';
}

// Include the admin notice functionality.
include_once 'includes/otfs-admin-notice.php';

/**
 * Add visibility option to performances and statistics.
 *
 * @param object $post The current post object.
 */
function otfs_add_visibility_option( $post ): void {
	// Add nonce field.
	wp_nonce_field( 'otfs_save_visibility_option_nonce', 'otfs_nonce' );

	if ( 'auto' === get_option( 'otfs_officials_columns', 'auto' ) ) {
		$otfs_visible = get_post_meta( $post->ID, 'otfs_visible', true );
		if ( '' === $otfs_visible ) {
			$otfs_visible = 1;
		}
		?>
			<p>
				<strong><?php esc_html_e( 'Visible at Official Profiles', 'officials-templates-for-sportspress' ); ?></strong>
				<i class="dashicons dashicons-editor-help sp-desc-tip" title="<?php esc_attr_e( 'Display in official profiles?', 'officials-templates-for-sportspress' ); ?>"></i>
			</p>
			<ul class="sp-visible-selector">
				<li>
					<label class="selectit">
						<input name="otfs_visible" id="otfs_visible_yes" type="radio" value="1" <?php checked( $otfs_visible ); ?>>
						<?php esc_html_e( 'Yes', 'sportspress' ); ?>
					</label>
				</li>
				<li>
					<label class="selectit">
						<input name="otfs_visible" id="otfs_visible_no" type="radio" value="0" <?php checked( ! $otfs_visible ); ?>>
						<?php esc_html_e( 'No', 'sportspress' ); ?>
					</label>
				</li>
			</ul>
			<?php
	}
}

/**
 * Save tournament selection to league table.
 *
 * @param int    $post_id The post ID.
 * @param object $post    The post object.
 */
function otfs_save_visibility_option( $post_id, $post ): void {
	// Verify nonce.
	$nonce = isset( $_POST['otfs_nonce'] ) ? sanitize_key( $_POST['otfs_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'otfs_save_visibility_option_nonce' ) ) {
		return;
	}

	if ( 'auto' === get_option( 'otfs_officials_columns', 'auto' ) ) {
		if ( isset( $_POST['otfs_visible'] ) ) {
			update_post_meta( $post_id, 'otfs_visible', intval( $_POST['otfs_visible'] ) );
		}
	}
}
