<?php

/**
 * Legacy AJAX Handler Class for Ultimate Extension for ACF
 * Based on the original flexible content modal AJAX handler
 *
 * @package Ultimate_Extension_for_ACF
 * @subpackage Legacy
 * @since 1.0.0
 */

// Security Check - Prevent direct access to this file
defined('ABSPATH') or die('Direct access forbidden.');

class UEFAX_Legacy_Ajax
{
    /**
     * Database table name for storing preview images
     */
    private static string $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        self::$db = $wpdb->prefix . 'uefax_modal_preview';
    }

    /**
     * Get Preview Image for a Component (Legacy)
     * Matches the original flexible content modal AJAX handler exactly
     */
    public static function get_uefax_images($component)
    {
        global $wpdb;

        // Input validation
        if (empty($component)) {
            return null;
        }

        // Use WordPress object cache to reduce database queries
        $cache_key = 'uefax_modal_preview_' . md5($component);
        $cached_result = wp_cache_get($cache_key, 'ultimate_extension_for_acf');

        // Return the cached result if available
        if ($cached_result !== false) {
            return $cached_result;
        }

        $return = null;

        // Query database for preview image ID
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table 'acf_modal_preview' has no WordPress API equivalent, requires direct query with proper preparation
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT preview_image FROM " . esc_sql(self::$db) . " WHERE component = %s",
            $component
        ));
        $return = $result ?? '';

        // Validate that the attachment still exists and is an image
        if ($return && !wp_attachment_is_image($return)) {
            self::delete_acfm_image($component);
            $return = null;
        }

        // Cache the result for 1 hour to reduce future database queries
        wp_cache_set($cache_key, $return, 'ultimate_extension_for_acf', HOUR_IN_SECONDS);

        return $return;
    }

    /**
     * Set Preview Image for a Component (Legacy) - AJAX Handler
     * Matches the original exactly
     */
    public static function set_uefax_image(): void
    {
        global $wpdb;

        // Verify nonce for security
        $nonce = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'] ?? $_POST['_wpnonce'] ?? ''));
        if (!wp_verify_nonce($nonce, 'uefax-ajax-nonce')) {
            wp_send_json_error(array(
                'message' => 'Security verification failed. Please refresh the page and try again.'
            ));
            return;
        }

        $component = $image = '';

        if (isset($_POST['layout'], $_POST['image'])) {
            $component = sanitize_text_field(wp_unslash($_POST['layout']));
            $image = sanitize_text_field(wp_unslash($_POST['image']));
        }

        // Validate component name
        if (empty($component)) {
            wp_send_json_error(array(
                'message' => 'Component name is required.'
            ));
            return;
        }

        // Validate image ID and ensure it's actually an image attachment
        if (!$image || !wp_attachment_is_image($image)) {
            wp_send_json_error(array(
                'message' => 'Invalid image ID provided. Please select a valid image.'
            ));
            return;
        }

        // Use WordPress optimized time function
        $date_updated = current_time('mysql');

        // Optimized database operation: Single query with ON DUPLICATE KEY UPDATE
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table 'acf_modal_preview' has no WordPress API equivalent, requires direct query with proper preparation
        $results = $wpdb->query($wpdb->prepare(
            "INSERT INTO " . esc_sql(self::$db) . " (component, preview_image, date_updated)
             VALUES (%s, %s, %s)
             ON DUPLICATE KEY UPDATE
             preview_image = VALUES(preview_image),
             date_updated = VALUES(date_updated)",
            $component, $image, $date_updated
        ));

        // Check for database errors
        if ($results === false) {
            wp_send_json_error(array(
                'message' => 'Database operation failed. Please try again.',
                'error' => $wpdb->last_error
            ));
            return;
        }

        // Invalidate cache for this component to ensure data consistency
        $cache_key = 'uefax_modal_preview_' . md5($component);
        wp_cache_delete($cache_key, 'ultimate_extension_for_acf');

        // Get the image URL for the response
        $image_url = wp_get_attachment_image_url($image, 'medium');

        // Return success response
        wp_send_json_success(array(
            'message' => 'Preview image updated successfully!',
            'component' => $component,
            'image_id' => $image,
            'image_url' => $image_url
        ));
    }

    /**
     * Delete Preview Image for a Component (Legacy)
     */
    public static function delete_uefax_image($component): bool
    {
        global $wpdb;

        // Invalidate cache when deleting to maintain data consistency
        $cache_key = 'uefax_modal_preview_' . md5($component);
        wp_cache_delete($cache_key, 'ultimate_extension_for_acf');

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table 'acf_modal_preview' has no WordPress API equivalent, requires direct query with proper preparation
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM " . esc_sql(self::$db) . " WHERE component = %s",
            $component
        ));
    }
}
