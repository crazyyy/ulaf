<?php
/**
 * Authority Mailer SMTP Tools Page
 *
 * Email Deliverability Checker and other tools
 *
 * @package Authority_Mailer
 * @since   1.0.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue tools page assets
 *
 * @since 1.0.3
 */
function authority_mailer_smtp_tools_enqueue_assets() {
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'authority-mailer-smtp-tools' ) === false ) {
		return;
	}

	// Enqueue white-glove installation service sidebar CSS/JS.
	$white_glove_css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/white-glove-sidebar.css';
	$white_glove_js_path  = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/white-glove-sidebar.js';

	if ( file_exists( $white_glove_css_path ) ) {
		$white_glove_css_url = plugins_url( 'assets/css/white-glove-sidebar.css', AUTHORITY_MAILER_PLUGIN_FILE );
		$white_glove_css_ver = filemtime( $white_glove_css_path );
		wp_enqueue_style( 'authority-mailer-white-glove-sidebar', $white_glove_css_url, array(), $white_glove_css_ver );
	}

	if ( file_exists( $white_glove_js_path ) ) {
		$white_glove_js_url = plugins_url( 'assets/js/white-glove-sidebar.js', AUTHORITY_MAILER_PLUGIN_FILE );
		$white_glove_js_ver = filemtime( $white_glove_js_path );
		wp_enqueue_script( 'authority-mailer-white-glove-sidebar', $white_glove_js_url, array( 'jquery' ), $white_glove_js_ver, true );
	}
}
add_action( 'admin_enqueue_scripts', 'authority_mailer_smtp_tools_enqueue_assets' );

/**
 * Render the Authority Mailer Tools page
 */
function authority_mailer_smtp_render_tools_page() {
	// Redirect to login if not authenticated.
	if ( ! is_user_logged_in() ) {
		auth_redirect();
		return;
	}

	// Check capability for logged-in users.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( authority_mailer_smtp_get_string( 'no_permission' ) ) );
	}

	$options            = get_option( 'authority_mailer_smtp_options', array() );
	$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';
	$is_premium         = apply_filters( 'authority_mailer_smtp_is_premium', false );

	// Get booking URL once to avoid duplication.
	$booking_url = apply_filters( 'authority_mailer_smtp_white_glove_booking_url', 'https://www.authorityplugins.com/products/authority-mailer-smtp/white-glove-setup' );

	?>
	<div class="am-wrap">
		<div class="am-container">
			<?php authority_mailer_smtp_render_admin_header( 'tools', $connected_provider ); ?>
		</div>
	</div>

	<div class="am-wrap am-tools-page">
		<div class="am-container">

			<!-- Two-column layout with main content + sidebar -->
			<div class="authority-mailer-wizard-layout">
				<div class="authority-mailer-wizard-body">

					<!-- LEFT: Main Tools Content -->
					<main class="authority-mailer-wizard-main">
						<!-- Email Deliverability Checker Card -->
			<div class="am-card">
				<div class="am-card-header">
					<h2 class="am-card-title">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
						<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_page_title' ) ); ?>
					</h2>
				</div>
				<div class="am-card-body">
					<p class="am-tools-description">
						<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_page_description' ) ); ?>
					</p>

					<!-- Domain Input Form -->
					<div class="am-tools-form" id="am-tools-form">
						<div class="am-form-group">
							<label for="am-domain-input" class="am-form-label">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_domain_label' ) ); ?>
							</label>
							<div class="am-input-group">
								<input
									type="text"
									id="am-domain-input"
									class="am-form-input"
									placeholder="e.g., authorityplugins.com"
									aria-describedby="domain-help"
								/>
								<button type="button" id="am-run-check-btn" class="am-btn am-btn-primary">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
										<polyline points="22 4 12 14.01 9 11.01"></polyline>
									</svg>
									<span class="btn-text"><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_run_check' ) ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<!-- Loading Indicator -->
					<div class="am-tools-loading" id="am-tools-loading" style="display: none;">
						<div class="am-spinner"></div>
						<p><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_checking' ) ); ?></p>
					</div>

					<!-- Results Container -->
					<div class="am-tools-results" id="am-tools-results" style="display: none;">
						<!-- Overall Deliverability Summary Banner -->
						<div class="am-deliverability-summary" id="am-deliverability-summary" style="display: none;">
							<div class="am-summary-icon" id="am-summary-icon" aria-hidden="true"></div>
							<div class="am-summary-content">
								<h3 class="am-summary-title" id="am-summary-title"></h3>
								<p class="am-summary-description" id="am-summary-description"></p>
							</div>
						</div>

						<div class="am-results-header">
							<h3><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_results_for' ) ); ?> <span id="checked-domain"></span></h3>
							<button type="button" id="am-run-again-btn" class="am-btn am-btn-secondary">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_run_again' ) ); ?>
							</button>
						</div>

						<div class="am-checks-grid">
							<!-- SPF Record -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_spf_record' ) ); ?></h4>
									<span class="am-check-badge" data-check="spf"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_spf_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="spf"></div>
								<!-- Action Area (shown only on fail) -->
								<div class="am-check-actions" data-actions="spf" style="display: none;">
									<a href="<?php echo esc_url( $booking_url ); ?>" target="_blank" rel="noopener" class="am-action-btn am-action-secondary">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_action_fix_for_me' ) ); ?>
									</a>
								</div>
							</div>

							<!-- DKIM Signature -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_dkim_signature' ) ); ?></h4>
									<span class="am-check-badge" data-check="dkim"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_dkim_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="dkim"></div>
								<!-- Action Area (shown only on fail) -->
								<div class="am-check-actions" data-actions="dkim" style="display: none;">
									<a href="<?php echo esc_url( $booking_url ); ?>" target="_blank" rel="noopener" class="am-action-btn am-action-secondary">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_action_fix_for_me' ) ); ?>
									</a>
								</div>
							</div>

							<!-- DMARC Policy -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_dmarc_policy' ) ); ?></h4>
									<span class="am-check-badge" data-check="dmarc"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_dmarc_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="dmarc"></div>
								<!-- Action Area (shown only on fail) -->
								<div class="am-check-actions" data-actions="dmarc" style="display: none;">
									<a href="<?php echo esc_url( $booking_url ); ?>" target="_blank" rel="noopener" class="am-action-btn am-action-secondary">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_action_fix_for_me' ) ); ?>
									</a>
								</div>
							</div>

							<!-- MX Records -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_mx_records' ) ); ?></h4>
									<span class="am-check-badge" data-check="mx"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_mx_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="mx"></div>
							</div>

							<!-- Reputation Score -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_reputation_score' ) ); ?></h4>
									<span class="am-check-badge" data-check="reputation"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_reputation_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="reputation"></div>
								<!-- Pro Feature Nudge (shown only for non-premium users) -->
								<?php if ( ! $is_premium ) : ?>
								<div class="am-pro-nudge">
									<a href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_pro_url', 'https://www.authorityplugins.com/products/authority-mailer-smtp-premium/' ) ); ?>" target="_blank" rel="noopener" class="am-pro-nudge-link">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_pro_nudge_monitoring' ) ); ?>
									</a>
								</div>
								<?php endif; ?>
							</div>

							<!-- Blacklist Status -->
							<div class="am-check-item">
								<div class="am-check-header">
									<h4><?php echo esc_html( authority_mailer_smtp_get_string( 'tools_blacklist_status' ) ); ?></h4>
									<span class="am-check-badge" data-check="blacklist"></span>
								</div>
								<p class="am-check-description">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_blacklist_description' ) ); ?>
								</p>
								<div class="am-check-details" data-details="blacklist"></div>
								<!-- Pro Feature Nudge (shown only for non-premium users) -->
								<?php if ( ! $is_premium ) : ?>
								<div class="am-pro-nudge">
									<a href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_pro_url', 'https://www.authorityplugins.com/products/authority-mailer-smtp-premium/' ) ); ?>" target="_blank" rel="noopener" class="am-pro-nudge-link">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_pro_nudge_monitoring' ) ); ?>
									</a>
								</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Social Proof (shown when failures exist) -->
						<div class="am-social-proof" id="am-social-proof" style="display: none;">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'tools_social_proof' ) ); ?>
						</div>
					</div>
				</div>
			</div>
					</main>

					<!-- RIGHT: Sidebar (Free Edition Only) -->
					<aside class="authority-mailer-wizard-sidebar" role="complementary" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'tools_sidebar_label' ) ); ?>">
						<?php
						// Display white-glove service sidebar (free edition only).
						if ( ! $is_premium ) {
							$white_glove_component = AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/components/white-glove-sidebar.php';
							if ( file_exists( $white_glove_component ) ) {
								require_once $white_glove_component;
								authority_mailer_smtp_render_white_glove_sidebar( 0 );
							}
						}
						?>
					</aside>

				</div><!-- .authority-mailer-wizard-body -->
			</div><!-- .authority-mailer-wizard-layout -->

		</div>
	</div>
	<?php
}
