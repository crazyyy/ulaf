<?php

/**
 * Current AJAX Handler Class for Ultimate Extension for ACF
 *
 * This class handles all AJAX operations for ACF versions 6.5 and newer.
 * It provides optimised database operations with caching to improve performance
 * when managing preview images for ACF flexible content layouts.
 *
 * Key Features:
 * - Object caching for database query optimization
 * - Modern WordPress AJAX response handling
 * - Input validation and sanitization
 * - Error handling with user-friendly messages
 * - Cache invalidation for data consistency
 *
 * @package Ultimate_Extension_for_ACF
 * @subpackage Current
 * @since 1.0.0
 * @author Ultimate Agency
 */

// Security Check - Prevent direct access to this file
defined( 'ABSPATH' ) or die( 'Direct access forbidden.' );

class UEFAX_Current_Ajax
{
/**
 * Database table name for storing preview images
 *
 * @var string
 * @since 1.0.0
 */
private static string $db;

/**
 * WordPress database object instance
 *
 * @var wpdb
 * @since 1.0.0
 */
private static wpdb $wpdb;

/**
 * Constructor
 *
 * Initialises the database connection and sets up the table name.
 * The constructor sets up static properties for database access.
 *
 * @since 1.0.0
 */
public function __construct()
{
global $wpdb;

        // Set up database table name with WordPress prefix
        self::$db = $wpdb->prefix . 'uefax_modal_preview';

// Store WordPress database object for queries
self::$wpdb = $wpdb;
}

/**
 * Get Preview Image for a Component - Optimized with Caching and Multisite Support
 *
 * Retrieves the preview image attachment ID for a given ACF layout component.
 * Uses WordPress object cache to minimise database queries and improve performance.
 * Includes validation to ensure the attachment still exists and is an image.
 *
 * Multisite Support:
 * - First checks current site for preview image
 * - If not found, falls back to the main site's preview image
 * - Only returns null if neither site has the image
 *
 * @param string $component The ACF layout name to get the preview image for
 *
 * @return string|null The attachment ID if found, null otherwise
 * @since 1.0.0
 */
    public static function get_uefax_images( string $component ): ?string
{
// Input validation
if ( empty( $component ) ) {
return null;
}

        // Use WordPress object cache to reduce database queries
        // Cache key includes component hash for uniqueness
        $cache_key     = 'uefax_modal_preview_' . md5( $component );
        $cached_result = wp_cache_get( $cache_key, 'ultimate_extension_for_acf' );

// Return the cached result if available
if ( $cached_result !== false ) {
return $cached_result;
}

$return = null;

// First, try to get preview image from current site
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query with proper preparation
$current_site_image = $wpdb->get_var( $wpdb->prepare(
"SELECT preview_image FROM " . esc_sql(self::$db) . " WHERE component = %s",
$component
) );

// Validate the current site image if found
if ( $current_site_image && wp_attachment_is_image( $current_site_image ) ) {
$return = $current_site_image;
} elseif ( $current_site_image ) {
// Clean up invalid image from the current site
self::delete_acfm_image( $component );
}

// If no valid image found in current site, and we're in a multisite setup,
// try to get the image from the main site
if ( ! $return && is_multisite() ) {
$main_site_id = get_main_site_id();
$current_site_id = get_current_blog_id();

// Only check the main site if we're not already on the main site
if ( $main_site_id !== $current_site_id ) {
// Switch to the main site to check for preview image
switch_to_blog( $main_site_id );

// Get the main site's database table name
global $wpdb;
$main_site_db = $wpdb->prefix . 'acf_modal_preview';

// Query main site database for preview image ID
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query with proper preparation
$main_site_image = $wpdb->get_var( $wpdb->prepare(
"SELECT preview_image FROM " . esc_sql($main_site_db) . " WHERE component = %s",
$component
) );

// Validate the main site image if found
if ( $main_site_image && wp_attachment_is_image( $main_site_image ) ) {
$return = $main_site_image;
} elseif ( $main_site_image ) {
// Clean up invalid image from the main site
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query with proper preparation
$wpdb->query( $wpdb->prepare(
"DELETE FROM " . esc_sql($main_site_db) . " WHERE component = %s",
$component
) );
}

// Restore current site
restore_current_blog();
}
}

        // Cache the result for 1 hour to reduce future database queries
        wp_cache_set( $cache_key, $return, 'ultimate_extension_for_acf', HOUR_IN_SECONDS );

return $return;
}

/**
 * Set Preview Image for a Component - AJAX Handler
 *
 * Handles AJAX requests to set or update the preview image for an ACF layout component.
 * Uses optimised database operations and includes comprehensive error handling.
 *
 * Expected POST Parameters:
 * - layout: The ACF layout name
 * - image: The WordPress attachment ID
 *
 * Response Format:
 * - Success: JSON with a message, component, image_id, and image_url
 * - Error: JSON with an error message
 *
 * @since 1.0.0
 */
    public static function set_uefax_image(): void
{
// Initialize variables
$component = $image = '';

        // Verify nonce for security
        $nonce = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'] ?? $_POST['_wpnonce'] ?? ''));
        if ( ! wp_verify_nonce( $nonce, 'uefax-ajax-nonce' ) ) {
wp_send_json_error( array(
'message' => 'Security verification failed. Please refresh the page and try again.'
) );

return;
}

// Sanitize and validate input parameters
if ( isset( $_POST['layout'], $_POST['image'] ) ) {
$component = sanitize_text_field( wp_unslash($_POST['layout']) );
$image     = sanitize_text_field( wp_unslash($_POST['image']) );
}

// Validate component name
if ( empty( $component ) ) {
wp_send_json_error( array(
'message' => 'Component name is required.'
) );

return;
}

// Validate image ID and ensure it's actually an image attachment
if ( ! $image || ! wp_attachment_is_image( $image ) ) {
wp_send_json_error( array(
'message' => 'Invalid image ID provided. Please select a valid image.'
) );

return;
}

// Use WordPress optimised time function
$date_updated = current_time( 'mysql' );

// Optimised database operation: Single query with ON DUPLICATE KEY UPDATE
// This eliminates the need for separate SELECT + UPDATE/INSERT operations
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query with proper preparation
$results = $wpdb->query( $wpdb->prepare(
"INSERT INTO " . esc_sql(self::$db) . " (component, preview_image, date_updated)
             VALUES (%s, %s, %s)
             ON DUPLICATE KEY UPDATE
             preview_image = VALUES(preview_image),
             date_updated = VALUES(date_updated)",
$component, $image, $date_updated
) );

// Check for database errors
if ( $results === false ) {
wp_send_json_error( array(
'message' => 'Database operation failed. Please try again.',
'error'   => $wpdb->last_error
) );

return;
}

        // Invalidate cache for this component to ensure data consistency
        $cache_key = 'uefax_modal_preview_' . md5( $component );
        wp_cache_delete( $cache_key, 'ultimate_extension_for_acf' );

// Get the image URL for the response (WordPress caches this internally)
$image_url = wp_get_attachment_image_url( $image, 'medium' );

// Return success response with all relevant data
wp_send_json_success( array(
'message'   => 'Preview image updated successfully!',
'component' => $component,
'image_id'  => $image,
'image_url' => $image_url
) );
}

/**
 * Delete Preview Image for a Component
 *
 * Removes the preview image record from the database for a given component.
 * Also, invalidates the cache to ensure data consistency.
 *
 * @param string $component The ACF layout name to delete the preview image for
 *
 * @return bool True if deletion was successful, false otherwise
 * @since 1.0.0
 */
    public static function delete_uefax_image( string $component ): bool
{
// Input validation
if ( empty( $component ) ) {
return false;
}

        // Invalidate cache when deleting to maintain data consistency
        $cache_key = 'uefax_modal_preview_' . md5( $component );
        wp_cache_delete( $cache_key, 'ultimate_extension_for_acf' );

// Execute delete query
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table query with proper preparation
$result = $wpdb->query( $wpdb->prepare(
"DELETE FROM " . esc_sql(self::$db) . " WHERE component = %s",
$component
) );

// Return true if a query executed successfully (even if no rows were affected)
return $result !== false;
}
}
