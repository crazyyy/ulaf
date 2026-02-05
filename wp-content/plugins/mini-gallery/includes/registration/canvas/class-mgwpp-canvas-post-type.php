<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canvas Post Type Registration
 * 
 * Registers the mgwpp_canvas post type for the visual drag-and-drop editor.
 * Separate from the existing mgwpp_soora gallery post type.
 */
class MGWPP_Canvas_Post_Type
{
    /**
     * Register the canvas post type
     */
    public static function register()
    {
        $labels = [
            'name'               => _x('Canvas Galleries', 'post type general name', 'mini-gallery'),
            'singular_name'      => _x('Canvas Gallery', 'post type singular name', 'mini-gallery'),
            'menu_name'          => _x('Canvas Editor', 'admin menu', 'mini-gallery'),
            'add_new'            => _x('Add New', 'canvas gallery', 'mini-gallery'),
            'add_new_item'       => __('Add New Canvas Gallery', 'mini-gallery'),
            'edit_item'          => __('Edit Canvas Gallery', 'mini-gallery'),
            'new_item'           => __('New Canvas Gallery', 'mini-gallery'),
            'view_item'          => __('View Canvas Gallery', 'mini-gallery'),
            'search_items'       => __('Search Canvas Galleries', 'mini-gallery'),
            'not_found'          => __('No canvas galleries found', 'mini-gallery'),
            'not_found_in_trash' => __('No canvas galleries found in Trash', 'mini-gallery'),
            'all_items'          => __('All Canvas Galleries', 'mini-gallery'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => false, // We use custom admin pages
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'mgwpp_canvas',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => ['title'],
        ];

        register_post_type('mgwpp_canvas', $args);
    }

    /**
     * Get canvas data from post meta
     * 
     * @param int $canvas_id Canvas post ID
     * @return array Canvas data or empty array
     */
    public static function get_canvas_data($canvas_id)
    {
        $canvas_id = absint($canvas_id);
        
        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            return [];
        }

        $data = get_post_meta($canvas_id, '_mgwpp_canvas_data', true);
        
        return is_array($data) ? $data : [];
    }

    /**
     * Save canvas data to post meta
     * 
     * @param int   $canvas_id Canvas post ID
     * @param array $data      Canvas data to save
     * @return bool Success status
     */
    public static function save_canvas_data($canvas_id, $data)
    {
        $canvas_id = absint($canvas_id);
        
        if (!$canvas_id || get_post_type($canvas_id) !== 'mgwpp_canvas') {
            return false;
        }

        // Sanitize canvas data
        $sanitized_data = self::sanitize_canvas_data($data);
        
        return update_post_meta($canvas_id, '_mgwpp_canvas_data', $sanitized_data);
    }

    /**
     * Sanitize canvas data recursively
     * 
     * @param array $data Raw canvas data
     * @return array Sanitized data
     */
    private static function sanitize_canvas_data($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $sanitized = [];

        // Sanitize canvas settings
        if (isset($data['canvas_settings'])) {
            $sanitized['canvas_settings'] = [
                'width'      => absint($data['canvas_settings']['width'] ?? 1200),
                'height'     => absint($data['canvas_settings']['height'] ?? 800),
                'background' => sanitize_hex_color($data['canvas_settings']['background'] ?? '#ffffff'),
            ];
        }

        // Sanitize slider settings
        if (isset($data['slider_settings'])) {
            $sanitized['slider_settings'] = [
                'autoplay'      => filter_var($data['slider_settings']['autoplay'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'autoplaySpeed' => absint($data['slider_settings']['autoplaySpeed'] ?? 3000),
                'effect'        => sanitize_key($data['slider_settings']['effect'] ?? 'slide'),
                'arrows'        => filter_var($data['slider_settings']['arrows'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'dots'          => filter_var($data['slider_settings']['dots'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        // Sanitize slides (New Structure)
        if (isset($data['slides']) && is_array($data['slides'])) {
            $sanitized['slides'] = [];
            
            foreach ($data['slides'] as $slide) {
                if (!is_array($slide)) continue;
                
                $sanitized_slide = [
                    'id' => sanitize_key($slide['id'] ?? 'slide_' . uniqid()),
                    'items' => []
                ];

                if (isset($slide['items']) && is_array($slide['items'])) {
                    foreach ($slide['items'] as $item) {
                        $sanitized_item = self::sanitize_canvas_item($item);
                        if ($sanitized_item) {
                            $sanitized_slide['items'][] = $sanitized_item;
                        }
                    }
                }
                
                $sanitized['slides'][] = $sanitized_slide;
            }
        }
        // Legacy support (convert items to slide if no slides exist)
        elseif (isset($data['items']) && is_array($data['items'])) {
             $sanitized['slides'] = [];
             $legacy_slide = [
                 'id' => 'slide_' . uniqid(),
                 'items' => []
             ];
             
             foreach ($data['items'] as $item) {
                $sanitized_item = self::sanitize_canvas_item($item);
                if ($sanitized_item) {
                    $legacy_slide['items'][] = $sanitized_item;
                }
            }
            $sanitized['slides'][] = $legacy_slide;
        }

        return $sanitized;
    }

    /**
     * Sanitize individual canvas item
     * 
     * @param array $item Raw item data
     * @return array|null Sanitized item or null if invalid
     */
    private static function sanitize_canvas_item($item)
    {
        if (!is_array($item) || empty($item['type'])) {
            return null;
        }

        $allowed_types = ['image', 'text', 'button', 'shape', 'container'];
        $type = sanitize_key($item['type']);
        
        if (!in_array($type, $allowed_types, true)) {
            return null;
        }

        // Common properties
        // Note: Dimensions can now be strings (e.g. "50%", "auto", "10px") so we accept strings but clean them.
        $sanitized = [
            'id'       => sanitize_key($item['id'] ?? 'item_' . uniqid()),
            'type'     => $type,
            'x'        => sanitize_text_field($item['x'] ?? '0'),
            'y'        => sanitize_text_field($item['y'] ?? '0'),
            'width'    => sanitize_text_field($item['width'] ?? '100'),
            'height'   => sanitize_text_field($item['height'] ?? '100'),
            'rotation' => floatval($item['rotation'] ?? 0),
            'z_index'  => absint($item['z_index'] ?? 1),
            'opacity'  => max(0, min(1, floatval($item['opacity'] ?? 1))),
        ];

        // Type-specific properties
        switch ($type) {
            case 'image':
                $sanitized['image_id']  = absint($item['image_id'] ?? 0);
                $sanitized['image_url'] = esc_url_raw($item['image_url'] ?? '');
                $sanitized['alt_text']  = sanitize_text_field($item['alt_text'] ?? '');
                $sanitized['link']      = esc_url_raw($item['link'] ?? '');
                break;

            case 'text':
                $sanitized['content']    = wp_kses_post($item['content'] ?? '');
                $sanitized['font_size']  = sanitize_text_field($item['font_size'] ?? '16');
                $sanitized['font_family'] = sanitize_text_field($item['font_family'] ?? 'inherit');
                $sanitized['color']      = sanitize_hex_color($item['color'] ?? '#000000');
                $sanitized['text_align'] = in_array($item['text_align'] ?? '', ['left', 'center', 'right']) 
                    ? $item['text_align'] : 'left';
                break;

            case 'button':
                $sanitized['text']       = sanitize_text_field($item['text'] ?? 'Button');
                $sanitized['link']       = esc_url_raw($item['link'] ?? '#');
                $sanitized['bg_color']   = sanitize_hex_color($item['bg_color'] ?? '#0073aa');
                $sanitized['text_color'] = sanitize_hex_color($item['text_color'] ?? '#ffffff');
                $sanitized['border_radius'] = sanitize_text_field($item['border_radius'] ?? '4');
                break;

            case 'shape':
                $sanitized['shape_type'] = in_array($item['shape_type'] ?? '', ['rectangle', 'circle', 'triangle']) 
                    ? $item['shape_type'] : 'rectangle';
                $sanitized['fill_color']   = sanitize_hex_color($item['fill_color'] ?? '#cccccc');
                $sanitized['stroke_color'] = sanitize_hex_color($item['stroke_color'] ?? '#000000');
                $sanitized['stroke_width'] = sanitize_text_field($item['stroke_width'] ?? '0');
                break;
                
            case 'container':
                $sanitized['display']   = 'flex'; // Enforce flex
                $sanitized['direction'] = in_array($item['direction'] ?? '', ['row', 'column']) ? $item['direction'] : 'row';
                $sanitized['justify']   = in_array($item['justify'] ?? '', ['flex-start', 'center', 'flex-end', 'space-between', 'space-around']) ? $item['justify'] : 'flex-start';
                $sanitized['align']     = in_array($item['align'] ?? '', ['flex-start', 'center', 'flex-end', 'stretch']) ? $item['align'] : 'stretch';
                $sanitized['gap']       = sanitize_text_field($item['gap'] ?? '0');
                $sanitized['padding']   = sanitize_text_field($item['padding'] ?? '0');
                $sanitized['bg_color']  = sanitize_hex_color($item['bg_color'] ?? 'transparent');
                $sanitized['border_width'] = sanitize_text_field($item['border_width'] ?? '0');
                $sanitized['border_color'] = sanitize_hex_color($item['border_color'] ?? 'transparent');
                
                // Recursive Children
                $sanitized['children'] = [];
                if (!empty($item['children']) && is_array($item['children'])) {
                    foreach ($item['children'] as $child) {
                        $sanitized_child = self::sanitize_canvas_item($child);
                        if ($sanitized_child) {
                            $sanitized['children'][] = $sanitized_child;
                        }
                    }
                }
                break;
        }

        return $sanitized;
    }
}
