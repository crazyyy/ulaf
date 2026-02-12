<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$stats = cf7sr_get_spam_stats();

?>

<div class="cf7sr-stats">
    <div class="cf7sr-stats-dashboard">
        <h2>
            <span class="dashicons dashicons-shield-alt"></span>
            <?php esc_html_e( 'Spam Protection Statistics', 'contact-form-7-simple-recaptcha' ); ?>
        </h2>

        <div class="cf7sr-stats-grid">
            <div class="cf7sr-stat-card cf7sr-stat-primary">
                <div class="cf7sr-stat-icon">
                    <span class="dashicons dashicons-shield"></span>
                </div>
                <div class="cf7sr-stat-content">
                    <div class="cf7sr-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_blocked'] ) ); ?></div>
                    <div class="cf7sr-stat-label"><?php esc_html_e( 'Total Spam Blocked', 'contact-form-7-simple-recaptcha' ); ?></div>
                </div>
            </div>

            <div class="cf7sr-stat-card cf7sr-stat-success">
                <div class="cf7sr-stat-icon">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <div class="cf7sr-stat-content">
                    <div class="cf7sr-stat-value"><?php echo esc_html( number_format_i18n( $stats['blocked_today'] ) ); ?></div>
                    <div class="cf7sr-stat-label"><?php esc_html_e( 'Blocked Today', 'contact-form-7-simple-recaptcha' ); ?></div>
                </div>
            </div>

            <div class="cf7sr-stat-card cf7sr-stat-info">
                <div class="cf7sr-stat-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <div class="cf7sr-stat-content">
                    <div class="cf7sr-stat-value"><?php echo esc_html( number_format_i18n( $stats['blocked_this_month'] ) ); ?></div>
                    <div class="cf7sr-stat-label"><?php esc_html_e( 'Blocked This Month', 'contact-form-7-simple-recaptcha' ); ?></div>
                </div>
            </div>
        </div>

        <div class="cf7sr-stats-breakdown">
            <h3><?php esc_html_e( 'Blocked by CAPTCHA Type', 'contact-form-7-simple-recaptcha' ); ?></h3>
            <div class="cf7sr-captcha-stats">
                <div class="cf7sr-captcha-stat">
                    <span class="cf7sr-captcha-label">
                        <span class="dashicons dashicons-google"></span>
                        <?php esc_html_e( 'Google reCAPTCHA V2', 'contact-form-7-simple-recaptcha' ); ?>
                    </span>
                    <span class="cf7sr-captcha-count"><?php echo esc_html( number_format_i18n( $stats['by_type']['recaptcha'] ) ); ?></span>
                </div>
                <div class="cf7sr-captcha-stat">
                    <span class="cf7sr-captcha-label">
                        <span class="dashicons dashicons-google"></span>
                        <?php esc_html_e( 'Google reCAPTCHA V3', 'contact-form-7-simple-recaptcha' ); ?>
                    </span>
                    <span class="cf7sr-captcha-count"><?php echo esc_html( number_format_i18n( $stats['by_type']['recaptcha-v3'] ) ); ?></span>
                </div>
                <div class="cf7sr-captcha-stat">
                    <span class="cf7sr-captcha-label">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e( 'hCaptcha', 'contact-form-7-simple-recaptcha' ); ?>
                    </span>
                    <span class="cf7sr-captcha-count"><?php echo esc_html( number_format_i18n( $stats['by_type']['hcaptcha'] ) ); ?></span>
                </div>
                <div class="cf7sr-captcha-stat">
                    <span class="cf7sr-captcha-label">
                        <span class="dashicons dashicons-cloud"></span>
                        <?php esc_html_e( 'Cloudflare Turnstile', 'contact-form-7-simple-recaptcha' ); ?>
                    </span>
                    <span class="cf7sr-captcha-count"><?php echo esc_html( number_format_i18n( $stats['by_type']['turnstile'] ) ); ?></span>
                </div>
            </div>
        </div>

        <div class="cf7sr-stats-actions">
            <form method="post" style="display: inline;">
                <?php wp_nonce_field( 'cf7sr_reset_stats', 'cf7sr_stats_nonce' ); ?>
                <button type="submit" name="cf7sr_reset_stats" class="button button-secondary"
                        onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all statistics? This cannot be undone.', 'contact-form-7-simple-recaptcha' ) ); ?>');">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e( 'Reset Statistics', 'contact-form-7-simple-recaptcha' ); ?>
                </button>
            </form>
        </div>
    </div>
    <div class="cf7sr-upgrade-banner">
        <div class="cf7sr-upgrade-container">

            <div class="cf7sr-header">
                <h2>
                    <span class="dashicons dashicons-performance"></span>
                    <?php esc_html_e('Unlock the Full Power of CF7 Captcha Pro', 'contact-form-7-simple-recaptcha'); ?>
                </h2>
                <p class="cf7sr-subtitle">
                    <?php esc_html_e('Stop spam completely, recover abandoned leads, and automate your workflows.', 'contact-form-7-simple-recaptcha'); ?>
                </p>
            </div>

            <div class="cf7sr-grid">

                <div class="cf7sr-feature-card">
                    <h3 class="cf7sr-card-title"><?php esc_html_e('Multi-Layer Spam Defense', 'contact-form-7-simple-recaptcha'); ?></h3>
                    <p class="cf7sr-card-text"><?php esc_html_e('Block 99.9% of spam with 6 intelligent protection methods.', 'contact-form-7-simple-recaptcha'); ?></p>
                    <ul class="cf7sr-list">
                        <li><?php esc_html_e('3 CAPTCHA systems (reCAPTCHA, hCaptcha, Turnstile)', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Advanced honeypot', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Rate and Time Limit Verification', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Geographic & IP filtering', 'contact-form-7-simple-recaptcha'); ?></li>
                    </ul>
                </div>

                <div class="cf7sr-feature-card">
                    <h3 class="cf7sr-card-title"><?php esc_html_e('Powerful Integrations', 'contact-form-7-simple-recaptcha'); ?></h3>
                    <p class="cf7sr-card-text"><?php esc_html_e('Connect your forms to any tool and automate workflows.', 'contact-form-7-simple-recaptcha'); ?></p>
                    <ul class="cf7sr-list">
                        <li><?php esc_html_e('Database storage for all leads', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Connect instantly to Zapier, Make.com (Integromat), or Pabbly to reach thousands of apps.', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Sync leads directly to HubSpot, Salesforce, GoHighLevel, or Zoho CRM.', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Mailchimp & Twilio SMS', 'contact-form-7-simple-recaptcha'); ?></li>
                    </ul>
                </div>

                <div class="cf7sr-feature-card">
                    <h3 class="cf7sr-card-title"><?php esc_html_e('Lead Recovery System', 'contact-form-7-simple-recaptcha'); ?></h3>
                    <p class="cf7sr-card-text"><?php esc_html_e('Capture form data in real-time, even before users submit.', 'contact-form-7-simple-recaptcha'); ?></p>
                    <ul class="cf7sr-list">
                        <li><?php esc_html_e('Real-time data capture as users type', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Track abandoned forms', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Export to CSV, Excel, or JSON', 'contact-form-7-simple-recaptcha'); ?></li>
                    </ul>
                </div>

                <div class="cf7sr-feature-card">
                    <h3 class="cf7sr-card-title"><?php esc_html_e('Smart Features', 'contact-form-7-simple-recaptcha'); ?></h3>
                    <p class="cf7sr-card-text"><?php esc_html_e('Mobile-friendly and optimized for performance.', 'contact-form-7-simple-recaptcha'); ?></p>
                    <ul class="cf7sr-list">
                        <li><?php esc_html_e('Auto-responsive CAPTCHA', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Custom redirect URLs', 'contact-form-7-simple-recaptcha'); ?></li>
                        <li><?php esc_html_e('Lazy-loading for speed', 'contact-form-7-simple-recaptcha'); ?></li>
                    </ul>
                </div>

            </div>

            <div class="cf7sr-cta-section">
                <a target="_blank" href="https://lukasapps.de/wordpress/plugins/cf7-captcha-pro/" class="cf7sr-btn-large">
                    <?php esc_html_e('Unlock All Pro Features Now', 'contact-form-7-simple-recaptcha'); ?>
                </a>
            </div>

        </div>
    </div>
</div>

