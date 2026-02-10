<?php
/**
 * Authority Mailer SMTP - Admin Helper Functions
 *
 * Contains shared functions and components used across all admin pages.
 *
 * @package Authority_Mailer
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue shared admin styles.
 *
 * This function should be called on admin pages to load the unified CSS.
 *
 * @since 1.0.0
 *
 * @param string $page_slug The current page slug (e.g., 'dashboard', 'email-log', 'onboarding').
 */
function authority_mailer_smtp_enqueue_admin_styles( $page_slug = '' ) {
	$admin_css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/authority-mailer-admin.css';
	$admin_css_url  = plugins_url( 'assets/css/authority-mailer-admin.css', AUTHORITY_MAILER_PLUGIN_FILE );
	$admin_css_ver  = file_exists( $admin_css_path ) ? filemtime( $admin_css_path ) : AUTHORITY_MAILER_VERSION;

	wp_enqueue_style( 'authority-mailer-smtp-admin', $admin_css_url, array(), $admin_css_ver );
}

/**
 * Render the unified admin header component.
 *
 * This function provides a consistent header across all Authority Mailer admin pages.
 * Dynamically shows license plan name (Pro, Premium, Enterprise) or "Free" based on
 * actual license status from the License Manager.
 *
 * @since 1.0.0
 *
 * @param string $current_page      The current page identifier.
 * @param string $connected_provider The currently connected provider (optional).
 */
function authority_mailer_smtp_render_admin_header( $current_page = 'dashboard', $connected_provider = '' ) {
	// Ensure parameters are never null for PHP 8.1+ compatibility.
	$current_page       = is_string( $current_page ) && ! empty( $current_page ) ? $current_page : 'dashboard';
	$connected_provider = is_string( $connected_provider ) ? $connected_provider : '';

	// Display "Free" for the free version.
	$plan_display = authority_mailer_smtp_get_string( 'common_free' );
	$is_premium   = false;
	?>
	<div class="am-page-header">
		<div class="am-header-brand">
			<div class="am-logo">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
				</svg>
			</div>
			<div class="am-header-info">
				<h1>
					Authority Mailer <span class="am-title-light">SMTP</span>					
					<span class="am-version-badge am-version-badge-secondary">v<?php echo esc_html( AUTHORITY_MAILER_VERSION ); ?></span>
				</h1>
				<div class="am-header-subtitle">
					<?php echo esc_html( authority_mailer_smtp_get_string( 'common_by' ) ); ?>
					<a href="https://authorityplugins.com" target="_blank" rel="noopener">Authority Plugins</a>
					<span style="color:var(--am-gray-300);">•</span>
					<?php echo esc_html( authority_mailer_smtp_get_string( 'common_professional_email_delivery' ) ); ?>
				</div>
			</div>
		</div>
		<div class="am-header-actions">
			<?php if ( $connected_provider && 'onboarding' !== $current_page ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=3&provider=' . $connected_provider ) ); ?>" class="am-btn am-btn-secondary">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
					</svg>
					<?php echo esc_html( authority_mailer_smtp_get_string( 'common_send_test' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
