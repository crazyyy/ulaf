<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/lib/addons/openai/moderation/AIPKit_Moderation_MessageProvider.php
// Status: NEW FILE

namespace WPAICG\Lib\Addons\OpenAI\Moderation;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Moderation_MessageProvider
 *
 * Provides the custom notification message for flagged content.
 */
class AIPKit_Moderation_MessageProvider {

    /**
     * Gets the custom notification message for flagged content from bot settings.
     *
     * @param array $bot_settings Bot-level settings for the current chatbot.
     * @return string The message to display.
     */
    public static function get(array $bot_settings = []): string {
        $message = $bot_settings['openai_moderation_message'] ?? __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator');

        // Fallback if the message is empty
        if (empty(trim($message))) {
            $message = __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator');
        }
        return $message;
    }
}
