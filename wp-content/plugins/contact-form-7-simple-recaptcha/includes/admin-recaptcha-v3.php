<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (
    isset( $_POST['cf7sr_nonce'] )
    && wp_verify_nonce( $_POST['cf7sr_nonce'], 'cf7sr_update_recaptcha_v3' )
    && current_user_can( 'manage_options' )
) {
    $cf7sr_key_v3 = ! empty( $_POST['cf7sr_key_v3'] ) ? sanitize_text_field( $_POST['cf7sr_key_v3'] ) : '';
    $cf7sr_secret_v3 = ! empty( $_POST['cf7sr_secret_v3'] ) ? sanitize_text_field( $_POST['cf7sr_secret_v3'] ) : '';
    $cf7sr_score_v3 = ! empty( $_POST['cf7sr_score_v3'] ) ? floatval( $_POST['cf7sr_score_v3'] ) : 0.5;
    $cf7sr_message_v3 = ! empty( $_POST['cf7sr_message_v3'] ) ? sanitize_text_field( $_POST['cf7sr_message_v3'] ) : '';

    update_option( 'cf7sr_key_v3', $cf7sr_key_v3 );
    update_option( 'cf7sr_secret_v3', $cf7sr_secret_v3 );
    update_option( 'cf7sr_score_v3', $cf7sr_score_v3 );
    update_option( 'cf7sr_message_v3', $cf7sr_message_v3 );

    add_settings_error(
        'cf7sr_messages',
        'cf7sr_message',
        __( 'Settings saved successfully.', 'contact-form-7-simple-recaptcha' ),
        'updated'
    );
}

$cf7sr_key_v3      = get_option( 'cf7sr_key_v3' );
$cf7sr_secret_v3   = get_option( 'cf7sr_secret_v3' );
$cf7sr_score_v3 = get_option( 'cf7sr_score_v3' );
$cf7sr_message_v3  = get_option( 'cf7sr_message_v3' );

settings_errors( 'cf7sr_messages' );
?>
<form class="cf7sr-settings" action="<?php echo esc_attr( admin_url( 'admin.php?page=cf7sr-edit&tab=recaptcha-v3' ) ); ?>" method="POST">
    <?php wp_nonce_field( 'cf7sr_update_recaptcha_v3', 'cf7sr_nonce' ); ?>

    <?php if (empty($cf7sr_key_v3) || empty($cf7sr_secret_v3)) { ?>
        <p class="cf7sr-title cf7sr-danger-msg"><?php esc_html_e( 'reCaptcha v3 will not work unless you set up the configuration below', 'contact-form-7-simple-recaptcha' ); ?></p>
    <?php } ?>

    <div class="cf7sr-row">
        <label>Site key</label>
        <input type="text" value="<?php echo esc_attr( $cf7sr_key_v3 ); ?>" name="cf7sr_key_v3">
    </div>

    <div class="cf7sr-row">
        <label>Secret key</label>
        <input type="text" value="<?php echo esc_attr( $cf7sr_secret_v3 ); ?>" name="cf7sr_secret_v3">
    </div>

    <div class="cf7sr-row">
        <label><?php esc_html_e('0.0 is very likely a bot, 1.0 is very likely a human. Recommended: 0.5', 'contact-form-7-simple-recaptcha'); ?></label>
        <select name="cf7sr_score_v3">
            <?php for ($i = 0.1; $i <= 1.0; $i += 0.1) : $val = number_format($i, 1); ?>
                <option value="<?php echo $val; ?>" <?php selected($cf7sr_score_v3, $val); ?>><?php echo $val; ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="cf7sr-row">
        <label><?php esc_html_e( 'Invalid captcha error message', 'contact-form-7-simple-recaptcha' ); ?></label>
        <input type="text" placeholder="<?php esc_html_e( 'Invalid captcha', 'contact-form-7-simple-recaptcha' ); ?>" value="<?php echo esc_attr( $cf7sr_message_v3 ); ?>" name="cf7sr_message_v3">
    </div>

    <input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Settings', 'contact-form-7-simple-recaptcha' ); ?>">
</form>

<p class="cf7sr-title cf7sr-info-msg">
    <?php
    echo wp_kses(
        __( 'To add reCaptcha V3 to Contact Form 7 form, add <strong>[cf7sr-v3-recaptcha]</strong> in your form.', 'contact-form-7-simple-recaptcha' ),
        array( 'strong' => array() )
    );
    ?>
</p>

<div class="cf7sr-generate">
    <p class="cf7sr-title cf7sr-warning-msg">
        <?php esc_html_e( 'Use this link to generate', 'contact-form-7-simple-recaptcha' ); ?>  <i>Site key</i> and <i>Secret key</i>: <a target="_blank" href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a><br>
        <?php esc_html_e( 'Choose:', 'contact-form-7-simple-recaptcha' ); ?> Score-based (v3)
    </p>
    <p><a target="_blank" href="https://www.google.com/recaptcha/admin">
            <img src="<?php echo esc_url( CF7SR_PLUGIN_URL . '/assets/img/recaptcha-v3.jpg' ); ?>" width="400" alt="captcha" />
        </a>
    </p>
</div>
