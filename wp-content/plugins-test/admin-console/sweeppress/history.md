# SweepPress Pro

## Changelog

### Version: 6.1 / october 24, 2024

* **new** tested and compatible with `PHP` 8.4 RC 2
* **new** do action when deleting options with information about the deletion
* **new** [PRO] do action when deleting sitemeta with information about the deletion
* **new** [PRO] do action when deleting metadata with information about the deletion
* **edit** [PRO] few more changes to the Pro code organization
* **edit** [PRO] improved code organization for sitemeta handling
* **edit** [PRO] improved code organization for metadata handling

### Version: 6.0.1 / october 11, 2024

* **fix** wrong JavaScript reference for Metadata confirmation handler
* **fix** several broken links to the Knowledge Base

### Version: 6.0 / october 8, 2024

* **new** tested and compatible with `PHP` 8.4 RC 1
* **new** [PRO] backup data into `SQL` export file before removal
* **new** [PRO] backup data supports most of the sweepers
* **new** [PRO] backup data supports options and sitemeta management
* **new** [PRO] backup data supports metadata management
* **new** [PRO] backup data can export to compressed GZIP or BZIP2 formats
* **new** [PRO] sweep backups panel for managing export SQL files
* **new** [PRO] sweep backups panel includes an option to download exports
* **new** [PRO] metadata panels to preview duplicated meta records
* **new** [PRO] sweeper panel shows the links to preview data for some sweepers
* **new** [PRO] each sweeper supporting backup shows the archive flag
* **new** [PRO] settings panel to control the Backup Data feature
* **new** [PRO] admin bar settings to enable more sweepers for a quick sweep menu
* **new** [PRO] metadata management panels show an option to clear cache
* **new** [PRO] uses `mysqldump` library v2.12 to create export files
* **new** sweeper: bbpress orphaned replies
* **new** sweeper: unused terms by taxonomy
* **new** sweeper: term relationships taxonomy orphans
* **new** sweeper: term relationships objects orphans
* **new** sweeper: remove postmeta duplicated records
* **new** sweeper: remove commentmeta duplicated records
* **new** sweeper: remove termmeta duplicated records
* **new** sweeper: remove usermeta duplicated records
* **new** options management: subpanel quick cleanup tasks
* **new** options management: filter by the autoload status
* **new** options management: bulk enable/disable of autoload
* **new** options management: filter options by any known source
* **new** options, sitemeta and metadata: use centralized `Removal` class
* **new** options, sitemeta and metadata: deletion support simulation mode
* **new** options, sitemeta and metadata: support logging queries
* **new** expanded `Sweepers` panel to show the status of Backup flag
* **new** expanded sweeper objects with the backup flag
* **new** sidebar banner to show if the simulation mode is active
* **new** option to control days to keep notices on `Sweep` panel
* **new** dashboard database statistics show the percentage of free space
* **edit** [PRO] expanded list of supported plugins for CRON jobs detection
* **edit** [PRO] the Pro version CSS code moved into an own file
* **edit** [PRO] the Pro version JavaScript code moved into an own file
* **edit** improvements to the display of the autoload options
* **edit** modernized the JavaScript code to use arrow functions
* **edit** optimized JavaScript code making the files smaller
* **edit** many improvements to the options, sitemeta and metadata handling
* **edit** expanded list of supported plugins in `Storage` for detection
* **edit** changed the auto sweep flag for several sweepers
* **edit** optimizations to the code used to delete options
* **edit** various improvements and changes to the `Removal` class
* **fix** various small issues with the styling on Sweep panel
* **fix** options panel actions to enable/disable autoload not working
* **fix** hardcoded database table names in Terms AMP Errors sweeper
* **fix** option to show days to keep for sweepers not working
* **fix** several small typos or wording issues in the options descriptions

### Version: 5.5 / september 11, 2024

* **new** sweep panel option to clear estimation cache
* **new** for each sweeper shows `Refresh` link to reload
* **new** show the limit of the days to keep for affected sweepers
* **new** CRON jobs identification can use regular expressions
* **edit** expanded detection list for CRON jobs
* **edit** many improvements to the PHP code structure
* **edit** improvements to the CRON jobs display
* **edit** few optimizations in the main JavaScript file

### Version: 5.4.1 / september 4, 2024

* **edit** changes to the `Information` class
* **edit** updated Library font CSS file

### Version: 5.4 / september 3, 2024

* **new** tested and compatible with `PHP` 8.4 Beta 4
* **new** many major changes to the code organization
* **new** sweeper log feature now available in the lite edition
* **new** non-licensed installation works as a lite edition
* **edit** updated some plugin system requirements
* **edit** improvements to the license handling for Pro features
* **edit** many updates related to the `Library` changes
* **edit** Dev4Press Library 5.1

### Version: 5.3 / july 3, 2024

* **new** sweeper: remove AMP validation error terms
* **edit** expanded list of supported plugins in `Storage` for detection
* **edit** expanded list of supported plugins for CRON jobs detection
* **edit** Dev4Press Library 4.9.2

### Version: 5.2 / june 13, 2024

* **edit** expanded list of supported plugins in `Storage` for detection
* **edit** improved panel CRON filtering to order sources
* **edit** updates to the license code validation
* **edit** Dev4Press Library 4.9.1
* **fix** panel CRON filter by Unknown is not working
* **fix** few CRON detection elements have wrong name

### Version: 5.1 / april 26, 2024

* **new** options and meta `Storage` supports other plugins registering own data
* **new** `Metadata` tables now should number of empty records (meta value is empty)
* **new** notice for the options and metadata management with an option to hide it
* **edit** expanded list of supported plugins in `Storage` for detection
* **edit** options and meta monitor has few more exceptions for matching
* **edit** options and meta scanner uses improved regular expressions
* **edit** improved meta `Monitor` precision when it comes to Dev4Press plugins
* **edit** improvements to the `Tracker` and `Scanner` object
* **edit** changes to the main menu organization and names
* **edit** expanded list of recognized CRON job sources
* **edit** on plugin update, clear the scanner cached data
* **fix** several issues with filtering the missing and installed records
* **fix** minor issue with the `Preview` class and missing property

### Version: 5.0 / april 22, 2024

* **new** objects to handle WordPress `Options` and Sitemeta entries
* **new** panel for managing WordPress `Options`
* **new** panel for managing WordPress Multisite `Sitemeta`
* **new** panel for managing various WordPress Metadata tables
* **new** support for metadata management of all WordPress metadata tables
* **new** support for metadata management of coreActivity Logmeta
* **new** preview of any metadata key for any metadata table
* **new** integration with `DebugPress` plugin to preview serialized data
* **new** regex based tracker for plugins and themes meta and options
* **new** monitor based tracked for plugins and themes meta and options
* **new** quick access menu added to the WordPress `Admin Bar`
* **new** few options for quick sweeping inside the `Admin Bar` menu
* **edit** database panel table expanded with the `Source` column
* **edit** database panel table optimize action for any free space available
* **edit** reorganized plugin dashboard with new widgets structure and layout
* **edit** expanded list of detected CRON jobs for WordPress and plugins
* **edit** expanded database tables detection list to support more plugins
* **edit** improvements to the styling for the statistics panel
* **edit** purge tools panel have options for the storage entries removal
* **edit** purge tools panel includes additional information about the process
* **edit** various improvements to the statistics panel data display
* **edit** various changes and improvements to the plugin settings
* **edit** license code is now required to activate and use the plugin
* **edit** improvements to sweeper's loading process
* **edit** Dev4Press Library 4.8
* **fix** button for hiding the backup notice not working
* **fix** minor issue with the license validation

### Version: 4.2 / march 14, 2024

* **edit** updated system requirements for all the sweepers
* **edit** updated some of the admin menu items names and order
* **edit** expanded default list of recognized CRON jobs for plugins
* **edit** expanded default list of recognized CRON jobs for WordPress Core
* **edit** Dev4Press Library 4.7.1

### Version: 4.1 / february 3, 2024

* **new** tested and compatible with PHP 8.3
* **new** added `License` settings panel for license activation
* **edit** few changes to the plugin settings organization
* **edit** various small improvements to the plugin interface
* **edit** many small changes and cleanup to the PHP code
* **edit** Dev4Press Library 4.6
* **fix** several minor layout and styling issues on some panels
* **fix** few small issues with passing arguments in `GETBack` object

### Version: 4.0 / september 25, 2023

* **new** use website database size to change estimate method
* **new** sweeper: remove unhooked CRON jobs only
* **new** sweeper: buddypress activity meta entries
* **new** sweeper: buddypress groups meta entries
* **new** sweeper: buddypress messages meta entries
* **new** sweeper: buddypress notifications meta entries
* **new** option to control method for the sweeper estimation
* **new** option to have estimates include the index size
* **edit** many updates and tweaks to the sweeper panels
* **edit** core updates related to the new shared library
* **edit** changes to the way action scheduler queries are run
* **edit** Dev4Press Library 4.3.2
* **fix** potential problem with action scheduler queries

### Version: 3.9 / august 11, 2023

* **new** settings panel for performance-related options
* **new** estimate options to run estimates with size or without size estimation
* **new** cache estimates to avoid running estimates query too often
* **new** database dashboard box showing overall statistics for the database
* **new** tools panel for purge of the cache estimation results
* **edit** many improvements to the code style and formatting

### Version: 3.8 / june 17, 2023

* **new** cron: panel expanded with the Action column
* **new** database: support for some more plugins for tables detection
* **new** action executed when the sweeping process has completed
* **new** action executed when the CRON hook is executed manually
* **new** action executed when the CRON hook is deleted
* **edit** database: improved detection of the WordPress core tables
* **edit** cron: improved styling for the panel
* **edit** Dev4Press Library 4.2
* **fix** database: detecting WordPress core tables can include wrong tables
* **fix** few issues with input has not been properly sanitized

### Version: 3.7 / may 15, 2023

* **new** sweeper: actionscheduler log entries
* **new** sweeper: actionscheduler log orphaned entries
* **new** sweeper: actionscheduler failed actions
* **new** sweeper: actionscheduler completed actions
* **new** sweeper: actionscheduler canceled actions
* **new** dashboard shows count of sweepers that are currently disabled
* **edit** improved loading process for the sweepers
* **edit** improved database panel actions processing
* **edit** Dev4Press Library 4.1.1
* **fix** few wrong icons used for the plugin interface

### Version: 3.6 / march 28, 2023

* **new** sweeper: draft posts revisions
* **new** sweeper: postmeta \`\_wp\_old\_\*\` data records
* **new** constant and filter to disable DB optimize/repair sweepers
* **new** enhanced preparation of quick and auto sweepers on dashboard
* **edit** various tweaks to the sweeping estimations
* **edit** expanded information for the Auto Sweep block
* **edit** expanded information for the Quick Sweep block
* **edit** link knowledge base for some settings groups
* **edit** link knowledge base for some plugin panels
* **edit** changes to some plugin settings default values
* **edit** changes to the availability for some sweepers
* **edit** Dev4Press Library 4.0
* **fix** wrong calculations for the post revisions sweeper

### Version: 3.5 / march 10, 2023

* **new** sweeper: multisite wp\_signups table
* **new** CLI subcommand: list all the registered CRON jobs
* **edit** few more improvements in calculating estimates size
* **edit** expanded content displayed in WordPress Help panel
* **edit** CLI subcommands: improved information returned
* **edit** CLI command: now with main description included
* **edit** various minor improvements to the forms code
* **fix** REST API results: shows HTML tags for size estimate
* **fix** REST API endpoints: additional information for arguments
* **fix** CLI command list: shows HTML tags for size estimate column
* **fix** CLI subcommands: few problems with the help information
* **fix** CLI results: few issues with labels and formatting

### Version: 3.3 / march 3, 2023

* **new** support for sweeping data from gravityforms plugin
* **new** sweeper: gravityforms trash forms removal
* **new** sweeper: gravityforms trash entries removal
* **new** sweeper: gravityforms spam entries removal
* **new** options to control days to keep for gravityforms entries
* **new** file to log all the sweeps and related database queries
* **new** sweepers on sweep and job panels have new toggle to list affected tables
* **edit** improved data size estimate calculation for NULL values
* **edit** several optimizations to the main JavaScript file
* **edit** various styling improvements and tweaks
* **edit** improved CSS and JS minification process
* **edit** Dev4Press Library 3.9.3
* **fix** statistics logging puts Sweep panel results under Quick
* **fix** statistics panel filter is throwing fatal error on load
* **fix** comments by status sweeper not taking comment type into account
* **fix** changelog link from the what's new about panel not working

### Version: 3.1 / february 3, 2023

* **new** tested with WordPress 6.1
* **new** tested with PHP 8.1 and 8.2
* **edit** all grid panels improved with new library base class
* **edit** various small styling updates and improvements
* **edit** important updates to the main Database class
* **edit** Dev4Press Library 3.9.1
* **fix** missing input sanitation for the CRON panel

### Version: 3.0 / october 31, 2022

* **new** database control: optimize, repair and database tables overview
* **new** database control: detect tables belonging to some plugins
* **new** database control: detailed help information included
* **new** database control: truncate and drop actions for tables
* **new** sweeper: repair broken database tables
* **new** alternative methods for the database tables optimization
* **new** run analyze method after the database tables optimization
* **new** each sweeper includes a plugin version when it was added
* **edit** updated information for some sweepers
* **edit** database status code with better views exclusion process

### Version: 2.0 / june 6, 2022

* **new** sweeper monitor - daily and weekly with notifications
* **edit** improved statistics panel collected data display
* **edit** improved the CRON object loading process
* **edit** expanded list of default CRON jobs for WordPress
* **fix** few issues with the CRON jobs detection
* **fix** potential division by zero issue with the size calculations
* **fix** problem with the uppercase database table names

### Version: 1.4.1 / may 29, 2022

* **fix** sweepers missing with WordPress 6.0
* **fix** sweepers missing with ClassicPress 1.4

### Version: 1.4 / may 18, 2022

* **edit** improved layout for the plugin dashboard
* **edit** Dev4Press Library 3.8
* **fix** responsive layout issue with auto sweep box

### Version: 1.3.1 / april 7, 2022

* **fix** plugin version issue clash with Lite edition

### Version: 1.3 / april 2, 2022

* **new** panel with list of all sweepers and where they can be used
* **edit** updated information in the Help areas for various panels

### Version: 1.2 / march 15, 2022

* **new** show list of affected tables more prominently inside help area
* **new** show percentage of the data to be removed compared to affected tables size
* **edit** many improvements to the sweepers core classes
* **edit** moved CRON object initialization to improve tracking
* **edit** improved query for calculation of tables to optimize
* **edit** calculation of tables to optimize takes index into account
* **edit** expanded help information for some sweepers
* **edit** expanded information for some plugin settings
* **edit** several minor styling and layout tweaks and improvements
* **edit** Dev4Press Library 3.7.4
* **removed** few unused and obsolete functions and methods
* **fix** minor issue with database fragmentation calculation

### Version: 1.1 / march 8, 2022

* **new** sweeper: akismet meta records removal
* **fix** minor issue with the translations format

### Version: 1.0 / march 3, 2022

* **new** first official version
