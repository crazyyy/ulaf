<?php

namespace Wpextended\Modules\FolderManager;

use Wpextended\Modules\BaseModule;
use Wpextended\Includes\Utils;
use Wpextended\Modules\FolderManager\Includes\{
    TaxonomyManager,
    FolderManager,
    AdminColumns,
    FilterManager,
    BulkActions,
    EditorIntegration,
    MediaLibraryIntegration,
    SidebarWidget
};
use Wpextended\Modules\FolderManager\Rest\Controller as RestController;

/**
 * Folder Manager Module Bootstrap
 *
 * Provides folder organization for posts and media using hierarchical taxonomy.
 */
class Bootstrap extends BaseModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('folder-manager');
    }

    /**
     * Initialize the module
     *
     * @return void
     */
    protected function init(): void
    {
        // Register taxonomy
        new TaxonomyManager($this);

        // Initialize core components
        new FolderManager($this);
        new RestController($this);

        // Admin UI components
        if (is_admin()) {
            new FilterManager($this);
            new BulkActions($this);
            new EditorIntegration($this);
            new MediaLibraryIntegration($this);
            new SidebarWidget($this);

            // Enqueue assets for both classic and block editor
            add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));
            add_action('enqueue_block_editor_assets', array($this, 'enqueueBlockEditorAssets'));
        }
    }

    /**
     * Get enabled post types from settings
     *
     * @return array Array of enabled post type slugs
     */
    public function getEnabledPostTypes(): array
    {
        // Free version only supports Media Library (attachment)
        if (!defined('WP_EXTENDED_PRO') || WP_EXTENDED_PRO === false) {
            return array('attachment');
        }

        // Pro version can have custom post types from settings
        $post_types = $this->getSetting('post_types', array('attachment'));
        return is_array($post_types) ? $post_types : array('attachment');
    }

    /**
     * Check if a post type is enabled
     *
     * @param string $post_type Post type slug
     * @return bool True if enabled
     */
    public function isPostTypeEnabled(string $post_type): bool
    {
        // Free version only allows Media Library
        if (!defined('WP_EXTENDED_PRO') || WP_EXTENDED_PRO === false) {
            return $post_type === 'attachment';
        }

        // Pro version: check settings and allow filtering
        $enabled = $this->getEnabledPostTypes();
        $is_enabled = in_array($post_type, $enabled, true);

        /**
         * Filter whether a post type is enabled for folder manager
         *
         * @param bool $is_enabled Whether the post type is enabled
         * @param string $post_type Post type slug
         * @param Bootstrap $module Module instance
         */
        return apply_filters('wpextended/folder-manager/is_post_type_enabled', $is_enabled, $post_type, $this);
    }

    /**
     * Get all available post types for settings
     *
     * @return array Array of post_type => label
     */
    public function getAvailablePostTypes(): array
    {
        $post_types = get_post_types(array(
            'public' => true,
            'show_ui' => true,
        ), 'objects');

        $choices = array();
        foreach ($post_types as $post_type) {
            $choices[$post_type->name] = $post_type->labels->name;
        }

        // Ensure attachment is included and label it as "Media Library"
        $choices['attachment'] = __('Media Library', WP_EXTENDED_TEXT_DOMAIN);

        return apply_filters('wpextended/folder-manager/available_post_types', $choices);
    }

    /**
     * Get settings fields for the module
     *
     * @return array Settings configuration
     */
    protected function getSettingsFields(): array
    {
        // Free version has no settings
        return array();
    }

    /**
     * Validate settings before save
     *
     * @param array $validations Current validation errors
     * @param array $input Input data to validate
     * @return array Updated validation errors
     */
    protected function validate($validations, $input): array
    {
        // Free version has no settings to validate
        return $validations;
    }

    /**
     * Handle settings save
     *
     * @param array $input Input data
     * @return array Modified input data
     */
    protected function onSave($input): array
    {
        // Free version has no settings to save
        return $input;
    }

    /**
     * Enqueue admin assets
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Only enqueue on post/media list pages
        $is_list_page = ($screen->base === 'edit' || $screen->base === 'upload');
        if (!$is_list_page) {
            return;
        }

        // Check if current post type is enabled
        $post_type = $screen->post_type ?? 'attachment';
        if (!$this->isPostTypeEnabled($post_type)) {
            return;
        }

        Utils::enqueueNotify();
        Utils::enqueueStyle(
            'wpext-folder-manager',
            $this->getPath('assets/css/folder-manager.css')
        );
        Utils::enqueueScript(
            'wpext-sortable',
            'includes/framework/assets/lib/sortable/sortable.min.js'
        );
        Utils::enqueueScript(
            'wpext-folder-manager',
            $this->getPath('assets/js/folder-manager.js'),
            array('jquery', 'wpext-notify', 'wpext-sortable')
        );

        // Get folders and uncategorized count for initial load
        $folder_manager = new FolderManager($this);
        $folders = $folder_manager->getFolderTree($post_type);
        $uncategorized_count = $folder_manager->getUncategorizedCount($post_type);

        // Get post type labels for notifications
        $post_type_obj = get_post_type_object($post_type);
        $post_type_singular = $post_type_obj ? $post_type_obj->labels->singular_name : __('Item', WP_EXTENDED_TEXT_DOMAIN);
        $post_type_plural = $post_type_obj ? $post_type_obj->labels->name : __('Items', WP_EXTENDED_TEXT_DOMAIN);

        wp_localize_script(
            'wpext-folder-manager',
            'wpextFolderManager',
            array(
                'restUrl' => rest_url(WP_EXTENDED_API_NAMESPACE . '/folder-manager'),
                'nonce' => wp_create_nonce('wp_rest'),
                'postType' => $post_type,
                'postTypeSingular' => $post_type_singular,
                'postTypePlural' => $post_type_plural,
                'folders' => $folders,
                'uncategorizedCount' => $uncategorized_count,
                'settings' => array(
                    'showFolderCounts' => (bool) $this->getSetting('show_folder_counts', true),
                ),
                'i18n' => array(
                    'allFolders' => __('All Folders', WP_EXTENDED_TEXT_DOMAIN),
                    'uncategorized' => __('Uncategorized', WP_EXTENDED_TEXT_DOMAIN),
                    'folder' => __('Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'newFolder' => __('New Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'newChildFolder' => __('New Child Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'addChildFolder' => __('Add Child Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'createFolder' => __('Create Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'renameFolder' => __('Rename Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'deleteFolder' => __('Delete Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'moveFolder' => __('Move Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'folderName' => __('Folder Name', WP_EXTENDED_TEXT_DOMAIN),
                    'cancel' => __('Cancel', WP_EXTENDED_TEXT_DOMAIN),
                    'save' => __('Save', WP_EXTENDED_TEXT_DOMAIN),
                    'delete' => __('Delete', WP_EXTENDED_TEXT_DOMAIN),
                    'confirmDelete' => __('Are you sure you want to delete this folder? All items will be moved to Uncategorized.', WP_EXTENDED_TEXT_DOMAIN),
                    'folderCreated' => __('Folder created successfully', WP_EXTENDED_TEXT_DOMAIN),
                    'folderRenamed' => __('Folder renamed successfully', WP_EXTENDED_TEXT_DOMAIN),
                    'folderDeleted' => __('Folder deleted successfully', WP_EXTENDED_TEXT_DOMAIN),
                    'folderMoved' => __('Folder moved successfully', WP_EXTENDED_TEXT_DOMAIN),
                    'error' => __('An error occurred', WP_EXTENDED_TEXT_DOMAIN),
                    'items' => __('items', WP_EXTENDED_TEXT_DOMAIN),
                    'loadingFolders' => __('Loading folders...', WP_EXTENDED_TEXT_DOMAIN),
                    'folderActions' => __('Folder Actions', WP_EXTENDED_TEXT_DOMAIN),
                    'itemMovedToFolder' => __('{type} moved to {folder}', WP_EXTENDED_TEXT_DOMAIN),
                    'itemRemovedFromFolder' => __('{type} removed from folder', WP_EXTENDED_TEXT_DOMAIN),
                ),
            )
        );
    }

    /**
     * Enqueue block editor assets
     *
     * @return void
     */
    public function enqueueBlockEditorAssets(): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Only enqueue on post edit pages
        if ($screen->base !== 'post') {
            return;
        }

        $post_type = $screen->post_type ?? '';
        if (!$this->isPostTypeEnabled($post_type)) {
            return;
        }

        // Get current folder for the post
        $post_id = get_the_ID();
        $current_folders = $post_id ? wp_get_object_terms($post_id, TaxonomyManager::getTaxonomy()) : array();
        $current_folder_id = !empty($current_folders) && !is_wp_error($current_folders) ? $current_folders[0]->term_id : 0;

        // Get folders for this post type
        $folder_manager = new Includes\FolderManager($this);
        $folders = $folder_manager->getFolderTree($post_type);

        Utils::enqueueScript(
            'wpext-folder-manager-editor',
            $this->getPath('assets/js/editor.js'),
            array('wp-plugins', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-dom-ready')
        );

        wp_localize_script(
            'wpext-folder-manager-editor',
            'wpextFolderManagerEditor',
            array(
                'restUrl' => rest_url(WP_EXTENDED_API_NAMESPACE . '/folder-manager'),
                'nonce' => wp_create_nonce('wp_rest'),
                'postType' => $post_type,
                'postId' => $post_id,
                'taxonomy' => TaxonomyManager::getTaxonomy(),
                'currentFolderId' => $current_folder_id,
                'folders' => $folders,
                'settings' => array(
                ),
                'i18n' => array(
                    'panelTitle' => __('Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'selectLabel' => __('Select Folder', WP_EXTENDED_TEXT_DOMAIN),
                    'uncategorized' => __('Uncategorized', WP_EXTENDED_TEXT_DOMAIN),
                    'noFolders' => __('No folders available', WP_EXTENDED_TEXT_DOMAIN),
                    'folderUpdated' => __('Folder updated successfully.', WP_EXTENDED_TEXT_DOMAIN),
                    'folderUpdateError' => __('Failed to update folder.', WP_EXTENDED_TEXT_DOMAIN),
                    'saving' => __('Saving...', WP_EXTENDED_TEXT_DOMAIN),
                ),
            )
        );
    }

    /**
     * Called when module is activated
     *
     * @return void
     */
    protected function onActivate(): void
    {
        // Ensure taxonomy is registered
        flush_rewrite_rules();
    }
}
