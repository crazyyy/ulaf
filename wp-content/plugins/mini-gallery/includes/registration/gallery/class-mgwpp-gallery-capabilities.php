<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Gallery_Capabilities
{

    // Assign custom capabilities to roles
    public static function mgwpp_gallery_capabilities()
    {
        // Only run if capabilities haven't been added yet (check stored version)
        $cap_version = get_option('mgwpp_gallery_cap_version', '0');
        $current_version = '1.6.1'; // Update this when capabilities change

        if ($cap_version === $current_version) {
            return; // Capabilities already set for this version
        }

        // Assign capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            // Add the custom capabilities for gallery management
            $admin_role->add_cap('edit_mgwpp_soora');
            $admin_role->add_cap('read_mgwpp_soora');
            $admin_role->add_cap('delete_mgwpp_soora');
            $admin_role->add_cap('edit_mgwpp_sooras');
            $admin_role->add_cap('edit_others_mgwpp_sooras');
            $admin_role->add_cap('publish_mgwpp_sooras');
            $admin_role->add_cap('read_private_mgwpp_sooras');
            $admin_role->add_cap('delete_mgwpp_sooras');
            $admin_role->add_cap('delete_private_mgwpp_sooras');
            $admin_role->add_cap('delete_published_mgwpp_sooras');
            $admin_role->add_cap('delete_others_mgwpp_sooras');
            $admin_role->add_cap('edit_private_mgwpp_sooras');
            $admin_role->add_cap('edit_published_mgwpp_sooras');
            $admin_role->add_cap('create_mgwpp_sooras');
        }

        // Mark capabilities as set for this version
        update_option('mgwpp_gallery_cap_version', $current_version);
    }

    /**
     * Check if current user can manage galleries
     * Works for administrators even if custom capabilities not yet assigned
     * 
     * @return bool
     */
    public static function current_user_can_manage_galleries()
    {
        // Administrators always have access (fallback for fresh installs)
        if (current_user_can('manage_options')) {
            return true;
        }

        // Check custom capability
        return current_user_can('publish_mgwpp_sooras');
    }
}

// Hook to 'init' instead of 'admin_init' so capabilities are available for AJAX requests
add_action('init', array('MGWPP_Gallery_Capabilities', 'mgwpp_gallery_capabilities'));
