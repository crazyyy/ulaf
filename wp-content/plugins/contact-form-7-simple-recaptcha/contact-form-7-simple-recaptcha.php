<?php
/*
Plugin Name: Contact Form 7 Captcha
Description: Add reCAPTCHA V2, hCAPTCHA or Cloudflare Turnstile CAPTCHA to Contact Form 7 using [cf7sr-recaptcha], [cf7sr-hcaptcha] or [cf7sr-turnstile] shortcode
Version: 0.1.7
Author: 247wd
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: contact-form-7-simple-recaptcha
Requires Plugins: contact-form-7
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CF7SR_VERSION', '0.1.7' );
define( 'CF7SR_PLUGIN', __FILE__ );
define( 'CF7SR_PLUGIN_BASENAME', plugin_basename( CF7SR_PLUGIN ) );
define( 'CF7SR_PLUGIN_NAME', untrailingslashit( dirname( CF7SR_PLUGIN_BASENAME ) ) );
define( 'CF7SR_PLUGIN_DIR', untrailingslashit( dirname( CF7SR_PLUGIN ) ) );
define( 'CF7SR_PLUGIN_URL', untrailingslashit( plugin_dir_url( CF7SR_PLUGIN ) ) );

function cf7sr_get_ip() {
    if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    $validated_ip = filter_var( trim( $ip ), FILTER_VALIDATE_IP );
    return $validated_ip ? $validated_ip : '';
}

function cf7sr_trigger_spam($message) {
    add_filter('wpcf7_spam', '__return_true');

    add_filter('wpcf7_display_message', function($default_msg, $status) use ($message) {
        if ($status === 'spam' && !empty($message)) {
            return $message;
        }
        return $default_msg;
    }, 10, 2);
}

$cf7sr_required_files = array(
    CF7SR_PLUGIN_DIR . '/includes/languages.php',
    CF7SR_PLUGIN_DIR . '/includes/stats.php',
    CF7SR_PLUGIN_DIR . '/includes/recaptcha.php',
    CF7SR_PLUGIN_DIR . '/includes/recaptcha-v3.php',
    CF7SR_PLUGIN_DIR . '/includes/hcaptcha.php',
    CF7SR_PLUGIN_DIR . '/includes/turnstile.php',
    CF7SR_PLUGIN_DIR . '/includes/insights.php'
);

foreach ( $cf7sr_required_files as $file ) {
    if ( file_exists( $file ) ) {
        continue;
    }
    if ( is_admin() ) {
        add_action( 'admin_notices', function() use ( $file ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Contact Form 7 Captcha error: Files are missing. Please reinstall the plugin.', 'contact-form-7-simple-recaptcha' ) . '</p></div>';
        });
    }
    return;
}

add_filter( 'wpcf7_form_elements', 'do_shortcode' );
foreach ( $cf7sr_required_files as $file ) {
    require_once $file;
}

function cf7sr_load_admin_css() {
    $page = ! empty( $_GET['page'] ) ? $_GET['page'] : '';

    if ( 'cf7sr-edit' == $page ) {
        wp_enqueue_style( 'cf7sr-admin-css', CF7SR_PLUGIN_URL . '/assets/css/admin.css', array(), CF7SR_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'cf7sr_load_admin_css' );

function cf7sr_add_action_links($links) {
    array_unshift($links , '<a href="' . admin_url( 'options-general.php?page=cf7sr-edit' ) . '">Settings</a>');
    array_unshift($links , '<a target="_blank" style="color: #df7128; font-weight: 700;" href="https://lukasapps.de/wordpress/plugins/cf7-captcha-pro/">Explore PRO Features</a>');
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cf7sr_add_action_links', 10, 2 );

function cf7sr_activation_notice() {
    $cf7sr_notice_017 = get_option('cf7sr_notice_017');

    if ( isset( $_GET['cf7sr-notice-017'] ) && '1' === $_GET['cf7sr-notice-017'] ) {
        update_option( 'cf7sr_notice_017', 1 );
        return;
    }

    if ( empty( $cf7sr_notice_017 ) ) { ?>
        <div class="notice notice-success" style="position: relative; padding: 10px 20px; border-left-color: #df7128;">
            <p style="margin: 0 0 10px 0;">
                <strong><?php esc_html_e( 'Contact Form 7 Captcha updated:', 'contact-form-7-simple-recaptcha' ); ?></strong>
                <?php esc_html_e( 'Added Google reCAPTCHA v3 Invisible, Submission Insights and Spam Protection Statistics!', 'contact-form-7-simple-recaptcha' ); ?>
            </p>
            <p style="margin: 0;">
                <?php esc_html_e( 'Upgrade to Pro for 6-layer spam defense and Lead Recovery. Automate with CRM sync, Zapier, and SMS alerts!', 'contact-form-7-simple-recaptcha' ); ?>
                <a target="_blank" style="font-weight: bold; color: #df7128; margin: 0 15px;" href="https://lukasapps.de/wordpress/plugins/cf7-captcha-pro/">
                    <?php esc_html_e( 'Get CF7 Captcha Pro →', 'contact-form-7-simple-recaptcha' ); ?>
                </a>

                <a href="<?php echo esc_url( add_query_arg( 'cf7sr-notice-017', '1' ) ); ?>"
                   style="font-size: 12px; color: #666; text-decoration: underline;">
                    <?php esc_html_e( 'Dismiss this notice', 'contact-form-7-simple-recaptcha' ); ?>
                </a>
            </p>
        </div>
    <?php }
}
add_action( 'admin_notices', 'cf7sr_activation_notice' );

function cf7sr_adminhtml() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'contact-form-7-simple-recaptcha' ) );
    }

    if ( ! class_exists( 'WPCF7_Submission' ) ) {
        echo '<p>' . wp_kses(
                __( 'To use <strong>Contact Form 7 Captcha</strong> please install or update <strong>Contact Form 7</strong> plugin as current version is not supported.', 'contact-form-7-simple-recaptcha' ),
                array( 'strong' => array() )
        ) . '</p>';
        return;
    }

    $tabs = array(
            'stats'       => 'Statistics',
            'recaptcha'   => 'Google reCaptcha v2',
            'recaptcha-v3'   => 'Google reCaptcha v3',
            'hcaptcha'    => 'hCaptcha',
            'turnstile'   => 'Cloudflare Turnstile Captcha',
            'insights'    => 'Insights',
    );

    $tab = ! empty( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? $_GET['tab'] : 'stats';

    ?>
    <div class="wrap">
        <nav class="nav-tab-wrapper">
            <?php foreach ( $tabs as $tabKey => $tabLabel ) { ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cf7sr-edit&tab=' . $tabKey ) ); ?>"
                   class="nav-tab <?php echo $tab == $tabKey ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html( $tabLabel ); ?>
                </a>
            <?php } ?>
        </nav>
        <div class="cf7sr-content">
            <?php
            $admin_tab_file = CF7SR_PLUGIN_DIR . '/includes/admin-' . $tab . '.php';
            if ( file_exists( $admin_tab_file ) ) {
                require_once $admin_tab_file;
            } else {
                echo '<p>' . esc_html__( 'Error: Admin tab file missing.', 'contact-form-7-simple-recaptcha' ) . '</p>';
            }
            ?>
        </div>
    </div>
    <script>
        var cf7srMsg = document.querySelector('.cf7sr-msg');
        if (cf7srMsg) {
            setTimeout(function() {
                cf7srMsg.remove();
            }, 3000);
        }
    </script>
    <?php
}

function cf7sr_addmenu() {
    add_menu_page(
        'CF7 Captcha',
        'CF7 Captcha',
        'manage_options',
        'cf7sr-edit',
        'cf7sr_adminhtml',
        'dashicons-shield-alt',
        30
    );
}
add_action('admin_menu', 'cf7sr_addmenu');
