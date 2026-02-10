<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/lib/addons/consent-compliance/AIPKit_Consent_IsActiveChecker.php
// Status: NEW FILE

namespace WPAICG\Lib\Addons\ConsentCompliance;

use WPAICG\aipkit_dashboard;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Consent_IsActiveChecker
 *
 * Checks if Consent Compliance is available.
 */
class AIPKit_Consent_IsActiveChecker {

    const ADDON_KEY = 'consent_compliance';

    /**
     * Checks if Consent Compliance is available for the current plan.
     *
     * @return bool True if the addon toggle is active, false otherwise.
     */
    public static function check(): bool {
        // Ensure the dashboard class is available for checking addon status
        if (!class_exists('\\WPAICG\\aipkit_dashboard')) {
            $dashboard_path = defined('WPAICG_PLUGIN_DIR') ? WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_dashboard.php' : null;
            if ($dashboard_path && file_exists($dashboard_path)) {
                require_once $dashboard_path;
            } else {
                 return false; // Fail safe: assume inactive if dependencies missing
            }
        }

        // Consent Compliance is always available for Pro builds.
        return aipkit_dashboard::is_pro_plan();
    }
}
