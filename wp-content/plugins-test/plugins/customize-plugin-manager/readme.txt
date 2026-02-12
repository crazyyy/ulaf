=== Customize Plugin Manager ===
Contributors: celloexpressions
Tags: customize, plugins
Requires at least: 4.8
Tested up to: 5.7
Stable tag: 0.2
Description: Manage plugins within the customizer (experimental).
License: GPLv2 or later

== Description ==
Live preview in the customizer allows you to see what plugins do to your site instantly. This experimental plugin brings the ability to activate and deactivate plugins from within the customizer. Plugin installation and updates could be a potential future phase depending on the success of this experiment.

Unfortunately, the customizer preview cannot currently reflect changes to the active plugins list. Additionally, plugins are not sandboxed - broken plugins activated from here could break your site. Use this plugin with caution. However, changes are not published to your live site until you "Save and Publish," and the plugin manager does functionally activate and deactivate plugins. Preview functionality requires hooking into WordPress before plugins are loaded (for obvious reasons), which isn't possible to do within a plugin.

See https://core.trac.wordpress.org/ticket/40451 for additional discussion.

== Screenshots ==
1. Plugins section in the customizer.

== Changelog ==
See full details here: https://plugins.trac.wordpress.org/log/customize-plugin-manager.

= 0.2 =
* Fix display of active versus network-active plugins on multisite.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.1 =
* Initial release.
