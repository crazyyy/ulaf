<?php
/**
 * @var string $cloneID The ID of the clone.
 * @var array  $data    An array of Clone data.
 * @var $license
 *
 * @see src/Backend/views/clone/ajax/single-overview.php:62
 */
?>
<a
    href="#"
    class="wpstg-sync-account wpstg-clone-action"
    data-clone="<?php echo esc_attr($cloneID) ?>"
    data-alert-title="<?php esc_attr_e('Confirm User Synchronization!', 'wp-staging') ?>"
    data-alert-body="<?php echo sprintf(esc_html__("This action synchronizes the current user's data with that of the staging site (%s). This will update the current user account password on the staging site or create the same user with identical username and password if it does not already exist. Do you want to continue?", "wp-staging"), esc_attr($cloneID)) ?>"
    data-confirm-btn-text="<?php esc_attr_e('Proceed', 'wp-staging') ?>"
    title="<?php echo sprintf(esc_html__("Sync the current user account with this staging site (%s). This will either add the current logged in user account to the staging site or create the same user with identical username and password if it does not exist.", "wp-staging"), esc_attr($cloneID)) ?>"
    >
    <?php esc_html_e("Sync User Account", "wp-staging"); ?>
</a>
