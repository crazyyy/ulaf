=== KolorWeb Log Manager: cleaver debugging management ===
Contributors: vincent06
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html
Tags: wp log viewer, log, error, debug, error log
Requires at least: 3.9
Tested up to: 6.8
Stable tag: 1.1.6

Really one click enable/disable debugging, clear debug.log, search, sort, and filter errors. See new errors automatically without refreshing.

== Description ==

KolorWeb Log Manager makes debugging your WordPress site easy and stress free.

= Features =

Some features of this plugin.

* Admin bar widget
* Toggle debugging with a click
* Control debug.log filesize
* Smart download
* One click error filtering
* Clear log with one click
* Group and list views
* Sort entries by date
* Live auto refresh
* Realtime search
* Error color legend
* Custom errors manager
* Debugging status, size and last modified debug.log file
* Dashboard widget
* Persistent settings

= Admin Bar Widget =

The admin bar widget gives you real-time visible information such as debug log mode and error count wherever you are in wp-admin. You will always know when there are errors and can access the log viewer with one click for more details.

= Toggle Debugging =

Now you can enable / disable debugging with one click. It is no longer necessary to manually edit wp-config.php. Head over to the help section, follow the simple instructions to enable this feature, and you're good to go.

= Always keep an eye on the size of the debug.log file =

With this feature your debug.log file will never be larger than you want.

= Smart Download =

When you click to download debug.log, a version of the smart log will be downloaded. What is a smart register? It is a version of debug.log similar to the group view. Only the last unique entry for each error will be included. The exported file will be in html format and can be opened directly in the browser for quick reading, or in your favorite text editor.

This makes it much easier to parse the file, look for errors, and skip all redundancy due to the many possible occurrences of the same error.

= One Click Error Filtering =

You can filter errors with a single click on the error legend. Click on multiple error types to filter for multiple error types. Click a second time to deselect an error type. This feature works great even with custom errors.

= Clear Log =

Easily clear your debug.log file with one click.

= Group View =

Log entries are grouped, making it much easier to see each unique error. You can click to list the date and time when the error occurred. Grouped items can be sorted by newest or latest.

= List View =

All log entries are listed by date and time and can be sorted by newest or latest.

= Sort By Date =

Log entries can be sorted by date by newest or latest in either list or group views.

= Automatic Refresh =

Log automatically refreshes to display new errors.  No need to manually refresh the screen. However, there is a link to manually refresh if desired.

= Realtime Search =

Quickly search and find specific errors.

= Custom Errors =

Now you can easily define custom error messages.  When that error occures in your log file it can have it's own color coding, count and label.  Testing for custom errors or issues is now much easier.

= Error Color Legend =

Errors are color coded to make it easier to identify certain errors such as fatal, notices, warnings, deprecated and database.

= Debug Status =

Debugging status is located at the top of the viewer and admin bar to make it easy to see if debugging is enabled or disabled.

You can also see log size and last modified timestamp. This information automatically updates when changed.

= Dashboard Widget =

This widget gives you a quick summary regarding how many and what type of errors are in the log view. You can also access the log viewer with just one click.

= Persistent Settings =

Customize your log viewer to your heart's content. Your settings such as view, sort order, sidebar folding and more persist accross logins. When you login as your user, log viewer will be just like you left it.


== Installation ==

= From your WordPress Dashboard =

1. Click on "Plugins > Add New" in the sidebar
2. Search for "KolorWeb Log Manager"
3. Activate WP KolorWeb Log Manager from the Plugins page

= From wordpress.org =

1. Search for "KolorWeb Log Manager"
2. Download the Plugin to your local computer
3. Upload the kolorweb-log-manager directory to your "/wp-content/plugins/" directory using your favorite ftp/sftp/scp program
4. Activate KolorWeb Log Manager from the Plugins page

= Once Activated =

Click on "Tools > Log Manager" in the sidebar or "Log Manager" in the admin bar.

= Requirements =
* PHP 5.4.0 or greater
* Wordpress 3.9 or above


== Frequently Asked Questions ==

= How to I access the plugin? =

Once activated, you can access the plugin one of 3 ways:

1. Click on the "Log Manager" link in the admin bar
2. Click on "Tools" in the sidebar, then click on "Log Manager"
3. Click on "Dashboard", then from the dashboard click the link in the widget

= What is debug toggling? =

This feature allows you to enable/disable debugging with a click. No more manually updating WP_DEBUG in wp-config.php.

= How do I enable debug toggling? =

When the plugin is activated, the system tries to automatically set all the DEBUG constants in your wp-config.php
If toggle button does not appear in the settings means that your wp-config.php is not writable or not found in the default WordPress configuration path.
If wp-config.php is not writable, you will have to manually set the values for DEBUG constants and you will not be able to use the one-click debug toggle function. The plugin will continue to work without problems, but without the magic of this feature.

### STEP 1

1.1  To manually enable WordPress debugging you can paste these lines of code into the wp-config.php file

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

### STEP 2

2.1  To manually disable WordPress debugging you can paste these lines of code into the wp-config.php file

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

That's it. Refresh the page in the browser and you are ready to go. Happy coding with WordPress!


= How do I filter errors? =

Errors are grouped by error types. These error types are listed at the top of the viewer and color coded. You can click on any of these error to filter results by those errors.

You can click on multiple errors to limit results to only those errors. Click a second time to deselect that error type. This feature works great with custom errors.

= What are custom errors? =

In settings, you can define custom errors.  Each custom error has a key, label, and colors. The key is used in your custom error message in order to allow the log manager to define and manage your custom errors. The label and color are used to easily filter and categorize the error while browsing the log.

= How do I use custom errors? =

When you write an error to the log, you have to start the error message with a # and the custom error key followed by a :.

Example: If you defined a custom error with a key: my-custom-error

In your code: error_log( '#my-custom-error:  The error message goes here' );

= Do I need to refresh the log viewer? =

No. The log manager will automatically refresh and display new entries every 15 seconds by default. This feature is implemented using AJAX to avoid reloading the entire page.

= What is Group View? =

In Group View similar error entries are grouped together making it much easier to see unique errors and when they happened. Click on show details to view all the dates of the events that generated the error.

= What is the count in the admin bar? =

The count shown in the admin bar represent the number of unique errors in the log. So if there are 10 entries for the same error, it will only count as 1.

= What is sidebar folding? =

By default the sidebar will be folded when the log viewer is active. This increases the viewing. To disable, or toggle this behavior, click on settings then "Fold sidebar ..."

= What is the minimum length for a search query? =

When using the realtime search, the search query must be at least 3 characters or more in length.

= Where can I report bugs? =

Report bugs and suggest ideas at:  https://wordpress.org/support/plugin/kolorweb-log-manager/


== Screenshots ==

1. Dashboard Overview - An overview of the main error log page
2. Grouped view - Grouped view makes it easier to analyze errors and debug code
3. Automatic real-time check for new errors - It is not necessary to update, the new errors will be displayed automatically and thanks to the widget in the top bar, you can keep an eye on the errors in each page of the backend
4. Realtime search - Makes finding what you are looking for super easy
5. One click filter errors - Click error types to filter results. Only see what you need
6. Settings Pane Details - Customize your experience in one place
7. Custom Errors Pane Details - Manage your custom mistakes quickly and comfortably
8. Help section - Have questions? Get answers


== Changelog ==

All notable changes will be tracked in this change log.

= 1.1.6 =

* Enforce user capability check on AJAX actions.

= 1.1.5 =

* Tested Up to WordPress 6.7
* Unload unnecessary CSS and JS in Customizer Preview Mode.
* Some readme.txt adjustments
* Plugin Check (PCP) test passed.

= 1.1.4 =

* Dependency Update
* Fix: Load React only if not loaded by other plugins. This fixes some loading issues with plugins that load React unconditionally such as Yoast SEO.

= 1.1.3 =

* Dependencies Update

= 1.1.2 =
Release date: 2022-05-27

* Tested up WP 6.0

= 1.1.1 =
Release date: 2022-03-13

* Feature:
	* A field has been introduced in the settings panel to set the limit on the number of alerts to be taken into account on the debug.log file. The default value set to 0 displays all lines in the file. With this feature your debug.log file will never be larger than you want.

* Fix:
	* Fixed some issues with custom error management
	* Fixed the error counter in the admin bar to reflect the currently enforced view. Previously it always counted errors in grouped mode, even when the view was set to list.
	* Typo fix into readme.txt file

* Improvements
	* Improved visibility of errors in the widget

= 1.1.0 =
Release date: 2022-03-09

* Feature:
	* Really One click automatic enable/disable debugging status

* Hook
	* Added filter kwlm_wp_config_file_path

= 1.0.0 =
Release date: 2022-03-05

* Feature:
	* One click downloading of smart log file
	* One click error filtering by clicking error legends
	* Smart downloads include only the latest unique entries.  Duplicates are removed to reduce filesize and make reading the file easier
	* Persist selected view, sorting across login sessions
	* Settings pane added for easy management of viewer settings
	* Add and edit custom error types
	* Ability to limit who can see WP Log Viewer
	* Dashboard widget supports custom errors
	* Debug simulation mode so when debug status can not be determined, user can still browse debug log if present

* Hook
	* Added filter kwlm_user_authorized
	* Added filter kwlm_can_download_log
	* Added filter kwlm_show_dashboard_widget
	* Added filter kwlm_show_adminbar_widget

* UI:
	* WordPress sidebar is folded to increase viewer space
	* Sidebar is sticky so actions are always present when scrolling
	* Updated the header to display more error messages and to use less vertical space
	* Display count for each error type
	* Smarter display of error legends. Only legends with errors are displayed
	* Search query is not displayed under search bar
	* Increased error message area to display more horizontally which will reduce scrolling
	* Made error message more readable by removing line number and file path
	* Reorganized error details (type, line number and file path) to make it more space efficient
