<?php

namespace WPAICG\Lib\Chat\Triggers\Actions;

use WPAICG\Lib\Chat\Triggers\Actions\AIPKit_Placeholder_Processor;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Action_Set_Variable
 *
 * Handles the 'set_variable' trigger action.
 */
class AIPKit_Action_Set_Variable {

    /**
     * Executes the 'set_variable' action.
     *
     * @param array $payload Payload containing 'scope', 'key', and 'value'.
     * @param array $context_data Contextual data.
     * @param int   $bot_id Bot ID.
     * @return array|WP_Error Action result.
     */
    public function execute(array $payload, array $context_data, int $bot_id): array|WP_Error {
        $scope = $payload['scope'] ?? null;
        $key_raw = $payload['key'] ?? '';
        $value = $payload['value'] ?? null;

        if (empty($scope) || !in_array($scope, ['user_meta', 'bot_context'], true)) {
            return new WP_Error('invalid_variable_scope', __('Invalid or missing scope for set_variable action.', 'gpt3-ai-content-generator'));
        }

        $key = sanitize_key((string) $key_raw);
        if ($key === '') {
            return new WP_Error('invalid_variable_key', __('Invalid or missing key for set_variable action.', 'gpt3-ai-content-generator'));
        }

        $processed_value = $value;
        if (is_string($value)) {
            if (class_exists(AIPKit_Placeholder_Processor::class)) {
                $processed_value = AIPKit_Placeholder_Processor::process($value, $context_data);
            } else {
                $processed_value = $value;
            }
        }

        if ($scope === 'user_meta') {
            $user_id = isset($context_data['user_id']) ? (int) $context_data['user_id'] : 0;
            if ($user_id <= 0) {
                return new WP_Error('missing_user_id', __('User ID is required to set user_meta.', 'gpt3-ai-content-generator'));
            }

            $meta_key = $key;
            if (!preg_match('/^_?aipkit_/', $meta_key)) {
                $meta_key = '_aipkit_' . $meta_key;
            }

            update_user_meta($user_id, $meta_key, $processed_value);

            return [
                'type'  => 'set_variable',
                'scope' => 'user_meta',
                'key'   => $meta_key,
                'value' => $processed_value,
            ];
        }

        return [
            'type'  => 'set_variable',
            'scope' => 'bot_context',
            'key'   => $key,
            'value' => $processed_value,
        ];
    }
}
