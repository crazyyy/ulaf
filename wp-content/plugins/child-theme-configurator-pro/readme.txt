=== Child Theme Configurator PRO ===
Contributors: lilaeamedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8QE5YJ8WE96AJ
Tags: child theme, custom theme, CSS, responsive design, CSS editor, theme generator
Requires at least: 5.0
Tested up to: 5.9
Stable tag: 2.3.8
License: see license.txt
License URI: http://www.lilaeamedia.com/license

Extends Child Theme Configurator to customize Plugin styles and enhance the User Interface.

== Description ==

Now you can easily modify styles for any WordPress Plugin installed on your website. Child Theme Configurator PRO scans your plugins and allows you to create custom stylesheets in your Child Theme. IMPORTANT: This is an extension to the main plugin. You must first install Child Theme Configurator 1.7.0 or later.

Why customize plugin styles using the Child Theme Configurator PRO?

* Apply changes in a Child Theme without touching the plugin
* Keep all of your plugin customizations in easy reach for updating
* Identify and override exact selectors from the plugin stylesheet
* Change colors, backgrounds, font styles globally without changing other rules
* Tweak individual style selectors
* Save color palettes for easy selection
* Automatically create and preview CSS3 gradients
* Automatically generate cross-browser and vendor-prefixed rules
* View style changes before committing to them
* Add and modify individual @media queries
* Import web fonts and use them for your plugins
* Save hours of development time

== Installation ==

1. Download Child Theme Configurator PRO plugin from LilaeaMedia.com
2. In the WordPress Admin, go to "Plugins > Add New."
3. Click the "Upload" link at the top of the page.
4. Browse for the zip file, select and click "Install."
5. In the WordPress Admin, go to "Plugins > Installed Plugins." 
6. Locate "Child Theme Configurator PRO" in the list and click "Activate."

== Frequently Asked Questions ==

See Child Theme Configurator Documentation.

== Screenshots ==

1. Location of the Plugin Styles Menu Item
2. Plugin Configurator Parent/Child Tab (with list of Plugin Stylesheets)
3. Recent Edits panel
4. All Styles Tab
5. Update Key Form (Files Tab)

== Changelog 
= 2.3.7 =
Fix get_plugins error notice and minor stylesheet updates.
= 2.3.6 =
*Updated for WP 5.6
*Fixed rendering of main tabs
= 2.3.5 =
* Fixed errors in minified Javascript
* WP 5.5 update removed commonL10n - causing scripts to break - replaced with CTC getxt value.
= 2.3.3 =
* Fixed deprecated curly brace syntax in Packer class 
= 2.3.1 =
* Added CodeMirror to Raw CSS input.
* Prepopulate new rule menu with all properties from CodeMirror list

= 2.3.0 =
* Merged Community and Pro versions into a single standalone plugin.
* Merged plugin mode and Child theme mode into a single interface that writes to a single stylesheet
* Removed custom theme editor since the WordPress core editor was overhauled and it is no longer necessary.
* Added ability to select specific stylesheets for parsing
* Added ability to write to Customizer Custom CSS (inline) option
* Added ability to select target stylesheet

= 2.2.x =
* Fixed copy theme mods to accommodate inconsistent Avia options
* Fixed duplicate editor bug
* Updated for CTC 2.1.0 compatiblility
* Custom version of theme editor:
  * editor depth greater than 1
  * allow js, txt and subdir files in theme editor
* "no plugin stylesheets" notice
* move html output to separate view includes
* remove unused classes
* use helper functions instead of passing object
* import/export mods
* child file extension incorrect when deleting, etc.
* show other files in child files view
* new/duplicate file/dir
* ajax loading on parent/child
* switch between child theme/plugins view button
* plugin stylesheets from analyzer
* extended "copy theme options" to include theme-specific options besides theme_mods

= 2.1.x =
* Fix for incorrect update cron hooks
* Added nonce to preview
* Refactored to be more lightweight on the front-end
* Removed undeclared variable from parent-child form template

= 2.0.x =
* Changed get_option to get_site_option for network WP compatibility.
* Added stylesheet and template filters to keep preview functionality working after WP v4.3 update.
* New Feature: Spectrum color palette from selected colors
* Return to using wp_print_styles because too many plugins are loading after wp_enqueue_scripts
* Added framework support - Genesis
* Fix: Quick preview loads correct theme mods for child theme
* New Features: Live Preview tab, Selector filter and Nav Menu filter for All Styles tab.
* Fix incorrect constant
* Fix: Recent edits disappears when panel is scrolled
* Moved debug output to main plugin
* Fix: obscure bug retrieving options fails on Windows as incomplete object error.
* New Feature: Debug mode 
* Fix: Always use active child theme parent, child and name.
* New Feature: All Styles tab 
* New Feature: Recent Edits tab
* Added upgrade/migration component for users of 1.0.0 and below
* Fix: Completely refactored program to remove dependency on database for front end users 
* Fix: Now uses CTC Additional Stylesheets mechanism to drive the plugin parent sources
* Fix: All styles are now written to a single child theme target CSS file

= 1.1.x =
* Refactored to use wp_filesystem
* Removed 'theme' stylesheets from the source definitions as this is not handled by the main plugin.
* Fixed external update data so it sends the correct download link. Updated to PUC 1.5.
* fixed bug in update checker init
= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.3 =
* Major interface and functionality improvements. See changelog for details.

== Overview ==

Child Theme Configurator PRO makes it easy to make your plugins exactly match your theme.

You can create custom plugin styles from any plugin, even if it uses multiple stylesheets. Child Theme Configurator PRO lets you choose from your installed plugins and save the results in your Child Theme directory.

== Getting Started ==

Read the instructions! If you are not familiar with our Child Theme Configurator, you will want to explore the documentation and videos first. http://www.childthemeplugin.com/documentation/

Now that you are an expert with the Child Theme Configurator, select an existing Parent and Child Theme. 
IMPORTANT: Custom plugin styles are configured for a specific Child Theme, so you must create and select one first.

Your active Source CSS options now appear on the "Parent/Child" tab under the "Parse Plugin Stylesheets" toggle section.

Click "Generate Child Theme Files" and the plugin stylesheet can then be customized in exactly the same way as the Parent Theme stylesheet. If you need a refresher on using the Child Theme Configurator, read the instructions here.

== Update Key ==

Your license includes software updates when they are released. To configure WordPress to automatically alert you of available updates, enter your Update Key in the box under "Extended Settings". Your Update Key was sent with the confirmation email you received with the download link. (See screenshot-5.)

== Need More Help? Contact Us! ==

Maintaining our reputation for excellent support is very important to us, so please do not hesitate to ask any question about this or any of our other plugins, large or small. http://www.lilaeamedia.com/about/contact or solutions@lilaeamedia.com

== Documentation ==

Go to http://www.childthemeplugin.com/child-theme-configurator-pro

https://www.youtube.com/watch?v=Yj8lxF1knTo

https://www.youtube.com/watch?v=z5g9FYygJ9E

(C) 2014-2016 Lilaea Media LLC - All Rights Reserved.
