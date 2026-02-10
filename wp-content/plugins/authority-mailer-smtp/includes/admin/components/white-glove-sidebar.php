<?php
/**
 * White-Glove Installation Service Sidebar Component
 *
 * Displays a professional white-glove installation service offering
 * in the onboarding wizard sidebar for free version users only.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the White-Glove Installation Service sidebar
 *
 * This component displays for all users without dismissal option.
 *
 * @since 1.0.0
 *
 * @param int $step Current onboarding wizard step (0-3).
 * @return void
 */
function authority_mailer_smtp_render_white_glove_sidebar( $step = 0 ) {

	// Get strings from centralized strings file
	$badge            = authority_mailer_smtp_get_string( 'white_glove_badge' );
	$headline         = authority_mailer_smtp_get_string( 'white_glove_headline' );
	$subheadline      = authority_mailer_smtp_get_string( 'white_glove_subheadline' );
	$benefit_1        = authority_mailer_smtp_get_string( 'white_glove_benefit_1' );
	$benefit_2        = authority_mailer_smtp_get_string( 'white_glove_benefit_2' );
	$benefit_3        = authority_mailer_smtp_get_string( 'white_glove_benefit_3' );
	$benefit_4        = authority_mailer_smtp_get_string( 'white_glove_benefit_4' );
	$benefit_5        = authority_mailer_smtp_get_string( 'white_glove_benefit_5' );
	$price            = authority_mailer_smtp_get_string( 'white_glove_price' );
	$original_price   = authority_mailer_smtp_get_string( 'white_glove_original_price' );
	$price_label      = authority_mailer_smtp_get_string( 'white_glove_price_label' );
	$save_badge       = authority_mailer_smtp_get_string( 'white_glove_save_badge' );
	$rating           = authority_mailer_smtp_get_string( 'white_glove_rating' );
	$social_proof     = authority_mailer_smtp_get_string( 'white_glove_social_proof' );
	$guarantee        = authority_mailer_smtp_get_string( 'white_glove_guarantee' );
	$turnaround       = authority_mailer_smtp_get_string( 'white_glove_turnaround' );
	$cta_button       = authority_mailer_smtp_get_string( 'white_glove_cta_button' );
	$cta_secondary    = authority_mailer_smtp_get_string( 'white_glove_cta_secondary' );
	$disclaimer       = authority_mailer_smtp_get_string( 'white_glove_disclaimer' );

	// Get step-specific message
	$step_messages = array(
		0 => authority_mailer_smtp_get_string( 'white_glove_step_1_message' ),
		1 => authority_mailer_smtp_get_string( 'white_glove_step_2_message' ),
		2 => authority_mailer_smtp_get_string( 'white_glove_step_3_message' ),
		3 => authority_mailer_smtp_get_string( 'white_glove_step_4_message' ),
	);

	$step_message = isset( $step_messages[ $step ] ) ? $step_messages[ $step ] : $step_messages[0];

	// Booking URL - links to authorityplugins.com booking page
	$booking_url  = 'https://www.authorityplugins.com/products/authority-mailer-smtp/white-glove-setup';
	$booking_url  = apply_filters( 'authority_mailer_smtp_white_glove_booking_url', $booking_url );

	?>
	<div class="am-white-glove-sidebar" role="complementary" aria-label="<?php echo esc_attr( $badge ); ?>">
		<div class="am-white-glove-header">
			<span class="am-white-glove-badge"><strong><?php echo esc_html( $badge ); ?></strong></span>
			<div class="am-white-glove-icon" aria-hidden="true">🛡️</div>
			<h3 class="am-white-glove-headline"><?php echo esc_html( $headline ); ?></h3>
			<p class="am-white-glove-subheadline"><?php echo esc_html( $subheadline ); ?></p>
		</div>

		<p class="am-white-glove-step-message"><strong><?php echo esc_html( $step_message ); ?></strong></p>

		<div class="am-white-glove-benefits">
			<div class="am-white-glove-benefit">
				<svg class="am-white-glove-check" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php echo esc_html( $benefit_2 ); ?></span>
			</div>
			<div class="am-white-glove-benefit">
				<svg class="am-white-glove-check" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php echo esc_html( $benefit_3 ); ?></span>
			</div>
			<div class="am-white-glove-benefit">
				<svg class="am-white-glove-check" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php echo esc_html( $benefit_4 ); ?></span>
			</div>
			<div class="am-white-glove-benefit">
				<svg class="am-white-glove-check" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span><?php echo esc_html( $benefit_5 ); ?></span>
			</div>
		</div>

		<div class="am-white-glove-trust-signals">
			<div class="am-white-glove-trust-signal">
				<span class="am-white-glove-trust-icon" aria-hidden="true">⭐⭐⭐⭐⭐</span>
				<span><?php echo esc_html( $rating ); ?></span>
			</div>
			<div class="am-white-glove-trust-signal">
				<span class="am-white-glove-trust-icon" aria-hidden="true">👥</span>
				<span><?php echo esc_html( $social_proof ); ?></span>
			</div>
			<div class="am-white-glove-trust-signal">
				<span class="am-white-glove-trust-icon" aria-hidden="true">⏱️</span>
				<span><?php echo esc_html( $turnaround ); ?></span>
			</div>
		</div>
		<p class="am-white-glove-disclaimer"><?php echo esc_html( $disclaimer ); ?></p>
		<div class="am-white-glove-cta">
			<a href="<?php echo esc_url( $booking_url ); ?>" target="_blank" rel="noopener" class="am-white-glove-cta-button">
				<?php echo esc_html( $cta_button ); ?>
			</a>
			<p class="am-white-glove-cta-secondary"><?php echo esc_html( $cta_secondary ); ?></p>
		</div>
	</div>
	<?php
}
