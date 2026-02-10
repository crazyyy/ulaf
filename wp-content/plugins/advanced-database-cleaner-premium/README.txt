=== Advanced Database Cleaner - Premium ===
Contributors: symptote
Donate Link: https://www.sigmaplugin.com/donation
Tags: clean, database, optimize, performance, postmeta
Requires at least: 5.0.0
Requires PHP: 7.0
Tested up to: 6.9
Stable tag: 4.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Clean database by deleting orphaned data such as 'revisions', 'expired transients', optimize database and more...

== Description ==

Advanced Database Cleaner Premium is a complete WordPress optimization tool that helps you clean and optimize your database by removing unused data such as old revisions, auto drafts, expired transients, orphan options, orphan tables, orphan metadata, abandoned cron jobs and more.

It provides detailed previews, powerful filters, and automation to safely control what gets cleaned. Its unique scan system analyzes your database to detect the real ownership of tables, options, post meta, user meta, cron jobs and transients, making it much easier to identify true orphaned entries and safely remove leftovers left behind by deleted plugins or themes.

It also gives you clear insights into how your database evolves over time through built-in analytics, and lets you monitor plugin and theme activity to better understand when new data is created or when leftovers appear.

(<a href="https://sigmaplugin.com/downloads/wordpress-advanced-database-cleaner">Official website</a>)

== Changelog ==

= 4.0.6 – 28/01/2026 =
- Fix: Some SQL queries did not run when database tables had different collations in Multisite setups.
- Fix: The "Show value" modal did not appear for expired transients.
- Fix: Deleted items could reappear as "ghost" entries after switching tabs and coming back.
- Fix: Some UI elements were incorrectly hidden on frontend pages.
- Fix: Extra characters in some translations within the UK '.po' file.
- Fix: [Premium] After a scan completed, correct counts were shown but disappeared when switching tabs and returning.
- Tweak: In Trashed Posts, only WordPress core post types are now displayed to prevent accidental deletion of unexpected data.
- Tweak: Allow selecting items by groups under the "General Cleanup" tab.
- Tweak: Increase the maximum number of selectable items per page from 200 to 1000.
- Tweak: General improvements to code quality and styling.

= 4.0.5 – 17/01/2026 =
- Fix: The plugin left menu was unstable in some environments.
- Fix: Some filters did not correctly reflect the displayed data.
- Fix: Certain strings were not translated in Multisite REST responses.
- Fix: Some special usermeta entries in Multisite and custom table prefix setups were not correctly assigned to WordPress core.
- Tweak: Improved the General Cleanup page to reduce the number of REST requests for better performance.
- Tweak: Take into account the site_status_autoloaded_options_size_limit filter when displaying the autoload size warning.
- Tweak: Added bulk actions to the bottom of tables as well.
- Tweak: Added the ability to select multiple items using the Shift key.
- Tweak: Optimized loading of scan results from files for improved performance.
- Tweak: Optimized the calculation of non-scanned items for better performance.
- Tweak: Added plugin settings to the System Info page.
- Tweak: Unified the structure of installed add-ons data sent during Remote Scan.
- Tweak: Various improvements to code quality, security, and styling.

= 4.0.4 – 25/12/2025 =
- Fix: [Premium] Prevented license activation from being unintentionally removed after one week.
- Fix: Resolved style conflicts with other plugins.
- Fix: Corrected an issue where sorting usermeta by meta key returned empty results when the "duplicated" filter was applied.
- Tweak: [Premium] Removed the weekly license check cron job when uninstalling the plugin.
- Tweak: Refactored code to improve loading performance by caching data.
- Tweak: Added translatable strings and corrected some date-format inconsistencies.
- Tweak: Improved UI consistency across all tables.
- Tweak: Increased Database Rows Batch limit to 50,000 by default for better performance on large sites.
- Tweak: Added a refresh icon to the highlighted orange sections for easier counts refresh.

= 4.0.3 – 14/12/2025 =
- Fix: Improved compatibility with PHP 7.
- Tweak: Optimized the loading of the Post Meta module for large websites.
- Tweak: Highlighted preset filter section counters are now fetched via separate endpoints for better performance.
- Tweak: Optimized the duplicated meta module to improve performance.
- Tweak: Optimized the General Cleanup module for faster loading.
- Tweak: Overall performance improvements and internal code optimizations.

= 4.0.2 – 05/12/2025 =
- Fix: Conflict with another plugin injecting links into our plugin settings.
- Fix: Syntax error: unexpected '...' (T_ELLIPSIS), expecting ']'.
- Fix: Deletion of transients and expired_transients in multisite within the sitemeta table when the transient's site_id is invalid.
- Fix: Duplicate "squared" transients and expired transients being displayed.
- Tweak: Synchronize Axios timeout (React) with PHP max execution time to avoid early request timeouts.
- Tweak: In trashed comments, count only trashed comments and ignore comments belonging to trashed posts.
- Tweak: Use crc32 hashing to speed up detection of duplicate values.
- Tweak: General code cleanup and optimization.
- Tweak: [Premium] Added new WordPress-related items for improved identification.
- New: [Free] new setting allowing to control the number of items retrieved from the database per request for better performance.
- New: Choose between native WordPress functions or direct SQL queries for deleting items (new setting added).
- New: Items in the General Cleanup page are now loaded individually, so content appears immediately without waiting for all items.
- New: Items can now be deleted one by one in General Cleanup without reloading the entire list after each action.
- Compatibility: Tested with WordPress 6.9.

= 4.0.1 – 01/12/2025 =
- Fix: handling FS_METHOD ftpext in the file system class.
- Fix: sub-sites in Multisites were not loaded correctly

= 4.0.0 – 28/11/2025 =

- New: Duplicated post meta cleanup type.
- New: Duplicated user meta cleanup type.
- New: Duplicated comment meta cleanup type.
- New: Duplicated term meta cleanup type.
- New: oEmbed caches cleanup type.
- New: Estimated size to clean displayed for each cleanup type, plus a total freed-space summary before running a cleanup.
- New: Sorting capability added to cleanup preview tables (e.g. by name, date, size, site ID).
- New: Value viewer added to several cleanup types, displaying serialized or JSON data in raw or formatted views.
- New: Dedicated Post Meta Management module to list, sort, inspect, and clean post meta, including detection of unused and duplicated metadata.
- New: Dedicated User Meta Management module to list, sort, inspect, and clean user meta, including detection of unused and duplicated metadata.
- New: Dedicated Transients Management module to inspect, sort, and clean transients, with expiration tracking, detection of large transients, and control over their autoload status.
- New: Tables Management can now detect tables with invalid prefixes that do not belong to the current WordPress installation, with their visibility controlled from the Settings page.
- New: Options Management now includes a formatted value viewer, detection of large options, and warnings for heavy autoloaded options to help reduce autoload size.
- New: Cron Jobs Management now includes detection of cron jobs with no valid action/callback to help you clean them safely.
- New: All six management modules now detect items owned by WordPress core and Advanced Database Cleaner, making it clearer where data comes from.
- New: All six management modules now include an Attention Area that highlights priority issues, warns you about items requiring action, and helps you quickly identify and target them.
- New: Introduced a built-in error and exception logging system, allowing logs to be copied or downloaded for support or user-side investigations.
- New: Added tools to display the current database size, show or hide the plugin’s menu tabs, and access the WordPress debug log directly from the interface.
- New: Modern, fully responsive interface rebuilt with React for a smoother, faster, and more intuitive user experience.

- Enhanced: Cleaning process in the General Cleanup module now uses WordPress native deletion functions for deeper, hook-aware cleanup, with direct SQL deletion kept only as a safe fallback when required.
- Enhanced: Automation is now centralized into a unified module with a clearer creation/edit flow and consistent use of the local timezone for all schedules.
- Enhanced: Options, Tables, and Cron Jobs modules now display richer information with additional columns and more detailed data for each item.
- Enhanced: System Info is now far more detailed and can be copied or downloaded, making it easier to share environment details, diagnose issues, and assist users during support.
- Enhanced: Overall multisite support now provides clearer separation between network and site data and safer network-wide cleanup and optimization.
- Enhanced: Backend architecture migrated to a REST API–driven system for significantly faster interactions and navigation without page reloads.
- Enhanced: Numerous bugs and edge cases were resolved across all modules, resulting in more stable behavior and more reliable, effective cleaning operations.

- Premium: New - Action Scheduler completed actions cleanup type.
- Premium: New - Action Scheduler failed actions cleanup type.
- Premium: New - Action Scheduler canceled actions cleanup type.
- Premium: New - Action Scheduler completed logs cleanup type.
- Premium: New - Action Scheduler failed logs cleanup type.
- Premium: New - Action Scheduler canceled logs cleanup type.
- Premium: New - Action Scheduler orphan logs cleanup type.
- Premium: New - "Keep last X items" rule introduced, either per parent (e.g. keep 5 revisions per post) or globally (e.g. keep the last 10 pingbacks), in addition to the existing "keep last X days" rule.
- Premium: New - Introduced Remote Scan system that combines the local scan with our cloud-based detection engine and continuously curated ownership database to deliver near-perfect accuracy when identifying the true owners of tables, options, post meta, user meta, transients, and cron jobs.
- Premium: New - Added the ability to anonymously send your ownership corrections to improve our global detection database and refine ownership results for all users.
- Premium: New - "Keep last X items" rule now configurable inside scheduled tasks, in addition to the existing "keep last X days", for more advanced and safer automated cleanups.
- Premium: New - Introduced Database Analytics module with daily and monthly charts, raw data views, and per-table analytics (size evolution, rows evolution, daily change breakdown), including multi-table selection for comparative analysis.
- Premium: New - Introduced Addons Activity module that automatically tracks plugin and theme activations, deactivations, uninstalls, and theme switches in a color-coded timeline using your local timezone.
- Premium: New - Added multisite filters to the General Cleanup preview, allowing items to be filtered by site ID or site name so you can focus on a specific site in the network.
- Premium: New - Introduced per-automation event logs showing what was cleaned, when each task ran, and how many items were processed.

- Premium: Enhanced - Scan process fully redesigned for greater robustness and accuracy, combining an improved local scan with Remote Scan results.
- Premium: Enhanced - Scan flow now offers clearer insights, guidance, and error handling throughout each step of the process.
- Premium: Enhanced - "Belongs to" ownership column enriched with cloud-backed data across all management modules for more accurate owner detection.
- Premium: Enhanced - Detailed ownership info modal added, showing all known plugins/themes related to each item.
- Premium: Enhanced - Owner status indicators added (active, inactive, or not installed) to support deeper investigations.
- Premium: Enhanced - Filtering capabilities expanded across all management modules with new filters by size, value content, autoload, expiration, owner type (plugin, theme, WordPress core, orphan, unknown), duplicates, unused, large, not-yet-scanned, and more, including filtering specifically by a chosen plugin or theme.
- Premium: Enhanced - Multisite experience improved with clearer cross-site visibility, safer network-level operations, and tighter integration of ownership and analytics across all sites.
- Premium: Enhanced - Numerous bugs and edge cases were resolved across all premium features, resulting in more stable behavior and more reliable, effective cleaning operations.

= Previous changelog =
For previous changelog of versions before 4.0.0 check (<a href="https://docs.sigmaplugin.com/article/41-adbc-pro-changelog">here</a>).
