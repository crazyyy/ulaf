<?php
/**
 * Admin interface and routing.
 *
 * Handles the WordPress admin interface, menu creation, asset loading,
 * and page rendering for the Hook Locator plugin.
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook Locator Admin class.
 *
 * Manages the admin interface including menu creation, asset enqueueing,
 * form handling, and page rendering.
 *
 * @since 1.0
 */
class Hook_Locator_Admin {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0
	 * @var   Hook_Locator_Admin|null
	 */
	private static $instance = null;

	/**
	 * Admin page hook suffix.
	 *
	 * @since 1.0
	 * @var   string
	 */
	private $page_hook = '';

	/**
	 * Get plugin instance.
	 *
	 * Implements singleton pattern to ensure only one instance exists.
	 *
	 * @since 1.0
	 * @return Hook_Locator_Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin menu item.
	 *
	 * Creates the Hook Locator admin page under the Tools menu.
	 *
	 * @since 1.0
	 */
	public function add_admin_menu() {
		$this->page_hook = add_management_page(
			__( 'Hook Locator', 'hook-locator' ),
			__( 'Hook Locator', 'hook-locator' ),
			'manage_options',
			'hook-locator',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * Loads CSS and JavaScript files only on the Hook Locator admin page.
	 *
	 * @since 1.0
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Only load assets on our admin page.
		if ( $this->page_hook !== $hook_suffix ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'hook-locator-admin',
			HOOK_LOCATOR_PLUGIN_URL . 'assets/css/hook-locator-admin.css',
			array(),
			HOOK_LOCATOR_VERSION
		);

		// Enqueue admin scripts.
		wp_enqueue_script(
			'hook-locator-admin',
			HOOK_LOCATOR_PLUGIN_URL . 'assets/js/hook-locator-admin.js',
			array( 'jquery' ),
			HOOK_LOCATOR_VERSION,
			true
		);

		// Localize script for translations and AJAX.
		wp_localize_script(
			'hook-locator-admin',
			'hookLocatorAdmin',
			array(
				'nonce'      => wp_create_nonce( 'hook_locator_admin' ),
				'searchText' => __( 'Searching...', 'hook-locator' ),
				'copyText'   => __( 'Code copied to clipboard!', 'hook-locator' ),
				'copyError'  => __( 'Could not copy to clipboard', 'hook-locator' ),
			)
		);
	}

	/**
	 * Render the main admin page.
	 *
	 * Displays the search form, results, or detail view based on URL parameters.
	 *
	 * @since 1.0
	 */
	public function render_admin_page() {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hook-locator' ) );
		}

		// Verify nonce before processing form data.
		if ( isset( $_GET['hook_name'] ) || isset( $_GET['type'] ) || isset( $_GET['directory'] ) || isset( $_GET['detail'] ) ) {
			if ( ! isset( $_GET['hook_locator_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['hook_locator_nonce'] ) ), 'hook_locator_search' ) ) {
				wp_die( esc_html__( 'Security check failed. Please try again.', 'hook-locator' ) );
			}
		}

		// Get sanitized parameters.
		$hook_name       = isset( $_GET['hook_name'] ) ? sanitize_text_field( wp_unslash( $_GET['hook_name'] ) ) : '';
		$type            = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
		$selected_folder = isset( $_GET['directory'] ) ? sanitize_text_field( wp_unslash( $_GET['directory'] ) ) : 'all';
		$detail_hash     = isset( $_GET['detail'] ) ? sanitize_text_field( wp_unslash( $_GET['detail'] ) ) : '';

		// Get organized directories for dropdown.
		$organized_dirs = Hook_Locator_Core::get_instance()->get_organized_directories();

		echo '<div class="wrap hook-locator-wrap">';
		$this->render_search_form( $hook_name, $selected_folder, $organized_dirs );

		// Display results or detail view.
		if ( ! empty( $hook_name ) && empty( $detail_hash ) ) {
			$this->render_search_results( $hook_name, $selected_folder );
		} elseif ( ! empty( $hook_name ) && ! empty( $detail_hash ) ) {
			$this->render_detail_view( $hook_name, $detail_hash, $selected_folder, $type );
		}

		echo '</div>';
	}

	/**
	 * Render search form.
	 *
	 * @since 1.0
	 * @param string $hook_name       Current hook name.
	 * @param string $selected_folder Current selected folder.
	 * @param array  $organized_dirs  Organized directory structure.
	 */
	private function render_search_form( $hook_name, $selected_folder, $organized_dirs ) {
		echo '<div class="hook-locator-card hook-locator-search-card">';
		echo '<h1 class="hook-locator-card-title">';
		echo '<a href="' . esc_url( admin_url( 'tools.php?page=hook-locator' ) ) . '" class="hook-locator-logo-link">';
		echo esc_html__( 'Hook Locator', 'hook-locator' );
		echo '</a>';
		echo '</h1>';

		echo '<form method="get" action="" class="hook-locator-search-form">';
		wp_nonce_field( 'hook_locator_search', 'hook_locator_nonce' );
		echo '<input type="hidden" name="page" value="hook-locator">';

		echo '<div class="hook-locator-form-row">';
		echo '<div class="hook-locator-form-field">';
		echo '<label for="hook_name" class="hook-locator-label">' . esc_html__( 'Hook Name', 'hook-locator' ) . '</label>';
		echo '<input type="text" id="hook_name" name="hook_name" value="' . esc_attr( $hook_name ) . '" ';
		echo 'class="hook-locator-input" placeholder="' . esc_attr__( 'e.g. init, wp_head, save_post', 'hook-locator' ) . '" required>';
		echo '<p class="hook-locator-field-description">' . esc_html__( 'Enter the exact hook name you want to search for', 'hook-locator' ) . '</p>';
		echo '</div>';

		echo '<div class="hook-locator-form-field">';
		echo '<label for="directory" class="hook-locator-label">' . esc_html__( 'Search Location', 'hook-locator' ) . '</label>';
		echo '<select id="directory" name="directory" class="hook-locator-select">';
		echo '<option value="all"' . selected( $selected_folder, 'all', false ) . '>';
		echo esc_html__( 'All Plugins & Themes', 'hook-locator' );
		echo '</option>';

		// Plugins optgroup.
		if ( ! empty( $organized_dirs['plugins'] ) ) {
			echo '<optgroup label="' . esc_attr__( 'Plugins', 'hook-locator' ) . '">';
			foreach ( $organized_dirs['plugins'] as $key => $name ) {
				$selected = selected( $selected_folder, $key, false );
				echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>';
				echo esc_html( $name );
				echo '</option>';
			}
			echo '</optgroup>';
		}

		// Themes optgroup.
		if ( ! empty( $organized_dirs['themes'] ) ) {
			echo '<optgroup label="' . esc_attr__( 'Themes', 'hook-locator' ) . '">';
			foreach ( $organized_dirs['themes'] as $key => $name ) {
				$selected = selected( $selected_folder, $key, false );
				echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>';
				echo esc_html( $name );
				echo '</option>';
			}
			echo '</optgroup>';
		}

		echo '</select>';
		echo '<p class="hook-locator-field-description">' . esc_html__( 'Choose where to search for the hook', 'hook-locator' ) . '</p>';
		echo '</div>';
		echo '</div>';

		echo '<div class="hook-locator-form-actions">';
		echo '<button type="submit" class="button button-primary button-large hook-locator-search-btn">';
		echo '<span class="dashicons dashicons-search"></span>';
		echo esc_html__( 'Search Hooks', 'hook-locator' );
		echo '</button>';
		echo '</div>';

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render search results.
	 *
	 * @since 1.0
	 * @param string $hook_name       Hook name being searched.
	 * @param string $selected_folder Selected directory.
	 */
	private function render_search_results( $hook_name, $selected_folder ) {
		// Verify nonce for security.
		if ( ! isset( $_GET['hook_locator_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['hook_locator_nonce'] ) ), 'hook_locator_search' ) ) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'hook-locator' ) );
		}

		$results = Hook_Locator_Search::scan_files( $hook_name, $selected_folder );

		echo '<div class="hook-locator-card hook-locator-results-card">';
		if ( empty( $results ) ) {
			echo '<div class="hook-locator-no-results">';
			echo '<div class="hook-locator-no-results-icon">';
			echo '<span class="dashicons dashicons-search"></span>';
			echo '</div>';
			echo '<h3>' . esc_html__( 'No results found', 'hook-locator' ) . '</h3>';
			echo '<p>' . esc_html__( 'No hooks matching your search criteria were found. Try:', 'hook-locator' ) . '</p>';
			echo '<ul>';
			echo '<li>' . esc_html__( 'Double-checking the hook name spelling', 'hook-locator' ) . '</li>';
			echo '<li>' . esc_html__( 'Searching in all plugins and themes', 'hook-locator' ) . '</li>';
			echo '<li>' . esc_html__( 'Trying a different hook name', 'hook-locator' ) . '</li>';
			echo '</ul>';
			echo '</div>';
		} else {
			echo '<div class="hook-locator-results-summary">';
			echo '<p>';
			printf(
				/* translators: 1: number of results, 2: hook name */
				'Found %1$s result for %2$s',
				'<strong>' . count( $results ) . '</strong>',
				'<code>' . esc_html( $hook_name ) . '</code>'
			);
			echo '</p>';
			echo '</div>';

			$results_table = new Hook_Locator_Results_Table( $results, $hook_name );
			$results_table->prepare_items();
			$results_table->display();
		}

		echo '</div>';
	}

	/**
	 * Render detail view.
	 *
	 * @since 1.0
	 * @param string $hook_name       Hook name.
	 * @param string $detail_hash     Detail identifier.
	 * @param string $selected_folder Selected directory.
	 * @param string $type            Hook type (action/filter).
	 */
	private function render_detail_view( $hook_name, $detail_hash, $selected_folder, $type ) {
		echo '<div class="hook-locator-card hook-locator-detail-card ' . esc_attr( $type ) . '">';
		Hook_Locator_Detail::display( $hook_name, $detail_hash, $selected_folder, $type );
		echo '</div>';
	}
}
