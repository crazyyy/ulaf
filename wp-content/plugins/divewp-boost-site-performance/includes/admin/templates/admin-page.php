<?php
/**
 * Main admin page template for DiveWP
 * 
 * This file handles the main admin interface of the DiveWP plugin.
 * Displays:
 * - Welcome content
 * - Version updates
 * 
 * Database operations are handled through DiveWP_DB_Access class
 * which provides a secure, centralized way to interact with:
 * - Email logs (divewp_email_log table)
 * - User events (divewp_user_events table)
 *
 * @package DiveWP
 * @since 1.0.0 Initial release

 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die(esc_html__('Direct access not permitted.', 'divewp-boost-site-performance'));
}

// Version constant for template
if (!defined('DIVEWP_CURRENT_VERSION')) {
    define('DIVEWP_CURRENT_VERSION', '1.0.0');
}
?>
<div class="welcome-content">
    <?php
    // Welcome content will be added here
    ?>
</div>