=== BugTrace - Debug Log Tool ===
Contributors: nsgawli
Tags: debug log, wordpress debug, PHP errors, database info, troubleshooting tool
Requires at least: 6.2
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Essential WordPress debug tool: View/download logs, toggle debug settings & inspect server info. Troubleshoot PHP errors & site issues faster!

== Description ==

Tired of cumbersome debugging processes? BugTrace - Debug Log Tool streamlines WordPress troubleshooting, providing developers and site administrators with a powerful yet minimalist interface to diagnose and resolve issues swiftly. Stop wasting time with manual wp-config.php edits or FTP access for basic debugging tasks.

With BugTrace, you can:

*   **Instantly Control Debug Constants:** Gain immediate access to toggle crucial WordPress debugging constants like WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY, and SCRIPT_DEBUG directly from your dashboard. Activate or deactivate them with a single click—no code editing required, saving you valuable development time and reducing the risk of site-breaking syntax errors.
*   **AI-Powered Error Resolution (ChatGPT, Gemini & Google):** Stop guessing and start solving! For every log entry, BugTrace provides one-click 'Help' links. Instantly send the error message to ChatGPT, Gemini, or Google to find solutions, code snippets, and explanations. Drastically reduce your troubleshooting time and solve complex problems faster than ever before.
*   **Effortlessly Access Debug Logs:** View and download your WordPress `debug.log` file directly from the admin panel. This allows for quick identification of PHP errors, warnings, and notices, helping you pinpoint the source of problems much faster than traditional methods.
*   **Dynamic Log Viewing with Auto & Manual Refresh:** Keep an eye on your `debug.log` in near real-time with the auto-refresh option, or manually refresh the log view whenever you need. This helps in actively monitoring errors as they occur and is invaluable for live debugging sessions.
*   **Securely Inspect Server Configurations:** Safely view the contents of vital server files such as `.htaccess` and wp-config.php (read-only), and review detailed `phpinfo()` output without ever leaving your WordPress environment. This provides essential insights for advanced WordPress troubleshooting and server diagnostics.
*   **Monitor Key System Behaviors:** Get a clear overview of your database table information (including size and row count), view active browser cookies (read-only), inspect WordPress transients (read-only), and check your list of scheduled WordPress cron jobs. This comprehensive visibility helps you understand your site's inner workings.
*   **Utilize Admin Bar Shortcuts:** Speed up your debugging workflow with convenient admin bar shortcuts to view, download, and clear the `debug.log` file from anywhere in your WordPress admin area.
*   **Quick Log to Clipboard:** Instantly copy the entire contents of your log to your clipboard with a single click. Perfect for quickly sharing log details with support teams, pasting into development tools, or for your own records.
*   **Enjoy a Minimalist Interface:** BugTrace is intentionally lightweight with a clean UI, focusing purely on essential debugging tools to ensure it doesn't bog down your WordPress site.

Whether you're developing a new theme or plugin, troubleshooting a tricky bug on a staging server, or performing routine site health checks, BugTrace - Debug Log Tool provides the critical information you need, efficiently and effectively.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for BugTrace - Debug Log Tool
3. Click 'Install Now'
4. Activate the plugin on the plugin dashboard

= Uploading in WordPress Dashboard =

1. Download `debug-log-tool.zip` from this page
2. Navigate to the 'Add New' in the plugins dashboard
3. Navigate to the 'Upload' area
4. Select `debug-log-tool.zip` from your computer
5. Click 'Install Now'
6. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `debug-log-tool.zip` from this page
2. Extract the `debug-log-tool` directory to your computer
3. Upload the `debug-log-tool` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

This plugin is almost plug and play!

== Frequently Asked Questions ==

= What are the AI Help Links (ChatGPT, Gemini, Google)? =
This is a powerful new feature designed to accelerate your debugging. Next to each error log, you'll find icons for ChatGPT, Gemini, and Google. Clicking one will open a new tab and automatically search that platform for a solution to that specific error, saving you the time of manually copying, pasting, and searching. It's like having an expert assistant for every error.

= Can I download the debug.log file? =
Yes, if a `debug.log` file exists and is writable by WordPress, BugTrace allows you to download it directly from the admin panel with a single click.

= Is it safe to use BugTrace on a production website? =
Yes, BugTrace can be used on a production site. However, exercise caution when enabling debugging modes (like WP_DEBUG or WP_DEBUG_DISPLAY) in a live environment, as this can sometimes expose sensitive information to visitors if errors occur. It's generally recommended for staging or development, or for brief troubleshooting periods on live sites.

= How does BugTrace help if I suspect issues with my `.htaccess` or wp-config.php files? =
BugTrace provides a read-only viewer for your `.htaccess` and wp-config.php files directly within the WordPress admin dashboard. This allows you to quickly inspect their contents for misconfigurations or unexpected code without needing FTP access or risking accidental direct edits to these critical files.

= Can I use BugTrace to monitor WordPress cron jobs? =
Yes, BugTrace includes a feature to view your scheduled WordPress cron jobs. This can help you verify that essential tasks are scheduled correctly and identify any unexpected or missing cron events that might be affecting your site's functionality.

= Is BugTrace lightweight and will it impact my site's performance? =
Absolutely. BugTrace is designed with a minimalist UI and focuses only on essential debugging functionalities. It's lightweight and should not add any noticeable performance overhead to your website's front-end or admin area during normal operation.

= What kind of server information can I inspect with BugTrace? =
Beyond specific file viewers, BugTrace allows you to see `phpinfo()` output for detailed PHP configuration, database table information (including size and row count), a list of active browser cookies (read-only), and WordPress transients (read-only). This helps in comprehensive server diagnostics.

= How does the admin bar shortcut for the debug log enhance my workflow? =
BugTrace adds a convenient shortcut to the WordPress admin bar. This allows you to quickly view, download, or clear the `debug.log` file from any page within the admin area, speeding up your debugging process significantly.

= How does the auto-refresh feature for the debug log work? =
The auto-refresh feature periodically reloads the debug log content within the BugTrace interface, allowing you to see new log entries as they are written without needing to manually refresh the page or the entire browser tab. You can also trigger a manual refresh at any time for immediate updates.

== Screenshots ==

1. Centralized BugTrace Dashboard: Viewing the WordPress Debug Log Clearly.
2. Effortless Debug Settings: Toggle WP_DEBUG & Other Constants Instantly.
3. Securely Inspect wp-config.php Contents via the WordPress Admin.
4. Quick.htaccess File Viewer: Essential for Server Troubleshooting.
5. Comprehensive PHP Information (phpinfo()) at Your Fingertips with BugTrace.
6. WordPress Database Table Insights: Monitor Size and Row Counts.
7. Diagnostic View of Active Browser Cookies (Read-Only).
8. Inspect WordPress Transients: Read-Only List for Effective Debugging.
9. Overview of Scheduled WordPress Cron Jobs within the BugTrace Tool.

== Changelog ==

= 1.0.7 =
New: Setting added to view logs in UTC or WordPress timezone

= 1.0.6 =
Fix: The log size is now set automatically based on your PHP memory limit, helping prevent memory issues.
Fix: Few css fixes.

= 1.0.5 =
New: Error logs are now grouped to reduce repetition and improve readability.

= 1.0.4 =
New: Added AI-Powered Help Links for each log entry (ChatGPT, Gemini, Google) for instant error resolution.
New: Implemented improved error handling to display a message if WordPress Cron is not working correctly.

= 1.0.3

New: Added auto-refresh and manual refresh options for the debug log view, enabling near real-time log monitoring.
New: Implemented a 'Copy to Clipboard' action button for the debug log, allowing for easy sharing of log contents.

= 1.0.2

New: Added Cookies list (read-only).
New: Added Transient list (read-only).
New: Added Cron Job list (read-only).
New: Added admin bar shortcode to view, download, and clear debug log.

= 1.0.1

New: Admin notice on plugin settings page to alert when wp-config.php is not writable.

= 1.0.0

Initial release
