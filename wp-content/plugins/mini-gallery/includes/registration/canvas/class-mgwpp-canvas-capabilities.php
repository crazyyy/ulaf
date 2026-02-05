<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canvas Capabilities
 * 
 * Registers custom capabilities for the canvas post type.
 * Follows the same pattern as existing gallery capabilities.
 */
class MGWPP_Canvas_Capabilities
{
    /**
     * Register canvas capabilities for administrator role
     */
    public static function register()
    {
        $role = get_role('administrator');
        
        if (!$role) {
            return;
        }

        $capabilities = [
            // Post type capabilities
            'edit_mgwpp_canvas',
            'read_mgwpp_canvas',
            'delete_mgwpp_canvas',
            'edit_mgwpp_canvases',
            'edit_others_mgwpp_canvases',
            'publish_mgwpp_canvases',
            'read_private_mgwpp_canvases',
            'delete_mgwpp_canvases',
            'delete_private_mgwpp_canvases',
            'delete_published_mgwpp_canvases',
            'delete_others_mgwpp_canvases',
            'edit_private_mgwpp_canvases',
            'edit_published_mgwpp_canvases',
        ];

        foreach ($capabilities as $cap) {
            $role->add_cap($cap);
        }
    }

    /**
     * Remove canvas capabilities (for uninstall)
     */
    public static function remove()
    {
        $role = get_role('administrator');
        
        if (!$role) {
            return;
        }

        $capabilities = [
            'edit_mgwpp_canvas',
            'read_mgwpp_canvas',
            'delete_mgwpp_canvas',
            'edit_mgwpp_canvases',
            'edit_others_mgwpp_canvases',
            'publish_mgwpp_canvases',
            'read_private_mgwpp_canvases',
            'delete_mgwpp_canvases',
            'delete_private_mgwpp_canvases',
            'delete_published_mgwpp_canvases',
            'delete_others_mgwpp_canvases',
            'edit_private_mgwpp_canvases',
            'edit_published_mgwpp_canvases',
        ];

        foreach ($capabilities as $cap) {
            $role->remove_cap($cap);
        }
    }

    /**
     * Check if current user can edit canvas galleries
     * 
     * @return bool
     */
    public static function current_user_can_edit()
    {
        return current_user_can('edit_mgwpp_canvases');
    }

    /**
     * Check if current user can delete canvas galleries
     * 
     * @return bool
     */
    public static function current_user_can_delete()
    {
        return current_user_can('delete_mgwpp_canvases');
    }
}
