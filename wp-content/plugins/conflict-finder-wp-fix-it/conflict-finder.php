<?php
/*

Plugin Name: Conflict Finder - WP Fix It

Description: Conflict Finder is a powerful, admin-only troubleshooting toolkit designed to safely diagnose WordPress issues caused by plugin conflicts, theme conflicts, debugging configuration, or email delivery problems.

With a single click, you can temporarily disable all active plugins, switch themes, enable and manage WP_DEBUG options, inspect error logs, and test outbound email all without permanently changing your site’s configuration.

Conflict Finder automatically saves your original setup and allows you to restore everything instantly once troubleshooting is complete, making it safe to use on live sites.

Version: 7.2

Author: WP Fix It

Author URI: https://www.wpfixit.com

Text Domain: conflict-finder-wp-fix-it

Requires PHP: 5.6

Requires at least: 4.9

License: GPLv2 or later

License URI: https://www.gnu.org/licenses/gpl-2.0.html

*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CONFLICT_FINDER_CONFLICT_PLUGIN', 'support-wpfi/support-wpfi.php' );

// Block activation if Support WPFI is active
register_activation_hook( __FILE__, function () {

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    if ( is_plugin_active( CONFLICT_FINDER_CONFLICT_PLUGIN ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );

wp_die(
    '<div style="max-width:680px;"><h2 style="margin-top:0;">❌ Conflict Finder Plugin Activation Blocked</h2>

    <p>
        The <strong>Conflict Finder</strong> plugin cannot be activated because the
        <strong>WP Fix It Support</strong> plugin is currently active.
    </p>

    <p>
        The troubleshooting and conflict-detection features provided by
        <strong>Conflict Finder</strong> are already fully included inside the
        <strong>WP Fix It Support</strong> plugin. Running both plugins at the same time
        would be redundant and could cause unexpected behavior. No action is needed. All Conflict Finder features are already available.
    </p></div>',
    'Plugin Conflict Detected',
    [
        'back_link' => true,
    ]
);

    }
});

// Auto-deactivate Conflict Finder if Support WPFI is activated later
add_action( 'activated_plugin', function ( $plugin ) {

    if ( $plugin !== CONFLICT_FINDER_CONFLICT_PLUGIN ) {
        return;
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $self = plugin_basename( __FILE__ );

    if ( is_plugin_active( $self ) ) {
        deactivate_plugins( $self );

        add_action( 'admin_notices', function () {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>Conflict Finder was deactivated.</strong><br>
                    It cannot run at the same time as <strong>Support WPFI</strong>.
                </p>
            </div>
            <?php
        } );
    }

}, 10, 1 );

//Add "Troubleshoot" link on Plugins page
add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    function ( $links ) {

        if ( current_user_can( 'manage_options' ) ) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( admin_url( 'tools.php?page=wpfi-troubleshooting-tools' ) ),
                esc_html__( 'Troubleshoot', 'conflict-finder-wp-fix-it' )
            );
        }

        return $links;
    }
);

class conflict_finder_wp_debug_toggle_tool {

    private $marker_start = "/* WP DEBUG OPTIONS START */";
    private $marker_end   = "/* WP DEBUG OPTIONS END */";

    private $option_name  = 'conflict_finder_debug_toggle_settings';
    private $menu_slug    = 'wpfi-troubleshooting-tools';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'conflict_finder_add_admin_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'conflict_finder_enqueue_assets' ] );
        add_action( 'admin_post_conflict_finder_save_wp_debug_settings', [ $this, 'conflict_finder_save_settings' ] );
        add_action( 'admin_post_conflict_finder_deactivate_plugins', [ $this, 'conflict_finder_deactivate_plugins' ] );
        add_action( 'admin_post_conflict_finder_reactivate_plugins', [ $this, 'conflict_finder_reactivate_plugins' ] );
        add_action( 'admin_post_conflict_finder_activate_single_plugin', [ $this, 'conflict_finder_activate_single_plugin' ] );
        add_action( 'admin_post_conflict_finder_activate_single_theme', [ $this, 'conflict_finder_activate_single_theme' ] );
        add_action( 'admin_post_conflict_finder_restore_saved_theme', [ $this, 'conflict_finder_restore_saved_theme' ] );
        add_action( 'wp_ajax_conflict_finder_get_debug_log', [ $this, 'conflict_finder_ajax_get_debug_log' ] );
        add_action( 'admin_post_conflict_finder_download_debug_log', [ $this, 'conflict_finder_download_debug_log' ] );
        add_action( 'wp_ajax_conflict_finder_clear_debug_log', [ $this, 'conflict_finder_ajax_clear_debug_log' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'conflict_finder_enqueue_global_admin_css' ] );
        add_action( 'admin_notices', [ $this, 'conflict_finder_global_wp_debug_notice' ] );
        add_action( 'admin_notices', [ $this, 'conflict_finder_plugin_conflict_notice' ] );
        add_action( 'admin_notices', [ $this, 'conflict_finder_theme_conflict_notice' ] );
        add_action( 'admin_post_conflict_finder_send_test_email', [ $this, 'conflict_finder_send_test_email' ] );
    }

public function conflict_finder_send_test_email() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_email_test' );

    $email = sanitize_email( $_POST['test_email'] ?? '' );

    if ( ! is_email( $email ) ) {
        wp_safe_redirect(
            admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=email&email_result=fail#wpfi-tab-email-tool' )
        );
        exit;
    }

   // =========================
// Match handler-settings.php email behavior
// (HTML + From Name only; no forced From email; no phpmailer_init)
// =========================
$html_content_type_filter = function() {
    return 'text/html';
};

$from_name_filter = function( $name ) {
    // match handler-settings.php behavior
    return 'Email Test';

    // or use site name instead:
    // return get_bloginfo( 'name' );
};

add_filter( 'wp_mail_content_type', $html_content_type_filter );
add_filter( 'wp_mail_from_name', $from_name_filter, 9999 );

    // =========================
    // Email content
    // =========================
    $subject = '✅ Email Delivery Test Successful';

    $site_name = get_bloginfo( 'name' );
    $site_url  = home_url();
    $admin_url = admin_url( 'tools.php?page=wpfi-troubleshooting-tools#wpfi-tab-email-tool' );

    $message = '
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Email Test</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f9;padding:30px 0;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.08);overflow:hidden;">

          <tr>
            <td style="background:#d16aff;padding:22px 26px;color:#ffffff;">
              <h1 style="margin:0;font-size:20px;font-weight:600;">Website Email Test</h1>
              <p style="margin:6px 0 0;font-size:14px;opacity:.9;">
                Email delivery is working correctly!
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:26px;">
              <p style="font-size:15px;color:#333;line-height:1.6;margin:0 0 14px;">
                Great news! Your site was able to successfully send this email.
              </p>

              <table cellpadding="0" cellspacing="0" style="margin:16px 0;width:100%;font-size:14px;">
                <tr>
                  <td style="padding:6px 0;color:#666;">Site</td>
                  <td style="padding:6px 0;font-weight:600;color:#111;">' . esc_html( $site_name ) . '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#666;">URL</td>
                  <td style="padding:6px 0;">
                    <a href="' . esc_url( $site_url ) . '" style="color:#d16aff;text-decoration:none;">
                      ' . esc_html( $site_url ) . '
                    </a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#666;">Time Sent</td>
                  <td style="padding:6px 0;color:#111;">' . date_i18n( 'F j, Y g:i a' ) . '</td>
                </tr>
              </table>

              <p style="font-size:14px;color:#555;line-height:1.6;margin:18px 0 0;">
                If this email landed in your inbox, your server’s email configuration is working.
                If you experience delivery issues elsewhere, consider using an SMTP plugin for best reliability.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background:#f6f7f9;padding:16px 26px;font-size:12px;color:#777;text-align:center;">
              Sent from your WordPress site<br>
              <a href="' . esc_url( $admin_url ) . '" style="color:#d16aff;text-decoration:none;">
                Send Another Test
              </a>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

$headers = [
    'Content-Type: text/html; charset=UTF-8',
];

    // =========================
    // Send
    // =========================
    $sent = wp_mail( $email, $subject, $message, $headers );

    // =========================
    // Cleanup (CRITICAL)
    // =========================
remove_filter( 'wp_mail_from_name', $from_name_filter, 9999 );
remove_filter( 'wp_mail_content_type', $html_content_type_filter );

    // =========================
    // Persist result + redirect
    // =========================
    update_option(
        'conflict_finder_email_test_status',
        [
            'status' => $sent ? 'success' : 'fail',
            'time'   => time(),
        ]
    );

    wp_safe_redirect(
        admin_url(
            'tools.php?page=' . $this->menu_slug .
            '&tool=email&email_result=' . ( $sent ? 'success' : 'fail' ) .
            '#wpfi-tab-email-tool'
        )
    );
    exit;
}

    private function conflict_finder_render_overview_tab_content() {

    $email_status = get_option( 'conflict_finder_email_test_status', [] );

$email_state = 'not_tested';
$email_label = '⚪ Not Tested';

if ( ! empty( $email_status['status'] ) ) {
    if ( $email_status['status'] === 'success' ) {
        $email_state = 'ok';
        $email_label = '✅ Working';
    } else {
        $email_state = 'warning';
        $email_label = '❌ Failed';
    }
}

    $base = admin_url( 'tools.php?page=' . $this->menu_slug );

    $debug_on  = defined( 'WP_DEBUG' ) && WP_DEBUG === true;
    $plugin_on = (bool) get_option( 'conflict_finder_plugins_temporarily_deactivated' );
    $theme_on  = (bool) get_option( 'conflict_finder_theme_temporarily_switched' );

    global $wp_version;

	$env = [
	    'WordPress Version' => [
	        'value' => $wp_version,
	        'icon'  => 'dashicons-wordpress',
	    ],
	    'PHP Version' => [
	        'value' => PHP_VERSION,
	        'icon'  => 'dashicons-editor-code',
	    ],
	    'PHP Memory Limit' => [
	        'value' => WP_MEMORY_LIMIT,
	        'icon'  => 'dashicons-performance',
	    ],
	    'Server Software' => [
	        'value' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
	        'icon'  => 'dashicons-cloud',
	    ],
	];
    ?>

     <!-- ENVIRONMENT SNAPSHOT -->
    <div style="margin-bottom:36px;margin-top: 33px;">

        <h2 style="margin-bottom:12px;">Environment Snapshot</h2>

        <div style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:18px;
        ">
            <?php foreach ( $env as $label => $data ) : ?>
    <div style="
        background:#fff;
        padding:18px 20px;
        border-radius:14px;
        box-shadow:0 8px 22px rgba(0,0,0,.06);
        border-left:5px solid #d16aff;
        display:flex;
        gap:14px;
        align-items:flex-start;
    ">

        <span class="dashicons <?php echo esc_attr( $data['icon'] ); ?>"
              style="
                font-size:26px;
                color:#d16aff;
                margin-top:2px;
              ">
        </span>

        <div>
            <strong style="display:block;margin-bottom:6px;">
                <?php echo esc_html( $label ); ?>
            </strong>

            <span style="color:#555;font-size:14px;">
                <?php echo esc_html( $data['value'] ); ?>
            </span>
        </div>

    </div>
<?php endforeach; ?>
        </div>

    </div>

    <!-- STATUS BAR -->
    <div class="wpfi-status-rail">

        <div class="wpfi-status-item <?php echo $debug_on ? 'warning' : 'ok'; ?>">
            <strong>WP_DEBUG Mode</strong>
            <span><?php echo $debug_on ? '⚠️ Enabled' : '✅ Disabled'; ?></span>
        </div>

        <div class="wpfi-status-item <?php echo $plugin_on ? 'warning' : 'ok'; ?>">
            <strong>Plugin Conflict Mode</strong>
            <span><?php echo $plugin_on ? '⚠️ Active' : '✅ Inactive'; ?></span>
        </div>

        <div class="wpfi-status-item <?php echo $theme_on ? 'warning' : 'ok'; ?>">
            <strong>Theme Conflict Mode</strong>
            <span><?php echo $theme_on ? '⚠️ Active' : '✅ Inactive'; ?></span>
        </div>

        <div class="wpfi-status-item <?php echo esc_attr( $email_state ); ?>">
    <strong>Email Delivery</strong>
    <span>
    <?php echo esc_html( $email_label ); ?>
    <?php if ( ! empty( $email_status['time'] ) ) : ?>
        <small style="display:block;font-size:11px;font-weight:400;opacity:.7;">
            <?php echo esc_html( human_time_diff( $email_status['time'], time() ) ); ?> ago
        </small>
    <?php endif; ?>
</span>

</div>

    </div>

    <h2 style="margin-bottom:15px;">Select Your Troubleshooting Tool</h2>

<div style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
            gap:28px;
        ">
            <!-- WP DEBUG TOOL -->
            <a href="<?php echo esc_url( $base . '&tool=debug#wpfi-tab-debug' ); ?>"
               style="text-decoration:none;color:inherit;">
                <div style="
                    background:#fff;
                    padding:28px;
                    border-radius:16px;
                    box-shadow:0 12px 30px rgba(0,0,0,.08);
                    border-top:6px solid #d16aff;
                    transition:transform .2s ease, box-shadow .2s ease;
                "
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 18px 45px rgba(0,0,0,.14)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                >

                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <span class="dashicons dashicons-warning"
                              style="
                                font-size:22px;
                                color:#d16aff;
                                background:#f6e9ff;
                                padding:12px;
                                border-radius:12px;
                                flex-shrink:0;
                              ">
                        </span>

                        <div>
                            <h2 style="margin:0 0 6px 0;">WP_DEBUG Tool</h2>

                            <p style="color:#555;font-size:15px;margin:0 0 12px 0;">
                                Enable WordPress debugging, capture errors,
                                and view logs without touching wp-config.php.
                            </p>

                            <span style="font-weight:600;color:#d16aff;">
                                Open Tool →
                            </span>
                        </div>
                    </div>

                </div>
            </a>

            <!-- PLUGIN CONFLICT TOOL -->
            <a href="<?php echo esc_url( $base . '&tool=plugins#wpfi-tab-plugin-tool' ); ?>"
               style="text-decoration:none;color:inherit;">
                <div style="
                    background:#fff;
                    padding:28px;
                    border-radius:16px;
                    box-shadow:0 12px 30px rgba(0,0,0,.08);
                    border-top:6px solid #d16aff;
                    transition:transform .2s ease, box-shadow .2s ease;
                "
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 18px 45px rgba(0,0,0,.14)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                >

                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <span class="dashicons dashicons-admin-plugins"
                              style="
                                font-size:22px;
                                color:#d16aff;
                                background:#f6e9ff;
                                padding:12px;
                                border-radius:12px;
                                flex-shrink:0;
                              ">
                        </span>

                        <div>
                            <h2 style="margin:0 0 6px 0;">Plugin Conflict Tool</h2>

                            <p style="color:#555;font-size:15px;margin:0 0 12px 0;">
                                Temporarily disable plugins and activate them
                                one by one to pinpoint conflicts.
                            </p>

                            <span style="font-weight:600;color:#d16aff;">
                                Open Tool →
                            </span>
                        </div>
                    </div>

                </div>
            </a>

            <!-- THEME CONFLICT TOOL -->
            <a href="<?php echo esc_url( $base . '&tool=theme#wpfi-tab-theme-tool' ); ?>"
               style="text-decoration:none;color:inherit;">
                <div style="
                    background:#fff;
                    padding:28px;
                    border-radius:16px;
                    box-shadow:0 12px 30px rgba(0,0,0,.08);
                    border-top:6px solid #d16aff;
                    transition:transform .2s ease, box-shadow .2s ease;
                "
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 18px 45px rgba(0,0,0,.14)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                >

                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <span class="dashicons dashicons-admin-appearance"
                              style="
                                font-size:22px;
                                color:#d16aff;
                                background:#f6e9ff;
                                padding:12px;
                                border-radius:12px;
                                flex-shrink:0;
                              ">
                        </span>

                        <div>
                            <h2 style="margin:0 0 6px 0;">Theme Conflict Tool</h2>

                            <p style="color:#555;font-size:15px;margin:0 0 12px 0;">
                                Switch to a safe fallback theme to determine
                                whether your active theme is the problem.
                            </p>

                            <span style="font-weight:600;color:#d16aff;">
                                Open Tool →
                            </span>
                        </div>
                    </div>

                </div>
            </a>

            <!-- EMAIL TOOL -->
            <a href="<?php echo esc_url( $base . '&tool=plugins#wpfi-tab-email-tool' ); ?>"
               style="text-decoration:none;color:inherit;">
                <div style="
                    background:#fff;
                    padding:28px;
                    border-radius:16px;
                    box-shadow:0 12px 30px rgba(0,0,0,.08);
                    border-top:6px solid #d16aff;
                    transition:transform .2s ease, box-shadow .2s ease;
                "
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 18px 45px rgba(0,0,0,.14)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                >

                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <span class="dashicons dashicons-admin-plugins"
                              style="
                                font-size:22px;
                                color:#d16aff;
                                background:#f6e9ff;
                                padding:12px;
                                border-radius:12px;
                                flex-shrink:0;
                              ">
                        </span>

                        <div>
                            <h2 style="margin:0 0 6px 0;">Email Delivery Tool</h2>

                            <p style="color:#555;font-size:15px;margin:0 0 12px 0;">
                                Use this tool to test whether your WordPress site can successfully send email.
                            </p>

                            <span style="font-weight:600;color:#d16aff;">
                                Open Tool →
                            </span>
                        </div>
                    </div>

                </div>
            </a>

        </div>

    <?php
}

    public function conflict_finder_plugin_conflict_notice() {

          // Do not show notice on the troubleshooting tools page
    if (
        isset( $_GET['page'] ) &&
        $_GET['page'] === 'wpfi-troubleshooting-tools'
    ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! get_option( 'conflict_finder_plugins_temporarily_deactivated' ) ) {
        return;
    }

    $reactivate_url = wp_nonce_url(
        admin_url(
            'admin-post.php?action=conflict_finder_reactivate_plugins'
        ),
        'conflict_finder_plugin_tool'
    );

    ?>
    <div class="notice notice-warning wp-debug-notice">
        <p>
            <strong>Plugin Conflict Mode Active:</strong>
            All plugins have been temporarily deactivated for troubleshooting.
        </p>
        <p>
            <a href="<?php echo esc_url( $reactivate_url ); ?>"
   class="button button-primary wpfi-primary-action">
                Reactivate Saved Plugins
            </a>
        </p>
    </div>
    <?php
}

    public function conflict_finder_theme_conflict_notice() {

    // Admins only
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // ❌ Do NOT show on the Troubleshooting Tools page
    if (
        isset( $_SERVER['REQUEST_URI'] ) &&
        strpos( $_SERVER['REQUEST_URI'], 'tools.php?page=wpfi-troubleshooting-tools' ) !== false
    ) {
        return;
    }

    // Only show when Theme Conflict Mode is active
    if ( ! get_option( 'conflict_finder_theme_temporarily_switched' ) ) {
        return;
    }

    $saved = get_option( 'conflict_finder_saved_active_theme', [] );

    $restore_url = wp_nonce_url(
        admin_url( 'admin-post.php?action=conflict_finder_restore_saved_theme' ),
        'conflict_finder_theme_tool'
    );
    ?>
    <div class="notice notice-warning wp-debug-notice">
        <p>
            <strong>Theme Conflict Mode Active:</strong>
            <?php if ( ! empty( $saved['name'] ) ) : ?>
                Your site is temporarily using a different theme instead of
                <strong><?php echo esc_html( $saved['name'] ); ?></strong>.
            <?php else : ?>
                Your site is temporarily using a different theme for troubleshooting.
            <?php endif; ?>
        </p>

        <p>
            <a href="<?php echo esc_url( $restore_url ); ?>"
   class="button button-primary wpfi-primary-action">
                Restore Original Theme
            </a>
        </p>
    </div>
    <?php
}

public function conflict_finder_activate_single_theme() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_theme_tool' );

    $theme = sanitize_text_field( $_POST['theme'] ?? '' );
    if ( empty( $theme ) ) {
        wp_safe_redirect( admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=theme' ) );
        exit;
    }

    // Save original theme ONCE
    if ( ! get_option( 'conflict_finder_theme_temporarily_switched' ) ) {
        $current = wp_get_theme();
        update_option( 'conflict_finder_saved_active_theme', [
            'stylesheet' => $current->get_stylesheet(),
            'template'   => $current->get_template(),
            'name'       => $current->get( 'Name' ),
        ] );
    }

    update_option( 'conflict_finder_theme_temporarily_switched', time() );
    switch_theme( $theme );

    wp_safe_redirect(
        admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=theme#wpfi-tab-theme-tool' )
    );
    exit;
}

public function conflict_finder_restore_saved_theme() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_theme_tool' );

    $saved = get_option( 'conflict_finder_saved_active_theme', [] );

    delete_option( 'conflict_finder_theme_temporarily_switched' );

    if ( ! empty( $saved['stylesheet'] ) ) {
        switch_theme( $saved['stylesheet'] );
    }

    delete_option( 'conflict_finder_saved_active_theme' );

    wp_safe_redirect(
        admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=theme#wpfi-tab-theme-tool' )
    );
    exit;
}

    public function conflict_finder_activate_single_plugin() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_activate_single_plugin' );

    // Only allow while conflict mode is active
    if ( ! get_option( 'conflict_finder_plugins_temporarily_deactivated' ) ) {
        wp_safe_redirect(
            admin_url( 'tools.php?page=' . $this->menu_slug )
        );
        exit;
    }

    $plugin = sanitize_text_field( $_POST['plugin'] ?? '' );

    if ( $plugin && file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
        activate_plugin( $plugin );
    }

    wp_safe_redirect(
        admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=plugins#wpfi-tab-plugin-tool' )
    );
    exit;
}

public function conflict_finder_global_wp_debug_notice() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // ❌ Do not show on the Troubleshooting Tools page (any variation)
    if (
        isset( $_SERVER['REQUEST_URI'] ) &&
        strpos( $_SERVER['REQUEST_URI'], 'tools.php?page=wpfi-troubleshooting-tools' ) !== false
    ) {
        return;
    }

    // Only show if WP_DEBUG is actually enabled
    if ( ! defined( 'WP_DEBUG' ) || WP_DEBUG !== true ) {
        return;
    }

    $tools_url = admin_url(
        'tools.php?page=wpfi-troubleshooting-tools&tool=debug#wpfi-tab-debug'
    );
    ?>
    <div class="notice notice-warning wp-debug-notice">
        <p>
            <strong>Warning:</strong> WordPress debug mode is enabled.
            <a href="<?php echo esc_url( $tools_url ); ?>">
                Click here to disable it…
            </a>
        </p>
    </div>
    <?php
}

    public function conflict_finder_enqueue_global_admin_css() {
    wp_add_inline_style( 'wp-admin', '
        :root {
            --accent: #d16aff;
        }

        .wp-debug-notice {
            border-left-color: var(--accent) !important;
            border-radius: 10px;
        }
        /* === Primary Action Buttons (Conflict Modes) === */
    .wpfi-primary-action {
        background-color: #d16aff !important;
        border-color: #d16aff !important;
        color: #fff !important;
    }

    .wpfi-primary-action:hover,
    .wpfi-primary-action:focus {
        background-color: #00D78B !important;
        border-color: #00D78B !important;
        color: #fff !important;
    }

    .wpfi-primary-action {
        transition: background-color .15s ease, border-color .15s ease;
    }
    ' );
}
    public function conflict_finder_ajax_clear_debug_log() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
    }

    check_ajax_referer( 'conflict_finder_debug_log', 'nonce' );

    $log = WP_CONTENT_DIR . '/debug.log';

    if ( ! file_exists( $log ) ) {
        wp_send_json_success( [ 'message' => 'debug.log does not exist.' ] );
    }

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
	if ( ! is_writable( $log ) ) {
	    wp_send_json_error( [ 'message' => 'debug.log is not writable.' ], 500 );
	}

    // Truncate file safely (do NOT delete)
    $result = file_put_contents( $log, '', LOCK_EX );

    if ( $result === false ) {
        wp_send_json_error( [ 'message' => 'Failed to clear debug.log.' ], 500 );
    }

    wp_send_json_success( [ 'message' => 'Debug log cleared.' ] );
}

    public function conflict_finder_add_admin_page() {
        add_management_page(
            'Troubleshoot',
            'Troubleshoot',
            'manage_options',
            $this->menu_slug,
            [ $this, 'conflict_finder_render_page' ]
        );
    }

    public function conflict_finder_enqueue_assets( $hook ) {
        // Hook name for Tools submenu pages is tools_page_{menu_slug}
        if ( $hook !== 'tools_page_' . $this->menu_slug ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', $this->conflict_finder_css() );
        wp_add_inline_script( 'jquery-core', $this->conflict_finder_js() );

        // Pass ajax url + nonce to inline JS
        $nonce = wp_create_nonce( 'conflict_finder_debug_log' );
        $data  = 'window.WPFI_DEBUG_LOG = ' . wp_json_encode( [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => $nonce,
        ] ) . ';';

        wp_add_inline_script( 'jquery-core', $data, 'before' );
    }

    public function conflict_finder_render_page() {
$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : 'overview';
if ( empty( $tool ) ) {
    $tool = 'overview';
}

        $settings = get_option( $this->option_name, [] );
        $enabled  = ! empty( $settings['WP_DEBUG'] );

        $option_meta = [
            'WP_DEBUG_DISPLAY' => [
                'title' => 'Display errors on the site',
                'desc'  => 'Shows WordPress and PHP errors directly on the page. Useful for debugging, but not recommended on live production sites.'
            ],
            'WP_DEBUG_LOG' => [
                'title' => 'Save errors to a log file',
                'desc'  => 'Writes errors to wp-content/debug.log so you can review them without showing visitors.'
            ],
            'SCRIPT_DEBUG' => [
                'title' => 'Load unminified scripts',
                'desc'  => 'Loads original (non-minified) CSS/JS files to help troubleshoot front-end or asset-related issues.'
            ],
            'WP_DISABLE_FATAL_ERROR_HANDLER' => [
                'title' => 'Disable WordPress fatal error recovery',
                'desc'  => 'Disables the WordPress recovery mode screen so fatal errors can surface directly for troubleshooting.'
            ],
            'DISPLAY_ERRORS' => [
                'title' => 'Force PHP errors to display',
                'desc'  => 'Forces PHP to display errors on-screen even if the server configuration normally hides them.'
            ],
            'LOG_ERRORS' => [
                'title' => 'Force PHP errors to log',
                'desc'  => 'Ensures PHP errors are written to the server error log (not the WordPress debug.log).'
            ],
            'ERROR_REPORTING' => [
                'title' => 'Report all PHP errors',
                'desc'  => 'Enables reporting for all PHP notices, warnings, and errors (E_ALL).'
            ],
        ];
        ?>

        <div class="wrap wp-debug-wrap">

    <!-- Tabs -->
    <h1 style="margin-bottom:4px;margin-top: 10px;;margin-top: 10px;">Troubleshooting Tools</h1>

        <p style="font-size:16px;color:#555;margin-bottom:10px;">
        Safely diagnose site issues by isolating plugins, themes, and configuration problems without permanently changing your setup.
    </p>

<?php
$base_url = admin_url( 'tools.php?page=' . $this->menu_slug );
$active_tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
?>

<h2 class="nav-tab-wrapper" style="margin-top:12px;">
    <a href="#wpfi-tab-overview"
       class="nav-tab <?php echo ( $tool === 'overview' ) ? 'nav-tab-active' : ''; ?>"
       data-wpfi-tab="wpfi-tab-overview">Overview</a>

    <a href="#wpfi-tab-debug"
       class="nav-tab <?php echo ( $tool === 'debug' ) ? 'nav-tab-active' : ''; ?>"
       data-wpfi-tab="wpfi-tab-debug">WP_DEBUG Tool</a>

    <a href="#wpfi-tab-plugin-tool"
       class="nav-tab <?php echo ( $tool === 'plugins' ) ? 'nav-tab-active' : ''; ?>"
       data-wpfi-tab="wpfi-tab-plugin-tool">Plugin Conflict Tool</a>

    <a href="#wpfi-tab-theme-tool"
       class="nav-tab <?php echo ( $tool === 'theme' ) ? 'nav-tab-active' : ''; ?>"
       data-wpfi-tab="wpfi-tab-theme-tool">Theme Conflict Tool</a>

    <a href="#wpfi-tab-email-tool"
   class="nav-tab <?php echo ( $tool === 'email' ) ? 'nav-tab-active' : ''; ?>"
   data-wpfi-tab="wpfi-tab-email-tool">Email Devilery Tool</a>
</h2>

<!-- Tab Panel: Overview -->
<div id="wpfi-tab-overview" class="wpfi-tab-panel"
     style="display:<?php echo ( $tool === 'overview' ) ? 'block' : 'none'; ?>;">

    <?php $this->conflict_finder_render_overview_tab_content(); ?>

</div>

    <!-- Tab Panel: WP Debug Tool -->
    <div id="wpfi-tab-debug" class="wpfi-tab-panel"
     style="display:<?php echo ( $tool === 'debug' ) ? 'block' : 'none'; ?>;">

        <div class="wp-debug-layout">

            <!-- LEFT COLUMN -->
            <div class="wp-debug-main">
                <p class="description" style="font-size: 16px;margin: 25px 5px 20px;">
                    Safely enable and manage WordPress debugging to help identify errors, capture detailed logs, and troubleshoot issues caused by plugins, themes, or custom code. This tool gives you full control over how <strong>WP_DEBUG</strong> behaves while automatically handling changes to <code>wp-config.php</code>, making it easier to diagnose problems and disable debugging once troubleshooting is complete.
                </p>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="conflict_finder_save_wp_debug_settings">
                    <?php wp_nonce_field( 'conflict_finder_save_wp_debug_settings' ); ?>

                    <div class="debug-card">
                        <h2>Master Controls</h2>
                        <p class="description">
                            Enable WordPress debugging and optionally activate all options.
                        </p>

                        <?php $this->conflict_finder_toggle( 'WP_DEBUG', 'Enable WP_DEBUG', $settings ); ?>
                        <?php $this->conflict_finder_toggle( 'ENABLE_ALL', 'Enable All WP_DEBUG Options', $settings ); ?>
                    </div>

                    <div class="debug-card debug-options <?php echo $enabled ? '' : 'disabled'; ?>">
                        <h2>WP_DEBUG Reporting Options</h2>
                        <p class="description">
                            These settings apply only when WP_DEBUG is enabled.
                        </p>

                        <?php
                        $options = [
                            'WP_DEBUG_DISPLAY' => $option_meta['WP_DEBUG_DISPLAY']['title'],
                            'WP_DEBUG_LOG' => $option_meta['WP_DEBUG_LOG']['title'],
                            'SCRIPT_DEBUG' => $option_meta['SCRIPT_DEBUG']['title'],
                            'WP_DISABLE_FATAL_ERROR_HANDLER' => $option_meta['WP_DISABLE_FATAL_ERROR_HANDLER']['title'],
                            'DISPLAY_ERRORS' => $option_meta['DISPLAY_ERRORS']['title'],
                            'LOG_ERRORS' => $option_meta['LOG_ERRORS']['title'],
                            'ERROR_REPORTING' => $option_meta['ERROR_REPORTING']['title'],
                        ];

                        foreach ( $options as $key => $label ) {
                            $desc = $option_meta[ $key ]['desc'] ?? '';
                            $this->conflict_finder_toggle( $key, $label, $settings, $desc );
                        }
                        ?>
                    </div>

                    <?php submit_button( 'Save Settings', 'primary large wp-debug-save' ); ?>
                </form>
            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="wp-debug-sidebar">
                <h2>How This Tool Works</h2>

                <p>
                    <strong>WP_DEBUG</strong> is WordPress’s built-in troubleshooting mode.
                    When enabled, it tells WordPress to show or log errors that help identify
                    issues with plugins, themes, or custom code.
                </p>
                <p class="note" style="background: #efe;padding: 10px; text-align: center;"><strong>Your wp-config.php file must be writable</strong></p>
                <hr>

                <h3>What this tool does</h3>

                <ul>
                    <li>Safely updates <code>wp-config.php</code> for you</li>
                    <li>Turns WP_DEBUG on or off with a single switch</li>
                    <li>Applies additional debug options</li>
                    <li>Removes cleanly when debugging is disabled</li>
                </ul>
                <hr>

                <h3>Recommended troubleshooting process</h3>

                <ol>
                    <li>Enable <strong>WP_DEBUG</strong></li>
                    <li>Turn on the debug options you need</li>
                    <li>Reproduce the issue on your site</li>
                    <li>Review errors shown on screen or in log below</li>
                    <li>Disable debugging once the issue is resolved</li>
                </ol>
                <hr>

                <h3>Debug Log Tools</h3>

                <p style="margin-top:8px;">
                    View or download <code>wp-content/debug.log</code>.
                </p>

                <p style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                    <button type="button" class="button" id="wpdtt-view-debug-log">
                        View Debug Log
                    </button>

                    <a class="button"
                       href="<?php echo esc_url( wp_nonce_url(
                           admin_url( 'admin-post.php?action=conflict_finder_download_debug_log' ),
                           'conflict_finder_download_debug_log'
                       ) ); ?>">
                        Download Debug Log
                    </a>
                </p>

                <div class="wp-debug-tip">
                    <strong>Best Practice:</strong><br>
                    Debug mode should only be enabled temporarily and never left on
                    for live production sites.
                </div>
            </aside>

        </div>

    </div><!-- /#wpfi-tab-debug -->

    <!-- Tab Panel: Placeholder 1 -->
    <div id="wpfi-tab-plugin-tool" class="wpfi-tab-panel"
     style="display:<?php echo ( $tool === 'plugins' ) ? 'block' : 'none'; ?>;">
        <div class="wp-debug-layout">

            <!-- LEFT COLUMN -->
            <div class="wp-debug-main">
                <p class="description" style="font-size: 16px;margin: 25px 5px 20px;">
                    This tool allows you to safely isolate plugin-related issues without manually disabling plugins one by one. With a single click, your currently active
    plugins are saved and temporarily deactivated so you can determine whether a plugin
    conflict is causing errors, white screens, broken layouts, or unexpected behavior.
        </p>
        <p class="description" style="font-size: 16px;margin: 5px 5px 20px;">
    <strong>Once troubleshooting is complete, your original plugin configuration can be fully
    restored instantly.</strong>
                </p>

<div class="debug-card">
    <h2>Plugin Conflict Isolation</h2>

    <p class="description">
        Temporarily disable all plugins to identify conflicts, then restore them instantly.
        Your active plugin list is saved safely before anything is changed.
    </p>

    <?php
$plugin_conflict_active = (bool) get_option( 'conflict_finder_plugins_temporarily_deactivated' );
?>

<?php if ( ! $plugin_conflict_active ) : ?>

    <!-- Deactivate button (only when NOT in conflict mode) -->
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'conflict_finder_plugin_tool' ); ?>
        <input type="hidden" name="action" value="conflict_finder_deactivate_plugins">

        <button class="button button-primary" id="conflict_finder_plugin_off">
            Deactivate All Plugins
        </button>
    </form>

<?php else : ?>

    <!-- Reactivate button (only when conflict mode IS active) -->
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'conflict_finder_plugin_tool' ); ?>
        <input type="hidden" name="action" value="conflict_finder_reactivate_plugins">

        <button class="button" id="conflict_finder_plugin_on">
            Reactivate Saved Plugins
        </button>
    </form>

<?php endif; ?>
</div>

<?php
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( $plugin_conflict_active ) {
    // During conflict mode → show the saved snapshot
    $plugin_files = get_option( 'conflict_finder_saved_active_plugins', [] );
} else {
    // Before conflict mode → show currently active plugins
    $plugin_files = get_option( 'active_plugins', [] );
}

$all_plugins   = get_plugins();
$self          = plugin_basename( __FILE__ );

$plugins_to_show = [];

foreach ( $plugin_files as $plugin_file ) {

    // Exclude this plugin
    if ( $plugin_file === $self ) {
        continue;
    }

    // Exclude support-wpfi folder
    if ( strpos( $plugin_file, 'support-wpfi/' ) === 0 ) {
        continue;
    }

    if ( empty( $all_plugins[ $plugin_file ] ) ) {
        continue;
    }

    $plugins_to_show[ $plugin_file ] = $all_plugins[ $plugin_file ];
}

// Sort plugins so ACTIVE ones appear first (only during conflict mode)
if ( $plugin_conflict_active ) {
    uksort( $plugins_to_show, function ( $a, $b ) {

        $a_active = is_plugin_active( $a );
        $b_active = is_plugin_active( $b );

        // Active plugins first
        if ( $a_active === $b_active ) {
            return strcasecmp( $a, $b ); // secondary alphabetical sort
        }

        return $a_active ? -1 : 1;
    });
}

$plugin_count = count( $plugins_to_show );

if ( $plugin_count > 0 ) :
?>
<div class="debug-card" style="grid-template-columns:1fr;">
<?php if ( ! $plugin_conflict_active ) : ?>

    <h2>
        <?php echo esc_html( $plugin_count ); ?>
        Plugin<?php echo $plugin_count === 1 ? '' : 's'; ?>
        Will Be Temporarily Disabled
    </h2>

    <p class="description">
        These plugins are currently active and will be restored exactly as-is
        once Plugin Conflict Mode is turned off.
    </p>

<?php else : ?>

    <h2>
        Activate Plugins One at a Time
    </h2>

    <p class="description">
        You can activate them one at a time to identify which plugin is causing the issue.
        When finished, click <strong>Reactivate Saved Plugins</strong> to restore everything.
    </p>

<?php endif; ?>

    <div style="
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap:16px;
        margin-top:12px;
    ">
        <?php foreach ( $plugins_to_show as $plugin_file => $plugin ) : ?>
<?php
$is_active = is_plugin_active( $plugin_file );

if ( $plugin_conflict_active ) {
    if ( $is_active ) {
        // Active plugin (green)
        $bg     = '#ecfdf3';
        $border = '#86efac';
        $icon   = '#16a34a';
    } else {
        // Inactive plugin (red)
        $bg     = '#fef2f2';
        $border = '#fca5a5';
        $icon   = '#dc2626';
    }
} else {
    // Normal mode
    $bg     = '#f9f9f9';
    $border = '#eee';
    $icon   = '#d16aff';
}
?>

<div style="
    display:flex;
    gap:10px;
    align-items:flex-start;
    background:<?php echo esc_attr( $bg ); ?>;
    padding:12px;
    border-radius:10px;
    border:1px solid <?php echo esc_attr( $border ); ?>;
">

<span
    class="dashicons dashicons-admin-plugins"
    style="font-size:23px;color:<?php echo esc_attr( $icon ); ?>;"
    aria-hidden="true"
></span>

                <div style="flex:1; min-width:0;">

<strong style="
    display:block;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:100%;
">
    <?php echo esc_html( $plugin['Name'] ); ?>
</strong>

                    <?php if ( ! empty( $plugin['Version'] ) ) : ?>
                        <span style="font-size:12px;color:#666;">
                            Version <?php echo esc_html( $plugin['Version'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $plugin_conflict_active ) : ?>

    <?php if ( ! is_plugin_active( $plugin_file ) ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px;">
    <?php wp_nonce_field( 'conflict_finder_activate_single_plugin' ); ?>
    <input type="hidden" name="action" value="conflict_finder_activate_single_plugin">
    <input type="hidden" name="plugin" value="<?php echo esc_attr( $plugin_file ); ?>">

    <button class="button button-small">
        Activate
    </button>
</form>

    <?php else : ?>
        <br>
        <span style="font-size:12px;color:#00a32a;">Active</span>
    <?php endif; ?>

<?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="wp-debug-sidebar">
    <h2>How This Tool Works</h2>

    <p>
        The Plugin Conflict Tool helps you identify issues caused by plugin conflicts
        without permanently changing your site configuration.
    </p>

    <p>
        It temporarily disables plugins so you can safely test your site and
        determine whether a specific plugin is responsible for errors, broken layouts,
        or unexpected behavior.
    </p>

    <hr>

    <h3>What this tool does</h3>

    <ul>
        <li>Saves a snapshot of all currently active plugins</li>
        <li>Temporarily disables plugins in one click</li>
        <li>Allows you to isolate individual plugins</li>
        <li>Restores your original setup when finished</li>
    </ul>

    <hr>

    <h3>Recommended troubleshooting process</h3>

    <ol>
        <li><strong>Deactivate All Plugins</strong> to start isolation mode</li>
        <li>Test your site with all plugins disabled</li>
        <li>Activate plugins one at a time to each change</li>
        <li>Identify the plugin that causes the issue</li>
        <li>Restore all plugins once complete</li>
    </ol>

    <hr>

    <div class="wp-debug-tip">
        <strong>Best Practice:</strong><br>
        Always restore your original plugin configuration after troubleshooting
        to avoid accidental misconfiguration.
    </div>
</aside>

        </div>
    </div>

    <!-- Tab Panel: Placeholder 2 -->
    <div id="wpfi-tab-theme-tool" class="wpfi-tab-panel" style="display:none;">
        <div class="wp-debug-layout">

            <!-- LEFT COLUMN -->
            <div class="wp-debug-main">
<?php
$theme_conflict_active = (bool) get_option( 'conflict_finder_theme_temporarily_switched' );
$saved_theme = get_option( 'conflict_finder_saved_active_theme', [] );
$current_theme = wp_get_theme();
$themes = wp_get_themes();
?>

<p class="description" style="font-size:16px;margin:25px 5px 20px;">
    Click a theme below to temporarily activate it and determine whether your current
    theme is causing the issue. You can restore your original theme at any time.
</p>

<div class="debug-card" style="grid-template-columns:1fr;">
    <h2>Theme Conflict Isolation</h2>

<?php
$current_theme = wp_get_theme();

if (
    $theme_conflict_active &&
    ! empty( $saved_theme['stylesheet'] ) &&
    $current_theme->get_stylesheet() !== $saved_theme['stylesheet']
) :
?>
    <div style="
        background:#fff7ed;
        border:1px solid #fdba74;
        border-radius:5px;
        padding:16px;
        margin-bottom:5px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
    ">
        <div>
            <strong style="font-size:15px;">Original Theme</strong><br>
            <span style="color:#9a3412;">
                <?php echo esc_html( $saved_theme['name'] ); ?>
            </span>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'conflict_finder_theme_tool' ); ?>
            <input type="hidden" name="action" value="conflict_finder_restore_saved_theme">

            <button class="button button-primary wpfi-primary-action" style="font-size:16px;">
                Restore Original Theme
            </button>
        </form>
    </div>
<?php endif; ?>

<div style="
    display:grid;
    gap:16px;
">
 <div class="wpfi-theme-grid">

<?php
$rendered = [];
$active_stylesheet   = $current_theme->get_stylesheet();
$original_stylesheet = $saved_theme['stylesheet'] ?? '';

/**
 * 1️⃣ ORIGINAL THEME FIRST
 */
if (
    $theme_conflict_active &&
    $original_stylesheet &&
    isset( $themes[ $original_stylesheet ] )
) :
    $theme = $themes[ $original_stylesheet ];
    $rendered[] = $original_stylesheet;
    $screenshot = $theme->get_screenshot();
    ?>
    <div class="wpfi-theme-card original">
        <?php if ( $screenshot ) : ?>
            <img src="<?php echo esc_url( $screenshot ); ?>" alt="">
        <?php endif; ?>

        <div class="wpfi-theme-meta">
            <span class="wpfi-theme-name"><?php echo esc_html( $theme->get( 'Name' ) ); ?></span>
            <span class="wpfi-theme-version">Version <?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
            <div class="wpfi-theme-badge wpfi-badge-original">Original Theme</div>
        </div>

        <?php if ( $active_stylesheet === $original_stylesheet ) : ?>
            <div class="wpfi-theme-badge wpfi-badge-active" style="text-align: center;font-size: 13px;">Active Theme</div>
        <?php else : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'conflict_finder_theme_tool' ); ?>
                <input type="hidden" name="action" value="conflict_finder_activate_single_theme">
                <input type="hidden" name="theme" value="<?php echo esc_attr( $original_stylesheet ); ?>">
                <button class="button button-small">Activate</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
/**
 * 2️⃣ ACTIVE THEME SECOND (IF NOT ORIGINAL)
 */
if (
    $active_stylesheet &&
    ! in_array( $active_stylesheet, $rendered, true ) &&
    isset( $themes[ $active_stylesheet ] )
) :
    $theme = $themes[ $active_stylesheet ];
    $rendered[] = $active_stylesheet;
    $screenshot = $theme->get_screenshot();
    ?>
    <div class="wpfi-theme-card active">
        <?php if ( $screenshot ) : ?>
            <img src="<?php echo esc_url( $screenshot ); ?>" alt="">
        <?php endif; ?>

        <div class="wpfi-theme-meta">
            <span class="wpfi-theme-name"><?php echo esc_html( $theme->get( 'Name' ) ); ?></span>
            <span class="wpfi-theme-version">Version <?php echo esc_html( $theme->get( 'Version' ) ); ?></span>

        </div>

        <div class="wpfi-theme-badge wpfi-badge-active" style="text-align: center;font-size: 13px;">Active Theme</div>
    </div>
<?php endif; ?>

<?php
/**
 * 3️⃣ ALL OTHER THEMES (ALPHABETICAL)
 */
foreach ( $themes as $stylesheet => $theme ) :

    if ( in_array( $stylesheet, $rendered, true ) ) {
        continue;
    }

    $screenshot = $theme->get_screenshot();
    ?>
    <div class="wpfi-theme-card">
        <?php if ( $screenshot ) : ?>
            <img src="<?php echo esc_url( $screenshot ); ?>" alt="">
        <?php endif; ?>

        <div class="wpfi-theme-meta">
            <span class="wpfi-theme-name"><?php echo esc_html( $theme->get( 'Name' ) ); ?></span>
            <span class="wpfi-theme-version">Version <?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'conflict_finder_theme_tool' ); ?>
            <input type="hidden" name="action" value="conflict_finder_activate_single_theme">
            <input type="hidden" name="theme" value="<?php echo esc_attr( $stylesheet ); ?>">
            <button class="button button-small">Activate</button>
        </form>
    </div>
<?php endforeach; ?>

</div>

    </div>
</div>

            </div>

            <!-- RIGHT SIDEBAR -->
            <aside class="wp-debug-sidebar">
    <h2>How This Tool Works</h2>

    <p>
        The <strong>Theme Conflict Tool</strong> helps you determine whether your
        active WordPress theme is responsible for layout issues, errors,
        or unexpected behavior.
    </p>

    <p>
        It allows you to temporarily switch themes without permanently changing
        your site’s configuration or losing your original theme settings.
    </p>

    <hr>

    <h3>What this tool does</h3>

    <ul>
        <li>Saves your currently active theme automatically</li>
        <li>Lets you activate any installed theme for testing</li>
        <li>Allows safe, one-click theme switching</li>
        <li>Restores your original theme instantly when finished</li>
    </ul>

    <hr>

    <h3>Recommended troubleshooting process</h3>

    <ol>
        <li>Activate a different theme from the list</li>
        <li>Check your site to see if the issue still occurs</li>
        <li>If the issue disappears, your theme is the cause</li>
        <li>Test additional themes if needed</li>
        <li>Restore your original theme once finished</li>
    </ol>

    <div class="wp-debug-tip">
        <strong>Best Practice:</strong><br>
        Always restore your original theme after troubleshooting
        to avoid leaving your site in a temporary testing state.
    </div>
</aside>

        </div>
    </div>

    <!-- Tab Panel: Email Tool -->
<div id="wpfi-tab-email-tool" class="wpfi-tab-panel"
     style="display:<?php echo ( $tool === 'email' ) ? 'block' : 'none'; ?>;">

    <div class="wp-debug-layout">

        <!-- LEFT COLUMN -->
        <div class="wp-debug-main">

            <p class="description" style="font-size:16px;margin:25px 5px 20px;">
                Use this tool to test whether your WordPress site can successfully send email.
                This sends a real test message using WordPress’s <code>wp_mail()</code> function.
            </p>

            <div class="debug-card" style="grid-template-columns:1fr;">
                <h2>Email Delivery Test</h2>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'conflict_finder_email_test' ); ?>
                    <input type="hidden" name="action" value="conflict_finder_send_test_email">

                    <label style="font-weight:600;display:block;margin-bottom:8px;">
                        Send test email to:
                    </label>

                    <input type="email"
                           name="test_email"
                           required
                           placeholder="you@example.com"
                           style="width:100%;max-width:420px;padding:10px;font-size:15px;">

                    <p style="margin-top:14px;">
                        <button class="button button-primary wpfi-primary-action">
                            Send Test Email
                        </button>
                    </p>
                </form>

                <?php if ( isset( $_GET['email_result'] ) ) : ?>
                    <?php if ( $_GET['email_result'] === 'success' ) : ?>
                        <div style="color:#166534;">
                        <span style="font-size: 15px;font-weight: 700;">    ✅ Test email was sent successfully!</span>
                            <p style="background: #efe;padding: 15px;border-radius: 10px;font-size: 13px;">If this message arrived in your spam or junk folder, your server can send email but is not yet trusted by mailbox providers.<br><br>For reliable inbox delivery, configure an SMTP plugin and DNS authentication (SPF, DKIM, DMARC).</p>
                        </div>
                    <?php else : ?>
                        <div style="margin-top:15px;color:#9a3412;">
                            ❌ Test email failed to send.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

        <!-- RIGHT SIDEBAR -->
        <aside class="wp-debug-sidebar">
            <h2>How This Tool Works</h2>

            <p>
                This test uses WordPress’s built-in email system. If the email fails,
                it usually means your server is missing mail configuration or SMTP is required.
            </p>

            <hr>

            <h3>Common reasons email fails</h3>
            <ul>
                <li>Check spam or junk folder</li>
                <li>No SMTP plugin configured</li>
                <li>Hosting provider blocks PHP mail()</li>
                <li>Incorrect sender domain</li>
                <li>Spam filtering</li>
            </ul>

            <div class="wp-debug-tip">
                <strong>Tip:</strong><br>
                If this test fails, installing an SMTP plugin is strongly recommended.
            </div>
        </aside>

    </div>
</div>

</div><!-- /.wrap -->

        <!-- Modal markup -->
        <div class="wpdtt-modal" id="wpdtt-debug-log-modal" aria-hidden="true" hidden>
            <div class="wpdtt-modal__overlay" data-wpdtt-close="1"></div>
            <div class="wpdtt-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="wpdtt-debug-log-title">
                <div class="wpdtt-modal__header">
                    <h2 id="wpdtt-debug-log-title" style="margin:0;">WP_DEBUG Log</h2>
                    <button type="button" class="button" data-wpdtt-close="1" aria-label="Close">✕</button>
                </div>

                <div class="wpdtt-modal__body">
                    <pre class="wpdtt-debug-log-pre" id="wpdtt-debug-log-pre">Loading…</pre>
                </div>

<div class="wpdtt-modal__footer">
    <button type="button" class="button" id="wpdtt-clear-debug-log">
        Clear Log
    </button>

    <button type="button" class="button button-primary" data-wpdtt-close="1">
        Close
    </button>
</div>
            </div>
        </div>

        <?php
    }

    private function conflict_finder_toggle( $key, $label, $settings, $description = '' ) {
        ?>
        <div class="debug-toggle-wrap">
            <label class="debug-toggle">
                <input type="checkbox"
                       name="settings[<?php echo esc_attr( $key ); ?>]"
                       value="1"
                    <?php checked( $settings[ $key ] ?? 0 ); ?>>
                <span class="slider"></span>
                <span class="label"><?php echo esc_html( $label ); ?></span>
            </label>

            <?php if ( ! empty( $description ) ) : ?>
                <button type="button" class="debug-help-toggle" aria-expanded="false">
                    What’s this?
                </button>
                <div class="debug-help-text" hidden>
                    <?php echo esc_html( $description ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function conflict_finder_deactivate_plugins() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_plugin_tool' );

$active_plugins = get_option( 'active_plugins', [] );

// Never deactivate this plugin
$self = plugin_basename( __FILE__ );

// Never deactivate anything in support-wpfi folder
$active_plugins = array_filter( $active_plugins, function( $plugin ) use ( $self ) {

    // Keep this plugin active
    if ( $plugin === $self ) {
        return false;
    }

    // Keep all plugins inside support-wpfi/
    if ( strpos( $plugin, 'support-wpfi/' ) === 0 ) {
        return false;
    }

    return true;
});

// Always refresh the saved plugin snapshot
update_option( 'conflict_finder_saved_active_plugins', $active_plugins );

// Mark conflict mode active
update_option( 'conflict_finder_plugins_temporarily_deactivated', time() );

// Deactivate plugins (safe even if already inactive)
deactivate_plugins( $active_plugins );

wp_safe_redirect(
    admin_url( 'tools.php?page=' . $this->menu_slug . '&tool=plugins#wpfi-tab-plugin-tool' )
);
    exit;
}

public function conflict_finder_reactivate_plugins() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    check_admin_referer( 'conflict_finder_plugin_tool' );

    $plugins = get_option( 'conflict_finder_saved_active_plugins', [] );
    delete_option( 'conflict_finder_plugins_temporarily_deactivated' );

    if ( empty( $plugins ) ) {
wp_safe_redirect(
    admin_url( 'tools.php?page=' . $this->menu_slug . '&plugins_restored=1#wpfi-tab-plugin-tool' )
);
        exit;
    }

    foreach ( $plugins as $plugin ) {
        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
            activate_plugin( $plugin );
        }
    }

    delete_option( 'conflict_finder_saved_active_plugins' );

    wp_safe_redirect(
        admin_url( 'tools.php?page=wpfi-troubleshooting-tools#wpfi-tab-plugin-tool' )
    );
    exit;
}

    public function conflict_finder_save_settings() {
        check_admin_referer( 'conflict_finder_save_wp_debug_settings' );

        $settings = $_POST['settings'] ?? [];

        if ( ! empty( $settings['ENABLE_ALL'] ) ) {
            foreach ( [
                'WP_DEBUG_DISPLAY',
                'WP_DEBUG_LOG',
                'SCRIPT_DEBUG',
                'WP_DISABLE_FATAL_ERROR_HANDLER',
                'DISPLAY_ERRORS',
                'LOG_ERRORS',
                'ERROR_REPORTING',
            ] as $key ) {
                $settings[ $key ] = 1;
            }
        }

        update_option( $this->option_name, $settings );
        $this->conflict_finder_update_wp_config( $settings );

wp_safe_redirect(
    admin_url(
        'tools.php?page=' . $this->menu_slug . '&tool=debug#wpfi-tab-debug'
    )
);
exit;

    }

    private function conflict_finder_update_wp_config( $settings ) {
        $config_path = ABSPATH . 'wp-config.php';
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
        if ( ! is_writable( $config_path ) ) {
            return;
        }

$raw = file_get_contents( $config_path );

// HARD SAFETY GUARD — abort if file cannot be read safely
if ( $raw === false || trim( $raw ) === '' ) {
    return;
}

$config = str_replace( ["\r\n", "\r"], "\n", $raw );

        // Remove any previous plugin-managed block
        $config = preg_replace(
            '/\n*\s*' . preg_quote( $this->marker_start, '/' ) . '.*?' . preg_quote( $this->marker_end, '/' ) . '\s*\n*/s',
            "\n",
            $config
        );

        // Remove any existing debug-related defines/ini_set/error_reporting (clean + safe)
        $config = $this->conflict_finder_strip_existing_debug_config_lines( $config );

        // Build new plugin-managed block
        $block = "{$this->marker_start}\n";

        if ( empty( $settings['WP_DEBUG'] ) ) {
            $block .= "define('WP_DEBUG', false);\n";
        } else {
            $block .= "define('WP_DEBUG', true);\n";
            $block .= "define('WP_DEBUG_DISPLAY', " . ( empty( $settings['WP_DEBUG_DISPLAY'] ) ? 'false' : 'true' ) . ");\n";

            foreach ( [
                'WP_DEBUG_LOG' => "define('WP_DEBUG_LOG', true);\n",
                'SCRIPT_DEBUG' => "define('SCRIPT_DEBUG', true);\n",
                'WP_DISABLE_FATAL_ERROR_HANDLER' => "define('WP_DISABLE_FATAL_ERROR_HANDLER', true);\n",
                'DISPLAY_ERRORS' => "@ini_set('display_errors', 1);\n",
                'LOG_ERRORS' => "@ini_set('log_errors', 1);\n",
                'ERROR_REPORTING' => "error_reporting(E_ALL);\n",
            ] as $key => $line ) {
                if ( ! empty( $settings[ $key ] ) ) {
                    $block .= $line;
                }
            }
        }

        $block .= "{$this->marker_end}\n";

        // Insert block before the WordPress stop comment
        $stop = "/* That's all, stop editing! Happy publishing. */";

        $config = strpos( $config, $stop ) !== false
            ? preg_replace( '/' . preg_quote( $stop, '/' ) . '/', "\n\n{$block}\n{$stop}", $config, 1 )
            : $config . "\n\n{$block}";

file_put_contents(
    $config_path,
    rtrim( $config ) . "\n",
    LOCK_EX
);

    }

    /**
     * Removes existing debug-related config lines from wp-config.php content.
     * This intentionally avoids touching commented blocks and only strips active lines.
     */
    private function conflict_finder_strip_existing_debug_config_lines( $config ) {

        $constants = [
            'WP_DEBUG',
            'WP_DEBUG_DISPLAY',
            'WP_DEBUG_LOG',
            'SCRIPT_DEBUG',
            'WP_DISABLE_FATAL_ERROR_HANDLER',
        ];

        // Remove guarded define blocks like:
        // if ( ! defined('WP_DEBUG') ) { define('WP_DEBUG', false); }
        foreach ( $constants as $c ) {
            $config = preg_replace(
                '/if\s*\(\s*!\s*defined\s*\(\s*[\'"]' . preg_quote( $c, '/' ) . '[\'"]\s*\)\s*\)\s*\{\s*define\s*\(\s*[\'"]' . preg_quote( $c, '/' ) . '[\'"]\s*,.*?\)\s*;\s*\}/is',
                '',
                $config
            );
        }

        // Process line-by-line removals (simple defines, ini_set, etc.)
        $lines = explode( "\n", $config );
        $out   = [];

        $in_block_comment = false;

        foreach ( $lines as $line ) {
            $trim = ltrim( $line );

            // Handle /* */ comments safely
            if ( ! $in_block_comment && strpos( $trim, '/*' ) !== false && strpos( $trim, '*/' ) === false ) {
                $in_block_comment = true;
            }

            if ( $in_block_comment ) {
                $out[] = $line;
                if ( strpos( $trim, '*/' ) !== false ) {
                    $in_block_comment = false;
                }
                continue;
            }

            // Skip commented lines
            if ( preg_match( '/^\s*(\/\/|#)/', $trim ) ) {
                $out[] = $line;
                continue;
            }

            // Strip simple define('CONSTANT', ...);
            $removed = false;
            foreach ( $constants as $c ) {
                if ( preg_match(
                    '/^\s*define\s*\(\s*[\'"]' . preg_quote( $c, '/' ) . '[\'"]\s*,.*?\)\s*;\s*(?:\/\/.*)?$/i',
                    $line
                ) ) {
                    $removed = true;
                    break;
                }
            }
            if ( $removed ) {
                continue;
            }

            // ini_set('display_errors', ...)
            if ( preg_match( '/^\s*@?ini_set\s*\(\s*[\'"]display_errors[\'"]\s*,.*?\)\s*;/i', $line ) ) {
                continue;
            }

            // ini_set('log_errors', ...)
            if ( preg_match( '/^\s*@?ini_set\s*\(\s*[\'"]log_errors[\'"]\s*,.*?\)\s*;/i', $line ) ) {
                continue;
            }

            // error_reporting(E_ALL);
            if ( preg_match( '/^\s*error_reporting\s*\(\s*E_ALL\s*\)\s*;/i', $line ) ) {
                continue;
            }

            $out[] = $line;
        }

        // Normalize spacing
        $result = implode( "\n", $out );
        $result = preg_replace( "/\n{3,}/", "\n\n", $result );

        return $result;
    }

private function conflict_finder_css() {
    return '
:root {
    --accent: #d16aff;
    --accent-dark: #b954e6;
    --dark: #333;
}
.wpfi-theme-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 16px;
    align-items: stretch;
}
/* === Troubleshooting Status Rail === */
.wpfi-status-rail {
    display: flex;
    gap: 14px;
    padding: 5px 18px 18px 18px;
}

.wpfi-status-item {
    flex: 1;
    padding: 14px 16px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
    background: #fff;
    border: 1px solid #e5e7eb;
}

.wpfi-status-item strong {
    font-weight: 700;
}

.wpfi-status-item.ok {
    background: #ecfdf3;
    border-color: #86efac;
    color: #166534;
}

.wpfi-status-item.warning {
    background: #fff7ed;
    border-color: #fdba74;
    color: #9a3412;
}
.wpfi-theme-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.wpfi-theme-card.original {
    background:#fff7ed;
    border:1px solid #fdba74;
}

.wpfi-theme-card.active {
    border: 2px solid #86efac;
    background: #ecfdf3;
}

.wpfi-theme-card img {
    width: 100%;
    height: 255px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 10px;
}

.wpfi-theme-meta {
    margin-bottom: 10px;
}

.wpfi-theme-name {
    font-weight: 600;
    display: block;
}

.wpfi-theme-version {
    font-size: 12px;
    color: #666;
}

.wpfi-theme-badge {
    display: inline-block;
    font-size: 11px;
    padding: 3px 6px;
    border-radius: 4px;
    margin-top: 6px;
}

.wpfi-badge-original {
    background: #fb923c;
    color: #fff;
}

.wpfi-badge-active {
    background: #16a34a;
    color: #fff;
}
.wp-debug-wrap { max-width: 98%; }

.wp-debug-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 32px;
}

/* Sidebar */
.wp-debug-sidebar {
    background: #fff;
    margin-top: 23px;
    padding: 24px;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    border-top: 5px solid var(--accent);
}

.wp-debug-sidebar h3 {
    text-transform: uppercase;
    font-size: 13px;
    color: var(--accent);
}

.wp-debug-tip {
    margin-top: 16px;
    background: rgba(209,106,255,.1);
    border-left: 4px solid var(--accent);
    padding: 12px;
    border-radius: 6px;
    font-size: 13px;
}

/* Cards */
.debug-card {
    background: #fff;
    padding: 22px 24px;
    border-radius: 10px;
    margin-bottom: 24px;
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    border-left: 5px solid var(--accent);
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px 32px;
}

.debug-card h2,
.debug-card .description {
    grid-column: 1 / -1;
    margin-bottom: 0;
    margin-top: 0;
}

.debug-toggle {
    display: flex;
    align-items: center;
    gap: 14px;
}

.debug-toggle input { display: none; }

.debug-toggle .slider {
    width: 50px;
    height: 26px;
    background: #ddd;
    border-radius: 30px;
    position: relative;
}

.debug-toggle .slider::before {
    content: "";
    width: 22px;
    height: 22px;
    background: #fff;
    position: absolute;
    top: 2px;
    left: 2px;
    border-radius: 50%;
    transition: .25s;
}

.debug-toggle input:checked + .slider {
    background: var(--accent);
}

.debug-toggle input:checked + .slider::before {
    transform: translateX(24px);
}

.debug-card.debug-options span.label,
.debug-card span.label {
    font-size: 15px;
}

.wp-debug-notice {
    border-left-color: var(--accent) !important;
    border-radius: 10px;
}

.wp-debug-save.button-primary {
    background: var(--accent) !important;
    border-color: var(--accent) !important;
}

/* description toggle UI */
.debug-toggle-wrap {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.debug-help-toggle {
    background: none;
    border: none;
    padding: 0;
    font-size: 12px;
    color: var(--accent);
    cursor: pointer;
    text-align: left;
    line-height: 1.2;
}

.debug-help-toggle:hover {
    text-decoration: underline;
}

.debug-help-text {
    font-size: 15px;
    color: #555;
    line-height: 1.4;
    max-width: 520px;
    background: #f9f9f9;
    padding: 10px;
    border-radius: 8px;
    border: solid 1px #eee;
}

/* Save Settings button hover effect */
.wp-debug-save.button-primary:hover,
.wp-debug-save.button-primary:focus {
    background-color: #00D78B !important;
    border-color: #00D78B !important;
    color: #fff !important;
}

@media (max-width: 1100px) {
    .wp-debug-layout {
        grid-template-columns: 1fr;
    }
}

/* modal styles (non-invasive; only targets .wpdtt-* classes) */
.wpdtt-modal[hidden] { display: none !important; }
.wpdtt-modal {
    position: fixed;
    inset: 0;
    z-index: 100000;
}
.wpdtt-modal__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.55);
}
.wpdtt-modal__dialog {
    position: relative;
    width: min(980px, 92vw);
    max-height: 82vh;
    margin: 6vh auto 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.wpdtt-modal__header,
.wpdtt-modal__footer {
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #eee;
}
.wpdtt-modal__footer {
    border-bottom: 0;
    border-top: 1px solid #eee;
    justify-content: flex-end;
}
.wpdtt-modal__body {
    padding: 0;
    overflow: auto;
}
.wpdtt-debug-log-pre {
    margin: 0;
    padding: 16px;
    font-size: 12.5px;
    line-height: 1.45;
    background: #111;
    color: #0f0;
    white-space: pre-wrap;
    word-break: break-word;
    min-height: 220px;
}
button#conflict_finder_plugin_on {
    background: #d16aff;
    border: 0;
    font-size: 18px;
    border-radius: 5px;
    color:#fff;
}
button#conflict_finder_plugin_on:hover {
    background: #00D78B;
}
button#conflict_finder_plugin_off {
    background: #d16aff;
    border: 0;
    font-size: 18px;
    border-radius: 5px;
}
button#conflict_finder_plugin_off:hover {
    background: #00D78B;
}
';
}

private function conflict_finder_js() {
    return '
jQuery(function($){
    const wpDebug   = $("input[name=\"settings[WP_DEBUG]\"]");
    const enableAll = $("input[name=\"settings[ENABLE_ALL]\"]");
    const options   = $(".debug-options input[type=\"checkbox\"]");

    function syncEnableAll() {
        const allChecked = options.length === options.filter(":checked").length;
        enableAll.prop("checked", allChecked);
    }

    // Toggle reporting section enable/disable
    wpDebug.on("change", function(){
        $(".debug-options").toggleClass("disabled", !this.checked);
    });

    // Enable All → checks everything
    enableAll.on("change", function(){
        options.prop("checked", this.checked);
        if (this.checked) {
            wpDebug.prop("checked", true).trigger("change");
        }
    });

    // If any reporting option is toggled OFF, disable Enable All
    options.on("change", function(){
        if (!this.checked) {
            enableAll.prop("checked", false);
        } else {
            syncEnableAll();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".nav-tab[data-wpfi-tab]").forEach(function (tab) {
        tab.addEventListener("click", function (e) {
            e.preventDefault();
            const target = this.getAttribute("data-wpfi-tab");
            history.replaceState(null, "", "#" + target);
        });
    });
});

// Accordion-style help toggles
document.addEventListener("click", function(e){
    const btn = e.target.closest(".debug-help-toggle");
    if (!btn) return;

    document.querySelectorAll(".debug-help-toggle").forEach(function(otherBtn){
        if (otherBtn !== btn) {
            otherBtn.setAttribute("aria-expanded", "false");
            const otherText = otherBtn.nextElementSibling;
            if (otherText && otherText.classList.contains("debug-help-text")) {
                otherText.hidden = true;
            }
        }
    });

    const expanded = btn.getAttribute("aria-expanded") === "true";
    btn.setAttribute("aria-expanded", expanded ? "false" : "true");

    const text = btn.nextElementSibling;
    if (text && text.classList.contains("debug-help-text")) {
        text.hidden = expanded;
    }
});

/* Debug log modal open/close + AJAX load */
jQuery(function($){
    const modal = document.getElementById("wpdtt-debug-log-modal");
    const pre   = document.getElementById("wpdtt-debug-log-pre");

    function openModal() {
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute("aria-hidden", "false");
        pre.textContent = "Loading…";

        const cfg = window.WPFI_DEBUG_LOG || {};
        $.post(cfg.ajaxUrl, {
            action: "conflict_finder_get_debug_log",
            nonce: cfg.nonce
        })
        .done(function(resp){
            if (resp && resp.success && resp.data && typeof resp.data.content === "string") {
                pre.textContent = resp.data.content;
            } else if (resp && resp.data && resp.data.message) {
                pre.textContent = resp.data.message;
            } else {
                pre.textContent = "Unable to load debug.log.";
            }
        })
        .fail(function(){
            pre.textContent = "Unable to load debug.log.";
        });
    }

    function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
    }

    $(document).on("click", "#wpdtt-view-debug-log", function(){
        openModal();
    });

    $(document).on("click", "[data-wpdtt-close=\"1\"]", function(){
        closeModal();
    });

    $(document).on("click", "#wpdtt-clear-debug-log", function(){
        if (!confirm("Are you sure you want to clear the debug log?")) {
            return;
        }

        const cfg = window.WPFI_DEBUG_LOG || {};
        pre.textContent = "Clearing log…";

        $.post(cfg.ajaxUrl, {
            action: "conflict_finder_clear_debug_log",
            nonce: cfg.nonce
        })
        .done(function(resp){
            if (resp && resp.success) {
                pre.textContent = "debug.log has been cleared.";
            } else if (resp && resp.data && resp.data.message) {
                pre.textContent = resp.data.message;
            } else {
                pre.textContent = "Failed to clear debug.log.";
            }
        })
        .fail(function(){
            pre.textContent = "Failed to clear debug.log.";
        });
    });

    document.addEventListener("keydown", function(e){
        if (e.key === "Escape" && modal && !modal.hidden) {
            closeModal();
        }
    });
});

// Tabs logic
document.addEventListener("click", function(e){
    const tab = e.target.closest(".nav-tab[data-wpfi-tab]");
    if (!tab) return;

    e.preventDefault();

    const targetId = tab.getAttribute("data-wpfi-tab");
    const targetEl = document.getElementById(targetId);
    if (!targetEl) return;

    document.querySelectorAll(".nav-tab-wrapper .nav-tab").forEach(function(t){
        t.classList.remove("nav-tab-active");
    });
    tab.classList.add("nav-tab-active");

    document.querySelectorAll(".wpfi-tab-panel").forEach(function(p){
        p.style.display = "none";
    });
    targetEl.style.display = "block";

    window.location.hash = tab.getAttribute("href");
});

document.addEventListener("DOMContentLoaded", function(){
    const hash = window.location.hash;
    if (!hash) return;

    const link = document.querySelector(".nav-tab-wrapper .nav-tab[href=\"" + hash + "\"]");
    if (link) link.click();
});
';
}

    public function conflict_finder_ajax_get_debug_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
        }

        check_ajax_referer( 'conflict_finder_debug_log', 'nonce' );

        $log = WP_CONTENT_DIR . '/debug.log';

        if ( ! file_exists( $log ) ) {
            wp_send_json_success( [ 'content' => "debug.log not found at: {$log}" ] );
        }

        $content = @file_get_contents( $log );

        if ( $content === false ) {
            wp_send_json_error( [ 'message' => 'Unable to read debug.log.' ], 500 );
        }

        wp_send_json_success( [ 'content' => $content ] );
    }

    public function conflict_finder_download_debug_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        check_admin_referer( 'conflict_finder_download_debug_log' );

        $log = WP_CONTENT_DIR . '/debug.log';

        if ( ! file_exists( $log ) ) {
            wp_die( 'debug.log not found.' );
        }

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=debug.log' );
        header( 'Content-Length: ' . filesize( $log ) );
        header( 'Cache-Control: private, max-age=0, no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        readfile( $log );
        exit;
    }
}

new conflict_finder_wp_debug_toggle_tool();