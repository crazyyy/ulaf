<?php
/**
 * Authority Mailer SMTP - Other/Custom SMTP Provider Settings Partial
 *
 * Partial: Other/Custom SMTP provider settings
 *
 * Ensures fields are stored under the canonical authority_mailer_options option and also
 * saved into a nested ['other'] group for backwards compatibility with the
 * provider tester (authority_mailer_test_other).
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* Pull in localized strings (kept as-is because this global is shared) */
global $AUTHORITY_MAILER_STRINGS;

/* canonical option used by providers */
$authority_mailer_options = get_option( 'authority_mailer_smtp_options', array() );
$authority_mailer_options = is_array( $authority_mailer_options ) ? $authority_mailer_options : array();

/* nested provider group fallback */
$authority_mailer_nested = isset( $authority_mailer_options['other'] ) && is_array( $authority_mailer_options['other'] ) ? $authority_mailer_options['other'] : array();

/* read values (top-level or nested fallback) */
$authority_mailer_other_from_name  = isset( $authority_mailer_options['other_from_name'] ) ? $authority_mailer_options['other_from_name'] : ( isset( $authority_mailer_nested['other_from_name'] ) ? $authority_mailer_nested['other_from_name'] : get_bloginfo( 'name' ) );
$authority_mailer_other_from_email = isset( $authority_mailer_options['other_from_email'] ) ? $authority_mailer_options['other_from_email'] : ( isset( $authority_mailer_nested['other_from_email'] ) ? $authority_mailer_nested['other_from_email'] : get_option( 'admin_email' ) );

$authority_mailer_other_smtp_host     = isset( $authority_mailer_options['other_smtp_host'] ) ? $authority_mailer_options['other_smtp_host'] : ( isset( $authority_mailer_nested['other_smtp_host'] ) ? $authority_mailer_nested['other_smtp_host'] : '' );
$authority_mailer_other_smtp_port     = isset( $authority_mailer_options['other_smtp_port'] ) ? $authority_mailer_options['other_smtp_port'] : ( isset( $authority_mailer_nested['other_smtp_port'] ) ? $authority_mailer_nested['other_smtp_port'] : '587' );
$authority_mailer_other_encryption    = isset( $authority_mailer_options['other_smtp_encryption'] ) ? $authority_mailer_options['other_smtp_encryption'] : ( isset( $authority_mailer_nested['other_smtp_encryption'] ) ? $authority_mailer_nested['other_smtp_encryption'] : 'tls' );
$authority_mailer_other_smtp_username = isset( $authority_mailer_options['other_smtp_username'] ) ? $authority_mailer_options['other_smtp_username'] : ( isset( $authority_mailer_nested['other_smtp_username'] ) ? $authority_mailer_nested['other_smtp_username'] : '' );
$authority_mailer_other_smtp_password = isset( $authority_mailer_options['other_smtp_password'] ) ? $authority_mailer_options['other_smtp_password'] : ( isset( $authority_mailer_nested['other_smtp_password'] ) ? $authority_mailer_nested['other_smtp_password'] : '' );
$authority_mailer_other_force_name    = isset( $authority_mailer_options['other_force_from_name'] ) ? (bool) $authority_mailer_options['other_force_from_name'] : ( isset( $authority_mailer_nested['other_force_from_name'] ) ? (bool) $authority_mailer_nested['other_force_from_name'] : true );
$authority_mailer_other_force_email   = isset( $authority_mailer_options['other_force_from_email'] ) ? (bool) $authority_mailer_options['other_force_from_email'] : ( isset( $authority_mailer_nested['other_force_from_email'] ) ? (bool) $authority_mailer_nested['other_force_from_email'] : true );
$authority_mailer_other_smtp_auth     = isset( $authority_mailer_options['other_smtp_auth'] ) ? (bool) $authority_mailer_options['other_smtp_auth'] : ( isset( $authority_mailer_nested['other_smtp_auth'] ) ? (bool) $authority_mailer_nested['other_smtp_auth'] : true );

/* UI strings (prefixed local variables) */
$authority_mailer_cta_label            = authority_mailer_smtp_get_string( 'provider_ctas' )['other'] ?? '';
$authority_mailer_provider_description = authority_mailer_smtp_get_string( 'provider_description_other' );
$authority_mailer_help_read_setup      = authority_mailer_smtp_get_string( 'help_read_setup_other' );

$authority_mailer_sub_sender   = authority_mailer_smtp_get_string( 'section_subheading_sender' );
$authority_mailer_sub_options  = authority_mailer_smtp_get_string( 'section_subheading_options' );
$authority_mailer_sub_provider = sprintf( authority_mailer_smtp_get_string( 'section_subheading_provider' ), 'SMTP' );
?>
<div class="wpmsl-mailer-settings wpmsl-mailer-settings--other" data-mailer="other">

	<?php if ( $authority_mailer_provider_description ) : ?>
	<div class="wpmsl-mailer-description wpmsl-mailer-description--above-cta"><?php echo wp_kses_post( wpautop( $authority_mailer_provider_description ) ); ?></div>
	<?php endif; ?>

	<div class="wpmsl-mailer-cta-row">
	<div><a class="wpmsl-btn-primary wpmsl-mailer-cta" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_link_other', '#' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_cta_label ); ?> &rarr;</a></div>
	<div><a class="wpmsl-mailer-help-link" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_docs_other', '#' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_help_read_setup ); ?></a></div>
	</div>

	<form id="wpmsl-mailer-form-other" class="wpmsl-mailer-form wpmsl-mailer-form--other" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-mailer-form="other" novalidate>
	<?php wp_nonce_field( 'authority_mailer_smtp_save_other', '_authority_mailer_other_nonce' ); ?>
	<input type="hidden" name="action" value="authority_mailer_smtp_save_other" />

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
			<label for="other_from_name" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_from_name'] ); ?></label>
			<input id="other_from_name" name="other_from_name" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_other_from_name ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_from_name_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="other_from_email" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_from_email'] ); ?></label>
			<input id="other_from_email" name="other_from_email" class="wpmsl-form-input" type="email" value="<?php echo esc_attr( $authority_mailer_other_from_email ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_from_email_desc' ) ); ?></p>
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
			<label for="other_smtp_host" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_smtp_host'] ?? 'SMTP Host' ); ?></label>
			<input id="other_smtp_host" name="other_smtp_host" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_other_smtp_host ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_smtp_host_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="other_smtp_port" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_port'] ?? 'Port' ); ?></label>
			<input id="other_smtp_port" name="other_smtp_port" class="wpmsl-form-input" type="number" min="1" max="65535" value="<?php echo esc_attr( $authority_mailer_other_smtp_port ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_smtp_port_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="other_smtp_encryption" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_encryption'] ?? 'Encryption' ); ?></label>
			<select id="other_smtp_encryption" name="other_smtp_encryption" class="wpmsl-form-input">
				<option value="none" <?php selected( $authority_mailer_other_encryption, 'none' ); ?>><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['settings_encryption_none'] ?? 'None' ); ?></option>
				<option value="ssl" <?php selected( $authority_mailer_other_encryption, 'ssl' ); ?>><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['settings_encryption_ssl'] ?? 'SSL' ); ?></option>
				<option value="tls" <?php selected( $authority_mailer_other_encryption, 'tls' ); ?>><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['settings_encryption_tls'] ?? 'TLS' ); ?></option>
			</select>
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_smtp_encryption_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="other_smtp_username" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_username'] ?? 'Username' ); ?></label>
			<input id="other_smtp_username" name="other_smtp_username" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_other_smtp_username ); ?>" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_smtp_username_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="other_smtp_password" class="wpmsl-form-label"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_password'] ?? 'Password' ); ?></label>
			<input id="other_smtp_password" name="other_smtp_password" class="wpmsl-form-input" type="password" value="<?php echo esc_attr( $authority_mailer_other_smtp_password ); ?>" autocomplete="new-password" />
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_smtp_password_desc' ) ); ?></p>
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
			<div class="wpmsl-toggle-control">
				<input type="checkbox" id="other_force_from_name" name="other_force_from_name" value="1" <?php checked( $authority_mailer_other_force_name, true ); ?> />
				<label for="other_force_from_name" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label>
			</div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_name'] ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_name'] ?? '' ); ?></p></div>
			</div>
		</div>

		<div class="wpmsl-toggle-block wpmsl-section-subheading--spaced">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control">
				<input type="checkbox" id="other_force_from_email" name="other_force_from_email" value="1" <?php checked( $authority_mailer_other_force_email, true ); ?> />
				<label for="other_force_from_email" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label>
			</div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_force_from_email'] ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_from_email'] ?? '' ); ?></p></div>
			</div>
		</div>

		<div class="wpmsl-toggle-block wpmsl-section-subheading--spaced">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control">
				<input type="checkbox" id="other_smtp_auth" name="other_smtp_auth" value="1" <?php checked( $authority_mailer_other_smtp_auth, true ); ?> />
				<label for="other_smtp_auth" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label>
			</div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['label_smtp_auth'] ?? 'Enable Authentication' ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( $AUTHORITY_MAILER_STRINGS['help_force_authentication'] ?? '' ); ?></p></div>
			</div>
		</div>
		</aside>
	</div>

	<div id="wpmsl-mailer-form-status" class="wpmsl-form-status" aria-live="polite" style="display:none;"></div>
	</form>
</div>
