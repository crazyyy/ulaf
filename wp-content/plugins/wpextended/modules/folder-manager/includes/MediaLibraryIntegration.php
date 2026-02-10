<?php

namespace Wpextended\Modules\FolderManager\Includes;

use Wpextended\Modules\FolderManager\Bootstrap;

/**
 * Media library grid/list view integration
 */
class MediaLibraryIntegration
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
        if (!$this->module->isPostTypeEnabled('attachment')) {
            return;
        }

        // Handle auto-assign on upload
        $auto_assign = $this->module->getSetting('auto_assign_on_upload', false);
        if ($auto_assign) {
            add_action('add_attachment', array($this, 'assignFolderOnUpload'));
        }

        // Add folder filter to media library grid
        add_action('wp_enqueue_media', array($this, 'enqueueMediaScripts'));
    }

    /**
     * Assign folder to uploaded media
     *
     * @param int $attachment_id Attachment ID
     * @return void
     */
    public function assignFolderOnUpload(int $attachment_id): void
    {
        $default_behavior = $this->module->getSetting('default_folder_behavior', 'uncategorized');
        if ($default_behavior === 'none') {
            return;
        }

        $folder_manager = new FolderManager($this->module);
        $folders = $folder_manager->getFoldersForPostType('attachment');

        if ($default_behavior === 'first_folder' && !empty($folders)) {
            wp_set_object_terms($attachment_id, array($folders[0]->term_id), $this->taxonomy);
        }
        // 'uncategorized' means no folder assignment
    }

    /**
     * Enqueue scripts for media library
     *
     * @return void
     */
    public function enqueueMediaScripts(): void
    {
        // Add JavaScript to hook into wp.media queries
        add_action('admin_footer', array($this, 'addMediaLibraryScript'));
    }

    /**
     * Add JavaScript to filter media library grid view queries
     *
     * @return void
     */
    public function addMediaLibraryScript(): void
    {
        // Only on media library page
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'upload') {
            return;
        }

        // Get current folder filter from URL using filter_input
        $folder_id_raw = filter_input(INPUT_GET, 'wpext_folder', FILTER_VALIDATE_INT);
        $folder_id = ($folder_id_raw !== false && $folder_id_raw !== null) ? absint($folder_id_raw) : '';
        $taxonomy = esc_js($this->taxonomy);
        ?>
        <script type="text/javascript">
        (function($) {
            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            var taxonomy = <?php echo json_encode($taxonomy); ?>;
            var folderId = <?php echo $folder_id !== '' ? json_encode($folder_id) : 'null'; ?>;

            // Function to get folder ID from URL
            function getFolderIdFromUrl() {
                var urlParams = new URLSearchParams(window.location.search);
                var folder = urlParams.get('wpext_folder');
                return folder !== null ? parseInt(folder, 10) : null;
            }

            // Override wp.media.model.Query to include folder filter
            var originalQuery = wp.media.model.Query.prototype.get;
            wp.media.model.Query.prototype.get = function(prop) {
                var result = originalQuery.apply(this, arguments);
                if (prop === 'query' && result) {
                    var urlFolderId = getFolderIdFromUrl();
                    var currentFolderId = urlFolderId !== null ? urlFolderId : folderId;
                    // Include folder filter (including 0 for uncategorized)
                    if (currentFolderId !== null) {
                        result[taxonomy] = currentFolderId;
                    }
                }
                return result;
            };

            // Override Attachments query to include folder filter
            var originalAttachments = wp.media.model.Attachments.prototype.sync;
            wp.media.model.Attachments.prototype.sync = function(method, model, options) {
                if (method === 'read' && options && options.data) {
                    var urlFolderId = getFolderIdFromUrl();
                    var currentFolderId = urlFolderId !== null ? urlFolderId : folderId;

                    // Include folder filter (including 0 for uncategorized)
                    if (currentFolderId !== null) {
                        // WordPress media library sends query parameters nested under 'query'
                        if (options.data.query && typeof options.data.query === 'object') {
                            options.data.query[taxonomy] = currentFolderId;
                        } else {
                            // Initialize query object if it doesn't exist
                            if (!options.data.query) {
                                options.data.query = {};
                            }
                            options.data.query[taxonomy] = currentFolderId;
                        }
                    }
                }
                return originalAttachments.apply(this, arguments);
            };
        })(jQuery);
        </script>
        <?php
    }
}
