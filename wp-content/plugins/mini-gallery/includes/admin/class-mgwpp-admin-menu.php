<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin_Menu
{
    private $view_classes = [];
    private $galleries_view;
    private $albums_view;
    private $testimonials_view;

    public function __construct($module_loader)
    {
        // Fetch galleries directly instead of using list table
        $gallery_items = $this->get_galleries_data();

        // Initialize view with items
        $this->galleries_view = new MGWPP_Galleries_View($gallery_items);

        // Initialize other views
        $this->albums_view = new MGWPP_Albums_View();

        // Only initialize testimonials view if module is enabled
        if (function_exists('mgwpp_is_module_enabled') && mgwpp_is_module_enabled('testimonials') && class_exists('MGWPP_Testimonials_View')) {
            $this->testimonials_view = new MGWPP_Testimonials_View();
        }
    }

    /**
     * Get galleries data formatted for the view
     */
    private function get_galleries_data()
    {
        $galleries = get_posts([
            'post_type'      => 'mgwpp_soora',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);

        $items = [];
        foreach ($galleries as $gallery) {
            $type = get_post_meta($gallery->ID, 'gallery_type', true);
            $type_label = ucfirst(str_replace('_', ' ', $type));

            $items[] = [
                'ID'        => $gallery->ID,
                'title'     => $gallery->post_title,
                'type'      => $type_label,
                'shortcode' => '[mgwpp_gallery id="' . $gallery->ID . '"]',
                'date'      => get_the_date('', $gallery),
                'actions'   => $this->get_gallery_actions($gallery->ID)
            ];
        }

        return $items;
    }

    /**
     * Generate action links for a gallery
     */
    private function get_gallery_actions($gallery_id)
    {
        $edit_url = add_query_arg([
            'page'       => 'mgwpp-edit-gallery',
            'gallery_id' => $gallery_id,
            '_wpnonce'   => wp_create_nonce('mgwpp_edit_gallery') //  nonce here
        ], admin_url('admin.php'));

        $delete_url = wp_nonce_url(
            admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $gallery_id),
            'mgwpp_delete_gallery'
        );

        return sprintf(
            '<a href="%s" class="mgwpp-edit-gallery" title="%s"><span class="dashicons dashicons-edit"></span></a> ' .
                '<a href="#" class="mgwpp-preview-gallery" data-id="%d" title="%s"><span class="dashicons dashicons-visibility"></span></a> ' .
                '<a href="%s" class="submitdelete" onclick="return confirm(\'%s\')" title="%s"><span class="dashicons dashicons-trash"></span></a>',
            esc_url($edit_url),
            esc_html__('Edit Gallery', 'mini-gallery'),
            esc_attr($gallery_id),
            esc_html__('Quick Preview', 'mini-gallery'),
            esc_url($delete_url),
            esc_js(__('Are you sure you want to delete this gallery?', 'mini-gallery')),
            esc_html__('Delete Gallery', 'mini-gallery')
        );
    }
    public function register_menus()
    {
        $this->setup_menu_structure();

        // Main menu
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [$this, 'render_dashboard'],
            MG_PLUGIN_URL . '/includes/admin/images/logo/mgwpp-logo-panel.png',
            20
        );

        // Submenus
        add_submenu_page(
            'mgwpp_dashboard',
            __('Dashboard', 'mini-gallery'),
            __('Dashboard', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [$this, 'render_dashboard']
        );

        $this->register_remaining_submenus();
    }

    private function setup_menu_structure()
    {
        $this->view_classes = [

            'galleries' => [
                'page_title' => __('Galleries', 'mini-gallery'),
                'callback' => [$this->galleries_view, 'render'],
                'capability' => 'edit_posts'
            ],
            'albums' => [
                'page_title' => __('Albums', 'mini-gallery'),
                'callback' => [$this->albums_view, 'render'],
                'capability' => 'edit_posts'
            ],
            'analytics' => [
                'page_title' => __('Analytics', 'mini-gallery'),
                'callback' => ['MGWPP_Analytics_View', 'render'],
                'capability' => 'manage_options'
            ],
            'settings' => [
                'page_title' => __('Settings', 'mini-gallery'),
                'callback' => ['MGWPP_Settings_View', 'render'],
                'capability' => 'manage_options'
            ],
        ];

        // Conditionally add Testimonials if module is enabled
        if (function_exists('mgwpp_is_module_enabled') && mgwpp_is_module_enabled('testimonials') && $this->testimonials_view !== null) {
            // Insert testimonials after albums
            $this->view_classes = array_slice($this->view_classes, 0, 2, true) +
                ['testimonials' => [
                    'page_title' => __('Testimonials', 'mini-gallery'),
                    'callback' => [$this->testimonials_view, 'render'],
                    'capability' => 'manage_options'
                ]] +
                array_slice($this->view_classes, 2, null, true);
        }
    }

    private function register_remaining_submenus()
    {
        foreach ($this->view_classes as $slug => $menu_item) {
            add_submenu_page(
                'mgwpp_dashboard',
                $menu_item['page_title'],
                $menu_item['page_title'],
                $menu_item['capability'],
                'mgwpp_' . $slug,
                $menu_item['callback']
            );
        }
    }

    public function render_dashboard()
    {
        MGWPP_Dashboard_View::render_dashboard();
    }
}
