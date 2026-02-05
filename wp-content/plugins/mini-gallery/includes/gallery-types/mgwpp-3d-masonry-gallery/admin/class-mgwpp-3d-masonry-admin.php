<?php

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_3D_Masonry_Admin
{

    public static function init()
    {
        add_action('mgwpp_edit_gallery_before_images', [self::class, 'render_settings'], 10, 1);
        add_action('mgwpp_save_gallery_data', [self::class, 'save_settings'], 10, 2);
    }

    public static function render_settings($gallery)
    {
        $type = get_post_meta($gallery->ID, 'gallery_type', true);

        // Only show for 3D Masonry Gallery
        if ($type !== '3d_masonry_gallery') {
            return;
        }

        $default_mode = get_post_meta($gallery->ID, 'mgwpp_3d_mode', true);
        if (!$default_mode) $default_mode = 'wall';

        $show_tabs = get_post_meta($gallery->ID, 'mgwpp_3d_show_tabs', true);
        // Default to '0' (false) if not set, but if it was never saved? 
        // We want default false. So empty string = false.

?>
        <div class="mgwpp-edit-section mgwpp-3d-masonry-settings">
            <h2><?php esc_html_e('3D Masonry Settings', 'mini-gallery'); ?></h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="mgwpp_3d_mode"><?php esc_html_e('Default View Mode', 'mini-gallery'); ?></label></th>
                    <td>
                        <select name="mgwpp_3d_mode" id="mgwpp_3d_mode">
                            <option value="wall" <?php selected($default_mode, 'wall'); ?>><?php esc_html_e('Wall (Vertical Tilt)', 'mini-gallery'); ?></option>
                            <option value="table" <?php selected($default_mode, 'table'); ?>><?php esc_html_e('Table (Floor Perspective)', 'mini-gallery'); ?></option>
                            <option value="tunnel" <?php selected($default_mode, 'tunnel'); ?>><?php esc_html_e('Tunnel (Immersive)', 'mini-gallery'); ?></option>
                            <option value="flat" <?php selected($default_mode, 'flat'); ?>><?php esc_html_e('Flat (2D Grid)', 'mini-gallery'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Select the initial view mode for the gallery.', 'mini-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mgwpp_3d_show_tabs"><?php esc_html_e('Show View Tabs', 'mini-gallery'); ?></label></th>
                    <td>
                        <input type="checkbox" name="mgwpp_3d_show_tabs" id="mgwpp_3d_show_tabs" value="1" <?php checked($show_tabs, '1'); ?>>
                        <label for="mgwpp_3d_show_tabs"><?php esc_html_e('Display the Wall/Table/Tunnel/Flat switcher tabs to visitors.', 'mini-gallery'); ?></label>
                    </td>
                </tr>
            </table>
        </div>
<?php
    }

    public static function save_settings($gallery_id, $data)
    {
        // Only save if these fields are present (checking nonce is handled by main saver)
        // But wait, if checkbox is unchecked, it won't be in $data.
        // We need to check if we are saving this gallery type.
        // Or checking if 'mgwpp_3d_mode' is passed is a good proxy that we are in this form.

        if (isset($data['mgwpp_3d_mode'])) {
            update_post_meta($gallery_id, 'mgwpp_3d_mode', sanitize_text_field($data['mgwpp_3d_mode']));

            $show_tabs = isset($data['mgwpp_3d_show_tabs']) ? '1' : '0';
            update_post_meta($gallery_id, 'mgwpp_3d_show_tabs', $show_tabs);
        }
    }
}

MGWPP_3D_Masonry_Admin::init();
