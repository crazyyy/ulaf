<?php
/**
 * Admin Left Sidebar Template
 *
 * Displays the main navigation menu including:
 * - Plugin logo
 * - Main navigation sections
 * - Feature tabs
 * - Contact information
 *
 * @package DiveWP
 * @since 1.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local variables only

// Define image paths and alt text
$logo_path = DIVEWP_PLUGIN_URL . 'assets/images/diveWP.svg';
$logo_alt = esc_attr__('DiveWP', 'divewp-boost-site-performance');

// Validate logo file exists
if (!file_exists(DIVEWP_PLUGIN_DIR . 'assets/images/diveWP.svg')) {
    if (defined('DIVEWP_DEBUG') && DIVEWP_DEBUG) {
        divewp_debug_log('Logo file not found at ' . $logo_path, 'error');
    }
    $logo_path = DIVEWP_PLUGIN_URL . 'assets/images/D.svg';
}
?>
<div class="divewp-tabs-column">
    <div class="divewp-logo-container">
        <?php
        printf(
            '<img src="%s" alt="%s" class="divewp-logo" width="120" height="50">',
            esc_url($logo_path),
            esc_attr($logo_alt)
        );
        ?>
    </div>
    <nav class="divewp-navigation">
        <!-- Main Section -->
        <div class="nav-section">
            <div class="nav-section-header"><?php esc_html_e('Main', 'divewp-boost-site-performance'); ?></div>
            <ul class="divewp-tabs">
                <li data-tab="welcome" data-feature="dashboard" class="active">
                    <i class="dashicons dashicons-dashboard"></i>
                    <?php esc_html_e('Dashboard', 'divewp-boost-site-performance'); ?>
                </li>
            </ul>
        </div>

        <!-- Utilities Section (moved above Analysis) -->
        <div class="nav-section">
            <div class="nav-section-header"><?php esc_html_e('Utilities & Monitoring', 'divewp-boost-site-performance'); ?></div>
            <ul class="divewp-tabs">
                <li data-tab="hosting" data-feature="hosting">
                    <i class="dashicons dashicons-admin-site-alt3"></i>
                    <?php esc_html_e('Hosting Benchmark', 'divewp-boost-site-performance'); ?>
                    <span class="new-feature-highlight-pill" data-feature-id="hosting"><?php esc_html_e('NEW', 'divewp-boost-site-performance'); ?></span>
                </li>
                <li data-tab="cron-jobs" data-feature="cron-jobs">
                    <i class="dashicons dashicons-clock"></i>
                    <?php esc_html_e('Cron Job Manager', 'divewp-boost-site-performance'); ?>
                    <span class="new-feature-highlight-pill" data-feature-id="cron-jobs"><?php esc_html_e('NEW', 'divewp-boost-site-performance'); ?></span>
                </li>
                <li data-tab="user-events" data-feature="user-events">
                    <i class="dashicons dashicons-groups"></i>
                    <?php esc_html_e('User Events', 'divewp-boost-site-performance'); ?>
                    <span class="new-feature-highlight-pill" data-feature-id="user-events"><?php esc_html_e('NEW', 'divewp-boost-site-performance'); ?></span>
                </li>
                <li data-tab="email" data-feature="email-insights">
                    <i class="dashicons dashicons-email"></i>
                    <?php esc_html_e('Email Communications', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="ai-capabilities" data-feature="ai-capabilities">
                    <i class="dashicons dashicons-rest-api"></i>
                    <?php esc_html_e('AI Capabilities', 'divewp-boost-site-performance'); ?>
                    <span class="new-feature-highlight-pill" data-feature-id="ai-capabilities"><?php esc_html_e('NEW', 'divewp-boost-site-performance'); ?></span>
                </li>
            </ul>
        </div>

        <!-- Analysis Section (moved below Utilities) -->
        <div class="nav-section">
            <div class="nav-section-header"><?php esc_html_e('Analysis', 'divewp-boost-site-performance'); ?></div>
            <ul class="divewp-tabs">
                <li data-tab="server-new" data-feature="server-insights-new">
                    <i class="dashicons dashicons-chart-area"></i>
                    <?php esc_html_e('Server Insights', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="performance-checks" data-feature="performance-checks">
                    <i class="dashicons dashicons-performance"></i>
                    <?php esc_html_e('Performance Checks', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="db-insights" data-feature="db-insights">
                    <i class="dashicons dashicons-database"></i>
                    <?php esc_html_e('Database Insights', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="security-new" data-feature="security">
                    <i class="dashicons dashicons-shield"></i>
                    <?php esc_html_e('Security Insights', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="theme-builder" data-feature="theme-builder">
                    <i class="dashicons dashicons-admin-appearance"></i>
                    <?php esc_html_e('Theme & Builder', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="woocommerce-best-practices" data-feature="woocommerce-best-practices">
                    <i class="dashicons dashicons-cart"></i>
                    <?php esc_html_e('WooCommerce Insights', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="seo-optimization" data-feature="seo-optimization">
                    <i class="dashicons dashicons-chart-line"></i>
                    <?php esc_html_e('SEO Optimization', 'divewp-boost-site-performance'); ?>
                </li>
            </ul>
        </div>

        <!-- Coming Soon Section -->
        <div class="nav-section">
            <div class="nav-section-header">
                <?php esc_html_e('Coming Soon', 'divewp-boost-site-performance'); ?>
                <span class="new-feature-coming-soon-pill"><?php esc_html_e('in development', 'divewp-boost-site-performance'); ?></span>
            </div>
            <ul class="divewp-tabs">
                <li data-tab="updates-management" data-feature="updates-management" class="disabled">
                    <i class="dashicons dashicons-update"></i>
                    <?php esc_html_e('Updates Management', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="essential-plugins" data-feature="essential-plugins" class="disabled">
                    <i class="dashicons dashicons-admin-plugins"></i>
                    <?php esc_html_e('Essential Plugins', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="wordpress-learning" data-feature="wordpress-learning" class="disabled">
                    <i class="dashicons dashicons-welcome-learn-more"></i>
                    <?php esc_html_e('Learn WordPress - courses, guides, etc.', 'divewp-boost-site-performance'); ?>
                </li>
                <li data-tab="backups" data-feature="backups" class="disabled">
                    <i class="dashicons dashicons-backup"></i>
                    <?php esc_html_e('Backups and more...', 'divewp-boost-site-performance'); ?>
                </li>
            </ul>
        </div>
        <div class="nav-section request-feature-section">
            <div class="contact-header">
                <?php esc_html_e('Request Feature', 'divewp-boost-site-performance'); ?>
                <span class="feature-pill info-pill"><?php esc_html_e('feedback', 'divewp-boost-site-performance'); ?></span>
            </div>
            <div class="contact-buttons">
                <a href="mailto:oleg.petrov@theweb.bg" class="contact-button">
                    <i class="dashicons dashicons-email-alt"></i>
                    <?php esc_html_e('Email', 'divewp-boost-site-performance'); ?>
                </a>
                <a href="https://www.facebook.com/ReplikonBG" target="_blank" class="contact-button">
                    <i class="dashicons dashicons-facebook"></i>
                    <?php esc_html_e('Facebook', 'divewp-boost-site-performance'); ?>
                </a>
            </div>
        </div>
    </nav>
</div>