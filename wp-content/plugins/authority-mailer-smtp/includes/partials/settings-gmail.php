<?php
/**
 * Authority Mailer SMTP - Gmail Provider Settings Partial
 *
 * Partial: Google / Gmail settings
 *
 * Left: From Email / From Name then client fields.
 * Right: Gmail API help + toggles.
 *
 * Note: form uses provider key "gmail" so redirects and saved-test playback
 * use the same provider identifier used by the onboarding chooser (tile id = 'gmail').
 * The tester accepts either google_* or gmail_* keys.
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
global $AUTHORITY_MAILER_STRINGS;

$authority_mailer_option_key = Authority_Mailer_Onboarding::OPTION_KEY;
$authority_mailer_options    = get_option( $authority_mailer_option_key, array() );

/* values (keep esc_attr on echo; store raw values here) */
$authority_mailer_google_from_email       = isset( $authority_mailer_options['google_from_email'] ) ? $authority_mailer_options['google_from_email'] : get_bloginfo( 'admin_email' );
$authority_mailer_google_from_name        = isset( $authority_mailer_options['google_from_name'] ) ? $authority_mailer_options['google_from_name'] : get_bloginfo( 'name' );
$authority_mailer_google_client_id        = isset( $authority_mailer_options['google_client_id'] ) ? $authority_mailer_options['google_client_id'] : '';
$authority_mailer_google_client_secret    = isset( $authority_mailer_options['google_client_secret'] ) ? $authority_mailer_options['google_client_secret'] : '';
$authority_mailer_google_redirect_uri     = isset( $authority_mailer_options['google_redirect_uri'] ) ? $authority_mailer_options['google_redirect_uri'] : esc_url( home_url( '/wp-json/authority-mailer-smtp/google/callback' ) );
$authority_mailer_connected               = ! empty( $authority_mailer_options['google_connected'] ) ? true : false;
$authority_mailer_google_force_from_name  = isset( $authority_mailer_options['google_force_from_name'] ) ? (bool) $authority_mailer_options['google_force_from_name'] : true;
$authority_mailer_google_force_from_email = isset( $authority_mailer_options['google_force_from_email'] ) ? (bool) $authority_mailer_options['google_force_from_email'] : true;

/* UI strings (prefixed local variables) */
$authority_mailer_help_gmail_api      = authority_mailer_smtp_get_string( 'help_gmail_api' );
$authority_mailer_label_from_email    = authority_mailer_smtp_get_string( 'label_from_email' );
$authority_mailer_label_from_name     = authority_mailer_smtp_get_string( 'label_from_name' );
$authority_mailer_label_client_id     = authority_mailer_smtp_get_string( 'label_client_id' );
$authority_mailer_label_client_secret = authority_mailer_smtp_get_string( 'label_client_secret' );
$authority_mailer_label_redirect_uri  = authority_mailer_smtp_get_string( 'label_redirect_uri' );
$authority_mailer_btn_copy            = authority_mailer_smtp_get_string( 'btn_copy' );
$authority_mailer_btn_connect         = authority_mailer_smtp_get_string( 'btn_connect_google' );

/*
Provider CTA / top description (match Elastic partial layout).
 * Ensure a one-line description exists even when no string is supplied.
 */
$authority_mailer_provider_id          = 'gmail';
$authority_mailer_provider_ctas        = authority_mailer_smtp_get_string( 'provider_ctas' );
$authority_mailer_cta_label            = isset( $authority_mailer_provider_ctas[ $authority_mailer_provider_id ] ) ? $authority_mailer_provider_ctas[ $authority_mailer_provider_id ] : authority_mailer_smtp_get_string( 'provider_cta_google' );
$authority_mailer_provider_description = authority_mailer_smtp_get_string( 'provider_description_gmail' );
if ( empty( $authority_mailer_provider_description ) ) {
	$authority_mailer_provider_description = authority_mailer_smtp_get_string( 'provider_description_gmail_oauth' );
}
$authority_mailer_help_read_setup = authority_mailer_smtp_get_string( 'help_read_setup_gmail' );
$authority_mailer_provider_link   = apply_filters( 'authority_mailer_smtp_provider_link_gmail', 'https://console.cloud.google.com/apis/credentials' );
$authority_mailer_provider_docs   = apply_filters( 'authority_mailer_smtp_provider_docs_gmail', 'https://developers.google.com/identity/protocols/oauth2' );

$authority_mailer_sub_sender   = authority_mailer_smtp_get_string( 'section_subheading_sender' );
$authority_mailer_sub_options  = authority_mailer_smtp_get_string( 'section_subheading_options' );
$authority_mailer_sub_provider = sprintf( authority_mailer_smtp_get_string( 'section_subheading_provider' ), 'OAuth / Client' );

?>
<div class="wpmsl-mailer-settings wpmsl-mailer-settings--google">

	<?php if ( $authority_mailer_provider_description ) : ?>
	<div class="wpmsl-mailer-description wpmsl-mailer-description--above-cta"><?php echo wp_kses_post( wpautop( $authority_mailer_provider_description ) ); ?></div>
	<?php endif; ?>

	<div class="wpmsl-mailer-cta-row">
	<div class="wpmsl-mailer-cta-left">
		<a class="wpmsl-btn-primary wpmsl-mailer-cta" href="<?php echo esc_url( $authority_mailer_provider_link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_cta_label ); ?> &rarr;</a>
	</div>
	<div class="wpmsl-mailer-cta-right">
		<a class="wpmsl-mailer-help-link" href="<?php echo esc_url( $authority_mailer_provider_docs ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_help_read_setup ); ?></a>
	</div>
	</div>

	<form id="wpmsl-mailer-form-google" class="wpmsl-mailer-form wpmsl-mailer-form--google" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-mailer-form="gmail" novalidate>
	<?php wp_nonce_field( 'authority_mailer_smtp_save_gmail', '_authority_mailer_gmail_nonce' ); ?>
	<input type="hidden" name="action" value="authority_mailer_smtp_save_gmail" />

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
			<label for="google_from_email" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_from_email ); ?></label>
			<input id="google_from_email" name="google_from_email" class="wpmsl-form-input" type="email" value="<?php echo esc_attr( $authority_mailer_google_from_email ); ?>" />
			<div id="google_from_email-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_from_email'] ?? 'This address will be used as the From email for outgoing messages. Use the email associated with your Google account or a verified alias in Gmail.' ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="google_from_name" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_from_name ); ?></label>
			<input id="google_from_name" name="google_from_name" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_google_from_name ); ?>" />
			<div id="google_from_name-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['gmail_section_from_address_note'] ?? 'This name appears to recipients as the sender (for example, "Your Company Support"). Keep it friendly and recognizable.' ); ?></p>
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
			<label for="google_client_id" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_client_id ); ?></label>
			<input id="google_client_id" name="google_client_id" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_google_client_id ); ?>" />
			<div id="google_client_id-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_client_id'] ?? 'The OAuth Client ID from your Google Cloud Console. Paste it here so Authority Mailer can request authorization.' ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="google_client_secret" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_client_secret ); ?></label>
			<input id="google_client_secret" name="google_client_secret" class="wpmsl-form-input" type="password" value="<?php echo esc_attr( $authority_mailer_google_client_secret ); ?>" />
			<div id="google_client_secret-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_client_secret'] ?? 'The OAuth Client Secret is used to exchange the authorization code for tokens. Keep it private.' ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="google_redirect_uri" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_redirect_uri ); ?></label>
			<div style="display:flex; gap:8px; align-items:center;">
			<input id="google_redirect_uri" name="google_redirect_uri" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_google_redirect_uri ); ?>" readonly style="flex:1;" />
			<button type="button" id="copy-redirect-uri-btn" class="wpmsl-btn-secondary"><?php echo esc_html( $authority_mailer_btn_copy ); ?></button>
			</div>
			<div id="google_redirect_uri-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_redirect_uri_google'] ?? "Copy this exact URL into the 'Authorized redirect URIs' field for your OAuth Client in the Google Cloud Console." ); ?></p>
		</div>

		<div class="wpmsl-toggle-block--spaced-large">
			<?php if ( $authority_mailer_connected ) : ?>
			<div class="wpmsl-connected"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['connected_to_google'] ?? 'Connected to Google' ); ?></div>
			<?php else : ?>
			<a id="authority-mailer-google-connect" class="wpmsl-btn-primary" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_google_auth_url', '#', $authority_mailer_options ) ); ?>"><?php echo esc_html( $authority_mailer_btn_connect ); ?></a>
			<?php endif; ?>
		</div>
		</div>

		<div class="wpmsl-vertical-divider" aria-hidden="true"></div>

		<div class="wpmsl-gmail-right">
		<div class="wpmsl-section-header">
			<h3 class="wpmsl-section-subheading">
				<svg class="wpmsl-section-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
				</svg>
				<?php echo esc_html( $authority_mailer_sub_options ); ?>
			</h3>
		</div>
		<p class="wpmsl-muted"><?php echo esc_html( $authority_mailer_help_gmail_api ); ?></p>

		<div class="wpmsl-toggle-block" style="margin-top:10px;">
			<div class="wpmsl-toggle-row wpmsl-pro-toggle-trigger" role="button" tabindex="0" data-pro="1" data-pro-name="Google" aria-describedby="google-oneclick-help">
			<div class="wpmsl-toggle-control"><input type="checkbox" id="google_one_click" name="google_one_click" value="1" disabled aria-disabled="true" /><label for="google_one_click" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label></div>
			<div class="wpmsl-toggle-meta">
				<strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_one_click_toggle'] ?? '' ); ?></strong>
				<p id="google-oneclick-help" class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['one_click_setup_note'] ?? '' ); ?></p>
			</div>
			</div>
		</div>

		<div class="wpmsl-toggle-block" class="wpmsl-toggle-block--spaced-large">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control"><input type="checkbox" id="google_force_from_name" name="google_force_from_name" value="1" <?php checked( $authority_mailer_google_force_from_name, true ); ?> /><label for="google_force_from_name" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label></div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_name'] ?? '' ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_name'] ?? '' ); ?></p></div>
			</div>
		</div>

		<div class="wpmsl-toggle-block" class="wpmsl-toggle-block--spaced-large">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control"><input type="checkbox" id="google_force_from_email" name="google_force_from_email" value="1" <?php checked( $authority_mailer_google_force_from_email, true ); ?> /><label for="google_force_from_email" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label></div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_email'] ?? '' ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_email'] ?? '' ); ?></p></div>
			</div>
		</div>
		</div>
	</div>

	<div id="wpmsl-mailer-form-status-global" class="wpmsl-form-status" aria-live="polite" style="display:none"></div>
	</form>
</div>
<?php
// Enqueue the Gmail settings script properly.
$authority_mailer_gmail_settings_js_path = AUTHORITY_MAILER_PLUGIN_DIR . 'assets/js/gmail-settings.js';
if ( file_exists( $authority_mailer_gmail_settings_js_path ) ) {
	wp_enqueue_script(
		'authority-mailer-gmail-settings',
		plugins_url( 'assets/js/gmail-settings.js', AUTHORITY_MAILER_PLUGIN_FILE ),
		array( 'jquery' ),
		filemtime( $authority_mailer_gmail_settings_js_path ),
		true
	);

	// Localize script with required strings if not already available.
	// The script will first check for authorityMailerOnboarding or authorityMailerSettings,
	// which are set in the main onboarding/settings pages. This ensures strings are available
	// when the partial is loaded in other contexts.
	$authority_mailer_gmail_settings_strings = array(
		'i18n_google_client_id_required'     => authority_mailer_smtp_get_string( 'i18n_google_client_id_required' ),
		'google_oauth_client_missing_detail' => authority_mailer_smtp_get_string( 'google_oauth_client_missing_detail' ),
		'btn_copy'                           => authority_mailer_smtp_get_string( 'btn_copy' ),
		'i18n_request_failed'                => authority_mailer_smtp_get_string( 'i18n_request_failed' ),
		'license_copied_clipboard'           => authority_mailer_smtp_get_string( 'license_copied_clipboard' ),
	);
	wp_localize_script( 'authority-mailer-gmail-settings', 'authorityMailerGmailSettings', $authority_mailer_gmail_settings_strings );

	// Add inline script for copy redirect URI button.
	$authority_mailer_copy_script = "
	(function() {
		var btn = document.getElementById('copy-redirect-uri-btn');
		if (btn) {
			btn.addEventListener('click', function() {
				var input = document.getElementById('google_redirect_uri');
				if (input) {
					try {
						navigator.clipboard.writeText(input.value);
						var prevText = btn.innerText;
						var strings = window.authorityMailerGmailSettings || {};
						var copiedText = strings.license_copied_clipboard || 'Copied!';
						btn.innerText = copiedText;
						setTimeout(function() {
							btn.innerText = prevText;
						}, 1200);
					} catch (e) {
						var failedMsg = (window.authorityMailerGmailSettings && window.authorityMailerGmailSettings.i18n_request_failed) || 'Request failed';
						alert(failedMsg);
					}
				}
			});
		}
	})();
	";
	wp_add_inline_script( 'authority-mailer-gmail-settings', $authority_mailer_copy_script );
}
?>
