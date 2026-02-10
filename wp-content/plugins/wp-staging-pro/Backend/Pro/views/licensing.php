<?php

/**
 * @see WPStaging\Backend\Administrator::getLicensePage
 *
 * @var object $license
 */

$message = '';
?>
<div class="wpstg_admin" id="wpstg-clonepage-wrapper">
    <?php

    require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/header.php');

    $isActiveLicensePage = true;
    require_once(WPSTG_PLUGIN_DIR . 'Backend/views/_main/main-navigation.php');
    ?>

    <div class="wpstg-metabox-holder" style="display:block;">
        <form method="post" action="#">
            <?php if (isset($license->license) && $license->license === 'valid') : ?>
                <h3 style="margin-top: 0;"><?php echo esc_html__('WP Staging Pro is activated.', 'wp-staging'); ?></h3>
                <input type="hidden" name="wpstg_deactivate_license" value="1">
                <input type="submit" class="wpstg-border-thin-button wpstg-button" style="margin-bottom:20px;" value="<?php esc_attr_e('Deactivate License', 'wp-staging'); ?>">
                <?php
                $customerName  = !empty($license->customer_name) ? $license->customer_name : '[unknown name]';
                $customerEmail = !empty($license->customer_email) ? $license->customer_email : '[unknown email address]';

                $message = '<div class="wpstg-license-active-message">';
                $message .= __('This license is active until ', 'wp-staging') . date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp')));
                $message .= '<br>';
                $message .= __(' Registered to ', 'wp-staging') . esc_html($customerName) . ' (' . esc_html($customerEmail) . ')';
                $message .= '<br><a href="http://wp-staging.com/your-account" id="wpstg--button--manage--license" class="wpstg-button--blue" target="_blank">' . __("Manage Your License in Your Account", "wp-staging") . '</a>';
                $message .= '</div>';
                ?>
            <?php else : ?>
                <?php echo sprintf(esc_html__('Enter your license key to activate WP STAGING | PRO. %s You can buy a license key on %s.', 'wp-staging'), '<br>', '<a href="https://wp-staging.com?utm_source=wpstg-license-ui&utm_medium=website&utm_campaign=enter-license-key&utm_id=purchase-key&utm_content=wpstaging" target="_blank">wp-staging.com</a>'); ?></label>
                <div style="display:flex;align-items:center;padding-top:20px;">
                    <label for="wpstg_license_key"></label><input type="text" name="wpstg_license_key" style="" id="wpstg_input_field_license_key" placeholder="<?php esc_attr_e('Please enter your license key', 'wp-staging');?>" value='<?php echo esc_attr(get_option('wpstg_license_key', '')); ?>'>
                    <input type="hidden" name="wpstg_activate_license" value="1">
                    <input type="submit" class="wpstg-button wpstg-blue-primary" style="margin-left: 10px;" value="<?php esc_attr_e('Activate License', 'wp-staging'); ?>">
                </div>
            <?php endif; ?>
            <?php
            if (isset($license->error) && $license->error === 'expired') {
                $message = '<span class="wpstg--red">' . __('Your license expired on ', 'wp-staging') . date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp'))) . '</span>';
            }
            wp_nonce_field('wpstg_license_nonce', 'wpstg_license_nonce');
            ?>
        </form>
        <?php echo '<div style="padding-top:0px;padding-bottom:10px;">' . wp_kses_post($message) . '</div>'; ?>
    </div>
</div>
<div class="wpstg-footer-logo" style="">
    <a href="https://wp-staging.com/tell-me-more/"><img src="<?php echo esc_url($this->assets->getAssetsUrl("img/logo.svg")) ?>" width="140" alt="WP Staging"></a>
</div>
