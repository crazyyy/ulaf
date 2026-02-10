<?php

namespace WPAICG\Lib\Chat\Triggers\Validation;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Trigger_Validator
 *
 * Validates trigger JSON structure against expected schema constraints.
 */
class AIPKit_Trigger_Validator {

    /**
     * Validate triggers array.
     *
     * @param array $triggers
     * @return true|WP_Error
     */
    public static function validate_triggers_array(array $triggers) {
        $schemas = self::load_schemas();
        if (!$schemas) {
            return true;
        }

        $event_enum = $schemas['EventSchema']['properties']['name']['enum'] ?? [];
        $condition_type_enum = $schemas['ConditionSchema']['properties']['type']['enum'] ?? [];
        $operator_enum = $schemas['ConditionSchema']['properties']['operator']['enum'] ?? [];
        $action_enum = $schemas['ActionSchema']['properties']['type']['enum'] ?? [];

        foreach ($triggers as $index => $trigger) {
            if (!is_array($trigger)) {
                return new WP_Error('invalid_trigger_structure', __('Trigger must be an object.', 'gpt3-ai-content-generator'), ['index' => $index]);
            }

            $trigger_id = $trigger['id'] ?? '';
            $trigger_name = $trigger['name'] ?? '';
            $event_name = $trigger['event_name'] ?? '';

            if (!is_string($trigger_id) || $trigger_id === '') {
                return new WP_Error('missing_trigger_id', __('Trigger ID is required.', 'gpt3-ai-content-generator'), ['index' => $index]);
            }
            if (!is_string($trigger_name) || $trigger_name === '') {
                return new WP_Error('missing_trigger_name', __('Trigger name is required.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            if (!is_string($event_name) || $event_name === '' || (!empty($event_enum) && !in_array($event_name, $event_enum, true))) {
                return new WP_Error('invalid_event_name', __('Invalid or missing event name.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }

            if (!isset($trigger['priority']) || !is_numeric($trigger['priority'])) {
                return new WP_Error('invalid_trigger_priority', __('Trigger priority must be numeric.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            if (!isset($trigger['is_active'])) {
                return new WP_Error('invalid_trigger_status', __('Trigger active flag is required.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            $is_active = $trigger['is_active'];
            $is_active_valid = is_bool($is_active) || (is_numeric($is_active) && in_array((int) $is_active, [0, 1], true)) || in_array($is_active, ['0', '1'], true);
            if (!$is_active_valid) {
                return new WP_Error('invalid_trigger_status', __('Trigger active flag must be boolean.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            if (isset($trigger['event_params']) && $trigger['event_params'] !== null && !is_array($trigger['event_params'])) {
                return new WP_Error('invalid_event_params', __('Trigger event_params must be an object.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }

            $conditions = $trigger['conditions'] ?? [];
            if (!is_array($conditions)) {
                return new WP_Error('invalid_conditions', __('Trigger conditions must be an array.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            foreach ($conditions as $condition_index => $condition) {
                if (!is_array($condition)) {
                    return new WP_Error('invalid_condition_structure', __('Condition must be an object.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
                $condition_type = $condition['type'] ?? '';
                $field = $condition['field'] ?? '';
                $operator = $condition['operator'] ?? '';
                $value = $condition['value'] ?? null;

                if (!is_string($condition_type) || $condition_type === '' || (!empty($condition_type_enum) && !in_array($condition_type, $condition_type_enum, true))) {
                    return new WP_Error('invalid_condition_type', __('Invalid condition type.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
                if (!is_string($field) || $field === '') {
                    return new WP_Error('invalid_condition_field', __('Condition field is required.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
                if (!is_string($operator) || $operator === '' || (!empty($operator_enum) && !in_array($operator, $operator_enum, true))) {
                    return new WP_Error('invalid_condition_operator', __('Invalid condition operator.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }

                $operators_without_value = ['is_true', 'is_false', 'is_empty', 'is_not_empty'];
                if (!in_array($operator, $operators_without_value, true) && !array_key_exists('value', $condition)) {
                    return new WP_Error('missing_condition_value', __('Condition value is required.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
                if (in_array($operator, ['is_one_of', 'is_not_one_of'], true) && !is_array($value)) {
                    return new WP_Error('invalid_condition_value', __('Condition value must be an array for list operators.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
                if (in_array($operator, ['greater_than', 'less_than', 'equals_numeric', 'not_equals_numeric', 'greater_than_or_equals', 'less_than_or_equals'], true) && !is_numeric($value)) {
                    return new WP_Error('invalid_condition_value', __('Condition value must be numeric for numeric operators.', 'gpt3-ai-content-generator'), ['id' => $trigger_id, 'condition_index' => $condition_index]);
                }
            }

            $action = $trigger['action'] ?? null;
            if (!is_array($action)) {
                return new WP_Error('invalid_action', __('Trigger action must be an object.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }
            $action_type = $action['type'] ?? '';
            if (!is_string($action_type) || $action_type === '' || (!empty($action_enum) && !in_array($action_type, $action_enum, true))) {
                return new WP_Error('invalid_action_type', __('Invalid action type.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }

            $payload = $action['payload'] ?? null;
            if (!is_array($payload)) {
                return new WP_Error('invalid_action_payload', __('Action payload must be an object.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
            }

            switch ($action_type) {
                case 'bot_reply':
                    if (empty($payload['message'])) {
                        return new WP_Error('missing_action_payload', __('Bot reply requires a message.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
                    }
                    break;
                case 'inject_context':
                    if (empty($payload['placement']) || empty($payload['content'])) {
                        return new WP_Error('missing_action_payload', __('Inject context requires placement and content.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
                    }
                    break;
                case 'call_webhook':
                    if (empty($payload['endpoint_url']) || empty($payload['http_method'])) {
                        return new WP_Error('missing_action_payload', __('Webhook requires endpoint URL and method.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
                    }
                    break;
                case 'set_variable':
                    $value_present = array_key_exists('value', $action) || array_key_exists('value', $payload);
                    if (empty($payload['scope']) || empty($payload['key']) || !$value_present) {
                        return new WP_Error('missing_action_payload', __('Set variable requires scope, key, and value.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
                    }
                    break;
                case 'display_form':
                    if (empty($payload['elements']) || !is_array($payload['elements'])) {
                        return new WP_Error('missing_action_payload', __('Display form requires elements.', 'gpt3-ai-content-generator'), ['id' => $trigger_id]);
                    }
                    break;
                case 'store_form_submission':
                case 'block_message':
                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Load schema definitions.
     *
     * @return array|null
     */
    private static function load_schemas(): ?array {
        if (!defined('WPAICG_LIB_DIR')) {
            return null;
        }
        $schema_path = WPAICG_LIB_DIR . 'schemas/triggers/trigger-schemas.php';
        if (!file_exists($schema_path)) {
            return null;
        }
        $schemas = require $schema_path;
        return is_array($schemas) ? $schemas : null;
    }
}
