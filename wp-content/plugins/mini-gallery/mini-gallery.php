<?php
/*
Plugin Name: Mini Gallery
Plugin URI: https://wordpress.org/plugins/mini-gallery/
Description: A Fully Open Source WordPress Gallery, Slider and Carousel Alternative for Premium Plugin Sliders. Choose one of our 10 Default Ones, or create your own.
 * Version:           1.6.7
Author: AGWS | And Go Web Solutions
Author URI: https://andgowebsolutions.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mini-gallery
Domain Path: /languages

= 1.6.7 =
* Fixed album editing "link expired" error caused by incorrect edit URL format

= 1.6.6 =
* Added customizable settings panel for Spotlight Carousel (colors, buttons, links, navigation)
* Added customizable settings panel for Full Page Slider (colors, overlay, buttons, transitions)
* Fixed WordPress Plugin Check lint errors (translators comments, escaping)
* Removed unexpected markdown files from plugin root
* CSS optimization and teal theme consistency improvements

Contribute: https://github.com/omarashzeinhom/mini-gallery-dev/
Docs: https://minigallery.andgowebsolutions.com/docs/
*/

// ======================
// SECURITY & CONSTANTS
// ======================
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('MG_VERSION', '1.6.7');
define('MG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MG_PLUGIN_URL', plugins_url('', __FILE__));
define('MGWPP_ASSET_VERSION', filemtime(__FILE__));
define('MGWPP_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'includes/admin/images/');

// ======================
// CORE FUNCTIONALITY
// ======================
function mgwpp_get_plugin_version()
{
    return defined('MGWPP_ASSET_VERSION') ? MGWPP_ASSET_VERSION : '1.4.0';
}

/**
 * Check if a module is enabled in settings
 * 
 * @param string $module Module name: 'testimonials', '3d_galleries', or 'canvas'
 * @return bool Whether the module is enabled
 */
function mgwpp_is_module_enabled($module)
{
    // Get saved settings or defaults
    $settings = get_option('mgwpp_module_settings', [
        'testimonials' => true,
        '3d_galleries' => true,
        'canvas' => true,
    ]);

    return isset($settings[$module]) ? (bool) $settings[$module] : true;
}

// ======================
// FILE INCLUSIONS
// ======================
// Core functionality
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-module-manager.php';
require_once MG_PLUGIN_PATH . 'includes/functions/class-mgwpp-shortcode.php';
require_once MG_PLUGIN_PATH . 'includes/functions/class-mgwpp-canvas-shortcode.php';

// Gallery types
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-single-gallery/class-mgwpp-single-gallery.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-multi-gallery/class-mgwpp-multi-gallery.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-grid-gallery/class-mgwpp-grid-gallery.php';

// Slider types
require_once MG_PLUGIN_PATH . 'includes/gallery-types/class-mgwpp-testimonial-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-threed-carousel/class-mgwpp-threed-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-mega-slider/class-mgwpp-mega-slider.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-pro-carousel/class-mgwpp-pro-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-neon-carousel/class-mgwpp-neon-carousel.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-full-page-slider/class-mgwpp-full-page-slider.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-spotlight-carousel/class-mgwpp-spotlight-carousel.php';

// 3D Model Carousel
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-model-carousel/class-mgwpp-3d-model-carousel.php';

// Capabilities and post types
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-capabilities.php';

// Gallery registration
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-post-type.php';
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-capabilities.php';
require_once MG_PLUGIN_PATH . 'includes/registration/gallery/class-mgwpp-gallery-manager.php';
//require_once MG_PLUGIN_PATH . 'includes/functions/class-mgwpp-upload.php';

// Album registration
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-post-type.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-display.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-capabilities.php';
require_once MG_PLUGIN_PATH . 'includes/registration/album/class-mgwpp-album-submit.php';

// Testimonials registration (conditional based on settings)
if (mgwpp_is_module_enabled('testimonials')) {
    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-post-type.php';
    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-capabilties.php';
    require_once MG_PLUGIN_PATH . 'includes/registration/testimonials/class-mgwpp-testimonials-manager.php';
}

// Admin core
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-admin-core.php';
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-data-handler.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/edit-gallery/class-mgwpp-edit-gallery.php';

// Uninstall and assets
require_once MG_PLUGIN_PATH . 'includes/registration/class-mgwpp-uninstall.php';
require_once MG_PLUGIN_PATH . 'includes/registration/assets/class-mgwpp-assets.php';
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp_ajax_handler.php';

// Settings View (load early for admin-post.php handling)
if (is_admin()) {
    require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';
    require_once MG_PLUGIN_PATH . 'includes/admin/views/settings/class-mgwpp-settings-view.php';
}

// 3D Galleries (conditional based on settings)
if (mgwpp_is_module_enabled('3d_galleries')) {
    // 3D Model Carousel
    require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-model-carousel/class-mgwpp-3d-model-carousel.php';
    require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-model-carousel/admin/class-mgwpp-3d-admin-integration.php';

    // 3D Masonry Gallery (with WALL, TABLE, TUNNEL, FLAT modes)
    require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-masonry-gallery/class-mgwpp-3d-masonry-gallery.php';

    // 3D Horizontal Marquee
    require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-3d-horizontal-marquee/class-mgwpp-3d-horizontal-marquee.php';
}

// Marquee Galleries (always loaded)
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-marquee-gallery/class-mgwpp-marquee-gallery.php';
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-marquee-gallery/admin/class-mgwpp-marquee-admin.php';

// Vertical Marquee Gallery
require_once MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-vertical-marquee/class-mgwpp-vertical-marquee.php';

// Canvas Editor (BETA - conditional based on settings)
if (mgwpp_is_module_enabled('canvas')) {
    require_once MG_PLUGIN_PATH . 'includes/registration/canvas/class-mgwpp-canvas-post-type.php';
    require_once MG_PLUGIN_PATH . 'includes/registration/canvas/class-mgwpp-canvas-capabilities.php';
    require_once MG_PLUGIN_PATH . 'includes/admin/views/canvas-editor/class-mgwpp-canvas-editor.php';
}

// Analytics
require_once MG_PLUGIN_PATH . 'includes/admin/class-mgwpp-analytics-manager.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/analytics/class-mgwpp-analytics-view.php';


// Initialize AJAX handler
MGWPP_Ajax_Handler::init();

// Initialize Canvas Editor (BETA - only if enabled)
if (mgwpp_is_module_enabled('canvas') && class_exists('MGWPP_Canvas_Editor_View')) {
    MGWPP_Canvas_Editor_View::init();
}

// Initialize Analytics
MGWPP_Analytics_Manager::init();

// ======================
// ACTIVATION/DEACTIVATION
// ======================
register_activation_hook(__FILE__, function () {
    // Always register core post types
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
    MGWPP_Gallery_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();

    // Conditional: Testimonials
    if (mgwpp_is_module_enabled('testimonials') && class_exists('MGWPP_Testimonial_Capabilities')) {
        MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    }

    // Create Analytics Table
    if (class_exists('MGWPP_Analytics_Manager')) {
        MGWPP_Analytics_Manager::create_table();
    }

    if (false === get_option('mgwpp_enabled_modules')) {
        // Module initialization placeholder
    }
    flush_rewrite_rules(false);
});

register_deactivation_hook(__FILE__, function () {
    unregister_post_type('mgwpp_testimonials');
    unregister_post_type('mgwpp_soora');
    unregister_post_type('mgwpp_album');
    flush_rewrite_rules(false);
});

register_uninstall_hook(__FILE__, 'mgwpp_plugin_uninstall');

function mgwpp_plugin_uninstall()
{
    $sowar = get_posts([
        'post_type' => 'mgwpp_soora',
        'numberposts' => -1,
        'post_status' => 'any'
    ]);

    foreach ($sowar as $gallery_image) {
        wp_delete_post(intval($gallery_image->ID), true);
    }
}

// ======================
// PLUGIN INITIALIZATION
// ======================
add_action('init', function () {
    // Register shortcodes
    add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
    add_shortcode('mgwpp_canvas', 'mgwpp_canvas_shortcode');

    // Initialize post types
    MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
    MGWPP_Album_Post_Type::mgwpp_register_album_post_type();

    // Conditional: Testimonials
    if (mgwpp_is_module_enabled('testimonials') && class_exists('MGWPP_Testimonial_Post_Type')) {
        MGWPP_Testimonial_Post_Type::mgwpp_register_testimonial_post_type();
    }

    // Conditional: Canvas (BETA)
    if (mgwpp_is_module_enabled('canvas') && class_exists('MGWPP_Canvas_Post_Type')) {
        MGWPP_Canvas_Post_Type::register();
    }

    // Initialize capabilities
    MGWPP_Gallery_Capabilities::mgwpp_gallery_capabilities();
    MGWPP_Album_Capabilities::mgwpp_album_capabilities();

    // Conditional: Testimonials capabilities
    if (mgwpp_is_module_enabled('testimonials') && class_exists('MGWPP_Testimonial_Capabilities')) {
        MGWPP_Testimonial_Capabilities::mgwpp_testimonial_capabilities();
    }

    // Conditional: Canvas capabilities (BETA)
    if (mgwpp_is_module_enabled('canvas') && class_exists('MGWPP_Canvas_Capabilities')) {
        MGWPP_Canvas_Capabilities::register();
    }

    // Register hooks
    MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action();
    MGWPP_Uninstall::mgwpp_register_uninstall_hook();

    // ADD THIS: Initialize 3D Model MIME types (only if 3D galleries enabled)
    if (mgwpp_is_module_enabled('3d_galleries') && class_exists('MGWPP_3D_Model_Carousel')) {
        // Add MIME type support for 3D models
        add_filter('upload_mimes', ['MGWPP_3D_Model_Carousel', 'register_mime_types']);
    }

    if (is_admin() && class_exists('MGWPP_3D_Admin_Integration')) {
        MGWPP_3D_Admin_Integration::init();
    }
});

add_action('after_setup_theme', function () {
    if (!current_theme_supports('post-thumbnails')) {
        add_theme_support('post-thumbnails');
    }
});

// ======================
// 3D MODEL UPLOAD SECURITY
// ======================
add_filter('wp_check_filetype_and_ext', function ($types, $file, $filename, $mimes) {
    if (false !== strpos($filename, '.gltf')) {
        $types['ext'] = 'gltf';
        $types['type'] = 'model/gltf+json';
    }
    if (false !== strpos($filename, '.glb')) {
        $types['ext'] = 'glb';
        $types['type'] = 'model/gltf-binary';
    }
    if (false !== strpos($filename, '.obj')) {
        $types['ext'] = 'obj';
        $types['type'] = 'model/obj';
    }
    if (false !== strpos($filename, '.fbx')) {
        $types['ext'] = 'fbx';
        $types['type'] = 'application/octet-stream';
    }
    return $types;
}, 10, 4);

// Limit 3D model file size (10MB) using proper WordPress prefilter
// This filter receives the file array as a parameter, avoiding direct $_FILES access
add_filter('wp_handle_upload_prefilter', function ($file) {
    // Get file extension from the name parameter passed by WordPress
    $filename = isset($file['name']) ? sanitize_file_name($file['name']) : '';
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Check if it's a 3D model file
    if (in_array($extension, ['gltf', 'glb', 'obj', 'fbx'], true)) {
        $max_size = 20 * 1024 * 1024; // 20MB
        $file_size = isset($file['size']) ? absint($file['size']) : 0;

        if ($file_size > $max_size) {
            $file['error'] = sprintf(
                /* translators: %s: maximum file size */
                __('3D model files must be smaller than %s.', 'mini-gallery'),
                size_format($max_size)
            );
        }
    }

    return $file;
});

// ======================
// ADMIN FUNCTIONALITY
// ======================
add_action('admin_menu', function () {
    if (is_admin()) {
        MGWPP_Admin_Core::init();
    } else {
        esc_html_e('Your Not Allowed To Access Mini Gallery: Access Has Been Reported to Security Plugin', 'mini-gallery');
    }
}, 5);

// ======================
// TEMPLATE HANDLING
// ======================
// In your main plugin file, simplify template handling:
add_filter('template_include', function ($template) {
    if (is_singular('mgwpp_soora')) {
        $custom_template = MG_PLUGIN_PATH . 'templates/single-mgwpp_soora.php';
        return file_exists($custom_template) ? $custom_template : $template;
    }

    if (is_singular('mgwpp_album')) {
        $custom_template = MG_PLUGIN_PATH . 'templates/single-mgwpp_album.php';
        return file_exists($custom_template) ? $custom_template : $template;
    }

    return $template;
});
// ======================
// PLUGIN LINKS
// ======================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $docs_link = '<a href="https://minigallery.andgowebsolutions.com/docs/" target="_blank">Docs</a>';
    array_unshift($links, $docs_link);
    return $links;
});

add_filter('plugin_row_meta', function ($links, $file) {
    if (plugin_basename(__FILE__) !== $file) {
        return $links;
    }

    $links[] = '<a href="https://github.com/omarashzeinhom/mini-gallery-dev" target="_blank">Contribute</a>';
    $links[] = '<a href="https://wordpress.org/support/plugin/mini-gallery/reviews/#new-post" target="_blank">Rate Plugin ★★★★★</a>';
    return $links;
}, 10, 2);
