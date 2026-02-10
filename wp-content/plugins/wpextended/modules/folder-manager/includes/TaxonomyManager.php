<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Handles taxonomy registration for folders
 */
class TaxonomyManager
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
    public const TAXONOMY = 'wpext_folder';

    /**
     * Constructor
     *
     * @param Bootstrap $module Module instance
     */
    public function __construct(Bootstrap $module)
    {
        $this->module = $module;
        $this->init();
    }

    /**
     * Initialize taxonomy registration
     *
     * @return void
     */
    private function init(): void
    {
        // Register immediately to ensure it's available before parse_query runs
        $this->registerTaxonomy();

        // Also register on init hook as a fallback
        add_action('init', array($this, 'registerTaxonomy'), 10);

        // Ensure taxonomy is registered before REST API routes
        add_action('rest_api_init', array($this, 'registerTaxonomy'), 5);
    }

    /**
     * Register the folder taxonomy
     *
     * @return void
     */
    public function registerTaxonomy(): void
    {
        // Prevent duplicate registration
        if (taxonomy_exists(self::TAXONOMY)) {
            return;
        }

        $enabled_post_types = $this->module->getEnabledPostTypes();
        if (empty($enabled_post_types)) {
            return;
        }

        $allow_nested = $this->module->getSetting('allow_nested_folders', true);

        $args = array(
            'hierarchical' => (bool) $allow_nested,
            'public' => false,
            'show_ui' => false, // We handle UI manually via sidebar widget
            'show_admin_column' => false, // We handle columns manually
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_in_rest' => true, // Enable REST API support
            'rewrite' => false, // No frontend URLs needed
            'labels' => array(
                'name' => __('Folders', WP_EXTENDED_TEXT_DOMAIN),
                'singular_name' => __('Folder', WP_EXTENDED_TEXT_DOMAIN),
                'search_items' => __('Search Folders', WP_EXTENDED_TEXT_DOMAIN),
                'all_items' => __('All Folders', WP_EXTENDED_TEXT_DOMAIN),
                'parent_item' => __('Parent Folder', WP_EXTENDED_TEXT_DOMAIN),
                'parent_item_colon' => __('Parent Folder:', WP_EXTENDED_TEXT_DOMAIN),
                'edit_item' => __('Edit Folder', WP_EXTENDED_TEXT_DOMAIN),
                'update_item' => __('Update Folder', WP_EXTENDED_TEXT_DOMAIN),
                'add_new_item' => __('Add New Folder', WP_EXTENDED_TEXT_DOMAIN),
                'new_item_name' => __('New Folder Name', WP_EXTENDED_TEXT_DOMAIN),
                'not_found' => __('No folders found', WP_EXTENDED_TEXT_DOMAIN),
            ),
        );

        register_taxonomy(self::TAXONOMY, $enabled_post_types, $args);
    }

    /**
     * Get taxonomy slug
     *
     * @return string Taxonomy slug
     */
    public static function getTaxonomy(): string
    {
        return self::TAXONOMY;
    }
}
