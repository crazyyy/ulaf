<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Sidebar widget for folder management on list pages
 */
class SidebarWidget
{
    /**
     * Module instance
     *
     * @var Bootstrap
     */
    private $module;

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
     * Initialize hooks
     *
     * @return void
     */
    private function init(): void
    {
        add_action('admin_footer', array($this, 'renderSidebarWidget'));
    }

    /**
     * Render sidebar widget HTML
     *
     * @return void
     */
    public function renderSidebarWidget(): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Only show on post/media list pages
        $is_list_page = ($screen->base === 'edit' || $screen->base === 'upload');
        if (!$is_list_page) {
            return;
        }

        $post_type = $screen->post_type ?? 'attachment';
        if (!$this->module->isPostTypeEnabled($post_type)) {
            return;
        }

        ?>
        <!-- Mobile Toggle Button -->
        <button type="button" id="wpext-folder-mobile-toggle" class="page-title-action wpext-folder-mobile-toggle" aria-label="<?php esc_attr_e('Toggle Folders', WP_EXTENDED_TEXT_DOMAIN); ?>" aria-expanded="false">
            <?php esc_html_e('Folders', WP_EXTENDED_TEXT_DOMAIN); ?>
        </button>

        <!-- Mobile Overlay -->
        <div id="wpext-folder-overlay" class="wpext-folder-overlay" aria-hidden="true"></div>

        <div id="wpext-folder-sidebar" class="wpext-folder-sidebar wpext-folder-sidebar-loading">
            <div class="wpext-folder-sidebar-header">
                <h3><?php esc_html_e('Folders', WP_EXTENDED_TEXT_DOMAIN); ?></h3>
                <div class="wpext-folder-header-actions">
                    <button type="button" class="wpext-folder-new-btn" aria-label="<?php esc_attr_e('New Folder', WP_EXTENDED_TEXT_DOMAIN); ?>" title="<?php esc_attr_e('New Folder', WP_EXTENDED_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-plus-alt"></span>
                    </button>
                    <button type="button" class="wpext-folder-close-btn" aria-label="<?php esc_attr_e('Close', WP_EXTENDED_TEXT_DOMAIN); ?>" title="<?php esc_attr_e('Close', WP_EXTENDED_TEXT_DOMAIN); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
            <div class="wpext-folder-sidebar-content">
                <div class="wpext-folder-tree" data-post-type="<?php echo esc_attr($post_type); ?>">
                    <!-- Folder tree will be populated via JavaScript -->
                </div>
            </div>
        </div>
        <?php
    }
}
