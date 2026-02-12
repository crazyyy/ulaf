=== Notice TraceLog ===
Contributors: shuliakmaster
Tags: debug, backtrace, notices, developer, troubleshooting
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily display PHP backtraces when Notices occur. Designed for developers to quickly identify the source of early execution issues in WordPress.

== Description ==

Notice TraceLog is a lightweight developer tool that displays PHP backtraces when Notices occur, helping you identify and fix issues caused by early execution or translation loading problems. Useful for debugging during plugin and theme development.

== Screenshots ==

1. Default WordPress notice without clear source information.
2. Error displayed by the Notice TraceLog plugin (compact mode).
3. Detailed error display with full trace log to the source of the issue.
4. Example of a detailed notice with backtrace written to the debug log.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/notice-trace-log` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure `WP_DEBUG` and `WP_DEBUG_DISPLAY` are enabled in your `wp-config.php`.

Example configuration in `wp-config.php`:

`define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);`

== Changelog ==

= 1.0.0 =
Release basic functionality

= 1.0.1 =
* Added dependency checks for WP_DEBUG_DISPLAY and WP_DEBUG_LOG
* Improved error display in the browser
* Backtrace logging added to error log if WP_DEBUG_LOG is enabled (writes to the default log file)
* Enhanced admin area messages