<?php

/**
 * Full Page Slider Admin Settings
 *
 * Adds settings panel for full page slider in the gallery editor
 *
 * @package Mini_Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Full_Page_Slider_Admin
{
    /**
     * Initialize admin hooks
     */
    public static function init()
    {
        add_action('mgwpp_edit_gallery_before_images', [self::class, 'render_settings'], 10, 1);
        add_action('mgwpp_save_gallery_data', [self::class, 'save_settings'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_color_picker']);
    }

    /**
     * Enqueue WordPress color picker
     */
    public static function enqueue_color_picker($hook)
    {
        if (strpos($hook, 'mgwpp-edit-gallery') === false) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    /**
     * Render full page slider settings in gallery editor
     *
     * @param WP_Post $gallery The gallery post object
     */
    public static function render_settings($gallery)
    {
        $type = get_post_meta($gallery->ID, 'gallery_type', true);

        // Only show for Full Page Slider
        if ($type !== 'full_page_slider') {
            return;
        }

        // Get existing settings
        $settings = get_post_meta($gallery->ID, '_mgwpp_fullpage_settings', true) ?: [];
        $cta_links = get_post_meta($gallery->ID, '_mgwpp_cta_links', true) ?: [];

        // Default values
        $primary_color = $settings['primary_color'] ?? '#07babe';
        $overlay_color = $settings['overlay_color'] ?? '#000000';
        $overlay_opacity = $settings['overlay_opacity'] ?? '40';
        $default_button_text = $settings['default_button_text'] ?? __('Explore Collection', 'mini-gallery');
        $default_button_link = $cta_links['primary'] ?? '';
        $show_arrows = isset($settings['show_arrows']) ? $settings['show_arrows'] : '1';
        $auto_play = isset($settings['auto_play']) ? $settings['auto_play'] : '1';
        $slide_duration = $settings['slide_duration'] ?? '6000';
        $transition_effect = $settings['transition_effect'] ?? 'fade';
?>
        <div class="mgwpp-edit-section mgwpp-fullpage-settings">
            <h2>
                <span class="dashicons dashicons-slides"></span>
                <?php esc_html_e('Full Page Slider Settings', 'mini-gallery'); ?>
            </h2>

            <table class="form-table" role="presentation">
                <!-- Color Settings -->
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_primary_color"><?php esc_html_e('Accent Color', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_fullpage_settings[primary_color]"
                            id="mgwpp_fullpage_primary_color"
                            value="<?php echo esc_attr($primary_color); ?>"
                            class="mgwpp-color-picker"
                            data-default-color="#07babe">
                        <p class="description"><?php esc_html_e('Color for buttons and highlights.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_overlay_color"><?php esc_html_e('Overlay Color', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_fullpage_settings[overlay_color]"
                            id="mgwpp_fullpage_overlay_color"
                            value="<?php echo esc_attr($overlay_color); ?>"
                            class="mgwpp-color-picker"
                            data-default-color="#000000">
                        <p class="description"><?php esc_html_e('Color of the image overlay.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_overlay_opacity"><?php esc_html_e('Overlay Opacity', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="range"
                            name="mgwpp_fullpage_settings[overlay_opacity]"
                            id="mgwpp_fullpage_overlay_opacity"
                            value="<?php echo esc_attr($overlay_opacity); ?>"
                            min="0" max="100" step="5">
                        <span id="mgwpp_opacity_value"><?php echo esc_html($overlay_opacity); ?>%</span>
                        <p class="description"><?php esc_html_e('Opacity of the dark overlay on images (0-100%).', 'mini-gallery'); ?></p>
                    </td>
                </tr>

                <!-- Button Settings -->
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_button_text"><?php esc_html_e('Default Button Text', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_fullpage_settings[default_button_text]"
                            id="mgwpp_fullpage_button_text"
                            value="<?php echo esc_attr($default_button_text); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('Explore Collection', 'mini-gallery'); ?>">
                        <p class="description"><?php esc_html_e('Default text for the CTA button. Can be overridden per-slide.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_button_link"><?php esc_html_e('Default Button Link', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="url"
                            name="mgwpp_cta_links[primary]"
                            id="mgwpp_fullpage_button_link"
                            value="<?php echo esc_url($default_button_link); ?>"
                            class="regular-text"
                            placeholder="https://example.com">
                        <p class="description"><?php esc_html_e('Default URL for buttons. Use Image Links section for per-image links.', 'mini-gallery'); ?></p>
                    </td>
                </tr>

                <!-- Transition Effect -->
                <tr>
                    <th scope="row">
                        <label for="mgwpp_fullpage_transition"><?php esc_html_e('Transition Effect', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <select name="mgwpp_fullpage_settings[transition_effect]" id="mgwpp_fullpage_transition">
                            <option value="fade" <?php selected($transition_effect, 'fade'); ?>><?php esc_html_e('Fade', 'mini-gallery'); ?></option>
                            <option value="slide" <?php selected($transition_effect, 'slide'); ?>><?php esc_html_e('Slide', 'mini-gallery'); ?></option>
                            <option value="zoom" <?php selected($transition_effect, 'zoom'); ?>><?php esc_html_e('Zoom', 'mini-gallery'); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Navigation -->
                <tr>
                    <th scope="row"><?php esc_html_e('Navigation', 'mini-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                name="mgwpp_fullpage_settings[show_arrows]"
                                value="1"
                                <?php checked($show_arrows, '1'); ?>>
                            <?php esc_html_e('Show navigation arrows', 'mini-gallery'); ?>
                        </label>
                    </td>
                </tr>

                <!-- Autoplay Settings -->
                <tr>
                    <th scope="row"><?php esc_html_e('Autoplay', 'mini-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                name="mgwpp_fullpage_settings[auto_play]"
                                id="mgwpp_fullpage_auto_play"
                                value="1"
                                <?php checked($auto_play, '1'); ?>>
                            <?php esc_html_e('Auto-advance slides', 'mini-gallery'); ?>
                        </label>
                        <br><br>
                        <label for="mgwpp_fullpage_duration">
                            <?php esc_html_e('Slide Duration (ms):', 'mini-gallery'); ?>
                        </label>
                        <input type="number"
                            name="mgwpp_fullpage_settings[slide_duration]"
                            id="mgwpp_fullpage_duration"
                            value="<?php echo esc_attr($slide_duration); ?>"
                            min="2000"
                            max="20000"
                            step="500"
                            class="small-text">
                        <p class="description"><?php esc_html_e('Time between slides (2000-20000ms).', 'mini-gallery'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('.mgwpp-color-picker').wpColorPicker();

                // Opacity slider value display
                $('#mgwpp_fullpage_overlay_opacity').on('input', function() {
                    $('#mgwpp_opacity_value').text($(this).val() + '%');
                });
            });
        </script>

        <style>
            .mgwpp-fullpage-settings {
                background: var(--mgwpp-section-bg, #fff);
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .mgwpp-fullpage-settings h2 {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-top: 0;
                color: #07babe;
            }

            .mgwpp-fullpage-settings .form-table th {
                padding: 15px 10px 15px 0;
                width: 200px;
            }

            .mgwpp-fullpage-settings .form-table td {
                padding: 15px 10px;
            }

            .mgwpp-fullpage-settings .description {
                color: #666;
                font-style: italic;
                margin-top: 5px;
            }

            #mgwpp_fullpage_overlay_opacity {
                width: 200px;
                vertical-align: middle;
            }

            #mgwpp_opacity_value {
                margin-left: 10px;
                font-weight: 600;
            }
        </style>
<?php
    }

    /**
     * Save full page slider settings
     *
     * @param int   $gallery_id Gallery post ID
     * @param array $data       Form data
     */
    public static function save_settings($gallery_id, $data)
    {
        // Check if fullpage settings are present
        if (!isset($data['mgwpp_fullpage_settings'])) {
            return;
        }

        $settings = [];
        $raw_settings = $data['mgwpp_fullpage_settings'];

        // Sanitize color values
        if (isset($raw_settings['primary_color'])) {
            $settings['primary_color'] = sanitize_hex_color($raw_settings['primary_color']) ?: '#07babe';
        }
        if (isset($raw_settings['overlay_color'])) {
            $settings['overlay_color'] = sanitize_hex_color($raw_settings['overlay_color']) ?: '#000000';
        }

        // Sanitize opacity (0-100)
        if (isset($raw_settings['overlay_opacity'])) {
            $opacity = absint($raw_settings['overlay_opacity']);
            $settings['overlay_opacity'] = max(0, min(100, $opacity));
        }

        // Sanitize text fields
        if (isset($raw_settings['default_button_text'])) {
            $settings['default_button_text'] = sanitize_text_field($raw_settings['default_button_text']);
        }

        // Sanitize select
        if (isset($raw_settings['transition_effect'])) {
            $allowed = ['fade', 'slide', 'zoom'];
            $settings['transition_effect'] = in_array($raw_settings['transition_effect'], $allowed, true)
                ? $raw_settings['transition_effect']
                : 'fade';
        }

        // Sanitize checkboxes
        $settings['show_arrows'] = isset($raw_settings['show_arrows']) ? '1' : '0';
        $settings['auto_play'] = isset($raw_settings['auto_play']) ? '1' : '0';

        // Sanitize numeric values
        if (isset($raw_settings['slide_duration'])) {
            $duration = absint($raw_settings['slide_duration']);
            $settings['slide_duration'] = max(2000, min(20000, $duration));
        }

        update_post_meta($gallery_id, '_mgwpp_fullpage_settings', $settings);

        // Save CTA links
        if (isset($data['mgwpp_cta_links']['primary'])) {
            $cta_links = get_post_meta($gallery_id, '_mgwpp_cta_links', true) ?: [];
            $cta_links['primary'] = esc_url_raw($data['mgwpp_cta_links']['primary']);
            update_post_meta($gallery_id, '_mgwpp_cta_links', $cta_links);
        }
    }
}

MGWPP_Full_Page_Slider_Admin::init();
