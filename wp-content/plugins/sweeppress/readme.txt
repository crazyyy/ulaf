=== SweepPress: Website Cleanup and Optimization ===
Contributors: GDragoN, freemius
Donate link: https://www.dev4press.com/plugins/sweeppress/
Tags: dev4press, database, cleanup, clean, optimize
Stable tag: 6.4.4
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Remove unused, orphaned, duplicated data in your WordPress website using 55+ sweepers, manage and clean Options table, optimize database.

== Description ==
SweepPress is an easy-to-use WordPress plugin built around 52 different Sweepers for database cleanup (the Pro version has 57), support for WP-CLI, and WP REST API to perform the cleanup operations. The plugin also features a powerful WordPress Options database table management panel.

= Quick Video Overview =
https://www.youtube.com/watch?v=vmy2XbjpYoI

= Options Management =
The WordPress Options database table holds settings for WordPress Core, all the plugins, and themes. This table can grow significantly, especially since records are not auto-deleted when removing a plugin or theme. SweepPress has Sweepers that deal with transient cache records (stored in this table), and now, since version 3.0, it has a full Management panel where you can see all the records in the `Options` table, detect source for the record and status to help you decide if you should delete the record or not.

SweepPress has two methods to detect the source of the record, and it will mark all the recognized records. This panel doesn't show transient records (used for cache) or WordPress options (deleting these options is a terrible idea). There is also a Quick Tasks subpanel, which can help you quickly remove options fitting into few standard predefined criteria.

**Options deletion process is not automatic! No matter how precise options usage detection is, you need to verify and confirm that you want to delete one or more records.**

[SweepPress Pro](https://www.dev4press/plugins/sweeppress/) It also has advanced management panels for all the WordPress metadata and multisite Sitemeta database tables, including options to track the usage of meta-records, and remove all the entries from the database associated with specific meta.


= Modes of Sweeping =
* **Auto Sweep**: available on the dashboard, running many (not all) sweepers without user input.
* **Quick Sweep**: available on the dashboard, allowing the sweepers to be selected.
* **Full Sweep**: available on its panel, with detailed information about sweepers and no limit run.
* **CLI Sweep**: the plugin supports WP-CLI and adds commands to run all the sweepers with the command line.
* **REST API Sweep**: the plugin supports WP REST API and allows sweeping via the new REST endpoints.

All sweepers use optimized SQL queries to find the data for removal, which is also done using SQL queries. This is the fastest and most efficient way to remove a large amount of data quickly.

= List of Sweepers =
* Posts: Auto Drafts
* Posts: Spam Content
* Posts: Trashed Content
* Posts: Posts Revisions
* Posts: Posts Orphaned Revisions
* Posts: Draft Posts Revisions
* Posts: Postmeta Locks
* Posts: Postmeta Last Edits
* Posts: Postmeta Oembeds
* Posts: Postmeta Old Data
* Posts: Postmeta Orphans
* Posts: Postmeta Duplicates
* Comments: Spam Comments
* Comments: Trashed Comments
* Comments: Unapproved Comments
* Comments: Orphaned Comments
* Comments: Comments User Agents
* Comments: Commentmeta Orphans
* Comments: Commentmeta Duplicates
* Comments: Pingbacks Cleanup
* Comments: Trackbacks Cleanup
* Comments: Akismet Meta Records
* Terms: Terms Orphans
* Terms: Termmeta Orphans
* Terms: Termmeta Duplicates
* Terms: Term Relationships Taxonomy Orphans
* Terms: Term Relationships Objects Orphans
* Terms: AMP Validation Errors
* Terms: Unused Terms
* Users: Usermeta Orphans
* Users: Usermeta Duplicates
* Options: Expired Transients
* Options: RSS Feeds
* Options: All Transients
* Options: CRON Jobs
* Options: Unhooked CRON Jobs
* Network: Expired Transients
* Network: All Transients
* Network: Inactive Signups
* Database: Optimize Database Tables
* Database: Repair Database Tables
* ActionScheduler: Log Entries
* ActionScheduler: Orphaned Log Entries
* ActionScheduler: Failed Actions
* ActionScheduler: Completed Actions
* ActionScheduler: Canceled Actions
* bbPress: Replies Orphans
* BuddyPress: Activity Meta Orphans
* BuddyPress: Groups Meta Orphans
* BuddyPress: Messages Meta Orphans
* BuddyPress: Notifications Meta Orphans
* Elementor: Trashed Submissions

= WP-CLI and WP REST API Support =
The plugin registers new CLI commands for running sweep operations from the command line (WP-CLI is required). It also registers the REST API endpoint for the same purpose: running remote sweep operations (only for the administrator role!). CLI and REST API support can be enabled through plugin settings.

= Action Scheduler Support =
The plugin can clean up the ActionScheduler tables. These tables are used by the Action Schedule plugin and components developed for WooCommerce but also in many other WordPress plugins, including WP Rocket and Rank Math. For these sweepers to be visible, at least one plugin using those tables needs to be active.

= Additional Features =
* Sweeper File Log: log every sweeper execution into a log file with all sweeper/removal SQL queries executed by each used sweeper.

= Special Notice =
The plugin will show the backup reminder notice by default (and it can be disabled) on every page. It is essential to understand that once the plugin deletes data, it can't be restored. So, if you change your mind later, it is important to make the backup before the data removal. The SweepPress plugin is not responsible for any data loss — ensure backups! In SweepPress Pro, there is a Backup Data feature that will create partial backups from data before it is removed and store it into SQL export files.

= Plugin Home Page =
* Learn more about the plugin: [SweepPress Home Page](https://www.dev4press/plugins/sweeppress/)
* Plugin knowledge base: [SweepPress on Dev4Press](https://www.dev4press.com/kb/product/sweeppress/)
* Support for the Lite version: [Support Forum on Dev4Press](https://support.dev4press.com/forums/forum/plugins-lite/sweeppress/)

= SweepPress Pro =
SweepPress Lite edition is a fully functional plugin with no limits to its operations. But SweepPress Pro contains a lot of additional features not available in the Lite version:

* Backup the data before it is deleted into SQL export files. Includes a management panel for Backup files.
* Management for ALL the WordPress Metadata tables: `postmeta`, `termmeta`, `commentmeta`, `usermeta` and `blogmeta`.
* Management for the WordPress Multisite Sitemeta table: this table is equivalent of the Options table, but for all the blogs in the multisite.
* Track and Detect use of metadata and allow removal of any meta-records for all supported metadata tables.
* Preview duplicated metadata records for all supported metadata tables. Preview data for some sweepers.
* Create and manage Sweeper Jobs: create custom background sweeper jobs to run at a specific date or as a recurring job.
* Control and track WordPress CRON jobs: list all the WordPress CRON jobs, track and display the source, and more.
* Sweeper Monitor: monitor the website for cleanup daily or weekly, and send notifications when the sweeping limit is reached.
* Database Tables: an overview of all tables in the database with source information and various actions for better control.
* GravityForms Support: three additional sweepers to remove all trashed forms, spammed, and trash entries by form.
* CLI Command to list CRON jobs: list all registered WP CRON jobs via the command line.

Future updates will include more exclusive Pro features.

== Installation ==
= General Requirements =
* PHP: 7.4 or newer

= PHP Notice =
* The plugin doesn't work with PHP 7.4 or older versions.

= WordPress Requirements =
* WordPress: 6.0 or newer
= Requirements Notices =
* The plugin doesn't work with PHP 7.3 or older versions.
* The plugin doesn't work with WordPress 5.9 or older versions.

= Basic Installation =
* Plugin folder in the WordPress plugins should be `sweeppress`.
* Upload the `sweeppress` folder to the `/wp-content/plugins/` directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Plugin adds a new top-level menu called 'SweepPress.'
* Check all the plugin settings before using the plugin.

== Frequently Asked Questions ==
= Where can I configure the plugin? =
The plugin adds a top-level 'SweepPress' panel in the WordPress Admin menu.

== Changelog ==
= Version: 6.4.4 (2025.11.12) =
* Edit: Freemius SDK 2.13

= Version: 6.4.3 (2025.09.04) =
* Edit: Freemius SDK 2.12.1

= Version: 6.4.2 (2025.07.04) =
* Edit: several minor improvements and tweaks
* Fix: potential division by zero issues in 3 places

= Version: 6.4.1 (2025.06.04) =
* Edit: Dev4Press Library 5.4
* Edit: Freemius SDK 2.12.0

= Version: 6.4 (2025.03.31) =
* Edit: more refactoring of the database related code organization
* Fix: missing headings on the `Sweep` panel with estimate on demand active

= Version: 6.3.1 (2025.03.14) =
* Edit: updated POT file with latest strings for translation

= Version: 6.3 (2025.01.29) =
* New: sweeper: elementor trashed submissions
* Edit: few small updates to the plugin loading process
* Edit: various improvements to the settings display
* Edit: Dev4Press Library 5.3
* Fix: minor issue getting real database table name
* Fix: minor issue with preparation class calculations

= Version: 6.2 (2024.12.27) =
* New: tested and compatible with `PHP` 8.4
* New: tested and compatible with `WordPress` 6.7
* New: loading of `Dev4Press Library` via Composer
* New: loading of `Freemius Library` via Composer
* Edit: Dev4Press Library 5.2.2
* Fix: sweeper for posts revisions issue with number of days to keep
* Fix: minor issue with the file scanner directory processor

= Version: 6.1 (2024.10.24) =
* New: tested and compatible with `PHP` 8.4 RC 2
* New: do action when deleting options with information about the deletion
* Edit: Freemius SDK 2.9

* New: tested and compatible with `PHP` 8.4 RC 1
* New: sweeper: bbpress orphaned replies
* New: sweeper: unused terms by taxonomy
* New: sweeper: term relationships taxonomy orphans
* New: sweeper: term relationships objects orphans
* New: sweeper: remove postmeta duplicated records
* New: sweeper: remove commentmeta duplicated records
* New: sweeper: remove termmeta duplicated records
* New: sweeper: remove usermeta duplicated records
* New: options management: subpanel quick cleanup tasks
* New: options management: filter by the autoload status
* New: options management: bulk enable/disable of autoload
* New: options management: filter options by any known source
* New: options, sitemeta and metadata: use centralized `Removal` class
* New: options, sitemeta and metadata: deletion support simulation mode
* New: options, sitemeta and metadata: support logging queries
* New: expanded `Sweepers` panel to show the status of Backup flag
* New: expanded sweeper objects with the backup flag
* New: sidebar banner to show if the simulation mode is active
* New: option to control days to keep notices on `Sweep` panel
* New: dashboard database statistics show the percentage of free space
* Edit: improvements to the display of the autoload options
* Edit: modernized the JavaScript code to use arrow functions
* Edit: optimized JavaScript code making the files smaller
* Edit: many improvements to the options, sitemeta and metadata handling
* Edit: expanded list of supported plugins in `Storage` for detection
* Edit: changed the auto sweep flag for several sweepers
* Edit: optimizations to the code used to delete options
* Edit: various improvements and changes to the `Removal` class
* Fix: various small issues with the styling on Sweep panel
* Fix: options panel actions to enable/disable autoload not working
* Fix: hardcoded database table names in Terms AMP Errors sweeper
* Fix: option to show days to keep for sweepers not working
* Fix: several small typos or wording issues in the options descriptions
* Fix: several broken links to the Knowledge Base

Full changelog: [SweepPress Changelog](https://www.dev4press.com/plugins/sweeppress/changelog/)

== Upgrade Notice ==
= 6.3 =
Added 1 new sweeper. Various updates and improvements.

= 6.2 =
Various updates and improvements.

= 6.1 =
Various updates and improvements.

= 6.0 =
Added 8 new sweepers. Options Management Quick Tasks. Many more updates, tweaks, and fixes.

== Screenshots ==
* Main plugin dashboard with status
* Quick Sweep Panel available on dashboard
* Part of the main Sweep panel
* Few more sweepers on the Sweep panel
* WordPress Options Management
* Options Management Quick Tasks
* List of all sweepers with scope and availability
* Settings to enable WP-CLI and REST API support
* List of sweepers via CLI command
