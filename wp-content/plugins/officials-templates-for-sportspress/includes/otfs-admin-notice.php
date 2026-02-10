<?php
/**
 * Admin notice to promote premium plugin
 */

/**
 * Display a non-intrusive admin notice to promote the premium plugin, only if it hasn't been dismissed.
 */
function otfs_premium_plugin_admin_notice() {
    // Check if the user has the capability to manage options (Admin users only) and if the notice is dismissed.
    $dismissed = get_option( 'otfs_dismissed_notice' );

    if ( current_user_can( 'manage_options' ) && !$dismissed ) {
        ?>
        <div class="notice notice-info is-dismissible otfs-premium-notice">
            <p>
                <?php esc_html_e( 'Want to display detailed reports of match officials on your site? Check out our Officials Report plugin, which allows you to create and showcase comprehensive officials’ reports on the frontend with ease, supporting both block and shortcode modes.', 'officials-templates-for-sportspress' ); ?>
            </p>
            <p>
                <a href="https://savvasha.com/officials-report-for-sportspress/" target="_blank" class="button button-primary">
                    <?php esc_html_e( 'Learn more about Officials Report for SportsPress', 'officials-templates-for-sportspress' ); ?>
                </a>
            </p>
        </div>
        <script type="text/javascript">
        (function($) {
            $(document).on('click', '.otfs-premium-notice .notice-dismiss', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'otfs_dismiss_premium_notice'
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}
add_action( 'admin_notices', 'otfs_premium_plugin_admin_notice' );

/**
 * Handle the dismissal of the admin notice.
 */
function otfs_dismiss_premium_notice() {
    // Set the option in the database to indicate the notice has been dismissed.
    update_option( 'otfs_dismissed_notice', 1 );
    wp_die(); // This is required to end the AJAX request.
}
add_action( 'wp_ajax_otfs_dismiss_premium_notice', 'otfs_dismiss_premium_notice' );

/**
 * Check plugin version and reset the notice dismissal on plugin update.
 */
function otfs_check_plugin_version() {
    $current_version = OTFS_VERSION; // Use the constant defined in the main file.
    $saved_version = get_option( 'otfs_plugin_version' );

    // If versions don't match, reset the dismissed notice and update the saved version.
    if ( $current_version !== $saved_version ) {
        update_option( 'otfs_plugin_version', $current_version );
        delete_option( 'otfs_dismissed_notice' ); // Reset the dismissal flag.
    }
}
add_action( 'admin_init', 'otfs_check_plugin_version' );