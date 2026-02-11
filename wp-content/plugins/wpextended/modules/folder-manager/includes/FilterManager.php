<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Handles folder filtering in admin list views
 */
class FilterManager
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
        // Handle filtering using parse_query filter (for list view)
        add_filter('parse_query', array($this, 'filterQuery'));

        // Handle filtering for media library grid view (AJAX)
        add_filter('ajax_query_attachments_args', array($this, 'filterAjaxAttachments'), 10, 1);
    }

    /**
     * Filter query by folder
     *
     * @param \WP_Query $query Query object
     * @return void
     */
    public function filterQuery(\WP_Query $query): void
    {
        global $pagenow;

        // Skip if filters are suppressed (e.g., during count calculations)
        if (isset($query->query_vars['suppress_filters']) && $query->query_vars['suppress_filters']) {
            return;
        }

        // Only run on admin list pages (edit.php, upload.php) for main queries
        // This prevents the filter from affecting count calculations
        if (!is_admin() || ($pagenow !== 'edit.php' && $pagenow !== 'upload.php')) {
            return;
        }

        // Skip if this is not the main query (count queries are usually not main queries)
        if (!$query->is_main_query()) {
            return;
        }

        // Use filter_input for better security
        $folder_id_raw = filter_input(INPUT_GET, 'wpext_folder', FILTER_VALIDATE_INT);

        if ($folder_id_raw === false || $folder_id_raw === null) {
            return; // No filter selected - show all
        }

        $folder_id = absint($folder_id_raw);

        // Get post type from query or GET parameter
        $post_type_raw = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // For upload.php, default to attachment
        if ($pagenow === 'upload.php' && empty($post_type_raw)) {
            $post_type = 'attachment';
        } else {
            $post_type = !empty($post_type_raw) ? sanitize_key($post_type_raw) : 'post';
        }

        // Ensure query post type matches (or set it if not set)
        if (isset($query->query_vars['post_type'])) {
            if ($query->query_vars['post_type'] !== $post_type) {
                return;
            }
        } else {
            // Set post type if not already set
            $query->set('post_type', $post_type);
        }

        if (!$this->module->isPostTypeEnabled($post_type)) {
            return;
        }

        // Ensure taxonomy is registered
        if (!taxonomy_exists($this->taxonomy)) {
            return;
        }

        // Build tax_query
        $tax_query_item = null;

        if ($folder_id === 0) {
            // Show uncategorized (items without folder)
            // Get all folder term IDs for this post type to exclude them
            $folder_manager = new FolderManager($this->module);
            $folders = $folder_manager->getFoldersForPostType($post_type);

            if (!empty($folders)) {
                $term_ids = array_map(function ($folder) {
                    return $folder->term_id;
                }, $folders);

                // Exclude items that have any folder assigned
                $tax_query_item = array(
                    'taxonomy' => $this->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_ids,
                    'operator' => 'NOT IN',
                );
            } else {
                // No folders exist, so all items are uncategorized
                // Use NOT EXISTS operator
                $tax_query_item = array(
                    'taxonomy' => $this->taxonomy,
                    'operator' => 'NOT EXISTS',
                );
            }
        } else {
            // Show items in specific folder
            $tax_query_item = array(
                'taxonomy' => $this->taxonomy,
                'field' => 'term_id',
                'terms' => $folder_id,
            );
        }

        // Merge with existing tax_query if present
        if (!empty($query->query_vars['tax_query']) && is_array($query->query_vars['tax_query'])) {
            $existing_tax_query = $query->query_vars['tax_query'];
            $new_tax_query = array();

            // Preserve relation if set
            if (isset($existing_tax_query['relation'])) {
                $new_tax_query['relation'] = $existing_tax_query['relation'];
            } else {
                $new_tax_query['relation'] = 'AND';
            }

            // Add our folder filter first
            $new_tax_query[] = $tax_query_item;

            // Add existing tax_query items (skip relation key)
            foreach ($existing_tax_query as $key => $item) {
                if ($key !== 'relation' && is_array($item)) {
                    $new_tax_query[] = $item;
                }
            }

            $query->set('tax_query', $new_tax_query);
        } else {
            // Set new tax_query
            $query->set('tax_query', array($tax_query_item));
        }

        // Remove wpext_folder from query_vars to prevent conflicts
        unset($query->query_vars['wpext_folder']);
    }

    /**
     * Filter AJAX attachment queries for media library grid view
     *
     * @param array $query Query arguments
     * @return array Modified query arguments
     */
    public function filterAjaxAttachments(array $query): array
    {
        // Return early: WordPress Gallery request (has post__in)
        if (isset($query['post__in'])) {
            return $query;
        }

        // Ensure taxonomy is registered
        if (!taxonomy_exists($this->taxonomy)) {
            return $query;
        }

        // WordPress media library queries default to 'attachment' post type
        // Don't filter if explicitly set to something else
        if (isset($query['post_type']) && $query['post_type'] !== 'attachment') {
            return $query;
        }

        // Get folder ID from query parameter or REQUEST (must allow 0 for uncategorized)
        $folder_id = null;

        // Check direct query array first (from JavaScript options.data.query)
        if (array_key_exists($this->taxonomy, $query) && $query[$this->taxonomy] !== null) {
            $folder_id = is_numeric($query[$this->taxonomy]) ? absint($query[$this->taxonomy]) : null;
        }

        // Check nested query param in GET (AJAX 'query' payload)
        if ($folder_id === null) {
            $request_query = filter_input(INPUT_GET, 'query', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (is_array($request_query) && array_key_exists($this->taxonomy, $request_query)) {
                $folder_id_raw = filter_var($request_query[$this->taxonomy], FILTER_VALIDATE_INT);
                if ($folder_id_raw !== false && $folder_id_raw !== null) {
                    $folder_id = absint($folder_id_raw);
                }
            }
        }

        // Check POST data (AJAX requests often use POST)
        if ($folder_id === null) {
            $post_query = filter_input(INPUT_POST, 'query', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (is_array($post_query) && array_key_exists($this->taxonomy, $post_query)) {
                $folder_id_raw = filter_var($post_query[$this->taxonomy], FILTER_VALIDATE_INT);
                if ($folder_id_raw !== false && $folder_id_raw !== null) {
                    $folder_id = absint($folder_id_raw);
                }
            }
        }

        // Check REQUEST superglobal (covers both GET and POST)
        if ($folder_id === null && isset($_REQUEST['query'][$this->taxonomy])) {
            $folder_id_raw = filter_var($_REQUEST['query'][$this->taxonomy], FILTER_VALIDATE_INT);
            if ($folder_id_raw !== false && $folder_id_raw !== null) {
                $folder_id = absint($folder_id_raw);
            }
        }

        // Fallback to top-level GET/POST param
        if ($folder_id === null) {
            $folder_id_raw = filter_input(INPUT_GET, 'wpext_folder', FILTER_VALIDATE_INT);
            if ($folder_id_raw === false || $folder_id_raw === null) {
                $folder_id_raw = filter_input(INPUT_POST, 'wpext_folder', FILTER_VALIDATE_INT);
            }
            if ($folder_id_raw !== false && $folder_id_raw !== null) {
                $folder_id = absint($folder_id_raw);
            }
        }

        // Check referer URL for folder parameter (AJAX requests often don't include it in query)
        if ($folder_id === null && isset($_SERVER['HTTP_REFERER'])) {
            $referer_url = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
            if ($referer_url) {
                parse_str($referer_url, $referer_params);
                if (isset($referer_params['wpext_folder'])) {
                    $folder_id_raw = filter_var($referer_params['wpext_folder'], FILTER_VALIDATE_INT);
                    if ($folder_id_raw !== false && $folder_id_raw !== null) {
                        $folder_id = absint($folder_id_raw);
                    }
                }
            }
        }

        // All items (no folder filter)
        if ($folder_id === null) {
            return $query;
        }

        // Ensure post_type is set to attachment
        $query['post_type'] = 'attachment';

        // Set up tax_query structure
        if (isset($query['tax_query']) && is_array($query['tax_query'])) {
            $query['tax_query']['relation'] = 'AND';
        } else {
            $query['tax_query'] = array('relation' => 'AND');
        }

        // Uncategorized (folder_id = 0)
        if ($folder_id === 0) {
            // Use NOT EXISTS to get items with no terms in this taxonomy
            // This is more reliable than NOT IN which can have issues
            $query['tax_query'][] = array(
                'taxonomy' => $this->taxonomy,
                'operator' => 'NOT EXISTS',
            );
        } else {
            // Specific folder
            $query['tax_query'][] = array(
                'taxonomy' => $this->taxonomy,
                'terms' => $folder_id,
                'field' => 'term_id',
                'include_children' => false,
            );
        }

        // Remove taxonomy from query to prevent conflicts
        unset($query[$this->taxonomy]);

        return $query;
    }
}
