=== Ultimate DebugBar ===
Contributors: avram
Donate link: https://paypal.me/avramator
Tags: debugging, queries, slow, database, sql, hooks, debug
Requires at least: 4.5
Tested up to: 5.8
Requires PHP: 7.1
License: MIT
Stable tag: trunk
Version: 0.2

Ultimate debug bar for your Wordpress website.

== Description ==

Ultimate debug bar for your Wordpress website. On each page of your website a neat debug bar will be displayed with the following info:

* PHP version
* Request execution time
* Request memory consumption
* Wordpress-related PHP constants defined in your wp-config.php file
* All website options (wp_options)
* Request data
* Timeline (waterfall) where you can see which portion of execution takes too much time
* List of all hooks triggered on the page whose execution time exceeds 1ms, along with some stats
* List of all database queries performed on the page, along with their source and execution time
* Last 100 lines of your debug.log file (if it exists in wp-content)
* List of loaded PHP files (categorized)

Besides that, it will track request data/hooks/db queries/mem. consumption in AJAX calls too (beta).

This plugin is meant to be used during development and/or debugging only. Only administrators and network administrators (in multi-site environment) can see the debugbar, unless the `WP_DEBUG` is set to `true`. In that case, everyone will see the debugbar.

Try it instantly on [tastewp.com](https://tastewp.com/new?pre-installed-plugin-slug=ultimate-debugbar&redirect=%2F&ni=true)!

== Installation ==
Use built in plugin installer or unzip ultimate-debugbar.zip in your wp-content/plugins and activate the plugin through the dashboard.

== Frequently Asked Questions ==
Q: AJAX requests are not working when plugin is active
A: Your server is blocking Ultimate Debugbar. If you're running nginx, (check this)[https://stackoverflow.com/questions/23844761/upstream-sent-too-big-header-while-reading-response-header-from-upstream].

Q: AJAX requests are not being diplayed in the debugbar
A: Plugin is trying to send too much data in the headers. I'm afraid there's no solution for this yet.

Q: How do I disable tracking AJAX requests?
A: Add the following code to your `wp-config.php`:

`define('ULTIMATE_DEBUG_AJAX', false);`

== Screenshots ==
1. List of all database queries with their timings
2. List of (slow) hooks lasting over 1ms in an AJAX request
3. Deserialized/formatted config options
4. Timeline (waterfall) of WP execution (TTFB)

== Changelog ==

**0.2**
- updated debugbar (requires PHP 7.1 now)
- added plugin icon

**0.1**
- initial release
