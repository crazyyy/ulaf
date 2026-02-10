<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Adds folder column to admin list views
 */
class AdminColumns
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

        // Add columns for each enabled post type
        foreach ($enabled_post_types as $post_type) {
            if ($post_type === 'attachment') {
                add_filter('manage_media_columns', array($this, 'addColumn'));
                add_action('manage_media_custom_column', array($this, 'populateColumn'), 10, 2);
            } else {
                add_filter("manage_edit-{$post_type}_columns", array($this, 'addColumn'));
                add_action("manage_{$post_type}_posts_custom_column", array($this, 'populateColumn'), 10, 2);
            }
        }

        // Make column sortable
        add_filter('manage_edit-post_sortable_columns', array($this, 'makeSortable'));
        add_filter('manage_edit-page_sortable_columns', array($this, 'makeSortable'));
        add_filter('manage_upload_sortable_columns', array($this, 'makeSortable'));

        // Handle sorting
        add_action('pre_get_posts', array($this, 'handleSorting'));
    }

    /**
     * Add folder column
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function addColumn(array $columns): array
    {
        $new_columns = array();
        $inserted = false;

        foreach ($columns as $key => $label) {
            // Insert after title column
            if ($key === 'title' || $key === 'name') {
                $new_columns[$key] = $label;
                $new_columns['wpext_folder'] = __('Folder', WP_EXTENDED_TEXT_DOMAIN);
                $inserted = true;
            } else {
                $new_columns[$key] = $label;
            }
        }

        // If title/name column not found, add at beginning
        if (!$inserted) {
            $new_columns = array_merge(
                array('wpext_folder' => __('Folder', WP_EXTENDED_TEXT_DOMAIN)),
                $new_columns
            );
        }

        return $new_columns;
    }

    /**
     * Populate folder column
     *
     * @param string $column_name Column name
     * @param int $post_id Post ID
     * @return void
     */
    public function populateColumn(string $column_name, int $post_id): void
    {
        if ($column_name !== 'wpext_folder') {
            return;
        }

        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $terms = wp_get_object_terms($post_id, $this->taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            echo '<span class="wpext-folder-uncategorized">' . esc_html__('Uncategorized', WP_EXTENDED_TEXT_DOMAIN) . '</span>';
            return;
        }

        $folder = $terms[0];
        echo '<span class="wpext-folder-name">' . esc_html($folder->name) . '</span>';
    }

    /**
     * Make folder column sortable
     *
     * @param array $columns Sortable columns
     * @return array Modified columns
     */
    public function makeSortable(array $columns): array
    {
        $columns['wpext_folder'] = 'wpext_folder';
        return $columns;
    }

    /**
     * Handle folder column sorting
     *
     * @param \WP_Query $query Query object
     * @return void
     */
    public function handleSorting(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');
        if ($orderby !== 'wpext_folder') {
            return;
        }

        // Use WordPress built-in taxonomy query with orderby
        // Set orderby to use taxonomy term name
        $query->set('orderby', 'title'); // Fallback for posts without terms

        // Add filters to modify the query
        add_filter('posts_join', array($this, 'addTaxonomyJoin'), 10, 2);
        add_filter('posts_orderby', array($this, 'orderByTaxonomy'), 10, 2);
    }

    /**
     * Add JOIN clause for taxonomy terms
     *
     * @param string $join JOIN clause
     * @param \WP_Query $query Query object
     * @return string Modified JOIN clause
     */
    public function addTaxonomyJoin(string $join, \WP_Query $query): string
    {
        global $wpdb;

        // Only modify if we're sorting by folder (check GET parameter, not query object)
        if (!$this->isSortingByFolder($query)) {
            return $join;
        }

        // Use WordPress database abstraction with prepared statement
        $taxonomy = sanitize_key($this->taxonomy);

        $join .= $wpdb->prepare(
            " LEFT JOIN (
                SELECT tr.object_id, t.name as term_name
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tt.taxonomy = %s
            ) AS folder_terms ON {$wpdb->posts}.ID = folder_terms.object_id",
            $taxonomy
        );

        return $join;
    }

    /**
     * Order posts by taxonomy term name using WordPress functions
     *
     * @param string $orderby ORDER BY clause
     * @param \WP_Query $query Query object
     * @return string Modified ORDER BY clause
     */
    public function orderByTaxonomy(string $orderby, \WP_Query $query): string
    {
        global $wpdb;

        // Only modify if we're sorting by folder
        if (!$this->isSortingByFolder($query)) {
            return $orderby;
        }

        // Get order direction and sanitize
        $order = $query->get('order') ?: 'ASC';
        $order = strtoupper($order);
        if (!in_array($order, array('ASC', 'DESC'), true)) {
            $order = 'ASC';
        }

        // Use WordPress esc_sql for order direction
        $order_escaped = esc_sql($order);

        // Order by term name first, then post title
        $orderby = "COALESCE(folder_terms.term_name, '') {$order_escaped}, {$wpdb->posts}.post_title {$order_escaped}";

        // Remove filters after use to prevent affecting other queries
        remove_filter('posts_join', array($this, 'addTaxonomyJoin'), 10);
        remove_filter('posts_orderby', array($this, 'orderByTaxonomy'), 10);

        return $orderby;
    }

    /**
     * Check if we're sorting by folder column
     *
     * @param \WP_Query $query Query object
     * @return bool True if sorting by folder
     */
    private function isSortingByFolder(\WP_Query $query): bool
    {
        // Check orderby from request (for admin list sorting)
        $orderby = filter_input(INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $orderby === 'wpext_folder';
    }
}
