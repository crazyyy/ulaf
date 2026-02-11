<?php
/**
 * Authority Mailer SMTP - Elastic Email Provider Settings Partial
 *
 * Partial: Elastic Email (updated layout & canonicalized field names)
 *
 * Normalizes field names to use the provider-canonical "elasticmail_" prefix so
 * the tester and saved options are consistent. Also emits hidden fallback values
 * for checkboxes so unchecked toggles are submitted as "0".
 *
 * @package Authority_Mailer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;
global $AUTHORITY_MAILER_STRINGS;

/*
 * Local variables in this template MUST be prefixed to avoid PHPCS/WPVIP
 * complaints about unprefixed globals. Use the authority_mailer_ prefix for clarity.
 */

/* option key and stored options */
$authority_mailer_option_key = Authority_Mailer_Onboarding::OPTION_KEY;
$authority_mailer_options    = get_option( $authority_mailer_option_key, array() );
$authority_mailer_options    = is_array( $authority_mailer_options ) ? $authority_mailer_options : array();

/* provider display values */
$authority_mailer_provider_id    = 'elastic';
$authority_mailer_provider_label = isset( $AUTHORITY_MAILER_STRINGS['provider_ctas'][ $authority_mailer_provider_id ] )
	? preg_replace( '/^Get Started with\s+/i', '', $AUTHORITY_MAILER_STRINGS['provider_ctas'][ $authority_mailer_provider_id ] )
	: 'Elastic Email';

/* From name/email: prefer elasticmail_ variant, fall back to elastic_ and then site defaults */
$authority_mailer_elastic_from_name = '';
if ( ! empty( $authority_mailer_options['elasticmail_from_name'] ) ) {
	$authority_mailer_elastic_from_name = $authority_mailer_options['elasticmail_from_name'];
} elseif ( ! empty( $authority_mailer_options['elastic_from_name'] ) ) {
	$authority_mailer_elastic_from_name = $authority_mailer_options['elastic_from_name'];
} else {
	$authority_mailer_elastic_from_name = get_bloginfo( 'name' );
}

$authority_mailer_elastic_from_email = '';
if ( ! empty( $authority_mailer_options['elasticmail_from_email'] ) ) {
	$authority_mailer_elastic_from_email = $authority_mailer_options['elasticmail_from_email'];
} elseif ( ! empty( $authority_mailer_options['elastic_from_email'] ) ) {
	$authority_mailer_elastic_from_email = $authority_mailer_options['elastic_from_email'];
} else {
	$authority_mailer_elastic_from_email = get_option( 'admin_email' );
}

/* Force toggles: accept both variants and normalize to boolean */
$authority_mailer_elastic_force_name = true; // Default to enabled for new configs
if ( isset( $authority_mailer_options['elasticmail_force_from_name'] ) ) {
	$authority_mailer_elastic_force_name = (bool) $authority_mailer_options['elasticmail_force_from_name'];
} elseif ( isset( $authority_mailer_options['elastic_force_from_name'] ) ) {
	$authority_mailer_elastic_force_name = (bool) $authority_mailer_options['elastic_force_from_name'];
}

$authority_mailer_elastic_force_email = true; // Default to enabled for new configs
if ( isset( $authority_mailer_options['elasticmail_force_from_email'] ) ) {
	$authority_mailer_elastic_force_email = (bool) $authority_mailer_options['elasticmail_force_from_email'];
} elseif ( isset( $authority_mailer_options['elastic_force_from_email'] ) ) {
	$authority_mailer_elastic_force_email = (bool) $authority_mailer_options['elastic_force_from_email'];
}

/* API key: prefer canonical elasticmail_api_key, then elastic_api_key */
$authority_mailer_elastic_api_key = '';
if ( ! empty( $authority_mailer_options['elasticmail_api_key'] ) ) {
	$authority_mailer_elastic_api_key = $authority_mailer_options['elasticmail_api_key'];
} elseif ( ! empty( $authority_mailer_options['elastic_api_key'] ) ) {
	$authority_mailer_elastic_api_key = $authority_mailer_options['elastic_api_key'];
}

/* UI strings (prefixed local variables) */
$authority_mailer_cta_label            = authority_mailer_smtp_get_string( 'provider_ctas' )[ $authority_mailer_provider_id ] ?? '';
$authority_mailer_provider_description = authority_mailer_smtp_get_string( 'provider_description_elastic' );
$authority_mailer_help_read_setup      = authority_mailer_smtp_get_string( 'help_read_setup_elastic' );

$authority_mailer_sub_sender   = authority_mailer_smtp_get_string( 'section_subheading_sender' );
$authority_mailer_sub_options  = authority_mailer_smtp_get_string( 'section_subheading_options' );
$authority_mailer_sub_provider = sprintf( authority_mailer_smtp_get_string( 'section_subheading_provider' ), $authority_mailer_provider_label );

$authority_mailer_label_from_name  = authority_mailer_smtp_get_string( 'label_from_name' );
$authority_mailer_label_from_email = authority_mailer_smtp_get_string( 'label_from_email' );
$authority_mailer_label_api_key    = authority_mailer_smtp_get_string( 'label_api_key' );
?>
<div class="wpmsl-mailer-settings wpmsl-mailer-settings--elastic">

	<?php if ( $authority_mailer_provider_description ) : ?>
	<div class="wpmsl-mailer-description wpmsl-mailer-description--above-cta"><?php echo wp_kses_post( wpautop( $authority_mailer_provider_description ) ); ?></div>
	<?php endif; ?>

	<div class="wpmsl-mailer-cta-row">
	<div class="wpmsl-mailer-cta-left">
		<a class="wpmsl-btn-primary wpmsl-mailer-cta" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_link_elastic', 'https://elasticemail.com/' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_cta_label ); ?> &rarr;</a>
	</div>
	<div class="wpmsl-mailer-cta-right">
		<a class="wpmsl-mailer-help-link" href="<?php echo esc_url( apply_filters( 'authority_mailer_smtp_provider_docs_elastic', 'https://elasticemail.com/support/' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $authority_mailer_help_read_setup ); ?></a>
	</div>
	</div>

	<form id="wpmsl-mailer-form-elastic" class="wpmsl-mailer-form wpmsl-mailer-form--elastic" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-mailer-form="elastic" novalidate>
	<?php wp_nonce_field( 'authority_mailer_smtp_save_elastic', '_authority_mailer_elastic_nonce' ); ?>
	<input type="hidden" name="action" value="authority_mailer_smtp_save_elastic" />

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
			<label for="elasticmail_from_name" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_from_name ); ?></label>
			<input id="elasticmail_from_name" name="elasticmail_from_name" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_elastic_from_name ); ?>" />
			<div id="elasticmail_from_name-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_from_name_desc' ) ); ?></p>
		</div>

		<div class="wpmsl-form-row">
			<label for="elasticmail_from_email" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_from_email ); ?></label>
			<input id="elasticmail_from_email" name="elasticmail_from_email" class="wpmsl-form-input" type="email" value="<?php echo esc_attr( $authority_mailer_elastic_from_email ); ?>" />
			<div id="elasticmail_from_email-error" class="wpmsl-input-error" style="display:none;"></div>
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
			<label for="elasticmail_api_key" class="wpmsl-form-label"><?php echo esc_html( $authority_mailer_label_api_key ); ?></label>
			<input id="elasticmail_api_key" name="elasticmail_api_key" class="wpmsl-form-input" type="text" value="<?php echo esc_attr( $authority_mailer_elastic_api_key ); ?>" />
			<div id="elasticmail_api_key-error" class="wpmsl-input-error" style="display:none;"></div>
			<p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_elastic_api_key_desc' ) ); ?></p>
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
				<!-- Hidden fallback so unchecked state submits 0 -->
				<input type="hidden" name="elasticmail_force_from_name" value="0" />
				<input type="checkbox" id="elasticmail_force_from_name" name="elasticmail_force_from_name" value="1" <?php checked( $authority_mailer_elastic_force_name, true ); ?> />
				<label for="elasticmail_force_from_name" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label>
			</div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( isset( $AUTHORITY_MAILER_STRINGS['label_force_from_name'] ) ? $AUTHORITY_MAILER_STRINGS['label_force_from_name'] : '' ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_force_from_name' ) ); ?></p></div>
			</div>
		</div>

		<div class="wpmsl-toggle-block" class="wpmsl-toggle-block--spaced-large">
			<div class="wpmsl-toggle-row">
			<div class="wpmsl-toggle-control">
				<!-- Hidden fallback so unchecked state submits 0 -->
				<input type="hidden" name="elasticmail_force_from_email" value="0" />
				<input type="checkbox" id="elasticmail_force_from_email" name="elasticmail_force_from_email" value="1" <?php checked( $authority_mailer_elastic_force_email, true ); ?> />
				<label for="elasticmail_force_from_email" class="wpmsl-switch-label"><span class="wpmsl-switch-knob"></span></label>
			</div>
			<div class="wpmsl-toggle-meta"><strong class="wpmsl-toggle-title"><?php echo esc_html( isset( $AUTHORITY_MAILER_STRINGS['label_force_from_email'] ) ? $AUTHORITY_MAILER_STRINGS['label_force_from_email'] : '' ); ?></strong><p class="wpmsl-form-help"><?php echo esc_html( authority_mailer_smtp_get_string( 'help_force_from_email' ) ); ?></p></div>
			</div>
		</div>
		</aside>
	</div>

	<div id="wpmsl-mailer-form-status" class="wpmsl-form-status" aria-live="polite" style="display:none;"></div>
	</form>
</div>
