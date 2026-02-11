=== PlugTracker ===
Contributors: guruplugins
Tags: plugins,plugin manager,plugin updates manager,plugin tracker
Requires at least: 4.0
Tested up to: 6.7.1
Stable tag: 1.0
Requires PHP: 5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tracks plugin activity, including activation, deactivation, addition, deletion, and updates, with date, time, and user information recorded.

== Description ==

PlugTracker is a comprehensive WordPress plugin designed to log and manage plugin update, activation, deactivation, installation, and deletion events. With this plugin, WordPress administrators can monitor and audit plugin-related activities in real-time. It provides detailed tracking information, including the plugin name, event type, user who triggered the action, date, and time.

Keep your site secure and organized by knowing exactly what happens with your plugins, ensuring better control over your WordPress environment.

Key Features:
Plugin Update and Activity Tracking

Logs each plugin update with details such as:
- Plugin name
- Previous version
- Update status (e.g., Success or Failed)
- User who performed the update
- Date and time of the update
- Event Logging for Plugin Status Changes

Tracks when plugins are:
- Activated
- Deactivated
- Added (installed)
- Deleted (removed)

Records details like:
- Plugin name
- Event type (e.g., Activated, Deactivated, etc.)
- Triggered user
- Date and time of the event
- Admin Dashboard Interface
- Provides a clean and user-friendly dashboard interface

Displays logged data in two organized tables:
- Plugin Update Logs
- Plugin Event Logs (Activation, Deactivation, Addition, and Deletion)
- Includes search and filter options for better usability (optional future extension)


Automatically creates database tables to store plugin event and update logs during activation.
Easy cleanup of all logs using the built-in “Delete All Data” button in the dashboard.
Robust and Secure

Validates permissions to ensure only administrators can access the plugin data and controls.
Compatible with WordPress multisite installations.
Fully Automated

Hooks into WordPress core functions to automatically track events without additional configurations.
Lightweight and Fast

Optimized for performance to ensure your WordPress site remains fast and efficient.

Benefits:
- Gain full visibility into plugin management on your site
- Quickly identify who performed specific actions, such as updating or deleting plugins
- Enhance site security by tracking unauthorized or accidental changes to plugins
- Maintain a complete audit trail for compliance and debugging purposes
- Effortlessly manage plugin-related data from a single dashboard

Use Cases:
- Site Administrators
- Monitor all plugin activities on your WordPress installation
- Identify and troubleshoot issues caused by plugin updates or changes
- Agencies and Developers
- Provide clients with detailed reports on plugin changes. Keep track of team activities across different WordPress environments

Security-Conscious Users
- Ensure accountability for all plugin-related actions
- Track potential security breaches or unauthorized changes

Requirements:
WordPress Version: 4.7 or higher.
PHP Version: 7.4 or higher.
Database: MySQL version 5.6 or greater / MariaDB version 10.1 or greater.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugtracker/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. That it! You are now tracking plugin updates in WordPress.

== Frequently Asked Questions ==

What does PlugTracker do?
PlugTracker logs all plugin updates on your WordPress site, including the plugin name, the user who performed the update, and the date and time. It provides a dashboard interface to view and manage this information.

How do I access the update history?
You can access the update history from the PluTracker menu in your WordPress admin dashboard. It displays a detailed list of all tracked updates.

Does this plugin track updates made by automated tools or scripts?
Plugin Update Tracker logs updates made through the WordPress admin interface. Automated updates may not be fully captured, depending on how they are executed.

Is this plugin compatible with multisite WordPress setups?
Currently, PlugTracker is designed for single-site installations. Multisite support may be added in future updates.

Can I export the update logs?
Export functionality is not included in the free version.
