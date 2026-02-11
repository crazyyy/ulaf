<?php
/**
 * Pro Banner Component
 *
 * Reusable banner component for displaying premium upgrade promotions
 * across different pages in the Authority Mailer SMTP plugin.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the Pro upgrade banner
 *
 * @param string $style Banner style: 'full', 'sidebar', or 'compact'. Default 'full'.
 * @return void
 */
function authority_mailer_smtp_render_pro_banner( $style = 'full' ) {
	$is_premium = apply_filters( 'authority_mailer_smtp_is_premium', false );

	// Don't show banner if premium is active
	if ( $is_premium ) {
		return;
	}

	$upgrade_url = apply_filters( 'authority_mailer_smtp_upgrade_url', 'https://www.authorityplugins.com/products/authority-mailer-smtp' );

	// Get strings from centralized strings file
	$strings = array(
		'title'                   => authority_mailer_smtp_get_string( 'pro_banner_title' ),
		'limited_offer'           => authority_mailer_smtp_get_string( 'pro_banner_limited_offer' ),
		'upgrade_cta'             => authority_mailer_smtp_get_string( 'pro_banner_upgrade_cta' ),
		'17_providers'            => authority_mailer_smtp_get_string( 'pro_banner_17_providers' ),
		'unlimited_logs'          => authority_mailer_smtp_get_string( 'pro_banner_unlimited_logs' ),
		'open_click_tracking'     => authority_mailer_smtp_get_string( 'pro_banner_open_click_tracking' ),
		'smart_failover'          => authority_mailer_smtp_get_string( 'pro_banner_smart_failover' ),
		'bounce_handling'         => authority_mailer_smtp_get_string( 'pro_banner_bounce_handling' ),
		'advanced_analytics'      => authority_mailer_smtp_get_string( 'pro_banner_advanced_analytics' ),
		'email_templates'         => authority_mailer_smtp_get_string( 'pro_banner_email_templates' ),
		'spam_checker'            => authority_mailer_smtp_get_string( 'pro_banner_spam_checker' ),
		'priority_support'        => authority_mailer_smtp_get_string( 'pro_banner_priority_support' ),
		'ai_insights'             => authority_mailer_smtp_get_string( 'pro_banner_ai_insights' ),
		'gdpr_compliance'         => authority_mailer_smtp_get_string( 'pro_banner_gdpr_compliance' ),
		'geographic_tracking'     => authority_mailer_smtp_get_string( 'pro_banner_geographic_tracking' ),
		'email_health'            => authority_mailer_smtp_get_string( 'pro_banner_email_health' ),
		'real_time_notifications' => authority_mailer_smtp_get_string( 'pro_banner_real_time_notifications' ),
		'money_back'              => authority_mailer_smtp_get_string( 'upgrade_money_back_guarantee' ),
		'testimonial_quote'       => authority_mailer_smtp_get_string( 'upgrade_testimonial_quote' ),
		'testimonial_author'      => authority_mailer_smtp_get_string( 'upgrade_testimonial_author' ),
		'testimonial_role'        => authority_mailer_smtp_get_string( 'upgrade_testimonial_role' ),
		'go_pro_today'            => authority_mailer_smtp_get_string( 'onboarding_go_pro_today' ),
		'reliable_delivery'       => authority_mailer_smtp_get_string( 'onboarding_reliable_delivery' ),
		'upgrade_to_premium'      => authority_mailer_smtp_get_string( 'dashboard_upgrade_to_premium' ),
		'upgrade_now'             => authority_mailer_smtp_get_string( 'common_upgrade_now' ),
	);

	// Render based on style
	switch ( $style ) {
		case 'sidebar':
			authority_mailer_smtp_render_pro_banner_sidebar( $upgrade_url, $strings );
			break;
		case 'compact':
		default:
			authority_mailer_smtp_render_pro_banner_compact( $upgrade_url, $strings );
			break;
	}
}


