<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Adds folder field to post/media editors
 */
class EditorIntegration
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

        // Add meta box for each enabled post type
        foreach ($enabled_post_types as $post_type) {
            if ($post_type !== 'attachment') {
                add_action('add_meta_boxes', array($this, 'addMetaBox'));
            }
        }

        // Save folder on post save
        add_action('save_post', array($this, 'saveFolder'), 10, 2);

        // Add folder field to media editor
        add_filter('attachment_fields_to_edit', array($this, 'addMediaField'), 10, 2);
        add_filter('attachment_fields_to_save', array($this, 'saveMediaField'), 10, 2);

        // Apply default folder behavior for new items
        add_action('wp_insert_post', array($this, 'applyDefaultFolder'), 10, 3);
    }

    /**
     * Add folder meta box
     *
     * @param string $post_type Post type
     * @return void
     */
    public function addMetaBox(string $post_type): void
    {
        // Skip meta box for block editor - use native integration instead
        if (\Wpextended\Includes\Utils::isBlockEditor()) {
            return;
        }

        if (!$this->module->isPostTypeEnabled($post_type)) {
            return;
        }

        add_meta_box(
            'wpext_folder',
            __('Folders', WP_EXTENDED_TEXT_DOMAIN),
            array($this, 'renderMetaBox'),
            $post_type,
            'side',
            'default'
        );
    }

    /**
     * Render folder meta box
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        wp_nonce_field('wpext_folder_save', 'wpext_folder_nonce');

        $current_folders = wp_get_object_terms($post->ID, $this->taxonomy);
        $current_folder_id = !empty($current_folders) && !is_wp_error($current_folders) ? $current_folders[0]->term_id : 0;

        $folder_manager = new FolderManager($this->module);
        $folders = $folder_manager->getFoldersForPostType($post->post_type);

        $allow_nested = $this->module->getSetting('allow_nested_folders', true);

        ?>
        <select name="wpext_folder_id" id="wpext-folder-select" style="width: 100%;">
            <option value="0"><?php esc_html_e('Uncategorized', WP_EXTENDED_TEXT_DOMAIN); ?></option>
            <?php
            if ($allow_nested) {
                $this->renderTreeSelect($folders, $current_folder_id);
            } else {
                $this->renderFlatSelect($folders, $current_folder_id);
            }
            ?>
        </select>
        <p class="description">
            <?php esc_html_e('Select a folder for this item.', WP_EXTENDED_TEXT_DOMAIN); ?>
        </p>
        <?php
    }

    /**
     * Render tree select options
     *
     * @param array $folders Array of term objects
     * @param int $selected Selected folder ID
     * @param int $parent_id Parent ID (0 for root)
     * @param int $depth Current depth
     * @return void
     */
    private function renderTreeSelect(array $folders, int $selected, int $parent_id = 0, int $depth = 0): void
    {
        foreach ($folders as $folder) {
            if ($folder->parent !== $parent_id) {
                continue;
            }

            $indent = $depth > 0 ? str_repeat('— ', $depth) : '';
            $folder_id = $folder->term_id;
            ?>
            <option value="<?php echo esc_attr($folder_id); ?>" <?php selected($selected, $folder_id); ?>>
                <?php echo esc_html($indent . $folder->name); ?>
            </option>
            <?php
            // Render children
            $this->renderTreeSelect($folders, $selected, $folder_id, $depth + 1);
        }
    }

    /**
     * Render flat select options
     *
     * @param array $folders Array of term objects
     * @param int $selected Selected folder ID
     * @return void
     */
    private function renderFlatSelect(array $folders, int $selected): void
    {
        foreach ($folders as $folder) {
            ?>
            <option value="<?php echo esc_attr($folder->term_id); ?>" <?php selected($selected, $folder->term_id); ?>>
                <?php echo esc_html($folder->name); ?>
            </option>
            <?php
        }
    }

    /**
     * Save folder on post save
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    public function saveFolder(int $post_id, \WP_Post $post): void
    {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Skip if block editor (handled via REST API)
        if (\Wpextended\Includes\Utils::isBlockEditor()) {
            return;
        }

        // Verify nonce using filter_input
        $nonce = filter_input(INPUT_POST, 'wpext_folder_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$nonce || !wp_verify_nonce($nonce, 'wpext_folder_save')) {
            return;
        }

        // Check post type is enabled
        if (!$this->module->isPostTypeEnabled($post->post_type)) {
            return;
        }

        // Get folder ID using filter_input
        $folder_id_raw = filter_input(INPUT_POST, 'wpext_folder_id', FILTER_VALIDATE_INT);
        $folder_id = ($folder_id_raw !== false && $folder_id_raw !== null) ? absint($folder_id_raw) : 0;
        $terms = $folder_id > 0 ? array($folder_id) : array();

        wp_set_object_terms($post_id, $terms, $this->taxonomy);
    }

    /**
     * Add folder field to media editor
     *
     * @param array $fields Attachment fields
     * @param \WP_Post $post Post object
     * @return array Modified fields
     */
    public function addMediaField(array $fields, \WP_Post $post): array
    {
        if (!$this->module->isPostTypeEnabled('attachment')) {
            return $fields;
        }

        $current_folders = wp_get_object_terms($post->ID, $this->taxonomy);
        $current_folder_id = !empty($current_folders) && !is_wp_error($current_folders) ? $current_folders[0]->term_id : 0;

        $folder_manager = new FolderManager($this->module);
        $folders = $folder_manager->getFoldersForPostType('attachment');

        $allow_nested = $this->module->getSetting('allow_nested_folders', true);

        ob_start();
        ?>
        <select name="attachments[<?php echo esc_attr($post->ID); ?>][wpext_folder_id]" id="attachments-<?php echo esc_attr($post->ID); ?>-wpext-folder">
            <option value="0"><?php esc_html_e('Uncategorized', WP_EXTENDED_TEXT_DOMAIN); ?></option>
            <?php
            if ($allow_nested) {
                $this->renderTreeSelect($folders, $current_folder_id);
            } else {
                $this->renderFlatSelect($folders, $current_folder_id);
            }
            ?>
        </select>
        <?php
        $select_html = ob_get_clean();

        $fields['wpext_folder'] = array(
            'label' => __('Folder', WP_EXTENDED_TEXT_DOMAIN),
            'input' => 'html',
            'html' => $select_html,
        );

        return $fields;
    }

    /**
     * Save folder field for media
     *
     * @param array $post Post data
     * @param array $attachment Attachment data
     * @return array Modified post data
     */
    public function saveMediaField(array $post, array $attachment): array
    {
        if (!$this->module->isPostTypeEnabled('attachment')) {
            return $post;
        }

        $post_id = $post['ID'] ?? 0;
        if (!$post_id) {
            return $post;
        }

        $folder_id = isset($attachment['wpext_folder_id']) ? absint($attachment['wpext_folder_id']) : 0;
        $terms = $folder_id > 0 ? array($folder_id) : array();

        wp_set_object_terms($post_id, $terms, $this->taxonomy);

        return $post;
    }

    /**
     * Apply default folder behavior for new items
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @param bool $update Whether this is an update
     * @return void
     */
    public function applyDefaultFolder(int $post_id, \WP_Post $post, bool $update): void
    {
        // Skip updates
        if ($update) {
            return;
        }

        // Check post type is enabled
        if (!$this->module->isPostTypeEnabled($post->post_type)) {
            return;
        }

        // No auto-assignment - items remain uncategorized by default
    }
}
