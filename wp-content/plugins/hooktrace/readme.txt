=== HookTrace - Trace Hooks with Precision ===
Contributors: smilingsyntax, codemadan
Tags: debugging, hooks, trace, hook trace, profiling
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cross-Plugin Debug & Trace Recorder - Records and visualizes hook execution order for WordPress developers.

== Description ==

HookTrace is a developer observability tool that records and visualizes the runtime execution order of WordPress hooks, filters, and plugin initialization for a single page request.

**For Developers Only**

This plugin is designed exclusively for development and staging environments. It helps developers understand:

* Which hooks fired, in what order
* Which callbacks executed on each hook
* Callback priority and execution time
* Source plugin, theme, or core location
* Exact file and line number
* Plugin and theme load timeline

**Key Features:**

* **Hook List Tracking** - Records all hooks that fire during a page request with type and source information
* **Detailed Callback Inspection** - When a hook is selected, displays comprehensive callback information including priority, execution order, duration, file path, and source
* **Modern Modal UI** - Beautiful, searchable interface with filtering capabilities
* **Zero Performance Impact** - In-memory storage only, no database writes
* **Early Boot Support** - MU-plugin bootstrap captures hooks from the very beginning

**Requirements:**

* `WP_DEBUG` must be set to `true` in `wp-config.php`
* User must have `manage_options` capability (administrator)
* PHP 8.0 or higher

**Safety Features:**

* Automatically disables when `WP_DEBUG` is false
* Never runs for non-admin users
* Never modifies WordPress behavior
* Observational only - no code execution changes

== Installation ==

1. Upload the `hooktrace` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure `WP_DEBUG` is set to `true` in your `wp-config.php` file
4. (Optional) For early hook capture, copy `hooktrace-bootstrap.php` from the plugin directory to `/wp-content/mu-plugins/`

== Frequently Asked Questions ==

= Does this plugin work in production? =

No. This plugin is designed exclusively for development and staging environments. It requires `WP_DEBUG` to be enabled and will automatically disable itself if `WP_DEBUG` is false.

= Will this slow down my site? =

The plugin is designed with minimal overhead (5-10ms average). However, it should only be used in development/staging environments where performance is not critical.

= Do I need to install the MU-plugin bootstrap? =

The MU-plugin bootstrap is optional but recommended. It allows the plugin to capture hooks from the very beginning of WordPress initialization, including must-use plugins and early core hooks.

= Can I use this to debug plugin conflicts? =

Yes! The timeline view shows which hooks fire and which plugins are registering callbacks, making it easier to identify conflicts and execution order issues.

= Does this modify WordPress behavior? =

No. This plugin is purely observational. It never modifies hook execution, suppresses errors, or changes WordPress behavior in any way.

= What data is stored? =

No data is stored. All trace information is kept in memory for the current request only and is discarded after the page loads.

= How do I inspect a specific hook? =

Click on any hook in the list to see detailed information about all callbacks registered for that hook, including priority, execution order, duration, and source file location.

== Screenshots ==

1. Hook list view showing all hooks that fired, with search and filter options
2. Selected hook detail view showing all callbacks with priority, execution order, duration, and file paths
3. Color-coded badges indicating source (core/theme/plugin) and type (action/filter)

== Changelog ==

= 1.1.0 =
* Added local editor integration - Open files directly in your favorite IDE (VS Code, PhpStorm, Sublime Text, Atom, Cursor, Antigravity, or custom protocol)
* Added settings page (Tools → HookTrace) for configuring local editor and path mapping
* Added function filter for single hook tracing - Filter callbacks by function name
* Added execution statistics - Display min, max, avg, and total execution time for filtered functions
* Improved callback execution tracking - Each callback execution is now tracked separately with accurate timing
* Enhanced UI/UX - Single scroll area, sticky filter and stats, more compact callback items
* Improved labels - Full descriptive words instead of abbreviations (Priority, Execution Order, etc.)
* Added translation support - All strings are now translatable with POT file included
* Added dual editor links - Display both local editor and WordPress editor links when available
* Improved modal positioning - Aligned to top to prevent flickering with dynamic content

= 1.0.0 =
* Initial release
* Hook list tracking (all hooks that fire)
* Detailed callback inspection for selected hooks
* Modern modal UI with search and filtering
* Admin bar integration
* MU-plugin bootstrap support
* Color-coded badges for source and type identification
* Responsive design with smooth animations