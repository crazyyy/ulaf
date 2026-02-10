<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/lib/addons/class-aipkit-openai-moderation.php
// Status: MODIFIED (Facade)

namespace WPAICG\Lib\Addons;

// Use the new component classes
use WPAICG\Lib\Addons\OpenAI\Moderation\AIPKit_Moderation_IsRequired;
use WPAICG\Lib\Addons\OpenAI\Moderation\AIPKit_Moderation_MessageProvider;
use WPAICG\Lib\Addons\OpenAI\Moderation\AIPKit_Moderation_Executor;
use WP_Error; // Keep for type hinting if any internal method would return it, though static public ones won't.

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_OpenAI_Moderation (Facade)
 *
 * Facade class that delegates OpenAI Moderation checks and execution
 * to specialized component classes.
 * Preserves the original static public API.
 */
class AIPKit_OpenAI_Moderation {

    // Constants remain here as part of the Facade's public contract/definition
    const ADDON_KEY = 'openai_moderation';

    /**
     * Checks if OpenAI Moderation is currently active AND enabled for the bot.
     * Delegates to AIPKit_Moderation_IsRequired::check().
     *
     * @param array $bot_settings Bot-level settings for the current chatbot.
     * @return bool True if moderation should be applied, false otherwise.
     */
    public static function is_required(array $bot_settings = []): bool {
        // Ensure component is loaded (should be by wpaicg__premium_only.php)
        if (!class_exists(AIPKit_Moderation_IsRequired::class)) {
             return false; // Fail safe
        }
        return AIPKit_Moderation_IsRequired::check($bot_settings);
    }

    /**
     * Gets the custom notification message for flagged content.
     * Delegates to AIPKit_Moderation_MessageProvider::get().
     *
     * @param array $bot_settings Bot-level settings for the current chatbot.
     * @return string The message to display.
     */
    public static function get_flagged_message(array $bot_settings = []): string {
        // Ensure component is loaded
        if (!class_exists(AIPKit_Moderation_MessageProvider::class)) {
             return __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'); // Fallback
        }
        return AIPKit_Moderation_MessageProvider::get($bot_settings);
    }

     /**
      * Performs OpenAI moderation check on the provided text.
      * Delegates to AIPKit_Moderation_Executor::execute().
      *
      * @param string $text The input text to moderate.
      * @param array $bot_settings Bot-level settings for the current chatbot.
      * @return string|false|null Returns the flagged message string if flagged,
      *                           false if not flagged,
      *                           null if moderation is not required or an API error occurred.
      */
    public static function perform_moderation(string $text, array $bot_settings = []): string|false|null {
        // Ensure component is loaded
        if (!class_exists(AIPKit_Moderation_Executor::class)) {
             return null; // Fail safe (moderation not applied)
        }
        return AIPKit_Moderation_Executor::execute($text, $bot_settings);
    }
}
