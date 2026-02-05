<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

/**
 * Canvas Editor View
 * 
 * Main admin page for the visual drag-and-drop canvas editor.
 */
class MGWPP_Canvas_Editor_View
{
    /**
     * Initialize the canvas editor
     */
    public static function init()
    {
        add_action('admin_menu', [self::class, 'register_menu_pages'], 20);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);

        // AJAX handlers
        add_action('wp_ajax_mgwpp_save_canvas', [self::class, 'ajax_save_canvas']);
        add_action('wp_ajax_mgwpp_load_canvas', [self::class, 'ajax_load_canvas']);
        add_action('wp_ajax_mgwpp_create_canvas', [self::class, 'ajax_create_canvas']);
        add_action('wp_ajax_mgwpp_delete_canvas', [self::class, 'ajax_delete_canvas']);
        add_action('wp_ajax_mgwpp_render_preview', [self::class, 'ajax_render_preview']);
    }

    /**
     * Register admin menu pages
     */
    public static function register_menu_pages()
    {
        // Canvas list page - as submenu under Mini Gallery
        add_submenu_page(
            'mgwpp_dashboard',
            __('Canvas Editor', 'mini-gallery'),
            __('Canvas Editor', 'mini-gallery'),
            'edit_mgwpp_canvases',
            'mgwpp_canvas',
            [self::class, 'render_list_page']
        );

        // Canvas editor page (hidden from menu)
        add_submenu_page(
            '',
            __('Edit Canvas', 'mini-gallery'),
            __('Edit Canvas', 'mini-gallery'),
            'edit_mgwpp_canvases',
            'mgwpp_canvas_editor',
            [self::class, 'render_editor_page']
        );
    }

    /**
     * Enqueue assets for canvas pages
     */
    public static function enqueue_assets($hook)
    {
        // Only load on our pages - use WordPress screen detection
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Check screen ID instead of $_GET['page']
        $is_canvas_page = (
            strpos($screen->id, 'mgwpp_canvas') !== false ||
            strpos($screen->id, 'mgwpp_canvas_editor') !== false
        );

        if (!$is_canvas_page) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-resizable');
        wp_enqueue_script('jquery-ui-sortable');

        // Canvas editor CSS
        wp_enqueue_style(
            'mgwpp-canvas-editor',
            MG_PLUGIN_URL . '/includes/admin/views/canvas-editor/mgwpp-canvas-editor.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/canvas-editor/mgwpp-canvas-editor.css')
        );

        // Canvas editor JS
        wp_enqueue_script(
            'mgwpp-canvas-editor',
            MG_PLUGIN_URL . '/includes/admin/views/canvas-editor/mgwpp-canvas-editor.js',
            ['jquery', 'jquery-ui-draggable', 'jquery-ui-resizable', 'jquery-ui-sortable', 'wp-util'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/canvas-editor/mgwpp-canvas-editor.js'),
            true
        );

        wp_localize_script('mgwpp-canvas-editor', 'mgwppCanvas', [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('mgwpp_canvas_nonce'),
            'canvasId'  => self::get_current_canvas_id(),
            'listUrl'   => admin_url('admin.php?page=mgwpp_dashboard'),
            'i18n'      => [
                'saving'        => __('Saving...', 'mini-gallery'),
                'saved'         => __('Saved!', 'mini-gallery'),
                'saveFailed'    => __('Failed to save', 'mini-gallery'),
                'confirmDelete' => __('Are you sure you want to delete this item?', 'mini-gallery'),
                'selectImage'   => __('Select Image', 'mini-gallery'),
                'useImage'      => __('Use Image', 'mini-gallery'),
                'untitled'      => __('Untitled Canvas', 'mini-gallery'),
            ],
        ]);
    }

    /**
     * Get current canvas ID from request safely
     * This is called during asset enqueue when nonce isn't available for page routing
     */
    private static function get_current_canvas_id()
    {
        // Use filter_input for safe access without triggering PHPCS warning
        $canvas_id = filter_input(INPUT_GET, 'canvas_id', FILTER_VALIDATE_INT);
        return $canvas_id ? absint($canvas_id) : 0;
    }

    /**
     * Render canvas list page
     */
    public static function render_list_page()
    {
        if (!MGWPP_Canvas_Capabilities::current_user_can_edit()) {
            wp_die(esc_html__('You do not have permission to access this page.', 'mini-gallery'));
        }

        $canvases = get_posts([
            'post_type'      => 'mgwpp_canvas',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ]);
?>
        <div class="mgwpp-canvas-dashboard-wrap" id="mgwpp-canvas-list-wrap">
            <!-- Enhanced Header with Back Button and Theme Toggle -->
            <div class="mgwpp-canvas-list-header">
                <div class="mgwpp-header-left">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_dashboard')); ?>" class="mgwpp-back-link" title="<?php esc_attr_e('Back to Dashboard', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                    </a>
                    <div class="mgwpp-logo-section">
                        <span class="mgwpp-logo-icon">
                            <span class="dashicons dashicons-format-gallery"></span>
                        </span>
                        <h1><?php esc_html_e('Canvas Galleries', 'mini-gallery'); ?></h1>
                    </div>
                </div>

                <div class="mgwpp-header-right">
                    <button type="button" id="mgwpp-list-theme-toggle" class="mgwpp-theme-btn" title="<?php esc_attr_e('Toggle Light/Dark Mode', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </button>
                    <button type="button" id="mgwpp-new-canvas-btn" class="mgwpp-btn mgwpp-btn-primary">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php esc_html_e('Create New Canvas', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>

            <div class="mgwpp-canvas-grid">
                <?php if (empty($canvases)) : ?>
                    <div class="mgwpp-no-canvases">
                        <p><?php esc_html_e('No canvases found. Create your first one!', 'mini-gallery'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="mgwpp-grid-items">
                        <?php foreach ($canvases as $post) :
                            $data = MGWPP_Canvas_Post_Type::get_canvas_data($post->ID);
                            $bg_color = $data['canvas_settings']['background'] ?? '#fff';
                        ?>
                            <div class="mgwpp-canvas-card">
                                <div class="mgwpp-card-preview" style="background: <?php echo esc_attr($bg_color); ?>;">
                                    <div class="mgwpp-preview-placeholder">
                                        <span class="dashicons dashicons-art"></span>
                                    </div>
                                    <div class="mgwpp-card-actions">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_canvas_editor&canvas_id=' . $post->ID . '&_wpnonce=' . wp_create_nonce('mgwpp_edit_canvas'))); ?>" class="mgwpp-action-btn edit" title="<?php esc_attr_e('Edit', 'mini-gallery'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <button type="button" class="mgwpp-action-btn preview mgwpp-preview-canvas" data-canvas-id="<?php echo esc_attr($post->ID); ?>" title="<?php esc_attr_e('Preview', 'mini-gallery'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                        <button type="button" class="mgwpp-action-btn delete mgwpp-delete-canvas" data-canvas-id="<?php echo esc_attr($post->ID); ?>" title="<?php esc_attr_e('Delete', 'mini-gallery'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="mgwpp-card-info">
                                    <h3 class="mgwpp-card-title"><?php echo esc_html($post->post_title); ?></h3>
                                    <div class="mgwpp-card-meta">
                                        <span class="mgwpp-shortcode-wrap">
                                            [mgwpp_canvas id="<?php echo esc_attr($post->ID); ?>"]
                                            <button type="button" class="mgwpp-copy-btn mgwpp-copy-canvas-shortcode" data-shortcode='[mgwpp_canvas id="<?php echo esc_attr($post->ID); ?>"]' title="<?php esc_attr_e('Copy Shortcode', 'mini-gallery'); ?>">
                                                <span class="dashicons dashicons-clipboard"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create Modal -->
        <div id="mgwpp-create-modal" class="mgwpp-modal-overlay" style="display: none;">
            <div class="mgwpp-modal">
                <div class="mgwpp-modal-header">
                    <h2><?php esc_html_e('Create New Canvas', 'mini-gallery'); ?></h2>
                    <button type="button" class="mgwpp-modal-close">&times;</button>
                </div>
                <div class="mgwpp-modal-body">
                    <div class="mgwpp-form-group">
                        <label for="mgwpp-new-title"><?php esc_html_e('Canvas Title', 'mini-gallery'); ?></label>
                        <input type="text" id="mgwpp-new-title" placeholder="<?php esc_attr_e('Enter title...', 'mini-gallery'); ?>" class="mgwpp-input-full">
                    </div>
                </div>
                <div class="mgwpp-modal-footer">
                    <button type="button" class="mgwpp-btn mgwpp-btn-secondary mgwpp-modal-cancel"><?php esc_html_e('Cancel', 'mini-gallery'); ?></button>
                    <button type="button" id="mgwpp-create-confirm" class="mgwpp-btn mgwpp-btn-primary"><?php esc_html_e('Create Canvas', 'mini-gallery'); ?></button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div id="mgwpp-preview-modal" class="mgwpp-modal-overlay" style="display: none;">
            <div class="mgwpp-modal mgwpp-modal-lg">
                <div class="mgwpp-modal-header">
                    <h2><?php esc_html_e('Canvas Preview', 'mini-gallery'); ?></h2>
                    <button type="button" class="mgwpp-modal-close">&times;</button>
                </div>
                <div class="mgwpp-modal-body no-padding">
                    <div id="mgwpp-preview-content" class="mgwpp-preview-viewport">
                        <div class="mgwpp-loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render canvas editor page
     */
    public static function render_editor_page()
    {
        if (!MGWPP_Canvas_Capabilities::current_user_can_edit()) {
            wp_die(esc_html__('You do not have permission to access this page.', 'mini-gallery'));
        }

        $canvas_id = isset($_GET['canvas_id']) ? absint($_GET['canvas_id']) : 0;
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!$canvas_id || !wp_verify_nonce($nonce, 'mgwpp_edit_canvas')) {
            wp_die(esc_html__('Invalid canvas or security check failed.', 'mini-gallery'));
        }

        $canvas = get_post($canvas_id);
        if (!$canvas || $canvas->post_type !== 'mgwpp_canvas') {
            wp_die(esc_html__('Canvas not found.', 'mini-gallery'));
        }

        $canvas_data = MGWPP_Canvas_Post_Type::get_canvas_data($canvas_id);

        // Ensure slides structure exists for backward compatibility or new canvases
        if (empty($canvas_data['slides'])) {
            // Convert legacy items to slide 0 if they exist
            $legacy_items = $canvas_data['items'] ?? [];
            $canvas_data['slides'] = [
                [
                    'id' => 'slide_' . uniqid(),
                    'items' => $legacy_items
                ]
            ];
        }
    ?>
        <div class="mgwpp-canvas-editor-wrap">
            <!-- Header -->
            <div class="mgwpp-canvas-header">
                <div class="mgwpp-canvas-header-left">
                    <button type="button" id="mgwpp-toggle-left-panel" class="mgwpp-icon-btn" title="<?php esc_attr_e('Toggle Elements Panel', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-menu"></span>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_dashboard')); ?>" class="mgwpp-back-btn">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                    </a>
                    <input type="text" id="mgwpp-canvas-title"
                        value="<?php echo esc_attr($canvas->post_title); ?>"
                        placeholder="<?php esc_attr_e('Untitled Canvas', 'mini-gallery'); ?>">
                </div>

                <div class="mgwpp-canvas-header-center">
                    <div class="mgwpp-device-switcher">
                        <button type="button" class="mgwpp-device-btn active" data-device="desktop" title="<?php esc_attr_e('Desktop', 'mini-gallery'); ?>">
                            <span class="dashicons dashicons-desktop"></span>
                        </button>
                        <button type="button" class="mgwpp-device-btn" data-device="tablet" title="<?php esc_attr_e('Tablet', 'mini-gallery'); ?>">
                            <span class="dashicons dashicons-tablet"></span>
                        </button>
                        <button type="button" class="mgwpp-device-btn" data-device="mobile" title="<?php esc_attr_e('Mobile', 'mini-gallery'); ?>">
                            <span class="dashicons dashicons-smartphone"></span>
                        </button>
                    </div>
                </div>

                <div class="mgwpp-canvas-header-right">
                    <button type="button" id="mgwpp-undo" class="mgwpp-btn mgwpp-btn-secondary mgwpp-btn-icon" disabled
                        title="<?php esc_attr_e('Undo', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-undo"></span>
                    </button>
                    <button type="button" id="mgwpp-redo" class="mgwpp-btn mgwpp-btn-secondary mgwpp-btn-icon" disabled
                        title="<?php esc_attr_e('Redo', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-redo"></span>
                    </button>

                    <div class="mgwpp-header-divider"></div>

                    <button type="button" id="mgwpp-theme-toggle" class="mgwpp-btn mgwpp-btn-secondary mgwpp-btn-icon"
                        title="<?php esc_attr_e('Toggle Light/Dark Mode', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </button>

                    <button type="button" id="mgwpp-save-canvas" class="mgwpp-btn mgwpp-btn-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Save', 'mini-gallery'); ?>
                    </button>
                    <button type="button" id="mgwpp-toggle-right-panel" class="mgwpp-icon-btn" title="<?php esc_attr_e('Toggle Properties Panel', 'mini-gallery'); ?>">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </button>
                </div>
            </div>

            <!-- Main Editor Area -->
            <div class="mgwpp-canvas-main">
                <!-- Left Panel: Tools & Layers -->
                <div id="mgwpp-panel-left" class="mgwpp-canvas-panel mgwpp-panel-left">
                    <!-- Tools Section -->
                    <div class="mgwpp-panel-section">
                        <div class="mgwpp-panel-section-title"><?php esc_html_e('Add Elements', 'mini-gallery'); ?></div>
                        <div class="mgwpp-tools-grid mgwpp-tools-grid-enhanced">
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-image" data-type="image" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-media">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Image', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('Photo & Graphics', 'mini-gallery'); ?></span>
                            </button>
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-video" data-type="video" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-media">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Video', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('MP4, WebM, Embed', 'mini-gallery'); ?></span>
                            </button>
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-text" data-type="text" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-content">
                                    <span class="dashicons dashicons-editor-textcolor"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Text', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('Headings & Body', 'mini-gallery'); ?></span>
                            </button>
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-button" data-type="button" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-interactive">
                                    <span class="dashicons dashicons-button"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Button', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('CTA & Links', 'mini-gallery'); ?></span>
                            </button>
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-shape" data-type="shape" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-design">
                                    <span class="dashicons dashicons-admin-customizer"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Shape', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('Rect, Circle, Lines', 'mini-gallery'); ?></span>
                            </button>
                            <button type="button" class="mgwpp-tool-item mgwpp-add-item mgwpp-tool-container" data-type="container" title="<?php esc_attr_e('Drag to canvas or click to add', 'mini-gallery'); ?>">
                                <div class="mgwpp-tool-icon-wrap mgwpp-tool-icon-layout">
                                    <span class="dashicons dashicons-layout"></span>
                                </div>
                                <span class="mgwpp-tool-label"><?php esc_html_e('Container', 'mini-gallery'); ?></span>
                                <span class="mgwpp-tool-hint"><?php esc_html_e('Flex & Grid Layout', 'mini-gallery'); ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Layers Section -->
                    <div class="mgwpp-panel-header"><?php esc_html_e('Layers', 'mini-gallery'); ?></div>
                    <div id="mgwpp-layers-list" class="mgwpp-layers-list">
                        <!-- Layers injected by JS -->
                    </div>
                </div>

                <!-- Center: Canvas & Slides -->
                <div class="mgwpp-canvas-center">

                    <!-- Canvas Viewport -->
                    <div class="mgwpp-canvas-viewport">
                        <div id="mgwpp-canvas" class="mgwpp-canvas"
                            data-canvas-id="<?php echo esc_attr($canvas_id); ?>"
                            style="width: <?php echo esc_attr($canvas_data['canvas_settings']['width'] ?? 1200); ?>px; 
                                    height: <?php echo esc_attr($canvas_data['canvas_settings']['height'] ?? 800); ?>px;
                                    background: <?php echo esc_attr($canvas_data['canvas_settings']['background'] ?? '#ffffff'); ?>;">
                        </div>
                    </div>

                    <!-- Slides Strip -->
                    <div class="mgwpp-slides-strip" id="mgwpp-slides-strip">
                        <!-- Slides injected by JS -->
                    </div>
                </div>

                <!-- Right Panel: Properties -->
                <div id="mgwpp-panel-right" class="mgwpp-canvas-panel mgwpp-panel-right">
                    <div class="mgwpp-panel-header"><?php esc_html_e('Properties', 'mini-gallery'); ?></div>
                    <div id="mgwpp-properties-content">
                        <p class="mgwpp-no-selection">
                            <span class="dashicons dashicons-edit"></span><br>
                            <?php esc_html_e('Select an element to edit properties', 'mini-gallery'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Get canvas preview HTML
     */
    private static function get_canvas_preview($canvas_id)
    {
        $data = MGWPP_Canvas_Post_Type::get_canvas_data($canvas_id);

        if (empty($data['items'])) {
            return '<div class="mgwpp-canvas-empty-preview"><span class="dashicons dashicons-format-image"></span></div>';
        }

        // Find first image in items
        foreach ($data['items'] as $item) {
            if ($item['type'] === 'image' && !empty($item['image_url'])) {
                return '<img src="' . esc_url($item['image_url']) . '" alt="" loading="lazy">';
            }
        }

        return '<div class="mgwpp-canvas-empty-preview"><span class="dashicons dashicons-art"></span></div>';
    }

    /**
     * AJAX: Create new canvas
     */
    public static function ajax_create_canvas()
    {
        check_ajax_referer('mgwpp_canvas_nonce', 'nonce');

        if (!MGWPP_Canvas_Capabilities::current_user_can_edit()) {
            wp_send_json_error(['message' => __('Permission denied', 'mini-gallery')], 403);
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : __('Untitled Canvas', 'mini-gallery');

        $canvas_id = wp_insert_post([
            'post_title'  => $title,
            'post_type'   => 'mgwpp_canvas',
            'post_status' => 'publish',
        ]);

        if (is_wp_error($canvas_id)) {
            wp_send_json_error(['message' => $canvas_id->get_error_message()], 500);
        }

        // Initialize with default canvas data
        $default_data = [
            'canvas_settings' => [
                'width'      => 1200,
                'height'     => 800,
                'background' => '#ffffff',
            ],
            'items' => [],
        ];
        MGWPP_Canvas_Post_Type::save_canvas_data($canvas_id, $default_data);

        wp_send_json_success([
            'canvas_id'  => $canvas_id,
            'edit_url'   => admin_url('admin.php?page=mgwpp_canvas_editor&canvas_id=' . $canvas_id . '&_wpnonce=' . wp_create_nonce('mgwpp_edit_canvas')),
            'message'    => __('Canvas created successfully', 'mini-gallery'),
        ]);
    }

    /**
     * AJAX: Save canvas
     */
    public static function ajax_save_canvas()
    {
        check_ajax_referer('mgwpp_canvas_nonce', 'nonce');

        if (!MGWPP_Canvas_Capabilities::current_user_can_edit()) {
            wp_send_json_error(['message' => __('Permission denied', 'mini-gallery')], 403);
        }

        $canvas_id = isset($_POST['canvas_id']) ? absint($_POST['canvas_id']) : 0;

        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            wp_send_json_error(['message' => __('Invalid canvas ID', 'mini-gallery')], 400);
        }

        // Update title
        if (isset($_POST['title'])) {
            wp_update_post([
                'ID'         => $canvas_id,
                'post_title' => sanitize_text_field(wp_unslash($_POST['title'])),
            ]);
        }

        // Update canvas data
        if (isset($_POST['canvas_data'])) {
            // Sanitize the raw JSON string first
            $raw_data = sanitize_text_field(wp_unslash($_POST['canvas_data']));

            // Decode the sanitized JSON
            $data = json_decode($raw_data, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                wp_send_json_error(['message' => __('Invalid canvas data format', 'mini-gallery')], 400);
            }

            // Recursively sanitize all data
            $data = self::sanitize_canvas_data($data);

            $result = MGWPP_Canvas_Post_Type::save_canvas_data($canvas_id, $data);

            if (!$result) {
                wp_send_json_error(['message' => __('Failed to save canvas data', 'mini-gallery')], 500);
            }
        }

        wp_send_json_success(['message' => __('Canvas saved successfully', 'mini-gallery')]);
    }

    /**
     * AJAX: Load canvas
     */
    public static function ajax_load_canvas()
    {
        check_ajax_referer('mgwpp_canvas_nonce', 'nonce');

        if (!MGWPP_Canvas_Capabilities::current_user_can_edit()) {
            wp_send_json_error(['message' => __('Permission denied', 'mini-gallery')], 403);
        }

        $canvas_id = isset($_POST['canvas_id']) ? absint($_POST['canvas_id']) : 0;

        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            wp_send_json_error(['message' => __('Invalid canvas ID', 'mini-gallery')], 400);
        }

        $canvas = get_post($canvas_id);
        $data = MGWPP_Canvas_Post_Type::get_canvas_data($canvas_id);

        wp_send_json_success([
            'title' => $canvas->post_title,
            'data'  => $data,
        ]);
    }

    /**
     * AJAX: Delete canvas
     */
    public static function ajax_delete_canvas()
    {
        check_ajax_referer('mgwpp_canvas_nonce', 'nonce');

        if (!MGWPP_Canvas_Capabilities::current_user_can_delete()) {
            wp_send_json_error(['message' => __('Permission denied', 'mini-gallery')], 403);
        }

        $canvas_id = isset($_POST['canvas_id']) ? absint($_POST['canvas_id']) : 0;

        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            wp_send_json_error(['message' => __('Invalid canvas ID', 'mini-gallery')], 400);
        }

        $result = wp_delete_post($canvas_id, true);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete canvas', 'mini-gallery')], 500);
        }

        wp_send_json_success(['message' => __('Canvas deleted successfully', 'mini-gallery')]);
    }

    /**
     * AJAX: Render canvas preview
     */
    public static function ajax_render_preview()
    {
        // 1. Validate nonce securely
        if (!check_ajax_referer('mgwpp_canvas_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => esc_html__('Security check failed. Please refresh the page.', 'mini-gallery')
            ], 403);
        }

        // 2. Check permissions securely
        if (!MGWPP_Canvas_Capabilities::current_user_can_view()) {
            wp_send_json_error([
                'message' => esc_html__('Insufficient permissions', 'mini-gallery')
            ], 403);
        }

        // 3. Validate canvas ID
        $canvas_id = isset($_POST['canvas_id']) ? absint(wp_unslash($_POST['canvas_id'])) : 0;
        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            wp_send_json_error([
                'message' => esc_html__('Invalid canvas ID', 'mini-gallery')
            ], 400);
        }

        // 4. Render HTML
        $html = self::get_canvas_preview($canvas_id);

        wp_send_json_success([
            'html' => $html
        ]);
    }

    /**
     * Recursively sanitize canvas data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    private static function sanitize_canvas_data($data)
    {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized_key = sanitize_key($key);
                $sanitized[$sanitized_key] = self::sanitize_canvas_data($value);
            }
            return $sanitized;
        }

        if (is_string($data)) {
            // Check if it looks like a URL
            if (filter_var($data, FILTER_VALIDATE_URL)) {
                return esc_url_raw($data);
            }
            // Check if it's HTML content
            if (preg_match('/<[^>]+>/', $data)) {
                return wp_kses_post($data);
            }
            return sanitize_text_field($data);
        }

        if (is_int($data) || is_float($data)) {
            return $data;
        }

        if (is_bool($data)) {
            return $data;
        }

        return '';
    }
}
