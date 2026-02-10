<?php

namespace WPAICG\Lib\Chat\Frontend\Ajax\Handlers;

use WPAICG\Lib\Chat\Frontend\Ajax\Utils\ChatUploadLogger;
use WPAICG\Lib\Chat\Frontend\Ajax\Utils\ChatUploadConfigLoader;
use WPAICG\AIPKit_Providers;
use WPAICG\Chat\Storage\BotStorage;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * ChatFileUploadClaudeHandler
 *
 * Handles Claude Files API uploads for frontend chatbot file context.
 */
class ChatFileUploadClaudeHandler
{
    private $config_loader;
    private $logger;
    private $bot_storage;

    public function __construct(ChatUploadConfigLoader $config_loader, ChatUploadLogger $logger)
    {
        $this->config_loader = $config_loader;
        $this->logger = $logger;

        if (!class_exists(BotStorage::class)) {
            $bot_storage_path = WPAICG_PLUGIN_DIR . 'classes/chat/storage/class-aipkit_chat_bot_storage.php';
            if (file_exists($bot_storage_path)) {
                require_once $bot_storage_path;
            }
        }
        $this->bot_storage = class_exists(BotStorage::class) ? new BotStorage() : null;

        if (!class_exists(AIPKit_Providers::class)) {
            $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
            if (file_exists($providers_path)) {
                require_once $providers_path;
            }
        }
    }

    /**
     * Gets Claude API configuration.
     *
     * @return array|WP_Error
     */
    public function get_provider_api_config(): array|WP_Error
    {
        if (!class_exists(AIPKit_Providers::class)) {
            return new WP_Error('dependency_missing_claude_providers', __('Provider configuration component is missing for Claude.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        $claude_data = AIPKit_Providers::get_provider_data('Claude');
        if (empty($claude_data['api_key'])) {
            return new WP_Error('missing_claude_key', __('Claude API Key is not configured.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        return [
            'api_key' => $claude_data['api_key'],
            'base_url' => $claude_data['base_url'] ?? 'https://api.anthropic.com',
            'api_version' => $claude_data['api_version'] ?? '2023-06-01',
        ];
    }

    /**
     * Processes the file upload for Claude Files API.
     *
     * @param array $file_data The $_FILES entry for the uploaded file.
     * @param int|null $user_id Current user ID (null for guests).
     * @param string|null $session_id Guest session ID.
     * @param array $claude_config Claude API config.
     * @param int $bot_id The ID of the chatbot.
     * @return array|WP_Error Success data or WP_Error.
     */
    public function process_upload(array $file_data, ?int $user_id, ?string $session_id, array $claude_config, int $bot_id): array|WP_Error
    {
        if (!$this->bot_storage) {
            return new WP_Error('internal_error_claude_bot_storage', __('Chatbot settings service is unavailable for Claude upload.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        $bot_settings = $this->bot_storage->get_chatbot_settings($bot_id);
        $main_provider = strtolower((string) ($bot_settings['provider'] ?? ''));
        $vector_provider = sanitize_key((string) ($bot_settings['vector_store_provider'] ?? ''));

        if ($main_provider !== 'claude') {
            return new WP_Error('claude_upload_provider_mismatch', __('Claude Files requires the chatbot AI provider to be Claude.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }
        if ($vector_provider !== 'claude_files') {
            return new WP_Error('claude_upload_mode_not_enabled', __('Select "Claude Files" as knowledge base provider to use this upload mode.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $api_key = (string) ($claude_config['api_key'] ?? '');
        $base_url = rtrim((string) ($claude_config['base_url'] ?? 'https://api.anthropic.com'), '/');
        $api_version = sanitize_text_field((string) ($claude_config['api_version'] ?? '2023-06-01'));

        if ($api_key === '') {
            return new WP_Error('claude_upload_missing_api_key', __('Claude API Key is missing.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        $upload_url = $base_url . '/v1/files';
        $file_name = sanitize_file_name((string) ($file_data['name'] ?? 'upload.bin'));
        $file_path = (string) ($file_data['tmp_name'] ?? '');
        $mime_type = '';

        if ($file_path === '' || !is_readable($file_path)) {
            return new WP_Error('claude_upload_missing_file', __('Uploaded file is not readable.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        if (function_exists('mime_content_type')) {
            $mime_type = (string) mime_content_type($file_path);
        }
        if ($mime_type === '' && !empty($file_data['type'])) {
            $mime_type = sanitize_text_field((string) $file_data['type']);
        }
        if ($mime_type === '') {
            $mime_type = 'application/octet-stream';
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading temporary upload file content for multipart request body.
        $file_contents = file_get_contents($file_path);
        if (!is_string($file_contents)) {
            return new WP_Error('claude_upload_read_error', __('Could not read uploaded file for Claude upload.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $boundary = '----aipkit-claude-' . wp_generate_password(16, false, false);
        $multipart_body = $this->build_multipart_body(
            $boundary,
            $file_name,
            $mime_type,
            $file_contents
        );

        $response = wp_remote_post($upload_url, [
            'timeout' => 120,
            'redirection' => 3,
            'sslverify' => apply_filters('https_local_ssl_verify', true),
            'headers' => [
                'x-api-key' => $api_key,
                'anthropic-version' => $api_version,
                'anthropic-beta' => 'files-api-2025-04-14',
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ],
            'body' => $multipart_body,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                'claude_upload_http_error',
                sprintf(__('Claude upload network error: %s', 'gpt3-ai-content-generator'), $response->get_error_message()),
                ['status' => 503]
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $response_body = (string) wp_remote_retrieve_body($response);
        $decoded = json_decode((string) $response_body, true);
        if ($status_code >= 400) {
            $error_message = $this->extract_error_message($decoded, (string) $response_body);
            return new WP_Error('claude_upload_api_error', sprintf(__('Claude Files API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, $error_message), ['status' => $status_code]);
        }

        $file_id = is_array($decoded) && !empty($decoded['id']) ? sanitize_text_field((string) $decoded['id']) : '';
        if ($file_id === '') {
            return new WP_Error('claude_upload_invalid_response', __('Claude upload did not return a valid file ID.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        $this->logger->log_event([
            'user_id' => $user_id ?: get_current_user_id(),
            'provider' => 'Claude',
            'vector_store_id' => $file_id,
            'vector_store_name' => 'Claude Files',
            'status' => 'success',
            'message' => 'File uploaded to Claude Files API for chat context.',
            'indexed_content' => $file_name,
            'file_id' => $file_id,
            'source_type_for_log' => 'chat_file_upload_claude',
            'batch_id' => $session_id ?: null,
        ]);

        return [
            'message' => __('File processed for Claude chat.', 'gpt3-ai-content-generator'),
            'file_context' => [
                'provider' => 'Claude',
                'file_id' => $file_id,
                'original_filename' => $file_name,
            ],
        ];
    }

    /**
     * Builds multipart/form-data body for Claude Files upload.
     *
     * @param string $boundary Multipart boundary.
     * @param string $file_name Uploaded filename.
     * @param string $mime_type Uploaded mime type.
     * @param string $file_contents Raw file bytes.
     * @return string
     */
    private function build_multipart_body(string $boundary, string $file_name, string $mime_type, string $file_contents): string
    {
        $eol = "\r\n";
        $safe_file_name = str_replace(["\r", "\n", '"'], ['', '', ''], $file_name);

        $body = '';
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="purpose"' . $eol . $eol;
        $body .= 'user_data' . $eol;

        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . $safe_file_name . '"' . $eol;
        $body .= 'Content-Type: ' . $mime_type . $eol . $eol;
        $body .= $file_contents . $eol;

        $body .= '--' . $boundary . '--' . $eol;

        return $body;
    }

    /**
     * Extracts a user-friendly API error message from Claude response.
     *
     * @param mixed $decoded Decoded response body.
     * @param string $raw_body Raw response body.
     * @return string
     */
    private function extract_error_message($decoded, string $raw_body): string
    {
        if (is_array($decoded)) {
            if (!empty($decoded['error']['message']) && is_string($decoded['error']['message'])) {
                return sanitize_text_field($decoded['error']['message']);
            }
            if (!empty($decoded['message']) && is_string($decoded['message'])) {
                return sanitize_text_field($decoded['message']);
            }
        }

        $trimmed = trim($raw_body);
        if ($trimmed !== '') {
            return wp_strip_all_tags($trimmed);
        }

        return __('Unknown Claude Files API error.', 'gpt3-ai-content-generator');
    }
}
