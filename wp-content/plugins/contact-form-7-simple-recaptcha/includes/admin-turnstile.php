<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (
    isset( $_POST['cf7sr_nonce'] )
    && wp_verify_nonce( $_POST['cf7sr_nonce'], 'cf7sr_update_turnstile' )
    && current_user_can( 'manage_options' )
) {
    $cf7sr_ts_key = ! empty( $_POST['cf7sr_ts_key'] ) ? sanitize_text_field( $_POST['cf7sr_ts_key'] ) : '';
    $cf7sr_ts_secret = ! empty( $_POST['cf7sr_ts_secret'] ) ? sanitize_text_field( $_POST['cf7sr_ts_secret'] ) : '';
    $cf7sr_ts_message = ! empty( $_POST['cf7sr_ts_message'] ) ? sanitize_text_field( $_POST['cf7sr_ts_message'] ) : '';
    $cf7sr_ts_language = ! empty( $_POST['cf7sr_ts_language'] ) ? sanitize_text_field( $_POST['cf7sr_ts_language'] ) : '';

    update_option( 'cf7sr_ts_key', $cf7sr_ts_key );
    update_option( 'cf7sr_ts_secret', $cf7sr_ts_secret );
    update_option( 'cf7sr_ts_message', $cf7sr_ts_message );
    update_option( 'cf7sr_ts_language', $cf7sr_ts_language );

    add_settings_error(
            'cf7sr_messages',
            'cf7sr_message',
            __( 'Settings saved successfully.', 'contact-form-7-simple-recaptcha' ),
            'updated'
    );
}

$cf7sr_ts_key      = get_option( 'cf7sr_ts_key' );
$cf7sr_ts_secret   = get_option( 'cf7sr_ts_secret' );
$cf7sr_ts_message  = get_option( 'cf7sr_ts_message' );
$cf7sr_ts_language = get_option( 'cf7sr_ts_language' );

settings_errors( 'cf7sr_messages' );
?>
<form class="cf7sr-settings" action="<?php echo esc_attr( admin_url( 'admin.php?page=cf7sr-edit&tab=turnstile' ) ); ?>" method="POST">
    <?php wp_nonce_field( 'cf7sr_update_turnstile', 'cf7sr_nonce' ); ?>

    <?php if (empty($cf7sr_ts_key) || empty($cf7sr_ts_secret)) { ?>
        <p class="cf7sr-title cf7sr-danger-msg"><?php esc_html_e( 'Cloudflare Turnstile CAPTCHA will not work unless you set up the configuration below', 'contact-form-7-simple-recaptcha' ); ?></p>
    <?php } ?>

    <div class="cf7sr-row">
        <label>Site key</label>
        <input type="text" value="<?php echo esc_attr( $cf7sr_ts_key ); ?>" name="cf7sr_ts_key">
    </div>

    <div class="cf7sr-row">
        <label>Secret key</label>
        <input type="text" value="<?php echo esc_attr( $cf7sr_ts_secret ); ?>" name="cf7sr_ts_secret">
    </div>

    <div class="cf7sr-row">
        <label><?php esc_html_e( 'Invalid captcha error message', 'contact-form-7-simple-recaptcha' ); ?></label>
        <input type="text" placeholder="<?php esc_html_e( 'Invalid captcha', 'contact-form-7-simple-recaptcha' ); ?>" value="<?php echo esc_attr( $cf7sr_ts_message ); ?>" name="cf7sr_ts_message">
    </div>

    <div class="cf7sr-row">
        <label><?php esc_html_e( 'Force CAPTCHA to render in a specific language.', 'contact-form-7-simple-recaptcha' ); ?></label>
        <select name="cf7sr_ts_language">
            <option value=""></option>
            <?php foreach ( CF7SR_LANGUAGES as $key => $label ) { ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($cf7sr_ts_language, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Settings', 'contact-form-7-simple-recaptcha' ); ?>">
</form>

<p class="cf7sr-title cf7sr-info-msg">
    <?php
    echo wp_kses(
            __( 'To add Cloudflare Turnstile CAPTCHA to Contact Form 7 form, add <strong>[cf7sr-turnstile]</strong> in your form (preferable above submit button)', 'contact-form-7-simple-recaptcha' ),
            array( 'strong' => array() )
    );
    ?>
</p>
<p class="cf7sr-title cf7sr-info-msg">
    <?php esc_html_e( 'Default size of CAPTCHA is normal, for compact or flexible use shortcode:', 'contact-form-7-simple-recaptcha' ); ?>
    <strong>[cf7sr-turnstile size="compact"] or [cf7sr-turnstile size="flexible"]</strong></p>
<p class="cf7sr-title cf7sr-info-msg">
    <?php esc_html_e( 'Default color theme of CAPTCHA is auto, for dark or light theme use shortcode:', 'contact-form-7-simple-recaptcha' ); ?>
    <strong>[cf7sr-turnstile theme="dark"] or [cf7sr-turnstile theme="light"]</strong>
</p>
<p class="cf7sr-title cf7sr-info-msg">
    <?php esc_html_e( 'Default appearance is always, for execute or interaction-only use shortcode:', 'contact-form-7-simple-recaptcha' ); ?>
    <strong>[cf7sr-turnstile appearance="execute"] or [cf7sr-turnstile appearance="interaction-only"]</strong>
</p>
<p class="cf7sr-title cf7sr-info-msg">
    <?php esc_html_e( 'You can combine multiple attributes, sample shortcode:', 'contact-form-7-simple-recaptcha' ); ?>
    <strong>[cf7sr-turnstile size="compact" theme="dark" appearance="interaction-only"]</strong>
</p>

<div class="cf7sr-generate">
    <p class="cf7sr-title cf7sr-warning-msg">
        <?php esc_html_e( 'Use this link to generate', 'contact-form-7-simple-recaptcha' ); ?>  <i>Site key</i> and <i>Secret key</i>: <a target="_blank" href="https://www.cloudflare.com/en-gb/products/turnstile/">https://www.cloudflare.com/en-gb/products/turnstile/</a><br>
    </p>
    <p>
        <a target="_blank" href="https://www.cloudflare.com/en-gb/products/turnstile/">
            <img src="<?php echo esc_url( CF7SR_PLUGIN_URL . '/assets/img/turnstile.jpg' ); ?>" width="400" alt="turnstile" />
        </a>
    </p>
</div>
