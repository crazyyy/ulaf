<?php
/**
 * Authority Mailer SMTP - Postmark Provider Settings Partial
 *
 * Partial: Postmark provider settings (fixed and prefixed)
 *
 * Ensures the onboarding UI reads/writes the canonical authority_mailer_options option
 * and uses the expected key name postmark_server_token that the provider tester
 * (authority_mailer_test_postmark) looks for.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
global $AUTHORITY_MAILER_STRINGS;

/* canonical option used by providers */
$authority_mailer_options = get_option( 'authority_mailer_smtp_options', array() );
$authority_mailer_options = is_array( $authority_mailer_options ) ? $authority_mailer_options : array();

/* provider nested group if present */
$authority_mailer_nested = isset( $authority_mailer_options['postmark'] ) && is_array( $authority_mailer_options['postmark'] ) ? $authority_mailer_options['postmark'] : array();

/* read values (top-level or nested fallback) */
$authority_mailer_postmark_from_name  = isset( $authority_mailer_options['postmark_from_name'] ) ? $authority_mailer_options['postmark_from_name'] : ( isset( $authority_mailer_nested['postmark_from_name'] ) ? $authority_mailer_nested['postmark_from_name'] : get_bloginfo( 'name' ) );
$authority_mailer_postmark_from_email = isset( $authority_mailer_options['postmark_from_email'] ) ? $authority_mailer_options['postmark_from_email'] : ( isset( $authority_mailer_nested['postmark_from_email'] ) ? $authority_mailer_nested['postmark_from_email'] : get_bloginfo( 'admin_email' ) );

/* Token: accept a few legacy names but prefer postmark_server_token */
$authority_mailer_postmark_server_token = '';
if ( ! empty( $authority_mailer_options['postmark_server_token'] ) ) {
	$authority_mailer_postmark_server_token = $authority_mailer_options['postmark_server_token'];
} elseif ( ! empty( $authority_mailer_nested['postmark_server_token'] ) ) {
	$authority_mailer_postmark_server_token = $authority_mailer_nested['postmark_server_token'];
} elseif ( ! empty( $authority_mailer_options['postmark_server_api_token'] ) ) {
	$authority_mailer_postmark_server_token = $authority_mailer_options['postmark_server_api_token'];
} elseif ( ! empty( $authority_mailer_nested['postmark_server_api_token'] ) ) {
	$authority_mailer_postmark_server_token = $authority_mailer_nested['postmark_server_api_token'];
}

/* message stream id (keep name compatible) */
$authority_mailer_postmark_message_stream_id = '';
if ( ! empty( $authority_mailer_options['postmark_message_stream_id'] ) ) {
	$authority_mailer_postmark_message_stream_id = $authority_mailer_options['postmark_message_stream_id'];
} elseif ( ! empty( $authority_mailer_nested['postmark_message_stream_id'] ) ) {
	$authority_mailer_postmark_message_stream_id = $authority_mailer_nested['postmark_message_stream_id'];
}

/* force toggles */
$authority_mailer_postmark_force_name  = isset( $authority_mailer_options['postmark_force_from_name'] ) ? (bool) $authority_mailer_options['postmark_force_from_name'] : ( isset( $authority_mailer_nested['postmark_force_from_name'] ) ? (bool) $authority_mailer_nested['postmark_force_from_name'] : true );
$authority_mailer_postmark_force_email = isset( $authority_mailer_options['postmark_force_from_email'] ) ? (bool) $authority_mailer_options['postmark_force_from_email'] : ( isset( $authority_mailer_nested['postmark_force_from_email'] ) ? (bool) $authority_mailer_nested['postmark_force_from_email'] : true );

/* strings */
$authority_mailer_cta_label            = authority_mailer_smtp_get_string( 'provider_ctas' )['postmark'] ?? '';
$authority_mailer_provider_description = authority_mailer_smtp_get_string( 'provider_description_postmark' );
$authority_mailer_help_read_setup      = authority_mailer_smtp_get_string( 'help_read_setup_postmark' );

$authority_mailer_sub_sender   = authority_mailer_smtp_get_string( 'section_subheading_sender' );
$authority_mailer_sub_options  = authority_mailer_smtp_get_string( 'section_subheading_options' );
$authority_mailer_sub_provider = sprintf( authority_mailer_smtp_get_string( 'section_subheading_provider' ), 'Postmark' );
?>
<div class="wpmsl-mailer-settings wpmsl-mailer-settings--postmark" data-mailer="postmark">

	<?php if ( $authority_mailer_provider_description ) : ?>
	<div class="wpmsl-mailer-description wpmsl-mailer-description--above-cta"><?php echo wp_kses_post( wpautop( $authority_mailer_provider_description ) ); ?></div>
	<?php endif; ?>

	<div class="wpmsl-mailer-cta-row">
	<div><a class="wpmsl-btn-primary wpmsl-mailer-cta" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_link_postmark', 'https://postmarkapp.com/' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_cta_label ); ?> &rarr;</a></div>
	<div><a class="wpmsl-mailer-help-link" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_docs_postmark', 'https://postmarkapp.com/developer' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_help_read_setup ); ?></a></div>
	</div>

	<form id="wpmsl-mailer-form-postmark" class="wpmsl-mailer-form wpmsl-mailer-form--postmark" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-mailer-form="postmark" novalidate>
	<?php wp_nonce_field( 'authority_mailer_smtp_save_postmark', '_authority_mailer_postmark_nonce' ); ?>
	<input type="hidden" name="action" value="authority_mailer_smtp_save_postmark" />

	<div class="wpmsl-form-grid wpmsl-form-grid--split">
		<div class="wpmsl-form-col wpmsl-form-col--main">
		<div class="wpmsl-section-header">
			<h3 class="wpmsl-section-subheading">
				<svg class="wpmsl-section-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
				</svg>
				<?php echo esc_html( $authority_mailer_sub_sender ); ?>
			</h3>
		</div>

		<div class="wpmsl-form-row">
			<label for="postmark_from_name" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_from_name'] ); ?></label>
			<input id="postmark_from_name" name="postmark_from_name" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_postmark_from_name ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_from_name_postmark'] ?? '' ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="postmark_from_email" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_from_email'] ); ?></label>
			<input id="postmark_from_email" name="postmark_from_email" class="wpmsl-form-input" type="email" value="<?php echo esc_attr( $authority_mailer_postmark_from_email ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_from_email_postmark'] ?? '' ); ?></p>
		</div>

		<div class="wpmsl-section-divider" aria-hidden="true"></div>

		<div class="wpmsl-section-header">
			<h3 class="wpmsl-section-subheading">
				<svg class="wpmsl-section-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
				</svg>
				<?php echo esc_html( $authority_mailer_sub_provider ); ?>
			</h3>
		</div>

		<div class="wpmsl-form-row">
			<label for="postmark_server_token" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_postmark_server_api_token'] ?? 'Server Token' ); ?></label>
			<input id="postmark_server_token" name="postmark_server_token" class="wpmsl-form-input" type="password" value="<?php echo esc_attr( $authority_mailer_postmark_server_token ); ?>" autocomplete="new-password" />
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_postmark_server_token'] ?? '' ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="postmark_message_stream_id" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_postmark_message_stream_id'] ?? 'Message Stream ID' ); ?></label>
			<input id="postmark_message_stream_id" name="postmark_message_stream_id" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_postmark_message_stream_id ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_postmark_message_stream_id'] ?? '' ); ?></p>
		</div>
		</div>

		<div class="wpmsl-vertical-divider" aria-hidden="true"></div>

		<aside class="wpmsl-form-col wpmsl-form-col--toggles">
		<div class="wpmsl-section-header">
			<h3 class="wpmsl-section-subheading">
				<svg class="wpmsl-section-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
				</svg>
				<?php echo esc_html( $authority_mailer_sub_options ); ?>
			</h3>
		</div>

		<div class="wpmsl-toggle-block">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control"><input type="checkbox" id="postmark_force_from_name" name="postmark_force_from_name" value="1" <?php checked( $authority_mailer_postmark_force_name, true ); ?> /><label for="postmark_force_from_name" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label></div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_name'] ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_name'] ?? '' ); ?></p></div>
			</div>
		</div>

		<div class="wpmsl-toggle-block" class="wpmsl-section-subheading--spaced">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control"><input type="checkbox" id="postmark_force_from_email" name="postmark_force_from_email" value="1" <?php checked( $authority_mailer_postmark_force_email, true ); ?> /><label for="postmark_force_from_email" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label></div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_email'] ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_email'] ?? '' ); ?></p></div>
			</div>
		</div>
		</aside>
	</div>

	<div id="wpmsl-mailer-form-status" class="wpmsl-form-status" aria-live="polite" style="display:none;"></div>
	</form>
</div>
