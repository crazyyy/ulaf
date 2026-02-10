<?php
/**
 * Class Renamer_UI_Manager
 * Manages the UI for the Image Renamer functionality
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
class Renamer_UI_Manager {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Initialize the class and set its properties.
     */
    private function __construct() {
        // Aggiungi gli script e gli stili necessari
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Il metodo add_admin_menu è stato rimosso e spostato in ImgSEO_Menu_Manager

    /**
     * Enqueue scripts and styles for the renamer UI
     */
    public function enqueue_scripts($hook) {
        // Carica script e stili solo nella pagina del renamer
        if ($hook !== 'imgseo_page_imgseo-renamer') {
            return;
        }

        // Registra lo stile principale
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-dialog');
        
        // Aggiungi jQuery UI Effects per il highlight effect
        wp_enqueue_script('jquery-effects-highlight');

        // Media uploader
        wp_enqueue_media();
    }

    /**
     * Render the renamer admin page
     */
    public function render_renamer_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'imgseo-ai-alt-text-generator'));
        }

        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'rename';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="imgseo-tabs">
                <div class="nav-tab-wrapper">
                    <a href="?page=imgseo-renamer&tab=rename" class="nav-tab <?php echo $active_tab == 'rename' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Rename Images', 'imgseo-ai-alt-text-generator'); ?></a>
                    <a href="?page=imgseo-renamer&tab=bulk-rename" class="nav-tab <?php echo $active_tab == 'bulk-rename' ? 'nav-tab-active' : ''; ?>">🚀 <?php esc_html_e('Bulk Rename', 'imgseo-ai-alt-text-generator'); ?></a>
                    <a href="?page=imgseo-renamer&tab=settings" class="nav-tab nav-tab-settings <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Renamer Settings', 'imgseo-ai-alt-text-generator'); ?></a>
                </div>

                <div id="tab-rename" class="tab-content <?php echo $active_tab == 'rename' ? 'active' : ''; ?>">
                    <?php $this->render_rename_tab(); ?>
                </div>

                <div id="tab-bulk-rename" class="tab-content <?php echo $active_tab == 'bulk-rename' ? 'active' : ''; ?>">
                    <?php $this->render_bulk_rename_tab(); ?>
                </div>

                <div id="tab-settings" class="tab-content <?php echo $active_tab == 'settings' ? 'active' : ''; ?>">
                    <form method="post" action="options.php">
                        <input type="hidden" name="imgseo_active_tab" value="settings">
                        <?php
                        settings_fields('imgseo_renamer_settings');
                        do_settings_sections('imgseo_renamer_settings');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
        </div>

        <style>
            /* Stili comuni per tutte le tab */
            .tab-content {
                display: none;
                padding: 20px 0;
            }
            .tab-content.active {
                display: block;
            }

            /* Stili specifici per immagini */
            .imgseo-image-list {
                margin-top: 15px;
            }
            .imgseo-image-list .image-column {
                width: 110px;
            }
            .imgseo-image-list .filename-column {
                width: auto;
            }
            .imgseo-image-list .actions-column {
                width: 240px;
                text-align: right;
            }
            .imgseo-image-list .thumbnail {
                max-width: 80px;
                max-height: 80px;
                margin: 5px;
            }
            .imgseo-filename-input {
                width: 100%;
            }
            .generate-ai-button {
                margin-top: 5px !important;
            }
            .imgseo-image-row {
                background-color: #f9f9f9;
            }
            .imgseo-image-row:nth-child(odd) {
                background-color: #ffffff;
            }
            .imgseo-image-list tr.success-highlight {
                background-color: #edfaef !important;
                transition: background-color 0.5s ease;
            }

            /* Log status styles */
            .imgseo-log-status {
                display: inline-block;
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 500;
                text-transform: capitalize;
            }

            .imgseo-log-status.success {
                background-color: #edfaef;
                color: #0a7a2c;
            }

            .imgseo-log-status.error {
                background-color: #fef1f1;
                color: #c32727;
            }

            .imgseo-log-status.restore {
                background-color: #e8f4fd;
                color: #0073aa;
            }

            /* Restore button styling */
            .restore-button {
                margin-left: 8px !important;
                vertical-align: middle !important;
            }

            /* Stili per la tab di Batch Rename */
            .imgseo-batch-options {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 3px;
            }

            .imgseo-patterns-help {
                background: #f8f9fa;
                padding: 10px 15px;
                border-left: 4px solid #007cba;
                margin: 10px 0;
            }

            .imgseo-patterns-help ul {
                margin-left: 20px;
            }

            .imgseo-batch-preview {
                margin-top: 25px;
            }

            .imgseo-selected-images {
                display: flex;
                flex-wrap: wrap;
                margin: 15px 0;
            }

            .imgseo-selected-image {
                margin: 0 10px 10px 0;
                background: #fff;
                border: 1px solid #ddd;
                padding: 10px;
                border-radius: 3px;
                width: 150px;
                text-align: center;
                position: relative;
            }

            .imgseo-selected-image img {
                max-width: 100%;
                height: auto;
                max-height: 100px;
            }

            .imgseo-selected-image .filename {
                margin-top: 5px;
                font-size: 12px;
                word-break: break-word;
            }

            .imgseo-selected-image .new-filename {
                font-weight: bold;
                color: #007cba;
            }

            .imgseo-remove-image {
                position: absolute;
                top: 5px;
                right: 5px;
                background: #f1f1f1;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                text-align: center;
                line-height: 18px;
                cursor: pointer;
                color: #999;
            }

            .imgseo-remove-image:hover {
                background: #e5e5e5;
                color: #666;
            }

            .imgseo-batch-actions {
                margin-top: 15px;
            }

            .imgseo-batch-results {
                margin-top: 25px;
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 15px;
                border-radius: 3px;
            }

            .imgseo-batch-result-item {
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid #f1f1f1;
            }

            .imgseo-batch-result-item:last-child {
                border-bottom: none;
            }

            .imgseo-batch-result-item.success {
                color: #0a7a2c;
            }

            .imgseo-batch-result-item.error {
                color: #c32727;
            }

            .imgseo-batch-result-item.skipped {
                color: #856404;
            }
        </style>
        <?php
    }

    /**
     * Render the rename tab content
     */
    private function render_rename_tab() {
        // Get current page number
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20; // Number of images per page

        // Query for all image attachments
        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $images_query = new WP_Query($args);
        $total_images = $images_query->found_posts;
        $total_pages = ceil($total_images / $per_page);
        ?>
        <div class="imgseo-renamer-container">
            <div class="imgseo-renamer-intro">
                <h2><?php esc_html_e('Image Renamer Tool', 'imgseo-ai-alt-text-generator'); ?></h2>
                <p><?php esc_html_e('Safely rename your WordPress media files while maintaining all references to prevent broken links and 404 errors.', 'imgseo-ai-alt-text-generator'); ?></p>
            </div>

            <!-- Important Warning Banner -->
            <div class="imgseo-warning-banner" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 20px 0; display: flex; align-items: flex-start;">
                <span class="dashicons dashicons-warning" style="color: #856404; font-size: 20px; margin-right: 10px; margin-top: 2px;"></span>
                <div>
                    <strong style="color: #856404;">⚠️ Important - Create Backup First</strong>
                    <p style="margin: 8px 0 0 0; color: #856404; line-height: 1.5;">Renaming files is a sensitive operation that affects file system, database, and content references. Always create a complete backup before renaming.</p>
                </div>
            </div>

            <div id="imgseo-renamer-result" class="imgseo-renamer-result hidden">
                <div id="imgseo-renamer-success" class="notice notice-success hidden">
                    <p>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Image successfully renamed!', 'imgseo-ai-alt-text-generator'); ?>
                    </p>
                    <p id="imgseo-renamer-success-details"></p>
                </div>

                <div id="imgseo-renamer-error" class="notice notice-error hidden">
                    <p>
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e('Error renaming image:', 'imgseo-ai-alt-text-generator'); ?>
                        <span id="imgseo-renamer-error-message"></span>
                    </p>
                </div>
            </div>

            <?php if ($images_query->have_posts()) : ?>
                <div id="imgseo-image-list-container">
                    <h3><?php esc_html_e('Media Library Images', 'imgseo-ai-alt-text-generator'); ?></h3>
                    <p class="description"><?php esc_html_e('Edit filenames without the extension. Special characters will be converted to hyphens.', 'imgseo-ai-alt-text-generator'); ?></p>

                    <div class="tablenav top">
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php 
                                // translators: %s is the number of items in the media library
                                printf(esc_html(_n('%s item', '%s items', $total_images, 'imgseo-ai-alt-text-generator')), esc_html(number_format_i18n($total_images))); ?>
                            </span>
                            <?php if ($total_pages > 1) : ?>
                                <span class="pagination-links">
                                    <?php
                                    // First page link
                                    if ($paged > 1) {
                                        printf('<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">«</span></a>',
                                            esc_url(add_query_arg(array('paged' => 1, 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                            esc_html__('First page', 'imgseo-ai-alt-text-generator')
                                        );
                                    } else {
                                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                                    }

                                    // Previous page link
                                    if ($paged > 1) {
                                        printf('<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span></a>',
                                            esc_url(add_query_arg(array('paged' => max(1, $paged - 1), 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                            esc_html__('Previous page', 'imgseo-ai-alt-text-generator')
                                        );
                                    } else {
                                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
                                    }

                                    // Current page number
                                    printf('<span class="paging-input">%s <span class="tablenav-paging-text">%s <span class="total-pages">%s</span></span></span>',
                                        esc_html($paged),
                                        esc_html__('of', 'imgseo-ai-alt-text-generator'),
                                        esc_html(number_format_i18n($total_pages))
                                    );

                                    // Next page link
                                    if ($paged < $total_pages) {
                                        printf('<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">›</span></a>',
                                            esc_url(add_query_arg(array('paged' => min($total_pages, $paged + 1), 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                            esc_html__('Next page', 'imgseo-ai-alt-text-generator')
                                        );
                                    } else {
                                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                                    }

                                    // Last page link
                                    if ($paged < $total_pages) {
                                        printf('<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">»</span></a>',
                                            esc_url(add_query_arg(array('paged' => $total_pages, 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                            esc_html__('Last page', 'imgseo-ai-alt-text-generator')
                                        );
                                    } else {
                                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="widefat striped imgseo-image-list">
                        <thead>
                            <tr>
                                <th class="image-column"><?php esc_html_e('Image', 'imgseo-ai-alt-text-generator'); ?></th>
                                <th class="filename-column"><?php esc_html_e('Filename', 'imgseo-ai-alt-text-generator'); ?></th>
                                <th class="actions-column"><?php esc_html_e('Actions', 'imgseo-ai-alt-text-generator'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="imgseo-image-list-body">
                            <?php
                            while ($images_query->have_posts()) {
                                $images_query->the_post();
                                $attachment_id = get_the_ID();
                                $attachment_url = wp_get_attachment_url($attachment_id);
                                $attachment_thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail')[0];
                                $filename = basename($attachment_url);
                                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
                                ?>
                                <tr id="imgseo-image-row-<?php echo esc_attr($attachment_id); ?>" class="imgseo-image-row">
                    <td class="image-column">
                        <img class="thumbnail" src="<?php echo esc_url($attachment_thumbnail); ?>" alt="<?php echo esc_attr($filename); ?>">
                        <div class="file-info-container">
                            <div class="filename-display" id="filename-display-<?php echo esc_attr($attachment_id); ?>">
                                <?php echo esc_html($filename); ?>
                            </div>
                            <div class="original-path" id="original-path-<?php echo esc_attr($attachment_id); ?>">
                                <?php
                                $upload_dir = wp_upload_dir();
                                $file_path = str_replace($upload_dir['baseurl'], '', $attachment_url);
                                $path_parts = pathinfo($file_path);
                                $dir_name = $path_parts['dirname'];
                                echo esc_html(trim($dir_name, '/'));
                                ?>
                            </div>
                        </div>
                    </td>
                                    <td class="filename-column">
                                        <input type="text" class="imgseo-filename-input" id="imgseo-filename-<?php echo esc_attr($attachment_id); ?>" value="<?php echo esc_attr($filename_without_ext); ?>">
                                        <br>
                                        <button type="button" class="button generate-ai-button" data-id="<?php echo esc_attr($attachment_id); ?>">
                                            <span class="dashicons dashicons-update"></span>
                                            <?php esc_html_e('Generate Filename (AI)', 'imgseo-ai-alt-text-generator'); ?>
                                        </button>
                                        <span class="ai-result-message" id="ai-result-<?php echo esc_attr($attachment_id); ?>"></span>
                                    </td>
                                    <td class="actions-column">
                                        <button type="button" class="button btn-custom-primary save-filename-button" data-id="<?php echo esc_attr($attachment_id); ?>" data-extension="<?php echo esc_attr('.' . $extension); ?>">
                                            <?php esc_html_e('Save Filename', 'imgseo-ai-alt-text-generator'); ?>
                                        </button>
                                        <span class="spinner" style="float: none; margin: 0 0 0 5px;"></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            wp_reset_postdata();
                            ?>
                        </tbody>
                    </table>

                    <?php if ($total_pages > 1) : ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num">
                                <?php 
                                // translators: %s is the number of items in the media library
                                printf(esc_html(_n('%s item', '%s items', $total_images, 'imgseo-ai-alt-text-generator')), esc_html(number_format_i18n($total_images))); ?>
                            </span>
                            <span class="pagination-links">
                                <?php
                                // First page link
                                if ($paged > 1) {
                                    printf('<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">«</span></a>',
                                        esc_url(add_query_arg(array('paged' => 1, 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                        esc_html__('First page', 'imgseo-ai-alt-text-generator')
                                    );
                                } else {
                                    echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                                }

                                // Previous page link
                                if ($paged > 1) {
                                    printf('<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span></a>',
                                        esc_url(add_query_arg(array('paged' => max(1, $paged - 1), 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                        esc_html__('Previous page', 'imgseo-ai-alt-text-generator')
                                    );
                                } else {
                                    echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
                                }

                                // Current page number
                                printf('<span class="paging-input">%s <span class="tablenav-paging-text">%s <span class="total-pages">%s</span></span></span>',
                                    esc_html($paged),
                                    esc_html__('of', 'imgseo-ai-alt-text-generator'),
                                    esc_html(number_format_i18n($total_pages))
                                );

                                // Next page link
                                if ($paged < $total_pages) {
                                    printf('<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">›</span></a>',
                                        esc_url(add_query_arg(array('paged' => min($total_pages, $paged + 1), 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                        esc_html__('Next page', 'imgseo-ai-alt-text-generator')
                                    );
                                } else {
                                    echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                                }

                                // Last page link
                                if ($paged < $total_pages) {
                                    printf('<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">»</span></a>',
                                        esc_url(add_query_arg(array('paged' => $total_pages, 'tab' => 'rename'), admin_url('admin.php?page=imgseo-renamer'))),
                                        esc_html__('Last page', 'imgseo-ai-alt-text-generator')
                                    );
                                } else {
                                    echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No images found in your media library.', 'imgseo-ai-alt-text-generator'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Gestione click sul pulsante Generate Filename (AI)
                $('.generate-ai-button').on('click', function() {
                    var $button = $(this);
                    var attachmentId = $button.data('id');
                    var $row = $button.closest('tr');
                    var $spinner = $row.find('.spinner');
                    var $input = $row.find('.imgseo-filename-input');
                    var $resultMessage = $('#ai-result-' + attachmentId);

                    // Disabilita il pulsante e mostra lo spinner
                    $button.prop('disabled', true);
                    $spinner.addClass('is-active');
                    $resultMessage.html('').removeClass('error success');

                    // Chiamata AJAX (genera e poi salva automaticamente)
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_generate_ai_filename',
                            attachment_id: attachmentId,
                            source: 'ai_generator', // Aggiungi il parametro source per ottimizzare la velocità
                            force_refresh: true, // Disabilita cache per permettere variazioni AI ogni volta
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Inserisci il nome file generato nell'input
                                $input.val(response.data.filename);
                                // Evidenzia l'input per attirare l'attenzione
                                $input.effect('highlight', {}, 1000);
                                // Procedi subito al salvataggio del filename generato
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'imgseo_rename_image',
                                        attachment_id: attachmentId,
                                        new_filename: response.data.filename,
                                        security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                                    },
                                    success: function(saveResponse) {
                                        $spinner.removeClass('is-active');
                                        $button.prop('disabled', false);

                                        if (saveResponse.success) {
                                            // Aggiorna il testo del nome file sotto l'immagine
                                            var $filenameDisplay = $('#filename-display-' + attachmentId);
                                            if ($filenameDisplay.length) {
                                                $filenameDisplay.text(saveResponse.data.new_filename);
                                                $filenameDisplay.css('background-color', '#c7f3c8');
                                                setTimeout(function() { $filenameDisplay.css('background-color', ''); }, 2000);
                                            }

                                            // Aggiorna la thumbnail per riflettere il nuovo file (se disponibile)
                                            var newUrl = saveResponse.data.new_url ? (saveResponse.data.new_url + '?v=' + (new Date().getTime())) : null;
                                            if (newUrl) {
                                                $row.find('.thumbnail').attr('src', newUrl).attr('alt', saveResponse.data.new_filename);
                                            }

                                            $resultMessage.html('✓ ' + '<?php echo esc_js(__('Name generated successfully and saved', 'imgseo-ai-alt-text-generator')); ?>').addClass('success');
                                        } else {
                                            $resultMessage.html('⚠ ' + (saveResponse.data.message || '<?php echo esc_js(__('Error saving filename', 'imgseo-ai-alt-text-generator')); ?>')).addClass('error');
                                        }
                                    },
                                    error: function() {
                                        $spinner.removeClass('is-active');
                                        $button.prop('disabled', false);
                                        $resultMessage.html('⚠ ' + '<?php echo esc_js(__('Server error while saving', 'imgseo-ai-alt-text-generator')); ?>').addClass('error');
                                    }
                                });
                            } else {
                                $spinner.removeClass('is-active');
                                $button.prop('disabled', false);
                                // Show error
                                $resultMessage.html('⚠ ' + (response.data.message || '<?php echo esc_js(__('Error generating filename', 'imgseo-ai-alt-text-generator')); ?>')).addClass('error');
                            }
                        },
                        error: function() {
                            $spinner.removeClass('is-active');
                            $button.prop('disabled', false);
                            $resultMessage.html('⚠ ' + '<?php echo esc_js(__('Server error', 'imgseo-ai-alt-text-generator')); ?>').addClass('error');
                        }
                    });
                });

                // Bind save button click for all images
                $('.save-filename-button').on('click', function() {
                    var attachmentId = $(this).data('id');
                    var extension = $(this).data('extension');
                    var newFilename = $('#imgseo-filename-' + attachmentId).val().trim();

                    if (!newFilename) {
                        alert('<?php echo esc_js(__('Please enter a filename.', 'imgseo-ai-alt-text-generator')); ?>');
                        return;
                    }

                    // Show spinner
                    var row = $('#imgseo-image-row-' + attachmentId);
                    var spinner = row.find('.spinner');
                    var saveButton = row.find('.save-filename-button');

                    spinner.addClass('is-active');
                    saveButton.prop('disabled', true);

                    // Hide previous results
                    $('#imgseo-renamer-result').addClass('hidden');
                    $('#imgseo-renamer-success').addClass('hidden');
                    $('#imgseo-renamer-error').addClass('hidden');

                    // Send AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_rename_image',
                            attachment_id: attachmentId,
                            new_filename: newFilename,
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                        },
                        success: function(response) {
                            spinner.removeClass('is-active');
                            saveButton.prop('disabled', false);

                            if (response.success) {
                                // Aggiorna l'immagine con il nuovo URL aggiungendo un parametro per forzare il reload
                                var newUrl = response.data.new_url + '?v=' + (new Date().getTime());
                                row.find('.thumbnail').attr('src', newUrl);

                                // Aggiorna anche il nome file visualizzato nell'input
                                var filenameWithoutExt = response.data.new_filename.split('.').slice(0, -1).join('.');
                                var $input = $('#imgseo-filename-' + attachmentId);
                                $input.val(filenameWithoutExt);
                                $input.effect('highlight', {color: '#c7f3c8'}, 2000);

                                // Aggiorna l'attributo alt dell'immagine
                                row.find('.thumbnail').attr('alt', response.data.new_filename);

                                // Aggiorna il testo del nome file sotto l'immagine
                                var $filenameDisplay = $('#filename-display-' + attachmentId);
                                $filenameDisplay.text(response.data.new_filename);
                                $filenameDisplay.css('background-color', '#c7f3c8');
                                setTimeout(function() {
                                    $filenameDisplay.css('background-color', '');
                                }, 2000);

                                // Il percorso rimane lo stesso, aggiorniamo solo l'aspetto
                                var $originalPath = $('#original-path-' + attachmentId);
                                if ($originalPath.length) {
                                    $originalPath.css('background-color', '#c7f3c8');
                                    setTimeout(function() {
                                        $originalPath.css('background-color', '');
                                    }, 2000);
                                }

                                // Highlight the row briefly to indicate success
                                row.addClass('success-highlight');
                                setTimeout(function() {
                                    row.removeClass('success-highlight');
                                }, 2000);

                                // Show success message
                                $('#imgseo-renamer-success-details').html('<?php esc_html_e('Renamed:', 'imgseo-ai-alt-text-generator'); ?> <strong>' + response.data.old_filename + '</strong> → <strong>' + response.data.new_filename + '</strong>');
                                $('#imgseo-renamer-success').removeClass('hidden');
                                $('#imgseo-renamer-result').removeClass('hidden');

                                // Scroll to success message
                                $('html, body').animate({
                                    scrollTop: $('#imgseo-renamer-result').offset().top - 50
                                }, 500);
                            } else {
                                // Show error message
                                $('#imgseo-renamer-error-message').text(response.data.message);
                                $('#imgseo-renamer-error').removeClass('hidden');
                                $('#imgseo-renamer-result').removeClass('hidden');

                                // Scroll to error message
                                $('html, body').animate({
                                    scrollTop: $('#imgseo-renamer-result').offset().top - 50
                                }, 500);
                            }
                        },
                        error: function(xhr, status, error) {
                            spinner.removeClass('is-active');
                            saveButton.prop('disabled', false);

                            $('#imgseo-renamer-error-message').text('<?php esc_html_e('Server error. Please try again.', 'imgseo-ai-alt-text-generator'); ?>');
                            $('#imgseo-renamer-error').removeClass('hidden');
                            $('#imgseo-renamer-result').removeClass('hidden');

                            // Scroll to error message
                            $('html, body').animate({
                                scrollTop: $('#imgseo-renamer-result').offset().top - 50
                            }, 500);
                        }
                    });
                });
            });
        </script>

        <style>
            .ai-result-message {
                display: inline-block;
                margin-left: 10px;
                font-size: 12px;
                padding: 3px 6px;
            }
            .ai-result-message.success {
                color: green;
            }
            .ai-result-message.error {
                color: red;
            }
            .file-info-container {
                margin-top: 5px;
                width: 100%;
            }
            .filename-display {
                font-size: 11px;
                color: #666;
                text-align: center;
                word-break: break-all;
                font-weight: bold;
            }
            .original-path {
                font-size: 10px;
                color: #888;
                margin-top: 3px;
                text-align: center;
                word-break: break-all;
                border-top: 1px dotted #ddd;
                padding-top: 3px;
            }
        </style>
        <?php
    }

    /**
     * Render the batch rename tab content
     */
    private function render_batch_tab() {
        // Ottieni le impostazioni salvate
        $settings_manager = Renamer_Settings_Manager::get_instance();
        $pattern_template = $settings_manager->get_setting('pattern_template', '{post_title}-{numero}');
        $remove_accents = $settings_manager->is_enabled('remove_accents', true);
        $lowercase = $settings_manager->is_enabled('lowercase', true);
        $handle_duplicates = $settings_manager->get_setting('handle_duplicates', 'increment');
        ?>
        <div class="imgseo-batch-renamer">
            <div class="imgseo-batch-intro">
                <h2><?php esc_html_e('Batch Image Renamer', 'imgseo-ai-alt-text-generator'); ?></h2>
                <p><?php esc_html_e('Rename multiple images at once using patterns and rules. Select images from the media library and apply a common naming pattern to all of them.', 'imgseo-ai-alt-text-generator'); ?></p>
            </div>

            <div id="imgseo-batch-result" class="imgseo-batch-result hidden">
                <div id="imgseo-batch-success" class="notice notice-success hidden">
                    <p>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span id="imgseo-batch-success-message"></span>
                    </p>
                </div>

                <div id="imgseo-batch-error" class="notice notice-error hidden">
                    <p>
                        <span class="dashicons dashicons-warning"></span>
                        <span id="imgseo-batch-error-message"></span>
                    </p>
                </div>
            </div>

            <div class="imgseo-batch-options">
                <h3><?php esc_html_e('Rename Options', 'imgseo-ai-alt-text-generator'); ?></h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Pattern', 'imgseo-ai-alt-text-generator'); ?></th>
                        <td>
                            <input type="text" id="imgseo-batch-pattern" class="regular-text" value="<?php echo esc_attr($pattern_template); ?>" />
                            <p class="description"><?php esc_html_e('Pattern to use for renaming files.', 'imgseo-ai-alt-text-generator'); ?></p>

                            <div class="imgseo-patterns-help">
                                <h4><?php esc_html_e('Available Patterns:', 'imgseo-ai-alt-text-generator'); ?></h4>
                                <ul>
                                    <li><code>{post_title}</code> - <?php esc_html_e('Title of associated post/page', 'imgseo-ai-alt-text-generator'); ?></li>
                                    <li><code>{category}</code> - <?php esc_html_e('Main category of associated post/page', 'imgseo-ai-alt-text-generator'); ?></li>
                                    <li><code>{numero}</code> - <?php esc_html_e('Sequential number (001, 002, etc.)', 'imgseo-ai-alt-text-generator'); ?></li>
                                    <li><code>{originale}</code> - <?php esc_html_e('Original filename', 'imgseo-ai-alt-text-generator'); ?></li>
                                    <li><code>{data}</code> - <?php esc_html_e('Date in YYYYMMDD format', 'imgseo-ai-alt-text-generator'); ?></li>
                                    <li><code>{alt}</code> - <?php esc_html_e('Alt text of the image', 'imgseo-ai-alt-text-generator'); ?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Sanitization', 'imgseo-ai-alt-text-generator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="imgseo-batch-lowercase" <?php checked($lowercase); ?> />
                                <?php esc_html_e('Convert to lowercase', 'imgseo-ai-alt-text-generator'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" id="imgseo-batch-remove-accents" <?php checked($remove_accents); ?> />
                                <?php esc_html_e('Remove accents', 'imgseo-ai-alt-text-generator'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Handle Duplicates', 'imgseo-ai-alt-text-generator'); ?></th>
                        <td>
                            <select id="imgseo-batch-handle-duplicates">
                                <option value="increment" <?php selected($handle_duplicates, 'increment'); ?>><?php esc_html_e('Add sequential number (file-1.jpg)', 'imgseo-ai-alt-text-generator'); ?></option>
                                <option value="timestamp" <?php selected($handle_duplicates, 'timestamp'); ?>><?php esc_html_e('Add timestamp (file-1679419361.jpg)', 'imgseo-ai-alt-text-generator'); ?></option>
                                <option value="fail" <?php selected($handle_duplicates, 'fail'); ?>><?php esc_html_e('Skip if duplicate exists', 'imgseo-ai-alt-text-generator'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="imgseo-batch-action">
                    <button type="button" id="imgseo-select-images" class="button btn-custom-primary">
                        <span class="dashicons dashicons-admin-media" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Select Images', 'imgseo-ai-alt-text-generator'); ?>
                    </button>
                </div>
            </div>

            <div id="imgseo-batch-preview" class="imgseo-batch-preview hidden">
                <h3><?php esc_html_e('Selected Images', 'imgseo-ai-alt-text-generator'); ?> (<span id="imgseo-selected-count">0</span>)</h3>

                <button type="button" id="imgseo-preview-rename" class="button btn-custom-secondary">
                    <span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span>
                    <?php esc_html_e('Preview New Filenames', 'imgseo-ai-alt-text-generator'); ?>
                </button>

                <div id="imgseo-selected-images" class="imgseo-selected-images"></div>

                <div class="imgseo-batch-actions">
                    <button type="button" id="imgseo-start-batch" class="button btn-custom-primary">
                        <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Rename Selected Images', 'imgseo-ai-alt-text-generator'); ?>
                    </button>
                    <span class="spinner" style="float: none; margin: 0 0 0 5px;"></span>
                </div>
            </div>

            <div id="imgseo-batch-results" class="imgseo-batch-results hidden">
                <h3><?php esc_html_e('Results', 'imgseo-ai-alt-text-generator'); ?></h3>
                <div id="imgseo-results-content"></div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                var selectedImages = [];

                // Apri il media uploader quando si clicca sul pulsante "Seleziona Immagini"
                $('#imgseo-select-images').on('click', function(e) {
                    e.preventDefault();

                    var mediaUploader = wp.media({
                        title: '<?php esc_html_e('Select Images to Rename', 'imgseo-ai-alt-text-generator'); ?>',
                        button: {
                            text: '<?php esc_html_e('Select Images', 'imgseo-ai-alt-text-generator'); ?>'
                        },
                        multiple: true,
                        library: {
                            type: 'image'
                        }
                    });

                    mediaUploader.on('select', function() {
                        var selection = mediaUploader.state().get('selection');

                        // Reset selection
                        selectedImages = [];

                        selection.each(function(attachment) {
                            // Get attachment details
                            var id = attachment.get('id');
                            var url = attachment.get('url');
                            var filename = url.split('/').pop();
                            var filenameWithoutExt = filename.split('.').slice(0, -1).join('.');
                            var extension = filename.split('.').pop();
                            var thumbnailUrl = attachment.get('sizes') && attachment.get('sizes').thumbnail ?
                                               attachment.get('sizes').thumbnail.url : url;

                            // Add to selected images array
                            selectedImages.push({
                                id: id,
                                url: url,
                                thumbnail: thumbnailUrl,
                                filename: filename,
                                filenameWithoutExt: filenameWithoutExt,
                                extension: extension,
                                title: attachment.get('title') || '',
                                alt: attachment.get('alt') || '',
                                newFilename: ''
                            });
                        });

                        // Show selected images
                        renderSelectedImages();

                        // Show preview section
                        $('#imgseo-batch-preview').removeClass('hidden');
                        $('#imgseo-selected-count').text(selectedImages.length);
                    });

                    mediaUploader.open();
                });

                // Funzione per visualizzare le immagini selezionate
                function renderSelectedImages() {
                    var html = '';

                    if (selectedImages.length === 0) {
                        $('#imgseo-selected-images').html('<p><?php esc_html_e('No images selected.', 'imgseo-ai-alt-text-generator'); ?></p>');
                        return;
                    }

                    $.each(selectedImages, function(index, image) {
                        html += '<div class="imgseo-selected-image" data-id="' + image.id + '">';
                        html += '<div class="imgseo-remove-image" data-index="' + index + '">×</div>';
                        html += '<img src="' + image.thumbnail + '" alt="' + image.filename + '" />';
                        html += '<div class="filename">' + image.filename + '</div>';

                        if (image.newFilename) {
                            html += '<div class="new-filename">' + image.newFilename + '.' + image.extension + '</div>';
                        }

                        html += '</div>';
                    });

                    $('#imgseo-selected-images').html(html);

                    // Bind remove action
                    $('.imgseo-remove-image').on('click', function() {
                        var index = $(this).data('index');
                        selectedImages.splice(index, 1);
                        renderSelectedImages();
                        $('#imgseo-selected-count').text(selectedImages.length);

                        if (selectedImages.length === 0) {
                            $('#imgseo-batch-preview').addClass('hidden');
                        }
                    });
                }

                // Anteprima dei nuovi nomi file
                $('#imgseo-preview-rename').on('click', function() {
                    var pattern = $('#imgseo-batch-pattern').val();
                    var lowercase = $('#imgseo-batch-lowercase').is(':checked');
                    var removeAccents = $('#imgseo-batch-remove-accents').is(':checked');
                    var handleDuplicates = $('#imgseo-batch-handle-duplicates').val();

                    // Hide previous results
                    $('#imgseo-batch-result').addClass('hidden');
                    $('#imgseo-batch-success').addClass('hidden');
                    $('#imgseo-batch-error').addClass('hidden');
                    $('#imgseo-results-content').empty();
                    $('#imgseo-batch-results').addClass('hidden');

                    if (selectedImages.length === 0) {
                        $('#imgseo-batch-error-message').text('<?php esc_html_e('No images selected.', 'imgseo-ai-alt-text-generator'); ?>');
                        $('#imgseo-batch-error').removeClass('hidden');
                        $('#imgseo-batch-result').removeClass('hidden');
                        return;
                    }

                    var attachmentIds = selectedImages.map(function(image) {
                        return image.id;
                    });

                    // Show loading
                    var $spinner = $('.imgseo-batch-actions .spinner');
                    var $previewButton = $('#imgseo-preview-rename');

                    $spinner.addClass('is-active');
                    $previewButton.prop('disabled', true);

                    // Send AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_preview_batch_rename',
                            attachment_ids: attachmentIds,
                            options: {
                                pattern: pattern,
                                lowercase: lowercase,
                                remove_accents: removeAccents,
                                handle_duplicates: handleDuplicates,
                                sanitize: true,
                                use_patterns: true
                            },
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                        },
                        success: function(response) {
                            $spinner.removeClass('is-active');
                            $previewButton.prop('disabled', false);

                            if (response.success) {
                                var previews = response.data.previews;

                                // Update selected images with previewed filenames
                                $.each(previews, function(index, preview) {
                                    var imageIndex = selectedImages.findIndex(function(image) {
                                        return image.id === preview.id;
                                    });

                                    if (imageIndex !== -1) {
                                        selectedImages[imageIndex].newFilename = preview.new_filename;
                                    }
                                });

                                // Re-render selected images
                                renderSelectedImages();

                                // Show success message
                                $('#imgseo-batch-success-message').text('<?php esc_html_e('Preview generated. Review the new filenames before proceeding.', 'imgseo-ai-alt-text-generator'); ?>');
                                $('#imgseo-batch-success').removeClass('hidden');
                                $('#imgseo-batch-result').removeClass('hidden');
                            } else {
                                // Show error message
                                $('#imgseo-batch-error-message').text(response.data.message);
                                $('#imgseo-batch-error').removeClass('hidden');
                                $('#imgseo-batch-result').removeClass('hidden');
                            }
                        },
                        error: function(xhr, status, error) {
                            $spinner.removeClass('is-active');
                            $previewButton.prop('disabled', false);

                            $('#imgseo-batch-error-message').text('<?php esc_html_e('Server error. Please try again.', 'imgseo-ai-alt-text-generator'); ?>');
                            $('#imgseo-batch-error').removeClass('hidden');
                            $('#imgseo-batch-result').removeClass('hidden');
                        }
                    });
                });

                // Esecuzione della rinomina in blocco
                $('#imgseo-start-batch').on('click', function() {
                    var pattern = $('#imgseo-batch-pattern').val();
                    var lowercase = $('#imgseo-batch-lowercase').is(':checked');
                    var removeAccents = $('#imgseo-batch-remove-accents').is(':checked');
                    var handleDuplicates = $('#imgseo-batch-handle-duplicates').val();

                    // Hide previous results
                    $('#imgseo-batch-result').addClass('hidden');
                    $('#imgseo-batch-success').addClass('hidden');
                    $('#imgseo-batch-error').addClass('hidden');

                    if (selectedImages.length === 0) {
                        $('#imgseo-batch-error-message').text('<?php esc_html_e('No images selected.', 'imgseo-ai-alt-text-generator'); ?>');
                        $('#imgseo-batch-error').removeClass('hidden');
                        $('#imgseo-batch-result').removeClass('hidden');
                        return;
                    }

                    if (!confirm('<?php echo esc_js(__('Are you sure you want to rename all selected images? This action cannot be undone.', 'imgseo-ai-alt-text-generator')); ?>')) {
                        return;
                    }

                    var attachmentIds = selectedImages.map(function(image) {
                        return image.id;
                    });

                    // Show loading
                    var $spinner = $('.imgseo-batch-actions .spinner');
                    var $batchButton = $('#imgseo-start-batch');

                    $spinner.addClass('is-active');
                    $batchButton.prop('disabled', true);

                    // Send AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_batch_rename',
                            attachment_ids: attachmentIds,
                            options: {
                                pattern: pattern,
                                lowercase: lowercase,
                                remove_accents: removeAccents,
                                handle_duplicates: handleDuplicates,
                                sanitize: true,
                                use_patterns: true
                            },
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                        },
                        success: function(response) {
                            $spinner.removeClass('is-active');
                            $batchButton.prop('disabled', false);

                            if (response.success) {
                                var results = response.data.results;
                                var totalSuccess = Object.keys(results.success).length;
                                var totalErrors = Object.keys(results.errors).length;
                                var totalSkipped = Object.keys(results.skipped || {}).length;

                                // Show success message
                                $('#imgseo-batch-success-message').text(response.data.message);
                                $('#imgseo-batch-success').removeClass('hidden');
                                $('#imgseo-batch-result').removeClass('hidden');

                                // Render results
                                var resultsHtml = '<div class="imgseo-batch-results-summary">';
                                resultsHtml += '<p><strong><?php esc_html_e('Total processed:', 'imgseo-ai-alt-text-generator'); ?></strong> ' + selectedImages.length + '</p>';
                                resultsHtml += '<p><strong><?php esc_html_e('Successfully renamed:', 'imgseo-ai-alt-text-generator'); ?></strong> ' + totalSuccess + '</p>';

                                if (totalSkipped > 0) {
                                    resultsHtml += '<p><strong><?php esc_html_e('Skipped:', 'imgseo-ai-alt-text-generator'); ?></strong> ' + totalSkipped + '</p>';
                                }

                                if (totalErrors > 0) {
                                    resultsHtml += '<p><strong><?php esc_html_e('Errors:', 'imgseo-ai-alt-text-generator'); ?></strong> ' + totalErrors + '</p>';
                                }

                                resultsHtml += '</div>';

                                // Success items
                                if (totalSuccess > 0) {
                                    resultsHtml += '<h4><?php esc_html_e('Successfully Renamed:', 'imgseo-ai-alt-text-generator'); ?></h4>';

                                    $.each(results.success, function(id, item) {
                                        resultsHtml += '<div class="imgseo-batch-result-item success">';
                                        resultsHtml += '<strong>' + item.old_filename + '</strong> → <strong>' + item.new_filename + '</strong>';
                                        resultsHtml += '</div>';
                                    });
                                }

                                // Skipped items
                                if (totalSkipped > 0) {
                                    resultsHtml += '<h4><?php esc_html_e('Skipped:', 'imgseo-ai-alt-text-generator'); ?></h4>';

                                    $.each(results.skipped, function(id, item) {
                                        resultsHtml += '<div class="imgseo-batch-result-item skipped">';
                                        resultsHtml += '<strong>' + item.old_filename + '</strong> - ' + item.message;
                                        resultsHtml += '</div>';
                                    });
                                }

                                // Error items
                                if (totalErrors > 0) {
                                    resultsHtml += '<h4><?php esc_html_e('Errors:', 'imgseo-ai-alt-text-generator'); ?></h4>';

                                    $.each(results.errors, function(id, item) {
                                        resultsHtml += '<div class="imgseo-batch-result-item error">';
                                        resultsHtml += '<strong>' + (item.old_filename || 'ID: ' + id) + '</strong> - ' + item.message;
                                        resultsHtml += '</div>';
                                    });
                                }

                                $('#imgseo-results-content').html(resultsHtml);
                                $('#imgseo-batch-results').removeClass('hidden');

                                // Scroll to results
                                $('html, body').animate({
                                    scrollTop: $('#imgseo-batch-results').offset().top - 50
                                }, 500);
                            } else {
                                // Show error message
                                $('#imgseo-batch-error-message').text(response.data.message);
                                $('#imgseo-batch-error').removeClass('hidden');
                                $('#imgseo-batch-result').removeClass('hidden');
                            }
                        },
                        error: function(xhr, status, error) {
                            $spinner.removeClass('is-active');
                            $batchButton.prop('disabled', false);

                            $('#imgseo-batch-error-message').text('<?php esc_html_e('Server error. Please try again.', 'imgseo-ai-alt-text-generator'); ?>');
                            $('#imgseo-batch-error').removeClass('hidden');
                            $('#imgseo-batch-result').removeClass('hidden');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Render the logs tab content - REMOVED
     */
    /* private function render_logs_tab() {
        // Get log retention days from settings manager
        $settings_manager = Renamer_Settings_Manager::get_instance();
        $log_retention_days = $settings_manager->get_log_retention_days();
        ?>
        <div class="imgseo-renamer-logs">
            <div class="imgseo-renamer-logs-header">
                <h2><?php esc_html_e('Rename Operation Logs', 'imgseo-ai-alt-text-generator'); ?></h2>
                <p><?php 
                // translators: %d is the number of days for log retention
                echo sprintf(esc_html__('Showing rename operations from the last %d days.', 'imgseo-ai-alt-text-generator'), esc_html($log_retention_days)); ?></p>

                <div class="imgseo-renamer-logs-actions">
                    <button type="button" id="imgseo-refresh-logs" class="button btn-custom-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Refresh Logs', 'imgseo-ai-alt-text-generator'); ?>
                    </button>
                    <button type="button" id="imgseo-delete-logs" class="button btn-custom-disconnect">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Delete All Logs', 'imgseo-ai-alt-text-generator'); ?>
                    </button>
                </div>
            </div>

            <div id="imgseo-logs-table-container">
                <table class="wp-list-table widefat fixed striped imgseo-renamer-logs-table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Date', 'imgseo-ai-alt-text-generator'); ?></th>
                            <th scope="col"><?php esc_html_e('Image ID', 'imgseo-ai-alt-text-generator'); ?></th>
                            <th scope="col"><?php esc_html_e('Original Filename', 'imgseo-ai-alt-text-generator'); ?></th>
                            <th scope="col"><?php esc_html_e('New Filename', 'imgseo-ai-alt-text-generator'); ?></th>
                            <th scope="col"><?php esc_html_e('Status', 'imgseo-ai-alt-text-generator'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="imgseo-logs-table-body">
                        <tr>
                            <td colspan="5" class="imgseo-logs-loading">
                                <?php esc_html_e('Loading logs...', 'imgseo-ai-alt-text-generator'); ?>
                                <span class="spinner is-active"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="imgseo-logs-pagination" class="imgseo-renamer-logs-pagination"></div>

            <div id="imgseo-logs-empty" class="imgseo-renamer-logs-empty hidden">
                <p><?php esc_html_e('No rename operations found in the logs.', 'imgseo-ai-alt-text-generator'); ?></p>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                var currentPage = 1;

                // Load logs on page load
                loadRenameLogs(currentPage);

                // Refresh logs
                $('#imgseo-refresh-logs').on('click', function() {
                    loadRenameLogs(1);
                });

                // Delete logs
                $('#imgseo-delete-logs').on('click', function() {
                    if (confirm('<?php echo esc_js(__('Are you sure you want to delete all rename logs? This action cannot be undone.', 'imgseo-ai-alt-text-generator')); ?>')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'imgseo_delete_rename_logs',
                                security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    loadRenameLogs(1);
                                } else {
                                    alert(response.data.message);
                                }
                            },
                            error: function() {
                                alert('<?php esc_html_e('Server error. Please try again.', 'imgseo-ai-alt-text-generator'); ?>');
                            }
                        });
                    }
                });

                // Function to load logs via AJAX
                function loadRenameLogs(page) {
                    currentPage = page;

                    // Show loading
                    $('#imgseo-logs-table-body').html('<tr><td colspan="5" class="imgseo-logs-loading"><?php esc_html_e('Loading logs...', 'imgseo-ai-alt-text-generator'); ?> <span class="spinner is-active"></span></td></tr>');
                    $('#imgseo-logs-pagination').empty();
                    $('#imgseo-logs-empty').addClass('hidden');

                    $.ajax({
                        url: ajaxurl,
                        type: 'GET',
                        data: {
                            action: 'imgseo_get_rename_logs',
                            page: page,
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.data.logs.length === 0) {
                                    $('#imgseo-logs-table-body').empty();
                                    $('#imgseo-logs-empty').removeClass('hidden');
                                } else {
                                    var logsHtml = '';

                                    $.each(response.data.logs, function(index, log) {
                                        var rowId = 'log-row-' + log.id;
                                        logsHtml += '<tr id="' + rowId + '">';
                                        logsHtml += '<td>' + log.created_at + '</td>';
                                        logsHtml += '<td>' + log.image_id + '</td>';
                                        logsHtml += '<td>' + log.old_filename + '</td>';
                                        logsHtml += '<td>' + log.new_filename + '</td>';

                                        // Status with restore button for success entries
                                        var statusClass = log.status === 'success' ? 'success' :
                                                        log.status === 'restore' ? 'restore' : 'error';

                                        logsHtml += '<td>';
                                        logsHtml += '<span class="imgseo-log-status ' + statusClass + '">' + log.status + '</span>';

                                        // Add restore button for successful rename operations
                                        // But not for entries that are already restored
                                        if (log.status === 'success') {
                                            logsHtml += ' <button type="button" id="restore-' + log.image_id + '" ' +
                                                        'class="button button-small restore-button" ' +
                                                        'data-image-id="' + log.image_id + '" ' +
                                                        'data-original="' + log.old_filename + '" ' +
                                                        'data-current="' + log.new_filename + '">' +
                                                        '<span class="dashicons dashicons-undo" style="font-size: 14px; vertical-align: text-bottom;"></span> ' +
                                                        '<?php esc_html_e('Restore', 'imgseo-ai-alt-text-generator'); ?>' +
                                                        '</button>';
                                            logsHtml += '<span class="spinner" style="float: none; margin: 0 0 0 5px;"></span>';
                                        }

                                        logsHtml += '</td>';
                                        logsHtml += '</tr>';
                                    });

                                    $('#imgseo-logs-table-body').html(logsHtml);

                                    // Bind restore buttons
                                    $('.restore-button').on('click', function() {
                                        var imageId = $(this).data('image-id');
                                        var originalFilename = $(this).data('original');
                                        var currentFilename = $(this).data('current');

                                        handleRestore(imageId, originalFilename, currentFilename);
                                    });

                                    // Generate pagination
                                    if (response.data.total_pages > 1) {
                                        var paginationHtml = '<div class="tablenav-pages">';
                                        paginationHtml += '<span class="displaying-num">' + response.data.total_items + ' <?php esc_html_e('items', 'imgseo-ai-alt-text-generator'); ?></span>';

                                                        if (response.data.total_pages > 1) {
                                                            paginationHtml += '<span class="pagination-links">';

                                                            // First page
                                                            if (page > 1) {
                                                                paginationHtml += '<a class="first-page button" href="#" data-page="1"><span class="screen-reader-text"><?php esc_html_e('First page', 'imgseo-ai-alt-text-generator'); ?></span><span aria-hidden="true">«</span></a>';
                                                            } else {
                                                                paginationHtml += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                                                            }

                                                            // Previous page
                                                            if (page > 1) {
                                                                paginationHtml += '<a class="prev-page button" href="#" data-page="' + (page - 1) + '"><span class="screen-reader-text"><?php esc_html_e('Previous page', 'imgseo-ai-alt-text-generator'); ?></span><span aria-hidden="true">‹</span></a>';
                                                            } else {
                                                                paginationHtml += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
                                                            }

                                                            // Current page
                                                            paginationHtml += '<span class="paging-input">';
                                                            paginationHtml += '<span class="tablenav-paging-text">' + page + ' <?php esc_html_e('of', 'imgseo-ai-alt-text-generator'); ?> <span class="total-pages">' + response.data.total_pages + '</span></span>';
                                                            paginationHtml += '</span>';

                                                            // Next page
                                                            if (page < response.data.total_pages) {
                                                                paginationHtml += '<a class="next-page button" href="#" data-page="' + (page + 1) + '"><span class="screen-reader-text"><?php esc_html_e('Next page', 'imgseo-ai-alt-text-generator'); ?></span><span aria-hidden="true">›</span></a>';
                                                            } else {
                                                                paginationHtml += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                                                            }

                                                            // Last page
                                                            if (page < response.data.total_pages) {
                                                                paginationHtml += '<a class="last-page button" href="#" data-page="' + response.data.total_pages + '"><span class="screen-reader-text"><?php esc_html_e('Last page', 'imgseo-ai-alt-text-generator'); ?></span><span aria-hidden="true">»</span></a>';
                                                            } else {
                                                                paginationHtml += '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
                                                            }

                                                            paginationHtml += '</span>';
                                                        }

                                                        paginationHtml += '</div>';

                                                        $('#imgseo-logs-pagination').html(paginationHtml);
                                                    }
                                                }
                                            } else {
                                                $('#imgseo-logs-table-body').html('<tr><td colspan="5" class="imgseo-logs-error">' + response.data.message + '</td></tr>');
                                            }
                                        },
                                        error: function() {
                                            $('#imgseo-logs-table-body').html('<tr><td colspan="5" class="imgseo-logs-error"><?php esc_html_e('Error loading logs. Please try again.', 'imgseo-ai-alt-text-generator'); ?></td></tr>');
                                        }
                                    });
                                }

                                // Function to handle restore operation
                                function handleRestore(imageId, originalFilename, currentFilename) {
                                    if (!confirm('<?php echo esc_js(__('Are you sure you want to restore this image to its original filename?', 'imgseo-ai-alt-text-generator')); ?>')) {
                                        return;
                                    }

                                    // Show loading spinner
                                    var $restoreBtn = $('#restore-' + imageId);
                                    var $spinner = $restoreBtn.next('.spinner');
                                    $restoreBtn.prop('disabled', true);
                                    $spinner.addClass('is-active');

                                    // Send AJAX request
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'imgseo_restore_image',
                                            image_id: imageId,
                                            original_filename: originalFilename,
                                            current_filename: currentFilename,
                                            security: '<?php echo esc_js(wp_create_nonce('imgseo_renamer_nonce')); ?>'
                                        },
                                        success: function(response) {
                                            $spinner.removeClass('is-active');

                                            if (response.success) {
                                                alert('<?php esc_html_e('Image successfully restored to its original filename.', 'imgseo-ai-alt-text-generator'); ?>');
                                                // Reload logs to show the new restore operation
                                                loadRenameLogs(currentPage);
                                            } else {
                                                $restoreBtn.prop('disabled', false);
                                                alert(response.data.message || '<?php esc_html_e('Error restoring image.', 'imgseo-ai-alt-text-generator'); ?>');
                                            }
                                        },
                                        error: function() {
                                            $spinner.removeClass('is-active');
                                            $restoreBtn.prop('disabled', false);
                                            alert('<?php esc_html_e('Server error. Please try again.', 'imgseo-ai-alt-text-generator'); ?>');
                                        }
                                    });
                                }

                                // Pagination clicks
                                $(document).on('click', '#imgseo-logs-pagination a.button', function(e) {
                                    e.preventDefault();
                                    var page = $(this).data('page');
                                    loadRenameLogs(page);
                                });
                            });
                        </script>
                        <?php
                    } */

    /**
     * Render the bulk rename tab content
     */
    private function render_bulk_rename_tab() {
        ?>
        <div class="imgseo-bulk-renamer-container">
            <!-- Header Section -->
            <div class="imgseo-bulk-renamer-header">
                <h2>🚀 Bulk Image Renamer</h2>
                <div class="imgseo-warning-banner" style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <div class="warning-content" style="display: flex; align-items: flex-start;">
                        <span class="dashicons dashicons-warning" style="color: #d63638; font-size: 20px; margin-right: 10px; margin-top: 2px;"></span>
                        <div>
                            <strong style="color: #721c24;">⚠️ DANGER ZONE - READ CAREFULLY</strong>
                            <p style="margin: 8px 0 0 0; color: #721c24; line-height: 1.5;">This tool can rename hundreds or thousands of files simultaneously. <strong>This action cannot be undone.</strong> Potential risks include incompatibilities with page builders (e.g., Visual Composer, Elementor), file permission issues, or broken image links in your content. <strong>Always backup your entire site before using this feature. Use at your own risk.</strong></p>
                        </div>
                    </div>
                </div>
                <p>Rename multiple images simultaneously using AI generation or custom patterns. This powerful tool processes files in parallel for maximum speed.</p>
            </div>

            <!-- Results/Messages Section -->
            <div id="imgseo-bulk-rename-result" class="imgseo-result-container hidden">
                <div id="imgseo-bulk-rename-success" class="notice notice-success hidden">
                    <p><span class="dashicons dashicons-yes-alt"></span> <span id="imgseo-bulk-rename-success-message"></span></p>
                </div>
                <div id="imgseo-bulk-rename-error" class="notice notice-error hidden">
                    <p><span class="dashicons dashicons-warning"></span> <span id="imgseo-bulk-rename-error-message"></span></p>
                </div>
            </div>

            <!-- Configuration Section -->
            <div class="imgseo-bulk-config-section">
                <h3>Configuration</h3>
                
                <div class="imgseo-config-row">
                    <div class="config-option">
                        <h4>AI-Powered Rename Configuration</h4>
                        <div class="ai-config-section">
                            <label for="ai-max-words">Maximum words in filename:</label>
                            <select id="ai-max-words" class="regular-text">
                                <option value="2">2 words</option>
                                <option value="3">3 words</option>
                                <option value="4" selected>4 words</option>
                                <option value="5">5 words</option>
                                <option value="6">6 words</option>
                                <option value="7">7 words</option>
                                <option value="8">8 words</option>
                            </select>
                            <p class="description">Uses the same AI settings and prompts as individual image renaming.</p>
                        </div>
                        
                        <div class="ai-context-options">
                            <h5>Context Information (same as single rename):</h5>
                            <label>
                                <input type="checkbox" id="include-post-title" checked>
                                Include post title in AI context
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" id="include-category" checked>
                                Include category in AI context
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" id="include-alt-text" checked>
                                Include existing alt text in AI context
                            </label>
                        </div>
                    </div>

                    <div class="config-option">
                        <h4>Processing Speed</h4>
                        <select id="bulk-processing-speed" class="regular-text">
                            <option value="safe">Safe (2 parallel requests, 2s intervals)</option>
                            <option value="normal" selected>Normal (4 parallel requests, 1s intervals)</option>
                            <option value="fast">Fast (6 parallel requests, 0.7s intervals)</option>
                            <option value="insane">⚠️ Insane (10 parallel requests, 0.4s intervals)</option>
                        </select>
                        <p class="description">Higher speeds process faster but require more server resources.</p>
                    </div>
                </div>

                <!-- Options -->
                <div class="imgseo-bulk-options">
                    <h4>Options</h4>
                    <label>
                        <input type="checkbox" id="lowercase-filenames" value="1" checked>
                        Convert filenames to lowercase
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" id="remove-accents" value="1" checked>
                        Remove accents and special characters
                    </label>
                </div>
            </div>

            <!-- Selection Section -->
            <div class="imgseo-selection-section">
                <h3>Image Selection</h3>
                <div class="selection-controls">
                    <button type="button" id="imgseo-select-all-images" class="button button-primary">
                        <span class="dashicons dashicons-admin-media"></span> Select All Images in Library
                    </button>
                    <button type="button" id="imgseo-select-custom-images" class="button">
                        <span class="dashicons dashicons-format-gallery"></span> Choose Specific Images
                    </button>
                    <button type="button" id="imgseo-filter-images" class="button">
                        <span class="dashicons dashicons-filter"></span> Filter by Criteria
                    </button>
                </div>

                <!-- Selected Images Display -->
                <div id="selected-images-container" class="hidden">
                    <div class="selection-summary">
                        <h4>Selected: <span id="selected-count">0</span> images</h4>
                        <button type="button" id="clear-selection" class="button">Clear Selection</button>
                    </div>
                    <div id="selected-images-list" class="selected-images-grid"></div>
                </div>
            </div>

            <!-- Preview Section -->
            <div id="preview-section" class="imgseo-preview-section hidden">
                <h3>Preview Changes</h3>
                <p class="description">Review the proposed changes before proceeding. This is your last chance to verify everything is correct.</p>
                
                <div class="execution-controls">
                    <button type="button" id="start-bulk-rename" class="button button-primary" style="background: #0073aa; border-color: #0073aa;" disabled>
                        <span class="dashicons dashicons-update"></span> RENAME SELECTED IMAGES
                    </button>
                    <button type="button" id="stop-bulk-rename" class="button hidden">
                        <span class="dashicons dashicons-no"></span> STOP PROCESS
                    </button>
                </div>
                
                <div id="rename-results" class="rename-results hidden">
                    <div class="results-table-container">
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Original Filename</th>
                                    <th>New Filename</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="results-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <!-- Progress Section -->
            <div id="progress-section" class="imgseo-progress-section hidden">
                <h3>Processing Progress</h3>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div id="progress-bar-fill" class="progress-bar-fill" style="width: 0%;"></div>
                    </div>
                    <div id="progress-text" class="progress-text">Ready to start...</div>
                    <div id="progress-details" class="progress-details">
                        <span class="detail">Active Requests: <strong id="active-requests">0</strong></span>
                        <span class="detail">Completed: <strong id="completed-count">0</strong></span>
                        <span class="detail">Errors: <strong id="error-count">0</strong></span>
                        <span class="detail">Estimated Time: <strong id="estimated-time">-</strong></span>
                    </div>
                </div>
                
                <!-- Real-time Logs -->
                <div class="processing-logs-container">
                    <h4>Real-time Processing Log</h4>
                    <div id="processing-logs" class="processing-logs"></div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="results-section" class="imgseo-results-section hidden">
                <h3>Processing Complete</h3>
                <div id="final-results" class="final-results"></div>
                <div class="post-process-actions">
                    <button type="button" id="download-report" class="button">
                        <span class="dashicons dashicons-download"></span> Download Report
                    </button>
                </div>
            </div>
        </div>

        <!-- CSS Styles -->
        <style>
            .imgseo-bulk-renamer-container {
                max-width: 1200px;
                margin: 0;
            }

            .imgseo-bulk-renamer-header {
                margin-bottom: 30px;
            }

            .imgseo-warning-banner {
                background: #fef1f1;
                border: 2px solid #d63638;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
            }

            .warning-content {
                display: flex;
                align-items: flex-start;
            }

            .warning-content p {
                margin: 5px 0 0 0;
                color: #721c24;
                font-size: 14px;
            }

            .imgseo-bulk-config-section,
            .imgseo-selection-section,
            .imgseo-preview-section,
            .imgseo-execution-section,
            .imgseo-progress-section,
            .imgseo-results-section {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }

            .imgseo-config-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 20px;
            }

            .config-option label {
                display: block;
                margin-bottom: 8px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: all 0.2s;
            }

            .config-option label:hover {
                background: #f8f9fa;
                border-color: #007cba;
            }

            .config-option input[type="radio"]:checked + strong {
                color: #007cba;
            }

            .imgseo-pattern-config {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                margin-top: 15px;
            }

            .selection-controls {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .selected-images-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }

            .selected-image-item {
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 10px;
                text-align: center;
                background: #fff;
                position: relative;
            }

            .selected-image-item img {
                max-width: 100%;
                height: 80px;
                object-fit: cover;
                border-radius: 4px;
            }

            .selected-image-item .filename {
                font-size: 11px;
                margin-top: 5px;
                word-break: break-all;
                color: #666;
            }

            .remove-selected {
                position: absolute;
                top: 5px;
                right: 5px;
                background: #d63638;
                color: white;
                border: none;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                cursor: pointer;
                font-size: 12px;
                line-height: 1;
            }

            .preview-controls {
                display: flex;
                align-items: center;
                gap: 20px;
                margin-bottom: 20px;
            }

            .preview-stats {
                display: flex;
                gap: 20px;
            }

            .preview-stats .stat {
                padding: 5px 10px;
                background: #f8f9fa;
                border-radius: 4px;
                font-size: 13px;
            }

            .preview-table-container {
                max-height: 400px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .danger-confirmation {
                background: #fef1f1;
                border: 2px solid #d63638;
                border-radius: 8px;
                padding: 20px;
            }

            .confirmation-checklist {
                margin: 15px 0;
            }

            .confirmation-item {
                display: block;
                padding: 8px 0;
                font-weight: 500;
            }

            .confirmation-item input[type="checkbox"] {
                margin-right: 8px;
            }

            .execution-controls {
                margin-top: 20px;
            }

            .progress-container {
                margin-bottom: 20px;
            }

            .progress-bar {
                width: 100%;
                height: 20px;
                background: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
                margin-bottom: 10px;
            }

            .progress-bar-fill {
                height: 100%;
                background: linear-gradient(90deg, #007cba, #005a87);
                transition: width 0.3s ease;
                border-radius: 10px;
            }

            .progress-text {
                font-weight: 600;
                margin-bottom: 10px;
            }

            .progress-details {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }

            .progress-details .detail {
                padding: 5px 10px;
                background: #f8f9fa;
                border-radius: 4px;
                font-size: 13px;
            }

            .processing-logs-container {
                margin-top: 20px;
            }

            .processing-logs {
                max-height: 300px;
                overflow-y: auto;
                background: #1e1e1e;
                color: #fff;
                padding: 15px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.4;
            }

            .log-entry {
                margin-bottom: 5px;
                padding: 2px 0;
            }

            .log-entry.log-success {
                color: #4ade80;
            }

            .log-entry.log-error {
                color: #f87171;
            }

            .log-entry.log-warning {
                color: #fbbf24;
            }

            .log-entry.log-info {
                color: #60a5fa;
            }

            .log-time {
                color: #9ca3af;
                margin-right: 8px;
            }

            .post-process-actions {
                margin-top: 20px;
                display: flex;
                gap: 10px;
            }

            .hidden {
                display: none !important;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .imgseo-config-row {
                    grid-template-columns: 1fr;
                }
                
                .selection-controls {
                    flex-direction: column;
                }
                
                .progress-details {
                    flex-direction: column;
                    gap: 10px;
                }
            }
        </style>

        <!-- JavaScript -->
        <script>
            jQuery(document).ready(function($) {
                // Global variables
                var selectedImages = [];
                var isProcessing = false;
                var processingStats = {
                    total: 0,
                    completed: 0,
                    errors: 0,
                    activeRequests: 0
                };

                // Initialize
                initializeBulkRenamer();

                function initializeBulkRenamer() {
                    bindEvents();
                    // updateConfirmationButton(); // Function not defined - commented out to prevent error
                }

                function bindEvents() {
                    // AI configuration handling
                    $('#ai-max-words').on('change', function() {
                        var words = $(this).val();
                        console.log('AI max words changed to:', words);
                    });

                    // Image selection buttons
                    $('#imgseo-select-all-images').on('click', handleSelectAllImages);
                    $('#imgseo-select-custom-images').on('click', handleSelectCustomImages);
                    $('#imgseo-filter-images').on('click', handleFilterImages);
                    $('#clear-selection').on('click', clearSelection);

                    // Enable/disable rename button based on selection
                    $('.image-checkbox').on('change', updateRenameButton);

                    // Process control
                    $('#start-bulk-rename').on('click', startBulkRename);
                    $('#stop-bulk-rename').on('click', stopBulkRename);

                    // Post-process actions
                    $('#download-report').on('click', downloadReport);
                }

                function handleSelectAllImages() {
                    if (confirm('This will select ALL images in your media library. This could be hundreds or thousands of files. Are you sure?')) {
                        showLoadingMessage('Loading all images from media library...');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'imgseo_get_all_images',
                                security: '<?php echo esc_js(wp_create_nonce('imgseo_bulk_renamer_nonce')); ?>'
                            },
                            success: function(response) {
                                hideLoadingMessage();
                                if (response.success) {
                                    selectedImages = response.data.images;
                                    updateSelectedImagesDisplay();
                                    showSection('preview-section');
                                } else {
                                    showError(response.data.message || 'Failed to load images');
                                }
                            },
                            error: function() {
                                hideLoadingMessage();
                                showError('Server error. Please try again.');
                            }
                        });
                    }
                }

                function handleSelectCustomImages() {
                    // Open WordPress media library
                    var mediaUploader = wp.media({
                        title: 'Select Images for Bulk Rename',
                        button: { text: 'Select Images' },
                        multiple: true,
                        library: { type: 'image' }
                    });

                    mediaUploader.on('select', function() {
                        var selection = mediaUploader.state().get('selection');
                        selectedImages = [];

                        selection.each(function(attachment) {
                            var data = attachment.toJSON();
                            selectedImages.push({
                                id: data.id,
                                url: data.url,
                                filename: data.filename,
                                title: data.title,
                                alt: data.alt || '',
                                thumbnail: data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url
                            });
                        });

                        updateSelectedImagesDisplay();
                        showSection('preview-section');
                    });

                    mediaUploader.open();
                }

                function handleFilterImages() {
                    // TODO: Implement advanced filtering
                    alert('Advanced filtering will be implemented in the next update. For now, please use "Choose Specific Images" option.');
                }

                function updateSelectedImagesDisplay() {
                    $('#selected-count').text(selectedImages.length);
                    
                    if (selectedImages.length === 0) {
                        $('#selected-images-container').addClass('hidden');
                        hideSection('preview-section');
                        hideSection('execution-section');
                        return;
                    }

                    $('#selected-images-container').removeClass('hidden');
                    
                    var html = '';
                    selectedImages.forEach(function(image, index) {
                        html += '<div class="selected-image-item" data-index="' + index + '">';
                        html += '<button class="remove-selected" onclick="removeSelectedImage(' + index + ')">×</button>';
                        html += '<img src="' + image.thumbnail + '" alt="' + image.filename + '">';
                        html += '<div class="filename">' + image.filename + '</div>';
                        html += '</div>';
                    });
                    
                    $('#selected-images-list').html(html);
                    
                    // Update rename button state
                    updateRenameButton();
                }

                window.removeSelectedImage = function(index) {
                    selectedImages.splice(index, 1);
                    updateSelectedImagesDisplay();
                };

                function clearSelection() {
                    selectedImages = [];
                    updateSelectedImagesDisplay();
                }



                function displayRenameResults(result, image) {
                    var statusClass = result.success ? 'success' : 'error';
                    var statusMessage = result.success ? 'Renamed successfully' : (result.message || 'Failed to rename');
                    
                    var html = '<tr class="' + statusClass + '">';
                    html += '<td><img src="' + image.thumbnail + '" style="width:40px;height:40px;object-fit:cover;"></td>';
                    html += '<td>' + image.filename + '</td>';
                    html += '<td>' + (result.success ? result.new_filename : '-') + '</td>';
                    html += '<td>' + statusMessage + '</td>';
                    html += '</tr>';
                    
                    $('#results-table-body').append(html);
                    $('#rename-results').removeClass('hidden');
                }

                function updateRenameButton() {
                    var hasSelectedImages = selectedImages && selectedImages.length > 0;
                    $('#start-bulk-rename').prop('disabled', !hasSelectedImages);
                }

                function startBulkRename() {
                    if (!selectedImages || selectedImages.length === 0) {
                        showError('No images selected for renaming.');
                        return;
                    }

                    isProcessing = true;
                    processingStats = { total: selectedImages.length, completed: 0, errors: 0, activeRequests: 0 };
                    
                    // Clear previous results and show results table
                    $('#results-table-body').empty();
                    $('#rename-results').removeClass('hidden');
                    
                    // Update UI
                    $('#start-bulk-rename').addClass('hidden');
                    $('#stop-bulk-rename').removeClass('hidden');
                    showSection('progress-section');
                    
                    // Start processing
                    initializeParallelProcessing();
                }

                function stopBulkRename() {
                    if (confirm('Are you sure you want to stop the bulk rename process?')) {
                        isProcessing = false;
                        $('#stop-bulk-rename').addClass('hidden');
                        $('#start-bulk-rename').removeClass('hidden');
                        addLog('Stopping bulk rename process...', 'warning');
                        
                        // Send stop request to server if we have a job ID
                        if (window.currentJobId) {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'imgseo_stop_bulk_rename',
                                    job_id: window.currentJobId,
                                    security: '<?php echo esc_js(wp_create_nonce('imgseo_bulk_renamer_nonce')); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        addLog('Job stopped successfully on server', 'info');
                                    } else {
                                        addLog('Warning: Failed to stop job on server', 'warning');
                                    }
                                },
                                error: function() {
                                    addLog('Warning: Could not communicate stop to server', 'warning');
                                }
                            });
                        }
                        
                        addLog('Process stopped by user', 'warning');
                    }
                }

                function initializeParallelProcessing() {
                    addLog('Starting bulk rename process...', 'info');
                    addLog('Processing ' + selectedImages.length + ' images in parallel', 'info');
                    
                    // Get processing speed configuration
                    var processingSpeed = $('#bulk-processing-speed').val() || 'normal';
                    var processingDelay;
                    var maxConcurrentRequests;
                    
                    // Configure parallel processing based on speed
                    switch(processingSpeed) {
                        case 'safe':
                            maxConcurrentRequests = 2;
                            processingDelay = 2000; // 2 seconds
                            break;
                        case 'normal':
                            maxConcurrentRequests = 4;
                            processingDelay = 1000; // 1 second
                            break;
                        case 'fast':
                            maxConcurrentRequests = 6;
                            processingDelay = 700; // 0.7 seconds
                            break;
                        case 'insane':
                            maxConcurrentRequests = 10;
                            processingDelay = 400; // 0.4 seconds
                            break;
                        default:
                            maxConcurrentRequests = 4;
                            processingDelay = 1000;
                    }
                    
                    addLog('Parallel configuration: ' + maxConcurrentRequests + ' concurrent, ' + (processingDelay/1000) + 's intervals', 'info');
                    
                    // Initialize processing variables
                    var currentImageIndex = 0;
                    var activeRequests = 0;
                    var jobId = null;
                    
                    // First, start the bulk rename job
                    startBulkRenameJob(function(response) {
                        if (response.success) {
                            jobId = response.data.job_id;
                            window.currentJobId = jobId; // Store globally for stop function
                            addLog('Job started with ID: ' + jobId, 'info');
                            
                            // Start parallel processing
                            startParallelRename();
                        } else {
                            addLog('Failed to start job: ' + response.data.message, 'error');
                            completeProcessing();
                        }
                    });
                    
                    function startParallelRename() {
                        // Start initial concurrent requests
                        for (var i = 0; i < Math.min(maxConcurrentRequests, selectedImages.length); i++) {
                            setTimeout(function() {
                                processNextImage();
                            }, i * processingDelay);
                        }
                    }
                    
                    function processNextImage() {
                        if (!isProcessing) {
                            addLog('Processing stopped by user', 'warning');
                            return;
                        }
                        
                        if (currentImageIndex >= selectedImages.length) {
                            // No more images to process
                            if (activeRequests === 0) {
                                completeProcessing();
                            }
                            return;
                        }
                        
                        var image = selectedImages[currentImageIndex];
                        currentImageIndex++;
                        activeRequests++;
                        
                        processingStats.activeRequests = activeRequests;
                        updateProgressDisplay();
                        
                        addLog('Starting: ' + image.filename, 'info');
                        
                        // Process single image
                        processRenameRequest(image, jobId, function(success, result) {
                            activeRequests--;
                            processingStats.activeRequests = activeRequests;
                            
                            // Display result in table
                            displayRenameResults({success: success, new_filename: result.new_filename, message: result.message}, image);
                            
                            if (success) {
                                processingStats.completed++;
                                addLog('✓ Renamed: ' + result.old_filename + ' → ' + result.new_filename, 'success');
                            } else {
                                processingStats.errors++;
                                addLog('✗ Failed: ' + image.filename + ' - ' + result.message, 'error');
                            }
                            
                            updateProgressDisplay();
                            
                            // Start next image if available
                            if (currentImageIndex < selectedImages.length) {
                                setTimeout(processNextImage, processingDelay);
                            } else if (activeRequests === 0) {
                                completeProcessing();
                            }
                        });
                    }
                }
                
                function startBulkRenameJob(callback) {
                    var options = {
                        method: 'ai', // Only AI method supported

                        lowercase: $('#lowercase-filenames').is(':checked'),
                        removeAccents: $('#remove-accents').is(':checked'),
                        // AI-specific options
                        max_words: parseInt($('#ai-max-words').val()) || 4,
                        include_post_title: $('#include-post-title').is(':checked'),
                        include_category: $('#include-category').is(':checked'),
                        include_alt_text: $('#include-alt-text').is(':checked')
                    };
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_start_bulk_rename',
                            images: selectedImages.map(img => img.id),
                            options: options,
                            processing_speed: $('#bulk-processing-speed').val(),
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_bulk_renamer_nonce')); ?>'
                        },
                        success: callback,
                        error: function() {
                            callback({success: false, data: {message: 'Server error starting job'}});
                        }
                    });
                }
                
                function processRenameRequest(image, jobId, callback) {
                    var options = {
                        method: 'ai', // Only AI method supported
    
                        lowercase: $('#lowercase-filenames').is(':checked'),
                        removeAccents: $('#remove-accents').is(':checked'),
                        // AI-specific options
                        max_words: parseInt($('#ai-max-words').val()) || 4,
                        include_post_title: $('#include-post-title').is(':checked'),
                        include_category: $('#include-category').is(':checked'),
                        include_alt_text: $('#include-alt-text').is(':checked')
                    };
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'imgseo_bulk_rename_single',
                            image_id: image.id,
                            job_id: jobId,
                            options: options,
                            security: '<?php echo esc_js(wp_create_nonce('imgseo_bulk_renamer_nonce')); ?>'
                        },
                        timeout: 30000, // 30 second timeout
                        success: function(response) {
                            callback(response.success, response.data);
                        },
                        error: function(xhr, status, error) {
                            var errorMsg = 'Network error';
                            if (status === 'timeout') {
                                errorMsg = 'Request timeout';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Server error';
                            }
                            callback(false, {message: errorMsg});
                        }
                    });
                }

                function completeProcessing() {
                    isProcessing = false;
                    $('#stop-bulk-rename').addClass('hidden');
                    hideSection('progress-section');
                    showSection('results-section');
                    
                    var html = '<div class="final-summary">';
                    html += '<h4>Process Complete!</h4>';
                    html += '<p>Successfully processed ' + processingStats.completed + ' out of ' + processingStats.total + ' images.</p>';
                    html += '<p>Errors: ' + processingStats.errors + '</p>';
                    html += '</div>';
                    
                    $('#final-results').html(html);
                    addLog('Bulk rename process completed', 'success');
                }

                function updateProgressDisplay() {
                    var percent = Math.round((processingStats.completed / processingStats.total) * 100);
                    $('#progress-bar-fill').css('width', percent + '%');
                    $('#progress-text').text('Processing: ' + processingStats.completed + ' of ' + processingStats.total + ' (' + percent + '%)');
                    $('#completed-count').text(processingStats.completed);
                    $('#error-count').text(processingStats.errors);
                    $('#active-requests').text(processingStats.activeRequests);
                }

                function addLog(message, type) {
                    type = type || 'info';
                    var timestamp = new Date().toLocaleTimeString();
                    var html = '<div class="log-entry log-' + type + '">';
                    html += '<span class="log-time">' + timestamp + '</span>';
                    html += message;
                    html += '</div>';
                    
                    $('#processing-logs').append(html);
                    $('#processing-logs').scrollTop($('#processing-logs')[0].scrollHeight);
                }

                function showSection(sectionId) {
                    $('#' + sectionId).removeClass('hidden');
                }

                function hideSection(sectionId) {
                    $('#' + sectionId).addClass('hidden');
                }

                function showError(message) {
                    $('#imgseo-bulk-rename-error-message').text(message);
                    $('#imgseo-bulk-rename-error').removeClass('hidden');
                    $('#imgseo-bulk-rename-result').removeClass('hidden');
                    scrollToElement('#imgseo-bulk-rename-result');
                }

                function showSuccess(message) {
                    $('#imgseo-bulk-rename-success-message').text(message);
                    $('#imgseo-bulk-rename-success').removeClass('hidden');
                    $('#imgseo-bulk-rename-result').removeClass('hidden');
                    scrollToElement('#imgseo-bulk-rename-result');
                }

                function showLoadingMessage(message) {
                    // TODO: Implement loading overlay
                    console.log('Loading: ' + message);
                }

                function hideLoadingMessage() {
                    // TODO: Hide loading overlay
                }

                function scrollToElement(selector) {
                    $('html, body').animate({
                        scrollTop: $(selector).offset().top - 50
                    }, 500);
                }

                function downloadReport() {
                    // TODO: Implement report download
                    alert('Report download will be implemented in the next update.');
                }
            });
        </script>
        <?php
    }
                }
