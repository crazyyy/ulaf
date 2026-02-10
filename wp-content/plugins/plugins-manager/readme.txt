=== Plugins Page Tweaker ===
Contributors: webtemyk
Tags: plugins, customize, plugins page, wp plugins, git
Requires at least: 5.0
Tested up to: 6.5
Stable tag: trunk
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The plugin adds icons on the plugins page and other tweaks, such as displaying the GIT branch

== Description ==

= It is customizes the plugins page: =
* Show a plugins icons
* Displays the changelog in the update notification
* The plugin list also displays logos and icons if the plugin is loaded via GIT. The update notification for these plug-ins is reduced so as not to distract attention from updating other plug-ins.
* Adds sorting of plugins on the fly: Active first/Not active at first
* In Wordpress 5.5+ changes the Auto-update column to small neat checkboxes
* The "Delete" plugin link is now active for activated plugins. It allows you to immediately deactivate and delete the plugin.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugins-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==
= If I accidentally deactivated and removed the plugin =
Deleted plugins cannot be restored. However, most plugins do not remove their settings, so you can reinstall it

== Screenshots ==

1. All plugin improvements are marked with a red line
2. Plugins that are not in the WordPress repository are marked with icons with the first letter of the plugin name to make it easier to search for plugins in the list.
3.v Plugin Settings page

== Changelog ==

= 1.4.5 =
* Compatibility with WP 6.5
* Change plugin name

= 1.4.4 =
* Fix: Shows error, when plugin icon is empty

= 1.4.3 =
* Fix: The plugins changelog was displayed in full, instead of displaying only the latest version

= 1.4.2 =
* Add: Multisite support

= 1.4.1 =
* Fix: Error on frontend

= 1.4.0 =
* The plugin is rewritten using a [WT Plugin Template](https://github.com/temyk/wt-plugin-template)
* Added a settings page where you can manage all the functions of the plugin.
* Added icons for plugins that are not in the WordPress repository
* Added the display of the GIT repository branch (slightly slows down the loading of the plugins page)
* Fixed removal of activated plugins with one button. Now there are no fatal errors
* Compatibility WP 5.8

= 1.3.0 =
* Rename the plugin from "Plugins Manager" to "WP Plugins Page Customize"
* In Wordpress 5.5+ changes the Auto-update column to small neat checkboxes

= 1.2.6 =
* Add settings page
* Show changelog in update notices
* Fix bug after upload plugin

= 1.2.2 =
* Fix git icon

= 1.2.1 =
* Add icon if the plugin is loaded via GIT

= 1.2.0 =
* Performance has been improved.
* Added sorting of plugins

= 1.1.2 =
* Fixed "Check all" doesn’t work

= 1.1.1 =
* Fixed white screen on plugins page

= 1.1.0 =
* [NEW] To the list of plugins added the column with a plugin icon.
* The "Deactivate and delete" link has been replaced with "Delete"

= 1.0.1 =
* Temporarily hide settings page
* Up version

= 1.0.0 =
* First release.