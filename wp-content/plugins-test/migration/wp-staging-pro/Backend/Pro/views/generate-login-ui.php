<?php

/**
 * @see WPStaging\Pro\Auth\LoginLinkGenerator::ajaxLoginLinkUserInterface
 *
 * @var object $clone
 */
use WPStaging\Framework\Facades\Sanitize;

if (!defined("WPINC")) {
    die();
}

$cloneName = isset($_POST["clone"]) ? Sanitize::sanitizeString($_POST["clone"]) : '';

?>
<input type="hidden" id="wpstg-generate-login-link-clone-id" name="wpstg-generate-login-link-clone-id" value="<?php echo esc_attr($cloneName); ?>">
<div class="wpstg-form-horizontal">
    <div class="wpstg-form-row">
        <h3><?php esc_html_e('Generate Login Link', 'wp-staging'); ?></h3>
    </div>
    <div>
        <p>
            <?php
            echo sprintf(esc_html__("This will generate a login link for the staging site \"%s\". You can use this link to login to the staging site without having to enter your username and password. This can be useful to share quick login links with your clients or team members.", "wp-staging"), esc_html($cloneName));
            ?>
        </p>
    </div>
    <div class="wpstg-form-row">
        <label id="wpstg-generate-login-link-user-role-label">
            <?php esc_html_e("Login as", "wp-staging"); ?>

            <select name="wpstg-generate-login-link-role" id="wpstg-generate-login-link-role">
                <?php
                wp_dropdown_roles(get_option('default_role'));
                ?>
            </select>
        </label>

    </div>
    <br />
    <div>
        <label id="wpstg-generate-login-link-minutes-label" for="wpstg-generate-login-link-minutes">
            <?php esc_html_e("Login will expire in", "wp-staging"); ?>
        </label>
        <?php
        $minutes = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
        '20','21', '22', '23', '24', '25', '26', '27', '28', '29',
        '30','31', '32', '33', '34', '35', '36', '37', '38', '39',
        '40','41', '42', '43', '44', '45', '46', '47', '48', '49',
        '50','51', '52', '53', '54', '55', '56', '57', '58', '59'
        ];
        ?>
        <select name="wpstg-generate-login-link-minutes" id="wpstg-generate-login-link-minutes">
            <?php foreach ($minutes as $minute) : ?>
                <option value="<?php echo esc_attr($minute) ?>">
                    <?php echo esc_html($minute); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("min", "wp-staging"); ?>
        <?php
        $hours = [ '1', '2', '3', '4', '5', '6', '7', '8', '9',
        '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
        '20', '22', '23'
        ];
        ?>
        <select name="wpstg-generate-login-link-hours" id="wpstg-generate-login-link-hours">
            <?php foreach ($hours as $hour) : ?>
                <option value="<?php echo esc_attr($hour) ?>">
                    <?php echo esc_html($hour); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("hours", "wp-staging"); ?>
        <?php
        $days = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ];
        ?>
        <select name="wpstg-generate-login-link-days" id="wpstg-generate-login-link-days">
            <?php foreach ($days as $day) : ?>
                <option value="<?php echo esc_attr($day) ?>">
                    <?php echo esc_html($day); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php esc_html_e("days", "wp-staging"); ?>
    </div>
    <br />
    <div id="wpstg-generate-login-link-container">
        <h3 id="wpstg-generate-login-link-head"><?php echo esc_html__("Copy this link now! It will be displayed only once:", "wp-staging") ?></h3>
        <span id="wpstg-generate-login-link-generated" data-url="<?php echo esc_url($clone['url'] . '/wp-login.php?wpstg_login=', "wp-staging"); ?>"></span>
        <span style="display:none" id="wpstg-generate-login-link-copy-text" data-copy="<?php echo esc_attr('Copy!', "wp-staging"); ?>" data-copied="<?php echo esc_attr__('Copied!', "wp-staging"); ?>"></span>
    </div>
</div>

<p>
<button type="button" class="wpstg-prev-step-link wpstg-button--primary">
    <?php esc_html_e("Back", "wp-staging") ?>
</button>
<button
    type="button"
    id="wpstg-generate-login-link"
    data-alert-title="<?php esc_attr_e('Do you want to create a new login link?', 'wp-staging') ?>"
    data-alert-body="<?php echo esc_html__("This action will remove and invalidate all prior login links and create a new one. Do you want to proceed?", "wp-staging") ?>"
    data-confirm-btn-text="<?php esc_attr_e('Proceed', 'wp-staging') ?>"
    class="wpstg-button--blue"
    >
    <?php esc_html_e('Create Login Link', 'wp-staging'); ?>
</button>
</p>
