<?php

/**

 * Template for the Structured Data administration page.

 *

 * @package ImgSEO

 * @since   1.2.0

 */



// Exit if accessed directly

if (!defined('ABSPATH')) {

    exit;

}



// Verify user capability

if (!current_user_can('manage_options')) {

    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'imgseo-ai-alt-text-generator'));

}



// Handle form submission

if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'imgseo_structured_data_settings-options')) {

    // Settings are automatically saved by WordPress settings API

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'imgseo-ai-alt-text-generator') . '</p></div>';

}



// Get current settings

$imgseo_admin = ImgSEO_Structured_Data_Admin::get_instance();

$imgseo_settings = $imgseo_admin->get_settings();



// Get structured data statistics using the new accurate system

$imgseo_structured_data = ImgSEO_Structured_Data::get_instance();

$imgseo_stats = $imgseo_structured_data->get_detailed_stats();

$imgseo_quick_stats = $imgseo_structured_data->get_structured_data_stats();



// Get system status (with fallback if new system not available)

$imgseo_system_info = array(

    'system_version' => '2.0.0',

    'initialized' => false,

    'tables_exist' => false

);



if (class_exists('ImgSEO_System_Initializer')) {

    $imgseo_system_initializer = ImgSEO_System_Initializer::get_instance();

    $imgseo_system_info = $imgseo_system_initializer->get_debug_info();

}

?>



<div class="wrap imgseo-structured-data-wrap">

    <div class="imgseo-admin-container">

        <div class="imgseo-main-content">



            <!-- Settings Form -->

            <div class="imgseo-card">

                <h2><?php esc_html_e('JSON-LD Configuration', 'imgseo-ai-alt-text-generator'); ?></h2>



                <form method="post" action="options.php">

                    <?php

                    settings_fields('imgseo_structured_data_settings');

                    do_settings_sections('imgseo_structured_data_settings');
                    ?>

                    <input type="submit" name="submit" id="submit" class="button btn-custom-primary" value="<?php esc_attr_e('Save Settings', 'imgseo-ai-alt-text-generator'); ?>">

                </form>

            </div>



            <!-- System Status -->

            <div class="imgseo-card">

                <h2><?php esc_html_e('System Status', 'imgseo-ai-alt-text-generator'); ?></h2>



                <div class="imgseo-system-status-simple">

                    <?php

                    $imgseo_is_active = $imgseo_system_info['initialized'] ?? false;

                    $imgseo_is_scanned = isset($imgseo_stats['scan_info']['last_full_scan_formatted']) && !empty($imgseo_stats['scan_info']['last_full_scan_formatted']);

                    ?>



                    <?php if ($imgseo_is_active && $imgseo_is_scanned): ?>

                        <div class="imgseo-status-success">

                            <span class="dashicons dashicons-yes-alt"></span>

                            <?php esc_html_e('JSON-LD system is active and scanning your content', 'imgseo-ai-alt-text-generator'); ?>

                        </div>

                    <?php elseif ($imgseo_is_active && !$imgseo_is_scanned): ?>

                        <div class="imgseo-status-warning">

                            <span class="dashicons dashicons-clock"></span>

                            <?php esc_html_e('JSON-LD system is active, initial scan in progress', 'imgseo-ai-alt-text-generator'); ?>

                        </div>

                    <?php else: ?>

                        <div class="imgseo-status-error">

                            <span class="dashicons dashicons-warning"></span>

                            <?php esc_html_e('JSON-LD system needs attention', 'imgseo-ai-alt-text-generator'); ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>



        <div class="imgseo-sidebar">



            <!-- Status Card -->

            <div class="imgseo-card">

                <h3><?php esc_html_e('Status', 'imgseo-ai-alt-text-generator'); ?></h3>



                <div class="imgseo-status-item">

                    <span class="imgseo-status-label"><?php esc_html_e('Structured Data:', 'imgseo-ai-alt-text-generator'); ?></span>

                    <span class="imgseo-status-value <?php echo $imgseo_settings['enable_structured_data'] ? 'enabled' : 'disabled'; ?>">

                        <?php echo $imgseo_settings['enable_structured_data'] ? esc_html__('Enabled', 'imgseo-ai-alt-text-generator') : esc_html__('Disabled', 'imgseo-ai-alt-text-generator'); ?>

                    </span>

                </div>



                <div class="imgseo-status-item">

                    <span class="imgseo-status-label"><?php esc_html_e('Thumbnails:', 'imgseo-ai-alt-text-generator'); ?></span>

                    <span class="imgseo-status-value <?php echo $imgseo_settings['include_thumbnails'] ? 'enabled' : 'disabled'; ?>">

                        <?php echo $imgseo_settings['include_thumbnails'] ? esc_html__('Included', 'imgseo-ai-alt-text-generator') : esc_html__('Excluded', 'imgseo-ai-alt-text-generator'); ?>

                    </span>

                </div>



                <div class="imgseo-status-item">

                    <span class="imgseo-status-label"><?php esc_html_e('Author Info:', 'imgseo-ai-alt-text-generator'); ?></span>

                    <span class="imgseo-status-value <?php echo $imgseo_settings['include_author'] ? 'enabled' : 'disabled'; ?>">

                        <?php echo $imgseo_settings['include_author'] ? esc_html__('Included', 'imgseo-ai-alt-text-generator') : esc_html__('Excluded', 'imgseo-ai-alt-text-generator'); ?>

                    </span>

                </div>

            </div>



            <!-- Help Card -->

            <div class="imgseo-card">

                <h3><?php esc_html_e('About Structured Data', 'imgseo-ai-alt-text-generator'); ?></h3>



                <p><?php esc_html_e('JSON-LD structured data helps search engines understand your images better, potentially improving your search visibility.', 'imgseo-ai-alt-text-generator'); ?></p>



                <h4><?php esc_html_e('Benefits:', 'imgseo-ai-alt-text-generator'); ?></h4>

                <ul>

                    <li><?php esc_html_e('Better image search results', 'imgseo-ai-alt-text-generator'); ?></li>

                    <li><?php esc_html_e('Enhanced rich snippets', 'imgseo-ai-alt-text-generator'); ?></li>

                    <li><?php esc_html_e('Improved SEO performance', 'imgseo-ai-alt-text-generator'); ?></li>

                </ul>



                <h4><?php esc_html_e('Testing:', 'imgseo-ai-alt-text-generator'); ?></h4>

                <p>

                    <a href="https://search.google.com/test/rich-results" target="_blank" class="button btn-custom-secondary">

                        <?php esc_html_e('Test with Google', 'imgseo-ai-alt-text-generator'); ?>

                    </a>

                </p>

            </div>



        </div>

    </div>

</div>



<style>

.imgseo-admin-container {

    display: flex;

    gap: 20px;

    margin-top: 20px;

}



.imgseo-main-content {

    flex: 2;

}



.imgseo-sidebar {

    flex: 1;

    max-width: 300px;

}



.imgseo-card {

    background: #fff;

    border: 1px solid #ccd0d4;

    border-radius: 4px;

    padding: 20px;

    margin-bottom: 20px;

    box-shadow: 0 1px 1px rgba(0,0,0,.04);

}



.imgseo-card h2,

.imgseo-card h3 {

    margin-top: 0;

    margin-bottom: 15px;

    color: #23282d;

}



.imgseo-stats-grid {

    display: grid;

    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));

    gap: 15px;

    margin-bottom: 15px;

}



.imgseo-stat-item {

    text-align: center;

    padding: 15px;

    background: #f8f9fa;

    border-radius: 4px;

}



.imgseo-stat-number {

    font-size: 24px;

    font-weight: bold;

    color: #0073aa;

    margin-bottom: 5px;

}



.imgseo-stat-label {

    font-size: 11px;

    color: #666;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    line-height: 1.3;

}



.imgseo-content-type-stats {

    display: grid;

    gap: 10px;

}



.imgseo-content-type-item {

    padding: 12px;

    background: #f8f9fa;

    border-radius: 4px;

    border-left: 4px solid #0073aa;

}



.imgseo-content-type-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 8px;

}



.imgseo-content-type-header h4 {

    margin: 0;

    font-size: 14px;

    color: #23282d;

}



.imgseo-content-type-coverage {

    font-size: 12px;

    font-weight: 600;

    color: #0073aa;

}



.imgseo-content-type-details {

    display: flex;

    gap: 15px;

    font-size: 12px;

    color: #666;

}



.imgseo-system-status {

    margin-bottom: 20px;

}



.imgseo-status-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 8px 0;

    border-bottom: 1px solid #eee;

}



.imgseo-status-item:last-child {

    border-bottom: none;

}



.imgseo-status-label {

    font-weight: 500;

}



.imgseo-status-value {

    padding: 2px 8px;

    border-radius: 3px;

    font-size: 12px;

    font-weight: 500;

    text-transform: uppercase;

}



.imgseo-status-value.enabled {

    background: #d4edda;

    color: #155724;

}



.imgseo-status-value.disabled {

    background: #f8d7da;

    color: #721c24;

}



.imgseo-code-preview {

    background: #f8f9fa;

    border: 1px solid #e9ecef;

    border-radius: 4px;

    padding: 15px;

    overflow-x: auto;

    font-size: 13px;

    line-height: 1.4;

}



.imgseo-code-preview code {

    color: #495057;

}



@media (max-width: 782px) {

    .imgseo-admin-container {

        flex-direction: column;

    }



    .imgseo-sidebar {

        max-width: none;

    }



    .imgseo-stats-grid {

        grid-template-columns: 1fr;

    }

}

.imgseo-system-actions {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}



.imgseo-system-actions .button {

    flex: 1;

    min-width: 120px;

}



.imgseo-loading {

    opacity: 0.6;

    pointer-events: none;

}



.imgseo-notice {

    padding: 10px 15px;

    margin: 10px 0;

    border-radius: 4px;

    font-size: 14px;

}



.imgseo-notice.success {

    background: #d4edda;

    color: #155724;

    border: 1px solid #c3e6cb;

}



.imgseo-notice.error {

    background: #f8d7da;

    color: #721c24;

    border: 1px solid #f5c6cb;

}



@media (max-width: 782px) {

    .imgseo-admin-container {

        flex-direction: column;

    }



    .imgseo-sidebar {

        max-width: none;

    }



    .imgseo-stats-grid {

        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));

    }



    .imgseo-content-type-details {

        flex-direction: column;

        gap: 5px;

    }



    .imgseo-system-actions {

        flex-direction: column;

    }



    .imgseo-system-actions .button {

        flex: none;

    }

}

</style>



<script type="text/javascript">

jQuery(document).ready(function($) {

    // Force scan functionality

    $('#imgseo-force-scan').on('click', function() {

        var $button = $(this);

        var $card = $button.closest('.imgseo-card');



        $button.prop('disabled', true).text('<?php esc_html_e('Scanning...', 'imgseo-ai-alt-text-generator'); ?>');

        $card.addClass('imgseo-loading');



        // Remove any existing notices

        $('.imgseo-notice').remove();



        $.ajax({

            url: ajaxurl,

            type: 'POST',

            data: {

                action: 'imgseo_force_scan',

                nonce: '<?php echo esc_js(wp_create_nonce('imgseo_force_scan')); ?>'

            },

            success: function(response) {

                if (response.success) {

                    $card.before('<div class="imgseo-notice success">Scan completed successfully! Found ' + (response.stats ? response.stats.images_found : 0) + ' images.</div>');

                    // Reload page after 2 seconds to show new data

                    setTimeout(function() {

                        location.reload();

                    }, 2000);

                } else {

                    $card.before('<div class="imgseo-notice error">Error during scan: ' + (response.error || 'Unknown error') + '</div>');

                }

            },

            error: function() {

                $card.before('<div class="imgseo-notice error">Server communication error.</div>');

            },

            complete: function() {

                $button.prop('disabled', false).text('<?php esc_html_e('Force Full Scan', 'imgseo-ai-alt-text-generator'); ?>');

                $card.removeClass('imgseo-loading');

            }

        });

    });



    // Clear cache functionality

    $('#imgseo-clear-cache').on('click', function() {

        var $button = $(this);

        var $card = $button.closest('.imgseo-card');



        $button.prop('disabled', true).text('<?php esc_html_e('Clearing...', 'imgseo-ai-alt-text-generator'); ?>');



        // Remove any existing notices

        $('.imgseo-notice').remove();



        $.ajax({

            url: ajaxurl,

            type: 'POST',

            data: {

                action: 'imgseo_clear_cache',

                nonce: '<?php echo esc_js(wp_create_nonce('imgseo_clear_cache')); ?>'

            },

            success: function(response) {

                if (response.success) {

                    $card.before('<div class="imgseo-notice success">Cache cleared successfully!</div>');

                } else {

                    $card.before('<div class="imgseo-notice error">Error clearing cache.</div>');

                }

            },

            error: function() {

                $card.before('<div class="imgseo-notice error">Server communication error.</div>');

            },

            complete: function() {

                $button.prop('disabled', false).text('<?php esc_html_e('Clear Cache', 'imgseo-ai-alt-text-generator'); ?>');

            }

        });

    });



    // Auto-hide notices after 5 seconds

    $(document).on('DOMNodeInserted', '.imgseo-notice', function() {

        var $notice = $(this);

        setTimeout(function() {

            $notice.fadeOut();

        }, 5000);

    });

});

</script>
