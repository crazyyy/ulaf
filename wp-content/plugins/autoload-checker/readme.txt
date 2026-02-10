=== Autoload Checker ===
Contributors: sixaxis
Tags: autoloads, autoloaded data, clean up options, clean database, optimize database
Requires at least: 4.3
Tested up to: 6.9
Stable tag: 1.2
License: GPLv2+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Checks the autoloaded data size and lists the top autoloaded data entries sorted by size.

== Description ==
Autoload Checker is a WordPress plugin that helps you monitor the total size of the autoloaded data in the `wp_options` table of your database. Autoloaded data is data that is loaded on every page load, regardless of whether it is needed or not. This can affect the performance and speed of your site, especially if you had a lot of plugins installed.

Autoload Checker does not remove any autoloaded data from your database, it only checks the status and displays it in your WordPress dashboard. You can see the total size of the autoloaded data, as well as the top autoloaded data entries sorted by size. This can help you identify which plugins or themes are adding the most autoloaded data to your database, and decide if you need to optimize them or not.

Autoload Checker is easy to use and does not require any configuration. Just install and activate the plugin, and you will find the tool on Tools > Autoload Checker.

== Installation ==
1. Install Autoload Checker via the plugin directory.
2. Activate Autoload Checker.
3. Find the tool on Tools > Autoload Checker.

== Changelog ==

### 1.0

* Initial release

### 1.1

**Release date: 28/11/2024**

* Tweak: Changed the way the autoloads total size is counted, to also account for serialization overhead.
* Tweak: Increased the top autoloaded data entries to 30.

### 1.2

**Release date: 11/12/2025**

* Added: Accelera box
