<?php
/**
 * Authority Mailer SMTP - Free vs Pro Comparison Page
 *
 * WordPress.org compliant informational comparison page showing feature differences
 * between the free and Pro versions.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue assets for the Free vs Pro page
 *
 * @since 1.0.0
 */
function authority_mailer_smtp_free_vs_pro_enqueue_assets() {
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'authority-mailer-free-vs-pro' ) === false ) {
		return;
	}

	// Enqueue unified admin styles.
	authority_mailer_smtp_enqueue_admin_styles( 'free-vs-pro' );

	// Enqueue premium assets (CSS only, no JS needed).
	Authority_Mailer_Admin_Assets::enqueue_premium_assets( false );

	// Enqueue Free vs Pro specific CSS.
	$css_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/css/free-vs-pro.css';
	$css_url  = plugins_url( 'assets/css/free-vs-pro.css', AUTHORITY_MAILER_PLUGIN_FILE );
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : AUTHORITY_MAILER_VERSION;
	wp_enqueue_style( 'authority-mailer-smtp-free-vs-pro', $css_url, array( 'authority-mailer-smtp-admin' ), $css_ver );

	// Enqueue dashicons for checkmark and minus icons.
	wp_enqueue_style( 'dashicons' );
}
add_action( 'admin_enqueue_scripts', 'authority_mailer_smtp_free_vs_pro_enqueue_assets' );

/**
 * Render the Free vs Pro comparison page
 *
 * @since 1.0.0
 */
function authority_mailer_smtp_render_free_vs_pro_page() {
	// Permission check.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( authority_mailer_smtp_get_string( 'no_permission' ) ) );
	}

	// Get connected provider for header.
	$options            = get_option( 'authority_mailer_smtp_options', array() );
	$connected_provider = isset( $options['selected_mailer'] ) && is_string( $options['selected_mailer'] ) ? $options['selected_mailer'] : '';

	$pro_url = 'https://www.authorityplugins.com/products/authority-mailer-smtp';

	// Comparison features data organized by category.
	$feature_groups = array(
		'delivery'    => array(
			'title' => authority_mailer_smtp_get_string( 'free_vs_pro_group_delivery' ),
			'icon'  => 'email',
			'features' => array(
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_core_smtp' ),
					'free' => 'check',
					'pro'  => 'check',
				),
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_provider_support' ),
					'free' => authority_mailer_smtp_get_string( 'free_vs_pro_14_providers' ),
					'pro'  => authority_mailer_smtp_get_string( 'free_vs_pro_17_providers' ),
				),
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_gmail_outlook' ),
					'free' => 'check',
					'pro'  => 'check',
				),
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_custom_smtp' ),
					'free' => 'check',
					'pro'  => 'check',
				),
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_email_logs' ),
					'free' => authority_mailer_smtp_get_string( 'free_vs_pro_basic' ),
					'pro'  => authority_mailer_smtp_get_string( 'free_vs_pro_unlimited' ),
				),
				array(
					'name' => authority_mailer_smtp_get_string( 'free_vs_pro_delivery_status' ),
					'free' => 'check',
					'pro'  => 'check',
				),
			),
		),
		'analytics'   => array(
			'title' => authority_mailer_smtp_get_string( 'free_vs_pro_group_analytics' ),
			'icon'  => 'chart-line',
			'features' => array(
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_open_click_tracking' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_open_click_tracking' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_analytics_dashboard' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_analytics_dashboard' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_delivery_trends' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_delivery_trends' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_geographic_analytics' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_geographic_analytics' ),
				),
			),
		),
		'reliability' => array(
			'title' => authority_mailer_smtp_get_string( 'free_vs_pro_group_reliability' ),
			'icon'  => 'shield',
			'features' => array(
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_bounce_spam' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_bounce_spam' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_webhook_receiver' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_webhook_receiver' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_smart_failover' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_smart_failover' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_ai_insights' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_ai_insights' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_email_templates' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_email_templates' ),
				),
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_gdpr_compliance' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_gdpr_compliance' ),
				),
			),
		),
		'support'     => array(
			'title' => authority_mailer_smtp_get_string( 'free_vs_pro_group_support' ),
			'icon'  => 'sos',
			'features' => array(
				array(
					'name'        => authority_mailer_smtp_get_string( 'free_vs_pro_priority_support' ),
					'free'        => 'minus',
					'pro'         => 'check',
					'description' => authority_mailer_smtp_get_string( 'free_vs_pro_desc_priority_support' ),
				),
			),
		),
	);
	?>
	<div class="am-wrap">
		<div class="am-container">
			<?php authority_mailer_smtp_render_admin_header( 'free-vs-pro', $connected_provider ); ?>
		</div>
	</div>

	<div class="wrap am-wrap">
		<h1><?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_page_title' ) ); ?></h1>

		<div class="authority-mailer-card">
			<!-- Enhanced Intro Section -->
			<div class="authority-mailer-intro-section">
				<h2 class="intro-heading"><?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_intro_heading' ) ); ?></h2>
				<p class="authority-mailer-intro">
					<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_intro' ) ); ?>
				</p>
				<p class="authority-mailer-intro-secondary">
					<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_intro_secondary' ) ); ?>
				</p>
			</div>

			<table class="wp-list-table widefat fixed striped" role="table" aria-label="<?php esc_attr_e( 'Feature comparison between Free and Pro versions', 'authority-mailer-smtp' ); ?>">
				<thead>
					<tr>
						<th scope="col" class="column-feature"><?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_feature_column' ) ); ?></th>
						<th scope="col" class="column-free"><?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_free_column' ) ); ?></th>
						<th scope="col" class="column-pro">
							<span class="pro-header-content">
								<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_pro_column' ) ); ?>
								<span class="recommended-badge" aria-label="<?php esc_attr_e( 'Recommended plan', 'authority-mailer-smtp' ); ?>">
									<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_recommended_badge' ) ); ?>
								</span>
							</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $feature_groups as $group_key => $group ) : ?>
						<!-- Feature Group Header -->
						<tr class="feature-group-header">
							<th colspan="3" scope="colgroup" class="group-title">
								<span class="dashicons dashicons-<?php echo esc_attr( $group['icon'] ); ?>" aria-hidden="true"></span>
								<?php echo esc_html( $group['title'] ); ?>
							</th>
						</tr>
						
						<!-- Feature Rows -->
						<?php foreach ( $group['features'] as $feature ) : ?>
							<tr>
								<td class="column-feature" scope="row">
									<strong><?php echo esc_html( $feature['name'] ); ?></strong>
									<?php if ( ! empty( $feature['description'] ) ) : ?>
										<span class="feature-description"><?php echo esc_html( $feature['description'] ); ?></span>
									<?php endif; ?>
								</td>
								<td class="column-free">
									<?php
									if ( 'check' === $feature['free'] ) {
										echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-label="' . esc_attr__( 'Available', 'authority-mailer-smtp' ) . '"></span>';
									} elseif ( 'minus' === $feature['free'] ) {
										echo '<span class="dashicons dashicons-minus" style="color: #999;" aria-label="' . esc_attr__( 'Not available', 'authority-mailer-smtp' ) . '"></span>';
									} else {
										echo esc_html( $feature['free'] );
									}
									?>
								</td>
								<td class="column-pro">
									<?php
									if ( 'check' === $feature['pro'] ) {
										echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" aria-label="' . esc_attr__( 'Available', 'authority-mailer-smtp' ) . '"></span>';
									} elseif ( 'minus' === $feature['pro'] ) {
										echo '<span class="dashicons dashicons-minus" style="color: #999;" aria-label="' . esc_attr__( 'Not available', 'authority-mailer-smtp' ) . '"></span>';
									} else {
										echo esc_html( $feature['pro'] );
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="authority-mailer-footer">
				<p class="authority-mailer-disclaimer">
					<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_footer_disclaimer' ) ); ?>
				</p>
				<p class="authority-mailer-cta">
					<a href="<?php echo esc_url( $pro_url ); ?>" 
						class="button button-primary button-hero" 
						target="_blank" 
						rel="noopener noreferrer">
						<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
						<?php echo esc_html( authority_mailer_smtp_get_string( 'free_vs_pro_upgrade_cta' ) ); ?>
					</a>
				</p>
			</div>
		</div>
	</div>
	<?php
}
