<?php

/**
 * Spotlight Carousel Admin Settings
 *
 * Adds settings panel for spotlight carousel in the gallery editor
 *
 * @package Mini_Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Spotlight_Carousel_Admin
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
     * Render spotlight-specific settings in gallery editor
     *
     * @param WP_Post $gallery The gallery post object
     */
    public static function render_settings($gallery)
    {
        $type = get_post_meta($gallery->ID, 'gallery_type', true);

        // Only show for Spotlight Slider/Carousel
        if ($type !== 'spotlight_slider' && $type !== 'spotlight_carousel') {
            return;
        }

        // Get existing settings
        $settings = get_post_meta($gallery->ID, '_mgwpp_spotlight_settings', true) ?: [];
        $cta_links = get_post_meta($gallery->ID, '_mgwpp_cta_links', true) ?: [];

        // Default values
        $primary_color = $settings['primary_color'] ?? '#07babe';
        $secondary_color = $settings['secondary_color'] ?? '#05a0a8';
        $default_button_text = $settings['default_button_text'] ?? __('Discover More', 'mini-gallery');
        $default_button_link = $cta_links['primary'] ?? '';
        $show_arrows = isset($settings['show_arrows']) ? $settings['show_arrows'] : '1';
        $show_nav_dots = isset($settings['show_nav_dots']) ? $settings['show_nav_dots'] : '1';
        $auto_play = isset($settings['auto_play']) ? $settings['auto_play'] : '1';
        $slide_duration = $settings['slide_duration'] ?? '8000';
?>
        <div class="mgwpp-edit-section mgwpp-spotlight-settings">
            <h2>
                <span class="dashicons dashicons-slides"></span>
                <?php esc_html_e('Spotlight Carousel Settings', 'mini-gallery'); ?>
            </h2>

            <table class="form-table" role="presentation">
                <!-- Color Settings -->
                <tr>
                    <th scope="row">
                        <label for="mgwpp_spotlight_primary_color"><?php esc_html_e('Primary Color', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_spotlight_settings[primary_color]"
                            id="mgwpp_spotlight_primary_color"
                            value="<?php echo esc_attr($primary_color); ?>"
                            class="mgwpp-color-picker"
                            data-default-color="#07babe">
                        <p class="description"><?php esc_html_e('Main color for buttons, highlights, and accents.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mgwpp_spotlight_secondary_color"><?php esc_html_e('Secondary Color', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_spotlight_settings[secondary_color]"
                            id="mgwpp_spotlight_secondary_color"
                            value="<?php echo esc_attr($secondary_color); ?>"
                            class="mgwpp-color-picker"
                            data-default-color="#05a0a8">
                        <p class="description"><?php esc_html_e('Secondary color used for gradients and hover effects.', 'mini-gallery'); ?></p>
                    </td>
                </tr>

                <!-- Button Settings -->
                <tr>
                    <th scope="row">
                        <label for="mgwpp_spotlight_button_text"><?php esc_html_e('Default Button Text', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                            name="mgwpp_spotlight_settings[default_button_text]"
                            id="mgwpp_spotlight_button_text"
                            value="<?php echo esc_attr($default_button_text); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('Discover More', 'mini-gallery'); ?>">
                        <p class="description"><?php esc_html_e('Default text for the CTA button. Can be overridden per-slide.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mgwpp_spotlight_button_link"><?php esc_html_e('Default Button Link', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="url"
                            name="mgwpp_cta_links[primary]"
                            id="mgwpp_spotlight_button_link"
                            value="<?php echo esc_url($default_button_link); ?>"
                            class="regular-text"
                            placeholder="https://example.com">
                        <p class="description"><?php esc_html_e('Default URL for buttons. Use the Image Links section below for per-image links.', 'mini-gallery'); ?></p>
                    </td>
                </tr>

                <!-- Display Options -->
                <tr>
                    <th scope="row"><?php esc_html_e('Navigation', 'mini-gallery'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                    name="mgwpp_spotlight_settings[show_arrows]"
                                    value="1"
                                    <?php checked($show_arrows, '1'); ?>>
                                <?php esc_html_e('Show navigation arrows', 'mini-gallery'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                    name="mgwpp_spotlight_settings[show_nav_dots]"
                                    value="1"
                                    <?php checked($show_nav_dots, '1'); ?>>
                                <?php esc_html_e('Show navigation dots', 'mini-gallery'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <!-- Autoplay Settings -->
                <tr>
                    <th scope="row"><?php esc_html_e('Autoplay', 'mini-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                name="mgwpp_spotlight_settings[auto_play]"
                                id="mgwpp_spotlight_auto_play"
                                value="1"
                                <?php checked($auto_play, '1'); ?>>
                            <?php esc_html_e('Auto-advance slides', 'mini-gallery'); ?>
                        </label>
                        <br><br>
                        <label for="mgwpp_spotlight_duration">
                            <?php esc_html_e('Slide Duration (ms):', 'mini-gallery'); ?>
                        </label>
                        <input type="number"
                            name="mgwpp_spotlight_settings[slide_duration]"
                            id="mgwpp_spotlight_duration"
                            value="<?php echo esc_attr($slide_duration); ?>"
                            min="2000"
                            max="20000"
                            step="500"
                            class="small-text">
                        <p class="description"><?php esc_html_e('Time in milliseconds between auto-advancing slides (2000-20000).', 'mini-gallery'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('.mgwpp-color-picker').wpColorPicker();
            });
        </script>

        <style>
            .mgwpp-spotlight-settings {
                background: var(--mgwpp-section-bg, #fff);
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .mgwpp-spotlight-settings h2 {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-top: 0;
                color: #07babe;
            }

            .mgwpp-spotlight-settings .form-table th {
                padding: 15px 10px 15px 0;
                width: 200px;
            }

            .mgwpp-spotlight-settings .form-table td {
                padding: 15px 10px;
            }

            .mgwpp-spotlight-settings .description {
                color: #666;
                font-style: italic;
                margin-top: 5px;
            }
        </style>
<?php
    }

    /**
     * Save spotlight settings
     *
     * @param int   $gallery_id Gallery post ID
     * @param array $data       Form data
     */
    public static function save_settings($gallery_id, $data)
    {
        // Check if spotlight settings are present
        if (!isset($data['mgwpp_spotlight_settings'])) {
            return;
        }

        $settings = [];
        $raw_settings = $data['mgwpp_spotlight_settings'];

        // Sanitize color values
        if (isset($raw_settings['primary_color'])) {
            $settings['primary_color'] = sanitize_hex_color($raw_settings['primary_color']) ?: '#07babe';
        }
        if (isset($raw_settings['secondary_color'])) {
            $settings['secondary_color'] = sanitize_hex_color($raw_settings['secondary_color']) ?: '#05a0a8';
        }

        // Sanitize text fields
        if (isset($raw_settings['default_button_text'])) {
            $settings['default_button_text'] = sanitize_text_field($raw_settings['default_button_text']);
        }

        // Sanitize checkboxes (will be '1' if checked, otherwise not set)
        $settings['show_arrows'] = isset($raw_settings['show_arrows']) ? '1' : '0';
        $settings['show_nav_dots'] = isset($raw_settings['show_nav_dots']) ? '1' : '0';
        $settings['auto_play'] = isset($raw_settings['auto_play']) ? '1' : '0';

        // Sanitize numeric values
        if (isset($raw_settings['slide_duration'])) {
            $duration = absint($raw_settings['slide_duration']);
            $settings['slide_duration'] = max(2000, min(20000, $duration));
        }

        update_post_meta($gallery_id, '_mgwpp_spotlight_settings', $settings);

        // Save CTA links (handled separately but we need to ensure it's saved)
        if (isset($data['mgwpp_cta_links']['primary'])) {
            $cta_links = get_post_meta($gallery_id, '_mgwpp_cta_links', true) ?: [];
            $cta_links['primary'] = esc_url_raw($data['mgwpp_cta_links']['primary']);
            update_post_meta($gallery_id, '_mgwpp_cta_links', $cta_links);
        }
    }
}

MGWPP_Spotlight_Carousel_Admin::init();
