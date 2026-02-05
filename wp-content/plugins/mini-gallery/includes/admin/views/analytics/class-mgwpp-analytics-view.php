<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Analytics_View
{
    public static function render()
    {
        $stats = MGWPP_Analytics_Manager::get_stats();
        $ga4_id = get_option('mgwpp_ga4_measurement_id', '');
        ?>
        <div class="wrap mgwpp-analytics-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Analytics Dashboard', 'mini-gallery'); ?></h1>
            <hr class="wp-header-end">

            <div class="mgwpp-analytics-grid">
                <!-- Summary Cards -->
                <div class="mgwpp-stat-card">
                    <div class="mgwpp-stat-label"><?php esc_html_e('Gallery Views (Last 30 Days)', 'mini-gallery'); ?></div>
                    <div class="mgwpp-stat-value"><?php echo number_format($stats['views']); ?></div>
                    <div class="mgwpp-stat-icon dashicons dashicons-visibility"></div>
                </div>

                <div class="mgwpp-stat-card">
                    <div class="mgwpp-stat-label"><?php esc_html_e('CTA Clicks (Total)', 'mini-gallery'); ?></div>
                    <div class="mgwpp-stat-value"><?php echo number_format($stats['cta_clicks']); ?></div>
                    <div class="mgwpp-stat-icon dashicons dashicons-external"></div>
                </div>

                <div class="mgwpp-stat-card">
                    <div class="mgwpp-stat-label"><?php esc_html_e('CTA Submissions (Total)', 'mini-gallery'); ?></div>
                    <div class="mgwpp-stat-value"><?php echo number_format($stats['cta_submissions']); ?></div>
                    <div class="mgwpp-stat-icon dashicons dashicons-email"></div>
                </div>
            </div>

            <div class="mgwpp-analytics-content">
                <div class="mgwpp-settings-box">
                    <h3><?php esc_html_e('Google Analytics 4 Configuration', 'mini-gallery'); ?></h3>
                    <p class="description">
                        <?php esc_html_e('Enter your GA4 Measurement ID (e.g., G-XXXXXXXXXX) to track events in Google Analytics.', 'mini-gallery'); ?>
                    </p>
                    <form method="post" action="options.php">
                        <?php settings_fields('mgwpp_analytics_settings'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="mgwpp_ga4_measurement_id"><?php esc_html_e('Measurement ID', 'mini-gallery'); ?></label></th>
                                <td>
                                    <input name="mgwpp_ga4_measurement_id" type="text" id="mgwpp_ga4_measurement_id" value="<?php echo esc_attr($ga4_id); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .mgwpp-analytics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .mgwpp-stat-card {
                background: #fff;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                position: relative;
                overflow: hidden;
            }
            .mgwpp-stat-label {
                font-size: 14px;
                color: #64748b;
                margin-bottom: 8px;
            }
            .mgwpp-stat-value {
                font-size: 32px;
                font-weight: 700;
                color: #1e293b;
            }
            .mgwpp-stat-icon {
                position: absolute;
                top: 20px;
                right: 20px;
                font-size: 40px;
                width: 40px;
                height: 40px;
                color: rgba(29, 193, 220, 0.1);
            }
            .mgwpp-settings-box {
                background: #fff;
                padding: 24px;
                border-radius: 12px;
                margin-top: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            .mgwpp-dark-mode .mgwpp-stat-card,
            .mgwpp-dark-mode .mgwpp-settings-box {
                background: #1e1e1e;
                color: #f5f5f5;
            }
            .mgwpp-dark-mode .mgwpp-stat-value {
                color: #f5f5f5;
            }
            .mgwpp-dark-mode .mgwpp-stat-label {
                color: #a0a0a0;
            }
        </style>
        <?php
    }
}
