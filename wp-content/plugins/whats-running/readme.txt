=== What's running ===
Contributors: szepe.viktor
Donate link: https://szepe.net/wp-donate/
Tags: debug, debugger, debugging, developer, development, performance, profiler, profiling
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 1.9.1
License: GPLv2

Lists WordPress require() calls mainly for plugin code refactoring.

== Description ==

= Only for development! =

= Now with OPcache memory consumption support =

This plugin dumps the colorized filenames and memory consumptions or file sizes after the normal WordPress HTML output,
after the closing html tag.
This generates invalid HTML but gives you an overview of loaded plugins and the current theme.

*What's running* lists all files parsed and executed by the PHP engine.
It can be used for plugin or theme refactoring.

= Now you can highlight your own plugin and theme =

Provide a part of its path in wp-config.php:

`define( 'WHATS_RUNNING_HIGHLIGHT', 'wp-content/plugins/my-plugin' );`

= Color codes =

* your code is highlighted with YELLOW background
* plugin files are in BLUE
* themes files are in ORANGE
* files in the wp-includes directory are in GREEN
* files in the wp-admin directory are in GREY
* all other files are in RED
* VIOLET bar after the filename represents the file size ( 0.5 kB/px )
* DARK MAGENTA bar after the filename represents the OPcache memory consumption ( 1 kB/px )

= Links =

You can watch [WP core bug #28364](https://core.trac.wordpress.org/ticket/28364) to get more information
about [WordPress entry points](https://github.com/szepeviktor/WPHW/blob/master/wp-entry-points.md).

You can find some documentaion here what makes a WordPress plugin efficient:
https://github.com/szepeviktor/WPHW

Development of this plugin goes on on [GitHub](https://github.com/szepeviktor/whats-running).

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `wp-requires.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= It messes up my backend, frontend! =

After finishing the refactor, please deactivate and delete this plugin.

= How to highlight my code? =

Insert a line into wp-config.php with a part of your files' path:

`define( 'WHATS_RUNNING_HIGHLIGHT', 'wp-content/plugins/my-plugin' );`

= Use in production? =

Please do not!

= How to switch between file size and memory consumption? =

When OPcache status is available memory consumption is displayed,
otherwise the file size.

== Screenshots ==

1. This is an old screenshot. You can see the filenames after the admin footer.
2. This is the current screenshot of the plugin's output on the Dashboard. The colored bars represent the file sizes.
3. Lines highlighted by the WHATS_RUNNING_HIGHLIGHT constant.

== Changelog ==

= 1.9.1 =
* FIX: Always display pre tag
* NEW: Semver

= 1.9 =
* NEW: Added OPcache memory consumption support (DARK MAGENTA bar).

= 1.8 =
* Changed wrapper element to div with id "whats-running".
* NEW: Added feature to highlight your code.

= 1.7 =
* NEW: Added size in kB to the title of the file size bars.

= 1.6 =
* NEW Added file sizes and total file size.

= 1.5 =
* NEW: Added inline styles.
* Link to WP core bug to watch.

= 1.4 =
* FIX: Prevent running on non-AJAX media uploads (async-upload.php).
* Tested up to WordPress 3.9.

= 1.3 =
* FIX: On file uploads (async-upload.php) DOING_AJAX is defined late.

= 1.2 =
* NEW: Legend for the colors.
* Now you don't have to collapse the admin menu.

= 1.1 =
* FIX: Plugin name correction in readme.

= 1.0 =
* Initial release.
* Colorized output.
