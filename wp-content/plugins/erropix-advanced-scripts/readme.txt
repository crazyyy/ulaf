=== Advanced Scripts ===
Tags: custom code, custom css, custom php, sass, less
Requires at least: 5.0
Tested up to: 5.8.2
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to create advanced custom scripts and styles using an intuitive interface

== Description ==

Advanced Scripts is the best scripts manager on the market. Besides PHP, CSS, and Javascript, you can also write SCSS and LESS code, use custom hooks, and all of that in a better interface.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/erropix-advanced-scripts` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to `Tool > Advanced Scripts` and create your awesome scripts.

== Changelog ==

= 2.3.1 - November 10, 2021 =

-   Fix: the oxygen template condition options were empty on some sites

= 2.3.0 - November 9, 2021 =

-   NEW: Added script folders for better organization
-   NEW: Added conditions for date and time
-   NEW: Added a condition for the current Oxygen template
-   NEW: Added a condition for post ancestors (parents)
-   NEW: Added a UI indicator for scripts with conditions
-   NEW: Start the import by dragging files over the scripts list
-   NEW: Ability to change the JSON filename during export

= 2.2.0 - June 7, 2021 =
-   New: added a better safe mode notice in the editor screen
-   New: allowed customization of the script shortcode tag
-   New: Renamed the script location "Manually" to "Shortcode"
-   Fixed: delete confirmation popup do not show in full screen mode
-   Fixed: oxygen context condition issues
-   Fixed: mac keyboard shortcuts conflict in the Emmet extension

= 2.1.0 - April 21, 2021 =

-   New: built-in condition for the Oxygen Builder context.
-   New: wp_body_open hook option with polyfill for the Oxygen builder.
-   New: button to toggle full screen mode.
-   New: include private, draft, and future post status for post condition.
-   New: display created scripts list after import.
-   New: option to enable scripts during import.
-   Fixed: removed the automatic admin menu collapse.
-   Fixed: keyboard shortcut ⌘ + S not working on Mac.
-   Fixed: unlink operation not permitted warnings in PHP processor.
-   Fixed: scripts are not executed when the license is inactive.
-   Fixed: uncaught type error with PHP version prior to 7.2.
-   Fixed: icons CSS conflict with the Toolset Types plugin.

= 2.0.2 - January 17, 2021 =

-   Fixed a critical bug that broke websites in some managed WordPress hosts.

= 2.0.1 - January 4, 2021 =

-   New interface that focus on ease of use.
-   New code editor with better autocomplete and Emmet support.
-   Conditions manager to restrict script execution.
-   Import/Export that supports code snippets json files.
-   Drag and drop to reorder scripts.
-   Improved the safe mode to catch more errors.

= 2.0.0 - December 22, 2020 =

-   Fixed the editor highlighting issue for long code.
-   Removed Wordpress version from CSS and javascript URL scripts.
-   Fixed the safemode notice not appearing for error types.
-   Fixed PHP warning when Oxygen builder is inactive.

= 1.1.0 - Septenber 3, 2020 =

-   Major imporvements to the interface and user experience.
-   A user-friendly safe mode and allows you to recover in case of fatal errors.

= 1.0.0 - june 19, 2020 =

-   Initial release.
