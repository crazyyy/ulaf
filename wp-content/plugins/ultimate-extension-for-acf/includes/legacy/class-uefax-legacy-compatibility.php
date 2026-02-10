<?php

/**
 * Legacy ACF Compatibility Class for Ultimate Extension for ACF
 * Handles ACF versions 5.6 to 6.4.x
 *
 * @package Ultimate_Extension_for_ACF
 * @subpackage Legacy
 * @since 1.0.0
 */

// File Security Check
defined('ABSPATH') or die("No script kiddies please!");

class UEFAX_Legacy_Compatibility
{
    /**
     * Unique identifier for the module
     */
    private static string $id = 'ultimate-extension-for-acf';

    /**
     * Path for assets
     */
    private static string $path = '';

    /**
     * Preview image styles
     */
    public static array $previewStyle = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize legacy compatibility
     */
    private function init()
    {
        global $acf;

        // Set path based on ACF version - matches original logic exactly
        if ($acf && version_compare($acf->version, '5.7.0', '<')) {
            self::$path = '/acf/5.6';
        }

        // Load AJAX handler
        require_once UEFAX_PLUGIN_DIR . 'includes/legacy/class-uefax-legacy-ajax.php';
        $initAjax = new UEFAX_Legacy_Ajax();

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks()
    {
        // Hooks
        add_action('init', array($this, 'theme_css'));
        add_action('init', array($this, 'theme_script'));
        add_action('admin_enqueue_scripts', array($this, 'admin_css'));
        add_action('admin_enqueue_scripts', array($this, 'admin_script'));
        add_action('admin_menu', array($this, 'register_submenu_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_media_files'));

        // Ajax requests
        if (is_admin()) {
            add_action('wp_ajax_uefax_getModalImage', array('UEFAX_Legacy_Ajax', 'get_uefax_images'));
            add_action('wp_ajax_nopriv_uefax_getModalImage', array('UEFAX_Legacy_Ajax', 'get_uefax_images'));
            add_action('wp_ajax_uefax_setModalImage', array('UEFAX_Legacy_Ajax', 'set_uefax_image'));
            add_action('wp_ajax_nopriv_uefax_setModalImage', array('UEFAX_Legacy_Ajax', 'set_uefax_image'));
        }

        // Add preview image filter
        add_filter('acf/fields/flexible_content/layout_title', array($this, 'uefax_flex_preview'), 10, 4);

        // Add action to output all collected preview styles once
        add_action('acf/input/admin_footer', array($this, 'output_preview_styles'));
        add_action('admin_footer', array($this, 'output_preview_styles'));
    }

    /**
     * Load media files
     */
    public function load_media_files()
    {
        wp_enqueue_media();
    }

    /**
     * Register modal submenu
     */
    public function register_submenu_page()
    {
        // Add an admin menu for modal preview images
        add_submenu_page(
            'edit.php?post_type=acf-field-group',
            __('Modal Settings', 'ultimate-extension-for-acf'),
            __('Modal Settings', 'ultimate-extension-for-acf'),
            'manage_options',
            'acf-modal-settings',
            array($this, 'modal_settings_callback')
        );
    }

    /**
     * Modal settings page callback
     */
    public function modal_settings_callback()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap modal-settings-wrap">
            <h1 class="modal-settings-title"><?php esc_html_e('Modal Settings', 'ultimate-extension-for-acf'); ?></h1>

            <!-- Table -->
            <table class="wp-list-table widefat fixed striped table-view-list modal-settings">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-number"></th>
                        <th scope="col" class="manage-column column-modal-title">Component Title</th>
                        <th scope="col" class="manage-column column-modal-image">Preview Image <small><i>( Optimal image width 420px )</i></small></th>
                        <th scope="col" class="manage-column column-update">Update</th>
                    </tr>
                </thead>

                <tbody id="the-list">
                    <?php
                    $groups = acf_get_field_groups();

                    if (is_array($groups)) {
                        foreach ($groups as $group) {
                            $fields = acf_get_fields($group['ID']);

                            foreach ($fields as $field) {
                                if ($field['type'] === 'flexible_content' && isset($field['layouts']) && is_array($field['layouts'])) {
                                    $i = 1;
                                    foreach ($field['layouts'] as $layout) {
                                        $preview = new UEFAX_Legacy_Ajax();
                                        $preview_image_id = $preview::get_uefax_images($layout['name']);
                                        $preview_image = '';
                                        if ($preview_image_id !== null) {
                                            $preview_image = wp_get_attachment_image_src($preview_image_id, 'full')[0] ?? '';
                                        }
                                        ?>
                                        <tr class="type-acf-field-group modal-preview-row">
                                            <td class="number column-number">
                                                <div class="number"><?php echo esc_html($i); ?></div>
                                            </td>
                                            <td class="title column-title column-primary page-title" data-colname="Title">
                                                <div class="flex-component-title"><strong><?php echo esc_html($layout['label'] ?? ''); ?></strong></div>
                                            </td>
                                            <td class="image column-image" data-colname="Preview Image">
                                                <div class="flex-component-image">
                                                    <img src="<?php echo esc_url($preview_image); ?>" class="modal-preview-image" alt="<?php echo esc_attr($preview_image ? $layout['label'] : ''); ?>">
                                                </div>
                                            </td>
                                            <td class="number column-update">
                                                <div class="acf-icon-update">
                                                    <a class="acf-icon -image small acf-modal-upload" data-layout="<?php echo esc_attr($layout['name'] ?? ''); ?>" title="<?php esc_attr_e('Edit Preview Image', 'ultimate-extension-for-acf'); ?>"></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $i++;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Register admin stylesheets
     * Only loads on ACF-related pages and the plugin's settings page.
     */
    public function admin_css()
    {
        // Only load on ACF-related pages and our settings page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Load on ACF pages, our settings page, and post/page edit screens
        $acf_pages = array(
            'acf-field-group',
            'acf-field-group_page_acf-modal-settings',
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
            self::$id,
            UEFAX_PLUGIN_URL . 'assets/legacy/style.css',
            array('acf-pro-input'),
            UEFAX_VERSION
        );
        wp_enqueue_style(self::$id);
    }

    /**
     * Register admin scripts
     * Only loads on ACF-related pages and the plugin's settings page.
     */
    public function admin_script()
    {
        // Only load on ACF-related pages and our settings page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Load on ACF pages, our settings page, and post/page edit screens
        $acf_pages = array(
            'acf-field-group',
            'acf-field-group_page_acf-modal-settings',
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
        // Register and enqueue main script
        wp_register_script(
            self::$id,
            UEFAX_PLUGIN_URL . 'assets/legacy/script.js',
            array('acf-pro-input'),
            UEFAX_VERSION,
            false
        );
        wp_enqueue_script(self::$id);

        // Register and enqueue upload script
        wp_register_script(
            self::$id . '-upload',
            UEFAX_PLUGIN_URL . 'assets/legacy/upload.js',
            array('acf-pro-input'),
            UEFAX_VERSION,
            true
        );
        wp_enqueue_script(self::$id . '-upload');

        // Pass ajax_url to script.js
        wp_localize_script(self::$id . '-upload', 'uefax_ajax',
            array(
                'nonce' => wp_create_nonce('uefax-ajax-nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
    }

    /**
     * Register theme stylesheets
     */
    public function theme_css()
    {
        // Legacy theme styles if needed
    }

    /**
     * Register theme scripts
     */
    public function theme_script()
    {
        // Legacy theme scripts if needed
    }

    /**
     * Add image preview to flexible content layout list
     */
    public function uefax_flex_preview($title, $field, $layout, $i)
    {
        $img_name = $layout['name'];
        $get_image = new UEFAX_Legacy_Ajax();
        $preview_img_id = $get_image::get_uefax_images($layout['name']);
        $preview_img = $preview_img_path = '';

        if ($preview_img_id !== null) {
            $preview_img = wp_get_attachment_image_src($preview_img_id, 'full')[0];
            $preview_img_path = wp_get_original_image_path($preview_img_id);
        }

        $img = $preview_img_path ? getimagesize($preview_img_path) : null;
        $x = $img ? $img[0] : null;
        $y = $img ? $img[1] : null;
        $width = 420;
        $height = ($x && $y) ? $y / ($x / $width) : 0;
        $customTitle = '';

        // Load subfield image
        if ($preview_img !== '') {
            $customTitle .= '<div class="thumbnail">';
            $customTitle .= '<img src="' . esc_url($preview_img) . '" width="100px" align="left" style="width: 85px; max-width: 85px; margin: .625rem 0 .625rem 1.5rem" />';
            $customTitle .= '</div>';
            $customTitle .= '<h4 class="layout-title">' . esc_html($layout['label']) . '</h4>';
            $style = '';

            if (!array_key_exists($img_name, self::$previewStyle)) {
                self::$previewStyle[$img_name] = '
                    .acf-fc-popup [data-layout="' . esc_attr($img_name) . '"]:after {
                        width: ' . esc_attr($width) . 'px;
                        height: ' . esc_attr($height) . 'px;
                        background-color: white;
                        background-image: url("' . esc_url($preview_img) . '");
                        background-repeat: no-repeat;
                        background-size: contain;
                        background-position: center;
                        content: " ";
                        top: -' . esc_attr(( ((int)$height - 15) / 2 )) . 'px;
                        position: absolute;
                        left: -' . esc_attr($width) . 'px;
                        border: 1px solid #666;
                        -webkit-box-shadow: 0px 0px 5px 0px rgb(0 0 0 / 30%);
                        box-shadow: 0px 0px 5px 0px rgb(0 0 0 / 30%);
                    }
                ';
            }
        }

        // Return
        if ($customTitle === '') {
            return '<h4 class="layout-title">' . esc_html($title) . '</h4>';
        }
        return $customTitle;
    }

    /**
     * Output All Collected Preview Styles
     *
     * Outputs all collected preview styles in a single style block
     * for better performance and organization.
     */
    public function output_preview_styles()
    {
        // Prevent duplicate output
        static $outputted = false;
        if ($outputted) {
            return;
        }
        $outputted = true;

        if (empty(self::$previewStyle)) {
            return;
        }

        // Enqueue a separate dynamic stylesheet
        wp_enqueue_style(
            self::$id . '-dynamic',
            UEFAX_PLUGIN_URL . 'assets/legacy/dynamic-styles.css',
            array('acf-pro-input'),
            UEFAX_VERSION
        );

        // Combine all styles into one block
        $combined_styles = implode("\n", self::$previewStyle);

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
