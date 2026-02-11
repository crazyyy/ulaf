<?php
/**
 * Hook detail view functionality.
 *
 * Provides detailed information about specific hook usage including
 * file context, code analysis, and navigation features.
 *
 * @package HookLocator
 * @since   1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook Locator Detail class.
 *
 * Handles the display of detailed hook information including file context,
 * code snippets, and analysis of hook usage patterns.
 *
 * @since 1.0
 */
class Hook_Locator_Detail {

	/**
	 * Number of context lines to show around target line.
	 *
	 * @since 1.0
	 * @var   int
	 */
	const CONTEXT_LINES = 15;

	/**
	 * Display detailed hook information.
	 *
	 * Shows comprehensive information about a specific hook usage including
	 * file location, code context, and analysis.
	 *
	 * @since 1.0
	 * @param string $hook_name    Hook name being analyzed.
	 * @param string $detail_hash  Base64 encoded file|line identifier.
	 * @param string $folder_key   Directory key for navigation.
	 * @param string $type         Hook type (action or filter).
	 */
	public static function display( $hook_name, $detail_hash, $folder_key, $type ) {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hook-locator' ) );
		}

		// Decode and validate detail hash.
		$decoded = base64_decode( $detail_hash );
		if ( false === $decoded ) {
			self::render_error( __( 'Invalid detail identifier.', 'hook-locator' ) );
			return;
		}

		$parts = explode( '|', $decoded );
		if ( 2 !== count( $parts ) ) {
			self::render_error( __( 'Invalid detail format.', 'hook-locator' ) );
			return;
		}

		$file = sanitize_text_field( $parts[0] );
		$line = absint( $parts[1] );

		// Validate file existence and readability.
		if ( empty( $file ) || ! file_exists( $file ) || ! is_readable( $file ) ) {
			self::render_error( __( 'File not found or not accessible.', 'hook-locator' ) );
			return;
		}

		// Validate line number.
		if ( 0 === $line ) {
			self::render_error( __( 'Invalid line number.', 'hook-locator' ) );
			return;
		}

		// Display the detail view.
		self::render_header( $hook_name, $folder_key );
		self::render_file_info( $hook_name, $file, $line, $type );
		self::render_code_context( $file, $line );
		self::render_analysis( $file, $line );
	}

	/**
	 * Render error message.
	 *
	 * @since 1.0
	 * @param string $message Error message to display.
	 */
	private static function render_error( $message ) {
		echo '<div class="hook-locator-error">';
		echo '<div class="hook-locator-error-icon"><span class="dashicons dashicons-warning"></span></div>';
		echo '<p>' . esc_html( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render detail view header with navigation.
	 *
	 * @since 1.0
	 * @param string $hook_name   Hook name being analyzed.
	 * @param string $folder_key  Directory key for navigation.
	 */
	private static function render_header( $hook_name, $folder_key ) {
		// Back navigation URL.
		$back_url = add_query_arg(
			array(
				'page'               => 'hook-locator',
				'hook_name'          => rawurlencode( $hook_name ),
				'directory'          => rawurlencode( $folder_key ),
				'hook_locator_nonce' => wp_create_nonce( 'hook_locator_search' ),
			),
			admin_url( 'tools.php' )
		);

		echo '<div class="hook-locator-detail-header">';
		echo '<div class="hook-locator-detail-navigation">';
		echo '<a href="' . esc_url( $back_url ) . '" class="button hook-locator-back-btn">';
		echo '<span class="dashicons dashicons-arrow-left-alt2"></span> ';
		echo esc_html__( 'Back to Results', 'hook-locator' );
		echo '</a>';
		echo '</div>';

		echo '<h2 class="hook-locator-detail-title">';
		echo '<span class="dashicons dashicons-code-standards"></span> ';
		echo esc_html__( 'Detail Analysis', 'hook-locator' );
		echo '</h2>';
		echo '</div>';
	}

	/**
	 * Render file information section.
	 *
	 * @since 1.0
	 * @param string $hook_name Hook name being analyzed.
	 * @param string $file      File path.
	 * @param int    $line      Line number.
	 * @param string $type      Hook type (action or filter).
	 */
	private static function render_file_info( $hook_name, $file, $line, $type ) {
		$relative_path = str_replace( ABSPATH, '', $file );
		$file_size     = size_format( filesize( $file ) );

		// Determine file type.
		$is_plugin = false !== strpos( $file, WP_PLUGIN_DIR );
		$is_theme  = false !== strpos( $file, get_theme_root() );

		$file_type = __( 'WordPress File', 'hook-locator' );
		$icon_class = 'dashicons-media-code';

		if ( $is_plugin ) {
			$file_type = __( 'Plugin File', 'hook-locator' );
			$icon_class = 'dashicons-admin-plugins';
		} elseif ( $is_theme ) {
			$file_type = __( 'Theme File', 'hook-locator' );
			$icon_class = 'dashicons-admin-appearance';
		}

		echo '<div class="hook-locator-file-info-section">';
		echo '<h3>' . esc_html__( 'File Information', 'hook-locator' ) . '</h3>';

		echo '<div class="hook-locator-info-grid">';

		echo '<div class="hook-locator-info-item">';
		echo '<span class="hook-locator-info-label">' . esc_html__( 'Hook Name:', 'hook-locator' ) . '</span>';
		echo '<span class="hook-locator-info-value"><code>' . esc_html( $hook_name ) . '</code></span>';
		echo '</div>';

		echo '<div class="hook-locator-info-item">';
		echo '<span class="hook-locator-info-label">' . esc_html__( 'File Path:', 'hook-locator' ) . '</span>';
		echo '<span class="hook-locator-info-value"><code>' . esc_html( $relative_path ) . '</code></span>';
		echo '</div>';

		echo '<div class="hook-locator-info-item">';
		echo '<span class="hook-locator-info-label">' . esc_html__( 'File Type:', 'hook-locator' ) . '</span>';
		echo '<span class="hook-locator-info-value">';
		echo '<span class="dashicons ' . esc_attr( $icon_class ) . '"></span> ' . esc_html( $file_type );
		echo '</span>';
		echo '</div>';

		echo '<div class="hook-locator-info-item">';
		echo '<span class="hook-locator-info-label">' . esc_html__( 'Line Number:', 'hook-locator' ) . '</span>';
		echo '<span class="hook-locator-info-value"><strong>' . esc_html( number_format_i18n( $line ) ) . '</strong></span>';
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render code context section with highlighted target line.
	 *
	 * @since 1.0
	 * @param string $file File path.
	 * @param int    $line Target line number.
	 */
	private static function render_code_context( $file, $line ) {
		$lines = @file( $file, FILE_IGNORE_NEW_LINES );
		if ( false === $lines ) {
			echo '<div class="hook-locator-error">';
			echo '<p>' . esc_html__( 'Could not read file contents.', 'hook-locator' ) . '</p>';
			echo '</div>';
			return;
		}

		$adjust_line = 10;

		$total_lines = count( $lines );
		$start_line  = max( 1, $line + $adjust_line - self::CONTEXT_LINES );
		$end_line    = min( $total_lines, $line + self::CONTEXT_LINES - $adjust_line );

		echo '<div class="hook-locator-code-section">';
		echo '<h3>' . esc_html__( 'Code Context', 'hook-locator' ) . '</h3>';

		echo '<div class="hook-locator-code-wrapper">';
		echo '<div class="hook-locator-code-header">';
		echo '<span class="hook-locator-code-info">';
		printf(
			/* translators: 1: start line, 2: end line, 3: total lines */
			esc_html__( 'Lines %1$d-%2$d of %3$d', 'hook-locator' ),
			esc_html( $start_line ),
			esc_html( $end_line ),
			esc_html( $total_lines )
		);
		echo '</span>';
		echo '<button type="button" class="button button-small hook-locator-copy-code" title="' . esc_attr__( 'Copy visible code', 'hook-locator' ) . '">';
		echo esc_html__( 'Copy', 'hook-locator' );
		echo '</button>';
		echo '</div>';

		echo '<pre class="hook-locator-code-display" data-start-line="' . esc_attr( $start_line ) . '">';

		for ( $i = $start_line; $i <= $end_line; $i++ ) {
			$current_line = isset( $lines[ $i - 1 ] ) ? $lines[ $i - 1 ] : '';
			$is_target    = ( $i === $line );
			$line_class   = $is_target ? 'hook-locator-target-line' : 'hook-locator-context-line';

			echo '<span class="' . esc_attr( $line_class ) . '" data-line="' . esc_attr( $i ) . '">';
			echo '<span class="hook-locator-line-number" title="' . esc_attr__( 'Line number', 'hook-locator' ) . '">' . sprintf( '%4d', esc_html( $i ) ) . '</span>';
			echo '<span class="hook-locator-line-content">' . esc_html( $current_line ) . '</span>';
			echo "</span>\n";
		}

		echo '</pre>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render analysis and tips section.
	 *
	 * @since 1.0
	 * @param string $file File path.
	 * @param int    $line Line number.
	 */
	private static function render_analysis( $file, $line ) {
		$lines = @file( $file, FILE_IGNORE_NEW_LINES );
		if ( false === $lines ) {
			return;
		}

		$target_line_code = isset( $lines[ $line - 1 ] ) ? trim( $lines[ $line - 1 ] ) : '';
		$hook_type = self::analyze_hook_usage( $target_line_code );

		echo '<div class="hook-locator-analysis-section">';
		echo '<h3>' . esc_html__( 'Analysis & Tips', 'hook-locator' ) . '</h3>';

		echo '<div class="hook-locator-analysis-content">';

		// Hook type analysis.
		echo '<div class="hook-locator-analysis-item">';
		echo '<h4><span class="dashicons dashicons-info"></span> ' . esc_html__( 'Hook Type', 'hook-locator' ) . '</h4>';
		echo '<p>';
		echo '<span class="hook-locator-type-badge hook-locator-type-' . esc_attr( str_replace( '_', '-', $hook_type ) ) . '">';
		echo esc_html( Hook_Locator_Search::get_hook_type_label( $hook_type ) );
		echo '</span>';
		echo '</p>';
		echo '<p>' . esc_html( self::get_hook_type_description( $hook_type ) ) . '</p>';
		echo '</div>';

		// Development tips.
		echo '<div class="hook-locator-analysis-item">';
		echo '<h4><span class="dashicons dashicons-lightbulb"></span> ' . esc_html__( 'Development Tips', 'hook-locator' ) . '</h4>';
		echo '<ul class="hook-locator-tips-list">';
		echo '<li>' . esc_html__( 'Look for function definitions above this line to understand the context', 'hook-locator' ) . '</li>';
		echo '<li>' . esc_html__( 'Check if this code is inside a class method or standalone function', 'hook-locator' ) . '</li>';
		echo '<li>' . esc_html__( 'Review surrounding code to understand when this hook is triggered', 'hook-locator' ) . '</li>';
		echo '<li>' . esc_html__( 'Consider the hook priority and number of parameters if hooking into this', 'hook-locator' ) . '</li>';
		echo '</ul>';
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Analyze hook usage from code line.
	 *
	 * @since 1.0
	 * @param string $code Code line to analyze.
	 * @return string Hook type identifier.
	 */
	private static function analyze_hook_usage( $code ) {
		$patterns = array(
			'add_action',
			'add_filter',
			'do_action',
			'apply_filters',
			'remove_action',
			'remove_filter',
			'has_action',
			'has_filter',
		);

		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $code, $pattern ) ) {
				return $pattern;
			}
		}

		return 'unknown';
	}

	/**
	 * Get description for hook type.
	 *
	 * @since 1.0
	 * @param string $hook_type Hook type identifier.
	 * @return string Description of the hook type.
	 */
	private static function get_hook_type_description( $hook_type ) {
		$descriptions = array(
			'add_action'    => __( 'Registers a function to run when this action hook is triggered.', 'hook-locator' ),
			'add_filter'    => __( 'Registers a function to modify data when this filter hook is applied.', 'hook-locator' ),
			'do_action'     => __( 'Triggers all functions attached to this action hook.', 'hook-locator' ),
			'apply_filters' => __( 'Applies all functions attached to this filter hook to modify the data.', 'hook-locator' ),
			'remove_action' => __( 'Removes a previously registered action hook function.', 'hook-locator' ),
			'remove_filter' => __( 'Removes a previously registered filter hook function.', 'hook-locator' ),
			'has_action'    => __( 'Checks if any functions are attached to this action hook.', 'hook-locator' ),
			'has_filter'    => __( 'Checks if any functions are attached to this filter hook.', 'hook-locator' ),
		);

		return isset( $descriptions[ $hook_type ] ) ? $descriptions[ $hook_type ] : __( 'Unknown hook usage pattern.', 'hook-locator' );
	}
}
