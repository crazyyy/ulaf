<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;
use Wpextended\Includes\Notices;

/**
 * Handles bulk move operations
 */
class BulkActions
{
    /**
     * Module instance
     *
     * @var Bootstrap
     */
    private $module;

    /**
     * Taxonomy slug
     */
    private $taxonomy;

    /**
     * Constructor
     *
     * @param Bootstrap $module Module instance
     */
    public function __construct(Bootstrap $module)
    {
        $this->module = $module;
        $this->taxonomy = TaxonomyManager::getTaxonomy();
        $this->init();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init(): void
    {
        $enabled_post_types = $this->module->getEnabledPostTypes();

        // Add bulk action for each enabled post type
        foreach ($enabled_post_types as $post_type) {
            if ($post_type === 'attachment') {
                add_filter('bulk_actions-upload', array($this, 'addBulkAction'));
            } else {
                add_filter("bulk_actions-edit-{$post_type}", array($this, 'addBulkAction'));
            }
        }

        // Handle bulk action processing
        add_filter('handle_bulk_actions-edit-post', array($this, 'handleBulkAction'), 10, 3);
        add_filter('handle_bulk_actions-edit-page', array($this, 'handleBulkAction'), 10, 3);
        add_filter('handle_bulk_actions-upload', array($this, 'handleBulkAction'), 10, 3);

        // Handle custom post types dynamically
        add_action('admin_init', array($this, 'registerCustomPostTypeBulkActions'));
    }

    /**
     * Register bulk actions for custom post types
     *
     * @return void
     */
    public function registerCustomPostTypeBulkActions(): void
    {
        $enabled_post_types = $this->module->getEnabledPostTypes();

        foreach ($enabled_post_types as $post_type) {
            if (!in_array($post_type, array('post', 'page', 'attachment'), true)) {
                add_filter("handle_bulk_actions-edit-{$post_type}", array($this, 'handleBulkAction'), 10, 3);
            }
        }
    }

    /**
     * Add bulk action to list
     *
     * @param array $actions Existing bulk actions
     * @return array Modified actions
     */
    public function addBulkAction(array $actions): array
    {
        $actions['wpext_move_to_folder'] = __('Move to Folder', WP_EXTENDED_TEXT_DOMAIN);
        return $actions;
    }

    /**
     * Handle bulk action processing
     *
     * @param string $redirect_url Redirect URL
     * @param string $action Action name
     * @param array $post_ids Post IDs
     * @return string Modified redirect URL
     */
    public function handleBulkAction(string $redirect_url, string $action, array $post_ids): string
    {
        if ($action !== 'wpext_move_to_folder') {
            return $redirect_url;
        }

        if (empty($post_ids)) {
            return $redirect_url;
        }

        // Check user capability first
        $first_post = get_post($post_ids[0]);
        if (!$first_post) {
            return $redirect_url;
        }

        $post_type = $first_post->post_type;
        $post_type_obj = get_post_type_object($post_type);
        if (!$post_type_obj) {
            return $redirect_url;
        }

        // Verify user has permission to edit posts of this type
        if (!current_user_can($post_type_obj->cap->edit_posts)) {
            Notices::add(array(
                'type' => 'error',
                'message' => __('You do not have permission to perform this action.', WP_EXTENDED_TEXT_DOMAIN),
            ));
            return $redirect_url;
        }

        // Verify nonce for bulk action
        if (!check_admin_referer('bulk-posts')) {
            return $redirect_url;
        }

        // Get folder ID from request using filter_input (POST; bulk actions submit via form)
        $folder_id_raw = filter_input(INPUT_POST, 'wpext_folder_id', FILTER_VALIDATE_INT);
        $folder_id = ($folder_id_raw !== false && $folder_id_raw !== null) ? absint($folder_id_raw) : 0;

        // Validate folder if provided
        if ($folder_id > 0) {
            $folder_post_type = get_term_meta($folder_id, FolderManager::POST_TYPE_META_KEY, true);
            if ($folder_post_type !== $post_type) {
                Notices::add(array(
                    'type' => 'error',
                    'message' => __('Selected folder does not belong to this post type.', WP_EXTENDED_TEXT_DOMAIN),
                ));
                return $redirect_url;
            }
        }

        // Move items
        $terms = $folder_id > 0 ? array($folder_id) : array();
        $updated = 0;
        $errors = 0;

        foreach ($post_ids as $post_id) {
            $result = wp_set_object_terms($post_id, $terms, $this->taxonomy);
            if (!is_wp_error($result)) {
                $updated++;
            } else {
                $errors++;
            }
        }

        // Add notice
        if ($updated > 0) {
            $post_type_singular = $post_type_obj->labels->singular_name ?? __('Item', WP_EXTENDED_TEXT_DOMAIN);
            $post_type_plural = $post_type_obj->labels->name ?? __('Items', WP_EXTENDED_TEXT_DOMAIN);
            Notices::add(array(
                'type' => 'success',
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
            ));
        }

        if ($errors > 0) {
            $post_type_singular = $post_type_obj->labels->singular_name ?? __('Item', WP_EXTENDED_TEXT_DOMAIN);
            $post_type_plural = $post_type_obj->labels->name ?? __('Items', WP_EXTENDED_TEXT_DOMAIN);
            Notices::add(array(
                'type' => 'error',
                'message' => sprintf(
                    _n(
                        'Failed to move %1$d %2$s.',
                        'Failed to move %1$d %3$s.',
                        $errors,
                        WP_EXTENDED_TEXT_DOMAIN
                    ),
                    $errors,
                    $post_type_singular,
                    $post_type_plural
                ),
            ));
        }

        return $redirect_url;
    }
}
