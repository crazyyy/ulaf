=== JS Error Logger ===
Contributors: jfgmedia
Donate link: https://paypal.me/jfgui
Tags: dashboard widget, error reporting, debug, javascript, js
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.3.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Logs front-end javascript errors, and displays them in a dashboard widget

== Description ==

The plugin catches most JS errors, logs them, and displays them in a dashboard widget.

Here are some of its features:
<ul>
<li>
Except for the plugin settings, there is no database storage involved. Log is written in a ".log" file.
</li>
<li>
Display latest JS errors in a dashboard widget.
</li>
<li>
Refresh errors from the dashboard widget.
</li>
<li>
See the full error log on a separate page.
</li>
<li>
Ignore errors if the user agent contains a specific string.
</li>
<li>
Ignore errors if the error contains a specific string.
</li>
<li>
Ignore errors if the script url contains a specific string.
</li>
<li>
See which page and which script triggered the errors.
</li>
<li>
Choose the maximum amount of errors to log per page load.
</li>
<li>
Exclude logging errors from specific post types.
</li>
<li>
Choose how ajax calls are made.
</li>
</ul>

= Developer hooks and filters =
The plugin cleans the log every 24 hours, to only keep the last 100 entries.
You may use the "jserrlog_max_log_entries" WP filter to enable more or less entries, by returning an integer: `add_filter('jserrlog_max_log_entries',function(){return 200;})`

Alter error data:
You may use the "jserrlog_pre_insert_error" WP filter to modify the error data before it's inserted into the log file: `add_filter('jserrlog_pre_insert_error',function($error_data){return $error_data;})`

Trigger integrations:
You may use the "jserrlog_after_log" WP hook to trigger an action (Slack notification, etc.) after an error was logged: `add_action('jserrlog_after_log',function($error_data){//do something})`

Backup old errors:
You may use the "jserrlog_before_log_maintenance" WP hook to trigger an action (archive errors, etc.) before old errors are deleted: `add_action('jserrlog_before_log_maintenance',function($errors){//do something})`


= Multisite =
The plugin works with multisite. There's one error log per site.

== Installation ==

1. Visit the Plugins page within your dashboard and select "Add New"
2. Search for "JS Error Logger"
3. Click "Install"

== Screenshots ==

1. The JS Error Logger dashboard widget
2. The full error log from within the plugin settings
3. Some of the settings

== Upgrade Notice ==
Not available at the moment

== Changelog ==

= 1.3.1 =

* Tested up to Wordpress 6.9
* Renamed some template variables to ensure they can't be mistaken for global variables
* Changed some native PHP functions such as mk_dir, rm_dir, fopen, etc... to use WP_Filesystem

= 1.3 =

* Dropped support for PHP<7.4
* Added links to our other plugins in the settings area
* Addition of an admin notice
* Addition of 2 developer hooks and 1 filter

= 1.2 =
* Accessibility improvements
* Fix: Make sure the mu-plugin is also deleted when deactivating the plugin, and not just when uninstalling it
* Added 3 more settings to the UI that were previously only accessible through WP filters

= 1.1.11 =
* Fix: Properly dequeue the early loaded script in the front-end, if js error logging is not enabled

= 1.1.10 =
* Security hardening
* Documentation of variables for the translators
* Minor Fix: Ensure the mu-plugins directory is writable before creating the early loader, to avoid PHP warnings

= 1.1.9 =
* Fix: Properly dequeue the early loaded script in admin, if back end logging is not enabled

= 1.1.7 =
* Improvement: better update mechanism to make sure the mu-plugin also gets updated

= 1.1.6 =
* New: it is now possible to also log back end js errors

= 1.1.5 =
* Fix: An HTML attribute was escaped with wp_kses instead of esc_attr, which could lead to some display issues if double quotes were in the attribute text

= 1.1.4 =
* Fix: When the dashboard widget was initially empty, then refreshed, and an error was there, the "View" button was not clickable

= 1.1.3 =
* Fix: A change in the ID of the dashboard widget was preventing the "Refresh log" button from working properly

= 1.1.2 =
* Fix: Removed the "string" return type on rewrite_script_src to prevent potential issues

= 1.1.1 =
* Fix: issue with special character encoding
* Fix: Missing trailing slash during the creation of the "js-error-logger-log" directory
* Improvement: Plugin should detect the most popular caching plugins, and remind users to clear their cache after changing some settings

= 1.0 =
* Initial Release