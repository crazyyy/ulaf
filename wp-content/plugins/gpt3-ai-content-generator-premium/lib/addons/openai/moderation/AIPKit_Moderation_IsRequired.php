<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/lib/addons/openai/moderation/AIPKit_Moderation_IsRequired.php
// Status: NEW FILE

namespace WPAICG\Lib\Addons\OpenAI\Moderation;

use WPAICG\aipkit_dashboard;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Moderation_IsRequired
 *
 * Checks if OpenAI Moderation is required based on plan and bot settings.
 */
class AIPKit_Moderation_IsRequired {

    /**
     * Checks if OpenAI Moderation is currently active AND enabled for the bot.
     *
     * @param array $bot_settings Bot-level settings for the current chatbot.
     * @return bool True if moderation should be applied, false otherwise.
     */
    public static function check(array $bot_settings = []): bool {
        // Ensure dashboard class is available
        if (!class_exists('\WPAICG\aipkit_dashboard')) {
            $dashboard_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_dashboard.php';
            if (file_exists($dashboard_path)) {
                require_once $dashboard_path;
            } else {
                 return false; // Fail safe
            }
        }

        // 1. Check Pro Plan status
        if (!aipkit_dashboard::is_pro_plan()) {
            return false;
        }

        // 2. Check if enabled in Chatbot Settings -> Security
        $enabled_value = $bot_settings['openai_moderation_enabled'] ?? '0';
        $enabled_in_settings = ($enabled_value === '1' || $enabled_value === 1 || $enabled_value === true);

        return $enabled_in_settings;
    }
}
