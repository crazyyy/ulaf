<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<p class="cf7sr-title cf7sr-info-msg">
    <?php
    echo wp_kses(
        __( 'To receive sender details in your email, you must add <strong>[cf7sr-insights]</strong> directly into the "Mail" tab of your Contact Form 7 settings.', 'contact-form-7-simple-recaptcha' ),
        array( 'strong' => array() )
    );
    ?>
</p>

<p class="cf7sr-title cf7sr-info-msg">
    <?php
    echo wp_kses(
        __( 'How to find it: Go to <strong>Contact</strong> > <strong>Contact Forms</strong>, edit your form, and click on the <strong>"Mail"</strong> tab. Paste the tag <strong>[cf7sr-insights]</strong> inside the <strong>"Message Body"</strong> field.', 'contact-form-7-simple-recaptcha' ),
        array( 'strong' => array() )
    );
    ?>
</p>

<p class="cf7sr-title cf7sr-info-msg">
    <?php
    echo wp_kses(
        __( 'Important: This tag will <strong>NOT</strong> work if added to the "Form" tab. It is only for the email message you receive.', 'contact-form-7-simple-recaptcha' ),
        array( 'strong' => array() )
    );
    ?>
</p>

<p class="cf7sr-title cf7sr-info-msg">
    <?php esc_html_e( 'The tag will be replaced in your email with: IP Address, Browser, and Operating System of the sender.', 'contact-form-7-simple-recaptcha' ); ?>
</p>

<p class="cf7sr-title cf7sr-warning-msg">
    <strong><?php esc_html_e( 'GDPR:', 'contact-form-7-simple-recaptcha' ); ?></strong>
    <?php esc_html_e( 'By using this tag, you are collecting User IP and Browser data. Make sure to update your Privacy Policy.', 'contact-form-7-simple-recaptcha' ); ?>
</p>
