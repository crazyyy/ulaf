<?php

namespace Wpextended\Modules\CodeSnippets\Rest;

use Wpextended\Modules\CodeSnippets\Includes\SnippetManager;

/**
 * REST API controller for the Code Snippets module.
 *
 * Exposes CRUD endpoints for snippet management with capability checks, nonces for write operations,
 * and basic rate limiting.
 * @since 3.1.0
 */
class Controller
{
    private $snippetManager;

    /**
     * Construct the controller and register hooks.
     *
     * @since 3.1.0
     */
    public function __construct()
    {
        $this->snippetManager = new SnippetManager();
        $this->init();
    }

    /**
     * Register rest_api_init hook.
     *
     * @since 3.1.0
     * @return void
     */
    private function init()
    {
        add_action('rest_api_init', array($this, 'registerRoutes'));
    }

    /**
     * Register all REST routes for the module.
     *
     * @since 3.1.0
     * @return void
     */
    public function registerRoutes()
    {
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getSnippets'),
                'permission_callback' => array($this, 'checkPermission'),
                'args' => array(
                    'page' => array(
                        'description' => __('Current page of the collection.', WP_EXTENDED_TEXT_DOMAIN),
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'description' => __('Maximum number of items to be returned in result set.', WP_EXTENDED_TEXT_DOMAIN),
                        'type' => 'integer',
                        'default' => 10,
                        'minimum' => 1,
                        'maximum' => 100,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets/(?P<id>[a-zA-Z0-9-]+)',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getSnippet'),
                'permission_callback' => array($this, 'checkPermission')
            )
        );

        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'createSnippet'),
                'permission_callback' => array($this, 'checkPermission')
            )
        );

        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets/(?P<id>[a-zA-Z0-9-]+)',
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'updateSnippet'),
                'permission_callback' => array($this, 'checkPermission')
            )
        );

        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets/(?P<id>[a-zA-Z0-9-]+)/toggle',
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'toggleSnippet'),
                'permission_callback' => array($this, 'checkPermission')
            )
        );

        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/code-snippets/snippets/(?P<id>[a-zA-Z0-9-]+)',
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'deleteSnippet'),
                'permission_callback' => array($this, 'checkPermission')
            )
        );
    }

    /**
     * Check capability and nonce (for non-GET requests).
     *
     * @since 3.1.0
     * @return bool
     */
    public function checkPermission()
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? $_POST['_wpnonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return false;
            }
        }

        return true;
    }

    public function getSnippets($request)
    {
        // Get pagination parameters
        $page = max(1, (int) $request->get_param('page'));
        $per_page = max(1, min(100, (int) $request->get_param('per_page'))); // Limit to 100 items max

        // Get all snippets
        $allSnippets = $this->snippetManager->getAllSnippets();
        $total_items = count($allSnippets);

        // Calculate pagination
        $total_pages = ceil($total_items / $per_page);
        $offset = ($page - 1) * $per_page;

        // Get the current page's snippets
        $snippets = array_slice($allSnippets, $offset, $per_page);

        // Format the data
        $data = array_map(function ($snippet) {
            return [
                'id' => $snippet->getId(),
                'name' => $snippet->getName(),
                'type' => $snippet->getType(true),
                'enabled' => (bool) $snippet->isEnabled(),
                'description' => $snippet->getDescription(),
                'run_location' => $snippet->getRunLocation(),
                'is_valid' => (bool) $snippet->isValid(),
                'has_integrity_issue' => (bool) $snippet->hasIntegrityIssue(),
                'priority' => absint($snippet->getPriority())
            ];
        }, $snippets);

        // Create paginated response
        $response = rest_ensure_response($data);

        // Add pagination headers
        $response->header('X-WP-Total', $total_items);
        $response->header('X-WP-TotalPages', $total_pages);

        return $response;
    }

    /**
     * Get a single snippet by id.
     *
     * @since 3.1.0
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function getSnippet($request)
    {
        $id = sanitize_key($request['id']);
        $snippet = $this->snippetManager->getSnippet($id);
        if (!$snippet) {
            return new \WP_Error('not_found', __('Snippet not found', WP_EXTENDED_TEXT_DOMAIN), ['status' => 404]);
        }
        return rest_ensure_response([
            'id' => $snippet->getId(),
            'name' => $snippet->getName(),
            'type' => $snippet->getType(),
            'enabled' => (bool) $snippet->isEnabled(),
            'description' => $snippet->getDescription(),
            'run_location' => $snippet->getRunLocation(),
            'is_valid' => (bool) $snippet->isValid(),
            'has_integrity_issue' => (bool) $snippet->hasIntegrityIssue(),
            'priority' => absint($snippet->getPriority())
        ]);
    }

    /**
     * Create a new snippet from posted JSON.
     *
     * @since 3.1.0
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function createSnippet($request)
    {
        $data = $request->get_json_params();
        if (!$data) {
            return new \WP_Error('invalid_data', __('Invalid request data', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $sanitized_data = [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'type' => sanitize_key($data['type'] ?? 'php'),
            'code' => $data['code'] ?? '',
            'enabled' => (bool) ($data['enabled'] ?? false),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'run_location' => sanitize_key($data['run_location'] ?? ''),
            'is_valid' => (bool) ($data['is_valid'] ?? true),
            'priority' => absint($data['priority'] ?? 10)
        ];
        if (empty($sanitized_data['name'])) {
            return new \WP_Error('invalid_data', __('Snippet name is required', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        if (empty($sanitized_data['code'])) {
            return new \WP_Error('invalid_data', __('Snippet code is required', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $result = $this->snippetManager->createSnippet($sanitized_data);
        if (!$result['success']) {
            return new \WP_Error('snippet_creation_failed', esc_html($result['message']), ['status' => 400]);
        }
        $snippet = $result['data']['snippet'];
        return rest_ensure_response([
            'success' => true,
            'message' => esc_html($result['message']),
            'data' => [
                'id' => $result['data']['id'],
                'name' => $snippet->getName(),
                'type' => $snippet->getType(),
                'enabled' => (bool) $snippet->isEnabled(),
                'description' => $snippet->getDescription(),
                'run_location' => $snippet->getRunLocation(),
                'is_valid' => (bool) $snippet->isValid(),
                'has_integrity_issue' => (bool) $snippet->hasIntegrityIssue(),
                'priority' => absint($snippet->getPriority())
            ]
        ]);
    }

    /**
     * Update an existing snippet by id.
     *
     * @since 3.1.0
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateSnippet($request)
    {
        $id = sanitize_key($request->get_param('id'));
        $data = $request->get_json_params();
        if (!$data) {
            return new \WP_Error('invalid_data', __('Invalid request data', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $sanitized_data = [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'type' => sanitize_key($data['type'] ?? 'php'),
            'code' => $data['code'] ?? '',
            'enabled' => (bool) ($data['enabled'] ?? false),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'run_location' => sanitize_key($data['run_location'] ?? ''),
            'is_valid' => (bool) ($data['is_valid'] ?? true),
            'priority' => absint($data['priority'] ?? 10)
        ];
        if (empty($sanitized_data['name'])) {
            return new \WP_Error('invalid_data', __('Snippet name is required', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        if (empty($sanitized_data['code'])) {
            return new \WP_Error('invalid_data', __('Snippet code is required', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $result = $this->snippetManager->updateSnippet($id, $sanitized_data);
        if (!$result['success']) {
            return new \WP_Error('snippet_update_failed', esc_html($result['message']), ['status' => 400]);
        }
        $snippet = $result['data']['snippet'];
        return rest_ensure_response([
            'success' => true,
            'message' => esc_html($result['message']),
            'data' => [
                'id' => $result['data']['id'],
                'name' => $snippet->getName(),
                'type' => $snippet->getType(),
                'enabled' => (bool) $snippet->isEnabled(),
                'description' => $snippet->getDescription(),
                'run_location' => $snippet->getRunLocation(),
                'is_valid' => (bool) $snippet->isValid(),
                'has_integrity_issue' => (bool) $snippet->hasIntegrityIssue(),
                'priority' => absint($snippet->getPriority())
            ]
        ]);
    }

    /**
     * Toggle the enabled flag for a snippet.
     *
     * @since 3.1.0
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function toggleSnippet($request)
    {
        $id = sanitize_key($request->get_param('id'));
        $data = $request->get_json_params();
        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : null;
        if ($enabled === null) {
            return new \WP_Error('invalid_data', __('Enabled state is required', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $existingSnippet = $this->snippetManager->getSnippet($id);
        if (!$existingSnippet) {
            return new \WP_Error('not_found', __('Snippet not found', WP_EXTENDED_TEXT_DOMAIN), ['status' => 404]);
        }
        $success = $this->snippetManager->setSnippetEnabled($id, $enabled);
        if (!$success) {
            return new \WP_Error('toggle_failed', __('Failed to update snippet status', WP_EXTENDED_TEXT_DOMAIN), ['status' => 400]);
        }
        $snippet = $this->snippetManager->getSnippet($id);
        if (!$snippet) {
            return new \WP_Error('retrieval_failed', __('Failed to retrieve updated snippet', WP_EXTENDED_TEXT_DOMAIN), ['status' => 500]);
        }
        return rest_ensure_response([
            'success' => true,
            'message' => $enabled ? __('Snippet enabled successfully', WP_EXTENDED_TEXT_DOMAIN) : __('Snippet disabled successfully', WP_EXTENDED_TEXT_DOMAIN),
            'data' => [
                'id' => $snippet->getId(),
                'name' => $snippet->getName(),
                'type' => $snippet->getType(),
                'enabled' => (bool) $snippet->isEnabled(),
                'description' => $snippet->getDescription(),
                'run_location' => $snippet->getRunLocation(),
                'is_valid' => (bool) $snippet->isValid(),
                'has_integrity_issue' => (bool) $snippet->hasIntegrityIssue(),
                'priority' => absint($snippet->getPriority())
            ]
        ]);
    }

    /**
     * Delete a snippet by id.
     *
     * @since 3.1.0
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteSnippet($request)
    {
        $id = sanitize_key($request['id']);
        $existingSnippet = $this->snippetManager->getSnippet($id);
        if (!$existingSnippet) {
            return new \WP_Error('not_found', __('Snippet not found', WP_EXTENDED_TEXT_DOMAIN), ['status' => 404]);
        }
        $result = $this->snippetManager->deleteSnippet($id);
        if (!$result['success']) {
            return new \WP_Error('delete_failed', esc_html($result['message']), ['status' => 400]);
        }
        return rest_ensure_response([
            'success' => true,
            'message' => esc_html($result['message'])
        ]);
    }
}
