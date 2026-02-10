<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Core folder management operations
 */
class FolderManager
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
     * Term meta key for post type association
     */
    public const POST_TYPE_META_KEY = 'wpext_folder_post_type';

    /**
     * Constructor
     *
     * @param Bootstrap $module Module instance
     */
    public function __construct(Bootstrap $module)
    {
        $this->module = $module;
        $this->taxonomy = TaxonomyManager::getTaxonomy();
    }

    /**
     * Create a new folder for a specific post type
     *
     * @param string $name Folder name
     * @param string $post_type Post type this folder belongs to
     * @param int $parent_id Parent folder ID (0 for root)
     * @return array|WP_Error Result with success status and folder data or error
     */
    public function createFolder(string $name, string $post_type, int $parent_id = 0)
    {
        // Ensure taxonomy is registered
        if (!taxonomy_exists($this->taxonomy)) {
            return new \WP_Error(
                'taxonomy_not_registered',
                __('Folder taxonomy is not registered. Please ensure the module is enabled and post types are configured.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Validate post type is enabled
        if (!$this->module->isPostTypeEnabled($post_type)) {
            return new \WP_Error(
                'invalid_post_type',
                __('Post type is not enabled for folder manager.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Check if taxonomy supports hierarchical (nested) folders
        $taxonomy_obj = get_taxonomy($this->taxonomy);
        $allow_nested = $taxonomy_obj && $taxonomy_obj->hierarchical;
        if ($parent_id > 0 && !$allow_nested) {
            return new \WP_Error(
                'nested_disabled',
                __('Nested folders are not supported by this taxonomy.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Validate parent if provided
        if ($parent_id > 0) {
            $parent = get_term($parent_id, $this->taxonomy);
            if (!$parent || is_wp_error($parent)) {
                return new \WP_Error(
                    'invalid_parent',
                    __('Invalid parent folder.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }

            // Ensure parent belongs to same post type
            $parent_post_type = get_term_meta($parent_id, self::POST_TYPE_META_KEY, true);
            if ($parent_post_type !== $post_type) {
                return new \WP_Error(
                    'parent_post_type_mismatch',
                    __('Parent folder must belong to the same post type.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }
        }

        // Sanitize folder name
        $name = sanitize_text_field($name);
        if (empty($name)) {
            return new \WP_Error(
                'empty_name',
                __('Folder name cannot be empty.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Validate folder name length (max 200 characters)
        if (mb_strlen($name) > 200) {
            return new \WP_Error(
                'name_too_long',
                __('Folder name cannot exceed 200 characters.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Check for duplicate names at same level within this post type only
        $existing = $this->getFoldersForPostType($post_type);
        foreach ($existing as $folder) {
            if ($folder->parent === $parent_id && strcasecmp($folder->name, $name) === 0) {
                return new \WP_Error(
                    'duplicate_name',
                    __('A folder with this name already exists at this level.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }
        }

        // Create term with unique slug that includes post type to avoid conflicts across post types
        $slug = sanitize_title($name);
        // Make slug unique by appending post type if needed
        // WordPress requires unique slugs, so we'll make it unique per post type
        $unique_slug = $slug . '-' . $post_type;

        // Check if this slug already exists in this taxonomy
        $existing_term = get_term_by('slug', $unique_slug, $this->taxonomy);
        if ($existing_term && !is_wp_error($existing_term)) {
            // If it exists, check if it belongs to this post type
            $existing_post_type = get_term_meta($existing_term->term_id, self::POST_TYPE_META_KEY, true);
            if ($existing_post_type === $post_type) {
                // This is a duplicate within the same post type, which we already checked above
                // But if we get here, add a number suffix
                $counter = 1;
                do {
                    $unique_slug = $slug . '-' . $post_type . '-' . $counter;
                    $existing_term = get_term_by('slug', $unique_slug, $this->taxonomy);
                    $counter++;
                } while ($existing_term && !is_wp_error($existing_term));
            }
        }

        // Create term with unique slug but original name
        $term_data = wp_insert_term($name, $this->taxonomy, array(
            'parent' => $parent_id > 0 ? $parent_id : 0,
            'slug' => $unique_slug,
        ));

        if (is_wp_error($term_data)) {
            return $term_data;
        }

        $term_id = $term_data['term_id'];

        // Store post type association
        update_term_meta($term_id, self::POST_TYPE_META_KEY, $post_type);

        return array(
            'success' => true,
            'folder' => $this->getFolderData($term_id),
        );
    }

    /**
     * Rename a folder
     *
     * @param int $folder_id Folder term ID
     * @param string $new_name New folder name
     * @return array|WP_Error Result with success status and folder data or error
     */
    public function renameFolder(int $folder_id, string $new_name)
    {
        $folder = get_term($folder_id, $this->taxonomy);
        if (!$folder || is_wp_error($folder)) {
            return new \WP_Error(
                'folder_not_found',
                __('Folder not found.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        $post_type = get_term_meta($folder_id, self::POST_TYPE_META_KEY, true);
        if (!$post_type) {
            return new \WP_Error(
                'invalid_folder',
                __('Folder is missing post type association.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Sanitize folder name
        $new_name = sanitize_text_field($new_name);
        if (empty($new_name)) {
            return new \WP_Error(
                'empty_name',
                __('Folder name cannot be empty.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Validate folder name length (max 200 characters)
        if (mb_strlen($new_name) > 200) {
            return new \WP_Error(
                'name_too_long',
                __('Folder name cannot exceed 200 characters.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Check for duplicate names at same level
        $existing = $this->getFoldersForPostType($post_type);
        foreach ($existing as $existing_folder) {
            if (
                $existing_folder->term_id !== $folder_id &&
                $existing_folder->parent === $folder->parent &&
                strcasecmp($existing_folder->name, $new_name) === 0
            ) {
                return new \WP_Error(
                    'duplicate_name',
                    __('A folder with this name already exists at this level.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }
        }

        // Update term
        $result = wp_update_term($folder_id, $this->taxonomy, array(
            'name' => $new_name,
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'success' => true,
            'folder' => $this->getFolderData($folder_id),
        );
    }

    /**
     * Delete a folder and move all items to Uncategorized
     *
     * @param int $folder_id Folder term ID
     * @return array|WP_Error Result with success status or error
     */
    public function deleteFolder(int $folder_id)
    {
        $folder = get_term($folder_id, $this->taxonomy);
        if (!$folder || is_wp_error($folder)) {
            return new \WP_Error(
                'folder_not_found',
                __('Folder not found.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        $post_type = get_term_meta($folder_id, self::POST_TYPE_META_KEY, true);
        if (!$post_type) {
            return new \WP_Error(
                'invalid_folder',
                __('Folder is missing post type association.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Get all items in this folder using WordPress functions
        $term = get_term($folder_id, $this->taxonomy);
        if (!$term || is_wp_error($term)) {
            return new \WP_Error(
                'folder_not_found',
                __('Folder not found.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Get all post IDs with this term using get_posts
        $items = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => $this->taxonomy,
                    'field' => 'term_id',
                    'terms' => $folder_id,
                ),
            ),
        ));

        // Remove folder assignment from all items (move to Uncategorized)
        // Use wp_set_object_terms in batches to avoid memory issues
        $batch_size = 100;
        $batches = array_chunk($items, $batch_size);

        foreach ($batches as $batch) {
            foreach ($batch as $item_id) {
                wp_set_object_terms($item_id, array(), $this->taxonomy);
            }
        }

        // Handle child folders - move to parent or root
        $taxonomy_obj = get_taxonomy($this->taxonomy);
        $allow_nested = $taxonomy_obj && $taxonomy_obj->hierarchical;
        $child_folders = get_terms(array(
            'taxonomy' => $this->taxonomy,
            'parent' => $folder_id,
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => self::POST_TYPE_META_KEY,
                    'value' => $post_type,
                    'compare' => '=',
                ),
            ),
            'number' => 0,
        ));

        if (!is_wp_error($child_folders) && !empty($child_folders)) {
            foreach ($child_folders as $child) {
                if ($allow_nested && $folder->parent > 0) {
                    // Move to parent folder
                    wp_update_term($child->term_id, $this->taxonomy, array(
                        'parent' => $folder->parent,
                    ));
                } else {
                    // Move to root (or uncategorized if nested disabled)
                    wp_update_term($child->term_id, $this->taxonomy, array(
                        'parent' => 0,
                    ));
                }
            }
        }

        // Delete the folder
        $result = wp_delete_term($folder_id, $this->taxonomy);
        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'success' => true,
            'message' => __('Folder deleted successfully. All items moved to Uncategorized.', WP_EXTENDED_TEXT_DOMAIN),
        );
    }

    /**
     * Move a folder to a new parent
     *
     * @param int $folder_id Folder term ID
     * @param int $new_parent_id New parent folder ID (0 for root)
     * @return array|WP_Error Result with success status and folder data or error
     */
    public function moveFolder(int $folder_id, int $new_parent_id)
    {
        $folder = get_term($folder_id, $this->taxonomy);
        if (!$folder || is_wp_error($folder)) {
            return new \WP_Error(
                'folder_not_found',
                __('Folder not found.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        $post_type = get_term_meta($folder_id, self::POST_TYPE_META_KEY, true);
        if (!$post_type) {
            return new \WP_Error(
                'invalid_folder',
                __('Folder is missing post type association.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Check if taxonomy supports hierarchical (nested) folders
        $taxonomy_obj = get_taxonomy($this->taxonomy);
        $allow_nested = $taxonomy_obj && $taxonomy_obj->hierarchical;
        if ($new_parent_id > 0 && !$allow_nested) {
            return new \WP_Error(
                'nested_disabled',
                __('Nested folders are not supported by this taxonomy.', WP_EXTENDED_TEXT_DOMAIN)
            );
        }

        // Validate new parent if provided
        if ($new_parent_id > 0) {
            $new_parent = get_term($new_parent_id, $this->taxonomy);
            if (!$new_parent || is_wp_error($new_parent)) {
                return new \WP_Error(
                    'invalid_parent',
                    __('Invalid parent folder.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }

            // Ensure parent belongs to same post type
            $parent_post_type = get_term_meta($new_parent_id, self::POST_TYPE_META_KEY, true);
            if ($parent_post_type !== $post_type) {
                return new \WP_Error(
                    'parent_post_type_mismatch',
                    __('Parent folder must belong to the same post type.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }

            // Prevent circular references
            if ($this->wouldCreateCircularReference($folder_id, $new_parent_id)) {
                return new \WP_Error(
                    'circular_reference',
                    __('Cannot move folder: would create circular reference.', WP_EXTENDED_TEXT_DOMAIN)
                );
            }
        }

        // Update term parent
        $old_parent = $folder->parent;
        $result = wp_update_term($folder_id, $this->taxonomy, array(
            'parent' => $new_parent_id > 0 ? $new_parent_id : 0,
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        return array(
            'success' => true,
            'folder' => $this->getFolderData($folder_id),
        );
    }

    /**
     * Check if moving folder would create circular reference
     *
     * @param int $folder_id Folder to move
     * @param int $new_parent_id New parent folder
     * @return bool True if would create circular reference
     */
    private function wouldCreateCircularReference(int $folder_id, int $new_parent_id): bool
    {
        $current_parent = $new_parent_id;
        while ($current_parent > 0) {
            if ($current_parent === $folder_id) {
                return true; // Circular reference detected
            }
            $parent_term = get_term($current_parent, $this->taxonomy);
            if (!$parent_term || is_wp_error($parent_term)) {
                break;
            }
            $current_parent = $parent_term->parent;
        }
        return false;
    }

    /**
     * Get folder tree for a specific post type
     *
     * @param string $post_type Post type
     * @return array Array of folder objects with hierarchy
     */
    public function getFolderTree(string $post_type): array
    {
        $folders = $this->getFoldersForPostType($post_type);

        // Always build hierarchical tree
        return $this->buildTree($folders);
    }

    /**
     * Build hierarchical tree structure
     *
     * @param array $folders Flat array of folder terms
     * @param int $parent_id Parent ID (0 for root)
     * @return array Hierarchical array
     */
    private function buildTree(array $folders, int $parent_id = 0): array
    {
        $tree = array();
        foreach ($folders as $folder) {
            // Ensure both values are integers for comparison
            $folder_parent = (int) $folder->parent;
            $parent_id_int = (int) $parent_id;

            if ($folder_parent === $parent_id_int) {
                $folder_data = $this->getFolderData($folder->term_id);
                $children = $this->buildTree($folders, $folder->term_id);
                if (!empty($children)) {
                    $folder_data['children'] = $children;
                }
                $tree[] = $folder_data;
            }
        }
        return $tree;
    }

    /**
     * Get folders for a specific post type
     *
     * @param string $post_type Post type
     * @return array Array of term objects
     */
    public function getFoldersForPostType(string $post_type): array
    {
        // Ensure taxonomy is registered
        if (!taxonomy_exists($this->taxonomy)) {
            return array();
        }

        // Use meta_query to filter by post type directly in the query
        // This is much more efficient than loading all terms and filtering in PHP
        $terms = get_terms(array(
            'taxonomy' => $this->taxonomy,
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => self::POST_TYPE_META_KEY,
                    'value' => $post_type,
                    'compare' => '=',
                ),
            ),
            'number' => 0, // Get all matching terms
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return is_array($terms) ? $terms : array();
    }

    /**
     * Get folder count (total including nested subfolders)
     *
     * @param int $folder_id Folder term ID
     * @param string $post_type Post type
     * @return int Item count
     */
    public function getFolderCount(int $folder_id, string $post_type): int
    {
        // Get direct items
        $direct_count = $this->getDirectFolderCount($folder_id, $post_type);

        // Get nested subfolders count using wp_count_terms
        $child_folders_count = wp_count_terms(array(
            'taxonomy' => $this->taxonomy,
            'parent' => $folder_id,
            'meta_query' => array(
                array(
                    'key' => self::POST_TYPE_META_KEY,
                    'value' => $post_type,
                    'compare' => '=',
                ),
            ),
        ));

        $nested_count = 0;
        if (!is_wp_error($child_folders_count) && $child_folders_count > 0) {
            // Get child folders to recursively count their items
            $child_folders = get_terms(array(
                'taxonomy' => $this->taxonomy,
                'parent' => $folder_id,
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => self::POST_TYPE_META_KEY,
                        'value' => $post_type,
                        'compare' => '=',
                    ),
                ),
                'number' => 0,
            ));

            if (!is_wp_error($child_folders) && !empty($child_folders) && is_array($child_folders)) {
                foreach ($child_folders as $child) {
                    $nested_count += $this->getFolderCount($child->term_id, $post_type);
                }
            }
        }

        return $direct_count + $nested_count;
    }

    /**
     * Get direct item count in folder (excluding nested)
     *
     * @param int $folder_id Folder term ID
     * @param string $post_type Post type
     * @return int Direct item count
     */
    private function getDirectFolderCount(int $folder_id, string $post_type): int
    {
        // Use WordPress get_objects_in_term to get post IDs assigned to this term
        $post_ids = get_objects_in_term($folder_id, $this->taxonomy);

        if (is_wp_error($post_ids) || empty($post_ids)) {
            return 0;
        }

        // Attachments use 'inherit' status, other post types use standard statuses
        $post_statuses = ($post_type === 'attachment')
            ? array('inherit')
            : array('publish', 'pending', 'draft', 'private', 'future');

        // Filter by post type using get_posts with post__in
        $posts = get_posts(array(
            'post_type' => $post_type,
            'post__in' => $post_ids,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => $post_statuses,
            'suppress_filters' => true, // Prevent parse_query filter from affecting count calculation
        ));

        return count($posts);
    }

    /**
     * Get uncategorized count for a post type
     *
     * @param string $post_type Post type
     * @return int Count of items without folder
     */
    public function getUncategorizedCount(string $post_type): int
    {
        // Define post statuses to count (must match what we filter for posts_with_folders)
        // Attachments use 'inherit' status, other post types use standard statuses
        $post_statuses = ($post_type === 'attachment')
            ? array('inherit')
            : array('publish', 'pending', 'draft', 'private', 'future');

        // Count total posts with the correct statuses only
        $total_count = 0;
        $post_counts = wp_count_posts($post_type);
        foreach ($post_statuses as $status) {
            if (isset($post_counts->$status)) {
                $total_count += (int) $post_counts->$status;
            }
        }

        if ($total_count === 0) {
            return 0;
        }

        // Get all folders for this post type
        $folders = $this->getFoldersForPostType($post_type);
        if (empty($folders)) {
            return $total_count;
        }

        // Get all post IDs that have folders assigned using get_objects_in_term
        $all_post_ids = array();
        foreach ($folders as $folder) {
            $post_ids = get_objects_in_term($folder->term_id, $this->taxonomy);
            if (!is_wp_error($post_ids) && !empty($post_ids)) {
                $all_post_ids = array_merge($all_post_ids, $post_ids);
            }
        }

        if (empty($all_post_ids)) {
            return $total_count;
        }

        // Filter by post type and count unique posts with same status filter
        $unique_post_ids = array_unique($all_post_ids);

        $posts_with_folders = get_posts(array(
            'post_type' => $post_type,
            'post__in' => $unique_post_ids,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => $post_statuses,
            'suppress_filters' => true, // Prevent parse_query filter from affecting count calculation
        ));

        $posts_with_folders_count = count($posts_with_folders);
        $uncategorized_count = max(0, $total_count - $posts_with_folders_count);

        return $uncategorized_count;
    }

    /**
     * Get folder data for API response
     *
     * @param int $term_id Term ID
     * @return array Folder data
     */
    public function getFolderData(int $term_id): array
    {
        $term = get_term($term_id, $this->taxonomy);
        if (!$term || is_wp_error($term)) {
            return array();
        }

        $post_type = get_term_meta($term_id, self::POST_TYPE_META_KEY, true);
        $count = 0;
        if ($post_type) {
            $count = $this->getFolderCount($term_id, $post_type);
        }

        return array(
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'parent' => $term->parent,
            'post_type' => $post_type,
            'count' => $count,
        );
    }
}
