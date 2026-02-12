=== Debug Log Viewer ===

Contributors: lysyiweb, freemius
Tags: log, debugging, error log, debug
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 2.0.5
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Effortlessly view, search, filter and manage your WordPress debug.log in the admin dashboard. Real-time monitoring and email alerts

== Tested up to ==
WordPress Version: 6.9
PHP Version: 8.4.1

== Description ==

Debug Log Viewer: Your Essential WordPress Debugging Tool

Tired of struggling to access and understand your WordPress debug.log file?  Debug Log Viewer simplifies WordPress debugging by providing a user-friendly interface to view, search, and manage your debug.log directly within your WordPress admin area.  It's the perfect solution for WordPress developers, site administrators, and anyone needing to quickly identify and resolve website issues.

Gain Real-Time Insights into Your WordPress Site Health

This plugin is designed to provide you with instant visibility into the inner workings of your WordPress website. By tracking errors, warnings, and deprecated function notices in real-time, Debug Log Viewer empowers you to proactively maintain a healthy and stable WordPress environment.

Key Features for Efficient WordPress Debugging:

* Real-Time Log Viewer:  Monitor your WordPress debug.log file in real-time, directly from your WordPress dashboard. No more hunting for files via FTP or cPanel!
* Easy Debug Log Access:  Access and view your full WordPress debug log within a clean and intuitive interface.
* Search and Filtering: Quickly find specific log entries with powerful search and filtering options. Filter by error type, keywords, or date (future feature) to pinpoint issues fast.
* Pagination: Navigate through large debug logs with ease using pagination, ensuring smooth performance even with extensive logs.
* Email Alerts for Critical Errors:  Get immediate email alerts when new errors are logged, allowing you to address critical issues before they impact your users (future feature: configurable severity levels).
* Flexible Settings Panel:
  * Control WP_DEBUG Constants:  Enable or disable WP_DEBUG and WP_DEBUG_LOG constants directly from the plugin settings, without editing wp-config.php.
  * Customize Logging Options: Configure your debug log settings to match your specific needs.
  * Tailor Your Error Tracking:  Personalize your debugging experience through plugin settings.
* Custom Log Path Support:  If you've defined a custom path for your debug.log file (e.g., using define( 'WP_DEBUG_LOG', ABSPATH . 'wp-content/logs/debug.log' );), Debug Log Viewer automatically detects and reads from it.

Benefits of Using Debug Log Viewer:

* Save Time and  Effort: Stop wasting time manually accessing and parsing your debug log file. Debug Log Viewer puts all the information you need at your fingertips within WordPress admin.
* Faster Error Detection: Real-time monitoring and email alerts help you catch errors as they happen, minimizing potential downtime.
* Simplified WordPress Troubleshooting:  Quickly identify the source of errors and warnings to streamline your WordPress troubleshooting process.
* Improved Website Stability: Proactive error monitoring and resolution contribute to a more stable and reliable WordPress website.
* User-Friendly Interface:  No coding skills required!  Debug Log Viewer is designed for ease of use, making debug log management accessible to everyone.

Debug Log Viewer Pro Features:

Upgrade to Pro for advanced debugging capabilities:

* Advanced Email Alert Customization: Choose specific error levels to monitor and reduce notification noise.
* CSV Export: Export debug log data for analysis, reporting, or sharing with team members.
* Custom Date Range Filtering: Filter log entries by specific time periods for targeted troubleshooting.

Who is Debug Log Viewer For?

* WordPress developers
* Website administrators
* Freelancers managing client sites
* Anyone who wants an easy way to monitor WordPress errors and improve website health

== Installation ==
1.  Installation: Install Debug Log Viewer from the WordPress Plugin Directory or upload the plugin zip file through your WordPress admin.
2.  Activation: Activate the plugin from your Plugins page.
3.  Access:  Navigate to the "Debug Log Viewer" menu in your WordPress dashboard to start monitoring your debug log.

== Screenshots ==

1. Main screen of the plugin interface showing the debug log viewer dashboard with error entries and filtering options.
2. Debug Log Viewer Dashboard:  The main view provides a clear and searchable interface for Browse your WordPress debug.log file within the WordPress admin.
3. Email Alerts setup page
4. Display Errors warning modal

== Changelog ==

= 2.0.5 =
* Adjustments for Pro plan modal
* Fixed alerts cron unshedule issue (thanks to Leo for the report)
* Freemius and WP-Config-Transformer update

= 2.0.4 =
* Security fix

= 2.0.3 =
* Minor UI fixes
* Added Ask for review banner

= 2.0.0 =
* Added datetime format selection (absolute/relative time display)
* Implemented custom date range filtering (Pro feature)
* Enhanced CSV export functionality (Pro feature)
* Introduced log file information popover with warnings and status
* Aadvanced email alerts, multiple error levels, up to 3 recipients (Pro feature)
* Increased log file size limit from 5MB to 10MB
* Security improvements and UI fixes
* Performance optimizations and code refactoring


= 1.4.3 =
* Updated design
* Added time display for the next notification
* Updated Freemius SDK to the latest version

= 1.4.2 =
* Callstack is now displayed in an accordion instead of a modal — allows viewing multiple entries simultaneously
* Added one-click copy functionality for Stack trace
* Updated Freemius SDK to the latest version
* Fixed issue with colVis extension, improved stability

= 1.4.1 =
* Updated design of Alert Emails
* Added chat for faster communication

= 1.4 =
* Added Custom Search Builder for advanced filtering conditions.
* Added a check for the debug file to determine if it is publicly accessible via a direct URL.
* Added a warning modal when enabling the "Display errors" toggle.

= 1.3 =
* Removed outdated and deprecated packages, reducing plugin size
* Parsing custom log records
* Added the ability to filter records by time intervals
* Updated Freemius SDK
* Updated DataTables
* Updated translations
* Various UI improvements

= 1.2.1 =
* Freemius and WP-Config-Transformer update

= 1.2 =
* Removed SSE implementation for live updates, because of unstable behaviour in some cases
* Implemented automatic live updates based on incremental AJAX ping requests.
* Added translations for the front-end phrases
* Implemented full-container width mode with ability to hide sidebar
* Intergated Freemius to become closer to users: contact us, forum links are added

= 1.1 =
* Fixed SSE streaming. Implemented incremental updates
* Decreased log reading limit from 10Mb to 5Mb
* Added ability to collapse Alert block in sidebar to make workspace more clear
* Fixed regular expression to parse datetime with long timezones

= 1.0.3 =
* Removed Toast plugin, used Bootstrap toasts instead
* Small refactoring

= 1.0.2 =
* Added assets (logo)

= 1.0.1 =
* Fix UUID generation

= 1.0.0 =
* Initial release
