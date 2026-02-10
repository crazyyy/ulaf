<?php
/**
 * Authority Mailer Admin Assets Manager
 *
 * Centralized asset loading for all admin pages to eliminate code duplication
 * and ensure consistent asset loading across the plugin.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Authority_Mailer_Admin_Assets
 *
 * Manages CSS and JavaScript asset loading for admin pages.
 *
 * @since 1.0.0
 */
class Authority_Mailer_Admin_Assets {

	/**
	 * Check if current page is an Authority Mailer admin page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page_slug Optional. Specific page slug to check (e.g., 'recipients', 'license').
	 *                          If empty, checks for any Authority Mailer page.
	 * @return bool True if on Authority Mailer page.
	 */
	public static function is_authority_mailer_page( $page_slug = '' ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && isset( $screen->id ) ) {
			// Check if screen ID contains our plugin identifier.
			$is_our_page = false !== strpos( $screen->id, 'authority-mailer-smtp' );

			if ( ! $is_our_page ) {
				return false;
			}

			// If specific page slug provided, check for it.
			if ( ! empty( $page_slug ) ) {
				return false !== strpos( $screen->id, $page_slug );
			}

			return true;
		}

		// Fallback to checking $_GET['page'] parameter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking page parameter for asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( empty( $page ) ) {
			return false;
		}

		$is_our_page = false !== strpos( $page, 'authority-mailer-smtp' );

		if ( ! $is_our_page ) {
			return false;
		}

		// If specific page slug provided, check for it.
		if ( ! empty( $page_slug ) ) {
			return false !== strpos( $page, $page_slug );
		}

		return true;
	}

	/**
	 * Enqueue common admin styles used across all/most admin pages.
	 *
	 * Loads the main authority-mailer-admin.css file with proper versioning.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_common_admin_styles() {
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/authority-mailer-admin.css';
		$css_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/authority-mailer-admin.css';
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : AUTHORITY_MAILER_VERSION;

		wp_enqueue_style( 'authority-mailer-smtp-admin', $css_url, array(), $css_ver );
	}

	/**
	 * Enqueue premium-specific assets (CSS and JS).
	 *
	 * Loads premium-settings.css and premium-settings.js used by most pro pages.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $enqueue_js Whether to also enqueue the premium JS file. Default true.
	 * @param array $localize_data Optional. Custom localization data for the premium settings script.
	 * @return void
	 */
	public static function enqueue_premium_assets( $enqueue_js = true, $localize_data = array() ) {
		// Enqueue premium settings CSS.
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/premium-settings.css';
		$css_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/premium-settings.css';
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : AUTHORITY_MAILER_VERSION;

		wp_enqueue_style(
			'authority-mailer-premium-settings',
			$css_url,
			array( 'authority-mailer-smtp-admin' ),
			$css_ver
		);

		// Enqueue premium settings JS if requested.
		if ( $enqueue_js ) {
			$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/premium-settings.js';
			$js_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/premium-settings.js';
			$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : AUTHORITY_MAILER_VERSION;

			wp_enqueue_script(
				'authority-mailer-premium-settings',
				$js_url,
				array( 'jquery' ),
				$js_ver,
				true
			);

			// Localize with provided data or defaults.
			if ( ! empty( $localize_data ) ) {
				wp_localize_script(
					'authority-mailer-premium-settings',
					'authorityMailerSettings',
					$localize_data
				);
			}
		}
	}

	/**
	 * Enqueue common admin scripts used across multiple pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_common_admin_scripts() {
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/modern-admin.js';
		$js_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/modern-admin.js';
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : AUTHORITY_MAILER_VERSION;

		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-modern-admin',
				$js_url,
				array( 'jquery' ),
				$js_ver,
				true
			);
		}
	}

	/**
	 * Enqueue analytics page assets.
	 *
	 * Loads Chart.js and analytics-specific CSS/JS.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_analytics_assets() {
		// Enqueue Chart.js.
		wp_enqueue_script(
			'chartjs',
			AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/vendor/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Enqueue analytics JavaScript.
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/analytics.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-analytics',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/analytics.js',
				array( 'jquery', 'chartjs' ),
				file_exists( $js_path ) ? filemtime( $js_path ) : AUTHORITY_MAILER_VERSION,
				true
			);

			// Localize analytics script.
			wp_localize_script(
				'authority-mailer-analytics',
				'authorityMailerAnalytics',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'restUrl' => rest_url( 'authority-mailer/v1/analytics/' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'strings' => array(
						'webhookCopied' => authority_mailer_smtp_get_string( 'analytics_webhook_copied' ),
					),
				)
			);
		}

		// Enqueue analytics CSS.
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/premium-analytics.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'authority-mailer-premium-analytics',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/premium-analytics.css',
				array( 'authority-mailer-smtp-admin' ),
				filemtime( $css_path )
			);
		}
	}

	/**
	 * Enqueue templates page assets.
	 *
	 * Loads template modal CSS and templates JS with code editor support.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_templates_assets() {
		// Enqueue template modal CSS.
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/template-modal.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'authority-mailer-template-modal',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/template-modal.css',
				array( 'authority-mailer-smtp-admin' ),
				filemtime( $css_path )
			);
		}

		// Enqueue WordPress code editor.
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		// Enqueue templates JavaScript.
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/templates.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-templates',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/templates.js',
				array( 'jquery', 'wp-codemirror' ),
				filemtime( $js_path ),
				true
			);

			wp_localize_script(
				'authority-mailer-templates',
				'authorityMailerTemplates',
				array(
					'restUrl' => rest_url( 'authority-mailer/v1/templates/' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'strings' => array(
						'confirmDelete' => __( 'Are you sure you want to delete this template?', 'authority-mailer-smtp' ),
					),
				)
			);
		}
	}

	/**
	 * Enqueue email log page assets.
	 *
	 * Loads email log specific CSS and JS.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_email_log_assets() {
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/admin-email-log.css';
		$js_path  = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/admin-email-log.js';

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'authority-mailer-smtp-email-log',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/admin-email-log.css',
				array( 'authority-mailer-smtp-admin' ),
				filemtime( $css_path )
			);
		}

		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-smtp-email-log-js',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/admin-email-log.js',
				array(),
				filemtime( $js_path ),
				true
			);
		}
	}

	/**
	 * Enqueue recipients page assets.
	 *
	 * Loads recipients-specific JS.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_recipients_assets() {
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/recipients.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-recipients',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/recipients.js',
				array( 'jquery' ),
				filemtime( $js_path ),
				true
			);

			wp_localize_script(
				'authority-mailer-recipients',
				'authorityMailerRecipients',
				array(
					'restUrl' => rest_url( 'authority-mailer/v1/recipients' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}

	/**
	 * Enqueue unsubscribed users page assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function enqueue_unsubscribed_users_assets() {
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/unsubscribed-users.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-unsubscribed-users',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/unsubscribed-users.js',
				array( 'jquery' ),
				filemtime( $js_path ),
				true
			);

			wp_localize_script(
				'authority-mailer-unsubscribed-users',
				'authorityMailerData',
				array(
					'restUrl' => rest_url( 'authority-mailer/v1/' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				)
			);
		}
	}

	/**
	 * Enqueue system info page assets.
	 *
	 * Loads system info CSS and JS with clipboard functionality.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_system_info_assets() {
		// Enqueue system info CSS.
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/system-info.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'authority-mailer-system-info',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/system-info.css',
				array( 'authority-mailer-smtp-admin' ),
				filemtime( $css_path )
			);
		}

		// Enqueue system info JavaScript.
		$js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/system-info.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-system-info',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/system-info.js',
				array( 'jquery' ),
				filemtime( $js_path ),
				true
			);

			// Localize script for clipboard functionality.
			wp_localize_script(
				'authority-mailer-system-info',
				'authorityMailerSystemInfo',
				array(
					'copyText'   => __( 'Copy to Clipboard', 'authority-mailer-smtp' ),
					'copiedText' => __( 'Copied!', 'authority-mailer-smtp' ),
					'failedText' => __( 'Failed to copy. Please copy manually.', 'authority-mailer-smtp' ),
				)
			);
		}
	}

	/**
	 * Enqueue tools page assets.
	 *
	 * Loads tools-specific CSS and JS for deliverability checker.
	 *
	 * @since 1.0.3
	 *
	 * @return void
	 */
	public static function enqueue_tools_assets() {
		$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/tools.css';
		$js_path  = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/tools.js';

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'authority-mailer-smtp-tools',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/css/tools.css',
				array( 'authority-mailer-smtp-admin' ),
				filemtime( $css_path )
			);
		}

		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'authority-mailer-smtp-tools-js',
				AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/tools.js',
				array( 'jquery' ),
				filemtime( $js_path ),
				true
			);

			// Localize script with AJAX nonce and strings.
			wp_localize_script(
				'authority-mailer-smtp-tools-js',
				'authorityMailerTools',
				array(
					'nonce'   => wp_create_nonce( 'authority_mailer_tools' ),
					'strings' => array(
						'emptyDomain'              => authority_mailer_smtp_get_string( 'tools_error_empty_domain' ),
						'checkFailed'              => authority_mailer_smtp_get_string( 'tools_error_check_failed' ),
						'networkError'             => authority_mailer_smtp_get_string( 'tools_error_network' ),
						'summarySuccessTitle'      => authority_mailer_smtp_get_string( 'tools_summary_success_title' ),
						'summarySuccessDescription' => authority_mailer_smtp_get_string( 'tools_summary_success_description' ),
						'summaryWarningTitle'      => authority_mailer_smtp_get_string( 'tools_summary_warning_title' ),
						'summaryWarningDescription' => authority_mailer_smtp_get_string( 'tools_summary_warning_description' ),
					),
				)
			);
		}
	}
}
