<?php

/**
 * Current ACF Compatibility Class for Ultimate Extension for ACF
 *
 * This class handles ACF versions 6.5 and newer, providing enhanced flexible
 * content functionality with image previews and accordion behavior.
 *
 * Key Features:
 * - Image preview management for ACF flexible content layouts
 * - Admin settings page for managing preview images
 * - Accordion functionality (one layout open at a time)
 * - Asset loading (CSS/JS) for admin interface
 * - AJAX handlers for image upload and management
 * - Integration with WordPress media library
 *
 * @package Ultimate_Extension_for_ACF
 * @subpackage Current
 * @since 1.0.0
 * @author Ultimate Agency
 */

// Security Check - Prevent direct access to this file
defined('ABSPATH') or die('Direct access forbidden.');

class UEFAX_Current_Compatibility
{
    /**
     * Unique identifier for the module
     * Used for script/style handles and text domain
     *
     * @var string
     * @since 1.0.0
     */
    private static string $id = 'ultimate-extension-for-acf';

    /**
     * Preview image styles collection
     * Used to collect all preview styles and output them once
     *
     * @var array
     * @since 1.0.0
     */
    private static array $preview_styles = [];

    /**
     * Constructor
     *
     * Initialises the compatibility layer for ACF v6.5+ by loading the AJAX
     * handler and setting up WordPress hooks for admin functionality.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Load AJAX handler for current ACF versions
        require_once UEFAX_PLUGIN_DIR . 'includes/current/class-uefax-current-ajax.php';
        new UEFAX_Current_Ajax();

        // Initialise WordPress hooks for admin functionality
        $this->init_hooks();
    }

    /**
     * Initialise WordPress Hooks
     *
     * Sets up all the necessary WordPress hooks for admin functionality,
     * AJAX handlers, and ACF integration.
     *
     * @since 1.0.0
     */
    private function init_hooks(): void
    {
        // Admin interface hooks
        add_action('admin_menu', array($this, 'register_submenu_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_media_files'));
        add_action('admin_enqueue_scripts', array($this, 'admin_css'));
        add_action('admin_enqueue_scripts', array($this, 'admin_script'));

        // AJAX handlers for image management
        // Note: Using nopriv_ actions for potential frontend use
        if (is_admin()) {
            add_action('wp_ajax_uefax_getModalImage', array('UEFAX_Current_Ajax', 'get_uefax_images'));
            add_action('wp_ajax_nopriv_uefax_getModalImage', array('UEFAX_Current_Ajax', 'get_uefax_images'));
            add_action('wp_ajax_uefax_setModalImage', array('UEFAX_Current_Ajax', 'set_uefax_image'));
            add_action('wp_ajax_nopriv_uefax_setModalImage', array('UEFAX_Current_Ajax', 'set_uefax_image'));
        }

        // Hook into ACF's flexible content layout title filter
        // This adds image previews to the layout handles
        add_filter('acf/fields/flexible_content/layout_title', array($this, 'uefax_flex_preview'), 10, 4);

        // Add action to output all collected preview styles once
        add_action('acf/input/admin_footer', array($this, 'output_preview_styles'));
        add_action('admin_footer', array($this, 'output_preview_styles'));
    }

    /**
     * Load WordPress Media Library Assets
     *
     * Enqueues the WordPress media library scripts and styles needed for
     * the image upload functionality in the admin interface.
     *
     * @since 1.0.0
     */
    public function load_media_files(): void
    {
        // Enqueue WordPress media library for image selection
        wp_enqueue_media();
    }

    /**
     * Register Admin Stylesheets
     *
     * Enqueues the plugin's CSS file for admin interface styling.
     * Depends on ACF Pro input styles for consistent appearance.
     * Only loads on ACF-related pages and the plugin's settings page.
     *
     * @since 1.0.0
     */
    public function admin_css(): void
    {
        // Only load on ACF-related pages and our settings page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Load on ACF pages, our settings page, and post/page edit screens
        $acf_pages = array(
            'acf-field-group',
            'acf-field-group_page_acf-image-preview-settings',
            'post',
            'page'
        );

        $is_acf_page = false;
        foreach ($acf_pages as $page) {
            if (strpos($screen->id, $page) !== false) {
                $is_acf_page = true;
                break;
            }
        }

        if (!$is_acf_page) {
            return;
        }

        // Register and enqueue the main stylesheet
        wp_register_style(
            self::$id,                                           // Handle
            UEFAX_PLUGIN_URL . 'assets/current/style.css',  // URL
            array('acf-pro-input'),                             // Dependencies
            UEFAX_VERSION                      // Version
        );
        wp_enqueue_style(self::$id);
    }

    /**
     * Register Admin Scripts
     *
     * Enqueues JavaScript files needed for plugin functionality:
     * - Upload a script for image management
     * - Accordion script for layout behaviour
     *
     * Also localises script data for AJAX communication.
     * Only loads on ACF-related pages and the plugin's settings page.
     *
     * @since 1.0.0
     */
    public function admin_script(): void
    {
        // Only load on ACF-related pages and our settings page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Load on ACF pages, our settings page, and post/page edit screens
        $acf_pages = array(
            'acf-field-group',
            'acf-field-group_page_acf-image-preview-settings',
            'post',
            'page'
        );

        $is_acf_page = false;
        foreach ($acf_pages as $page) {
            if (strpos($screen->id, $page) !== false) {
                $is_acf_page = true;
                break;
            }
        }

        if (!$is_acf_page) {
            return;
        }
        // Register and enqueue upload functionality script
        wp_register_script(
            self::$id . '-upload',                             // Handle
            UEFAX_PLUGIN_URL . 'assets/current/upload.js',  // URL
            array('acf-pro-input'),                            // Dependencies
            UEFAX_VERSION,                    // Version
            true                                               // Load in footer
        );
        wp_enqueue_script(self::$id . '-upload');

        // Localise script data for AJAX communication
        // Provides nonce for security and AJAX URL for requests
        wp_localize_script(self::$id . '-upload', 'uefax_ajax', array(
            'nonce' => wp_create_nonce('uefax-ajax-nonce'),     // Security nonce
            'ajax_url' => admin_url('admin-ajax.php')          // AJAX endpoint
        ));

        // Register and enqueue accordion script for flexible content fields
        // Ensures only one layout is open at a time
        wp_register_script(
            self::$id . '-accordion',                          // Handle
            UEFAX_PLUGIN_URL . 'assets/current/accordion.js',  // URL
            array('jquery', 'acf-pro-input'),                  // Dependencies
            UEFAX_VERSION,                    // Version
            true                                               // Load in footer
        );
        wp_enqueue_script(self::$id . '-accordion');
    }

    /**
     * Register Image Preview Settings Submenu
     *
     * Adds a submenu page under ACF Field Groups for managing preview images.
     * Only visible to users with 'manage_options' capability.
     *
     * @since 1.0.0
     */
    public function register_submenu_page(): void
    {
        // Add a submenu page under ACF Field Groups
        add_submenu_page(
            'edit.php?post_type=acf-field-group',             // Parent slug
            __('Image Preview Settings', 'ultimate-extension-for-acf'),  // Page title
            __('Image Preview Settings', 'ultimate-extension-for-acf'),  // Menu title
            'manage_options',                                  // Capability required
            'acf-image-preview-settings',                     // Menu slug
            array($this, 'image_preview_settings_callback')   // Callback function
        );
    }

    /**
     * Image Preview Settings Page Callback
     *
     * Renders the admin page for managing preview images. Includes security
     * check and calls the table rendering method.
     *
     * @since 1.0.0
     */
    public function image_preview_settings_callback(): void
    {
        // Security check - ensure the user has proper permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'ultimate-extension-for-acf'));
        }

        // Render the settings table with upload functionality
        $this->render_simple_table();
    }

    /**
     * Render a Simple Table with Individual Upload Buttons
     *
     * Renders the main admin interface table showing all ACF flexible content
     * layouts with their current preview images and upload buttons.
     *
     * Table Structure:
     * - Row number
     * - Component title (layout label)
     * - Preview image (with optimal width note)
     * - Update button for image management
     *
     * @since 1.0.0
     */
    private function render_simple_table(): void
    {
        // Main wrapper div with styling class
        echo '<div class="wrap image-preview-settings-wrap">';

        // Page title
        echo '<h1 class="image-preview-settings-title">' .
             esc_html__('Image Preview Settings', 'ultimate-extension-for-acf') . '</h1>';

        // Start a table with WordPress standard classes
        echo '<table class="wp-list-table widefat fixed striped table-view-list image-preview-settings">';

        // Table header
        echo '<thead><tr>';
        echo '<th scope="col" class="manage-column column-number">' . esc_html__('#', 'ultimate-extension-for-acf') . '</th>';
        echo '<th scope="col" class="manage-column column-layout-title">' . esc_html__('Component Title', 'ultimate-extension-for-acf') . '</th>';
        echo '<th scope="col" class="manage-column column-preview-image">' .
             esc_html__('Preview Image', 'ultimate-extension-for-acf') .
             ' <small><i>(' . esc_html__('Optimal image width 420px', 'ultimate-extension-for-acf') . ')</i></small></th>';
        echo '<th scope="col" class="manage-column column-update">' . esc_html__('Update', 'ultimate-extension-for-acf') . '</th>';
        echo '</tr></thead>';

        // Table body - populated by a separate method
        echo '<tbody>';
        $this->populate_table_rows();
        echo '</tbody>';

        // Close table and wrapper
        echo '</table></div>';
    }

    /**
     * Populate Table Rows with Layout Data
     *
     * Iterates through all ACF field groups to find flexible content fields
     * and their layouts, then renders a table row for each layout with its
     * current preview image and management controls.
     *
     * @since 1.0.0
     */
    private function populate_table_rows(): void
    {
        // Get all ACF field groups
        $groups = acf_get_field_groups();
        $row_number = 1;

        // Check if field groups exist
        if (!is_array($groups) || empty($groups)) {
            echo '<tr><td colspan="4">' . esc_html__('No ACF field groups found.', 'ultimate-extension-for-acf') . '</td></tr>';
            return;
        }

        $layouts_found = false;

        // Iterate through each field group
        foreach ($groups as $group) {
            $fields = acf_get_fields($group['ID']);

            // Skip if no fields in this group
            if (!is_array($fields)) {
                continue;
            }

            // Check each field for a flexible content type
            foreach ($fields as $field) {
                // Only process flexible content fields with layouts
                if ($field['type'] !== 'flexible_content' ||
                    !isset($field['layouts']) ||
                    !is_array($field['layouts'])) {
                    continue;
                }

                // Process each layout in the flexible content field
                foreach ($field['layouts'] as $layout) {
                    $layouts_found = true;

                    // Get preview image data
                    $preview_image_id = $this->get_preview_image_id($layout['name']);
                    $preview_image_url = '';

                    if ($preview_image_id) {
                        $preview_image_url = $this->get_multisite_image_url($preview_image_id, 'medium');
                    }

                    // Render table row
                    echo '<tr class="image-preview-row modal-preview-row">';

                    // Row number column
                    echo '<td class="number column-number">';
                    echo '<div class="number">' . esc_html($row_number) . '</div>';
                    echo '</td>';

                    // Component title column
                    echo '<td class="title column-title column-primary page-title" data-colname="Title">';
                    echo '<div class="flex-component-title"><strong>' . esc_html($layout['label']) . '</strong></div>';
                    echo '</td>';

                    // Preview image column
                    echo '<td class="image column-image" data-colname="Preview Image">';
                    echo '<div class="flex-component-image">';

                    if ($preview_image_url) {
                        // Show existing preview image
                        echo '<img src="' . esc_url($preview_image_url) . '" ' .
                             'class="image-preview-image modal-preview-image" ' .
                             'alt="' . esc_attr($layout['label']) . '">';
                    } else {
                        // Show placeholder when no image is set
                        echo '<span class="no-image">' .
                             esc_html__('No preview image set', 'ultimate-extension-for-acf') . '</span>';
                    }

                    echo '</div></td>';

                    // Update button column
                    echo '<td class="number column-update">';
                    echo '<div class="acf-icon-update">';
                    echo '<a class="acf-icon -image small acf-modal-upload" ' .
                         'data-layout="' . esc_attr($layout['name']) . '" ' .
                         'title="' . esc_attr__('Edit Preview Image', 'ultimate-extension-for-acf') . '"></a>';
                    echo '</div></td>';

                    echo '</tr>';

                    $row_number++;
                }
            }
        }

        // Show message if no flexible content layouts found
        if (!$layouts_found) {
            echo '<tr><td colspan="4">' .
                 esc_html__('No flexible content layouts found in your ACF field groups.', 'ultimate-extension-for-acf') .
                 '</td></tr>';
        }
    }

    /**
     * Get Preview Image ID for a Layout
     *
     * Retrieves the stored preview image attachment ID for a given layout name.
     * Uses the AJAX handler's caching mechanism for performance.
     *
     * @param string $layout_name The ACF layout name
     *
     * @return string|null The attachment ID if found, null otherwise
     * @since 1.0.0
     */
    private function get_preview_image_id( string $layout_name): ?string
    {
        // Input validation
        if (empty($layout_name)) {
            return null;
        }

        // Use the AJAX handler to get cached image data
        $ajax_handler = new UEFAX_Current_Ajax();
        return $ajax_handler::get_uefax_images($layout_name);
    }

    /**
     * Get Multisite-Compatible Image URL
     *
     * Retrieves the image URL for a given attachment ID, handling multisite scenarios
     * where the image might be from the main site but needs to be displayed on a subsite.
     *
     * @param int|string $attachment_id The WordPress attachment ID
     * @param string $size The image size to retrieve
     *
     * @return string The image URL, empty string if not found
     * @since 1.0.0
     */
    private function get_multisite_image_url( int|string $attachment_id, string $size = 'medium'): string
    {
        if (!$attachment_id) {
            return '';
        }

        // First, try to get the image URL in the current site context
        $image_url = wp_get_attachment_image_url($attachment_id, $size);

        if ($image_url) {
            return $image_url;
        }

        // If not found, and we're in multisite, the image might be from the main site
        if (is_multisite()) {
            $main_site_id = get_main_site_id();
            $current_site_id = get_current_blog_id();

            // Only check the main site if we're not already on the main site
            if ($main_site_id !== $current_site_id) {
                // Switch to the main site to get the image URL
                switch_to_blog($main_site_id);
                $image_url = wp_get_attachment_image_url($attachment_id, $size);
                restore_current_blog();
            }
        }

        return $image_url ?: '';
    }

    /**
     * Get Multisite-Compatible Image Path
     *
     * Retrieves the file system path for a given attachment ID, handling multisite scenarios
     * where the image might be from the main site, but we need the path on a subsite.
     *
     * @param int|string $attachment_id The WordPress attachment ID
     *
     * @return string The file system path, empty string if not found
     * @since 1.0.0
     */
    private function get_multisite_image_path( int|string $attachment_id): string
    {
        if (!$attachment_id) {
            return '';
        }

        // First try to get the image path in the current site context
        $image_path = wp_get_original_image_path($attachment_id);

        if ($image_path && file_exists($image_path)) {
            return $image_path;
        }

        // If not found, and we're in multisite, the image might be from the main site
        if (is_multisite()) {
            $main_site_id = get_main_site_id();
            $current_site_id = get_current_blog_id();

            // Only check the main site if we're not already on the main site
            if ($main_site_id !== $current_site_id) {
                // Switch to the main site to get the image path
                switch_to_blog($main_site_id);
                $image_path = wp_get_original_image_path($attachment_id);
                restore_current_blog();

                // Verify the path exists and is accessible
                if ($image_path && file_exists($image_path)) {
                    return $image_path;
                }
            }
        }

        return '';
    }

    /**
     * Add Image Preview to Flexible Content Layout List
     *
     * This method is hooked into ACF's layout title filter to add image previews
     * to flexible content layout handles. It modifies the layout title display
     * to include thumbnail images and generates CSS for hover popups.
     *
     * @param string $title The original layout title
     * @param array $field The ACF field data
     * @param array $layout The layout configuration
     * @param int $i The layout index
     * @return string Modified title HTML with image preview
     * @since 1.0.0
     */
    public function uefax_flex_preview($title, $field, $layout, $i): string
    {
        $img_name = $layout['name'];

        // Get preview image data using AJAX handler
        $get_image = new UEFAX_Current_Ajax();
        $preview_img_id = $get_image::get_uefax_images($layout['name']);
        $preview_img = $preview_img_path = '';

        // Get image URLs and paths if the preview image exists
        if ($preview_img_id) {
            $preview_img = $this->get_multisite_image_url($preview_img_id, 'full');
            $preview_img_path = $this->get_multisite_image_path($preview_img_id);
        }

        // Calculate image dimensions for proper popup sizing
        $img = $preview_img_path ? getimagesize($preview_img_path) : null;
        $original_width = $img ? $img[0] : null;
        $original_height = $img ? $img[1] : null;

        // Standard popup width and calculated proportional height
        $popup_width = 420;
        $popup_height = ($original_width && $original_height) ?
                       $original_height / ($original_width / $popup_width) : 0;

        $custom_title = '';

        // Generate custom title with image preview if image exists
        if ($preview_img) {
            // Create a thumbnail container
            $custom_title .= '<div class="thumbnail">';
            $custom_title .= '<img src="' . esc_url($preview_img) . '" ' .
                           'width="85" ' .
                           'style="width: 85px; max-width: 85px; margin: .625rem 0 .625rem 1.5rem; object-fit: cover;" ' .
                           'alt="' . esc_attr($layout['label']) . '" />';
            $custom_title .= '</div>';

            // Add layout title
            $custom_title .= '<h4 class="layout-title">' . esc_html($layout['label']) . '</h4>';

            // Generate CSS for popup hover effects
            $popup_style = '
                .acf-fc-popup [data-layout="' . esc_attr($img_name) . '"]:after {
                    width: ' . $popup_width . 'px;
                    height: ' . (int)$popup_height . 'px;
                    background-color: white;
                    background-image: url("' . esc_url($preview_img) . '");
                    background-repeat: no-repeat;
                    background-size: contain;
                    background-position: center;
                    content: " ";
                    top: -' . ((int)$popup_height - 15) / 2 . 'px;
                    position: absolute;
                    left: -' . ($popup_width + 5) . 'px;
                    border: 1px solid #666;
                    -webkit-box-shadow: 0px 0px 5px 0px rgb(0 0 0 / 30%);
                    box-shadow: 0px 0px 5px 0px rgb(0 0 0 / 30%);
                }
            ';

            // Collect the CSS for later output (prevents duplicate style blocks)
            if (!array_key_exists($img_name, self::$preview_styles)) {
                self::$preview_styles[$img_name] = $popup_style;
            }
        }

        // Return the appropriate title based on whether the image preview exists
        if (empty($custom_title)) {
            return '<h4 class="layout-title">' . esc_html($title) . '</h4>';
        }

        return $custom_title;
    }

    /**
     * Output All Collected Preview Styles
     *
     * Outputs all collected preview styles in a single style block
     * for better performance and organization.
     *
     * @since 1.0.0
     */
    public function output_preview_styles(): void
    {
        // Prevent duplicate output
        static $outputted = false;
        if ($outputted) {
            return;
        }
        $outputted = true;

        if (empty(self::$preview_styles)) {
            return;
        }

        // Enqueue a separate dynamic stylesheet
        wp_enqueue_style(
            self::$id . '-dynamic',
            UEFAX_PLUGIN_URL . 'assets/current/dynamic-styles.css',
            array('acf-pro-input'),
            UEFAX_VERSION
        );

        // Combine all styles into one block
        $combined_styles = implode("\n", array_values(self::$preview_styles));

        // Add the base fade effect CSS to ensure it's not overridden
        $base_css = '
        .acf-fc-popup a[data-layout]:after {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.95);
            transition: opacity 0.2s ease-out, visibility 0.2s ease-out, transform 0.2s ease-out;
        }
        .acf-fc-popup a[data-layout]:hover:after {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }';

        // Combine base CSS with dynamic styles
        $combined_styles = "/* Dynamic ACF Preview Styles */\n" . $base_css . "\n" . $combined_styles;

        // Add inline styles to the dynamic stylesheet
        wp_add_inline_style(self::$id . '-dynamic', $combined_styles);
    }
}
