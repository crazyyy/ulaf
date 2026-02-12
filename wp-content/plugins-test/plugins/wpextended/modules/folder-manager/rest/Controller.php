<?php

namespace Wpextended\Modules\FolderManager\Rest;

use Wpextended\Modules\FolderManager\Bootstrap;
use Wpextended\Modules\FolderManager\Includes\FolderManager;
use Wpextended\Modules\FolderManager\Includes\TaxonomyManager;

/**
 * REST API Controller for Folder Manager
 */
class Controller
{
    /**
     * Module instance
     *
     * @var Bootstrap
     */
    private $module;

    /**
     * Folder manager instance
     *
     * @var FolderManager
     */
    private $folderManager;

    /**
     * Taxonomy manager instance
     *
     * @var TaxonomyManager
     */
    private $taxonomyManager;

    /**
     * Constructor
     *
     * @param Bootstrap $module Module instance
     */
    public function __construct(Bootstrap $module)
    {
        $this->module = $module;

        // Ensure taxonomy is registered first
        $this->taxonomyManager = new TaxonomyManager($module);

        $this->folderManager = new FolderManager($module);
        $this->init();
    }

    /**
     * Initialize REST routes
     *
     * @return void
     */
    private function init(): void
    {
        add_action('rest_api_init', array($this, 'registerRoutes'));
    }

    /**
     * Register all REST routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // Get folders for a post type
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getFolders'),
                'permission_callback' => array($this, 'checkPermission'),
                'args' => array(
                    'post_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => array($this, 'validatePostType'),
                    ),
                ),
            )
        );

        // Create folder
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'createFolder'),
                'permission_callback' => array($this, 'checkFolderPermission'),
            )
        );

        // Update folder (rename)
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders/(?P<id>\d+)',
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'updateFolder'),
                'permission_callback' => array($this, 'checkFolderPermission'),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Delete folder
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders/(?P<id>\d+)',
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'deleteFolder'),
                'permission_callback' => array($this, 'checkFolderPermission'),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Move folder
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders/(?P<id>\d+)/move',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'moveFolder'),
                'permission_callback' => array($this, 'checkFolderPermission'),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Bulk move items
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/items/move',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'bulkMoveItems'),
                'permission_callback' => array($this, 'checkItemPermission'),
            )
        );

        // Get folder count
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/folders/(?P<id>\d+)/count',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getFolderCount'),
                'permission_callback' => array($this, 'checkPermission'),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'post_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => array($this, 'validatePostType'),
                    ),
                ),
            )
        );

        // Get attachment IDs for a folder (for media library filtering)
        register_rest_route(
            WP_EXTENDED_API_NAMESPACE,
            '/folder-manager/attachments',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'getAttachmentIds'),
                'permission_callback' => array($this, 'checkPermission'),
                'args' => array(
                    'folder_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'default' => 0,
                    ),
                    'post_type' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_key',
                        'default' => 'attachment',
                    ),
                ),
            )
        );
    }

    /**
     * Check basic permission
     *
     * @return bool
     */
    public function checkPermission(): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * Check folder management permission
     *
     * @return bool
     */
    public function checkFolderPermission(): bool
    {
        // Check capability
        if (!current_user_can('manage_categories')) {
            return false;
        }

        // Verify nonce for non-GET requests (WordPress REST API handles this automatically,
        // but explicit check follows pattern from other modules)
        $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($request_method && $request_method !== 'GET') {
            $nonce = filter_input(INPUT_SERVER, 'HTTP_X_WP_NONCE', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check item management permission
     *
     * @param \WP_REST_Request $request Request object
     * @return bool
     */
    public function checkItemPermission(\WP_REST_Request $request): bool
    {
        // Verify nonce for non-GET requests
        $request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($request_method && $request_method !== 'GET') {
            $nonce = filter_input(INPUT_SERVER, 'HTTP_X_WP_NONCE', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return false;
            }
        }

        $data = $request->get_json_params();
        $post_type = isset($data['post_type']) ? sanitize_key($data['post_type']) : '';

        if (empty($post_type)) {
            return false;
        }

        if ($post_type === 'attachment') {
            return current_user_can('upload_files');
        }

        $post_type_obj = get_post_type_object($post_type);
        if (!$post_type_obj) {
            return false;
        }

        return current_user_can($post_type_obj->cap->edit_posts);
    }

    /**
     * Validate post type
     *
     * @param string $post_type Post type
     * @param \WP_REST_Request $request Request object
     * @param string $param Parameter name
     * @return bool
     */
    public function validatePostType(string $post_type, \WP_REST_Request $request, string $param): bool
    {
        return $this->module->isPostTypeEnabled($post_type);
    }

    /**
     * Get folders for a post type
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function getFolders(\WP_REST_Request $request)
    {
        $post_type = $request->get_param('post_type');
        $uncategorized_count = $this->folderManager->getUncategorizedCount($post_type);

        $folders = $this->folderManager->getFolderTree($post_type);

        return rest_ensure_response(array(
            'folders' => $folders,
            'uncategorized_count' => $uncategorized_count,
        ));
    }

    /**
     * Create a new folder
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function createFolder(\WP_REST_Request $request)
    {
        try {
            $data = $request->get_json_params();
            $name = sanitize_text_field($data['name'] ?? '');
            $post_type = sanitize_key($data['post_type'] ?? '');
            $parent_id = isset($data['parent_id']) ? absint($data['parent_id']) : 0;

            // Validate folder name
            if (empty($name)) {
                return new \WP_Error(
                    'missing_name',
                    __('Folder name is required.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }

            // Validate folder name length (max 200 characters)
            if (mb_strlen($name) > 200) {
                return new \WP_Error(
                    'name_too_long',
                    __('Folder name cannot exceed 200 characters.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }

            // Validate post type
            if (empty($post_type)) {
                return new \WP_Error(
                    'missing_post_type',
                    __('Post type is required.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }

            // Validate post type exists and is enabled
            if (!$this->module->isPostTypeEnabled($post_type)) {
                return new \WP_Error(
                    'invalid_post_type',
                    __('Post type is not enabled for folder manager.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }

            // Validate parent_id is non-negative integer
            if ($parent_id < 0) {
                return new \WP_Error(
                    'invalid_parent_id',
                    __('Parent folder ID must be a non-negative integer.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }

            $result = $this->folderManager->createFolder($name, $post_type, $parent_id);
            if (is_wp_error($result)) {
                return $result;
            }

            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Folder created successfully.', WP_EXTENDED_TEXT_DOMAIN),
                'data' => $result['folder'],
            ));
        } catch (\Exception $e) {
            return new \WP_Error(
                'server_error',
                __('An error occurred while creating the folder.', WP_EXTENDED_TEXT_DOMAIN) . ' ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Update folder (rename)
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateFolder(\WP_REST_Request $request)
    {
        $folder_id = $request->get_param('id');
        $data = $request->get_json_params();
        $new_name = sanitize_text_field($data['name'] ?? '');

        // Validate folder name
        if (empty($new_name)) {
            return new \WP_Error(
                'missing_name',
                __('Folder name is required.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        // Validate folder name length (max 200 characters)
        if (mb_strlen($new_name) > 200) {
            return new \WP_Error(
                'name_too_long',
                __('Folder name cannot exceed 200 characters.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        $result = $this->folderManager->renameFolder($folder_id, $new_name);
        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Folder renamed successfully.', WP_EXTENDED_TEXT_DOMAIN),
            'data' => $result['folder'],
        ));
    }

    /**
     * Delete folder
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteFolder(\WP_REST_Request $request)
    {
        $folder_id = $request->get_param('id');

        $result = $this->folderManager->deleteFolder($folder_id);
        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => $result['message'],
        ));
    }

    /**
     * Move folder
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function moveFolder(\WP_REST_Request $request)
    {
        $folder_id = $request->get_param('id');
        $data = $request->get_json_params();
        $new_parent_id = isset($data['parent_id']) ? absint($data['parent_id']) : 0;

        $result = $this->folderManager->moveFolder($folder_id, $new_parent_id);
        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Folder moved successfully.', WP_EXTENDED_TEXT_DOMAIN),
            'data' => $result['folder'],
        ));
    }

    /**
     * Bulk move items to folder
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function bulkMoveItems(\WP_REST_Request $request)
    {
        $data = $request->get_json_params();

        // Validate and sanitize item_ids
        if (!isset($data['item_ids']) || !is_array($data['item_ids'])) {
            return new \WP_Error(
                'missing_items',
                __('No items provided.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        // Sanitize and validate each item ID
        $item_ids = array();
        foreach ($data['item_ids'] as $item_id) {
            $sanitized_id = absint($item_id);
            if ($sanitized_id > 0) {
                $item_ids[] = $sanitized_id;
            }
        }

        if (empty($item_ids)) {
            return new \WP_Error(
                'invalid_items',
                __('No valid item IDs provided.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        // Validate folder_id
        $folder_id_raw = isset($data['folder_id']) ? filter_var($data['folder_id'], FILTER_VALIDATE_INT) : 0;
        $folder_id = ($folder_id_raw !== false && $folder_id_raw !== null && $folder_id_raw >= 0) ? absint($folder_id_raw) : 0;

        // Validate post_type
        $post_type = sanitize_key($data['post_type'] ?? '');
        if (empty($post_type)) {
            return new \WP_Error(
                'missing_post_type',
                __('Post type is required.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        // Validate post type is enabled
        if (!$this->module->isPostTypeEnabled($post_type)) {
            return new \WP_Error(
                'invalid_post_type',
                __('Post type is not enabled for folder manager.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        $taxonomy = \Wpextended\Modules\FolderManager\Includes\TaxonomyManager::getTaxonomy();
        $terms = array();

        if ($folder_id > 0) {
            // Validate folder belongs to post type
            $folder_post_type = get_term_meta($folder_id, FolderManager::POST_TYPE_META_KEY, true);
            if ($folder_post_type !== $post_type) {
                return new \WP_Error(
                    'invalid_folder',
                    __('Folder does not belong to the specified post type.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 400)
                );
            }
            $terms = array($folder_id);
        }

        $updated = 0;
        $errors = array();

        foreach ($item_ids as $item_id) {
            // Verify post type
            $post = get_post($item_id);
            if (!$post || $post->post_type !== $post_type) {
                $errors[] = sprintf(__('Item %d does not match post type.', WP_EXTENDED_TEXT_DOMAIN), $item_id);
                continue;
            }

            $result = wp_set_object_terms($item_id, $terms, $taxonomy);
            if (!is_wp_error($result)) {
                $updated++;
            } else {
                $errors[] = sprintf(__('Failed to update item %d: %s', WP_EXTENDED_TEXT_DOMAIN), $item_id, $result->get_error_message());
            }
        }

        // Return error if no items were updated
        if ($updated === 0) {
            return new \WP_Error(
                'no_items_updated',
                __('No items were moved. Please check the item IDs and try again.', WP_EXTENDED_TEXT_DOMAIN),
                array(
                    'status' => 400,
                    'updated' => 0,
                    'errors' => $errors,
                )
            );
        }

        // Get post type labels for message
        $post_type_obj = get_post_type_object($post_type);
        $post_type_singular = $post_type_obj ? $post_type_obj->labels->singular_name : __('Item', WP_EXTENDED_TEXT_DOMAIN);
        $post_type_plural = $post_type_obj ? $post_type_obj->labels->name : __('Items', WP_EXTENDED_TEXT_DOMAIN);

        return rest_ensure_response(array(
            'success' => true,
            'message' => sprintf(
                _n(
                    '%1$d %2$s moved successfully.',
                    '%1$d %3$s moved successfully.',
                    $updated,
                    WP_EXTENDED_TEXT_DOMAIN
                ),
                $updated,
                $post_type_singular,
                $post_type_plural
            ),
            'updated' => $updated,
            'errors' => $errors,
        ));
    }

    /**
     * Get folder count
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function getFolderCount(\WP_REST_Request $request)
    {
        $folder_id = $request->get_param('id');
        $post_type = $request->get_param('post_type');

        $count = $this->folderManager->getFolderCount($folder_id, $post_type);

        return rest_ensure_response(array(
            'count' => $count,
        ));
    }

    /**
     * Get attachment IDs for a folder (for media library filtering)
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function getAttachmentIds(\WP_REST_Request $request)
    {
        $folder_id = $request->get_param('folder_id');
        $post_type = $request->get_param('post_type') ?: 'attachment';

        if (!$this->module->isPostTypeEnabled($post_type)) {
            return new \WP_Error(
                'invalid_post_type',
                __('Post type is not enabled for folders.', WP_EXTENDED_TEXT_DOMAIN),
                array('status' => 400)
            );
        }

        $taxonomy = TaxonomyManager::getTaxonomy();
        $attachment_ids = array();

        if ($folder_id === 0) {
            // Get uncategorized attachments (those without any folder)
            $folder_manager = new FolderManager($this->module);
            $folders = $folder_manager->getFoldersForPostType($post_type);

            if (!empty($folders)) {
                $term_ids = array_map(function ($folder) {
                    return $folder->term_id;
                }, $folders);

                // Get all attachments that don't have any folder
                $args = array(
                    'post_type' => $post_type,
                    'post_status' => 'inherit',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'tax_query' => array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field' => 'term_id',
                            'terms' => $term_ids,
                            'operator' => 'NOT IN',
                        ),
                    ),
                );
                $query = new \WP_Query($args);
                $attachment_ids = $query->posts;
            } else {
                // No folders exist, so all attachments are uncategorized
                $args = array(
                    'post_type' => $post_type,
                    'post_status' => 'inherit',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                );
                $query = new \WP_Query($args);
                $attachment_ids = $query->posts;
            }
        } else {
            // Get attachments in specific folder
            $term = get_term($folder_id, $taxonomy);
            if (!$term || is_wp_error($term)) {
                return new \WP_Error(
                    'folder_not_found',
                    __('Folder not found.', WP_EXTENDED_TEXT_DOMAIN),
                    array('status' => 404)
                );
            }

            // Get all post IDs with this term
            $post_ids = get_objects_in_term($folder_id, $taxonomy);
            if (!is_wp_error($post_ids) && !empty($post_ids)) {
                // Filter by post type
                $args = array(
                    'post_type' => $post_type,
                    'post_status' => 'inherit',
                    'post__in' => $post_ids,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                );
                $query = new \WP_Query($args);
                $attachment_ids = $query->posts;
            }
        }

        return rest_ensure_response(array(
            'ids' => $attachment_ids,
            'count' => count($attachment_ids),
        ));
    }
}
