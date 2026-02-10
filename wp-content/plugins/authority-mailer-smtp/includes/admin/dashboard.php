<?php
/**
 * Authority Mailer SMTP Dashboard - Professional Edition
 * by Authority Plugins
 *
 * @package Authority_Mailer
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add body class to hide Send Test button when no provider is configured
 */
add_filter(
	'admin_body_class',
	function ( $classes ) {
		$options            = get_option( 'authority_mailer_smtp_options', array() );
		$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';

		if ( empty( $connected_provider ) ) {
			$classes .= ' authority-mailer-no-provider';
		}

		return $classes;
	}
);

/**
 * Render the Authority Mailer Dashboard page
 */
function authority_mailer_smtp_render_dashboard_page() {
	// Redirect to login if not authenticated
	if ( ! is_user_logged_in() ) {
		auth_redirect();
		return;
	}

	// Check capability for logged-in users
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( authority_mailer_smtp_get_string( 'no_permission' ) ) );
	}

	$options            = get_option( 'authority_mailer_smtp_options', array() );
	$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';
	$is_premium         = apply_filters( 'authority_mailer_smtp_is_premium', false );
	$upgrade_url        = apply_filters( 'authority_mailer_smtp_upgrade_url', 'https://www.authorityplugins.com/products/authority-mailer-smtp' );

	// Load pro banner component
	require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/components/pro-banner.php';

	// Load dashboard stats class
	require_once AUTHORITY_MAILER_PLUGIN_DIR . 'includes/admin/class-authority-mailer-dashboard-stats.php';

	// Get cached dashboard statistics
	$dashboard_data  = Authority_Mailer_Dashboard_Stats::get_stats();
	$stats           = $dashboard_data['summary'];
	$daily_stats     = $dashboard_data['daily'];
	$weekly_stats    = $dashboard_data['weekly'];
	$monthly_stats   = $dashboard_data['monthly'];
	$total_log_count = $stats['total_all'];

	// Check if we have any email data for empty state detection
	$has_email_data = $total_log_count > 0;

	$success_rate  = $stats['total_7d'] > 0 ? round( ( $stats['success_7d'] / $stats['total_7d'] ) * 100, 1 ) : 0;
	$avg_daily     = round( $stats['total_7d'] / 7, 1 );
	$trend_percent = $avg_daily > 0 ? round( ( ( $stats['total_today'] - $avg_daily ) / $avg_daily ) * 100, 0 ) : 0;

	$provider_names = array(
		'sendlayer' => 'SendLayer',
		'smtpcom'   => 'SMTP.com',
		'brevo'     => 'Brevo',
		'mailgun'   => 'Mailgun',
		'sendgrid'  => 'SendGrid',
		'gmail'     => 'Gmail',
		'google'    => 'Gmail',
		'postmark'  => 'Postmark',
		'other'     => 'Custom SMTP',
	);
	$provider_name  = isset( $provider_names[ $connected_provider ] ) ? $provider_names[ $connected_provider ] : ucfirst( $connected_provider );

	$provider_colors = array(
		'sendgrid'  => '#1A82E2',
		'mailgun'   => '#F06B66',
		'gmail'     => '#EA4335',
		'google'    => '#EA4335',
		'brevo'     => '#0B996E',
		'postmark'  => '#FFDE00',
		'sendlayer' => '#6366F1',
		'other'     => '#6366F1',
	);
	$provider_color  = isset( $provider_colors[ $connected_provider ] ) ? $provider_colors[ $connected_provider ] : '#6366F1';

	$max_daily         = ! empty( $daily_stats ) ? max( array_column( $daily_stats, 'total' ) ) : 1;
	$max_daily         = max( $max_daily, 1 );
	$log_usage_percent = min( round( ( $total_log_count / 100 ) * 100 ), 100 );
	$circumference     = 2 * 3.14159 * 45;
	$offset            = $circumference - ( $success_rate / 100 ) * $circumference;

	// Enqueue Chart.js from local assets.
	$chartjs_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/chart.umd.min.js';
	$chartjs_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/chart.umd.min.js';
	$chartjs_ver  = file_exists( $chartjs_path ) ? filemtime( $chartjs_path ) : AUTHORITY_MAILER_VERSION;

	wp_enqueue_script(
		'chart-js',
		$chartjs_url,
		array(),
		$chartjs_ver,
		true
	);

	// Enqueue dashboard chart JavaScript.
	$chart_js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/dashboard-chart.js';
	$chart_js_url  = AUTHORITY_MAILER_PLUGIN_URL . 'assets/js/dashboard-chart.js';
	$chart_js_ver  = file_exists( $chart_js_path ) ? filemtime( $chart_js_path ) : AUTHORITY_MAILER_VERSION;

	wp_enqueue_script(
		'authority-mailer-dashboard-chart',
		$chart_js_url,
		array( 'jquery', 'chart-js' ),
		$chart_js_ver,
		true
	);

	// Localize chart data and strings for JavaScript.
	wp_localize_script(
		'authority-mailer-dashboard-chart',
		'authorityMailerChartData',
		array(
			'daily'   => $daily_stats,
			'weekly'  => $weekly_stats,
			'monthly' => $monthly_stats,
			'strings' => array(
				'daily'      => authority_mailer_smtp_get_string( 'dashboard_daily' ),
				'weekly'     => authority_mailer_smtp_get_string( 'dashboard_weekly' ),
				'monthly'    => authority_mailer_smtp_get_string( 'dashboard_monthly' ),
				'success'    => authority_mailer_smtp_get_string( 'dashboard_success' ),
				'failed'     => authority_mailer_smtp_get_string( 'dashboard_failed' ),
				'pending'    => authority_mailer_smtp_get_string( 'dashboard_pending' ),
				'emailsSent' => authority_mailer_smtp_get_string( 'dashboard_emails_sent' ),
				'chartTitle' => authority_mailer_smtp_get_string( 'dashboard_email_analytics' ),
				'total'      => authority_mailer_smtp_get_string( 'dashboard_total' ),
			),
		)
	);

	// Render the header component.
	?>
	<div class="am-wrap">
		<div class="am-container">
			<?php authority_mailer_smtp_render_admin_header( 'dashboard', $connected_provider ); ?>
		</div>
	</div>

	<div class="am-wrap am-dashboard">
		<div class="am-container">
			<!-- Provider Status Card -->
			<div class="am-card am-mb-6">
				<?php if ( empty( $connected_provider ) ) : ?>
					<!-- No Provider Configured -->
					<div class="am-card-body am-provider-empty-state">
						<div class="am-provider-empty-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--am-danger)" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<line x1="12" y1="8" x2="12" y2="12"></line>
								<line x1="12" y1="16" x2="12.01" y2="16"></line>
							</svg>
						</div>
						<h3 class="am-provider-empty-title">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_no_provider_configured' ) ); ?>
						</h3>
						<p class="am-provider-empty-desc">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_no_provider_desc' ) ); ?>
						</p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding' ) ); ?>" class="am-btn am-btn-primary">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="3"></circle>
								<path d="M12 1v6m0 6v6m-5.5-10.5 4.24 4.24m4.24 4.24 4.24 4.24M1 12h6m6 0h6M4.22 4.22l4.24 4.24m4.24 4.24 4.24 4.24"></path>
							</svg>
							<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_configure_provider' ) ); ?>
						</a>
					</div>
				<?php else : ?>
					<!-- Provider Configured -->
					<?php
					// Get provider display name.
					$provider_names   = array(
						'sendgrid'       => 'SendGrid',
						'mailgun'        => 'Mailgun',
						'postmark'       => 'Postmark',
						'sparkpost'      => 'SparkPost',
						'brevo'          => 'Brevo',
						'sendlayer'      => 'SendLayer',
						'smtpcom'        => 'SMTP.com',
						'mailjet'        => 'Mailjet',
						'amazonses'      => 'Amazon SES',
						'mandrill'       => 'Mandrill',
						'elasticemail'   => 'Elastic Email',
						'mailersend'     => 'MailerSend',
						'postmastersend' => 'Postmaster',
						'zoho'           => 'Zoho Mail',
						'gmail'          => 'Gmail',
						'office365'      => 'Office 365',
						'other'          => 'Other SMTP',
					);
					$provider_display = isset( $provider_names[ $connected_provider ] ) ? $provider_names[ $connected_provider ] : ucfirst( $connected_provider );

					// Get last email sent timestamp
					global $wpdb;
					$last_email_time = '';
					if ( $has_email_data ) {
						$table = $wpdb->prefix . 'am_email_log';
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$last_email = $wpdb->get_row( $wpdb->prepare( "SELECT created_at FROM `{$table}` ORDER BY created_at DESC LIMIT %d", 1 ) );
						if ( $last_email && isset( $last_email->created_at ) ) {
							$ts = strtotime( $last_email->created_at );
							if ( $ts ) {
								$last_email_time = human_time_diff( $ts, current_time( 'timestamp' ) ) . ' ' . authority_mailer_smtp_get_string( 'dashboard_ago' );
							}
						}
					}
					?>
					<div class="am-card-body am-provider-connected am-provider-connected-lighter">
						<div class="am-provider-connected-flex">
							<div class="am-provider-connected-icon am-provider-icon-lighter">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
									<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
									<polyline points="22 4 12 14.01 9 11.01"></polyline>
								</svg>
							</div>
							<div class="am-provider-connected-content">
								<div class="am-provider-title-row">
									<h3 class="am-provider-title">
										<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_all_systems_operational' ) ); ?>
									</h3>
									<span class="am-badge am-badge-success-light am-provider-badge-enhanced"><?php echo esc_html( $provider_display ); ?></span>
								</div>
								<p class="am-provider-desc">
									<?php
									if ( $last_email_time ) {
										printf(
											/* translators: %1$s: provider name, %2$s: time since last email */
											esc_html( authority_mailer_smtp_get_string( 'dashboard_provider_active_with_time' ) ),
											'<strong>' . esc_html( $provider_display ) . '</strong>',
											'<span class="am-last-email-time">' . esc_html( $last_email_time ) . '</span>'
										);
									} else {
										printf(
											/* translators: %s: provider name */
											esc_html( authority_mailer_smtp_get_string( 'dashboard_provider_active_desc' ) ),
											'<strong>' . esc_html( $provider_display ) . '</strong>'
										);
									}
									?>
								</p>
							</div>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-onboarding&step=3&provider=' . $connected_provider ) ); ?>" class="am-btn am-btn-secondary">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="12" cy="12" r="3"></circle>
									<path d="M12 1v6m0 6v6m-5.5-10.5 4.24 4.24m4.24 4.24 4.24 4.24M1 12h6m6 0h6M4.22 4.22l4.24 4.24m4.24 4.24 4.24 4.24"></path>
								</svg>
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_manage_settings' ) ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<!-- Stats Grid -->
			<div class="am-dashboard-stats">
				<div class="am-stat-card" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_total_sent_tooltip' ) ); ?>">
					<div class="am-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
						</svg>
					</div>
					<div class="am-stat-value"><?php echo esc_html( number_format( $stats['total_7d'] ) ); ?></div>
					<div class="am-stat-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_total_sent' ) ); ?></div>
					<?php if ( 0 !== $trend_percent ) : ?>
						<div class="am-stat-trend">
							<?php echo $trend_percent > 0 ? '↑' : '↓'; ?> <?php echo esc_html( abs( $trend_percent ) ); ?>% vs <?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_last_7_days' ) ); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="am-stat-card am-stat-success" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_delivered_tooltip' ) ); ?>">
					<div class="am-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
					</div>
					<div class="am-stat-value"><?php echo esc_html( number_format( $stats['success_7d'] ) ); ?></div>
					<div class="am-stat-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_delivered' ) ); ?></div>
					<?php if ( 0 === $stats['failed_7d'] ) : ?>
						<div class="am-stat-helper">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_no_delivery_issues' ) ); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="am-stat-card am-stat-danger" title="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_failed_tooltip' ) ); ?>">
					<div class="am-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="8" x2="12" y2="12"></line>
							<line x1="12" y1="16" x2="12.01" y2="16"></line>
						</svg>
					</div>
					<div class="am-stat-value"><?php echo esc_html( number_format( $stats['failed_7d'] ) ); ?></div>
					<div class="am-stat-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_failed' ) ); ?></div>
					<?php if ( $stats['failed_7d'] > 0 ) : ?>
						<div class="am-stat-trend">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log&status=error' ) ); ?>" style="color: white; text-decoration: underline;">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_view' ) ); ?> →
							</a>
						</div>
					<?php else : ?>
						<div class="am-stat-helper">
							<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_no_failures_period' ) ); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="am-stat-card am-stat-warning am-stat-pro-feature <?php echo ! $is_premium ? 'locked' : ''; ?>">
					<?php if ( ! $is_premium ) : ?>
						<div class="am-stat-lock-overlay am-stat-lock-nonblocking">
							<span class="am-stat-lock-text-inline"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_pro_feature_badge' ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="am-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="20" x2="18" y2="10"></line>
							<line x1="12" y1="20" x2="12" y2="4"></line>
							<line x1="6" y1="20" x2="6" y2="14"></line>
						</svg>
					</div>
					<div class="am-stat-value">—</div>
					<div class="am-stat-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_bounce_tracking' ) ); ?></div>
					<div class="am-stat-helper">
						<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_pro_feature_badge' ) ); ?>
					</div>
				</div>
			</div>

			<!-- Main Content Grid -->
			<div class="am-dashboard-main-grid">
				<div>
					<!-- Email Analytics Chart -->
				<div class="am-card am-chart-card">
					<div class="am-card-header am-chart-header">
						<div class="am-chart-title-wrapper">
							<h2 class="am-card-title">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="18" y1="20" x2="18" y2="10"></line>
									<line x1="12" y1="20" x2="12" y2="4"></line>
									<line x1="6" y1="20" x2="6" y2="14"></line>
								</svg>
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_email_analytics' ) ); ?>
							</h2>
						</div>
						<div class="am-chart-toggle-buttons" role="tablist" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_chart_period_selector' ) ); ?>">
							<button class="am-chart-toggle active" data-period="daily" type="button" role="tab" aria-selected="true" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_daily' ) ); ?>">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_daily' ) ); ?>
							</button>
							<button class="am-chart-toggle" data-period="weekly" type="button" role="tab" aria-selected="false" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_weekly' ) ); ?>">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_weekly' ) ); ?>
							</button>
							<button class="am-chart-toggle" data-period="monthly" type="button" role="tab" aria-selected="false" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_monthly' ) ); ?>">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_monthly' ) ); ?>
							</button>
						</div>
					</div>
					<div class="am-card-body am-chart-body">
					<?php if ( ! $has_email_data ) : ?>
						<!-- Empty State - Calm & Reassuring -->
						<div class="am-chart-empty-state am-chart-empty-reduced">
							<div class="am-empty-illustration">
								<!-- Friendly envelope illustration with soft colors -->
								<svg width="100" height="100" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<!-- Envelope base -->
									<rect x="20" y="40" width="80" height="50" rx="4" fill="#E0E7FF" stroke="#A5B4FC" stroke-width="2"/>
									<!-- Envelope flap -->
									<path d="M20 40 L60 65 L100 40" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
									<!-- Small decorative dots representing future data -->
									<circle cx="35" cy="100" r="2" fill="#C7D2FE" opacity="0.6"/>
									<circle cx="50" cy="105" r="2" fill="#C7D2FE" opacity="0.6"/>
									<circle cx="65" cy="103" r="2" fill="#C7D2FE" opacity="0.6"/>
									<circle cx="80" cy="108" r="2" fill="#C7D2FE" opacity="0.6"/>
								</svg>
							</div>
							<h3 class="am-empty-state-title"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_no_email_activity' ) ); ?></h3>
							<p class="am-empty-state-description">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_chart_empty_message' ) ); ?>
							</p>
							<div class="am-empty-state-actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-tools&tab=test-email' ) ); ?>" class="am-btn am-btn-primary am-btn-sm">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
									</svg>
									<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_send_test_email' ) ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log' ) ); ?>" class="am-btn am-btn-secondary am-btn-sm">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_view_email_logs' ) ); ?>
								</a>
							</div>
						</div>
					<?php else : ?>
						<div class="am-chart-legend">
							<div class="am-chart-legend-item">
								<span class="am-chart-legend-dot" style="background-color: #10B981;"></span>
								<span><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_success' ) ); ?></span>
							</div>
							<div class="am-chart-legend-item">
								<span class="am-chart-legend-dot" style="background-color: #EF4444;"></span>
								<span><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_failed' ) ); ?></span>
							</div>
							<div class="am-chart-legend-item">
								<span class="am-chart-legend-dot" style="background-color: #F59E0B;"></span>
								<span><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_pending' ) ); ?></span>
							</div>
						</div>
						<div class="am-chart-container">
							<canvas id="authorityMailerEmailChart" role="img" aria-label="<?php echo esc_attr( authority_mailer_smtp_get_string( 'dashboard_email_analytics' ) ); ?>"></canvas>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Recent Emails -->
				<?php
				global $wpdb;
					$table        = $wpdb->prefix . 'am_email_log';
					$table_exists = $has_email_data; // Use the same check
					if ( $has_email_data ) {
						// Fetch recent emails from the database
						$escaped_table = esc_sql( $table );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$recent_emails = $wpdb->get_results( $wpdb->prepare( "SELECT id, to_email, subject, status, created_at FROM `{$escaped_table}` ORDER BY created_at DESC LIMIT %d", 10 ) );
					} else {
						$recent_emails = array();
					}
					?>
					<div class="am-card">
						<div class="am-card-header">
							<h2 class="am-card-title">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
								</svg>
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_recent_emails' ) ); ?>
							</h2>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=authority-mailer-smtp-email-log' ) ); ?>" class="am-view-all-link">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_view_all' ) ); ?> →
							</a>
						</div>
						<div class="am-card-body">
							<?php if ( ! empty( $recent_emails ) ) : ?>
								<table class="am-table">
									<thead>
										<tr>
											<th><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_to' ) ); ?></th>
											<th><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_subject' ) ); ?></th>
											<th><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_status' ) ); ?></th>
											<th><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_date' ) ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $recent_emails as $email ) : ?>
											<?php
											$email_status = isset( $email->status ) ? trim( strtolower( $email->status ) ) : 'unknown';
											$status_class = 'pending';

											if ( in_array( $email_status, array( 'success', 'accepted', 'sent' ), true ) ) {
												$status_class = 'success';
											} elseif ( in_array( $email_status, array( 'error', 'failed', 'bounce' ), true ) ) {
												$status_class = 'error';
											} elseif ( in_array( $email_status, array( 'pending', 'attempt', 'queued' ), true ) ) {
												$status_class = 'pending';
											}

											$email_date = isset( $email->created_at ) ? $email->created_at : '';
											$time_ago   = '';
											if ( $email_date ) {
												$ts = strtotime( $email_date );
												if ( $ts ) {
													$time_ago = human_time_diff( $ts, current_time( 'timestamp' ) ) . ' ' . authority_mailer_smtp_get_string( 'dashboard_ago' );
												}
											}
											?>
											<tr>
												<td>
													<span class="am-text-ellipsis-email">
														<?php echo isset( $email->to_email ) ? esc_html( $email->to_email ) : '—'; ?>
													</span>
												</td>
												<td>
													<span class="am-text-ellipsis-subject">
														<?php echo isset( $email->subject ) ? esc_html( $email->subject ) : '—'; ?>
													</span>
												</td>
												<td>
													<span class="am-status-badge <?php echo esc_attr( $status_class ); ?>">
														<?php echo isset( $email->status ) ? esc_html( strtoupper( $email->status ) ) : '—'; ?>
													</span>
												</td>
												<td>
													<span class="am-timestamp-text">
														<?php echo esc_html( $time_ago ); ?>
													</span>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
						<?php else : ?>
							<!-- Empty State - Recent Emails -->
							<div class="am-empty-state">
								<div class="am-empty-illustration am-empty-illustration-sm">
									<!-- Simple inbox illustration -->
									<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<!-- Inbox tray -->
										<rect x="15" y="30" width="50" height="35" rx="3" fill="#F3F4F6" stroke="#D1D5DB" stroke-width="2"/>
										<path d="M15 45 L30 45 C30 50 35 55 40 55 C45 55 50 50 50 45 L65 45" stroke="#9CA3AF" stroke-width="2" fill="none"/>
										<!-- Small envelope inside -->
										<rect x="28" y="20" width="24" height="16" rx="2" fill="#E0E7FF" stroke="#A5B4FC" stroke-width="1.5"/>
										<path d="M28 20 L40 30 L52 20" stroke="#6366F1" stroke-width="1.5" stroke-linecap="round" fill="none"/>
									</svg>
								</div>
								<h4 class="am-empty-state-title-sm">No emails logged yet</h4>
								<p class="am-empty-state-text">
									Your sent emails will be listed here once you start sending.
								</p>
							</div>
						<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Sidebar -->
				<div>
					<!-- Delivery Health -->
					<div class="am-card">
						<div class="am-card-header">
							<h2 class="am-card-title">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
									<polyline points="22 4 12 14.01 9 11.01"></polyline>
								</svg>
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_delivery_health' ) ); ?>
							</h2>
						</div>
						<div class="am-card-body">
							<div class="am-health-score">
								<div class="am-health-circle">
									<svg viewBox="0 0 110 110">
										<circle class="bg" cx="55" cy="55" r="45"></circle>
										<circle class="progress" cx="55" cy="55" r="45" stroke-dasharray="<?php echo esc_attr( $circumference ); ?>" stroke-dashoffset="<?php echo esc_attr( $offset ); ?>"></circle>
									</svg>
									<span class="am-health-value"><?php echo esc_html( round( $success_rate ) ); ?>%</span>
								</div>
								<p class="am-health-label"><?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_delivery_rate' ) ); ?></p>
								<p class="am-health-status am-health-status-text" style="color:<?php echo $success_rate >= 95 ? 'var(--am-success)' : ( $success_rate >= 80 ? 'var(--am-warning)' : 'var(--am-danger)' ); ?>;">
									● <?php echo $success_rate >= 95 ? esc_html( authority_mailer_smtp_get_string( 'dashboard_excellent' ) ) : ( $success_rate >= 80 ? esc_html( authority_mailer_smtp_get_string( 'dashboard_good' ) ) : esc_html( authority_mailer_smtp_get_string( 'dashboard_needs_attention' ) ) ); ?>
								</p>
							</div>
						</div>
					</div>


				<?php if ( ! $is_premium ) : ?>
					<!-- Unlock Detailed Email Analytics Card -->
					<div class="am-card am-pro-feature-card">
						<div class="am-card-body">
							<div class="am-pro-feature-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
								</svg>
							</div>
							<h3 class="am-pro-feature-title">Unlock Detailed Email Analytics!</h3>
							<ul class="am-pro-feature-list">
								<li>
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<div>
										<strong>Engagement Tracking</strong>
										<span>(Opens &amp; Clicks analytics)</span>
									</div>
								</li>
								<li>
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<div>
										<strong>Advanced Email Testing</strong>
										<span>(Real-time delivery tests)</span>
									</div>
								</li>
								<li>
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<div>
										<strong>Compliance Tools</strong>
										<span>(Spam score check)</span>
									</div>
								</li>
							</ul>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="am-btn am-btn-primary am-pro-feature-btn">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'dashboard_unlock_pro_analytics' ) ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
